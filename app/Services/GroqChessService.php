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

    /**
     * Lấy chuỗi dịch theo locale hiện tại (resources/lang/{locale}.json, khóa "chess_coach.*").
     * Dùng strtr() để thay placeholder một lượt, tránh việc nội dung người dùng bị thay thế lần hai.
     */
    protected function t(string $key, array $vars = []): string
    {
        $text = __('chess_coach.' . $key);

        if ($vars) {
            $pairs = [];
            foreach ($vars as $name => $value) {
                $pairs[':' . $name] = (string) $value;
            }
            $text = strtr($text, $pairs);
        }

        return $text;
    }

    protected function commonRules(): string
    {
        return $this->t('common_rules');
    }

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

        $visual = "\n" . $this->t('board_header') . "\n";
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
        $visual .= $this->t('board_legend') . "\n";

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
            $engineContext = "\n" . $this->t('engine_analyze', ['move' => $engineBestMove]);
        }

        // FEN nằm trong System Prompt để AI coi đây là bối cảnh bắt buộc
        $systemPrompt = $this->commonRules() . "\n\n" . $this->t('analyze_system', [
            'fen' => $fen,
            'visual_board' => $visualBoard,
            'engine_context' => $engineContext,
        ]);

        // User prompt chỉ cần kích hoạt nhiệm vụ
        $userPrompt = $this->commonRules() . "\n\n" . $this->t('analyze_user', [
            'fen' => $fen,
            'engine_move' => $engineBestMove,
        ]);

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
            $engineContext = "\n" . $this->t('engine_chat', ['move' => $engineBestMove]);
        }

        $systemPrompt = $this->commonRules() . "\n\n" . $this->t('chat_system', [
            'fen' => $fen,
            'visual_board' => $visualBoard,
            'engine_context' => $engineContext,
        ]);

        // User Prompt chỉ chứa câu hỏi, tránh lặp lại FEN gây nhiễu
        $userPrompt = $this->commonRules() . "\n\n" . $this->t('chat_user', [
            'fen' => $fen,
            'question' => $userQuestion,
        ]);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        // Gọi hàm đệ quy
        $result = $this->callWithFallback($messages, 0, false, 1);

        return $result ?? $this->t('busy_error');
    }

    /**
     * [NEW] Coach nhanh: trả về tip ngắn 1-3 câu, cá nhân hoá theo intent.
     * Không dùng JSON mode — trả plain text để hiển thị trực tiếp trong bubble.
     */
    public function quickCoach(string $fen, string $intent, ?string $engineBestMove = null): ?string
    {
        $visualBoard = $this->fenToVisualBoard($fen);

        $engineContext = $engineBestMove
            ? "\n" . $this->t('engine_chat', ['move' => $engineBestMove])
            : '';

        $intentInstruction = match ($intent) {
            'hint'    => $this->t('coach_intent_hint'),
            'threats' => $this->t('coach_intent_threats'),
            'eval'    => $this->t('coach_intent_eval'),
            default   => $this->t('coach_intent_eval'),
        };

        $systemPrompt = $this->commonRules() . "\n\n" . $this->t('coach_system', [
            'fen'          => $fen,
            'visual_board' => $visualBoard,
            'engine_context' => $engineContext,
            'intent'       => $intentInstruction,
        ]);

        $userPrompt = $this->t('coach_user', ['intent' => $intent]);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $userPrompt],
        ];

        return $this->callWithFallback($messages, 0, false, 0.85);
    }
}
