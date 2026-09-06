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
 */
class XiangqiPoolStopCommand extends Command
{
    protected $signature = 'xiangqi:pool:stop';

    protected $description = 'Stop all running Xiangqi engine workers';

    public function handle(): int
    {
        $socketDir = config('xiangqi.socket_dir', storage_path('app/xiangqi'));
        $workerCount = (int) config('xiangqi.worker_count', 4);

        for ($id = 0; $id < $workerCount; $id++) {
            $pidPath = rtrim($socketDir, '/') . "/engine-{$id}.pid";
            $startedPath = rtrim($socketDir, '/') . "/engine-{$id}.started";

            if (!file_exists($pidPath)) {
                $this->line("[worker {$id}] no pid file, nothing to stop");
                @unlink($startedPath);
                continue;
            }

            $pid = (int) trim((string) file_get_contents($pidPath));
            if ($pid > 0 && (function_exists('posix_kill') ? posix_kill($pid, 0) : is_dir("/proc/{$pid}"))) {
                if (function_exists('posix_kill')) {
                    posix_kill($pid, SIGTERM);
                } else {
                    shell_exec('kill ' . escapeshellarg((string) $pid));
                }
                $this->info("[worker {$id}] sent SIGTERM to pid {$pid}");
            } else {
                $this->line("[worker {$id}] pid {$pid} not running");
            }

            @unlink($pidPath);
            @unlink($startedPath);
        }

        return self::SUCCESS;
    }
}
