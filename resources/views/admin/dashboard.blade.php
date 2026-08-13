@extends('layouts.admin')

@section('title', 'Dashboard Overview')

@section('content')
<!-- KPI Metric Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase text-gray-400">Total Users</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($stats['total_users']) }}</p>
        </div>
        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center text-xl"><i class="fa-solid fa-users"></i></div>
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
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
        <h2 class="text-base font-semibold text-gray-700 mb-4"><i class="fa-solid fa-chart-line mr-2 text-indigo-500"></i> User Registration Trend</h2>
        <div class="relative h-64">
            <canvas id="userGrowthChart"></canvas>
        </div>
    </div>

    <!-- Doughnut Chart: Room Status Distribution -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h2 class="text-base font-semibold text-gray-700 mb-4"><i class="fa-solid fa-chart-pie mr-2 text-emerald-500"></i> Room Activity Status</h2>
        <div class="relative h-64 flex justify-center items-center">
            <canvas id="roomStatusChart"></canvas>
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
                scales: { y: { beginAtZero: true } }
            }
        });

        // Chart 2: Room Status Doughnut Chart
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
