{{--
    Shared client-side game logic for every "play against the engine" page.

    Params:
      startFen           'start' for the standard opening, or a FEN string
      level              engine level (used for the timeout and sent to the API)
      sendLevelWithMove  (bool, default true) include `level` in the best-move request
      autoStart          null, or ['delay' => ms, 'ifBlackToMove' => bool]; lets the engine make the first move
--}}
@php
    $gameConfig = [
        'startFen'          => $startFen ?? 'start',
        'level'             => $level ?? 3,
        'sendLevelWithMove' => $sendLevelWithMove ?? true,
        'autoStart'         => $autoStart ?? null,
    ];
@endphp
<style>
    .fa-spinner { margin-left: 5px; }
    .disabled { opacity: 0.5; pointer-events: none; }
    .highlight { background-color: #ffeb3b !important; opacity: 0.6; }
    #ban-co .hint-from {
        background: radial-gradient(circle, rgba(52, 211, 153, 0.65) 0%, rgba(16, 185, 129, 0.4) 100%) !important;
        box-shadow: inset 0 0 0 3px #059669, 0 4px 10px rgba(16, 185, 129, 0.35) !important;
        border-radius: 8px;
    }
    #ban-co .hint-to {
        background: radial-gradient(circle, rgba(251, 191, 36, 0.75) 0%, rgba(245, 158, 11, 0.45) 100%) !important;
        box-shadow: inset 0 0 0 3px #d97706, 0 4px 12px rgba(245, 158, 11, 0.4) !important;
        border-radius: 8px;
    }
</style>
<script>
    const GAME_CONFIG = @json($gameConfig);
    const DEFAULT_START_POSITION = 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR';
    const USES_DEFAULT_START = GAME_CONFIG.startFen === 'start';
    const START_BOARD_POSITION = USES_DEFAULT_START ? DEFAULT_START_POSITION : GAME_CONFIG.startFen;
    const START_GAME_FEN = USES_DEFAULT_START ? DEFAULT_START_POSITION + ' r - - 0 1' : GAME_CONFIG.startFen;

    const ENGINE_TIMEOUTS_MS = { 1: 150, 2: 250, 3: 400, 4: 600, 5: 900, 8: 1500 };
    const DEFAULT_ENGINE_TIMEOUT_MS = 400;
    const HINT_TIMEOUT_MS = 700;

    let board = null;
    let game = new Xiangqi();
    let isComputerThinking = false;
    let resignAlertShown = false;
    let kypho = null;
    let isHintPending = false;
    let hintMove = null;
    let hasMoved = false;

    // ---------------------------------------------------------------- helpers

    // True while the player is not allowed to touch the board / controls.
    function isBusy() {
        return isComputerThinking || isHintPending;
    }

    // Records an engine move in the move list, redraws the board and plays the right sound.
    function applyMove(moveResult) {
        if (kypho) kypho.recordMove(moveResult);
        board.position(game.fen());
        playMoveResultSound();
        updateStatus();
    }

    function postJson(url, payload, extraHeaders = {}) {
        return fetch(url, {
            method: 'POST',
            headers: Object.assign({
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }, extraHeaders),
            body: JSON.stringify(payload)
        });
    }

    function toggleDisabled($el, disabled) {
        $el.toggleClass('disabled', disabled).attr('aria-disabled', disabled);
    }

    // ------------------------------------------------------------------ sound

    // Picks the right synthesized sound for the move that was just made.
    // Checkmate/stalemate are left to updateStatus() so they aren't
    // doubled up with a plain "move" or "check" sound.
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

    // ------------------------------------------------------------------- hint

    function clearHint() {
        hintMove = null;
        $('#ban-co .square-2b8ce').removeClass('hint-from hint-to');
        $('#hint-arrow').remove();
    }

    // Draws an animated "sticker style" hint arrow from the "from" square to the
    // "to" square: a curved, gradient arrow with a white outline and glow that
    // draws itself in, with light dots flowing along it, a start marker, and a
    // pulsing target ring. Positions are read from the DOM, so it stays correct
    // when the board is flipped or resized.
    function drawHintArrow() {
        $('#hint-arrow').remove();
        if (!hintMove) return;

        const boardEl = document.getElementById('ban-co');
        if (!boardEl) return;
        const fromEl = boardEl.querySelector('.square-' + hintMove.from);
        const toEl = boardEl.querySelector('.square-' + hintMove.to);
        if (!fromEl || !toEl) return;

        if (getComputedStyle(boardEl).position === 'static') boardEl.style.position = 'relative';

        const b = boardEl.getBoundingClientRect();
        const f = fromEl.getBoundingClientRect();
        const t = toEl.getBoundingClientRect();
        const size = Math.min(f.width, f.height);

        const x1 = f.left + f.width / 2 - b.left;
        const y1 = f.top + f.height / 2 - b.top;
        const x2 = t.left + t.width / 2 - b.left;
        const y2 = t.top + t.height / 2 - b.top;

        const dx = x2 - x1, dy = y2 - y1;
        const len = Math.hypot(dx, dy);
        if (len < 1) return;
        const ux = dx / len, uy = dy / len;
        const px = -uy, py = ux; // perpendicular

        const shaftW = size * 0.2;
        const outline = Math.max(2, size * 0.06);
        const headLen = size * 0.46;
        const headW = size * 0.62;

        // Gentle bend so the arrow feels hand-drawn and friendly, and so it
        // doesn't hide the pieces it passes over as much as a ruler-straight line.
        const bend = Math.min(len * 0.14, size * 0.55);
        const cx = (x1 + x2) / 2 + px * bend;
        const cy = (y1 + y2) / 2 + py * bend;

        // Start just off the piece centre, end just short of the target centre.
        const sx = x1 + ux * size * 0.28, sy = y1 + uy * size * 0.28;
        const tipX = x2 - ux * size * 0.12, tipY = y2 - uy * size * 0.12;

        // Arrowhead direction follows the end of the curve (control point -> tip).
        let hx = tipX - cx, hy = tipY - cy;
        const hl = Math.hypot(hx, hy) || 1;
        hx /= hl; hy /= hl;
        const baseX = tipX - hx * headLen, baseY = tipY - hy * headLen;
        if ((baseX - sx) * ux + (baseY - sy) * uy <= 0) return; // too short to draw

        const hpx = -hy, hpy = hx;
        const head = [
            [tipX, tipY],
            [baseX + hpx * headW / 2, baseY + hpy * headW / 2],
            [baseX - hpx * headW / 2, baseY - hpy * headW / 2]
        ].map(p => p[0].toFixed(1) + ',' + p[1].toFixed(1)).join(' ');

        const pathD = 'M' + sx.toFixed(1) + ' ' + sy.toFixed(1) +
                    ' Q' + cx.toFixed(1) + ' ' + cy.toFixed(1) +
                    ' ' + baseX.toFixed(1) + ' ' + baseY.toFixed(1);

        const svgNS = 'http://www.w3.org/2000/svg';
        const el = (name, attrs, parent) => {
            const n = document.createElementNS(svgNS, name);
            Object.entries(attrs || {}).forEach(([k, v]) => n.setAttribute(k, v));
            if (parent) parent.appendChild(n);
            return n;
        };

        const svg = el('svg', {
            id: 'hint-arrow',
            width: b.width,
            height: b.height,
            viewBox: '0 0 ' + b.width + ' ' + b.height
        });
        svg.style.cssText = 'position:absolute;left:0;top:0;pointer-events:none;z-index:50;overflow:visible;' +
            'filter:drop-shadow(0 3px 6px rgba(0,0,0,.4)) drop-shadow(0 0 10px rgba(251,191,36,.55));';

        // Animations live inside the SVG so the function stays self-contained.
        const style = el('style', {}, svg);
        style.textContent =
            '@keyframes ha-draw{from{stroke-dashoffset:1}to{stroke-dashoffset:0}}' +
            '@keyframes ha-pop{0%{transform:scale(0);opacity:0}70%{transform:scale(1.25);opacity:1}100%{transform:scale(1);opacity:1}}' +
            '@keyframes ha-ring{0%{transform:scale(.55);opacity:.95}100%{transform:scale(1.5);opacity:0}}' +
            '@keyframes ha-bob{0%,100%{transform:translate(0,0)}50%{transform:translate(' + (ux * size * 0.07).toFixed(1) + 'px,' + (uy * size * 0.07).toFixed(1) + 'px)}}' +
            '#hint-arrow .ha-shaft{stroke-dasharray:1;stroke-dashoffset:1;animation:ha-draw .45s cubic-bezier(.3,.7,.3,1) forwards}' +
            '#hint-arrow .ha-pop{transform-box:fill-box;transform-origin:center;animation:ha-pop .35s .38s cubic-bezier(.2,1.6,.4,1) both}' +
            '#hint-arrow .ha-bob{animation:ha-bob 1.1s 0.8s ease-in-out infinite}' +
            '#hint-arrow .ha-ring{transform-box:fill-box;transform-origin:center;animation:ha-ring 1.4s .5s ease-out infinite}' +
            '#hint-arrow .ha-ring2{animation-delay:1.2s}' +
            '@media (prefers-reduced-motion:reduce){#hint-arrow *{animation:none!important}#hint-arrow .ha-shaft{stroke-dashoffset:0!important}}';

        // Gradient runs along the arrow: fresh green at the piece -> warm amber at the target.
        const defs = el('defs', {}, svg);
        const grad = el('linearGradient', {
            id: 'ha-grad', gradientUnits: 'userSpaceOnUse',
            x1: sx, y1: sy, x2: tipX, y2: tipY
        }, defs);
        el('stop', { offset: '0%', 'stop-color': '#34d399' }, grad);
        el('stop', { offset: '55%', 'stop-color': '#fbbf24' }, grad);
        el('stop', { offset: '100%', 'stop-color': '#f97316' }, grad);

        // Pulsing target rings: "land here!"
        const ringR = size * 0.4;
        [0, 1].forEach(i => {
            el('circle', {
                class: 'ha-ring' + (i ? ' ha-ring2' : ''),
                cx: x2, cy: y2, r: ringR,
                fill: 'rgba(249,115,22,.18)',
                stroke: '#f97316',
                'stroke-width': Math.max(2, size * 0.05)
            }, svg);
        });

        // Shaft: white outline underneath, gradient on top (sticker look).
        el('path', {
            d: pathD, pathLength: 1, fill: 'none', stroke: '#fff',
            'stroke-width': shaftW + outline * 2, 'stroke-linecap': 'round', class: 'ha-shaft'
        }, svg);
        el('path', {
            d: pathD, pathLength: 1, fill: 'none', stroke: 'url(#ha-grad)',
            'stroke-width': shaftW, 'stroke-linecap': 'round', class: 'ha-shaft'
        }, svg);

        // Little lights flowing along the arrow to show direction.
        [0, 0.5, 1].forEach(i => {
            const dot = el('circle', { r: shaftW * 0.24, fill: '#fff', opacity: 0 }, svg);
            const motion = el('animateMotion', {
                dur: '1.5s', begin: (0.6 + i * 0.5) + 's', repeatCount: 'indefinite',
                path: pathD, keyPoints: '0;1', keyTimes: '0;1', calcMode: 'linear'
            }, dot);
            el('animate', {
                attributeName: 'opacity', values: '0;.95;.95;0', keyTimes: '0;.15;.85;1',
                dur: '1.5s', begin: (0.6 + i * 0.5) + 's', repeatCount: 'indefinite'
            }, dot);
        });

        // Start marker: a friendly "this piece" dot.
        const startG = el('g', { class: 'ha-pop' }, svg);
        el('circle', { cx: x1, cy: y1, r: size * 0.17, fill: '#34d399', stroke: '#fff', 'stroke-width': outline }, startG);
        el('circle', { cx: x1, cy: y1, r: size * 0.06, fill: '#fff' }, startG);

        // Arrowhead: pops in after the shaft draws, then gently bobs toward the target.
        const bob = el('g', { class: 'ha-bob' }, svg);
        const headG = el('g', { class: 'ha-pop' }, bob);
        el('polygon', {
            points: head, fill: '#f97316', stroke: '#fff',
            'stroke-width': outline * 2, 'stroke-linejoin': 'round', 'paint-order': 'stroke'
        }, headG);

        boardEl.appendChild(svg);
    }

    $(window).on('resize.hintArrow', function () {
        if (hintMove) setTimeout(drawHintArrow, 120);
    });

    function showHint(move) {
        clearHint();
        if (!move || move.length !== 4) return;
        hintMove = { from: move.substring(0, 2), to: move.substring(2, 4) };
        // $('#ban-co .square-' + hintMove.from).addClass('hint-from');
        // $('#ban-co .square-' + hintMove.to).addClass('hint-to');
        drawHintArrow();
    }

    function showVipUpsellDialog(message) {
        bootbox.dialog({
            title: '<i class="fas fa-crown text-warning"></i> {{ __("Nâng cấp Kỳ Thủ VIP") }}',
            message: '<div class="text-center py-2"><p>' + (message || '{{ __("Bạn đã dùng hết lượt gợi ý miễn phí hôm nay.") }}') + '</p><a href="{{ localized_url("app.vip") ?? "/vip" }}" class="btn btn-warning font-weight-bold text-dark mt-2 shadow" style="border-radius: 20px;"><i class="fas fa-bolt"></i> {{ __("Nâng cấp VIP (Chỉ từ 19k)") }}</a></div>',
            size: 'small',
            backdrop: true,
            centerVertical: true,
        });
    }

    async function fetchHint() {
        if (isHintPending || isComputerThinking || game.game_over() || game.turn() !== 'r') return;

        isHintPending = true;
        clearHint();
        toggleDisabled($('#hint-btn'), true);
        $('#game-status').append(' <i class="fas fa-spinner fa-spin"></i>');

        try {
            const response = await postJson('/api/xiangqi/hint', {
                fen: game.fen(),
                timeout: HINT_TIMEOUT_MS,
                level: GAME_CONFIG.level
            }, { 'Accept': 'application/json' });

            const data = await response.json();
            if (!response.ok || !data.success || !data.hint_move) {
                if (data.error === 'free_limit_reached' && typeof bootbox !== 'undefined') {
                    showVipUpsellDialog(data.message);
                    return;
                }
                throw new Error(data.message || data.error || 'Hint unavailable');
            }

            // Do not apply a response belonging to an older board position.
            if (game.turn() !== 'r' || game.fen() !== data.fen) return;
            showHint(data.hint_move);
        } catch (error) {
            clearHint();
            if (typeof bootbox !== 'undefined') {
                bootbox.alert({
                    message: '{{ __('Không thể lấy gợi ý nước đi lúc này.') }}',
                    size: 'small'
                });
            }
        } finally {
            isHintPending = false;
            updateStatus();
        }
    }

    // ----------------------------------------------------------- board events

    function removeGreySquares () {
        $('#ban-co .square-2b8ce').removeClass('highlight');
    }

    function greySquare (square) {
        $('#ban-co .square-' + square).addClass('highlight');
    }

    function onDragStart (source, piece, position, orientation) {
        if (game.in_checkmate() === true || game.in_draw() === true || piece.search(/^b/) !== -1 || isBusy()) {
            return false;
        }
    }

    function onDrop (source, target) {
        if (isBusy()) return 'snapback';
        clearHint();

        let move = game.move({
            from: source,
            to: target,
            promotion: 'q'
        });

        if (move === null) return 'snapback';
        if (kypho) kypho.recordMove(move);

        updateStatus();

        if (!game.game_over() && game.turn() === 'b') {
            setTimeout(makeBestMove, 500);
        }
    }

    function onMouseoverSquare (square, piece) {
        if (isComputerThinking) return;
        let moves = game.moves({ square: square, verbose: true });
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
        playMoveResultSound();
        updateStatus();
    }

    // ----------------------------------------------------------------- engine

    function getTimeoutByLevel(level) {
        return ENGINE_TIMEOUTS_MS[parseInt(level, 10)] ?? DEFAULT_ENGINE_TIMEOUT_MS;
    }

    function convertEngineMoveToXiangqiJS(engineMove) {
        if (!engineMove || engineMove.length !== 4) return null;
        return {
            from: engineMove.substring(0, 2),
            to: engineMove.substring(2, 4)
        };
    }

    function makeRandomMove() {
        clearHint();
        const moves = game.moves({verbose: true});
        if (moves.length === 0) return;

        const moveResult = game.move(moves[Math.floor(Math.random() * moves.length)]);
        if (moveResult !== null) applyMove(moveResult);
    }

    async function makeBestMove() {
        if (isComputerThinking || game.game_over()) return;

        isComputerThinking = true;
        $('#game-status').html('{{ __("Đang suy nghĩ") }}... <i class="fas fa-spinner fa-spin"></i>');

        try {
            const payload = {
                fen: game.fen(),
                timeout: getTimeoutByLevel(GAME_CONFIG.level)
            };
            if (GAME_CONFIG.sendLevelWithMove) payload.level = GAME_CONFIG.level;

            const response = await postJson('/api/xiangqi/best-move', payload);
            const data = await response.json();

            const move = data.success && data.best_move ? convertEngineMoveToXiangqiJS(data.best_move) : null;
            const moveResult = move ? game.move(move) : null;

            if (moveResult !== null) {
                clearHint();
                applyMove(moveResult);
            } else {
                makeRandomMove();
            }
        } catch (error) {
            makeRandomMove();
        } finally {
            isComputerThinking = false;
        }
    }

    // ----------------------------------------------------------------- status

    function updateStatus () {
        var status = '';
        var moveColor = game.turn() === 'b' ? '{{ __("Đen") }}' : '{{ __("Đỏ") }}';

        if (game.in_checkmate()) {
            status = moveColor + ' {{ __("bị chiếu bí") }}';
        } else if (game.in_draw()) {
            status = '{{ __("Hòa") }}';
        } else {
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

        $('#game-status').toggleClass('red', game.turn() === 'r').toggleClass('black', game.turn() === 'b');

        // Hint only ever suggests the player's (Red's) own next move —
        // disable it whenever it isn't Red's turn (i.e. while the AI
        // is about to move or thinking), so the button never invites
        // a click that fetchHint() would just refuse anyway.
        toggleDisabled($('#hint-btn'), !(game.turn() === 'r' && !game.game_over()));

        $('#game-status').html(status);

        const $headerStatus = $('#header-status');
        const setHeaderStatus = (suffix = '') => {
            if ($headerStatus.length) $headerStatus.html(': ' + status + suffix);
        };
        setHeaderStatus();

        if (game.game_over()) {
            if (typeof XiangqiSound !== 'undefined') {
                if (game.in_checkmate()) {
                    XiangqiSound.playCheckmate();
                } else {
                    XiangqiSound.playStalemate();
                }
            }
            setHeaderStatus(' - {{ __("Hết trận") }}');
            $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> {{ __("Hết trận") }}');
            isComputerThinking = false;
        }

        if (game.fen().includes('resign') && !resignAlertShown) {
            setHeaderStatus(' - {{ __("Đã bỏ cuộc") }}');

            if (typeof bootbox !== 'undefined') {
                bootbox.alert({
                    message: '<i class="fad fa-flag-checkered"></i> {{ __("Đã bỏ cuộc") }}',
                    locale: '{{ __("vi") }}',
                    centerVertical: true,
                    closeButton: false,
                    size: 'small',
                    buttons: { ok: { className: 'btn-danger' } }
                });
            }

            $('#game-over').html('<i class="fad fa-flag-checkered"></i> {{ __("Đã bỏ cuộc") }}');
            toggleDisabled($('#resign, #switch'), true);
            config.draggable = false;
            isComputerThinking = false;
            resignAlertShown = true;
        }
        if (kypho) kypho.updateControls();
    }

    // ------------------------------------------------------------------- init

    let config = {
        draggable: true,
        position: USES_DEFAULT_START ? 'start' : GAME_CONFIG.startFen,
        onDragStart: onDragStart,
        onDrop: onDrop,
        onMouseoutSquare: onMouseoutSquare,
        onMouseoverSquare: onMouseoverSquare,
        onSnapEnd: onSnapEnd,
        showNotation: true
    };

    board = Xiangqiboard('ban-co', config);
    $(window).resize(board.resize);
    if (!USES_DEFAULT_START) game.load(START_GAME_FEN);

    updateStatus();
    kypho = KyPho.initLocal({
        board: board,
        startFen: game.fen(),
        isLive: function() { return !game.game_over(); }
    });

    $(document).ready(function() {
        $('#FEN').val(game.fen());

        const autoStart = GAME_CONFIG.autoStart;
        if (autoStart && (!autoStart.ifBlackToMove || game.turn() === 'b')) {
            setTimeout(makeBestMove, autoStart.delay || 0);
        }
    });

    // --------------------------------------------------------------- controls

    $('#hint-btn').on('click', function(e){
        e.preventDefault();
        fetchHint();
    });

    $('#resign').on('click', function() {
        clearHint();
        if (isBusy()) return;
        game.load(game.fen() + ' resign');
        updateStatus();
    });

    $('#undo').on('click', function(){
        clearHint();
        if (isBusy()) return;
        if (game.history().length >= 2) {
            game.undo();
            game.undo();
            board.position(game.fen());
            if (typeof XiangqiSound !== 'undefined') XiangqiSound.playMove();
            updateStatus();
            if (kypho) kypho.setMoves(game.history());
        }
    });

    $('#switch').on('click', function() {
        clearHint();
        if (isBusy()) return;
        board.flip();
    });

    $('#reset').on('click', function() {
        clearHint();
        if (isHintPending) return;
        isComputerThinking = false;
        resignAlertShown = false;
        hasMoved = false;
        board.position(START_BOARD_POSITION);
        game.load(START_GAME_FEN);
        $('#game-status').removeClass('black').addClass('red');
        updateStatus();
        $('#game-over').removeClass('d-inline-block').addClass('d-none');
        toggleDisabled($('#resign, #switch'), false);
        config.draggable = true;
        if (kypho) kypho.setMoves([]);
    });

    // Links that hand the current position over to another page.
    $('.add-fen').on('click auxclick', function(e) {
        e.preventDefault();
        window.location.href = $(this).attr('href') + '/' + game.fen();
    });

    // Level dropdown: hand-pointer marker on the active item and on hover.
    $('.level.dropup .dropdown-item').each(function(){
        const activePointer = '<i class="far fa-hand-point-right"></i>  ';
        if ($(this).hasClass('active')) $(this).prepend(activePointer);
        $(this).not('.add-fen').on('click auxclick', function(e){
            window.location.href = $(this).attr('href');
        });
        $(this).on('mouseenter mouseleave', function(){
            if ($(this).has('i').length && !$(this).hasClass('text-danger')) {
                $(this).find('i').remove();
            } else if (!$(this).hasClass('text-danger')) {
                $(this).prepend(activePointer);
            }
        });
    });
</script>
