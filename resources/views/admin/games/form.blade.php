@extends('layouts.admin')

@section('title', $game->exists ? 'Cập nhật ván cờ' : 'Thêm ván cờ mới')

@section('content')
<div class="fade-up max-w-3xl mx-auto">
    <a href="{{ route('admin.games.index') }}"
       class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-indigo-600 transition mb-5">
        <i class="fa-solid fa-arrow-left text-xs"></i>
        Quay lại danh sách
    </a>

    <div class="bg-white rounded-2xl shadow-soft border border-slate-100 p-6 sm:p-8">
        <h2 class="text-xl font-extrabold text-slate-900 mb-1">
            {{ $game->exists ? 'Cập nhật ván cờ' : 'Thêm ván cờ mới' }}
        </h2>
        <p class="text-sm text-slate-500 mb-6">Chia sẻ thế cờ hoặc một trận đấu hay của bạn.</p>

        <form id="gameForm" action="{{ $game->exists ? route('admin.games.update', $game->slug) : route('admin.games.store') }}" method="POST" class="space-y-5">
            @csrf
            @if($game->exists)
                @method('PUT')
            @endif

            {{-- Tiêu đề --}}
            <div>
                <label for="title" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Tiêu đề ván cờ <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title" id="title" value="{{ old('title', $game->title) }}" required
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400 @error('title') border-rose-300 @enderror"
                    placeholder="VD: Cờ tàn thực chiến: Đơn Xe phá Sĩ Tượng toàn">
                @error('title')
                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Mô tả --}}
            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700 mb-1.5">Mô tả chi tiết</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400 @error('description') border-rose-300 @enderror"
                    placeholder="Vài dòng giới thiệu về bối cảnh hoặc điểm mấu chốt của ván cờ...">{{ old('description', $game->description) }}</textarea>
                @error('description')
                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Mã FEN --}}
            <div>
                <label for="initial_fen" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Mã FEN ban đầu <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="initial_fen" id="initial_fen" value="{{ old('initial_fen', $game->initial_fen) }}" required
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400 @error('initial_fen') border-rose-300 @enderror">
                @error('initial_fen')
                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nước đi (Moves) --}}
            <div>
                <label for="raw_moves" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Biên bản nước đi <span class="text-rose-500">*</span>
                </label>

                {{-- Textarea để nhập text thuần. --}}
                <textarea id="raw_moves" rows="5" name="raw_moves"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400 @error('moves') border-rose-300 @enderror"
                    placeholder="VD: C2=5 n8+7... hoặc 1. Cbe3 Nhg8...">{{ is_string(old('raw_moves')) ? old('raw_moves') : '' }}</textarea>

                <p class="mt-1.5 text-xs text-slate-400">Dán kỳ phổ dạng chuỗi WXF (C2=5 n8+7) hoặc Standard Algebraic (1. Cbe3 Nhg8). Hệ thống sẽ tự động format JSON khi lưu.</p>

                {{-- Input ẨN lưu JSON mảng [{from, to, raw}] gửi xuống backend --}}
                <input type="hidden" name="moves" id="hidden_moves" value="">

                @error('moves')
                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold shadow-lift hover:bg-indigo-500 transition">
                    <i class="fa-solid fa-floppy-disk"></i>
                    {{ $game->exists ? 'Lưu thay đổi' : 'Tạo ván cờ' }}
                </button>
                <a href="{{ route('admin.games.index') }}"
                   class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Huỷ
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Bổ sung thư viện xiangqi.js vào form để bắt tọa độ -->
<script src="{{ asset('js/xiangqi.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dbMoves = @json($game->moves) || [];
        const initialFenInput = document.getElementById('initial_fen');
        const rawMovesTextarea = document.getElementById('raw_moves');

        // ========================================================
        // HÀM 1: Dịch Tọa độ ({from, to}) sang WXF (VD: C2=5)
        // ========================================================
        function convertMoveToWXF(move, color) {
            let piece = move.piece.toUpperCase();
            if (piece === 'E' || piece === 'V') piece = 'B';
            if (piece === 'H') piece = 'N';

            const isRed = (color === 'w' || color === 'r');

            const fCode = move.from.charCodeAt(0);
            const fRank = parseInt(move.from.charAt(1));
            const fFile = isRed ? (10 - (fCode - 96)) : (fCode - 96);

            const tCode = move.to.charCodeAt(0);
            const tRank = parseInt(move.to.charAt(1));
            const tFile = isRed ? (10 - (tCode - 96)) : (tCode - 96);

            let action = '=';
            if (fRank !== tRank) {
                if (isRed) action = tRank > fRank ? '+' : '-';
                else action = tRank < fRank ? '+' : '-';
            }

            let target = tFile;
            if (action !== '=') {
                const isStep = ['R', 'C', 'P', 'K'].includes(piece);
                if (isStep) target = Math.abs(tRank - fRank);
            }

            return piece + fFile + action + target;
        }

        // ========================================================
        // HÀM 2: Dịch WXF (C2=5) sang Tọa độ ({from, to})
        // ========================================================
        function parseWXFToMove(userStr, game) {
            const str = userStr.trim().toLowerCase();
            const regex = /^([+-]?)([a-z])(\d)([=+\-.])(\d)$/;
            const match = str.match(regex);

            if (!match) return null;

            let uPiece = match[2];
            const fFile = parseInt(match[3]);
            let action = match[4];
            if (action === '.') action = '=';
            const target = parseInt(match[5]);

            if (uPiece === 'e' || uPiece === 'v' || uPiece === 't') uPiece = 'b';
            if (uPiece === 'h') uPiece = 'n';

            const color = game.turn();
            const isRed = (color === 'w' || color === 'r');
            const legalMoves = game.moves({ verbose: true });

            for (let move of legalMoves) {
                let mPiece = move.piece.toLowerCase();
                if (mPiece === 'e' || mPiece === 'v') mPiece = 'b';
                if (mPiece === 'h') mPiece = 'n';
                if (mPiece !== uPiece) continue;

                const mc = move.from.charCodeAt(0);
                const mr = parseInt(move.from.charAt(1));
                const mFromFile = isRed ? (10 - (mc - 96)) : (mc - 96);
                if (mFromFile !== fFile) continue;

                const tc = move.to.charCodeAt(0);
                const tr = parseInt(move.to.charAt(1));
                const mToFile = isRed ? (10 - (tc - 96)) : (tc - 96);

                let mAction = '=';
                if (mr !== tr) {
                    if (isRed) mAction = tr > mr ? '+' : '-';
                    else mAction = tr < mr ? '+' : '-';
                }
                if (mAction !== action) continue;

                let mTarget = mToFile;
                if (mAction !== '=') {
                    const isStep = ['r', 'c', 'p', 'k'].includes(mPiece);
                    if (isStep) mTarget = Math.abs(tr - mr);
                }
                if (mTarget !== target) continue;

                return move;
            }
            return null;
        }

        // ========================================================
        // HÀM 3: Dịch SAN (Cbe3, Nhg8, Cxe7+) sang Tọa độ ({from, to})
        // ========================================================
        function parseSANToMove(userStr, game) {
            let str = userStr.replace(/[+#]/g, '').trim();

            const regex = /^([a-zA-Z]?)([a-i]?)([1-9]|10)?([xX]?)([a-i])([1-9]|10)$/i;
            const match = str.match(regex);

            if (!match) return null;

            let uPiece = match[1].toLowerCase();
            if (uPiece === 'e' || uPiece === 'v' || uPiece === 't') uPiece = 'b';
            if (uPiece === 'h') uPiece = 'n';
            if (uPiece === '') uPiece = 'p';

            const fromFile = match[2].toLowerCase();
            const fromRank = match[3];
            const toFile = match[5].toLowerCase();
            const toRank = parseInt(match[6]) - 1;
            const targetSquare = toFile + toRank;

            const legalMoves = game.moves({ verbose: true });

            for (let move of legalMoves) {
                let mPiece = move.piece.toLowerCase();
                if (mPiece === 'e' || mPiece === 'v') mPiece = 'b';
                if (mPiece === 'h') mPiece = 'n';
                if (mPiece !== uPiece) continue;

                if (move.to !== targetSquare) continue;

                if (fromFile && move.from.charAt(0) !== fromFile) continue;
                if (fromRank && parseInt(move.from.charAt(1)) !== parseInt(fromRank) - 1) continue;

                return move;
            }
            return null;
        }

        // ========================================================
        // 1. KHI LOAD FORM: Khôi phục biên bản từ Database
        // ========================================================
        if (dbMoves.length > 0 && typeof dbMoves[0] === 'object') {
            try {
                // Chỉ khôi phục nếu textarea đang trống (tránh ghi đè input lỗi từ old() khi validation failed)
                if (!rawMovesTextarea.value.trim()) {
                    const initialFen = initialFenInput.value || 'start';
                    const tempGame = new Xiangqi(initialFen === 'start' ? undefined : initialFen);
                    let displayMoves = [];

                    dbMoves.forEach(move => {
                        const color = tempGame.turn();
                        const res = tempGame.move({ from: move.from, to: move.to });

                        if (res) {
                            // Ưu tiên trả về chính xác định dạng đã nhập (SAN hoặc WXF) nếu có tồn tại.
                            // Nếu không có (ví dụ: data cũ), tự động sinh mã WXF.
                            displayMoves.push(move.raw ? move.raw : convertMoveToWXF(res, color));
                        }
                    });

                    rawMovesTextarea.value = displayMoves.join(' ');
                }
            } catch (e) {
                console.error("Lỗi khôi phục biên bản:", e);
            }
        }

        // ========================================================
        // 2. KHI SUBMIT: Dịch và đóng gói dữ liệu
        // ========================================================
        document.getElementById('gameForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const rawInput = rawMovesTextarea.value.trim();
            const initialFen = initialFenInput.value || 'start';
            let movesArray = [];

            if (rawInput) {
                const rawMoves = rawInput.split(/[\s,\n]+/).filter(m => m.length > 0);
                try {
                    const game = new Xiangqi(initialFen === 'start' ? undefined : initialFen);

                    for (let i = 0; i < rawMoves.length; i++) {
                        const token = rawMoves[i];

                        // Bỏ qua các số thứ tự đánh dấu lượt đi (Ví dụ: "1.", "25.")
                        if (/^\d+\.$/.test(token)) continue;

                        let moveObj = null;

                        // Thử nghiệm 1: Parse với WXF
                        const wxfMove = parseWXFToMove(token, game);
                        if (wxfMove) {
                            moveObj = game.move({ from: wxfMove.from, to: wxfMove.to });
                        }

                        // Thử nghiệm 2: Parse với SAN
                        if (!moveObj) {
                            const sanMove = parseSANToMove(token, game);
                            if (sanMove) {
                                moveObj = game.move({ from: sanMove.from, to: sanMove.to });
                            }
                        }

                        // Thử nghiệm 3: Fallback tự parse của xiangqi.js
                        if (!moveObj) {
                            try { moveObj = game.move(token); } catch(err) {}
                        }

                        if (moveObj) {
                            // Quan trọng: Lưu trực tiếp `raw: token` vào JSON gửi xuống backend
                            // để ghi nhớ định dạng người dùng (WXF hay SAN)
                            movesArray.push({ from: moveObj.from, to: moveObj.to, raw: token });
                        } else {
                            alert(`Nước đi không hợp lệ tại: ${token} \nVui lòng kiểm tra lại tính hợp lệ của kỳ phổ.`);
                            return false;
                        }
                    }
                } catch (err) {
                    alert('Lỗi FEN hoặc định dạng. Vui lòng kiểm tra lại!');
                    return false;
                }
            }

            document.getElementById('hidden_moves').value = JSON.stringify(movesArray);
            this.submit();
        });
    });
</script>
@endsection
