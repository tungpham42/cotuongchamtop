<?php

namespace App\Events;

use App\Models\Room;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class RoomUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $room;

    public function __construct(Room $room)
    {
        $this->room = $room;
    }

    // Đặt tên kênh (channel) dựa trên mã phòng
    public function broadcastOn()
    {
        return new Channel('room.' . $this->room->code);
    }

    // (Tùy chọn) Đặt tên sự kiện
    public function broadcastAs()
    {
        return 'room.updated';
    }
}
