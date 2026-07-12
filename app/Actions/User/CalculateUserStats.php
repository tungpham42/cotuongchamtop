<?php

namespace App\Actions\User;

use App\Models\Room;
use App\Models\User;

class CalculateUserStats
{
    public function updatePoints(int $userId): int
    {
        $stats = $this->getMatchStats($userId);
        $totalPoints = 3 * ($stats['win'] + $stats['lose']) + $stats['draw']; // Based on your original formula

        User::where('id', $userId)->update(['points' => $totalPoints]);

        return $totalPoints;
    }

    public function getMatchStats(int $userId): array
    {
        $winHost = Room::where('host_id', $userId)->where('result', '1')->count();
        $winGuest = Room::where('guest_id', $userId)->where('result', '-1')->count();
        $loseHost = Room::where('guest_id', $userId)->where('result', '1')->count();
        $loseGuest = Room::where('host_id', $userId)->where('result', '-1')->count();
        $drawHost = Room::where('host_id', $userId)->where('result', '0')->count();
        $drawGuest = Room::where('guest_id', $userId)->where('result', '0')->count();

        return [
            'win' => $winHost + $winGuest,
            'lose' => $loseHost + $loseGuest,
            'draw' => $drawHost + $drawGuest,
            'total' => $winHost + $winGuest + $loseHost + $loseGuest + $drawHost + $drawGuest,
        ];
    }
}
