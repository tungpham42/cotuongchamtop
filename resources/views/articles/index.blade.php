@extends('layouts.mainlayout')

@section('og_image', asset('img/news.jpg'))

@section('aboveContent')
<div class="container my-5">

    <!-- Banner tiêu đề & tìm kiếm -->
    <div class="article-hero mb-5">
        <svg class="article-hero-motif" viewBox="0 0 100 100" fill="none" aria-hidden="true">
            <circle cx="50" cy="50" r="46" stroke="currentColor" stroke-width="1.5"/>
            <circle cx="50" cy="50" r="34" stroke="currentColor" stroke-width="1"/>
            <path d="M50 4 L54 46 L50 50 L46 46 Z" fill="currentColor"/>
            <path d="M50 96 L54 54 L50 50 L46 54 Z" fill="currentColor"/>
        </svg>
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
                <p class="article-hero-count">
                    @if(!empty($search))
                        {{ __('Kết quả cho') }} “<strong>{{ $search }}</strong>” &middot; <strong>{{ $articles->total() }}</strong> {{ __('bài viết') }}
                    @else
                        <strong>{{ $articles->total() }}</strong> {{ __('bài viết đang chờ bạn khám phá') }}
                    @endif
                </p>
            </div>
            <div class="col-lg-6">
                <form action="{{ url()->current() }}" method="GET" class="article-search">
                    <div class="input-group">
                        <input type="text" name="query" value="{{ $search ?? '' }}"
                               class="form-control"
                               aria-label="{{ __('Tìm kiếm bài viết') }}"
                               placeholder="{{ __('Tìm kiếm bài viết...') }}">
                        <button class="btn btn-royal" type="submit" aria-label="{{ __('Tìm kiếm') }}">
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
    <div class="row g-4">
        @forelse($articles as $article)
            @php
                $showRoute = app()->getLocale() === config('locales.default', 'vi')
                           ? 'article.show'
                           : app()->getLocale() . '.article.show';

                $isSpotlight = $loop->first && empty($search) && $articles->currentPage() === 1;
                $isHot  = ($article->views ?? 0) >= 500;
                $isNew  = $article->created_at && $article->created_at->diffInDays(now()) < 3;
                $readMins = max(1, (int) ceil(mb_strlen(strip_tags($article->content)) / 800));
                $delay = min($loop->index * 0.07, 0.56);
            @endphp

            @if($isSpotlight)
                {{-- ===== SPOTLIGHT: featured lead article ===== --}}
                <div class="col-12">
                    <div class="article-spotlight article-fade-in-up">
                        <div class="article-spotlight-img">
                            <span class="article-spotlight-badge">
                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l2.4 7.2H22l-6 4.6 2.3 7.2-6.3-4.5-6.3 4.5 2.3-7.2-6-4.6h7.6z"></path>
                                </svg>
                                {{ __('Nổi bật') }}
                            </span>
                            @if($article->featured_image)
                                <img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}">
                            @else
                                <div class="article-card-band"></div>
                            @endif
                        </div>
                        <div class="article-spotlight-body">
                            <div class="d-flex align-items-center gap-3">
                                <span class="article-seal" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M12 2l2.4 7.2H22l-6 4.6 2.3 7.2-6.3-4.5-6.3 4.5 2.3-7.2-6-4.6h7.6z"/>
                                    </svg>
                                </span>
                                <h2 class="article-spotlight-title">
                                    <a href="{{ route($showRoute, ['slug' => $article->slug]) }}">
                                        {{ $article->title }}
                                    </a>
                                </h2>
                            </div>
                            <p class="article-spotlight-excerpt">
                                {{ Str::limit(strip_tags($article->content), 220) }}
                            </p>
                            <div class="article-spotlight-meta">
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
                                <span class="article-meta-pill">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.5C10.5 5 8 4.2 5 4.5v13c3-.3 5.5.5 7 2 1.5-1.5 4-2.3 7-2v-13c-3-.3-5.5.5-7 2z"></path>
                                    </svg>
                                    {{ $readMins }} {{ __('phút đọc') }}
                                </span>
                            </div>
                            <div class="article-spotlight-cta">
                                <a href="{{ route($showRoute, ['slug' => $article->slug]) }}" class="btn btn-royal">
                                    {{ __('Đọc bài viết') }}
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="ms-1">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- ===== STANDARD CARD ===== --}}
                <div class="col-md-6 col-lg-4">
                    <div class="card article-card h-100 border-0 article-fade-in-up" style="animation-delay: {{ $delay }}s">
                        <div class="article-card-img-wrap">
                            @if($isNew)
                                <span class="article-card-ribbon article-card-ribbon-new">{{ __('Mới') }}</span>
                            @elseif($isHot)
                                <span class="article-card-ribbon article-card-ribbon-hot">{{ __('Hot') }}</span>
                            @endif
                            @if($article->featured_image)
                                <img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}"
                                     class="article-card-img w-100" style="aspect-ratio: 1200 / 630; object-fit: cover;">
                            @else
                                <div class="article-card-band"></div>
                            @endif
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title article-card-title fw-bold mb-3">
                                <a href="{{ route($showRoute, ['slug' => $article->slug]) }}" class="text-decoration-none" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    {{ $article->title }}
                                </a>
                            </h5>

                            <!-- Tóm tắt bài viết giới hạn 3 dòng -->
                            <p class="card-text article-card-excerpt flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ Str::limit(strip_tags($article->content), 120) }}
                            </p>

                            <a href="{{ route($showRoute, ['slug' => $article->slug]) }}" class="article-cta-link">
                                {{ __('Đọc tiếp') }}
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                </svg>
                            </a>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.5C10.5 5 8 4.2 5 4.5v13c3-.3 5.5.5 7 2 1.5-1.5 4-2.3 7-2v-13c-3-.3-5.5.5-7 2z"></path>
                                </svg>
                                {{ $readMins }} {{ __('phút') }}
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
            @endif
        @empty
            <div class="col-12">
                <div class="article-empty text-center py-5">
                    <svg class="mx-auto mb-3 d-block" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="fs-5 mb-0">
                        @if(!empty($search))
                            {{ __('Không tìm thấy bài viết nào phù hợp.') }}
                        @else
                            {{ __('Chưa có bài viết nào.') }}
                        @endif
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Phân trang -->
    <div class="d-flex justify-content-center mt-5">
        <div class="article-pagination-wrap">
            {{ $articles->links('vendor.pagination.vi') }}
        </div>
    </div>
</div>
@endsection
