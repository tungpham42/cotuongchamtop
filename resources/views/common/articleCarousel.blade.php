{{--
    Carousel danh sách bài viết (Articles Carousel) - Liquid Glass Royal Theme

    Cách dùng:
        @include('common.articleCarousel', ['articles' => $articles])

    Props:
        $articles      Collection|LengthAwarePaginator các Article
                        (mỗi item cần: ->title, ->featured_image_url, ->views,
                         ->created_at, ->translation->slug — được cung cấp bởi
                         Article model + Translatable trait).
        $carouselTitle string|null  Tiêu đề section (mặc định: 'Bài viết mới nhất')
        $seeAllRoute   string|null  Tên route "Xem tất cả" (mặc định: 'article.index')

    Lưu ý: file này chỉ include 1 lần / trang vì có JS khởi tạo scroll bên dưới.
    Nếu dùng nhiều carousel trên cùng 1 trang, đổi id 'article-carousel'
    thành dynamic id (ví dụ $carouselId) để tránh đụng nhau.
--}}

@php
    $carouselTitle = $carouselTitle ?? __('Bài viết mới nhất');
    $seeAllRoute = $seeAllRoute ?? localized_url('article.index');
@endphp

@if (isset($articles) && $articles->count() > 0)
    <section class="article-carousel-section">
        <style>
            .article-carousel-section {
                padding: 30px 0;
            }
            .article-carousel-section .ac-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 20px;
            }
            .article-carousel-section .ac-title {
                font-family: "Texturina", serif;
                font-size: 1.75rem;
                font-weight: 700;
                color: var(--royal-gold);
                text-transform: uppercase;
                letter-spacing: 1px;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
                margin: 0;
            }
            .article-carousel-section .ac-see-all {
                font-size: 0.9rem;
                font-weight: 600;
                color: var(--royal-gold-light);
                text-transform: uppercase;
                letter-spacing: 1px;
                text-decoration: none;
                white-space: nowrap;
                transition: all 0.3s ease;
            }
            .article-carousel-section .ac-see-all:hover {
                color: var(--royal-gold);
                text-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
                text-decoration: none;
            }

            .article-carousel-wrap {
                position: relative;
            }

            .article-carousel-track {
                display: flex;
                gap: 20px;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                scroll-behavior: smooth;
                padding-bottom: 15px;
                padding-top: 10px;
                -webkit-overflow-scrolling: touch;
            }

            /* Royal Scrollbar for the track */
            .article-carousel-track::-webkit-scrollbar { height: 10px; }
            .article-carousel-track::-webkit-scrollbar-track {
                background: rgba(11, 12, 16, 0.6);
                border-radius: 6px;
                box-shadow: inset 0 0 8px rgba(0, 0, 0, 0.9);
            }
            .article-carousel-track::-webkit-scrollbar-thumb {
                background: linear-gradient(90deg, var(--royal-red), #5c0a0a);
                border: 1px solid var(--royal-gold);
                border-radius: 6px;
                box-shadow: inset 0 0 5px rgba(255, 215, 0, 0.3);
            }
            .article-carousel-track::-webkit-scrollbar-thumb:hover {
                background: linear-gradient(90deg, #d4af37, #b89020);
                border-color: #fff;
                box-shadow: 0 0 10px rgba(212, 175, 55, 0.8);
            }

            /* Liquid Glass Article Card */
            .article-card {
                flex: 0 0 auto;
                width: 280px;
                scroll-snap-align: start;
                background: var(--glass-bg-dark);
                backdrop-filter: var(--glass-blur);
                -webkit-backdrop-filter: var(--glass-blur);
                border: 1px solid var(--glass-border);
                border-top: 1px solid rgba(255, 215, 0, 0.5);
                border-radius: 12px;
                overflow: hidden;
                text-decoration: none;
                display: block;
                box-shadow: var(--liquid-shadow), inset 0 2px 15px var(--liquid-highlight);
                transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            }
            .article-card:hover {
                transform: translateY(-5px) scale(1.02);
                border-color: rgba(212, 175, 55, 0.6);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.9), 0 0 25px rgba(212, 175, 55, 0.3), inset 0 4px 20px var(--liquid-highlight);
                text-decoration: none;
            }
            .article-card .ac-thumb {
                width: 100%;
                height: 160px;
                object-fit: cover;
                background-color: var(--royal-bg);
                display: block;
                border-bottom: 1px solid rgba(212, 175, 55, 0.2);
            }
            .article-card .ac-thumb-placeholder {
                width: 100%;
                height: 160px;
                background-color: rgba(11, 12, 16, 0.8);
                border-bottom: 1px solid rgba(212, 175, 55, 0.2);
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--royal-gold);
                font-size: 2.5rem;
                opacity: 0.6;
            }
            .article-card .ac-body {
                padding: 15px;
            }
            .article-card .ac-headline {
                font-family: "Plus Jakarta Sans", sans-serif;
                font-size: 1rem;
                font-weight: 600;
                color: var(--royal-gold);
                line-height: 1.4;
                margin: 0 0 10px;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
                text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
                transition: color 0.3s ease;
            }
            .article-card:hover .ac-headline {
                color: #fff;
                text-shadow: 0 0 8px rgba(255, 215, 0, 0.8);
            }
            .article-card .ac-meta {
                font-size: 0.8rem;
                color: var(--royal-gold-light);
                opacity: 0.8;
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .article-card .ac-meta i {
                margin-right: 4px;
                color: var(--royal-gold);
            }

            /* Liquid Glass Navigation Buttons */
            .article-carousel-nav {
                position: absolute;
                top: calc(50% - 15px);
                transform: translateY(-50%);
                width: 42px;
                height: 42px;
                border-radius: 50%;
                background: var(--glass-bg-dark);
                backdrop-filter: var(--glass-blur);
                -webkit-backdrop-filter: var(--glass-blur);
                border: 1px solid var(--glass-border);
                border-top: 2px solid rgba(255, 215, 0, 0.6);
                box-shadow: var(--liquid-shadow), inset 0 3px 10px var(--liquid-highlight);
                color: var(--royal-gold-light);
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                z-index: 2;
                transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            }
            .article-carousel-nav:hover {
                background-color: var(--glass-bg-red);
                border-color: var(--royal-gold);
                color: var(--royal-gold);
                transform: translateY(-50%) scale(1.15);
                box-shadow: 0 15px 30px rgba(0, 0, 0, 0.9), 0 0 20px rgba(212, 175, 55, 0.6), inset 0 4px 15px var(--liquid-highlight);
            }
            .article-carousel-nav i {
                font-size: 1.1rem;
            }
            .article-carousel-nav.ac-prev { left: -20px; }
            .article-carousel-nav.ac-next { right: -20px; }

            @media (max-width: 576px) {
                .article-carousel-nav { display: none; }
                .article-card { width: 240px; }
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
                            href="{{ $articleSlug ? localized_url('article.show', ['slug' => $articleSlug]) : '#' }}"
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
                    var step = card ? (card.offsetWidth + 20) * 2 : 300; // cuộn ~2 thẻ / lần (gap 20px)
                    track.scrollBy({ left: direction * step, behavior: 'smooth' });
                }

                if (prevBtn) prevBtn.addEventListener('click', function () { scrollByCard(-1); });
                if (nextBtn) nextBtn.addEventListener('click', function () { scrollByCard(1); });
            })();
        </script>
    </section>
@endif
