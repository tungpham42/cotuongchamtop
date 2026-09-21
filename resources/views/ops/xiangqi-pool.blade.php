{{--
    Secret ops page: Xiangqi engine pool control.

    Variables (passed from the route in routes/web.php):
      $pool       array|null  ['available' => int, 'total' => int], or null when the pool can't be read
      $statusUrl  string      GET  -> JSON pool status
      $runUrl     string      POST -> runs the artisan command, returns JSON
      $command    string      Command shown in the UI

    Standalone on purpose: no layout, no Vite, no site JS. It keeps working
    even when the rest of the app is unhealthy.
--}}
@php
    $pool = $pool ?? null;
    // Every Han character used on the page. Google Fonts serves only these glyphs.
    $glyphs = '帥仕相車馬炮兵楚河漢界';
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Engine pool</title>
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="referrer" content="no-referrer">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#08181A">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='15' fill='%23F3E9D2'/%3E%3Ccircle cx='16' cy='16' r='11' fill='none' stroke='%23D23B2E' stroke-width='2'/%3E%3C/svg%3E">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wdth,wght@12..96,75..100,200..800&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Serif+TC:wght@900&display=swap&text={{ rawurlencode($glyphs) }}">

@verbatim
    <style>
        :root {
            color-scheme: dark;

            --river-950: #08181A;   /* page */
            --river-900: #0C2226;   /* board surface */
            --river-800: #123238;   /* the river */
            --river-700: #1C464D;   /* panel borders */
            --celadon:   #8DBFB2;   /* board lines */
            --muted:     #9DB9B2;
            --text:      #E6F1ED;
            --ivory:     #F3E9D2;
            --ivory-2:   #DCCB9F;
            --ivory-edge:#A98F55;
            --cinnabar:  #D23B2E;
            --jade:      #5FE0AE;
            --amber:     #F0AE4E;
            --coral:     #FF7462;

            --font-ui:   "Bricolage Grotesque", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            --font-han:  "Noto Serif TC", "Songti TC", "Noto Serif CJK TC", "PMingLiU", serif;
            --font-mono: ui-monospace, SFMono-Regular, "JetBrains Mono", Menlo, Consolas, monospace;
        }

        *, *::before, *::after { box-sizing: border-box; }
        [hidden] { display: none !important; }

        html { -webkit-text-size-adjust: 100%; }
        body {
            margin: 0;
            min-height: 100dvh;
            background: var(--river-950);
            color: var(--text);
            font: 400 1rem/1.55 var(--font-ui);
            -webkit-font-smoothing: antialiased;
        }

        .sr-only {
            position: absolute; width: 1px; height: 1px; margin: -1px; padding: 0;
            overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; border: 0;
        }

        .wrap {
            width: min(940px, 100% - 32px);
            margin-inline: auto;
            padding-block: clamp(28px, 6vw, 64px) 40px;
        }

        /* ---------- Header ---------- */
        .head {
            display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start;
            gap: 20px 32px;
            margin-bottom: clamp(20px, 4vw, 32px);
        }
        h1 {
            margin: 0 0 .6rem;
            font: 700 clamp(2.4rem, 6.2vw, 3.7rem)/1 var(--font-ui);
            font-stretch: 84%;
            letter-spacing: -.025em;
            color: var(--ivory);
        }
        .lede { margin: 0; max-width: 44ch; color: var(--muted); text-wrap: pretty; }

        .status { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; padding-top: .35rem; }
        .pill {
            --tone: var(--muted); --tone-line: rgba(157,185,178,.35); --tone-bg: rgba(157,185,178,.08); --tone-text: var(--text);
            display: inline-flex; align-items: center; gap: .65rem;
            padding: .5rem 1rem .5rem .85rem;
            border: 1px solid var(--tone-line); border-radius: 999px;
            background: var(--tone-bg); color: var(--tone-text);
            font-weight: 600; font-size: .95rem;
            transition: background-color .25s, border-color .25s, color .25s;
        }
        .pill[data-tone="ok"]      { --tone: var(--jade);  --tone-line: rgba(95,224,174,.42);  --tone-bg: rgba(95,224,174,.09);  --tone-text: #CDF6E4; }
        .pill[data-tone="partial"] { --tone: var(--amber); --tone-line: rgba(240,174,78,.45);  --tone-bg: rgba(240,174,78,.10);  --tone-text: #FFE3B8; }
        .pill[data-tone="down"]    { --tone: var(--coral); --tone-line: rgba(255,116,98,.45);  --tone-bg: rgba(255,116,98,.10);  --tone-text: #FFD3CB; }
        .dot {
            width: .6rem; height: .6rem; border-radius: 50%;
            background: var(--tone);
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--tone) 22%, transparent);
        }
        .status-sub { display: flex; align-items: center; gap: .9rem; font-size: .85rem; color: var(--muted); }
        .link {
            padding: 0; border: 0; background: none; cursor: pointer;
            color: var(--celadon); font: 600 .85rem var(--font-ui);
            text-decoration: underline; text-underline-offset: 3px; text-decoration-color: rgba(141,191,176,.4);
        }
        .link:hover { text-decoration-color: currentColor; }
        .link:focus-visible, .ghost:focus-visible { outline: 2px solid #FFD98A; outline-offset: 3px; border-radius: 4px; }

        /* ---------- The board ---------- */
        .board {
            container-type: inline-size;
            overflow: hidden;
            border: 1px solid var(--river-700);
            border-radius: 14px;
            background: var(--river-900);
        }

        .camp { position: relative; aspect-ratio: 940 / 472; }
        .camp-svg { position: absolute; inset: 0; width: 100%; height: 100%; display: block; }
        .camp-svg path { fill: none; vector-effect: non-scaling-stroke; }
        .camp-svg .frame { stroke: var(--celadon); stroke-opacity: .55; stroke-width: 2; }
        .camp-svg .grid  { stroke: var(--celadon); stroke-opacity: .26; stroke-width: 1; }

        .camp-empty {
            position: absolute; inset: 0; margin: 0;
            display: grid; place-items: center; padding: 0 12%;
            text-align: center; color: var(--muted); text-wrap: balance;
        }

        .workers { position: absolute; inset: 0; margin: 0; padding: 0; list-style: none; }

        .worker {
            --d: 8.4cqw;
            position: absolute; left: var(--x); top: var(--y);
            translate: -50% -50%;
            width: var(--d); aspect-ratio: 1; border-radius: 50%;
            display: grid; place-items: center;
            font-family: var(--font-han); font-weight: 900; font-size: calc(var(--d) * .52); line-height: 1;
        }
        .worker::before {
            content: ""; position: absolute; inset: 8%; border-radius: 50%;
            border: max(1px, .22cqw) solid currentColor; opacity: .75;
        }
        .worker-glyph { transform: translateY(-.03em); }
        .worker.is-up {
            background: radial-gradient(circle at 34% 26%, #FFFBEF 0, var(--ivory) 42%, var(--ivory-2) 100%);
            color: var(--cinnabar);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.7), 0 .7cqw 0 var(--ivory-edge), 0 1.6cqw 2.2cqw -.4cqw rgba(0,0,0,.6);
            text-shadow: 0 1px 0 rgba(255,255,255,.5);
        }
        .worker:not(.is-up) {
            background: radial-gradient(circle at 34% 26%, #2A555C 0, #183840 55%, #112A30 100%);
            color: rgba(141,191,176,.4);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.08), 0 .7cqw 0 #0A1B1F, 0 1.6cqw 2.2cqw -.4cqw rgba(0,0,0,.6);
        }
        .worker:not(.is-up)::before { border-style: dashed; }

        /* The one page-load moment: pieces are placed on the board. */
        .worker.is-placing {
            animation: place .55s cubic-bezier(.2, .8, .25, 1.15) backwards;
            animation-delay: calc(120ms + var(--i) * 75ms);
        }
        @keyframes place {
            from { opacity: 0; transform: translateY(-2.2cqw) scale(1.14); }
            to   { opacity: 1; transform: none; }
        }

        /* ---------- The river ---------- */
        .river {
            position: relative; isolation: isolate;
            display: grid; grid-template-columns: 1fr auto 1fr; align-items: center;
            padding: clamp(22px, 4.5cqw, 40px) 10.5%;
        }
        .river-water {
            position: absolute; inset: 0 7.447%; z-index: -2;
            background:
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='56' height='14' viewBox='0 0 56 14'%3E%3Cpath d='M0 7 Q14 1 28 7 T56 7' fill='none' stroke='%238DBFB2' stroke-opacity='.09' stroke-width='1.2'/%3E%3C/svg%3E"),
                var(--river-800);
            border-block: 1px solid rgba(141,191,176,.3);
        }
        .river-lines { position: absolute; inset: 0; z-index: -1; width: 100%; height: 100%; pointer-events: none; }
        .river-lines path { fill: none; vector-effect: non-scaling-stroke; }
        .river-word {
            font: 900 clamp(1.4rem, 4.4cqw, 2.6rem)/1 var(--font-han);
            letter-spacing: .12em; color: var(--celadon); opacity: .42; user-select: none;
        }
        .river-word.is-right { justify-self: end; }

        /* ---------- The button: a Xiangqi piece ---------- */
        .go {
            display: grid; justify-items: center; gap: 16px;
            padding: 0; border: 0; background: none; color: inherit; font: inherit;
            cursor: pointer; -webkit-tap-highlight-color: transparent;
        }
        .go:focus-visible { outline: 3px solid #FFD98A; outline-offset: 10px; border-radius: 28px; }
        .go[aria-disabled="true"] { cursor: progress; }

        .go-disc {
            --d: clamp(108px, 17cqw, 152px);
            position: relative; display: grid; place-items: center;
            width: var(--d); height: var(--d); border-radius: 50%;
            background: radial-gradient(circle at 34% 26%, #FFFBEF 0, var(--ivory) 40%, var(--ivory-2) 100%);
            color: var(--cinnabar);
            box-shadow:
                inset 0 2px 0 rgba(255,255,255,.75),
                inset 0 -8px 14px rgba(120,90,30,.22),
                0 9px 0 var(--ivory-edge),
                0 9px 0 1px rgba(0,0,0,.35),
                0 26px 34px -8px rgba(0,0,0,.7);
            transition: transform .14s cubic-bezier(.3,.7,.4,1), box-shadow .14s;
        }
        .go-disc::before {
            content: ""; position: absolute; inset: 7%; border-radius: 50%;
            border: 2px solid currentColor; opacity: .8;
        }
        .go-glyph {
            font: 900 calc(var(--d) * .5)/1 var(--font-han);
            text-shadow: 0 1px 0 rgba(255,255,255,.55), 0 -1px 0 rgba(90,20,10,.3);
            transform: translateY(-.03em);
        }
        .go:hover .go-disc { transform: translateY(-3px); }
        .go:active .go-disc,
        .go[aria-disabled="true"] .go-disc {
            transform: translateY(6px);
            box-shadow:
                inset 0 2px 0 rgba(255,255,255,.75),
                inset 0 -8px 14px rgba(120,90,30,.22),
                0 3px 0 var(--ivory-edge),
                0 3px 0 1px rgba(0,0,0,.35),
                0 12px 18px -8px rgba(0,0,0,.7);
        }
        .go-label { font-weight: 650; font-size: 1.05rem; color: var(--ivory); }

        /* Progress / result ring around the piece */
        .go-ring {
            --c: var(--jade);
            position: absolute; inset: -10px; border-radius: 50%; pointer-events: none; opacity: 0;
            background: var(--c);
            -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 4px), #000 calc(100% - 3px));
                    mask: radial-gradient(farthest-side, transparent calc(100% - 4px), #000 calc(100% - 3px));
        }
        .go[data-state="running"] .go-ring {
            opacity: 1;
            background: conic-gradient(from 0deg, transparent 0 38%, var(--c) 100%);
            animation: spin 1.05s linear infinite;
        }
        .go[data-state="ok"]   .go-ring { --c: var(--jade);  animation: settle 1.7s ease-out forwards; }
        .go[data-state="warn"] .go-ring { --c: var(--amber); animation: settle 1.7s ease-out forwards; }
        .go[data-state="fail"] .go-ring { --c: var(--coral); animation: settle 1.7s ease-out forwards; }
        @keyframes spin   { to { transform: rotate(360deg); } }
        @keyframes settle { 0% { opacity: 1; transform: scale(.94); } 30% { transform: scale(1); } 100% { opacity: 0; transform: scale(1.06); } }

        /* ---------- Caption row ---------- */
        .action {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
            gap: 12px 28px; margin: 22px 2px 30px;
        }
        .caption { margin: 0; font-weight: 500; min-height: 1.55em; text-wrap: pretty; }
        .caption[data-tone="busy"] { color: var(--celadon); }
        .caption[data-tone="ok"]   { color: var(--jade); }
        .caption[data-tone="warn"] { color: var(--amber); }
        .caption[data-tone="fail"] { color: var(--coral); }
        .cmd { display: inline-flex; align-items: center; gap: .6rem; font-size: .85rem; color: var(--muted); }
        .cmd code {
            padding: .3rem .65rem; border: 1px solid var(--river-700); border-radius: 7px;
            background: rgba(0,0,0,.28); color: #CFE3DC;
            font: 500 .82rem var(--font-mono);
        }

        /* ---------- Command output ---------- */
        .term { border: 1px solid var(--river-700); border-radius: 12px; background: #050F11; overflow: hidden; }
        .term-bar {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            min-height: 3rem; padding: .5rem 1rem; border-bottom: 1px solid var(--river-700);
        }
        .term-bar h2 { margin: 0; font: 600 .95rem var(--font-ui); color: var(--muted); }
        .ghost {
            padding: .3rem .75rem; border: 1px solid var(--river-700); border-radius: 8px;
            background: transparent; color: var(--celadon); cursor: pointer; font: 600 .8rem var(--font-ui);
        }
        .ghost:hover { background: rgba(141,191,176,.08); }
        .term-body {
            margin: 0; padding: 1rem 1.1rem; min-height: 9.5rem; max-height: 22rem; overflow: auto;
            font: 400 .84rem/1.65 var(--font-mono); color: #D5E6E0; tab-size: 4;
        }
        .term-body:focus-visible { outline: 2px solid #FFD98A; outline-offset: -2px; }
        .term-empty, .ln-muted { color: var(--muted); }
        .ln-prompt { color: var(--celadon); }
        .ln-warn   { color: var(--amber); }
        .ln-fail   { color: var(--coral); }
        .caret {
            display: inline-block; width: .55em; height: 1.05em; vertical-align: -.2em;
            background: var(--celadon); animation: blink 1s steps(1) infinite;
        }
        @keyframes blink { 50% { opacity: 0; } }

        .term-meta {
            display: flex; flex-wrap: wrap; gap: .35rem 1.8rem; margin: 0;
            padding: .75rem 1.1rem; border-top: 1px solid var(--river-700); font-size: .85rem;
        }
        .term-meta div { display: flex; gap: .5rem; }
        .term-meta dt { color: var(--muted); }
        .term-meta dd { margin: 0; font-family: var(--font-mono); font-size: .82rem; }
        .term-meta dd[data-tone="ok"]   { color: var(--jade); }
        .term-meta dd[data-tone="fail"] { color: var(--coral); }

        footer {
            display: flex; flex-wrap: wrap; gap: .3rem 1.4rem;
            margin-top: 28px; font-size: .85rem; color: var(--muted);
        }

        @media (max-width: 560px) {
            .status { align-items: flex-start; }
            .river { padding-inline: 6%; }
            .go-disc { --d: clamp(96px, 30cqw, 120px); }
        }

        @media (prefers-reduced-motion: reduce) {
            .worker.is-placing { animation: none; }
            .go-disc { transition: none; }
            .caret { animation: none; }
            .go[data-state="running"] .go-ring { animation: none; background: var(--c); opacity: .55; }
            .go[data-state="ok"] .go-ring,
            .go[data-state="warn"] .go-ring,
            .go[data-state="fail"] .go-ring { animation: none; opacity: .9; }
        }
    </style>
@endverbatim
</head>

<body>
<main
    id="app"
    class="wrap"
    data-status-url="{{ $statusUrl }}"
    data-run-url="{{ $runUrl }}"
    data-command="{{ $command }}"
    @if ($pool) data-available="{{ (int) $pool['available'] }}" data-total="{{ (int) $pool['total'] }}" @endif
>
    <header class="head">
        <div>
            <h1>Engine pool</h1>
            <p class="lede">The Pikafish workers that answer the Xiangqi AI. If some have stopped, start them again from here.</p>
        </div>

        <div class="status">
            <div class="pill" id="pill" data-tone="unknown" role="status">
                <span class="dot" aria-hidden="true"></span>
                <span id="pill-text">Checking workers</span>
            </div>
            <div class="status-sub">
                <span id="checked"></span>
                <button class="link" id="recheck" type="button">Check again</button>
            </div>
        </div>
    </header>

    <section class="board" aria-label="Worker pool">
        <div class="camp" id="camp">
            <svg class="camp-svg" viewBox="0 0 940 472" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                <path class="frame" d="M24 472V24H916V472"/>
                <path class="grid" d="M70 70H870M70 170H870M70 270H870M70 370H870M70 470H870"/>
                <path class="grid" d="M70 70V472M870 70V472"/>
                <path class="grid" d="M170 70V470M270 70V470M370 70V470M470 70V470M570 70V470M670 70V470M770 70V470"/>
                <path class="grid" d="M370 70L570 270M570 70L370 270"/>
            </svg>
            <ul class="workers" id="workers"></ul>
            <p class="camp-empty" id="camp-empty" hidden></p>
        </div>

        <div class="river">
            <div class="river-water" aria-hidden="true"></div>
            <svg class="river-lines" viewBox="0 0 940 100" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                <path d="M24 0V100M916 0V100M24 100H916" stroke="#8DBFB2" stroke-opacity=".55" stroke-width="2"/>
                <path d="M70 0V100M870 0V100" stroke="#8DBFB2" stroke-opacity=".26" stroke-width="1"/>
            </svg>

            <span class="river-word" aria-hidden="true">楚河</span>

            <button class="go" id="go" type="button" data-state="idle" aria-describedby="caption">
                <span class="go-disc">
                    <span class="go-ring" aria-hidden="true"></span>
                    <span class="go-glyph" aria-hidden="true">帥</span>
                </span>
                <span class="go-label" id="go-label">Ensure pool</span>
            </button>

            <span class="river-word is-right" aria-hidden="true">漢界</span>
        </div>
    </section>

    <div class="action">
        <p class="caption" id="caption" data-tone="idle" role="status" aria-live="polite"></p>
        <div class="cmd"><span>Runs</span><code>{{ $command }}</code></div>
    </div>

    <section class="term" aria-labelledby="term-title">
        <div class="term-bar">
            <h2 id="term-title">Command output</h2>
            <button class="ghost" id="copy" type="button" hidden>Copy output</button>
        </div>
        <pre class="term-body" id="term" tabindex="0"><span class="term-empty">Nothing has run yet. The command's output will appear here.</span></pre>
        <dl class="term-meta" id="meta" hidden>
            <div><dt>Exit code</dt><dd id="m-exit"></dd></div>
            <div><dt>Took</dt><dd id="m-time"></dd></div>
            <div><dt>Finished</dt><dd id="m-at"></dd></div>
        </dl>
    </section>

    <footer>
        <span>Private page, hidden from search engines.</span>
        <span>{{ app()->environment() }} on {{ request()->getHost() }}</span>
    </footer>
</main>

@verbatim
<script>
(() => {
    'use strict';

    const root = document.getElementById('app');
    const urls = { status: root.dataset.statusUrl, run: root.dataset.runUrl };
    const command = root.dataset.command;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const $ = (id) => document.getElementById(id);
    const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
    const clock = () => new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });

    const els = {
        pill: $('pill'), pillText: $('pill-text'), checked: $('checked'), recheck: $('recheck'),
        workers: $('workers'), campEmpty: $('camp-empty'),
        go: $('go'), goLabel: $('go-label'), caption: $('caption'),
        term: $('term'), meta: $('meta'), mExit: $('m-exit'), mTime: $('m-time'), mAt: $('m-at'), copy: $('copy'),
    };

    /* ---------- Board layout: workers sit on the back rank, symmetric ---------- */

    const GLYPH_BY_COL = ['車', '馬', '相', '仕', '帥', '仕', '相', '馬', '車'];
    const SLOTS = {
        1: [4], 2: [2, 6], 3: [2, 4, 6], 4: [1, 3, 5, 7], 5: [0, 2, 4, 6, 8],
        6: [0, 2, 3, 5, 6, 8], 7: [0, 1, 2, 4, 6, 7, 8], 8: [0, 1, 2, 3, 5, 6, 7, 8], 9: [0, 1, 2, 3, 4, 5, 6, 7, 8],
    };
    const MAX_SHOWN = 18;

    function layout(total) {
        const shown = Math.min(total, MAX_SHOWN);
        const spots = [];
        for (let rank = 0; spots.length < shown; rank++) {
            const count = Math.min(9, shown - rank * 9);
            SLOTS[count].forEach((col) => spots.push({ col, row: rank, glyph: rank === 0 ? GLYPH_BY_COL[col] : '兵' }));
        }
        return spots;
    }

    /* ---------- Pool state ---------- */

    let pool = 'total' in root.dataset
        ? { available: Number(root.dataset.available), total: Number(root.dataset.total) }
        : null;
    let discs = [];

    function paintDisc(disc, up, index) {
        disc.el.classList.toggle('is-up', up);
        disc.el.lastElementChild.textContent = `Worker ${index + 1}: ${up ? 'running' : 'stopped'}`;
        disc.up = up;
    }

    // A piece turns over when its worker changes state.
    function flipDisc(disc, up, index) {
        if (reduceMotion || !disc.el.animate) return paintDisc(disc, up, index);
        const out = disc.el.animate(
            [{ transform: 'perspective(500px) rotateY(0)' }, { transform: 'perspective(500px) rotateY(90deg)' }],
            { duration: 170, easing: 'ease-in', fill: 'forwards' }
        );
        out.onfinish = () => {
            paintDisc(disc, up, index);
            disc.el.animate(
                [{ transform: 'perspective(500px) rotateY(-90deg)' }, { transform: 'perspective(500px) rotateY(0)' }],
                { duration: 240, easing: 'cubic-bezier(.2,.9,.3,1.3)' }
            );
            out.cancel();
        };
    }

    function renderWorkers(next, { intro = false } = {}) {
        const total = next ? next.total : 0;
        const available = next ? next.available : 0;

        els.workers.textContent = '';
        discs = layout(total).map((spot, i) => {
            const li = document.createElement('li');
            li.className = 'worker' + (intro && !reduceMotion ? ' is-placing' : '');
            li.style.setProperty('--x', ((70 + 100 * spot.col) / 9.4).toFixed(3) + '%');
            li.style.setProperty('--y', ((70 + 100 * spot.row) / 4.72).toFixed(3) + '%');
            li.style.setProperty('--i', i);
            li.innerHTML = '<span class="worker-glyph" aria-hidden="true"></span><span class="sr-only"></span>';
            li.firstElementChild.textContent = spot.glyph;
            li.addEventListener('animationend', () => li.classList.remove('is-placing'), { once: true });
            els.workers.appendChild(li);
            const disc = { el: li, up: null };
            paintDisc(disc, i < available, i);
            return disc;
        });

        if (!next) {
            els.campEmpty.textContent = "Couldn't read the pool status. Press Ensure pool to start the workers.";
        } else if (next.total === 0) {
            els.campEmpty.textContent = 'The pool reports no workers. Press Ensure pool to start them.';
        }
        els.campEmpty.hidden = discs.length > 0;
    }

    function toneOf(next) {
        if (!next || next.total === 0) return next ? 'down' : 'unknown';
        if (next.available === next.total) return 'ok';
        return next.available === 0 ? 'down' : 'partial';
    }

    function paintPill(next, override) {
        const tone = override ? 'unknown' : toneOf(next);
        els.pill.dataset.tone = tone;
        els.pillText.textContent = override
            || (!next ? 'Status unavailable'
            : next.total === 0 ? 'No workers configured'
            : tone === 'ok' ? `All ${next.total} workers up`
            : tone === 'down' ? 'No workers responding'
            : `${next.available} of ${next.total} workers up`);
        els.checked.textContent = `Checked ${clock()}`;
    }

    function setPool(next, { intro = false } = {}) {
        const prev = pool;
        pool = next;
        paintPill(next);

        const sameShape = prev && next && prev.total === next.total && discs.length === Math.min(next.total, MAX_SHOWN);
        if (!sameShape) return renderWorkers(next, { intro });

        let k = 0;
        discs.forEach((disc, i) => {
            const up = i < next.available;
            if (disc.up !== up) setTimeout(() => flipDisc(disc, up, i), k++ * 140);
        });
    }

    async function fetchStatus() {
        const res = await fetch(urls.status, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin', cache: 'no-store',
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        return data.ok ? { available: data.available, total: data.total } : null;
    }

    async function refresh() {
        try {
            setPool(await fetchStatus());
        } catch {
            paintPill(pool, 'Status check failed');
        }
    }

    /* ---------- Caption + terminal ---------- */

    function say(text, tone = 'idle') {
        els.caption.textContent = text;
        els.caption.dataset.tone = tone;
    }

    const caret = Object.assign(document.createElement('span'), { className: 'caret' });
    caret.setAttribute('aria-hidden', 'true');

    function put(text, cls = '') {
        const line = document.createElement('span');
        line.className = cls;
        line.textContent = text + '\n';
        els.term.insertBefore(line, caret.parentNode === els.term ? caret : null);
        els.term.scrollTop = els.term.scrollHeight;
    }

    function termStart() {
        els.term.textContent = '';
        els.term.append(caret);
        els.meta.hidden = true;
        els.copy.hidden = true;
        put('$ ' + command, 'ln-prompt');
    }

    async function termReveal(text) {
        const body = String(text || '').replace(/\r\n?/g, '\n').replace(/\n+$/, '');
        if (!body) return put('The command finished without printing anything.', 'ln-muted');

        const lines = body.split('\n');
        const delay = reduceMotion || lines.length > 80 ? 0 : Math.min(45, 900 / lines.length);
        for (const line of lines) {
            put(line, /\b(error|failed|exception|fatal)\b/i.test(line) ? 'ln-warn' : '');
            if (delay) await sleep(delay);
        }
    }

    function termFinish({ exit, ms }) {
        caret.remove();
        els.mExit.textContent = exit === null ? 'none' : String(exit);
        els.mExit.dataset.tone = exit === 0 ? 'ok' : 'fail';
        els.mTime.textContent = ms < 1000 ? `${Math.round(ms)} ms` : `${(ms / 1000).toFixed(1)} s`;
        els.mAt.textContent = clock();
        els.meta.hidden = false;
        els.copy.hidden = false;
    }

    els.copy.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(els.term.innerText.trim());
            els.copy.textContent = 'Copied';
        } catch {
            els.copy.textContent = 'Copy failed';
        }
        setTimeout(() => (els.copy.textContent = 'Copy output'), 1600);
    });

    /* ---------- Running the command ---------- */

    let running = false;

    function busy(on) {
        running = on;
        els.go.setAttribute('aria-disabled', String(on));
        els.go.setAttribute('aria-busy', String(on));
        els.go.dataset.state = on ? 'running' : els.go.dataset.state;
        els.goLabel.textContent = on ? 'Ensuring pool…' : 'Ensure pool';
    }

    function flash(state) {
        els.go.dataset.state = state;
        setTimeout(() => { if (!running) els.go.dataset.state = 'idle'; }, 1800);
    }

    const HTTP_MESSAGES = {
        419: 'Your session expired. Reload this page, then press Ensure pool again.',
        409: 'Another run is already in progress. Wait for it to finish, then try again.',
        429: 'Too many requests. Wait a minute, then try again.',
        404: 'This page is no longer available. Check that XIANGQI_OPS_SECRET is still set on the server.',
    };

    async function request() {
        const ctrl = new AbortController();
        const timer = setTimeout(() => ctrl.abort(), 130000);
        try {
            const res = await fetch(urls.run, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                signal: ctrl.signal,
            });
            const data = await res.json().catch(() => null);
            if (res.ok && data) return { ok: true, data };
            return {
                ok: false,
                message: HTTP_MESSAGES[res.status] || data?.message || `The server answered with HTTP ${res.status}.`,
                output: data?.output || '',
            };
        } catch (e) {
            return {
                ok: false,
                message: e.name === 'AbortError'
                    ? 'The request timed out after 130 seconds.'
                    : "Couldn't reach the server. Check your connection and try again.",
                output: '',
            };
        } finally {
            clearTimeout(timer);
        }
    }

    // The command can return before every worker has finished booting, so keep checking briefly.
    async function waitForWorkers() {
        for (let i = 0; i < 12 && pool && pool.total > 0 && pool.available < pool.total; i++) {
            say(`Waiting for workers to boot. ${pool.available} of ${pool.total} are up.`, 'busy');
            await sleep(1500);
            try { setPool(await fetchStatus()); } catch { break; }
        }
    }

    async function run() {
        if (running) return;
        busy(true);
        termStart();
        say('Ensuring the pool. This can take a few seconds.', 'busy');

        const started = performance.now();
        const result = await request();

        if (!result.ok) {
            if (result.output) await termReveal(result.output);
            put(result.message, 'ln-fail');
            termFinish({ exit: null, ms: performance.now() - started });
            say(result.message, 'fail');
            busy(false);
            return flash('fail');
        }

        const { data } = result;
        await termReveal(data.output);
        if (data.pool) setPool(data.pool);
        termFinish({ exit: data.exit_code, ms: data.duration_ms ?? performance.now() - started });

        if (data.exit_code !== 0) {
            say(`The command failed with exit code ${data.exit_code}. Check the output below.`, 'fail');
            busy(false);
            return flash('fail');
        }

        await waitForWorkers();
        busy(false);

        if (!pool) {
            say("The command finished, but the pool status couldn't be read.", 'warn');
            flash('warn');
        } else if (pool.total > 0 && pool.available === pool.total) {
            say(`Pool ensured. All ${pool.total} workers are up.`, 'ok');
            flash('ok');
        } else {
            say(`The command finished, but only ${pool.available} of ${pool.total} workers are responding. Check storage/app/xiangqi/engine-*.log for boot errors.`, 'warn');
            flash('warn');
        }
    }

    /* ---------- Wire up ---------- */

    els.go.addEventListener('click', run);
    els.recheck.addEventListener('click', async () => {
        els.recheck.textContent = 'Checking…';
        await refresh();
        els.recheck.textContent = 'Check again';
    });

    setInterval(() => { if (!running && !document.hidden) refresh(); }, 20000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden && !running) refresh(); });

    paintPill(pool);
    renderWorkers(pool, { intro: true });
    say(pool && pool.total > 0 && pool.available < pool.total
        ? `${pool.total - pool.available} of ${pool.total} workers are down. Press Ensure pool to start them.`
        : 'Ready. Press Ensure pool to start any workers that have stopped.');
})();
</script>
@endverbatim
</body>
</html>
