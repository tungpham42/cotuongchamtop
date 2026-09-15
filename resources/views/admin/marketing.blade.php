{{--
    resources/views/admin/marketing.blade.php

    Marketing Metrics & Funnels — Admin
    ------------------------------------
    Drop this in resources/views/admin/marketing.blade.php.
    It extends the same layouts.admin used by the rest of the admin
    console, so the sidebar / header / Chart.js include all come for free.

    EXPECTED VARIABLES (pass these from your controller — see the
    MarketingController@index sketch at the bottom of this comment):

    $stats = [
        'total_users'          => int,   // signups, all time
        'new_users_week'       => int,
        'user_growth_pct'      => float, // WoW signup growth

        'activated_users'      => int,   // users who have played >= 1 match
        'activation_rate'      => float, // activated_users / total_users * 100

        'engaged_users'        => int,   // users with >= 3 karma events (repeat play)
        'engagement_rate'      => float,

        'retained_users'       => int,   // last_seen_at within 7 days
        'retention_rate'       => float,

        'converted_users'      => int,   // active paid subscription_plan = 'standard'
        'conversion_rate'      => float,

        'total_articles'       => int,
        'published_articles'   => int,
        'draft_articles'       => int,
        'total_article_views'  => int,
        'avg_article_views'    => float,

        'total_games'          => int,
        'total_game_views'     => int,
        'avg_game_views'       => float,
    ];

    $funnel = [
        ['key' => 'registered', 'label' => 'Registered',        'value' => 5000, 'icon' => 'fa-user-plus',     'color' => 'indigo'],
        ['key' => 'activated',  'label' => 'Activated (1st match)', 'value' => 3120, 'icon' => 'fa-chess-board', 'color' => 'sky'],
        ['key' => 'engaged',    'label' => 'Engaged (3+ sessions)', 'value' => 1780, 'icon' => 'fa-fire',        'color' => 'amber'],
        ['key' => 'retained',   'label' => 'Retained (7-day)',  'value' => 940,  'icon' => 'fa-heart-pulse',    'color' => 'emerald'],
        ['key' => 'converted',  'label' => 'Converted (Paid)',  'value' => 212,  'icon' => 'fa-crown',          'color' => 'violet'],
    ];

    $signupTrend        = collect(); // ->pluck('month'), ->pluck('count') — same shape as AdminController's $userGrowth
    $contentViewsByType = ['articles' => 0, 'games' => 0];
    $retentionSnapshot  = ['retained' => 0, 'not_retained' => 0];
    $topArticles        = collect(); // Article::with('translation')->orderByDesc('views')->take(5)->get()
    $topGames           = collect(); // Game::with('user')->orderByDesc('views')->take(5)->get()
--}}
@extends('layouts.admin')

@section('title', 'Marketing Metrics')

@section('content')

@php
    $trend = function ($pct) {
        if ($pct > 0) return ['color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'icon' => 'fa-arrow-up', 'sign' => '+'];
        if ($pct < 0) return ['color' => 'text-rose-700', 'bg' => 'bg-rose-50', 'icon' => 'fa-arrow-down', 'sign' => ''];
        return ['color' => 'text-slate-500', 'bg' => 'bg-slate-100', 'icon' => 'fa-minus', 'sign' => ''];
    };
    $userTrend = $trend($stats['user_growth_pct'] ?? 0);

    $funnelMax   = collect($funnel)->max('value') ?: 1;
    $funnelTotal = collect($funnel)->first()['value'] ?? 1;

    $activationRate = round($stats['activation_rate'] ?? 0, 1);
    $engagementRate = round($stats['engagement_rate'] ?? 0, 1);
    $retentionRate  = round($stats['retention_rate'] ?? 0, 1);
    $conversionRate = round($stats['conversion_rate'] ?? 0, 1);
@endphp

<div class="space-y-8">

    {{-- Hero / marketing pulse --}}
    <section class="relative overflow-hidden rounded-[28px] bg-slate-950 text-white shadow-lift">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_15%_20%,rgba(99,102,241,.38),transparent_28%),radial-gradient(circle_at_90%_5%,rgba(14,165,233,.30),transparent_26%)]"></div>
        <div class="relative p-6 sm:p-8 lg:p-10">
            <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-7">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-[.18em] text-indigo-100">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        Marketing pulse
                    </div>
                    <h2 class="mt-5 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-[1.05]">
                        From first click
                        <span class="text-indigo-300">to paying player.</span>
                    </h2>
                    <p class="mt-4 max-w-xl text-sm sm:text-base leading-7 text-slate-300">
                        Where people drop off between signing up and sticking around — and which content is actually pulling its weight.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:min-w-[360px]">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <div class="text-xs font-semibold text-slate-400">Activation rate</div>
                        <div class="mt-1 text-2xl font-extrabold">{{ $activationRate }}%</div>
                        <div class="mt-1 text-xs text-sky-300"><i class="fa-solid fa-chess-board mr-1"></i>Played 1st match</div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <div class="text-xs font-semibold text-slate-400">Retention (7d)</div>
                        <div class="mt-1 text-2xl font-extrabold">{{ $retentionRate }}%</div>
                        <div class="mt-1 text-xs text-emerald-300"><i class="fa-solid fa-heart-pulse mr-1"></i>Still active</div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <div class="text-xs font-semibold text-slate-400">Conversion rate</div>
                        <div class="mt-1 text-2xl font-extrabold">{{ $conversionRate }}%</div>
                        <div class="mt-1 text-xs text-violet-300"><i class="fa-solid fa-crown mr-1"></i>Free → paid</div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <div class="text-xs font-semibold text-slate-400">New signups</div>
                        <div class="mt-1 text-2xl font-extrabold">{{ number_format($stats['new_users_week'] ?? 0) }}</div>
                        <div class="mt-1 text-xs text-indigo-300"><i class="fa-solid fa-arrow-trend-up mr-1"></i>Last 7 days</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- KPI grid --}}
    <section>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Total signups</span>
                    <span class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="fa-solid fa-user-plus"></i></span>
                </div>
                <div class="mt-4 flex items-end justify-between gap-3">
                    <div>
                        <div class="text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($stats['total_users'] ?? 0) }}</div>
                        <div class="mt-2 text-xs text-slate-400">{{ number_format($stats['new_users_week'] ?? 0) }} new this week</div>
                    </div>
                    <span class="{{ $userTrend['color'] }} {{ $userTrend['bg'] }} rounded-full px-2.5 py-1 text-[11px] font-extrabold">
                        <i class="fa-solid {{ $userTrend['icon'] }} mr-1"></i>{{ $userTrend['sign'] }}{{ $stats['user_growth_pct'] ?? 0 }}%
                    </span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Activation rate</span>
                    <span class="h-10 w-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center"><i class="fa-solid fa-chess-board"></i></span>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-extrabold tracking-tight text-slate-900">{{ $activationRate }}%</div>
                    <div class="mt-2 text-xs text-slate-400">{{ number_format($stats['activated_users'] ?? 0) }} played a first match</div>
                    <div class="mt-4 h-2 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-indigo-500" style="width: {{ $activationRate }}%"></div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Retention (7-day)</span>
                    <span class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fa-solid fa-heart-pulse"></i></span>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-extrabold tracking-tight text-slate-900">{{ $retentionRate }}%</div>
                    <div class="mt-2 text-xs text-slate-400">{{ number_format($stats['retained_users'] ?? 0) }} active in the last 7 days</div>
                    <div class="mt-4 h-2 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500" style="width: {{ $retentionRate }}%"></div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Conversion rate</span>
                    <span class="h-10 w-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center"><i class="fa-solid fa-crown"></i></span>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-extrabold tracking-tight text-slate-900">{{ $conversionRate }}%</div>
                    <div class="mt-2 text-xs text-slate-400">{{ number_format($stats['converted_users'] ?? 0) }} on a paid plan</div>
                    <div class="mt-4 h-2 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-fuchsia-500" style="width: {{ $conversionRate }}%"></div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Engagement rate</span>
                    <span class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center"><i class="fa-solid fa-fire"></i></span>
                </div>
                <div class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900">{{ $engagementRate }}%</div>
                <div class="mt-2 text-xs text-slate-400">{{ number_format($stats['engaged_users'] ?? 0) }} users with 3+ sessions</div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Article reach</span>
                    <span class="h-10 w-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center"><i class="fa-solid fa-newspaper"></i></span>
                </div>
                <div class="mt-4 flex items-end justify-between gap-3">
                    <div>
                        <div class="text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($stats['total_article_views'] ?? 0) }}</div>
                        <div class="mt-2 text-xs text-slate-400">{{ number_format($stats['avg_article_views'] ?? 0, 1) }} avg views / article</div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="text-[11px] font-extrabold text-emerald-700">{{ number_format($stats['published_articles'] ?? 0) }} live</div>
                        <div class="text-[11px] font-extrabold text-slate-400">{{ number_format($stats['draft_articles'] ?? 0) }} draft</div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Game reach</span>
                    <span class="h-10 w-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center"><i class="fa-solid fa-book-open"></i></span>
                </div>
                <div class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($stats['total_game_views'] ?? 0) }}</div>
                <div class="mt-2 text-xs text-slate-400">{{ number_format($stats['avg_game_views'] ?? 0, 1) }} avg views / saved game</div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Drop-off, top of funnel</span>
                    <span class="h-10 w-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center"><i class="fa-solid fa-filter-circle-xmark"></i></span>
                </div>
                @php
                    $topDrop = $funnelTotal > 0 && isset($funnel[1]) ? round((($funnel[0]['value'] - $funnel[1]['value']) / $funnelTotal) * 100, 1) : 0;
                @endphp
                <div class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900">{{ $topDrop }}%</div>
                <div class="mt-2 text-xs text-slate-400">Registered but never played a match</div>
            </div>

        </div>
    </section>

    {{-- Funnel --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-soft">
        <div class="flex items-center justify-between mb-6">
            <div>
                <div class="text-[11px] font-extrabold uppercase tracking-[.18em] text-slate-400">Marketing funnel</div>
                <h3 class="mt-1 text-lg font-extrabold text-slate-900">Signup → activated → engaged → retained → paid</h3>
            </div>
            <div class="text-xs text-slate-400">% of registered users</div>
        </div>

        <div class="space-y-2">
            @foreach ($funnel as $i => $stage)
                @php
                    $widthPct = $funnelMax > 0 ? max(6, round(($stage['value'] / $funnelMax) * 100, 1)) : 6;
                    $pctOfTotal = $funnelTotal > 0 ? round(($stage['value'] / $funnelTotal) * 100, 1) : 0;
                    $prev = $funnel[$i - 1] ?? null;
                    $dropCount = $prev ? $prev['value'] - $stage['value'] : 0;
                    $dropPct = ($prev && $prev['value'] > 0) ? round(($dropCount / $prev['value']) * 100, 1) : 0;
                @endphp

                @if ($i > 0)
                    <div class="flex items-center gap-2 pl-2 py-1 text-[11px] font-semibold text-rose-500">
                        <i class="fa-solid fa-arrow-turn-down"></i>
                        <span>-{{ number_format($dropCount) }} users ({{ $dropPct }}%) didn't make it to "{{ $stage['label'] }}"</span>
                    </div>
                @endif

                <div class="flex items-center gap-4">
                    <div class="w-40 sm:w-56 shrink-0 flex items-center gap-2.5">
                        <span class="h-8 w-8 rounded-lg bg-{{ $stage['color'] }}-50 text-{{ $stage['color'] }}-600 flex items-center justify-center text-xs">
                            <i class="fa-solid {{ $stage['icon'] }}"></i>
                        </span>
                        <span class="text-sm font-bold text-slate-700 truncate">{{ $stage['label'] }}</span>
                    </div>
                    <div class="flex-1 h-9 rounded-lg bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-lg bg-gradient-to-r from-{{ $stage['color'] }}-500 to-{{ $stage['color'] }}-400 flex items-center justify-end px-3 transition-all"
                             style="width: {{ $widthPct }}%">
                            <span class="text-[11px] font-extrabold text-white drop-shadow">{{ number_format($stage['value']) }}</span>
                        </div>
                    </div>
                    <div class="w-16 shrink-0 text-right text-sm font-extrabold text-slate-500">{{ $pctOfTotal }}%</div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Charts --}}
    <section>
        <div class="flex items-center justify-between mb-4">
            <div>
                <div class="text-[11px] font-extrabold uppercase tracking-[.18em] text-slate-400">Trends</div>
                <h3 class="mt-1 text-lg font-extrabold text-slate-900">Acquisition & content performance</h3>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
            <div class="xl:col-span-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between gap-4 mb-5">
                    <div>
                        <h4 class="font-extrabold text-slate-900">Signup trend</h4>
                        <p class="text-xs text-slate-400 mt-1">New registrations · last 6 months</p>
                    </div>
                    <span class="h-9 w-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="fa-solid fa-arrow-trend-up"></i></span>
                </div>
                <div class="h-72"><canvas id="signupTrendChart"></canvas></div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between gap-4 mb-5">
                    <div>
                        <h4 class="font-extrabold text-slate-900">7-day retention</h4>
                        <p class="text-xs text-slate-400 mt-1">Of activated users</p>
                    </div>
                    <span class="h-9 w-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fa-solid fa-heart-pulse"></i></span>
                </div>
                <div class="h-72"><canvas id="retentionChart"></canvas></div>
            </div>

            <div class="xl:col-span-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between gap-4 mb-5">
                    <div>
                        <h4 class="font-extrabold text-slate-900">Content views by type</h4>
                        <p class="text-xs text-slate-400 mt-1">Total views, articles vs. saved games</p>
                    </div>
                    <span class="h-9 w-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center"><i class="fa-solid fa-eye"></i></span>
                </div>
                <div class="h-56"><canvas id="contentViewsChart"></canvas></div>
            </div>
        </div>
    </section>

    {{-- Content performance --}}
    <section>
        <div class="flex items-center justify-between mb-4">
            <div>
                <div class="text-[11px] font-extrabold uppercase tracking-[.18em] text-slate-400">Content performance</div>
                <h3 class="mt-1 text-lg font-extrabold text-slate-900">What's actually working</h3>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-soft overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h4 class="font-extrabold text-slate-900 text-sm">Top articles</h4>
                    <span class="text-xs text-slate-400">By views</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($topArticles as $article)
                        <div class="px-5 py-3.5 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-slate-800 truncate">{{ $article->translation->title ?? '(no translation)' }}</div>
                                <div class="mt-0.5 text-xs text-slate-400">
                                    <span class="{{ $article->status === 'published' ? 'text-emerald-600' : 'text-amber-600' }} font-bold">{{ ucfirst($article->status) }}</span>
                                    · {{ $article->created_at?->format('M d, Y') }}
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                <div class="text-sm font-extrabold text-slate-900">{{ number_format($article->views) }}</div>
                                <div class="text-[11px] text-slate-400">views</div>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-slate-400">No articles yet.</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-soft overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h4 class="font-extrabold text-slate-900 text-sm">Top saved games</h4>
                    <span class="text-xs text-slate-400">By views</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($topGames as $game)
                        <div class="px-5 py-3.5 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-slate-800 truncate">{{ $game->title }}</div>
                                <div class="mt-0.5 text-xs text-slate-400">by {{ $game->user->name ?? 'Unknown' }}</div>
                            </div>
                            <div class="shrink-0 text-right">
                                <div class="text-sm font-extrabold text-slate-900">{{ number_format($game->views) }}</div>
                                <div class="text-[11px] text-slate-400">views</div>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-slate-400">No saved games yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- Insights & recommendations --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-soft">
        <div class="flex items-center justify-between mb-5">
            <div>
                <div class="text-[11px] font-extrabold uppercase tracking-[.18em] text-slate-400">Insights</div>
                <h3 class="mt-1 text-lg font-extrabold text-slate-900">Where to focus next</h3>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if ($activationRate < 50)
                <div class="rounded-xl border border-sky-100 bg-sky-50/70 p-4 flex gap-3">
                    <span class="h-9 w-9 shrink-0 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center"><i class="fa-solid fa-chess-board"></i></span>
                    <div>
                        <div class="text-sm font-extrabold text-slate-800">Activation is the biggest leak</div>
                        <p class="text-xs text-slate-500 mt-1">Only {{ $activationRate }}% of signups ever play a match. Try a forced first-match prompt or a matchmaking nudge right after registration.</p>
                    </div>
                </div>
            @endif

            @if ($retentionRate < 30)
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/70 p-4 flex gap-3">
                    <span class="h-9 w-9 shrink-0 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center"><i class="fa-solid fa-heart-pulse"></i></span>
                    <div>
                        <div class="text-sm font-extrabold text-slate-800">Weak 7-day retention</div>
                        <p class="text-xs text-slate-500 mt-1">{{ $retentionRate }}% of activated users are still active a week later. Consider a return-visit email or karma bonus for logging back in.</p>
                    </div>
                </div>
            @endif

            @if ($conversionRate < 5)
                <div class="rounded-xl border border-violet-100 bg-violet-50/70 p-4 flex gap-3">
                    <span class="h-9 w-9 shrink-0 rounded-lg bg-violet-100 text-violet-600 flex items-center justify-center"><i class="fa-solid fa-crown"></i></span>
                    <div>
                        <div class="text-sm font-extrabold text-slate-800">Low free-to-paid conversion</div>
                        <p class="text-xs text-slate-500 mt-1">Just {{ $conversionRate }}% of users are on a paid plan. Surface the "remove ads" benefit closer to the moment they hit an ad, not just in settings.</p>
                    </div>
                </div>
            @endif

            <div class="rounded-xl border border-amber-100 bg-amber-50/70 p-4 flex gap-3">
                <span class="h-9 w-9 shrink-0 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center"><i class="fa-solid fa-circle-info"></i></span>
                <div>
                    <div class="text-sm font-extrabold text-slate-800">No acquisition-channel tracking yet</div>
                    <p class="text-xs text-slate-500 mt-1">The users table has no UTM/referrer column, so this page can't break signups down by channel. Add a `signup_source` column (or a landing-page UTM capture) to unlock that view.</p>
                </div>
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

    const signupEl = document.getElementById('signupTrendChart');
    if (signupEl) {
        const ctx = signupEl.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($signupTrend->pluck('month')) !!},
                datasets: [{
                    label: 'New signups',
                    data: {!! json_encode($signupTrend->pluck('count')) !!},
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

    const retentionEl = document.getElementById('retentionChart');
    if (retentionEl) {
        new Chart(retentionEl.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Retained', 'Not retained'],
                datasets: [{
                    data: [
                        {{ $retentionSnapshot['retained'] ?? 0 }},
                        {{ $retentionSnapshot['not_retained'] ?? 0 }}
                    ],
                    backgroundColor: ['#10b981', '#e2e8f0'],
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

    const contentEl = document.getElementById('contentViewsChart');
    if (contentEl) {
        new Chart(contentEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Articles', 'Saved games'],
                datasets: [{
                    label: 'Total views',
                    data: [
                        {{ $contentViewsByType['articles'] ?? 0 }},
                        {{ $contentViewsByType['games'] ?? 0 }}
                    ],
                    backgroundColor: ['#fb7185', '#2dd4bf'],
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 60
                }]
            },
            options: {
                ...common,
                indexAxis: 'y'
            }
        });
    }
});
</script>
@endpush
