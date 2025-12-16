<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FullMatchFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_fen_multiple_times_without_hanging()
    {
        $room = Room::create([
            'code' => 'flow-room',
            'fen' => env('INITIAL_FEN', 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1'),
            'modified_at' => now(),
        ]);

        $lastFen = null;

        // Mô phỏng 300 lần cập nhật FEN (đủ dài để lộ ra lỗi treo/ghi đè)
        for ($i = 1; $i <= 300; $i++) {
            $turn = $i % 2 === 0 ? 'r' : 'b';
            $lastFen = "rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR {$turn} - - 0 {$i}";

            $this->postJson('/api/updateFEN', [
                'ma-phong' => $room->code,
                'FEN' => $lastFen,
            ])->assertStatus(200);
        }

        // Đảm bảo FEN mới nhất được lưu sau nhiều lần cập nhật
        $this->get('/api/readFEN/'.$room->code)
            ->assertOk()
            ->assertSee($lastFen);

        // SSE trả FEN cuối cùng trong môi trường test (stream 1 lần)
        $response = $this->get('/api/getFEN/'.$room->code);
        $payload = $response->streamedContent();

        $lines = array_filter(array_map('trim', explode("\n", $payload)));
        $dataLine = collect($lines)->first(function ($line) {
            return str_starts_with($line, 'data: ');
        });

        $this->assertNotNull($dataLine, 'No data line found in SSE response');

        $json = substr($dataLine, strlen('data: '));
        $parsed = json_decode($json, true);

        $this->assertIsArray($parsed);
        $this->assertEquals($lastFen, $parsed['fen'] ?? null);
    }
}
