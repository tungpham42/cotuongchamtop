<button id="find-match-btn" class="px-5 py-2 mx-auto mt-3 btn btn-lg btn-danger d-inline-block">
    <i class="fad fa-play mr-2"></i>
    @if ( $roomCode == '' )
        Find Match
    @else
        Find New Match
    @endif
</button>
<span id="match-status" class="mt-3 d-inline w-100 text-center"></span>
<script>
    // Set up Axios default headers for CSRF
    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let sessionId = localStorage.getItem('anonymous_match_id');

    document.getElementById('find-match-btn').addEventListener('click', function () {
        this.disabled = true;
        document.getElementById('match-status').innerText = 'Looking for opponent...';

        // Call anonymous-quick-match endpoint
        axios.post('/anonymous-quick-match/en')
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
                document.getElementById('match-status').innerText = 'Cannot find match.';
                this.disabled = false;
            });
    });

    function startPolling() {
        let hasMatched = false; // <-- prevents flicker & multiple modals

        const poll = setInterval(() => {
            axios.get('/check-anonymous-match-status/en', {
                params: { session_id: sessionId }
            })
                .then(response => {
                    if (response.data.status === 'matched' && !hasMatched) {
                        hasMatched = true; // mark as matched so it doesn't repeat
                        clearInterval(poll);

                        let countdown = 10;
                        const modalHTML = `
                            <div class="modal fade" id="countdownModal" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content text-center p-4" style="background-color: #E1BF85; border-radius: 15px;">
                                        <h4 class="mb-3 text-danger">
                                            <img width="42" height="42" src="/img/xiangqipieces/wiki/rK.svg" alt="Chinese Chess" class="mr-2">
                                            Opponent Found!
                                        </h4>
                                        <p class="fs-5 mb-3">The game will start in:</p>
                                        <div class="display-4 fw-bold text-danger" id="countdownNumber">${countdown}</div>
                                        <p class="mt-3" style="color: #413E3C;"><i class="fas fa-clock"></i> Get ready...</p>
                                    </div>
                                </div>
                            </div>
                        `;

                        // Insert only if not already present
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
                                    `Match found! Go to room "${response.data.room_name}" with ${response.data.color} side.`;
                                window.location.href = `/room/${response.data.room_code}/${response.data.side}`;
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
                    document.getElementById('match-status').innerText = 'Error.';
                    document.getElementById('find-match-btn').disabled = false;
                });
        }, 2000);
    }
</script>
