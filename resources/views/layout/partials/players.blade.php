@if(isset($_GET['loai']) && ($_GET['loai'] == 'van-da-dau' || $_GET['loai'] == 'van-dau' || $_GET['loai'] == 'co-the' || $_GET['loai'] == 'the-co'))
@if($firstPagePlayers->total() > 0)
<span style="background-color: transparent; margin-top: -15px;" class="d-block w-100 pb-5 mb-5" id="ky-thu"></span>
<div style="background-color: transparent" class="container-fluid puzzles px-0">
    <div class="container mx-auto px-3 pt-0">
        <div class="row my-0">
            <h2 class="d-block w-100 text-light ml-3 mb-4">
                <i class="fas fa-users"></i> {{ $firstPagePlayers->total() }} kỳ thủ đang hoạt động, mời bạn <a class="text-light animate-light showPromotion" href="{{ url('/') }}/dang-ky">tham gia</a>
            </h2>
            {{ $firstPagePlayers->links('vendor.pagination.playerVi') }}
            @foreach($firstPagePlayers as $player)
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div class="bg-dark row mx-0">
                    <h4 class="py-2 col-12 text-light text-left">{!! app('App\Http\Controllers\UserController')::renderName($player->id) !!}</h4>
                    <span class="py-2 col-12 text-light text-left lead">Elo: {!! app('App\Http\Controllers\UserController')::renderElo($player->id) !!}</span>
                    @if (auth()->check())
                        @if (auth()->id() != $player->id)
                            <a class="btn btn-danger text-light mr-1 w-100" href="javascript:compete({{ $player->id }});"><i class="far fa-mouse"></i> Thách đấu</a>
                        @else
                            <a class="btn btn-dark text-light mr-1 w-100" style="cursor: not-allowed !important;" href="javascript:void(0);"><i class="far fa-ban"></i> Thách đấu</a>
                        @endif
                    @else
                        <a class="btn btn-danger text-light mr-1 w-100" href=" {{ url('/dang-nhap') }} "><i class="far fa-sign-in"></i> Thách đấu</a>
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
            @endforeach
            {{ $firstPagePlayers->links('vendor.pagination.playerVi') }}
        </div>
    </div>
</div>
@endif
@else
    @if ( Request::get('page') <= ceil($players->total() / $players->perPage()) && $players->total() > 0 )
    <span style="background-color: transparent; margin-top: -15px;" class="d-block w-100 pb-5 mb-5" id="ky-thu"></span>
    <div style="background-color: transparent" class="container-fluid puzzles px-0">
        <div class="container mx-auto px-3 pt-0">
            <div class="row my-0">
                <h2 class="d-block w-100 text-light ml-3 mb-4">
                    <i class="fas fa-users"></i> {{ $players->total() }} kỳ thủ đang hoạt động, mời bạn  <a class="text-light animate-light showPromotion" href="{{ url('/') }}/dang-ky">tham gia</a>
                </h2>
                {{ $players->links('vendor.pagination.playerVi') }}
                @foreach($players as $player)
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-4">
                    <div class="bg-dark row mx-0">
                        <h4 class="py-2 col-12 text-light text-left">{!! app('App\Http\Controllers\UserController')::renderName($player->id) !!}</h4>
                        <span class="py-2 col-12 text-light text-left lead">Elo: {!! app('App\Http\Controllers\UserController')::renderElo($player->id) !!}</span>
                        @if (auth()->check())
                            @if (auth()->id() != $player->id)
                                <a class="btn btn-danger text-light mr-1 w-100" href="javascript:compete({{ $player->id }});"><i class="far fa-mouse"></i> Thách đấu</a>
                            @else
                                <a class="btn btn-dark text-light mr-1 w-100" style="cursor: not-allowed !important;" href="javascript:void(0);"><i class="far fa-ban"></i> Thách đấu</a>
                            @endif
                        @else
                            <a class="btn btn-danger text-light mr-1 w-100" href=" {{ url('/dang-nhap') }} "><i class="far fa-sign-in"></i> Thách đấu</a>
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
                @endforeach
                {{ $players->links('vendor.pagination.playerVi') }}
            </div>
        </div>
    </div>
    @endif
@endif