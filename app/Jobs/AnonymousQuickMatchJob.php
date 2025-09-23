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
        // Clean up old rooms (older than 5 minutes with no guest)
        Room::whereNotNull('host_session')
            ->whereNull('guest_session')
            ->where('modified_at', '<', now()->subMinutes(5))
            ->delete();

        // Find an open anonymous room (has host, no guest)
        $room = Room::findOpenAnonymousRoom();

        if ($room) {
            // Join as guest
            $room->update([
                'guest_session' => $this->sessionId,
                'modified_at' => now(),
            ]);
            return;
        }

        // No open room found, create a new one as host
        $roomCode = md5(time() . $this->sessionId);
        Room::create([
            'code' => $roomCode,
            'fen' => env('INITIAL_FEN'),
            'name' => Haikunator::haikunate(["tokenLength" => 0, "delimiter" => " "]),
            'host_session' => $this->sessionId,
            'guest_session' => null,
            'host_id' => null,
            'guest_id' => null,
            'pass' => null,
            'modified_at' => now(),
        ]);
    }
}