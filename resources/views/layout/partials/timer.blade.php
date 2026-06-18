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
        align-items: flex-start;
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

    .red-glass .clock { color: #ff5252; }
    .red-glass .label { color: #ff5252; }
    .red-glass .move-clock { color: #ff8a80; }

    .red-glass.active {
        background: linear-gradient(145deg, #5c1616, #300808);
        border-color: rgba(255, 82, 82, 0.4);
        box-shadow:
            0 0 20px rgba(255, 82, 82, 0.3),
            inset 0 2px 4px rgba(255, 138, 128, 0.2);
        transform: translateY(-3px) scale(1.03);
    }

    .black-glass .clock { color: #ffd54f; }
    .black-glass .label { color: #ffd54f; }
    .black-glass .move-clock { color: #ffe082; }

    .black-glass.active {
        background: linear-gradient(145deg, #2b2b2b, #111111);
        border-color: rgba(255, 213, 79, 0.4);
        box-shadow:
            0 0 20px rgba(255, 213, 79, 0.2),
            inset 0 2px 4px rgba(255, 224, 130, 0.2);
        transform: translateY(-3px) scale(1.03);
    }

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
    const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' };

    const ui = {
        red: { clock: document.getElementById("red-clock"), box: document.getElementById("red-box"), moveClock: document.getElementById("red-move-clock") },
        black: { clock: document.getElementById("black-clock"), box: document.getElementById("black-box"), moveClock: document.getElementById("black-move-clock") }
    };

    let time = { red: 600, black: 600 };
    let serverMoveElapsed = 0;
    const MOVE_TIME_LIMIT = 120;

    let activePlayer = null, isGameOver = false;
    let intervals = { tick: null };
    let localLastUpdate = Date.now();

    // Presence Variables
    let playersOnline = 0;
    let systemPaused = false;
    let previousPresenceState = null;

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

    const updateUI = () => {
        if (isGameOver) return;

        let elapsedSinceSync = (activePlayer && !systemPaused) ? (Date.now() - localLastUpdate) / 1000 : 0;

        let currentRed = time.red;
        let currentBlack = time.black;
        let redMove = MOVE_TIME_LIMIT;
        let blackMove = MOVE_TIME_LIMIT;

        if (activePlayer === 'red') {
            currentRed = Math.max(0, currentRed - elapsedSinceSync);
            redMove = Math.max(0, MOVE_TIME_LIMIT - (serverMoveElapsed + elapsedSinceSync));
            if (redMove <= 0) currentRed = 0;
        }

        if (activePlayer === 'black') {
            currentBlack = Math.max(0, currentBlack - elapsedSinceSync);
            blackMove = Math.max(0, MOVE_TIME_LIMIT - (serverMoveElapsed + elapsedSinceSync));
            if (blackMove <= 0) currentBlack = 0;
        }

        ui.red.clock.innerText = formatTime(Math.ceil(currentRed));
        ui.black.clock.innerText = formatTime(Math.ceil(currentBlack));
        ui.red.moveClock.innerText = formatTime(Math.ceil(redMove));
        ui.black.moveClock.innerText = formatTime(Math.ceil(blackMove));

        ui.red.moveClock.classList.toggle('danger', redMove <= 10 && activePlayer === 'red' && !systemPaused);
        ui.black.moveClock.classList.toggle('danger', blackMove <= 10 && activePlayer === 'black' && !systemPaused);

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
        } else if (!systemPaused) {
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

    const syncTimerState = (serverData) => {
        if (isGameOver) return;
        time.red = parseFloat(serverData.red_time);
        time.black = parseFloat(serverData.black_time);

        let sPlayer = serverData.active_player;

        if (sPlayer && sPlayer.startsWith('paused:')) {
            activePlayer = sPlayer.split(':')[1];
            systemPaused = true;

            // Enforce visual paused state
            ui.red.box.classList.add('paused-offline');
            ui.black.box.classList.add('paused-offline');
            ui.red.box.classList.remove('active');
            ui.black.box.classList.remove('active');
        } else {
            activePlayer = sPlayer;
            systemPaused = false; // CRITICAL: Reset the pause flag when active

            // Remove visual paused state
            ui.red.box.classList.remove('paused-offline');
            ui.black.box.classList.remove('paused-offline');
        }

        serverMoveElapsed = serverData.move_elapsed !== undefined ? parseFloat(serverData.move_elapsed) : 0;
        localLastUpdate = Date.now();

        updateUI();

        if (activePlayer && !systemPaused) startAll();
        else stopAll();
    };

    const handlePresenceChange = () => {
        const isCurrentlyPaused = playersOnline < 2;

        if (previousPresenceState === isCurrentlyPaused) return;
        previousPresenceState = isCurrentlyPaused;

        if (isCurrentlyPaused) {
            // Optimistically pause locally
            systemPaused = true;
            stopAll();

            ui.red.box.classList.add('paused-offline');
            ui.black.box.classList.add('paused-offline');
            ui.red.box.classList.remove('active');
            ui.black.box.classList.remove('active');

            if (activePlayer) {
                // FIX: Added `{}` to force a POST request so the server actually pauses the DB timer
                apiReq(`/pauseTimer/${roomCode}/${activePlayer}`, {});
            }
        } else {
            apiReq(`/getTime/${roomCode}`).then(data => {
                if (data && !data.error) {
                    let wasPaused = data.active_player && data.active_player.startsWith('paused:');
                    syncTimerState(data);

                    // Only request the server to unpause if it was officially paused
                    if (wasPaused && activePlayer) {
                        // FIX: Added `{}` to force a POST request
                        apiReq(`/startTimer/${roomCode}/${activePlayer}`, {}).then(() => {
                            // Ensure strict synchronization after unpausing
                            apiReq(`/getTime/${roomCode}`).then(newData => {
                                if (newData && !newData.error) syncTimerState(newData);
                            });
                        });
                    }
                }
            });
        }
    };

    window.pauseTimer = (rc, p) => apiReq(`/pauseTimer/${rc}/${p}`, {});
    window.startTimer = (rc, p) => apiReq(`/startTimer/${rc}/${p}`, {});

    window.switchTurn = async (rc, currentPlayer) => {
        if (systemPaused) return;

        const elapsed = (Date.now() - localLastUpdate) / 1000;

        if (currentPlayer === 'red') {
            time.red = Math.max(0, time.red - elapsed);
            if (serverMoveElapsed + elapsed >= MOVE_TIME_LIMIT) time.red = 0;
        }
        if (currentPlayer === 'black') {
            time.black = Math.max(0, time.black - elapsed);
            if (serverMoveElapsed + elapsed >= MOVE_TIME_LIMIT) time.black = 0;
        }

        activePlayer = currentPlayer === 'red' ? 'black' : 'red';
        serverMoveElapsed = 0;
        localLastUpdate = Date.now();

        updateUI();
        startAll();

        await apiReq(`/switchTurn/${rc}`, { current_player: currentPlayer });
    };

    // Pusher Initialization Fallback & Channels Logic
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.Echo === 'undefined') {
            if (typeof Pusher !== 'undefined' && typeof Echo !== 'undefined') {
                window.Pusher = Pusher;
                window.Echo = new Echo({
                    broadcaster: 'pusher',
                    key: '{{ env("PUSHER_APP_KEY") }}',
                    cluster: '{{ env("PUSHER_APP_CLUSTER", "ap1") }}',
                    forceTLS: true,
                    authEndpoint: '/custom/broadcasting/auth',
                    auth: {
                        headers: {
                            'X-CSRF-Token': '{{ csrf_token() }}'
                        }
                    }
                });
            } else {
                console.warn("Pusher or Echo library is missing. Counter cannot connect.");
                return;
            }
        }

        // 1. PRESENCE TRACKER (Monitors Disconnects)
        window.Echo.join(`room.${roomCode}`)
            .here((users) => {
                playersOnline = users.length;
                handlePresenceChange();
            })
            .joining((user) => {
                playersOnline++;
                handlePresenceChange();
            })
            .leaving((user) => {
                playersOnline = playersOnline > 0 ? playersOnline - 1 : 0;
                handlePresenceChange();
            })
            .error((error) => {
                console.error('Pusher auth error:', error);
            });

        // 2. PUBLIC LISTENER (Monitors Game Events)
        window.Echo.channel(`room.${roomCode}`)
            .listen('.room.updated', (e) => {
                if (e.room) {
                    apiReq(`/getTime/${roomCode}`).then(data => {
                        if (data && !data.error) syncTimerState(data);
                    });
                }
            });

        // Initial sync fetch on page load
        (async () => {
            const data = await apiReq(`/getTime/${roomCode}`);
            if (data && !data.error) syncTimerState(data);
        })();
    });
</script>
