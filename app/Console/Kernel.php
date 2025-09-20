<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\DeleteRoomsCommand;
use App\Console\Commands\DeleteNonameRoomsCommand;
use App\Console\Commands\DeleteBadWordsCommand;
use App\Console\Commands\UpdatePointsCommand;
use App\Console\Commands\UpdatePuzzleSlugsCommand;
// use App\Console\Commands\UpdateSitemapCommand;
use App\Console\Commands\UpdateRoomsCommand;
use App\Console\Commands\CreateNewRoom;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\DeleteRoomsCommand::class,
        \App\Console\Commands\DeleteNonameRoomsCommand::class,
        \App\Console\Commands\DeleteBadWordsCommand::class,
        \App\Console\Commands\UpdatePointsCommand::class,
        \App\Console\Commands\UpdatePuzzleSlugsCommand::class,
        // \App\Console\Commands\UpdateSitemapCommand::class,
        \App\Console\Commands\UpdateRoomsCommand::class,
        \App\Console\Commands\CreateNewRoom::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(DeleteBadWordsCommand::class)->everyFiveMinutes();
        $schedule->command(DeleteRoomsCommand::class)->hourly();
        $schedule->command(DeleteNonameRoomsCommand::class)->hourly();
        $schedule->command(UpdatePointsCommand::class)->hourly();
        $schedule->command(UpdatePuzzleSlugsCommand::class)->hourly();
        $schedule->command(UpdateRoomsCommand::class)->hourly();
        $schedule->command(CreateNewRoom::class)->everySixHours($minutes = 0);
        // $schedule->command(UpdateSitemapCommand::class)->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
