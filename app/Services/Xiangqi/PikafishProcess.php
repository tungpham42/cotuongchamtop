<?php

namespace App\Services\Xiangqi;

use App\Helpers\XiangqiHelper;
use Exception;

/**
 * Wraps a single, long-lived Pikafish UCI subprocess.
 *
 * This class is meant to be instantiated ONCE per worker process (see
 * XiangqiEngineWorkerCommand) and reused for many requests, not created
 * per HTTP request. That's the whole point of the refactor: the expensive
 * proc_open() + UCI handshake + network load happens once, at worker boot,
 * not on every "best move" call.
 *
 * I/O is done with stream_select() so we block efficiently on the pipe
 * instead of spinning in a usleep() loop burning CPU while waiting.
 */
class PikafishProcess
{
    private $process;
    private array $pipes = [];
    private string $enginePath;
    private string $networkPath;
    private bool $running = false;
    private bool $ready = false;
    private string $buffer = '';

    public function __construct(string $enginePath, string $networkPath)
    {
        $this->enginePath = $enginePath;
        $this->networkPath = $networkPath;
    }

    public function start(): void
    {
        if (!file_exists($this->enginePath)) {
            throw new Exception("Pikafish engine not found at: {$this->enginePath}");
        }
        if (!file_exists($this->networkPath)) {
            throw new Exception("Neural network file not found at: {$this->networkPath}");
        }
        if (!is_executable($this->enginePath)) {
            chmod($this->enginePath, 0755);
        }

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $this->process = proc_open($this->enginePath, $descriptors, $this->pipes, null, null);

        if (!is_resource($this->process)) {
            throw new Exception('Failed to start Pikafish engine process');
        }

        $this->running = true;
        stream_set_blocking($this->pipes[1], false);
        stream_set_blocking($this->pipes[2], false);
        stream_set_write_buffer($this->pipes[0], 0);

        $this->send('uci');
        $this->waitFor('uciok', 10.0);

        $this->send('setoption name UCI_Variant value xiangqi');
        $this->send('setoption name EvalFile value ' . $this->networkPath);

        // No fixed sleep here. We just wait (with a select-based, non-busy
        // wait) for the engine to actually confirm it's ready, up to a
        // generous ceiling for network loading. If it's ready in 300ms we
        // move on in 300ms, not 2000ms.
        $this->send('isready');
        $response = $this->waitFor('readyok', 15.0);

        if (strpos($response, 'readyok') === false) {
            throw new Exception('Engine did not become ready: ' . substr($response, 0, 300));
        }

        $this->ready = true;
    }

    public function isAlive(): bool
    {
        if (!$this->running || !is_resource($this->process)) {
            return false;
        }
        $status = proc_get_status($this->process);
        if (!$status['running']) {
            $this->running = false;
            return false;
        }
        return true;
    }

    public function isReady(): bool
    {
        return $this->ready && $this->isAlive();
    }

    /**
     * Ask for the best move on a position. Uses stream_select to wait for
     * output instead of a usleep() poll loop, so we only wake up when data
     * is actually available and never spend more wall-clock time waiting
     * than $timeoutMs plus a small grace window.
     */
    public function bestMove(string $fen, int $timeoutMs): ?string
    {
        if (!$this->isReady()) {
            throw new Exception('Engine not ready');
        }
        if (!XiangqiHelper::validateFen($fen)) {
            throw new Exception('Invalid Xiangqi FEN position: ' . $fen);
        }

        $this->drain();
        $this->send('position fen ' . $fen);
        $this->send('go movetime ' . $timeoutMs);

        $graceSeconds = 3.0;
        $output = $this->readUntil('bestmove', ($timeoutMs / 1000) + $graceSeconds);

        if (preg_match('/bestmove\s+(\S+)/', $output, $m)) {
            $move = trim($m[1]);
            if ($move && $move !== '(none)' && $move !== '0000' && $move !== 'resign') {
                return XiangqiHelper::normalizeMove($move);
            }
        }

        return null;
    }

    public function analyze(string $fen, int $depth): array
    {
        if (!$this->isReady()) {
            throw new Exception('Engine not ready');
        }
        if (!XiangqiHelper::validateFen($fen)) {
            throw new Exception('Invalid Xiangqi FEN position: ' . $fen);
        }

        $this->drain();
        $this->send('position fen ' . $fen);
        $this->send('go depth ' . $depth);

        $output = $this->readUntil('bestmove', self::analysisTimeoutSeconds($depth));

        return $this->parseAnalysis($output);
    }

    /**
     * Depth-scaled time budget for a "go depth N" search to return
     * bestmove. A flat ceiling (the old hardcoded 30.0) is wrong in both
     * directions: deep searches (depth approaching the controller's max
     * of 30) can legitimately run past 30s and get cut off mid-search,
     * while shallow searches (depth 5-10, which typically resolve in a
     * couple seconds) hold the pipe open far longer than they need to.
     *
     * This is intentionally the single source of truth for that budget:
     * XiangqiEngineClient::analyzePosition() calls this same method (it's
     * a pure calculation, no I/O) to size its socket read timeout, so the
     * web-request side and the worker side never disagree about how long
     * a given depth is allowed to take.
     */
    public static function analysisTimeoutSeconds(int $depth): float
    {
        $depth = max(1, $depth);
        $seconds = 4.0 + ($depth * 1.2);

        return min(max($seconds, 5.0), 60.0);
    }

    private function parseAnalysis(string $output): array
    {
        $analysis = [
            'best_move' => null,
            'score' => null,
            'score_type' => null,
            'depth' => null,
            'pv' => [],
        ];

        if (preg_match('/bestmove\s+(\S+)/', $output, $m)) {
            $analysis['best_move'] = XiangqiHelper::normalizeMove($m[1]);
        }
        if (preg_match('/score\s+(cp|mate)\s+(-?\d+)/', $output, $m)) {
            $analysis['score_type'] = $m[1];
            $analysis['score'] = (int) $m[2];
        }
        if (preg_match('/\bdepth\s+(\d+)/', $output, $m)) {
            $analysis['depth'] = (int) $m[1];
        }
        if (preg_match('/\spv\s+([^\r\n]+)/', $output, $m)) {
            $pv = explode(' ', trim($m[1]));
            $analysis['pv'] = array_map([XiangqiHelper::class, 'normalizeMove'], array_filter($pv));
        }

        return $analysis;
    }

    /** Send a UCI command. No artificial delay — fflush is enough. */
    private function send(string $command): void
    {
        if (!$this->running || !is_resource($this->process)) {
            throw new Exception('Engine process is not running');
        }
        fwrite($this->pipes[0], $command . "\n");
        fflush($this->pipes[0]);
    }

    /** Non-blocking drain of anything left in stdout from a previous call. */
    private function drain(): void
    {
        $this->buffer = '';
        while (($chunk = fread($this->pipes[1], 8192)) !== false && $chunk !== '') {
            // discard
        }
    }

    /**
     * Block (efficiently, via stream_select) until $needle appears in
     * stdout or $timeoutSeconds elapses. Returns everything read.
     */
    private function readUntil(string $needle, float $timeoutSeconds): string
    {
        $deadline = microtime(true) + $timeoutSeconds;
        $out = $this->buffer;
        $this->buffer = '';

        if (strpos($out, $needle) !== false) {
            return $out;
        }

        while (microtime(true) < $deadline) {
            $remaining = $deadline - microtime(true);
            $read = [$this->pipes[1]];
            $write = null;
            $except = null;

            $sec = (int) floor(max($remaining, 0));
            $usec = (int) (($remaining - $sec) * 1_000_000);
            $changed = @stream_select($read, $write, $except, $sec, $usec);

            if ($changed === false) {
                break; // interrupted / error — return what we have
            }
            if ($changed === 0) {
                continue; // timed out this iteration, loop re-checks deadline
            }

            $chunk = fread($this->pipes[1], 8192);
            if ($chunk === false || $chunk === '') {
                // EOF or nothing to read right now
                if (feof($this->pipes[1])) {
                    $this->running = false;
                    break;
                }
                continue;
            }

            $out .= $chunk;
            if (strpos($out, $needle) !== false) {
                return $out;
            }
        }

        return $out;
    }

    private function waitFor(string $needle, float $timeoutSeconds): string
    {
        return $this->readUntil($needle, $timeoutSeconds);
    }

    public function stop(): void
    {
        if (is_array($this->pipes)) {
            foreach ($this->pipes as $pipe) {
                if (is_resource($pipe)) {
                    fclose($pipe);
                }
            }
        }
        if (is_resource($this->process)) {
            proc_terminate($this->process);
            proc_close($this->process);
        }
        $this->running = false;
        $this->ready = false;
    }

    public function __destruct()
    {
        $this->stop();
    }
}
