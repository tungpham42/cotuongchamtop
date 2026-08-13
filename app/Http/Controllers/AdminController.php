<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Room;
use App\Models\Tournament;
use App\Models\Puzzle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        // 1. KPI Cards Summary (Expanded)
        $stats = [
            'total_users'       => User::count(),
            'active_rooms'      => Room::whereNull('result')->count(),
            'total_tournaments' => Tournament::count(),
            'total_puzzles'     => Puzzle::count(),
            // New KPI: Total matches ever completed
            'total_matches'     => Room::whereNotNull('result')->count(),
        ];

        // 2. Chart 1 Data: Monthly User Registration Trend (Last 6 Months)
        $userGrowth = User::select(
                DB::raw('MONTHNAME(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'), DB::raw('MONTHNAME(created_at)'))
            ->orderBy(DB::raw('YEAR(created_at)'), 'asc')
            ->orderBy(DB::raw('MONTH(created_at)'), 'asc')
            ->get();

        // 3. Chart 2 Data: Room Status Distribution
        $roomDistribution = [
            'ongoing'  => Room::whereNull('result')->whereNotNull('host_id')->count(),
            'finished' => Room::whereNotNull('result')->count(),
            'waiting'  => Room::whereNull('result')->whereNull('guest_id')->count(),
        ];

        // 4. Chart 3 Data: Matches Played Last 7 Days (New)
        $matchesTrend = Room::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereNotNull('result')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();

        // Ensure all 7 days are represented even if 0 matches were played
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $dateStr = now()->subDays($i)->toDateString();
            $matchData = $matchesTrend->firstWhere('date', $dateStr);
            $last7Days->push([
                'date' => Carbon::parse($dateStr)->format('M d'),
                'count' => $matchData ? $matchData->count : 0
            ]);
        }

        // 5. Recent Registrations & Active Rooms
        $recentUsers = User::latest()->take(5)->get();
        $recentRooms = Room::with(['host', 'guest'])->latest('modified_at')->take(5)->get();

        return view('admin.dashboard', compact(
            'stats',
            'userGrowth',
            'roomDistribution',
            'last7Days',
            'recentUsers',
            'recentRooms'
        ));
    }
}
