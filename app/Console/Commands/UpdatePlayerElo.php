<?php

namespace App\Console\Commands;

use App\Models\Room;
use App\Models\User;
use Illuminate\Console\Command;

class UpdatePlayerElo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:playerElo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update players elo';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::all();

        foreach ($users as $user) {
            $this->updatePlayerElo($user);
            $user->save();
        }

        $this->info('Elo of ' . User::count() . ' players updated successfully!');
    }
        /**
     * Update the Elo ratings for the room.
     *
     * @param User $room
     */
    protected function updatePlayerElo(User $user)
    {
        $userId = $user->id;
    
        $room = Room::select('host_elo', 'guest_elo')
            ->where(function ($query) use ($userId) {
                $query->where('host_id', $userId)
                      ->orWhere('guest_id', $userId);
            })
            ->first();
    
        if ($room) {
            $hostElo = $room->host_elo;
            $guestElo = $room->guest_elo;
            $user->elo = round(($hostElo + $guestElo) / 2);
        }
    }    
}