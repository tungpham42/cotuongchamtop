<button id="find-match-btn" class="px-5 py-2 mx-auto mt-3 btn btn-lg btn-danger d-inline-block">
    <i class="fad fa-play mr-2"></i>
    @if ( $roomCode == '' )
        対戦相手を探す
    @else
        新しい相手を探す
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
        document.getElementById('match-status').innerText = '対戦相手を検索中...';

        // Updated Endpoint for Japanese
        axios.post('/anonymous-quick-match/ja')
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
                document.getElementById('match-status').innerText = 'サーバー接続エラー。';
                this.disabled = false;
            });
    });

    function startPolling() {
        let hasMatched = false;

        pollInterval = setInterval(() => {
            // Updated Endpoint for Japanese
            axios.get('/check-anonymous-match-status/ja', {
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
                    stopPolling('サーバーとの接続が切れました。');
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
                            <img width="42" height="42" src="/img/xiangqipieces/wiki/rK.svg" alt="シャンチー" class="mr-2">
                            対戦相手が見つかりました！
                        </h4>
                        <p class="fs-5 mb-3">対局開始まで：</p>
                        <div class="display-4 fw-bold text-danger" id="countdownNumber">${countdown}</div>
                        <p class="mt-3" style="color: #413E3C;">
                            <i class="fas fa-clock"></i> 準備してください...
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
                    `見つかりました！ルーム「${data.room_name}」に${data.color}として入室します。`;
                // Updated URL structure for Japanese
                window.location.href = `/rumu/${data.room_code}/${data.side}`;
            }
        }, 1000);
    }
</script>
