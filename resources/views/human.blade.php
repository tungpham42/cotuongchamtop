@extends('layouts.gamelayout')

@section('aboveBoard')
    <h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="{{ __("Tăng kỹ năng chơi cờ") }}">{{ __("Bạn đang chơi một mình") }}<span id="puzzle-title"></span></h5>
@endsection

@section('rightSide')
    <p class="w-100 text-center m-0">
        <span class="rounded p-0 d-block" id="game-status"></span>
    </p>
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
    @include('layout.partials.kypho')
    <script>
        let board = null;
        let $board = $('#ban-co');
        let game = new Xiangqi();
        let kypho = null;
        let squareToHighlight = null;
        let colorToHighlight = null;
        let squareClass = 'square-2b8ce';
        let showHighlight = true;

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
            if (game.game_over()) return false;

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
            nuocCo.play();
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
                hetTran.play();
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
        $('#resign').on('click', function() {
            game.load(game.fen() + ' resign');
            updateStatus();
        });
        $('#undo').on('click', function(){
            game.undo();
            board.position(game.fen());
            nuocCo.play();
            updateStatus();
            if (kypho) {
                kypho.setMoves(game.history());
            }
        });
        $('#switch').on('click', board.flip);
        $('#reset').on('click', function() {
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
    @include('layout.partials.players')
    @include('layout.partials.userPuzzles')
    @include('layout.partials.boards')
    @include('layout.partials.playedBoards')
@endsection
