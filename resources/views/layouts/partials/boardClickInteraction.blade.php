<style>
    /* ==========================================================================
       BOARD CLICK INTERACTION — LIQUID GLASS + CUNG ĐÌNH HUẾ
       Classes toggled by paint(): click-selected, click-target,
       click-last-from, click-last-to
       ========================================================================== */
    #ban-co .square-2b8ce { pointer-events: auto !important; cursor: pointer; }

    /* Pieces sit above the halo layers */
    #ban-co .square-2b8ce img {
        position: relative;
        z-index: 2;
        transition: transform .28s cubic-bezier(.175, .885, .32, 1.275), filter .28s ease;
    }

    /* ---------- Last move: soft gold wash (from) + glass tile (to) ---------- */
    /* No square tile on from/to: only the bead (from) and piece ring (to) remain. */
    #ban-co .click-last-to img {
        border-radius: 50%;
        outline: 2px solid rgba(255, 215, 0, .95);
        outline-offset: 1px;
        box-shadow: 0 0 12px rgba(255, 215, 0, .65);
    }
    /* Vacated square: tiny glossy bead */
    #ban-co .click-last-from::after {
        content: '';
        position: absolute; left: 50%; top: 50%;
        width: 16%; height: 16%; border-radius: 50%;
        transform: translate(-50%, -50%);
        background: radial-gradient(circle at 35% 30%, #fff, rgba(255, 215, 0, .7) 70%);
        box-shadow: 0 0 8px rgba(255, 215, 0, .8);
        pointer-events: none; z-index: 3;
    }

    /* ---------- Selected piece: lifted, glowing gold halo ---------- */
    #ban-co .square-2b8ce.click-selected {
        background:
            radial-gradient(circle at 50% 50%, rgba(255, 215, 0, .55), rgba(255, 215, 0, .15) 60%, transparent 75%) !important;
        z-index: 4;
    }
    #ban-co .square-2b8ce.click-selected img,
    #ban-co .square-2b8ce.click-selected:hover img {
        transform: translateY(-6px) scale(1.12) !important;
        filter:
            drop-shadow(0 8px 6px rgba(0, 0, 0, .55))
            drop-shadow(0 0 10px rgba(255, 215, 0, .95)) !important;
    }
    @keyframes clickSelectedPulse {
        0%, 100% { box-shadow: inset 0 0 0 2px var(--royal-gold), inset 0 0 14px rgba(255, 215, 0, .6); }
        50%      { box-shadow: inset 0 0 0 2px #fff,              inset 0 0 26px rgba(255, 215, 0, .95); }
    }

    /* ---------- Legal move (empty square): glossy gold glass orb ---------- */
    #ban-co .click-target::after {
        content: '';
        position: absolute; left: 50%; top: 50%;
        width: 26%; height: 26%; border-radius: 50%;
        transform: translate(-50%, -50%);
        background:
            radial-gradient(circle at 32% 28%, #fff 0%, rgba(255, 242, 204, .95) 18%, var(--royal-gold) 55%, #b89020 100%);
        box-shadow:
            0 0 0 2px rgba(138, 21, 21, .55),
            0 3px 6px rgba(0, 0, 0, .55),
            0 0 14px rgba(255, 215, 0, .85),
            inset 0 -2px 4px rgba(0, 0, 0, .25);
        pointer-events: none; z-index: 3;
        animation: clickOrbIn .3s cubic-bezier(.175, .885, .32, 1.275) both,
                   clickOrbGlow 1.8s ease-in-out .3s infinite;
    }
    #ban-co .click-target:hover::after {
        width: 36%; height: 36%;
        transition: width .2s ease, height .2s ease;
    }
    #ban-co .click-target:not(:has(img)):hover {
        background: radial-gradient(circle, rgba(255, 215, 0, .35), transparent 75%) !important;
    }

    /* ---------- Capture (square holds an enemy piece): ruby-gold ring ---------- */
    #ban-co .click-target:has(img)::after {
        width: 92%; height: 92%;
        background: transparent;
        border: 3px solid transparent;
        background-image:
            linear-gradient(transparent, transparent),
            conic-gradient(from 0deg, var(--royal-red-light), var(--royal-gold), #fff, var(--royal-gold), var(--royal-red-light));
        background-origin: border-box;
        background-clip: padding-box, border-box;
        box-shadow:
            0 0 14px rgba(230, 57, 70, .9),
            inset 0 0 14px rgba(230, 57, 70, .6);
        animation: clickRingIn .3s cubic-bezier(.175, .885, .32, 1.275) both,
                   clickRingSpin 3s linear .3s infinite,
                   clickRingGlow 1.2s ease-in-out .3s infinite;
    }
    #ban-co .click-target:has(img) img {
        filter: drop-shadow(0 0 8px rgba(230, 57, 70, .9));
    }

    @keyframes clickOrbIn {
        from { transform: translate(-50%, -50%) scale(0); opacity: 0; }
        to   { transform: translate(-50%, -50%) scale(1); opacity: 1; }
    }
    @keyframes clickOrbGlow {
        0%, 100% { filter: brightness(1); }
        50%      { filter: brightness(1.2) drop-shadow(0 0 6px rgba(255, 215, 0, .9)); }
    }
    @keyframes clickRingIn {
        from { transform: translate(-50%, -50%) scale(.4); opacity: 0; }
        to   { transform: translate(-50%, -50%) scale(1); opacity: 1; }
    }
    @keyframes clickRingSpin {
        to { filter: hue-rotate(0deg); transform: translate(-50%, -50%) rotate(360deg); }
    }
    @keyframes clickRingGlow {
        0%, 100% { opacity: .75; }
        50%      { opacity: 1; }
    }

    /* Existing highlight helpers stay neutral */
    #ban-co .highlight-red, #ban-co .highlight-black { background: transparent !important; box-shadow: none !important; }

    @media (prefers-reduced-motion: reduce) {
        #ban-co .click-selected,
        #ban-co .click-target::after,
        #ban-co .click-target:has(img)::after { animation: none !important; }
        #ban-co .square-2b8ce img { transition: none; }
    }
    #ban-co .square-2b8ce.last-move-from {
        background: none !important;
    }
    #ban-co .square-2b8ce.last-move-to {
        box-shadow: none !important;
        background: none !important;
    }
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
            // Glide with a soft ease-out, a small lift mid-flight, and a gold trail glow.
            const animation = image.animate([
                { translate: `${rects[source].left - end.left}px ${rects[source].top - end.top}px`,
                  scale: '1.12', filter: 'drop-shadow(0 8px 6px rgba(0,0,0,.5)) drop-shadow(0 0 12px rgba(255,215,0,.9))' },
                { translate: '0px 0px', scale: '1', filter: 'drop-shadow(0 0 0 rgba(255,215,0,0))' }
            ], { duration: 180, easing: 'cubic-bezier(.22, .8, .3, 1)' });
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
        if (board.isReviewing || config.clickMovesEnabled === false) return;
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
