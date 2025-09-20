<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdatePointsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:points';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update users points';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get all users
        $users = User::all();

        // Loop through the users and update their points
        foreach ($users as $user) {
            $id = $user->id;

            $hostPoints = Room::where('host_id', '=', $id)
                                ->where('result', '=', '1')
                                ->count();

            $guestPoints = Room::where('guest_id', '=', $id)
                                ->where('result', '=', '-1')
                                ->count();

            $hostDrawPoints = Room::where('host_id', '=', $id)
                                ->where('result', '=', '0')
                                ->count();

            $guestDrawPoints = Room::where('guest_id', '=', $id)
                                ->where('result', '=', '0')
                                ->count();

            $userPoints = 3 * ($hostPoints + $guestPoints) + $hostDrawPoints + $guestDrawPoints;
            $user->points = $userPoints;
            $user->save();
        }

        $this->info('Points of ' . User::count() . ' users updated successfully!');
    }
}
