<?php

namespace Tests\Feature;

use App\Events\RoomUpdated;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RoomSwitchTurnTest extends TestCase
{
    use RefreshDatabase;

    private function makeRoom(array $overrides = []): Room
    {
        return Room::create(array_merge([
            'code'         => 'room-' . uniqid(),
            'fen'          => env('INITIAL_FEN', 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1'),
            'red_time'     => 600,
            'black_time'   => 600,
            'active_player'=> null,
            'last_update'  => now(),
            'modified_at'  => now(),
        ], $overrides));
    }

    public function test_switch_turn_calculates_elapsed_and_flips_active_player()
    {
        Carbon::setTestNow(Carbon::parse('2024-01-01 00:00:05'));

        $room = $this->makeRoom([
            'code'          => 'switch-room',
            'active_player' => 'red',
            'last_update'   => Carbon::now()->subSeconds(5),
            'red_time'      => 600,
            'black_time'    => 600,
        ]);

        $response = $this->postJson("/switchTurn/{$room->code}", [
            'current_player' => 'red',
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $updated = Room::where('code', $room->code)->first();
        $this->assertSame(595, $updated->red_time, 'Red time should reduce by elapsed seconds');
        $this->assertSame(600, $updated->black_time);
        $this->assertSame('black', $updated->active_player);
        $this->assertEquals($updated->last_update, $updated->modified_at);

        Carbon::setTestNow();
    }

    public function test_switch_turn_dispatches_room_updated_event()
    {
        Event::fake([RoomUpdated::class]);
        Carbon::setTestNow(Carbon::parse('2024-01-01 00:00:10'));

        $room = $this->makeRoom([
            'code'          => 'event-room',
            'active_player' => 'black',
            'last_update'   => Carbon::now()->subSeconds(2),
        ]);

        $this->postJson("/switchTurn/{$room->code}", [
            'current_player' => 'black',
        ])->assertOk();

        Event::assertDispatched(RoomUpdated::class, function ($event) use ($room) {
            return $event->room->code === $room->code
                && $event->room->active_player === 'red'
                && $event->room->black_time < 600;
        });

        Carbon::setTestNow();
    }

    public function test_update_fen_triggers_event_and_updates_room()
    {
        Event::fake([RoomUpdated::class]);
        $room = $this->makeRoom([
            'code' => 'fen-room',
            'fen'  => 'old-fen',
        ]);

        $payload = [
            'ma-phong' => $room->code,
            'FEN'      => 'new-fen-position',
        ];

        $this->postJson('/api/updateFEN', $payload)
            ->assertOk()
            ->assertJson(['success' => true]);

        $updated = Room::where('code', $room->code)->first();
        $this->assertSame('new-fen-position', $updated->fen);

        Event::assertDispatched(RoomUpdated::class, function ($event) use ($room) {
            return $event->room->code === $room->code
                && $event->room->fen === 'new-fen-position';
        });
    }
}
