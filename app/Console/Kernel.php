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

        // Backstop for the warm Pikafish worker pool — the last of three
        // self-heal layers. Each worker restarts itself within ~1-2s via
        // its own respawn loop if it crashes, and every running worker
        // also watches one neighbor (a ring across the whole pool) and
        // spawns it directly if that neighbor is down — see
        // WorkerSupervisor and XiangqiEngineWorkerCommand. XiangqiEngineClient
        // additionally triggers this same command on-demand the instant a
        // web request finds the whole pool down. This scheduled tick just
        // covers what none of those can reach: nothing was ever started
        // at all (e.g. right after a server reboot, before any worker
        // exists to watch anything). --respect-stop means it never fights
        // a deliberate `xiangqi:pool:stop` mid-deploy.
        // See deploy/README.md in the xiangqi refactor bundle.
        $schedule->command(XiangqiPoolEnsureCommand::class, ['--respect-stop'])
            ->everyMinute()
            ->withoutOverlapping(1)
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
