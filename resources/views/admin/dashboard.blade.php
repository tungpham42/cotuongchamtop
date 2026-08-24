@extends('layouts.admin')

@section('title', 'Dashboard Overview')

@section('content')

@php
    $trend = function ($pct) {
        if ($pct > 0) return ['color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'icon' => 'fa-arrow-up', 'sign' => '+'];
        if ($pct < 0) return ['color' => 'text-rose-700', 'bg' => 'bg-rose-50', 'icon' => 'fa-arrow-down', 'sign' => ''];
        return ['color' => 'text-slate-500', 'bg' => 'bg-slate-100', 'icon' => 'fa-minus', 'sign' => ''];
    };

    $userTrend  = $trend($stats['user_growth_pct']);
    $matchTrend = $trend($stats['match_growth_pct']);

    $completion = min(max((float) $stats['completion_rate'], 0), 100);
    $activeRooms = (int) $stats['active_rooms'];
    $waitingRooms = (int) $stats['waiting_rooms'];
    $ongoingRooms = (int) $roomDistribution['ongoing'];
@endphp

<div class="space-y-8">

    {{-- Hero / command center --}}
    <section class="relative overflow-hidden rounded-[28px] bg-slate-950 text-white shadow-lift">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_15%_20%,rgba(99,102,241,.38),transparent_28%),radial-gradient(circle_at_90%_5%,rgba(14,165,233,.30),transparent_26%)]"></div>
        <div class="relative p-6 sm:p-8 lg:p-10">
            <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-7">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-[.18em] text-indigo-100">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        Live admin overview
                    </div>
                    <h2 class="mt-5 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-[1.05]">
                        The board is alive.
                        <span class="text-indigo-300">Keep the arena moving.</span>
                    </h2>
                    <p class="mt-4 max-w-xl text-sm sm:text-base leading-7 text-slate-300">
                        A single place to monitor players, rooms, puzzles and tournaments — with the most important signals surfaced first.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:min-w-[360px]">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <div class="text-xs font-semibold text-slate-400">Playing now</div>
                        <div class="mt-1 text-2xl font-extrabold">{{ number_format($ongoingRooms) }}</div>
                        <div class="mt-1 text-xs text-emerald-300"><i class="fa-solid fa-circle text-[7px] mr-1"></i>Live rooms</div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <div class="text-xs font-semibold text-slate-400">Waiting</div>
                        <div class="mt-1 text-2xl font-extrabold">{{ number_format($waitingRooms) }}</div>
                        <div class="mt-1 text-xs text-amber-300"><i class="fa-solid fa-hourglass-half mr-1"></i>Matchmaking</div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <div class="text-xs font-semibold text-slate-400">Matches today</div>
                        <div class="mt-1 text-2xl font-extrabold">{{ number_format($stats['matches_today']) }}</div>
                        <div class="mt-1 text-xs text-sky-300"><i class="fa-solid fa-bolt mr-1"></i>Today</div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <div class="text-xs font-semibold text-slate-400">Completion</div>
                        <div class="mt-1 text-2xl font-extrabold">{{ $completion }}%</div>
                        <div class="mt-2 h-1.5 rounded-full bg-white/10 overflow-hidden">
                            <div class="h-full rounded-full bg-indigo-300" style="width: {{ $completion }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Quick actions --}}
    <section>
        <div class="flex items-center justify-between mb-4">
            <div>
                <div class="text-[11px] font-extrabold uppercase tracking-[.18em] text-slate-400">Shortcuts</div>
                <h3 class="mt-1 text-lg font-extrabold text-slate-900">Jump into the action</h3>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <a href="{{ route('admin.users.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-soft transition hover:-translate-y-1 hover:border-indigo-200 hover:shadow-lift">
                <div class="flex items-start justify-between">
                    <span class="h-11 w-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="fa-solid fa-user-plus"></i>
                    </span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-slate-300 group-hover:text-indigo-500"></i>
                </div>
                <div class="mt-4 text-sm font-extrabold text-slate-900">Manage users</div>
                <div class="mt-1 text-xs leading-5 text-slate-500">Review registrations and player activity.</div>
            </a>

            <a href="{{ route('admin.rooms.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-soft transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lift">
                <div class="flex items-start justify-between">
                    <span class="h-11 w-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i class="fa-solid fa-chess-board"></i>
                    </span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-slate-300 group-hover:text-emerald-500"></i>
                </div>
                <div class="mt-4 text-sm font-extrabold text-slate-900">Inspect rooms</div>
                <div class="mt-1 text-xs leading-5 text-slate-500">See waiting, live and completed matches.</div>
            </a>

            <a href="{{ route('admin.tournaments.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-soft transition hover:-translate-y-1 hover:border-amber-200 hover:shadow-lift">
                <div class="flex items-start justify-between">
                    <span class="h-11 w-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i class="fa-solid fa-trophy"></i>
                    </span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-slate-300 group-hover:text-amber-500"></i>
                </div>
                <div class="mt-4 text-sm font-extrabold text-slate-900">Tournaments</div>
                <div class="mt-1 text-xs leading-5 text-slate-500">Manage competitive events and participants.</div>
            </a>

            <a href="{{ route('admin.puzzles.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-soft transition hover:-translate-y-1 hover:border-fuchsia-200 hover:shadow-lift">
                <div class="flex items-start justify-between">
                    <span class="h-11 w-11 rounded-2xl bg-fuchsia-50 text-fuchsia-600 flex items-center justify-center">
                        <i class="fa-solid fa-puzzle-piece"></i>
                    </span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-slate-300 group-hover:text-fuchsia-500"></i>
                </div>
                <div class="mt-4 text-sm font-extrabold text-slate-900">Puzzle library</div>
                <div class="mt-1 text-xs leading-5 text-slate-500">Keep tactics and learning content fresh.</div>
            </a>

            <a href="{{ route('admin.articles.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-soft transition hover:-translate-y-1 hover:border-rose-200 hover:shadow-lift">
                <div class="flex items-start justify-between">
                    <span class="h-11 w-11 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                        <i class="fa-solid fa-newspaper"></i>
                    </span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-slate-300 group-hover:text-rose-500"></i>
                </div>
                <div class="mt-4 text-sm font-extrabold text-slate-900">Manage articles</div>
                <div class="mt-1 text-xs leading-5 text-slate-500">Publish and translate site content.</div>
            </a>
        </div>
    </section>

    {{-- KPI grid --}}
    <section>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Total users</span>
                    <span class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="fa-solid fa-users"></i></span>
                </div>
                <div class="mt-4 flex items-end justify-between gap-3">
                    <div>
                        <div class="text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($stats['total_users']) }}</div>
                        <div class="mt-2 text-xs text-slate-400">{{ number_format($stats['new_users_week']) }} new this week</div>
                    </div>
                    <span class="{{ $userTrend['color'] }} {{ $userTrend['bg'] }} rounded-full px-2.5 py-1 text-[11px] font-extrabold">
                        <i class="fa-solid {{ $userTrend['icon'] }} mr-1"></i>{{ $userTrend['sign'] }}{{ $stats['user_growth_pct'] }}%
                    </span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Total matches</span>
                    <span class="h-10 w-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center"><i class="fa-solid fa-chess-knight"></i></span>
                </div>
                <div class="mt-4 flex items-end justify-between gap-3">
                    <div>
                        <div class="text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($stats['total_matches']) }}</div>
                        <div class="mt-2 text-xs text-slate-400">{{ number_format($stats['matches_today']) }} played today</div>
                    </div>
                    <span class="{{ $matchTrend['color'] }} {{ $matchTrend['bg'] }} rounded-full px-2.5 py-1 text-[11px] font-extrabold">
                        <i class="fa-solid {{ $matchTrend['icon'] }} mr-1"></i>{{ $matchTrend['sign'] }}{{ $stats['match_growth_pct'] }}%
                    </span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Active rooms</span>
                    <span class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fa-solid fa-gamepad"></i></span>
                </div>
                <div class="mt-4 flex items-end justify-between">
                    <div>
                        <div class="text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($activeRooms) }}</div>
                        <div class="mt-2 text-xs text-slate-400">{{ number_format($waitingRooms) }} waiting for opponent</div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-extrabold text-emerald-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Live
                    </span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Completion rate</span>
                    <span class="h-10 w-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center"><i class="fa-solid fa-flag-checkered"></i></span>
                </div>
                <div class="mt-4">
                    <div class="flex items-end justify-between">
                        <div class="text-3xl font-extrabold tracking-tight text-slate-900">{{ $completion }}%</div>
                        <span class="text-xs font-semibold text-slate-400">all rooms</span>
                    </div>
                    <div class="mt-4 h-2 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-indigo-500" style="width: {{ $completion }}%"></div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Tournaments</span>
                    <span class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center"><i class="fa-solid fa-trophy"></i></span>
                </div>
                <div class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($stats['total_tournaments']) }}</div>
                <div class="mt-2 text-xs text-slate-400">{{ number_format($stats['new_tournaments_month']) }} created this month</div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Puzzle library</span>
                    <span class="h-10 w-10 rounded-xl bg-fuchsia-50 text-fuchsia-600 flex items-center justify-center"><i class="fa-solid fa-puzzle-piece"></i></span>
                </div>
                <div class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($stats['total_puzzles']) }}</div>
                <div class="mt-2 text-xs text-slate-400">{{ number_format($stats['new_puzzles_month']) }} added this month</div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Articles</span>
                    <span class="h-10 w-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center"><i class="fa-solid fa-newspaper"></i></span>
                </div>
                <div class="mt-4 flex items-end justify-between gap-3">
                    <div>
                        <div class="text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($stats['total_articles']) }}</div>
                        <div class="mt-2 text-xs text-slate-400">{{ number_format($stats['new_articles_month']) }} added this month</div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="text-[11px] font-extrabold text-emerald-700">{{ number_format($stats['published_articles']) }} live</div>
                        <div class="text-[11px] font-extrabold text-slate-400">{{ number_format($stats['draft_articles']) }} draft</div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Avg. matches / day</span>
                    <span class="h-10 w-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center"><i class="fa-solid fa-chart-line"></i></span>
                </div>
                <div class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900">{{ $avgMatchesPerDay }}</div>
                <div class="mt-2 text-xs text-slate-400">14-day average</div>
            </div>

            <div class="rounded-2xl border {{ $staleRooms->count() > 0 ? 'border-rose-200 bg-rose-50/40' : 'border-slate-200 bg-white' }} p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wide {{ $staleRooms->count() > 0 ? 'text-rose-500' : 'text-slate-400' }}">Needs attention</span>
                    <span class="h-10 w-10 rounded-xl {{ $staleRooms->count() > 0 ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center"><i class="fa-solid fa-triangle-exclamation"></i></span>
                </div>
                <div class="mt-4 flex items-end justify-between">
                    <div>
                        <div class="text-3xl font-extrabold tracking-tight {{ $staleRooms->count() > 0 ? 'text-rose-700' : 'text-slate-900' }}">{{ $staleRooms->count() }}</div>
                        <div class="mt-2 text-xs {{ $staleRooms->count() > 0 ? 'text-rose-600' : 'text-slate-400' }}">rooms waiting 15+ min</div>
                    </div>
                    @if($staleRooms->count() > 0)
                        <a href="{{ route('admin.rooms.index') }}" class="text-xs font-extrabold text-rose-700 hover:text-rose-900">Inspect <i class="fa-solid fa-arrow-right ml-1"></i></a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Analytics --}}
    <section>
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-4">
            <div>
                <div class="text-[11px] font-extrabold uppercase tracking-[.18em] text-slate-400">Analytics</div>
                <h3 class="mt-1 text-lg font-extrabold text-slate-900">Performance at a glance</h3>
            </div>
            <div class="text-xs text-slate-400">Live data from the current admin overview</div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
            <div class="xl:col-span-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between gap-4 mb-5">
                    <div>
                        <h4 class="font-extrabold text-slate-900">User registrations</h4>
                        <p class="text-xs text-slate-400 mt-1">Monthly growth · last 6 months</p>
                    </div>
                    <span class="h-9 w-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="fa-solid fa-users"></i></span>
                </div>
                <div class="h-72"><canvas id="userGrowthChart"></canvas></div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between gap-4 mb-5">
                    <div>
                        <h4 class="font-extrabold text-slate-900">Room health</h4>
                        <p class="text-xs text-slate-400 mt-1">Current room distribution</p>
                    </div>
                    <span class="h-9 w-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fa-solid fa-chart-pie"></i></span>
                </div>
                <div class="h-72"><canvas id="roomStatusChart"></canvas></div>
            </div>

            <div class="xl:col-span-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between gap-4 mb-5">
                    <div>
                        <h4 class="font-extrabold text-slate-900">Match activity</h4>
                        <p class="text-xs text-slate-400 mt-1">Completed matches · last 14 days</p>
                    </div>
                    <span class="h-9 w-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center"><i class="fa-solid fa-chart-column"></i></span>
                </div>
                <div class="h-72"><canvas id="matchTrendChart"></canvas></div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between gap-4 mb-5">
                    <div>
                        <h4 class="font-extrabold text-slate-900">Tournament momentum</h4>
                        <p class="text-xs text-slate-400 mt-1">Created · last 6 months</p>
                    </div>
                    <span class="h-9 w-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center"><i class="fa-solid fa-trophy"></i></span>
                </div>
                <div class="h-72"><canvas id="tournamentGrowthChart"></canvas></div>
            </div>
        </div>
    </section>

    {{-- Operations --}}
    <section>
        <div class="flex items-center justify-between mb-4">
            <div>
                <div class="text-[11px] font-extrabold uppercase tracking-[.18em] text-slate-400">Operations</div>
                <h3 class="mt-1 text-lg font-extrabold text-slate-900">What needs your attention</h3>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
            <div class="xl:col-span-1 rounded-2xl border border-slate-200 bg-white shadow-soft overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h4 class="font-extrabold text-slate-900">Stale rooms</h4>
                        <p class="text-xs text-slate-400 mt-1">Waiting too long for an opponent</p>
                    </div>
                    <span class="h-9 w-9 rounded-xl {{ $staleRooms->count() ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center">
                        <i class="fa-solid {{ $staleRooms->count() ? 'fa-triangle-exclamation' : 'fa-check' }}"></i>
                    </span>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($staleRooms as $room)
                        <div class="px-5 py-4 flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="h-7 w-7 rounded-lg bg-slate-100 flex items-center justify-center text-xs text-slate-500"><i class="fa-solid fa-door-open"></i></span>
                                    <span class="font-mono text-sm font-bold text-slate-900">{{ $room->name }}</span>
                                </div>
                                <div class="mt-1 text-xs text-slate-400 truncate">Host: {{ $room->host ? $room->host->name : 'Unknown' }}</div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="rounded-full bg-rose-50 text-rose-700 px-2 py-1 text-[10px] font-extrabold">{{ $room->created_at->diffForHumans(null, true) }}</div>
                                <a href="{{ route('admin.rooms.edit', $room) }}" class="mt-1 inline-block text-xs font-bold text-indigo-600 hover:text-indigo-800">Inspect</a>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center">
                            <div class="mx-auto h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div class="mt-3 text-sm font-bold text-slate-800">All clear</div>
                            <div class="mt-1 text-xs text-slate-400">No stale waiting rooms right now.</div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="xl:col-span-2 rounded-2xl border border-slate-200 bg-white shadow-soft overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h4 class="font-extrabold text-slate-900">Recent activity</h4>
                        <p class="text-xs text-slate-400 mt-1">Newest registrations and rooms</p>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="text-xs font-extrabold text-indigo-600 hover:text-indigo-800">View users</a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 lg:divide-x divide-slate-100">
                    <div>
                        <div class="px-5 py-3 bg-slate-50/70 text-[10px] uppercase tracking-[.16em] font-extrabold text-slate-400">New players</div>
                        <div class="divide-y divide-slate-100">
                            @forelse($recentUsers as $user)
                                <div class="px-5 py-3.5 flex items-center gap-3">
                                    <img src="{{ $user->getAvatarUrl() }}" class="w-9 h-9 rounded-xl object-cover border border-slate-200" alt="">
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-bold text-slate-800 truncate">{{ $user->name }}</div>
                                        <div class="text-xs text-slate-400 truncate">{{ $user->email }}</div>
                                    </div>
                                    <div class="text-[10px] font-semibold text-slate-400 shrink-0">{{ $user->created_at->diffForHumans() }}</div>
                                </div>
                            @empty
                                <div class="px-5 py-8 text-center text-xs text-slate-400">No recent users found.</div>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <div class="px-5 py-3 bg-slate-50/70 text-[10px] uppercase tracking-[.16em] font-extrabold text-slate-400">Recent rooms</div>
                        <div class="divide-y divide-slate-100">
                            @forelse($recentRooms as $room)
                                @php
                                    $status = $room->result
                                        ? ['label' => 'Finished', 'class' => 'bg-slate-100 text-slate-600', 'icon' => 'fa-flag-checkered']
                                        : ($room->guest_id
                                            ? ['label' => 'Playing', 'class' => 'bg-emerald-50 text-emerald-700', 'icon' => 'fa-bolt']
                                            : ['label' => 'Waiting', 'class' => 'bg-amber-50 text-amber-700', 'icon' => 'fa-hourglass-half']);
                                @endphp
                                <div class="px-5 py-3.5 flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500">
                                        <i class="fa-solid fa-door-open text-sm"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-sm font-bold text-slate-800">{{ $room->name }}</span>
                                            <span class="rounded-full px-2 py-0.5 text-[10px] font-extrabold {{ $status['class'] }}">
                                                <i class="fa-solid {{ $status['icon'] }} mr-1"></i>{{ $status['label'] }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-slate-400 mt-1 truncate">
                                            {{ $room->host ? $room->host->name : 'Unknown' }}
                                        </div>
                                    </div>
                                    <div class="text-[10px] font-semibold text-slate-400 shrink-0">{{ optional($room->modified_at)->diffForHumans() ?? '—' }}</div>
                                </div>
                            @empty
                                <div class="px-5 py-8 text-center text-xs text-slate-400">No recent rooms found.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Content --}}
    <section>
        <div class="flex items-center justify-between mb-4">
            <div>
                <div class="text-[11px] font-extrabold uppercase tracking-[.18em] text-slate-400">Content</div>
                <h3 class="mt-1 text-lg font-extrabold text-slate-900">Latest articles</h3>
            </div>
            <a href="{{ route('admin.articles.index') }}" class="text-xs font-extrabold text-indigo-600 hover:text-indigo-800">View all <i class="fa-solid fa-arrow-right ml-1"></i></a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-soft overflow-hidden">
            <div class="divide-y divide-slate-100">
                @forelse($recentArticles as $article)
                    <div class="px-5 py-3.5 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600 shrink-0">
                            <i class="fa-solid fa-newspaper text-sm"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-slate-800 truncate">{{ $article->title ?? '(No translation yet)' }}</span>
                                @if($article->status == 'published')
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-extrabold bg-emerald-50 text-emerald-700 shrink-0">Published</span>
                                @else
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-extrabold bg-slate-100 text-slate-600 shrink-0">Draft</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-400 mt-1">{{ number_format($article->views) }} views</div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <div class="text-[10px] font-semibold text-slate-400">{{ $article->created_at->diffForHumans() }}</div>
                            <a href="{{ route('admin.articles.edit', $article->id) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Edit</a>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center">
                        <div class="mx-auto h-12 w-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                            <i class="fa-solid fa-newspaper"></i>
                        </div>
                        <div class="mt-3 text-sm font-bold text-slate-800">No articles yet</div>
                        <div class="mt-1 text-xs text-slate-400">Create your first article to see it here.</div>
                        <a href="{{ route('admin.articles.create') }}" class="mt-3 inline-block text-xs font-extrabold text-indigo-600 hover:text-indigo-800">Add article <i class="fa-solid fa-arrow-right ml-1"></i></a>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const common = {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 700, easing: 'easeOutQuart' },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0f172a',
                titleColor: '#fff',
                bodyColor: '#cbd5e1',
                padding: 12,
                cornerRadius: 10,
                displayColors: false
            }
        },
        scales: {
            x: {
                grid: { display: false },
                border: { display: false },
                ticks: { color: '#94a3b8', font: { size: 11 } }
            },
            y: {
                beginAtZero: true,
                border: { display: false },
                grid: { color: 'rgba(148,163,184,.12)' },
                ticks: { color: '#94a3b8', font: { size: 11 }, precision: 0 }
            }
        }
    };

    const makeGradient = (ctx, top, bottom) => {
        const gradient = ctx.createLinearGradient(0, 0, 0, 280);
        gradient.addColorStop(0, top);
        gradient.addColorStop(1, bottom);
        return gradient;
    };

    const userEl = document.getElementById('userGrowthChart');
    if (userEl) {
        const ctx = userEl.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($userGrowth->pluck('month')) !!},
                datasets: [{
                    label: 'New registrations',
                    data: {!! json_encode($userGrowth->pluck('count')) !!},
                    borderColor: '#6366f1',
                    backgroundColor: makeGradient(ctx, 'rgba(99,102,241,.23)', 'rgba(99,102,241,0)'),
                    fill: true,
                    tension: .42,
                    borderWidth: 3,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#6366f1',
                    pointBorderWidth: 2
                }]
            },
            options: common
        });
    }

    const matchEl = document.getElementById('matchTrendChart');
    if (matchEl) {
        new Chart(matchEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($last14Days->pluck('date')) !!},
                datasets: [{
                    label: 'Matches played',
                    data: {!! json_encode($last14Days->pluck('count')) !!},
                    backgroundColor: '#38bdf8',
                    hoverBackgroundColor: '#0284c7',
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 26
                }]
            },
            options: {
                ...common,
                scales: {
                    ...common.scales,
                    x: { ...common.scales.x, ticks: { ...common.scales.x.ticks, maxRotation: 0, autoSkip: true, maxTicksLimit: 7 } }
                }
            }
        });
    }

    const roomEl = document.getElementById('roomStatusChart');
    if (roomEl) {
        new Chart(roomEl.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Playing', 'Finished', 'Waiting'],
                datasets: [{
                    data: [
                        {{ $roomDistribution['ongoing'] }},
                        {{ $roomDistribution['finished'] }},
                        {{ $roomDistribution['waiting'] }}
                    ],
                    backgroundColor: ['#10b981', '#94a3b8', '#f59e0b'],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: { usePointStyle: true, pointStyle: 'circle', padding: 18, color: '#64748b', font: { size: 11, weight: 600 } }
                    },
                    tooltip: common.plugins.tooltip
                }
            }
        });
    }

    const tournamentEl = document.getElementById('tournamentGrowthChart');
    if (tournamentEl) {
        new Chart(tournamentEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($tournamentGrowth->pluck('month')) !!},
                datasets: [{
                    label: 'Tournaments created',
                    data: {!! json_encode($tournamentGrowth->pluck('count')) !!},
                    backgroundColor: '#fbbf24',
                    hoverBackgroundColor: '#d97706',
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 32
                }]
            },
            options: common
        });
    }
});
</script>
@endpush
