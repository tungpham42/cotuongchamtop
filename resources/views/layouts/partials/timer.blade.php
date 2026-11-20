{{--
    Game clocks (10 min + 2 min per move), split in two parts around the board:
      - the OPPONENT's clock sits ABOVE the board,
      - YOUR clock sits BELOW it,
    following the board orientation (red at the bottom => red clock at the bottom, and so on).

    Included twice from room_master (sections `boardTop` / `boardBottom`, rendered by boardSlot):
      @include('layouts.partials.timer', ['timerPart' => 'top'])     plaque + styles
      @include('layouts.partials.timer', ['timerPart' => 'bottom'])  plaque + caption + script
    The script comes last so both plaques already exist in the DOM when it runs.
    Needs: $roomCode, $orientation ('red' | 'black' = the side shown at the bottom).
    Layout: both parts sit inside .xq-stage (boardSlot), which sizes board + clocks to fit the viewport height.
--}}
@php
    $timerPart = $timerPart ?? 'bottom';
    $timerBottomSide = ($orientation ?? 'red') === 'black' ? 'black' : 'red';
    $timerTopSide = $timerBottomSide === 'red' ? 'black' : 'red';
@endphp

@if ($timerPart === 'top')
    <div class="royal-timer royal-timer-top" data-position="top">
        @include('layouts.partials.timerPlaque', ['timerSide' => $timerTopSide, 'timerMine' => false])
    </div>
@else
    {{-- The "waiting for opponent" caption is an overlay on the plaque (position:absolute), so it never
         adds height to the stage and the board does not jump when the opponent joins. --}}
    <div class="royal-timer royal-timer-bottom" data-position="bottom">
        @include('layouts.partials.timerPlaque', ['timerSide' => $timerBottomSide, 'timerMine' => true])
        <div id="timer-caption" class="timer-caption" role="status" aria-live="polite">
            <i class="fas fa-circle-notch fa-spin"></i> <span>{{ __("Đang chờ đối thủ vào phòng...") }}</span>
        </div>
    </div>
@endif

@if ($timerPart === 'top')
<style>
    /* ==========================================================================
       ROYAL DUAL CLOCKS  ·  10 min + 2 min per move
       Theme: Liquid Glass + Cung đình Huế (uses the :root royal tokens from index_new.css)
       - each side is a glass "plaque" with its general's seal (帥 / 將)
       - the side to move: gold light orbits the border, lantern dot pulses, plaque lifts
       - per-move bar drains gold → amber → red, total time turns red when low

       SIZING: plaques no longer have their own width/margins. They live in .xq-stage
       (boardSlot.blade.php), which is exactly as wide as the board and sizes the whole
       stack to the viewport height. Only --xq-timer-h (plaque height) is read from there;
       the seal, clock and spacing all derive from it.
       ========================================================================== */
    @property --pt-angle {
        syntax: "<angle>";
        inherits: false;
        initial-value: 0deg;
    }

    .royal-timer {
        --pt-gold: var(--royal-gold, #ffd700);
        --pt-gold-light: var(--royal-gold-light, #fff2cc);
        position: relative;          /* anchors the waiting caption overlay */
        flex: none;
        width: 100%;
        max-width: var(--xq-board-w, 380px);   /* standalone fallback; inside the stage it is 100% of it */
        margin: 0 auto;
        user-select: none;
        -webkit-user-select: none;
    }

    /* ---------- Plaque ---------- */
    .ptimer {
        --pt-h: var(--xq-timer-h, 64px);
        --pt-seal: calc(var(--pt-h) - 16px);
        position: relative;
        box-sizing: border-box;
        width: 100%;
        height: var(--pt-h);
        display: flex;
        align-items: center;
        gap: clamp(6px, calc(var(--pt-h) * .16), 12px);
        padding: 0 clamp(10px, calc(var(--pt-h) * .25), 16px) 0 6px;
        border-radius: clamp(12px, calc(var(--pt-h) * .22), 16px);
        color: var(--pt-gold-light);
        border: 2px solid transparent;
        overflow: hidden;
        backdrop-filter: var(--glass-blur, blur(20px));
        -webkit-backdrop-filter: var(--glass-blur, blur(20px));
        box-shadow: var(--liquid-shadow, 0 8px 32px rgba(0, 0, 0, 0.8)),
                    inset 0 1px 6px rgba(255, 255, 255, 0.12);
        transition: transform .45s cubic-bezier(.175, .885, .32, 1.275),
                    box-shadow .45s ease, opacity .3s ease, filter .3s ease;
        /* the border is a second background layer so the orbiting light can animate it */
        background:
            linear-gradient(var(--pt-fill-a), var(--pt-fill-b)) padding-box,
            linear-gradient(135deg, rgba(255, 215, 0, .55), rgba(255, 215, 0, .15), rgba(255, 215, 0, .55)) border-box;
    }
    .ptimer.red-glass {
        --pt-fill-a: rgba(150, 24, 24, .88);
        --pt-fill-b: rgba(58, 8, 8, .94);
        --pt-accent: #ff6b6b;
    }
    .ptimer.black-glass {
        --pt-fill-a: rgba(36, 38, 48, .92);
        --pt-fill-b: rgba(9, 10, 14, .96);
        --pt-accent: #ffd54f;
    }
    /* glossy top highlight */
    .ptimer::before {
        content: "";
        position: absolute;
        inset: 0 0 auto 0;
        height: 46%;
        border-radius: 14px 14px 0 0;
        background: linear-gradient(to bottom, rgba(255, 255, 255, .16), rgba(255, 255, 255, 0));
        pointer-events: none;
    }
    /* liquid sheen sweep (runs on the active plaque) */
    .ptimer::after {
        content: "";
        position: absolute;
        top: 0; bottom: 0; left: -60%;
        width: 40%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .22), transparent);
        transform: skewX(-20deg);
        opacity: 0;
        pointer-events: none;
    }

    /* ---------- Seal (帥 / 將) ---------- */
    .pt-seal {
        position: relative;
        flex: none;
        width: var(--pt-seal);
        height: var(--pt-seal);
        border-radius: 50%;
        display: grid;
        place-items: center;
        font-family: "Texturina", "Noto Sans SC", "Noto Sans JP", serif;
        font-size: calc(var(--pt-seal) * .52);
        font-weight: 900;
        line-height: 1;
        z-index: 1;
        box-shadow: 0 4px 10px rgba(0, 0, 0, .6), inset 0 2px 4px rgba(255, 255, 255, .35),
                    inset 0 -3px 6px rgba(0, 0, 0, .45);
        transition: transform .45s cubic-bezier(.175, .885, .32, 1.275), box-shadow .45s ease;
    }
    .pt-seal::before { /* inner engraved ring */
        content: "";
        position: absolute;
        inset: 3px;
        border-radius: 50%;
        border: 1.5px solid currentColor;
        opacity: .55;
    }
    .red-glass .pt-seal {
        background: radial-gradient(circle at 32% 28%, #fff7e0 0%, #f3dca6 55%, #d6b568 100%);
        color: #b11d1d;
        text-shadow: 0 1px 0 rgba(255, 255, 255, .6);
    }
    .black-glass .pt-seal {
        background: radial-gradient(circle at 32% 28%, #f3dca6 0%, #e3c27c 55%, #b89020 100%);
        color: #15161c;
        text-shadow: 0 1px 0 rgba(255, 255, 255, .45);
    }

    /* ---------- Body ---------- */
    .pt-body {
        position: relative;
        z-index: 1;
        flex: 1 1 auto;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: clamp(3px, calc(var(--pt-h) * .09), 6px);
    }
    .pt-head {
        display: flex;
        align-items: center;
        gap: 6px;
        line-height: 1;
    }
    .ptimer .label {
        font-family: "Texturina", serif;
        font-size: clamp(.62rem, calc(var(--pt-h) * .012rem), .78rem);
        font-weight: 800;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: var(--pt-gold);
        text-shadow: 0 1px 2px rgba(0, 0, 0, .8);
    }
    .pt-you {
        padding: 2px 7px;
        border-radius: 999px;
        font-size: .62rem;
        font-weight: 800;
        letter-spacing: .5px;
        text-transform: uppercase;
        color: #1a0a00;
        background: linear-gradient(to bottom, #ffe680, #d4af37);
        box-shadow: 0 1px 4px rgba(0, 0, 0, .5);
    }
    /* lantern dot = "it is this side's move" */
    .pt-turn {
        margin-left: auto;
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .18);
        transition: background .3s ease, box-shadow .3s ease;
    }

    .ptimer .clock {
        flex: none;
        display: block;
        margin-left: auto;
        text-align: right;
        min-width: 4.4ch;
        font-family: "Noto Sans Mono", "JetBrains Mono", "Courier New", monospace;
        font-variant-numeric: tabular-nums;
        font-size: calc(var(--pt-h) * .5);
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: 1px;
        color: #fff;
        text-shadow: 0 2px 6px rgba(0, 0, 0, .7);
        transition: color .3s ease, text-shadow .3s ease;
    }

    /* ---------- Per-move bar ---------- */
    .pt-move {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .pt-bar {
        flex: 1 1 auto;
        height: 6px;
        border-radius: 999px;
        overflow: hidden;
        background: rgba(0, 0, 0, .55);
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, .9);
    }
    .pt-bar > i {
        display: block;
        height: 100%;
        width: 100%;
        border-radius: inherit;
        transform-origin: left center;
        background: linear-gradient(90deg, #b89020, var(--pt-gold));
        box-shadow: 0 0 8px rgba(255, 215, 0, .55);
        transition: background .3s ease, box-shadow .3s ease;
    }
    .ptimer .move-clock {
        flex: none;
        min-width: 3.1ch;
        text-align: right;
        font-family: "Noto Sans Mono", "JetBrains Mono", monospace;
        font-variant-numeric: tabular-nums;
        font-size: .72rem;
        font-weight: 700;
        color: var(--pt-gold-light);
        opacity: .85;
    }
    .ptimer[data-level="warn"] .pt-bar > i {
        background: linear-gradient(90deg, #e08a00, #ffb300);
        box-shadow: 0 0 8px rgba(255, 179, 0, .7);
    }
    .ptimer[data-level="danger"] .pt-bar > i {
        background: linear-gradient(90deg, #b71c1c, #ff5252);
        box-shadow: 0 0 10px rgba(255, 82, 82, .9);
    }

    /* ---------- Idle (not this side's move) ---------- */
    .ptimer:not(.active):not(.paused-offline):not(.finished) {
        opacity: .78;
        filter: saturate(.8);
    }

    /* ---------- ACTIVE: orbiting gold light, lift, lantern ---------- */
    .ptimer.active {
        transform: scale(1.02);
        z-index: 2;
        box-shadow: 0 14px 34px rgba(0, 0, 0, .85),
                    0 0 26px rgba(255, 215, 0, .35),
                    inset 0 1px 8px rgba(255, 255, 255, .2);
        background:
            linear-gradient(var(--pt-fill-a), var(--pt-fill-b)) padding-box,
            conic-gradient(from var(--pt-angle),
                rgba(255, 215, 0, .15) 0deg,
                #fff2cc 40deg,
                var(--pt-gold) 70deg,
                rgba(255, 215, 0, .15) 130deg,
                rgba(255, 215, 0, .15) 360deg) border-box;
        animation: ptOrbit 3.2s linear infinite;
    }
    .ptimer.active::after { animation: ptSheen 3.6s ease-in-out infinite; }
    .ptimer.active .pt-seal {
        transform: scale(1.04) rotate(-4deg);
        box-shadow: 0 0 0 3px rgba(255, 215, 0, .55), 0 0 18px rgba(255, 215, 0, .65),
                    0 4px 10px rgba(0, 0, 0, .6), inset 0 2px 4px rgba(255, 255, 255, .35);
    }
    .ptimer.active .pt-turn {
        background: var(--pt-gold);
        box-shadow: 0 0 10px 2px rgba(255, 215, 0, .85);
        animation: ptPulse 1.2s ease-in-out infinite;
    }
    .ptimer.active .clock {
        color: var(--pt-gold-light);
        text-shadow: 0 0 14px rgba(255, 215, 0, .55), 0 2px 6px rgba(0, 0, 0, .7);
    }

    /* ---------- LOW TIME (≤ 60 s total) ---------- */
    .ptimer.low .clock { color: #ff8a80; }
    .ptimer.low.active .clock {
        color: #ff5252;
        text-shadow: 0 0 16px rgba(255, 82, 82, .85), 0 2px 6px rgba(0, 0, 0, .7);
        animation: ptThrob 1s ease-in-out infinite;
    }
    /* ---------- CRITICAL (≤ 10 s on this move or in total) ---------- */
    .ptimer.critical.active {
        box-shadow: 0 14px 34px rgba(0, 0, 0, .85), 0 0 34px rgba(255, 82, 82, .75),
                    inset 0 0 18px rgba(255, 82, 82, .3);
        animation: ptOrbit 1.4s linear infinite, ptAlarm .6s ease-in-out infinite alternate;
    }
    .ptimer.critical.active .clock { animation: ptThrob .5s ease-in-out infinite; }

    /* ---------- Waiting / finished ---------- */
    .ptimer.paused-offline {
        opacity: .5;
        filter: grayscale(.7);
        transform: none;
        box-shadow: none;
        pointer-events: none;
    }
    .ptimer.finished { opacity: .9; filter: saturate(.9); }
    .ptimer.expired {
        box-shadow: 0 0 28px rgba(255, 82, 82, .7), var(--liquid-shadow, 0 8px 32px rgba(0, 0, 0, .8));
    }
    .ptimer.expired .clock { color: #ff5252; }

    /* ---------- Caption (waiting for opponent) ----------
       An overlay on the bottom plaque instead of a block under it: it adds no height, so the
       board + clocks keep fitting the screen and nothing shifts when the opponent arrives. */
    .timer-caption {
        position: absolute;
        inset: 0;
        z-index: 3;
        display: none;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 10px;
        border-radius: clamp(12px, calc(var(--xq-timer-h, 64px) * .22), 16px);
        background: rgba(10, 10, 14, .72);
        -webkit-backdrop-filter: blur(3px);
        backdrop-filter: blur(3px);
        text-align: center;
        font-size: clamp(.62rem, calc(var(--xq-timer-h, 64px) * .0125rem), .8rem);
        font-weight: 700;
        letter-spacing: 1px;
        line-height: 1.2;
        text-transform: uppercase;
        color: var(--royal-gold-light, #fff2cc);
    }
    .timer-caption.show { display: flex; }

    /* ---------- Keyframes ---------- */
    @keyframes ptOrbit  { to { --pt-angle: 360deg; } }
    @keyframes ptSheen  { 0%, 55% { left: -60%; opacity: 0; } 62% { opacity: 1; } 100% { left: 160%; opacity: 0; } }
    @keyframes ptPulse  { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.5); opacity: .6; } }
    @keyframes ptThrob  { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.06); } }
    @keyframes ptAlarm  { from { filter: brightness(1); } to { filter: brightness(1.25) saturate(1.25); } }

    /* ---------- Small screens (size follows --xq-timer-h; only text/spacing is trimmed) ---------- */
    @media (max-width: 575.98px) {
        .ptimer { gap: 8px; padding-right: 10px; }
        .ptimer .clock { font-size: calc(var(--pt-h) * .44); letter-spacing: 0; }
        .pt-you { font-size: .55rem; padding: 1px 5px; }
        .ptimer.active { transform: scale(1.015); }
    }
    @media (max-width: 359.98px) {
        .pt-seal { display: none; }
    }
    /* Very short screens: drop the per-move text, keep the bar */
    @media (max-height: 560px) {
        .ptimer .move-clock { display: none; }
        .pt-you { display: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .ptimer, .ptimer * { animation: none !important; transition: none !important; }
    }
</style>
@else
<script>
    const roomCode = "{{ $roomCode }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // Đã đổi url('/') thành url('/api') để trỏ đúng vào group API của bạn
    const apiBase = "{{ url('/api') }}";

    // Refactored apiReq to support explicit HTTP methods (e.g., POST with no body)
    const apiReq = async (endpoint, methodOrBody = 'GET', bodyData = null) => {
        let method = 'GET';
        let body = null;

        if (typeof methodOrBody === 'string') {
            method = methodOrBody.toUpperCase();
            body = bodyData;
        } else if (typeof methodOrBody === 'object' && methodOrBody !== null) {
            method = 'POST';
            body = methodOrBody;
        }

        try {
            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            };

            if (body && method !== 'GET') {
                options.body = JSON.stringify(body);
            }

            const res = await fetch(`${apiBase}/${endpoint}`, options);
            if (!res.ok) throw new Error(`API Error: ${res.status}`);
            return await res.json();
        } catch (err) {
            console.error(err);
        }
    };

    const ui = {
        red:   { box: document.getElementById("red-box"),   clock: document.getElementById("red-clock"),   moveClock: document.getElementById("red-move-clock"),   moveBar: document.getElementById("red-move-bar") },
        black: { box: document.getElementById("black-box"), clock: document.getElementById("black-clock"), moveClock: document.getElementById("black-move-clock"), moveBar: document.getElementById("black-move-bar") }
    };
    const timerCaption = document.getElementById("timer-caption");

    const MOVE_TIME_LIMIT = 120;
    const LOW_TIME = 60;        // total time: plaque turns red
    const CRITICAL_TIME = 10;   // total or move time: alarm + tenths of a second
    let time = { red: 600, black: 600 };
    let serverMoveElapsed = 0, activePlayer = null, isGameOver = false;
    let tickInterval = null, localLastUpdate = Date.now();

    let playersOnline = 0;
    window.hasMatchStarted = false;

    const pad2 = (n) => n.toString().padStart(2, '0');
    const formatTime = (s) => `${Math.floor(s / 60)}:${pad2(Math.floor(s) % 60)}`;
    // Under 10 s the main clock shows tenths (0:09.4) so the last seconds feel tense and precise.
    const formatClock = (s, withTenths) => {
        if (withTenths && s < CRITICAL_TIME) {
            const t = Math.max(0, Math.floor(s * 10) / 10);
            return `0:${pad2(Math.floor(t))}.${Math.floor((t * 10) % 10)}`;
        }
        return formatTime(Math.ceil(s));
    };
    const stopAll = () => clearInterval(tickInterval);
    const startAll = () => { stopAll(); localLastUpdate = Date.now(); tickInterval = setInterval(updateUI, 100); };

    // Show / hide the "waiting for opponent" state.
    const setWaiting = (waiting) => {
        ['red', 'black'].forEach(side => ui[side].box.classList.toggle('paused-offline', waiting));
        if (timerCaption) timerCaption.classList.toggle('show', waiting);
    };

    const updateUI = () => {
        if (isGameOver) return;

        const elapsed = activePlayer ? (Date.now() - localLastUpdate) / 1000 : 0;
        const current = { red: time.red, black: time.black };
        const moveTime = { red: MOVE_TIME_LIMIT, black: MOVE_TIME_LIMIT };

        if (activePlayer) {
            current[activePlayer] = Math.max(0, current[activePlayer] - elapsed);
            moveTime[activePlayer] = Math.max(0, MOVE_TIME_LIMIT - (serverMoveElapsed + elapsed));
            if (moveTime[activePlayer] <= 0) current[activePlayer] = 0;
        }

        ['red', 'black'].forEach(side => {
            const isActive = activePlayer === side;
            const box = ui[side].box;
            const movePct = Math.max(0, Math.min(1, moveTime[side] / MOVE_TIME_LIMIT));

            ui[side].clock.innerText = formatClock(current[side], isActive);
            ui[side].moveClock.innerText = formatTime(Math.ceil(moveTime[side]));
            ui[side].moveBar.style.transform = `scaleX(${movePct})`;

            box.classList.toggle('active', isActive);
            box.classList.toggle('low', current[side] <= LOW_TIME);
            box.classList.toggle('critical', isActive && (current[side] <= CRITICAL_TIME || moveTime[side] <= CRITICAL_TIME));
            box.dataset.level = !isActive ? 'ok'
                : (moveTime[side] <= CRITICAL_TIME || movePct < 0.25) ? 'danger'
                : (movePct < 0.5 ? 'warn' : 'ok');
        });

        if (current.red <= 0 || current.black <= 0) {
            isGameOver = true;
            stopAll();
            activePlayer = null;
            ['red', 'black'].forEach(side => {
                const b = ui[side].box;
                b.classList.remove('active', 'critical');
                b.classList.add('finished');
                b.classList.toggle('expired', current[side] <= 0);
            });

            if (typeof updateResult === 'function') {
                const result = current.red <= 0 && current.black <= 0 ? '0' : (current.red <= 0 ? '-1' : '1');
                updateResult(roomCode, result);
            }
        }
    };

    // Called by the room page when the game ends for any other reason (checkmate, draw, resign):
    // stop the clocks where they are instead of letting the active side run out afterwards.
    window.freezeTimer = () => {
        if (isGameOver) return;
        updateUI();
        isGameOver = true;
        stopAll();
        activePlayer = null;
        ['red', 'black'].forEach(side => {
            ui[side].box.classList.remove('active', 'critical');
            ui[side].box.classList.add('finished');
        });
        if (timerCaption) timerCaption.classList.remove('show');
    };

    const syncTimerState = (serverData) => {
        if (isGameOver || !serverData) return;

        const sPlayer = serverData.active_player;
        let newActivePlayer = null;

        if (!sPlayer || sPlayer === 'waiting') {
            window.hasMatchStarted = false;
        } else {
            newActivePlayer = sPlayer.startsWith('paused:') ? sPlayer.split(':')[1] : sPlayer;
            window.hasMatchStarted = true;
            setWaiting(false);
        }

        const newRed = parseFloat(serverData.red_time);
        const newBlack = parseFloat(serverData.black_time);

        if (activePlayer !== newActivePlayer || Math.abs(time.red - newRed) > 1.5 || Math.abs(time.black - newBlack) > 1.5) {
            time.red = newRed;
            time.black = newBlack;
            serverMoveElapsed = parseFloat(serverData.move_elapsed || 0);
        }

        activePlayer = newActivePlayer;
        localLastUpdate = Date.now();

        updateUI();

        if (activePlayer) startAll();
        else stopAll();
    };

    const handlePresenceChange = () => {
        if (!window.hasMatchStarted && playersOnline >= 2) {
            window.hasMatchStarted = true;
            setWaiting(false);

            // Explicitly pass 'POST' method for startMatch
            apiReq(`startMatch/${roomCode}`, 'POST').then(() => {
                apiReq(`getTime/${roomCode}`).then(syncTimerState);
            });
        }

        if (!window.hasMatchStarted && playersOnline < 2) {
            setWaiting(true);
        }
    };

    const checkRoomReadiness = async () => {
        if (window.hasMatchStarted) return;

        try {
            const res = await apiReq('getRoomIds', { 'ma-phong': roomCode });

            const hasHost = res && (res.host_id || res.host_session);
            const hasGuest = res && (res.guest_id || res.guest_session);

            if (hasHost && hasGuest) {
                window.hasMatchStarted = true;
                setWaiting(false);

                // Explicitly pass 'POST' method for startMatch
                await apiReq(`startMatch/${roomCode}`, 'POST');
                apiReq(`getTime/${roomCode}`).then(syncTimerState);
            }
        } catch (e) {
            console.warn("Error checking room readiness:", e);
        }
    };

    window.switchTurn = async (rc, currentPlayer) => {
        const elapsed = (Date.now() - localLastUpdate) / 1000;

        if (time[currentPlayer]) {
            time[currentPlayer] = Math.max(0, time[currentPlayer] - elapsed);
            if (serverMoveElapsed + elapsed >= MOVE_TIME_LIMIT) time[currentPlayer] = 0;
        }

        activePlayer = currentPlayer === 'red' ? 'black' : 'red';
        serverMoveElapsed = 0;
        localLastUpdate = Date.now();

        updateUI();
        startAll();

        apiReq(`switchTurn/${rc}`, { current_player: currentPlayer }).then(serverData => {
            if (serverData) syncTimerState(serverData);
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        // Until we know a match is running, show the "waiting for opponent" state.
        if (!window.hasMatchStarted) setWaiting(true);

        if (typeof window.Echo === 'undefined' && typeof Echo !== 'undefined') {
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: '{{ config("broadcasting.connections.pusher.key") }}',
                cluster: '{{ config("broadcasting.connections.pusher.options.cluster", "ap1") }}',
                forceTLS: true,
                authEndpoint: '/custom/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-Token': '{{ csrf_token() }}'
                    }
                }
            });
        }

        if (window.Echo) {
            window.Echo.join(`room.${roomCode}`)
                .here(users => { playersOnline = users.length; handlePresenceChange(); })
                .joining(() => { playersOnline++; handlePresenceChange(); })
                .leaving(() => { playersOnline = Math.max(0, playersOnline - 1); handlePresenceChange(); });

            let syncTimeout = null;

            window.Echo.channel(`room.${roomCode}`)
                .listen('.room.updated', (e) => {
                    if (e.room) {
                        checkRoomReadiness();
                        clearTimeout(syncTimeout);
                        syncTimeout = setTimeout(() => {
                            apiReq(`getTime/${roomCode}`).then(syncTimerState);
                        }, 200);
                    }
                });
        }

        apiReq(`getTime/${roomCode}`).then(syncTimerState);

        const startMatchFallbackInterval = setInterval(() => {
            if (window.hasMatchStarted) {
                clearInterval(startMatchFallbackInterval);
            } else {
                checkRoomReadiness();
            }
        }, 3000);

        checkRoomReadiness();
    });
</script>
@endif
