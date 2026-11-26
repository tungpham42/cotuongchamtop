{{--
    Captured pieces, shown quietly around the board: each side's tray sits next to that side and
    holds the pieces it has taken (top tray = top player, bottom tray = bottom player).

    Included from head; initBoardClickInteraction() creates the trays, so every playable board gets
    them without per-page markup. What is "captured" is derived from the position on screen:
        starting pieces (config.capturedBaseline, else config.position) minus pieces on the board.
    That stays correct after undo, reset, move review, and positions loaded from the server
    (where game.history() is empty).
--}}
<style>
    .xq-captured {
        display: flex;
        align-items: center;
        flex: none;
        box-sizing: border-box;
        width: 100%;
        max-width: 382px;          /* #ban-co incl. border; the script then tracks its real width */
        height: var(--xq-captured-h, 20px);
        margin-left: auto;
        margin-right: auto;
        padding: 0 6px;
        overflow: hidden;
        opacity: .72;
        pointer-events: none;
        user-select: none;
        -webkit-user-select: none;
    }
    /* #ban-co keeps a 15px bottom margin and has a 5px gilded rim: sit just under it. */
    .xq-captured-top { margin-bottom: 8px; }
    .xq-captured-bottom { margin-top: -7px; margin-bottom: 8px; }

    /* Identical pieces overlap into a small stack; a little air separates the kinds. */
    .xq-captured img {
        flex: 0 1 auto;
        width: var(--xq-captured-h, 20px);
        height: var(--xq-captured-h, 20px);
        min-width: 0;
        object-fit: contain;
    }
    .xq-captured img + img { margin-left: 5px; }
    .xq-captured img.is-repeat { margin-left: calc(var(--xq-captured-h, 20px) * -.5); }

    /* Fit-to-viewport stage (play pages): budget the two trays so the stack still fits the screen. */
    .xq-stage.xq-has-captured {
        --xq-captured-h: 18px;
        --xq-captured-total: calc(2 * (var(--xq-captured-h) + var(--xq-gap)));
    }
    .xq-stage .xq-captured { max-width: none; margin: 0; }
    @media (max-height: 560px) {
        .xq-stage.xq-has-captured { --xq-captured-h: 14px; }
    }
</style>
<script>
window.initCapturedPieces = function (board, element, config) {
    // Most valuable first. Kings are never captured.
    const ORDER = ['R', 'C', 'N', 'B', 'A', 'P'];
    const START = 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR';
    const label = @json(__('Quân đã bị ăn'));

    // Counts pieces by board code ('rR', 'bP', ...) from 'start', a FEN or a position object.
    function count(position) {
        let codes = [];
        if (typeof position === 'string') {
            const placement = position === 'start' ? START : position.trim().split(/\s+/)[0];
            codes = (placement.match(/[a-z]/gi) || []).map(letter =>
                (letter === letter.toUpperCase() ? 'r' : 'b') + letter.toUpperCase());
        } else if (position) {
            codes = Object.values(position);
        }
        return codes.reduce((totals, code) => {
            totals[code] = (totals[code] || 0) + 1;
            return totals;
        }, {});
    }

    const baseline = count(config.capturedBaseline || config.position);

    function createTray(place) {
        const tray = document.createElement('div');
        tray.className = 'xq-captured xq-captured-' + place;
        tray.setAttribute('role', 'img');
        element.parentNode.insertBefore(tray, place === 'top' ? element : element.nextSibling);
        return tray;
    }
    const top = createTray('top');
    const bottom = createTray('bottom');

    function fill(tray, color) {
        const onBoard = count(board.position());
        const codes = [];
        ORDER.forEach(type => {
            const code = color + type;
            for (let i = (onBoard[code] || 0); i < (baseline[code] || 0); i++) codes.push(code);
        });
        const theme = config.pieceTheme;
        const key = [theme, ...codes].join('|');
        if (tray.dataset.key === key) return;
        tray.dataset.key = key;
        tray.setAttribute('aria-label', label + ': ' + codes.length);
        tray.replaceChildren(...codes.map((code, i) => {
            const image = document.createElement('img');
            image.src = typeof theme === 'function' ? theme(code) : theme.replace('{piece}', code);
            image.alt = '';
            if (code === codes[i - 1]) image.className = 'is-repeat';
            return image;
        }));
    }

    function update() {
        const bottomColor = board.orientation() === 'black' ? 'b' : 'r';
        // Each player's tray holds the opponent's pieces.
        fill(top, bottomColor);
        fill(bottom, bottomColor === 'r' ? 'b' : 'r');
    }

    const stage = element.closest('.xq-stage');
    if (stage) {
        // The trays take some height, so the board has to be measured again.
        stage.classList.add('xq-has-captured');
        board.resize();
    } else if (window.ResizeObserver) {
        new ResizeObserver(() => {
            const width = element.offsetWidth + 'px';
            [top, bottom].forEach(tray => { tray.style.width = tray.style.maxWidth = width; });
        }).observe(element);
    }

    update();
    return { update: update };
};
</script>
