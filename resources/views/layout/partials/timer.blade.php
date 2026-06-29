<div class="mini-timer">
    <div id="red-box" class="ptimer red-glass">
        <div class="timer-details">
            <span class="label">{{ __("Đỏ") }}</span>
            <span id="red-move-clock" class="move-clock">2:00</span>
        </div>
        <span id="red-clock" class="clock">10:00</span>
    </div>

    <div id="black-box" class="ptimer black-glass">
        <div class="timer-details">
            <span class="label">{{ __("Đen") }}</span>
            <span id="black-move-clock" class="move-clock">2:00</span>
        </div>
        <span id="black-clock" class="clock">10:00</span>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Exo+2:ital,wght@0,100..900;1,100..900&display=swap');

    /* ==========================================================================
       GLOSSY & COMPACT DUAL-TIMERS (10M + 2M/Move)
       ========================================================================== */
    .mini-timer {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 16px;
        margin: 10px auto;
        font-family: "Exo 2", "Plus Jakarta Sans", sans-serif;
        user-select: none;
    }

    .ptimer {
        position: relative;
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 8px 20px;
        border-radius: 40px;
        min-width: 150px;
        overflow: hidden;
        background: linear-gradient(145deg, #2a2d38, #1a1c23);
        box-shadow:
            6px 6px 12px rgba(0, 0, 0, 0.6),
            -2px -2px 8px rgba(255, 255, 255, 0.03),
            inset 0 1px 1px rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.05);
        color: #e0e0e0;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .ptimer::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; height: 45%;
        background: linear-gradient(to bottom, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0) 100%);
        border-radius: 40px 40px 0 0;
        pointer-events: none;
    }

    .timer-details {
        display: flex;
        flex-direction: column;
        align-items: center;
        z-index: 1;
    }

    .timer-details .label {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.9;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .timer-details .move-clock {
        font-size: 0.75rem;
        font-weight: 700;
        font-family: "JetBrains Mono", "Courier New", monospace;
        background: rgba(0, 0, 0, 0.5);
        padding: 2px 8px;
        border-radius: 6px;
        margin-top: 4px;
        box-shadow: inset 0 1px 4px rgba(0,0,0,0.8);
        transition: all 0.2s ease;
    }

    .ptimer .clock {
        font-family: "JetBrains Mono", "Courier New", monospace;
        font-size: 1.4rem;
        font-weight: 900;
        margin-left: auto;
        z-index: 1;
        text-shadow: 0 2px 4px rgba(0,0,0,0.6);
    }

    /* Colors for RED */
    .red-glass .clock, .red-glass .label { color: #ff5252; }
    .red-glass .move-clock { color: #ff8a80; }
    .red-glass.active {
        background: linear-gradient(145deg, #5c1616, #300808);
        border-color: rgba(255, 82, 82, 0.4);
        box-shadow: 0 0 20px rgba(255, 82, 82, 0.3), inset 0 2px 4px rgba(255, 138, 128, 0.2);
        transform: translateY(-3px) scale(1.03);
    }

    /* Colors for BLACK */
    .black-glass .clock, .black-glass .label { color: #ffd54f; }
    .black-glass .move-clock { color: #ffe082; }
    .black-glass.active {
        background: linear-gradient(145deg, #2b2b2b, #111111);
        border-color: rgba(255, 213, 79, 0.4);
        box-shadow: 0 0 20px rgba(255, 213, 79, 0.2), inset 0 2px 4px rgba(255, 224, 130, 0.2);
        transform: translateY(-3px) scale(1.03);
    }

    /* Utility Classes */
    .ptimer.paused-offline {
        opacity: 0.5;
        filter: grayscale(80%);
        pointer-events: none;
        transform: translateY(0) scale(1);
        box-shadow: none;
    }

    .move-clock.danger {
        color: #ffffff !important;
        background: #e53935 !important;
        box-shadow: 0 0 12px #e53935, inset 0 1px 2px rgba(0,0,0,0.5) !important;
        animation: urgent-strobe 0.6s infinite alternate cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes urgent-strobe {
        0% { opacity: 1; transform: scale(1); }
        100% { opacity: 0.85; transform: scale(1.05); }
    }
</style>

<script>
    const roomCode = "{{ $roomCode }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // Đã đổi url('/') thành url('/api') để trỏ đúng vào group API của bạn
    const apiBase = "{{ url('/api') }}";

    const apiReq = async (endpoint, body = null) => {
        try {
            const res = await fetch(`${apiBase}/${endpoint}`, {
                method: body ? 'POST' : 'GET',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: body ? JSON.stringify(body) : null
            });
            if (!res.ok) throw new Error(`API Error: ${res.status}`);
            return await res.json();
        } catch (err) { console.error(err); }
    };

    const ui = {
        red: { box: document.getElementById("red-box"), clock: document.getElementById("red-clock"), moveClock: document.getElementById("red-move-clock") },
        black: { box: document.getElementById("black-box"), clock: document.getElementById("black-clock"), moveClock: document.getElementById("black-move-clock") }
    };

    const MOVE_TIME_LIMIT = 120;
    let time = { red: 600, black: 600 };
    let serverMoveElapsed = 0, activePlayer = null, isGameOver = false;
    let tickInterval = null, localLastUpdate = Date.now();

    let playersOnline = 0;
    window.hasMatchStarted = false;

    const formatTime = (s) => `${Math.floor(s / 60)}:${(Math.floor(s) % 60).toString().padStart(2, '0')}`;
    const stopAll = () => clearInterval(tickInterval);
    const startAll = () => { stopAll(); localLastUpdate = Date.now(); tickInterval = setInterval(updateUI, 100); };

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
            ui[side].clock.innerText = formatTime(Math.ceil(current[side]));
            ui[side].moveClock.innerText = formatTime(Math.ceil(moveTime[side]));
            ui[side].moveClock.classList.toggle('danger', moveTime[side] <= 10 && activePlayer === side);
            ui[side].box.classList.toggle('active', activePlayer === side);
        });

        if (current.red <= 0 || current.black <= 0) {
            isGameOver = true;
            stopAll();
            activePlayer = null;
            ui.red.box.classList.remove("active");
            ui.black.box.classList.remove("active");

            if (typeof updateResult === 'function') {
                const result = current.red <= 0 && current.black <= 0 ? '0' : (current.red <= 0 ? '-1' : '1');
                updateResult(roomCode, result);
            }
        }
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
        }

        const newRed = parseFloat(serverData.red_time);
        const newBlack = parseFloat(serverData.black_time);

        // Kỹ thuật Threshold: CHỈ ghi đè thời gian nếu sai số giữa Client và Server
        // lớn hơn 1.5 giây, HOẶC khi có sự thay đổi lượt đi.
        // Điều này giúp timer trên giao diện không bị giật lùi do độ trễ mạng (Ping).
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
            ui.red.box.classList.remove('paused-offline');
            ui.black.box.classList.remove('paused-offline');

            apiReq(`startMatch/${roomCode}`).then(() => {
                apiReq(`getTime/${roomCode}`).then(syncTimerState);
            });
        }

        if (!window.hasMatchStarted && playersOnline < 2) {
            ui.red.box.classList.add('paused-offline');
            ui.black.box.classList.add('paused-offline');
        }
    };

    // FALLBACK MỚI THÊM: Chủ động gọi API kiểm tra phòng
    const checkRoomReadiness = async () => {
        if (window.hasMatchStarted) return;

        try {
            const res = await apiReq('getRoomIds', { 'ma-phong': roomCode });

            // Phòng đủ điều kiện khi có cả Host (ID hoặc Session) và Guest (ID hoặc Session)
            const hasHost = res && (res.host_id || res.host_session);
            const hasGuest = res && (res.guest_id || res.guest_session);

            if (hasHost && hasGuest) {
                window.hasMatchStarted = true;
                ui.red.box.classList.remove('paused-offline');
                ui.black.box.classList.remove('paused-offline');

                await apiReq(`startMatch/${roomCode}`);
                apiReq(`getTime/${roomCode}`).then(syncTimerState);
            }
        } catch (e) {
            console.warn("Error checking room readiness:", e);
        }
    };

    window.switchTurn = async (rc, currentPlayer) => {
        // Cập nhật UI ngay lập tức (Optimistic Update)
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

        // Gọi API ngầm mà không chặn UI
        apiReq(`switchTurn/${rc}`, { current_player: currentPlayer }).then(serverData => {
            if(serverData) syncTimerState(serverData);
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof window.Echo === 'undefined' && typeof window.Pusher !== 'undefined' && typeof Echo !== 'undefined') {
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: '{{ env("PUSHER_APP_KEY") }}',
                cluster: '{{ env("PUSHER_APP_CLUSTER", "ap1") }}',
                forceTLS: true,
                authEndpoint: '/custom/broadcasting/auth',
                auth: { headers: { 'X-CSRF-Token': csrfToken } }
            });
        }

        if (window.Echo) {
            window.Echo.join(`room.${roomCode}`)
                .here(users => { playersOnline = users.length; handlePresenceChange(); })
                .joining(() => { playersOnline++; handlePresenceChange(); })
                .leaving(() => { playersOnline = Math.max(0, playersOnline - 1); handlePresenceChange(); });

            // Thêm biến để debounce
            let syncTimeout = null;

            window.Echo.channel(`room.${roomCode}`)
                .listen('.room.updated', (e) => {
                    if (e.room) {
                        checkRoomReadiness();
                        // Chờ 200ms để gộp các event (updateFEN + switchTurn) lại thành 1 lần gọi API
                        clearTimeout(syncTimeout);
                        syncTimeout = setTimeout(() => {
                            apiReq(`getTime/${roomCode}`).then(syncTimerState);
                        }, 200);
                    }
                });
        }

        apiReq(`getTime/${roomCode}`).then(syncTimerState);

        // CƠ CHẾ POLLING BẢO HIỂM (Chạy ngầm): Cứ mỗi 3 giây sẽ tự check lại một lần
        // nếu trận đấu chưa được bắt đầu, phòng ngừa trường hợp Websocket bị chặn.
        const startMatchFallbackInterval = setInterval(() => {
            if (window.hasMatchStarted) {
                clearInterval(startMatchFallbackInterval);
            } else {
                checkRoomReadiness();
            }
        }, 3000);

        // Gọi liền ngay khi load xong DOM cho chắc ăn
        checkRoomReadiness();
    });
</script>
