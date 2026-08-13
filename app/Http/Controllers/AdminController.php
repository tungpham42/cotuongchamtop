<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Room;
use App\Models\Tournament;
use App\Models\Puzzle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        // 1. KPI Cards Summary
        $stats = [
            'total_users'       => User::count(),
            'active_rooms'      => Room::whereNull('result')->count(),
            'total_tournaments' => Tournament::count(),
            'total_puzzles'     => Puzzle::count(),
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

        // 4. Recent Registrations & Active Rooms
        $recentUsers = User::latest()->take(5)->get();
        $recentRooms = Room::with(['host', 'guest'])->latest('modified_at')->take(5)->get();

        return view('admin.dashboard', compact('stats', 'userGrowth', 'roomDistribution', 'recentUsers', 'recentRooms'));
    }
}
