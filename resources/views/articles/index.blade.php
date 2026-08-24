@extends('layouts.mainlayout')

@section('og_image', asset('img/news.jpg'))

@section('aboveContent')
<div class="container my-5">

    <!-- Banner tiêu đề & tìm kiếm -->
    <div class="article-hero mb-5">
        <div class="row align-items-center gy-3">
            <div class="col-lg-6">
                <span class="article-hero-eyebrow">
                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2l2.4 7.2H22l-6 4.6 2.3 7.2-6.3-4.5-6.3 4.5 2.3-7.2-6-4.6h7.6z"></path>
                    </svg>
                    {{ __('Tin tức') }}
                </span>
                <h1 class="article-hero-title fw-bold h2">{{ __('Danh sách bài viết') }}</h1>
                <p class="article-hero-subtitle mb-0">{{ __('Cập nhật những bài viết mới nhất') }}</p>
            </div>
            <div class="col-lg-6">
                <form action="{{ url()->current() }}" method="GET" class="article-search">
                    <div class="input-group">
                        <input type="text" name="query" value="{{ $search ?? '' }}"
                               class="form-control"
                               placeholder="{{ __('Tìm kiếm bài viết...') }}">
                        <button class="btn btn-royal" type="submit">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Lưới bài viết -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        @forelse($articles as $article)
            <div class="col mb-4">
                <div class="card article-card h-100 border-0">
                    <div class="article-card-band"></div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title article-card-title fw-bold mb-3">
                            @php
                                $showRoute = app()->getLocale() === config('locales.default', 'vi')
                                           ? 'article.show'
                                           : app()->getLocale() . '.article.show';
                            @endphp
                            <a href="{{ route($showRoute, ['slug' => $article->slug]) }}" class="text-decoration-none" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $article->title }}
                            </a>
                        </h5>

                        <!-- Tóm tắt bài viết giới hạn 3 dòng -->
                        <p class="card-text article-card-excerpt flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ Str::limit(strip_tags($article->content), 120) }}
                        </p>
                    </div>

                    <!-- Footer card hiển thị thông số -->
                    <div class="card-footer article-card-footer border-0 d-flex justify-content-between align-items-center py-3">
                        <span class="article-meta-pill">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            {{ $article->created_at->format('d/m/Y') }}
                        </span>
                        <span class="article-meta-pill">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            {{ number_format($article->views ?? 0) }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="article-empty text-center py-5">
                    <svg class="mx-auto mb-3 d-block" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="fs-5 mb-0">{{ __('Chưa có bài viết nào.') }}</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Phân trang -->
    <div class="d-flex justify-content-center mt-5">
        {{ $articles->links('vendor.pagination.vi') }}
    </div>
</div>
@endsection
