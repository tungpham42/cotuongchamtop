@extends('layouts.mainlayout')

@section('og_image', $article->featured_image_url ?? asset('img/news.jpg'))

@section('aboveContent')

<!-- Thanh tiến trình đọc -->
<div class="reading-progress-track">
    <div class="reading-progress-bar" id="readingProgressBar"></div>
</div>

<div class="container my-5">
    @php
        $indexRoute = app()->getLocale() === config('locales.default', 'vi')
                    ? 'article.index'
                    : app()->getLocale() . '.article.index';
        $readMins = max(1, (int) ceil(mb_strlen(strip_tags($article->content)) / 800));
        $currentUrl = url()->current();
    @endphp

    <!-- Breadcrumb -->
    <nav class="article-breadcrumb mb-3" aria-label="breadcrumb">
        <a href="{{ url('/') }}">{{ __('Trang chủ') }}</a>
        <span class="sep">/</span>
        <a href="{{ route($indexRoute) }}">{{ __('Tin tức') }}</a>
        <span class="sep">/</span>
        <span class="current">{{ $article->title }}</span>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card article-detail-card border-0">
                <div class="card-body p-4 p-md-5">

                    @if($article->featured_image)
                        <!-- Ảnh đại diện dạng "cuộn chiếu" với tiêu đề phủ lên -->
                        <div class="article-detail-hero">
                            <img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}">
                            <div class="article-detail-hero-content">
                                <span class="article-seal article-detail-seal-wrap" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M12 2l2.4 7.2H22l-6 4.6 2.3 7.2-6.3-4.5-6.3 4.5 2.3-7.2-6-4.6h7.6z"/>
                                    </svg>
                                </span>
                                <h1 class="article-detail-title fw-bold">{{ $article->title }}</h1>
                            </div>
                        </div>
                    @else
                        <!-- Nhãn chuyên mục -->
                        <span class="article-hero-eyebrow d-inline-flex mb-2">
                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l2.4 7.2H22l-6 4.6 2.3 7.2-6.3-4.5-6.3 4.5 2.3-7.2-6-4.6h7.6z"></path>
                            </svg>
                            {{ __('Tin tức') }}
                        </span>
                        <h1 class="article-detail-title fw-bold mb-3">{{ $article->title }}</h1>
                    @endif

                    <!-- Meta thông tin -->
                    <div class="article-detail-meta d-flex flex-wrap align-items-center gap-3 mb-4 pb-3">
                        <span class="article-meta-pill">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            {{ $article->created_at->format('d/m/Y') }}
                        </span>
                        <span class="article-meta-pill">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            {{ number_format($article->views ?? 0) }} {{ __('lượt xem') }}
                        </span>
                        <span class="article-meta-pill">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.5C10.5 5 8 4.2 5 4.5v13c3-.3 5.5.5 7 2 1.5-1.5 4-2.3 7-2v-13c-3-.3-5.5.5-7 2z"></path>
                            </svg>
                            {{ $readMins }} {{ __('phút đọc') }}
                        </span>

                        <div class="article-share ms-lg-auto">
                            <span class="article-share-label d-none d-sm-inline">{{ __('Chia sẻ') }}</span>
                            <a class="article-share-btn" target="_blank" rel="noopener"
                               aria-label="Facebook"
                               href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($currentUrl) }}">
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 12a10 10 0 10-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.4h-1.2c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0022 12z"/></svg>
                            </a>
                            <a class="article-share-btn" target="_blank" rel="noopener"
                               aria-label="X (Twitter)"
                               href="https://twitter.com/intent/tweet?url={{ urlencode($currentUrl) }}&text={{ urlencode($article->title) }}">
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 2H22l-7.6 8.7L23 22h-6.9l-5.4-6.6L4.5 22H1.4l8.1-9.3L1 2h7l4.9 6z"/></svg>
                            </a>
                            <button type="button" class="article-share-btn" id="copyLinkBtn" aria-label="{{ __('Sao chép liên kết') }}" data-url="{{ $currentUrl }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757M10.81 15.312a4.5 4.5 0 01-1.242-7.244l4.5-4.5a4.5 4.5 0 016.364 6.364l-1.757 1.757"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Nội dung chính -->
                    <article class="article-body" style="font-size: 1.1rem; line-height: 1.7;">
                        {!! $article->content !!}
                    </article>

                    <!-- Dải hoa văn trang trí -->
                    <div class="ornament-divider" aria-hidden="true">
                        <span class="line"></span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l2.4 7.2H22l-6 4.6 2.3 7.2-6.3-4.5-6.3 4.5 2.3-7.2-6-4.6h7.6z"></path>
                        </svg>
                        <span class="line"></span>
                    </div>

                    <!-- Nút quay lại -->
                    <div>
                        <a href="{{ route($indexRoute) }}" class="btn btn-royal-outline">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="me-1" style="vertical-align: text-top;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            {{ __('Quay lại danh sách') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Nút cuộn lên đầu trang -->
<button type="button" class="back-to-top" id="backToTopBtn" aria-label="{{ __('Lên đầu trang') }}">
    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
    </svg>
</button>

<script>
(function () {
    var progressBar = document.getElementById('readingProgressBar');
    var backToTop = document.getElementById('backToTopBtn');
    var copyBtn = document.getElementById('copyLinkBtn');
    var copyIcon = copyBtn ? copyBtn.innerHTML : '';
    var checkIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';

    function onScroll() {
        var scrollTop = window.scrollY || document.documentElement.scrollTop;
        var docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        var pct = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
        if (progressBar) progressBar.style.width = pct + '%';
        if (backToTop) backToTop.classList.toggle('is-visible', scrollTop > 400);
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    if (backToTop) {
        backToTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            var url = copyBtn.getAttribute('data-url');
            var done = function () {
                copyBtn.innerHTML = checkIcon;
                copyBtn.classList.add('is-copied');
                setTimeout(function () {
                    copyBtn.innerHTML = copyIcon;
                    copyBtn.classList.remove('is-copied');
                }, 1500);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(done).catch(done);
            } else {
                done();
            }
        });
    }
})();
</script>
@endsection
