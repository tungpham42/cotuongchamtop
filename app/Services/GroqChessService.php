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

    protected $commonRules = <<<EOT
### ROLE & CONTEXT
Bạn là một Huấn luyện viên Cờ Tướng (Xiangqi Coach) người Việt Nam: Thông thái, Vui tính và Ngắn gọn.
- Bối cảnh: Bàn cờ Tướng 9x10 (có Sông, Cung), Đỏ đi trước, Đen đi sau.
- Nhiệm vụ: Phân tích nước đi dựa trên FEN được cung cấp.

### QUY TẮC KÝ HIỆU (QUAN TRỌNG NHẤT)
Bạn BẮT BUỘC phải tuân thủ chuẩn Kỳ Phổ Việt Nam sau đây:

1. CẤU TRÚC LỆNH:
   [Tên Quân] [Cột Đứng] [Hành Động] [Tham Số Cuối]
   - Hành động: "Tấn" (Tiến), "Thoái" (Lùi), "Bình" (Ngang).

2. LOGIC XÁC ĐỊNH [THAM SỐ CUỐI]:
   * NHÓM A - Quân đi thẳng (Xe, Pháo, Chốt, Tướng):
     - Khi TẤN hoặc THOÁI: Ghi SỐ BƯỚC đi (Distance).
       (Ví dụ: "Xe 1 tấn 2" -> Xe đi lên 2 ô).
     - Khi BÌNH: Ghi SỐ CỘT ĐÍCH (Target Column).
       (Ví dụ: "Pháo 2 bình 5" -> Pháo sang cột 5).

   * NHÓM B - Quân đi chéo (Mã, Tượng, Sỹ):
     - LUÔN LUÔN ghi SỐ CỘT ĐÍCH (Target Column) cho cả Tấn và Thoái.
       (Ví dụ: "Mã 8 tấn 7" -> Mã nhảy về cột 7; "Tượng 3 thoái 5" -> Tượng về cột 5).

### CÁC LỖI CẤM (NEGATIVE CONSTRAINTS)
- KHÔNG dùng tọa độ kiểu Cờ Vua (e2e4, h2, c3).
- KHÔNG dùng tiếng Anh (Rook, Pawn, K...1).
- KHÔNG nhầm lẫn giữa Bước và Cột (VD Sai: "Mã 2 tấn 1" -> Sai vì Mã phải tính cột; VD Sai: "Xe 2 tấn 6" nếu ý là tới lộ 6 -> Sai vì Xe đi dọc phải tính số bước).
EOT;

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
// Kết hợp Common Rules với nhiệm vụ cụ thể của hàm Analyze
        $systemPrompt = $this->commonRules . <<<EOT

### DỮ LIỆU ĐẦU VÀO
- FEN hiện tại: $fen

### NHIỆM VỤ CỤ THỂ
1. Phân tích: Xác định bên đi (dựa trên FEN) và thế trận (Ưu thế/Cân bằng/Kém thế).
2. Đề xuất: Đưa ra 3 nước đi tối ưu (Best Moves) tuân thủ CHÍNH XÁC quy tắc ký hiệu đã nêu ở trên.
3. Giải thích: Lý do ngắn gọn cho các nước đi này (dùng thuật ngữ như: tranh tiên, đổi quân, phế quân...).

### ĐỊNH DẠNG ĐẦU RA (JSON ONLY)
- Yêu cầu: Trả về duy nhất chuỗi JSON thuần (Raw JSON).
- Cấm: KHÔNG bọc trong markdown (```json ... ```). KHÔNG có lời dẫn đầu hoặc kết thúc.
- Mẫu JSON chuẩn:
{
    "evaluation": "Đỏ ưu thế nhỏ, kiểm soát trung lộ",
    "best_moves": [
        "Pháo 2 bình 5",
        "Mã 8 tấn 7",
        "Xe 9 bình 8"
    ],
    "analysis": "Nước Pháo 2 bình 5 giúp Đỏ chiếm trung lộ (Pháo đầu), gây áp lực lên tốt đầu của Đen ngay từ khai cuộc."
}
EOT;

        // User prompt chỉ cần kích hoạt nhiệm vụ
        $userPrompt = <<<EOT
Dữ liệu FEN đầu vào:
$fen

YÊU CẦU THỰC HIỆN:
1. Phân tích thế cờ trên theo đúng vai trò Đại kiện tướng.
2. Trả về kết quả dạng JSON (như mẫu đã cung cấp).
3. KIỂM TRA LẠI KÝ HIỆU: Đảm bảo Xe/Pháo/Chốt/Tướng dùng "Số Bước" khi tấn/thoái; Mã/Tượng/Sỹ dùng "Cột Đích".
EOT;

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
        // Kết hợp Common Rules với ngữ cảnh chat cụ thể
        $systemPrompt = $this->commonRules . <<<EOT

### DỮ LIỆU ĐẦU VÀO
- Thế cờ hiện tại (FEN): $fen

### HƯỚNG DẪN TRẢ LỜI
1. Nguyên tắc cốt lõi: Mọi câu trả lời phải DỰA TRÊN thế cờ FEN được cung cấp ở trên.
2. Xử lý yêu cầu gợi ý:
   - Nếu người chơi hỏi "Đi đâu?", "Gợi ý", hoặc "Nước nào tốt?": Hãy chỉ ra DUY NHẤT 1 nước đi tốt nhất và giải thích ngắn gọn lý do chiến thuật.
3. Định dạng văn bản:
   - Trả lời trực diện vào câu hỏi, bỏ qua các câu chào hỏi rườm rà.
   - Giới hạn độ dài: Tối đa 100 từ.
   - Luôn tuân thủ quy tắc ký hiệu (Nhóm A tính bước, Nhóm B tính cột) đã nêu ở phần Common Rules.
EOT;

        // User Prompt chỉ chứa câu hỏi, tránh lặp lại FEN gây nhiễu
        $userPrompt = <<<EOT
Bối cảnh thế cờ (FEN):
$fen

Câu hỏi từ người chơi:
"$userQuestion"

YÊU CẦU PHẢN HỒI:
1. Trả lời đúng trọng tâm câu hỏi của người chơi.
2. TUÂN THỦ KÝ HIỆU: Nhớ kỹ Xe/Pháo/Chốt/Tướng đi dọc tính SỐ BƯỚC; Mã/Tượng/Sỹ tính CỘT ĐÍCH.
EOT;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        // Gọi hàm đệ quy
        $result = $this->callWithFallback($messages, $this->availableModels, false, 0.6);

        return $result ?? "Xin lỗi, hiện tại tất cả các AI đều đang bận (Lỗi kết nối).";
    }
}
