@extends('layouts.gamelayout')

@section('aboveBoard')
    <h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="{{ __("Bàn cờ thế") }}">{{ __("Bạn đang xếp") }}<span id="puzzle-title"> để thi {{ __("đấu") }}</span></h5>
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
        <a data-step="1" data-intro="Ấn vào đây để vào trang giải {{ __("thế cờ") }}" id="solve-puzzle" class="btn btn-danger btn-lg" href="{{ url('/giai-co-the') }}"><i class="fad fa-abacus"></i> {{ __("Giải cờ thế") }}</a>
        <a data-step="2" data-intro="Ấn vào đây để lưu và chia sẻ {{ __("thế cờ") }}" id="name-puzzle" class="btn btn-lg btn-dark" href="javascript:void(0);"><i class="fad fa-save"></i> Lưu &amp; chia sẻ</a>
    </p>
    @if ($board != '')
        <p class="w-100 text-center mt-0 mb-1">
            <i class="fad fa-external-link-alt"></i> {{ __("Mời bạn bè chơi bằng cách gửi liên kết bên dưới") }}.
        </p>
        <div id="copy-url" class="input-group my-1 w-50 mx-auto" data-toggle="tooltip" data-placement="bottom" data-original-title="Ấn để sao chép">
            <div class="input-group-prepend">
                <span class="input-group-text" id="url-addon"><i class="fal fa-copy"></i></span>
            </div>
            <input data-step="3" data-intro="{{ __("Ấn vào đây để mời bạn bè cùng chơi") }}" type="text" class="form-control" id="url" value="{{ url('/') }}/thi-dau/{{ $board }}">
        </div>
        <script>
            const savePuzzleFormTemplate = `
                <form id="save-puzzle-form">
                    <div class="form-group">
                        <label for="save-puzzle-name" class="font-weight-bold">Tên {{ __("thế cờ") }}</label>
                        <input type="text" class="form-control" id="save-puzzle-name" maxlength="255" placeholder="Ví dụ: Cửu tử hồn - thế khó nhất 2025" required>
                    </div>
                    <div class="form-group">
                        <label for="save-puzzle-description" class="font-weight-bold">Mô tả / hướng dẫn (không bắt buộc)</label>
                        <textarea class="form-control" id="save-puzzle-description" rows="3" maxlength="1000" placeholder="Viết ghi chú ngắn hoặc hướng giải..."></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold d-block">Chế độ hiển thị</label>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="save-puzzle-public" name="save-puzzle-privacy" class="custom-control-input" value="public" checked>
                            <label class="custom-control-label" for="save-puzzle-public">Công khai (mọi người đều xem được)</label>
                        </div>
                        <div class="custom-control custom-radio mt-1">
                            <input type="radio" id="save-puzzle-private" name="save-puzzle-privacy" class="custom-control-input" value="private">
                            <label class="custom-control-label" for="save-puzzle-private">{{ __("Riêng tư") }} (chỉ ai có link mới xem được)</label>
                        </div>
                    </div>
                    <div class="alert alert-danger d-none mt-3 mb-0" id="save-puzzle-error"></div>
                </form>
            `;

            function submitPuzzle(dialog) {
                const errorBox = dialog.find('#save-puzzle-error');
                const nameInput = dialog.find('#save-puzzle-name');
                const descriptionInput = dialog.find('#save-puzzle-description');
                const privacyValue = dialog.find('input[name="save-puzzle-privacy"]:checked').val();
                const confirmButton = dialog.find('.btn-danger');

                const puzzleName = nameInput.val().trim();
                if (!puzzleName.length) {
                    errorBox.removeClass('d-none').text('Vui lòng đặt tên cho Thế cờ!');
                    nameInput.focus();
                    return false;
                }

                const checkGame = new Xiangqi();
                const validation = checkGame.validate_fen(board.fen() + ' r - - 0 1');
                if (!validation.valid) {
                    errorBox.removeClass('d-none').text('{{ __("Bàn cờ thế") }} không hợp lệ, vui lòng xếp lại.');
                    return false;
                }

                confirmButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang lưu...');
                errorBox.addClass('d-none').text('');

                $.ajax({
                    url: '{{ url('/api/puzzles') }}',
                    method: 'POST',
                    data: {
                        name: puzzleName,
                        description: descriptionInput.val().trim(),
                        fen: board.fen(),
                        is_public: privacyValue === 'public' ? 1 : 0,
                        rating: 0
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).done(function(response) {
                    window.location.href = response.url;
                }).fail(function(xhr) {
                    confirmButton.prop('disabled', false).html('<i class="fas fa-share-alt"></i> {{ __("Lưu & chia sẻ") }}');
                    let message = 'Không thể lưu {{ __("thế cờ") }}, vui lòng thử lại.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            message = Object.values(xhr.responseJSON.errors).flat().join(' ');
                        }
                    }
                    errorBox.removeClass('d-none').text(message);
                });

                return false;
            }

            $('#name-puzzle').on('click auxclick', function(e) {
                e.preventDefault();

                const checkGame = new Xiangqi();
                if (!checkGame.validate_fen(board.fen() + ' r - - 0 1').valid) {
                    bootbox.alert({
                        message: "{{ __("Bàn cờ thế") }} không hợp lệ",
                        locale: '{{ __("vi") }}',
                        centerVertical: true,
                        closeButton: false,
                        size: 'small',
                        buttons: {
                            ok: {
                                className: 'btn-danger',
                                label: '{{ __("Xếp lại") }}'
                            }
                        }
                    });
                    return;
                }

                const dialog = bootbox.dialog({
                    title: "{{ __("Lưu & chia sẻ") }} {{ __("thế cờ") }}",
                    message: savePuzzleFormTemplate,
                    locale: '{{ __("vi") }}',
                    centerVertical: true,
                    closeButton: false,
                    buttons: {
                        cancel: {
                            label: 'Hủy',
                            className: 'btn-dark text-light'
                        },
                        confirm: {
                            label: '<i class="fas fa-share-alt"></i> {{ __("Lưu & chia sẻ") }}',
                            className: 'btn-danger',
                            callback: function() {
                                return submitPuzzle(dialog);
                            }
                        }
                    }
                });

                dialog.init(function() {
                    dialog.find('#save-puzzle-name').trigger('focus');
                });
            });

            $('#copy-url').on('click', function() {
                copyToClipboard('#url');
                selectText('#url');
                $(this).tooltip('update');
            });
        </script>
    @endif
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.min.js" integrity="sha512-OqcrADJLG261FZjar4Z6c4CfLqd861A3yPNMb+vRQ2JwzFT49WT4lozrh3bcKxHxtDTgNiqgYbEUStzvZQRfgQ==" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.svg.min.js" integrity="sha512-cX+p7MRIKvgo59Ap3QDj2ymdc7XFFCEJ71X+nWPT+3UxNylm/ztqgDJTbko2atIo4jiozj0dUpYb+xfv1bCl8g==" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.2/dist/FileSaver.min.js" integrity="sha256-u/J1Urdrk3nCYFefpoeTMgI5viU1ujCDu2fXXoSJjhg=" crossorigin="anonymous"></script>
    @include('common.volume')
    <script>
        @if ($board != '')
            let history = ['{{ $board }}'];
        @else
            let history = ['9/9/9/9/9/9/9/9/9/9'];
        @endif

        function onSnapEnd () {
            if (board.fen() != history[history.length - 1]){
                history.push(board.fen());
            }
            nuocCo.play();
            console.log(history);
        }

        function undo () {
            if (history.length > 1) {
                history.pop();
                board.position(history[history.length - 1]);
            }
            console.log(history);
        }

        let game = new Xiangqi();
        const board = Xiangqiboard('ban-co', {
            draggable: true,
            dropOffBoard: 'trash',
            sparePieces: true,
            @if ($board != '')
                position: '{{ $board }}',
            @endif
            showNotation: true,
            onSnapEnd: onSnapEnd
        });

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
                $('#resign, #switch').addClass('disabled').attr('aria-disabled', true);
                config.draggable = false;
            }
        }

        const ratio = $('#ban-co').height() / $('#ban-co').width();
        function adjustBoard() {
            const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
            width = ($(window).height() - 195) / ratio;
            width = Math.min(width, $('header > .container').width());
            height = width * ratio;
            $('#ban-co').css({'width': width});
            board.resize();
        }

        $(window).resize(board.resize);

        $('#new-board').on('click auxclick', function(e){
            if (!game.validate_fen(board.fen() + ' r - - 0 1').valid) {
                bootbox.alert({
                    message: "{{ __("Bàn cờ thế") }} không hợp lệ",
                    locale: '{{ __("vi") }}',
                    centerVertical: true,
                    closeButton: false,
                    size: 'small',
                    buttons: {
                        ok: {
                            className: 'btn-danger',
                            label: '{{ __("Xếp lại") }}'
                        }
                    }
                });
            } else {
                window.location.href = "{{ url('/co-the/') }}/" + board.fen();
            }
        });

        $('#undo').on('click', undo);

        $("#capture").on('click', function() {
            if (!game.validate_fen(board.fen() + ' r - - 0 1').valid) {
                bootbox.alert({
                    message: "{{ __("Bàn cờ thế") }} không hợp lệ",
                    locale: '{{ __("vi") }}',
                    centerVertical: true,
                    closeButton: false,
                    size: 'small',
                    buttons: {
                        ok: {
                            className: 'btn-danger',
                            label: '{{ __("Xếp lại") }}'
                        }
                    }
                });
            } else {
                html2canvas(document.getElementsByClassName("board-1ef78")[0], {
                    windowWidth: document.getElementsByClassName("board-1ef78")[0].scrollWidth,
                    windowHeight: document.getElementsByClassName("board-1ef78")[0].scrollHeight,
                    allowTaint: true,
                    useCORS: true,
                    onrendered: function(canvas) {
                        var context = canvas.getContext('2d');

                        context.font = '25px cursive';
                        context.globalCompositeOperation = 'multiply';
                        context.fillStyle = '#444422';
                        context.textAlign = 'center';
                        context.textBaseline = 'middle';
                        context.fillText('COTUONG.TOP', canvas.width / 2, canvas.height / 2);

                        canvas.toBlob(function(blob) {
                            saveAs(blob, "ban-co-{{ date('Y-m-d h:i:s A') }}.png");
                        });
                    }
                });
            }
        });

        $('#solve-puzzle').on('click auxclick', function(e){
            e.preventDefault();
            if (!game.validate_fen(board.fen() + ' r - - 0 1').valid) {
                bootbox.alert({
                    message: "{{ __("Bàn cờ thế") }} không hợp lệ",
                    locale: '{{ __("vi") }}',
                    centerVertical: true,
                    closeButton: false,
                    size: 'small',
                    buttons: {
                        ok: {
                            className: 'btn-danger',
                            label: '{{ __("Xếp lại") }}'
                        }
                    }
                });
            } else {
                window.location.href = $(this).attr('href') + '/' + board.fen() + ' r - - 0 1';
            }
        });
    </script>
    @include('layouts.partials.players')
    @include('layouts.partials.userPuzzles')
    @include('layouts.partials.boards')
    @include('layouts.partials.playedBoards')
@endsection
