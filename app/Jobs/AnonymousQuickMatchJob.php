<?php

namespace App\Jobs;

use App\Models\Room;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Atrox\Haikunator;

class AnonymousQuickMatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sessionId;

    /**
     * Create a new job instance.
     *
     * @param string $sessionId
     */
    public function __construct($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Find an open anonymous room
        $room = Room::findOpenAnonymousRoom();

        if ($room) {
            // Mark the room as having a second player
            $room->update([
                'name' => $room->name ?: Haikunator::haikunate(["tokenLength" => 0, "delimiter" => " "]),
                'modified_at' => now(),
            ]);

            // Store session IDs in session storage (handled by frontend)
            // Optionally, store guest_token if schema is updated
            return;
        }

        // No open room found, create a new one
        $roomCode = md5(time());
        Room::create([
            'code' => $roomCode,
            'fen' => env('INITIAL_FEN'),
            'name' => Haikunator::haikunate(["tokenLength" => 0, "delimiter" => " "]),
            'host_id' => null,
            'guest_id' => null,
            'pass' => null,
            'modified_at' => now(),
        ]);
    }
}