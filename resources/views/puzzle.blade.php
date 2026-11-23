@extends('layouts.gamelayout')

@section('og_image', asset('img/puzzle.jpg'))

@section('aboveBoard')
    <h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="{{ __("Bàn cờ thế") }}">{{ __("Bạn đang xếp") }}<span id="puzzle-title"></span></h5>
    <p class="w-100 text-center m-0">
        <span class="rounded p-0 d-block" id="game-status"></span>
    </p>
    <p class="w-100 text-center mt-0 mb-1">
        <a data-step="1" data-intro="Ấn vào đây để tải bàn cờ về khi đã xếp xong" id="capture" class="btn btn-danger btn-lg text-light" href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="{{ __("Lưu thành ảnh nào") }}"><i class="fad fa-download"></i> {{ __("Tải bàn cờ thế") }}</a>
        @include('common.volumeBtn')
        @include('common.tourBtn')
    </p>
@endsection

@section('rightSide')
    <p class="w-100 text-center mx-0 mb-0 mt-2">
        <span class="rounded d-none" id="game-over"><i class="fad fa-flag-checkered"></i> {{ __("HẾT TRẬN") }}</span>
    </p>
    <div class="sharethis-inline-reaction-buttons"></div>
    <div class="dropup mx-auto text-center my-1">
        <button class="btn btn-danger btn-lg dropdown-toggle" type="button" id="hostDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span data-toggle="tooltip" data-placement="top" title="{{ __("Đấu với bạn bè trong phòng") }}"><i class="fad fa-gamepad-alt"></i> {{ __("Chơi online") }}</span>
        </button>
        <a id="switch" class="btn btn-dark btn-lg mx-auto"><i class="fad fa-sync"></i> {{ __("Đổi bên") }}</a>
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
        <a data-step="2" data-intro="Ấn vào đây để vào trang giải {{ __("thế cờ") }}" id="solve-puzzle" class="w-25 btn btn-dark btn-lg" href="{{ url('/giai-co-the') }}"><i class="fad fa-abacus"></i> {{ __("Giải cờ thế") }}</a>
        <a data-step="3" data-intro="Ấn vào đây để lưu và chia sẻ {{ __("thế cờ") }}" id="name-puzzle" class="w-25 btn btn-lg btn-dark" href="javascript:void(0);"><i class="fad fa-save"></i> {{ __("Lưu & chia sẻ") }}</a>
    </p>
    <p class="w-100 text-center mt-0 mb-1">
        <a data-step="4" data-intro="Ấn vào đây để lưu trạng thái bàn cờ" id="new-board" class="w-25 btn btn-dark btn-lg"><i class="fad fa-chess-board"></i> {{ __("Lưu bàn cờ") }}</a>
        <a data-step="5" data-intro="Ấn vào đây để xếp lại nước trước đó" id="undo" class="w-25 btn btn-dark btn-lg"><i class="fad fa-undo"></i> {{ __("Xếp lại") }}</a>
    </p>
    @if ($board != '')
        <p class="w-100 text-center mt-0 mb-1">
            <i class="fad fa-external-link-alt"></i> {{ __("Mời bạn bè chơi bằng cách gửi liên kết bên dưới") }}.
        </p>
        <div id="copy-url" class="input-group my-1 w-50 mx-auto" data-toggle="tooltip" data-placement="bottom" data-original-title="Ấn để sao chép">
            <div class="input-group-prepend">
                <span class="input-group-text" id="url-addon"><i class="fal fa-copy"></i></span>
            </div>
            <input data-step="6" data-intro="Ấn vào đây để mời bạn bè cùng chơi" type="text" class="form-control" id="url" value="{{ url('/') }}{{ __('/co-the/') }}{{ $board }}">
        </div>
        <script>
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
    <style>
        /* Click to move: pieces are clickable, never draggable */
        #ban-co .square-2b8ce { pointer-events: auto !important; cursor: pointer; }
        #ban-co img[data-piece] {
            cursor: pointer;
            -webkit-user-drag: none;
            user-select: none;
            transition: transform .28s cubic-bezier(.175, .885, .32, 1.275), filter .28s ease;
        }
        #ban-co .square-2b8ce img { position: relative; z-index: 2; }

        /* ---------- Selected piece (board square or tray): honey halo, lifted ---------- */
        #ban-co .square-2b8ce.pz-selected {
            background:
                radial-gradient(circle at 50% 50%, rgba(255, 200, 97, .6), rgba(255, 170, 70, .18) 60%, transparent 76%) !important;
            z-index: 4;
        }
        #ban-co .square-2b8ce.pz-selected img,
        #ban-co img[data-piece].pz-selected {
            transform: translateY(-6px) scale(1.12);
            filter:
                drop-shadow(0 8px 6px rgba(0, 0, 0, .55))
                drop-shadow(0 0 10px rgba(255, 200, 97, .95));
        }

        /* ---------- Drop preview (empty square): warm honey pearl + tea-ripple ---------- */
        #ban-co.pz-armed .square-2b8ce:hover::after {
            content: '';
            position: absolute; left: 50%; top: 50%;
            width: 38%; height: 38%; border-radius: 50%;
            transform: translate(-50%, -50%);
            background:
                radial-gradient(circle at 33% 28%, #ffffff 0%, #fff4d9 18%, #ffd47a 52%, #f0a23a 82%, #d9822a 100%);
            box-shadow:
                0 0 0 2px #fff,
                0 0 0 4px rgba(90, 38, 18, .4),
                0 6px 12px rgba(20, 8, 4, .55),
                0 0 26px 7px rgba(255, 184, 82, .95),
                inset 0 -3px 5px rgba(160, 80, 20, .35);
            pointer-events: none; z-index: 3;
            animation: pzOrbIn .38s cubic-bezier(.34, 1.56, .64, 1) both,
                       pzOrbBreath 2.2s ease-in-out .4s infinite;
        }
        #ban-co.pz-armed .square-2b8ce:hover::before {
            content: '';
            position: absolute; left: 50%; top: 50%;
            width: 38%; height: 38%; border-radius: 50%;
            transform: translate(-50%, -50%);
            border: 2px solid rgba(255, 206, 120, .85);
            pointer-events: none; z-index: 2;
            animation: pzRipple 1.2s ease-out infinite;
        }
        #ban-co.pz-armed .square-2b8ce:not(:has(img)):hover {
            background: radial-gradient(circle, rgba(255, 190, 90, .5), rgba(255, 170, 70, .16) 55%, transparent 78%) !important;
        }

        /* ---------- Drop preview (occupied square, will be replaced): ember ring ---------- */
        #ban-co.pz-armed .square-2b8ce:hover:has(img)::before {
            width: 96%; height: 96%;
            background: radial-gradient(circle, rgba(255, 120, 70, .55), rgba(238, 100, 60, .25) 55%, transparent 74%);
            border: 0;
            animation: pzEmber 1.4s ease-in-out infinite;
            z-index: 1;
        }
        #ban-co.pz-armed .square-2b8ce:hover:has(img)::after {
            width: 94%; height: 94%;
            background: conic-gradient(from 0deg, #ff8a4c, #ffe3a0, #ee5a3c, #ffc861, #ff8a4c);
                -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 5px), #000 calc(100% - 4px));
                mask: radial-gradient(farthest-side, transparent calc(100% - 5px), #000 calc(100% - 4px));
            box-shadow:
                0 0 0 1px rgba(90, 38, 18, .35),
                0 0 16px rgba(255, 120, 70, .9),
                inset 0 0 14px rgba(255, 120, 70, .55);
            animation: pzRingIn .34s cubic-bezier(.34, 1.56, .64, 1) both,
                       pzRingSpin 4s linear .35s infinite;
        }
        #ban-co.pz-armed .square-2b8ce:hover:has(img) img {
            filter: drop-shadow(0 0 9px rgba(255, 138, 76, .95));
        }

        @keyframes pzOrbIn {
            from { transform: translate(-50%, -50%) scale(0); opacity: 0; }
            to   { transform: translate(-50%, -50%) scale(1); opacity: 1; }
        }
        @keyframes pzOrbBreath {
            0%, 100% { transform: translate(-50%, -50%) scale(1); }
            50%      { transform: translate(-50%, -50%) scale(1.12); }
        }
        @keyframes pzRipple {
            0%   { transform: translate(-50%, -50%) scale(1);   opacity: .85; }
            100% { transform: translate(-50%, -50%) scale(2.4); opacity: 0; }
        }
        @keyframes pzEmber {
            0%, 100% { opacity: .7; }
            50%      { opacity: 1; }
        }
        @keyframes pzRingIn {
            from { transform: translate(-50%, -50%) scale(.4); opacity: 0; }
            to   { transform: translate(-50%, -50%) scale(1); opacity: 1; }
        }
        @keyframes pzRingSpin {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }

        @media (prefers-reduced-motion: reduce) {
            #ban-co.pz-armed .square-2b8ce:hover::before,
            #ban-co.pz-armed .square-2b8ce:hover::after,
            #ban-co.pz-armed .square-2b8ce:hover:has(img)::before,
            #ban-co.pz-armed .square-2b8ce:hover:has(img)::after { animation: none !important; }
            #ban-co img[data-piece] { transition: none; }
        }
    </style>
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
            XiangqiSound.playMove();
            console.log(history);
        }

        function undo () {
            if (history.length > 1) {
                history.pop();
                board.position(history[history.length - 1]);
            }
            console.log(history);
        }

        const board = Xiangqiboard('ban-co', {
            // sparePieces requires draggable, so keep it on but cancel every drag.
            // Pieces are placed / moved / removed by clicking (see "Click to move" below).
            draggable: true,
            onDragStart: function () { return false; },
            sparePieces: true,
            @if ($board != '')
                position: '{{ $board }}',
            @endif
            showNotation: true,
            onSnapEnd: onSnapEnd
        });

        let game = new Xiangqi(board.fen() + ' r - - 0 1');

        /* ==================== Click to move (replaces drag & drop) ====================
           - Click a piece in the tray, then a square          -> place it
           - Click a piece on the board, then another square   -> move it (replaces any piece there)
           - Click a piece on the board, then the tray         -> remove it (also Delete / Backspace)
           - Click the selected piece again, press Esc, or click outside the board -> cancel
        */
        (function () {
            const root = document.getElementById('ban-co');
            let sel = null; // { from: 'spare' | squareName, piece: 'rK' ... }

            // Pieces are re-rendered on resize / flip, so selection is re-applied on every DOM change.
            function paint() {
                if (sel && sel.from !== 'spare' && board.position()[sel.from] !== sel.piece) sel = null;
                root.classList.toggle('pz-armed', !!sel);
                root.querySelectorAll('.pz-selected').forEach(el => el.classList.remove('pz-selected'));
                if (!sel) return;
                if (sel.from === 'spare') {
                    root.querySelectorAll('img[data-piece="' + sel.piece + '"]').forEach(img => {
                        if (!img.closest('[data-square]')) img.classList.add('pz-selected');
                    });
                } else {
                    root.querySelector('[data-square="' + sel.from + '"]')?.classList.add('pz-selected');
                }
            }

            function commit(position) {
                sel = null;
                board.position(position, false);
                onSnapEnd();
                paint();
            }

            function removeSelected() {
                if (!sel || sel.from === 'spare') return false;
                const position = board.position();
                delete position[sel.from];
                commit(position);
                return true;
            }

            root.addEventListener('click', function (event) {
                const cell = event.target.closest('[data-square]');
                const img = event.target.closest('img[data-piece]');
                const spareImg = img && !img.closest('[data-square]') ? img : null;
                const inTray = !cell && (spareImg || event.target.closest('[class*="spare-pieces"]'));

                if (spareImg) {
                    const piece = spareImg.dataset.piece;
                    if (removeSelected()) return;
                    sel = (sel && sel.from === 'spare' && sel.piece === piece) ? null : { from: 'spare', piece: piece };
                    paint();
                    return;
                }

                if (inTray) {
                    if (!removeSelected()) { sel = null; paint(); }
                    return;
                }

                if (!cell) { sel = null; paint(); return; }

                const target = cell.dataset.square;
                const position = board.position();
                const occupant = position[target];

                if (!sel) {
                    if (occupant) sel = { from: target, piece: occupant };
                    paint();
                    return;
                }
                if (sel.from === target) { sel = null; paint(); return; }
                // Clicking another piece of the same colour just switches the selection.
                if (sel.from !== 'spare' && occupant && occupant.charAt(0) === sel.piece.charAt(0)) {
                    sel = { from: target, piece: occupant };
                    paint();
                    return;
                }
                position[target] = sel.piece;
                if (sel.from !== 'spare') delete position[sel.from];
                commit(position);
            });

            // Click anywhere else on the page cancels the selection.
            document.addEventListener('click', function (event) {
                if (sel && !root.contains(event.target)) { sel = null; paint(); }
            });

            document.addEventListener('keydown', function (event) {
                if (!sel || event.target.closest('input, textarea, select, [contenteditable]')) return;
                if (event.key === 'Escape') { sel = null; paint(); }
                else if (event.key === 'Delete' || event.key === 'Backspace') {
                    if (removeSelected()) event.preventDefault();
                }
            });

            // Block the browser's native image drag ghost.
            root.addEventListener('dragstart', e => e.preventDefault());

            new MutationObserver(paint).observe(root, { childList: true, subtree: true });
            paint();
        })();

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
                if (game.in_checkmate()) {
                    XiangqiSound.playCheckmate();
                } else {
                    XiangqiSound.playStalemate();
                }
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
                    message: "{{ __("Bàn cờ thế không hợp lệ") }}",
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
                window.location.href = "{{ url(__('/co-the/')) }}" + "/" + board.fen();
            }
        });

        $('#undo').on('click', undo);

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

            const validation = game.validate_fen(board.fen() + ' r - - 0 1');
            if (!validation.valid) {
                errorBox.removeClass('d-none').text('{{ __("Bàn cờ thế không hợp lệ") }}');
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

            if (!game.validate_fen(board.fen() + ' r - - 0 1').valid) {
                bootbox.alert({
                    message: "{{ __("Bàn cờ thế không hợp lệ") }}",
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

        $("#capture").on('click', function() {
            if (!game.validate_fen(board.fen() + ' r - - 0 1').valid) {
                bootbox.alert({
                    message: "{{ __("Bàn cờ thế không hợp lệ") }}",
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
                    message: "{{ __("Bàn cờ thế không hợp lệ") }}",
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

        $('#switch').on('click', board.flip);

        $('#compete').on('click auxclick', function(e){
            e.preventDefault();
            if (!game.validate_fen(board.fen() + ' r - - 0 1').valid) {
                bootbox.alert({
                    message: "{{ __("Bàn cờ thế không hợp lệ") }}",
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
                window.location.href = $(this).attr('href') + '/' + board.fen();
            }
        });
    </script>
    @include('layouts.partials.players')
    @include('layouts.partials.userPuzzles')
    @include('layouts.partials.boards')
    @include('layouts.partials.playedBoards')
@endsection
