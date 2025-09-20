@extends('layout.app')

@section('content')
<div class="container">
    <div class="row justify-content-center text-center mb-4">
        <div class="col-12">
            <!-- CO_res -->
            <ins class="adsbygoogle"
            style="display:block"
            data-ad-client="ca-pub-3585118770961536"
            data-ad-slot="7831723879"
            data-ad-format="auto"
            data-full-width-responsive="true"></ins>
            <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-gamepad-alt"></i> Thi đấu xếp hạng
                    @include('layout.partials.app.tourBtn')
                </div>
                <div class="card-body">
                    @include('layout.partials.app.createRoom')
                    <span style="background-color: #ffffff; margin-top: -70px;" class="d-block w-100 pb-5 mb-5" id="result-board"></span>
                    <h2 data-step="2" data-intro="Danh sách 10 kỳ thủ nhiều điểm nhất"><i class="fas fa-medal"></i> TOP 10</h2>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="rankingTable">
                            <thead>
                                <tr>
                                    <th scope="col">Hạng</th>
                                    <th scope="col">Tên</th>
                                    <th scope="col">Elo</th>
                                    <th scope="col">Ngày giờ gia nhập</th>
                                    <th scope="col">Lần trực tuyến gần nhất</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($matchUsers as $user)
                                <tr data-user="{{ $user->id }}">
                                    <th scope="row">{!! app('App\Http\Controllers\UserController')::renderUserRank($user->id) !!}</th>
                                    <td class="name">{!! app('App\Http\Controllers\UserController')::renderPlayerName($user->id) !!}</td>
                                    <td class="elo">{!! app('App\Http\Controllers\UserController')::renderElo($user->id) !!}</td>
                                    <td class="room-time">{{ $user->created_at }}</td>
                                    <td class="room-time">{{ $user->last_seen_at }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <span style="background-color: #ffffff; margin-top: -70px;" class="d-block w-100 pb-5 mb-5" id="result-board"></span>
                    <h2 data-step="3" data-intro="Danh sách các ván đấu đang diễn ra" class="mt-3"><i class="fas fa-list"></i> {{ $playingRooms->total() }} ván cờ đang thi đấu ({!! app('App\Http\Controllers\UserController')::renderOnlinePlayers() !!})</h2>
                    <div class="table-responsive mb-3">
                        <table class="table table-striped table-hover" id="results-table">
                            <thead>
                                <tr>
                                    <th scope="col">Tên phòng</th>
                                    <th scope="col">Chủ phòng</th>
                                    <th scope="col">Khách</th>
                                    <th scope="col">Tới lượt</th>
                                    <th scope="col">Thi đấu</th>
                                    <th scope="col">Lần cuối chơi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{ $playingRooms->links('vendor.pagination.match') }}
                                @foreach($playingRooms as $room)
                                <tr data-code="{{ $room->code }}" data-fen="{{ $room->fen }}" data-name="{{ $room->name }}">
                                    <th scope="row" class="roomCode"><a class="text-danger showPromotion animate" href="{{ url('/phong/') }}/{{ $room->code }}/theo-doi">{{ ((isset($room->name) && $room->name != '') ? $room->name: $room->code) }}</a></th>
                                    <td class="host-name">
                                        {!! app('App\Http\Controllers\UserController')::renderPlayerName($room->host_id) !!}
                                    </td>
                                    <td class="guest-name">
                                        {!! app('App\Http\Controllers\UserController')::renderPlayerName($room->guest_id) !!}
                                    </td>
                                    <td class="text-center">
                                        @if (str_contains($room->fen, ' r '))
                                        <span class="text-danger">Đỏ</span>
                                        @elseif (str_contains($room->fen, ' b '))
                                        <span class="text-dark">Đen</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if (!isset($room->result))
                                            @if (auth()->check())
                                                @if (isset($room->guest_id))
                                                <a class="btn btn-sm btn-dark" href="javascript:joinMatch('{{ $room->code }}')"><i class="fad fa-mouse"></i> Chơi</a>
                                                @else
                                                <a class="btn btn-sm btn-danger pulse-red" href="javascript:joinMatch('{{ $room->code }}')"><i class="fad fa-mouse"></i> Chơi</a>
                                                @endif
                                            @else
                                                @if (isset($room->guest_id))
                                                <a class="btn btn-sm btn-dark showPromotion" href="{{ url('/dang-nhap') }}"><i class="fad fa-sign-in"></i> Đăng nhập</a>
                                                @else
                                                <a class="btn btn-sm btn-danger pulse-red showPromotion" href="{{ url('/dang-nhap') }}"><i class="fad fa-sign-in"></i> Đăng nhập</a>
                                                @endif
                                            @endif
                                        @else
                                            <span class="text-danger">Đã đấu xong</span>
                                        @endif
                                    </td>
                                    <td class="room-time">{{ $room->modified_at }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if (auth()->check())
                    <script>
                    function joinMatch(roomCode) {
                        var hostId = '';
                        var guestId = '';
                        $.ajax({
                            type: "POST",
                            url: '{{ url('/api') }}/getRoomIds',
                            data: {
                                'ma-phong': roomCode
                            },
                            dataType: 'json'
                        }).done(function(data){
                            hostId = data.host_id;
                            guestId = data.guest_id;
                            console.log(data);
                            console.log(data.host_id);
                            console.log(data.guest_id);
                            if (hostId != '{{ auth()->id() }}' && guestId != '{{ auth()->id() }}') {
                                $.ajax({
                                    type: "POST",
                                    url: '{{ url('/api') }}/joinRoom',
                                    data: {
                                        'ma-phong': roomCode,
                                        'guest_id': '{{ auth()->id() }}'
                                    },
                                    dataType: 'text'
                                }).done(function() {
                                    bootbox.alert({
                                        message: "Hãy chuẩn bị vào phòng!",
                                        size: 'small',
                                        centerVertical: true,
                                        closeButton: false,
                                        buttons: {
                                            ok: {
                                                className: 'btn-danger pulse-red',
                                                label: 'Oki'
                                            }
                                        },
                                        callback: function(){
                                            window.location.href = '{{ url('/phong/') }}' + '/' + roomCode + '/khach';
                                        }
                                    });
                                });
                            } else if (guestId == '{{ auth()->id() }}') {
                                bootbox.alert({
                                    message: "Mời bạn quay lại phòng!",
                                    size: 'small',
                                    centerVertical: true,
                                    closeButton: false,
                                    buttons: {
                                        ok: {
                                            className: 'btn-danger pulse-red',
                                            label: 'Oki'
                                        }
                                    },
                                    callback: function(){
                                        window.location.href = '{{ url('/phong/') }}' + '/' + roomCode + '/khach';
                                    }
                                });
                            } else if (hostId == '{{ auth()->id() }}') {
                                bootbox.alert({
                                    message: "Mời bạn vào lại phòng của mình!",
                                    size: 'small',
                                    centerVertical: true,
                                    closeButton: false,
                                    buttons: {
                                        ok: {
                                            className: 'btn-danger pulse-red',
                                            label: 'Oki'
                                        }
                                    },
                                    callback: function(){
                                        window.location.href = '{{ url('/phong/') }}' + '/' + roomCode;
                                    }
                                });
                            }
                        });
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

                    {{-- {{ __('You are logged in!') }} --}}
                </div>
            </div>
        </div>
    </div>
</div>
@include('layout.partials.app.fb')
@endsection
