<?php

namespace Tests\Feature;

use App\Events\RoomUpdated;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TimerFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-10-12 12:00:00.500'));
        Event::fake([RoomUpdated::class]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Cache::flush();
        parent::tearDown();
    }

    private function createRoom(array $attributes = []): Room
    {
        return Room::create(array_merge([
            'code' => 'timer-room',
            'fen' => Room::INITIAL_FEN,
            'red_time' => 600,
            'black_time' => 600,
            'active_player' => 'waiting',
            'last_update' => null,
            'modified_at' => now(),
            'host_session' => 'host-test',
            'guest_session' => 'guest-test',
        ], $attributes));
    }

    public function test_start_match_is_idempotent_and_does_not_reset_the_anchor(): void
    {
        $room = $this->createRoom();
        $this->postJson("/api/startMatch/{$room->code}")
            ->assertOk()->assertJsonPath('active_player', 'red')->assertJsonPath('timer_revision', 1);
        Carbon::setTestNow(now()->addSeconds(3));
        $this->postJson("/api/startMatch/{$room->code}")
            ->assertOk()->assertJsonPath('red_time', 597)->assertJsonPath('timer_revision', 1);
    }

    public function test_start_match_waits_for_both_players(): void
    {
        $room = $this->createRoom(['guest_session' => null]);
        $this->postJson("/api/startMatch/{$room->code}")
            ->assertOk()->assertJsonPath('active_player', 'waiting')->assertJsonPath('timer_revision', 0);
    }

    public function test_anonymous_readiness_does_not_expose_session_identifiers(): void
    {
        $room = $this->createRoom();
        $response = $this->postJson('/api/getRoomIds', ['ma-phong' => $room->code])
            ->assertOk()->assertJsonPath('has_host', true)->assertJsonPath('has_guest', true);
        $this->assertArrayNotHasKey('host_session', $response->json());
        $this->assertArrayNotHasKey('guest_session', $response->json());
    }

    public function test_resume_does_not_charge_the_paused_period(): void
    {
        $room = $this->createRoom([
            'active_player' => 'paused:red', 'red_time' => 590,
            'last_update' => now()->subMinutes(5),
        ]);
        Cache::put("room_{$room->code}_move_elapsed", 10);
        $this->postJson("/api/startTimer/{$room->code}/red")
            ->assertOk()->assertJsonPath('red_time', 590)->assertJsonPath('move_elapsed', 10);
        Carbon::setTestNow(now()->addSeconds(2));
        $this->getJson("/api/getTime/{$room->code}")
            ->assertOk()->assertJsonPath('red_time', 588)->assertJsonPath('move_elapsed', 12);
    }

    public function test_save_time_clamps_negative_values_and_preserves_fractions(): void
    {
        $room = $this->createRoom();
        $this->postJson("/api/saveTime/{$room->code}", ['red_time' => -10, 'black_time' => 5.125])
            ->assertOk()->assertJsonPath('red_time', 0)->assertJsonPath('black_time', 5.125);
        $this->assertEquals(5.125, (float) $room->fresh()->black_time);
    }

    public function test_get_time_returns_a_live_snapshot_with_version_and_timestamp(): void
    {
        $room = $this->createRoom(['active_player' => 'black', 'last_update' => now()->subSeconds(3)]);
        $this->getJson("/api/getTime/{$room->code}")
            ->assertOk()->assertJsonPath('active_player', 'black')->assertJsonPath('red_time', 600)
            ->assertJsonPath('black_time', 597)->assertJsonPath('timed_out', false)
            ->assertJsonPath('snapshot_at', now()->getTimestampMs())->assertJsonPath('timer_revision', 0);
    }

    private function movePayload(Room $room): array
    {
        return [
            'ma-phong' => $room->code, 'expected_fen' => $room->fen,
            'FEN' => str_replace(' r ', ' b ', $room->fen),
            'move' => 'a3a4', 'current_player' => 'red',
        ];
    }

    public function test_move_saves_fen_and_timer_together_and_retry_does_not_charge_again(): void
    {
        $room = $this->createRoom(['active_player' => 'red', 'last_update' => now()->subMilliseconds(1250)]);
        $payload = $this->movePayload($room);
        $this->postJson('/api/updateFEN', $payload)->assertOk()
            ->assertJsonPath('success', true)->assertJsonPath('fen', $payload['FEN'])
            ->assertJsonPath('active_player', 'black')->assertJsonPath('red_time', 598.75)
            ->assertJsonPath('timer_revision', 1);
        Carbon::setTestNow(now()->addSeconds(2));
        $this->postJson('/api/updateFEN', $payload)->assertOk()
            ->assertJsonPath('success', true)->assertJsonPath('red_time', 598.75)
            ->assertJsonPath('black_time', 598)->assertJsonPath('timer_revision', 1);
        $this->assertEquals(598.75, (float) $room->fresh()->red_time);
        Event::assertDispatchedTimes(RoomUpdated::class, 1);
        Event::assertDispatched(RoomUpdated::class, fn ($event) =>
            $event->timer['fen'] === $payload['FEN'] && $event->timer['timer_revision'] === 1);
    }

    public function test_stale_move_is_rejected_without_changing_board_or_clock(): void
    {
        $room = $this->createRoom(['active_player' => 'red', 'last_update' => now()]);
        $payload = $this->movePayload($room);
        $payload['expected_fen'] = 'stale';
        $this->postJson('/api/updateFEN', $payload)->assertOk()
            ->assertJsonPath('success', false)->assertJsonPath('fen', $room->fen)
            ->assertJsonPath('active_player', 'red')->assertJsonPath('timer_revision', 0);
        Event::assertNotDispatched(RoomUpdated::class);
    }

    public function test_expired_player_cannot_switch_turn_or_save_a_move(): void
    {
        $room = $this->createRoom(['active_player' => 'red', 'last_update' => now()->subSeconds(120)]);
        $this->postJson('/api/updateFEN', $this->movePayload($room))->assertOk()
            ->assertJsonPath('success', false)->assertJsonPath('fen', $room->fen)
            ->assertJsonPath('active_player', 'red')->assertJsonPath('timed_out', true);
        $this->postJson("/api/switchTurn/{$room->code}", ['current_player' => 'red'])
            ->assertOk()->assertJsonPath('success', false)->assertJsonPath('timed_out', true);
    }

    public function test_legacy_switch_turn_does_not_charge_duplicate_requests(): void
    {
        $room = $this->createRoom(['active_player' => 'red', 'last_update' => now()->subSeconds(5)]);
        $this->postJson("/api/switchTurn/{$room->code}", ['current_player' => 'red'])
            ->assertOk()->assertJsonPath('success', true)->assertJsonPath('red_time', 595);
        $this->postJson("/api/switchTurn/{$room->code}", ['current_player' => 'red'])
            ->assertOk()->assertJsonPath('success', false)->assertJsonPath('red_time', 595)
            ->assertJsonPath('timer_revision', 1);
    }

    public function test_timeout_result_is_rejected_until_the_server_clock_expires(): void
    {
        $room = $this->createRoom(['active_player' => 'red', 'last_update' => now()]);
        $this->postJson('/api/updateSideResult', [
            'ma-phong' => $room->code, 'result' => '-1', 'side' => 'red', 'timeout' => true,
        ])->assertStatus(409);
        $this->assertNull($room->fresh()->result);
        Carbon::setTestNow(now()->addSeconds(120));
        $this->postJson('/api/updateSideResult', [
            'ma-phong' => $room->code, 'result' => '-1', 'side' => 'red', 'timeout' => true,
        ])->assertOk();
        $this->assertEquals(-1, $room->fresh()->result);
    }
}
