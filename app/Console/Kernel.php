<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\DeleteRoomsCommand;
use App\Console\Commands\DeleteNonameRoomsCommand;
use App\Console\Commands\CensorBadWordsCommand;
use App\Console\Commands\UpdatePointsCommand;
use App\Console\Commands\UpdatePuzzleSlugsCommand;
use App\Console\Commands\UpdateRoomsCommand;
use App\Console\Commands\CreateNewRoom;
use App\Console\Commands\XiangqiPoolEnsureCommand;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * Note: XiangqiEngineWorkerCommand and XiangqiPoolStopCommand aren't
     * listed here on purpose — commands() below already auto-loads every
     * class in app/Console/Commands via $this->load(), so anything not
     * needed directly by the scheduler doesn't need a manual entry. They
     * still work fine as `php artisan xiangqi:engine-worker` and
     * `php artisan xiangqi:pool:stop`. XiangqiPoolEnsureCommand IS listed
     * here only because it's referenced by class name in schedule() below,
     * same as your other scheduled commands.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\DeleteRoomsCommand::class,
        \App\Console\Commands\DeleteNonameRoomsCommand::class,
        \App\Console\Commands\CensorBadWordsCommand::class,
        \App\Console\Commands\UpdatePointsCommand::class,
        \App\Console\Commands\UpdatePuzzleSlugsCommand::class,
        \App\Console\Commands\UpdateRoomsCommand::class,
        // \App\Console\Commands\UpdateSitemapCommand::class,
        \App\Console\Commands\CreateNewRoom::class,
        \App\Console\Commands\XiangqiPoolEnsureCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(CensorBadWordsCommand::class)->everyFiveMinutes();
        $schedule->command(UpdateRoomsCommand::class)->everyMinute();
        $schedule->command(DeleteRoomsCommand::class)->hourly();
        $schedule->command(DeleteNonameRoomsCommand::class)->hourly();
        $schedule->command(UpdatePointsCommand::class)->hourly();
        $schedule->command(UpdatePuzzleSlugsCommand::class)->hourly();
        $schedule->command(CreateNewRoom::class)->everySixHours($minutes = 0);
        // $schedule->command(UpdateSitemapCommand::class)->daily();

        // Keeps the warm Pikafish worker pool alive without Supervisor:
        // checks each worker's pid + socket every minute and respawns any
        // that died. See deploy/README.md in the xiangqi refactor bundle.
        $schedule->command(XiangqiPoolEnsureCommand::class)
            ->everyMinute()
            ->withoutOverlapping(1) // 1 minute is enough, since the command itself uses a flock
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/xiangqi-pool-ensure.log'));
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
