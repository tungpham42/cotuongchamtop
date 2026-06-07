@extends('layout.app')

@php
    // Cache the image to prevent high server disk I/O on every page load
    $avatarDir = public_path('players');
    $avatarPath = $avatarDir . '/' . md5($player->email) . '.jpg';

    if (!file_exists($avatarPath)) {
        if (!is_dir($avatarDir)) {
            mkdir($avatarDir, 0755, true);
        }
        Avatar::create($player->name)->setDimension(200)->setFontSize(100)->save($avatarPath, 100);
    }
@endphp

@section('og_image', url('/players/' . md5($player->email) . '.jpg'))
@section('og_image_width', '200')
@section('og_image_height', '200')

@section('content')
<div class="container">
    @if ($showAds ?? true)
    <div class="row justify-content-center text-center mb-4">
        <div class="col-12">
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
    @endif
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <img src="{{ Avatar::create($player->name)->setDimension(48)->setFontSize(24) }}" />
                    @if ($player->id == auth()->id() && !str_contains(url()->current(), url('/ky-thu').'/'))
                    {{ __("Hồ sơ của tôi") }}
                    @else
                    {{ __("Hồ sơ kỳ thủ") }}
                    @endif
                    {!! app('App\Http\Controllers\UserController')::onlineStatus($player->id) !!}
                    @include('layout.partials.app.tourBtn')
                    @if (auth()->check())
                        @if (auth()->id() != $player->id)
                            <a class="btn btn-danger text-light mr-1" style="width: 140px;" href="javascript:compete({{ $player->id }});"><i class="far fa-mouse"></i> {{ __("Thách đấu") }}</a>
                        @else
                            <a class="btn btn-dark text-light mr-1" style="width: 140px; cursor: not-allowed !important;" href="javascript:void(0);"><i class="far fa-ban"></i> {{ __("Thách đấu") }}</a>
                        @endif
                    @else
                        <a class="btn btn-danger text-light mr-1" style="width: 140px;" href=" {{ localized_url('login') }} "><i class="far fa-sign-in"></i> {{ __("Thách đấu") }}</a>
                    @endif
                </div>
                <div class="card-body">
                    <h5>{{ __("Tên:") }} {{ $player->name }}</h5>
                    @if ($player->id == auth()->id() && !str_contains(url()->current(), url('/ky-thu').'/'))
                    <h5>{{ __("Email:") }} {!! app('App\Http\Controllers\UserController')::renderPlayerEmail($player->id) !!}</h5>
                    @endif
                    <h5>{{ __("Ngày giờ gia nhập:") }} {{ $player->created_at }}</h5>
                    <h5>{{ __("Lần trực tuyến gần nhất:") }} {{ $player->last_seen_at }}</h5>
                    <h5>{{ __("Thứ hạng:") }} {!! app('App\Http\Controllers\UserController')::renderPlayerRank($player->id) !!}</h5>
                    <h5>Elo: <span id="elo">{!! app('App\Http\Controllers\UserController')::renderElo($player->id) !!}</span></h5>
                    <h5>{{ __("Số trận thắng:") }} <span id="winPoints">{!! app('App\Http\Controllers\UserController')::renderWinMatchPoints($player->id) !!}</span></h5>
                    <h5>{{ __("Số trận hòa:") }} <span id="drawPoints">{!! app('App\Http\Controllers\UserController')::renderDrawMatchPoints($player->id) !!}</span></h5>
                    <h5>{{ __("Số trận thua:") }} <span id="losePoints">{!! app('App\Http\Controllers\UserController')::renderLoseMatchPoints($player->id) !!}</span></h5>
                    <h5>{{ __("Tổng số trận đã đấu xong:") }} <span id="totalPoints">{!! app('App\Http\Controllers\UserController')::renderTotalMatchPoints($player->id) !!}</span></h5>
                    @if (auth()->check() && $player->id === auth()->id())
                    <div id="standard-plan" class="alert alert-warning d-flex align-items-center justify-content-between mt-3">
                        <div class="text-left">
                            <strong>{{ __("Gói hiện tại:") }}</strong>
                            @if (auth()->user()->isStandard())
                                {{ __("Standard (đã ẩn quảng cáo)") }}
                                @if (auth()->user()->subscription_started_at)
                                    <small class="text-muted">({{ __("kích hoạt") }} {{ auth()->user()->subscription_started_at->format('d/m/Y H:i') }})</small>
                                @endif
                            @else
                                {{ __("Miễn phí (đang hiển thị quảng cáo)") }}
                                @php $latestPayment = auth()->user()->payosPayments()->latest()->first(); @endphp
                                @if ($latestPayment && $latestPayment->status !== 'paid')
                                    <br><small class="text-muted">{{ __("Giao dịch gần nhất:") }} {{ $latestPayment->status }} - mã {{ $latestPayment->order_code }}</small>
                                @endif
                            @endif
                        </div>
                        @if (!auth()->user()->isStandard())
                        <form method="POST" action="{{ route('payos.standard') }}" class="mb-0">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="far fa-crown"></i>
                                {{ __("Nâng cấp Standard -") }} {{ number_format(config('payos.standard_amount', 100000), 0, ',', '.') }}đ
                            </button>
                        </form>
                        @else
                        <span class="badge badge-success p-2">Ads Free</span>
                        @endif
                    </div>
                    @endif
                    @if ($player->id == auth()->id() && !str_contains(url()->current(), url('/ky-thu').'/'))
                    <p class="w-100 text-left">
                        <a href="{{ url('/doi-ten') }}" class="btn btn-lg btn-dark showPromotion"><i class="fad fa-user-edit"></i> {{ __("Đổi tên") }}</a>
                        <a href="{{ url('/doi-mat-khau') }}" class="btn btn-lg btn-dark showPromotion"><i class="fad fa-lock-alt"></i> {{ __("Đổi mật khẩu") }}</a>
                        <a href="{{ url('/doi-giao-dien') }}" class="btn btn-lg btn-dark showPromotion"><i class="fad fa-palette"></i> {{ __("Đổi giao diện") }}</a>
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
                                    title: "{{ __("Mời đặt tên cho Phòng thi đấu:") }}",
                                    locale: '{{ __("vi") }}',
                                    centerVertical: true,
                                    closeButton: false,
                                    maxlength: 32,
                                    buttons: {
                                        confirm: {
                                            label: '<i class="fas fa-check"></i> {{ __("Đặt tên") }}',
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
                                                message: "{{ __("Vui lòng đặt tên cho phòng!") }}",
                                                size: 'small',
                                                locale: '{{ __("vi") }}',
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
                                                    message: "{{ __("Bạn đã tạo phòng thành công.") }}",
                                                    size: 'small',
                                                    centerVertical: true,
                                                    closeButton: false,
                                                    buttons: {
                                                        ok: {
                                                            className: 'btn-danger',
                                                            label: '{{ __("Oki") }}'
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
                                                                        label: '{{ __("Oki") }}'
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
                                    message: "{{ __("Mã phòng bị trùng, vui lòng thử lại.") }}",
                                    size: 'small',
                                    centerVertical: true,
                                    closeButton: false,
                                    buttons: {
                                        ok: {
                                            className: 'btn-danger',
                                            label: '{{ __("Oki") }}'
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
                    <h2 data-step="1" data-intro="{{ __("Danh sách các trận đấu của kỳ thủ") }} '{{ $player->name }}'" class="mt-3"><i class="fas fa-list-ul"></i> {{ __("Kết quả thi đấu") }}</h2>
                    <div class="table-responsive mb-3">
                        <table class="table table-striped table-hover" id="results-table">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __("Tên phòng") }}</th>
                                    <th scope="col">{{ __("Chủ phòng") }}</th>
                                    <th scope="col">{{ __("Khách") }}</th>
                                    <th scope="col">{{ __("Tới lượt") }}</th>
                                    <th scope="col">{{ __("Kết quả") }}</th>
                                    <th scope="col">{{ __("Thi đấu") }}</th>
                                    <th scope="col">{{ __("Lần cuối chơi") }}</th>
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
                                        <span class="text-danger">{{ __("Đỏ") }}</span>
                                        @elseif (str_contains($room->fen, ' b '))
                                        <span class="text-dark">{{ __("Đen") }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($room->result == '1')
                                            {{ __("Chủ phòng thắng") }}
                                        @elseif ($room->result == '0')
                                            {{ __("Hòa") }}
                                        @elseif ($room->result == '-1')
                                            {{ __("Khách thắng") }}
                                        @else
                                            {{ __("Chưa có kết quả") }}
                                        @endif
                                    </td>
                                    <td>
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
                                            <span class="text-danger">{{ __("Đã đấu xong") }}</span>
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
                                                        message: "{{ __("Hãy chuẩn bị vào phòng!") }}",
                                                        size: 'small',
                                                        centerVertical: true,
                                                        closeButton: false,
                                                        buttons: {
                                                            ok: {
                                                                className: 'btn-danger pulse-red',
                                                                label: '{{ __("Oki") }}'
                                                            }
                                                        },
                                                        callback: function(){
                                                            window.location.href = '{{ url('/phong/') }}' + '/' + roomCode + '/khach';
                                                        }
                                                    });
                                                });
                                            } else if (guestId == '{{ auth()->id() }}') {
                                                bootbox.alert({
                                                    message: "{{ __("Mời bạn quay lại phòng!") }}",
                                                    size: 'small',
                                                    centerVertical: true,
                                                    closeButton: false,
                                                    buttons: {
                                                        ok: {
                                                            className: 'btn-danger pulse-red',
                                                            label: '{{ __("Oki") }}'
                                                        }
                                                    },
                                                    callback: function(){
                                                        window.location.href = '{{ url('/phong/') }}' + '/' + roomCode + '/khach';
                                                    }
                                                });
                                            } else if (hostId == '{{ auth()->id() }}') {
                                                bootbox.alert({
                                                    message: "{{ __("Mời bạn vào lại phòng của mình!") }}",
                                                    size: 'small',
                                                    centerVertical: true,
                                                    closeButton: false,
                                                    buttons: {
                                                        ok: {
                                                            className: 'btn-danger pulse-red',
                                                            label: '{{ __("Oki") }}'
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
{{-- @include('layout.partials.app.fb') --}}
@endsection
