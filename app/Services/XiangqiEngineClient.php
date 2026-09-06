<?php

namespace App\Services;

use App\Services\Xiangqi\PikafishProcess;

/**
 * Replaces the old XiangqiEngineService for the web request path.
 *
 * This is intentionally "dumb" and cheap to construct: it does NOT start
 * any engine process. It just knows where the warm worker sockets live and
 * talks to whichever one is free. If every worker is momentarily busy, it
 * fails fast (short connect timeout) rather than blocking the HTTP request
 * for seconds — the controller already has a fallback move generator for
 * exactly this case.
 */
class XiangqiEngineClient
{
    private string $socketDir;
    private int $workerCount;
    private float $connectTimeoutSeconds;

    public function __construct(?string $socketDir = null, ?int $workerCount = null, float $connectTimeoutSeconds = 0.15)
    {
        $this->socketDir = $socketDir ?? config('xiangqi.socket_dir', storage_path('app/xiangqi'));
        $this->workerCount = $workerCount ?? (int) config('xiangqi.worker_count', 4);
        $this->connectTimeoutSeconds = $connectTimeoutSeconds;
    }

    public function getBestMove(string $fen, int $timeoutMs = 3000): ?string
    {
        $response = $this->request([
            'action' => 'best_move',
            'fen' => $fen,
            'timeout' => $timeoutMs,
        ], ($timeoutMs / 1000) + 3.0);

        return $response['move'] ?? null;
    }

    public function analyzePosition(string $fen, int $depth = 15): ?array
    {
        // Same +3.0s IPC/queueing grace getBestMove() adds on top of the
        // engine's own budget — the socket timeout must stay a bit looser
        // than the worker's internal wait (PikafishProcess::analyze), or
        // we'd give up on the response a moment before the worker sends
        // it. Using the same formula the worker uses (via the shared
        // static method) means this never drifts out of sync with it.
        $readTimeoutSeconds = PikafishProcess::analysisTimeoutSeconds($depth) + 3.0;

        $response = $this->request([
            'action' => 'analyze',
            'fen' => $fen,
            'depth' => $depth,
        ], $readTimeoutSeconds);

        return $response['analysis'] ?? null;
    }

    /**
     * @return array{available:int, total:int}
     */
    public function poolStatus(): array
    {
        $available = 0;
        for ($i = 0; $i < $this->workerCount; $i++) {
            $resp = $this->pingWorkerRaw($i);
            if ($resp['success'] ?? false) {
                $available++;
            }
        }

        if ($available === 0) {
            $this->triggerPoolEnsure();
        }

        return ['available' => $available, 'total' => $this->workerCount];
    }

    public function isAnyWorkerReady(): bool
    {
        for ($i = 0; $i < $this->workerCount; $i++) {
            $resp = $this->pingWorkerRaw($i);
            if (($resp['success'] ?? false) && ($resp['ready'] ?? false)) {
                return true;
            }
        }

        $this->triggerPoolEnsure();

        return false;
    }

    /**
     * True if worker $id responded ready to a ping. Used by
     * XiangqiPoolEnsureCommand (the cron-driven Supervisor replacement)
     * to decide whether a worker with a live PID is actually serving
     * requests, not just running-but-stuck.
     */
    public function pingWorker(int $id): bool
    {
        $resp = $this->pingWorkerRaw($id);
        return ($resp['success'] ?? false) && ($resp['ready'] ?? false);
    }

    private function pingWorkerRaw(int $id): array
    {
        return $this->requestOnSocket($this->socketPath($id), ['action' => 'ping'], 0.2) ?? [];
    }

    /**
     * Try each worker socket in random order; use the first one that
     * accepts a connection. Returns null (never throws) if none are
     * available within budget, so callers can fall back gracefully.
     */
    private function request(array $payload, float $readTimeoutSeconds): array
    {
        $order = range(0, $this->workerCount - 1);
        shuffle($order);

        foreach ($order as $id) {
            $result = $this->requestOnSocket($this->socketPath($id), $payload, $readTimeoutSeconds);
            if ($result !== null) {
                return $result;
            }
        }

        // Every socket was missing/refused/timed out — from this request's
        // point of view the pool is fully down. Kick off pool:ensure in the
        // background so the *next* request has a chance, instead of every
        // request failing silently until the next cron tick (up to 60s away).
        $this->triggerPoolEnsure();

        return [];
    }

    /**
     * Fire `php artisan xiangqi:pool:ensure` in the background (detached,
     * non-blocking — same setsid/nohup pattern XiangqiPoolEnsureCommand
     * itself uses to launch workers) the moment we notice zero workers
     * responding, rather than waiting for the next scheduler tick.
     *
     * Debounced via a lock file: xiangqi:pool:ensure already takes its own
     * flock, which prevents overlapping *runs*, but does nothing to stop a
     * burst of concurrent web requests during an outage from each calling
     * shell_exec() here and piling up processes in the table while one
     * ensure run is still mid-flight (which, remember, can legitimately
     * take BOOT_GRACE_SECONDS-ish per cold-started worker). This cooldown
     * makes a storm of simultaneous callers trigger it once.
     */
    private function triggerPoolEnsure(): void
    {
        if (!config('xiangqi.auto_ensure_on_empty', true)) {
            return;
        }

        if (!is_dir($this->socketDir)) {
            @mkdir($this->socketDir, 0770, true);
        }

        $triggerPath = rtrim($this->socketDir, '/') . '/pool-ensure-trigger.lock';
        $cooldownSeconds = (float) config('xiangqi.auto_ensure_cooldown_seconds', 10.0);

        $lastTriggered = @filemtime($triggerPath);
        if ($lastTriggered !== false && (time() - $lastTriggered) < $cooldownSeconds) {
            return; // triggered recently — let that run finish before we try again
        }

        // Touch the lock file before shelling out, so concurrent requests
        // racing us here see a fresh mtime immediately rather than all
        // passing the check together.
        @touch($triggerPath);

        $artisan = escapeshellarg(base_path('artisan'));
        $php = escapeshellarg(PHP_BINARY);
        $launcher = trim((string) shell_exec('command -v setsid')) !== '' ? 'setsid' : 'nohup';

        // Detached and output-discarded: this runs on the request thread,
        // so it must return immediately regardless of how long
        // xiangqi:pool:ensure itself takes to finish.
        shell_exec("{$launcher} {$php} {$artisan} xiangqi:pool:ensure > /dev/null 2>&1 < /dev/null &");
    }

    private function requestOnSocket(string $socketPath, array $payload, float $readTimeoutSeconds): ?array
    {
        if (!file_exists($socketPath)) {
            return null;
        }

        $conn = @stream_socket_client(
            "unix://{$socketPath}",
            $errno,
            $errstr,
            $this->connectTimeoutSeconds,
            STREAM_CLIENT_CONNECT
        );

        if (!$conn) {
            return null; // worker down or refusing (busy) — try next
        }

        stream_set_timeout($conn, (int) ceil($readTimeoutSeconds));
        fwrite($conn, json_encode($payload) . "\n");
        fflush($conn);

        $line = fgets($conn, 65536);
        fclose($conn);

        if ($line === false) {
            return null;
        }

        $decoded = json_decode(trim($line), true);
        return is_array($decoded) ? $decoded : null;
    }

    private function socketPath(int $id): string
    {
        return rtrim($this->socketDir, '/') . "/engine-{$id}.sock";
    }
}
