<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GroqChessService;
use Illuminate\Http\Request;

class ChessAnalysisController extends Controller
{
    protected $groqService;

    public function __construct(GroqChessService $groqService)
    {
        $this->groqService = $groqService;
    }

    public function analyze(Request $request)
    {
        // Validate chỉ yêu cầu FEN
        $request->validate([
            'fen' => 'required|string',
        ]);

        $fen = $request->input('fen');

        // Gọi service chỉ với FEN
        $analysis = $this->groqService->analyzeGame($fen);

        if (!$analysis) {
            return response()->json(['message' => 'Lỗi phân tích.'], 500);
        }

        return response()->json([
            'success' => true,
            'data' => json_decode($analysis)
        ]);
    }

    public function chat(Request $request)
    {
        $request->validate([
            'fen' => 'required|string',
            'message' => 'required|string',
        ]);

        $fen = $request->input('fen');
        $message = $request->input('message');

        $reply = $this->groqService->chatWithCoach($fen, $message);

        return response()->json([
            'success' => true,
            'reply' => $reply
        ]);
    }
}
