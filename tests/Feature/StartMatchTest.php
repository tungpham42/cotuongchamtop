<?php

namespace Tests\Feature;

use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StartMatchTest extends TestCase
{
    use RefreshDatabase;

    private const INITIAL_FEN = 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1';

    private function makeRoom(array $overrides = []): Room
    {
        return Room::create(array_merge([
            'code'          => 'start-' . uniqid(),
            'fen'           => self::INITIAL_FEN,
            'red_time'      => 600,
            'black_time'    => 600,
            'active_player' => null,
            'last_update'   => null,
            'modified_at'   => now(),
        ], $overrides));
    }

    public function test_start_match_starts_red_clock_for_room_without_active_player()
    {
        // Matchmaking / invite / rematch rooms are created with a null active_player.
        $room = $this->makeRoom();

        $this->postJson("/api/startMatch/{$room->code}")->assertOk()->assertJson(['success' => true]);

        $room->refresh();
        $this->assertSame('red', $room->active_player);
        $this->assertNotNull($room->last_update);
    }

    public function test_start_match_starts_tournament_room_in_waiting_state()
    {
        $room = $this->makeRoom(['active_player' => 'waiting']);

        $this->postJson("/api/startMatch/{$room->code}")->assertOk();

        $this->assertSame('red', $room->fresh()->active_player);
    }

    public function test_start_match_uses_the_side_to_move_from_the_fen()
    {
        $room = $this->makeRoom([
            'fen' => str_replace(' r ', ' b ', self::INITIAL_FEN),
        ]);

        $this->postJson("/api/startMatch/{$room->code}")->assertOk();

        $this->assertSame('black', $room->fresh()->active_player);
    }

    public function test_start_match_does_not_touch_a_running_clock()
    {
        $room = $this->makeRoom(['active_player' => 'black']);

        $this->postJson("/api/startMatch/{$room->code}")->assertOk();

        $this->assertSame('black', $room->fresh()->active_player);
    }

    public function test_start_match_does_not_restart_a_finished_room()
    {
        $room = $this->makeRoom(['result' => '1']);

        $this->postJson("/api/startMatch/{$room->code}")->assertOk();

        $this->assertNull($room->fresh()->active_player);
    }
}
