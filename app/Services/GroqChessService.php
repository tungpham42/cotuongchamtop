<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqChessService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';

    // Danh sách các model theo thứ tự ưu tiên
    protected $availableModels = [
        "openai/gpt-oss-120b",        // Ưu tiên 1 (hoặc lấy từ ENV)
        "qwen/qwen3-32b",
        "openai/gpt-oss-20b",
        "llama-3.3-70b-versatile",
        "llama-3.1-8b-instant",
        "mixtral-8x7b-32768"
    ];

    protected $commonRules =
            "Bạn là một Huấn luyện viên Cờ Tướng (Xiangqi Coach) người Việt Nam thông thái, vui tính và ngắn gọn. " .
            "QUAN TRỌNG: Bạn đang phân tích bàn cờ Tướng (lưới 9x10, có Sông, có Cung), TUYỆT ĐỐI KHÔNG phải Cờ Vua (International Chess 8x8). Hai bên là Đỏ và Đen. " .
            "QUY TẮC KÝ HIỆU CỜ TƯỚNG VIỆT NAM (BẮT BUỘC): " .
            "1. Cấu trúc chung: [Tên quân] [Cột nguồn] [Hành động] [Thông số]. " .
            "2. Hành động: 'Tấn' (đi lên), 'Thoái' (đi về), 'Bình' (đi ngang). " .
            "3. Tuyệt đối KHÔNG dùng ký hiệu tiếng Anh (C2.5, H8+7) hay tọa độ (h2e2) trong phần lời văn. " .
            "4. QUY TẮC CHO 'XE', 'PHÁO', 'CHỐT (Tốt)', 'TƯỚNG': " .
            "   - Khi TẤN (lên) hoặc THOÁI (về): Dùng SỐ BƯỚC đi. (VD: 'Xe 2 tấn 4' là đi lên 4 ô; 'Chốt 5 tấn 1' là lên 1 ô). " .
            "   - Khi BÌNH (ngang): Dùng CỘT ĐÍCH (1-9). (VD: 'Pháo 2 bình 5'). " .
            "5. QUY TẮC CHO 'MÃ', 'TƯỢNG', 'SỸ': " .
            "   - Luôn dùng CỘT ĐÍCH cho cả Tấn và Thoái. (VD: 'Mã 8 tấn 7' là nhảy về cột 7; 'Sỹ 4 tấn 5'). " .
            "Ví dụ ĐÚNG: 'Xe 9 bình 8', 'Pháo 2 tấn 4', 'Mã 8 tấn 7', 'Chốt 5 tấn 1'. " .
            "Ví dụ SAI: 'Xe 2 tấn 6' (nếu ý là đến cột 6 -> sai, phải là bình 6), 'Mã 8 tấn 1' (sai, Mã phải tính cột).";

    public function __construct()
    {
        $this->apiKey = env('GROQ_API_KEY');

        // Nếu có model trong ENV, đưa nó lên đầu danh sách ưu tiên
        $envModel = env('GROQ_MODEL');
        if ($envModel && !in_array($envModel, $this->availableModels)) {
            array_unshift($this->availableModels, $envModel);
        }
    }

    /**
     * Hàm đệ quy xử lý gọi API và tự động đổi Model khi gặp lỗi 429
     * * @param array $messages Mảng lịch sử chat
     * @param array $models Danh sách các model cần thử (sẽ giảm dần qua mỗi lần đệ quy)
     * @param bool $jsonMode Có bắt buộc trả về JSON không
     * @param float $temperature Độ sáng tạo
     * @return string|null Kết quả trả về hoặc null nếu thất bại hết
     */
    protected function callWithFallback(array $messages, array $models, bool $jsonMode = false, float $temperature = 0.5)
    {
        // 1. Điều kiện dừng: Nếu hết model để thử -> Trả về null
        if (empty($models)) {
            Log::error('GroqChessService: All models exhausted or failed.');
            return null;
        }

        // 2. Lấy model đầu tiên trong danh sách để thử
        // array_values để reset key về 0 sau khi cắt mảng
        $currentModel = array_values($models)[0];

        try {
            $payload = [
                'model' => $currentModel,
                'messages' => $messages,
                'temperature' => $temperature,
            ];

            if ($jsonMode) {
                $payload['response_format'] = ['type' => 'json_object'];
            }

            // Gọi API
            $response = Http::withToken($this->apiKey)
                ->post($this->baseUrl, $payload);

            // 3. Trường hợp thành công (200 OK)
            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'];
            }

            // 4. Trường hợp gặp lỗi 429 (Too Many Requests) -> ĐỆ QUY
            if ($response->status() === 429) {
                Log::warning("Groq Model [$currentModel] overloaded (429). Switching to next model...");

                // Loại bỏ model hiện tại ra khỏi danh sách
                array_shift($models);

                // Gọi lại chính hàm này với danh sách model còn lại
                return $this->callWithFallback($messages, $models, $jsonMode, $temperature);
            }

            // Các lỗi khác (400, 401, 500...) -> Không thử lại, log lỗi
            Log::error("Groq API Error [$currentModel]: " . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error("Groq Exception [$currentModel]: " . $e->getMessage());

            // Tùy chọn: Nếu lỗi mạng (timeout), có thể cũng muốn thử model khác:
            // array_shift($models);
            // return $this->callWithFallback($messages, $models, $jsonMode, $temperature);

            return null;
        }
    }

    /**
     * Phân tích ván cờ CHỈ dựa trên FEN
     * @param string $fen Mã FEN của thế cờ hiện tại
     */
    public function analyzeGame($fen)
    {
        // [FIX] Đưa FEN vào System Prompt để AI nhận diện đây là bối cảnh bắt buộc
        $systemPrompt = $this->commonRules . " " .
            "Thế cờ hiện tại (FEN) mà bạn đang xem là: " . $fen . ". " . // <--- CẬP NHẬT FEN TẠI ĐÂY
            "Nhiệm vụ của bạn là PHÂN TÍCH thế cờ này. " .
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
        $userPrompt = "Đây là mã FEN của thế cờ hiện tại: " . $fen . "\n\n" .
                      "Hãy phân tích thế cờ này theo đúng định dạng JSON và quy ước ký hiệu Tiếng Việt đã yêu cầu.";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        // Gọi hàm đệ quy với danh sách model đầy đủ
        return $this->callWithFallback($messages, $this->availableModels, true, 0.4);
    }

    /**
     * Chat với AI Coach dựa trên ngữ cảnh FEN
     * @param string $fen Mã FEN hiện tại
     * @param string $userQuestion Câu hỏi của người dùng
     */
    public function chatWithCoach($fen, $userQuestion)
    {
        // [FIX] Cập nhật System Prompt chứa FEN để đảm bảo ngữ cảnh đúng
        $systemPrompt = $this->commonRules . " " .
            "Thế cờ hiện tại (FEN) mà bạn đang xem là: " . $fen . ". " . // <--- QUAN TRỌNG: Gắn FEN vào System Prompt
            "Nhiệm vụ của bạn là trả lời câu hỏi của người chơi DỰA TRÊN thế cờ này. " .
            "Yêu cầu câu trả lời: " .
            "- Nếu người dùng xin gợi ý, hãy chỉ ra 1 nước đi tốt nhất từ FEN này và giải thích lý do. " .
            "- Trả lời trực tiếp vào vấn đề, không dài dòng. " .
            "- Giữ câu trả lời dưới 100 từ.";

        // User Prompt chỉ chứa câu hỏi, tránh lặp lại FEN gây nhiễu
        $userPrompt = "Dữ liệu thế cờ hiện tại (FEN): " . $fen . "\n\n" .
                      "Câu hỏi của tôi: " . $userQuestion;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        // Gọi hàm đệ quy
        $result = $this->callWithFallback($messages, $this->availableModels, false, 0.6);

        return $result ?? "Xin lỗi, hiện tại tất cả các AI đều đang bận (Lỗi kết nối).";
    }
}
