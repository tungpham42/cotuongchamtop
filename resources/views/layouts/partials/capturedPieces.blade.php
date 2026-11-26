{{--
    Capture trays: a small lacquer tray on each side of the board holding the pieces that side has
    taken (top tray = top player, bottom tray = bottom player).

      - seal (帥 / 將) says whose tray it is
      - one token per kind, with a ×N counter
      - the latest capture keeps a warm ember ring, and flies in from its square

    Included from head; initBoardClickInteraction() creates the trays, so every playable board gets
    them without per-page markup. What is "captured" is derived from the position on screen:
        starting pieces (config.capturedBaseline, else config.position) minus pieces on the board.
    That stays correct after undo, reset, move review, and positions loaded from the server
    (where game.history() is empty).
--}}
<style>
    /* ---------- Tokens: lamp-lit lacquer wood (dark) / morning tea paper (light) ---------- */
    /* One tray size for every page, game rooms included. */
    :root { --xq-captured-size: clamp(30px, 9vw, 38px); }
    .xq-captured {
        --cap-h: var(--xq-captured-size);
        --cap-piece: calc(var(--cap-h) - 10px);
        --cap-bg: linear-gradient(180deg, rgba(92, 52, 30, .94), rgba(45, 24, 14, .96));
        --cap-border: rgba(255, 200, 97, .42);
        --cap-inner: inset 0 1px 0 rgba(255, 226, 170, .22), inset 0 -6px 14px rgba(0, 0, 0, .32);
        --cap-shadow: 0 3px 8px rgba(0, 0, 0, .38);
        --cap-glow: rgba(255, 168, 66, .5);
        --cap-text: rgba(255, 232, 196, .72);
        --cap-count-bg: #ffe3a8;
        --cap-count-text: #3a1c0c;
    }
    :root[data-theme="light"] .xq-captured {
        --cap-bg: linear-gradient(180deg, #fff8e8, #f4dfb8);
        --cap-border: rgba(160, 84, 40, .38);
        --cap-inner: inset 0 1px 0 #fff, inset 0 -6px 14px rgba(180, 116, 56, .16);
        --cap-shadow: 0 3px 8px rgba(107, 58, 34, .2);
        --cap-glow: rgba(214, 103, 60, .45);
        --cap-text: #7a4a2c;
        --cap-count-bg: #a8402c;
        --cap-count-text: #fff6e4;
    }

    /* ---------- Tray ---------- */
    .xq-captured {
        container-type: inline-size;
        position: relative;
        display: flex;
        align-items: center;
        gap: 8px;
        flex: none;
        box-sizing: border-box;
        width: 100%;
        max-width: 382px;          /* #ban-co incl. border; the script then tracks its real width */
        height: var(--cap-h);
        margin-left: auto;
        margin-right: auto;
        padding: 0 5px 0 4px;
        border: 1px solid var(--cap-border);
        border-radius: 999px;
        background: var(--cap-bg);
        box-shadow: var(--cap-inner), var(--cap-shadow);
        line-height: 1;
        user-select: none;
        -webkit-user-select: none;
    }
    /* #ban-co keeps a 15px bottom margin and has a 5px gilded rim: sit just clear of it. */
    .xq-captured-top { margin-bottom: 10px; }
    .xq-captured-bottom { margin-top: -5px; margin-bottom: 10px; }
    /* ---------- Seal: whose tray this is ---------- */
    .xqc-seal {
        flex: none;
        display: grid;
        place-items: center;
        width: calc(var(--cap-h) - 8px);
        height: calc(var(--cap-h) - 8px);
        border-radius: 32%;
        font-family: "Texturina", "Noto Serif SC", serif;
        font-size: calc(var(--cap-h) * .43);
        font-weight: 900;
        color: #fff3da;
        box-shadow: inset 0 0 0 1.5px rgba(255, 226, 170, .65), 0 1px 3px rgba(0, 0, 0, .35);
    }
    .xq-captured[data-owner="r"] .xqc-seal { background: linear-gradient(145deg, #d8543b, #8e1f16); }
    .xq-captured[data-owner="b"] .xqc-seal { background: linear-gradient(145deg, #55464a, #17100f); }

    /* ---------- Captured tokens ---------- */
    .xqc-list {
        flex: 1 1 auto;
        min-width: 0;
        height: 100%;
        display: flex;
        align-items: center;
        gap: clamp(3px, 2.2cqw, 10px);
    }
    .xqc-empty {
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        font-size: clamp(.66rem, 3.2cqw, .76rem);
        font-style: italic;
        letter-spacing: .3px;
        color: var(--cap-text);
    }
    .xqc-item {
        position: relative;
        flex: 0 1 auto;
        width: var(--cap-piece);
        min-width: 0;
        aspect-ratio: 1;
        border-radius: 50%;
        cursor: help;
        transition: transform .2s cubic-bezier(.2, 1.6, .4, 1);
    }
    .xqc-item:hover { transform: translateY(-2px) scale(1.14); z-index: 2; }
    .xqc-item img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: contain;
        filter: drop-shadow(0 2px 2px rgba(0, 0, 0, .45));
    }
    /* a second bead peeking out underneath = "more than one" */
    .xqc-item.is-stack::before {
        content: "";
        position: absolute;
        inset: 1px -3px -1px 3px;
        border-radius: 50%;
        background: #b98a52;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .4);
    }
    .xqc-item.is-stack img { position: relative; }
    .xqc-count {
        position: absolute;
        right: -5px;
        bottom: -3px;
        min-width: 1.5em;
        padding: 1px 2px;
        box-sizing: border-box;
        border-radius: 999px;
        background: var(--cap-count-bg);
        color: var(--cap-count-text);
        font-size: clamp(8px, calc(var(--cap-h) * .27), 11px);
        font-weight: 800;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .45);
    }

    /* Latest capture: ember ring */
    .xqc-item.is-latest { box-shadow: 0 0 0 2px #ffb347, 0 0 10px 3px var(--cap-glow); }
    .xqc-item.is-landing { animation: xqcLand .55s cubic-bezier(.2, 1.5, .4, 1); }
    .xqc-item.is-landing::after {
        content: "";
        position: absolute;
        inset: -3px;
        border-radius: 50%;
        border: 2px solid #ffd27a;
        pointer-events: none;
        animation: xqcRipple .65s ease-out forwards;
    }
    .xqc-item.is-incoming { visibility: hidden; }

    /* ---------- Piece in flight from its square to the tray ---------- */
    .xqc-flyer {
        position: fixed;
        z-index: 2000;
        pointer-events: none;
        transform-origin: top left;
        will-change: transform;
    }

    @keyframes xqcLand   { 0% { transform: scale(1.6); } 60% { transform: scale(.88); } 100% { transform: scale(1); } }
    @keyframes xqcRipple { from { transform: scale(.9); opacity: 1; } to { transform: scale(2); opacity: 0; } }

    /* ---------- Responsive ---------- */
    /* Fit-to-viewport stage (game rooms): same trays and spacing as elsewhere. The stage only
       reserves their height, so the board is sized to leave room for them. */
    .xq-stage.xq-has-captured {
        --xq-captured-space: 10px;   /* between a tray and the board */
        --xq-captured-total: calc(2 * (var(--xq-captured-size) + var(--xq-captured-space)));
    }
    .xq-stage .xq-captured { max-width: none; margin: 0; }
    /* the stage already puts --xq-gap between its children: add only the difference */
    .xq-stage .xq-captured-top { margin-bottom: calc(var(--xq-captured-space) - var(--xq-gap)); }
    .xq-stage .xq-captured-bottom { margin-top: calc(var(--xq-captured-space) - var(--xq-gap)); }
    /* Very short screens (landscape phones): slimmer trays */
    @media (max-height: 560px) {
        .xq-stage { --xq-captured-size: 28px; }
    }
    /* Narrow boards: give the tokens the room */
    @container (max-width: 290px) {
        .xqc-seal { display: none; }
        .xqc-list { padding-left: 4px; }
        .xqc-count { right: -4px; bottom: -4px; min-width: 0; padding: 0 1px; font-size: 8px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .xq-captured, .xq-captured * { animation: none !important; transition: none !important; }
        .xqc-item.is-landing::after { display: none; }
    }
</style>
<script>
window.initCapturedPieces = function (board, element, config) {
    // Display order in a tray. Kings are never captured.
    const ORDER = ['R', 'C', 'N', 'B', 'A', 'P'];
    const START = 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR';
    const TEXT = {
        title: @json(__('Quân đã bị ăn')),
        empty: @json(__('Chưa ăn được quân nào'))
    };

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

    function node(tag, className, text) {
        const el = document.createElement(tag);
        el.className = className;
        if (text !== undefined) el.textContent = text;
        return el;
    }

    function createTray(place) {
        const tray = node('div', 'xq-captured xq-captured-' + place);
        tray.setAttribute('role', 'img');
        const seal = node('span', 'xqc-seal');
        const list = node('div', 'xqc-list');
        tray.append(seal, list);
        element.parentNode.insertBefore(tray, place === 'top' ? element : element.nextSibling);
        return { el: tray, seal: seal, list: list, key: '' };
    }
    const top = createTray('top');
    const bottom = createTray('bottom');

    let previous = null;      // position at the last update
    let latest = null;        // code of the most recent capture
    let flight = null;

    function pieceUrl(code) {
        const theme = config.pieceTheme;
        return typeof theme === 'function' ? theme(code) : theme.replace('{piece}', code);
    }

    function pieceName(code) {
        const names = window.locale || {};
        return names['piece_' + code.charAt(1).toLowerCase()] || code.charAt(1);
    }

    // Pieces of one colour that are missing from the board.
    function missing(onBoard, color) {
        return ORDER.map(type => {
            const code = color + type;
            return { code: code, n: Math.max(0, (baseline[code] || 0) - (onBoard[code] || 0)) };
        }).filter(item => item.n);
    }

    // `owner` is the player the tray belongs to; `items` are the opponent pieces they have taken.
    function render(tray, owner, items) {
        const key = [config.pieceTheme, owner, latest,
            ...items.map(item => item.code + item.n)].join('|');
        if (tray.key === key) return;
        tray.key = key;

        tray.el.dataset.owner = owner;
        tray.seal.textContent = owner === 'r' ? '帥' : '將';

        const spoken = items.map(item => pieceName(item.code) + ' ×' + item.n).join(', ');
        tray.el.setAttribute('aria-label', TEXT.title + ': ' + (spoken || TEXT.empty));

        if (!items.length) {
            tray.list.replaceChildren(node('span', 'xqc-empty', TEXT.empty));
            return;
        }
        tray.list.replaceChildren(...items.map(item => {
            const token = node('span', 'xqc-item');
            token.dataset.code = item.code;
            token.title = pieceName(item.code) + ' ×' + item.n;
            if (item.n > 1) token.classList.add('is-stack');
            if (item.code === latest) token.classList.add('is-latest');
            const image = document.createElement('img');
            image.src = pieceUrl(item.code);
            image.alt = '';
            token.appendChild(image);
            if (item.n > 1) token.appendChild(node('span', 'xqc-count', '×' + item.n));
            return token;
        }));
    }

    // Exactly one more piece gone than last time = a capture that just happened. Anything else
    // (first paint, undo, reset, jumping through the move list) is shown without ceremony.
    function newCapture(before, now) {
        let gained = null, total = 0;
        ['r', 'b'].forEach(color => ORDER.forEach(type => {
            const code = color + type;
            const diff = (before[code] || 0) - (now[code] || 0);
            total += Math.abs(diff);
            if (diff === 1) gained = code;
        }));
        return total === 1 ? gained : null;
    }

    function land(token) {
        token.classList.remove('is-incoming');
        token.classList.add('is-landing');
        token.addEventListener('animationend', event => {
            if (event.animationName === 'xqcLand') token.classList.remove('is-landing');
        });
    }

    function stopFlight() {
        if (flight) flight.cancel();
    }

    // Carries a copy of the captured piece from its square into the tray.
    function fly(code, squareName, token) {
        const from = element.querySelector('.square-' + squareName);
        const still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (still || !from || !token.animate) return land(token);
        const a = from.getBoundingClientRect();
        const b = token.getBoundingClientRect();
        if (!a.width || !b.width) return land(token);

        const flyer = node('img', 'xqc-flyer');
        flyer.src = pieceUrl(code);
        flyer.alt = '';
        Object.assign(flyer.style, { left: a.left + 'px', top: a.top + 'px', width: a.width + 'px', height: a.height + 'px' });
        document.body.appendChild(flyer);
        token.classList.add('is-incoming');

        const dx = b.left - a.left;
        const dy = b.top - a.top;
        const scale = b.width / a.width;
        const animation = flyer.animate([
            { transform: 'translate(0, 0) scale(1)', filter: 'drop-shadow(0 0 0 rgba(255,190,90,0))' },
            { transform: `translate(${dx * .35}px, ${dy * .35 - a.height * .7}px) scale(1.35)`,
              filter: 'drop-shadow(0 10px 8px rgba(0,0,0,.45)) drop-shadow(0 0 14px rgba(255,190,90,1))', offset: .38 },
            { transform: `translate(${dx}px, ${dy}px) scale(${scale})`, filter: 'drop-shadow(0 0 6px rgba(255,190,90,.8))' }
        ], { duration: 620, easing: 'cubic-bezier(.45, .05, .3, 1)' });
        flight = animation;
        const done = () => {
            flyer.remove();
            if (flight === animation) flight = null;
            land(token);
        };
        animation.onfinish = done;
        animation.oncancel = done;
    }

    function update() {
        const position = board.position();
        const onBoard = count(position);
        const moved = previous && JSON.stringify(previous) !== JSON.stringify(position);
        let captured = null;
        if (moved) {
            stopFlight();
            captured = newCapture(count(previous), onBoard);
            // The ring follows the newest capture and is dropped once that piece is back.
            if (captured) latest = captured;
            else if (latest && (onBoard[latest] || 0) > (count(previous)[latest] || 0)) latest = null;
        }

        const bottomColor = board.orientation() === 'black' ? 'b' : 'r';
        const topColor = bottomColor === 'r' ? 'b' : 'r';
        render(top, topColor, missing(onBoard, bottomColor));
        render(bottom, bottomColor, missing(onBoard, topColor));

        if (captured) {
            const square = Object.keys(previous).find(name =>
                previous[name] === captured && position[name] && position[name] !== captured);
            const token = [top, bottom].map(tray =>
                tray.list.querySelector('[data-code="' + captured + '"]')).find(Boolean);
            if (token && square) fly(captured, square, token);
            else if (token) land(token);
        }
        previous = position;
    }

    const stage = element.closest('.xq-stage');
    if (stage) {
        // The trays take some height, so the board has to be measured again.
        stage.classList.add('xq-has-captured');
        board.resize();
    } else if (window.ResizeObserver) {
        new ResizeObserver(() => {
            const width = element.offsetWidth + 'px';
            [top, bottom].forEach(tray => { tray.el.style.width = tray.el.style.maxWidth = width; });
        }).observe(element);
    }
    // A flight aims at fixed screen coordinates: finish it early if the page moves underneath.
    window.addEventListener('scroll', stopFlight, { passive: true });
    window.addEventListener('resize', stopFlight);

    update();
    return { update: update };
};
</script>
