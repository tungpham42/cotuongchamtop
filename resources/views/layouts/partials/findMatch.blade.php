<button id="find-match-btn" class="px-5 py-2 mx-auto mt-3 btn btn-lg btn-danger d-inline-block" type="button">
    <i class="fad fa-play mr-2"></i> {{ __('Tìm trận') }}
</button>

<div class="mt-4 w-100 text-center">
    <span id="match-status" class="d-inline-block"></span>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.8.4/axios.min.js"
        integrity="sha512-2A1+/TAny5loNGk3RBbk11FwoKXYOMfAK6R7r4CpQH7Luz4pezqEGcfphoNzB7SM4dixUoJsKkBsB6kg+dNE2g=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"></script>

<script>
(function () {
    'use strict';

    const button = document.getElementById('find-match-btn');
    const status = document.getElementById('match-status');

    if (!button || !status) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    }

    const sessionStorageKey = 'match_session_id';
    let sessionId = sessionStorage.getItem(sessionStorageKey);

    if (!sessionId) {
        sessionId = 'guest_' + Math.random().toString(36).slice(2) + Date.now().toString(36);
        sessionStorage.setItem(sessionStorageKey, sessionId);
    }

    const routes = {
        findMatch: @json(route('match.find')),
        roomRed: @json(localized_path('room.red', ['code' => '__ROOM_CODE__'])),
        roomBlack: @json(localized_path('room.black', ['code' => '__ROOM_CODE__']))
    };

    function roomUrl(side, roomCode) {
        const template = side === 'black' ? routes.roomBlack : routes.roomRed;
        return template.replace('__ROOM_CODE__', encodeURIComponent(roomCode));
    }

    function setBusy(isBusy) {
        button.disabled = isBusy;
        button.classList.toggle('disabled', isBusy);
    }

    function setStatus(message) {
        status.textContent = message || '';
    }

    async function findMatch() {
        setBusy(true);
        setStatus(@json(__('Đang tìm phòng gần nhất...')));

        try {
            /*
             * The server is responsible for room assignment.
             * It selects the newest available initial-FEN room, locks it,
             * and assigns this browser session to the remaining seat.
             */
            const response = await axios.post(routes.findMatch, {
                session_id: sessionId
            });

            const data = response.data || {};

            if (data.session_id) {
                sessionId = data.session_id;
                sessionStorage.setItem(sessionStorageKey, sessionId);
            }

            if (data.code !== 1 || !data.room_code) {
                throw new Error(data.message || @json(__('Không tìm thấy phòng phù hợp.')));
            }

            const side = data.side === 'black' ? 'black' : 'red';
            const targetUrl = roomUrl(side, data.room_code);

            setStatus(@json(__('Đã tìm thấy phòng. Đang vào trận...')));
            window.location.replace(targetUrl);
        } catch (error) {
            console.error('Matchmaking error:', error);

            const message = error?.response?.data?.message
                || error?.message
                || @json(__('Không thể tìm trận lúc này. Vui lòng thử lại.'));

            setStatus(message);
            setBusy(false);
        }
    }

    button.addEventListener('click', findMatch);
})();
</script>
