@extends('layouts.admin')

@section('title', 'Dashboard Overview')

@section('content')

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
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase text-gray-400">Total Users</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($stats['total_users']) }}</p>
        </div>
        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center text-xl"><i class="fa-solid fa-users"></i></div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase text-gray-400">Total Matches</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($stats['total_matches']) }}</p>
        </div>
        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-xl"><i class="fa-solid fa-chess"></i></div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase text-gray-400">Active Rooms</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['active_rooms']) }}</p>
        </div>
        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center text-xl"><i class="fa-solid fa-chess-board"></i></div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase text-gray-400">Tournaments</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($stats['total_tournaments']) }}</p>
        </div>
        <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center text-xl"><i class="fa-solid fa-trophy"></i></div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase text-gray-400">Puzzles</p>
            <p class="text-2xl font-bold text-purple-600 mt-1">{{ number_format($stats['total_puzzles']) }}</p>
        </div>
        <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center text-xl"><i class="fa-solid fa-puzzle-piece"></i></div>
    </div>
</div>

<!-- Interactive Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Line Chart: Monthly User Growth -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h2 class="text-base font-semibold text-gray-700 mb-4"><i class="fa-solid fa-chart-line mr-2 text-indigo-500"></i> User Registrations</h2>
        <div class="relative h-64">
            <canvas id="userGrowthChart"></canvas>
        </div>
    </div>

    <!-- Bar Chart: Match Activity (7 Days) -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h2 class="text-base font-semibold text-gray-700 mb-4"><i class="fa-solid fa-chart-column mr-2 text-blue-500"></i> Matches (Last 7 Days)</h2>
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
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $user->name }}</td>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-400">No active rooms found.</td>
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
        // Chart 1: User Growth Line Chart
        const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
        new Chart(userGrowthCtx, {
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
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });

        // Chart 2: Matches (7 Days) Bar Chart
        const matchTrendCtx = document.getElementById('matchTrendChart').getContext('2d');
        new Chart(matchTrendCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($last7Days->pluck('date')) !!},
                datasets: [{
                    label: 'Matches Played',
                    data: {!! json_encode($last7Days->pluck('count')) !!},
                    backgroundColor: '#3b82f6',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });

        // Chart 3: Room Status Doughnut Chart
        const roomStatusCtx = document.getElementById('roomStatusChart').getContext('2d');
        new Chart(roomStatusCtx, {
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
    });
</script>
@endpush
