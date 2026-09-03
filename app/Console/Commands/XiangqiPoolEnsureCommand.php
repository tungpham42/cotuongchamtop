<?php

namespace App\Console\Commands;

use App\Services\XiangqiEngineClient;
use Illuminate\Console\Command;

/**
 * `php artisan xiangqi:pool:ensure`
 *
 * Replaces what Supervisor was doing: makes sure `worker_count` engine
 * workers are alive, and (re)spawns any that aren't — fully detached, so
 * they keep running after this command exits. Meant to be run:
 *
 *   1. Once manually after deploy, to bring the pool up.
 *   2. Every minute via Laravel's scheduler — in Laravel 9 that means
 *      registering it in app/Console/Kernel.php's schedule() method (see
 *      the Kernel.php in this bundle), so a crashed worker is back within
 *      ~60s without any manual intervention. That only needs the one
 *      standard Laravel cron entry (`php artisan schedule:run` every
 *      minute) most Laravel 9 apps already have — no bespoke crontab
 *      line required.
 *
 * Uses a flock so overlapping cron runs (or a slow run) can't spawn
 * duplicate workers for the same id.
 */
class XiangqiPoolEnsureCommand extends Command
{
    protected $signature = 'xiangqi:pool:ensure';

    protected $description = 'Start any Xiangqi engine workers that are not currently running';

    public function handle(): int
    {
        $socketDir = config('xiangqi.socket_dir', storage_path('app/xiangqi'));
        $workerCount = (int) config('xiangqi.worker_count', 4);

        if (!is_dir($socketDir)) {
            mkdir($socketDir, 0770, true);
        }

        $lockPath = rtrim($socketDir, '/') . '/pool-ensure.lock';
        $lockHandle = fopen($lockPath, 'c');
        if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
            $this->info('Another xiangqi:pool:ensure is already running — skipping.');
            return self::SUCCESS;
        }

        try {
            for ($id = 0; $id < $workerCount; $id++) {
                $this->ensureWorker($id, $socketDir);
            }
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }

        return self::SUCCESS;
    }

    private function ensureWorker(int $id, string $socketDir): void
    {
        $pidPath = rtrim($socketDir, '/') . "/engine-{$id}.pid";

        if ($this->isWorkerAlive($id, $pidPath)) {
            $this->line("[worker {$id}] alive, skipping");
            return;
        }

        $this->warn("[worker {$id}] not running — starting it");

        // Stale files from a crashed process.
        @unlink($pidPath);
        @unlink(rtrim($socketDir, '/') . "/engine-{$id}.sock");

        $logPath = rtrim($socketDir, '/') . "/engine-{$id}.log";
        $artisan = escapeshellarg(base_path('artisan'));
        $php = escapeshellarg(PHP_BINARY);
        $log = escapeshellarg($logPath);

        // setsid fully detaches the process from this command's session so
        // it survives long after `php artisan xiangqi:pool:ensure` (and,
        // if run from cron, cron's own short-lived shell) has exited.
        // nohup additionally ignores SIGHUP as a fallback where setsid
        // isn't available.
        $launcher = $this->hasSetsid() ? 'setsid' : 'nohup';

        $cmd = "{$launcher} {$php} {$artisan} xiangqi:engine-worker {$id} >> {$log} 2>&1 < /dev/null & echo \$!";
        $pid = trim((string) shell_exec($cmd));

        if (ctype_digit($pid)) {
            $this->info("[worker {$id}] launched with pid {$pid} (check {$logPath} for boot status)");
        } else {
            $this->error("[worker {$id}] failed to launch — check that exec/shell_exec is allowed for CLI PHP");
        }
    }

    /**
     * A worker only counts as "alive" if its PID file points at a running
     * process that is actually our worker (guards against a stale PID
     * having been reused by an unrelated process) AND it responds to a
     * ping on its socket.
     */
    private function isWorkerAlive(int $id, string $pidPath): bool
    {
        if (!file_exists($pidPath)) {
            return false;
        }

        $pid = (int) trim((string) file_get_contents($pidPath));
        if ($pid <= 0 || !$this->isPidRunning($pid, $id)) {
            return false;
        }

        // Process exists, but confirm it's actually accepting connections
        // and not, say, stuck mid-boot loading the network file forever.
        $client = new XiangqiEngineClient();
        return $client->pingWorker($id);
    }

    private function isPidRunning(int $pid, int $expectedId): bool
    {
        if (function_exists('posix_kill') && !posix_kill($pid, 0)) {
            return false;
        } elseif (!function_exists('posix_kill') && !is_dir("/proc/{$pid}")) {
            return false;
        }

        // Best-effort sanity check on Linux: confirm the running process
        // actually is this worker, not an unrelated process that reused
        // the PID after a crash.
        $cmdlinePath = "/proc/{$pid}/cmdline";
        if (is_readable($cmdlinePath)) {
            $cmdline = str_replace("\0", ' ', (string) file_get_contents($cmdlinePath));
            if (strpos($cmdline, 'xiangqi:engine-worker') === false) {
                return false;
            }
            if (strpos($cmdline, (string) $expectedId) === false) {
                return false;
            }
        }

        return true;
    }

    private function hasSetsid(): bool
    {
        static $has = null;
        if ($has === null) {
            $has = trim((string) shell_exec('command -v setsid')) !== '';
        }
        return $has;
    }
}
