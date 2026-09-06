<?php

namespace App\Http\Controllers;

use App\Services\XiangqiEngineClient;
use App\Helpers\XiangqiHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class XiangqiController extends Controller
{
    private XiangqiEngineClient $xiangqiEngine;

    public function __construct(XiangqiEngineClient $xiangqiEngine)
    {
        // Constructing XiangqiEngineClient does no I/O at all (just two
        // storage_path() lookups), so routes that don't touch the engine
        // (validateFen, switchActiveColor, getPieceInfo, etc.) still pay
        // nothing. The actual Pikafish process is spawned on demand, only
        // inside getBestMove()/analyzePosition(), and torn back down right
        // after — there's no persistent worker pool to manage.
        $this->xiangqiEngine = $xiangqiEngine;
    }

    public function getBestMove(Request $request): JsonResponse
    {
        $request->validate([
            'fen' => 'required|string',
            'timeout' => 'sometimes|integer|min:100|max:30000',
            'level' => 'sometimes|integer|min:1|max:5',
        ]);

        $fen = $request->input('fen');
        $timeout = $request->input('timeout', 3000);
        $level = $request->input('level', 3);

        if (!XiangqiHelper::validateFen($fen)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid Xiangqi FEN position',
            ], 422);
        }

        $adjustedTimeout = $this->getAdjustedTimeout($timeout, $level);
        $usedFallback = false;

        try {
            // Spawns a fresh Pikafish process, waits for it to become
            // ready (no fixed sleep — moves on as soon as uciok/readyok
            // actually arrive), gets the move, then shuts it back down.
            // Any engine failure below falls through to the fallback
            // move generator rather than surfacing an error to the user.
            $bestMove = $this->xiangqiEngine->getBestMove($fen, $adjustedTimeout);
        } catch (\Throwable $e) {
            Log::error('Xiangqi engine client error: ' . $e->getMessage());
            $bestMove = null;
        }

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
            'message' => $usedFallback ? 'Using fallback move' : 'Engine move',
        ]);
    }

    private function getAdjustedTimeout(int $baseTimeout, int $level): int
    {
        $multipliers = [
            1 => 0.5,  // Mới chơi: faster
            2 => 0.8,  // Dễ
            3 => 1.0,  // Bình thường
            4 => 1.5,  // Khó
            5 => 2.0,  // Khó nhất: slower
        ];

        $multiplier = $multipliers[$level] ?? 1.0;
        return (int) ($baseTimeout * $multiplier);
    }

    private function getFallbackMove(string $fen): ?string
    {
        try {
            $parts = explode(' ', $fen);
            $activeColor = $parts[1] ?? 'r';

            $commonMoves = [
                'e3e4', 'h2e2', 'b2e2', 'g2e2', 'c3c4',
                'g3g4', 'i3i4', 'a3a4', 'h0g2', 'b0c2',
            ];

            if ($activeColor === 'b') {
                $commonMoves = [
                    'e6e5', 'h7e7', 'b7e7', 'g7e7', 'c6c5',
                    'g6g5', 'i6i5', 'a6a5', 'h9g7', 'b9c7',
                ];
            }

            return $commonMoves[array_rand($commonMoves)];
        } catch (\Throwable $e) {
            Log::error('Fallback move generation failed: ' . $e->getMessage());
            return 'e3e4';
        }
    }

    public function analyzePosition(Request $request): JsonResponse
    {
        $request->validate([
            'fen' => 'required|string',
            'depth' => 'sometimes|integer|min:1|max:30',
        ]);

        $fen = $request->input('fen');
        $depth = $request->input('depth', 15);

        if (!XiangqiHelper::validateFen($fen)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid Xiangqi FEN position',
            ], 422);
        }

        try {
            $analysis = $this->xiangqiEngine->analyzePosition($fen, $depth);
        } catch (\Throwable $e) {
            Log::error('Position analysis error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }

        if ($analysis === null) {
            return response()->json([
                'success' => false,
                'error' => 'Engine unavailable for analysis',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'analysis' => $analysis,
            'fen' => $fen,
            'depth' => $depth,
        ]);
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
        ]);
    }

    public function validateFen(Request $request): JsonResponse
    {
        $request->validate(['fen' => 'required|string']);

        $fen = $request->input('fen');
        $isValid = XiangqiHelper::validateFen($fen);

        $response = [
            'success' => true,
            'valid' => $isValid,
            'fen' => $fen,
        ];

        if ($isValid) {
            $response['active_color'] = XiangqiHelper::getActiveColor($fen);
            $response['active_color_code'] = XiangqiHelper::getActiveColorCode($fen);
        }

        return response()->json($response);
    }

    public function switchActiveColor(Request $request): JsonResponse
    {
        $request->validate(['fen' => 'required|string']);
        $fen = $request->input('fen');

        if (!XiangqiHelper::validateFen($fen)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid Xiangqi FEN position',
            ], 422);
        }

        $newFen = XiangqiHelper::switchActiveColor($fen);

        return response()->json([
            'success' => true,
            'fen' => $newFen,
            'active_color' => XiangqiHelper::getActiveColor($newFen),
            'active_color_code' => XiangqiHelper::getActiveColorCode($newFen),
            'description' => 'Active color switched',
        ]);
    }

    public function getPieceInfo(Request $request): JsonResponse
    {
        $request->validate(['piece' => 'required|string|size:1']);

        $piece = $request->input('piece');
        $validPieces = XiangqiHelper::getValidPieces();

        if (!in_array($piece, $validPieces, true)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid Xiangqi piece code',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'piece' => [
                'code' => $piece,
                'name' => XiangqiHelper::getPieceName($piece),
                'color' => XiangqiHelper::getPieceColor($piece),
            ],
        ]);
    }

    /**
     * Cheap status check: confirms the engine binary and network file are
     * present and executable, without actually spawning Pikafish. There's
     * no pool to ping — the engine only runs while a request is being
     * served.
     */
    public function getEngineStatus(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'ready' => $this->xiangqiEngine->isEngineAvailable(),
        ]);
    }

    public function healthCheck(): JsonResponse
    {
        $networkExists = file_exists(storage_path('engines/pikafish.nnue'));
        $engineExists = file_exists(storage_path('engines/pikafish_vps'));
        $engineExecutable = $engineExists && is_executable(storage_path('engines/pikafish_vps'));

        $health = ($engineExists && $networkExists && $engineExecutable) ? 'healthy' : 'unhealthy';

        return response()->json([
            'success' => true,
            'status' => $health,
            'files' => [
                'engine_exists' => $engineExists,
                'network_exists' => $networkExists,
                'engine_executable' => $engineExecutable,
            ],
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * There's no persistent engine process to restart anymore — Pikafish
     * is spawned fresh for each getBestMove()/analyzePosition() call and
     * torn down right after, so nothing is left running between requests
     * for this endpoint to bounce. Kept only so any existing dashboard
     * hitting this route doesn't 404; it just reports whether the engine
     * binary/network are in place to spawn from.
     */
    public function restartEngine(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'note' => 'Nothing to restart: the engine is started fresh per request, not kept as a persistent process.',
            'ready' => $this->xiangqiEngine->isEngineAvailable(),
        ]);
    }
}
