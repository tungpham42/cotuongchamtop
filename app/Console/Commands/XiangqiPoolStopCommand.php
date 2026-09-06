<?php

namespace App\Console\Commands;

use App\Services\Xiangqi\WorkerSupervisor;
use Illuminate\Console\Command;

/**
 * `php artisan xiangqi:pool:stop`
 *
 * Gracefully stops all engine workers (SIGTERM, same signal Supervisor
 * would have sent). Useful before a deploy that replaces the worker code,
 * or for maintenance. Run `xiangqi:pool:ensure` afterwards (or wait for
 * the next cron tick) to bring them back up.
 *
 * IMPORTANT: this writes a pool-wide stop flag (via WorkerSupervisor)
 * BEFORE signaling anything, for two reasons that both matter now that
 * workers self-heal at two independent layers:
 *
 *   1. Each worker's own respawn loop checks this flag before relaunching
 *      a worker that just exited.
 *   2. Each worker's ring-watchdog (see XiangqiEngineWorkerCommand) also
 *      checks it before spawning a downed neighbor.
 *
 * Without the flag, SIGTERM'ing a worker here would just cause its own
 * loop — or its neighbor's watchdog — to bring it right back within a
 * second or two, and this command would appear to do nothing.
 *
 * The flag is only cleared by running `xiangqi:pool:ensure` WITHOUT
 * --respect-stop (i.e. a human explicitly bringing the pool back up).
 */
class XiangqiPoolStopCommand extends Command
{
    protected $signature = 'xiangqi:pool:stop';

    protected $description = 'Stop all running Xiangqi engine workers';

    public function handle(): int
    {
        $supervisor = new WorkerSupervisor();

        // Write the stop flag FIRST, before touching any individual
        // worker, so there's no window where a loop or a watchdog could
        // still decide to respawn because it checked the flag a moment
        // too early.
        $supervisor->writeStopFlag();
        $this->info('Pool marked as intentionally stopped.');

        for ($id = 0; $id < $supervisor->workerCount(); $id++) {
            $this->stopWorker($supervisor, $id);
        }

        return self::SUCCESS;
    }

    private function stopWorker(WorkerSupervisor $supervisor, int $id): void
    {
        $pidPath = $supervisor->pidPath($id);
        $startedPath = $supervisor->startedPath($id);
        $loopPidPath = $supervisor->loopPidPath($id);

        // Kill the respawn loop itself first (if any) so it can't react
        // to the worker's exit at all — belt-and-suspenders alongside the
        // stop flag the loop (and any watching neighbor) also checks.
        if (file_exists($loopPidPath)) {
            $loopPid = (int) trim((string) file_get_contents($loopPidPath));
            if ($loopPid > 0 && $this->isPidAlive($loopPid)) {
                $this->killPid($loopPid, SIGTERM);
                $this->info("[worker {$id}] sent SIGTERM to respawn loop (pid {$loopPid})");
            }
            @unlink($loopPidPath);
        }

        if (!file_exists($pidPath)) {
            $this->line("[worker {$id}] no pid file, nothing to stop");
            @unlink($startedPath);
            return;
        }

        $pid = (int) trim((string) file_get_contents($pidPath));
        if ($pid > 0 && $this->isPidAlive($pid)) {
            $this->killPid($pid, SIGTERM);
            $this->info("[worker {$id}] sent SIGTERM to pid {$pid}");
        } else {
            $this->line("[worker {$id}] pid {$pid} not running");
        }

        @unlink($pidPath);
        @unlink($startedPath);
    }

    private function isPidAlive(int $pid): bool
    {
        return function_exists('posix_kill') ? posix_kill($pid, 0) : is_dir("/proc/{$pid}");
    }

    private function killPid(int $pid, int $signal): void
    {
        if (function_exists('posix_kill')) {
            posix_kill($pid, $signal);
        } else {
            shell_exec('kill ' . escapeshellarg((string) $pid));
        }
    }
}
