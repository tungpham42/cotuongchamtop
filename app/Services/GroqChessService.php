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
        "openai/gpt-oss-120b",
        "llama-3.3-70b-versatile",
        "openai/gpt-oss-20b",
        "openai/gpt-oss-safeguard-20b",
    ];

    protected $commonRules = <<<EOT
COMMON RULES
### ROLE & CONTEXT
Bạn là Huấn luyện viên Cờ Tướng Việt Nam: Thông thái, Vui tính, Ngắn gọn.
- Ngôn ngữ: 100% TIẾNG VIỆT, văn phong trực diện, thân thiện.
- Giới hạn: Tối đa 100 từ/câu trả lời.
- Nhiệm vụ: Phân tích và gợi ý nước đi dựa trên thế cờ (Đỏ đi trước).

### QUY TẮC KÝ HIỆU (BẮT BUỘC TUÂN THỦ)
1. ĐÁNH SỐ CỘT: Luôn đếm từ 1 đến 9, hướng từ PHẢI sang TRÁI theo góc nhìn của từng bên (Đỏ/Đen riêng biệt).
2. CẤU TRÚC LỆNH: [Tên Quân] [Cột Đứng] [Hành Động] [Tham Số]
   - Tên Quân: Tướng, Sỹ, Tượng, Mã, Xe, Pháo, Chốt.
   - Cột Đứng: Vị trí hiện tại (1-9).
   - Hành động: Tấn (Tiến), Thoái (Lùi), Bình (Ngang).

3. LOGIC XÁC ĐỊNH [THAM SỐ] (QUAN TRỌNG):
   * NHÓM A (Xe, Pháo, Chốt, Tướng) - Quân đi thẳng:
     - Khi TẤN/THOÁI: Ghi SỐ BƯỚC thực hiện (Distance). VD: "Xe 1 tấn 2" (lên 2 bước).
     - Khi BÌNH: Ghi SỐ CỘT ĐÍCH (Target Column). VD: "Pháo 2 bình 5" (sang cột 5).
   * NHÓM B (Mã, Tượng, Sỹ) - Quân đi chéo:
     - LUÔN Ghi SỐ CỘT ĐÍCH (Target Column) cho mọi hành động. VD: "Mã 8 tấn 7" (nhảy về cột 7).

### CÁC LỖI CẤM (NEGATIVE CONSTRAINTS)
- CẤM dùng: Tiếng Anh (Move, Rook...), Ký hiệu quốc tế/viết tắt (UCI, PGN, e2e4, N, B, R, K...), Số La Mã, Số Ả Rập chỉ quân.
- CẤM dùng các từ chỉ hướng tiếng Anh (Forward, Rank, File...).
- CẤM dùng dấu câu thừa (gạch ngang, dấu chấm, ngoặc) hoặc từ đệm ("nước đi", "play") trong lệnh.
- TUYỆT ĐỐI KHÔNG nhầm lẫn logic: Không dùng số bước cho Mã/Tượng; Không dùng số cột khi Xe/Pháo Tấn/Thoái.
EOT;

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key');

        // Nếu có model trong ENV, đưa nó lên đầu danh sách ưu tiên
        $envModel = config('services.groq.model');
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
     * Hàm gọi API đệ quy với cơ chế Fallback (Index-based) giống GroqService.
     * @param array $messages       Lịch sử chat gửi lên AI.
     * @param int   $modelIndex     Chỉ mục của model trong mảng availableModels đang thử.
     * @param bool  $jsonMode       Có bắt buộc trả về JSON không.
     * @param float $temperature    Độ sáng tạo.
     * @return string|null          Nội dung trả lời hoặc null nếu thất bại hoàn toàn.
     */
    protected function callWithFallback(array $messages, int $modelIndex = 0, bool $jsonMode = false, float $temperature = 1)
    {
        // 1. Kiểm tra điều kiện dừng: Nếu index vượt quá số lượng model khả dụng
        if (!isset($this->availableModels[$modelIndex])) {
            Log::error("GroqChessService: All models failed. Last attempted index: " . ($modelIndex - 1));
            return null;
        }

        // 2. Lấy model hiện tại theo index
        $currentModel = $this->availableModels[$modelIndex];

        try {
            // Cấu hình payload gửi đi
            $payload = [
                'model' => $currentModel,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => 4096,
            ];

            if ($jsonMode) {
                $payload['response_format'] = ['type' => 'json_object'];
            }

            // Thực hiện gọi HTTP Request
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post($this->baseUrl, $payload);

            // 3. Xử lý kết quả

            // TRƯỜNG HỢP: THÀNH CÔNG (200 OK)
            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'];
            }

            // TRƯỜNG HỢP: LỖI CÓ THỂ THỬ LẠI (429, 5xx)
            if (in_array($response->status(), [429, 500, 502, 503, 504])) {

                // [NEW] Xử lý Retry-After Header
                if ($response->status() === 429) {
                    $retryAfter = $response->header('Retry-After');

                    if ($retryAfter) {
                        $seconds = (int) $retryAfter;
                        // Nếu thời gian chờ hợp lý (ví dụ < 10s), ta sleep để tôn trọng API
                        // Nếu quá lâu, vẫn sleep để tránh spam rồi chuyển model khác
                        Log::warning("GroqChessService: Rate limit hit (429). Sleeping for {$seconds}s based on Retry-After header.");
                        sleep($seconds);
                    }
                }

                Log::warning("GroqChessService Model [$currentModel] failed with status {$response->status()}. Switching to next model...");

                // ĐỆ QUY: Thử model tiếp theo (Index + 1)
                return $this->callWithFallback($messages, $modelIndex + 1, $jsonMode, $temperature);
            }

            // TRƯỜNG HỢP: LỖI KHÔNG THỂ THỬ LẠI (400, 401...)
            Log::error("GroqChessService API Permanent Error [$currentModel]: " . $response->body());
            return null;

        } catch (\Exception $e) {
            // Lỗi kết nối hoặc timeout -> Thử model tiếp theo
            Log::warning("GroqChessService Exception [$currentModel]: " . $e->getMessage());

            // ĐỆ QUY: Thử model tiếp theo (Index + 1)
            return $this->callWithFallback($messages, $modelIndex + 1, $jsonMode, $temperature);
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
        return $this->callWithFallback($messages, 0, true, 1);
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
        $result = $this->callWithFallback($messages, 0, false, 1);

        return $result ?? "Xin lỗi, hiện tại tất cả các AI đều đang bận (Lỗi kết nối).";
    }
}
