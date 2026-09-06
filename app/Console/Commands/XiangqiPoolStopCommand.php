<?php

namespace App\Console\Commands;

use App\Services\Xiangqi\WorkerSupervisor;
use Illuminate\Console\Command;

/**
 * `php artisan xiangqi:pool:stop`
 *
 * Gracefully stops all engine workers. Useful before a deploy that
 * replaces the worker code, or for maintenance. Run `xiangqi:pool:ensure`
 * afterwards (or let supervisord's own autostart bring it back on next
 * boot) to resume.
 *
 * IMPORTANT: this writes a pool-wide stop flag (via WorkerSupervisor)
 * BEFORE asking supervisord to stop anything. supervisord's own state
 * has no notion of "this stop was deliberate" — it just sees processes
 * go from RUNNING to STOPPED, the same as if they'd crashed and
 * exhausted startretries. The flag is what stops
 * XiangqiEngineClient's on-demand self-heal (`xiangqi:pool:ensure
 * --respect-stop`) from immediately calling `supervisorctl start` again
 * the moment the next request notices the pool is down — which, without
 * the flag, would make this command appear to do nothing.
 *
 * The flag is only cleared by running `xiangqi:pool:ensure` WITHOUT
 * --respect-stop (i.e. a human explicitly bringing the pool back up).
 */
class XiangqiPoolStopCommand extends Command
{
    protected $signature = 'xiangqi:pool:stop';

    protected $description = 'Stop all running Xiangqi engine workers via supervisorctl';

    public function handle(): int
    {
        $supervisor = new WorkerSupervisor();

        // Write the stop flag FIRST, before touching supervisord, so
        // there's no window where the client-triggered self-heal could
        // race in and immediately undo the stop.
        $supervisor->writeStopFlag();
        $this->info('Pool marked as intentionally stopped.');

        $supervisor->stopAll();
        $this->info('supervisorctl stop issued for xiangqi-worker:* — check `supervisorctl status xiangqi-worker:*` to confirm.');

        return self::SUCCESS;
    }
}
