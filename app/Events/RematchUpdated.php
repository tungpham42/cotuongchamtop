<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast on the *finished* room's rematch channel.
 *
 * status: requested | accepted | declined | cancelled
 *
 * The regular room.{code} channel is left by the clients once a game is over,
 * so rematch negotiation uses its own channel.
 */
class RematchUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $roomCode,
        public string $status,
        public ?int $by = null,
        public ?string $newCode = null,
        public ?int $newHostId = null,
    ) {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('rematch.' . $this->roomCode);
    }

    public function broadcastAs(): string
    {
        return 'rematch.updated';
    }
}
