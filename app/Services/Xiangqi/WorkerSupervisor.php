<?php

namespace App\Services\Xiangqi;

use App\Services\XiangqiEngineClient;
use Illuminate\Support\Facades\Log;

/**
 * Single source of truth for "is worker $id alive, and if not, start it."
 *
 * This used to live entirely inside XiangqiPoolEnsureCommand. It's been
 * pulled out here so the SAME logic can be triggered from two independent
 * places, giving the pool two layers of self-healing that don't depend on
 * each other:
 *
 *   1. XiangqiPoolEnsureCommand — a full sweep over every worker id, run
 *      manually, via cron, and via XiangqiEngineClient's request-triggered
 *      self-heal.
 *   2. Each running worker's own peer-watchdog (see
 *      XiangqiEngineWorkerCommand) — every worker, while idle between
 *      connections, periodically checks on ONE neighbor in a ring
 *      (worker N watches worker N+1) and spawns it directly if it's down.
 *      This means the fleet keeps healing itself even if pool:ensure,
 *      cron, and the web-triggered self-heal were ALL somehow disabled —
 *      as long as at least one worker is alive, the ring eventually
 *      notices and repairs any gap.
 *
 * Every entry point funnels through ensureWorker(), which takes a
 * per-worker-id lock before touching anything — so a cron sweep, a peer
 * watchdog check, and a manual run can all reach the same id at the same
 * moment without racing to spawn duplicate processes.
 */
class WorkerSupervisor
{
    /**
     * Presence of this file means "an operator deliberately stopped the
     * pool via xiangqi:pool:stop — don't auto-restart it." Cleared only by
     * a manual, non---respect-stop run of xiangqi:pool:ensure.
     */
    public const STOP_FLAG_FILENAME = 'pool.stopped';

    /**
     * Generous ceiling for a single worker's full boot: proc_open +
     * uciok (up to 10s) + readyok (up to 15s) + a little slack for
     * scheduling delay under a cold-start pool. Keep this at or above
     * PikafishProcess's own uciok/readyok ceilings (10s + 15s) or you'll
     * reintroduce the exact "pid running, socket not answering yet" race
     * this is meant to prevent.
     */
    private const BOOT_GRACE_SECONDS = 30.0;

    private string $socketDir;
    private int $workerCount;

    public function __construct(?string $socketDir = null, ?int $workerCount = null)
    {
        $this->socketDir = rtrim($socketDir ?? config('xiangqi.socket_dir', storage_path('app/xiangqi')), '/');
        $this->workerCount = $workerCount ?? (int) config('xiangqi.worker_count', 12);

        if (!is_dir($this->socketDir)) {
            @mkdir($this->socketDir, 0770, true);
        }
    }

    public function workerCount(): int
    {
        return $this->workerCount;
    }

    public function isStopped(): bool
    {
        return file_exists($this->stopFlagPath());
    }

    /** Called by xiangqi:pool:stop, before it signals any individual worker. */
    public function writeStopFlag(): void
    {
        file_put_contents($this->stopFlagPath(), (string) microtime(true));
    }

    /** Called by a manual (non---respect-stop) xiangqi:pool:ensure run. */
    public function clearStopFlag(): void
    {
        @unlink($this->stopFlagPath());
    }

    /**
     * Make sure worker $id is alive, launching it (wrapped in its own
     * self-respawn loop) if it isn't. Safe to call concurrently for the
     * same id from multiple processes — a per-id lock means only one
     * caller ever wins the race to actually spawn.
     *
     * @param bool $respectStop if true, this is a no-op while the pool is
     *     intentionally stopped. Automatic callers (cron, client self-heal,
     *     peer watchdogs) should always pass true (the default). A manual
     *     `pool:ensure` run resolves the stop flag itself before calling
     *     this, so it passes false.
     * @return bool true if this call actually launched a new process
     */
    public function ensureWorker(int $id, bool $respectStop = true): bool
    {
        if ($respectStop && $this->isStopped()) {
            return false;
        }

        $lockPath = "{$this->socketDir}/engine-{$id}.spawn.lock";
        $lockHandle = @fopen($lockPath, 'c');
        if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
            // Someone else (cron sweep, another worker's watchdog, a
            // manual run) is already deciding about this exact id.
            return false;
        }

        try {
            return $this->ensureWorkerLocked($id);
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    private function ensureWorkerLocked(int $id): bool
    {
        $pidPath = $this->pidPath($id);
        $startedPath = $this->startedPath($id);

        if ($this->isWorkerAlive($id, $pidPath, $startedPath)) {
            return false;
        }

        Log::warning("[xiangqi supervisor] worker {$id} not running — starting it");

        // Stale files from a crashed process (or a loser of a prior race).
        @unlink($pidPath);
        @unlink($startedPath);
        @unlink($this->socketPath($id));
        @unlink($this->loopPidPath($id));

        $logPath = $this->logPath($id);

        // Record the launch time BEFORE shelling out, so there is no
        // window where a worker is running-but-unpingable with no
        // corresponding grace-period record.
        file_put_contents($startedPath, microtime(true));

        $loopPid = $this->launchWorker($id, $logPath);

        if ($loopPid !== null) {
            file_put_contents($this->loopPidPath($id), $loopPid);
            Log::info("[xiangqi supervisor] worker {$id} launched self-respawning loop (pid {$loopPid})");
        } else {
            Log::error("[xiangqi supervisor] worker {$id} failed to launch — check that exec/shell_exec is allowed for CLI PHP");
            @unlink($startedPath);
        }

        return true;
    }

    /**
     * Launches worker $id inside its own tiny detached shell loop instead
     * of as a one-shot process. If the worker exits for any reason —
     * crash, OOM kill, an unhandled exception escaping handle() — the
     * loop relaunches it after a short pause, all without needing
     * anything external to notice and intervene.
     *
     * The loop checks the pool-wide stop flag before each relaunch, so an
     * intentional `xiangqi:pool:stop` is honored instead of being
     * immediately undone by the very thing that's supposed to keep
     * workers alive.
     *
     * setsid detaches the LOOP itself (not just the php process inside
     * it) from the calling process's session, so it survives long after
     * that process exits. Falls back to nohup (SIGHUP only) where setsid
     * isn't available.
     *
     * @return int|null the loop's own pid, or null if launching failed
     */
    private function launchWorker(int $id, string $logPath): ?int
    {
        $artisan = escapeshellarg(base_path('artisan'));
        $php = escapeshellarg(PHP_BINARY);
        $log = escapeshellarg($logPath);
        $stopFlag = escapeshellarg($this->stopFlagPath());

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

        $client = new XiangqiEngineClient($this->socketDir, $this->workerCount);
        if ($client->pingWorker($id)) {
            // Fully up — grace record no longer needed.
            @unlink($startedPath);
            return true;
        }

        return $this->withinBootGrace($startedPath);
    }

    private function withinBootGrace(string $startedPath): bool
    {
        if (!file_exists($startedPath)) {
            // No record of when this one launched. Fail safe toward "no
            // grace" rather than granting an indefinite pass.
            return false;
        }

        $startedAt = (float) trim((string) file_get_contents($startedPath));
        if ($startedAt <= 0) {
            return false;
        }

        return (microtime(true) - $startedAt) < self::BOOT_GRACE_SECONDS;
    }

    /**
     * Best-effort sanity check on Linux: confirm the running process
     * actually is this worker, not an unrelated process that reused the
     * PID after a crash. Reads raw (null-separated) /proc/{pid}/cmdline
     * and matches whole argv entries — matching on a naively
     * space-joined string with strpos() would let worker id 1 match a
     * cmdline that actually belongs to worker 11 (or a --socket-dir
     * containing "1"), which matters a lot now that workers spawn other
     * workers: a false match here means a dead sibling gets silently
     * skipped instead of respawned.
     */
    private function isPidRunning(int $pid, int $expectedId): bool
    {
        if (function_exists('posix_kill') && !posix_kill($pid, 0)) {
            return false;
        } elseif (!function_exists('posix_kill') && !is_dir("/proc/{$pid}")) {
            return false;
        }

        $cmdlinePath = "/proc/{$pid}/cmdline";
        if (is_readable($cmdlinePath)) {
            $raw = (string) file_get_contents($cmdlinePath);
            $args = array_filter(explode("\0", $raw), fn ($a) => $a !== '');

            if (!in_array('xiangqi:engine-worker', $args, true)) {
                return false;
            }
            if (!in_array((string) $expectedId, $args, true)) {
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

    public function pidPath(int $id): string
    {
        return "{$this->socketDir}/engine-{$id}.pid";
    }

    public function startedPath(int $id): string
    {
        return "{$this->socketDir}/engine-{$id}.started";
    }

    public function socketPath(int $id): string
    {
        return "{$this->socketDir}/engine-{$id}.sock";
    }

    public function loopPidPath(int $id): string
    {
        return "{$this->socketDir}/engine-{$id}.loop.pid";
    }

    public function logPath(int $id): string
    {
        return "{$this->socketDir}/engine-{$id}.log";
    }

    private function stopFlagPath(): string
    {
        return "{$this->socketDir}/" . self::STOP_FLAG_FILENAME;
    }
}
