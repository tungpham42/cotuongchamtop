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

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * Note: XiangqiEngineWorkerCommand, XiangqiPoolEnsureCommand, and
     * XiangqiPoolStopCommand aren't listed here — commands() below
     * already auto-loads every class in app/Console/Commands via
     * $this->load(), and none of the three is referenced by class name
     * in schedule() anymore (see below), so none of them needs a manual
     * entry. They still work fine as `php artisan xiangqi:engine-worker`,
     * `php artisan xiangqi:pool:ensure`, and `php artisan xiangqi:pool:stop`.
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

        // NOTE: the old `$schedule->command(XiangqiPoolEnsureCommand::class,
        // ['--respect-stop'])->everyMinute()...` cron backstop has been
        // removed on purpose. It existed to cover "nothing was ever
        // started" (fresh deploy / server reboot) — that's now supervisord's
        // job: `systemctl enable --now supervisord` starts supervisord at
        // boot, and every xiangqi-worker process has autostart=true (see
        // deploy/supervisord/xiangqi-workers.ini), so the pool comes up on
        // its own without waiting on Laravel's scheduler at all. Crash
        // recovery is autorestart=true on the same processes — see
        // WorkerSupervisor's docblock for the full before/after picture.
        //
        // What's left of the app-level pool tooling
        // (XiangqiPoolEnsureCommand / XiangqiPoolStopCommand) is now purely
        // manual/on-demand: an operator running them directly, or
        // XiangqiEngineClient's request-triggered self-heal calling
        // `xiangqi:pool:ensure --respect-stop` the instant a request finds
        // the whole pool unreachable, instead of waiting on supervisord's
        // own per-process retry backoff. See deploy/README-supervisor.md.
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
