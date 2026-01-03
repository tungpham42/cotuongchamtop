<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GroqChessService;
use App\Services\XiangqiEngineService; // [NEW] Import Engine Service
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChessAnalysisController extends Controller
{
    protected $groqService;
    protected $xiangqiService; // [NEW]

    // [NEW] Inject XiangqiEngineService
    public function __construct(GroqChessService $groqService, XiangqiEngineService $xiangqiService)
    {
        $this->groqService = $groqService;
        $this->xiangqiService = $xiangqiService;
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'fen' => 'required|string',
        ]);

        $fen = $request->input('fen');

        // 1. [NEW] Lấy nước đi chuẩn xác từ Pikafish (Engine)
        $engineBestMove = null;
        $engineData = null;

        try {
            // Kiểm tra engine có sẵn sàng không
            if ($this->xiangqiService->isReady()) {
                // Phân tích độ sâu 15 (đủ mạnh và nhanh cho API)
                $engineResult = $this->xiangqiService->analyzePosition($fen, 15);

                if (!empty($engineResult['best_move'])) {
                    $engineBestMove = $engineResult['best_move'];
                    $engineData = $engineResult; // Lưu lại để trả về nếu cần
                }
            }
        } catch (\Exception $e) {
            Log::warning("Engine analysis failed, falling back to pure AI: " . $e->getMessage());
            // Không làm gì cả, tiếp tục dùng Groq mà không có gợi ý
        }

        // 2. Gọi Service AI (Groq) kèm theo nước đi của Engine
        $analysisJson = $this->groqService->analyzeGame($fen, $engineBestMove);

        if (!$analysisJson) {
            return response()->json(['message' => 'Lỗi phân tích từ AI.'], 500);
        }

        $data = json_decode($analysisJson, true);

        // [OPTIONAL] Gộp dữ liệu kỹ thuật từ Engine vào phản hồi (Score, Depth...)
        if ($engineData) {
            $data['engine_debug'] = [
                'score' => $engineData['score'],
                'depth' => $engineData['depth'],
                'best_move_raw' => $engineData['best_move']
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data
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

        // [NEW] 1. Phát hiện ý định "Xin gợi ý"
        $engineBestMove = null;
        $suggestionKeywords = ['gợi ý', 'nước đi', 'đi đâu', 'tốt nhất', 'tiếp theo', 'nước nào'];

        // Kiểm tra xem message có chứa từ khóa gợi ý không
        if (Str::contains(Str::lower($message), $suggestionKeywords)) {
            try {
                // Kiểm tra Engine có sẵn sàng không
                if ($this->xiangqiService->isReady()) {
                    // Lấy nước đi tốt nhất (Timeout 2s để phản hồi nhanh cho Chat)
                    $engineBestMove = $this->xiangqiService->getBestMove($fen, 2000);
                }
            } catch (\Exception $e) {
                Log::warning("Chat Engine Fallback Error: " . $e->getMessage());
                // Nếu lỗi Engine, biến $engineBestMove vẫn là null -> AI sẽ tự nghĩ
            }
        }

        // [NEW] 2. Gọi AI Coach và truyền nước đi Engine vào (nếu có)
        $reply = $this->groqService->chatWithCoach($fen, $message, $engineBestMove);

        return response()->json([
            'success' => true,
            'reply' => $reply,
            'debug_move' => $engineBestMove // (Optional) Để debug xem có lấy được move không
        ]);
    }
}
