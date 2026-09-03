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
