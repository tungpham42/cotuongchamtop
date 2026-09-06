<?php

namespace App\Console\Commands;

use App\Services\Xiangqi\WorkerSupervisor;
use Illuminate\Console\Command;

/**
 * `php artisan xiangqi:pool:ensure`
 *
 * On CentOS 9, supervisord is the thing actually keeping workers alive
 * (autostart on boot, autorestart on crash — see
 * deploy/supervisord/xiangqi-workers.ini). This command is no longer a
 * scheduled cron backstop (that entry has been removed from
 * app/Console/Kernel.php entirely, since supervisord's own
 * autostart/autorestart already covers "nothing was ever started" and
 * "a worker died"). What's left for this command:
 *
 *   1. A manual convenience wrapper an operator can run after a deploy:
 *      `php artisan xiangqi:pool:ensure` — clears any stop flag left
 *      over from `xiangqi:pool:stop` and asks supervisord to start
 *      anything not already running.
 *   2. The on-demand call XiangqiEngineClient makes (with
 *      --respect-stop) the instant a web request finds the whole pool
 *      unreachable, so recovery starts immediately rather than waiting
 *      on supervisord's own retry backoff for every worker at once.
 *
 * INTENTIONAL STOP: `xiangqi:pool:stop` writes a pool-wide stop-flag file
 * before asking supervisord to stop anything (see WorkerSupervisor's
 * docblock for why supervisord itself can't track "this was on
 * purpose"). Passing --respect-stop here makes this command honor that
 * flag (no-op while it's present); running the command WITHOUT
 * --respect-stop — i.e. a human typing `php artisan xiangqi:pool:ensure`
 * — always clears the flag and brings the pool back up, matching what an
 * operator expects that command to do.
 */
class XiangqiPoolEnsureCommand extends Command
{
    protected $signature = 'xiangqi:pool:ensure
        {--respect-stop : No-op if the pool was intentionally stopped via xiangqi:pool:stop. Used by the client-triggered self-heal; a plain manual run never passes this and always clears the stop flag.}';

    protected $description = 'Ask supervisord to start any Xiangqi engine workers that are not currently running';

    public function handle(): int
    {
        $supervisor = new WorkerSupervisor();

        if ($this->option('respect-stop') && $supervisor->isStopped()) {
            $this->info('Pool was intentionally stopped — skipping (run `php artisan xiangqi:pool:ensure` without --respect-stop to bring it back up).');
            return self::SUCCESS;
        }

        // A plain/manual run always means "I want the pool up."
        $supervisor->clearStopFlag();

        for ($id = 0; $id < $supervisor->workerCount(); $id++) {
            $started = $supervisor->ensureWorker($id, respectStop: false);
            $this->line($started
                ? "[worker {$id}] was down — supervisorctl start issued"
                : "[worker {$id}] already running, skipping");
        }

        return self::SUCCESS;
    }
}
