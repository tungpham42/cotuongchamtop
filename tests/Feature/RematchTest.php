<?php

namespace Tests\Feature;

use App\Events\RematchUpdated;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RematchTest extends TestCase
{
    use RefreshDatabase;

    private function finishedRoom(array $overrides = []): array
    {
        $host = User::factory()->create();
        $guest = User::factory()->create();
        $room = Room::create(array_merge([
            'code' => 'finished-room',
            'fen' => Room::INITIAL_FEN,
            'name' => 'Test',
            'host_id' => $host->id,
            'guest_id' => $guest->id,
            'result' => '1',
            'modified_at' => now(),
        ], $overrides));

        Cache::forget('rematch_finished-room');

        return [$room, $host, $guest];
    }

    public function test_request_then_accept_creates_swapped_room(): void
    {
        Event::fake([RematchUpdated::class]);
        [$room, $host, $guest] = $this->finishedRoom();

        $this->postJson('/api/room/finished-room/rematch', ['action' => 'request', 'id' => $host->id])
            ->assertOk()->assertJson(['status' => 'requested']);

        $res = $this->postJson('/api/room/finished-room/rematch', ['action' => 'accept', 'id' => $guest->id])
            ->assertOk()->assertJson(['status' => 'accepted']);

        $new = Room::where('code', $res->json('new_code'))->first();
        $this->assertNotNull($new);
        $this->assertSame($guest->id, $new->host_id);
        $this->assertSame($host->id, $new->guest_id);
        $this->assertNull($new->result);
        $this->assertSame(Room::INITIAL_FEN, $new->fen);

        Event::assertDispatched(RematchUpdated::class, fn ($e) => $e->status === 'accepted' && $e->by === 'black' && $e->newCode === $new->code);
    }

    public function test_simultaneous_requests_auto_accept_once(): void
    {
        [$room, $host, $guest] = $this->finishedRoom();

        $this->postJson('/api/room/finished-room/rematch', ['action' => 'request', 'id' => $host->id])->assertOk();
        $a = $this->postJson('/api/room/finished-room/rematch', ['action' => 'request', 'id' => $guest->id])
            ->assertOk()->assertJson(['status' => 'accepted']);
        $b = $this->postJson('/api/room/finished-room/rematch', ['action' => 'accept', 'id' => $host->id])
            ->assertOk()->assertJson(['status' => 'accepted']);

        $this->assertSame($a->json('new_code'), $b->json('new_code'));
        $this->assertSame(2, Room::count());
    }

    public function test_decline_clears_pending_request(): void
    {
        [$room, $host, $guest] = $this->finishedRoom();

        $this->postJson('/api/room/finished-room/rematch', ['action' => 'request', 'id' => $host->id])->assertOk();
        $this->postJson('/api/room/finished-room/rematch', ['action' => 'decline', 'id' => $guest->id])
            ->assertOk()->assertJson(['status' => 'declined']);
        $this->postJson('/api/room/finished-room/rematch', ['action' => 'accept', 'id' => $guest->id])->assertStatus(409);
        $this->assertSame(1, Room::count());
    }

    public function test_outsider_and_unfinished_and_tournament_rooms_are_rejected(): void
    {
        [$room, $host, $guest] = $this->finishedRoom();
        $outsider = User::factory()->create();

        $this->postJson('/api/room/finished-room/rematch', ['action' => 'request', 'id' => $outsider->id])->assertStatus(403);

        $room->update(['result' => null]);
        $this->postJson('/api/room/finished-room/rematch', ['action' => 'request', 'id' => $host->id])->assertStatus(409);

        $room->update(['result' => '1', 'tournament_id' => 1]);
        $this->postJson('/api/room/finished-room/rematch', ['action' => 'request', 'id' => $host->id])->assertStatus(409);

        $this->postJson('/api/room/nope/rematch', ['action' => 'request', 'id' => $host->id])->assertStatus(404);
    }

    private function finishedGuestRoom(): Room
    {
        Cache::forget('rematch_guest-room');

        return Room::create([
            'code' => 'guest-room',
            'fen' => Room::INITIAL_FEN,
            'name' => 'Guests',
            'host_session' => 'guest_red_session',
            'guest_session' => 'guest_black_session',
            'result' => '-1',
            'modified_at' => now(),
        ]);
    }

    public function test_guests_can_rematch_with_swapped_sessions(): void
    {
        Event::fake([RematchUpdated::class]);
        $this->finishedGuestRoom();

        $this->postJson('/api/room/guest-room/rematch', ['action' => 'request', 'session_id' => 'guest_red_session'])
            ->assertOk()->assertJson(['status' => 'requested']);

        $res = $this->postJson('/api/room/guest-room/rematch', ['action' => 'accept', 'session_id' => 'guest_black_session'])
            ->assertOk()->assertJson(['status' => 'accepted']);

        $new = Room::where('code', $res->json('new_code'))->first();
        $this->assertSame('guest_black_session', $new->host_session);
        $this->assertSame('guest_red_session', $new->guest_session);
        $this->assertNull($new->host_id);
        $this->assertNull($new->guest_id);
        $this->assertNull($new->result);

        // The session ids are credentials and must never be broadcast.
        Event::assertDispatched(RematchUpdated::class, fn ($e) => $e->status === 'requested' && $e->by === 'red');
        Event::assertDispatched(RematchUpdated::class, fn ($e) => $e->status === 'accepted' && $e->newCode === $new->code);
    }

    public function test_guest_rematch_rejects_unknown_or_missing_session(): void
    {
        $this->finishedGuestRoom();

        $this->postJson('/api/room/guest-room/rematch', ['action' => 'request', 'session_id' => 'someone_else'])->assertStatus(403);
        $this->postJson('/api/room/guest-room/rematch', ['action' => 'request'])->assertStatus(403);
        $this->assertSame(1, Room::count());
    }

    public function test_user_cannot_rematch_a_guest_room(): void
    {
        $this->finishedGuestRoom();
        $user = User::factory()->create();

        $this->postJson('/api/room/guest-room/rematch', ['action' => 'request', 'id' => $user->id])->assertStatus(403);
    }
}
