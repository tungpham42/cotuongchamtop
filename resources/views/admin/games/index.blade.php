@extends('layouts.admin')

@section('title', 'Games')

@section('content')
    <div class="fade-up flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900">Ván cờ</h2>
            <p class="text-sm text-slate-500 mt-1">Quản lý toàn bộ ván cờ hiển thị trong thư viện công khai.</p>
        </div>
        <a href="{{ route('admin.games.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold shadow-lift hover:bg-indigo-500 transition">
            <i class="fa-solid fa-plus"></i>
            Thêm ván cờ
        </a>
    </div>

    <div class="fade-up bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-3.5">Tiêu đề</th>
                        <th class="px-5 py-3.5">Tác giả</th>
                        <th class="px-5 py-3.5 text-center">Lượt xem</th>
                        <th class="px-5 py-3.5">Ngày tạo</th>
                        <th class="px-5 py-3.5 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($games as $game)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-800">{{ $game->title }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">/{{ $game->slug }}</div>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $game->user->name ?? '—' }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold">
                                    <i class="fa-regular fa-eye text-[11px]"></i>
                                    {{ number_format($game->views) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-500">
                                {{ $game->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.games.edit', $game) }}"
                                       class="h-9 w-9 inline-flex items-center justify-center rounded-lg bg-slate-100 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 transition"
                                       title="Sửa">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.games.destroy', $game) }}" method="POST"
                                          onsubmit="return confirm('Xoá ván cờ này? Hành động không thể hoàn tác.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="h-9 w-9 inline-flex items-center justify-center rounded-lg bg-slate-100 text-slate-600 hover:bg-rose-50 hover:text-rose-600 transition"
                                                title="Xoá">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                                <i class="fa-regular fa-chess-board text-2xl mb-2 block"></i>
                                Chưa có ván cờ nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($games->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $games->links() }}
            </div>
        @endif
    </div>
@endsection
