(function (window, document) {
    "use strict";

    const config = window.__ROOM_REPLAY_CONFIG__ || null;
    if (!config || !config.code) {
        return;
    }

    const MAX_DEPENDENCY_ATTEMPTS = 40;
    const DEPENDENCY_WAIT_MS = 200;

    const state = {
        initialized: false,
        history: Array.isArray(config.history)
            ? config.history.map(cloneMove)
            : [],
        currentIndex: 0,
        pendingMove: null,
        fetching: false,
        replayGame: null,
        replayBoard: null,
        elements: {},
        lastFetchedFen: null,
    };

    function cloneMove(move) {
        if (!move || typeof move !== "object") {
            return {};
        }
        return JSON.parse(JSON.stringify(move));
    }

    function resolveGlobal(name) {
        if (typeof window[name] !== "undefined") {
            return window[name];
        }
        try {
            // eslint-disable-next-line no-new-func
            return Function(
                "return typeof " +
                    name +
                    ' !== "undefined" ? ' +
                    name +
                    " : undefined;"
            )();
        } catch (err) {
            return undefined;
        }
    }

    function getGameInstance() {
        return resolveGlobal("game") || null;
    }

    function getBoardInstance() {
        return resolveGlobal("board") || null;
    }

    function dependencyReadyCheck() {
        return (
            typeof window.jQuery !== "undefined" &&
            typeof window.Xiangqiboard === "function" &&
            typeof window.Xiangqi === "function"
        );
    }

    function waitForDependencies(attempt) {
        if (dependencyReadyCheck()) {
            start(window.jQuery);
            return;
        }
        if (attempt >= MAX_DEPENDENCY_ATTEMPTS) {
            return;
        }
        setTimeout(function () {
            waitForDependencies(attempt + 1);
        }, DEPENDENCY_WAIT_MS);
    }

    function waitForBoard($, attempt) {
        const game = getGameInstance();
        const board = getBoardInstance();
        if (game && board) {
            initialize($, game);
            return;
        }
        if (attempt >= MAX_DEPENDENCY_ATTEMPTS) {
            return;
        }
        setTimeout(function () {
            waitForBoard($, attempt + 1);
        }, DEPENDENCY_WAIT_MS);
    }

    function start($) {
        $(function () {
            waitForBoard($, 0);
        });
    }

    function initialize($, game) {
        if (state.initialized) {
            return;
        }
        state.initialized = true;
        injectStyles();
        prepareState();
        setupReplayBoard($);
        hookGameMove(game);
        hookAjax($);
        hookFenPolling($);
        updateStatusUI();
        watchGameStatus($);
    }

    function prepareState() {
        state.currentIndex = state.history.length;
        state.replayGame = new window.Xiangqi();
    }

    function injectStyles() {
        if (document.getElementById("room-replay-style")) {
            return;
        }
        const style = document.createElement("style");
        style.id = "room-replay-style";
        style.textContent = `
      #room-replay-panel {
        background-color: rgba(24, 26, 27, 0.65);
        border-radius: 12px;
        padding: 12px;
        margin-top: 16px;
      }
      #room-replay-panel .room-replay-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #f8f9fa;
      }

      #room-replay-controls .btn {
        min-width: 42px;
      }
      .room-replay-move-list {
        margin-top: 12px;
        max-height: 160px;
        overflow-y: auto;
        font-size: 0.9rem;
      }
      .room-replay-move {
        cursor: pointer;
        display: inline-block;
        margin: 2px 4px;
        padding: 2px 6px;
        border-radius: 4px;
        color: #f8f9fa;
        background-color: rgba(255, 255, 255, 0.08);
      }
      .room-replay-move.red {
        border: 1px solid rgba(220, 53, 69, 0.6);
      }
      .room-replay-move.black {
        border: 1px solid rgba(52, 58, 64, 0.6);
      }
      .room-replay-move.active {
        background-color: rgba(220, 53, 69, 0.65);
        font-weight: 600;
      }
      .room-replay-empty {
        color: rgba(248, 249, 250, 0.65);
        font-style: italic;
      }
    `;
        document.head.appendChild(style);
    }

    function getLangFromPath() {
        const path = window.location.pathname.toLowerCase();
        if (path.includes("/phong/")) return "vi"; // Vietnamese
        if (path.includes("/room/")) return "en"; // English
        if (path.includes("/rumu/")) return "ja"; // Japanese
        if (path.includes("/bang/")) return "ko"; // Korean
        if (path.includes("/fangjian/")) return "zh"; // Chinese
        return "vi"; // default fallback
    }

    function i18n(lang) {
        const dict = {
            vi: {
                bookTitle: "Kỳ phổ",
                hideText: "Ẩn Kỳ phổ",
                showText: "Hiện Kỳ phổ",
                firstTip: "Về đầu",
                prevTip: "Lùi 1 nước",
                nextTip: "Tiến 1 nước",
                lastTip: "Đến cuối",
                autoPlayTip: "Tự động phát",
                emptyText:
                    "Chưa có nước đi nào — kỳ phổ sẽ cập nhật sau khi bạn đi nước đầu tiên.",
                statusText: (cur, total) => `${cur} / ${total} nước`,
                noHistoryAlert: "Trận đấu này chưa có kỳ phổ để xem lại.",
            },
            en: {
                bookTitle: "Move List",
                hideText: "Hide Moves",
                showText: "Show Moves",
                firstTip: "Go to start",
                prevTip: "Previous move",
                nextTip: "Next move",
                lastTip: "Go to end",
                autoPlayTip: "Auto play",
                emptyText:
                    "No moves yet — the move list will appear after your first move.",
                statusText: (cur, total) => `${cur} / ${total} moves`,
                noHistoryAlert:
                    "This room has no move history available for replay.",
            },
            ja: {
                bookTitle: "棋譜",
                hideText: "棋譜を隠す",
                showText: "棋譜を表示",
                firstTip: "最初へ",
                prevTip: "前の手へ",
                nextTip: "次の手へ",
                lastTip: "最後へ",
                autoPlayTip: "自動再生",
                emptyText:
                    "まだ着手がありません。最初の手を指すと自動で表示されます。",
                statusText: (cur, total) => `${cur} / ${total} 手`,
                noHistoryAlert: "この部屋には再生可能な棋譜がありません。",
            },
            ko: {
                bookTitle: "기보",
                hideText: "기보 숨기기",
                showText: "기보 보기",
                firstTip: "처음으로",
                prevTip: "이전 수",
                nextTip: "다음 수",
                lastTip: "마지막으로",
                autoPlayTip: "자동 재생",
                emptyText:
                    "아직 착수가 없습니다. 첫 수를 두면 자동으로 표시됩니다.",
                statusText: (cur, total) => `${cur} / ${total} 수`,
                noHistoryAlert: "이 방에는 재생할 수 있는 기보가 없습니다.",
            },
            zh: {
                bookTitle: "棋谱",
                hideText: "隐藏棋谱",
                showText: "显示棋谱",
                firstTip: "回到开局",
                prevTip: "上一步",
                nextTip: "下一步",
                lastTip: "最后一步",
                autoPlayTip: "自动播放",
                emptyText: "暂无走子记录——下第一步后会自动显示。",
                statusText: (cur, total) => `${cur} / ${total} 步`,
                noHistoryAlert: "此房间暂无可回放的棋谱记录。",
            },
        };
        return dict[lang] || dict.vi;
    }

    function setupReplayBoard() {
        const lang = getLangFromPath();
        const t = i18n(lang);

        const hasHistory = state.history && state.history.length > 0;

        console.log(
            "🌐 setupReplayBoard detected language:",
            lang,
            "hasHistory:",
            hasHistory
        );
        if (!state.history || state.history.length === 0) {
            console.log("🌐 No move history available for replay.");
            const noHistoryPanel = $(`
        <section id="room-no-history" style="background-color: rgba(24, 26, 27, 0.65); border-radius: 12px; padding: 12px; margin-top: 16px; color: #f8f9fa;">
          <div class="room-replay-header d-flex justify-content-between align-items-center mb-2">
            <span><i class="fal fa-info-circle"></i> ${t.bookTitle}</span>
          </div>
          <div class="text-center py-3 text-muted">
            <i class="fal fa-info-circle mb-2" style="font-size: 1.8rem;"></i>
            <div>${t.noHistoryAlert}</div>
          </div>
        </section>
      `);
            noHistoryPanel.hide().insertAfter("#ban-co").fadeIn(400);
        }
        const panel = $(`
      <section id="room-replay-panel" style="">
        <div class="room-replay-header">
          <span><i class="fad fa-book-open"></i> ${t.bookTitle}</span>
          <span id="room-replay-status" class="small text-muted"></span>
        </div>
        <div id="room-replay-controls" class="btn-group btn-group-sm d-flex justify-content-center" role="group">
          <button type="button" class="btn btn-outline-light" data-replay-action="first" title="${t.firstTip}"><i class="fal fa-angle-double-left"></i></button>
          <button type="button" class="btn btn-outline-light" data-replay-action="prev" title="${t.prevTip}"><i class="fal fa-angle-left"></i></button>
          <button type="button" class="btn btn-outline-light" data-replay-action="next" title="${t.nextTip}"><i class="fal fa-angle-right"></i></button>
          <button type="button" class="btn btn-outline-light" data-replay-action="last" title="${t.lastTip}"><i class="fal fa-angle-double-right"></i></button>
          <button type="button" class="btn btn-outline-light" data-replay-action="play" title="${t.autoPlayTip}"><i class="fal fa-play"></i></button>
        </div>
        <div class="room-replay-move-list mt-2" id="room-replay-move-list"></div>
      </section>
    `);

        $("#ban-co").after(panel);

        const toggleButton = $(`
      <div class="text-center mt-2" id="toggle-replay-container">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="toggle-replay-panel">
          <i class="fal fa-book-open"></i> <span class="toggle-text">${t.hideText}</span>
        </button>
      </div>
    `);

        $("#ban-co").after(toggleButton);

        $("#toggle-replay-panel").on("click", function () {
            const panel = $("#room-replay-panel");
            const text = $(this).find(".toggle-text");
            if (panel.is(":visible")) {
                panel.slideUp(300);
                text.text(t.showText);
            } else {
                panel.slideDown(300);
                text.text(t.hideText);
            }
        });

        state.elements.status = $("#room-replay-status");
        state.elements.moveList = $("#room-replay-move-list");
        state.elements.playButton = panel.find('[data-replay-action="play"]');
        panel.find("[data-replay-action]").on("click", handleControlClick);

        if (!hasHistory) {
            state.elements.moveList.html(`
          <div class="room-replay-empty text-center py-3 text-muted">
            <small>${t.emptyText}</small>
          </div>
        `);
            updateStatusUI = function () {
                if (!state.elements.status) return;
                state.elements.status.text(t.statusText(0, 0));
            };
            updateStatusUI();
            return;
        }

        // ✅ Render moves immediately
        renderMoveList();
        setIndex(state.history.length, { silent: true });

        // Localized status update
        const originalUpdateStatusUI = updateStatusUI;
        updateStatusUI = function () {
            if (!state.elements.status) return;
            const total = state.history.length;
            const current = state.currentIndex;
            state.elements.status.text(t.statusText(current, total));
        };

        syncReplayBoard();
        updateControlButtons();
    }

    function handleControlClick(event) {
        event.preventDefault();
        const action = event.currentTarget.getAttribute("data-replay-action");
        switch (action) {
            case "first":
                setIndex(0);
                break;
            case "prev":
                setIndex(state.currentIndex - 1);
                break;
            case "next":
                setIndex(state.currentIndex + 1);
                break;
            case "last":
                setIndex(state.history.length);
                break;
            case "play":
                toggleAutoplay();
                break;
            default:
                break;
        }
    }

    let autoplayTimer = null;
    function toggleAutoplay() {
        if (!state.elements.playButton) {
            return;
        }
        if (autoplayTimer) {
            clearInterval(autoplayTimer);
            autoplayTimer = null;
            state.elements.playButton.html('<i class="fal fa-play"></i>');
            return;
        }
        state.elements.playButton.html('<i class="fal fa-pause"></i>');
        autoplayTimer = setInterval(function () {
            if (state.currentIndex >= state.history.length) {
                clearInterval(autoplayTimer);
                autoplayTimer = null;
                if (state.elements.playButton) {
                    state.elements.playButton.html(
                        '<i class="fal fa-play"></i>'
                    );
                }
                return;
            }
            setIndex(state.currentIndex + 1);
        }, 1200);
    }

    function setIndex(index, options) {
        const nextIndex = Math.max(0, Math.min(index, state.history.length));
        state.currentIndex = nextIndex;
        if (!options || !options.silent) {
            syncReplayBoard();
        }
        updateStatusUI();
        updateControlButtons();
        highlightActiveMove();
    }

    function syncReplayBoard() {
        if (!state.replayGame) {
            return;
        }
        const fen = getFenForIndex(state.currentIndex);
        try {
            state.replayGame.load(fen);

            // Sử dụng bàn cờ chính thay vì tạo bàn cờ riêng
            const mainBoard = getBoardInstance();
            if (mainBoard && typeof mainBoard.position === "function") {
                mainBoard.position(fen, true);
            }
        } catch (err) {
            // Ignore invalid FEN
        }
    }

    function getFenForIndex(index) {
        if (index <= 0) {
            return config.initialFen;
        }
        const move = state.history[index - 1] || {};
        return move.fen || config.initialFen;
    }

    function formatMoveLabel(move, idx) {
        if (!move) {
            return "";
        }

        // Chuyển đổi sang ký hiệu cờ tướng truyền thống
        const xiangqiNotation = convertToXiangqiNotation(move);
        const display =
            xiangqiNotation ||
            move.san ||
            move.iccs ||
            (move.from && move.to ? move.from + "-" + move.to : "");
        return display ? idx + 1 + ". " + display : idx + 1 + ".";
    }

    function convertToXiangqiNotation(move) {
        if (!move.from || !move.to) {
            return null;
        }

        // Bản đồ quân cờ viết tắt tiếng Việt
        const pieceSymbols = {
            r: "X",
            R: "X", // Xe
            h: "M",
            H: "M",
            n: "M",
            N: "M", // Mã
            b: "T",
            B: "T",
            e: "T",
            E: "T", // Tượng/Tịnh
            a: "S",
            A: "S", // Sĩ
            k: "G",
            K: "G", // Tướng (General)
            c: "P",
            C: "P", // Pháo
            p: "C",
            P: "C", // Chốt/Tốt
        };

        // Chuyển đổi vị trí từ ký hiệu sang số
        function parsePosition(pos) {
            const file = pos.charCodeAt(0) - 97; // a=0, b=1, etc
            const rank = parseInt(pos[1]) - 1;
            return { file, rank };
        }

        const fromPos = parsePosition(move.from);
        const toPos = parsePosition(move.to);

        const piece = pieceSymbols[move.piece] || move.piece || "";
        const isRed = move.color === "r";

        // Tính toán hướng di chuyển
        const fileDiff = toPos.file - fromPos.file;
        const rankDiff = toPos.rank - fromPos.rank;

        let directionSymbol = "";
        let target = "";

        if (rankDiff === 0) {
            // Di chuyển ngang (bình)
            directionSymbol = ".";
            target = 9 - toPos.file; // Cột đích
        } else if (fileDiff === 0) {
            // Di chuyển thẳng
            if ((isRed && rankDiff > 0) || (!isRed && rankDiff < 0)) {
                directionSymbol = "+"; // Tiến
            } else {
                directionSymbol = "-"; // Thoái
            }
            target = Math.abs(rankDiff); // Số ô di chuyển
        } else {
            // Di chuyển chéo (Tượng, Mã)
            if ((isRed && rankDiff > 0) || (!isRed && rankDiff < 0)) {
                directionSymbol = "+"; // Tiến
            } else {
                directionSymbol = "-"; // Thoái
            }
            target = 9 - toPos.file; // Cột đích
        }

        // Số cột xuất phát (1-9 từ trái qua phải)
        const fromColumn = 9 - fromPos.file;

        return `${piece}${fromColumn}${directionSymbol}${target}`;
    }

    function renderMoveList() {
        if (!state.elements.moveList) {
            return;
        }
        const listEl = state.elements.moveList;
        listEl.empty();

        if (!state.history.length) {
            const emptyMessage = $(`
        <div class="room-replay-empty text-center py-3">
          <div class="mb-2">
            <i class="fal fa-info-circle text-muted" style="font-size: 2rem;"></i>
          </div>
          <div class="text-muted">
            <strong>Kỳ phổ không khả dụng</strong><br>
            <small>Trận đấu này được tạo trước khi có tính năng ghi kỳ phổ.<br>
            Chỉ các trận đấu mới sẽ có thể xem lại kỳ phổ.</small>
          </div>
        </div>
      `);
            listEl.append(emptyMessage);
            return;
        }

        state.history.forEach(function (move, idx) {
            const label = formatMoveLabel(move, idx);
            const item = $("<span>")
                .addClass("room-replay-move")
                .addClass(move.color === "r" ? "red" : "black")
                .attr("data-move-index", idx + 1)
                .text(label);

            item.on("click", function () {
                if (autoplayTimer) {
                    toggleAutoplay();
                }
                setIndex(idx + 1);
            });

            listEl.append(item);
        });

        highlightActiveMove();
    }

    function highlightActiveMove() {
        if (!state.elements.moveList) {
            return;
        }
        state.elements.moveList.find(".room-replay-move").removeClass("active");
        if (state.currentIndex === 0) {
            return;
        }
        const active = state.elements.moveList.find(
            '[data-move-index="' + state.currentIndex + '"]'
        );
        active.addClass("active");
        ensureMoveVisible(active);
    }

    function updateControlButtons() {
        const panel = $("#room-replay-panel");
        if (!panel.length) {
            return;
        }

        const isAtStart = state.currentIndex === 0;
        const isAtEnd = state.currentIndex >= state.history.length;

        // Nút "Về đầu" và "Lùi 1 nước"
        const firstBtn = panel.find('[data-replay-action="first"]');
        const prevBtn = panel.find('[data-replay-action="prev"]');

        if (isAtStart) {
            firstBtn
                .addClass("disabled")
                .attr("disabled", true)
                .removeClass("btn-outline-light")
                .addClass("btn-outline-secondary");
            prevBtn
                .addClass("disabled")
                .attr("disabled", true)
                .removeClass("btn-outline-light")
                .addClass("btn-outline-secondary");
        } else {
            firstBtn
                .removeClass("disabled")
                .attr("disabled", false)
                .removeClass("btn-outline-secondary")
                .addClass("btn-outline-light");
            prevBtn
                .removeClass("disabled")
                .attr("disabled", false)
                .removeClass("btn-outline-secondary")
                .addClass("btn-outline-light");
        }

        // Nút "Tiến 1 nước" và "Đến cuối"
        const nextBtn = panel.find('[data-replay-action="next"]');
        const lastBtn = panel.find('[data-replay-action="last"]');

        if (isAtEnd) {
            nextBtn
                .addClass("disabled")
                .attr("disabled", true)
                .removeClass("btn-outline-light")
                .addClass("btn-outline-secondary");
            lastBtn
                .addClass("disabled")
                .attr("disabled", true)
                .removeClass("btn-outline-light")
                .addClass("btn-outline-secondary");
        } else {
            nextBtn
                .removeClass("disabled")
                .attr("disabled", false)
                .removeClass("btn-outline-secondary")
                .addClass("btn-outline-light");
            lastBtn
                .removeClass("disabled")
                .attr("disabled", false)
                .removeClass("btn-outline-secondary")
                .addClass("btn-outline-light");
        }
    }

    function ensureMoveVisible($element) {
        if (!$element || !$element.length) {
            return;
        }
        const container = state.elements.moveList.get(0);
        if (!container) {
            return;
        }
        const elementTop = $element.position().top;
        const elementBottom = elementTop + $element.outerHeight();
        if (elementTop < 0) {
            container.scrollTop += elementTop;
        } else if (elementBottom > container.clientHeight) {
            container.scrollTop += elementBottom - container.clientHeight;
        }
    }

    function updateStatusUI() {
        if (!state.elements.status) {
            return;
        }
        const total = state.history.length;
        const current = state.currentIndex;
        state.elements.status.text(current + " / " + total + " nước");
    }

    function hookGameMove(game) {
        if (!game || typeof game.move !== "function" || state.moveHooked) {
            return;
        }
        const originalMove = game.move.bind(game);
        game.move = function () {
            const fenBefore = game.fen();
            const result = originalMove.apply(game, arguments);
            if (result) {
                state.pendingMove = {
                    fen_before: fenBefore || config.initialFen,
                    move: cloneMove(result),
                };
            } else {
                state.pendingMove = null;
            }
            return result;
        };
        state.moveHooked = true;
    }

    function buildMoveEntry(game) {
        if (!state.pendingMove || !game) {
            return null;
        }

        const move = state.pendingMove.move || {};
        const fenBefore =
            state.pendingMove.fen_before ||
            (state.history.length
                ? state.history[state.history.length - 1].fen
                : config.initialFen);

        const verboseHistory = game.history({ verbose: true }) || [];
        const historyLength = verboseHistory.length;
        const lastMove = verboseHistory.length
            ? verboseHistory[verboseHistory.length - 1]
            : move;

        const entry = {
            ply: historyLength || state.history.length + 1,
            san: lastMove.san || move.san || null,
            iccs:
                lastMove.iccs ||
                move.iccs ||
                (move.from && move.to ? move.from + move.to : null),
            from: lastMove.from || move.from || null,
            to: lastMove.to || move.to || null,
            piece: lastMove.piece || move.piece || null,
            captured: lastMove.captured || move.captured || null,
            color: lastMove.color || move.color || null,
            flags: lastMove.flags || move.flags || null,
            fen_before: fenBefore,
            fen: game.fen(),
            created_at: new Date().toISOString(),
        };

        state.pendingMove = null;
        return entry;
    }

    function appendMove(entry, options) {
        if (!entry || !entry.fen) {
            return;
        }
        const last = state.history[state.history.length - 1];
        if (last && last.fen === entry.fen) {
            return;
        }
        const wasAtEnd = state.currentIndex === state.history.length;
        state.history.push(entry);

        if (!options || !options.skipRender) {
            renderMoveList();
        }

        if (!options || !options.skipStatus) {
            updateStatusUI();
        }

        if (!options || !options.silent) {
            if (wasAtEnd) {
                setIndex(state.history.length);
            } else {
                highlightActiveMove();
            }
        } else if (!options || !options.skipRender) {
            highlightActiveMove();
        }
    }

    function hookAjax($) {
        $.ajaxPrefilter(function (options, originalOptions) {
            if (!options || !options.url) {
                return;
            }
            const url = options.url;
            if (!/\/updateFEN\b/.test(url)) {
                return;
            }
            const game = getGameInstance();
            const entry = buildMoveEntry(game);
            if (!entry) {
                return;
            }

            const payload = JSON.stringify(entry);

            if (
                options.processData === false &&
                options.data instanceof FormData
            ) {
                options.data.append("move", payload);
            } else if (typeof options.data === "string") {
                options.data +=
                    (options.data.length ? "&" : "") +
                    "move=" +
                    encodeURIComponent(payload);
            } else {
                options.data = $.extend({}, options.data || {}, {
                    move: payload,
                });
            }

            const originalSuccess = options.success;
            options.success = function (data, status, xhr) {
                appendMove(entry);
                if (typeof originalSuccess === "function") {
                    originalSuccess.call(this, data, status, xhr);
                }
            };
        });
    }

    function hookFenPolling($) {
        $(document).ajaxComplete(function (event, xhr, settings) {
            if (!settings || !settings.url) {
                return;
            }
            if (!/\/readFEN\//.test(settings.url)) {
                return;
            }
            const fen =
                (xhr && xhr.responseText ? xhr.responseText.trim() : "") || "";
            if (!fen || fen === state.lastFetchedFen) {
                return;
            }
            state.lastFetchedFen = fen;
            const lastHistoryFen = state.history.length
                ? state.history[state.history.length - 1].fen
                : config.initialFen;
            if (fen !== lastHistoryFen) {
                requestLatestMoves();
            }
        });
    }

    function requestLatestMoves() {
        if (state.fetching) {
            return;
        }
        state.fetching = true;
        const after = state.history.length;
        const url = "/api/rooms/" + encodeURIComponent(config.code) + "/moves";
        window.jQuery
            .getJSON(url, { after: after })
            .done(function (data) {
                if (!data || !Array.isArray(data.moves) || !data.moves.length) {
                    return;
                }
                const atEnd = state.currentIndex === state.history.length;
                data.moves.forEach(function (move) {
                    appendMove(cloneMove(move), {
                        silent: true,
                        skipRender: true,
                        skipStatus: true,
                    });
                });
                updateStatusUI();
                renderMoveList();
                if (atEnd) {
                    setIndex(state.history.length);
                } else {
                    highlightActiveMove();
                }
            })
            .always(function () {
                state.fetching = false;
            });
    }

    function watchGameStatus($) {
        const lang = getLangFromPath();
        const t = i18n(lang);

        // Multi-language keywords for win/loss/draw detection
        const endKeywords = {
            vi: ["thắng", "thua", "hòa", "kết thúc"],
            en: ["win", "lose", "draw", "end"],
            ja: ["勝", "負", "引き分け", "終了"],
            ko: ["승리", "패배", "무승부", "끝"],
            zh: ["胜", "负", "平局", "结束"],
        };

        const currentEndWords = endKeywords[lang] || endKeywords.en;

        const checkGameStatus = function () {
            const gameOverVisible = $("#game-over:visible").length > 0;
            const gameResultVisible = $(".game-result:visible").length > 0;
            const statusText = ($("#game-status").text() || "").toLowerCase();

            const hasWinLoseText = currentEndWords.some((word) =>
                statusText.includes(word)
            );

            const hasHistory = state.history && state.history.length > 0;

            // Detect viewing context by URL
            const isViewingReplay =
                window.location.pathname.includes("/phong/") ||
                window.location.pathname.includes("/room/") ||
                window.location.pathname.includes("/bang/") ||
                window.location.pathname.includes("/rumu/") ||
                window.location.pathname.includes("/fangjian/");

            // Display replay panel if:
            // 1. Game has ended OR
            // 2. Viewing replay and there is move history
            const canShowReplay =
                gameOverVisible ||
                gameResultVisible ||
                hasWinLoseText ||
                (isViewingReplay && hasHistory);

            console.log("🌐 Replay status check:", {
                lang,
                gameOverVisible,
                gameResultVisible,
                hasWinLoseText,
                statusText,
                hasHistory,
                historyLength: state.history ? state.history.length : 0,
                isViewingReplay,
                currentURL: window.location.pathname,
                canShowReplay,
            });

            const panel = $("#room-replay-panel");
            const toggleTextEl = $("#toggle-replay-panel .toggle-text");
            const toggleContainer = $("#toggle-replay-container");

            if (!panel.length) return;

            if (canShowReplay) {
                panel.slideDown(300);
                toggleContainer.slideDown(300);
                if (toggleTextEl.length) toggleTextEl.text(t.hideText);
                console.log(`✅ Showing replay panel (${lang})`);
            } else {
                panel.slideUp(300);
                toggleContainer.slideUp(300);
                if (toggleTextEl.length) toggleTextEl.text(t.showText);
                console.log(`⏸ Hiding replay panel (${lang})`);
            }
        };

        // Initial check
        setTimeout(checkGameStatus, 500);

        // Observe game-status text
        if ($("#game-status").length) {
            const observer = new MutationObserver(checkGameStatus);
            observer.observe($("#game-status")[0], {
                childList: true,
                subtree: true,
                characterData: true,
            });
        }

        // Observe game-over visibility
        if ($("#game-over").length) {
            const observer = new MutationObserver(checkGameStatus);
            observer.observe($("#game-over")[0], {
                attributes: true,
                attributeFilter: ["style", "class"],
            });
        }

        // Poll every 3 seconds as backup
        setInterval(checkGameStatus, 3000);
    }

    waitForDependencies(0);
})(window, document);
