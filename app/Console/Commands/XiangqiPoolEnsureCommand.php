<?php

namespace App\Console\Commands;

use App\Services\XiangqiEngineClient;
use Illuminate\Console\Command;

/**
 * `php artisan xiangqi:pool:ensure`
 *
 * Makes sure `worker_count` engine workers are alive and (re)spawns any
 * that aren't, fully detached. Run once after deploy and every minute via
 * Laravel's scheduler.
 *
 * LIVENESS: a worker that is busy searching cannot answer a ping until the
 * search ends, so a failed ping alone does NOT mean it is dead. A worker is
 * only declared dead if its process is gone, or if it fails MAX_PING_FAILURES
 * consecutive checks (each with a generous ping timeout) after its boot-grace
 * window. A stuck worker is killed (whole process group) before respawn so
 * duplicates can never pile up.
 */
class XiangqiPoolEnsureCommand extends Command
{
    protected $signature = 'xiangqi:pool:ensure';

    protected $description = 'Start any Xiangqi engine workers that are not currently running';

    private const BOOT_GRACE_SECONDS = 30.0;
    private const LAUNCH_STAGGER_SECONDS = 2.0;
    private const PING_TIMEOUT_SECONDS = 5.0;
    private const MAX_PING_FAILURES = 3;

    public function handle(): int
    {
        $socketDir = config('xiangqi.socket_dir', storage_path('app/xiangqi'));
        $workerCount = (int) config('xiangqi.worker_count', 5);

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
        $dir = rtrim($socketDir, '/');
        $pidPath = "{$dir}/engine-{$id}.pid";
        $startedPath = "{$dir}/engine-{$id}.started";
        $failPath = "{$dir}/engine-{$id}.fails";

        if ($this->isWorkerAlive($id, $pidPath, $startedPath, $failPath)) {
            $this->line("[worker {$id}] alive, skipping");
            return false;
        }

        $this->warn("[worker {$id}] not running — starting it");

        @unlink($pidPath);
        @unlink($startedPath);
        @unlink($failPath);
        @unlink("{$dir}/engine-{$id}.sock");

        $logPath = "{$dir}/engine-{$id}.log";
        $artisan = escapeshellarg(base_path('artisan'));
        $php = escapeshellarg($this->resolvePhpBinary());
        $log = escapeshellarg($logPath);

        $launcher = $this->hasSetsid() ? 'setsid' : 'nohup';

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

    private function isWorkerAlive(int $id, string $pidPath, string $startedPath, string $failPath): bool
    {
        if (!file_exists($pidPath)) {
            return false;
        }

        $pid = (int) trim((string) file_get_contents($pidPath));
        if ($pid <= 0 || !$this->isPidRunning($pid, $id)) {
            return false;
        }

        // Long timeout: a busy worker only answers after its current search.
        if ((new XiangqiEngineClient())->pingWorker($id, self::PING_TIMEOUT_SECONDS)) {
            @unlink($startedPath);
            @unlink($failPath);
            return true;
        }

        if ($this->withinBootGrace($startedPath)) {
            $this->line("[worker {$id}] pid running, not answering yet — within boot grace, treating as alive");
            return true;
        }

        $fails = (int) trim((string) @file_get_contents($failPath)) + 1;
        if ($fails < self::MAX_PING_FAILURES) {
            file_put_contents($failPath, (string) $fails);
            $this->line("[worker {$id}] ping failed ({$fails}/" . self::MAX_PING_FAILURES . ') — leaving it alone');
            return true;
        }

        // Genuinely stuck: kill worker + its pikafish child (setsid made the
        // worker its process-group leader) so no duplicate is left behind.
        $this->warn("[worker {$id}] unresponsive for {$fails} checks — killing pid {$pid}");
        if (function_exists('posix_kill')) {
            @posix_kill(-$pid, 9);
            @posix_kill($pid, 9);
        } else {
            @shell_exec('kill -9 -- -' . (int) $pid . ' ' . (int) $pid . ' 2>/dev/null');
        }
        usleep(300_000);

        return false;
    }

    private function withinBootGrace(string $startedPath): bool
    {
        if (!file_exists($startedPath)) {
            return false;
        }

        $startedAt = (float) trim((string) file_get_contents($startedPath));
        if ($startedAt <= 0) {
            return false;
        }

        return (microtime(true) - $startedAt) < self::BOOT_GRACE_SECONDS;
    }

    private function isPidRunning(int $pid, int $expectedId): bool
    {
        if (function_exists('posix_kill') && !posix_kill($pid, 0)) {
            return false;
        } elseif (!function_exists('posix_kill') && !is_dir("/proc/{$pid}")) {
            return false;
        }

        $cmdlinePath = "/proc/{$pid}/cmdline";
        if (is_readable($cmdlinePath)) {
            $cmdline = str_replace("\0", ' ', (string) file_get_contents($cmdlinePath));
            if (!preg_match('/xiangqi:engine-worker\s+' . $expectedId . '(\s|$)/', $cmdline)) {
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

    private function resolvePhpBinary(): string
    {
        $candidate = (new \Symfony\Component\Process\PhpExecutableFinder())->find(false)
            ?: (PHP_BINARY !== '' ? PHP_BINARY : null)
            ?: config('xiangqi.php_binary');

        if (!$candidate || !is_executable($candidate)) {
            throw new \RuntimeException(
                'Could not resolve a usable PHP CLI binary to launch xiangqi workers. '
                . 'Set XIANGQI_PHP_BINARY in .env to your CLI php path (e.g. /usr/bin/php) '
                . 'and wire it into config/xiangqi.php as php_binary.'
            );
        }

        return $candidate;
    }
}
