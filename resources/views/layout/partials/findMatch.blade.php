<button id="find-match-btn" class="px-5 py-2 mx-auto mt-3 btn btn-lg btn-danger d-inline-block">
    <i class="fad fa-play mr-2"></i>
    @if ( $roomCode == '' )
        {{ __("Tìm trận") }}
    @else
        {{ __("Tìm trận") }} mới
    @endif
</button>
<span id="match-status" class="mt-3 d-inline w-100 text-center"></span>

<script>
    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let sessionId = localStorage.getItem('anonymous_match_id');
    let pollInterval;
    let errorCount = 0;

    @php
        $localeSuffix = app()->getLocale() == 'vi' ? '' : '-' . app()->getLocale();
        $quickMatchRoute = 'anonymous-quick-match' . $localeSuffix;
        $checkStatusRoute = 'check-anonymous-match-status' . $localeSuffix;
    @endphp

    const routes = {
        quickMatch: '{{ route($quickMatchRoute, [], false) }}',
        checkStatus: '{{ route($checkStatusRoute, [], false) }}',
        roomRed: '{{ localized_path("room.red", ["code" => ":code"]) }}',
        roomBlack: '{{ localized_path("room.black", ["code" => ":code"]) }}'
    };

    document.getElementById('find-match-btn').addEventListener('click', function () {
        this.disabled = true;
        document.getElementById('match-status').innerText = '{{ __("Đang tìm đối thủ...") }}';

        axios.post(routes.quickMatch)
            .then(response => {
                if (response.data.code === 1) {
                    sessionId = response.data.session_id;
                    localStorage.setItem('anonymous_match_id', sessionId);
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
                params: { session_id: sessionId }
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
                
                let targetUrl = '';
                if (data.side === 'do' || data.side === 'red' || data.side === 'aka' || data.side === 'ppalgan' || data.side === 'hongse') {
                    targetUrl = routes.roomRed.replace(':code', data.room_code);
                } else {
                    targetUrl = routes.roomBlack.replace(':code', data.room_code);
                }
                
                document.getElementById('match-status').innerText =
                    `{{ __("Đã tìm thấy!") }} {{ __("Vào phòng") }} "${data.room_name}" {{ __("với quân") }} ${data.color}.`;
                window.location.href = targetUrl;
            }
        }, 1000);
    }
</script>
