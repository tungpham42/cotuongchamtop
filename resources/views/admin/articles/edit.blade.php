@extends('layouts.admin')

@section('title', 'Chỉnh sửa Bài Viết #' . $article->id)

@section('content')
<div class="fade-up max-w-5xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-lg font-bold text-slate-800">Chỉnh sửa Bài Viết</h2>
        <a href="{{ route('admin.articles.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-800">
            <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại
        </a>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3.5 text-rose-800 text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.articles.update', $article->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Card: Thông tin chung -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 mb-6">
            <h3 class="text-sm font-bold text-slate-800 mb-4 uppercase tracking-wider">Thông tin chung</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Trạng thái</label>
                    <select name="status" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition">
                        <option value="published" {{ (old('status') ?? $article->status) == 'published' ? 'selected' : '' }}>Xuất bản (Published)</option>
                        <option value="draft" {{ (old('status') ?? $article->status) == 'draft' ? 'selected' : '' }}>Bản nháp (Draft)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Card: Bản dịch đa ngôn ngữ -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-8">
            <div class="border-b border-slate-200/80 px-2 pt-2 bg-slate-50/50 flex overflow-x-auto">
                @foreach($locales as $index => $code)
                    <button type="button"
                            onclick="switchTab('{{ $code }}')"
                            id="tab-btn-{{ $code }}"
                            class="tab-btn px-5 py-3 text-sm font-semibold whitespace-nowrap transition border-b-2 {{ $index === 0 ? 'text-indigo-600 border-indigo-600' : 'text-slate-500 border-transparent hover:text-slate-700' }}">
                        {{ strtoupper($code) }} {!! $code === 'vi' ? '<span class="text-rose-500">*</span>' : '' !!}
                    </button>
                @endforeach
            </div>

            <div class="p-6">
                @foreach($locales as $index => $code)
                    @php
                        // Tìm bản dịch hiện tại từ DB
                        $trans = $article->translations->firstWhere('locale', $code);
                    @endphp
                    <div id="content-{{ $code }}" class="tab-content {{ $index === 0 ? 'block' : 'hidden' }}">

                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tiêu đề ({{ strtoupper($code) }})</label>
                            <input type="text" name="translations[{{ $code }}][title]"
                                   value="{{ old("translations.$code.title", $trans->title ?? '') }}"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition"
                                   placeholder="Nhập tiêu đề bài viết...">
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Slug ({{ strtoupper($code) }})</label>
                            <input type="text" name="translations[{{ $code }}][slug]"
                                   value="{{ old("translations.$code.slug", $trans->slug ?? '') }}"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nội dung ({{ strtoupper($code) }})</label>
                            <textarea name="translations[{{ $code }}][content]" rows="8"
                                      class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition">{{ old("translations.$code.content", $trans->content ?? '') }}</textarea>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-sm shadow-indigo-200 transition">
                <i class="fa-solid fa-floppy-disk mr-2"></i> Cập nhật Bài Viết
            </button>
            <a href="{{ route('admin.articles.index') }}" class="bg-white border border-slate-200 text-slate-700 px-6 py-3 rounded-xl text-sm font-bold hover:bg-slate-50 transition">
                Huỷ bỏ
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function switchTab(locale) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('content-' + locale).classList.remove('hidden');

        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('text-indigo-600', 'border-indigo-600');
            el.classList.add('text-slate-500', 'border-transparent');
        });

        const activeBtn = document.getElementById('tab-btn-' + locale);
        activeBtn.classList.remove('text-slate-500', 'border-transparent');
        activeBtn.classList.add('text-indigo-600', 'border-indigo-600');
    }
</script>
@endpush
