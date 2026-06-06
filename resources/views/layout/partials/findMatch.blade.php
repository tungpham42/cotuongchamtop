<button id="find-match-btn" class="px-5 py-2 mx-auto mt-3 btn btn-lg btn-danger d-inline-block">
    <i class="fad fa-play mr-2"></i> {{ __("Tìm trận") }}
</button>
<span id="match-status" class="mt-3 d-inline w-100 text-center"></span>

<script>
    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let sessionId = sessionStorage.getItem('match_session_id') || 'guest_' + Math.random().toString(36).substr(2, 9);
    let pollInterval;
    let errorCount = 0;
    let waitSeconds = 0; // Track waiting time

    // Map locales to their corresponding "Hardest" AI routes based on web.php
    const botRoutes = {
        'vi': '/kien-tuong',
        'en': '/master',
        'ja': '/masuta',
        'ko': '/maseuteo',
        'zh': '/dashi'
    };
    const currentLocale = '{{ app()->getLocale() }}';
    const aiTargetUrl = '{{ url("") }}' + (botRoutes[currentLocale] || '/kho-nhat');

    const routes = {
        findMatch: '{{ route("match.find") }}',
        checkStatus: '{{ route("match.status") }}',
        roomRed: '{{ localized_path("room.red", ["code" => ":code"]) }}',
        roomBlack: '{{ localized_path("room.black", ["code" => ":code"]) }}'
    };

    document.getElementById('find-match-btn').addEventListener('click', function () {
        this.disabled = true;
        document.getElementById('match-status').innerText = '{{ __("Đang tìm đối thủ...") }}';

        axios.post(routes.findMatch, { session_id: sessionId })
            .then(response => {
                if (response.data.code === 1) {
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
        waitSeconds = 0; // Reset counter every time queue starts

        pollInterval = setInterval(() => {
            waitSeconds++;

            // If 10 seconds have passed and no human is found, match with Phạm Tùng (Bot)
            if (waitSeconds >= 10 && !hasMatched) {
                hasMatched = true;
                clearInterval(pollInterval);
                showMatchFoundModal(null, true);
                return;
            }

            axios.get(routes.checkStatus, {
                params: { session_id: sessionStorage.getItem('match_session_id') }
            })
            .then(response => {
                errorCount = 0;

                if (response.data.status === 'matched' && !hasMatched) {
                    hasMatched = true;
                    clearInterval(pollInterval);
                    showMatchFoundModal(response.data, false);
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

    function showMatchFoundModal(data, isBot = false) {
        let countdown = 5;

        // Dynamically set modal text to show "Phạm Tùng" instead of "Máy"
        const matchTitle = isBot ? '{{ __("Đã ghép với Phạm Tùng!") }}' : '{{ __("Đã tìm thấy đối thủ!") }}';

        const modalHTML = `
            <div class="modal fade" id="countdownModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content text-center" style="background-color: #f4dca6; border: 4px solid #8b4513; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.6); overflow: hidden;">

                        <!-- Thematic Header -->
                        <div style="background-color: #8b4513; color: #f4dca6; padding: 15px 0; border-bottom: 2px solid #5c2c16;">
                            <h4 class="mb-0" style="font-weight: bold; letter-spacing: 0.5px;">
                                <img width="38" height="38" src="/img/xiangqipieces/wiki/rK.svg" alt="{{ __("Cờ tướng") }}" class="mr-2" style="filter: drop-shadow(1px 1px 2px rgba(0,0,0,0.5));">
                                ${matchTitle}
                            </h4>
                        </div>

                        <!-- Modal Body -->
                        <div class="p-4" style="background-image: radial-gradient(circle, #fcf0d8 0%, #E1BF85 100%);">
                            <p class="h5 mb-3" style="color: #5c2c16; font-weight: 600;">
                                {{ __("Ván cờ sẽ bắt đầu sau:") }}
                            </p>

                            <!-- Countdown Number -->
                            <div class="display-2 font-weight-bold mb-3" id="countdownNumber" style="color: #b30000; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">
                                ${countdown}
                            </div>

                            <hr style="border-top: 1px dashed #c09060; margin: 20px 0;">

                            <!-- Footer Text -->
                            <p class="mb-0" style="color: #704214; font-size: 1.1rem; font-weight: 500;">
                                <i class="fas fa-hourglass-half fa-spin mr-2" style="animation-duration: 2s;"></i> {{ __("Chuẩn bị sẵn sàng...") }}
                            </p>
                        </div>

                    </div>
                </div>
            </div>
        `;

        if (!document.getElementById("countdownModal")) {
            document.body.insertAdjacentHTML("beforeend", modalHTML);
        } else {
            document.querySelector('#countdownModal h4').innerHTML = `
                <img width="42" height="42" src="/img/xiangqipieces/wiki/rK.svg" alt="{{ __("Cờ tướng") }}" class="mr-2">
                ${matchTitle}
            `;
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
                $modal.remove(); // Prevent DOM clutter on repeated matches
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');

                let targetUrl = '';

                // Route user appropriately based on match type
                if (isBot) {
                    targetUrl = aiTargetUrl;
                    // Update the loading text to mention Phạm Tùng
                    document.getElementById('match-status').innerText = '{{ __("Đang vào trận với Phạm Tùng...") }}';
                } else {
                    targetUrl = (data.side === 'red')
                        ? routes.roomRed.replace(':code', data.room_code)
                        : routes.roomBlack.replace(':code', data.room_code);

                    document.getElementById('match-status').innerText =
                        `{{ __("Đã tìm thấy!") }} {{ __("Vào phòng") }} "${data.room_name}".`;
                }

                window.location.href = targetUrl;
            }
        }, 1000);
    }
</script>
