{{--
    Carousel danh sách bài viết (Articles Carousel)

    Cách dùng:
        @include('common.articleCarousel', ['articles' => $articles])

    Props:
        $articles      Collection|LengthAwarePaginator các Article
                        (mỗi item cần: ->title, ->featured_image_url, ->views,
                         ->created_at, ->translation->slug — được cung cấp bởi
                         Article model + Translatable trait).
        $carouselTitle string|null  Tiêu đề section (mặc định: 'Bài viết mới nhất')
        $seeAllRoute   string|null  Tên route "Xem tất cả" (mặc định: 'articles.index')

    Lưu ý: file này chỉ include 1 lần / trang vì có JS khởi tạo scroll bên dưới.
    Nếu dùng nhiều carousel trên cùng 1 trang, đổi id 'article-carousel'
    thành dynamic id (ví dụ $carouselId) để tránh đụng nhau.
--}}

@php
    $carouselTitle = $carouselTitle ?? __('Bài viết mới nhất');
    $seeAllRoute = $seeAllRoute ?? 'articles.index';
@endphp

@if (isset($articles) && $articles->count() > 0)
    <section class="article-carousel-section">
        <style>
            .article-carousel-section {
                padding: 20px 0;
            }
            .article-carousel-section .ac-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 15px;
            }
            .article-carousel-section .ac-title {
                font-size: 1.4rem;
                font-weight: 700;
                color: #fff;
                margin: 0;
            }
            .article-carousel-section .ac-see-all {
                font-size: 0.9rem;
                color: #ff9800;
                text-decoration: none;
                white-space: nowrap;
            }
            .article-carousel-section .ac-see-all:hover {
                color: #ffb74d;
                text-decoration: underline;
            }

            .article-carousel-wrap {
                position: relative;
            }

            .article-carousel-track {
                display: flex;
                gap: 15px;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                scroll-behavior: smooth;
                padding-bottom: 6px;
                -webkit-overflow-scrolling: touch;
            }
            .article-carousel-track::-webkit-scrollbar { height: 8px; }
            .article-carousel-track::-webkit-scrollbar-track { background: #121418; border-radius: 4px; }
            .article-carousel-track::-webkit-scrollbar-thumb { background: #3a3f4c; border-radius: 4px; }
            .article-carousel-track::-webkit-scrollbar-thumb:hover { background: #505769; }

            .article-card {
                flex: 0 0 auto;
                width: 270px;
                scroll-snap-align: start;
                background-color: #252a36;
                border-radius: 8px;
                overflow: hidden;
                text-decoration: none;
                display: block;
                transition: transform .15s ease, box-shadow .15s ease;
                border: 1px solid #2f3542;
            }
            .article-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 6px 18px rgba(0, 0, 0, .35);
                text-decoration: none;
            }
            .article-card .ac-thumb {
                width: 100%;
                height: 150px;
                object-fit: cover;
                background-color: #1b1e26;
                display: block;
            }
            .article-card .ac-thumb-placeholder {
                width: 100%;
                height: 150px;
                background-color: #1b1e26;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #3a3f4c;
                font-size: 2rem;
            }
            .article-card .ac-body {
                padding: 12px 14px 14px;
            }
            .article-card .ac-headline {
                font-size: 0.98rem;
                font-weight: 600;
                color: #fff;
                line-height: 1.35;
                margin: 0 0 8px;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            .article-card .ac-meta {
                font-size: 0.78rem;
                color: #8a90a2;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .article-card .ac-meta i {
                margin-right: 3px;
            }

            .article-carousel-nav {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                width: 34px;
                height: 34px;
                border-radius: 50%;
                background-color: rgba(37, 42, 54, .9);
                border: 1px solid #3a3f4c;
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                z-index: 2;
                opacity: .95;
            }
            .article-carousel-nav:hover { background-color: #3a3f4c; }
            .article-carousel-nav.ac-prev { left: -12px; }
            .article-carousel-nav.ac-next { right: -12px; }

            @media (max-width: 576px) {
                .article-carousel-nav { display: none; }
                .article-card { width: 220px; }
            }
        </style>

        <div class="container">
            <div class="ac-header">
                <h2 class="ac-title">{{ $carouselTitle }}</h2>
                @if (\Illuminate\Support\Facades\Route::has($seeAllRoute))
                    <a href="{{ route($seeAllRoute) }}" class="ac-see-all">
                        {{ __('Xem tất cả') }} <i class="fas fa-angle-right"></i>
                    </a>
                @endif
            </div>

            <div class="article-carousel-wrap">
                <div class="article-carousel-nav ac-prev" id="articleCarouselPrev" aria-label="{{ __('Trước') }}">
                    <i class="fas fa-chevron-left"></i>
                </div>

                <div class="article-carousel-track" id="article-carousel">
                    @foreach ($articles as $article)
                        @php
                            // slug theo locale hiện tại (Article::$with = ['translation'])
                            $articleSlug = $article->translation->slug ?? $article->slug ?? null;
                            $excerpt = \Illuminate\Support\Str::limit(
                                strip_tags($article->content ?? ''),
                                90
                            );
                        @endphp
                        <a
                            href="{{ $articleSlug ? route('articles.show', ['slug' => $articleSlug]) : '#' }}"
                            class="article-card"
                        >
                            @if ($article->featured_image_url)
                                <img
                                    src="{{ $article->featured_image_url }}"
                                    alt="{{ $article->title }}"
                                    class="ac-thumb"
                                    loading="lazy"
                                >
                            @else
                                <div class="ac-thumb-placeholder">
                                    <i class="far fa-newspaper"></i>
                                </div>
                            @endif
                            <div class="ac-body">
                                <p class="ac-headline">{{ $article->title }}</p>
                                <div class="ac-meta">
                                    <span><i class="far fa-eye"></i>{{ number_format($article->views) }}</span>
                                    @if ($article->created_at)
                                        <span><i class="far fa-clock"></i>{{ $article->created_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="article-carousel-nav ac-next" id="articleCarouselNext" aria-label="{{ __('Tiếp') }}">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
        </div>

        <script>
            (function () {
                var track = document.getElementById('article-carousel');
                var prevBtn = document.getElementById('articleCarouselPrev');
                var nextBtn = document.getElementById('articleCarouselNext');
                if (!track) return;

                function scrollByCard(direction) {
                    var card = track.querySelector('.article-card');
                    var step = card ? (card.offsetWidth + 15) * 2 : 300; // cuộn ~2 thẻ / lần
                    track.scrollBy({ left: direction * step, behavior: 'smooth' });
                }

                if (prevBtn) prevBtn.addEventListener('click', function () { scrollByCard(-1); });
                if (nextBtn) nextBtn.addEventListener('click', function () { scrollByCard(1); });
            })();
        </script>
    </section>
@endif
