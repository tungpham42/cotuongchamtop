<button id="find-match-btn" class="px-5 py-2 mx-auto mt-3 btn btn-lg btn-danger d-inline-block">
    <i class="fad fa-play mr-2"></i>
    @if ( $roomCode == '' )
        寻找对手
    @else
        寻找新对手
    @endif
</button>
<span id="match-status" class="mt-3 d-inline w-100 text-center"></span>

<script>
    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let sessionId = localStorage.getItem('anonymous_match_id');
    let pollInterval;
    let errorCount = 0;

    document.getElementById('find-match-btn').addEventListener('click', function () {
        this.disabled = true;
        document.getElementById('match-status').innerText = '正在寻找对手...';

        // Updated Endpoint for Chinese
        axios.post('/anonymous-quick-match/zh')
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
                document.getElementById('match-status').innerText = '服务器连接错误。';
                this.disabled = false;
            });
    });

    function startPolling() {
        let hasMatched = false;

        pollInterval = setInterval(() => {
            // Updated Endpoint for Chinese
            axios.get('/check-anonymous-match-status/zh', {
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
                    stopPolling('与服务器断开连接。');
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
            <div class="modal fade" id="countdownModal" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content text-center p-4" style="background-color: #E1BF85; border-radius: 15px;">
                        <h4 class="mb-3 text-danger">
                            <img width="42" height="42" src="/img/xiangqipieces/wiki/rK.svg" alt="象棋" class="mr-2">
                            已找到对手！
                        </h4>
                        <p class="fs-5 mb-3">对局将于：</p>
                        <div class="display-4 fw-bold text-danger" id="countdownNumber">${countdown}</div>
                        <p class="mt-3" style="color: #413E3C;">
                            <i class="fas fa-clock"></i> 请准备...
                        </p>
                    </div>
                </div>
            </div>
        `;

        if (!document.getElementById("countdownModal")) {
            document.body.insertAdjacentHTML("beforeend", modalHTML);
        }

        const tickSound = new Audio("/sound/tick.mp3");
        const modalEl = new bootstrap.Modal(document.getElementById('countdownModal'));
        modalEl.show();

        const countdownEl = document.getElementById("countdownNumber");
        const countdownInterval = setInterval(() => {
            countdown--;
            countdownEl.textContent = countdown;
            tickSound.currentTime = 0;
            tickSound.play().catch(() => {});

            if (countdown <= 0) {
                clearInterval(countdownInterval);
                modalEl.hide();
                document.getElementById('match-status').innerText =
                    `已找到！正在作为${data.color}进入房间“${data.room_name}”。`;
                // Updated URL structure for Chinese
                window.location.href = `/fangjian/${data.room_code}/${data.side}`;
            }
        }, 1000);
    }
</script>
