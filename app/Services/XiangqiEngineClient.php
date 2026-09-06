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
 *
 * SELF-HEAL: each worker now runs inside its own respawn loop (see
 * XiangqiPoolEnsureCommand), so a single crashed worker recovers on its
 * own in about a second and this class never needs to know about that.
 * But if EVERY worker is unreachable — nothing was ever started (fresh
 * deploy / reboot), or every loop somehow died together — this class
 * kicks off `xiangqi:pool:ensure --respect-stop` in the background the
 * moment it notices, rather than silently reporting zero and leaving
 * recovery to whenever the next cron minute happens to land. It's
 * rate-limited via a cooldown file so a burst of concurrent requests
 * during an outage triggers this once, not once per request.
 */
class XiangqiEngineClient
{
    private string $socketDir;
    private int $workerCount;
    private float $connectTimeoutSeconds;

    /**
     * Minimum time between self-heal triggers. Must comfortably exceed a
     * single worker's worst-case boot time (~25-30s, see PikafishProcess
     * and XiangqiPoolEnsureCommand's BOOT_GRACE_SECONDS) so we don't fire
     * a second `pool:ensure` while the first one's launches are still
     * legitimately warming up.
     */
    private const SELF_HEAL_COOLDOWN_SECONDS = 45.0;

    public function __construct(?string $socketDir = null, ?int $workerCount = null, float $connectTimeoutSeconds = 0.15)
    {
        $this->socketDir = $socketDir ?? config('xiangqi.socket_dir', storage_path('app/xiangqi'));
        // Was previously defaulting to 4 here while XiangqiPoolEnsureCommand
        // defaulted to 12 — if config('xiangqi.worker_count') were ever
        // unset, this class would only ever check/ping the first 4 of a
        // 12-worker pool. Keep this default in lockstep with the other
        // classes that read the same config key.
        $this->workerCount = $workerCount ?? (int) config('xiangqi.worker_count', 12);
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
            $this->triggerSelfHeal();
        }

        return ['available' => $available, 'total' => $this->workerCount];
    }

    /**
     * Kicks off `php artisan xiangqi:pool:ensure --respect-stop` in the
     * background so a fully-down pool starts recovering the instant a
     * request notices, instead of waiting for the next cron tick (up to
     * 60s away). --respect-stop means this never fights a deliberate
     * `xiangqi:pool:stop` (e.g. mid-deploy).
     *
     * Fire-and-forget and best-effort: failures here are swallowed. This
     * is a convenience nudge on top of the respawn loops and cron, not a
     * load-bearing recovery path — the caller (poolStatus/request) has
     * already decided what to tell the user regardless of whether this
     * succeeds.
     */
    private function triggerSelfHeal(): void
    {
        $cooldownPath = rtrim($this->socketDir, '/') . '/self-heal.cooldown';

        if (file_exists($cooldownPath)) {
            $last = (float) trim((string) @file_get_contents($cooldownPath));
            if ($last > 0 && (microtime(true) - $last) < self::SELF_HEAL_COOLDOWN_SECONDS) {
                return;
            }
        }

        // Write the cooldown marker BEFORE shelling out so a burst of
        // concurrent requests can't all pass the check above at once.
        if (!is_dir($this->socketDir)) {
            @mkdir($this->socketDir, 0770, true);
        }
        @file_put_contents($cooldownPath, (string) microtime(true));

        $artisan = escapeshellarg(base_path('artisan'));
        $php = escapeshellarg(PHP_BINARY);
        $log = escapeshellarg(rtrim($this->socketDir, '/') . '/self-heal.log');

        $cmd = "{$php} {$artisan} xiangqi:pool:ensure --respect-stop >> {$log} 2>&1 < /dev/null &";
        @shell_exec($cmd);
    }

    public function isAnyWorkerReady(): bool
    {
        for ($i = 0; $i < $this->workerCount; $i++) {
            $resp = $this->pingWorkerRaw($i);
            if (($resp['success'] ?? false) && ($resp['ready'] ?? false)) {
                return true;
            }
        }
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

        // Every socket in the pool refused/timed out for an actual
        // gameplay request (not just a status check) — this is the case
        // that matters most, since it's what a real player would hit.
        // Nudge recovery the same way poolStatus() does.
        $this->triggerSelfHeal();

        return [];
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
