<?php

namespace App\Console\Commands;

use App\Models\Room;
use App\Models\Puzzle;
use App\Models\User;
use Illuminate\Console\Command;

class DeleteBadWordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:badWords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete bad words';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $badWords = [' Cu', ' cu', 'vl', 'dcm', 'dm', 'vailon', 'dume', 'ditme', ' ngu ', 'chó', 'Chó', 'Cặc', ' lồn ', 'con cac', 'con cặc', 'con cu', 'cặc', 'cac', 'ccc', 'cc', 'vcl', 'vú', 'địt', 'dit', 'đụ', 'stupid', 'shit', 'piss', 'fuck', 'cunt', 'cocksucker', 'motherfucker', 'tits', 'sex', 'sexy', 'nude', 'naked', 'porn'];

        Room::whereIn('name', $badWords)->delete();
        Puzzle::whereIn('name', $badWords)->delete();

        foreach ($badWords as $word) {
            Room::where('name', 'LIKE', "{$word}%")->delete();
            Puzzle::where('name', 'LIKE', "{$word}%")->delete();
            User::where('name', 'LIKE', "{$word}%")->delete();
            Room::where('name', 'LIKE', "%{$word}")->delete();
            Puzzle::where('name', 'LIKE', "%{$word}")->delete();
            User::where('name', 'LIKE', "%{$word}")->delete();
            Room::where('name', 'LIKE', "%{$word}%")->delete();
            Puzzle::where('name', 'LIKE', "%{$word}%")->delete();
            User::where('name', 'LIKE', "%{$word}%")->delete();
        }

        $this->info('Rooms, Users and Puzzles with bad words in name have been deleted.');
    }
}
