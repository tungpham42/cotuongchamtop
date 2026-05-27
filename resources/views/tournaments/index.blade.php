@extends('layout.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="text-light"><i class="fad fa-trophy-alt text-warning"></i> {{ __('Danh sách Giải đấu') }}</h2>
            <p class="text-light">{{ __('Tham gia các giải đấu để tranh tài và nâng cao thứ hạng của bạn.') }}</p>
        </div>
        @if(auth()->check() && auth()->user()->is_admin)
        <div class="col-md-4 text-right">
            <a href="{{ route('tournaments.create') }}" class="btn btn-warning text-dark font-weight-bold">
                <i class="fad fa-plus-circle"></i> {{ __('Tạo Giải Đấu') }}
            </a>
        </div>
        @endif
    </div>

    {{-- Thêm Banner Dành Riêng Cho Khách --}}
    @guest
    <div class="alert alert-info bg-dark border-info text-light d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 p-4 shadow" style="border-radius: 12px; border-left: 5px solid #17a2b8 !important;">
        <div class="mb-3 mb-md-0">
            <h5 class="text-info font-weight-bold mb-1"><i class="fad fa-chess-knight"></i> {{ __('Sẵn sàng thử thách bản thân?') }}</h5>
            <p class="mb-0 text-muted">{{ __('Tạo tài khoản miễn phí để ghi danh vào các giải đấu, cạnh tranh với cao thủ và khắc tên lên bảng vàng.') }}</p>
        </div>
        <a href="{{ route('register') }}" class="btn btn-info text-dark font-weight-bold px-4 py-2" style="white-space: nowrap; transition: 0.3s;">
            <i class="fad fa-user-plus"></i> {{ __('Tạo tài khoản miễn phí') }}
        </a>
    </div>
    @endguest

    @if (session('success'))
        <div class="alert alert-success bg-dark text-success border-success">
            <i class="fad fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger bg-dark text-danger border-danger">
            <i class="fad fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="row">
        @forelse($tournaments as $tournament)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card bg-secondary text-dark h-100 border-0 shadow-sm" style="border-radius: 12px;">
                    @if($tournament->cover_photo)
                        <a href="{{ route('tournaments.show', $tournament->slug) }}">
                            <img src="{{ asset('storage/' . $tournament->cover_photo) }}" class="card-img-top w-100" alt="{{ $tournament->name }}" style="aspect-ratio: 16/9; object-fit: cover; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                        </a>
                    @else
                        <a href="{{ route('tournaments.show', $tournament->slug) }}">
                            <div class="card-img-top bg-dark d-flex align-items-center justify-content-center w-100" style="aspect-ratio: 16/9; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                                <i class="fad fa-trophy-alt fa-4x text-muted"></i>
                            </div>
                        </a>
                    @endif
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <a href="{{ route('tournaments.show', $tournament->slug) }}" class="text-decoration-none">
                                <h5 class="card-title text-warning font-weight-bold mb-0">{{ $tournament->name }}</h5>
                            </a>
                            @if($tournament->status === 'open')
                                <span class="badge badge-success">{{ __('Mở đăng ký') }}</span>
                            @elseif($tournament->status === 'in_progress')
                                <span class="badge badge-warning">{{ __('Đang diễn ra') }}</span>
                            @else
                                <span class="badge badge-dark text-muted">{{ __('Đã kết thúc') }}</span>
                            @endif
                        </div>
                        <p class="card-text text-dark small">{{ $tournament->description ?? __('Không có mô tả.') }}</p>

                        <ul class="list-unstyled mb-4 small">
                            <li><i class="fad fa-calendar-alt w-20px text-center"></i> <strong>{{ __('Bắt đầu:') }}</strong> {{ \Carbon\Carbon::parse($tournament->start_date)->format('d/m/Y H:i') }}</li>
                            <li><i class="fad fa-users w-20px text-center"></i> <strong>{{ __('Kỳ thủ:') }}</strong> {{ $tournament->users_count }} / {{ $tournament->max_players }}</li>
                        </ul>
                    </div>

                    <div class="card-footer bg-transparent border-0 pt-0">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('tournaments.show', $tournament->slug) }}" class="btn btn-outline-light btn-sm w-100 text-dark mr-2">
                                <i class="fad fa-eye"></i> {{ __('Xem chi tiết') }}
                            </a>
                            @php
                                // Added auth()->check() to prevent errors for guests
                                $isJoined = auth()->check() ? $tournament->users->contains(auth()->id()) : false;
                                $isOpen = $tournament->status === 'open';
                                // Changed to users_count to prevent N+1 queries (avoids lazy-loading the whole users collection)
                                $isFull = $tournament->users_count >= $tournament->max_players;
                            @endphp
                            @if($isJoined)
                                <button class="btn btn-secondary btn-sm w-100 ml-2" disabled style="opacity: 0.8; cursor: not-allowed;">
                                    <i class="fad fa-check-circle text-success"></i> {{ __('Đã tham gia') }}
                                </button>
                            @elseif($isOpen && !$isFull)
                                <form action="{{ route('tournaments.join', $tournament->slug) }}" method="POST" class="w-100 ml-2">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm w-100">
                                        <i class="fad fa-sign-in-alt"></i> {{ __('Tham gia') }}
                                    </button>
                                </form>
                            @elseif($isFull)
                                <button class="btn btn-secondary btn-sm w-100 ml-2" disabled>
                                    <i class="fad fa-users-slash"></i> {{ __('Đã đầy') }}
                                </button>
                            @elseif($tournament->status === 'in_progress')
                                <button class="btn btn-secondary btn-sm w-100 ml-2" disabled>
                                    <i class="fad fa-spinner fa-spin"></i> {{ __('Đang diễn ra') }}
                                </button>
                            @else
                                <button class="btn btn-secondary btn-sm w-100 ml-2" disabled>
                                    <i class="fad fa-check-circle"></i> {{ __('Đã kết thúc') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-secondary bg-dark text-center border-0 p-5">
                    <i class="fad fa-box-open fa-3x text-muted mb-3"></i>
                    <h5 class="text-light">{{ __('Chưa có giải đấu nào.') }}</h5>
                </div>
            </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $tournaments->links() }}
    </div>
</div>
@endsection
