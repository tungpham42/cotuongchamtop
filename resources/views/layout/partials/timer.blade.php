<div class="mini-timer">
    <div id="red-box" class="ptimer">
        <span class="icon">⏳</span> {{ __("Đỏ") }}: <span id="red-clock">0:00</span>
    </div>
    <div id="black-box" class="ptimer">
        <span class="icon">⏳</span> {{ __("Đen") }}: <span id="black-clock">0:00</span>
    </div>
</div>

<style>
    .mini-timer {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin: 10px auto;
        font-family: "Texturina", "Noto Sans JP", serif;
        font-size: 1.1rem;
        font-weight: bold;
        color: var(--royal-gold-light);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .ptimer {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 6px;
        min-width: 130px;
        background: rgba(28, 17, 10, 0.85);
        border: var(--royal-border);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.8), inset 0 0 5px rgba(212, 175, 55, 0.1);
        transition: all 0.3s ease;
    }

    .ptimer .icon { filter: drop-shadow(1px 1px 1px rgba(0,0,0,0.8)); }

    #red-clock, #black-clock {
        border-radius: 3px;
        padding: 3px 8px;
        border: 1px solid var(--royal-gold);
        font-family: "Noto Sans Mono", monospace;
        font-weight: 800;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
    }

    #red-clock { background: linear-gradient(to bottom, var(--royal-red), #5c0a0a); }
    #black-clock { color: var(--royal-gold); background: linear-gradient(to bottom, #2a1910, var(--royal-bg)); }

    .ptimer.active {
        border-color: var(--royal-gold);
        background: rgba(74, 37, 17, 0.9);
        box-shadow: 0 0 12px rgba(212, 175, 55, 0.5), inset 0 0 8px rgba(212, 175, 55, 0.2);
        transform: scale(1.03);
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
    let localLastUpdate = Date.now(); // Anchor for smooth 100ms interpolation

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
        localLastUpdate = Date.now(); // Snap our local anchor back to reality

        updateUI();

        if (activePlayer) startAll();
        else stopAll();
    };

    // --- Server Communication ---
    window.pauseTimer = (rc, p) => apiReq(`/pauseTimer/${rc}/${p}`);
    window.startTimer = (rc, p) => apiReq(`/startTimer/${rc}/${p}`);

    window.switchTurn = async (rc, currentPlayer) => {
        // 1. Optimistic Update (Immediate UI response for the player who just moved)
        const elapsed = (Date.now() - localLastUpdate) / 1000;
        if (currentPlayer === 'red') time.red = Math.max(0, time.red - elapsed);
        if (currentPlayer === 'black') time.black = Math.max(0, time.black - elapsed);

        activePlayer = currentPlayer === 'red' ? 'black' : 'red';
        localLastUpdate = Date.now();
        updateUI();
        startAll();

        // 2. Trigger the server (The server will process this and fire the Pusher event to everyone)
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

    // --- Initialization (Load initial time on mount) ---
    (async () => {
        const data = await apiReq(`/getTime/${roomCode}`);
        if (data && !data.error) syncTimerState(data);
    })();
</script>
