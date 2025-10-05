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
    let isGameOver = false;

    function startRealtimeSave() {
        if (saveInterval) clearInterval(saveInterval);
        saveInterval = setInterval(async () => {
            if (!activePlayer || isGameOver) return;

            await fetch(`/saveTime/${roomCode}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ red_time: redTime, black_time: blackTime }),
            });
        }, 1000); // Save every second
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
        stopRealtimeSave();
        const nextPlayer = currentPlayer === "red" ? "black" : "red";
        await pauseTimer(roomCode, currentPlayer);
        await startTimer(roomCode, nextPlayer);
        startRealtimeSave();
        console.log(`Turn switched: ${currentPlayer} → ${nextPlayer}`);
        fetchTime();
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

            redTime = data.red_time;
            blackTime = data.black_time;
            activePlayer = data.active_player;

            updateClockDisplay();
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
            updateResult('{{ $roomCode }}', result);
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

    // Initialize timer
    fetchTime();
    setInterval(fetchTime, 1000); // Fetch time every second
</script>
