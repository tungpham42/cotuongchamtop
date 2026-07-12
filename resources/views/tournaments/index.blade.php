@extends('layout.app')


@section('content')
<div class="container mt-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 style="color: var(--royal-gold); font-family: 'Texturina', serif; text-transform: uppercase; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
                <i class="fad fa-trophy-alt"></i> {{ __('Danh sách Giải đấu') }}
            </h2>
            <p style="color: var(--royal-gold-light); font-style: italic;">{{ __('Tham gia các giải đấu để tranh tài và nâng cao thứ hạng của bạn.') }}</p>
        </div>
        @if(auth()->check())
        <div class="col-md-4 text-right">
            <a href="{{ localized_url('tournaments.create') }}" class="btn font-weight-bold pulse-gold" style="background: linear-gradient(to bottom, #d4af37, #b89020); color: var(--royal-red); border: 2px solid #fff;">
                <i class="fad fa-plus-circle"></i> {{ __('Tạo Giải Đấu') }}
            </a>
        </div>
        @endif
    </div>

    @guest
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-center mb-4 p-4 shadow-sm" style="background: rgba(138, 21, 21, 0.2); border-left: 4px solid var(--royal-gold); border-right: 4px solid var(--royal-gold); border-radius: 4px;">
        <div class="mb-3 mb-md-0">
            <h5 class="font-weight-bold mb-1" style="color: var(--royal-gold); font-family: 'Texturina', serif;"><i class="fad fa-swords"></i> {{ __('Sẵn sàng thử thách bản thân?') }}</h5>
            <p class="mb-0 mr-3" style="color: var(--royal-gold-light);">{{ __('Tạo tài khoản miễn phí để Tạo giải đấu, ghi danh vào các giải đấu, cạnh tranh với cao thủ và khắc tên lên bảng vàng.') }}</p>
        </div>
        <a href="{{ localized_url('register') }}" class="btn font-weight-bold px-4 py-2 pulse-gold" style="background: linear-gradient(to bottom, #d4af37, #b89020); color: var(--royal-red); border: 2px solid #fff; white-space: nowrap;">
            <i class="fad fa-user-plus"></i> {{ __('Tạo tài khoản') }}
        </a>
    </div>
    @endguest

    @if (session('success'))
        <div class="p-3 mb-4" style="background: rgba(45, 106, 79, 0.2); border-left: 4px solid #2d6a4f; color: #a3b18a;">
            <i class="fad fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="p-3 mb-4" style="background: rgba(138, 21, 21, 0.2); border-left: 4px solid var(--royal-red); color: #ffb7b2;">
            <i class="fad fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="row">
        @forelse($tournaments as $tournament)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-lg" style="background: #2a1910; border: 1px solid var(--royal-gold); border-radius: 4px; overflow: hidden;">
                    @if($tournament->cover_photo)
                        <a href="{{ localized_url('tournaments.show', ['slug' => $tournament->slug]) }}">
                            <img src="{{ asset('storage/' . $tournament->cover_photo) }}" class="card-img-top w-100" alt="{{ $tournament->name }}" style="aspect-ratio: 16/9; object-fit: cover; border-bottom: 1px solid var(--royal-wood);">
                        </a>
                    @else
                        <a href="{{ localized_url('tournaments.show', ['slug' => $tournament->slug]) }}">
                            <div class="card-img-top d-flex align-items-center justify-content-center w-100" style="background: var(--royal-bg); aspect-ratio: 16/9; border-bottom: 1px solid var(--royal-wood);">
                                <i class="fad fa-trophy-alt fa-4x" style="color: var(--royal-wood);"></i>
                            </div>
                        </a>
                    @endif
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <a href="{{ localized_url('tournaments.show', ['slug' => $tournament->slug]) }}" class="text-decoration-none">
                                <h5 class="card-title font-weight-bold mb-0" style="color: var(--royal-gold); font-family: 'Texturina', serif; text-transform: uppercase;">{{ $tournament->name }}</h5>
                            </a>
                        </div>
                        <div class="mb-3">
                            @if($tournament->status === 'open')
                                <span class="badge" style="background: var(--royal-red); color: var(--royal-gold); border: 1px solid var(--royal-gold);">{{ __('Mở đăng ký') }}</span>
                            @elseif($tournament->status === 'in_progress')
                                <span class="badge" style="background: var(--royal-gold); color: var(--royal-red);">{{ __('Đang diễn ra') }}</span>
                            @elseif($tournament->status === 'cancelled')
                                <span class="badge" style="background: #5c0a0a; color: #ffb7b2; border: 1px solid var(--royal-red);">{{ __('Đã hủy') }}</span>
                            @else
                                <span class="badge" style="background: var(--royal-wood); color: var(--royal-gold-light);">{{ __('Đã kết thúc') }}</span>
                            @endif
                        </div>

                        <p class="card-text small" style="color: var(--royal-gold-light);">{{ $tournament->description ?? __('Không có mô tả.') }}</p>

                        <ul class="list-unstyled mb-4 small" style="color: #aa8c4a;">
                            <li><i class="fad fa-calendar-alt w-20px text-center"></i> <strong>{{ __('Bắt đầu:') }}</strong> {{ \Carbon\Carbon::parse($tournament->start_date)->format('d/m/Y h:i A') }}</li>
                            <li><i class="fad fa-users w-20px text-center"></i> <strong>{{ __('Kỳ thủ:') }}</strong> {{ $tournament->users_count }} / {{ $tournament->max_players }}</li>
                        </ul>
                    </div>

                    <div class="card-footer bg-transparent border-0 pt-0 pb-3">
                        <div class="d-flex justify-content-between">
                            <a href="{{ localized_url('tournaments.show', ['slug' => $tournament->slug]) }}" class="btn btn-sm w-100 mr-2" style="color: var(--royal-gold); border: 1px solid var(--royal-gold); background: transparent;">
                                <i class="fad fa-eye"></i> {{ __('Chi tiết') }}
                            </a>
                            @php
                                $isJoined = auth()->check() ? $tournament->users->contains(auth()->id()) : false;
                                $isOpen = $tournament->status === 'open';
                                $isCancelled = $tournament->status === 'cancelled';
                                $isFull = $tournament->users_count >= $tournament->max_players;
                            @endphp

                            @if($isCancelled)
                                <button class="btn btn-sm w-100 ml-2" disabled style="background: var(--royal-wood); color: var(--royal-gold-light); opacity: 0.8;">
                                    <i class="fad fa-ban"></i> {{ __('Bị hủy') }}
                                </button>
                            @elseif($isJoined)
                                <button class="btn btn-sm w-100 ml-2" disabled style="background: var(--royal-wood); color: var(--royal-gold-light); opacity: 0.8;">
                                    <i class="fad fa-check-circle"></i> {{ __('Đã tham gia') }}
                                </button>
                            @elseif($isOpen && !$isFull)
                                <form action="{{ localized_url('tournaments.join', ['slug' => $tournament->slug]) }}" method="POST" class="w-100 ml-2">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm w-100 pulse-red">
                                        <i class="fad fa-sign-in-alt"></i> {{ __('Tham gia') }}
                                    </button>
                                </form>
                            @elseif($isFull)
                                <button class="btn btn-sm w-100 ml-2" disabled style="background: var(--royal-wood); color: var(--royal-gold-light);">
                                    <i class="fad fa-users-slash"></i> {{ __('Đã đầy') }}
                                </button>
                            @elseif($tournament->status === 'in_progress')
                                <button class="btn btn-sm w-100 ml-2" disabled style="background: var(--royal-wood); color: var(--royal-gold-light);">
                                    <i class="fad fa-spinner fa-spin"></i> {{ __('Đang diễn ra') }}
                                </button>
                            @else
                                <button class="btn btn-sm w-100 ml-2" disabled style="background: var(--royal-wood); color: var(--royal-gold-light);">
                                    <i class="fad fa-check-circle"></i> {{ __('Kết thúc') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="p-5 text-center shadow-lg" style="background: #2a1910; border: 1px solid var(--royal-wood); border-radius: 4px;">
                    <i class="fad fa-box-open fa-3x mb-3" style="color: var(--royal-wood);"></i>
                    <h5 style="color: var(--royal-gold-light);">{{ __('Chưa có giải đấu nào.') }}</h5>
                </div>
            </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $tournaments->links() }}
    </div>
</div>
@endsection
