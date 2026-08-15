<?php

namespace App\Actions\Room;

use App\Models\Room;

class GetRandomRoomAction
{
    public function execute(): ?Room
    {
        return Room::whereNull('pass')
            ->whereNull('host_id')
            ->whereNull('result')
            ->where('fen', '!=', Room::INITIAL_FEN)
            ->where('fen', 'LIKE', '% b %')
            ->inRandomOrder()
            ->first();
    }
}
