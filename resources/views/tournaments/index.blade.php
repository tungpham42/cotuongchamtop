@extends('layout.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="text-light"><i class="fad fa-trophy-alt text-warning"></i> {{ __('Danh sách Giải đấu') }}</h2>
            <p class="text-secondary">{{ __('Tham gia các giải đấu để tranh tài và nâng cao thứ hạng của bạn.') }}</p>
        </div>
    </div>

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
                <div class="card bg-secondary text-light h-100 border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title text-warning font-weight-bold mb-0">{{ $tournament->name }}</h5>
                            @if($tournament->status === 'open')
                                <span class="badge badge-success">{{ __('Mở đăng ký') }}</span>
                            @elseif($tournament->status === 'in_progress')
                                <span class="badge badge-warning">{{ __('Đang diễn ra') }}</span>
                            @else
                                <span class="badge badge-dark text-muted">{{ __('Đã kết thúc') }}</span>
                            @endif
                        </div>
                        <p class="card-text text-light small">{{ $tournament->description ?? __('Không có mô tả.') }}</p>

                        <ul class="list-unstyled mb-4 small">
                            <li><i class="fad fa-calendar-alt w-20px text-center"></i> <strong>{{ __('Bắt đầu:') }}</strong> {{ \Carbon\Carbon::parse($tournament->start_date)->format('d/m/Y H:i') }}</li>
                            <li><i class="fad fa-users w-20px text-center"></i> <strong>{{ __('Kỳ thủ:') }}</strong> {{ $tournament->users_count }} / {{ $tournament->max_players }}</li>
                        </ul>
                    </div>

                    <div class="card-footer bg-transparent border-0 pt-0">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('tournaments.show', $tournament->id) }}" class="btn btn-outline-light btn-sm w-100 mr-2">
                                <i class="fad fa-eye"></i> {{ __('Xem chi tiết') }}
                            </a>

                            @if($tournament->status === 'open' && $tournament->users_count < $tournament->max_players)
                                <form action="{{ route('tournaments.join', $tournament->id) }}" method="POST" class="w-100 ml-2">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm w-100">
                                        <i class="fad fa-sign-in-alt"></i> {{ __('Tham gia') }}
                                    </button>
                                </form>
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
