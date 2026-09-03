@extends('layouts.app')

@section('content')
<div class="container gm-page">

    {{-- Hero / toolbar, built on the same glossy panel used by the articles hero --}}
    <div class="article-hero mb-4">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-3">
            <div>
                <span class="article-hero-eyebrow">
                    <i class="fa-solid fa-chess-board"></i> {{ __('Thư viện') }}
                </span>
                <h1 class="article-hero-title h2 mb-1">{{ __('Thư viện ván cờ') }}</h1>
                <p class="article-hero-subtitle mb-0">{{ __('Xem lại các ván cờ được chia sẻ trên hệ thống.') }}</p>
            </div>

            <span class="article-meta-pill">
                <i class="fa-regular fa-clone"></i>
                {{ number_format($games->total()) }} {{ __('ván cờ') }}
            </span>
        </div>

        <form method="GET" class="row g-2 article-search">
            <div class="col-12 col-md-7">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    value="{{ $search }}"
                    placeholder="{{ __('Tìm theo tiêu đề, mô tả hoặc kỳ thủ...') }}">
            </div>

            <div class="col-8 col-md-3">
                <select name="sort" class="form-control h-100">
                    <option value="latest" @selected($sort === 'latest')>{{ __('Mới nhất') }}</option>
                    <option value="oldest" @selected($sort === 'oldest')>{{ __('Cũ nhất') }}</option>
                    <option value="views_desc" @selected($sort === 'views_desc')>{{ __('Xem nhiều nhất') }}</option>
                    <option value="views_asc" @selected($sort === 'views_asc')>{{ __('Xem ít nhất') }}</option>
                    <option value="alpha_asc" @selected($sort === 'alpha_asc')>{{ __('Tên A-Z') }}</option>
                    <option value="alpha_desc" @selected($sort === 'alpha_desc')>{{ __('Tên Z-A') }}</option>
                </select>
            </div>

            <div class="col-4 col-md-2">
                <button type="submit" class="btn btn-royal w-100 h-100">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> {{ __('Tìm') }}
                </button>
            </div>
        </form>
    </div>

    @if ($games->isEmpty())
        <div class="article-empty text-center py-5">
            <i class="fa-regular fa-chess-board fa-2x mb-3 d-block"></i>
            {{ __('Không tìm thấy ván cờ nào phù hợp.') }}
        </div>
    @else
        <div class="row g-4">
            @foreach ($games as $game)
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="{{ localized_path('games.show', ['slug' => $game->slug]) }}"
                       class="article-card d-flex flex-column h-100 text-decoration-none">
                        <div class="article-card-band"></div>

                        <div class="p-3 d-flex flex-column flex-grow-1">
                            <h3 class="article-card-title mb-2">{{ $game->title }}</h3>

                            <p class="article-card-excerpt flex-grow-1 mb-3">
                                {{ Str::limit($game->description, 110) ?: __('Chưa có mô tả cho ván cờ này.') }}
                            </p>

                            <div class="d-flex flex-wrap gap-2">
                                <span class="article-meta-pill">
                                    <i class="fa-regular fa-user"></i>
                                    {{ $game->user->name ?? __('Ẩn danh') }}
                                </span>
                                <span class="article-meta-pill">
                                    <i class="fa-regular fa-eye"></i>
                                    {{ number_format($game->views) }}
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        @if ($games->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                {{ $games->links('games.partials.pagination') }}
            </div>
        @endif
    @endif
</div>
@endsection
