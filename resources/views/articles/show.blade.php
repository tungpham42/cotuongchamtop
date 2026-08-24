@extends('layouts.mainlayout')

@section('aboveContent')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <!-- Tiêu đề bài viết -->
                    <h1 class="fw-bold mb-3">{{ $article->title }}</h1>

                    <!-- Meta thông tin -->
                    <div class="d-flex align-items-center text-muted small mb-4 pb-3 border-bottom">
                        <div class="me-4 d-flex align-items-center">
                            <svg class="me-2" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>{{ $article->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <svg class="me-2" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <span>{{ number_format($article->views ?? 0) }} lượt xem</span>
                        </div>
                    </div>

                    <!-- Nội dung chính -->
                    <article class="article-content" style="font-size: 1.1rem; line-height: 1.7;">
                        {!! $article->content !!}
                    </article>

                    <!-- Nút quay lại -->
                    <div class="mt-5 pt-4 border-top">
                        @php
                            $indexRoute = app()->getLocale() === config('locales.default', 'vi')
                                        ? 'article.index'
                                        : app()->getLocale() . '.article.index';
                        @endphp
                        <a href="{{ route($indexRoute) }}" class="btn btn-outline-primary">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="me-1" style="vertical-align: text-top;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Quay lại danh sách
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
