(function () {
    "use strict";

    if (typeof Xiangqi === "undefined") {
        return;
    }

    var PIECE_LABELS = {
        k: "T",
        a: "S",
        b: "tg",
        n: "M",
        r: "X",
        c: "P",
        p: "B",
    };

    function formatMove(move) {
        if (!move) {
            return "";
        }

        // Đọc biến ngôn ngữ từ object locale (fallback về tiếng Việt nếu chưa tải kịp)
        var L = window.locale || {};
        var PIECE_LABELS = {
            k: L.piece_k || "Tướng",
            a: L.piece_a || "Sĩ",
            b: L.piece_b || "Tượng",
            n: L.piece_n || "Mã",
            r: L.piece_r || "Xe",
            c: L.piece_c || "Pháo",
            p: L.piece_p || "Chốt",
        };

        var piece =
            PIECE_LABELS[move.piece] || String(move.piece || "").toUpperCase();
        var from = parseSquare(move.from);
        var to = parseSquare(move.to);

        if (!from || !to) {
            return piece;
        }

        var color = move.color || "r";
        var fromCol = columnFromFile(from.fileIndex, color);
        var toCol = columnFromFile(to.fileIndex, color);
        var isSameRank = from.rank === to.rank;
        var actionStr;

        if (isSameRank) {
            actionStr = L.move_traverse || "bình";
        } else {
            var forward =
                color === "r" ? to.rank > from.rank : to.rank < from.rank;
            actionStr = forward
                ? L.move_advance || "tấn"
                : L.move_retreat || "thoái";
        }

        var last;
        if (isSameRank) {
            last = toCol;
        } else {
            var usesTargetColumn =
                move.piece === "n" || move.piece === "b" || move.piece === "a";
            last = usesTargetColumn ? toCol : Math.abs(to.rank - from.rank);
        }

        // Nối chuỗi bằng khoảng trắng để hiển thị đầy đủ: VD "Xe 2 bình 5"
        return piece + " " + fromCol + " " + actionStr + " " + last;
    }

    function parseSquare(square) {
        if (!square || square.length < 2) {
            return null;
        }
        var fileChar = square.charAt(0);
        var rankChar = square.charAt(1);
        var fileIndex = "abcdefghi".indexOf(fileChar);
        var rank = parseInt(rankChar, 10);
        if (fileIndex === -1 || isNaN(rank)) {
            return null;
        }
        return {
            fileIndex: fileIndex,
            rank: rank,
        };
    }

    function columnFromFile(fileIndex, color) {
        return color === "r" ? 9 - fileIndex : fileIndex + 1;
    }

    function buildFromMoves(startFen, moves) {
        var tempGame;
        try {
            tempGame = new Xiangqi(startFen);
        } catch (error) {
            tempGame = new Xiangqi();
        }

        var fens = [tempGame.fen()];
        var prettyMoves = [];

        for (var i = 0; i < moves.length; i++) {
            var moveStr = moves[i];
            var move = tempGame.move(moveStr);
            if (!move) {
                continue;
            }
            prettyMoves.push(move);
            fens.push(tempGame.fen());
        }

        return {
            fens: fens,
            prettyMoves: prettyMoves,
        };
    }

    function createKyPho(options) {
        var listEl = document.getElementById("kypho-list");
        var prevBtn = document.getElementById("kypho-prev");
        var nextBtn = document.getElementById("kypho-next");
        var playBtn = document.getElementById("kypho-play");
        var pauseBtn = document.getElementById("kypho-pause");
        var copyBtn = document.getElementById("kypho-copy");
        var videoBtn = document.getElementById("kypho-video"); // Nút Video mới
        var panelEl = document.getElementById("kypho-panel");

        if (
            !listEl ||
            !prevBtn ||
            !nextBtn ||
            !playBtn ||
            !pauseBtn ||
            !panelEl
        ) {
            return null;
        }

        (function placePanelNearTheme() {
            var themeWrapper = document.querySelector(
                ".theme-selector-wrapper",
            );
            if (!themeWrapper || !themeWrapper.parentNode) {
                return;
            }
            if (panelEl.parentNode !== themeWrapper.parentNode) {
                themeWrapper.parentNode.insertBefore(
                    panelEl,
                    themeWrapper.nextSibling,
                );
            } else if (panelEl.previousElementSibling !== themeWrapper) {
                themeWrapper.parentNode.insertBefore(
                    panelEl,
                    themeWrapper.nextSibling,
                );
            }
        })();

        var state = {
            board: options.board,
            startFen: options.startFen,
            moves: [],
            fens: [],
            prettyMoves: [],
            currentIndex: 0,
            isPlaying: false,
            playTimer: null,
            playDelay: options.playDelay || 800,
            isLive: options.isLive,
            roomCode: options.roomCode || null,
        };

        function renderMoves() {
            listEl.innerHTML = "";
            if (!state.prettyMoves.length) {
                return;
            }
            for (var i = 0; i < state.prettyMoves.length; i++) {
                var moveNumber = Math.floor(i / 2) + 1;
                var moveText = formatMove(state.prettyMoves[i]);
                var moveEl = document.createElement("span");
                moveEl.className =
                    "kypho-move " + (i % 2 === 0 ? "red" : "black");
                moveEl.setAttribute("data-kypho-index", i);

                if (i % 2 === 0) {
                    var numberEl = document.createElement("span");
                    numberEl.className = "kypho-move-number";
                    numberEl.textContent = moveNumber + ".";
                    moveEl.appendChild(numberEl);
                    moveEl.appendChild(document.createTextNode(" "));
                }

                moveEl.appendChild(document.createTextNode(moveText));
                listEl.appendChild(moveEl);
            }
        }

        function highlightMove(index) {
            var moves = listEl.querySelectorAll(".kypho-move");
            for (var i = 0; i < moves.length; i++) {
                moves[i].classList.remove("kypho-current");
            }
            if (index < 0) {
                return;
            }
            var active = listEl.querySelector(
                '[data-kypho-index="' + index + '"]',
            );
            if (active) {
                active.classList.add("kypho-current");
            }
        }

        function stopPlayback() {
            if (state.playTimer) {
                clearInterval(state.playTimer);
                state.playTimer = null;
            }
            state.isPlaying = false;
        }

        // Hàm chuyển đổi mảng nước đi thành chuỗi văn bản chuẩn xác
        function generateKyPhoText() {
            var text = "";
            for (var i = 0; i < state.prettyMoves.length; i++) {
                var moveText = formatMove(state.prettyMoves[i]);
                if (i % 2 === 0) {
                    // Nước đi của Đỏ: Bắt đầu bằng số thứ tự
                    var moveNumber = Math.floor(i / 2) + 1;
                    text += moveNumber + ". " + moveText;
                } else {
                    // Nước đi của Đen: Cách bằng 2 khoảng trắng và xuống dòng
                    text += ", " + moveText + "\n";
                }
            }
            return text.trim();
        }

        // Bắt sự kiện click vào nút Copy
        if (copyBtn) {
            copyBtn.addEventListener("click", function () {
                var text = generateKyPhoText();
                if (!text) return;

                // Dùng textarea ảo để hỗ trợ tương thích tốt trên các trình duyệt cũ/mobile
                var textArea = document.createElement("textarea");
                textArea.value = text;

                // Ẩn textarea khỏi tầm nhìn
                textArea.style.position = "fixed";
                textArea.style.left = "-999999px";
                textArea.style.top = "-999999px";
                document.body.appendChild(textArea);

                textArea.focus();
                textArea.select();

                try {
                    document.execCommand("copy");

                    // Lấy chuỗi thông báo theo ngôn ngữ hiện tại
                    var L = window.locale || {};
                    var successMsg =
                        L.kypho_copied || "Đã sao chép kỳ phổ thành công!";

                    // Hiện thông báo thành công
                    if (window.bootbox) {
                        bootbox.alert({
                            message:
                                '<i class="fad fa-check-circle text-danger"></i> ' +
                                successMsg,
                            size: "small",
                            centerVertical: true,
                            closeButton: false,
                            backdrop: true,
                            buttons: {
                                ok: {
                                    className: "btn-danger",
                                },
                            },
                        });
                    } else {
                        alert(successMsg);
                    }
                } catch (err) {
                    console.error("Lỗi khi sao chép", err);
                }

                document.body.removeChild(textArea);
            });
        }

        function updateControls() {
            var live =
                typeof state.isLive === "function" ? state.isLive() : false;
            var hasMoves = state.moves.length > 0;
            var enabled = !live && hasMoves;

            if (prevBtn) prevBtn.disabled = !enabled || state.isPlaying;
            if (nextBtn) nextBtn.disabled = !enabled || state.isPlaying;
            if (playBtn) playBtn.disabled = !enabled || state.isPlaying;
            if (pauseBtn) pauseBtn.disabled = !enabled || !state.isPlaying;

            if (copyBtn) copyBtn.disabled = !hasMoves;
            if (videoBtn) videoBtn.disabled = !hasMoves; // Cập nhật trạng thái nút video

            if (live && state.isPlaying) {
                stopPlayback();
            }
        }

        // Logic xuất video 9:16 bằng Native MediaRecorder API
        if (videoBtn) {
            videoBtn.addEventListener("click", async function () {
                var L = window.locale || {}; // Lấy object ngôn ngữ

                if (videoBtn.disabled) return;

                if (typeof html2canvas === "undefined") {
                    alert(
                        L.video_loading_lib ||
                            "Đang tải thư viện tạo ảnh, vui lòng thử lại sau giây lát.",
                    );
                    return;
                }

                var originalHtml = videoBtn.innerHTML;
                videoBtn.innerHTML =
                    '<i class="fad fa-spinner fa-spin"></i> ' +
                    (L.video_processing || "Đang xử lý...");
                videoBtn.disabled = true;

                try {
                    await generateAndDownloadVideo();
                } catch (error) {
                    console.error("Lỗi tạo video:", error);
                    alert(L.video_error || "Có lỗi xảy ra khi tạo video.");
                }

                videoBtn.innerHTML = originalHtml;
                videoBtn.disabled = false;
            });
        }

        async function generateAndDownloadVideo() {
            var L = window.locale || {}; // Lấy object ngôn ngữ
            var originalIndex = state.currentIndex;
            var originalFen = state.fens[originalIndex];

            var canvas = document.createElement("canvas");
            canvas.width = 720;
            canvas.height = 1280;
            var ctx = canvas.getContext("2d");
            var boardEl = document.getElementById("ban-co");

            // BƯỚC 1: Chụp toàn bộ ảnh các nước đi trước để video không bị giật lag
            var frames = [];
            for (var i = 0; i < state.fens.length; i++) {
                state.board.position(state.fens[i], false);
                // Đợi render DOM
                await new Promise((resolve) => setTimeout(resolve, 150));

                var boardCanvas = await html2canvas(boardEl, {
                    backgroundColor: null,
                    scale: 2,
                });

                // Dịch chữ "Bắt đầu"
                var moveText =
                    i === 0
                        ? L.video_start || "Bắt đầu"
                        : formatMove(state.prettyMoves[i - 1]);

                frames.push({
                    board: boardCanvas,
                    text: moveText,
                    index: i,
                });
            }

            // Khôi phục lại trạng thái bàn cờ ban đầu
            state.board.position(originalFen, false);
            setIndex(originalIndex);

            // BƯỚC 2: Cài đặt Native MediaRecorder
            var stream = canvas.captureStream(30); // Capture ở 30 FPS
            var mimeType = "video/webm";

            // Ưu tiên MP4 trên Safari/iOS nếu có hỗ trợ, nếu không dùng WebM
            if (MediaRecorder.isTypeSupported("video/mp4")) {
                mimeType = "video/mp4";
            } else if (
                MediaRecorder.isTypeSupported("video/webm; codecs=vp9")
            ) {
                mimeType = "video/webm; codecs=vp9";
            }

            var recorder = new MediaRecorder(stream, { mimeType: mimeType });
            var chunks = [];

            recorder.ondataavailable = function (e) {
                if (e.data.size > 0) chunks.push(e.data);
            };

            var recordingPromise = new Promise(function (resolve) {
                recorder.onstop = function () {
                    var blob = new Blob(chunks, { type: mimeType });
                    var url = URL.createObjectURL(blob);
                    var a = document.createElement("a");
                    a.style.display = "none";
                    a.href = url;

                    var ext = mimeType.includes("mp4") ? "mp4" : "webm";
                    a.download = "video" + Date.now() + "." + ext;

                    document.body.appendChild(a);
                    a.click();
                    setTimeout(function () {
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);
                        resolve();
                    }, 100);
                };
            });

            // Bắt đầu ghi hình
            recorder.start();

            // BƯỚC 3: Vẽ frame lên canvas theo thời gian thực (1.5 giây mỗi nước đi)
            var fps = 30;
            var holdSeconds = 1.5;
            var totalFramesPerMove = fps * holdSeconds;

            for (var i = 0; i < frames.length; i++) {
                var f = frames[i];

                // Lặp lại việc vẽ để MediaRecorder bắt được chuyển động ở 30fps
                for (
                    var frameCount = 0;
                    frameCount < totalFramesPerMove;
                    frameCount++
                ) {
                    ctx.fillStyle = "#1e2024";
                    ctx.fillRect(0, 0, canvas.width, canvas.height);

                    ctx.fillStyle = "#ffffff";
                    ctx.font = "bold 50px sans-serif";
                    ctx.textAlign = "center";

                    // Dịch tiêu đề "Trận Đấu Cờ Tướng"
                    ctx.fillText(
                        L.video_title || "Trận Đấu Cờ Tướng",
                        canvas.width / 2,
                        120,
                    );

                    var moveColor = f.index % 2 !== 0 ? "#ff6b6b" : "#cbd3da";
                    ctx.fillStyle = f.index === 0 ? "#ffffff" : moveColor;
                    ctx.font = "40px sans-serif";

                    // Dịch chữ "Nước"
                    ctx.fillText(
                        f.index === 0
                            ? f.text
                            : (L.video_move || "Nước") +
                                  " " +
                                  f.index +
                                  ": " +
                                  f.text,
                        canvas.width / 2,
                        200,
                    );

                    var padding = 40;
                    var boardWidth = canvas.width - padding * 2;
                    var boardHeight =
                        (f.board.height / f.board.width) * boardWidth;
                    var startX = padding;
                    var startY = (canvas.height - boardHeight) / 2;
                    ctx.drawImage(
                        f.board,
                        startX,
                        startY,
                        boardWidth,
                        boardHeight,
                    );

                    ctx.fillStyle = "#888888";
                    ctx.font = "24px sans-serif";

                    // Dịch chữ footer
                    ctx.fillText(
                        L.video_footer || "Tạo bởi nền tảng Cờ Tướng Chấm Top",
                        canvas.width / 2,
                        canvas.height - 100,
                    );

                    // Nghỉ ~33ms để khớp với khung hình 30 FPS
                    await new Promise((resolve) =>
                        setTimeout(resolve, 1000 / fps),
                    );
                }
            }

            // Dừng ghi hình và tải file xuống
            recorder.stop();
            await recordingPromise;
        }

        // Helper function to play the move sound
        function playMoveSound() {
            var audio = document.getElementById("nuoc-co");
            if (audio) {
                audio.currentTime = 0; // Reset so rapid moves play from the start
                var playPromise = audio.play();
                if (playPromise !== undefined) {
                    playPromise.catch(function (error) {
                        // Suppress errors if the browser blocks autoplay before user interaction
                        console.warn("Audio blocked by browser policy:", error);
                    });
                }
            }
        }

        function setIndex(index) {
            if (!state.fens.length) {
                return;
            }
            if (index < 0) {
                index = 0;
            }
            if (index > state.fens.length - 1) {
                index = state.fens.length - 1;
            }
            state.currentIndex = index;
            if (state.board) {
                state.board.position(state.fens[state.currentIndex], true);
            }
            highlightMove(state.currentIndex - 1);
        }

        function stepNext() {
            if (state.currentIndex >= state.fens.length - 1) {
                stopPlayback();
                updateControls();
                return;
            }
            setIndex(state.currentIndex + 1);
            playMoveSound();
        }

        function stepPrev() {
            if (state.currentIndex > 0) {
                setIndex(state.currentIndex - 1);
                playMoveSound();
            }
        }

        function startPlayback() {
            var live =
                typeof state.isLive === "function" ? state.isLive() : false;
            if (live || !state.moves.length) {
                return;
            }
            if (state.currentIndex >= state.fens.length - 1) {
                setIndex(0);
            }
            state.isPlaying = true;
            updateControls();
            state.playTimer = setInterval(function () {
                stepNext();
            }, state.playDelay);
        }

        prevBtn.addEventListener("click", function () {
            stopPlayback();
            stepPrev();
            updateControls();
        });

        nextBtn.addEventListener("click", function () {
            stopPlayback();
            stepNext();
            updateControls();
        });

        playBtn.addEventListener("click", function () {
            playMoveSound();
            startPlayback();
        });

        pauseBtn.addEventListener("click", function () {
            playMoveSound();
            stopPlayback();
            updateControls();
        });

        function setMoves(moves) {
            state.moves = moves.slice();
            var built = buildFromMoves(state.startFen, state.moves);
            state.fens = built.fens;
            state.prettyMoves = built.prettyMoves;
            renderMoves();
            if (!state.isPlaying) {
                setIndex(state.fens.length - 1);
            }
            updateControls();
        }

        function recordMove(moveInput) {
            if (!moveInput) {
                return;
            }
            var moveStr =
                typeof moveInput === "string" ? moveInput : moveInput.iccs;
            if (!moveStr) {
                return;
            }
            var last = state.moves[state.moves.length - 1];
            if (last === moveStr) {
                return;
            }
            var nextMoves = state.moves.slice();
            nextMoves.push(moveStr);
            setMoves(nextMoves);
        }

        function syncMoves(url) {
            if (!url) {
                return;
            }
            fetch(url, { credentials: "same-origin" })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error("Failed to fetch moves");
                    }
                    return response.json();
                })
                .then(function (data) {
                    if (!Array.isArray(data)) {
                        return;
                    }
                    if (
                        data.length === state.moves.length &&
                        data[data.length - 1] ===
                            state.moves[state.moves.length - 1]
                    ) {
                        updateControls();
                        return;
                    }
                    setMoves(data);
                })
                .catch(function () {
                    updateControls();
                });
        }

        updateControls();

        return {
            setMoves: setMoves,
            recordMove: recordMove,
            syncMoves: syncMoves,
            setIndex: setIndex,
            updateControls: updateControls,
            stopPlayback: stopPlayback,
        };
    }

    window.KyPho = {
        initLocal: function (options) {
            return createKyPho(options);
        },
        initRoom: function (options) {
            return createKyPho(options);
        },
    };
})();
