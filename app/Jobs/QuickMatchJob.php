<?php

namespace App\Jobs;

use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Room;

class QuickMatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get the current user
        $user = auth()->user();

        // Check if there are any players waiting for a match
        $waitingPlayers = Cache::get('waiting_players', []);

        if (!empty($waitingPlayers)) {
            // Match the current player with the first waiting player
            $opponent = array_shift($waitingPlayers);

            // Create a new room with the two players
            DB::table('rooms')
                ->updateOrInsert(
                ['code' => md5(time())],
                [
                    'fen' => 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1',
                    'host_id' => $user->id,
                    'guest_id' => $opponent['id'],
                    'pass' => '',
                    'modified_at' => date('Y-m-d H:i:s')
                ]
            );
            // $room = Room::create([
            //     'host_id' => $user->id,
            //     'guest_id' => $opponent['id'],
            // ]);

            // Notify both players about the match
            // event(new GameStarted($room, [$user->id, $opponent['id']]));

            // Update the waiting players list
            Cache::put('waiting_players', $waitingPlayers);
        } else {
            // Add the current player to the waiting list
            $waitingPlayers[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];

            // Update the waiting players list
            Cache::put('waiting_players', $waitingPlayers, now()->addMinutes(5));
        }
    }
}
