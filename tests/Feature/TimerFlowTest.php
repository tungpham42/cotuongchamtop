<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class TimerFlowTest extends TestCase
{
    use RefreshDatabase;

    private function createRoom(): Room
    {
        return Room::create([
            'code' => 'timer-room',
            'fen' => env('INITIAL_FEN', 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1'),
            'red_time' => 600,
            'black_time' => 600,
            'active_player' => null,
            'last_update' => null,
            'modified_at' => now(),
        ]);
    }

    public function test_start_and_pause_timer_decreases_time()
    {
        $room = $this->createRoom();

        // Bắt đầu cho Đỏ
        $this->postJson("/startTimer/{$room->code}/red")->assertStatus(200);

        // Giả lập đã trôi 5 giây: cập nhật last_update lùi 5s
        DB::table('rooms')->where('code', $room->code)->update([
            'last_update' => now()->subSeconds(5),
        ]);

        // Pause sẽ trừ thời gian
        $this->postJson("/pauseTimer/{$room->code}/red")->assertStatus(200);

        $updated = Room::where('code', $room->code)->first();
        $this->assertLessThan(600, $updated->red_time);
        $this->assertEquals(595, $updated->red_time);
        $this->assertNull($updated->active_player);
    }

    public function test_save_time_clamps_non_negative()
    {
        $room = $this->createRoom();

        $this->postJson("/saveTime/{$room->code}", [
            'red_time' => -10,
            'black_time' => 5,
        ])->assertStatus(200);

        $updated = Room::where('code', $room->code)->first();
        $this->assertEquals(0, $updated->red_time);
        $this->assertEquals(5, $updated->black_time);
    }

    public function test_get_time_returns_active_player_and_live_time()
    {
        $room = $this->createRoom();

        // start timer for black
        $this->postJson("/startTimer/{$room->code}/black")->assertStatus(200);

        // lùi last_update 3s để giả lập thời gian trôi
        DB::table('rooms')->where('code', $room->code)->update([
            'last_update' => now()->subSeconds(3),
        ]);

        $response = $this->getJson("/getTime/{$room->code}");
        $response->assertStatus(200);
        $payload = $response->json();

        $this->assertEquals('black', $payload['active_player']);
        $this->assertEquals(600, $payload['red_time']);
        // black_time đã trôi khoảng 3s (có thể sai lệch 1s do thời gian chạy)
        $this->assertTrue($payload['black_time'] <= 597 && $payload['black_time'] >= 596);
    }
}
