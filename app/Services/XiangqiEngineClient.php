<?php

namespace App\Services;

use App\Services\Xiangqi\PikafishProcess;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\PhpExecutableFinder;

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
        $this->workerCount = $workerCount ?? (int) config('xiangqi.worker_count', 5);
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
        // than the worker's internal wait (PikafishProcess::analyze).
        $readTimeoutSeconds = PikafishProcess::analysisTimeoutSeconds($depth) + 3.0;

        $response = $this->request([
            'action' => 'analyze',
            'fen' => $fen,
            'depth' => $depth,
        ], $readTimeoutSeconds);

        return $response['analysis'] ?? null;
    }

    /**
     * "Available" means actually able to serve a request (ready), not
     * merely "socket exists and answered a ping".
     *
     * @return array{available:int, total:int}
     */
    public function poolStatus(): array
    {
        $ready = 0;
        for ($i = 0; $i < $this->workerCount; $i++) {
            $resp = $this->pingWorkerRaw($i);
            if (($resp['success'] ?? false) && ($resp['ready'] ?? false)) {
                $ready++;
            }
        }

        if ($ready === 0) {
            $this->triggerPoolEnsure();
        }

        return ['available' => $ready, 'total' => $this->workerCount];
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
     * XiangqiPoolEnsureCommand to decide whether a worker with a live PID
     * is actually serving requests, not just running-but-stuck.
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
     * accepts a connection. Returns an empty array (never throws) if none
     * are available within budget, so callers can fall back gracefully.
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

        // Every socket was missing/refused/timed out. Kick off pool:ensure
        // in the background so the *next* request has a chance.
        $this->triggerPoolEnsure();

        return [];
    }

    /**
     * Fire `php artisan xiangqi:pool:ensure` in the background (detached,
     * non-blocking) the moment we notice zero workers responding.
     *
     * Hardening vs. the previous version:
     *  - Debounce is now atomic (flock + timestamp stored in the file),
     *    so a burst of concurrent requests triggers it exactly once.
     *  - The launcher (setsid/nohup) is resolved to an absolute path from
     *    fixed system directories and shell-quoted, instead of being
     *    looked up through PATH.
     *  - The PHP binary must look like a PHP CLI binary (php, php8.3, ...),
     *    not just be "some executable".
     *  - Every interpolated value is escapeshellarg()'d; the rest of the
     *    command string is a fixed literal, so no request data can reach it.
     *
     * shell_exec is kept (rather than proc_open) because backgrounding with
     * `&` needs a shell, and proc_open's resource destructor would wait on
     * the child and block the HTTP request.
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

        $fh = @fopen($triggerPath, 'c+');
        if ($fh === false) {
            Log::error('xiangqi: could not open pool-ensure trigger lock file: ' . $triggerPath);
            return;
        }

        try {
            // Non-blocking exclusive lock: if another request holds it, that
            // request is already doing the trigger — nothing for us to do.
            if (!flock($fh, LOCK_EX | LOCK_NB)) {
                return;
            }

            // Cooldown check happens *inside* the lock, so it is race-free.
            $lastTriggered = (float) trim((string) stream_get_contents($fh));
            $now = microtime(true);
            if ($lastTriggered > 0 && ($now - $lastTriggered) < $cooldownSeconds) {
                return;
            }

            ftruncate($fh, 0);
            rewind($fh);
            fwrite($fh, (string) $now);
            fflush($fh);

            $phpBinary = $this->resolvePhpBinary();
            if ($phpBinary === null) {
                Log::error(
                    'xiangqi: could not resolve a usable PHP CLI binary to trigger pool:ensure '
                    . '(PhpExecutableFinder and PHP_BINARY both empty/non-executable/not a php binary). '
                    . 'Set XIANGQI_PHP_BINARY in .env and wire it into config/xiangqi.php as php_binary.'
                );
                return;
            }

            $artisan = escapeshellarg(base_path('artisan'));
            $php = escapeshellarg($phpBinary);

            // Absolute path to setsid, else nohup, else none (plain `&`).
            $launcherPath = $this->findSystemBinary(['setsid', 'nohup']);
            $launcher = $launcherPath !== null ? escapeshellarg($launcherPath) . ' ' : '';

            // Detached and output-discarded: this runs on the request thread,
            // so it must return immediately regardless of how long
            // xiangqi:pool:ensure itself takes to finish.
            shell_exec("{$launcher}{$php} {$artisan} xiangqi:pool:ensure > /dev/null 2>&1 < /dev/null &");
        } finally {
            flock($fh, LOCK_UN);
            fclose($fh);
        }
    }

    /**
     * Resolve a real, executable CLI PHP binary.
     *
     * Prefers Symfony's PhpExecutableFinder (reliable across SAPIs), falls
     * back to PHP_BINARY, then to config('xiangqi.php_binary'). The result
     * must be executable AND its basename must look like a PHP CLI binary
     * (php, php8, php8.3), so a bad/overridden config value can't point this
     * at an arbitrary program. Returns null if nothing valid resolves.
     */
    private function resolvePhpBinary(): ?string
    {
        $candidate = (new PhpExecutableFinder())->find(false)
            ?: (PHP_BINARY !== '' ? PHP_BINARY : null)
            ?: config('xiangqi.php_binary');

        if (!$candidate || !is_string($candidate) || !is_executable($candidate)) {
            return null;
        }

        if (!preg_match('/^php(\d+(\.\d+)*)?$/', basename($candidate))) {
            return null;
        }

        return $candidate;
    }

    /**
     * Find an executable by name in a fixed set of system directories
     * (no PATH lookup, no shell). Returns the first match in the order of
     * $names, or null.
     *
     * @param string[] $names
     */
    private function findSystemBinary(array $names): ?string
    {
        $dirs = ['/usr/bin', '/bin', '/usr/local/bin', '/usr/sbin', '/sbin'];

        foreach ($names as $name) {
            foreach ($dirs as $dir) {
                $path = $dir . '/' . $name;
                if (is_file($path) && is_executable($path)) {
                    return $path;
                }
            }
        }

        return null;
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
