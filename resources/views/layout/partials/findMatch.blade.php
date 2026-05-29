<button id="find-match-btn" class="px-5 py-2 mx-auto mt-3 btn btn-lg btn-danger d-inline-block">
    <i class="fad fa-play mr-2"></i> {{ __("Tìm trận") }}
</button>
<span id="match-status" class="mt-3 d-inline w-100 text-center"></span>

<script>
    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let sessionId = sessionStorage.getItem('match_session_id') || 'guest_' + Math.random().toString(36).substr(2, 9);
    let pollInterval;
    let errorCount = 0;

    const routes = {
        findMatch: '{{ route("match.find") }}',
        checkStatus: '{{ route("match.status") }}',
        roomRed: '{{ localized_path("room.red", ["code" => ":code"]) }}',
        roomBlack: '{{ localized_path("room.black", ["code" => ":code"]) }}'
    };

    document.getElementById('find-match-btn').addEventListener('click', function () {
        this.disabled = true;
        document.getElementById('match-status').innerText = '{{ __("Đang tìm đối thủ...") }}';

        // Send session ID so the server can track this specific client queue
        axios.post(routes.findMatch, { session_id: sessionId })
            .then(response => {
                if (response.data.code === 1) {
                    // Changed from localStorage to sessionStorage
                    sessionStorage.setItem('match_session_id', response.data.session_id || sessionId);
                    document.getElementById('match-status').innerText = response.data.message;
                    startPolling();
                } else {
                    document.getElementById('match-status').innerText = response.data.message;
                    this.disabled = false;
                }
            })
            .catch(error => {
                console.error(error);
                document.getElementById('match-status').innerText = '{{ __("Lỗi kết nối server.") }}';
                this.disabled = false;
            });
    });

    function startPolling() {
        let hasMatched = false;

        pollInterval = setInterval(() => {
            axios.get(routes.checkStatus, {
                // Changed from localStorage to sessionStorage
                params: { session_id: sessionStorage.getItem('match_session_id') }
            })
            .then(response => {
                errorCount = 0;

                if (response.data.status === 'matched' && !hasMatched) {
                    hasMatched = true;
                    clearInterval(pollInterval);
                    showMatchFoundModal(response.data);
                } else if (response.data.status === 'error') {
                    stopPolling(response.data.message);
                }
            })
            .catch((err) => {
                console.error(err);
                errorCount++;
                if(errorCount > 5) {
                    stopPolling('{{ __("Mất kết nối với máy chủ.") }}');
                }
            });
        }, 1000);
    }

    function stopPolling(message) {
        clearInterval(pollInterval);
        document.getElementById('match-status').innerText = message;
        document.getElementById('find-match-btn').disabled = false;
    }

    function showMatchFoundModal(data) {
        let countdown = 5;

        const modalHTML = `
            <div class="modal fade" id="countdownModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content text-center p-4" style="background-color: #E1BF85; border-radius: 15px;">
                        <h4 class="mb-3 text-danger">
                            <img width="42" height="42" src="/img/xiangqipieces/wiki/rK.svg" alt="{{ __("Cờ tướng") }}" class="mr-2">
                            {{ __("Đã tìm thấy đối thủ!") }}
                        </h4>
                        <p class="fs-5 mb-3">{{ __("Ván cờ sẽ bắt đầu sau:") }}</p>
                        <div class="display-4 fw-bold text-danger" id="countdownNumber">${countdown}</div>
                        <p class="mt-3" style="color: #413E3C;">
                            <i class="fas fa-clock"></i> {{ __("Chuẩn bị sẵn sàng...") }}
                        </p>
                    </div>
                </div>
            </div>
        `;

        if (!document.getElementById("countdownModal")) {
            document.body.insertAdjacentHTML("beforeend", modalHTML);
        }

        const tickSound = new Audio("/sound/tick.mp3");
        const $modal = $('#countdownModal');
        $modal.modal('show');

        const countdownEl = document.getElementById("countdownNumber");
        const countdownInterval = setInterval(() => {
            countdown--;
            countdownEl.textContent = countdown;
            tickSound.currentTime = 0;
            tickSound.play().catch(() => {});

            if (countdown <= 0) {
                clearInterval(countdownInterval);
                $modal.modal('hide');

                // Cleanly check standardized side strings from backend
                let targetUrl = (data.side === 'red')
                    ? routes.roomRed.replace(':code', data.room_code)
                    : routes.roomBlack.replace(':code', data.room_code);

                document.getElementById('match-status').innerText =
                    `{{ __("Đã tìm thấy!") }} {{ __("Vào phòng") }} "${data.room_name}".`;
                window.location.href = targetUrl;
            }
        }, 1000);
    }
</script>
