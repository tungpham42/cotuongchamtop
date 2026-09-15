<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Game;
use App\Models\KarmaLog;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class AdminMarketingController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();

        $activatedIds = Room::whereNotNull('host_id')->pluck('host_id')
            ->merge(Room::whereNotNull('guest_id')->pluck('guest_id'))
            ->unique();
        $activatedUsers = $activatedIds->count();

        $engagedUsers = KarmaLog::select('user_id')->groupBy('user_id')
            ->havingRaw('COUNT(*) >= 3')->pluck('user_id')->count();

        $retainedUsers = User::where('last_seen_at', '>=', now()->subDays(7))->count();

        $convertedUsers = User::where('subscription_plan', 'standard')
            ->where(fn ($q) => $q->whereNull('subscription_ends_at')->orWhere('subscription_ends_at', '>', now()))
            ->count();

        $newUsersThisWeek = User::where('created_at', '>=', now()->subDays(7))->count();
        $newUsersLastWeek = User::whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])->count();

        $stats = [
            'total_users' => $totalUsers,
            'new_users_week' => $newUsersThisWeek,
            'user_growth_pct' => $this->percentChange($newUsersLastWeek, $newUsersThisWeek), // reuse from AdminController
            'activated_users' => $activatedUsers,
            'activation_rate' => $totalUsers ? $activatedUsers / $totalUsers * 100 : 0,
            'engaged_users' => $engagedUsers,
            'engagement_rate' => $totalUsers ? $engagedUsers / $totalUsers * 100 : 0,
            'retained_users' => $retainedUsers,
            'retention_rate' => $totalUsers ? $retainedUsers / $totalUsers * 100 : 0,
            'converted_users' => $convertedUsers,
            'conversion_rate' => $totalUsers ? $convertedUsers / $totalUsers * 100 : 0,
            'total_articles' => Article::count(),
            'published_articles' => Article::where('status', 'published')->count(),
            'draft_articles' => Article::where('status', 'draft')->count(),
            'total_article_views' => (int) Article::sum('views'),
            'avg_article_views' => Article::avg('views'),
            'total_games' => Game::count(),
            'total_game_views' => (int) Game::sum('views'),
            'avg_game_views' => Game::avg('views'),
        ];

        $funnel = [
            ['key' => 'registered', 'label' => 'Registered', 'value' => $totalUsers, 'icon' => 'fa-user-plus', 'color' => 'indigo'],
            ['key' => 'activated', 'label' => 'Activated (1st match)', 'value' => $activatedUsers, 'icon' => 'fa-chess-board', 'color' => 'sky'],
            ['key' => 'engaged', 'label' => 'Engaged (3+ sessions)', 'value' => $engagedUsers, 'icon' => 'fa-fire', 'color' => 'amber'],
            ['key' => 'retained', 'label' => 'Retained (7-day)', 'value' => $retainedUsers, 'icon' => 'fa-heart-pulse', 'color' => 'emerald'],
            ['key' => 'converted', 'label' => 'Converted (Paid)', 'value' => $convertedUsers, 'icon' => 'fa-crown', 'color' => 'violet'],
        ];

        $signupTrend = User::select(DB::raw('MONTHNAME(created_at) as month'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'), DB::raw('MONTHNAME(created_at)'))
            ->orderBy(DB::raw('YEAR(created_at)'))->orderBy(DB::raw('MONTH(created_at)'))
            ->get();

        $retentionSnapshot = [
            'retained' => $retainedUsers,
            'not_retained' => max(0, $activatedUsers - $retainedUsers),
        ];

        $contentViewsByType = [
            'articles' => (int) Article::sum('views'),
            'games' => (int) Game::sum('views'),
        ];

        $topArticles = Article::with('translation')->orderByDesc('views')->take(5)->get();
        $topGames = Game::with('user')->orderByDesc('views')->take(5)->get();

        return view('admin.marketing', compact(
            'stats', 'funnel', 'signupTrend', 'retentionSnapshot', 'contentViewsByType', 'topArticles', 'topGames'
        ));
    }
}
