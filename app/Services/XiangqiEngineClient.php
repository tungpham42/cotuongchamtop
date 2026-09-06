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
