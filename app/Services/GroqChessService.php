<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqChessService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';
    protected $model;

    public function __construct()
    {
        $this->apiKey = env('GROQ_API_KEY');
        $this->model = env('GROQ_MODEL', 'openai/gpt-oss-120b');
    }

    /**
     * Phân tích ván cờ CHỈ dựa trên FEN
     * @param string $fen Mã FEN của thế cờ hiện tại
     */
    public function analyzeGame($fen)
    {
        // [FIX] Đưa FEN vào System Prompt để AI nhận diện đây là bối cảnh bắt buộc
        $systemPrompt = "Bạn là một đại kiện tướng Cờ Tướng (Xiangqi) hàng đầu Việt Nam. " .
            "Bạn đang quan sát bàn cờ với mã FEN: " . $fen . ". " . // <--- CẬP NHẬT FEN TẠI ĐÂY
            "Nhiệm vụ của bạn là phân tích thế cờ này. " .

            // 1. Yêu cầu về định dạng nước đi
            "QUY ƯỚC KÝ HIỆU (Bắt buộc dùng Tiếng Việt chuẩn): " .
            "- Cấu trúc: [Tên quân] [Cột nguồn] [Hành động] [Đích]. " .
            "- Hành động: 'Tấn' (đi lên), 'Thoái' (đi về), 'Bình' (đi ngang). " .
            "- Ví dụ đúng: 'Pháo 2 bình 5', 'Mã 8 tấn 7', 'Xe 9 thoái 1', 'Tốt 5 bình 6'. " .
            "- Tuyệt đối KHÔNG dùng ký hiệu tiếng Anh (C2.5, H8+7) hay tọa độ (h2e2) trong phần lời văn. " .

            // 2. Yêu cầu nhiệm vụ
            "Hãy thực hiện: " .
            "1. Xác định bên đi (Đỏ/Đen) dựa trên FEN và đánh giá ưu thế. " .
            "2. Đề xuất 3 nước đi tối ưu (best moves) bằng ký hiệu Tiếng Việt đã quy ước ở trên. " .
            "3. Phân tích ngắn gọn tại sao nên đi như vậy. " .

            // 3. Định dạng JSON trả về
            "Trả về kết quả duy nhất là chuỗi JSON (không có markdown): " .
            "{ " .
                "'evaluation': 'Đánh giá ngắn gọn (VD: Đỏ ưu thế, Cờ cân bằng)', " .
                "'best_moves': ['Pháo 2 bình 5', 'Mã 8 tấn 7', ...], " .
                "'analysis': 'Lời bình luận chi tiết sử dụng thuật ngữ chuyên môn (tiên thủ, đổi quân, phế quân...)' " .
            "}";

        // User prompt chỉ cần kích hoạt nhiệm vụ
        $userPrompt = "Hãy phân tích thế cờ FEN đã cung cấp trong hệ thống.";

        try {
            $response = Http::withToken($this->apiKey)
                ->post($this->baseUrl, [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'temperature' => 0.4,
                    'response_format' => ['type' => 'json_object']
                ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'];
            }
            Log::error('Groq API Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Groq Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Chat với AI Coach dựa trên ngữ cảnh FEN
     * @param string $fen Mã FEN hiện tại
     * @param string $userQuestion Câu hỏi của người dùng
     */
    public function chatWithCoach($fen, $userQuestion)
    {
        // [FIX] Cập nhật System Prompt chứa FEN để đảm bảo ngữ cảnh đúng
        $systemPrompt = "Bạn là một Huấn luyện viên Cờ Tướng (Xiangqi Coach) người Việt Nam thông thái, vui tính và ngắn gọn. " .
            "Thế cờ hiện tại (FEN) mà bạn đang xem là: " . $fen . ". " . // <--- QUAN TRỌNG: Gắn FEN vào System Prompt
            "Nhiệm vụ của bạn là trả lời câu hỏi của người chơi DỰA TRÊN thế cờ này. " .

            "QUY TẮC KÝ HIỆU (BẮT BUỘC): " .
            "1. Tuyệt đối chỉ dùng thuật ngữ Tiếng Việt (Tấn, Thoái, Bình). " .
            "2. Cấu trúc chuẩn: [Tên quân] [Cột nguồn] [Hành động] [Đích/Số bước]. " .
            "3. Ví dụ mẫu: 'Tốt 5 tấn 1', 'Xe 2 bình 8', 'Mã 8 tấn 7', 'Pháo 2 bình 5'. " .
            "4. KHÔNG sử dụng ký hiệu tiếng Anh (Pawn, Rook...) hoặc tọa độ (e2e4, h2e2, e4, e2). " .

            "Yêu cầu câu trả lời: " .
            "- Nếu người dùng xin gợi ý, hãy chỉ ra 1 nước đi tốt nhất từ FEN này và giải thích lý do. " .
            "- Trả lời trực tiếp vào vấn đề, không dài dòng. " .
            "- Giữ câu trả lời dưới 100 từ.";

        // User Prompt chỉ chứa câu hỏi, tránh lặp lại FEN gây nhiễu
        $userPrompt = "Câu hỏi của tôi: $userQuestion";

        try {
            $response = Http::withToken($this->apiKey)
                ->post($this->baseUrl, [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'temperature' => 0.6,
                ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'];
            }
            return "Xin lỗi, hiện tại tôi đang bị quá tải suy nghĩ (Lỗi kết nối).";
        } catch (\Exception $e) {
            return "Đã có lỗi xảy ra: " . $e->getMessage();
        }
    }
}
