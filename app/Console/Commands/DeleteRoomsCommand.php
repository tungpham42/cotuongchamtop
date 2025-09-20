<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\User;
use Illuminate\Console\Command;

class DeleteRoomsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:rooms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old rooms and rooms with non-existent users';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = 9; // Number of days after which rooms should be deleted
        $date = Carbon::now()->subDays($days);

        // Delete rooms based on existing conditions
        Room::where('modified_at', '<', $date)
            ->where(function ($query) {
                $query->where('fen', '=', 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1')
                      ->orWhere('fen', '=', 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1 resign');
            })
            ->delete();
        
        Room::where('modified_at', '<', $date)
            ->whereNull('host_id')
            ->whereNull('result')
            ->delete();
        
        Room::where('modified_at', '<', $date)
            ->whereNotNull('host_id')
            ->whereNotNull('result')
            ->where('fen', 'LIKE', '% - - 0 1%')
            ->delete();
        
        // Delete rooms where host_id or guest_id doesn't exist in users table
        Room::whereNotIn('host_id', User::pluck('id')->toArray())
            ->orWhereNotIn('guest_id', User::pluck('id')->toArray())
            ->delete();
        
        $this->info('Rooms older than ' . $days . ' days and rooms with missing users have been deleted.');
    }
}
