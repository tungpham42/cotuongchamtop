@extends('layouts.app')

@section('content')
<div class="container gm-page">

    <a href="{{ localized_path('games.library') }}" class="btn btn-royal-outline btn-sm mb-4">
        <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Quay lại thư viện') }}
    </a>

    <div class="article-detail-card p-3 p-md-4">
        <div class="row g-4">
            <div class="col-12 col-lg-5">
                <div class="gm-board-wrap">
                    {{-- Điểm gắn cho board renderer hiện có của site (giống cách 'fen' được
                         truyền cho view 'board' trong web.php) — chưa có sẵn markup/JS cụ thể
                         nên chỉ để lại data attributes để script hiện tại của bạn hook vào. --}}
                    <div id="gameBoard" data-fen="{{ $game->initial_fen }}" data-moves='@json($game->moves ?? [])'></div>
                </div>
                <p class="text-center small mt-2 gm-fen">{{ $game->initial_fen }}</p>
            </div>

            <div class="col-12 col-lg-7">
                <span class="article-hero-eyebrow">
                    <i class="fa-solid fa-chess-board"></i> {{ __('Ván cờ') }}
                </span>
                <h1 class="article-detail-title h3 mt-1 mb-3">{{ $game->title }}</h1>

                <div class="d-flex flex-wrap gap-2 article-detail-meta pb-3 mb-3">
                    <span class="article-meta-pill">
                        <i class="fa-regular fa-user"></i>
                        {{ $game->user->name ?? __('Ẩn danh') }}
                    </span>
                    <span class="article-meta-pill">
                        <i class="fa-regular fa-eye"></i>
                        {{ number_format($game->views) }} {{ __('lượt xem') }}
                    </span>
                    <span class="article-meta-pill">
                        <i class="fa-regular fa-calendar"></i>
                        {{ $game->created_at->format('d/m/Y') }}
                    </span>
                </div>

                @if ($game->description)
                    <div class="article-body mb-4">
                        <p>{{ $game->description }}</p>
                    </div>
                @endif

                <p class="gm-moves-title">{{ __('Nước đi') }}</p>

                @if (!empty($game->moves))
                    <div class="gm-moves-list">
                        @foreach ($game->moves as $i => $move)
                            <span class="article-meta-pill gm-move-pill">{{ $i + 1 }}. {{ $move }}</span>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted small mb-0">{{ __('Ván cờ này chưa có dữ liệu nước đi.') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    /* Board panel — same liquid-glass recipe as the site's cards, no direct
       component for this yet so it's scoped here with fallbacks. */
    .gm-board-wrap {
        background: var(--glass-bg-dark, rgba(11, 12, 16, .85));
        backdrop-filter: var(--glass-blur, blur(20px));
        -webkit-backdrop-filter: var(--glass-blur, blur(20px));
        border: 1px solid var(--glass-border, rgba(255, 215, 0, .55));
        border-radius: 14px;
        padding: 16px;
        aspect-ratio: 9 / 10;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow:
            var(--liquid-shadow, 0 8px 32px rgba(0, 0, 0, .8)),
            inset 0 2px 15px rgba(255, 255, 255, .15);
    }
    .gm-board-wrap #gameBoard { width: 100%; height: 100%; }

    .gm-fen { color: #6b7280; word-break: break-all; }

    .gm-moves-title {
        font-size: .82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--royal-gold, #ffd700);
        margin-bottom: 10px;
    }

    .gm-moves-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        max-height: 260px;
        overflow-y: auto;
        padding-right: 4px;
    }
    .gm-move-pill { font-family: ui-monospace, monospace; }

    @media (max-width: 991px) {
        .gm-board-wrap { aspect-ratio: 1 / 1; }
    }
</style>
@endsection
