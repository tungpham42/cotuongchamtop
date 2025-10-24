<button id="find-match-btn" class="px-5 py-2 mx-auto mt-3 btn btn-lg btn-danger d-inline-block">
    <i class="fad fa-play mr-2"></i>
    @if ( $roomCode == '' )
        查找匹配
    @else
        查找新匹配
    @endif
</button>
<span id="match-status" class="mt-3 d-inline w-100 text-center"></span>
<script>
    // Set up Axios default headers for CSRF
    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let sessionId = localStorage.getItem('anonymous_match_id');

    document.getElementById('find-match-btn').addEventListener('click', function () {
        this.disabled = true;
        document.getElementById('match-status').innerText = '寻找对手...';

        // Call anonymous-quick-match endpoint
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
                document.getElementById('match-status').innerText = '找不到匹配。';
                this.disabled = false;
            });
    });

    function startPolling() {
        let hasMatched = false; // 避免多次顯示模態框

        const poll = setInterval(() => {
            axios.get('/check-anonymous-match-status/zh', {
                params: { session_id: sessionId }
            })
                .then(response => {
                    if (response.data.status === 'matched' && !hasMatched) {
                        hasMatched = true;
                        clearInterval(poll);

                        let countdown = 10;
                        const modalHTML = `
                            <div class="modal fade" id="countdownModal" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content text-center p-4" style="background-color: #E1BF85; border-radius: 15px;">
                                        <h4 class="mb-3 text-danger">
                                            <img width="42" height="42" src="/img/xiangqipieces/wiki/rK.svg" alt="象棋" class="mr-2">
                                            找到对手了！
                                        </h4>
                                        <p class="fs-5 mb-3">棋局将在以下时间后开始：</p>
                                        <div class="display-4 fw-bold text-danger" id="countdownNumber">${countdown}</div>
                                        <p class="mt-3" style="color: #413E3C;">
                                            <i class="fas fa-clock"></i> 请做好准备...
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;

                        // 只添加一次模態框
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
                                    `找到匹配！前往 ${response.data.color} 一侧的房间“${response.data.room_name}”。`;
                                // 跳轉房間
                                window.location.href = `/fangjian/${response.data.room_code}/${response.data.side}`;
                            }
                        }, 1000);
                    } else if (response.data.status === 'error') {
                        clearInterval(poll);
                        document.getElementById('match-status').innerText = response.data.message;
                        document.getElementById('find-match-btn').disabled = false;
                    }
                })
                .catch(() => {
                    clearInterval(poll);
                    document.getElementById('match-status').innerText = '错误。';
                    document.getElementById('find-match-btn').disabled = false;
                });
        }, 2000); // 每2秒檢查一次
    }
</script>
