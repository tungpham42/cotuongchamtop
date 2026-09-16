@extends('admin')

@section('title', 'Marketing Reports')

@section('content')
<div id="marketingApp" class="space-y-6" data-start="{{ $startDate }}" data-end="{{ $endDate }}" data-preset="{{ $preset }}">

    @if ($report['error'])
        <div class="rounded-2xl border border-rose-200 bg-rose-50/90 px-4 py-3.5 text-rose-800 shadow-sm flex items-center gap-3">
            <span class="h-9 w-9 rounded-xl bg-rose-100 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </span>
            <span class="text-sm font-semibold">{{ $report['error'] }}</span>
        </div>
    @endif

    {{-- Date range controls --}}
    <div class="fade-up flex flex-wrap items-center justify-between gap-4 bg-white rounded-2xl shadow-soft border border-slate-100 px-5 py-4">
        <div>
            <div class="text-sm font-bold text-slate-800">Reporting period</div>
            <div class="text-xs text-slate-400" id="rangeLabel">{{ $startDate }} &rarr; {{ $endDate }}</div>
        </div>
        <div class="flex items-center gap-2" id="rangeButtons">
            @foreach ($presets as $p)
                <button type="button" data-range="{{ $p }}"
                        class="range-btn px-4 py-2 rounded-xl text-sm font-semibold border transition
                        {{ $preset === $p ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                    {{ strtoupper($p) }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Overview cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4" id="overviewCards">
        @php
            $cards = [
                ['key' => 'sessions', 'label' => 'Sessions', 'icon' => 'fa-arrow-trend-up', 'color' => 'indigo'],
                ['key' => 'active_users', 'label' => 'Active Users', 'icon' => 'fa-users', 'color' => 'sky'],
                ['key' => 'new_users', 'label' => 'New Users', 'icon' => 'fa-user-plus', 'color' => 'emerald'],
                ['key' => 'engagement_rate', 'label' => 'Engagement', 'icon' => 'fa-bolt', 'color' => 'amber', 'suffix' => '%'],
                ['key' => 'page_views', 'label' => 'Page Views', 'icon' => 'fa-eye', 'color' => 'fuchsia'],
                ['key' => 'conversions', 'label' => 'Conversions', 'icon' => 'fa-flag-checkered', 'color' => 'rose'],
            ];
        @endphp
        @foreach ($cards as $c)
            <div class="fade-up bg-white rounded-2xl shadow-soft border border-slate-100 p-4">
                <div class="w-9 h-9 rounded-xl bg-{{ $c['color'] }}-500/10 text-{{ $c['color'] }}-600 flex items-center justify-center mb-3">
                    <i class="fa-solid {{ $c['icon'] }} text-sm"></i>
                </div>
                <div class="text-2xl font-extrabold text-slate-900 leading-tight" data-metric="{{ $c['key'] }}">
                    {{ number_format($report['overview'][$c['key']]) }}{{ $c['suffix'] ?? '' }}
                </div>
                <div class="text-xs font-semibold text-slate-400 mt-1">{{ $c['label'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Marketing funnel: TOFU / MOFU / BOFU --}}
    <div class="fade-up bg-white rounded-2xl shadow-soft border border-slate-100 p-6" id="funnelCard">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-sm font-extrabold text-slate-800">Marketing Funnel</h2>
            <span class="text-xs font-semibold text-slate-400">TOFU &rarr; MOFU &rarr; BOFU</span>
        </div>
        <p class="text-xs text-slate-400 mb-6">Awareness, engagement, and conversion for this period.</p>

        <div class="flex flex-col items-center gap-2 max-w-xl mx-auto" id="funnelStages">
            {{-- populated by renderFunnel() in JS, including on initial load --}}
        </div>
    </div>

    {{-- Sessions/users trend + traffic sources --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 fade-up bg-white rounded-2xl shadow-soft border border-slate-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-extrabold text-slate-800">Sessions &amp; Active Users</h2>
            </div>
            <canvas id="trendChart" height="110"></canvas>
        </div>

        <div class="fade-up bg-white rounded-2xl shadow-soft border border-slate-100 p-5">
            <h2 class="text-sm font-extrabold text-slate-800 mb-4">Traffic by Channel</h2>
            <canvas id="channelChart" height="220"></canvas>
        </div>
    </div>

    {{-- Devices + top pages --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="fade-up bg-white rounded-2xl shadow-soft border border-slate-100 p-5">
            <h2 class="text-sm font-extrabold text-slate-800 mb-4">Devices</h2>
            <canvas id="deviceChart" height="220"></canvas>
        </div>

        <div class="xl:col-span-2 fade-up bg-white rounded-2xl shadow-soft border border-slate-100 p-5">
            <h2 class="text-sm font-extrabold text-slate-800 mb-4">Top Pages</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-bold uppercase tracking-wide text-slate-400 border-b border-slate-100">
                            <th class="py-2 pr-4">Page</th>
                            <th class="py-2 pr-4">Views</th>
                            <th class="py-2 pr-4">Sessions</th>
                            <th class="py-2">Avg. Duration</th>
                        </tr>
                    </thead>
                    <tbody id="topPagesBody" class="divide-y divide-slate-50">
                        @forelse ($report['top_pages'] as $page)
                            <tr>
                                <td class="py-2.5 pr-4 font-medium text-slate-700 truncate max-w-[280px]">{{ $page['path'] }}</td>
                                <td class="py-2.5 pr-4 text-slate-600">{{ number_format($page['views']) }}</td>
                                <td class="py-2.5 pr-4 text-slate-600">{{ number_format($page['sessions']) }}</td>
                                <td class="py-2.5 text-slate-600">{{ gmdate('i:s', (int) $page['avg_duration']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-slate-400">No data for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Top countries --}}
    <div class="fade-up bg-white rounded-2xl shadow-soft border border-slate-100 p-5">
        <h2 class="text-sm font-extrabold text-slate-800 mb-4">Top Countries</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3" id="countriesGrid">
            @forelse ($report['countries'] as $country)
                <div class="rounded-xl border border-slate-100 bg-slate-50/60 px-3 py-2.5">
                    <div class="text-xs font-bold text-slate-500">{{ $country['country'] }}</div>
                    <div class="text-lg font-extrabold text-slate-900">{{ number_format($country['users']) }}</div>
                </div>
            @empty
                <div class="text-sm text-slate-400">No data for this period.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const app = document.getElementById('marketingApp');
    const dataUrl = @json(route('admin.marketing.data'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    const palette = {
        indigo: '#6366f1', sky: '#0ea5e9', emerald: '#10b981',
        amber: '#f59e0b', fuchsia: '#d946ef', rose: '#f43f5e', slate: '#94a3b8',
    };

    let trendChart, channelChart, deviceChart;

    function buildTrendChart(ts) {
        const ctx = document.getElementById('trendChart');
        const data = {
            labels: ts.labels,
            datasets: [
                { label: 'Sessions', data: ts.sessions, borderColor: palette.indigo, backgroundColor: 'transparent', tension: 0.35 },
                { label: 'Active Users', data: ts.users, borderColor: palette.sky, backgroundColor: 'transparent', tension: 0.35 },
            ],
        };
        if (trendChart) { trendChart.data = data; trendChart.update(); return; }
        trendChart = new Chart(ctx, {
            type: 'line',
            data,
            options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } },
        });
    }

    function buildChannelChart(rows) {
        const ctx = document.getElementById('channelChart');
        const colors = [palette.indigo, palette.sky, palette.emerald, palette.amber, palette.fuchsia, palette.rose, palette.slate];
        const data = {
            labels: rows.map(r => r.channel || 'Unassigned'),
            datasets: [{ data: rows.map(r => r.sessions), backgroundColor: colors }],
        };
        if (channelChart) { channelChart.data = data; channelChart.update(); return; }
        channelChart = new Chart(ctx, { type: 'doughnut', data, options: { plugins: { legend: { position: 'bottom' } } } });
    }

    function buildDeviceChart(rows) {
        const ctx = document.getElementById('deviceChart');
        const colors = [palette.indigo, palette.sky, palette.emerald];
        const data = {
            labels: rows.map(r => r.device),
            datasets: [{ data: rows.map(r => r.users), backgroundColor: colors }],
        };
        if (deviceChart) { deviceChart.data = data; deviceChart.update(); return; }
        deviceChart = new Chart(ctx, { type: 'pie', data, options: { plugins: { legend: { position: 'bottom' } } } });
    }

    function renderOverview(overview) {
        document.querySelectorAll('[data-metric]').forEach(el => {
            const key = el.dataset.metric;
            const suffix = key === 'engagement_rate' ? '%' : '';
            el.textContent = Number(overview[key] ?? 0).toLocaleString() + suffix;
        });
    }

    function renderTopPages(pages) {
        const body = document.getElementById('topPagesBody');
        if (!pages.length) {
            body.innerHTML = '<tr><td colspan="4" class="py-4 text-center text-slate-400">No data for this period.</td></tr>';
            return;
        }
        body.innerHTML = pages.map(p => {
            const mins = Math.floor(p.avg_duration / 60).toString().padStart(2, '0');
            const secs = Math.floor(p.avg_duration % 60).toString().padStart(2, '0');
            return `<tr>
                <td class="py-2.5 pr-4 font-medium text-slate-700 truncate max-w-[280px]">${p.path}</td>
                <td class="py-2.5 pr-4 text-slate-600">${Number(p.views).toLocaleString()}</td>
                <td class="py-2.5 pr-4 text-slate-600">${Number(p.sessions).toLocaleString()}</td>
                <td class="py-2.5 text-slate-600">${mins}:${secs}</td>
            </tr>`;
        }).join('');
    }

    function renderCountries(countries) {
        const grid = document.getElementById('countriesGrid');
        if (!countries.length) {
            grid.innerHTML = '<div class="text-sm text-slate-400">No data for this period.</div>';
            return;
        }
        grid.innerHTML = countries.map(c => `
            <div class="rounded-xl border border-slate-100 bg-slate-50/60 px-3 py-2.5">
                <div class="text-xs font-bold text-slate-500">${c.country}</div>
                <div class="text-lg font-extrabold text-slate-900">${Number(c.users).toLocaleString()}</div>
            </div>`).join('');
    }

    function renderFunnel(funnel) {
        const stages = [
            { key: 'tofu', title: 'TOFU', sub: funnel.labels?.tofu ?? 'Sessions', value: funnel.tofu, color: palette.indigo, widthPct: 100 },
            { key: 'mofu', title: 'MOFU', sub: funnel.labels?.mofu ?? 'Engaged Sessions', value: funnel.mofu, color: palette.sky,
              widthPct: funnel.tofu > 0 ? Math.max(25, Math.min(100, Math.round((funnel.mofu / funnel.tofu) * 100))) : 25 },
            { key: 'bofu', title: 'BOFU', sub: funnel.labels?.bofu ?? 'Conversions', value: funnel.bofu, color: palette.rose,
              widthPct: funnel.tofu > 0 ? Math.max(12, Math.min(100, Math.round((funnel.bofu / funnel.tofu) * 100))) : 12 },
        ];
        const rates = [null, funnel.mofu_rate, funnel.bofu_rate];

        const container = document.getElementById('funnelStages');
        container.innerHTML = stages.map((s, i) => {
            const arrow = i === 0 ? '' : `
                <div class="flex flex-col items-center py-1 text-slate-400">
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                    <span class="text-[11px] font-bold text-slate-500">${rates[i]}% of ${stages[i - 1].title}</span>
                </div>`;
            return `${arrow}
                <div class="w-full flex justify-center">
                    <div class="rounded-2xl px-5 py-4 text-white shadow-sm transition-all duration-500"
                         style="width: ${s.widthPct}%; background: ${s.color};">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-wider opacity-80">${s.title}</div>
                                <div class="text-xs opacity-80">${s.sub}</div>
                            </div>
                            <div class="text-xl font-extrabold whitespace-nowrap">${Number(s.value).toLocaleString()}</div>
                        </div>
                    </div>
                </div>`;
        }).join('');

        const overallLabel = document.querySelector('#funnelCard .text-slate-400.mb-6');
        if (overallLabel) {
            overallLabel.textContent = `Awareness, engagement, and conversion for this period — ${funnel.overall_rate}% overall TOFU→BOFU conversion.`;
        }
    }

    function renderAll(report) {
        renderOverview(report.overview);
        buildTrendChart(report.timeseries);
        buildChannelChart(report.traffic_sources);
        buildDeviceChart(report.devices);
        renderTopPages(report.top_pages);
        renderCountries(report.countries);
        renderFunnel(report.funnel);
    }

    async function fetchRange(range) {
        document.getElementById('rangeButtons').querySelectorAll('.range-btn').forEach(btn => {
            const active = btn.dataset.range === range;
            btn.classList.toggle('bg-indigo-600', active);
            btn.classList.toggle('text-white', active);
            btn.classList.toggle('border-indigo-600', active);
            btn.classList.toggle('bg-white', !active);
            btn.classList.toggle('text-slate-600', !active);
            btn.classList.toggle('border-slate-200', !active);
        });

        const res = await fetch(`${dataUrl}?range=${encodeURIComponent(range)}`, {
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        const report = await res.json();
        renderAll(report);
    }

    // Initial render from server-side data (avoids a redundant fetch on load).
    renderAll(@json($report));

    document.getElementById('rangeButtons').addEventListener('click', (e) => {
        const btn = e.target.closest('.range-btn');
        if (!btn) return;
        fetchRange(btn.dataset.range);
    });
});
</script>
@endpush
