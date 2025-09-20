<?php

namespace App\Console\Commands;

use App\Models\Room;
use Illuminate\Console\Command;
use Atrox\Haikunator;

class CreateNewRoom extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:room';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new room';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Room::updateOrInsert(
            ['code' => md5(time())],
            ['fen' => env('INITIAL_FEN'), 'host_id' => NULL, 'result' => NULL, 'name' => Haikunator::haikunate(["tokenLength" => 0, "delimiter" => " "]), 'pass' => NULL, 'modified_at' => date('Y-m-d H:i:s')]
        );
        $this->info('New room created successfully!');
    }
}
