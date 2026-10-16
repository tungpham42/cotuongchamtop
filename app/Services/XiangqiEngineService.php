<?php

namespace App\Services;

use App\Helpers\XiangqiHelper;
use Illuminate\Support\Facades\Log;

class XiangqiEngineService
{
    private $process;
    private $pipes;
    private $enginePath;
    private $networkPath;
    private $isInitialized = false;
    private $isRunning = false;

    public function __construct()
    {
        $this->enginePath = storage_path('engines/pikafish');
        $this->networkPath = storage_path('engines/pikafish.nnue');

        if (!file_exists($this->enginePath)) {
            throw new \Exception("Pikafish engine not found at: " . $this->enginePath);
        }

        if (!file_exists($this->networkPath)) {
            throw new \Exception("Neural network file not found at: " . $this->networkPath);
        }

        // Make sure the engine is executable
        if (!is_executable($this->enginePath)) {
            chmod($this->enginePath, 0755);
        }

        $this->initializeEngine();
    }

    private function initializeEngine()
    {
        try {
            $descriptorspec = [
                0 => ["pipe", "r"], // stdin
                1 => ["pipe", "w"], // stdout
                2 => ["pipe", "w"]  // stderr
            ];

            $this->process = proc_open($this->enginePath, $descriptorspec, $this->pipes);

            if (!is_resource($this->process)) {
                throw new \Exception("Failed to start Pikafish engine process");
            }

            $this->isRunning = true;

            // Set streams to non-blocking
            stream_set_blocking($this->pipes[1], false);
            stream_set_blocking($this->pipes[2], false);

            // Initialize UCI protocol
            $this->sendCommand('uci');
            $response = $this->waitForResponse('uciok', 10);

            if (strpos($response, 'uciok') === false) {
                throw new \Exception("Engine failed to initialize UCI protocol. Response: " . $response);
            }

            // Set Xiangqi variant and neural network path
            $this->sendCommand('setoption name UCI_Variant value xiangqi');
            $this->sendCommand('setoption name EvalFile value ' . $this->networkPath);

            // Wait a bit for network to load
            usleep(2000000); // 2 seconds

            $this->sendCommand('isready');
            $response = $this->waitForResponse('readyok', 10);

            if (strpos($response, 'readyok') === false) {
                throw new \Exception("Engine is not ready. Response: " . $response);
            }

            $this->isInitialized = true;
            Log::info('Pikafish engine initialized successfully with neural network');

        } catch (\Exception $e) {
            Log::error('Failed to initialize Xiangqi engine: ' . $e->getMessage());
            $this->cleanup();
            throw $e;
        }
    }

    private function sendCommand(string $command)
    {
        if (!$this->isRunning || !is_resource($this->process)) {
            throw new \Exception("Engine process is not running");
        }

        Log::debug("Sending command to engine: " . $command);
        fwrite($this->pipes[0], $command . "\n");
        fflush($this->pipes[0]);
        usleep(100000); // 100ms delay
    }

    private function readOutput(): string
    {
        $output = '';

        if (is_resource($this->pipes[1])) {
            // Read from stdout
            while (($line = fgets($this->pipes[1])) !== false) {
                $output .= $line;
            }
        }

        return $output;
    }

    private function waitForResponse(string $expected, int $timeoutSeconds = 5): string
    {
        $output = '';
        $startTime = microtime(true);

        while ((microtime(true) - $startTime) < $timeoutSeconds) {
            $output .= $this->readOutput();

            if (strpos($output, $expected) !== false) {
                Log::debug("Found expected response: " . $expected);
                return $output;
            }

            usleep(100000); // 100ms
        }

        Log::warning("Timeout waiting for: " . $expected . ". Got: " . substr($output, 0, 200));
        return $output;
    }

    private function getOutput(int $timeoutSeconds = 5): string
    {
        $output = '';
        $startTime = microtime(true);

        while ((microtime(true) - $startTime) < $timeoutSeconds) {
            $output .= $this->readOutput();

            // If we got a bestmove, return immediately
            if (strpos($output, 'bestmove') !== false) {
                return $output;
            }

            usleep(50000); // 50ms
        }

        return $output;
    }

    public function getBestMove(string $fen, int $timeout = 3000): ?string
    {
        if (!$this->isInitialized) {
            throw new \Exception("Engine not initialized");
        }

        if (!XiangqiHelper::validateFen($fen)) {
            throw new \Exception("Invalid Xiangqi FEN position: " . $fen);
        }

        try {
            Log::info("Requesting best move for FEN: " . $fen . " with timeout: " . $timeout . "ms");

            // Clear any previous output
            $this->getOutput(1);

            // Send position command
            $this->sendCommand('position fen ' . $fen);

            // Send go command with movetime
            $this->sendCommand('go movetime ' . $timeout);

            // Wait for response
            $output = $this->getOutput(($timeout / 1000) + 5);

            Log::info("Engine output length: " . strlen($output));
            if (strlen($output) > 500) {
                Log::info("Engine output (first 500 chars): " . substr($output, 0, 500));
            } else {
                Log::info("Engine output: " . $output);
            }

            // Parse bestmove from output
            if (preg_match('/bestmove\s+(\S+)/', $output, $matches)) {
                $bestMove = trim($matches[1]);

                // Validate the move
                if ($bestMove && $bestMove !== '(none)' && $bestMove !== '0000' && $bestMove !== 'resign') {
                    $formattedMove = XiangqiHelper::normalizeMove($bestMove);
                    Log::info("Best move found: " . $formattedMove);
                    return $formattedMove;
                } else {
                    Log::warning("Invalid bestmove received: " . $bestMove);
                }
            } else {
                Log::warning("No bestmove pattern found in output");
                // Log what we did get for debugging
                if (!empty($output)) {
                    Log::warning("Output received: " . $output);
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error getting best move: ' . $e->getMessage());
            return null;
        }
    }

    public function analyzePosition(string $fen, int $depth = 15): array
    {
        if (!$this->isInitialized) {
            throw new \Exception("Engine not initialized");
        }

        $this->sendCommand('position fen ' . $fen);
        $this->sendCommand('go depth ' . $depth);

        $output = $this->getOutput(30);

        return $this->parseAnalysis($output);
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

        if (preg_match('/bestmove\s+(\S+)/', $output, $matches)) {
            $analysis['best_move'] = XiangqiHelper::normalizeMove($matches[1]);
        }

        if (preg_match('/score\s+(cp|mate)\s+(-?\d+)/', $output, $matches)) {
            $analysis['score_type'] = $matches[1];
            $analysis['score'] = (int)$matches[2];
        }

        if (preg_match('/depth\s+(\d+)/', $output, $matches)) {
            $analysis['depth'] = (int)$matches[1];
        }

        if (preg_match('/pv\s+([^\r\n]+)/', $output, $matches)) {
            $pvMoves = explode(' ', trim($matches[1]));
            $analysis['pv'] = array_map([XiangqiHelper::class, 'normalizeMove'], array_filter($pvMoves));
        }

        return $analysis;
    }

    public function isReady(): bool
    {
        try {
            if (!$this->isRunning || !is_resource($this->process)) {
                return false;
            }

            $status = proc_get_status($this->process);
            if (!$status['running']) {
                $this->isRunning = false;
                return false;
            }

            $this->sendCommand('isready');
            $response = $this->waitForResponse('readyok', 3);
            return strpos($response, 'readyok') !== false;
        } catch (\Exception $e) {
            Log::error('Engine readiness check failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getEngineInfo(): array
    {
        $status = $this->isRunning && is_resource($this->process) ? proc_get_status($this->process) : ['running' => false];

        return [
            'name' => 'Pikafish',
            'author' => 'Pikafish Developers',
            'variant' => 'xiangqi',
            'initialized' => $this->isInitialized,
            'running' => $status['running'] ?? false,
            'network_loaded' => file_exists($this->networkPath),
            'pid' => $status['pid'] ?? null
        ];
    }

    private function cleanup()
    {
        // Close pipes
        if (is_array($this->pipes)) {
            foreach ($this->pipes as $pipe) {
                if (is_resource($pipe)) {
                    fclose($pipe);
                }
            }
        }

        // Terminate process
        if (is_resource($this->process)) {
            proc_terminate($this->process);
            proc_close($this->process);
        }

        $this->isRunning = false;
        $this->isInitialized = false;
    }

    public function __destruct()
    {
        $this->cleanup();
    }
}
