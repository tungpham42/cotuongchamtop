<?php

namespace App\Console\Commands;

use App\Services\Xiangqi\PikafishProcess;
use App\Services\Xiangqi\WorkerSupervisor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * `php artisan xiangqi:engine-worker {id}`
 *
 * Runs forever as ONE OS process, launched (by WorkerSupervisor, from
 * either XiangqiPoolEnsureCommand or another worker's watchdog — see
 * below) inside a detached respawn loop. Each worker has a unique id, its
 * own socket, and its own pre-warmed Pikafish subprocess.
 *
 * Protocol on the Unix socket: one line of JSON in, one line of JSON out.
 *   in:  {"action":"best_move","fen":"...","timeout":3000}
 *   in:  {"action":"analyze","fen":"...","depth":15}
 *   in:  {"action":"ping"}
 *   out: {"success":true,"move":"e3e4"}  /  {"success":true,"analysis":{...}}
 *        {"success":false,"error":"..."}
 *
 * This process handles ONE connection at a time (accept -> handle ->
 * close -> accept). That's fine: the client (XiangqiEngineClient) tries
 * several worker sockets and falls back gracefully if all are momentarily
 * busy, so we don't need an async event loop here.
 *
 * SELF-HEALING, top to bottom:
 *   1. If the Pikafish SUBPROCESS dies but this PHP worker process is
 *      still alive, handle() below notices (isReady() check after each
 *      connection) and restarts just the subprocess — no OS process churn
 *      at all.
 *   2. If this whole PHP worker process dies (crash, OOM, an unhandled
 *      exception), the detached shell loop it was launched inside
 *      (WorkerSupervisor::launchWorker) restarts it within ~1-2s.
 *   3. PEER WATCHDOG (new): while idle waiting for a connection, this
 *      worker also periodically checks on ONE neighbor — worker N watches
 *      worker (N+1) mod workerCount, forming a ring — and spawns it
 *      directly via WorkerSupervisor if it's down. This covers the case
 *      where even a neighbor's respawn loop is gone (e.g. both the loop
 *      and its child got OOM-killed together): as long as ANY worker in
 *      the ring is alive, it will eventually notice and repair the gap,
 *      with no dependency on cron or a central "ensure" process at all.
 *   4. `xiangqi:pool:ensure` (manual, cron every minute, and triggered
 *      on-demand by XiangqiEngineClient when a web request finds the
 *      whole pool down) is the final backstop for the case none of the
 *      above can reach: nothing was ever started in the first place.
 */
class XiangqiEngineWorkerCommand extends Command
{
    protected $signature = 'xiangqi:engine-worker
        {id : Worker id, e.g. 0, 1, 2 — determines the socket filename}
        {--socket-dir= : Directory for the unix socket / pid file (defaults to config)}';

    protected $description = 'Run one persistent, pre-warmed Pikafish worker listening on a unix socket';

    /**
     * How often (in idle accept-timeout ticks, each ~5s) this worker
     * checks on its watched neighbor. Every tick would still be cheap —
     * a ping to a local unix socket plus a pid check — but there's no
     * need to check more often than a neighbor's own boot-grace window
     * could meaningfully change, so this trades a little detection
     * latency for less log/ping noise. 3 ticks ≈ 15s.
     */
    private const WATCHDOG_EVERY_N_IDLE_TICKS = 3;

    public function handle(): int
    {
        $id = (int) $this->argument('id');
        $socketDir = $this->option('socket-dir') ?: config('xiangqi.socket_dir', storage_path('app/xiangqi'));
        $socketPath = rtrim($socketDir, '/') . "/engine-{$id}.sock";
        $pidPath = rtrim($socketDir, '/') . "/engine-{$id}.pid";
        $workerCount = (int) config('xiangqi.worker_count', 12);

        if (!is_dir($socketDir)) {
            mkdir($socketDir, 0770, true);
        }
        if (file_exists($socketPath)) {
            unlink($socketPath);
        }

        // Record our own PID so WorkerSupervisor (whether invoked by
        // xiangqi:pool:ensure or by a neighbor's watchdog) can tell later
        // whether we're still alive.
        file_put_contents($pidPath, getmypid());

        $enginePath = storage_path('engines/pikafish_vps');
        $networkPath = storage_path('engines/pikafish.nnue');

        $this->info("[worker {$id}] starting Pikafish (pid " . getmypid() . ")...");
        $engine = new PikafishProcess($enginePath, $networkPath);
        $engine->start();
        $this->info("[worker {$id}] engine ready");

        $server = stream_socket_server("unix://{$socketPath}", $errno, $errstr);
        if (!$server) {
            $this->error("[worker {$id}] failed to bind socket: {$errstr}");
            @unlink($pidPath);
            return self::FAILURE;
        }
        chmod($socketPath, 0660);
        $this->info("[worker {$id}] listening on {$socketPath}");

        // Watch exactly one neighbor, forming a ring across the whole
        // pool. Skip entirely for a single-worker pool (nothing else to
        // watch). Picking (id+1) mod N rather than something random keeps
        // coverage complete and easy to reason about: every id has
        // exactly one watcher and watches exactly one other id.
        $watchId = $workerCount > 1 ? ($id + 1) % $workerCount : null;
        $supervisor = $watchId !== null ? new WorkerSupervisor($socketDir, $workerCount) : null;
        $idleTicks = 0;

        if ($watchId !== null) {
            $this->info("[worker {$id}] watching worker {$watchId} as part of the self-heal ring");
        }

        // Handle graceful shutdown (SIGTERM from xiangqi:pool:stop, or
        // SIGINT if run in a foreground terminal for testing).
        $shouldStop = false;
        if (function_exists('pcntl_signal')) {
            pcntl_async_signals(true);
            pcntl_signal(SIGTERM, function () use (&$shouldStop) { $shouldStop = true; });
            pcntl_signal(SIGINT, function () use (&$shouldStop) { $shouldStop = true; });
        }

        while (!$shouldStop) {
            $conn = @stream_socket_accept($server, 5); // 5s so we can check $shouldStop periodically
            if ($conn === false) {
                if ($supervisor !== null && ++$idleTicks >= self::WATCHDOG_EVERY_N_IDLE_TICKS) {
                    $idleTicks = 0;
                    $this->checkOnNeighbor($supervisor, $id, $watchId);
                }
                continue;
            }

            try {
                $this->handleConnection($conn, $engine, $id);
            } catch (\Throwable $e) {
                Log::error("[xiangqi worker {$id}] " . $e->getMessage());
                @fwrite($conn, json_encode(['success' => false, 'error' => $e->getMessage()]) . "\n");
            } finally {
                fclose($conn);
            }

            // Self-heal: if the engine subprocess died (crash, OOM, etc.),
            // restart it before serving the next request instead of
            // silently failing forever.
            if (!$engine->isReady()) {
                Log::warning("[xiangqi worker {$id}] engine not ready, restarting");
                $engine->stop();
                $engine = new PikafishProcess($enginePath, $networkPath);
                $engine->start();
            }
        }

        $engine->stop();
        fclose($server);
        @unlink($socketPath);
        @unlink($pidPath);
        $this->info("[worker {$id}] stopped");
        return self::SUCCESS;
    }

    /**
     * Checks on the one neighbor this worker is responsible for and
     * spawns it (via the shared, lock-protected WorkerSupervisor) if it's
     * down. Any failure here is logged and swallowed — a watchdog hiccup
     * should never take down the worker actually serving requests.
     */
    private function checkOnNeighbor(WorkerSupervisor $supervisor, int $selfId, int $watchId): void
    {
        try {
            $launched = $supervisor->ensureWorker($watchId);
            if ($launched) {
                $this->warn("[worker {$selfId}] ring watchdog: worker {$watchId} was down — spawn triggered");
            }
        } catch (\Throwable $e) {
            Log::error("[xiangqi worker {$selfId}] ring watchdog error for worker {$watchId}: " . $e->getMessage());
        }
    }

    private function handleConnection($conn, PikafishProcess $engine, int $id): void
    {
        stream_set_timeout($conn, 2);
        $line = fgets($conn, 65536);
        if ($line === false) {
            return;
        }

        $request = json_decode(trim($line), true);
        if (!is_array($request) || !isset($request['action'])) {
            fwrite($conn, json_encode(['success' => false, 'error' => 'Malformed request']) . "\n");
            return;
        }

        switch ($request['action']) {
            case 'ping':
                fwrite($conn, json_encode(['success' => true, 'ready' => $engine->isReady(), 'worker' => $id]) . "\n");
                break;

            case 'best_move':
                $fen = $request['fen'] ?? '';
                $timeout = (int) ($request['timeout'] ?? 3000);
                $move = $engine->bestMove($fen, $timeout);
                fwrite($conn, json_encode(['success' => (bool) $move, 'move' => $move]) . "\n");
                break;

            case 'analyze':
                $fen = $request['fen'] ?? '';
                $depth = (int) ($request['depth'] ?? 15);
                $analysis = $engine->analyze($fen, $depth);
                fwrite($conn, json_encode(['success' => true, 'analysis' => $analysis]) . "\n");
                break;

            default:
                fwrite($conn, json_encode(['success' => false, 'error' => 'Unknown action']) . "\n");
        }
    }
}
