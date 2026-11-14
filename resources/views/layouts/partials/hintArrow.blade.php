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

        const shaftW = size * 0.22;
        const trim = Math.max(2, size * 0.055);   // gold trim thickness
        const headLen = size * 0.5;
        const headW = size * 0.68;

        // Gentle bend, so the arrow doesn't hide pieces as much as a ruler-straight line.
        const bend = Math.min(len * 0.14, size * 0.55);
        const cx = (x1 + x2) / 2 + px * bend;
        const cy = (y1 + y2) / 2 + py * bend;

        const sx = x1 + ux * size * 0.28, sy = y1 + uy * size * 0.28;
        const tipX = x2 - ux * size * 0.12, tipY = y2 - uy * size * 0.12;

        let hx = tipX - cx, hy = tipY - cy;
        const hl = Math.hypot(hx, hy) || 1;
        hx /= hl; hy /= hl;
        const baseX = tipX - hx * headLen, baseY = tipY - hy * headLen;
        if ((baseX - sx) * ux + (baseY - sy) * uy <= 0) return; // too short to draw

        const hpx = -hy, hpy = hx;
        const baseL = [baseX + hpx * headW / 2, baseY + hpy * headW / 2];
        const baseR = [baseX - hpx * headW / 2, baseY - hpy * headW / 2];
        const pts = (arr) => arr.map(p => p[0].toFixed(1) + ',' + p[1].toFixed(1)).join(' ');

        const pathFor = (ox, oy) =>
            'M' + (sx + ox).toFixed(1) + ' ' + (sy + oy).toFixed(1) +
            ' Q' + (cx + ox).toFixed(1) + ' ' + (cy + oy).toFixed(1) +
            ' ' + (baseX + ox).toFixed(1) + ' ' + (baseY + oy).toFixed(1);

        const pathD = pathFor(0, 0);
        // Glossy highlight rides slightly off-centre, like light on wet glass.
        const glossD = pathFor(-px * shaftW * 0.2, -py * shaftW * 0.2);

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
            'filter:drop-shadow(0 6px 10px rgba(0,0,0,.7)) drop-shadow(0 0 14px rgba(255,215,0,.55));';

        const style = el('style', {}, svg);
        style.textContent =
            '@keyframes ha-draw{from{stroke-dashoffset:1}to{stroke-dashoffset:0}}' +
            '@keyframes ha-pop{0%{transform:scale(0);opacity:0}70%{transform:scale(1.2);opacity:1}100%{transform:scale(1);opacity:1}}' +
            '@keyframes ha-ring{0%{transform:scale(.55);opacity:.95}100%{transform:scale(1.55);opacity:0}}' +
            '@keyframes ha-bob{0%,100%{transform:translate(0,0)}50%{transform:translate(' + (ux * size * 0.07).toFixed(1) + 'px,' + (uy * size * 0.07).toFixed(1) + 'px)}}' +
            '@keyframes ha-sheen{from{stroke-dashoffset:.16}to{stroke-dashoffset:-1}}' +
            '@keyframes ha-twinkle{0%,100%{transform:scale(.4) rotate(0deg);opacity:.2}50%{transform:scale(1) rotate(45deg);opacity:1}}' +
            '#hint-arrow .ha-shaft{stroke-dasharray:1;stroke-dashoffset:1;animation:ha-draw .45s cubic-bezier(.3,.7,.3,1) forwards}' +
            '#hint-arrow .ha-sheen{stroke-dasharray:.16 2;stroke-dashoffset:.16;opacity:0;animation:ha-sheen 1.8s 0.9s ease-in-out infinite;}' +
            '#hint-arrow .ha-pop{transform-box:fill-box;transform-origin:center;animation:ha-pop .35s .38s cubic-bezier(.2,1.6,.4,1) both}' +
            '#hint-arrow .ha-bob{animation:ha-bob 1.1s .8s ease-in-out infinite}' +
            '#hint-arrow .ha-ring{transform-box:fill-box;transform-origin:center;animation:ha-ring 1.5s .5s ease-out infinite}' +
            '#hint-arrow .ha-ring2{animation-delay:1.25s}' +
            '#hint-arrow .ha-spark{transform-box:fill-box;transform-origin:center;animation:ha-twinkle 1.6s .9s ease-in-out infinite}' +
            '@media (prefers-reduced-motion:reduce){#hint-arrow *{animation:none!important}#hint-arrow .ha-shaft{stroke-dashoffset:0!important}#hint-arrow .ha-sheen{display:none}}';

        // Gradients run along the arrow: ruby glass body, liquid gold trim.
        const defs = el('defs', {}, svg);
        const lin = (id, stops) => {
            const g = el('linearGradient', {
                id, gradientUnits: 'userSpaceOnUse', x1: sx, y1: sy, x2: tipX, y2: tipY
            }, defs);
            stops.forEach(([o, c]) => el('stop', { offset: o, 'stop-color': c }, g));
        };
        lin('ha-ruby', [['0%', '#5c0a0a'], ['55%', '#b72222'], ['100%', '#e63946']]);
        lin('ha-gold', [['0%', '#b89020'], ['50%', '#ffd700'], ['100%', '#fff2cc']]);

        const radial = el('radialGradient', { id: 'ha-aura' }, defs);
        el('stop', { offset: '0%', 'stop-color': 'rgba(255,215,0,.35)' }, radial);
        el('stop', { offset: '100%', 'stop-color': 'rgba(138,21,21,.15)' }, radial);

        // Target: royal gold aura + pulsing rings.
        const ringR = size * 0.4;
        el('circle', { cx: x2, cy: y2, r: ringR, fill: 'url(#ha-aura)', stroke: '#ffd700', 'stroke-width': Math.max(2, size * 0.04) }, svg);
        [0, 1].forEach(i => {
            el('circle', {
                class: 'ha-ring' + (i ? ' ha-ring2' : ''),
                cx: x2, cy: y2, r: ringR,
                fill: 'none', stroke: '#ffd700',
                'stroke-width': Math.max(2, size * 0.05)
            }, svg);
        });

        // Shaft: gold trim -> dark gap -> ruby glass body.
        const shaft = (stroke, w, extra) => el('path', Object.assign({
            d: pathD, pathLength: 1, fill: 'none', stroke,
            'stroke-width': w, 'stroke-linecap': 'round', class: 'ha-shaft'
        }, extra || {}), svg);

        shaft('url(#ha-gold)', shaftW + trim * 2.6);
        shaft('#0b0c10', shaftW + trim * 0.8);
        shaft('url(#ha-ruby)', shaftW);

        // Glossy specular streak.
        el('path', {
            d: glossD, pathLength: 1, fill: 'none', stroke: 'rgba(255,255,255,.55)',
            'stroke-width': shaftW * 0.24, 'stroke-linecap': 'round', class: 'ha-shaft'
        }, svg);

        // Liquid sheen: a bright band that sweeps along the shaft, like the buttons.
        el('path', {
            d: pathD, pathLength: 1, fill: 'none', stroke: 'rgba(255,255,255,.85)',
            'stroke-width': shaftW * 0.85, 'stroke-linecap': 'butt', class: 'ha-sheen'
        }, svg);

        // Start marker: gold coin with a ruby gem.
        const startG = el('g', { class: 'ha-pop' }, svg);
        el('circle', { cx: x1, cy: y1, r: size * 0.18, fill: '#0b0c10', stroke: 'url(#ha-gold)', 'stroke-width': trim * 1.4 }, startG);
        el('circle', { cx: x1, cy: y1, r: size * 0.1, fill: '#e63946' }, startG);
        el('circle', { cx: x1 - size * 0.035, cy: y1 - size * 0.035, r: size * 0.03, fill: 'rgba(255,255,255,.8)' }, startG);

        // Arrowhead: faceted ruby gem with gold trim, pops in then bobs toward the target.
        const bob = el('g', { class: 'ha-bob' }, svg);
        const headG = el('g', { class: 'ha-pop' }, bob);
        const base = [(baseL[0] + baseR[0]) / 2, (baseL[1] + baseR[1]) / 2];
        el('polygon', {
            points: pts([[tipX, tipY], baseL, baseR]),
            fill: 'url(#ha-ruby)', stroke: 'url(#ha-gold)',
            'stroke-width': trim * 2.4, 'stroke-linejoin': 'round', 'paint-order': 'stroke'
        }, headG);
        // Lit facet (half of the head) for the glossy, cut-gem look.
        el('polygon', {
            points: pts([[tipX, tipY], baseL, base]),
            fill: 'rgba(255,255,255,.28)'
        }, headG);

        // Twinkling sparkle on the tip.
        const sparkR = size * 0.2;
        const sparkG = el('g', { transform: 'translate(' + tipX.toFixed(1) + ' ' + tipY.toFixed(1) + ')' }, svg);
        el('path', {
            class: 'ha-spark',
            d: 'M0 ' + (-sparkR) + ' Q0 0 ' + sparkR + ' 0 Q0 0 0 ' + sparkR + ' Q0 0 ' + (-sparkR) + ' 0 Q0 0 0 ' + (-sparkR) + 'Z',
            fill: '#fff'
        }, sparkG);

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
