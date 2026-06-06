<!-- Styles for the Find Match Section & Modal -->
<style>
    /* ==========================================================================
       THE FIND MATCH BUTTON & TEXT
       ========================================================================== */
    .btn-find-match {
        background: linear-gradient(135deg, #e63946, #8a1515);
        color: #fff;
        border: 1px solid rgba(255, 215, 0, 0.4);
        border-radius: 30px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 2px;
        box-shadow:
            0 0 20px rgba(230, 57, 70, 0.4),
            inset 0 0 10px rgba(255, 255, 255, 0.1);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }

    .btn-find-match:hover:not(:disabled) {
        transform: translateY(-3px) scale(1.05);
        box-shadow:
            0 10px 30px rgba(230, 57, 70, 0.6),
            0 0 25px rgba(255, 215, 0, 0.3);
        color: #ffd700;
        border-color: #ffd700;
    }

    .btn-find-match:disabled {
        background: linear-gradient(135deg, #3a3f4c, #1a1c23);
        border-color: #505769;
        color: #888;
        box-shadow: none;
        cursor: not-allowed;
    }

    #match-status {
        color: var(--royal-gold-light, #fff2cc);
        font-family: "Texturina", serif;
        font-size: 1.1rem;
        letter-spacing: 1px;
        text-shadow: 0 0 10px rgba(255, 215, 0, 0.2);
    }

    /* ==========================================================================
       THE OBSIDIAN GLASS MODAL
       ========================================================================== */
    .royal-glass-modal {
        background: rgba(18, 20, 24, 0.85) !important;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(212, 175, 55, 0.3) !important;
        border-radius: 20px !important;
        box-shadow:
            0 25px 50px rgba(0, 0, 0, 0.9),
            inset 0 0 30px rgba(212, 175, 55, 0.05) !important;
        position: relative;
        overflow: hidden;
    }

    /* Glowing decorative top border */
    .modal-glow-top {
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, transparent, #ffd700, #e63946, transparent);
        box-shadow: 0 0 15px rgba(255, 215, 0, 0.8);
    }

    .royal-title {
        color: #ffd700;
        font-family: "Texturina", serif;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-shadow: 0 0 15px rgba(212, 175, 55, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Pulsing Chess Piece */
    .piece-pulse {
        filter: drop-shadow(0 0 10px rgba(230, 57, 70, 0.8));
        animation: pulseRed 2s infinite;
    }

    /* Epic Heartbeat Countdown */
    .countdown-epic {
        font-family: "Texturina", serif;
        color: transparent;
        background: linear-gradient(to bottom, #ffffff, #e63946);
        -webkit-background-clip: text;
        background-clip: text;
        text-shadow: 0 0 30px rgba(230, 57, 70, 0.6);
        font-size: 6rem;
        line-height: 1;
        font-weight: 900;
        margin: 15px 0;
        animation: heartbeat 1s infinite cubic-bezier(0.215, 0.61, 0.355, 1);
    }

    .divider-gold {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.5), transparent);
        width: 80%;
        margin: 0 auto;
    }

    @keyframes pulseRed {
        0% { filter: drop-shadow(0 0 5px rgba(230, 57, 70, 0.5)); transform: scale(1); }
        50% { filter: drop-shadow(0 0 15px rgba(230, 57, 70, 1)); transform: scale(1.1); }
        100% { filter: drop-shadow(0 0 5px rgba(230, 57, 70, 0.5)); transform: scale(1); }
    }

    @keyframes heartbeat {
        0% { transform: scale(1); text-shadow: 0 0 30px rgba(230, 57, 70, 0.6); }
        15% { transform: scale(1.15); text-shadow: 0 0 50px rgba(230, 57, 70, 0.9); }
        30% { transform: scale(1); text-shadow: 0 0 30px rgba(230, 57, 70, 0.6); }
        100% { transform: scale(1); }
    }
</style>

<!-- Find Match UI -->
<button id="find-match-btn" class="px-5 py-3 mx-auto mt-3 btn btn-lg btn-find-match d-inline-block">
    <i class="fad fa-play mr-2"></i> {{ __("Tìm trận") }}
</button>
<span id="match-status" class="mt-3 d-inline w-100 text-center d-block"></span>

<!-- Matching Logic -->
<script>
    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let sessionId = sessionStorage.getItem('match_session_id') || 'guest_' + Math.random().toString(36).substr(2, 9);
    let pollInterval;
    let errorCount = 0;
    let waitSeconds = 0;

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
        document.getElementById('match-status').innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2 text-warning"></i> {{ __("Đang tìm đối thủ...") }}';

        axios.post(routes.findMatch, { session_id: sessionId })
            .then(response => {
                if (response.data.code === 1) {
                    sessionStorage.setItem('match_session_id', response.data.session_id || sessionId);
                    document.getElementById('match-status').innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2 text-warning"></i> ' + response.data.message;
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
        waitSeconds = 0;

        pollInterval = setInterval(() => {
            waitSeconds++;

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

        const matchTitle = isBot ? '{{ __("Đã ghép với Phạm Tùng!") }}' : '{{ __("Đã tìm thấy đối thủ!") }}';

        const modalHTML = `
            <div class="modal fade" id="countdownModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content text-center p-5 royal-glass-modal">
                        <div class="modal-glow-top"></div>

                        <h4 class="mb-4 royal-title">
                            <img width="48" height="48" src="/img/xiangqipieces/wiki/rK.svg" alt="{{ __("Cờ tướng") }}" class="mr-3 piece-pulse">
                            ${matchTitle}
                        </h4>

                        <p class="fs-5 mb-0" style="color: #fff2cc;">{{ __("Ván cờ sẽ bắt đầu sau:") }}</p>

                        <div class="countdown-epic" id="countdownNumber">${countdown}</div>

                        <div class="divider-gold my-4"></div>

                        <p class="mt-2 mb-0" style="color: #aaaaaa; letter-spacing: 1px;">
                            <i class="fas fa-spinner fa-spin mr-2" style="color: #ffd700;"></i> {{ __("Chuẩn bị sẵn sàng...") }}
                        </p>
                    </div>
                </div>
            </div>
        `;

        if (!document.getElementById("countdownModal")) {
            document.body.insertAdjacentHTML("beforeend", modalHTML);
        } else {
            // Update the title structure to match the new design if the modal already exists in the DOM
            document.querySelector('#countdownModal .royal-title').innerHTML = `
                <img width="48" height="48" src="/img/xiangqipieces/wiki/rK.svg" alt="{{ __("Cờ tướng") }}" class="mr-3 piece-pulse">
                ${matchTitle}
            `;
            // Reset the countdown class in case it was modified
            document.getElementById("countdownNumber").className = "countdown-epic";
            document.getElementById("countdownNumber").textContent = countdown;
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

                // Allow Bootstrap to finish its fade animation before removing from DOM
                setTimeout(() => {
                    $modal.remove();
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                }, 300);

                let targetUrl = '';

                if (isBot) {
                    targetUrl = aiTargetUrl;
                    document.getElementById('match-status').innerHTML = '<i class="fas fa-chess-knight mr-2" style="color: #ffd700;"></i> {{ __("Đang vào trận với Phạm Tùng...") }}';
                } else {
                    targetUrl = (data.side === 'red')
                        ? routes.roomRed.replace(':code', data.room_code)
                        : routes.roomBlack.replace(':code', data.room_code);

                    document.getElementById('match-status').innerHTML =
                        `<i class="fas fa-check-circle mr-2 text-success"></i> {{ __("Đã tìm thấy!") }} {{ __("Vào phòng") }} "${data.room_name}".`;
                }

                window.location.href = targetUrl;
            }
        }, 1000);
    }
</script>
