<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Room;

class GameStarted implements ShouldBroadcast
{
    public $room;
    public $users;

    public function __construct(Room $room, array $userIds)
    {
        $this->room = $room;
        $this->userIds = $userIds;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('game.' . $this->room->code);
    }
}
