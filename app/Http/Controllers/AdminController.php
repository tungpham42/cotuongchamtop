<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Room;
use App\Models\Tournament;
use App\Models\Puzzle;
use App\Models\Article;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        // ==========================================
        // 1. KPI CARDS — Core Totals + Period Comparisons
        // ==========================================
        $totalUsers  = User::count();
        $totalRooms  = Room::count();
        $totalMatches = Room::whereNotNull('result')->count();

        // Week-over-week comparisons (drives the trend badges on the KPI cards)
        $newUsersThisWeek = User::where('created_at', '>=', now()->subDays(7))->count();
        $newUsersLastWeek = User::whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])->count();

        $matchesThisWeek = Room::whereNotNull('result')->where('created_at', '>=', now()->subDays(7))->count();
        $matchesLastWeek = Room::whereNotNull('result')->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])->count();

        $matchesToday = Room::whereNotNull('result')->whereDate('created_at', today())->count();

        $stats = [
            'total_users'           => $totalUsers,
            'new_users_week'        => $newUsersThisWeek,
            'user_growth_pct'       => $this->percentChange($newUsersLastWeek, $newUsersThisWeek),

            'total_matches'         => $totalMatches,
            'matches_today'         => $matchesToday,
            'matches_week'          => $matchesThisWeek,
            'match_growth_pct'      => $this->percentChange($matchesLastWeek, $matchesThisWeek),

            'active_rooms'          => Room::whereNull('result')->count(),
            'waiting_rooms'         => Room::whereNull('result')->whereNull('guest_id')->count(),

            'total_tournaments'     => Tournament::count(),
            'new_tournaments_month' => Tournament::where('created_at', '>=', now()->startOfMonth())->count(),

            'total_puzzles'         => Puzzle::count(),
            'new_puzzles_month'     => Puzzle::where('created_at', '>=', now()->startOfMonth())->count(),

            'total_articles'        => Article::count(),
            'published_articles'    => Article::where('status', 'published')->count(),
            'draft_articles'        => Article::where('status', 'draft')->count(),
            'new_articles_month'    => Article::where('created_at', '>=', now()->startOfMonth())->count(),

            'total_games'           => Game::count(),
            'new_games_month'       => Game::where('created_at', '>=', now()->startOfMonth())->count(),
            'total_game_views'      => (int) Game::sum('views'),

            'completion_rate'       => $totalRooms > 0 ? round(($totalMatches / $totalRooms) * 100, 1) : 0,
        ];

        // ==========================================
        // 2. Chart: Monthly User Registration Trend (Last 6 Months)
        // ==========================================
        $userGrowth = User::select(
                DB::raw('MONTHNAME(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'), DB::raw('MONTHNAME(created_at)'))
            ->orderBy(DB::raw('YEAR(created_at)'), 'asc')
            ->orderBy(DB::raw('MONTH(created_at)'), 'asc')
            ->get();

        // ==========================================
        // 3. Chart: Room Status Distribution
        // ==========================================
        $roomDistribution = [
            'ongoing'  => Room::whereNull('result')->whereNotNull('guest_id')->count(),
            'finished' => $totalMatches,
            'waiting'  => $stats['waiting_rooms'],
        ];

        // ==========================================
        // 4. Chart: Matches Played — Last 14 Days
        // ==========================================
        $matchesTrend = Room::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereNotNull('result')
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();

        $last14Days = collect();
        for ($i = 13; $i >= 0; $i--) {
            $dateStr = now()->subDays($i)->toDateString();
            $matchData = $matchesTrend->firstWhere('date', $dateStr);
            $last14Days->push([
                'date'  => Carbon::parse($dateStr)->format('M d'),
                'count' => $matchData ? $matchData->count : 0,
            ]);
        }

        $avgMatchesPerDay = $last14Days->count() > 0 ? round($last14Days->avg('count'), 1) : 0;

        // ==========================================
        // 5. Chart: Tournament Creation Trend (Last 6 Months)
        // ==========================================
        $tournamentGrowth = Tournament::select(
                DB::raw('MONTHNAME(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'), DB::raw('MONTHNAME(created_at)'))
            ->orderBy(DB::raw('YEAR(created_at)'), 'asc')
            ->orderBy(DB::raw('MONTH(created_at)'), 'asc')
            ->get();

        // ==========================================
        // 6. Needs Attention — Rooms stuck waiting for an opponent (> 15 min, no guest)
        // ==========================================
        $staleRooms = Room::whereNull('result')
            ->whereNull('guest_id')
            ->where('created_at', '<=', now()->subMinutes(15))
            ->with('host')
            ->latest('created_at')
            ->take(5)
            ->get();

        // ==========================================
        // 7. Recent Registrations & Recent Rooms
        // ==========================================
        $recentUsers = User::latest()->take(5)->get();
        $recentRooms = Room::with(['host', 'guest'])->latest('modified_at')->take(5)->get();

        // ==========================================
        // 8. Recent Articles (for the Content section)
        // ==========================================
        $recentArticles = Article::with('translation')->latest()->take(5)->get();

        // ==========================================
        // 9. Recent Games (for the Content section)
        // ==========================================
        $recentGames = Game::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'stats',
            'userGrowth',
            'roomDistribution',
            'last14Days',
            'avgMatchesPerDay',
            'tournamentGrowth',
            'staleRooms',
            'recentUsers',
            'recentRooms',
            'recentArticles',
            'recentGames'
        ));
    }

    /**
     * Admin-only engine pool diagnostic page (mirrors the old /test-engine
     * dev route, but lives behind the auth + IsAdmin middleware instead of
     * being gated purely by app()->environment()).
     *
     * Exercises the warm XiangqiEngineClient worker pool — the same path
     * XiangqiController uses in production — rather than spinning up a
     * fresh engine process per request. All markup/styling lives in the
     * admin.test-engine view; this method only gathers the data.
     */
    public function testEngine()
    {
        $checkedAt = now()->format('D, d M Y H:i:s');
        $overallOk = true;
        $checks    = [];
        $pool      = null;
        $moveTest  = null;
        $error     = null;

        try {
            // --- 1. Filesystem checks -------------------------------------------------
            $enginePath  = storage_path('engines/pikafish_vps');
            $networkPath = storage_path('engines/pikafish.nnue');

            $binaryExists  = file_exists($enginePath);
            $networkExists = file_exists($networkPath);
            $isExecutable  = $binaryExists && is_executable($enginePath);
            $networkSize   = $networkExists ? filesize($networkPath) : null;

            $overallOk = $overallOk && $binaryExists && $networkExists && $isExecutable;

            $checks = [
                [
                    'title'    => 'Engine Binary',
                    'icon'     => '⚙️',
                    'ok'       => $binaryExists,
                    'subtitle' => $binaryExists ? 'pikafish_vps found on disk' : 'Missing at ' . $enginePath,
                ],
                [
                    'title'    => 'Network File',
                    'icon'     => '🧠',
                    'ok'       => $networkExists,
                    'subtitle' => $networkExists ? $this->formatBytes($networkSize) . ' on disk' : 'Missing at ' . $networkPath,
                ],
                [
                    'title'    => 'Executable Permission',
                    'icon'     => '🔐',
                    'ok'       => $isExecutable,
                    'subtitle' => $isExecutable ? 'Binary is runnable' : 'Not executable — check chmod',
                ],
            ];

            // --- 2. Worker pool status --------------------------------------------------
            $client = new \App\Services\XiangqiEngineClient();
            $status = $client->poolStatus();
            $available = (int) ($status['available'] ?? 0);
            $total     = (int) ($status['total'] ?? 0);
            $poolHealthy = $available > 0;
            $overallOk = $overallOk && $poolHealthy;

            $pool = [
                'available' => $available,
                'total'     => $total,
                'pct'       => $total > 0 ? round(($available / $total) * 100) : 0,
                'healthy'   => $poolHealthy,
                'tone'      => $poolHealthy ? ($available === $total ? 'good' : 'warn') : 'bad',
                'label'     => $poolHealthy ? ($available === $total ? 'Healthy' : 'Degraded') : 'Down',
            ];

            // --- 3. Live move test (only if we have a worker to ask) --------------------
            if ($poolHealthy) {
                $fen = 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1';
                $start = microtime(true);
                $bestMove = $client->getBestMove($fen, 3000);
                $elapsedMs = (int) round((microtime(true) - $start) * 1000);
                $moveFound = !is_null($bestMove);
                $overallOk = $overallOk && $moveFound;

                $moveTest = [
                    'fen'        => $fen,
                    'bestMove'   => $bestMove,
                    'moveFound'  => $moveFound,
                    'elapsedMs'  => $elapsedMs,
                    'speedTone'  => $elapsedMs < 400 ? 'good' : ($elapsedMs < 1000 ? 'warn' : 'bad'),
                    'speedLabel' => $elapsedMs < 400 ? '⚡ Fast' : ($elapsedMs < 1000 ? '🙂 OK' : '🐢 Slow'),
                ];
            }
        } catch (\Throwable $e) {
            $overallOk = false;
            $error = [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ];
        }

        return view('admin.test-engine', [
            'checkedAt'   => $checkedAt,
            'overallOk'   => $overallOk,
            'statusTone'  => $overallOk ? 'good' : 'bad',
            'statusLabel' => $overallOk ? 'All systems operational' : 'Attention needed',
            'checks'      => $checks,
            'pool'        => $pool,
            'moveTest'    => $moveTest,
            'error'       => $error,
        ]);
    }

    /**
     * Human-readable byte size for the engine diagnostics page.
     */
    private function formatBytes(?int $bytes): string
    {
        if ($bytes === null) {
            return '—';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        $value = (float) $bytes;

        while ($value >= 1024 && $i < count($units) - 1) {
            $value /= 1024;
            $i++;
        }

        return round($value, $i === 0 ? 0 : 1) . ' ' . $units[$i];
    }

    /**
     * Percentage change between two period counts, used for KPI trend badges.
     */
    private function percentChange(int $previous, int $current): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
