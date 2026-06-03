@extends('layout.app')

@section('meta_description', $tournament->description ?? 'Giải đấu cờ tướng hấp dẫn.')

@section('og_image', $tournament->cover_photo ? asset('storage/' . $tournament->cover_photo) : asset('img/1200x630.jpg'))
@section('og_image_width', '1200')
@section('og_image_height', '630')

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
        <a href="{{ localized_url('tournaments.index') }}" class="btn btn-dark text-light"><i class="fad fa-arrow-left"></i> {{ __('Quay lại') }}</a>
    </div>

    <div class="card bg-secondary text-dark border-0 mb-4" style="border-radius: 12px; overflow: hidden;">

        @if($tournament->cover_photo)
            <div style="width: 100%; aspect-ratio: 16/9; background-image: url('{{ asset('storage/' . $tournament->cover_photo) }}'); background-size: cover; background-position: center;">
            </div>
        @endif

        <div class="card-body">
            <h2 class="text-warning font-weight-bold">{{ $tournament->name }}</h2>
            <p>{{ $tournament->description }}</p>

            <div class="row mt-4">
                <div class="col-md-3">
                    <p>
                        <i class="fad fa-user-crown text-muted"></i> <strong>{{ __('Người tạo:') }}</strong>
                        @if($tournament->creator)
                            <a href="{{ localized_url('app.player', ['id' => $tournament->creator->id]) }}" class="text-warning font-weight-bold" style="text-decoration: none;">
                                {{ $tournament->creator->name }}
                            </a>
                        @else
                            <span class="text-muted">{{ __('Hệ thống') }}</span>
                        @endif
                    </p>
                </div>
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
                            <form action="{{ localized_url('tournaments.join', ['slug' => $tournament->slug]) }}" method="POST" class="d-inline-block mb-2">
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
                        @if(auth()->check() && (auth()->user()->is_admin || $tournament->user_id === auth()->id()))
                            <span class="text-muted mx-2">|</span>
                            @if($isOpen && $tournament->users->count() >= 2)
                            <form action="{{ localized_url('tournaments.generate', ['slug' => $tournament->slug]) }}" method="POST" class="d-inline-block mb-2">
                                @csrf
                                <button type="submit" class="btn btn-success font-weight-bold">
                                    <i class="fad fa-sitemap"></i> {{ __('Bốc thăm') }}
                                </button>
                            </form>
                            @endif

                            <a href="{{ localized_url('tournaments.edit', ['slug' => $tournament->slug]) }}" class="btn btn-warning text-dark font-weight-bold d-inline-block">
                                <i class="fad fa-edit"></i>
                            </a>

                            <form action="{{ localized_url('tournaments.destroy', ['slug' => $tournament->slug]) }}" method="POST" class="d-inline-block mb-2" onsubmit="return confirm('{{ __('Bạn có chắc chắn muốn xóa giải đấu này không?') }}');">
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
                            <a href="{{ localized_url('register') }}" class="btn btn-primary font-weight-bold btn-sm mr-2" style="box-shadow: 0 0 10px rgba(0, 123, 255, 0.4);">
                                <i class="fad fa-user-plus"></i> {{ __('Đăng ký') }}
                            </a>
                            <a href="{{ localized_url('login') }}" class="btn btn-outline-light font-weight-bold btn-sm">
                                {{ __('Đăng nhập') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- DANH SÁCH NGƯỜI CHƠI (CHỈ DÀNH CHO ADMIN) --}}
    <div class="card bg-white text-dark mb-4 shadow-sm" style="border-radius: 12px; overflow: hidden; border: 1px solid #dee2e6;">
        <div class="card-header bg-light border-bottom" style="border-color: #dee2e6 !important;">
            <h5 class="mb-0 text-dark font-weight-bold">
                <i class="fad fa-users-cog text-primary"></i> {{ __('Danh sách kỳ thủ đã đăng ký') }}
            </h5>
        </div>
        <div class="card-body p-0">
            @if($tournament->users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-borderless mb-0">
                        <thead class="bg-light text-dark" style="border-bottom: 2px solid #dee2e6;">
                            <tr>
                                <th class="pl-4">#</th>
                                <th>{{ __('Tên kỳ thủ') }}</th>
                                <th>{{ __('Elo') }}</th>
                                <th>{{ __('Thời gian đăng ký') }}</th>
                                <th class="pr-4 text-right">{{ __('Trạng thái') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tournament->users as $index => $player)
                                <tr style="border-bottom: 1px solid #e9ecef;">
                                    <td class="pl-4 align-middle text-muted">{{ $index + 1 }}</td>
                                    <td class="align-middle">
                                        <a href="{{ localized_url('app.player', ['id' => $player->id]) }}" class="text-primary font-weight-bold" style="text-decoration: none;">
                                            {{ $player->name }}
                                        </a>
                                    </td>
                                    <td class="text-danger font-weight-bold align-middle">{{ intval($player->elo) }}</td>
                                    <td class="text-muted align-middle">{{ $player->pivot->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="pr-4 text-right align-middle">
                                        @if($player->last_seen_at && \Carbon\Carbon::parse($player->last_seen_at)->diffInMinutes() < 5)
                                            <span class="badge badge-success px-2 py-1"><i class="fad fa-circle" style="font-size: 0.7rem;"></i> Online</span>
                                        @else
                                            <span class="badge badge-secondary px-2 py-1"><i class="fad fa-circle" style="font-size: 0.7rem;"></i> Offline</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-4 text-center text-muted bg-white">
                    <i class="fad fa-box-open fa-2x mb-2 text-secondary"></i>
                    <p class="mb-0">{{ __('Chưa có kỳ thủ nào tham gia giải đấu này.') }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- SƠ ĐỒ THI ĐẤU --}}
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
                                $roomUrl = url(__('/phong/') . $match->code . __('/theo-doi'));

                                // Nếu đã đăng nhập, kiểm tra xem có phải là người chơi trong phòng không
                                if (auth()->check()) {
                                    if (auth()->id() === $match->host_id && $match->result === null) {
                                        $roomUrl = url(__('/phong/') . $match->code . __('/do')); // Chủ phòng (Bên đỏ)
                                    } elseif (auth()->id() === $match->guest_id && $match->result === null) {
                                        $roomUrl = url(__('/phong/') . $match->code . __('/den')); // Khách (Bên đen)
                                    } elseif ($match->result !== null) {
                                        // Dù đã đăng nhập nhưng trận đấu đã có kết quả, vẫn chuyển về chế độ xem
                                        $roomUrl = url(__('/phong/') . $match->code . __('/theo-doi'));
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
