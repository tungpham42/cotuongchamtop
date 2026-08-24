@extends('layouts.admin')

@section('title', 'Thêm Bài Viết Mới')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
    .quill-editor .ql-container {
        min-height: 220px;
        font-size: 0.875rem;
    }
    .quill-editor .ql-toolbar {
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
        background: #f8fafc;
    }
    .quill-editor .ql-container {
        border-bottom-left-radius: 0.75rem;
        border-bottom-right-radius: 0.75rem;
    }
</style>
@endpush

@section('content')
<div class="fade-up max-w-5xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-lg font-bold text-slate-800">Thêm Bài Viết Mới</h2>
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

    <form action="{{ route('admin.articles.store') }}" method="POST">
        @csrf

        <!-- Card: Thông tin chung -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 mb-6">
            <h3 class="text-sm font-bold text-slate-800 mb-4 uppercase tracking-wider">Thông tin chung</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Trạng thái</label>
                    <select name="status" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition">
                        <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Xuất bản (Published)</option>
                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Bản nháp (Draft)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Card: Bản dịch đa ngôn ngữ -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-8">
            <div class="border-b border-slate-200/80 px-2 pt-2 bg-slate-50/50 flex overflow-x-auto" id="langTabs">
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
                    <div id="content-{{ $code }}" class="tab-content {{ $index === 0 ? 'block' : 'hidden' }}">

                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tiêu đề ({{ strtoupper($code) }})</label>
                            <input type="text" name="translations[{{ $code }}][title]"
                                   value="{{ old("translations.$code.title") }}"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition"
                                   placeholder="Nhập tiêu đề bài viết...">
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Slug ({{ strtoupper($code) }})
                                <span class="text-slate-400 font-normal ml-1">- Để trống sẽ tự động tạo từ tiêu đề</span>
                            </label>
                            <input type="text" name="translations[{{ $code }}][slug]"
                                   value="{{ old("translations.$code.slug") }}"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition"
                                   placeholder="bai-viet-moi...">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nội dung ({{ strtoupper($code) }})</label>
                            <div id="editor-{{ $code }}" class="quill-editor border border-slate-200 bg-slate-50">{!! old("translations.$code.content") !!}</div>
                            <textarea id="content-input-{{ $code }}" name="translations[{{ $code }}][content]" class="hidden">{{ old("translations.$code.content") }}</textarea>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-sm shadow-indigo-200 transition">
                <i class="fa-solid fa-floppy-disk mr-2"></i> Lưu Bài Viết
            </button>
            <a href="{{ route('admin.articles.index') }}" class="bg-white border border-slate-200 text-slate-700 px-6 py-3 rounded-xl text-sm font-bold hover:bg-slate-50 transition">
                Huỷ bỏ
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.js"></script>
<script>
    // Khởi tạo trình soạn thảo WYSIWYG (Quill) cho từng ngôn ngữ
    const quillEditors = {};
    const quillToolbarOptions = [
        [{ header: [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ color: [] }, { background: [] }],
        [{ list: 'ordered' }, { list: 'bullet' }],
        [{ align: [] }],
        ['blockquote', 'code-block'],
        ['link', 'image'],
        ['clean']
    ];

    document.querySelectorAll('.quill-editor').forEach(function (el) {
        const locale = el.id.replace('editor-', '');
        quillEditors[locale] = new Quill('#' + el.id, {
            theme: 'snow',
            modules: { toolbar: quillToolbarOptions }
        });
    });

    // Trước khi submit, đồng bộ nội dung HTML từ Quill vào textarea ẩn
    document.querySelector('form').addEventListener('submit', function () {
        Object.keys(quillEditors).forEach(function (locale) {
            document.getElementById('content-input-' + locale).value = quillEditors[locale].root.innerHTML;
        });
    });

    function switchTab(locale) {
        // Ẩn tất cả content
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        // Hiển thị content được chọn
        document.getElementById('content-' + locale).classList.remove('hidden');

        // Reset styles của tất cả nút tab
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('text-indigo-600', 'border-indigo-600');
            el.classList.add('text-slate-500', 'border-transparent');
        });

        // Active nút tab được chọn
        const activeBtn = document.getElementById('tab-btn-' + locale);
        activeBtn.classList.remove('text-slate-500', 'border-transparent');
        activeBtn.classList.add('text-indigo-600', 'border-indigo-600');
    }
</script>
@endpush
