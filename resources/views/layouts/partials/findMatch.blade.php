<!-- Added the pulse-red class and a subtle inner glow -->
<button id="find-match-btn" class="px-5 py-2 mx-auto mt-3 btn btn-lg btn-danger d-inline-block" style="text-shadow: 0 0 8px rgba(255, 215, 0, 0.5);">
    <i class="fad fa-play mr-2"></i> {{ __("Tìm trận") }}
</button>
<div class="mt-4 w-100 text-center">
    <span id="match-status" class="d-inline-block"></span>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.8.4/axios.min.js" integrity="sha512-2A1+/TAny5loNGk3RBbk11FwoKXYOMfAK6R7r4CpQH7Luz4pezqEGcfphoNzB7SM4dixUoJsKkBsB6kg+dNE2g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
    // --- Configuration & Laravel Blade Injections ---
    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const MatchmakingConfig = {
        currentLocale: '{{ app()->getLocale() }}',
        botRoutes: {
            'vi': '/kien-tuong',
            'en': '/master',
            'ja': '/masuta',
            'ko': '/maseuteo',
            'zh': '/dashi'
        },
        routes: {
            findMatch: '{{ route("match.find") }}',
            checkStatus: '{{ route("match.status") }}',
            roomRed: '{{ localized_path("room.red", ["code" => ":code"]) }}',
            roomBlack: '{{ localized_path("room.black", ["code" => ":code"]) }}'
        },
        baseUrl: '{{ url("") }}',
        maxPollErrors: 5,
        aiTimeoutSeconds: 10,
        pollIntervalMs: 1000
    };

    MatchmakingConfig.aiTargetUrl = MatchmakingConfig.baseUrl + (MatchmakingConfig.botRoutes[MatchmakingConfig.currentLocale] || '/kho-nhat');

    // --- Core Matchmaking Module ---
    const Matchmaker = (() => {
        // State
        let sessionId = sessionStorage.getItem('match_session_id') || 'guest_' + Math.random().toString(36).substr(2, 9);
        let errorCount = 0;
        let waitSeconds = 0;
        let isPolling = false;
        let pollingTimer = null;

        // DOM Elements
        const elements = {
            btn: document.getElementById('find-match-btn'),
            status: document.getElementById('match-status')
        };

        // Helper: Centralized UI updater
        const updateUI = (message, disableBtn) => {
            if (elements.status && message !== null) elements.status.innerText = message;
            if (elements.btn && disableBtn !== null) elements.btn.disabled = disableBtn;
        };

        // Helper: Stop Polling
        const stopPolling = (message) => {
            isPolling = false;
            if (pollingTimer) clearTimeout(pollingTimer);
            updateUI(message, false);
        };

        // Asynchronous Polling Logic (Replaces setInterval to prevent race conditions)
        const pollStatus = async () => {
            if (!isPolling) return;

            waitSeconds++;

            // AI Fallback if timeout reached
            if (waitSeconds >= MatchmakingConfig.aiTimeoutSeconds) {
                isPolling = false;
                showMatchFoundModal(null, true);
                return;
            }

            try {
                const response = await axios.get(MatchmakingConfig.routes.checkStatus, {
                    params: { session_id: sessionStorage.getItem('match_session_id') }
                });

                errorCount = 0; // Reset error count on successful ping

                if (response.data.status === 'matched') {
                    isPolling = false;
                    showMatchFoundModal(response.data, false);
                    return; // Exit poll loop
                } else if (response.data.status === 'error') {
                    stopPolling(response.data.message);
                    return;
                }
            } catch (err) {
                console.error("Polling Error:", err);
                errorCount++;
                if (errorCount > MatchmakingConfig.maxPollErrors) {
                    stopPolling('{{ __("Mất kết nối với máy chủ.") }}');
                    return;
                }
            }

            // Schedule the next poll only after the current one completes
            if (isPolling) {
                pollingTimer = setTimeout(pollStatus, MatchmakingConfig.pollIntervalMs);
            }
        };

        // Start Matchmaking Logic
        const start = async () => {
            updateUI('{{ __("Đang tìm đối thủ...") }}', true);
            errorCount = 0;
            waitSeconds = 0;

            try {
                const response = await axios.post(MatchmakingConfig.routes.findMatch, { session_id: sessionId });

                if (response.data.code === 1) {
                    sessionStorage.setItem('match_session_id', response.data.session_id || sessionId);
                    updateUI(response.data.message, true);

                    // Begin asynchronous polling loop
                    isPolling = true;
                    pollingTimer = setTimeout(pollStatus, MatchmakingConfig.pollIntervalMs);
                } else {
                    updateUI(response.data.message, false);
                }
            } catch (error) {
                console.error("Matchmaking Error:", error);
                updateUI('{{ __("Lỗi kết nối server.") }}', false);
            }
        };

        // UI: Match Found Modal Logic
        const showMatchFoundModal = (data, isBot = false) => {
            let countdown = 5;
            const matchTitle = isBot ? '{{ __("Đã ghép với AI!") }}' : '{{ __("Đã tìm thấy đối thủ!") }}';

            const modalHTML = `
                <div class="modal fade" id="countdownModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content text-center" style="background: var(--glass-bg-dark); backdrop-filter: var(--glass-blur); -webkit-backdrop-filter: var(--glass-blur); border: 1px solid var(--glass-border); border-top: 2px solid rgba(255, 215, 0, 0.5); border-radius: 12px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.9), inset 0 3px 15px var(--liquid-highlight); overflow: hidden;">
                            <div style="background: linear-gradient(90deg, rgba(138, 21, 21, 0.5), rgba(92, 10, 10, 0.3)); border-bottom: 1px solid var(--glass-border); padding: 16px 0;">
                                <h4 class="mb-0" style="font-family: 'Texturina', serif; color: var(--royal-gold); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; text-shadow: 0 0 10px rgba(255, 215, 0, 0.5);">
                                    <img width="38" height="38" src="/img/xiangqipieces/wiki/rK.svg" alt="{{ __("Cờ tướng") }}" class="mr-2" style="filter: drop-shadow(0 0 5px rgba(255, 215, 0, 0.8));">
                                    ${matchTitle}
                                </h4>
                            </div>
                            <div class="p-4" style="background: transparent;">
                                <p class="h5 mb-3" style="color: var(--royal-gold-light); font-weight: 600; text-shadow: 1px 1px 3px rgba(0,0,0,0.8);">
                                    {{ __("Ván cờ sẽ bắt đầu sau:") }}
                                </p>
                                <div class="display-1 font-weight-bold mb-3" id="countdownNumber" style="font-family: 'Texturina', serif; background: linear-gradient(to bottom, #fff, var(--royal-gold)); -webkit-background-clip: text; background-clip: text; color: transparent; text-shadow: 0 0 30px var(--royal-red-light), 0 0 60px var(--royal-red-dark); line-height: 1.2;">
                                    ${countdown}
                                </div>
                                <hr style="border-top: 1px solid rgba(255, 215, 0, 0.2); box-shadow: 0 1px 2px rgba(0,0,0,0.5); margin: 20px 0;">
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

            const countdownEl = document.getElementById("countdownNumber");
            const countdownInterval = setInterval(() => {
                countdown--;
                countdownEl.textContent = countdown;
                tickSound.currentTime = 0;
                tickSound.play().catch(() => {}); // Catch prevents console spam if user hasn't interacted with DOM

                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    $modal.modal('hide');
                    $modal.remove();
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');

                    let targetUrl = '';
                    if (isBot) {
                        targetUrl = MatchmakingConfig.aiTargetUrl;
                        updateUI('{{ __("Đang vào trận với AI...") }}', null);
                    } else {
                        targetUrl = (data.side === 'red')
                            ? MatchmakingConfig.routes.roomRed.replace(':code', data.room_code)
                            : MatchmakingConfig.routes.roomBlack.replace(':code', data.room_code);

                        updateUI(`{{ __("Đã tìm thấy!") }} {{ __("Vào phòng") }} "${data.room_name}".`, null);
                    }
                    window.location.href = targetUrl;
                }
            }, 1000);
        };

        // Expose Public API
        return {
            init: () => {
                if (elements.btn) {
                    elements.btn.addEventListener('click', start);
                }
            }
        };
    })();

    // Initialize Matchmaker
    document.addEventListener("DOMContentLoaded", () => {
        Matchmaker.init();
    });
</script>
