@extends('layout.gamelayout')

@section('aboveBoard')
    @php
        $locale = app()->getLocale() ?: 'vi';
        $levelUrls = [
            'vi' => ['ban-co-moi-choi', 'ban-co-de', 'ban-co-binh-thuong', 'ban-co-kho', 'ban-co-kho-nhat'],
            'en' => ['newbie-board', 'easy-board', 'normal-board', 'hard-board', 'hardest-board'],
            'ja' => ['shoshinsha-bodo', 'kantan-bodo', 'tsujo-bodo', 'hado-bodo', 'mottomo-muzukashi-bodo'],
            'ko' => ['nyubi-bodeu', 'iji-bodeu', 'nomol-bodeu', 'hadeu-bodeu', 'gajang-dandanhan-bodeu'],
            'zh' => ['xinshouban', 'jianyiban', 'putongban', 'yingban', 'zuiyingban'],
        ];
        $urls = $levelUrls[$locale] ?? $levelUrls['vi'];
    @endphp
    <h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="{{ __("Cấp độ") }}: {{ $levelTxt }}">{{ __("Bạn đang giải") }}<span id="puzzle-title"></span> {{ __("với máy") }}</h5>
@endsection

@section('aboveContent')
@endsection

@section('rightSide')
    <p class="w-100 text-center m-0">
        <span class="rounded p-0 d-block" id="game-status"></span>
    </p>
    <p class="w-100 text-center mx-0 mb-0 mt-2">
        <span class="rounded d-none" id="game-over"><i class="fad fa-flag-checkered"></i> {{ __("HẾT TRẬN") }}</span>
    </p>
    <div class="sharethis-inline-reaction-buttons"></div>
    <h5 class="text-center my-1">{{ __("Cấp độ") }}: {{ $levelTxt }}</h5>
    <div class="level dropup mx-auto text-center my-1">
        <button class="btn btn-lg btn-dark dropdown-toggle" type="button" id="levelDropdown" data-toggle="dropdown" aria-haspopup="true" data-step="1" data-intro="Hãy chọn cấp độ phù hợp với bạn nhé" aria-expanded="false">
            <i class="fad fa-robot"></i> {{ __("Chọn cấp độ bàn cờ") }}
        </button>
        <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="levelDropdown">
            <a class="add-fen dropdown-item{{ $levelTxt === 'Mới chơi' ? ' active disabled' : '' }}" href="{{ url('/' . $urls[0]) }}" style="cursor: pointer !important;">{{ __("Mới chơi") }}</a>
            <a class="add-fen dropdown-item{{ $levelTxt === 'Dễ' ? ' active disabled' : '' }}" href="{{ url('/' . $urls[1]) }}" style="cursor: pointer !important;">{{ __("Dễ") }}</a>
            <a class="add-fen dropdown-item{{ $levelTxt === 'Bình thường' ? ' active disabled' : '' }}" href="{{ url('/' . $urls[2]) }}" style="cursor: pointer !important;">{{ __("Bình thường") }}</a>
            <a class="add-fen dropdown-item{{ $levelTxt === 'Khó' ? ' active disabled' : '' }}" href="{{ url('/' . $urls[3]) }}" style="cursor: pointer !important;">{{ __("Khó") }}</a>
            <a class="add-fen dropdown-item{{ $levelTxt === 'Khó nhất' ? ' active disabled' : '' }}" href="{{ url('/' . $urls[4]) }}" style="cursor: pointer !important;">{{ __("Khó nhất") }}</a>
        </div>
    </div>
    <div class="dropup mx-auto text-center my-1">
        <button class="btn btn-danger btn-lg dropdown-toggle" type="button" id="hostDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span data-toggle="tooltip" data-placement="top" title="{{ __("Đấu với bạn bè trong phòng") }}"><i class="fad fa-gamepad-alt"></i> {{ __("Chơi online") }}</span>
        </button>
        <a id="switch" class="btn btn-dark btn-lg mx-auto"><i class="fad fa-sync"></i> {{ __("Đổi bên") }}</a>
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
    <p class="w-100 text-center mt-0 mb-1">
        <a data-step="2" data-intro="Ấn vào đây nếu bạn không biết đi nước nào" id="resign" class="w-25 btn btn-dark btn-lg"><i class="fad fa-flag"></i> {{ __("Bỏ cuộc") }}</a>
        <a data-step="3" data-intro="Ấn vào đây để quay lại nước trước đó" id="undo" class="w-25 btn btn-dark btn-lg"><i class="fad fa-undo-alt"></i> {{ __("Đi lại") }}</a>
    </p>
    <p class="w-100 text-center mt-0 mb-1">
        <a data-step="4" data-intro="Ấn vào đây để tự giải bàn cờ" id="board" class="add-fen w-25 btn btn-dark btn-lg" href="{{ url('/ban-co') }}"><i class="fad fa-user"></i> Tự giải</a>
        <a data-step="5" data-intro="Ấn vào đây để {{ __("chơi") }} lại từ đầu" id="reset" class="w-25 btn btn-dark btn-lg"><i class="fad fa-redo-alt"></i> {{ __("Chơi lại") }}</a>
    </p>
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
                            console.error('Invalid move from engine:', data.best_move);
                            makeRandomMove();
                        }
                    } else {
                        console.error('Invalid move from engine:', data.best_move);
                        makeRandomMove();
                    }
                } else {
                    console.error('Engine error:', data.error);
                    makeRandomMove();
                }
            } catch (error) {
                console.error('Request failed:', error);
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
            const timeouts = {
                1: 500,
                2: 1000,
                3: 1500,
                4: 2000,
                5: 2500
            };
            return timeouts[level] || 3000;
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
            var status = ''

            var moveColor = '{{ __("Đỏ") }}'
            if (game.turn() === 'b') {
                moveColor = '{{ __("Đen") }}'
            }

            if (game.in_checkmate()) {
                status = moveColor + ' {{ __("bị chiếu bí") }}'
            } else if (game.in_draw()) {
                status = '{{ __("Hòa") }}'
            } else {
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
                $('#header-status').html(': '+status+' - {{ __("Hết trận") }}');
                $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> {{ __("Hết trận") }}');
            }
            if (game.fen().includes('resign')) {
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
            position: '{{ $fen }}',
            onDragStart: onDragStart,
            onDrop: onDrop,
            onMouseoutSquare: onMouseoutSquare,
            onMouseoverSquare: onMouseoverSquare,
            onSnapEnd: onSnapEnd,
            showNotation: true
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
            game.load(game.fen() + ' resign');
            updateStatus();
        });
        $('#undo').on('click', function(){
            if (game.turn() == 'r') {
                game.undo();
                game.undo();
                board.position(game.fen());
                nuocCo.play();
                updateStatus();
                if (kypho) {
                    kypho.setMoves(game.history());
                }
            }
        });
        $('#switch').on('click', board.flip);
        $('#reset').on('click', function() {
            board.position('{{ $fen }}');
            game.load('{{ $fen }}');
            $('#game-status').removeClass('black').addClass('red');
            updateStatus();
            $('#game-over').removeClass('d-inline-block').addClass('d-none');
            $('#resign').removeClass('disabled').attr('aria-disabled', false);
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
                e.preventDefault();
                window.location.href = $(this).attr('href') + '/' + game.fen();
            }).on('mouseenter mouseleave', function(){
                if ($(this).has('i').length) {
                    $(this).find('i').remove();
                } else {
                    $(this).prepend(activePointer);
                }
            });
        });
    </script>
    @include('layout.partials.players')
    @include('layout.partials.userPuzzles')
    @include('layout.partials.boards')
    @include('layout.partials.playedBoards')
@endsection
