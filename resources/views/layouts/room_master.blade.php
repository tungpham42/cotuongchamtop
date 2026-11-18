@extends('layouts.playlayout')

@php
    // Rematch is offered to both seats (Host/Red and Guest/Black) of a normal (non-tournament) match:
    //  - registered players (host_id / guest_id), or
    //  - guests paired through matchmaking (host_session / guest_session).
    //
    // NOTE: this must NOT require the opponent to be present at render time. The host's page is
    // usually rendered while waiting for an opponent, so guest_id / guest_session are still empty
    // then. Whether an opponent really exists is checked when the game ends (showRematchPanel)
    // and again by the server.
    $isUserRematch = in_array($role, ['host', 'guest', 'red', 'black'], true)
        && auth()->check()
        && !empty($room->host_id)
        && in_array(auth()->id(), [$room->host_id, $room->guest_id])
        && !isset($room->tournament_id);
    $isGuestRematch = in_array($role, ['red', 'black'], true)
        && empty($room->host_id) && empty($room->guest_id)
        && !empty($room->host_session)
        && !isset($room->tournament_id);
    $canRematch = $isUserRematch || $isGuestRematch;
@endphp

@section('aboveBoard')
<div class="pvp-scoreboard">
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
    <span id="room-name"><i class="fad fa-chess-board"></i> {{ __('Tên phòng') }}: {{ $room->name }}</span>
    <div id="pvp-turn-banner" class="pvp-turn-banner" role="status" aria-live="polite" data-state="idle">
        <span class="pvp-turn-dot"></span>
        <span id="pvp-turn-text">&nbsp;</span>
        <span id="pvp-conn" class="pvp-conn" data-state="ok" title="{{ __('Đã kết nối') }}"><i class="fas fa-circle"></i></span>
    </div>
</div>

@if ($canRematch)
    {{-- Shown when the game is over. Sits right under the scoreboard so it is the first thing seen. --}}
    <style>
        #rematch-panel {
            max-width: 560px;
            margin: .75rem auto;
            padding: .9rem 1rem;
            border-radius: 12px;
            border: 1px solid var(--royal-gold, #d4af37);
            background: var(--glass-bg-dark, rgba(20, 20, 20, .85));
            -webkit-backdrop-filter: var(--glass-blur, blur(8px));
            backdrop-filter: var(--glass-blur, blur(8px));
            box-shadow: 0 6px 22px rgba(0, 0, 0, .55), 0 0 18px rgba(255, 215, 0, .25);
            animation: rematchPanelIn .35s cubic-bezier(.175, .885, .32, 1.275) both;
        }
        #rematch-panel .btn { min-width: 9rem; }
        @keyframes rematchPanelIn {
            from { opacity: 0; transform: translateY(-8px) scale(.97); }
            to   { opacity: 1; transform: none; }
        }
        @media (prefers-reduced-motion: reduce) { #rematch-panel { animation: none; } }
    </style>
    <div id="rematch-panel" class="text-center d-none" role="status" aria-live="polite"></div>
@endif

    @if ($role !== 'watch')
        {{-- @include('layouts.partials.timer') --}}

        <!-- INCLUDE VIDEO CALL COMPONENT HERE -->
        @if(isset($room->host_id) && in_array($role, ['host', 'guest', 'red', 'black']) && auth()->check())
            <div class="w-75 mx-auto">
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
                currentFEN = game.fen();
                expectedFEN = game.fen();
                const payload = { 'ma-phong': roomCode, 'FEN': game.fen() };
                if (moveIccs) { payload.move = moveIccs; }

                // [SỬA Ở ĐÂY] Gọi chuyển lượt ngay lập tức trên Client (Optimistic UI)
                if (!game.game_over()) {
                    let activeColor = game.turn() === 'b' ? 'red' : 'black';
                    if (typeof window.switchTurn === 'function') {
                        window.switchTurn('{{ $roomCode }}', activeColor);
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
                });
            @endif
        }

        if (typeof Echo !== 'undefined') {
            Echo.channel('room.{{ $roomCode }}')
                .listen('.room.updated', (e) => {
                    let newFEN = e.room.fen;
                    if (!newFEN) return;

                    // -------------------------------------------------------------
                    // FIX RUBBER-BANDING (GIẬT CỤC CỜ)
                    // Bỏ qua bản tin FEN cũ do race condition của API switchTurn
                    // -------------------------------------------------------------
                    if (expectedFEN && newFEN !== expectedFEN && game.fen() === expectedFEN) {
                        return;
                    }
                    // Khi server trả về đúng FEN mới nhất, ta mở khóa
                    if (expectedFEN && newFEN === expectedFEN) {
                        expectedFEN = null;
                    }

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
        function updateResult(roomCode, result) {
            if (alertShown) return;
            alertShown = true;
            @if($role !== 'random' && auth()->check() && (auth()->id() == $room->host_id || auth()->id() == $room->guest_id))
                let successMsg = '{{ __("Trận đấu kết thúc") }}';
                $.ajax({
                    type: "POST",
                    url: '{{ url('/api') }}/updateResult',
                    data: { 'ma-phong': roomCode, 'result': result, 'id': '{{ auth()->id() }}', 'lang': '{{ __('vi') }}' },
                    dataType: 'json'
                }).done(function(data) {
                    if (data && data.success) successMsg = data.success;
                    if (data && data.karma) successMsg += buildKarmaMessage(data.karma);
                }).always(function() {
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
                                @if ($isUserRematch)
                                // Stay on the page and offer a rematch instead of redirecting away.
                                // If no rematch can be offered (e.g. the opponent was an anonymous
                                // random player), fall back to the old redirect.
                                showRematchPanel(function () {
                                    setTimeout(function () {
                                        window.location.href = "{{ localized_url('app.dashboard') }}";
                                    }, 500);
                                });
                                @else
                                setTimeout(function(){
                                    @if (!isset($room->tournament_id))
                                    window.location.href = "{{ localized_url('app.dashboard') }}";
                                    @else
                                    window.location.href = "{{ localized_url('tournaments.show', ['slug' => $tournament->slug ?? '']) }}";
                                    @endif
                                }, 500);
                                @endif
                            });
                        }
                    });
                });
            @elseif(!isset($room->host_id) || $role === 'random')
                let sideMsg = '{{ __("Trận đấu kết thúc") }}';
                $.ajax({
                    type: "POST",
                    url: '{{ url('/api') }}/updateSideResult',
                    data: { 'ma-phong': roomCode, 'result': result, 'lang': '{{ __('vi') }}', 'side': '{{ $updateSide }}' },
                    dataType: 'json'
                }).done(function(data) {
                    if (data && data.success) sideMsg = data.success;
                }).always(function() {
                    bootbox.alert({
                        message: sideMsg,
                        size: 'small',
                        centerVertical: true,
                        closeButton: false,
                        locale: '{{ __("vi") }}',
                        buttons: { ok: { className: 'btn-danger pulse-red' } },
                        callback: function () {
                            // No redirect to the lobby: stay on the finished game. Guests who still
                            // have their match session are offered a rematch.
                            @if ($isGuestRematch)
                            showRematchPanel();
                            @endif
                        }
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
                updateStatus();
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
            updateFenCode('{{ $roomCode }}', lastMoveIccs);
            lastMoveIccs = null;
        }

        @if ($canRematch)
        // ---- Rematch ----
        // A finished room is immutable, so an accepted rematch creates a new room
        // (colours swapped) and both players are sent to it.
        const REMATCH_URL = '{{ url('/api') }}/room/{{ $roomCode }}/rematch';
        // 'user': registered players, identified by user id. 'guest': matchmaking guests,
        // identified by the browser match session id (same identity findMatch uses).
        const REMATCH_MODE = '{{ $isUserRematch ? 'user' : 'guest' }}';
        // Colour in THIS room; colours swap in the rematch room.
        const MY_SIDE = '{{ in_array($role, ['host', 'red']) ? 'red' : 'black' }}';
        const MY_USER_ID = {{ (int) auth()->id() }};
        function getGuestSessionId() {
            try { return sessionStorage.getItem('match_session_id') || ''; } catch (e) { return ''; }
        }
        function rematchIdentity() {
            return REMATCH_MODE === 'user' ? { id: MY_USER_ID } : { session_id: getGuestSessionId() };
        }
        const T_REMATCH = '{{ __("Đấu lại") }}';
        const T_REMATCH_WAITING = '{{ __("Đã gửi lời mời đấu lại. Đang chờ đối thủ...") }}';
        const T_REMATCH_INCOMING = '{{ __("Đối thủ muốn đấu lại!") }}';
        const T_REMATCH_DECLINED = '{{ __("Đối thủ đã từ chối đấu lại.") }}';
        const T_REMATCH_STARTING = '{{ __("Đang chuyển sang ván mới...") }}';
        const T_REMATCH_ERROR = '{{ __("Không thể đấu lại lúc này") }}';
        const T_ACCEPT = '{{ __("Chấp nhận") }}';
        const T_DECLINE = '{{ __("Từ chối") }}';
        const T_CANCEL_INVITE = '{{ __("Hủy lời mời") }}';
        const T_LEAVE = '{{ __("Về trang chính") }}';
        const REMATCH_HOME_URL = '{{ localized_url('app.dashboard') }}';

        let rematchState = 'idle'; // idle | waiting | incoming | declined | closed | error | redirecting
        let rematchPanelVisible = false;

        function renderRematch() {
            const $p = $('#rematch-panel');
            if (!rematchPanelVisible || !$p.length) return;
            const $leave = $('<a class="btn btn-dark text-light m-1"></a>').attr('href', REMATCH_HOME_URL)
                .append('<i class="fad fa-home"></i> ').append(document.createTextNode(T_LEAVE));
            const btn = function (cls, icon, label, handler) {
                return $('<button type="button" class="btn m-1"></button>').addClass(cls)
                    .append('<i class="' + icon + '"></i> ').append(document.createTextNode(label)).on('click', handler);
            };
            const msg = function (text) { return $('<p class="text-light lead mb-2"></p>').text(text); };

            const wasHidden = $p.hasClass('d-none');
            $p.empty().removeClass('d-none');
            if (rematchState === 'redirecting') {
                $p.append(msg(T_REMATCH_STARTING));
            } else if (rematchState === 'waiting') {
                $p.append(msg(T_REMATCH_WAITING))
                  .append(btn('btn-dark text-light', 'fad fa-times', T_CANCEL_INVITE, function () { rematchCall('decline'); }))
                  .append($leave);
            } else if (rematchState === 'incoming') {
                $p.append(msg(T_REMATCH_INCOMING))
                  .append(btn('btn-danger pulse-red', 'fad fa-check', T_ACCEPT, function () { rematchCall('accept'); }))
                  .append(btn('btn-dark text-light', 'fad fa-times', T_DECLINE, function () { rematchCall('decline'); }))
                  .append($leave);
            } else if (rematchState === 'declined' || rematchState === 'closed' || rematchState === 'error') {
                if (rematchState === 'declined') $p.append(msg(T_REMATCH_DECLINED));
                if (rematchState === 'error') $p.append(msg(T_REMATCH_ERROR));
                if (rematchState === 'error') {
                    $p.append(btn('btn-danger', 'fad fa-redo', T_REMATCH, function () { rematchCall('request'); }));
                }
                $p.append($leave);
            } else {
                $p.append(btn('btn-danger pulse-red', 'fad fa-redo', T_REMATCH, function () { rematchCall('request'); }))
                  .append($leave);
            }
            // The panel is above the board: make sure a player scrolled down at the board still sees it.
            if (wasHidden || rematchState === 'incoming') {
                try { $p[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) { $p[0].scrollIntoView(); }
            }
        }

        // Shows the rematch panel when a rematch is actually possible; otherwise calls `unavailable`.
        function showRematchPanel(unavailable) {
            const offer = function () { rematchPanelVisible = true; renderRematch(); };
            const cannot = function () { if (typeof unavailable === 'function') unavailable(); };

            if (REMATCH_MODE === 'guest') {
                // Guests are identified by the match session id kept in this browser tab.
                if (getGuestSessionId()) offer(); else cannot();
                return;
            }
            // Registered players: re-check the opponent now. This page may have been rendered
            // before the opponent joined, and a random-room opponent has no account at all.
            $.ajax({
                type: 'POST',
                url: '{{ url('/api/getRoomIds') }}',
                data: { 'ma-phong': '{{ $roomCode }}' },
                dataType: 'json'
            }).done(function (ids) {
                if (ids && ids.host_id && ids.guest_id) offer(); else cannot();
            }).fail(offer); // the rematch endpoint validates again
        }

        function rematchRedirect(newCode) {
            if (!newCode || rematchState === 'redirecting') return;
            rematchState = 'redirecting';
            renderRematch();
            // Colours swap: the old black player is red/host in the new room.
            const newSide = MY_SIDE === 'red' ? 'black' : 'red';
            if (REMATCH_MODE === 'user') {
                window.location.href = newSide === 'red' ? xqHostRoomUrl(newCode) : xqGuestRoomUrl(newCode);
            } else {
                window.location.href = newSide === 'red' ? xqRedRoomUrl(newCode) : xqBlackRoomUrl(newCode);
            }
        }

        function rematchCall(action) {
            $('#rematch-panel button').prop('disabled', true);
            $.ajax({
                type: 'POST',
                url: REMATCH_URL,
                data: Object.assign({ action: action }, rematchIdentity()),
                dataType: 'json'
            }).done(function (data) {
                if (data.status === 'accepted') {
                    rematchRedirect(data.new_code);
                } else if (data.status === 'requested') {
                    rematchState = 'waiting';
                    renderRematch();
                } else if (data.status === 'declined') {
                    rematchState = (rematchState === 'incoming') ? 'closed' : 'idle';
                    renderRematch();
                }
            }).fail(function () {
                rematchState = 'error';
                renderRematch();
            });
        }

        if (typeof Echo !== 'undefined') {
            Echo.channel('rematch.{{ $roomCode }}').listen('.rematch.updated', function (e) {
                const mine = e.by === MY_SIDE;
                if (e.status === 'requested') {
                    rematchState = mine ? 'waiting' : 'incoming';
                } else if (e.status === 'accepted') {
                    rematchRedirect(e.newCode);
                    return;
                } else if (e.status === 'declined') {
                    rematchState = mine ? 'closed' : 'declined';
                } else if (e.status === 'cancelled') {
                    rematchState = 'idle';
                }
                renderRematch();
            });
        }
        @endif

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

        // ---- PvP UX helpers: last-move highlight, turn banner, tab title ----
        const baseTitle = document.title;
        const T_YOUR_TURN = '{{ __("Đến lượt bạn") }}';
        const T_WAITING = '{{ __("Chờ đối thủ đi") }}';
        const T_TURN_OF = '{{ __("Lượt của") }}';
        const T_OVER = '{{ __("Hết trận") }}';

        function markLastMove() {
            $('#ban-co .square-2b8ce').removeClass('last-move-from last-move-to');
            const h = game.history({ verbose: true });
            if (!h.length) return;
            const m = h[h.length - 1];
            $('#ban-co .square-' + m.from).addClass('last-move-from');
            $('#ban-co .square-' + m.to).addClass('last-move-to');
        }

        function updateTurnBanner() {
            const $b = $('#pvp-turn-banner');
            if (!$b.length) return;
            const turnName = game.turn() === 'r' ? '{{ __("Đỏ") }}' : '{{ __("Đen") }}';
            let state = 'idle', text = '';
            if (game.game_over() || game.fen().includes('resign')) {
                state = 'over'; text = T_OVER;
            @if ($role === 'watch')
            } else {
                state = game.turn() === 'r' ? 'red' : 'black';
                text = T_TURN_OF + ' ' + turnName;
            }
            @else
            } else if ((board.orientation() === 'red' && game.turn() === 'r') || (board.orientation() === 'black' && game.turn() === 'b')) {
                state = 'mine'; text = T_YOUR_TURN + ' (' + turnName + ')';
            } else {
                state = 'theirs'; text = T_WAITING + '…';
            }
            @endif
            $b.attr('data-state', state);
            $('#pvp-turn-text').text(text);
            document.title = (state === 'mine' ? '● ' + T_YOUR_TURN + ' · ' : '') + baseTitle;
        }

        (function watchConnection() {
            try {
                const conn = Echo.connector.pusher.connection;
                const set = function (st) { $('#pvp-conn').attr('data-state', st).attr('title', st === 'ok' ? '{{ __("Đã kết nối") }}' : '{{ __("Mất kết nối, đang thử lại...") }}'); };
                conn.bind('connected', function () { set('ok'); });
                conn.bind('connecting', function () { set('bad'); });
                conn.bind('unavailable', function () { set('bad'); });
                conn.bind('disconnected', function () { set('bad'); });
            } catch (e) { /* Echo not ready: indicator stays neutral */ }
        })();

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

            markLastMove();
            updateTurnBanner();
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
            onSnapEnd: onSnapEnd,
            showNotation: true
        };

        config.clickMovesEnabled = config.draggable;
        config.draggable = false;
        board = Xiangqiboard('ban-co', config);
        initBoardClickInteraction(board, game, config);
        $(window).resize(board.resize);
        kypho = KyPho.initRoom({
            board: board,
            startFen: '{{ config('xiangqi.initial_fen') }}',
            isLive: function() { return !game.game_over(); }
        });

        if (kypho) { kypho.syncMoves('{{ url('/api') }}/readMoves/{{ $roomCode }}'); }
        updateStatus();

        @if ($role !== 'watch')
        $('#resign').on('click', function(e) {
            e.preventDefault();
            if ($(this).hasClass('disabled') || game.game_over()) return;
            bootbox.confirm({
                title: '<i class="fad fa-flag"></i> {{ __("Bạn chắc chắn muốn bỏ cuộc?") }}',
                message: '{{ __("Bạn sẽ bị xử thua ván này.") }}',
                centerVertical: true,
                closeButton: false,
                size: 'small',
                locale: '{{ __("vi") }}',
                buttons: {
                    confirm: { label: '<i class="fad fa-flag"></i> {{ __("Bỏ cuộc") }}', className: 'btn-danger' },
                    cancel: { label: '{{ __("Chơi tiếp") }}', className: 'btn-dark text-light' }
                },
                callback: function(ok) {
                    if (!ok) return;
                    game.load(game.fen() + ' resign-' + board.orientation());
                    updateFenCode('{{ $roomCode }}');
                    updateStatus();
                }
            });
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
