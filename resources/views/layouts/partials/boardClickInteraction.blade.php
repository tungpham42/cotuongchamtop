<style>
    #ban-co .square-2b8ce { pointer-events: auto !important; cursor: pointer; }
    #ban-co .square-2b8ce.click-selected img,
    #ban-co .square-2b8ce.click-selected:hover img { transform: translateY(-5px) !important; }
    #ban-co .click-target::after, #ban-co .click-last-from::after {
        content: ''; position: absolute; left: 50%; top: 50%;
        width: 20%; height: 20%; border-radius: 50%;
        transform: translate(-50%, -50%); background: rgba(255,255,255,.85);
        pointer-events: none; z-index: 3;
    }
    #ban-co .click-target:has(img)::after {
        width: 80%; height: 80%; background: transparent; border: 3px solid white;
    }
    #ban-co .click-last-to img { border-radius: 50%; outline: 2px solid white; outline-offset: 1px; }
    #ban-co .highlight-red, #ban-co .highlight-black { background: transparent !important; box-shadow: none !important; }
</style>
<script>
window.initBoardClickInteraction = function (board, game, config) {
    const element = document.getElementById('ban-co');
    board.applyTheme = function (boardTheme, pieceTheme) {
        config.boardTheme = boardTheme;
        config.pieceTheme = pieceTheme;
        element.querySelectorAll('.board-1ef78').forEach(el => {
            el.style.backgroundImage = `url("${boardTheme}")`;
        });
        element.querySelectorAll('img[data-piece]').forEach(image => {
            image.src = pieceTheme.replace('{piece}', image.dataset.piece);
        });
    };
    // Render once, then slide the actual destination image. No flying clones.
    const originalPosition = board.position;
    let sliding = [];
    board.position = function (position) {
        if (arguments.length === 0) return originalPosition.call(board);
        if (position === 'fen') return originalPosition.call(board, position);
        const before = originalPosition.call(board);
        const rects = {};
        Object.keys(before).forEach(name => {
            const image = square(name)?.querySelector('img');
            if (image) rects[name] = image.getBoundingClientRect();
        });
        // Repeated status/snap callbacks often request the same position.
        // Avoid replacing an image while its slide is still running.
        const fen = typeof position === 'string' ? position.split(' ')[0] : null;
        if (fen && fen === originalPosition.call(board, 'fen')) return;
        if (typeof position === 'object' && JSON.stringify(position) === JSON.stringify(before)) return;
        sliding.forEach(animation => animation.cancel());
        sliding = [];
        const result = originalPosition.call(board, position, false);
        const after = originalPosition.call(board);
        paint();
        const sources = Object.keys(before).filter(name => before[name] !== after[name]);
        Object.keys(after).filter(name => after[name] !== before[name]).forEach(target => {
            const source = sources.find(name => before[name] === after[target] && rects[name]);
            if (!source) return;
            sources.splice(sources.indexOf(source), 1);
            const image = square(target)?.querySelector('img');
            if (!image) return;
            const end = image.getBoundingClientRect();
            const cell = square(target);
            cell.style.zIndex = '4';
            const animation = image.animate([
                { translate: `${rects[source].left - end.left}px ${rects[source].top - end.top}px` },
                { translate: '0px 0px' }
            ], { duration: 100, easing: 'linear' });
            sliding.push(animation);
            const cleanup = () => { cell.style.zIndex = ''; };
            animation.onfinish = cleanup;
            animation.oncancel = cleanup;
        });
        return result;
    };
    let selected = null;
    let selectedFen = null;
    function square(name) { return element.querySelector('.square-' + name); }
    function paint() {
        if (board.isReviewing || selectedFen !== game.fen()) selected = null;
        element.querySelectorAll('.square-2b8ce').forEach(el => {
            el.classList.remove('click-selected', 'click-target', 'click-last-from', 'click-last-to');
        });
        const history = game.history({ verbose: true });
        const last = board.isReviewing ? board.reviewMove : history[history.length - 1];
        if (last) {
            square(last.from)?.classList.add('click-last-from');
            square(last.to)?.classList.add('click-last-to');
        }
        if (selected) {
            square(selected)?.classList.add('click-selected');
            game.moves({ square: selected, verbose: true }).forEach(move => {
                square(move.to)?.classList.add('click-target');
            });
        }
    }
    element.addEventListener('click', function (event) {
        if (board.isReviewing) return;
        if (sliding.some(animation => animation.playState === 'running')) return;
        const cell = event.target.closest('[data-square]');
        if (!cell || !element.contains(cell)) return;
        const target = cell.dataset.square;
        if (selectedFen !== game.fen()) selected = null;
        if (selected === target) { selected = null; paint(); return; }
        if (selected) {
            const position = board.position();
            const allowed = !config.onDragStart || config.onDragStart(selected, position[selected], position, board.orientation()) !== false;
            const legal = game.moves({ square: selected, verbose: true }).some(move => move.to === target);
            if (allowed && legal) {
                const source = selected;
                selected = null;
                if (config.onDrop(source, target) !== 'snapback') {
                    board.position(game.fen(), false);
                    if (config.onSnapEnd) config.onSnapEnd();
                }
                paint();
                return;
            }
        }
        selected = null;
        const position = board.position();
        const piece = position[target];
        if (piece && (!config.onDragStart || config.onDragStart(target, piece, position, board.orientation()) !== false)
            && game.moves({ square: target, verbose: true }).length) {
            selected = target;
            selectedFen = game.fen();
        }
        paint();
    });
    new MutationObserver(paint).observe(element, { childList: true, subtree: true });
    paint();
};
</script>
