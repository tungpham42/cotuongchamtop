<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Room;
use Illuminate\Console\Command;

class UpdateRoomsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:rooms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update rooms';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $rooms = Room::where('fen', 'LIKE', '%resign%')->get();

        foreach ($rooms as $room) {
            // Update rooms' results based on resign
            $room->result = '0';
            $room->save();
        }

        $this->info($rooms->count() . ' rooms with resign updated successfully!');
    }
}
