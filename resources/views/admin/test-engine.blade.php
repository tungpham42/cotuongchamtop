@extends('layouts.admin')

@section('title', 'Engine Diagnostics')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Overall status banner --}}
    <div class="fade-up rounded-2xl border {{ $overallOk ? 'border-emerald-200 bg-emerald-50/90' : 'border-rose-200 bg-rose-50/90' }} px-5 py-4 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <span class="h-11 w-11 shrink-0 rounded-2xl {{ $overallOk ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }} flex items-center justify-center">
                <i class="fa-solid fa-server"></i>
            </span>
            <div class="min-w-0">
                <div class="text-sm font-extrabold {{ $overallOk ? 'text-emerald-800' : 'text-rose-800' }}">Pikafish Engine Diagnostics</div>
                <div class="text-xs text-slate-500 truncate">Live health check of the warm Xiangqi worker pool &middot; checked {{ $checkedAt }}</div>
            </div>
        </div>
        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold {{ $overallOk ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
            <span class="h-2 w-2 rounded-full {{ $overallOk ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
            {{ $statusLabel }}
        </span>
    </div>

    {{-- Filesystem checks --}}
    @if (!empty($checks))
        @php
            $checkColors = ['indigo', 'sky', 'fuchsia'];
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach ($checks as $i => $check)
                @php $color = $checkColors[$i % count($checkColors)]; @endphp
                <div class="fade-up rounded-2xl border border-slate-200 bg-white shadow-soft p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="h-10 w-10 rounded-xl bg-{{ $color }}-50 text-{{ $color }}-600 flex items-center justify-center text-lg">
                            {{ $check['icon'] }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $check['ok'] ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            <i class="fa-solid {{ $check['ok'] ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                            {{ $check['ok'] ? 'OK' : 'Missing' }}
                        </span>
                    </div>
                    <div class="text-[11px] font-bold uppercase tracking-[.14em] text-slate-400 mb-1">{{ $check['title'] }}</div>
                    <div class="text-sm text-slate-600">{{ $check['subtitle'] }}</div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Worker pool status --}}
    @if ($pool)
        @php
            $poolColor = $pool['tone'] === 'good' ? 'emerald' : ($pool['tone'] === 'warn' ? 'amber' : 'rose');
        @endphp
        <div class="fade-up rounded-2xl border border-slate-200 bg-white shadow-soft p-6">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <span class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="fa-solid fa-network-wired"></i>
                    </span>
                    <h2 class="text-base font-extrabold text-slate-900">Worker Pool</h2>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-{{ $poolColor }}-100 text-{{ $poolColor }}-700">
                    {{ $pool['label'] }}
                </span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-5 mb-2">
                <div class="flex items-baseline gap-1 shrink-0 min-w-[190px]">
                    <span class="text-3xl font-extrabold text-indigo-600">{{ $pool['available'] }}</span>
                    <span class="text-xl text-slate-300">/</span>
                    <span class="text-xl font-semibold text-slate-500">{{ $pool['total'] }}</span>
                    <span class="text-xs text-slate-400 ml-2">workers available</span>
                </div>
                <div class="flex-1 h-2.5 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full rounded-full bg-{{ $poolColor }}-500 transition-all duration-500" style="width: {{ $pool['pct'] }}%"></div>
                </div>
            </div>

            @if (!$pool['healthy'])
                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50/80 px-4 py-3 text-sm text-rose-800 leading-relaxed">
                    <i class="fa-solid fa-triangle-exclamation mr-1.5"></i>
                    <strong>No workers responding.</strong> Run
                    <code class="px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 text-xs">php artisan xiangqi:pool:ensure</code>
                    and inspect <code class="px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 text-xs">storage/app/xiangqi/engine-*.log</code> for boot errors.
                </div>
            @elseif ($moveTest)
                @php
                    $speedColor = $moveTest['speedTone'] === 'good' ? 'emerald' : ($moveTest['speedTone'] === 'warn' ? 'amber' : 'rose');
                @endphp
                <div class="mt-6 pt-5 border-t border-dashed border-slate-200">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-[11px] font-bold uppercase tracking-[.14em] text-slate-400">Live Move Test</h3>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-{{ $speedColor }}-100 text-{{ $speedColor }}-700">
                            {{ $moveTest['speedLabel'] }} &middot; {{ $moveTest['elapsedMs'] }}ms
                        </span>
                    </div>

                    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 mb-4 overflow-x-auto">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 shrink-0">FEN</span>
                        <code class="text-xs text-slate-600 whitespace-nowrap font-mono">{{ $moveTest['fen'] }}</code>
                    </div>

                    <div>
                        @if ($moveTest['moveFound'])
                            <span class="text-2xl font-extrabold font-mono tracking-wide text-indigo-600">{{ $moveTest['bestMove'] }}</span>
                        @else
                            <span class="text-sm font-semibold text-rose-600">No move found &mdash; fell through to fallback logic</span>
                        @endif
                    </div>
                    <p class="mt-3 text-xs text-slate-400">A cold-spawn engine takes seconds; a warm worker should stay well under a second.</p>
                </div>
            @endif
        </div>
    @endif

    {{-- Exception --}}
    @if ($error)
        <div class="fade-up rounded-2xl border border-rose-200 bg-white shadow-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <span class="h-10 w-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                        <i class="fa-solid fa-bug"></i>
                    </span>
                    <h2 class="text-base font-extrabold text-slate-900">Exception</h2>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700">Error</span>
            </div>
            <p class="rounded-xl bg-rose-50 border border-rose-100 px-4 py-3 text-sm text-rose-800">{{ $error['message'] }}</p>
            <details class="mt-3 group">
                <summary class="cursor-pointer text-xs font-semibold text-slate-400 hover:text-slate-600">Stack trace</summary>
                <pre class="mt-2 max-h-80 overflow-auto rounded-xl bg-slate-900 text-slate-200 text-[11px] leading-relaxed p-4">{{ $error['trace'] }}</pre>
            </details>
        </div>
    @endif

    <p class="text-center text-xs text-slate-400">Exercises the same XiangqiEngineClient path used by production &mdash; no per-request engine spawns.</p>
</div>
@endsection
