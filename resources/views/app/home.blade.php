@extends('layouts.app')


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Royal Glassmorphism Card -->
            <div class="card shadow-lg mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-gamepad-alt text-gold"></i> {{ __("Thi đấu xếp hạng") }}</h4>
                    @include('layouts.partials.app.tourBtn')
                </div>
                <div class="card-body p-0">
                    <div class="p-3 border-bottom" style="border-color: rgba(212, 175, 55, 0.2) !important;">
                        @include('layouts.partials.app.createRoom')
                        <h2 data-step="2" data-intro="{{ __("Danh sách 10 kỳ thủ nhiều điểm nhất") }}" class="mt-4 mb-3 h4">
                            <i class="fas fa-medal text-gold"></i> {{ __("TOP 10") }}
                        </h2>

                        <!-- Top 10 Table -->
                        <div class="table-responsive rounded border border-warning" style="border-color: rgba(212, 175, 55, 0.3) !important;">
                            <table class="table table-hover table-sm mb-0" id="rankingTable">
                                <thead>
                                    <tr>
                                        <th class="text-center" scope="col">{{ __("Hạng") }}</th>
                                        <th class="text-center" scope="col">{{ __("Tên") }}</th>
                                        <th class="text-center" scope="col">Elo</th>
                                        <th class="text-center" scope="col">Karma</th>
                                        <th class="text-center" scope="col">{{ __("Ngày giờ gia nhập") }}</th>
                                        <th class="text-center" scope="col">{{ __("Lần trực tuyến gần nhất") }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($matchUsers as $user)
                                    <tr data-user="{{ $user->id }}">
                                        <td class="text-center font-weight-bold">
                                            <span class="badge badge-status" style="background: linear-gradient(to bottom, #d4af37, #b89020); color: #0b0c10; box-shadow: 0 0 5px rgba(212, 175, 55, 0.6);"><i class="fas fa-trophy"></i> {!! $userPresenter->renderUserRank($user->id) !!}</span>
                                        </td>
                                        <td class="text-center name">{!! $userPresenter->renderPlayerName($user->id) !!}</td>
                                        <td class="text-center elo text-gold font-weight-bold">{!! $userPresenter->renderElo($user->id) !!}</td>
                                        <td class="text-center karma text-info font-weight-bold"><i class="fas fa-seedling"></i> {{ $user->karma ?? 0 }}</td>
                                        <td class="text-center room-time">{{ $user->created_at }}</td>
                                        <td class="text-center room-time">{{ $user->last_seen_at }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <h2 data-step="3" data-intro="{{ __("Danh sách các ván đấu đang diễn ra") }}" class="mt-5 mb-3 h4">
                            <i class="fas fa-list text-gold"></i> {{ $playingRooms->total() }} {{ __("ván cờ đang thi đấu") }} <small class="text-muted">({!! $userPresenter->renderOnlinePlayersCount() !!})</small>
                        </h2>
                    </div>

                    <!-- Playing Rooms Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0" id="results-table">
                            <thead>
                                <tr>
                                    <th class="text-center" scope="col">{{ __("Tên phòng") }}</th>
                                    <th class="text-center" scope="col">{{ __("Chủ phòng") }}</th>
                                    <th class="text-center" scope="col">{{ __("Khách") }}</th>
                                    <th class="text-center" scope="col">{{ __("Tới lượt") }}</th>
                                    <th class="text-center" scope="col">{{ __("Thi đấu") }}</th>
                                    <th class="text-center" scope="col">{{ __("Lần cuối chơi") }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($playingRooms as $room)
                                <tr data-code="{{ $room->code }}" data-fen="{{ $room->fen }}" data-name="{{ $room->name }}">
                                    <td class="text-center room-code">
                                        <span><a class="animate text-gold" href="{{ localized_url('room.watch', ['code' => $room->code]) }}">{{ ((isset($room->name) && $room->name != '') ? $room->name: $room->code) }}</a></span>
                                    </td>
                                    <td class="text-center host-name">
                                        {!! $userPresenter->renderPlayerName($room->host_id) !!}
                                    </td>
                                    <td class="text-center guest-name">
                                        {!! $userPresenter->renderPlayerName($room->guest_id) !!}
                                    </td>
                                    <td class="text-center">
                                        @if (str_contains($room->fen, ' r '))
                                        <span class="badge badge-status" style="background: linear-gradient(to bottom, #8a1515, #5c0a0a); color: var(--royal-gold); border: 1px solid var(--royal-gold); box-shadow: 0 0 8px rgba(138, 21, 21, 0.6);"><i class="fas fa-chess-knight"></i> {{ __("Đỏ") }}</span>
                                        @elseif (str_contains($room->fen, ' b '))
                                        <span class="badge badge-status" style="background: linear-gradient(145deg, #252a36, #121418); color: var(--royal-gold-light); border: 1px solid rgba(212, 175, 55, 0.3); box-shadow: 0 0 8px rgba(0, 0, 0, 0.8);"><i class="fas fa-chess-knight"></i> {{ __("Đen") }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if (!isset($room->result))
                                            @if (auth()->check())
                                                @if (isset($room->guest_id))
                                                <a class="btn btn-sm btn-dark" href="javascript:joinMatch('{{ $room->code }}')"><i class="fad fa-mouse"></i> {{ __("Chơi") }}</a>
                                                @else
                                                <a class="btn btn-sm btn-danger pulse-red" href="javascript:joinMatch('{{ $room->code }}')"><i class="fad fa-mouse"></i> {{ __("Chơi") }}</a>
                                                @endif
                                            @else
                                                @if (isset($room->guest_id))
                                                <a class="btn btn-sm btn-dark showPromotion" href="{{ localized_url('login') }}"><i class="fad fa-sign-in"></i> {{ __("Đăng nhập") }}</a>
                                                @else
                                                <a class="btn btn-sm btn-danger pulse-red showPromotion" href="{{ localized_url('login') }}"><i class="fad fa-sign-in"></i> {{ __("Đăng nhập") }}</a>
                                                @endif
                                            @endif
                                        @else
                                            <span class="badge badge-status badge-offline p-2 text-danger"><i class="fas fa-flag-checkered"></i> {{ __("Đã đấu xong") }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center room-time">{{ $room->modified_at }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($playingRooms->hasPages())
                <div class="card-footer d-flex justify-content-center pt-3 pb-1 border-top" style="border-color: rgba(212, 175, 55, 0.2) !important;">
                    {{ $playingRooms->links('vendor.pagination.match') }}
                </div>
                @endif
            </div>

            @if (auth()->check())
            <script>
            async function joinMatch(roomCode) {
                const currentUserId = Number('{{ auth()->id() }}');
                const hostUrl = '{{ url(__('/phong/')) }}/' + roomCode;
                const guestUrl = '{{ url(__('/phong/')) }}/' + roomCode + '{{ __('/khach') }}';

                try {
                    // 1. Fetch host and guest IDs for the room
                    const data = await $.ajax({
                        type: "POST",
                        url: '{{ url('/api/getRoomIds') }}',
                        data: { 'ma-phong': roomCode },
                        dataType: 'json'
                    });

                    const hostId = data?.host_id ? Number(data.host_id) : null;
                    const guestId = data?.guest_id ? Number(data.guest_id) : null;

                    let alertMessage = '';
                    let targetUrl = '';

                    // 2. Determine action based on current user role
                    if (hostId === currentUserId) {
                        alertMessage = "{{ __('Mời bạn vào lại phòng của mình!') }}";
                        targetUrl = hostUrl;
                    } else if (guestId === currentUserId) {
                        alertMessage = "{{ __('Mời bạn quay lại phòng!') }}";
                        targetUrl = guestUrl;
                    } else {
                        // New user joining as guest
                        await $.ajax({
                            type: "POST",
                            url: '{{ url('/api/joinRoom') }}',
                            data: {
                                'ma-phong': roomCode,
                                'guest_id': currentUserId
                            },
                            dataType: 'text'
                        });

                        alertMessage = "{{ __('Hãy chuẩn bị vào phòng!') }}";
                        targetUrl = guestUrl;
                    }

                    // 3. Show message and redirect upon user confirmation
                    await bootboxAlertAsync(alertMessage);
                    window.location.href = targetUrl;

                } catch (error) {
                    console.error('Failed to join match:', error);
                }
            }
            </script>
            <script>
                $(document).ajaxStart(function(){
                    $('body').addClass('waiting');
                }).ajaxComplete(function(){
                    $('body').removeClass('waiting');
                })
            </script>
            @endif
        </div>
    </div>
</div>
@endsection
