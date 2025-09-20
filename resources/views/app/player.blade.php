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
                    <img src="{{ Avatar::create($player->name)->setDimension(48)->setFontSize(24) }}" />
                    @if ($player->id == auth()->id() && !str_contains(url()->current(), url('/ky-thu').'/'))
                    Hồ sơ của tôi
                    @else
                    Hồ sơ kỳ thủ
                    @endif
                    {!! app('App\Http\Controllers\UserController')::onlineStatus($player->id) !!}
                    @include('layout.partials.app.tourBtn')
                    @if (auth()->check())
                        @if (auth()->id() != $player->id)
                            <a class="btn btn-danger text-light mr-1" style="width: 140px;" href="javascript:compete({{ $player->id }});"><i class="far fa-mouse"></i> Thách đấu</a>
                        @else
                            <a class="btn btn-dark text-light mr-1" style="width: 140px; cursor: not-allowed !important;" href="javascript:void(0);"><i class="far fa-ban"></i> Thách đấu</a>
                        @endif
                    @else
                        <a class="btn btn-danger text-light mr-1" style="width: 140px;" href=" {{ url('/dang-nhap') }} "><i class="far fa-sign-in"></i> Thách đấu</a>
                    @endif
                </div>
                <div class="card-body">
                    <h5>Tên: {{ $player->name }}</h5>
                    @if ($player->id == auth()->id() && !str_contains(url()->current(), url('/ky-thu').'/'))
                    <h5>Email: {!! app('App\Http\Controllers\UserController')::renderPlayerEmail($player->id) !!}</h5>
                    @endif
                    <h5>Ngày giờ gia nhập: {{ $player->created_at }}</h5>
                    <h5>Lần trực tuyến gần nhất: {{ $player->last_seen_at }}</h5>
                    <h5>Thứ hạng: {!! app('App\Http\Controllers\UserController')::renderPlayerRank($player->id) !!}</h5>
                    <h5>Elo: <span id="elo">{!! app('App\Http\Controllers\UserController')::renderElo($player->id) !!}</span></h5>
                    <h5>Số trận thắng: <span id="winPoints">{!! app('App\Http\Controllers\UserController')::renderWinMatchPoints($player->id) !!}</span></h5>
                    <h5>Số trận hòa: <span id="drawPoints">{!! app('App\Http\Controllers\UserController')::renderDrawMatchPoints($player->id) !!}</span></h5>
                    <h5>Số trận thua: <span id="losePoints">{!! app('App\Http\Controllers\UserController')::renderLoseMatchPoints($player->id) !!}</span></h5>
                    <h5>Tổng số trận đã đấu xong: <span id="totalPoints">{!! app('App\Http\Controllers\UserController')::renderTotalMatchPoints($player->id) !!}</span></h5>
                    @if ($player->id == auth()->id() && !str_contains(url()->current(), url('/ky-thu').'/'))
                    <p class="w-100 text-left">
                        <a href="{{ url('/doi-ten') }}" class="btn btn-lg btn-dark showPromotion"><i class="fad fa-user-edit"></i> Đổi tên</a>
                        <a href="{{ url('/doi-mat-khau') }}" class="btn btn-lg btn-dark showPromotion"><i class="fad fa-lock-alt"></i> Đổi mật khẩu</a>
                        <a href="{{ url('/doi-giao-dien') }}" class="btn btn-lg btn-dark showPromotion"><i class="fad fa-palette"></i> Đổi giao diện</a>
                    </p>
                    @endif
                    @if (auth()->check())
                    <script>
                    function compete(guestId) {
                        var maPhong = '{{ md5(time()) }}';
                        $.ajax({
                            type: "POST",
                            url: '{{ url('/api') }}/hasRoomcode',
                            data: {
                                'ma-phong': maPhong
                            },
                            dataType: 'text'
                        }).done(function(data){
                            if (data == 'no') {
                                bootbox.prompt({
                                    title: "Mời đặt tên cho Phòng thi đấu:",
                                    locale: 'vi',
                                    centerVertical: true,
                                    closeButton: false,
                                    maxlength: 32,
                                    buttons: {
                                        confirm: {
                                            label: '<i class="fas fa-check"></i> Đặt tên',
                                            className: 'btn-danger'
                                        },
                                        cancel: {
                                            className: 'btn-dark text-light'
                                        }
                                    },
                                    callback: function(roomName){
                                    if (roomName != null) {
                                        if (roomName.trim().length === 0 || roomName.length === 0) {
                                            bootbox.alert({
                                                message: "Vui lòng đặt tên cho phòng!",
                                                size: 'small',
                                                locale: 'vi',
                                                centerVertical: true,
                                                closeButton: false,
                                                buttons: {
                                                    ok: {
                                                        className: 'btn-danger'
                                                    }
                                                },
                                                callback: function () {
                                                    $('#create-room').trigger('click');
                                                }
                                            });
                                        } else {
                                            $.ajax({
                                                type: "POST",
                                                url: '{{ url('/api') }}/compete',
                                                data: {
                                                    'ma-phong': maPhong,
                                                    'ten-phong': roomName,
                                                    'FEN': '{{ env('INITIAL_FEN') }}',
                                                    'pass': '',
                                                    'host_id': '{{ auth()->id() }}',
                                                    'guest_id': guestId
                                                },
                                                dataType: 'text'
                                            }).done(function() {
                                                bootbox.alert({
                                                    message: "Bạn đã tạo phòng thành công.",
                                                    size: 'small',
                                                    centerVertical: true,
                                                    closeButton: false,
                                                    buttons: {
                                                        ok: {
                                                            className: 'btn-danger',
                                                            label: 'Oki'
                                                        }
                                                    },
                                                    callback: function(){
                                                        $.ajax({
                                                            type: "POST",
                                                            url: '{{ url('/api') }}/competeMail',
                                                            data: {
                                                                'ma-phong': maPhong,
                                                                'ten-phong': roomName,
                                                                'host_id': '{{ auth()->id() }}',
                                                                'guest_id': guestId
                                                            },
                                                            dataType: 'json'
                                                        }).done(function(mailData) {
                                                            bootbox.alert({
                                                                message: mailData.message,
                                                                size: 'small',
                                                                centerVertical: true,
                                                                closeButton: false,
                                                                buttons: {
                                                                    ok: {
                                                                        className: 'btn-danger',
                                                                        label: 'Oki'
                                                                    }
                                                                },
                                                                callback: function(){
                                                                    window.location.href = '{{ url('/phong/') }}' + '/' + maPhong;
                                                                }
                                                            });
                                                        });
                                                    }
                                                });
                                            });
                                        }
                                    }
                                }
                            });
                            } else if (data == 'yes') {
                                bootbox.alert({
                                    message: "Mã phòng bị trùng, vui lòng thử lại.",
                                    size: 'small',
                                    centerVertical: true,
                                    closeButton: false,
                                    buttons: {
                                        ok: {
                                            className: 'btn-danger',
                                            label: 'Oki'
                                        }
                                    },
                                    callback: function(){
                                        setTimeout(() => {
                                            location.reload();
                                        }, 500);
                                    }
                                });
                            }
                        });
                    }
                    </script>
                    @endif
                    @if ($playerRooms->total() > 0)
                    <span style="background-color: #ffffff; margin-top: -70px;" class="d-block w-100 pb-5 mb-5" id="result-board"></span>
                    <h2 data-step="1" data-intro="Danh sách các trận đấu của kỳ thủ '{{ $player->name }}'" class="mt-3"><i class="fas fa-list-ul"></i> Kết quả thi đấu</h2>
                    <div class="table-responsive mb-3">
                        <table class="table table-striped table-hover" id="results-table">
                            <thead>
                                <tr>
                                    <th scope="col">Tên phòng</th>
                                    <th scope="col">Chủ phòng</th>
                                    <th scope="col">Khách</th>
                                    <th scope="col">Tới lượt</th>
                                    <th scope="col">Kết quả</th>
                                    <th scope="col">Thi đấu</th>
                                    <th scope="col">Lần cuối chơi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{ $playerRooms->links('vendor.pagination.match') }}
                                @foreach($playerRooms as $room)
                                <tr data-code="{{ $room->code }}" data-fen="{{ $room->fen }}">
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
                                        @if ($room->result == '1')
                                            Chủ phòng thắng
                                        @elseif ($room->result == '0')
                                            Hòa
                                        @elseif ($room->result == '-1')
                                            Khách thắng
                                        @else
                                            Chưa có kết quả
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
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <script>

                    </script>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@include('layout.partials.app.fb')
@endsection
