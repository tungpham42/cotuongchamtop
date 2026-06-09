<div class="mini-timer">
    <div id="red-box" class="ptimer red-glass">
        <span class="icon">⏳</span>
        <span class="label">{{ __("Đỏ") }}</span>
        <span id="red-clock" class="clock">0:00</span>
    </div>
    <div id="black-box" class="ptimer black-glass">
        <span class="icon">⏳</span>
        <span class="label">{{ __("Đen") }}</span>
        <span id="black-clock" class="clock">0:00</span>
    </div>
</div>

<style>
    /* ==========================================================================
       COMPACT LIQUID GLASS TIMERS
       ========================================================================== */
    .mini-timer {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin: 5px auto;
        font-family: "Texturina", "Noto Sans JP", serif;
        user-select: none;
    }

    .ptimer {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 4px 14px;
        border-radius: 20px; /* Sleek compact pill shape */
        min-width: 120px;

        /* Base Liquid Glass Setup */
        background: var(--glass-bg-dark);
        backdrop-filter: var(--glass-blur);
        -webkit-backdrop-filter: var(--glass-blur);
        border: 1px solid var(--glass-border);
        border-top: 1px solid rgba(255, 215, 0, 0.5); /* Glossy top edge */
        box-shadow: var(--liquid-shadow), inset 0 2px 8px var(--liquid-highlight);

        color: var(--royal-gold-light);
        font-size: 0.95rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .ptimer .icon {
        font-size: 0.85rem;
        filter: drop-shadow(0 0 2px rgba(255, 255, 255, 0.5));
    }

    .ptimer .clock {
        font-family: "Noto Sans Mono", monospace;
        font-weight: 800;
        margin-left: auto; /* Pushes the clock text to the right edge */
    }

    /* Red Player Gloss & Glow */
    .red-glass .clock {
        color: #ff4d4d;
        text-shadow: 0 0 8px rgba(255, 0, 0, 0.6);
    }

    .red-glass.active {
        background: linear-gradient(90deg, rgba(138, 21, 21, 0.7), rgba(92, 10, 10, 0.4));
        border-color: rgba(255, 77, 77, 0.6);
        box-shadow: 0 0 15px rgba(255, 0, 0, 0.4), inset 0 2px 10px var(--liquid-highlight);
        transform: scale(1.05);
    }

    /* Black Player Gloss & Glow */
    .black-glass .clock {
        color: var(--royal-gold);
        text-shadow: 0 0 8px rgba(212, 175, 55, 0.6);
    }

    .black-glass.active {
        background: linear-gradient(90deg, rgba(40, 40, 40, 0.9), rgba(11, 12, 16, 0.7));
        border-color: var(--royal-gold);
        box-shadow: 0 0 15px rgba(212, 175, 55, 0.3), inset 0 2px 10px var(--liquid-highlight);
        transform: scale(1.05);
    }
</style>

<script>
    const roomCode = "{{ $roomCode }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' };

    const ui = {
        red: { clock: document.getElementById("red-clock"), box: document.getElementById("red-box") },
        black: { clock: document.getElementById("black-clock"), box: document.getElementById("black-box") }
    };

    let time = { red: 0, black: 0 };
    let activePlayer = null, isGameOver = false;
    let intervals = { tick: null };
    let localLastUpdate = Date.now();

    const formatTime = (s) => `${Math.floor(s / 60)}:${(Math.floor(s) % 60).toString().padStart(2, '0')}`;

    const apiReq = async (endpoint, body = null) => {
        try {
            const res = await fetch(endpoint, {
                method: body ? 'POST' : 'GET',
                headers, credentials: 'same-origin',
                body: body ? JSON.stringify(body) : null
            });
            if (!res.ok) throw new Error('API Error');
            return await res.json();
        } catch (err) { console.error(err); }
    };

    // --- Core UI Logic ---
    const updateUI = () => {
        if (isGameOver) return;

        let currentRed = time.red;
        let currentBlack = time.black;

        if (activePlayer) {
            let elapsed = (Date.now() - localLastUpdate) / 1000;
            if (activePlayer === 'red') currentRed = Math.max(0, currentRed - elapsed);
            if (activePlayer === 'black') currentBlack = Math.max(0, currentBlack - elapsed);
        }

        ui.red.clock.innerText = formatTime(Math.ceil(currentRed));
        ui.black.clock.innerText = formatTime(Math.ceil(currentBlack));

        if (currentRed <= 0 || currentBlack <= 0) {
            isGameOver = true;
            stopAll();
            activePlayer = null;
            ui.red.box.classList.remove("active");
            ui.black.box.classList.remove("active");

            if (typeof updateResult === 'function') {
                const result = currentRed <= 0 && currentBlack <= 0 ? '0' : (currentRed <= 0 ? '-1' : '1');
                updateResult(roomCode, result);
            }
        } else {
            ui.red.box.classList.toggle("active", activePlayer === "red");
            ui.black.box.classList.toggle("active", activePlayer === "black");
        }
    };

    const stopAll = () => clearInterval(intervals.tick);

    const startAll = () => {
        stopAll();
        localLastUpdate = Date.now();
        intervals.tick = setInterval(updateUI, 100);
    };

    // --- State Synchronization ---
    const syncTimerState = (serverData) => {
        if (isGameOver) return;
        time.red = parseFloat(serverData.red_time);
        time.black = parseFloat(serverData.black_time);
        activePlayer = serverData.active_player;
        localLastUpdate = Date.now();

        updateUI();

        if (activePlayer) startAll();
        else stopAll();
    };

    // --- Server Communication ---
    window.pauseTimer = (rc, p) => apiReq(`/pauseTimer/${rc}/${p}`);
    window.startTimer = (rc, p) => apiReq(`/startTimer/${rc}/${p}`);

    window.switchTurn = async (rc, currentPlayer) => {
        // 1. Optimistic Update (Immediate UI response)
        const elapsed = (Date.now() - localLastUpdate) / 1000;
        if (currentPlayer === 'red') time.red = Math.max(0, time.red - elapsed);
        if (currentPlayer === 'black') time.black = Math.max(0, time.black - elapsed);

        activePlayer = currentPlayer === 'red' ? 'black' : 'red';
        localLastUpdate = Date.now();
        updateUI();
        startAll();

        // 2. Trigger the server
        await apiReq(`/switchTurn/${rc}`, { current_player: currentPlayer });
    };

    // --- PUSHER (Laravel Echo) WebSocket Listener ---
    if (typeof Echo !== 'undefined') {
        Echo.channel(`room.${roomCode}`)
            .listen('.room.updated', (e) => {
                if (e.room) {
                    syncTimerState({
                        red_time: e.room.red_time,
                        black_time: e.room.black_time,
                        active_player: e.room.active_player
                    });
                }
            });
    }

    // --- Initialization ---
    (async () => {
        const data = await apiReq(`/getTime/${roomCode}`);
        if (data && !data.error) syncTimerState(data);
    })();
</script>
