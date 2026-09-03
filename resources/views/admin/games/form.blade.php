@extends('layouts.admin')

@section('title', $game->exists ? 'Sửa ván cờ' : 'Thêm ván cờ')

@section('content')
    <div class="fade-up max-w-3xl">
        <a href="{{ route('admin.games.index') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-indigo-600 transition mb-5">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            Quay lại danh sách
        </a>

        <div class="bg-white rounded-2xl shadow-soft border border-slate-100 p-6 sm:p-8">
            <h2 class="text-xl font-extrabold text-slate-900 mb-1">
                {{ $game->exists ? 'Sửa ván cờ' : 'Thêm ván cờ mới' }}
            </h2>
            <p class="text-sm text-slate-500 mb-6">
                {{ $game->exists ? 'Cập nhật thông tin ván cờ #' . $game->id . '.' : 'Ván cờ sẽ xuất hiện trong thư viện công khai sau khi lưu.' }}
            </p>

            <form action="{{ $game->exists ? route('admin.games.update', $game) : route('admin.games.store') }}"
                  method="POST" class="space-y-5">
                @csrf
                @if ($game->exists)
                    @method('PUT')
                @endif

                <div>
                    <label for="title" class="block text-sm font-semibold text-slate-700 mb-1.5">Tiêu đề</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $game->title) }}"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400 @error('title') border-rose-300 @enderror"
                           placeholder="Vd: Ván đấu chung kết giải Xuân 2026">
                    @error('title')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-xs text-slate-400">
                        Đường dẫn (slug) sẽ tự sinh từ tiêu đề nếu để trống.
                        @if ($game->exists)
                            Slug hiện tại: <span class="font-mono">/{{ $game->slug }}</span>
                        @endif
                    </p>
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-slate-700 mb-1.5">Mô tả</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400 @error('description') border-rose-300 @enderror"
                              placeholder="Tóm tắt ngắn gọn về ván cờ (không bắt buộc)">{{ old('description', $game->description) }}</textarea>
                    @error('description')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="initial_fen" class="block text-sm font-semibold text-slate-700 mb-1.5">Bàn cờ ban đầu (FEN)</label>
                    <input type="text" name="initial_fen" id="initial_fen"
                           value="{{ old('initial_fen', $game->initial_fen) }}"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400 @error('initial_fen') border-rose-300 @enderror">
                    @error('initial_fen')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="moves" class="block text-sm font-semibold text-slate-700 mb-1.5">Nước đi (JSON)</label>
                    <textarea name="moves" id="moves" rows="6"
                              class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400 @error('moves') border-rose-300 @enderror"
                              placeholder='["h2e2", "h7e7", ...]'>{{ old('moves', $game->moves ? json_encode($game->moves, JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                    @error('moves')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-xs text-slate-400">Danh sách nước đi dạng mảng JSON. Có thể để trống.</p>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold shadow-lift hover:bg-indigo-500 transition">
                        <i class="fa-solid fa-floppy-disk"></i>
                        {{ $game->exists ? 'Cập nhật' : 'Lưu ván cờ' }}
                    </button>
                    <a href="{{ route('admin.games.index') }}"
                       class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                        Huỷ
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
