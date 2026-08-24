@extends('layouts.admin')

@section('title', 'Quản lý Bài viết')

@section('content')
<div class="fade-up">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Danh sách Bài viết</h2>
            <p class="text-sm text-slate-500">Quản lý nội dung đa ngôn ngữ của hệ thống</p>
        </div>
        <a href="{{ route('admin.articles.create') }}"
           class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition shadow-sm shadow-indigo-200">
            <i class="fa-solid fa-plus"></i>
            Thêm bài viết
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50/50 text-slate-500 border-b border-slate-200/80">
                    <tr>
                        <th class="px-6 py-4 font-semibold">ID</th>
                        <th class="px-6 py-4 font-semibold">Tiêu đề (Bản dịch hiện tại)</th>
                        <th class="px-6 py-4 font-semibold">Trạng thái</th>
                        <th class="px-6 py-4 font-semibold">Lượt xem</th>
                        <th class="px-6 py-4 font-semibold text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($articles as $article)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 text-slate-600 font-medium">#{{ $article->id }}</td>
                        <td class="px-6 py-4 text-slate-800 font-medium">
                            {{ $article->title ?? '(Chưa có bản dịch cho '. strtoupper(app()->getLocale()) .')' }}
                        </td>
                        <td class="px-6 py-4">
                            @if($article->status == 'published')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-100 text-emerald-700">
                                    Xuất bản
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-600">
                                    Bản nháp
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-500">
                            {{ number_format($article->views) }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.articles.edit', $article->id) }}"
                                   class="h-8 w-8 rounded-lg flex items-center justify-center text-sky-600 bg-sky-50 hover:bg-sky-100 transition"
                                   title="Chỉnh sửa">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.articles.destroy', $article->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Bạn có chắc chắn muốn xoá bài viết này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="h-8 w-8 rounded-lg flex items-center justify-center text-rose-600 bg-rose-50 hover:bg-rose-100 transition"
                                            title="Xoá">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center gap-2">
                                <i class="fa-regular fa-folder-open text-3xl text-slate-300"></i>
                                <p>Chưa có bài viết nào.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($articles->hasPages())
        <div class="px-6 py-4 border-t border-slate-200/80 bg-slate-50/50">
            {{ $articles->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
