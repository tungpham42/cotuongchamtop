<?php

namespace App\Events;

use App\Models\Room;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $room;

    public function __construct(Room $room)
    {
        $this->room = $room;
    }

    public function broadcastOn()
    {
        return new Channel('room.' . $this->room->code);
    }

    public function broadcastAs()
    {
        return 'room.updated';
    }

    public function broadcastWith()
    {
        return [
            'code'          => $this->room->code,
            'fen'           => $this->room->fen,
            'red_time'      => $this->room->red_time,
            'black_time'    => $this->room->black_time,
            'active_player' => $this->room->active_player,
            'modified_at'   => (string) $this->room->modified_at,
            'last_update'   => (string) $this->room->last_update,
        ];
    }
}
