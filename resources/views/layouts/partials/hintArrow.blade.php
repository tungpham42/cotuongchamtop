{{--
    Hint arrow + VIP upsell dialog (extracted from ai/gameScript.blade.php so the
    "play alone" page can reuse them).

    Provides: hintMove, clearHint(), drawHintArrow(), showHint(move), showVipUpsellDialog(msg)
    Do NOT include this on a page that also includes ai/gameScript (it declares the same names).
--}}
<script>
    let hintMove = null;

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
</script>
