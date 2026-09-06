<?php

namespace App\Console\Commands;

use App\Services\Xiangqi\WorkerSupervisor;
use Illuminate\Console\Command;

/**
 * `php artisan xiangqi:pool:ensure`
 *
 * Sweeps every worker id and starts any that aren't running. This is a
 * *backstop*, not the primary recovery mechanism — the pool now heals
 * itself at two other layers first (see WorkerSupervisor's docblock for
 * the full picture):
 *
 *   1. Each worker is launched inside its own detached respawn loop, so
 *      a crashed worker restarts itself within ~1-2s without anything
 *      external noticing.
 *   2. Each running worker also watches one neighbor in a ring and spawns
 *      it directly if that neighbor is down (see
 *      XiangqiEngineWorkerCommand) — so the fleet keeps healing itself
 *      even if this command, cron, and web-triggered self-heal were all
 *      disabled.
 *
 * This command matters for the case those two miss entirely: nothing was
 * ever started (fresh deploy / server reboot, before any worker exists to
 * watch anything), or an id's respawn loop AND its ring-watcher neighbor
 * both died at once.
 *
 * Meant to run from three places, all funneling through the same
 * WorkerSupervisor::ensureWorker() used by the ring-watchdog above:
 *
 *   1. Once manually after deploy, to bring the pool up the first time.
 *   2. Every minute via Laravel's scheduler (app/Console/Kernel.php),
 *      passing --respect-stop.
 *   3. On-demand, triggered by XiangqiEngineClient itself (also with
 *      --respect-stop) the moment a web request finds zero workers
 *      responding.
 *
 * WorkerSupervisor::ensureWorker() takes a per-id lock, so overlapping
 * callers (cron, a peer watchdog, a manual run) can never spawn duplicate
 * workers for the same id. The extra pool-wide lock below is just to keep
 * two full sweeps from interleaving their launch-stagger delays.
 *
 * INTENTIONAL STOP: `xiangqi:pool:stop` writes a pool-wide stop-flag file
 * before it signals workers, and every spawn path (respawn loops, the
 * ring watchdog, and this command) checks that flag first — so a
 * deliberate stop (e.g. before a deploy) actually stays stopped. Passing
 * --respect-stop here makes this command honor that flag (no-op while
 * it's present); running the command WITHOUT --respect-stop — i.e. a
 * human typing `php artisan xiangqi:pool:ensure` — always clears the flag
 * and brings the pool back up, matching what an operator expects that
 * command to do.
 */
class XiangqiPoolEnsureCommand extends Command
{
    protected $signature = 'xiangqi:pool:ensure
        {--respect-stop : No-op if the pool was intentionally stopped via xiangqi:pool:stop. Used by cron and by the self-heal trigger; a plain manual run never passes this and always clears the stop flag.}';

    protected $description = 'Start any Xiangqi engine workers that are not currently running';

    /**
     * Delay between launching consecutive cold-start workers during a
     * full sweep. Loading the same .nnue file N times simultaneously is
     * the single biggest cause of any one worker blowing past its boot
     * grace window in the first place — staggering keeps disk/CPU/RAM
     * contention down. Not used by the ring watchdog, which only ever
     * launches one id at a time anyway.
     */
    private const LAUNCH_STAGGER_SECONDS = 2.0;

    public function handle(): int
    {
        $supervisor = new WorkerSupervisor();
        $socketDir = config('xiangqi.socket_dir', storage_path('app/xiangqi'));

        if ($this->option('respect-stop') && $supervisor->isStopped()) {
            $this->info('Pool was intentionally stopped — skipping (run `php artisan xiangqi:pool:ensure` without --respect-stop to bring it back up).');
            return self::SUCCESS;
        }
        // A plain/manual run always means "I want the pool up."
        $supervisor->clearStopFlag();

        $lockPath = rtrim($socketDir, '/') . '/pool-ensure.lock';
        $lockHandle = fopen($lockPath, 'c');
        if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
            $this->info('Another xiangqi:pool:ensure is already running — skipping.');
            return self::SUCCESS;
        }

        try {
            $launchedAny = false;
            for ($id = 0; $id < $supervisor->workerCount(); $id++) {
                if ($launchedAny) {
                    usleep((int) (self::LAUNCH_STAGGER_SECONDS * 1_000_000));
                }

                // The stop flag was already resolved above; don't re-check
                // it per id.
                $launched = $supervisor->ensureWorker($id, respectStop: false);

                if ($launched) {
                    $this->info("[worker {$id}] was down — spawn triggered (check {$supervisor->logPath($id)} for boot status)");
                } else {
                    $this->line("[worker {$id}] alive, skipping");
                }

                $launchedAny = $launched || $launchedAny;
            }
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }

        return self::SUCCESS;
    }
}
