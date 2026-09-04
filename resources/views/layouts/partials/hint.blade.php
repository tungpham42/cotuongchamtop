{{--
    layouts/partials/hint.blade.php

    "Hint" widget: shows the engine's suggested continuation for the
    current position, capped to a number of half-moves that depends on
    the AI level the player picked. Weaker levels get a longer look-ahead
    (more help), stronger levels get less, and Master gets none at all.

    The player is always Red in this game mode (see ai.blade.php's
    onDragStart, which blocks dragging any `b`-prefixed piece), and the
    whole point of this widget is to help the player beat the computer —
    so the suggested line always starts with a Red move, i.e. a move the
    player can actually play next. Between the player's own move landing
    and the AI's reply resolving, the board's FEN briefly has Black to
    move (see ai.blade.php's onDrop → setTimeout(makeBestMove, ...)); the
    hint button is disabled during that window (ai.blade.php's
    updateStatus) and fetchHint() below refuses to fire even if it's
    clicked anyway, so a hint request is never sent against a
    Black-to-move position and the first move shown is never the
    computer's.

        Level          value   hint cap (half-moves)
        Mới chơi       1       8
        Dễ             2       5
        Bình thường    3       3
        Khó            4       2
        Khó nhất       5       1
        Kiện tướng     8       0  (no hint UI is rendered)

    Moves are displayed using classical Kỳ Phổ notation (e.g. "Pháo nhị
    bình ngũ") instead of raw square coordinates. The piece names and
    move-type words are pulled from the global `locale` object defined
    in scripts.blade.php:

        piece_k / piece_a / piece_b / piece_n / piece_r / piece_c / piece_p
        move_advance / move_retreat / move_traverse

    Requires (already present on any page that includes ai.blade.php):
      - a global `game` (Xiangqi.js instance) and `board` (Xiangqiboard)
      - jQuery + bootbox (with the 'vi' locale added, see scripts.blade.php)
      - the global `locale` object from scripts.blade.php (must have run
        before the Hint button is clicked — it doesn't need to run before
        this partial's own <script> tag, just before the user interacts)
      - CSRF meta tag (see scripts.blade.php's $.ajaxSetup)
      - the `#ban-co .square-XX` DOM squares used elsewhere for highlighting

    Usage (e.g. from ai.blade.php's @section('belowContent'), near the
    resign/undo buttons):

        @include('layouts.partials.hint')

    No outer @if is needed at the call site — this partial hides itself
    automatically when the computed hint cap for $level is 0 (Master).
--}}
@php
    // Beginner=1, Easy=2, Normal=3, Hard=4, Hardest=5, Master=8.
    $hintCapsByLevel = [
        1 => 8, // Mới chơi
        2 => 5, // Dễ
        3 => 3, // Bình thường
        4 => 2, // Khó
        5 => 1,  // Khó nhất
        8 => 0,  // Kiện tướng — no hints
    ];
    $currentLevel = (int) ($level ?? 3);
    $hintCap = $hintCapsByLevel[$currentLevel] ?? 15;
@endphp

@if ($hintCap > 0)
    <div class="hint-widget mx-auto text-center my-1" id="hint-widget">
        <a data-step="6"
           data-intro="{{ __('Ấn vào đây để xem trước những nước đi máy gợi ý') }}"
           id="hint-btn"
           class="btn btn-dark btn-lg"
           data-toggle="tooltip" data-placement="top"
           title="{{ __('Xem trước tối đa') }} {{ $hintCap }} {{ __('nước đi') }}">
            <i class="fad fa-lightbulb-on"></i> {{ __('Gợi ý') }}
        </a>
    </div>

    <script>
        (function () {
            const HINT_CAP = {{ $hintCap }};
            let hintRequestInFlight = false;
            let hintHighlightTimer = null;
            let hintDialog = null;

            const STRAIGHT_MOVERS = ['r', 'c', 'p', 'k']; // xe, pháo, tốt, tướng: move in straight lines
            // mã (n), tượng (b), sĩ (a) always change both file and rank

            function clearHintHighlights() {
                $('#ban-co .square-2b8ce').removeClass('hint-from hint-to');
                if (hintHighlightTimer) {
                    clearTimeout(hintHighlightTimer);
                    hintHighlightTimer = null;
                }
            }

            function highlightHintMove(move) {
                clearHintHighlights();
                if (!move || move.length < 4) return;
                const from = move.substring(0, 2);
                const to = move.substring(2, 4);
                $('#ban-co .square-' + from).addClass('hint-from');
                $('#ban-co .square-' + to).addClass('hint-to');
                hintHighlightTimer = setTimeout(clearHintHighlights, 4000);
            }

            // ---- Kỳ Phổ notation helpers -------------------------------

            /** Parse a FEN's piece-placement field into { "file,rank": {type, color} }. */
            function parseFenBoard(fen) {
                const boardState = {};
                const rows = fen.split(' ')[0].split('/');
                rows.forEach(function (row, r) {
                    const rank = 9 - r; // FEN row 0 (top) is rank 9 (Black's back rank)
                    let file = 0;
                    for (const ch of row) {
                        if (/\d/.test(ch)) {
                            file += parseInt(ch, 10);
                        } else {
                            boardState[file + ',' + rank] = {
                                type: ch.toLowerCase(),
                                color: (ch === ch.toUpperCase()) ? 'r' : 'b'
                            };
                            file += 1;
                        }
                    }
                });
                return boardState;
            }

            function squareToFileRank(square) {
                return {
                    file: square.charCodeAt(0) - 'a'.charCodeAt(0),
                    rank: parseInt(square.substring(1), 10)
                };
            }

            // Red counts columns 9→1 from its own right (file a) to left (file i);
            // Black counts columns 1→9 the same visual direction, mirrored.
            function kyphoColumn(fileIndex, color) {
                return color === 'r' ? (9 - fileIndex) : (fileIndex + 1);
            }

            function pieceLabel(type) {
                const key = 'piece_' + type;
                return (typeof locale !== 'undefined' && locale[key]) ? locale[key] : type.toUpperCase();
            }

            function moveTypeLabel(key) {
                return (typeof locale !== 'undefined' && locale[key]) ? locale[key] : key;
            }

            /** Build a classical Kỳ Phổ string for one half-move, given the board *before* it. */
            function kyphoNotation(boardState, move) {
                const from = squareToFileRank(move.substring(0, 2));
                const to = squareToFileRank(move.substring(2, 4));
                const piece = boardState[from.file + ',' + from.rank];

                if (!piece) {
                    // Should not happen if boardState is kept in sync with the pv,
                    // but fall back to raw coordinates rather than break the UI.
                    return move.substring(0, 2).toUpperCase() + ' \u2192 ' + move.substring(2, 4).toUpperCase();
                }

                const sourceCol = kyphoColumn(from.file, piece.color);
                const destCol = kyphoColumn(to.file, piece.color);
                const movesForward = piece.color === 'r' ? (to.rank > from.rank) : (to.rank < from.rank);
                const sameFile = to.file === from.file;

                let typeKey, amount;
                if (STRAIGHT_MOVERS.includes(piece.type) && sameFile) {
                    typeKey = movesForward ? 'move_advance' : 'move_retreat';
                    amount = Math.abs(to.rank - from.rank);
                } else if (STRAIGHT_MOVERS.includes(piece.type) && !sameFile) {
                    typeKey = 'move_traverse';
                    amount = destCol;
                } else {
                    // Horse / elephant / advisor: always change file and rank together.
                    typeKey = movesForward ? 'move_advance' : 'move_retreat';
                    amount = destCol;
                }

                return pieceLabel(piece.type) + ' ' + sourceCol + ' ' + moveTypeLabel(typeKey) + ' ' + amount;
            }

            /** Mutate boardState in place to reflect one half-move having been played. */
            function applyMoveToBoard(boardState, move) {
                const from = squareToFileRank(move.substring(0, 2));
                const to = squareToFileRank(move.substring(2, 4));
                const fromKey = from.file + ',' + from.rank;
                const toKey = to.file + ',' + to.rank;
                const piece = boardState[fromKey];
                delete boardState[fromKey];
                if (piece) {
                    boardState[toKey] = piece;
                } else {
                    delete boardState[toKey];
                }
            }

            /** Text for one half-move: color + Kỳ Phổ notation, explicitly alternating */
            function moveLabel(boardState, move, index) {
                // The widget only triggers on Red's turn, so index 0 is always Red.
                const isRed = (index % 2 === 0);
                const colorLabel = isRed ? '{{ __("Đỏ") }}' : '{{ __("Đen") }}';
                return colorLabel + ': ' + kyphoNotation(boardState, move);
            }

            // --------------------------------------------------------------

            function showHintModal(moves, fen) {
                const bodyHtml = moves.length
                    ? '<ol class="hint-move-list mb-0 pl-3 text-left" id="hint-move-list"></ol>'
                    : '<p class="text-muted mb-0">{{ __("Không có gợi ý cho thế cờ hiện tại.") }}</p>';

                hintDialog = bootbox.dialog({
                    title: '<i class="fad fa-route"></i> {{ __("Nước đi gợi ý") }}',
                    message: bodyHtml,
                    locale: '{{ __("vi") }}',
                    centerVertical: true,
                    closeButton: true,
                    className: 'hint-modal',
                    buttons: {
                        close: {
                            label: '<i class="fas fa-times"></i> {{ __("Đóng") }}',
                            className: 'btn-lg btn-dark text-light'
                        }
                    }
                });

                // Reset board highlights once the dialog is dismissed,
                // whichever way (close button, backdrop click, Esc).
                hintDialog.on('hidden.bs.modal', function () {
                    clearHintHighlights();
                });

                if (moves.length) {
                    const $list = hintDialog.find('#hint-move-list');
                    const boardState = parseFenBoard(fen);

                    // Render all moves sequentially in a single list.
                    // It explicitly enforces the Red-first alternation on every line.
                    moves.forEach(function (move, index) {
                        const text = moveLabel(boardState, move, index);
                        applyMoveToBoard(boardState, move);

                        const $li = $('<li>');
                        const $moveSpan = $('<span>').addClass('hint-move-half').text(text).css('cursor', 'pointer');

                        $moveSpan.on('click', function () { highlightHintMove(move); });

                        $li.append($moveSpan);
                        $list.append($li);
                    });

                    // Immediately show the very next move so the player
                    // has something actionable as soon as the modal opens.
                    highlightHintMove(moves[0]);
                }
            }

            /**
             * A single "go depth N" call has no guarantee of returning a PV as
             * long as N — engines commonly cut the extracted line short (hash
             * cutoffs, mate found early, etc.), so requesting depth == HINT_CAP
             * routinely comes back with fewer moves than promised.
             *
             * Instead we stitch PVs together: analyze, take however many moves
             * came back, replay them on a scratch board to get the resulting
             * position, and analyze again from there for the remainder — until
             * we reach HINT_CAP, the game ends, or we run out of safety rounds.
             * Returns a Promise that always resolves (never rejects) with the
             * best move list we managed to collect.
             */
            function fetchPvChain(startFen, cap) {
                return new Promise(function (resolve) {
                    let collected = [];
                    let currentFen = startFen;
                    let round = 0;
                    const maxRounds = 6; // safety net against pathological repeated short PVs

                    function nextRound() {
                        round++;
                        const remaining = cap - collected.length;
                        if (remaining <= 0 || round > maxRounds) {
                            resolve(collected);
                            return;
                        }

                        // Depth per round only needs to cover what's still missing,
                        // plus a buffer since PV length isn't guaranteed to match depth.
                        const depth = Math.min(30, Math.max(12, remaining + 8));

                        $.ajax({
                            type: 'POST',
                            url: '{{ url('/api/xiangqi/analyze') }}',
                            contentType: 'application/json',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: JSON.stringify({ fen: currentFen, depth: depth }),
                            dataType: 'json'
                        }).done(function (data) {
                            const pv = (data && data.success && data.analysis && Array.isArray(data.analysis.pv))
                                ? data.analysis.pv
                                : [];

                            if (!pv.length) {
                                resolve(collected); // engine had nothing more to say
                                return;
                            }

                            const take = pv.slice(0, remaining);
                            collected = collected.concat(take);

                            // Advance a scratch instance so the next round's analyze
                            // call continues exactly where this round's PV left off.
                            const scratch = new Xiangqi();
                            scratch.load(currentFen);
                            let allApplied = true;
                            take.forEach(function (mv) {
                                const applied = scratch.move({
                                    from: mv.substring(0, 2),
                                    to: mv.substring(2, 4),
                                    promotion: 'q'
                                });
                                if (!applied) allApplied = false;
                            });
                            currentFen = scratch.fen();

                            if (collected.length >= cap || !allApplied || scratch.game_over()) {
                                resolve(collected);
                                return;
                            }
                            nextRound();
                        }).fail(function () {
                            resolve(collected);
                        });
                    }

                    nextRound();
                });
            }

            function fetchHint() {
                if (hintRequestInFlight) return;
                if (typeof game === 'undefined' || typeof game.fen !== 'function') return;
                if (typeof game.game_over === 'function' && game.game_over()) return;

                // This widget exists to help the player (always Red) pick
                // their own next move. Right after the player moves and
                // before the AI's reply lands, game.turn() is briefly 'b'
                // — an analyze call made in that window would suggest the
                // *computer's* upcoming move, not something the player can
                // act on. The button is disabled during that window (see
                // ai.blade.php's updateStatus), but guard here too in case
                // fetchHint() ever gets called from somewhere else.
                if (typeof game.turn === 'function' && game.turn() !== 'r') return;
                if (typeof isComputerThinking !== 'undefined' && isComputerThinking) return;

                hintRequestInFlight = true;
                const $btn = $('#hint-btn');
                const originalHtml = $btn.html();
                $btn.addClass('disabled').attr('aria-disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i> {{ __("Đang tính toán") }}...');

                const currentFen = game.fen();

                fetchPvChain(currentFen, HINT_CAP).then(function (moves) {
                    showHintModal(moves, currentFen);
                }).catch(function () {
                    showHintModal([], currentFen);
                }).then(function () {
                    hintRequestInFlight = false;
                    $btn.removeClass('disabled').attr('aria-disabled', false).html(originalHtml);
                });
            }

            $(document).on('click', '#hint-btn', function (e) {
                e.preventDefault();
                fetchHint();
            });

            const hintStyle = document.createElement('style');
            hintStyle.textContent = `
                .hint-move-list li { padding: 2px 0; }
                .hint-move-list .hint-move-half:hover { color: #ffd700; }
                .square-2b8ce.hint-from { background-color: #64b5f6 !important; opacity: 0.7; }
                .square-2b8ce.hint-to { background-color: #4caf50 !important; opacity: 0.7; }
            `;
            document.head.appendChild(hintStyle);
        })();
    </script>
@endif
