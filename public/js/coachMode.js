/* Kỳ Sư Đồng Hành — Live Coach Mode
 * Thay thế hoàn toàn aiChatWidget.
 * Tương tác: mascot → vòng nút → speech bubble + SVG arrow trên bàn cờ.
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

    let ttsEnabled = false;
    let bubbleTimer = null;
    let lastIntent = null;

    /* ============ 1. Mở / đóng vòng nút ============ */
    mascot.addEventListener("click", () => {
        if (bubble.classList.contains("show")) {
            hideBubble();
        } else {
            actions.classList.toggle("open");
            if (
                !actions.classList.contains("open") &&
                !bubble.classList.contains("show")
            ) {
                openBubble(CFG.i18n.greeting, "info");
                autoHide(5000);
            }
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

    /* ============ 3. TTS toggle ============ */
    document.getElementById("coach-tts").addEventListener("click", function () {
        ttsEnabled = !ttsEnabled;
        this.classList.toggle("active", ttsEnabled);
        if (!ttsEnabled) window.speechSynthesis?.cancel();
    });

    /* ============ 4. Xử lý intent ============ */
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

        // Lấy FEN hiện tại
        if (typeof game === "undefined" || typeof game.fen !== "function") {
            openBubble("⚠️ " + CFG.i18n.noBoard, "info");
            return;
        }
        const fen = game.fen();
        if (!fen) return;

        actions.classList.remove("open");
        lastIntent = intent;
        mascot.classList.add("thinking");
        openBubble(CFG.i18n.thinking, "thinking");

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

            // Vẽ mũi tên nếu có move
            if (data.move) drawArrow(data.move);
            else clearArrow();

            // Hiện nội dung
            openBubble(data.reply, intent, buildFollowups(intent));
            autoHide(intent === "eval" ? 9000 : 11000);
            speak(stripHtml(data.reply));
        } catch (err) {
            console.error(err);
            mascot.classList.remove("thinking");
            openBubble(CFG.i18n.errNet, "warn");
        }
    }

    /* ============ 5. Follow-up buttons ============ */
    function buildFollowups(intent) {
        const btns = [];
        if (intent === "hint")
            btns.push({
                label: "🛡️ " + CFG.i18n.askThreats,
                cb: () => handleIntent("threats"),
            });
        if (intent === "threats")
            btns.push({
                label: "💡 " + CFG.i18n.askHint,
                cb: () => handleIntent("hint"),
            });
        if (intent !== "eval")
            btns.push({
                label: "⚖️ " + CFG.i18n.evalBtn,
                cb: () => handleIntent("eval"),
            });
        return btns;
    }

    /* ============ 6. Bubble UI ============ */
    function openBubble(html, type = "info", actionsList = []) {
        if (bubbleTimer) {
            clearTimeout(bubbleTimer);
            bubbleTimer = null;
        }

        const icon =
            {
                hint: "💡",
                threats: "🛡️",
                eval: "⚖️",
                warn: "⚠️",
                vip: "👑",
                thinking: "💭",
            }[type] || "🎯";
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
        if (bubbleTimer) {
            clearTimeout(bubbleTimer);
            bubbleTimer = null;
        }
    }

    function autoHide(ms) {
        bubbleTimer = setTimeout(hideBubble, ms);
    }

    /* ============ 7. SVG arrow overlay ============ */
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
        const f = s.charCodeAt(0) - 97; // 'a'..'i' → 0..8
        const r = parseInt(s[1], 10); // '0'..'9'
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

        // move "h2e2" → from h2, to e2
        const from = parseSquare(move.slice(0, 2));
        const to = parseSquare(move.slice(2, 4));
        if (!from || !to) return;

        const [x1, y1] = fileRankToXY(from.file, from.rank);
        const [x2, y2] = fileRankToXY(to.file, to.rank);

        // Đường mũi tên (rút gọn 15% mỗi đầu để không đè lên quân)
        const dx = x2 - x1,
            dy = y2 - y1;
        const len = Math.hypot(dx, dy);
        const ux = dx / len,
            uy = dy / len;
        const sx = x1 + ux * 30,
            sy = y1 + uy * 30;
        const ex = x2 - ux * 45,
            ey = y2 - uy * 45;

        const line = document.createElementNS(
            "http://www.w3.org/2000/svg",
            "line",
        );
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

        // Vòng tròn highlight ô xuất phát
        const circle = document.createElementNS(
            "http://www.w3.org/2000/svg",
            "circle",
        );
        circle.setAttribute("cx", x1);
        circle.setAttribute("cy", y1);
        circle.setAttribute("r", "32");
        circle.setAttribute("fill", "none");
        circle.setAttribute("stroke", "#2e7d32");
        circle.setAttribute("stroke-width", "5");
        circle.setAttribute("stroke-dasharray", "8 6");
        g.appendChild(circle);

        // Auto-xóa sau 15s
        clearTimeout(drawArrow._t);
        drawArrow._t = setTimeout(clearArrow, 15000);
    }

    function clearArrow() {
        const g = document.querySelector("#coach-svg-lines");
        if (g) g.innerHTML = "";
    }

    // Theo dõi resize bàn cờ
    const boardEl = document.getElementById("ban-co");
    if (boardEl && window.ResizeObserver) {
        new ResizeObserver(() => {
            // viewBox tự scale, không cần làm gì — nhưng đảm bảo overlay tồn tại
            ensureOverlay();
        }).observe(boardEl);
    }

    /* ============ 8. TTS ============ */
    const TTS_LANGS = {
        vi: "vi-VN", en: "en-US", de: "de-DE", es: "es-ES", fr: "fr-FR",
        ja: "ja-JP", ko: "ko-KR", ru: "ru-RU", zh: "zh-CN",
    };
    function speak(text) {
        if (!ttsEnabled || !("speechSynthesis" in window) || !text) return;
        window.speechSynthesis.cancel();
        const u = new SpeechSynthesisUtterance(text);
        u.lang = TTS_LANGS[CFG.locale] || CFG.locale || "vi-VN";
        u.rate = 1.05;
        u.pitch = 1.05;
        window.speechSynthesis.speak(u);
    }
    function stripHtml(s) {
        const d = document.createElement("div");
        d.innerHTML = s;
        return d.textContent || "";
    }

    /* ============ 9. Lời chào lần đầu ============ */
    if (CFG.isAuth) {
        setTimeout(() => {
            openBubble(CFG.i18n.greeting, "info");
            autoHide(5000);
        }, 1200);
    }
})();
