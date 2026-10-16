<?php

namespace App\Services\Xiangqi;

use App\Helpers\XiangqiHelper;
use Exception;

/**
 * Wraps a single, long-lived Pikafish UCI subprocess.
 *
 * Instantiated ONCE per worker process (see XiangqiEngineWorkerCommand) and
 * reused for many requests.
 */
class PikafishProcess
{
    private $process;
    private array $pipes = [];
    private string $enginePath;
    private string $networkPath;
    private bool $running = false;
    private bool $ready = false;
    private bool $searching = false; // true while a "go" has not yet produced "bestmove"
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

        // Run from the engine's own directory so it can find side files.
        $this->process = proc_open($this->enginePath, $descriptors, $this->pipes, dirname($this->enginePath), null);

        if (!is_resource($this->process)) {
            throw new Exception('Failed to start Pikafish engine process');
        }

        $this->running = true;
        stream_set_blocking($this->pipes[1], false);
        stream_set_blocking($this->pipes[2], false);
        stream_set_write_buffer($this->pipes[0], 0);

        $this->send('uci');
        $uciResponse = $this->waitFor('uciok', 10.0);
        if (strpos($uciResponse, 'uciok') === false) {
            throw new Exception($this->describeStartupFailure('Engine did not answer "uci"', $uciResponse));
        }

        // Pikafish has no UCI_Variant option (that's Fairy-Stockfish), so it is not sent.
        $this->send('setoption name EvalFile value ' . $this->networkPath);

        $this->send('isready');
        $response = $this->waitFor('readyok', 15.0);

        if (strpos($response, 'readyok') === false) {
            throw new Exception($this->describeStartupFailure('Engine did not become ready', $response));
        }

        $this->ready = true;
    }

    private function describeStartupFailure(string $what, string $stdout): string
    {
        $stderr = '';
        if (isset($this->pipes[2]) && is_resource($this->pipes[2])) {
            $stderr = trim((string) stream_get_contents($this->pipes[2]));
        }

        $state = 'process is still running but silent';
        if (is_resource($this->process)) {
            $status = proc_get_status($this->process);
            if ($status['running'] && is_resource($this->pipes[1] ?? null) && feof($this->pipes[1])) {
                for ($i = 0; $i < 10 && $status['running']; $i++) {
                    usleep(20000);
                    $status = proc_get_status($this->process);
                }
            }
            if (!$status['running']) {
                $code = $status['signaled'] ? 128 + (int) $status['termsig'] : (int) $status['exitcode'];
                $state = $code < 0
                    ? 'process has exited (status unavailable)'
                    : 'process exited with code ' . $code . self::exitCodeHint($code);
            }
        }

        return $what . ' (' . $state . ')'
            . ($stderr !== '' ? ' | stderr: ' . substr($stderr, 0, 500) : '')
            . ($stdout !== '' ? ' | stdout: ' . substr($stdout, 0, 300) : '');
    }

    private static function exitCodeHint(int $code): string
    {
        $hints = [
            126 => ' - found but could not be executed: permission denied, a noexec mount, or a binary built for another CPU architecture',
            127 => ' - not found: missing file or missing shared library',
            132 => ' - illegal instruction: the binary uses CPU features this server lacks',
            139 => ' - segmentation fault',
        ];

        return $hints[$code] ?? '';
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
     * Build a clean 6-field FEN for Pikafish.
     *
     * The app's FEN uses 'r' for Red to move; Pikafish only understands 'w'
     * (Red) and 'b' (Black). Extra trailing tokens (e.g. " resign") are
     * dropped and missing fields are filled in.
     */
    private function toEngineFen(string $fen): string
    {
        $parts = preg_split('/\s+/', trim($fen));
        $color = (($parts[1] ?? 'r') === 'b') ? 'b' : 'w';
        $half = (isset($parts[4]) && ctype_digit($parts[4])) ? $parts[4] : '0';
        $full = (isset($parts[5]) && ctype_digit($parts[5])) ? $parts[5] : '1';

        return "{$parts[0]} {$color} - - {$half} {$full}";
    }

    public function bestMove(string $fen, int $timeoutMs): ?string
    {
        if (!$this->isReady()) {
            throw new Exception('Engine not ready');
        }
        if (!XiangqiHelper::validateFen($fen)) {
            throw new Exception('Invalid Xiangqi FEN position: ' . $fen);
        }

        $this->prepareForSearch();
        $this->send('position fen ' . $this->toEngineFen($fen));
        $this->send('go movetime ' . $timeoutMs);
        $this->searching = true;

        $graceSeconds = 3.0;
        $output = $this->readUntil('bestmove', ($timeoutMs / 1000) + $graceSeconds);
        $this->searching = (strpos($output, 'bestmove') === false);

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

        $this->prepareForSearch();
        $this->send('position fen ' . $this->toEngineFen($fen));
        $this->send('go depth ' . $depth);
        $this->searching = true;

        $output = $this->readUntil('bestmove', self::analysisTimeoutSeconds($depth));
        $this->searching = (strpos($output, 'bestmove') === false);

        return $this->parseAnalysis($output);
    }

    /**
     * If a previous search never delivered "bestmove" (timed out), stop it
     * and wait for its output so it can't leak into the next search.
     */
    private function prepareForSearch(): void
    {
        if ($this->searching) {
            $this->send('stop');
            $this->readUntil('bestmove', 2.0);
            $this->searching = false;
        }
        $this->drain();
    }

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
        if (preg_match_all('/score\s+(cp|mate)\s+(-?\d+)/', $output, $m)) {
            $analysis['score_type'] = end($m[1]);
            $analysis['score'] = (int) end($m[2]);
        }
        if (preg_match_all('/\bdepth\s+(\d+)/', $output, $m)) {
            $analysis['depth'] = (int) end($m[1]);
        }
        if (preg_match_all('/\spv\s+([^\r\n]+)/', $output, $m)) {
            $pv = explode(' ', trim(end($m[1])));
            $analysis['pv'] = array_map([XiangqiHelper::class, 'normalizeMove'], array_filter($pv));
        }

        return $analysis;
    }

    private function send(string $command): void
    {
        if (!$this->running || !is_resource($this->process)) {
            throw new Exception('Engine process is not running');
        }
        fwrite($this->pipes[0], $command . "\n");
        fflush($this->pipes[0]);
    }

    /** Non-blocking drain of stdout and stderr (a full stderr pipe would block the engine). */
    private function drain(): void
    {
        $this->buffer = '';
        while (($chunk = fread($this->pipes[1], 8192)) !== false && $chunk !== '') {
            // discard
        }
        if (is_resource($this->pipes[2] ?? null)) {
            while (($chunk = fread($this->pipes[2], 8192)) !== false && $chunk !== '') {
                // discard
            }
        }
    }

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
                break;
            }
            if ($changed === 0) {
                continue;
            }

            $chunk = fread($this->pipes[1], 8192);
            if ($chunk === false || $chunk === '') {
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
        $this->searching = false;
    }

    public function __destruct()
    {
        $this->stop();
    }
}
