<?php

namespace App\Console\Commands;

use App\Models\Room;
use Illuminate\Console\Command;

class DeleteNonameRoomsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:nonameroom';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete noname rooms';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Room::where('name', '=', null)->orWhere('name', '=', '')->delete(); // Roomms have no name

        $this->info('Rooms with no name have been deleted.');
    }
}
