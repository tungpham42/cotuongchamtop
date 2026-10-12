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
    let requestSequence = 0, mutationGeneration = 0;

    // Refactored apiReq to support explicit HTTP methods (e.g., POST with no body)
    const apiReq = async (endpoint, methodOrBody = 'GET', bodyData = null) => {
        const requestId = ++requestSequence;
        const generation = mutationGeneration;
        const sentAt = performance.now();
        const abortController = new AbortController();
        const requestTimeout = setTimeout(() => abortController.abort(), 8000);
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
                signal: abortController.signal,
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
            const data = await res.json();
            return { ...data, _requestId: requestId, _generation: generation,
                _sentAt: sentAt, _receivedAt: performance.now() };
        } catch (err) {
            console.error(err);
        } finally {
            clearTimeout(requestTimeout);
        }
    };

    const ui = {
        red: { box: document.getElementById("red-box"), clock: document.getElementById("red-clock"), moveClock: document.getElementById("red-move-clock") },
        black: { box: document.getElementById("black-box"), clock: document.getElementById("black-clock"), moveClock: document.getElementById("black-move-clock") }
    };

    const MOVE_TIME_LIMIT = 120;
    let time = { red: 600, black: 600 };
    let serverMoveElapsed = 0, activePlayer = null, isGameOver = false;
    let tickInterval = null, localLastUpdate = performance.now();
    let pausedPlayer = null, pendingMove = null;
    let lastSnapshotAt = -Infinity, lastRequestId = 0;
    let lastRevision = -1;
    let serverOffset = null, bestRoundTrip = Infinity;
    let syncInFlight = false, lastTimeoutCheck = -Infinity;
    let timeoutRetryAt = 0;
    let readinessInFlight = false;

    let playersOnline = 0;
    window.hasMatchStarted = false;

    const formatTime = (s) => `${Math.floor(s / 60)}:${(Math.floor(s) % 60).toString().padStart(2, '0')}`;
    const stopAll = () => { clearInterval(tickInterval); tickInterval = null; };
    // Starting a renderer must not change the snapshot's time anchor.
    const startAll = () => { if (tickInterval === null) tickInterval = setInterval(updateUI, 100); };
    const setText = (node, value) => { if (node.textContent !== value) node.textContent = value; };
    const refreshTimer = async () => {
        if (syncInFlight || pendingMove || isGameOver) return;
        syncInFlight = true;
        try {
            const data = await apiReq(`getTime/${roomCode}`);
            syncTimerState(data);
            return data;
        }
        finally { syncInFlight = false; }
    };

    const updateUI = (checkExpiry = true) => {
        if (isGameOver) return;

        const elapsed = activePlayer ? Math.max(0, performance.now() - localLastUpdate) / 1000 : 0;
        const current = { red: time.red, black: time.black };
        const moveTime = { red: MOVE_TIME_LIMIT, black: MOVE_TIME_LIMIT };
        if (pausedPlayer) moveTime[pausedPlayer] = Math.max(0, MOVE_TIME_LIMIT - serverMoveElapsed);

        if (activePlayer) {
            current[activePlayer] = Math.max(0, current[activePlayer] - elapsed);
            moveTime[activePlayer] = Math.max(0, MOVE_TIME_LIMIT - (serverMoveElapsed + elapsed));
            if (moveTime[activePlayer] <= 0) current[activePlayer] = 0;
        }

        ['red', 'black'].forEach(side => {
            setText(ui[side].clock, formatTime(Math.ceil(current[side])));
            setText(ui[side].moveClock, formatTime(Math.ceil(moveTime[side])));
            ui[side].moveClock.classList.toggle('danger', moveTime[side] <= 10 && activePlayer === side);
            ui[side].box.classList.toggle('active', activePlayer === side);
        });

        if (checkExpiry && activePlayer && (current.red <= 0 || current.black <= 0) &&
            performance.now() - lastTimeoutCheck >= 1000) {
            lastTimeoutCheck = performance.now();
            // A local estimate may be wrong; only the server can confirm expiry.
            refreshTimer();
        }
    };

    const syncTimerState = (serverData) => {
        if (isGameOver || !serverData) return false;

        if (serverData._generation !== undefined && serverData._generation !== mutationGeneration) return false;
        if (pendingMove && serverData.fen !== pendingMove.fen) return false;
        const snapshotAt = Number(serverData.snapshot_at);
        const hasTimestamp = Number.isFinite(snapshotAt);
        const revision = Number(serverData.timer_revision ?? lastRevision);
        if (serverData.timer_revision !== undefined && (!Number.isSafeInteger(revision) || revision < 0)) return false;
        if (revision < lastRevision) return false;
        if (revision === lastRevision && hasTimestamp && snapshotAt < lastSnapshotAt) return false;
        if (revision === lastRevision && serverData._requestId && serverData._requestId < lastRequestId) return false;

        const newRed = Number(serverData.red_time), newBlack = Number(serverData.black_time);
        const newMoveElapsed = Number(serverData.move_elapsed || 0);
        const sPlayer = serverData.active_player;
        if (![newRed, newBlack, newMoveElapsed].every(Number.isFinite) ||
            ![null, undefined, 'waiting', 'red', 'black', 'paused:red', 'paused:black'].includes(sPlayer)) return false;

        let newActivePlayer = null;
        pausedPlayer = null;

        if (!sPlayer || sPlayer === 'waiting') {
            window.hasMatchStarted = false;
        } else {
            if (sPlayer.startsWith('paused:')) pausedPlayer = sPlayer.split(':')[1];
            else newActivePlayer = sPlayer;
            window.hasMatchStarted = true;
        }

        // Estimate server clock offset using the fastest HTTP sample. Events use
        // the same offset, so network transport time is included in elapsed.
        if (hasTimestamp && Number.isFinite(serverData._sentAt) && Number.isFinite(serverData._receivedAt)) {
            const roundTrip = serverData._receivedAt - serverData._sentAt;
            if (roundTrip >= 0 && roundTrip < bestRoundTrip) {
                bestRoundTrip = roundTrip;
                serverOffset = snapshotAt - (serverData._sentAt + serverData._receivedAt) / 2;
            }
        }
        const age = hasTimestamp && serverOffset !== null
            ? Math.max(0, performance.now() + serverOffset - snapshotAt) : 0;

        time.red = Math.max(0, newRed);
        time.black = Math.max(0, newBlack);
        serverMoveElapsed = Math.max(0, newMoveElapsed);
        activePlayer = newActivePlayer;
        localLastUpdate = performance.now() - age;
        if (hasTimestamp) lastSnapshotAt = snapshotAt;
        lastRevision = revision;
        if (serverData._requestId) lastRequestId = serverData._requestId;
        if (serverData.fen && !pendingMove) {
            window.dispatchEvent(new CustomEvent('room:timer-snapshot', { detail: serverData }));
        }

        // Include paused move time in the final render before stopping.
        if ((serverData.result !== null && serverData.result !== undefined) ||
            (serverData.timed_out === true && performance.now() >= timeoutRetryAt)) {
            const result = serverData.result ?? (sPlayer === 'red' ? '-1' : '1');
            updateUI(false);
            isGameOver = true;
            stopAll();
            activePlayer = null;
            ui.red.box.classList.remove('active');
            ui.black.box.classList.remove('active');
            if (typeof updateResult === 'function') updateResult(roomCode, String(result), serverData.timed_out === true);
            return true;
        }

        updateUI();

        if (activePlayer) startAll();
        else stopAll();
        return true;
    };

    const handlePresenceChange = () => {
        if (!window.hasMatchStarted && playersOnline >= 2) {
            window.hasMatchStarted = true;
            ui.red.box.classList.remove('paused-offline');
            ui.black.box.classList.remove('paused-offline');

            // Explicitly pass 'POST' method for startMatch
            apiReq(`startMatch/${roomCode}`, 'POST').then(syncTimerState);
        }

        if (!window.hasMatchStarted && playersOnline < 2) {
            ui.red.box.classList.add('paused-offline');
            ui.black.box.classList.add('paused-offline');
        }
    };

    const checkRoomReadiness = async () => {
        if (window.hasMatchStarted || readinessInFlight) return;
        readinessInFlight = true;

        try {
            const res = await apiReq('getRoomIds', { 'ma-phong': roomCode });

            const hasHost = res && (res.host_id || res.has_host);
            const hasGuest = res && (res.guest_id || res.has_guest);

            if (hasHost && hasGuest) {
                window.hasMatchStarted = true;
                ui.red.box.classList.remove('paused-offline');
                ui.black.box.classList.remove('paused-offline');

                // Explicitly pass 'POST' method for startMatch
                syncTimerState(await apiReq(`startMatch/${roomCode}`, 'POST'));
            }
        } catch (e) {
            console.warn("Error checking room readiness:", e);
        } finally {
            readinessInFlight = false;
        }
    };

    window.switchTurn = async (rc, currentPlayer, movePayload = null) => {
        if (isGameOver || pendingMove || activePlayer !== currentPlayer) return null;
        mutationGeneration++;
        const generation = mutationGeneration;
        const elapsed = Math.max(0, performance.now() - localLastUpdate) / 1000;
        pendingMove = { fen: movePayload?.FEN };

        if (time[currentPlayer]) {
            time[currentPlayer] = Math.max(0, time[currentPlayer] - elapsed);
            if (serverMoveElapsed + elapsed >= MOVE_TIME_LIMIT) time[currentPlayer] = 0;
        }

        activePlayer = currentPlayer === 'red' ? 'black' : 'red';
        serverMoveElapsed = 0;
        localLastUpdate = performance.now();

        updateUI();
        startAll();

        const serverData = await apiReq(movePayload ? 'updateFEN' : `switchTurn/${rc}`,
            { ...movePayload, current_player: currentPlayer });
        if (generation !== mutationGeneration) return null;
        pendingMove = null;
        if (serverData) syncTimerState(serverData);
        else {
            const recovered = await refreshTimer();
            return recovered ? { ...recovered, success: recovered.fen === movePayload?.FEN } : null;
        }
        return serverData;
    };
    window.canSubmitRoomMove = side => !isGameOver && !pendingMove && activePlayer === side;
    window.resumeRoomTimer = () => {
        isGameOver = false;
        timeoutRetryAt = performance.now() + 3000;
        lastTimeoutCheck = timeoutRetryAt;
        return refreshTimer();
    };

    document.addEventListener('DOMContentLoaded', () => {
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
            window.Echo.connector?.pusher?.connection?.bind('connected', refreshTimer);
            window.Echo.join(`room.${roomCode}`)
                .here(users => { playersOnline = users.length; handlePresenceChange(); })
                .joining(() => { playersOnline++; handlePresenceChange(); })
                .leaving(() => { playersOnline = Math.max(0, playersOnline - 1); handlePresenceChange(); });

            window.Echo.channel(`room.${roomCode}`)
                .listen('.room.updated', (e) => {
                    if (e.room) {
                        checkRoomReadiness();
                        if (e.timer) syncTimerState(e.timer);
                        else refreshTimer();
                    }
                });
        }

        refreshTimer();
        // Recover from missed events without overlapping HTTP polls.
        setInterval(() => { if (!document.hidden) refreshTimer(); }, 15000);
        document.addEventListener('visibilitychange', () => { if (!document.hidden) refreshTimer(); });
        window.addEventListener('online', refreshTimer);

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
