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

    <div class="card bg-secondary text-light border-0 mb-4" style="border-radius: 12px;">
        <div class="card-body">
            <h2 class="text-warning font-weight-bold">{{ $tournament->name }}</h2>
            <p>{{ $tournament->description }}</p>

            <div class="row mt-4">
                <div class="col-md-4">
                    <p><i class="fad fa-calendar text-muted"></i> <strong>{{ __('Khởi tranh:') }}</strong> {{ \Carbon\Carbon::parse($tournament->start_date)->format('d/m/Y H:i') }}</p>
                </div>
                <div class="col-md-4">
                    <p><i class="fad fa-users text-muted"></i> <strong>{{ __('Số lượng:') }}</strong> {{ $tournament->users->count() }} / {{ $tournament->max_players }}</p>
                </div>
                <div class="col-md-4 text-right">
                    @if($tournament->status === 'open' && auth()->user()->role === 'admin')
                        <form action="{{ route('tournaments.generate', $tournament->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning text-dark font-weight-bold">
                                <i class="fad fa-sitemap"></i> {{ __('Bốc thăm chia cặp') }}
                            </button>
                        </form>
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
                    <h6 class="text-center text-secondary mb-3">{{ __('Vòng') }} {{ $roundNumber }}</h6>

                    @foreach($matches as $match)
                        @php
                            $hostIsWinner = $match->result === 1;
                            $guestIsWinner = $match->result === -1;
                        @endphp
                        <div class="match-card shadow-sm">
                            <div class="match-player text-light {{ $hostIsWinner ? 'match-winner' : '' }}">
                                <span><i class="fad fa-chess-pawn text-danger"></i> {{ $match->host->name ?? __('Chờ đối thủ...') }}</span>
                                @if($hostIsWinner) <i class="fad fa-crown"></i> @endif
                            </div>

                            <div class="match-player text-light {{ $guestIsWinner ? 'match-winner' : '' }}">
                                <span><i class="fad fa-chess-pawn text-dark"></i> {{ $match->guest->name ?? __('Chờ đối thủ...') }}</span>
                                @if($guestIsWinner) <i class="fad fa-crown"></i> @endif
                            </div>

                            <a href="{{ url('/phong/'.$match->code) }}" class="bracket-room-link">
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
            <h5 class="text-light">{{ __('Sơ đồ giải đấu chưa được tạo.') }}</h5>
            <p class="text-muted">{{ __('Sơ đồ sẽ xuất hiện khi giải đấu bắt đầu và bốc thăm hoàn tất.') }}</p>
        </div>
    @endif
</div>
@endsection
