<!-- Added the pulse-red class and a subtle inner glow -->
<button id="find-match-btn" class="px-5 py-2 mx-auto mt-3 btn btn-lg btn-danger d-inline-block" style="text-shadow: 0 0 8px rgba(255, 215, 0, 0.5);">
    <i class="fad fa-play mr-2"></i> {{ __("Tìm trận") }}
</button>
<div class="mt-4 w-100 text-center">
    <span id="match-status" class="d-inline-block"></span>
</div>

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

    // Replace your existing showMatchFoundModal function
    function showMatchFoundModal(data, isBot = false) {
        let countdown = 5;

        const matchTitle = isBot ? '{{ __("Đã ghép với Phạm Tùng!") }}' : '{{ __("Đã tìm thấy đối thủ!") }}';

        // Upgraded to Liquid Glass UI
        const modalHTML = `
            <div class="modal fade" id="countdownModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content text-center" style="background: var(--glass-bg-dark); backdrop-filter: var(--glass-blur); -webkit-backdrop-filter: var(--glass-blur); border: 1px solid var(--glass-border); border-top: 2px solid rgba(255, 215, 0, 0.5); border-radius: 12px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.9), inset 0 3px 15px var(--liquid-highlight); overflow: hidden;">

                        <!-- Thematic Glass Header -->
                        <div style="background: linear-gradient(90deg, rgba(138, 21, 21, 0.5), rgba(92, 10, 10, 0.3)); border-bottom: 1px solid var(--glass-border); padding: 16px 0;">
                            <h4 class="mb-0" style="font-family: 'Texturina', serif; color: var(--royal-gold); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; text-shadow: 0 0 10px rgba(255, 215, 0, 0.5);">
                                <img width="38" height="38" src="/img/xiangqipieces/wiki/rK.svg" alt="{{ __("Cờ tướng") }}" class="mr-2" style="filter: drop-shadow(0 0 5px rgba(255, 215, 0, 0.8));">
                                ${matchTitle}
                            </h4>
                        </div>

                        <!-- Modal Body -->
                        <div class="p-4" style="background: transparent;">
                            <p class="h5 mb-3" style="color: var(--royal-gold-light); font-weight: 600; text-shadow: 1px 1px 3px rgba(0,0,0,0.8);">
                                {{ __("Ván cờ sẽ bắt đầu sau:") }}
                            </p>

                            <!-- Glossy Countdown Number (Epic Glow) -->
                            <div class="display-1 font-weight-bold mb-3" id="countdownNumber" style="font-family: 'Texturina', serif; background: linear-gradient(to bottom, #fff, var(--royal-gold)); -webkit-background-clip: text; background-clip: text; color: transparent; text-shadow: 0 0 30px var(--royal-red-light), 0 0 60px var(--royal-red-dark); line-height: 1.2;">
                                ${countdown}
                            </div>

                            <hr style="border-top: 1px solid rgba(255, 215, 0, 0.2); box-shadow: 0 1px 2px rgba(0,0,0,0.5); margin: 20px 0;">

                            <!-- Footer Text -->
                            <p class="mb-0" style="color: var(--royal-gold); font-size: 1.1rem; font-weight: 500; text-shadow: 0 0 5px rgba(255, 215, 0, 0.4);">
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
                <img width="42" height="42" src="/img/xiangqipieces/wiki/rK.svg" alt="{{ __("Cờ tướng") }}" class="mr-2" style="filter: drop-shadow(0 0 5px rgba(255, 215, 0, 0.8));">
                ${matchTitle}
            `;
        }

        const tickSound = new Audio("/sound/tick.mp3");
        const $modal = $('#countdownModal');
        $modal.modal('show');

        // ... (Keep the rest of your countdown Interval logic exactly the same)
        const countdownEl = document.getElementById("countdownNumber");
        const countdownInterval = setInterval(() => {
            countdown--;
            countdownEl.textContent = countdown;
            tickSound.currentTime = 0;
            tickSound.play().catch(() => {});

            if (countdown <= 0) {
                clearInterval(countdownInterval);
                $modal.modal('hide');
                $modal.remove();
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');

                let targetUrl = '';
                if (isBot) {
                    targetUrl = aiTargetUrl;
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
