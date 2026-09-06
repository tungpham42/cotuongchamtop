<?php

namespace App\Services\Xiangqi;

use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around `supervisorctl`, the process manager now
 * responsible for keeping the Pikafish worker pool alive on CentOS 9.
 *
 * This class used to contain ALL of the pool's self-healing logic:
 * detached `while true; do ...; done` respawn shell loops launched via
 * shell_exec, a peer-watchdog ring where each worker checked one
 * neighbor, PID-file bookkeeping, /proc/{pid}/cmdline sanity checks, and
 * a "boot grace" window to avoid false-killing a still-loading worker.
 * All of that is deleted. `supervisord` does it natively and more
 * reliably:
 *
 *   - supervisord starts every `xiangqi-worker_00`..`xiangqi-worker_NN`
 *     process on boot (autostart=true in the .ini) — this replaces the
 *     old "nothing was ever started" cron backstop entirely; there is no
 *     longer a scheduled `xiangqi:pool:ensure` tick in Kernel.php.
 *   - supervisord restarts a worker the moment its process exits, for
 *     any reason — crash, OOM kill, an unhandled exception escaping
 *     handle() — via autorestart=true. This replaces BOTH the detached
 *     respawn loop AND the ring watchdog in one stroke, and does it
 *     better: supervisord is one already-running daemon (managed by
 *     systemd, started at boot) that is not itself a sibling worker, so
 *     there's no "what if the thing that respawns me also died"
 *     bootstrapping problem to solve with a ring.
 *   - `supervisorctl start|stop xiangqi-worker:*` replaces
 *     XiangqiPoolEnsureCommand/XiangqiPoolStopCommand's manual
 *     pid-file + flock + shell_exec dance.
 *
 * What's LEFT for this class to do, now that process supervision itself
 * is someone else's job:
 *
 *   1. Ask supervisord whether a given worker id is RUNNING.
 *   2. Ask supervisord to (re)start one worker or the whole group.
 *   3. Track the "intentionally stopped" flag. supervisord has no idea
 *      that a stop was deliberate vs. transient — see STOP_FLAG_FILENAME
 *      below for why the app still needs to track that itself.
 *
 * See deploy/supervisord/xiangqi-workers.ini for the actual process
 * definitions and deploy/README-supervisor.md for CentOS 9 setup
 * (installing supervisord, socket permissions, SELinux notes).
 */
class WorkerSupervisor
{
    /**
     * The supervisord "program" name from xiangqi-workers.ini. Process
     * names within the group follow supervisord's numprocs convention:
     * `xiangqi-worker_00`, `xiangqi-worker_01`, ... — see
     * process_name=%(program_name)s_%(process_num)02d in the .ini.
     */
    public const PROGRAM_GROUP = 'xiangqi-worker';

    /**
     * Presence of this file means "an operator deliberately stopped the
     * pool via xiangqi:pool:stop — don't auto-restart it."
     *
     * This still has to live in the app (not in supervisord) because
     * supervisord's own state doesn't distinguish "an operator ran
     * `supervisorctl stop` on purpose before a deploy" from "a worker is
     * momentarily down for some other reason." Without this flag,
     * XiangqiEngineClient's request-triggered self-heal would call
     * `supervisorctl start` on a pool an operator just intentionally took
     * down mid-deploy, undoing the stop within milliseconds.
     *
     * Cleared only by a manual, non---respect-stop run of
     * `xiangqi:pool:ensure`.
     */
    public const STOP_FLAG_FILENAME = 'pool.stopped';

    private string $socketDir;
    private int $workerCount;
    private string $supervisorctlBin;

    public function __construct(?string $socketDir = null, ?int $workerCount = null, ?string $supervisorctlBin = null)
    {
        $this->socketDir = rtrim($socketDir ?? config('xiangqi.socket_dir', storage_path('app/xiangqi')), '/');
        $this->workerCount = $workerCount ?? (int) config('xiangqi.worker_count', 12);
        $this->supervisorctlBin = $supervisorctlBin ?? config('xiangqi.supervisorctl_bin', '/usr/bin/supervisorctl');

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

    /** Called by xiangqi:pool:stop, before it asks supervisord to stop anything. */
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
     * supervisord's fully-qualified process name for worker $id, e.g.
     * "xiangqi-worker:xiangqi-worker_03". Matches numprocs_start=0 and
     * the %(process_num)02d format in xiangqi-workers.ini.
     */
    public function processName(int $id): string
    {
        return sprintf('%s:%s_%02d', self::PROGRAM_GROUP, self::PROGRAM_GROUP, $id);
    }

    /**
     * True if supervisord currently reports this worker's OS process as
     * RUNNING. This says nothing about whether the Pikafish engine
     * inside it has finished loading and is answering pings — that's a
     * separate, faster-changing concern XiangqiEngineClient::pingWorker()
     * already handles over the worker's own unix socket. Conflating the
     * two was exactly the bug class the old BOOT_GRACE_SECONDS logic
     * existed to paper over; keeping them separate here means this class
     * no longer needs to know anything about boot timing at all.
     */
    public function isWorkerRunning(int $id): bool
    {
        $output = $this->supervisorctl(['status', $this->processName($id)]);
        return (bool) preg_match('/\bRUNNING\b/', $output);
    }

    /**
     * Ask supervisord to start worker $id if it isn't already running.
     * Idempotent and safe to call concurrently from multiple
     * processes/requests — supervisorctl itself serializes against
     * supervisord's control socket, so there's no need for the
     * file-based per-id flock the old ensureWorker() used.
     *
     * @param bool $respectStop if true, this is a no-op while the pool is
     *     intentionally stopped. Automatic callers (the request-triggered
     *     self-heal in XiangqiEngineClient) should always pass true. A
     *     manual `xiangqi:pool:ensure` run resolves the stop flag itself
     *     before calling this, so it passes false.
     * @return bool true if a start was actually issued (i.e. the worker
     *     was not already reported RUNNING)
     */
    public function ensureWorker(int $id, bool $respectStop = true): bool
    {
        if ($respectStop && $this->isStopped()) {
            return false;
        }

        if ($this->isWorkerRunning($id)) {
            return false;
        }

        Log::warning("[xiangqi supervisor] worker {$id} not running — issuing supervisorctl start");
        $this->supervisorctl(['start', $this->processName($id)]);

        return true;
    }

    /**
     * Ask supervisord to start every worker that isn't already running.
     * Used by XiangqiEngineClient's on-demand self-heal (when a request
     * finds the whole pool unreachable) and by `xiangqi:pool:ensure`.
     */
    public function startAll(bool $respectStop = true): void
    {
        if ($respectStop && $this->isStopped()) {
            return;
        }

        $this->supervisorctl(['start', self::PROGRAM_GROUP . ':*']);
    }

    /** Used by `xiangqi:pool:stop`. */
    public function stopAll(): void
    {
        $this->supervisorctl(['stop', self::PROGRAM_GROUP . ':*']);
    }

    /**
     * Raw `supervisorctl status` output for every worker in the group,
     * for diagnostics/health endpoints. One line per worker, e.g.
     * "xiangqi-worker:xiangqi-worker_00   RUNNING   pid 1234, uptime 0:12:03".
     */
    public function statusAll(): string
    {
        return $this->supervisorctl(['status', self::PROGRAM_GROUP . ':*']);
    }

    /**
     * Shells out to supervisorctl. Best-effort: supervisorctl's own exit
     * code/stderr on a transient RPC hiccup shouldn't blow up a web
     * request, so failures here just come back as empty/unmatched output
     * rather than throwing — callers already treat "not confirmed
     * running" and "confirmed not running" the same way.
     */
    private function supervisorctl(array $args): string
    {
        $cmd = escapeshellcmd($this->supervisorctlBin) . ' '
            . implode(' ', array_map('escapeshellarg', $args))
            . ' 2>&1';

        return (string) @shell_exec($cmd);
    }

    private function stopFlagPath(): string
    {
        return "{$this->socketDir}/" . self::STOP_FLAG_FILENAME;
    }
}
