<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EventStreamTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_stream_pushes_current_fen_once_in_testing()
    {
        $room = Room::create([
            'code' => 'room-test',
            'fen' => env('INITIAL_FEN', 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1'),
            'modified_at' => now(),
        ]);

        $response = $this->get('/api/getFEN/'.$room->code);

        $response->assertOk();
        $this->assertStringContainsString('text/event-stream', $response->headers->get('Content-Type'));

        $content = $response->streamedContent();

        // SSE format: lines with "data: {...}\n\n"
        $lines = array_filter(array_map('trim', explode("\n", $content)));
        $dataLine = collect($lines)->first(function ($line) {
            return str_starts_with($line, 'data: ');
        });

        $this->assertNotNull($dataLine, 'No data line found in SSE response');

        $json = substr($dataLine, strlen('data: '));
        $payload = json_decode($json, true);

        $this->assertIsArray($payload);
        $this->assertEquals($room->fen, $payload['fen'] ?? null);
    }
}
