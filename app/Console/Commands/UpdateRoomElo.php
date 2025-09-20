<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Room;
use App\Http\Controllers\GameController;
use Illuminate\Console\Command;

class UpdateRoomElo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:roomElo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update rooms elo';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $rooms = Room::all();

        foreach ($rooms as $room) {
            $this->updateRoomScores($room);
            $this->updateRoomElo($room);
            $room->save();
        }

        $this->info('Elo of ' . Room::count() . ' rooms updated successfully!');
    }

    /**
     * Update the scores of the room based on host and guest results.
     *
     * @param Room $room
     */
    protected function updateRoomScores(Room $room)
    {
        $room->host_score = $this->calculateScore($room->host_id, 'host');
        $room->guest_score = $this->calculateScore($room->guest_id, 'guest');
    }

    /**
     * Calculate the score for a given player (host or guest).
     *
     * @param int $playerId
     * @param string $role
     * @return float
     */
    protected function calculateScore($playerId, $role)
    {
        $winScore = Room::where($role . '_id', $playerId)
            ->where('result', $role === 'host' ? '1' : '-1')
            ->count();

        $drawScore = Room::where($role . '_id', $playerId)
            ->where('result', '0')
            ->count();

        return $winScore + 0.5 * $drawScore;
    }

    /**
     * Update the Elo ratings for the room.
     *
     * @param Room $room
     */
    protected function updateRoomElo(Room $room)
    {
        if ($room->host_id !== null && $room->guest_id !== null) {
            $host = User::find($room->host_id);
            $guest = User::find($room->guest_id);

            $room->host_elo = GameController::calculateElo($host->elo, $guest->elo, $room->host_score);
            $room->guest_elo = GameController::calculateElo($guest->elo, $host->elo, $room->guest_score);
        }
    }
}
