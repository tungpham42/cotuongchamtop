<?php

namespace App\Services;

use App\Services\Xiangqi\PikafishProcess;

/**
 * Talks to Pikafish directly — no worker pool, no unix sockets, no
 * background processes for cron/Supervisor to keep alive.
 *
 * Each call to getBestMove()/analyzePosition() spawns a fresh
 * PikafishProcess, runs the UCI handshake, answers the one request, and
 * shuts the process back down. PikafishProcess::start() has no artificial
 * sleep in it — it moves on the instant the engine actually reports
 * uciok/readyok (via stream_select, not a poll loop) — so this is as
 * prompt as a cold engine start can be, it just isn't free: expect the
 * handshake (uciok up to 10s, readyok up to 15s ceiling, usually far
 * faster in practice) to run on every request.
 *
 * Constructing this class does no I/O at all (just two storage_path()
 * lookups), so routes that never touch the engine — validateFen,
 * switchActiveColor, getPieceInfo, etc. — still pay nothing, same
 * guarantee the previous pool-backed client gave.
 */
class XiangqiEngineClient
{
    private string $enginePath;
    private string $networkPath;

    public function __construct(?string $enginePath = null, ?string $networkPath = null)
    {
        $this->enginePath = $enginePath ?? config('xiangqi.engine_path', storage_path('engines/pikafish_vps'));
        $this->networkPath = $networkPath ?? config('xiangqi.network_path', storage_path('engines/pikafish.nnue'));
    }

    public function getBestMove(string $fen, int $timeoutMs = 3000): ?string
    {
        $engine = $this->boot();

        try {
            return $engine->bestMove($fen, $timeoutMs);
        } finally {
            $engine->stop();
        }
    }

    public function analyzePosition(string $fen, int $depth = 15): ?array
    {
        $engine = $this->boot();

        try {
            return $engine->analyze($fen, $depth);
        } finally {
            $engine->stop();
        }
    }

    /**
     * Cheap sanity check for status/health endpoints: just confirms the
     * binary and network file are present (and the binary executable),
     * without actually spawning an engine. There's no pool to ping.
     */
    public function isEngineAvailable(): bool
    {
        return file_exists($this->enginePath)
            && is_executable($this->enginePath)
            && file_exists($this->networkPath);
    }

    private function boot(): PikafishProcess
    {
        $engine = new PikafishProcess($this->enginePath, $this->networkPath);
        $engine->start();

        return $engine;
    }
}
