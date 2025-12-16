<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateResultAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_update_result_without_role()
    {
        $host = User::factory()->create();
        $guest = User::factory()->create();

        Room::create([
            'code' => 'room-auth',
            'fen' => env('INITIAL_FEN', 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1'),
            'host_id' => $host->id,
            'guest_id' => $guest->id,
            'modified_at' => now(),
        ]);

        $response = $this->postJson('/api/updateResult', [
            'ma-phong' => 'room-auth',
            'result' => '1',
            'id' => 999, // user không thuộc phòng
        ]);

        $response->assertStatus(403);
        $this->assertNull(Room::where('code', 'room-auth')->value('result'));
    }

    public function test_host_can_update_result()
    {
        $host = User::factory()->create();
        $guest = User::factory()->create();

        Room::create([
            'code' => 'room-auth-ok',
            'fen' => env('INITIAL_FEN', 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1'),
            'host_id' => $host->id,
            'guest_id' => $guest->id,
            'modified_at' => now(),
        ]);

        $response = $this->actingAs($host)->postJson('/api/updateResult', [
            'ma-phong' => 'room-auth-ok',
            'result' => '1',
        ]);

        $response->assertOk();
        $this->assertEquals('1', Room::where('code', 'room-auth-ok')->value('result'));
    }
}
