@extends('layouts.gamelayout')

@section('og_image', asset('img/human.jpg'))

@section('aboveBoard')
    <h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="{{ __("Tăng kỹ năng chơi cờ") }}">{{ __("Bạn đang chơi một mình") }}<span id="puzzle-title"></span></h5>
    <p class="w-100 text-center m-0">
        <span class="rounded p-0 d-block" id="game-status"></span>
    </p>
@endsection

@section('rightSide')
    @include('layouts.partials.hint')
    <p class="w-100 text-center mx-0 mb-0 mt-2">
        <span class="rounded d-none" id="game-over"><i class="fad fa-flag-checkered"></i> {{ __("HẾT TRẬN") }}</span>
    </p>
    <div class="sharethis-inline-reaction-buttons"></div>
    <div class="dropup mx-auto text-center my-1">
        <button class="btn btn-danger btn-lg dropdown-toggle" type="button" id="hostDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span data-toggle="tooltip" data-placement="top" title="{{ __("Đấu với bạn bè trong phòng") }}"><i class="fad fa-gamepad-alt"></i> {{ __("Chơi online") }}</span>
        </button>
        <a id="switch" class="btn btn-dark btn-lg mx-auto"><i class="fad fa-sync"></i> {{ __("Đổi bên") }}</a>
        <a id="toggle-highlight" class="btn btn-dark btn-lg mx-auto"><i class="fad fa-lightbulb-on"></i> {{ __("Bật/tắt đánh dấu") }}</a>
        @include('common.volumeBtn')
        @include('common.tourBtn')
        <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="hostDropdown" id="tao-phong" data-phong="{{ md5(time()) }}" data-url="{{ localized_url('room.host', ['code' => md5(time())]) }}">
            @if (!auth()->check())
                <a data-toggle="tooltip" data-placement="bottom" title="{{ __("Đăng nhập để tham gia thi đấu") }}" class="dropdown-item thi-dau" style="cursor: pointer !important;" href="{{ localized_url('login') }}"><i class="fas fa-sign-in text-dark"></i> {{ __("Đăng nhập") }}</a>
            @else
                <a data-toggle="tooltip" data-placement="bottom" title="{{ __("Thi đấu tính điểm và xếp hạng") }}" id="create-room" class="dropdown-item thi-dau" style="cursor: pointer !important;" href="javascript:createRoom();"><i class="fas fa-trophy-alt text-dark"></i> {{ __("Thi đấu") }}</a>
            @endif
            <a data-toggle="tooltip" data-placement="bottom" title="Chơi cần mật khẩu" id="tao-phong-private" class="dropdown-item" style="cursor: pointer !important;"><i class="fas fa-lock text-dark"></i> {{ __("Riêng tư") }}</a>
            @if ($randomRoom != null)
                <a data-toggle="tooltip" data-placement="bottom" title="Chơi trong phòng Công khai ngẫu nhiên" id="random-room" class="dropdown-item" style="cursor: pointer !important;" href="{{ localized_url('room.random', ['code' => $randomRoom['code'] ]) }}"><i class="fas fa-random text-dark"></i> {{ __("Ngẫu nhiên") }}</a>
            @endif
            <a data-toggle="tooltip" data-placement="bottom" title="Tìm phòng trống" id="room-list" class="dropdown-item rooms-list" style="cursor: pointer !important;" href="{{ localized_url('room.list') }}"><i class="fas fa-list-alt text-dark"></i> {{ __("Sảnh chờ") }}</a>
        </div>
    </div>
@endsection

@section('belowContent')
    <p class="w-100 text-center mt-0 mb-1">
        <a data-step="1" data-intro="Ấn vào đây nếu bạn không biết đi nước nào" id="resign" class="w-25 btn btn-dark btn-lg"><i class="fad fa-flag"></i> {{ __("Bỏ cuộc") }}</a>
        <a data-step="2" data-intro="Ấn vào đây để quay lại nước trước đó" id="undo" class="w-25 btn btn-dark btn-lg"><i class="fad fa-undo-alt"></i> {{ __("Đi lại") }}</a>
    </p>
    <p class="w-100 text-center mt-0 mb-1">
        <a data-step="3" data-intro="Ấn vào đây để đánh {{ __("với máy") }}" class="w-25 btn btn-dark btn-lg showPromotion" href="{{ localized_url('ai.home') }}"><i class="fad fa-desktop"></i> {{ __("Với máy") }}</a>
        <a data-step="4" data-intro="Ấn vào đây để {{ __("chơi") }} lại từ đầu" id="reset" class="w-25 btn btn-dark btn-lg"><i class="fad fa-redo-alt"></i> {{ __("Chơi lại") }}</a>
    </p>
    @include('layouts.partials.kypho')
    @include('layouts.partials.hintArrow')
    <script>
        const HINT_TIMEOUT_MS = 700;
        const HINT_LEVEL = 3;
        let isHintPending = false;

        function postJson (url, payload, extraHeaders = {}) {
            return fetch(url, {
                method: 'POST',
                headers: Object.assign({
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }, extraHeaders),
                body: JSON.stringify(payload)
            });
        }

        function toggleDisabled ($el, disabled) {
            $el.toggleClass('disabled', disabled).attr('aria-disabled', disabled);
        }

        // A hint is available for whichever side is to move (Red or Black),
        // as long as the game is still running and no request is in flight.
        function isHintAvailable () {
            return !isHintPending && !game.game_over() && !game.fen().includes('resign');
        }

        // Asks the engine for a suggestion for the side to move.
        async function fetchHint () {
            if (!isHintAvailable()) return;

            const requestFen = game.fen();
            isHintPending = true;
            clearHint();
            toggleDisabled($('#hint-btn'), true);
            $('#game-status').append(' <i class="fas fa-spinner fa-spin"></i>');

            try {
                const response = await postJson('/api/xiangqi/hint', {
                    fen: requestFen,
                    timeout: HINT_TIMEOUT_MS,
                    level: HINT_LEVEL
                }, { 'Accept': 'application/json' });

                const data = await response.json();
                if (!response.ok || !data.success || !data.hint_move) {
                    if (data.error === 'free_limit_reached' && typeof bootbox !== 'undefined') {
                        showVipUpsellDialog(data.message);
                        return;
                    }
                    throw new Error(data.message || data.error || 'Hint unavailable');
                }

                // Ignore a response that belongs to an older position (move/undo/reset in the meantime).
                if (game.fen() !== requestFen) return;
                showHint(data.hint_move);
            } catch (error) {
                clearHint();
                console.error('Hint error:', error);
                if (typeof bootbox !== 'undefined') {
                    bootbox.alert({
                        message: error.message || '{{ __("Không thể lấy gợi ý nước đi lúc này.") }}',
                        size: 'small'
                    });
                }
            } finally {
                isHintPending = false;
                updateStatus();
            }
        }

        let board = null;
        let $board = $('#ban-co');
        let game = new Xiangqi();
        let kypho = null;
        let squareToHighlight = null;
        let colorToHighlight = null;
        let squareClass = 'square-2b8ce';
        let showHighlight = true;
        let hasMoved = false;

        // Picks the right synthesized sound for the move that was just made.
        // Checkmate/stalemate are left to updateStatus() so they aren't
        // doubled up with a plain "move" or "check" sound.
        function playMoveResultSound () {
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

        function removeHighlights (color) {
            $board.find('.' + squareClass).removeClass('highlight-' + color);
        }

        function removeGreySquares () {
            $('#ban-co .square-2b8ce').removeClass('highlight');
        }

        function greySquare (square) {
            let $square = $('#ban-co .square-' + square);
            $square.addClass('highlight');
        }

        function onDragStart (source, piece) {
            if (game.game_over() || isHintPending) return false;

            if ((game.turn() === 'r' && piece.search(/^b/) !== -1) ||
                (game.turn() === 'b' && piece.search(/^r/) !== -1)) {
                return false;
            }
        }

        function onDrop (source, target) {
            removeGreySquares();

            let move = game.move({
                from: source,
                to: target
            });
            if (move === null) return 'snapback';
            clearHint();
            if (kypho) {
                kypho.recordMove(move);
            }

            if (move.color === 'r') {
                removeHighlights('red');
                if (showHighlight) {
                    $board.find('.square-' + source).addClass('highlight-red');
                    $board.find('.square-' + target).addClass('highlight-red');
                }
                squareToHighlight = target;
                colorToHighlight = 'red';
            } else {
                removeHighlights('black');
                if (showHighlight) {
                    $board.find('.square-' + source).addClass('highlight-black');
                    $board.find('.square-' + target).addClass('highlight-black');
                }
                squareToHighlight = target;
                colorToHighlight = 'black';
            }
            updateStatus();
        }

        function onMouseoverSquare (square, piece) {
            if (!showHighlight) return;

            let moves = game.moves({
                square: square,
                verbose: true
            });

            if (moves.length === 0) return;

            greySquare(square);

            for (let i = 0; i < moves.length; i++) {
                greySquare(moves[i].to);
            }
        }

        function onMouseoutSquare (square, piece) {
            removeGreySquares();
        }

        function onSnapEnd () {
            board.position(game.fen());
            $('#FEN').val(game.fen());
            playMoveResultSound();
            updateStatus();
        }

        function onMoveEnd () {
            if (showHighlight && squareToHighlight) {
                $board.find('.square-' + squareToHighlight).addClass('highlight-' + colorToHighlight);
            }
        }

        function updateStatus () {
            var status = ''

            var moveColor = '{{ __("Đỏ") }}'
            if (game.turn() === 'b') {
                moveColor = '{{ __("Đen") }}'
            }

            if (game.in_checkmate()) {
                status = moveColor + ' {{ __("bị chiếu bí") }}'
            }
            else if (game.in_draw()) {
                status = '{{ __("Hòa") }}'
            }
            else {
                status = moveColor
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
                if (game.in_checkmate()) {
                    XiangqiSound.playCheckmate();
                } else {
                    XiangqiSound.playStalemate();
                }
                $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> {{ __("Hết trận") }}');
                $('#header-status').html(': '+status+' - {{ __("Hết trận") }}');
            }
            if (game.fen().includes('resign') && !resignAlertShown) {
                $('#header-status').html(': '+status+' - {{ __("Đã bỏ cuộc") }}');
                bootbox.alert({
                    message: '<i class="fad fa-flag-checkered"></i> {{ __("Đã bỏ cuộc") }}',
                    locale: '{{ __("vi") }}',
                    centerVertical: true,
                    closeButton: false,
                    size: 'small',
                    buttons: {
                        ok: {
                            className: 'btn-danger'
                        }
                    }
                });
                $('#game-over').html('<i class="fad fa-flag-checkered"></i> {{ __("Đã bỏ cuộc") }}');
                $('#resign').addClass('disabled').attr('aria-disabled', true);
                config.draggable = false;
            }
            toggleDisabled($('#hint-btn'), !isHintAvailable());
            if (kypho) {
                kypho.updateControls();
            }
        }
        let config = {
            draggable: true,
            position: 'start',
            onDragStart: onDragStart,
            onDrop: onDrop,
            onMouseoutSquare: onMouseoutSquare,
            onMouseoverSquare: onMouseoverSquare,
            onSnapEnd: onSnapEnd,
            onMoveEnd: onMoveEnd,
            showNotation: true
        };
        board = Xiangqiboard('ban-co', config);
        $(window).resize(board.resize);
        updateStatus();
        kypho = KyPho.initLocal({
            board: board,
            startFen: game.fen(),
            isLive: function() { return !game.game_over(); }
        });
        $('#toggle-highlight').on('click', function() {
            showHighlight = !showHighlight;
            if (!showHighlight) {
                removeHighlights('red');
                removeHighlights('black');
                removeGreySquares();
                $(this).removeClass('btn-dark').removeClass('btn-danger').addClass('btn-danger');
            } else {
                $(this).removeClass('btn-danger').removeClass('btn-dark').addClass('btn-dark');
                if (squareToHighlight) {
                    $board.find('.square-' + squareToHighlight).addClass('highlight-' + colorToHighlight);
                }
            }
        });
        $('#hint-btn').on('click', function(e) {
            e.preventDefault();
            fetchHint();
        });
        $('#resign').on('click', function() {
            clearHint();
            game.load(game.fen() + ' resign');
            updateStatus();
        });
        $('#undo').on('click', function(){
            clearHint();
            game.undo();
            board.position(game.fen());
            XiangqiSound.playMove();
            updateStatus();
            if (kypho) {
                kypho.setMoves(game.history());
            }
        });
        $('#switch').on('click', function() {
            board.flip();
            if (hintMove) setTimeout(drawHintArrow, 150); // re-anchor the arrow to the flipped squares
        });
        $('#reset').on('click', function() {
            clearHint();
            hasMoved = false;
            board.position('rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR');
            game.load('rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1');
            $('#game-status').removeClass('black').addClass('red');
            updateStatus();
            $('#game-over').removeClass('d-inline-block').addClass('d-none');
            $('#resign').removeClass('disabled').attr('aria-disabled', false);
            config.draggable = true;
            if (kypho) {
                kypho.setMoves([]);
            }
        });
    </script>
    @include('layouts.partials.players')
    @include('layouts.partials.userPuzzles')
    @include('layouts.partials.boards')
    @include('layouts.partials.playedBoards')
@endsection
