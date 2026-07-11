<?php

namespace App\Actions\Room;

use App\Models\Room;
use App\Models\User;
use App\Actions\Game\CalculateEloRatingsAction;
use Illuminate\Support\Facades\DB;
use Exception;

class UpdateRoomEloAction
{
    private CalculateEloRatingsAction $calculateEloAction;

    public function __construct(CalculateEloRatingsAction $calculateEloAction)
    {
        $this->calculateEloAction = $calculateEloAction;
    }

    public function execute(string $code, string $result)
    {
        return DB::transaction(function () use ($code, $result) {
            $room = Room::select('host_id', 'guest_id')->where('code', $code)->firstOrFail();

            $host = User::lockForUpdate()->findOrFail($room->host_id);
            $guest = User::lockForUpdate()->findOrFail($room->guest_id);

            // Execute the new Action class instead of the static controller method
            $eloRatings = $this->calculateEloAction->execute($host->elo, $guest->elo, $result);

            [$host->elo, $guest->elo] = $eloRatings;

            $host->save();
            $guest->save();

            return [
                'host_elo' => $host->elo,
                'guest_elo' => $guest->elo,
            ];
        });
    }
}
