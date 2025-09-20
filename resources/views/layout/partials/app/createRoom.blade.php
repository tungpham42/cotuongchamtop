@if (session('status'))
    <div class="alert alert-success" role="alert">
        {{ session('status') }}
    </div>
@endif
@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-warning">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@if(auth()->check())
    <h2 class="mt-3"><i class="fas fa-gamepad-alt"></i> Thi đấu xếp hạng</h2>
    <form method="POST" id="create-form">
        <div class="form-group">
            @csrf
            <input name="ma-phong" type="hidden" value="{{ md5(time()) }}" disabled readonly>
            <button data-step="1" data-intro="Ấn vào đây để tạo phòng thi đấu với các kỳ thủ khác" type="submit" class="btn btn-danger btn-lg my-3"><i class="fad fa-plus-octagon"></i> Tạo phòng mới</button>
        </div>
    </form>
    <script>  
    var locale = {
        OK: '<i class="fas fa-check"></i> Đồng ý',
        CONFIRM: '<i class="fas fa-check"></i> Chấp nhận',
        CANCEL: '<i class="fas fa-times"></i> Hủy'
    };
    bootbox.addLocale('vi', locale);
    $('#create-form').on('submit', function(e){
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: '{{ url('/api') }}/hasRoomcode',
            data: {
                'ma-phong': '{{ md5(time()) }}'
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
                            className: 'btn-lg btn-danger pulse-red'
                        },
                        cancel: {
                            className: 'btn-lg btn-dark text-light'
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
                                            className: 'btn-lg btn-danger pulse-red'
                                        }
                                    },
                                    callback: function () {
                                        $('#create-form').trigger('submit');
                                    }
                                });
                            } else {
                                $.ajax({
                                    type: "POST",
                                    url: '{{ url('/api') }}/createRoom',
                                    data: {
                                        'ma-phong': '{{ md5(time()) }}',
                                        'ten-phong': roomName,
                                        'FEN': 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1',
                                        'pass': '',
                                        'host_id': '{{ auth()->id() }}'
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
                                                className: 'btn-lg btn-danger pulse-red',
                                                label: 'Oki'
                                            }
                                        },
                                        callback: function(){
                                            window.location.href = '{{ url('/phong/') }}' + '/' + '{{ md5(time()) }}';
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
                            className: 'btn-lg btn-danger pulse-red',
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
    })
    </script>
@else
<div class="alert alert-secondary" role="alert">
    <a data-step="1" data-intro="Ấn vào đây để đăng nhập vào thi đấu xếp hạng" class="stopPromotion" href="{{ url('/dang-nhap') }}">Đăng nhập</a> để tham gia thi đấu
</div>
@endif