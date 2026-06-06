<?php

namespace App\Http\Controllers;

use App\Services\XiangqiEngineService;
use App\Helpers\XiangqiHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class XiangqiController extends Controller
{
    private $xiangqiEngine;
    private $engineAvailable = false;

    public function __construct()
    {
        try {
            $this->xiangqiEngine = new XiangqiEngineService();
            $this->engineAvailable = $this->xiangqiEngine->isReady();
            Log::info('XiangqiController initialized - Engine available: ' . ($this->engineAvailable ? 'YES' : 'NO'));
        } catch (\Exception $e) {
            Log::error('Failed to initialize Xiangqi engine in controller: ' . $e->getMessage());
            $this->engineAvailable = false;
        }
    }

    public function getBestMove(Request $request): JsonResponse
    {
        $request->validate([
            'fen' => 'required|string',
            'timeout' => 'sometimes|integer|min:100|max:30000',
            'level' => 'sometimes|integer|min:1|max:5'
        ]);

        try {
            $fen = $request->input('fen');
            $timeout = $request->input('timeout', 3000);
            $level = $request->input('level', 3);

            Log::info('Best move request received', [
                'fen' => $fen,
                'timeout' => $timeout,
                'level' => $level,
                'engine_available' => $this->engineAvailable
            ]);

            if (!XiangqiHelper::validateFen($fen)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid Xiangqi FEN position'
                ], 422);
            }

            // Adjust timeout based on level
            $adjustedTimeout = $this->getAdjustedTimeout($timeout, $level);

            $bestMove = null;
            $usedFallback = false;

            if ($this->engineAvailable && $this->xiangqiEngine->isReady()) {
                Log::info('Attempting to get best move from engine');
                $bestMove = $this->xiangqiEngine->getBestMove($fen, $adjustedTimeout);

                if ($bestMove) {
                    Log::info('Engine returned best move: ' . $bestMove);
                } else {
                    Log::warning('Engine failed to return a best move');
                    $usedFallback = true;
                }
            } else {
                Log::warning('Engine not available, using fallback');
                $usedFallback = true;
            }

            // If engine failed or is not available, use fallback
            if (!$bestMove) {
                $bestMove = $this->getFallbackMove($fen);
                $usedFallback = true;
                Log::info('Using fallback move: ' . $bestMove);
            }

            return response()->json([
                'success' => true,
                'best_move' => $bestMove,
                'fen' => $fen,
                'level' => $level,
                'timeout' => $adjustedTimeout,
                'fallback' => $usedFallback,
                'engine_available' => $this->engineAvailable,
                'message' => $usedFallback ? 'Using fallback move' : 'Engine move'
            ]);

        } catch (\Exception $e) {
            Log::error('Xiangqi controller error: ' . $e->getMessage());

            // Try fallback move
            $fallbackMove = $this->getFallbackMove($request->input('fen'));

            return response()->json([
                'success' => $fallbackMove !== null,
                'best_move' => $fallbackMove,
                'fen' => $request->input('fen'),
                'fallback' => true,
                'engine_available' => $this->engineAvailable,
                'error' => $fallbackMove ? 'Engine error, using fallback move' : 'Engine error: ' . $e->getMessage()
            ]);
        }
    }

    private function getAdjustedTimeout(int $baseTimeout, int $level): int
    {
        // Adjust thinking time based on difficulty level
        $multipliers = [
            1 => 0.5,  // Mới chơi: faster
            2 => 0.8,  // Dễ
            3 => 1.0,  // Bình thường
            4 => 1.5,  // Khó
            5 => 2.0   // Khó nhất: slower
        ];

        $multiplier = $multipliers[$level] ?? 1.0;
        return (int)($baseTimeout * $multiplier);
    }

    private function getFallbackMove(string $fen): ?string
    {
        try {
            // Parse FEN to understand the position
            $parts = explode(' ', $fen);
            $boardPart = $parts[0];
            $activeColor = $parts[1] ?? 'r';

            // Simple fallback logic based on common opening moves
            $commonMoves = [
                'e3e4', // Center pawn forward (most common opening)
                'h2e2', // Cannon to center
                'b2e2', // Left cannon to center
                'g2e2', // Right cannon to center
                'c3c4', // Left pawn forward
                'g3g4', // Right pawn forward
                'i3i4', // Edge pawn forward
                'a3a4', // Left edge pawn forward
                'h0g2', // Horse development
                'b0c2', // Left horse development
            ];

            // If it's black's turn, adjust coordinates for black pieces
            if ($activeColor === 'b') {
                $commonMoves = [
                    'e6e5', // Center pawn forward for black
                    'h7e7', // Cannon to center for black
                    'b7e7', // Left cannon to center for black
                    'g7e7', // Right cannon to center for black
                    'c6c5', // Left pawn forward for black
                    'g6g5', // Right pawn forward for black
                    'i6i5', // Edge pawn forward for black
                    'a6a5', // Left edge pawn forward for black
                    'h9g7', // Horse development for black
                    'b9c7', // Left horse development for black
                ];
            }

            // Return a random common move
            return $commonMoves[array_rand($commonMoves)];

        } catch (\Exception $e) {
            Log::error('Fallback move generation failed: ' . $e->getMessage());
            return 'e3e4'; // Default safe move
        }
    }

    public function analyzePosition(Request $request): JsonResponse
    {
        $request->validate([
            'fen' => 'required|string',
            'depth' => 'sometimes|integer|min:1|max:30'
        ]);

        try {
            $fen = $request->input('fen');
            $depth = $request->input('depth', 15);

            if (!XiangqiHelper::validateFen($fen)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid Xiangqi FEN position'
                ], 422);
            }

            if (!$this->engineAvailable) {
                return response()->json([
                    'success' => false,
                    'error' => 'Engine not available for analysis'
                ], 503);
            }

            $analysis = $this->xiangqiEngine->analyzePosition($fen, $depth);

            return response()->json([
                'success' => true,
                'analysis' => $analysis,
                'fen' => $fen,
                'depth' => $depth,
                'engine_available' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Position analysis error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'engine_available' => $this->engineAvailable
            ], 500);
        }
    }

    public function getStartingPosition(): JsonResponse
    {
        $fen = XiangqiHelper::STANDARD_START_FEN;

        return response()->json([
            'success' => true,
            'fen' => $fen,
            'active_color' => XiangqiHelper::getActiveColor($fen),
            'active_color_code' => XiangqiHelper::getActiveColorCode($fen),
            'description' => 'Standard Xiangqi starting position - Red to move',
            'engine_available' => $this->engineAvailable
        ]);
    }

    public function validateFen(Request $request): JsonResponse
    {
        $request->validate([
            'fen' => 'required|string'
        ]);

        $fen = $request->input('fen');
        $isValid = XiangqiHelper::validateFen($fen);

        $response = [
            'success' => true,
            'valid' => $isValid,
            'fen' => $fen,
            'engine_available' => $this->engineAvailable
        ];

        if ($isValid) {
            $response['active_color'] = XiangqiHelper::getActiveColor($fen);
            $response['active_color_code'] = XiangqiHelper::getActiveColorCode($fen);
        }

        return response()->json($response);
    }

    public function switchActiveColor(Request $request): JsonResponse
    {
        $request->validate([
            'fen' => 'required|string'
        ]);

        $fen = $request->input('fen');

        if (!XiangqiHelper::validateFen($fen)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid Xiangqi FEN position'
            ], 422);
        }

        $newFen = XiangqiHelper::switchActiveColor($fen);

        return response()->json([
            'success' => true,
            'fen' => $newFen,
            'active_color' => XiangqiHelper::getActiveColor($newFen),
            'active_color_code' => XiangqiHelper::getActiveColorCode($newFen),
            'description' => 'Active color switched',
            'engine_available' => $this->engineAvailable
        ]);
    }

    public function getPieceInfo(Request $request): JsonResponse
    {
        $request->validate([
            'piece' => 'required|string|size:1'
        ]);

        $piece = $request->input('piece');
        $validPieces = XiangqiHelper::getValidPieces();

        if (!in_array($piece, $validPieces)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid Xiangqi piece code'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'piece' => [
                'code' => $piece,
                'name' => XiangqiHelper::getPieceName($piece),
                'color' => XiangqiHelper::getPieceColor($piece)
            ],
            'engine_available' => $this->engineAvailable
        ]);
    }

    public function getEngineStatus(): JsonResponse
    {
        try {
            $isReady = $this->engineAvailable && $this->xiangqiEngine->isReady();
            $engineInfo = $this->engineAvailable ? $this->xiangqiEngine->getEngineInfo() : [
                'name' => 'Pikafish',
                'author' => 'Pikafish Developers',
                'variant' => 'xiangqi',
                'initialized' => false,
                'running' => false,
                'network_loaded' => file_exists(storage_path('engines/pikafish.nnue'))
            ];

            return response()->json([
                'success' => true,
                'ready' => $isReady,
                'engine' => $engineInfo,
                'controller_initialized' => $this->engineAvailable
            ]);

        } catch (\Exception $e) {
            Log::error('Engine status check failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'ready' => false,
                'controller_initialized' => $this->engineAvailable
            ], 500);
        }
    }

    public function healthCheck(): JsonResponse
    {
        try {
            $isReady = $this->engineAvailable && $this->xiangqiEngine->isReady();
            $networkExists = file_exists(storage_path('engines/pikafish.nnue'));
            $engineExists = file_exists(storage_path('engines/pikafish_vps'));

            return response()->json([
                'success' => true,
                'status' => $isReady ? 'healthy' : ($this->engineAvailable ? 'degraded' : 'unhealthy'),
                'engine_ready' => $isReady,
                'engine_available' => $this->engineAvailable,
                'files' => [
                    'engine_exists' => $engineExists,
                    'network_exists' => $networkExists,
                    'engine_executable' => $engineExists ? is_executable(storage_path('engines/pikafish_vps')) : false
                ],
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'error' => $e->getMessage(),
                'engine_available' => $this->engineAvailable,
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    public function restartEngine(): JsonResponse
    {
        try {
            // Destroy the current engine instance
            unset($this->xiangqiEngine);
            $this->engineAvailable = false;

            // Create a new instance
            $this->xiangqiEngine = new XiangqiEngineService();
            $this->engineAvailable = $this->xiangqiEngine->isReady();

            return response()->json([
                'success' => true,
                'restarted' => true,
                'engine_available' => $this->engineAvailable,
                'message' => $this->engineAvailable ? 'Engine restarted successfully' : 'Engine restart failed'
            ]);

        } catch (\Exception $e) {
            Log::error('Engine restart failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'restarted' => false,
                'engine_available' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
