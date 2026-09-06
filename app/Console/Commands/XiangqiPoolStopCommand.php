<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * `php artisan xiangqi:pool:stop`
 *
 * Gracefully stops all engine workers (SIGTERM, same signal Supervisor
 * would have sent). Useful before a deploy that replaces the worker code,
 * or for maintenance. Run `xiangqi:pool:ensure` afterwards (or wait for
 * the next cron tick) to bring them back up.
 *
 * IMPORTANT: this writes a pool-wide stop flag BEFORE signaling anything,
 * for two reasons that both matter now that workers self-heal:
 *
 *   1. Each worker's respawn loop (launched by XiangqiPoolEnsureCommand)
 *      checks this flag before relaunching a worker that just exited —
 *      without it, SIGTERM'ing a worker here would just cause its own
 *      loop to bring it right back within ~1s, and this command would
 *      appear to do nothing.
 *   2. The scheduler ticks xiangqi:pool:ensure every minute with
 *      --respect-stop specifically so it also backs off while this flag
 *      is present, instead of undoing an intentional stop within 60s.
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
        $socketDir = config('xiangqi.socket_dir', storage_path('app/xiangqi'));

        // Was previously hardcoded to 4 here while Ensure/Client defaulted
        // to 12 — meaning a pool of 12 workers would only ever have its
        // first 4 stopped, leaving 8 running "invisibly" through a deploy.
        // Keep this in lockstep with the other commands' default.
        $workerCount = (int) config('xiangqi.worker_count', 12);

        if (!is_dir($socketDir)) {
            mkdir($socketDir, 0770, true);
        }

        // Write the stop flag FIRST, before touching any individual
        // worker, so there's no window where a loop could still decide to
        // respawn because it checked the flag a moment too early.
        $stopFlagPath = rtrim($socketDir, '/') . '/' . XiangqiPoolEnsureCommand::STOP_FLAG_FILENAME;
        file_put_contents($stopFlagPath, (string) microtime(true));
        $this->info('Pool marked as intentionally stopped.');

        for ($id = 0; $id < $workerCount; $id++) {
            $this->stopWorker($id, $socketDir);
        }

        return self::SUCCESS;
    }

    private function stopWorker(int $id, string $socketDir): void
    {
        $pidPath = rtrim($socketDir, '/') . "/engine-{$id}.pid";
        $startedPath = rtrim($socketDir, '/') . "/engine-{$id}.started";
        $loopPidPath = rtrim($socketDir, '/') . "/engine-{$id}.loop.pid";

        // Kill the respawn loop itself first (if any) so it can't react
        // to the worker's exit at all — belt-and-suspenders alongside the
        // stop flag the loop also checks on its own.
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
