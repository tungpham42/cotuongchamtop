@extends('layouts.playlayout')

@section('aboveBoard')
    @if ($role === 'watch')
        @if (isset($room->host_id))
            <h5 id="room-title" class="text-center my-1"><span id="host-title">{!! $userPresenter->renderPlayerName($room->host_id, true) !!}</span> <span id="guest-title">{!! $userPresenter->renderPlayerName($room->guest_id, true) !!}</span></h5>
        @else
            <h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="{{ __('Quan sát hai kỳ thủ đang chơi') }}">{{ __('Bạn đang theo dõi') }}</h5>
        @endif
    @elseif ($role === 'random')
        <h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="{{ __('Bạn đang đi quân đen') }}">{{ __('Bạn đang đánh ngẫu nhiên') }}</h5>
    @else
        @if (isset($room->host_id))
            <h5 id="room-title" class="text-center my-1"><span id="host-title">{!! $userPresenter->renderPlayerName($room->host_id, true) !!}</span> <span id="guest-title">{!! $userPresenter->renderPlayerName($room->guest_id, true) !!}</span></h5>
        @else
            @php
                $tooltip = $orientation === 'red' ? 'Bạn đang đi quân đỏ' : 'Bạn đang đi quân đen';
                $titleMap = [
                    'host' => 'Bạn là chủ phòng',
                    'guest' => 'Bạn đã được mời',
                    'red' => 'Bạn là quân Đỏ',
                    'black' => 'Bạn là quân Đen',
                ];
            @endphp
            <h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="{{ __($tooltip) }}">{{ __($titleMap[$role]) }}</h5>
        @endif
    @endif
    <span id="room-name">{{ __('Tên phòng') }}: {{ $room->name }}</span>

    @include('layouts.partials.timer')

    @if ($role !== 'watch')

        <!-- INCLUDE VIDEO CALL COMPONENT HERE -->
        @if(isset($room->host_id) && in_array($role, ['host', 'guest', 'red', 'black']) && auth()->check())
            <div class="w-50 mx-auto">
                @include('layouts.partials.videoCall')
            </div>
        @endif
        <!-- END VIDEO CALL COMPONENT -->
    @endif
@endsection

@section('aboveContent')
@include('layouts.partials.inviteLink')
@endsection

@section('belowContent')
    @if ($role === 'watch')
        @if (!isset($room->host_id) && !isset($room->result))
            <p class="w-100 text-center">
                @if (str_contains($room->fen, ' r '))
                    <a data-step="2" data-intro="{{ __('Ấn vào đây để vào ván đấu khi đến lượt bạn') }}" id="join-link" class="btn btn-danger text-light btn-lg showPromotion" href="{{ localized_url('room.host', ['code' => $roomCode]) }}" data-toggle="tooltip" data-placement="top" title="{{ __('Đến lược bạn đi') }}"><i class="fad fa-sign-in-alt"></i> {{ __('Vào trận') }}</a>
                @elseif (str_contains($room->fen, ' b '))
                    <a data-step="2" data-intro="{{ __('Ấn vào đây để vào ván đấu khi đến lượt bạn') }}" id="join-link" class="btn btn-dark text-light btn-lg showPromotion" href="{{ localized_url('room.guest', ['code' => $roomCode]) }}" data-toggle="tooltip" data-placement="top" title="{{ __('Đến lược bạn đi') }}"><i class="fad fa-sign-in-alt"></i> {{ __('Vào trận') }}</a>
                @endif
            </p>
        @elseif (isset($room->result))
            <p class="w-100 text-center">
                <span class="text-light lead">{{ __('Đã đấu xong') }}</span>
            </p>
        @endif
    @else
        @php
            $resignSteps = ['host' => '4', 'red' => '5', 'guest' => '2', 'black' => '2', 'random' => '1'];
            $resignStep = $resignSteps[$role] ?? '1';
        @endphp
        <p class="w-100 text-center">
            <a data-step="{{ $resignStep }}" data-intro="{{ __('Ấn vào đây nếu bạn không biết đi nước nào') }}" id="resign" class="btn btn-dark btn-lg {{ in_array($role, ['host', 'red']) ? 'w-25' : '' }}"><i class="fad fa-flag"></i> {{ __('Bỏ cuộc') }}</a>
        </p>
    @endif

    @include('layouts.partials.kypho')

    <script>
        @if ($role === 'host' && $room['pass'] != null)
        function validateForm() {
            document.getElementById('status').innerHTML = "{{ __('Đang xử lý') }}...";

            let formData = {
                'ma-phong': '{{ $roomCode }}',
                'pass': $('#inputPassword').val(),
                'lang': '{{ app()->getLocale() }}' // <-- Tell the API which language to use
            };

            $.ajax({
                url: "{{ url('/api/changePass') }}",
                type: "POST",
                data : formData,
                dataType: 'json',
                success: function(data) {
                    $('#status').text(data.message);
                    if (data.code) $('#inputPassword').val("");
                },
                error: function (jqXHR) { $('#status').text(jqXHR); }
            });
        }
        @endif

        @if ($role !== 'watch' && $room['pass'] != null)
        $(document).ready(function() {
            bootbox.prompt({
                title: "{{ __('Nhập mật khẩu để vào phòng') }}:",
                centerVertical: true,
                closeButton: false,
                locale: '{{ __('vi') }}',
                buttons: {
                    confirm: { label: '<i class="fas fa-check"></i> {{ __('Nhập') }}', className: 'btn-danger pulse-red' },
                    cancel: { className: 'btn-dark text-light' }
                },
                callback: function(password){
                    if (password != null) {
                        $.ajax({
                            type: "GET",
                            url: '{{ url('/api') }}/getPass/{{ $roomCode }}',
                            dataType: 'text'
                        }).done(function(data) {
                            if (data != password) {
                                bootbox.alert({
                                    message: "{{ __('Sai mật khẩu! Bạn sẽ được chuyển hướng về Trang chủ') }}",
                                    size: 'small',
                                    centerVertical: true,
                                    closeButton: false,
                                    locale: '{{ __('vi') }}',
                                    buttons: { ok: { className: 'btn-danger pulse-red' } },
                                    callback: function () { window.location.href = '{{ localized_url('ai.home') }}'; }
                                });
                            }
                        });
                    } else {
                        bootbox.alert({
                            message: "{{ __('Bạn đã ấn Hủy! Bạn sẽ được chuyển hướng về Trang chủ') }}",
                            size: 'small',
                            centerVertical: true,
                            closeButton: false,
                            locale: '{{ __('vi') }}',
                            buttons: { ok: { className: 'btn-danger pulse-red' } },
                            callback: function () { window.location.href = '{{ localized_url('ai.home') }}'; }
                        });
                    }
                }
            });
        });
        @endif

        let board = null;
        let game = new Xiangqi();
        let currentFEN = '';
        let alertShown = false;
        let hasGameOverSound = false;
        let resignAlertShown = false;
        let kypho = null;
        let lastMoveIccs = null;
        let expectedFEN = null;
        let boardRevision = {{ (int) ($room->timer_revision ?? 0) }};
        // HTTP recovery also restores a move that was never accepted by the server.
        window.addEventListener('room:timer-snapshot', event => {
            const data = event.detail;
            const revision = Number(data.timer_revision ?? boardRevision);
            if (revision < boardRevision) return;
            boardRevision = revision;
            expectedFEN = null;
            if (data.fen && data.fen !== game.fen()) {
                const positionChanged = data.fen.split(' ')[0] !== game.fen().split(' ')[0];
                currentFEN = data.fen;
                game.load(data.fen);
                board.position(data.fen, true);
                if (positionChanged && !data.fen.includes('resign')) playMoveResultSound();
                if (kypho) kypho.syncMoves('{{ url('/api') }}/readMoves/{{ $roomCode }}');
                updateStatus();
            }
        });

        @if ($role === 'watch')
            let serverFen = '{!! $room->fen !!}';
            let cleanFen = serverFen ? serverFen.replace(' resign', '') : null;
            if (cleanFen) { game.load(cleanFen); }
            currentFEN = game.fen();
        @else
            @if (!empty($room->fen))
                game.load('{!! $room->fen !!}');
            @endif
            currentFEN = game.fen();
        @endif

        // Best-effort "has a move already been made" flag, used only to
        // decide whether the very next move gets the "opening" chime
        // instead of a plain move/check sound.
        let hasMoved = currentFEN !== '{{ config('xiangqi.initial_fen') }}';

        // Picks the right synthesized sound for the move that was just
        // applied to `game`. Checkmate/stalemate are left to updateStatus()
        // so they aren't doubled up with a plain "move" or "check" sound.
        function playMoveResultSound () {
            if (typeof XiangqiSound === 'undefined') return;
            if (game.in_checkmate() || game.in_draw()) return;
            if (!hasMoved) {
                hasMoved = true;
                XiangqiSound.playOpening();
                return;
            }
            if (game.in_check()) {
                XiangqiSound.playCheck();
                return;
            }
            // Inspect the last move to determine if a piece was taken
            const history = game.history({ verbose: true });
            if (history.length > 0) {
                const lastMove = history[history.length - 1];

                // Xiangqi.js sets a 'captured' property or a 'c' flag for captures
                if (lastMove.captured || (lastMove.flags && lastMove.flags.includes('c'))) {
                    XiangqiSound.playCapture();
                    return;
                }
            }
            XiangqiSound.playMove();
        }

        function updateFenCode(roomCode, moveIccs) {
            @if ($role === 'watch')
                board.position(game.fen(), true);
                game.load(game.fen());
                $.ajax({
                    type: "POST",
                    url: '{{ url('/api') }}/updateFEN',
                    data: { 'ma-phong': roomCode, 'FEN': game.fen() },
                    dataType: 'text'
                });
            @else
                const previousFEN = currentFEN;
                currentFEN = game.fen();
                expectedFEN = game.fen();
                const payload = { 'ma-phong': roomCode, 'FEN': game.fen(), 'expected_fen': previousFEN };
                if (moveIccs) { payload.move = moveIccs; }

                // Save the move and clock together; render the new turn immediately.
                if (moveIccs || !game.game_over()) {
                    let activeColor = game.turn() === 'b' ? 'red' : 'black';
                    if (typeof window.switchTurn === 'function') {
                        return window.switchTurn(roomCode, activeColor, payload).then(data => {
                            if (data && data.timer_revision < boardRevision) return;
                            if (data) {
                                boardRevision = Number(data.timer_revision ?? boardRevision);
                                expectedFEN = null;
                            }
                            if (data?.success === false && data.fen) {
                                expectedFEN = null;
                                currentFEN = data.fen;
                                game.load(data.fen);
                                board.position(data.fen, true);
                                updateStatus();
                            }
                            if (kypho) kypho.syncMoves('{{ url('/api') }}/readMoves/' + roomCode);
                        });
                    }
                }

                // Gọi API cập nhật FEN chạy ngầm
                $.ajax({
                    type: "POST",
                    url: '{{ url('/api') }}/updateFEN',
                    data: payload,
                    dataType: 'text'
                }).done(function() {
                    // Chỉ đồng bộ kỳ phổ sau khi FEN đã lưu
                    if (kypho) { kypho.syncMoves('{{ url('/api') }}/readMoves/' + roomCode); }
                    updateStatus();
                });
            @endif
        }

        if (typeof Echo !== 'undefined') {
            Echo.channel('room.{{ $roomCode }}')
                .listen('.room.updated', (e) => {
                    let newFEN = e.room.fen;
                    if (!newFEN) return;
                    const revision = Number(e.timer?.timer_revision ?? boardRevision);
                    if (revision < boardRevision) return;

                    // -------------------------------------------------------------
                    // FIX RUBBER-BANDING (GIẬT CỤC CỜ)
                    // Bỏ qua bản tin FEN cũ do race condition của API switchTurn
                    // -------------------------------------------------------------
                    if (expectedFEN && newFEN !== expectedFEN && game.fen() === expectedFEN && revision <= boardRevision) {
                        return;
                    }
                    // Khi server trả về đúng FEN mới nhất, ta mở khóa
                    if (expectedFEN && (newFEN === expectedFEN || revision > boardRevision)) {
                        expectedFEN = null;
                    }
                    boardRevision = revision;

                    // So sánh phần vị trí quân cờ (bỏ qua lượt đi, đếm số bước ở đuôi FEN)
                    let newBoardState = newFEN.split(' ')[0];
                    let currentBoardState = game.fen().split(' ')[0];

                    if (newBoardState !== currentBoardState) {
                        currentFEN = newFEN;
                        game.load(newFEN);
                        board.position(newFEN, true); // Animate di chuyển quân cờ

                        if (!newFEN.includes('resign')) {
                            playMoveResultSound();
                        }

                        if (typeof kypho !== 'undefined' && kypho !== null) {
                            kypho.syncMoves('{{ url('/api') }}/readMoves/{{ $roomCode }}');
                        }
                    } else {
                        // Thế cờ giống hệt, chỉ cập nhật trạng thái logic ngầm,
                        // KHÔNG gọi board.position() để tránh UI bị nháy (micro-stutter)
                        currentFEN = newFEN;
                        if (newFEN !== game.fen()) {
                            game.load(newFEN);
                        }
                    }
                    updateStatus();
                });
        }

        @if ($role !== 'watch')
        function updateResult(roomCode, result, timeout = false) {
            if (alertShown) return;
            alertShown = true;
            let timeoutRejected = false;
            const recoverTimeout = () => {
                if (!timeout) return;
                timeoutRejected = true;
                alertShown = false;
                window.resumeRoomTimer?.();
            };
            @if($role !== 'random' && auth()->check() && (auth()->id() == $room->host_id || auth()->id() == $room->guest_id))
                let successMsg = '{{ __("Trận đấu kết thúc") }}';
                $.ajax({
                    type: "POST",
                    url: '{{ url('/api') }}/updateResult',
                    data: { 'ma-phong': roomCode, 'result': result, 'timeout': timeout ? 1 : 0, 'id': '{{ auth()->id() }}', 'lang': '{{ __('vi') }}' },
                    dataType: 'json'
                }).done(function(data) {
                    if (timeout && data?.success === false) { recoverTimeout(); return; }
                    if (data && data.success) successMsg = data.success;
                    if (data && data.karma) successMsg += buildKarmaMessage(data.karma);
                }).fail(recoverTimeout).always(function() {
                    if (timeoutRejected) return;
                    bootbox.alert({
                        message: successMsg,
                        size: 'small',
                        centerVertical: true,
                        closeButton: false,
                        locale: '{{ __("vi") }}',
                        buttons: { ok: { className: 'btn-danger pulse-red' } },
                        callback: function () {
                            $.ajax({
                                type: "POST",
                                url: '{{ url('/api') }}/updateElo',
                                data: { 'ma-phong': roomCode, 'result': result },
                                dataType: 'json'
                            }).always(function(){
                                setTimeout(function(){
                                    @if (!isset($room->tournament_id))
                                    window.location.href = "{{ localized_url('app.dashboard') }}";
                                    @else
                                    window.location.href = "{{ localized_url('tournaments.show', ['slug' => $tournament->slug ?? '']) }}";
                                    @endif
                                }, 500);
                            });
                        }
                    });
                });
            @elseif(!isset($room->host_id) || $role === 'random')
                let sideMsg = '{{ __("Trận đấu kết thúc") }}';
                $.ajax({
                    type: "POST",
                    url: '{{ url('/api') }}/updateSideResult',
                    data: { 'ma-phong': roomCode, 'result': result, 'timeout': timeout ? 1 : 0, 'lang': '{{ __('vi') }}', 'side': '{{ $updateSide }}' },
                    dataType: 'json'
                }).done(function(data) {
                    if (timeout && data?.success === false) { recoverTimeout(); return; }
                    if (data && data.success) sideMsg = data.success;
                }).fail(recoverTimeout).always(function() {
                    if (timeoutRejected) return;
                    bootbox.alert({
                        message: sideMsg,
                        size: 'small',
                        centerVertical: true,
                        closeButton: false,
                        locale: '{{ __("vi") }}',
                        buttons: { ok: { className: 'btn-danger pulse-red' } },
                        callback: function () { window.location.href = "{{ localized_url('room.list') }}"; }
                    });
                });
            @endif
        }
        @endif

        function removeGreySquares () { $('#ban-co .square-2b8ce').removeClass('highlight'); }
        function greySquare (square) { $('#ban-co .square-' + square).addClass('highlight'); }

        function onDragStart (source, piece) {
            @if ($role === 'watch')
                return false;
            @else
                {{-- Disable drag in tournament mode if room lacks 2 players --}}
                @if (isset($room->tournament_id) && (!isset($room->host_id) || !isset($room->guest_id)))
                    return false;
                @endif

                if (game.game_over()) return false;
                if (window.canSubmitRoomMove && !window.canSubmitRoomMove(game.turn() === 'r' ? 'red' : 'black')) return false;
                if (typeof systemPaused !== 'undefined' && systemPaused) return false;
                if ((game.turn() === 'r' && piece.search(/^b/) !== -1) || (game.turn() === 'b' && piece.search(/^r/) !== -1)) return false;
                if ((board.orientation() == 'red' && game.turn() === 'b') || (board.orientation() == 'black' && game.turn() === 'r')) return false;
            @endif
        }

        function onDrop (source, target) {
            removeGreySquares();
            let move = game.move({ from: source, to: target });
            @if ($role === 'watch')
                updateStatus();
            @else
                if (move !== null) { lastMoveIccs = move.iccs; } else { return 'snapback'; }
                // A terminal result must wait until its final move is stored.
                if (!game.game_over()) updateStatus();
            @endif
        }

        function onMouseoverSquare (square, piece) {
            let moves = game.moves({ square: square, verbose: true });
            if (moves.length === 0) return;
            greySquare(square);
            for (let i = 0; i < moves.length; i++) { greySquare(moves[i].to); }
        }

        function onMouseoutSquare (square, piece) { removeGreySquares(); }

        function onSnapEnd () {
            playMoveResultSound();
            const saved = updateFenCode('{{ $roomCode }}', lastMoveIccs);
            lastMoveIccs = null;
            saved?.then?.(() => updateStatus());
        }

        function updatePlayersTitle() {
            $.ajax({
                type: "POST",
                url: '{{ url('/api') }}/renderPlayersTitle',
                data: { 'ma-phong': '{{ $roomCode }}' },
                dataType: 'text'
            }).done(function(data){ $('h5#room-title').html(data); });
        }

        @if (isset($room->host_id))
        const updatePlayers = setInterval(function() { updatePlayersTitle(); }, 5000);
        @endif

        function updateStatus () {
            var status = ''
            var moveColor = '{{ __("Đỏ") }}'
            if (game.turn() === 'b') moveColor = '{{ __("Đen") }}'

            if (game.in_checkmate()) {
                status = moveColor + ' {{ __("bị chiếu bí") }}';
                @if ($role !== 'watch')
                    if (game.turn() === 'b') {
                        @if($role === 'random') updateResult('{{ $roomCode }}', '1'); @else setTimeout(function() { updateResult('{{ $roomCode }}', '1'); }, 1000); @endif
                    } else if (game.turn() === 'r') {
                        @if($role === 'random') updateResult('{{ $roomCode }}', '-1'); @else setTimeout(function() { updateResult('{{ $roomCode }}', '-1'); }, 1000); @endif
                    }
                @endif
            } else if (game.in_draw()) {
                status = '{{ __("Hòa") }}';
                @if ($role !== 'watch')
                    @if($role === 'random') updateResult('{{ $roomCode }}', '0'); @else setTimeout(function() { updateResult('{{ $roomCode }}', '0'); }, 1000); @endif
                @endif
            } else {
                status = moveColor
                if (game.game_over() && !game.in_draw() && !game.fen().includes('resign')) {
                    @if ($role !== 'watch')
                        if (game.turn() === 'b') {
                            @if($role === 'random') updateResult('{{ $roomCode }}', '1'); @else setTimeout(function() { updateResult('{{ $roomCode }}', '1'); }, 1000); @endif
                        } else if (game.turn() === 'r') {
                            @if($role === 'random') updateResult('{{ $roomCode }}', '-1'); @else setTimeout(function() { updateResult('{{ $roomCode }}', '-1'); }, 1000); @endif
                        }
                    @endif
                }
                if (game.in_check()) {
                    status += ', ' + moveColor + ' {{ __("đang bị chiếu") }}'
                    if ((board.orientation() == 'red' && game.turn() === 'r') || (board.orientation() == 'black' && game.turn() === 'b')) {
                        $('#checkmateText').show();
                    }
                } else {
                    $('#checkmateText').hide();
                }
            }

            if (game.turn() === 'r') {
                $('#game-status').removeClass('black').addClass('red');
            } else if (game.turn() === 'b') {
                $('#game-status').removeClass('red').addClass('black');
            }

            $('#game-status').html(status);
            $('#header-status').html(': '+status);

            if (game.game_over()) {
                if (!hasGameOverSound) {
                    hasGameOverSound = true;
                    if (game.in_checkmate()) {
                        XiangqiSound.playCheckmate();
                    } else {
                        XiangqiSound.playStalemate();
                    }
                }
                $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> {{ __("Hết trận") }}');
                $('#header-status').html(': '+status+' - {{ __("Hết trận") }}');

                if (typeof Echo !== 'undefined') { Echo.leave('room.{{ $roomCode }}'); }
                @if (isset($room->host_id))
                clearInterval(updatePlayers);
                @endif
            }

            if (game.fen().includes('resign') && !resignAlertShown) {
                resignAlertShown = true;
                $('#header-status').html(': '+status+' - {{ __("Đã bỏ cuộc") }}');
                let resignResult = (game.fen().includes('resign-black')) ? '1' : (game.fen().includes('resign-red') ? '-1' : (board.orientation() === 'black' ? '1' : '-1'));

                bootbox.alert({
                    message: '<i class="fad fa-flag-checkered"></i> {{ __("Đã bỏ cuộc") }}',
                    locale: '{{ __("vi") }}',
                    centerVertical: true,
                    closeButton: false,
                    size: 'small',
                    buttons: { ok: { className: 'btn-danger pulse-red' } },
                    @if ($role !== 'watch')
                    callback: function() { updateResult('{{ $roomCode }}', resignResult); }
                    @endif
                });
                $('#game-over').html('<i class="fad fa-flag-checkered"></i> {{ __("Đã bỏ cuộc") }}');
                $('#resign').addClass('disabled').attr('aria-disabled', true);
            }

            if (kypho) { kypho.updateControls(); }
        }

        let config = {
            @if ($role === 'watch')
                draggable: false,
                position: cleanFen ? cleanFen : 'start',
                orientation: "{{ str_contains($room->fen, ' r ') ? 'red' : 'black' }}",
            @else
                @if ($role === 'random')
                    draggable: {{ ($room->red_time == 0 || $room->black_time == 0) ? 'false' : 'true' }},
                    position: 'start',
                @else
                    {{-- Disable draggable config in tournament mode if room lacks 2 players --}}
                    draggable: {{ (isset($room->tournament_id) && (!isset($room->host_id) || !isset($room->guest_id))) ? 'false' : 'true' }},
                    position: game.fen(),
                @endif
                orientation: "{{ $orientation }}",
            @endif
            onDragStart: onDragStart,
            onDrop: onDrop,
            onMouseoutSquare: onMouseoutSquare,
            onMouseoverSquare: onMouseoverSquare,
            onSnapEnd: onSnapEnd,
            showNotation: true
        };

        board = Xiangqiboard('ban-co', config);
        $(window).resize(board.resize);
        kypho = KyPho.initRoom({
            board: board,
            startFen: '{{ config('xiangqi.initial_fen') }}',
            isLive: function() { return !game.game_over(); }
        });

        if (kypho) { kypho.syncMoves('{{ url('/api') }}/readMoves/{{ $roomCode }}'); }
        updateStatus();

        @if ($role !== 'watch')
        $('#resign').on('click', function() {
            game.load(game.fen() + ' resign-' + board.orientation());
            updateFenCode('{{ $roomCode }}');
            updateStatus();
        });
        @endif

        @if ($role === 'host' || $role === 'red')
            @if (isset($room->host_id) && auth()->id() == $room->host_id)
            $('#choi').removeClass('pulse-red').addClass('disabled');
            @endif
        @elseif ($role === 'guest' || $role === 'black')
            @if (isset($room->guest_id) && auth()->id() == $room->guest_id)
            $('#choi').removeClass('pulse-red').addClass('disabled');
            @endif
        @endif

        const style = document.createElement('style');
        style.textContent = `
            .fa-spinner { margin-left: 5px; }
            .disabled { opacity: 0.5; pointer-events: none; }
            .highlight { background-color: #ffeb3b !important; opacity: 0.6; }
        `;
        document.head.appendChild(style);
    </script>
@endsection
