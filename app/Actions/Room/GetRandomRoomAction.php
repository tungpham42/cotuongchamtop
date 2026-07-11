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
            ->where('fen', '!=', env('INITIAL_FEN', 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1'))
            ->where('fen', 'LIKE', '% b %')
            ->inRandomOrder()
            ->first();
    }
}
