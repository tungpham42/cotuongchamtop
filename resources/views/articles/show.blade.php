@extends('layouts.mainlayout')

@section('og_image', asset('img/news.jpg'))

@section('aboveContent')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card article-detail-card border-0">
                <div class="card-body p-4 p-md-5">
                    <!-- Nhãn chuyên mục -->
                    <span class="article-hero-eyebrow d-inline-flex mb-2">
                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l2.4 7.2H22l-6 4.6 2.3 7.2-6.3-4.5-6.3 4.5 2.3-7.2-6-4.6h7.6z"></path>
                        </svg>
                        {{ __('Tin tức') }}
                    </span>

                    <!-- Tiêu đề bài viết -->
                    <h1 class="article-detail-title fw-bold mb-3">{{ $article->title }}</h1>

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
                    </div>

                    <!-- Nội dung chính -->
                    <article class="article-body" style="font-size: 1.1rem; line-height: 1.7;">
                        {!! $article->content !!}
                    </article>

                    <!-- Nút quay lại -->
                    <div class="mt-5 pt-4" style="border-top: 1px solid rgba(255,215,0,0.15);">
                        @php
                            $indexRoute = app()->getLocale() === config('locales.default', 'vi')
                                        ? 'article.index'
                                        : app()->getLocale() . '.article.index';
                        @endphp
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
@endsection
