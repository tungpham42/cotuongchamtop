<?php

namespace App\Services;

use App\Services\Xiangqi\PikafishProcess;

/**
 * Thin, cheap-to-construct client for the warm Pikafish worker sockets.
 *
 * Each request takes an exclusive per-worker lock (engine-N.busy) so it only
 * ever talks to a FREE worker. Without this, a unix socket happily accepts
 * connections into its backlog while the worker is mid-search, so requests
 * queued behind busy workers, timed out, and got random fallback moves.
 */
class XiangqiEngineClient
{
    /** Max time a request waits for any worker to become free. */
    private const MAX_WAIT_FOR_FREE_WORKER = 1.5;

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
        $ready = 0;
        for ($i = 0; $i < $this->workerCount; $i++) {
            if ($this->pingWorker($i, 1.0)) {
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
            if ($this->pingWorker($i, 1.0)) {
                return true;
            }
        }

        $this->triggerPoolEnsure();

        return false;
    }

    /**
     * True if worker $id answered a ping with ready=true.
     * $timeout is how long to wait for the answer; a busy worker only
     * answers after its current search, so callers that must not
     * misjudge a busy worker as dead should pass a longer value.
     */
    public function pingWorker(int $id, float $timeout = 0.2): bool
    {
        $resp = $this->requestOnSocket($this->socketPath($id), ['action' => 'ping'], $timeout) ?? [];
        return ($resp['success'] ?? false) && ($resp['ready'] ?? false);
    }

    /**
     * Send $payload to the first FREE, healthy worker. Returns [] (never
     * throws) if none could serve it, so callers can fall back gracefully.
     */
    private function request(array $payload, float $readTimeoutSeconds): array
    {
        $deadline = microtime(true) + self::MAX_WAIT_FOR_FREE_WORKER;

        do {
            $order = range(0, $this->workerCount - 1);
            shuffle($order);
            $anyBusy = false;

            foreach ($order as $id) {
                $lock = $this->tryLockWorker($id);
                if ($lock === null) {
                    $anyBusy = true; // serving another request right now
                    continue;
                }

                try {
                    $result = $this->requestOnSocket($this->socketPath($id), $payload, $readTimeoutSeconds);
                } finally {
                    flock($lock, LOCK_UN);
                    fclose($lock);
                }

                // An "error" reply (e.g. "Engine not ready") means: try another worker.
                if ($result !== null && !isset($result['error'])) {
                    return $result;
                }
            }

            if (!$anyBusy) {
                break; // nobody is merely busy -> pool is down, don't wait
            }
            usleep(50_000);
        } while (microtime(true) < $deadline);

        $this->triggerPoolEnsure();

        return [];
    }

    /** @return resource|null exclusive lock on worker $id, or null if it's in use */
    private function tryLockWorker(int $id)
    {
        if (!is_dir($this->socketDir)) {
            return null;
        }

        $handle = @fopen(rtrim($this->socketDir, '/') . "/engine-{$id}.busy", 'c');
        if (!$handle) {
            return null;
        }
        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            return null;
        }

        return $handle;
    }

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
            return;
        }

        @touch($triggerPath);

        $phpBinary = $this->resolvePhpBinary();
        if ($phpBinary === null) {
            \Illuminate\Support\Facades\Log::error(
                'xiangqi: could not resolve a usable PHP CLI binary to trigger pool:ensure '
                . '(PhpExecutableFinder and PHP_BINARY both empty/non-executable). '
                . 'Set XIANGQI_PHP_BINARY in .env and wire it into config/xiangqi.php as php_binary.'
            );
            return;
        }

        $artisan = escapeshellarg(base_path('artisan'));
        $php = escapeshellarg($phpBinary);
        $launcher = trim((string) shell_exec('command -v setsid')) !== '' ? 'setsid' : 'nohup';

        shell_exec("{$launcher} {$php} {$artisan} xiangqi:pool:ensure > /dev/null 2>&1 < /dev/null &");
    }

    private function resolvePhpBinary(): ?string
    {
        $candidate = (new \Symfony\Component\Process\PhpExecutableFinder())->find(false)
            ?: (PHP_BINARY !== '' ? PHP_BINARY : null)
            ?: config('xiangqi.php_binary');

        return ($candidate && is_executable($candidate)) ? $candidate : null;
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
            return null;
        }

        $seconds = (int) floor($readTimeoutSeconds);
        $micro = (int) (($readTimeoutSeconds - $seconds) * 1_000_000);
        stream_set_timeout($conn, $seconds, $micro);
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
