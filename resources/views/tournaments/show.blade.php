@extends('layout.app')

@section('meta_description', $tournament->description ?? 'Giải đấu cờ tướng hấp dẫn.')

@section('og_image', $tournament->cover_photo ? asset('storage/' . $tournament->cover_photo) : asset('img/1200x630.jpg'))
@section('og_image_width', '1200')
@section('og_image_height', '630')

@section('content')
<div class="container mt-4">
    <div class="mb-3">
        <a href="{{ localized_url('tournaments.index') }}" class="btn" style="color: var(--royal-gold-light); border: 1px solid var(--royal-wood);"><i class="fad fa-arrow-left"></i> {{ __('Quay lại') }}</a>
    </div>

    <div class="card shadow-lg mb-4" style="border-radius: 4px; background: rgba(28, 17, 10, 0.85); border: 2px solid var(--royal-gold); overflow: hidden;">
        @if($tournament->cover_photo)
            <div style="width: 100%; aspect-ratio: 16/9; background-image: url('{{ asset('storage/' . $tournament->cover_photo) }}'); background-size: cover; background-position: center; border-bottom: 2px solid var(--royal-gold);">
            </div>
        @endif

        <div class="card-body p-4">
            <h2 style="color: var(--royal-gold); font-family: 'Texturina', serif; text-transform: uppercase; letter-spacing: 1px;">
                {{ $tournament->name }}
                @if($tournament->status === 'cancelled')
                    <span class="badge ml-2" style="font-size: 14px; vertical-align: middle; background: #5c0a0a; color: #ffb7b2; border: 1px solid var(--royal-red);">{{ __('Đã Hủy') }}</span>
                @endif
            </h2>
            <p style="color: var(--royal-gold-light);">{{ $tournament->description }}</p>

            <div class="row mt-4" style="color: #aa8c4a;">
                <div class="col-md-2">
                    <p>
                        <i class="fad fa-user-crown"></i> <strong>{{ __('Người tạo:') }}</strong>
                        @if($tournament->creator)
                            <a href="{{ localized_url('app.player', ['id' => $tournament->creator->id]) }}" style="color: var(--royal-gold); font-weight: bold; text-decoration: none;">
                                {{ $tournament->creator->name }}
                            </a>
                        @else
                            <span style="color: var(--royal-gold-light);">{{ __('Hệ thống') }}</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-2">
                    <p><i class="fad fa-calendar"></i> <strong>{{ __('Khởi tranh:') }}</strong> {{ \Carbon\Carbon::parse($tournament->start_date)->format('d/m/Y h:i:s A') }}</p>
                </div>
                <div class="col-md-2">
                    <p><i class="fad fa-users"></i> <strong>{{ __('Số lượng:') }}</strong> {{ $tournament->users->count() }} / {{ $tournament->max_players }}</p>
                </div>
                <div class="col-md-6 text-right">
                    @if(auth()->check())
                        @php
                            $isJoined = $tournament->users->contains(auth()->id());
                            $isOpen = $tournament->status === 'open';
                            $isCancelled = $tournament->status === 'cancelled';
                            $isFull = $tournament->users->count() >= $tournament->max_players;
                        @endphp

                        @if($isJoined && !$isCancelled)
                            <button class="btn d-inline-block" disabled style="background: var(--royal-wood); color: var(--royal-gold-light); opacity: 0.8; border: 1px solid var(--royal-gold);">
                                <i class="fad fa-check-circle" style="color: var(--royal-gold);"></i> {{ __('Đã tham gia') }}
                            </button>
                        @elseif($isOpen && !$isFull)
                            <form action="{{ localized_url('tournaments.join', ['slug' => $tournament->slug]) }}" method="POST" class="d-inline-block">
                                @csrf
                                <button type="submit" class="btn btn-danger font-weight-bold pulse-red">
                                    <i class="fad fa-sign-in-alt"></i> {{ __('Tham gia') }}
                                </button>
                            </form>
                        @elseif($isOpen && $isFull)
                            <button class="btn d-inline-block" disabled style="background: var(--royal-wood); color: var(--royal-gold-light);">
                                <i class="fad fa-users-slash"></i> {{ __('Đã đầy') }}
                            </button>
                        @endif

                        {{-- Action Buttons cho Creator/Admin --}}
                        @if(auth()->user()->is_admin || $tournament->user_id === auth()->id())
                            <span style="color: var(--royal-wood);" class="mx-2">|</span>

                            @if($isOpen && $tournament->users->count() >= 2)
                            <form action="{{ localized_url('tournaments.generate', ['slug' => $tournament->slug]) }}" method="POST" class="d-inline-block">
                                @csrf
                                <button type="submit" class="btn btn-success font-weight-bold pulse">
                                    <i class="fad fa-sitemap"></i> {{ __('Bốc thăm') }}
                                </button>
                            </form>
                            @endif

                            <a href="{{ localized_url('tournaments.edit', ['slug' => $tournament->slug]) }}" class="btn font-weight-bold d-inline-block" style="background: linear-gradient(to bottom, #d4af37, #b89020); color: var(--royal-red); border: 1px solid #fff; border-radius: 4px;">
                                <i class="fad fa-edit"></i>
                            </a>

                            {{-- Logic Xóa hoặc Hủy --}}
                            @if($tournament->status === 'open')
                                @if($tournament->users->count() <= 1)
                                    <form action="{{ localized_url('tournaments.destroy', ['slug' => $tournament->slug]) }}" method="POST" class="d-inline-block delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger font-weight-bold" style="border-radius: 4px;" title="{{ __('Xóa vĩnh viễn') }}">
                                            <i class="fad fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ localized_url('tournaments.cancel', ['slug' => $tournament->slug]) }}" method="POST" class="d-inline-block cancel-form">
                                        @csrf
                                        <button type="submit" class="btn btn-warning font-weight-bold" style="border-radius: 4px; background: #c27a29; color: #fff; border: 1px solid #fff;" title="{{ __('Hủy giải đấu') }}">
                                            <i class="fad fa-ban"></i>
                                        </button>
                                    </form>
                                @endif
                            @elseif($tournament->status === 'cancelled')
                                <form action="{{ localized_url('tournaments.destroy', ['slug' => $tournament->slug]) }}" method="POST" class="d-inline-block delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger font-weight-bold" style="border-radius: 4px;" title="{{ __('Xóa vĩnh viễn (Đã Hủy)') }}">
                                        <i class="fad fa-trash-alt"></i>
                                    </button>
                                </form>
                            @endif
                        @endif
                    @else
                        <div class="d-inline-flex align-items-center p-2 shadow" style="background: #2a1910; border-radius: 4px; border: 1px solid var(--royal-gold);">
                            <span class="small mr-3 ml-2" style="color: var(--royal-gold-light);"><i class="fad fa-lock-alt"></i> {{ __('Yêu cầu tài khoản') }}</span>
                            <a href="{{ localized_url('register') }}" class="btn font-weight-bold btn-sm mr-2 pulse-gold" style="background: linear-gradient(to bottom, #d4af37, #b89020); color: var(--royal-red); border: 1px solid #fff;">
                                <i class="fad fa-user-plus"></i> {{ __('Đăng ký') }}
                            </a>
                            <a href="{{ localized_url('login') }}" class="btn btn-sm" style="color: var(--royal-gold); border: 1px solid var(--royal-gold);">
                                {{ __('Đăng nhập') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- DANH SÁCH NGƯỜI CHƠI --}}
    <div class="card mb-4 shadow-lg" style="background: #2a1910; border-radius: 4px; overflow: hidden; border: 1px solid var(--royal-gold);">
        <div class="card-header border-0 py-3" style="background: linear-gradient(to bottom, #8a1515, #5c0a0a); border-bottom: 2px solid var(--royal-gold) !important;">
            <h5 class="mb-0 font-weight-bold" style="color: var(--royal-gold); text-transform: uppercase;">
                <i class="fad fa-users-cog"></i> {{ __('Danh sách kỳ thủ đã đăng ký') }}
            </h5>
        </div>
        <div class="card-body p-0">
            @if($tournament->users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <thead style="border-bottom: 2px solid var(--royal-gold);">
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
                                <tr>
                                    <td class="pl-4 align-middle" style="color: #aa8c4a;">{{ $index + 1 }}</td>
                                    <td class="align-middle">
                                        <a href="{{ localized_url('app.player', ['id' => $player->id]) }}" class="font-weight-bold" style="color: var(--royal-gold); text-decoration: none;">
                                            {{ $player->name }}
                                        </a>
                                    </td>
                                    <td class="font-weight-bold align-middle" style="color: #ff3333;">{{ intval($player->elo) }}</td>
                                    <td class="align-middle" style="color: var(--royal-gold-light);">{{ $player->pivot->created_at->format('d/m/Y h:i A') }}</td>
                                    <td class="pr-4 text-right align-middle">
                                        @if($player->last_seen_at && \Carbon\Carbon::parse($player->last_seen_at)->diffInMinutes() < 5)
                                            <span class="badge-status badge-online"><i class="fad fa-circle"></i> Online</span>
                                        @else
                                            <span class="badge-status badge-offline"><i class="fad fa-circle"></i> Offline</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-4 text-center" style="background: #2a1910;">
                    <i class="fad fa-box-open fa-2x" style="color: var(--royal-wood);"></i>
                    <p class="mb-0" style="color: var(--royal-gold-light);">{{ __('Chưa có kỳ thủ nào tham gia giải đấu này.') }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- SƠ ĐỒ THI ĐẤU --}}
    @if($tournament->status !== 'open' && $rounds->count() > 0)
        <h4 class="mb-4" style="color: var(--royal-gold); font-family: 'Texturina', serif; text-transform: uppercase; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);">
            <i class="fad fa-sitemap" style="color: var(--royal-red-light);"></i> {{ __('Sơ đồ thi đấu') }}
        </h4>
        <div class="bracket-container custom-scrollbar">

            @foreach($rounds as $roundNumber => $matches)
                <div class="bracket-round">
                    <h6 class="h5 text-center mb-0" style="color: var(--royal-gold-light); font-family: 'Texturina', serif; text-transform: uppercase;">{{ __('Vòng') }} {{ $roundNumber }}</h6>

                    @foreach($matches as $match)
                        @php
                            $hostIsWinner = $match->result === 1;
                            $guestIsWinner = $match->result === -1;
                        @endphp
                        <div class="match-card">
                            <div class="match-player red-side {{ $hostIsWinner ? 'match-winner' : '' }}">
                                <span><i class="fad fa-chess-pawn"></i> {{ $match->host->name ?? __('Chờ đối thủ...') }}</span>
                                @if($hostIsWinner) <i class="fad fa-crown" style="color: var(--royal-gold); filter: drop-shadow(0 0 5px rgba(212, 175, 55, 0.8));"></i> @endif
                            </div>

                            <div class="match-player black-side {{ $guestIsWinner ? 'match-winner' : '' }}">
                                <span><i class="fad fa-chess-pawn"></i> {{ $match->guest->name ?? __('Chờ đối thủ...') }}</span>
                                @if($guestIsWinner) <i class="fad fa-crown" style="color: var(--royal-gold); filter: drop-shadow(0 0 5px rgba(212, 175, 55, 0.8));"></i> @endif
                            </div>
                            @php
                                $roomUrl = localized_url('room.watch', ['code' => $match->code]);
                                if (auth()->check()) {
                                    if (auth()->id() === $match->host_id && $match->result === null) {
                                        $roomUrl = localized_url('room.red', ['code' => $match->code]);
                                    } elseif (auth()->id() === $match->guest_id && $match->result === null) {
                                        $roomUrl = localized_url('room.black', ['code' => $match->code]);
                                    } elseif ($match->result !== null) {
                                        $roomUrl = localized_url('room.watch', ['code' => $match->code]);
                                    }
                                }
                            @endphp
                            <a href="{{ $roomUrl }}" class="bracket-room-link font-weight-bold">
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
        <div class="p-5 mt-4 text-center shadow-lg" style="background: var(--glass-bg-dark); backdrop-filter: var(--glass-blur); -webkit-backdrop-filter: var(--glass-blur); border: 1px solid var(--glass-border); border-top: 2px solid rgba(255, 215, 0, 0.5); border-radius: 12px; box-shadow: var(--liquid-shadow), inset 0 2px 15px var(--liquid-highlight);">
            <i class="fad fa-project-diagram fa-4x mb-3" style="color: var(--royal-gold); filter: drop-shadow(0 0 10px rgba(212,175,55,0.5));"></i>
            <h5 style="color: var(--royal-gold); font-family: 'Texturina', serif; text-transform: uppercase;">{{ __('Sơ đồ giải đấu chưa được tạo.') }}</h5>
            <p style="color: var(--royal-gold-light);">{{ __('Sơ đồ sẽ xuất hiện khi giải đấu bắt đầu và bốc thăm hoàn tất.') }}</p>
        </div>
    @endif
</div>
<script>
    $(document).ready(function() {
        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            var form = this;
            bootbox.confirm({
                message: "{{ __('Giải đấu sẽ bị xóa vĩnh viễn khỏi hệ thống. Bạn có chắc chắn?') }}",
                centerVertical: true,
                buttons: {
                    confirm: { label: '{{ __("Có, Xóa") }}', className: 'btn-danger' },
                    cancel: { label: '{{ __("Hủy thao tác") }}', className: 'btn-dark' }
                },
                callback: function(result) {
                    if (result) form.submit();
                }
            });
        });

        $('.cancel-form').on('submit', function(e) {
            e.preventDefault();
            var form = this;
            bootbox.confirm({
                message: "{{ __('Giải đấu này đã có người đăng ký. Bạn có chắc chắn muốn Hủy giải đấu này không? Trạng thái sẽ chuyển thành Đã Hủy.') }}",
                centerVertical: true,
                buttons: {
                    confirm: { label: '{{ __("Có, Hủy giải") }}', className: 'btn-warning' },
                    cancel: { label: '{{ __("Giữ lại") }}', className: 'btn-dark' }
                },
                callback: function(result) {
                    if (result) form.submit();
                }
            });
        });
    });
</script>
@endsection
