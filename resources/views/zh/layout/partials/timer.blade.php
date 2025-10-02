<div class="timer-container">
    <div>⏳ 红色的: <span id="red-clock">0:00</span></div>
    <div>⏳ 黑色的: <span id="black-clock">0:00</span></div>
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
    const roomCode = "{{ $roomCode }}"; // truyền từ Controller sang view
    let redTime = 0;
    let blackTime = 0;
    let activePlayer = null;
    let lastFetchTime = Date.now();
    let saveInterval = null;

    function startRealtimeSave() {
        if (saveInterval) clearInterval(saveInterval);
        saveInterval = setInterval(async () => {
            if (!activePlayer) return;

            let now = Date.now();
            let elapsed = Math.floor((now - lastFetchTime) / 1000);

            let redToSave = redTime;
            let blackToSave = blackTime;

            if (activePlayer === 'red') redToSave = Math.max(0, redTime - elapsed);
            if (activePlayer === 'black') blackToSave = Math.max(0, blackTime - elapsed);

            await fetch(`/saveTime/${roomCode}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ red_time: redToSave, black_time: blackToSave }),
            });
        }, 1000); // mỗi giây
    }

    function stopRealtimeSave() {
        if (saveInterval) clearInterval(saveInterval);
    }
    async function pauseTimer(roomCode, player) {
        return fetch(`/pauseTimer/${roomCode}/${player}`, { method: "POST" });
    }

    async function startTimer(roomCode, player) {
        return fetch(`/startTimer/${roomCode}/${player}`, { method: "POST" });
    }

    async function switchTurn(roomCode, currentPlayer) {
        stopRealtimeSave(); // dừng save thời gian cũ
        const nextPlayer = currentPlayer === "red" ? "black" : "red";
        await pauseTimer(roomCode, currentPlayer);
        await startTimer(roomCode, nextPlayer);
        startRealtimeSave(); // bắt đầu save cho người mới
        console.log(`Chuyển lượt: ${currentPlayer} → ${nextPlayer}`);
        fetchTime();
    }

    async function fetchTime() {
        try {
            const res = await fetch(`/getTime/${roomCode}`);
            const data = await res.json();

            redTime = data.red_time;
            blackTime = data.black_time;
            activePlayer = data.active_player;
            lastFetchTime = Date.now();

            updateClockDisplay();
        } catch (err) {
            console.error("Lỗi fetchTime:", err);
        }
    }

    function updateClockDisplay() {
        let now = Date.now();
        let elapsed = Math.floor((now - lastFetchTime) / 1000);

        let redDisplay = redTime;
        let blackDisplay = blackTime;

        if (activePlayer === "red") {
            redDisplay = Math.max(0, redTime - elapsed);
        } else if (activePlayer === "black") {
            blackDisplay = Math.max(0, blackTime - elapsed);
        }

        document.getElementById("red-clock").innerText = formatTime(redDisplay);
        document.getElementById("black-clock").innerText = formatTime(blackDisplay);

        // KIỂM TRA HẾT GIỜ
        if ((redDisplay <= 0 || blackDisplay <= 0)) {
            stopRealtimeSave();
            activePlayer = null; // dừng lượt

            // remove class active luôn khi hết giờ
            document.getElementById("red-clock").parentElement.classList.remove("active");
            document.getElementById("black-clock").parentElement.classList.remove("active");

            if (redDisplay == 0 && blackDisplay == 0) {
                updateResult('{{ $roomCode }}', '0');
            } else if (redDisplay == 0) {
                updateResult('{{ $roomCode }}', '-1');
            } else if (blackDisplay == 0) {
                updateResult('{{ $roomCode }}', '1');
            }
        } else {
            // chỉ toggle khi còn đang chơi
            document.getElementById("red-clock").parentElement.classList.toggle("active", activePlayer === "red");
            document.getElementById("black-clock").parentElement.classList.toggle("active", activePlayer === "black");
        }
    }

    function formatTime(seconds) {
        let m = Math.floor(seconds / 60);
        let s = seconds % 60;
        return `${m}:${s.toString().padStart(2, '0')}`;
    }

    // Khởi động timer
    fetchTime();
    setInterval(updateClockDisplay, 1000);
    setInterval(fetchTime, 5000);
</script>
