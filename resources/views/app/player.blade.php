@extends('layout.app')

@php
    $avatarDir = public_path('players');
    $avatarPath = $avatarDir . '/' . md5($player->email) . '.jpg';

    // If custom profile picture exists, use it. Otherwise, cache the Gravatar.
    $ogImage = $player->profile_picture ? asset('storage/' . $player->profile_picture) : url('/players/' . md5($player->email) . '.jpg');

    if (!$player->profile_picture && !file_exists($avatarPath)) {
        if (!is_dir($avatarDir)) {
            mkdir($avatarDir, 0755, true);
        }
        Avatar::create($player->name)->setDimension(200)->setFontSize(100)->save($avatarPath, 100);
    }
@endphp

@section('og_image', $ogImage)

@section('og_image_width', '200')
@section('og_image_height', '200')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <!-- Royal Glassmorphism Profile Card -->
            <div class="card shadow-lg mb-4">
                <div class="card-header d-flex align-items-center">
                    <img src="{{ $player->getAvatarUrl(48, 24) }}" class="mr-2 rounded" style="width: 48px; height: 48px; object-fit: cover;" />
                    <h4 class="mb-0 text-gold">
                        @if ($player->id == auth()->id() && !str_contains(url()->current(), url('/ky-thu').'/'))
                            <i class="fas fa-user-circle"></i> {{ __("Hồ sơ của tôi") }}
                        @else
                            <i class="fas fa-user"></i> {{ __("Hồ sơ kỳ thủ") }}
                        @endif
                    </h4>
                    <div class="ml-2">
                        {!! app('App\Http\Controllers\UserController')::onlineStatus($player->id) !!}
                    </div>

                    <div class="ml-auto d-flex align-items-center">
                        @include('layout.partials.app.tourBtn')
                        @if (auth()->check())
                            @if (auth()->id() != $player->id)
                                <a class="btn btn-danger text-light ml-2 pulse-red" style="width: 140px;" href="javascript:compete({{ $player->id }});"><i class="far fa-mouse"></i> {{ __("Thách đấu") }}</a>
                            @else
                                <a class="btn btn-dark text-light ml-2" style="width: 140px; cursor: not-allowed !important;" href="javascript:void(0);"><i class="far fa-ban"></i> {{ __("Thách đấu") }}</a>
                            @endif
                        @else
                            <a class="btn btn-danger text-light ml-2 pulse-red" style="width: 140px;" href=" {{ localized_url('login') }} "><i class="far fa-sign-in"></i> {{ __("Thách đấu") }}</a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <h5>{{ __("Tên:") }} <span class="text-gold font-weight-bold">{{ $player->name }}</span></h5>
                    @if ($player->id == auth()->id() && !str_contains(url()->current(), url('/ky-thu').'/'))
                    <h5>{{ __("Email:") }} {!! app('App\Http\Controllers\UserController')::renderPlayerEmail($player->id) !!}</h5>
                    @endif
                    <h5>{{ __("Ngày giờ gia nhập:") }} <span class="text-light">{{ $player->created_at }}</span></h5>
                    <h5>{{ __("Lần trực tuyến gần nhất:") }} <span class="text-light">{{ $player->last_seen_at }}</span></h5>
                    <h5>{{ __("Thứ hạng:") }} {!! app('App\Http\Controllers\UserController')::renderPlayerRank($player->id) !!}</h5>
                    <h5>Elo: <span id="elo" class="text-gold font-weight-bold" style="font-size: 1.2em;">{!! app('App\Http\Controllers\UserController')::renderElo($player->id) !!}</span></h5>
                    <hr style="border-color: rgba(212, 175, 55, 0.2);">
                    <h5>{{ __("Số trận thắng:") }} <span id="winPoints" class="text-success font-weight-bold">{!! app('App\Http\Controllers\UserController')::renderWinMatchPoints($player->id) !!}</span></h5>
                    <h5>{{ __("Số trận hòa:") }} <span id="drawPoints" class="text-warning font-weight-bold">{!! app('App\Http\Controllers\UserController')::renderDrawMatchPoints($player->id) !!}</span></h5>
                    <h5>{{ __("Số trận thua:") }} <span id="losePoints" class="text-danger font-weight-bold">{!! app('App\Http\Controllers\UserController')::renderLoseMatchPoints($player->id) !!}</span></h5>
                    <h5>{{ __("Tổng số trận đã đấu xong:") }} <span id="totalPoints" class="text-light font-weight-bold">{!! app('App\Http\Controllers\UserController')::renderTotalMatchPoints($player->id) !!}</span></h5>

                    @if (auth()->check() && $player->id === auth()->id())
                    <div id="standard-plan" class="alert alert-warning d-flex align-items-center justify-content-between mt-4 mb-0 shadow-lg" style="background: rgba(212, 175, 55, 0.1); border: 1px solid var(--royal-gold); color: var(--royal-gold-light);">
                        <div class="text-left">
                            <strong class="text-gold"><i class="fas fa-crown"></i> {{ __("Gói hiện tại:") }}</strong>
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
                            <button type="submit" class="btn btn-danger pulse-red">
                                <i class="far fa-crown"></i>
                                {{ __("Nâng cấp Standard -") }} {{ number_format(config('payos.standard_amount', 100000), 0, ',', '.') }}đ
                            </button>
                        </form>
                        @else
                        <span class="badge badge-success p-2" style="background: linear-gradient(to bottom, #d4af37, #b89020); color: #0b0c10;">Ads Free</span>
                        @endif
                    </div>
                    @endif

                    @if ($player->id == auth()->id() && !str_contains(url()->current(), url('/ky-thu').'/'))
                    <div class="w-100 text-left mt-4">
                        <a href="{{ localized_url('app.name') }}" class="btn btn-dark showPromotion"><i class="fad fa-user-edit"></i> {{ __("Đổi tên") }}</a>
                        <a href="{{ localized_url('app.password') }}" class="btn btn-dark showPromotion"><i class="fad fa-lock-alt"></i> {{ __("Đổi mật khẩu") }}</a>
                        <a href="{{ localized_url('app.ui') }}" class="btn btn-dark showPromotion"><i class="fad fa-palette"></i> {{ __("Đổi giao diện") }}</a>

                        <!-- Upload Image Form -->
                        <form action="{{ localized_url('profile.picture.upload') }}" method="POST" enctype="multipart/form-data" class="d-inline-block">
                            @csrf
                            <label class="btn btn-dark showPromotion mb-0" style="cursor: pointer;">
                                <i class="fad fa-upload"></i> {{ __("Đổi ảnh đại diện") }}
                                <input type="file" name="profile_picture" class="d-none" onchange="this.form.submit()" accept="image/png, image/jpeg, image/gif">
                            </label>
                        </form>

                        <!-- Remove Image Form -->
                        @if(auth()->user()->profile_picture)
                        <form action="{{ localized_url('profile.picture.remove') }}" method="POST" class="d-inline-block">
                            @csrf
                            <button type="submit" class="btn btn-dark showPromotion"><i class="fad fa-trash"></i> {{ __("Xóa ảnh") }}</button>
                        </form>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

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
                                    className: 'btn-danger pulse-red'
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
                                                            window.location.href = '{{ url(__('/phong/')) }}' + '/' + maPhong;
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

            <!-- Royal Matches History Card -->
            @if ($playerRooms->total() > 0)
            <div data-step="1" data-intro="{{ __("Danh sách các trận đấu của kỳ thủ") }} '{{ $player->name }}'" class="card shadow-lg mb-4 mt-4">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-list-ul text-gold"></i> {{ __("Kết quả thi đấu") }}</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0" id="results-table">
                            <thead>
                                <tr>
                                    <th class="text-center" scope="col">{{ __("Tên phòng") }}</th>
                                    <th class="text-center" scope="col">{{ __("Chủ phòng") }}</th>
                                    <th class="text-center" scope="col">{{ __("Khách") }}</th>
                                    <th class="text-center" scope="col">{{ __("Tới lượt") }}</th>
                                    <th class="text-center" scope="col">{{ __("Kết quả") }}</th>
                                    <th class="text-center" scope="col">{{ __("Thi đấu") }}</th>
                                    <th class="text-center" scope="col">{{ __("Lần cuối chơi") }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($playerRooms as $room)
                                <tr data-code="{{ $room->code }}" data-fen="{{ $room->fen }}">
                                    <td class="text-center room-code">
                                        <span><a class="animate text-gold" href="{{ localized_url('room.watch', ['code' => $room->code]) }}">{{ ((isset($room->name) && $room->name != '') ? $room->name: $room->code) }}</a></span>
                                    </td>
                                    <td class="text-center host-name">
                                        {!! app('App\Http\Controllers\UserController')::renderPlayerName($room->host_id) !!}
                                    </td>
                                    <td class="text-center guest-name">
                                        {!! app('App\Http\Controllers\UserController')::renderPlayerName($room->guest_id) !!}
                                    </td>
                                    <td class="text-center">
                                        @if (str_contains($room->fen, ' r '))
                                        <span class="badge badge-status" style="background: linear-gradient(to bottom, #8a1515, #5c0a0a); color: var(--royal-gold); border: 1px solid var(--royal-gold);"><i class="fas fa-chess-knight"></i> {{ __("Đỏ") }}</span>
                                        @elseif (str_contains($room->fen, ' b '))
                                        <span class="badge badge-status" style="background: linear-gradient(145deg, #252a36, #121418); color: var(--royal-gold-light); border: 1px solid rgba(212, 175, 55, 0.3);"><i class="fas fa-chess-knight"></i> {{ __("Đen") }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($room->result == '1')
                                            <span class="badge badge-status" style="background: linear-gradient(to bottom, #8a1515, #5c0a0a); color: var(--royal-gold);"><i class="fas fa-crown"></i> {{ __("Chủ phòng thắng") }}</span>
                                        @elseif ($room->result == '0')
                                            <span class="badge badge-status badge-offline"><i class="fas fa-handshake"></i> {{ __("Hòa") }}</span>
                                        @elseif ($room->result == '-1')
                                            <span class="badge badge-status" style="background: linear-gradient(145deg, #252a36, #121418); color: var(--royal-gold);"><i class="fas fa-crown"></i> {{ __("Khách thắng") }}</span>
                                        @else
                                            <span class="text-muted"><i class="fas fa-hourglass-half"></i> {{ __("Chưa có kết quả") }}</span>
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
                                            <span class="badge badge-status badge-offline p-2 text-danger"><i class="fas fa-flag-checkered"></i> {{ __("Đã xong") }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center room-time">{{ $room->modified_at }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($playerRooms->hasPages())
                <div class="card-footer d-flex justify-content-center pt-3 pb-1 border-top" style="border-color: rgba(212, 175, 55, 0.2) !important;">
                    {{ $playerRooms->links('vendor.pagination.match') }}
                </div>
                @endif
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
