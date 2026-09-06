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
 *
 * BOOT-GRACE WINDOW: a freshly launched worker writes its pid file
 * immediately (see XiangqiEngineWorkerCommand::handle()) but only binds
 * its socket once PikafishProcess::start() finishes, which can
 * legitimately take up to ~25s (10s uciok + 15s readyok) even without
 * contention, and longer under a cold-start where all N workers are
 * loading the same .nnue file at once. Without a grace period, a cron
 * tick landing in that window sees "pid running, socket not answering"
 * and concludes the worker is dead — then kills its own pid file/socket
 * and launches a SECOND process for the same id, which fights the first
 * for the socket path and can leave the pid file pointing at whichever
 * one lost. This class tracks a launch timestamp per worker and refuses
 * to declare a worker dead on ping failure alone until that grace window
 * has elapsed.
 */
class XiangqiPoolEnsureCommand extends Command
{
    protected $signature = 'xiangqi:pool:ensure';

    protected $description = 'Start any Xiangqi engine workers that are not currently running';

    /**
     * Generous ceiling for a single worker's full boot: proc_open +
     * uciok (up to 10s) + readyok (up to 15s) + a little slack for
     * scheduling delay under a cold-start pool. Keep this at or above
     * PikafishProcess's own uciok/readyok ceilings (10s + 15s) or you'll
     * reintroduce the exact race this is meant to prevent.
     */
    private const BOOT_GRACE_SECONDS = 30.0;

    /**
     * Delay between launching consecutive cold-start workers. Loading the
     * same .nnue file 12x simultaneously is the single biggest cause of
     * any one worker blowing past BOOT_GRACE_SECONDS in the first place —
     * staggering launches keeps disk/CPU/RAM contention down so each
     * worker's own boot stays close to its unloaded-machine time.
     */
    private const LAUNCH_STAGGER_SECONDS = 2.0;

    public function handle(): int
    {
        $socketDir = config('xiangqi.socket_dir', storage_path('app/xiangqi'));
        $workerCount = (int) config('xiangqi.worker_count', 12);

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
            $launchedAny = false;
            for ($id = 0; $id < $workerCount; $id++) {
                // Stagger only actual launches, not the (common, cheap)
                // case where a worker is already alive and we just skip it.
                if ($launchedAny) {
                    usleep((int) (self::LAUNCH_STAGGER_SECONDS * 1_000_000));
                }
                $launchedAny = $this->ensureWorker($id, $socketDir) || $launchedAny;
            }
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }

        return self::SUCCESS;
    }

    /**
     * @return bool true if this call actually launched a new process for $id
     */
    private function ensureWorker(int $id, string $socketDir): bool
    {
        $pidPath = rtrim($socketDir, '/') . "/engine-{$id}.pid";
        $startedPath = $this->startedPath($socketDir, $id);

        if ($this->isWorkerAlive($id, $pidPath, $startedPath)) {
            $this->line("[worker {$id}] alive, skipping");
            return false;
        }

        $this->warn("[worker {$id}] not running — starting it");

        // Stale files from a crashed process (or a loser of a prior race).
        @unlink($pidPath);
        @unlink($startedPath);
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

        // Record the launch time BEFORE shelling out, so there is no
        // window where a worker is running-but-unpingable with no
        // corresponding grace-period record.
        file_put_contents($startedPath, microtime(true));

        $cmd = "{$launcher} {$php} {$artisan} xiangqi:engine-worker {$id} >> {$log} 2>&1 < /dev/null & echo \$!";
        $pid = trim((string) shell_exec($cmd));

        if (ctype_digit($pid)) {
            $this->info("[worker {$id}] launched with pid {$pid} (check {$logPath} for boot status)");
        } else {
            $this->error("[worker {$id}] failed to launch — check that exec/shell_exec is allowed for CLI PHP");
            @unlink($startedPath);
        }

        return true;
    }

    /**
     * A worker counts as "alive" if:
     *   - its PID file points at a running process that is actually our
     *     worker (guards against a stale PID having been reused by an
     *     unrelated process), AND EITHER
     *   - it responds to a ping on its socket, OR
     *   - it's still within its boot-grace window, in which case a failed
     *     ping just means "still loading," not "dead."
     */
    private function isWorkerAlive(int $id, string $pidPath, string $startedPath): bool
    {
        if (!file_exists($pidPath)) {
            return false;
        }

        $pid = (int) trim((string) file_get_contents($pidPath));
        if ($pid <= 0 || !$this->isPidRunning($pid, $id)) {
            return false;
        }

        $client = new XiangqiEngineClient();
        if ($client->pingWorker($id)) {
            // Fully up — grace record no longer needed.
            @unlink($startedPath);
            return true;
        }

        if ($this->withinBootGrace($startedPath)) {
            $this->line("[worker {$id}] pid running, not answering yet — within boot grace, treating as alive");
            return true;
        }

        return false;
    }

    private function withinBootGrace(string $startedPath): bool
    {
        if (!file_exists($startedPath)) {
            // No record of when this one launched (e.g. it predates this
            // fix, or the file was cleaned up some other way). Fail safe
            // toward "no grace" rather than granting an indefinite pass.
            return false;
        }

        $startedAt = (float) trim((string) file_get_contents($startedPath));
        if ($startedAt <= 0) {
            return false;
        }

        return (microtime(true) - $startedAt) < self::BOOT_GRACE_SECONDS;
    }

    private function startedPath(string $socketDir, int $id): string
    {
        return rtrim($socketDir, '/') . "/engine-{$id}.started";
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
