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
        gap: 12px; /* Reduced from 40px */
        margin: 10px auto; /* Reduced margin */
        font-family: "Texturina", "Noto Sans JP", serif;
        font-size: 1.1rem; /* Reduced from 1.5rem */
        font-weight: bold;
        color: var(--royal-gold-light);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .ptimer {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px; /* Tighter padding */
        border-radius: 6px;
        min-width: 130px; /* Reduced from 160px */
        background: rgba(28, 17, 10, 0.85);
        border: var(--royal-border);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.8), inset 0 0 5px rgba(212, 175, 55, 0.1);
        transition: all 0.3s ease;
    }

    .ptimer .icon { filter: drop-shadow(1px 1px 1px rgba(0,0,0,0.8)); }

    #red-clock, #black-clock {
        border-radius: 3px;
        padding: 3px 8px; /* Tighter padding */
        border: 1px solid var(--royal-gold);
        font-family: "Noto Sans Mono", monospace;
        font-weight: 800;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
    }

    #red-clock { background: linear-gradient(to bottom, var(--royal-red), #5c0a0a); }
    #black-clock { color: var(--royal-gold); background: linear-gradient(to bottom, #2a1910, var(--royal-bg)); }

    /* Active State Glow */
    .ptimer.active {
        border-color: var(--royal-gold);
        background: rgba(74, 37, 17, 0.9);
        box-shadow: 0 0 12px rgba(212, 175, 55, 0.5), inset 0 0 8px rgba(212, 175, 55, 0.2);
        transform: scale(1.03); /* Subtler scale to save layout shifting */
    }
</style>

<script>
    const roomCode = "{{ $roomCode }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' };

    // Cache DOM to save space and processing
    const ui = {
        red: { clock: document.getElementById("red-clock"), box: document.getElementById("red-box") },
        black: { clock: document.getElementById("black-clock"), box: document.getElementById("black-box") }
    };

    let time = { red: 0, black: 0 };
    let activePlayer = null, isGameOver = false;
    let intervals = { tick: null, save: null };

    // --- Helpers ---
    const formatTime = (s) => `${Math.floor(s / 60)}:${(s % 60).toString().padStart(2, '0')}`;

    // Unified API caller
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

    // --- Core Logic ---
    const updateUI = () => {
        if (isGameOver) return;
        ui.red.clock.innerText = formatTime(time.red);
        ui.black.clock.innerText = formatTime(time.black);

        if (time.red <= 0 || time.black <= 0) {
            isGameOver = true;
            stopAll();
            activePlayer = null;
            ui.red.box.classList.remove("active");
            ui.black.box.classList.remove("active");

            if (typeof updateResult === 'function') {
                const result = time.red <= 0 && time.black <= 0 ? '0' : (time.red <= 0 ? '-1' : '1');
                updateResult(roomCode, result);
            }
        } else {
            ui.red.box.classList.toggle("active", activePlayer === "red");
            ui.black.box.classList.toggle("active", activePlayer === "black");
        }
    };

    const stopAll = () => { clearInterval(intervals.tick); clearInterval(intervals.save); };

    const startAll = () => {
        stopAll();
        intervals.tick = setInterval(() => {
            if (!isGameOver && activePlayer) {
                time[activePlayer] = Math.max(0, time[activePlayer] - 1);
                updateUI();
            }
        }, 1000);

        intervals.save = setInterval(() => {
            if (!isGameOver && activePlayer) apiReq(`/saveTime/${roomCode}`, { red_time: time.red, black_time: time.black });
        }, 5000);
    };

    // --- Server Communication ---
    window.pauseTimer = (rc, p) => apiReq(`/pauseTimer/${rc}/${p}`);
    window.startTimer = (rc, p) => apiReq(`/startTimer/${rc}/${p}`);

    window.switchTurn = async (rc, currentPlayer) => {
        stopAll();
        const data = await apiReq(`/switchTurn/${rc}`, { current_player: currentPlayer });
        if (data) {
            time.red = data.red_time; time.black = data.black_time;
            activePlayer = data.active_player;
            updateUI();
            startAll();
        }
    };

    const fetchTime = async () => {
        if (isGameOver) return;
        const data = await apiReq(`/getTime/${roomCode}`);
        if (!data || data.error) return;

        const prevActive = activePlayer;
        activePlayer = data.active_player;
        time.red = data.red_time; time.black = data.black_time;

        updateUI();

        if (activePlayer && activePlayer !== prevActive) startAll();
        else if (!activePlayer) stopAll();
        return data;
    };

    // --- Initialization ---
    (async () => {
        const data = await fetchTime();
        if (data && data.active_player) startAll();
    })();

    setInterval(fetchTime, 10000); // 10s safety sync
</script>
