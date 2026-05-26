@extends('layout.app')

@section('content')
<style>
    /* Visual Bracket Styles */
    .bracket-container {
        display: flex;
        flex-direction: row;
        overflow-x: auto;
        padding: 20px 0;
        gap: 30px;
    }
    .bracket-round {
        display: flex;
        flex-direction: column;
        justify-content: space-around;
        min-width: 250px;
        gap: 20px;
    }
    .match-card {
        background-color: #252a36;
        border: 1px solid #3a3f4c;
        border-radius: 8px;
        overflow: hidden;
        position: relative;
    }
    .match-player {
        padding: 10px 15px;
        border-bottom: 1px solid #1a1c23;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .match-player:last-child {
        border-bottom: none;
    }
    .match-winner {
        background-color: rgba(46, 125, 50, 0.2);
        color: #81c784 !important;
        font-weight: bold;
    }
    .bracket-room-link {
        display: block;
        text-align: center;
        background: #1a1c23;
        padding: 5px;
        font-size: 0.8rem;
        text-decoration: none !important;
        color: #ffb74d;
        transition: 0.2s;
    }
    .bracket-room-link:hover {
        background: #d32f2f;
        color: #fff;
    }
</style>

<div class="container mt-4">
    <div class="mb-3">
        <a href="{{ route('tournaments.index') }}" class="btn btn-dark text-light"><i class="fad fa-arrow-left"></i> {{ __('Quay lại') }}</a>
    </div>

    <div class="card bg-secondary text-dark border-0 mb-4" style="border-radius: 12px;">
        <div class="card-body">
            <h2 class="text-warning font-weight-bold">{{ $tournament->name }}</h2>
            <p>{{ $tournament->description }}</p>

            <div class="row mt-4">
                <div class="col-md-3">
                    <p><i class="fad fa-calendar text-muted"></i> <strong>{{ __('Khởi tranh:') }}</strong> {{ \Carbon\Carbon::parse($tournament->start_date)->format('d/m/Y H:i') }}</p>
                </div>
                <div class="col-md-2">
                    <p><i class="fad fa-users text-muted"></i> <strong>{{ __('Số lượng:') }}</strong> {{ $tournament->users->count() }} / {{ $tournament->max_players }}</p>
                </div>
                <div class="col-md-7 text-right">
                    {{-- NÚT THAM GIA CHO NGƯỜI CHƠI --}}
                    @if(auth()->check())
                        @php
                            $isJoined = $tournament->users->contains(auth()->id());
                            $isOpen = $tournament->status === 'open';
                            $isFull = $tournament->users->count() >= $tournament->max_players;
                        @endphp

                        @if($isJoined)
                            <button class="btn btn-secondary font-weight-bold d-inline-block mb-2" disabled style="opacity: 0.8; cursor: not-allowed;">
                                <i class="fad fa-check-circle text-success"></i> {{ __('Đã tham gia') }}
                            </button>
                        @elseif($isOpen && !$isFull)
                            <form action="{{ route('tournaments.join', $tournament->slug) }}" method="POST" class="d-inline-block mb-2">
                                @csrf
                                <button type="submit" class="btn btn-primary font-weight-bold pulse-red">
                                    <i class="fad fa-sign-in-alt"></i> {{ __('Tham gia') }}
                                </button>
                            </form>
                        @elseif($isOpen && $isFull)
                            <button class="btn btn-secondary font-weight-bold d-inline-block mb-2" disabled>
                                <i class="fad fa-users-slash"></i> {{ __('Đã đầy') }}
                            </button>
                        @endif

                        {{-- CÁC NÚT QUẢN LÝ DÀNH RIÊNG CHO ADMIN --}}
                        @if(auth()->user()->is_admin)
                            <span class="text-muted mx-2">|</span>
                            @if($isOpen)
                            <form action="{{ route('tournaments.generate', $tournament->slug) }}" method="POST" class="d-inline-block mb-2">
                                @csrf
                                <button type="submit" class="btn btn-success font-weight-bold">
                                    <i class="fad fa-sitemap"></i> {{ __('Bốc thăm') }}
                                </button>
                            </form>
                            @endif

                            <a href="{{ route('tournaments.edit', $tournament->slug) }}" class="btn btn-warning text-dark font-weight-bold d-inline-block">
                                <i class="fad fa-edit"></i>
                            </a>

                            <form action="{{ route('tournaments.destroy', $tournament->slug) }}" method="POST" class="d-inline-block mb-2" onsubmit="return confirm('{{ __('Bạn có chắc chắn muốn xóa giải đấu này không?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger font-weight-bold">
                                    <i class="fad fa-trash-alt"></i>
                                </button>
                            </form>
                        @endif
                    @else
                        {{-- CHƯA ĐĂNG NHẬP --}}
                        <div class="d-inline-flex align-items-center bg-dark p-2" style="border-radius: 8px; border: 1px solid #3a3f4c;">
                            <span class="text-muted small mr-3 ml-2"><i class="fad fa-lock-alt"></i> {{ __('Yêu cầu tài khoản') }}</span>
                            <a href="{{ route('register') }}" class="btn btn-primary font-weight-bold btn-sm mr-2" style="box-shadow: 0 0 10px rgba(0, 123, 255, 0.4);">
                                <i class="fad fa-user-plus"></i> {{ __('Đăng ký') }}
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-outline-light font-weight-bold btn-sm">
                                {{ __('Đăng nhập') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($rounds->count() > 0)
        <h4 class="text-light mb-4"><i class="fad fa-sitemap text-danger"></i> {{ __('Sơ đồ thi đấu') }}</h4>
        <div class="bracket-container custom-scrollbar">

            @foreach($rounds as $roundNumber => $matches)
                <div class="bracket-round">
                    <h6 class="h5 text-center text-light mb-0">{{ __('Vòng') }} {{ $roundNumber }}</h6>

                    @foreach($matches as $match)
                        @php
                            $hostIsWinner = $match->result === 1;
                            $guestIsWinner = $match->result === -1;
                        @endphp
                        <div class="match-card bg-light shadow-sm">
                            <div class="match-player text-danger {{ $hostIsWinner ? 'match-winner' : '' }}">
                                <span><i class="fad fa-chess-pawn text-danger"></i> {{ $match->host->name ?? __('Chờ đối thủ...') }}</span>
                                @if($hostIsWinner) <i class="fad fa-crown"></i> @endif
                            </div>

                            <div class="match-player text-dark {{ $guestIsWinner ? 'match-winner' : '' }}">
                                <span><i class="fad fa-chess-pawn text-dark"></i> {{ $match->guest->name ?? __('Chờ đối thủ...') }}</span>
                                @if($guestIsWinner) <i class="fad fa-crown"></i> @endif
                            </div>
@php
    // Mặc định link là vào xem phòng
    $roomUrl = url('/phong/'.$match->code);

    // Nếu đã đăng nhập, kiểm tra xem có phải là người chơi trong phòng không
    if (auth()->check()) {
        if (auth()->id() === $match->host_id && $match->result === null) {
            $roomUrl = url('/phong/'.$match->code.'/do'); // Chủ phòng (Bên đỏ)
        } elseif (auth()->id() === $match->guest_id && $match->result === null) {
            $roomUrl = url('/phong/'.$match->code.'/den'); // Khách (Bên đen)
        } elseif ($match->result !== null) {
        // Nếu chưa đăng nhập nhưng trận đấu đã có kết quả, vẫn cho phép xem kết quả
        $roomUrl = url('/phong/'.$match->code.'/theo-doi');
    }
    }
@endphp
                            <a href="{{ $roomUrl }}" class="bracket-room-link bg-light text-dark font-weight-bold">
                                @if(is_null($match->result))
                                    <i class="fad fa-mouse"></i> {{ __('Vào phòng đấu') }}
                                @else
                                    <i class="fad fa-archive"></i> {{ __('Xem kết quả') }}
                                @endif
                            </a>
                        </div>
                    @endforeach
                </div>
            @endforeach

        </div>
    @else
        <div class="alert alert-dark bg-secondary text-center border-0 p-5 mt-4" style="border-radius: 12px;">
            <i class="fad fa-project-diagram fa-4x text-muted mb-3"></i>
            <h5 class="text-dark">{{ __('Sơ đồ giải đấu chưa được tạo.') }}</h5>
            <p class="text-muted">{{ __('Sơ đồ sẽ xuất hiện khi giải đấu bắt đầu và bốc thăm hoàn tất.') }}</p>
        </div>
    @endif
</div>
@endsection
