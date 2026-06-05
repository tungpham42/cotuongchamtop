<div class="timer-container">
    <div class="player-timer red-player">
        <span class="icon">⏳</span> {{ __("Đỏ") }}: <span id="red-clock">0:00</span>
    </div>
    <div class="player-timer black-player">
        <span class="icon">⏳</span> {{ __("Đen") }}: <span id="black-clock">0:00</span>
    </div>
</div>

<style>
    .timer-container {
        display: flex;
        justify-content: center;
        gap: 40px;
        margin: 20px auto;
        font-family: "Texturina", "Noto Sans JP", serif; /* Matches royal headings */
        font-size: 1.5rem;
        font-weight: bold;
        color: var(--royal-gold-light); /* Ivory/Gold text */
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .timer-container .player-timer {
        padding: 12px 24px;
        border-radius: 8px;
        min-width: 160px;
        text-align: center;
        background: rgba(28, 17, 10, 0.85); /* Transparent dark wood background */
        border: var(--royal-border); /* Royal double gold border */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.8), inset 0 0 10px rgba(212, 175, 55, 0.1);
        transition: all 0.4s ease-in-out;
    }

    .timer-container .player-timer .icon {
        filter: drop-shadow(1px 1px 2px rgba(0,0,0,0.8));
    }

    #red-clock, #black-clock {
        border-radius: 4px;
        padding: 6px 12px;
        display: inline-block;
        border: 1px solid var(--royal-gold);
        font-family: "Noto Sans Mono", monospace; /* Monospace for numbers */
        font-weight: 800;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
    }

    #red-clock {
        color: var(--royal-gold-light);
        background: linear-gradient(to bottom, var(--royal-red), #5c0a0a); /* Red gradient */
    }

    #black-clock {
        color: var(--royal-gold);
        background: linear-gradient(to bottom, #2a1910, var(--royal-bg)); /* Dark wood gradient */
    }

    /* Active State Glow */
    .timer-container .player-timer.active {
        border-color: var(--royal-gold);
        background: rgba(74, 37, 17, 0.9); /* Lighter royal wood background */
        box-shadow: 0 0 20px rgba(212, 175, 55, 0.6), inset 0 0 15px rgba(212, 175, 55, 0.2);
        transform: scale(1.05);
    }
</style>

<script>
    const roomCode = "{{ $roomCode }}"; // Transmitted from Controller to view
    let redTime = 0;
    let blackTime = 0;
    let activePlayer = null;
    let saveInterval = null;
    let tickInterval = null; // Interval for local ticking
    let isGameOver = false;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const defaultHeaders = {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken || '',
        'X-Requested-With': 'XMLHttpRequest',
    };

    // --- Time Ticking Logic ---

    function startLocalTick() {
        if (tickInterval) clearInterval(tickInterval);
        tickInterval = setInterval(() => {
            if (isGameOver || !activePlayer) return;

            if (activePlayer === 'red') {
                redTime = Math.max(0, redTime - 1);
            } else if (activePlayer === 'black') {
                blackTime = Math.max(0, blackTime - 1);
            }

            updateClockDisplay();
        }, 1000);
    }

    function stopLocalTick() {
        if (tickInterval) clearInterval(tickInterval);
    }

    // --- Server Communication ---

    function startRealtimeSave() {
        if (saveInterval) clearInterval(saveInterval);
        saveInterval = setInterval(async () => {
            if (!activePlayer || isGameOver) return;

            await fetch(`/saveTime/${roomCode}`, {
                method: 'POST',
                headers: defaultHeaders,
                credentials: 'same-origin',
                body: JSON.stringify({ red_time: redTime, black_time: blackTime }),
            });
        }, 5000); // Save every 5 seconds
    }

    function stopRealtimeSave() {
        if (saveInterval) clearInterval(saveInterval);
    }

    async function pauseTimer(roomCode, player) {
        try {
            const response = await fetch(`/pauseTimer/${roomCode}/${player}`, { method: "POST", headers: defaultHeaders, credentials: 'same-origin' });
            if (!response.ok) throw new Error('Failed to pause timer');
            return response.json();
        } catch (err) {
            console.error("Error pausing timer:", err);
        }
    }

    async function startTimer(roomCode, player) {
        try {
            const response = await fetch(`/startTimer/${roomCode}/${player}`, { method: "POST", headers: defaultHeaders, credentials: 'same-origin' });
            if (!response.ok) throw new Error('Failed to start timer');
            return response.json();
        } catch (err) {
            console.error("Error starting timer:", err);
        }
    }

    async function switchTurn(roomCode, currentPlayer) {
        stopLocalTick();
        stopRealtimeSave();
        try {
            const res = await fetch(`/switchTurn/${roomCode}`, {
                method: "POST",
                headers: defaultHeaders,
                credentials: 'same-origin',
                body: JSON.stringify({ current_player: currentPlayer }),
            });

            if (!res.ok) throw new Error('Failed to switch turn');
            const data = await res.json();

            redTime = data.red_time;
            blackTime = data.black_time;
            activePlayer = data.active_player;
            updateClockDisplay();

            startLocalTick();
            startRealtimeSave();
        } catch (err) {
            console.error("Error switching turn:", err);
        }
    }

    async function fetchTime() {
        if (isGameOver) return;
        try {
            const res = await fetch(`/getTime/${roomCode}`, { credentials: 'same-origin' });
            if (!res.ok) throw new Error('Failed to fetch time');
            const data = await res.json();

            if (data.error) {
                console.error("Fetch time error:", data.error);
                return;
            }

            redTime = data.red_time;
            blackTime = data.black_time;
            const previousActivePlayer = activePlayer;
            activePlayer = data.active_player;

            updateClockDisplay();

            if (activePlayer && activePlayer !== previousActivePlayer) {
                 stopLocalTick();
                 startLocalTick();
            } else if (!activePlayer) {
                 stopLocalTick();
                 stopRealtimeSave();
            }

            return data;
        } catch (err) {
            console.error("Error fetching time:", err);
            updateClockDisplay();
        }
    }

    function updateClockDisplay() {
        if (isGameOver) return;

        document.getElementById("red-clock").innerText = formatTime(redTime);
        document.getElementById("black-clock").innerText = formatTime(blackTime);

        // Check for game over due to time
        if (redTime <= 0 || blackTime <= 0) {
            isGameOver = true;
            stopLocalTick();
            stopRealtimeSave();
            activePlayer = null;

            document.getElementById("red-clock").parentElement.classList.remove("active");
            document.getElementById("black-clock").parentElement.classList.remove("active");

            let result;
            if (redTime <= 0 && blackTime <= 0) {
                result = '0'; // Draw
            } else if (redTime <= 0) {
                result = '-1'; // Black wins
            } else if (blackTime <= 0) {
                result = '1'; // Red wins
            }
            if (typeof updateResult === 'function') {
                updateResult('{{ $roomCode }}', result);
            }
        } else {
            // This is the line that triggers the smooth CSS transition
            document.getElementById("red-clock").parentElement.classList.toggle("active", activePlayer === "red");
            document.getElementById("black-clock").parentElement.classList.toggle("active", activePlayer === "black");
        }
    }

    function formatTime(seconds) {
        let m = Math.floor(seconds / 60);
        let s = seconds % 60;
        return `${m}:${s.toString().padStart(2, '0')}`;
    }

    // --- Initialization ---

    async function initializeTimer() {
        const data = await fetchTime();
        if (data && data.active_player) {
            startLocalTick();
            startRealtimeSave();
        }
    }

    initializeTimer();

    // Sync with server every 10 seconds as a safety net
    setInterval(fetchTime, 10000);
</script>
