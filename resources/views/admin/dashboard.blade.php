@extends('layouts.admin')

@section('title', 'Dashboard Overview')

@section('content')

@php
    // Small helper to render a consistent up/down/flat trend badge from a % value
    $trend = function ($pct) {
        if ($pct > 0) return ['color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'icon' => 'fa-arrow-up', 'sign' => '+'];
        if ($pct < 0) return ['color' => 'text-rose-700', 'bg' => 'bg-rose-50', 'icon' => 'fa-arrow-down', 'sign' => ''];
        return ['color' => 'text-gray-500', 'bg' => 'bg-gray-100', 'icon' => 'fa-minus', 'sign' => ''];
    };
    $userTrend  = $trend($stats['user_growth_pct']);
    $matchTrend = $trend($stats['match_growth_pct']);
@endphp

<!-- Quick Actions -->
<div class="mb-8 flex flex-wrap gap-4">
    <a href="{{ route('admin.users.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm">
        <i class="fa-solid fa-user-plus mr-2"></i> Manage Users
    </a>
    <a href="{{ route('admin.rooms.index') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm">
        <i class="fa-solid fa-chess-board mr-2"></i> View Rooms
    </a>
    <a href="{{ route('admin.tournaments.index') }}" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm">
        <i class="fa-solid fa-trophy mr-2"></i> Tournaments
    </a>
    <a href="{{ route('admin.puzzles.index') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm">
        <i class="fa-solid fa-puzzle-piece mr-2"></i> Manage Puzzles
    </a>
</div>

<!-- KPI Metric Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

    <!-- Total Users -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <p class="text-xs font-semibold uppercase text-gray-400">Total Users</p>
            <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center text-lg"><i class="fa-solid fa-users"></i></div>
        </div>
        <p class="text-2xl font-bold text-gray-800 mt-3">{{ number_format($stats['total_users']) }}</p>
        <div class="flex items-center gap-2 mt-2">
            <span class="{{ $userTrend['color'] }} {{ $userTrend['bg'] }} text-xs font-semibold px-2 py-0.5 rounded-full">
                <i class="fa-solid {{ $userTrend['icon'] }} mr-1"></i>{{ $userTrend['sign'] }}{{ $stats['user_growth_pct'] }}%
            </span>
            <span class="text-xs text-gray-400">{{ $stats['new_users_week'] }} new this week</span>
        </div>
    </div>

    <!-- Total Matches -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <p class="text-xs font-semibold uppercase text-gray-400">Total Matches</p>
            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-lg"><i class="fa-solid fa-chess"></i></div>
        </div>
        <p class="text-2xl font-bold text-blue-600 mt-3">{{ number_format($stats['total_matches']) }}</p>
        <div class="flex items-center gap-2 mt-2">
            <span class="{{ $matchTrend['color'] }} {{ $matchTrend['bg'] }} text-xs font-semibold px-2 py-0.5 rounded-full">
                <i class="fa-solid {{ $matchTrend['icon'] }} mr-1"></i>{{ $matchTrend['sign'] }}{{ $stats['match_growth_pct'] }}%
            </span>
            <span class="text-xs text-gray-400">{{ $stats['matches_today'] }} today</span>
        </div>
    </div>

    <!-- Active Rooms -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <p class="text-xs font-semibold uppercase text-gray-400">Active Rooms</p>
            <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center text-lg"><i class="fa-solid fa-chess-board"></i></div>
        </div>
        <p class="text-2xl font-bold text-emerald-600 mt-3">{{ number_format($stats['active_rooms']) }}</p>
        <div class="flex items-center gap-2 mt-2">
            <span class="text-xs text-gray-400"><i class="fa-solid fa-hourglass-half mr-1"></i>{{ $stats['waiting_rooms'] }} waiting for opponent</span>
        </div>
    </div>

    <!-- Completion Rate -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <p class="text-xs font-semibold uppercase text-gray-400">Completion Rate</p>
            <div class="w-10 h-10 bg-teal-50 text-teal-600 rounded-lg flex items-center justify-center text-lg"><i class="fa-solid fa-flag-checkered"></i></div>
        </div>
        <p class="text-2xl font-bold text-teal-600 mt-3">{{ $stats['completion_rate'] }}%</p>
        <div class="w-full bg-gray-100 rounded-full h-2 mt-3">
            <div class="bg-teal-500 h-2 rounded-full" style="width: {{ min($stats['completion_rate'], 100) }}%"></div>
        </div>
    </div>

    <!-- Tournaments -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <p class="text-xs font-semibold uppercase text-gray-400">Tournaments</p>
            <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center text-lg"><i class="fa-solid fa-trophy"></i></div>
        </div>
        <p class="text-2xl font-bold text-amber-600 mt-3">{{ number_format($stats['total_tournaments']) }}</p>
        <div class="flex items-center gap-2 mt-2">
            <span class="text-xs text-gray-400">{{ $stats['new_tournaments_month'] }} created this month</span>
        </div>
    </div>

    <!-- Puzzles -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <p class="text-xs font-semibold uppercase text-gray-400">Puzzles</p>
            <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center text-lg"><i class="fa-solid fa-puzzle-piece"></i></div>
        </div>
        <p class="text-2xl font-bold text-purple-600 mt-3">{{ number_format($stats['total_puzzles']) }}</p>
        <div class="flex items-center gap-2 mt-2">
            <span class="text-xs text-gray-400">{{ $stats['new_puzzles_month'] }} added this month</span>
        </div>
    </div>

    <!-- Avg Matches / Day -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <p class="text-xs font-semibold uppercase text-gray-400">Avg Matches / Day</p>
            <div class="w-10 h-10 bg-sky-50 text-sky-600 rounded-lg flex items-center justify-center text-lg"><i class="fa-solid fa-chart-simple"></i></div>
        </div>
        <p class="text-2xl font-bold text-sky-600 mt-3">{{ $avgMatchesPerDay }}</p>
        <div class="flex items-center gap-2 mt-2">
            <span class="text-xs text-gray-400">over the last 14 days</span>
        </div>
    </div>

    <!-- Needs Attention -->
    <div class="bg-white p-6 rounded-xl shadow-sm border {{ $staleRooms->count() > 0 ? 'border-rose-200' : 'border-gray-100' }}">
        <div class="flex items-center justify-between">
            <p class="text-xs font-semibold uppercase text-gray-400">Needs Attention</p>
            <div class="w-10 h-10 {{ $staleRooms->count() > 0 ? 'bg-rose-50 text-rose-600' : 'bg-gray-50 text-gray-400' }} rounded-lg flex items-center justify-center text-lg">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>
        <p class="text-2xl font-bold {{ $staleRooms->count() > 0 ? 'text-rose-600' : 'text-gray-800' }} mt-3">{{ $staleRooms->count() }}</p>
        <div class="flex items-center gap-2 mt-2">
            <span class="text-xs text-gray-400">stale rooms (15+ min unmatched)</span>
        </div>
    </div>
</div>

<!-- Interactive Charts Row 1 -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Line Chart: Monthly User Growth -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h2 class="text-base font-semibold text-gray-700 mb-4"><i class="fa-solid fa-chart-line mr-2 text-indigo-500"></i> User Registrations</h2>
        <div class="relative h-64">
            <canvas id="userGrowthChart"></canvas>
        </div>
    </div>

    <!-- Bar Chart: Match Activity (14 Days) -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h2 class="text-base font-semibold text-gray-700 mb-4"><i class="fa-solid fa-chart-column mr-2 text-blue-500"></i> Matches (Last 14 Days)</h2>
        <div class="relative h-64">
            <canvas id="matchTrendChart"></canvas>
        </div>
    </div>

    <!-- Doughnut Chart: Room Status Distribution -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h2 class="text-base font-semibold text-gray-700 mb-4"><i class="fa-solid fa-chart-pie mr-2 text-emerald-500"></i> Room Status</h2>
        <div class="relative h-64 flex justify-center items-center">
            <canvas id="roomStatusChart"></canvas>
        </div>
    </div>
</div>

<!-- Interactive Charts Row 2 -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Bar Chart: Tournament Creation Trend -->
    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h2 class="text-base font-semibold text-gray-700 mb-4"><i class="fa-solid fa-trophy mr-2 text-amber-500"></i> Tournaments Created (Last 6 Months)</h2>
        <div class="relative h-64">
            <canvas id="tournamentGrowthChart"></canvas>
        </div>
    </div>

    <!-- Needs Attention Panel -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-base font-semibold text-gray-700"><i class="fa-solid fa-triangle-exclamation mr-2 text-rose-500"></i> Stale Rooms</h2>
            <a href="{{ route('admin.rooms.index') }}" class="text-xs font-medium text-rose-600 hover:text-rose-800">View All</a>
        </div>
        <div class="flex-1 divide-y divide-gray-50">
            @forelse($staleRooms as $room)
                <div class="px-6 py-3 flex items-center justify-between">
                    <div>
                        <p class="font-mono text-sm font-medium text-gray-900">{{ $room->code }}</p>
                        <p class="text-xs text-gray-400">Host: {{ $room->host ? $room->host->name : 'Unknown' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="bg-rose-100 text-rose-700 text-xs font-medium px-2 py-0.5 rounded">{{ $room->created_at->diffForHumans(null, true) }}</span>
                        <p class="text-xs text-gray-400 mt-1">
                            <a href="{{ route('admin.rooms.show', $room) }}" class="text-indigo-600 hover:text-indigo-800">Inspect</a>
                        </p>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-emerald-600 text-sm">
                    <i class="fa-solid fa-circle-check text-2xl mb-2 block"></i>
                    All rooms are matching quickly. Nothing needs attention.
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Data Tables Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Recent Users Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-base font-semibold text-gray-700"><i class="fa-solid fa-user-clock mr-2 text-indigo-500"></i> Recent Registrations</h2>
            <a href="{{ route('admin.users.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Name</th>
                        <th scope="col" class="px-6 py-3">Email</th>
                        <th scope="col" class="px-6 py-3">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentUsers as $user)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900 flex items-center gap-2">
                                <img src="{{ $user->getAvatarUrl() }}" class="w-6 h-6 rounded-full object-cover border" alt="">
                                {{ $user->name }}
                            </td>
                            <td class="px-6 py-4">{{ $user->email }}</td>
                            <td class="px-6 py-4">{{ $user->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-400">No recent users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Rooms Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-base font-semibold text-gray-700"><i class="fa-solid fa-gamepad mr-2 text-emerald-500"></i> Recent Rooms</h2>
            <a href="{{ route('admin.rooms.index') }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-800">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Code</th>
                        <th scope="col" class="px-6 py-3">Host</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3">Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentRooms as $room)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-mono font-medium text-gray-900">{{ $room->code }}</td>
                            <td class="px-6 py-4">{{ $room->host ? $room->host->name : 'Unknown' }}</td>
                            <td class="px-6 py-4">
                                @if($room->result)
                                    <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">Finished</span>
                                @elseif($room->guest_id)
                                    <span class="bg-emerald-100 text-emerald-800 text-xs font-medium px-2.5 py-0.5 rounded">Playing</span>
                                @else
                                    <span class="bg-amber-100 text-amber-800 text-xs font-medium px-2.5 py-0.5 rounded">Waiting</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-400">{{ optional($room->modified_at)->diffForHumans() ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-400">No active rooms found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {

        const baseOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        };

        // Chart 1: User Growth Line Chart
        new Chart(document.getElementById('userGrowthChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($userGrowth->pluck('month')) !!},
                datasets: [{
                    label: 'New Registrations',
                    data: {!! json_encode($userGrowth->pluck('count')) !!},
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: baseOptions
        });

        // Chart 2: Matches (14 Days) Bar Chart
        new Chart(document.getElementById('matchTrendChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($last14Days->pluck('date')) !!},
                datasets: [{
                    label: 'Matches Played',
                    data: {!! json_encode($last14Days->pluck('count')) !!},
                    backgroundColor: '#3b82f6',
                    borderRadius: 4
                }]
            },
            options: baseOptions
        });

        // Chart 3: Room Status Doughnut Chart
        new Chart(document.getElementById('roomStatusChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Ongoing', 'Finished', 'Waiting'],
                datasets: [{
                    data: [
                        {{ $roomDistribution['ongoing'] }},
                        {{ $roomDistribution['finished'] }},
                        {{ $roomDistribution['waiting'] }}
                    ],
                    backgroundColor: ['#10b981', '#6b7280', '#f59e0b'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // Chart 4: Tournament Growth Bar Chart
        new Chart(document.getElementById('tournamentGrowthChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($tournamentGrowth->pluck('month')) !!},
                datasets: [{
                    label: 'Tournaments Created',
                    data: {!! json_encode($tournamentGrowth->pluck('count')) !!},
                    backgroundColor: '#f59e0b',
                    borderRadius: 4
                }]
            },
            options: baseOptions
        });
    });
</script>
@endpush
