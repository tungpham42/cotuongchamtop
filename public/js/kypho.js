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

        // Bắt sự kiện xuất video 9:16 (Bao gồm Fix Mobile & iOS)
        if (videoBtn) {
            videoBtn.addEventListener("click", async function () {
                var L = window.locale || {};

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
            var L = window.locale || {};
            var originalIndex = state.currentIndex;
            var originalFen = state.fens[originalIndex];
            var isMobile = window.innerWidth < 768;

            var canvas = document.createElement("canvas");
            canvas.width = 720;
            canvas.height = 1280;

            // FIX 1: Chèn canvas ẩn vào DOM để tránh lỗi đen màn hình (Black Screen) trên iOS
            canvas.style.position = "fixed";
            canvas.style.top = "-9999px";
            canvas.style.visibility = "hidden";
            document.body.appendChild(canvas);

            var ctx = canvas.getContext("2d");
            var boardEl = document.getElementById("ban-co");
            var frames = [];

            // BƯỚC 1: Chụp ảnh và chuyển ngay sang định dạng Image để giải phóng RAM
            for (var i = 0; i < state.fens.length; i++) {
                state.board.position(state.fens[i], false);

                // Nghỉ một chút để DOM kịp render trên các thiết bị yếu
                await new Promise((resolve) =>
                    setTimeout(resolve, isMobile ? 250 : 150),
                );

                var boardCanvas = await html2canvas(boardEl, {
                    backgroundColor: null,
                    scale: isMobile ? 1.5 : 2, // Giảm scale trên mobile để chống crash RAM
                });

                // Chuyển Canvas thành Image data và xóa Canvas ngay lập tức
                var img = new Image();
                img.src = boardCanvas.toDataURL("image/png");
                await new Promise(function (resolve) {
                    img.onload = resolve;
                });

                var moveText =
                    i === 0
                        ? L.video_start || "Bắt đầu"
                        : formatMove(state.prettyMoves[i - 1]);

                frames.push({
                    boardImg: img,
                    text: moveText,
                    index: i,
                    width: boardCanvas.width,
                    height: boardCanvas.height,
                });

                // FIX 2: Giải phóng triệt để Canvas Memory Quota (tránh iOS crash)
                boardCanvas.width = 0;
                boardCanvas.height = 0;
                boardCanvas = null;
            }

            state.board.position(originalFen, false);
            setIndex(originalIndex);

            // BƯỚC 2: Cài đặt MediaRecorder
            var stream = canvas.captureStream(30);
            var mimeType = "video/webm";
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
                if (e.data && e.data.size > 0) chunks.push(e.data);
            };

            var recordingPromise = new Promise(function (resolve) {
                recorder.onstop = function () {
                    var blob = new Blob(chunks, { type: mimeType });
                    var ext = mimeType.includes("mp4") ? "mp4" : "webm";
                    var filename = "Ky_Pho_" + Date.now() + "." + ext;
                    var url = URL.createObjectURL(blob);

                    if (canvas.parentNode)
                        canvas.parentNode.removeChild(canvas);
                    resolve({
                        url: url,
                        blob: blob,
                        filename: filename,
                        mimeType: mimeType,
                    });
                };
            });

            recorder.start();

            // BƯỚC 3: Vẽ Frame theo thời gian thực
            var holdMs = 1500; // 1.5 giây mỗi nước

            for (var i = 0; i < frames.length; i++) {
                var f = frames[i];
                var startMoveTime = performance.now();

                // FIX 3: Dùng vòng lặp kiểm tra thời gian thay vì setTimeout tĩnh
                while (performance.now() - startMoveTime < holdMs) {
                    ctx.fillStyle = "#1e2024";
                    ctx.fillRect(0, 0, canvas.width, canvas.height);

                    ctx.fillStyle = "#ffffff";
                    ctx.font = "bold 50px sans-serif";
                    ctx.textAlign = "center";
                    ctx.fillText(
                        L.video_title || "Trận Đấu Cờ Tướng",
                        canvas.width / 2,
                        120,
                    );

                    var moveColor = f.index % 2 !== 0 ? "#ff6b6b" : "#cbd3da";
                    ctx.fillStyle = f.index === 0 ? "#ffffff" : moveColor;
                    ctx.font = "40px sans-serif";
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
                    var boardHeight = (f.height / f.width) * boardWidth;
                    ctx.drawImage(
                        f.boardImg,
                        padding,
                        (canvas.height - boardHeight) / 2,
                        boardWidth,
                        boardHeight,
                    );

                    ctx.fillStyle = "#888888";
                    ctx.font = "24px sans-serif";
                    ctx.fillText(
                        L.video_footer || "Tạo bởi nền tảng Cờ Tướng",
                        canvas.width / 2,
                        canvas.height - 100,
                    );

                    // Nhường luồng cho trình duyệt render, chống giật lag
                    await new Promise((resolve) =>
                        requestAnimationFrame(resolve),
                    );
                }
            }

            recorder.stop();
            var videoData = await recordingPromise;

            // BƯỚC 4: Fix lỗi bảo mật Download của iOS
            var file = new File([videoData.blob], videoData.filename, {
                type: videoData.mimeType,
            });
            var canShare =
                typeof navigator.canShare === "function" &&
                navigator.canShare({ files: [file] });

            var btns = {
                download: {
                    label:
                        '<i class="fad fa-download"></i> ' +
                        (L.video_download || "Tải xuống"),
                    className: "btn-danger",
                    callback: function () {
                        var a = document.createElement("a");
                        a.style.display = "none";
                        a.href = videoData.url;
                        a.download = videoData.filename;
                        document.body.appendChild(a);
                        a.click();
                        setTimeout(function () {
                            document.body.removeChild(a);
                            window.URL.revokeObjectURL(videoData.url);
                        }, 500);
                    },
                },
            };

            if (canShare) {
                btns.share = {
                    label:
                        '<i class="fad fa-share-alt"></i> ' +
                        (L.video_share || "Chia sẻ"),
                    className: "btn-dark",
                    callback: function () {
                        navigator
                            .share({
                                title: L.video_title || "Trận Đấu Cờ Tướng",
                                files: [file],
                            })
                            .catch(function (e) {
                                console.warn("Share cancel:", e);
                            });
                        return false;
                    },
                };
            }

            if (window.bootbox) {
                bootbox.dialog({
                    title: L.video_completed || "Thành công!",
                    message:
                        '<p class="text-center">' +
                        (L.video_success || "Video đã được tạo thành công.") +
                        "</p>",
                    centerVertical: true,
                    closeButton: true,
                    buttons: btns,
                });
            } else {
                btns.download.callback();
            }
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
