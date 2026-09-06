<?php

namespace App\Console\Commands;

use App\Services\XiangqiEngineClient;
use Illuminate\Console\Command;

/**
 * `php artisan xiangqi:pool:ensure`
 *
 * Replaces what Supervisor was doing: makes sure `worker_count` engine
 * workers are alive, and (re)spawns any that aren't. This is now a
 * *backstop*, not the primary recovery mechanism — each worker it
 * launches is wrapped in its own tiny detached respawn loop (see
 * launchWorker() below), so a worker that crashes restarts itself within
 * about a second on its own, without waiting for this command to run
 * again. That's what actually keeps the pool from ever going fully dark:
 * with N workers each self-healing independently, a single crash never
 * takes the whole pool down, and cron/this command only matters for the
 * case where a worker (or its loop) never started in the first place —
 * e.g. right after a fresh deploy or server reboot.
 *
 * Meant to run from three places, all of which converge on the same
 * idempotent logic below:
 *
 *   1. Once manually after deploy, to bring the pool up the first time.
 *   2. Every minute via Laravel's scheduler (app/Console/Kernel.php),
 *      passing --respect-stop, as a safety net in case a respawn loop
 *      itself died (e.g. OOM-killed along with its child).
 *   3. On-demand, triggered by XiangqiEngineClient itself (also with
 *      --respect-stop) the moment a web request finds zero workers
 *      responding — so recovery starts immediately on the first failed
 *      request instead of waiting for the next cron tick.
 *
 * Uses a flock so overlapping runs (cron, self-heal, manual) can't spawn
 * duplicate workers for the same id.
 *
 * INTENTIONAL STOP: `xiangqi:pool:stop` writes a pool-wide stop-flag file
 * before it signals workers, and each respawn loop checks that flag
 * before relaunching its worker — so a deliberate stop (e.g. before a
 * deploy) actually stays stopped instead of being fought by the next
 * cron tick or self-heal trigger. Passing --respect-stop here makes this
 * command honor that flag (no-op while it's present); running the
 * command WITHOUT --respect-stop — i.e. a human typing
 * `php artisan xiangqi:pool:ensure` — always clears the flag and brings
 * the pool back up, matching what an operator expects that command to
 * do.
 *
 * BOOT-GRACE WINDOW: a freshly launched worker writes its pid file
 * immediately (see XiangqiEngineWorkerCommand::handle()) but only binds
 * its socket once PikafishProcess::start() finishes, which can
 * legitimately take up to ~25s (10s uciok + 15s readyok) even without
 * contention, and longer under a cold-start where all N workers are
 * loading the same .nnue file at once. Without a grace period, a check
 * landing in that window sees "pid running, socket not answering" and
 * concludes the worker is dead — then kills its own pid file/socket and
 * launches a SECOND process for the same id, which fights the first for
 * the socket path and can leave the pid file pointing at whichever one
 * lost. This class tracks a launch timestamp per worker and refuses to
 * declare a worker dead on ping failure alone until that grace window
 * has elapsed.
 */
class XiangqiPoolEnsureCommand extends Command
{
    protected $signature = 'xiangqi:pool:ensure
        {--respect-stop : No-op if the pool was intentionally stopped via xiangqi:pool:stop. Used by cron and by the self-heal trigger; a plain manual run never passes this and always clears the stop flag.}';

    protected $description = 'Start any Xiangqi engine workers that are not currently running';

    /**
     * Shared with XiangqiPoolStopCommand — the presence of this file means
     * "an operator deliberately stopped the pool, don't auto-restart it."
     * Keep this filename in sync between the two commands.
     */
    public const STOP_FLAG_FILENAME = 'pool.stopped';

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

        $stopFlagPath = rtrim($socketDir, '/') . '/' . self::STOP_FLAG_FILENAME;
        if ($this->option('respect-stop') && file_exists($stopFlagPath)) {
            $this->info('Pool was intentionally stopped — skipping (run `php artisan xiangqi:pool:ensure` without --respect-stop to bring it back up).');
            return self::SUCCESS;
        }
        // A plain/manual run always means "I want the pool up," so it
        // clears any stale intentional-stop flag before proceeding.
        @unlink($stopFlagPath);

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
        // Note: we deliberately do NOT touch the pool-wide stop flag here —
        // handle() already resolved that before we got this far.
        @unlink($pidPath);
        @unlink($startedPath);
        @unlink(rtrim($socketDir, '/') . "/engine-{$id}.sock");
        @unlink($this->loopPidPath($socketDir, $id));

        $logPath = rtrim($socketDir, '/') . "/engine-{$id}.log";

        // Record the launch time BEFORE shelling out, so there is no
        // window where a worker is running-but-unpingable with no
        // corresponding grace-period record.
        file_put_contents($startedPath, microtime(true));

        $loopPid = $this->launchWorker($id, $socketDir, $logPath);

        if (ctype_digit((string) $loopPid) && (int) $loopPid > 0) {
            file_put_contents($this->loopPidPath($socketDir, $id), $loopPid);
            $this->info("[worker {$id}] launched self-respawning loop (pid {$loopPid}) — check {$logPath} for boot status");
        } else {
            $this->error("[worker {$id}] failed to launch — check that exec/shell_exec is allowed for CLI PHP");
            @unlink($startedPath);
        }

        return true;
    }

    /**
     * Launches worker $id inside its own tiny detached shell loop instead
     * of as a one-shot process. If the worker exits for any reason —
     * crash, OOM kill, an unhandled exception escaping handle() — the
     * loop relaunches it after a short pause, all without needing
     * xiangqi:pool:ensure to notice and intervene. That's what turns a
     * worker crash into a ~1-2s blip instead of an up-to-60s outage for
     * that worker (previously bounded only by the cron interval).
     *
     * The loop checks the pool-wide stop flag before each relaunch, so an
     * intentional `xiangqi:pool:stop` is honored instead of being
     * immediately undone by the very thing that's supposed to keep
     * workers alive.
     *
     * setsid detaches the LOOP itself (not just the php process inside
     * it) from this command's session, so the loop survives long after
     * `php artisan xiangqi:pool:ensure` — and, if run from cron, cron's
     * own short-lived shell — has exited. Falls back to nohup, which
     * only ignores SIGHUP, where setsid isn't available.
     *
     * @return int|null the loop's own pid, or null if launching failed
     */
    private function launchWorker(int $id, string $socketDir, string $logPath): ?int
    {
        $artisan = escapeshellarg(base_path('artisan'));
        $php = escapeshellarg(PHP_BINARY);
        $log = escapeshellarg($logPath);
        $stopFlag = escapeshellarg(rtrim($socketDir, '/') . '/' . self::STOP_FLAG_FILENAME);

        $loopScript =
            'while true; do ' .
                "echo \"\$(date -Iseconds) [worker {$id}] (re)starting\" >> {$log}; " .
                "{$php} {$artisan} xiangqi:engine-worker {$id} >> {$log} 2>&1 < /dev/null; " .
                "code=\$?; " .
                "if [ -f {$stopFlag} ]; then " .
                    "echo \"\$(date -Iseconds) [worker {$id}] exited (code \$code), stop flag present — not respawning\" >> {$log}; " .
                    'break; ' .
                'fi; ' .
                "echo \"\$(date -Iseconds) [worker {$id}] exited (code \$code) — respawning in 1s\" >> {$log}; " .
                'sleep 1; ' .
            'done';

        $launcher = $this->hasSetsid() ? 'setsid' : 'nohup';
        $cmd = "{$launcher} bash -c " . escapeshellarg($loopScript) . " >> {$log} 2>&1 < /dev/null & echo \$!";

        $pid = trim((string) shell_exec($cmd));

        return ctype_digit($pid) ? (int) $pid : null;
    }

    private function loopPidPath(string $socketDir, int $id): string
    {
        return rtrim($socketDir, '/') . "/engine-{$id}.loop.pid";
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
