<?php

namespace App\Console\Commands;

use App\Services\Xiangqi\PikafishProcess;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * `php artisan xiangqi:engine-worker {id}`
 *
 * Runs forever as ONE OS process. On CentOS 9 it is launched and kept
 * alive by supervisord (see deploy/supervisord/xiangqi-workers.ini),
 * NOT by a hand-rolled shell respawn loop or a peer-watchdog ring — both
 * of those have been deleted. Each worker still has a unique id, its own
 * socket, and its own pre-warmed Pikafish subprocess.
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
 * SELF-HEALING, top to bottom (much shorter than it used to be):
 *   1. If the Pikafish SUBPROCESS dies but this PHP worker process is
 *      still alive, handle() below notices (isReady() check after each
 *      connection) and restarts just the subprocess — no OS process churn
 *      at all. This is the one layer that has to live in-process, since
 *      supervisord only sees this PHP process, not the child engine
 *      binary it manages internally via proc_open().
 *   2. If this whole PHP worker process dies (crash, OOM, an unhandled
 *      exception), supervisord (autorestart=true) restarts it. This
 *      replaces the old detached shell respawn loop AND the ring
 *      watchdog that used to live in this file — supervisord is a single
 *      already-running daemon independent of any worker, so "what
 *      restarts the thing that restarts workers" is no longer a problem
 *      this codebase has to solve for itself.
 *   3. supervisord itself is started by systemd at boot
 *      (systemctl enable supervisord) and restarts every worker
 *      immediately (autostart=true), covering the case that used to need
 *      a cron-driven `xiangqi:pool:ensure` backstop: nothing was ever
 *      started in the first place.
 *
 * Graceful shutdown: supervisord sends SIGTERM on `supervisorctl stop` /
 * during a restart, waits up to `stopwaitsecs` (see the .ini), then
 * SIGKILLs if the process hasn't exited. We still handle SIGTERM/SIGINT
 * below so a stop is clean (socket/pid files removed, engine subprocess
 * terminated) rather than relying on the SIGKILL fallback every time.
 */
class XiangqiEngineWorkerCommand extends Command
{
    protected $signature = 'xiangqi:engine-worker
        {id : Worker id, e.g. 0, 1, 2 — determines the socket filename}
        {--socket-dir= : Directory for the unix socket / pid file (defaults to config)}';

    protected $description = 'Run one persistent, pre-warmed Pikafish worker listening on a unix socket';

    public function handle(): int
    {
        $id = (int) $this->argument('id');
        $socketDir = $this->option('socket-dir') ?: config('xiangqi.socket_dir', storage_path('app/xiangqi'));
        $socketPath = rtrim($socketDir, '/') . "/engine-{$id}.sock";
        $pidPath = rtrim($socketDir, '/') . "/engine-{$id}.pid";

        if (!is_dir($socketDir)) {
            mkdir($socketDir, 0770, true);
        }
        if (file_exists($socketPath)) {
            unlink($socketPath);
        }

        // Record our own PID purely for diagnostics/health checks now —
        // nothing in-app reads it to decide whether to respawn us
        // anymore (supervisord tracks that itself), but it's still handy
        // for `kill -0` sanity checks from ops tooling.
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

        // Handle graceful shutdown (SIGTERM from `supervisorctl stop` or
        // a restart; SIGINT if run in a foreground terminal for testing).
        $shouldStop = false;
        if (function_exists('pcntl_signal')) {
            pcntl_async_signals(true);
            pcntl_signal(SIGTERM, function () use (&$shouldStop) { $shouldStop = true; });
            pcntl_signal(SIGINT, function () use (&$shouldStop) { $shouldStop = true; });
        }

        while (!$shouldStop) {
            // Short accept timeout just so we notice $shouldStop promptly;
            // there's no watchdog tick to drive anymore.
            $conn = @stream_socket_accept($server, 5);
            if ($conn === false) {
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
            // silently failing forever. supervisord has no visibility
            // into this child process, so this has to stay in-app.
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
