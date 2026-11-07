<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GroqChessService;
use App\Services\XiangqiEngineService; // [NEW] Import Engine Service
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'require_login' => true,
                'reply' => __('Vui lòng đăng nhập để sử dụng tính năng phân tích thế cờ.'),
            ], 401);
        }

        if (!$user->isVip()) {
            $usageKey = 'ai_coach_usage_' . $user->id . '_' . date('Ymd');
            $usedCount = (int) Cache::get($usageKey, 0);
            if ($usedCount >= 2) {
                return response()->json([
                    'success' => false,
                    'require_vip' => true,
                    'reply' => __('Bạn đã dùng hết lượt trải nghiệm phân tích AI miễn phí hôm nay. Vui lòng nâng cấp VIP để tiếp tục không giới hạn!'),
                ], 403);
            }
            Cache::put($usageKey, $usedCount + 1, now()->endOfDay());
        }

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
        $analysisJson = null;
        try {
            $analysisJson = $this->groqService->analyzeGame($fen, $engineBestMove);
        } catch (\Throwable $e) {
            Log::warning("Groq AI analysis error: " . $e->getMessage());
        }

        if ($analysisJson) {
            $data = json_decode($analysisJson, true) ?: [];
        } else {
            // Graceful fallback using Pikafish Engine data
            $score = $engineData['score'] ?? 0;
            $depth = $engineData['depth'] ?? 15;
            $bestMove = $engineBestMove ?: 'N/A';
            $evalText = $score > 50 ? 'Bên Đỏ đang có ưu thế tấn công chủ động (+'.number_format($score/100, 1).').' : ($score < -50 ? 'Bên Đen đang có thế trận tốt hơn ('.number_format($score/100, 1).').' : 'Thế trận hai bên đang tương đối giằng co và cân bằng.');

            $data = [
                'best_move' => $bestMove,
                'overview' => $evalText,
                'tactics' => "Nước đi tối ưu tính toán bởi Pikafish Engine (độ sâu {$depth}): {$bestMove}.",
            ];
        }

        // [OPTIONAL] Gộp dữ liệu kỹ thuật từ Engine vào phản hồi (Score, Depth...)
        if ($engineData) {
            $data['engine_debug'] = [
                'score' => $engineData['score'] ?? 0,
                'depth' => $engineData['depth'] ?? 15,
                'best_move_raw' => $engineData['best_move'] ?? null,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function chat(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'require_login' => true,
                'reply' => __('Vui lòng đăng nhập để sử dụng Trợ lý AI Coach.'),
            ], 401);
        }

        if (!$user->isVip()) {
            $usageKey = 'ai_coach_usage_' . $user->id . '_' . date('Ymd');
            $usedCount = (int) Cache::get($usageKey, 0);
            if ($usedCount >= 2) {
                return response()->json([
                    'success' => false,
                    'require_vip' => true,
                    'reply' => __('Bạn đã dùng hết 2 lượt trải nghiệm AI Coach miễn phí hôm nay. Vui lòng nâng cấp VIP để tiếp tục sử dụng không giới hạn!'),
                ], 403);
            }
            Cache::put($usageKey, $usedCount + 1, now()->endOfDay());
        }

        $request->validate([
            'fen' => 'required|string',
            'message' => 'required|string',
        ]);

        $fen = $request->input('fen');
        $message = $request->input('message');

        // [NEW] 1. Phát hiện ý định "Xin gợi ý"
        $engineBestMove = null;
        $suggestionKeywords = ['gợi ý', 'nước đi', 'đi đâu', 'tốt nhất', 'tiếp theo', 'nước nào', 'hint', 'move'];

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
        $reply = null;
        try {
            $reply = $this->groqService->chatWithCoach($fen, $message, $engineBestMove);
        } catch (\Throwable $e) {
            Log::warning("Groq chat error: " . $e->getMessage());
        }

        if (empty($reply)) {
            if ($engineBestMove) {
                $reply = "Nước đi gợi ý tối ưu nhất cho thế trận này là: **{$engineBestMove}** (Được tính toán bởi Engine Pikafish). Hãy kiểm soát các lộ quan trọng và duy trì thế công!";
            } else {
                $reply = "Thế cờ hiện tại đang diễn ra rất kịch tính. Bạn hãy tập trung phát triển các quân Xe, Pháo, Mã để chiếm các lộ trọng yếu!";
            }
        }

        return response()->json([
            'success' => true,
            'reply' => $reply,
            'debug_move' => $engineBestMove // (Optional) Để debug xem có lấy được move không
        ]);
    }
}
