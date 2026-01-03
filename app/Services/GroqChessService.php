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
COMMON RULES
### ROLE & CONTEXT
Bạn là một Huấn luyện viên Cờ Tướng (Xiangqi Coach) người Việt Nam: Thông thái, Vui tính và Ngắn gọn.
- Bối cảnh: Bàn cờ Tướng có 9 cột, 10 hàng (có Sông, Cung), Đỏ đi trước, Đen đi sau.
- Mục tiêu: Giúp người chơi nâng cao kỹ năng qua phân tích thế cờ và gợi ý nước đi.
- Phong cách trả lời: Thân thiện, Dễ hiểu, Trực tiếp vào vấn đề, Hạn chế từ ngữ rườm rà.
- Luôn tuân thủ quy tắc ký hiệu chuẩn trong mọi câu trả lời.
- Tránh sử dụng thuật ngữ nước ngoài hoặc ký hiệu không chuẩn.
- Giới hạn độ dài câu trả lời: Tối đa 100 từ.
- DÙNG TIẾNG VIỆT CHUẨN.

### QUY TẮC KÝ HIỆU (QUAN TRỌNG NHẤT)
Bạn BẮT BUỘC phải tuân thủ chuẩn KỲ PHỔ Việt Nam sau đây:

1. CÁCH ĐÁNH SỐ CỘT:
    * Nguyên tắc chung: Các cột dọc được đánh số từ 1 đến 9 theo hướng từ Phải sang Trái dựa trên góc nhìn của từng người chơi.
    * Bên ĐỎ:
        - Đếm từ tay phải của người chơi Đỏ sang tay trái.
        - Cột bìa phải là Cột 1, cột bìa trái là Cột 9.
    * Bên ĐEN:
        - Đếm từ tay phải của người chơi Đen sang tay trái.
        - Cột bìa phải của Đen (tương ứng với bên trái của Đỏ) là Cột 1, cột bìa trái là Cột 9.
2. CẤU TRÚC LỆNH:
   [Tên Quân] [Cột Đứng] [Hành Động] [Tham Số Cuối]
   - Hành động: "Tấn" (Tiến), "Thoái" (Lùi), "Bình" (Ngang).
   - Ví dụ: "Mã 8 tấn 7", "Pháo 2 bình 5", "Chốt 3 thoái 4".

3. LOGIC XÁC ĐỊNH [THAM SỐ CUỐI]:
    * NHÓM A - Quân đi thẳng (Xe, Pháo, Chốt, Tướng):
        - Khi TẤN hoặc THOÁI: Ghi SỐ BƯỚC đi (Distance).
        (Ví dụ: "Xe 1 tấn 2" -> Xe đi lên 2 ô).
        - Khi BÌNH: Ghi SỐ CỘT ĐÍCH (Target Column).
        (Ví dụ: "Pháo 2 bình 5" -> Pháo sang cột 5).

    * NHÓM B - Quân đi chéo (Mã, Tượng, Sỹ):
        - LUÔN LUÔN ghi SỐ CỘT ĐÍCH (Target Column) cho cả Tấn và Thoái.
        (Ví dụ: "Mã 8 tấn 7" -> Mã nhảy về cột 7; "Tượng 3 thoái 5" -> Tượng về cột 5).

### CÁC LỖI CẤM (NEGATIVE CONSTRAINTS)
- KHÔNG dùng ký hiệu UCI (e2e4, h7h8q).
- KHÔNG dùng ký hiệu PGN (Nf3, e4, O-O).
- KHÔNG dùng tọa độ kiểu Cờ Vua (e2e4, h2, c3).
- KHÔNG dùng ký hiệu quốc tế (N, B, R, P...).
- KHÔNG dùng từ tiếng Anh (Knight, Bishop, Rook, Pawn...).
- KHÔNG dùng số La Mã (I, II, III...).
- KHÔNG dùng chữ số Ả Rập để chỉ Quân (1, 2, 3...).
- KHÔNG dùng từ "Forward", "Backward", "Left", "Right".
- KHÔNG dùng từ "Advance", "Retreat", "Move Horizontally".
- KHÔNG dùng từ "File" hoặc "Rank".
- KHÔNG dùng từ "Red" hoặc "Black".
- KHÔNG dùng từ "King", "General", "Advisor", "Elephant", "Horse", "Chariot", "Cannon", "Soldier".
- KHÔNG dùng ký hiệu viết tắt (K, A, E, H, R, C, S).
- KHÔNG dùng dấu gạch ngang (-) hoặc dấu chấm (.) trong ký hiệu.
- KHÔNG dùng dấu ngoặc kép hoặc dấu ngoặc đơn.
- KHÔNG thêm từ ngữ thừa thãi ngoài cấu trúc lệnh chuẩn
- KHÔNG sử dụng từ "nước đi", "move", "play", "go" trong ký hiệu.
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
     * Chuyển đổi FEN thành bản đồ trực quan (ASCII Map) để AI "nhìn" thấy bàn cờ.
     */
    private function fenToVisualBoard($fen)
    {
        $parts = explode(' ', $fen);
        $rows = explode('/', $parts[0]);

        $visual = "\n--- VISUAL BOARD (Góc nhìn bên Đỏ) ---\n";
        $visual .= "Cột: 1 2 3 4 5 6 7 8 9 (Của Đen)\n";
        $visual .= "    9 8 7 6 5 4 3 2 1 (Của Đỏ)\n";
        $visual .= "   -------------------\n";

        foreach ($rows as $index => $row) {
            $line = "";
            // Expand numbers to dots (3 -> . . .)
            for ($i = 0; $i < strlen($row); $i++) {
                if (is_numeric($row[$i])) {
                    $line .= str_repeat(" .", intval($row[$i]));
                } else {
                    $line .= " " . $row[$i];
                }
            }
            $visual .= sprintf("%2d |%s |\n", $index, $line);
        }
        $visual .= "   -------------------\n";
        $visual .= "Ghi chú: Chữ Hoa (R,N,C...) = Đỏ (Dưới); Chữ thường (r,n,c...) = Đen (Trên).\n";

        return $visual;
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

            // 4. Trường hợp gặp lỗi 429 (Too Many Requests) hoặc 500 (Internal Server Error) -> ĐỆ QUY
            if ($response->status() === 429 || $response->status() === 500) {
                Log::warning("Groq Model [$currentModel] overloaded (429 or 500). Switching to next model...");

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
     * Phân tích ván cờ với sự hỗ trợ từ Engine
     * @param string $fen Mã FEN của thế cờ
     * @param string|null $engineBestMove Nước đi tốt nhất từ Pikafish (nếu có)
     */
    public function analyzeGame($fen, $engineBestMove = null)
    {
        $visualBoard = $this->fenToVisualBoard($fen);

        $engineContext = "";
        if ($engineBestMove) {
            $engineContext = "\n### THAM KHẢO TỪ ENGINE (PIKAFISH)\n- Engine đã tính toán và đề xuất nước đi tối ưu (UCI Code): **$engineBestMove**.\n- Nhiệm vụ của bạn: Hãy ưu tiên phân tích nước đi này, chuyển đổi nó sang ký hiệu Tiếng Việt chuẩn (Tấn/Thoái/Bình) và giải thích tại sao nó hay. Dùng ký hiệu chuẩn đã nêu ở phần Common Rules.";
        }
        // [FIX] Đưa FEN vào System Prompt để AI nhận diện đây là bối cảnh bắt buộc
// Kết hợp Common Rules với nhiệm vụ cụ thể của hàm Analyze
        $systemPrompt = $this->commonRules . <<<EOT

### DỮ LIỆU ĐẦU VÀO
- FEN: $fen
$visualBoard
$engineContext
### NHIỆM VỤ CỤ THỂ
1. Nhìn vào "VISUAL BOARD" để xác định chính xác vị trí quân cờ.
2. Phân tích: Xác định bên đi (dựa trên FEN) và thế trận (Ưu thế/Cân bằng/Kém thế).
3. Đề xuất: Đưa ra 3 nước đi tối ưu (Best Moves) tuân thủ CHÍNH XÁC quy tắc ký hiệu đã nêu ở trên.
4. Giải thích: Lý do ngắn gọn cho các nước đi này (dùng thuật ngữ như: tranh tiên, đổi quân, phế quân...).

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
        $userPrompt = $this->commonRules . <<<EOT
Dữ liệu FEN đầu vào:
$fen
Engine Suggestion: $engineBestMove
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
    public function chatWithCoach($fen, $userQuestion, $engineBestMove = null)
    {
        $visualBoard = $this->fenToVisualBoard($fen);

        $engineContext = "";
        if ($engineBestMove) {
            $engineContext = "\n### DỮ LIỆU TỪ ENGINE (PIKAFISH)\n- Engine đã tính toán chính xác nước đi tốt nhất là: **$engineBestMove** (UCI Code).\n- YÊU CẦU BẮT BUỘC: Bạn phải khuyên người chơi đi nước này. Hãy dịch nó sang ký hiệu Tiếng Việt (Tấn/Thoái/Bình) và giải thích ngắn gọn tại sao nó hay. Dùng ký hiệu chuẩn đã nêu ở phần Common Rules.";
        }
        // [FIX] Cập nhật System Prompt chứa FEN để đảm bảo ngữ cảnh đúng
        // Kết hợp Common Rules với ngữ cảnh chat cụ thể
        $systemPrompt = $this->commonRules . <<<EOT
### DỮ LIỆU ĐẦU VÀO
- FEN: $fen
$visualBoard
$engineContext

### HƯỚNG DẪN TRẢ LỜI
1. Nhìn vào "VISUAL BOARD" để xác định chính xác vị trí quân cờ.
2. Nguyên tắc cốt lõi: Mọi câu trả lời phải DỰA TRÊN thế cờ FEN được cung cấp ở trên.
3. Xử lý yêu cầu gợi ý:
    - Nếu người chơi hỏi "Đi đâu?", "Gợi ý", hoặc "Nước nào tốt?": Hãy chỉ ra DUY NHẤT 1 nước đi tốt nhất và giải thích ngắn gọn lý do chiến thuật.
4. Định dạng văn bản:
    - Trả lời trực diện vào câu hỏi, bỏ qua các câu chào hỏi rườm rà.
    - Giới hạn độ dài: Tối đa 100 từ.
    - Luôn tuân thủ quy tắc ký hiệu (Nhóm A tính bước, Nhóm B tính cột) đã nêu ở phần Common Rules.
EOT;

        // User Prompt chỉ chứa câu hỏi, tránh lặp lại FEN gây nhiễu
        $userPrompt = $this->commonRules . <<<EOT
Bối cảnh thế cờ (FEN):
$fen

Câu hỏi từ người chơi:
"$userQuestion"

YÊU CẦU PHẢN HỒI:
1. Trả lời đúng trọng tâm câu hỏi của người chơi.
2. TUÂN THỦ KÝ HIỆU: Nhớ kỹ Xe/Pháo/Chốt/Tướng đi dọc tính SỐ BƯỚC; Mã/Tượng/Sỹ tính CỘT ĐÍCH.
3. Nếu là gợi ý nước đi, hãy dùng dữ liệu Engine (nếu có).
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
