<?php

namespace App\Console\Commands;

use App\Models\Puzzle;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

class UpdatePuzzleSlugsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:puzzleSlugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update puzzles\' slugs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get all puzzles
        $puzzles = Puzzle::all();

        // Loop through the puzzles and update their points
        foreach ($puzzles as $puzzle) {
            $name = $puzzle->name;
            $puzzle->slug = Str::slug($name, '-');
            $puzzle->save();
        }

        $this->info('Slugs of ' . Puzzle::count() . ' puzzles updated successfully!');
    }
}
