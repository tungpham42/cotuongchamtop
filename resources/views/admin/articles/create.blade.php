@extends('layouts.admin')

@section('title', 'Thêm Bài Viết Mới')

@push('styles')
<style>
    .wysiwyg-editor .ck-editor__editable_inline {
        min-height: 180px;
        font-size: 0.875rem;
        padding: 0 1rem;
    }
    .wysiwyg-editor .ck.ck-toolbar {
        border-top-left-radius: 0.75rem !important;
        border-top-right-radius: 0.75rem !important;
        background: #f8fafc !important;
        border-color: #e2e8f0 !important;
    }
    .wysiwyg-editor .ck.ck-editor__main > .ck-editor__editable {
        border-bottom-left-radius: 0.75rem !important;
        border-bottom-right-radius: 0.75rem !important;
        border-color: #e2e8f0 !important;
        background: #f8fafc !important;
    }
    .wysiwyg-editor .ck.ck-editor__main > .ck-editor__editable.ck-focused {
        border-color: #6366f1 !important;
        box-shadow: none !important;
    }

    /*
     * Style các thẻ HTML bên trong nội dung soạn thảo (.ck-content).
     * Vì vùng soạn thảo là contenteditable nên trình duyệt áp dụng các
     * quy tắc CSS này ngay lập tức (real time) mỗi khi người dùng gõ,
     * dán nội dung, hoặc dùng toolbar để chèn heading/list/table/...
     */
    .ck-content {
        line-height: 1.7;
        color: #334155;
    }
    .ck-content h1,
    .ck-content h2,
    .ck-content h3,
    .ck-content h4 {
        font-weight: 700;
        color: #1e293b;
        line-height: 1.3;
        margin-top: 1.25em;
        margin-bottom: 0.5em;
    }
    .ck-content h1 { font-size: 1.75rem !important; }
    .ck-content h2 { font-size: 1.5rem !important; }
    .ck-content h3 { font-size: 1.25rem !important; }
    .ck-content h4 { font-size: 1.1rem !important; }
    .ck-content p {
        margin-top: 0;
        margin-bottom: 1em;
    }
    .ck-content a {
        color: #6366f1;
        text-decoration: underline;
        text-underline-offset: 2px;
    }
    .ck-content a:hover {
        color: #4f46e5;
    }
    .ck-content strong { font-weight: 700; color: #1e293b; }
    .ck-content em { font-style: italic; }
    .ck-content ul,
    .ck-content ol {
        margin: 0 0 1em;
        padding-left: 1.5em;
    }
    .ck-content ul { list-style: disc; }
    .ck-content ol { list-style: decimal; }
    .ck-content li { margin-bottom: 0.35em; }
    .ck-content blockquote {
        margin: 1em 0;
        padding: 0.5em 1.25em;
        border-left: 4px solid #6366f1;
        background: #eef2ff;
        color: #475569;
        font-style: italic;
        border-radius: 0.5rem;
    }
    .ck-content img {
        max-width: 100%;
        height: auto;
        border-radius: 0.75rem;
        margin: 0.75em 0;
    }
    .ck-content figure.table {
        margin: 1em 0;
    }
    .ck-content table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }
    .ck-content table td,
    .ck-content table th {
        border: 1px solid #e2e8f0;
        padding: 0.5em 0.75em;
    }
    .ck-content table th {
        background: #f1f5f9;
        font-weight: 700;
        color: #1e293b;
    }
    .ck-content code {
        background: #f1f5f9;
        color: #db2777;
        padding: 0.15em 0.4em;
        border-radius: 0.35rem;
        font-size: 0.875em;
    }
    .ck-content hr {
        border: none;
        border-top: 1px solid #e2e8f0;
        margin: 1.5em 0;
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
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm mb-8">
            <div class="border-b border-slate-200/80 rounded-t-2xl px-2 pt-2 bg-slate-50/50 flex overflow-x-auto" id="langTabs">
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
                            <div id="editor-{{ $code }}" class="wysiwyg-editor">{!! old("translations.$code.content") !!}</div>
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
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    // Khởi tạo trình soạn thảo WYSIWYG (CKEditor 5) cho từng ngôn ngữ
    const ckEditors = {};
    const ckReady = [];

    document.querySelectorAll('.wysiwyg-editor').forEach(function (el) {
        const locale = el.id.replace('editor-', '');
        const initPromise = ClassicEditor
            .create(el, {
                toolbar: [
                    'heading', '|',
                    'bold', 'italic', 'link', '|',
                    'bulletedList', 'numberedList', '|',
                    'outdent', 'indent', '|',
                    'blockQuote', 'insertTable', 'mediaEmbed', '|',
                    'undo', 'redo'
                ]
            })
            .then(function (editor) {
                ckEditors[locale] = editor;

                // Đồng bộ nội dung HTML sang textarea ẩn theo thời gian thực
                // (mỗi khi nội dung trong CKEditor thay đổi), thay vì chỉ đồng bộ
                // lúc submit form.
                const hiddenInput = document.getElementById('content-input-' + locale);
                editor.model.document.on('change:data', function () {
                    hiddenInput.value = editor.getData();
                });
            })
            .catch(function (error) {
                console.error('CKEditor init error (' + locale + '):', error);
            });
        ckReady.push(initPromise);
    });

    // Trước khi submit, đồng bộ nội dung HTML từ CKEditor vào textarea ẩn.
    // Chặn submit mặc định cho tới khi mọi editor đã khởi tạo xong, để
    // tránh trường hợp người dùng bấm Lưu quá nhanh trước khi CKEditor sẵn sàng.
    const articleForm = document.querySelector('form');
    articleForm.addEventListener('submit', function (e) {
        if (ckReady.length && Object.keys(ckEditors).length < ckReady.length) {
            e.preventDefault();
            Promise.all(ckReady).then(function () {
                Object.keys(ckEditors).forEach(function (locale) {
                    document.getElementById('content-input-' + locale).value = ckEditors[locale].getData();
                });
                articleForm.submit();
            });
            return;
        }
        Object.keys(ckEditors).forEach(function (locale) {
            document.getElementById('content-input-' + locale).value = ckEditors[locale].getData();
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
