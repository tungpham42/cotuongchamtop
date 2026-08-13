@extends('layouts.gamelayout')

@inject('puzzleService', 'App\Services\PuzzleService')
@php
    $puzzleName = $puzzleService->getNameByFen($fen);
@endphp

{{-- Set the dynamic Open Graph Image and Alt text using a placeholder service --}}
@if($puzzleName && !containsCJK($puzzleName))
    @section('og_image', 'https://placehold.co/1080x1080/DA251D/FFFF00/jpeg?font=roboto&text=' . urlencode(__("Thế cờ") . "\n" . $puzzleName))
    @section('og_image_alt', $puzzleName)
    @section('og_image_width', '1080')
    @section('og_image_height', '1080')
    @section('og_image_type', 'image/jpeg')
@else
    @section('og_image', url('/') . '/img/co-the.jpg')
    @section('og_image_alt', 'Cờ thế')
    @section('og_image_width', '1080')
    @section('og_image_height', '1080')
    @section('og_image_type', 'image/jpeg')
@endif

@section('aboveBoard')
    <h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="{{ __("Bạn đang giải cờ thế với máy") }}">
        {{ __("Bạn đang giải") }} @if($puzzleName) "{{ $puzzleName }}" @else cờ thế @endif
    </h5>
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
        @if ( isset($name) && $name != '' )
            <a id="switch" class="btn btn-dark btn-lg mx-auto"><i class="fad fa-chess-board"></i> {{ __("Đổi bên") }}</a>
        @endif
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
        <a data-step="3" data-intro="Ấn vào đây để xếp cờ thế" class="w-25 btn btn-dark btn-lg showPromotion" href="{{ url('/co-the') }}"><i class="fad fa-puzzle-piece"></i> {{ __("Xếp cờ") }}</a>
        <a data-step="4" data-intro="Ấn vào đây để {{ __("chơi") }} lại từ đầu" id="reset" class="w-25 btn btn-dark btn-lg"><i class="fad fa-redo-alt"></i> {{ __("Chơi lại") }}</a>
    </p>
    <p class="w-100 text-center mt-0 mb-1">
        <i class="fad fa-external-link-alt"></i> {{ __("Mời bạn bè chơi bằng cách gửi liên kết bên dưới") }}.
    </p>
    <div id="copy-url" class="input-group my-1 w-50 mx-auto" data-toggle="tooltip" data-placement="bottom" data-original-title="Ấn để sao chép">
        <div class="input-group-prepend">
            <span class="input-group-text" id="url-addon"><i class="fal fa-copy"></i></span>
        </div>
        <input data-step="{{ __("6\" data-intro=\"Ấn vào đây để mời bạn bè cùng chơi") }}" type="text" class="form-control" id="url" value="{{ url()->current() }}">
    </div>
    <script>
        $('#copy-url').on('click', function() {
            copyToClipboard('#url');
            selectText('#url');
            $(this).tooltip('update');
        });
    </script>
    @include('layout.partials.kypho')
    <script>
        let board = null;
        let game = new Xiangqi();
        let isComputerThinking = false;
        let resignAlertShown = false;
        let kypho = null;

        function removeGreySquares () {
            $('#ban-co .square-2b8ce').removeClass('highlight');
        }

        function greySquare (square) {
            let $square = $('#ban-co .square-' + square);
            $square.addClass('highlight');
        }

        function onDragStart (source, piece, position, orientation) {
            if (game.in_checkmate() === true || game.in_draw() === true ||
                piece.search(/^b/) !== -1 || isComputerThinking) {
                return false;
            }
        }

        async function makeBestMove() {
            if (isComputerThinking || game.game_over()) return;

            isComputerThinking = true;
            $('#game-status').html('{{ __("Đang suy nghĩ") }}... <i class="fas fa-spinner fa-spin"></i>');

            try {
                const response = await fetch('/api/xiangqi/best-move', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        fen: game.fen(),
                        timeout: getTimeoutByLevel({{ $level }})
                    })
                });

                const data = await response.json();

                if (data.success && data.best_move) {
                    const move = convertEngineMoveToXiangqiJS(data.best_move);

                    if (move) {
                        const moveResult = game.move(move);
                        if (moveResult !== null) {
                            if (kypho) {
                                kypho.recordMove(moveResult);
                            }
                            board.position(game.fen());
                            nuocCo.play();
                            updateStatus();
                        } else {
                            makeRandomMove();
                        }
                    } else {
                        makeRandomMove();
                    }
                } else {
                    makeRandomMove();
                }
            } catch (error) {
                makeRandomMove();
            } finally {
                isComputerThinking = false;
            }
        }

        function convertEngineMoveToXiangqiJS(engineMove) {
            if (!engineMove || engineMove.length !== 4) return null;

            const from = engineMove.substring(0, 2);
            const to = engineMove.substring(2, 4);

            return {
                from: from,
                to: to
            };
        }

        function getTimeoutByLevel(level) {
            const parsedLevel = parseInt(level, 10);
            const timeouts = {
                1: 150,   // Newbie
                2: 250,   // Easy
                3: 400,   // Normal
                4: 600,   // Hard
                5: 900,   // Hardest
            };
            return timeouts[parsedLevel] ?? 400;
        }

        function makeRandomMove() {
            const moves = game.moves({verbose: true});
            if (moves.length > 0) {
                const randomMove = moves[Math.floor(Math.random() * moves.length)];
                const moveResult = game.move(randomMove);
                if (moveResult !== null) {
                    if (kypho) {
                        kypho.recordMove(moveResult);
                    }
                    board.position(game.fen());
                    nuocCo.play();
                    updateStatus();
                }
            }
        }

        function onDrop (source, target) {
            if (isComputerThinking) return 'snapback';

            let move = game.move({
                from: source,
                to: target,
                promotion: 'q'
            });

            if (move === null) return 'snapback';
            if (kypho) {
                kypho.recordMove(move);
            }

            updateStatus();

            if (!game.game_over() && game.turn() === 'b') {
                setTimeout(makeBestMove, 500);
            }
        }

        function onMouseoverSquare (square, piece) {
            if (isComputerThinking) return;

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
            nuocCo.play();
            updateStatus();
        }

        function updateStatus () {
            var status = '';
            var moveColor = '{{ __("Đỏ") }}';

            if (game.turn() === 'b') {
                moveColor = '{{ __("Đen") }}';
            }

            if (game.in_checkmate()) {
                status = moveColor + ' {{ __("bị chiếu bí") }}';
            }
            else if (game.in_draw()) {
                status = '{{ __("Hòa") }}';
            }
            else {
                status = moveColor;
                if (game.in_check()) {
                    status += ', ' + moveColor + ' {{ __("đang bị chiếu") }}';
                    if ((board.orientation() == 'red' && game.turn() === 'r') ||
                        (board.orientation() == 'black' && game.turn() === 'b')) {
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

            if (typeof $('#header-status') !== 'undefined' && $('#header-status').length) {
                $('#header-status').html(': '+status);
            }

            if (game.game_over()) {
                if (typeof hetTran !== 'undefined') {
                    hetTran.play();
                }
                if (typeof $('#header-status') !== 'undefined' && $('#header-status').length) {
                    $('#header-status').html(': '+status+' - {{ __("Hết trận") }}');
                }
                $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> {{ __("Hết trận") }}');
                isComputerThinking = false;
            }

            if (game.fen().includes('resign') && !resignAlertShown) {
                if (typeof $('#header-status') !== 'undefined' && $('#header-status').length) {
                    $('#header-status').html(': '+status+' - {{ __("Đã bỏ cuộc") }}');
                }

                if (typeof bootbox !== 'undefined') {
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
                }

                $('#game-over').html('<i class="fad fa-flag-checkered"></i> {{ __("Đã bỏ cuộc") }}');
                $('#resign, #switch').addClass('disabled').attr('aria-disabled', true);
                config.draggable = false;
                isComputerThinking = false;
                resignAlertShown = true;
            }
            if (kypho) {
                kypho.updateControls();
            }
        }

        let config = {
            draggable: true,
            position: '{{ $fen }}',
            onDragStart: onDragStart,
            onDrop: onDrop,
            onMouseoutSquare: onMouseoutSquare,
            onMouseoverSquare: onMouseoverSquare,
            onSnapEnd: onSnapEnd,
            showNotation: true,
        };
        board = Xiangqiboard('ban-co', config);
        $(window).resize(board.resize);
        game.load('{{ $fen }}');
        updateStatus();
        kypho = KyPho.initLocal({
            board: board,
            startFen: game.fen(),
            isLive: function() { return !game.game_over(); }
        });
        $(document).ready(function() {
            $('#FEN').val(game.fen());
            if (game.turn() === 'b') {
                makeBestMove();
            }
        });
        $('#resign').on('click', function() {
            if (isComputerThinking) return;
            game.load(game.fen() + ' resign');
            updateStatus();
        });

        $('#undo').on('click', function(){
            if (isComputerThinking) return;

            if (game.history().length >= 2) {
                game.undo();
                game.undo();
                board.position(game.fen());
                if (typeof nuocCo !== 'undefined') {
                    nuocCo.play();
                }
                updateStatus();
                if (kypho) {
                    kypho.setMoves(game.history());
                }
            }
        });

        $('#switch').on('click', function() {
            if (isComputerThinking) return;
            board.flip();
        });

        $('#reset').on('click', function() {
            isComputerThinking = false;
            resignAlertShown = false;
            board.position('rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR');
            game.load('rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1');
            $('#game-status').removeClass('black').addClass('red');
            updateStatus();
            $('#game-over').removeClass('d-inline-block').addClass('d-none');
            $('#resign, #switch').removeClass('disabled').attr('aria-disabled', false);
            config.draggable = true;
            if (kypho) {
                kypho.setMoves([]);
            }
        });

        $('#board').on('click auxclick', function(e){
            e.preventDefault();
            window.location.href = $(this).attr('href') + '/' + game.fen();
        });
        $('.level.dropup .dropdown-item').each(function(){
            const activePointer = '<i class="far fa-hand-point-right"></i>  ';
            if ($(this).hasClass('active')) {
                $(this).prepend(activePointer);
            }
            $(this).on('click auxclick', function(e){
                window.location.href = $(this).attr('href') + '/' + game.fen();
            }).on('mouseenter mouseleave', function(){
                if ($(this).has('i').length) {
                    $(this).find('i').remove();
                } else {
                    $(this).prepend(activePointer);
                }
            });
        });

        const style = document.createElement('style');
        style.textContent = `
            .fa-spinner {
                margin-left: 5px;
            }
            .disabled {
                opacity: 0.5;
                pointer-events: none;
            }
            .highlight {
                background-color: #ffeb3b !important;
                opacity: 0.6;
            }
        `;
        document.head.appendChild(style);

        @if(isset($computerStarts) && $computerStarts)
            $(document).ready(function() {
                setTimeout(makeBestMove, 1000);
            });
        @endif
    </script>
    @include('layout.partials.players')
    @include('layout.partials.userPuzzles')
    @include('layout.partials.boards')
    @include('layout.partials.playedBoards')
@endsection
