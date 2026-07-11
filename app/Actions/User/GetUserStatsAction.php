<?php

namespace App\Actions\User;

use App\Models\Room;
use App\Models\User;
use App\Models\Session as DbSession;

class GetUserStatsAction
{
    public function getTotalMatchPoints(int $id): int
    {
        $winHostMatchPoints = Room::where('host_id', $id)->where('result', '1')->count();
        $winGuestMatchPoints = Room::where('guest_id', $id)->where('result', '-1')->count();

        $loseHostMatchPoints = Room::where('guest_id', $id)->where('result', '1')->count();
        $loseGuestMatchPoints = Room::where('host_id', $id)->where('result', '-1')->count();

        $drawHostMatchPoints = Room::where('host_id', $id)->where('result', '0')->count();
        $drawGuestMatchPoints = Room::where('guest_id', $id)->where('result', '0')->count();

        return $winHostMatchPoints + $winGuestMatchPoints +
               $loseHostMatchPoints + $loseGuestMatchPoints +
               $drawHostMatchPoints + $drawGuestMatchPoints;
    }

    public function getOnlinePlayersCount(): int
    {
        return DbSession::whereNotNull('user_id')->pluck('user_id')->unique()->count();
    }
}
