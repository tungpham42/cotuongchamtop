<button id="find-match-btn" class="px-5 py-2 mx-auto mt-3 btn btn-lg btn-danger d-inline-block">
    <i class="fad fa-play mr-2"></i>
    @if ( $roomCode == '' )
        一致するものを探す
    @else
        新しいマッチを見つける
    @endif
</button>
<span id="match-status" class="mt-3 d-inline w-100 text-center"></span>
<script>
    // Set up Axios default headers for CSRF
    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let sessionId = localStorage.getItem('anonymous_match_id');

    document.getElementById('find-match-btn').addEventListener('click', function () {
        this.disabled = true;
        document.getElementById('match-status').innerText = '対戦相手を探しています...';

        // Call anonymous-quick-match endpoint
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
                document.getElementById('match-status').innerText = '一致するものが見つかりません。';
                this.disabled = false;
            });
    });

    function startPolling() {
        const poll = setInterval(() => {
            axios.get('/check-anonymous-match-status/ja', {
                params: { session_id: sessionId }
            })
                .then(response => {
                    if (response.data.status === 'matched') {
                        clearInterval(poll);
                        document.getElementById('match-status').innerText = `一致が見つかりました！${response.data.color} 側の部屋「${response.data.room_name}」に移動します。`;
                        // Redirect to the room
                        window.location.href = `/rumu/${response.data.room_code}/${response.data.side}`;
                    } else if (response.data.status === 'error') {
                        clearInterval(poll);
                        document.getElementById('match-status').innerText = response.data.message;
                        document.getElementById('find-match-btn').disabled = false;
                    }
                })
                .catch(error => {
                    clearInterval(poll);
                    document.getElementById('match-status').innerText = 'エラー。';
                    document.getElementById('find-match-btn').disabled = false;
                });
        }, 2000); // Poll every 2 seconds
    }
</script>