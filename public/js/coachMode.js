/* Kỳ Sư Đồng Hành — Live Coach Mode (arrow-only)
 * mascot → vòng nút → mũi tên trên bàn cờ. Mũi tên giữ nguyên đến khi có nước mới.
 */
(function () {
    "use strict";
    if (!window.COACH_CONFIG) return;
    const CFG = window.COACH_CONFIG;

    const mascot = document.getElementById("coach-mascot");
    const actions = document.getElementById("coach-actions");
    const bubble = document.getElementById("coach-bubble");
    const bubbleBody = document.getElementById("coach-bubble-body");
    const bubbleFoot = document.getElementById("coach-bubble-foot");
    const badge = document.getElementById("coach-badge");
    if (!mascot || !bubble) return;

    /* ============ 1. Mở / đóng vòng nút ============ */
    mascot.addEventListener("click", () => {
        if (bubble.classList.contains("show")) {
            hideBubble();
        } else {
            actions.classList.toggle("open");
        }
    });

    document.addEventListener("click", (e) => {
        if (!document.getElementById("ai-coach").contains(e.target)) {
            actions.classList.remove("open");
        }
    });

    /* ============ 2. Nút hành động ============ */
    document.querySelectorAll(".coach-action-btn").forEach((btn) => {
        btn.addEventListener("click", () => handleIntent(btn.dataset.intent));
    });

    document
        .getElementById("coach-close")
        .addEventListener("click", hideBubble);

    /* ============ 3. Xử lý intent ============ */
    async function handleIntent(intent) {
        if (!CFG.isAuth) {
            openBubble(CFG.i18n.loginFirst, "warn", [
                {
                    label: "🔑 " + CFG.i18n.login,
                    cb: () => (location.href = CFG.loginUrl),
                },
            ]);
            return;
        }
        if (!CFG.isVip && CFG.remaining <= 0) {
            openBubble(CFG.i18n.upgradeVip, "vip", [
                {
                    label: "👑 " + CFG.i18n.upgrade,
                    cb: () => (location.href = CFG.vipUrl),
                },
            ]);
            return;
        }

        if (typeof game === "undefined" || typeof game.fen !== "function") {
            openBubble("⚠️ " + CFG.i18n.noBoard, "warn");
            return;
        }
        const fen = game.fen();
        if (!fen) return;

        actions.classList.remove("open");
        hideBubble();
        mascot.classList.add("thinking");

        try {
            const res = await fetch(CFG.apiUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": CFG.csrf,
                },
                body: JSON.stringify({ fen, intent }),
            });
            const data = await res.json();

            mascot.classList.remove("thinking");
            mascot.classList.add("happy");
            setTimeout(() => mascot.classList.remove("happy"), 700);

            if (!data.success) {
                if (data.require_login)
                    return openBubble(
                        data.reply || CFG.i18n.loginFirst,
                        "warn",
                    );
                if (data.require_vip)
                    return openBubble(
                        data.reply || CFG.i18n.upgradeVip,
                        "vip",
                        [
                            {
                                label: "👑 " + CFG.i18n.upgrade,
                                cb: () => (location.href = CFG.vipUrl),
                            },
                        ],
                    );
                return openBubble(data.reply || CFG.i18n.errNet, "warn");
            }

            // Cập nhật badge
            if (!CFG.isVip && typeof data.remaining === "number") {
                CFG.remaining = data.remaining;
                if (badge) badge.textContent = data.remaining;
            }

            // Chỉ vẽ mũi tên, không hiện chữ
            if (data.move) drawArrow(data.move);
            else clearArrow();
        } catch (err) {
            console.error(err);
            mascot.classList.remove("thinking");
            openBubble(CFG.i18n.errNet, "warn");
        }
    }

    /* ============ 4. Bubble (chỉ dùng cho cảnh báo) ============ */
    function openBubble(html, type = "warn", actionsList = []) {
        const icon = { warn: "⚠️", vip: "👑" }[type] || "⚠️";
        bubbleBody.innerHTML = `<div style="display:flex;gap:8px;align-items:flex-start;">
            <span style="font-size:20px;line-height:1.2;">${icon}</span>
            <div>${html}</div></div>`;

        bubbleFoot.innerHTML = "";
        actionsList.forEach((a) => {
            const b = document.createElement("button");
            b.textContent = a.label;
            b.addEventListener("click", a.cb);
            bubbleFoot.appendChild(b);
        });

        bubble.classList.add("show");
    }

    function hideBubble() {
        bubble.classList.remove("show");
    }

    /* ============ 5. SVG arrow overlay ============ */
    const SQUARE_W = 100,
        SQUARE_H = 100; // viewBox 900x1000
    const NUM_FILES = 9,
        NUM_RANKS = 10;

    function ensureOverlay() {
        const boardEl = document.getElementById("ban-co");
        if (!boardEl) return null;
        let ov = boardEl.querySelector(".board-coach-overlay");
        if (!ov) {
            ov = document.createElement("div");
            ov.className = "board-coach-overlay";
            ov.innerHTML = `<svg viewBox="0 0 900 1000" preserveAspectRatio="none">
                <defs>
                    <marker id="coach-arrow" viewBox="0 0 10 10" refX="8" refY="5"
                            markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                        <path d="M0,0 L10,5 L0,10 z" fill="#2e7d32"/>
                    </marker>
                </defs>
                <g id="coach-svg-lines"></g>
            </svg>`;
            boardEl.style.position = "relative";
            boardEl.appendChild(ov);
        }
        return ov;
    }

    function fileRankToXY(file, rank) {
        const cx = file * SQUARE_W + SQUARE_W / 2;
        const cy = (NUM_RANKS - 1 - rank) * SQUARE_H + SQUARE_H / 2;
        return [cx, cy];
    }

    function parseSquare(s) {
        if (s.length < 2) return null;
        const f = s.charCodeAt(0) - 97;
        const r = parseInt(s[1], 10);
        if (
            isNaN(f) ||
            isNaN(r) ||
            f < 0 ||
            f >= NUM_FILES ||
            r < 0 ||
            r >= NUM_RANKS
        )
            return null;
        return { file: f, rank: r };
    }

    function drawArrow(move) {
        const ov = ensureOverlay();
        if (!ov) return;
        const g = ov.querySelector("#coach-svg-lines");
        g.innerHTML = "";

        const from = parseSquare(move.slice(0, 2));
        const to = parseSquare(move.slice(2, 4));
        if (!from || !to) return;

        const [x1, y1] = fileRankToXY(from.file, from.rank);
        const [x2, y2] = fileRankToXY(to.file, to.rank);

        const dx = x2 - x1,
            dy = y2 - y1;
        const len = Math.hypot(dx, dy);
        if (!len) return;
        const ux = dx / len,
            uy = dy / len;
        const sx = x1 + ux * 30,
            sy = y1 + uy * 30;
        const ex = x2 - ux * 45,
            ey = y2 - uy * 45;

        const NS = "http://www.w3.org/2000/svg";

        const line = document.createElementNS(NS, "line");
        line.setAttribute("x1", sx);
        line.setAttribute("y1", sy);
        line.setAttribute("x2", ex);
        line.setAttribute("y2", ey);
        line.setAttribute("stroke", "#2e7d32");
        line.setAttribute("stroke-width", "8");
        line.setAttribute("stroke-linecap", "round");
        line.setAttribute("marker-end", "url(#coach-arrow)");
        line.setAttribute("opacity", "0.9");
        g.appendChild(line);

        const circle = document.createElementNS(NS, "circle");
        circle.setAttribute("cx", x1);
        circle.setAttribute("cy", y1);
        circle.setAttribute("r", "32");
        circle.setAttribute("fill", "none");
        circle.setAttribute("stroke", "#2e7d32");
        circle.setAttribute("stroke-width", "5");
        circle.setAttribute("stroke-dasharray", "8 6");
        g.appendChild(circle);
        // Không còn auto-xóa: mũi tên giữ nguyên.
    }

    function clearArrow() {
        const g = document.querySelector("#coach-svg-lines");
        if (g) g.innerHTML = "";
    }

    const boardEl = document.getElementById("ban-co");
    if (boardEl && window.ResizeObserver) {
        new ResizeObserver(() => ensureOverlay()).observe(boardEl);
    }
})();
