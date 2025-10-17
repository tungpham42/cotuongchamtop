<div class="timer-container">
    <div>⏳ Red: <span id="red-clock">0:00</span></div>
    <div>⏳ Black: <span id="black-clock">0:00</span></div>
</div>

<style>
    .timer-container {
        display: flex;
        justify-content: center;
        gap: 40px;
        margin: 20px auto;
        font-family: 'Arial', sans-serif;
        font-size: 1.5rem;
        font-weight: bold;
    }

    .timer-container div {
        padding: 12px 24px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        min-width: 120px;
        text-align: center;
    }

    #red-clock {
        color: #fff;
        background: #d9534f;
        border-radius: 8px;
        padding: 6px 12px;
        display: inline-block;
    }

    #black-clock {
        color: #fff;
        background: #343a40;
        border-radius: 8px;
        padding: 6px 12px;
        display: inline-block;
    }

    .timer-container div.active {
        border: 3px solid gold;
    }
</style>

<script>
    const roomCode = "{{ $roomCode }}"; // Transmitted from Controller to view
    let redTime = 0;
    let blackTime = 0;
    let activePlayer = null;
    let saveInterval = null;
    let tickInterval = null; // New interval for local ticking
    let isGameOver = false;

    // --- Time Ticking Logic (Fix for flickering/lagging) ---

    function startLocalTick() {
        if (tickInterval) clearInterval(tickInterval);
        tickInterval = setInterval(() => {
            if (isGameOver || !activePlayer) return;

            if (activePlayer === 'red') {
                redTime = Math.max(0, redTime - 1);
            } else if (activePlayer === 'black') {
                blackTime = Math.max(0, blackTime - 1);
            }

            // Update display locally every second for smooth ticking
            updateClockDisplay();
        }, 1000);
    }

    function stopLocalTick() {
        if (tickInterval) clearInterval(tickInterval);
    }

    // --- Server Communication (Modified) ---

    function startRealtimeSave() {
        if (saveInterval) clearInterval(saveInterval);
        // Save less frequently (e.g., every 5 seconds) since the local tick handles display
        saveInterval = setInterval(async () => {
            if (!activePlayer || isGameOver) return;

            await fetch(`/saveTime/${roomCode}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ red_time: redTime, black_time: blackTime }),
            });
        }, 5000); // Save every 5 seconds
    }

    function stopRealtimeSave() {
        if (saveInterval) clearInterval(saveInterval);
    }

    async function pauseTimer(roomCode, player) {
        try {
            const response = await fetch(`/pauseTimer/${roomCode}/${player}`, { method: "POST" });
            if (!response.ok) throw new Error('Failed to pause timer');
            return response.json();
        } catch (err) {
            console.error("Error pausing timer:", err);
        }
    }

    async function startTimer(roomCode, player) {
        try {
            const response = await fetch(`/startTimer/${roomCode}/${player}`, { method: "POST" });
            if (!response.ok) throw new Error('Failed to start timer');
            return response.json();
        } catch (err) {
            console.error("Error starting timer:", err);
        }
    }

    async function switchTurn(roomCode, currentPlayer) {
        stopLocalTick();
        stopRealtimeSave();
        const nextPlayer = currentPlayer === "red" ? "black" : "red";

        // 1. Pause current
        await pauseTimer(roomCode, currentPlayer);
        // 2. Start next
        await startTimer(roomCode, nextPlayer);

        console.log(`Turn switched: ${currentPlayer} → ${nextPlayer}`);

        // 3. Synchronize time from server and restart local intervals
        await fetchTime();
        startLocalTick();
        startRealtimeSave();
    }

    async function fetchTime() {
        if (isGameOver) return;
        try {
            const res = await fetch(`/getTime/${roomCode}`);
            if (!res.ok) throw new Error('Failed to fetch time');
            const data = await res.json();

            if (data.error) {
                console.error("Fetch time error:", data.error);
                return;
            }

            // Update local state with server data (Synchronization)
            redTime = data.red_time;
            blackTime = data.black_time;
            const previousActivePlayer = activePlayer;
            activePlayer = data.active_player;

            updateClockDisplay();

            // Re-evaluate ticking if the active player status has changed
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
            // Fallback to client-side update if server fetch fails
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
            // Assume updateResult is a global function defined elsewhere
            if (typeof updateResult === 'function') {
                updateResult('{{ $roomCode }}', result);
            }
        } else {
            document.getElementById("red-clock").parentElement.classList.toggle("active", activePlayer === "red");
            document.getElementById("black-clock").parentElement.classList.toggle("active", activePlayer === "black");
        }
    }

    function formatTime(seconds) {
        let m = Math.floor(seconds / 60);
        let s = seconds % 60;
        return `${m}:${s.toString().padStart(2, '0')}`;
    }

    // --- Initialization (Modified) ---

    async function initializeTimer() {
        const data = await fetchTime();
        if (data && data.active_player) {
            startLocalTick();
            startRealtimeSave();
        }
    }

    initializeTimer();

    // Safety net: Synchronize time with server every 10 seconds (much less frequent than original)
    setInterval(fetchTime, 10000);
</script>
