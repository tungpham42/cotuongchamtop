<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebRtcSignal implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $roomCode;
    public array $payload;
    public string|int $senderId;

    public function __construct(string $roomCode, array $payload, string|int $senderId)
    {
        $this->roomCode = $roomCode;
        $this->payload = $payload;
        $this->senderId = $senderId;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('room.' . $this->roomCode);
    }

    public function broadcastAs(): string
    {
        return 'webrtc.signal';
    }
}
