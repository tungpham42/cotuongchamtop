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
    <h2 class="mt-3"><i class="fas fa-gamepad-alt"></i> {{ __("Thi đấu xếp hạng") }}</h2>
    <form method="POST" id="create-form">
        <div class="form-group">
            @csrf
            <input name="ma-phong" type="hidden" value="{{ md5(time()) }}" disabled readonly>
            <button data-step="1" data-intro="{{ __("Ấn vào đây để tạo phòng thi đấu với các kỳ thủ khác") }}" type="submit" class="btn btn-danger btn-lg my-3"><i class="fad fa-plus-octagon"></i> {{ __("Tạo phòng mới") }}</button>
        </div>
    </form>
    <script>
    // Bootbox locale configuration
    bootbox.addLocale('vi', {
        OK: '<i class="fas fa-check"></i> {{ __("Đồng ý") }}',
        CONFIRM: '<i class="fas fa-check"></i> {{ __("Chấp nhận") }}',
        CANCEL: '<i class="fas fa-times"></i> {{ __("Hủy") }}'
    });

    // Promise wrappers to make Bootbox modals awaitable
    const bootboxAlertAsync = (options) => new Promise(resolve => {
        bootbox.alert({ ...options, callback: resolve });
    });

    const bootboxPromptAsync = (options) => new Promise(resolve => {
        bootbox.prompt({ ...options, callback: resolve });
    });

    $('#create-form').on('submit', async function(e) {
        e.preventDefault();

        // Dynamically generate a unique 32-character hex room code for this request
        const roomCode = Array.from(crypto.getRandomValues(new Uint8Array(16)))
            .map(b => b.toString(16).padStart(2, '0'))
            .join('');

        try {
            // 1. Check if the generated room code is available
            const checkRes = await $.ajax({
                type: "POST",
                url: '{{ route('hasRoomcode') }}',
                data: { 'ma-phong': roomCode },
                dataType: 'json'
            });

            if (checkRes && checkRes.exists) {
                await bootboxAlertAsync({
                    message: '{{ __("Mã phòng bị trùng, vui lòng thử lại.") }}',
                    size: 'small',
                    centerVertical: true,
                    closeButton: false,
                    buttons: {
                        ok: {
                            className: 'btn-lg btn-danger pulse-red',
                            label: '{{ __("Oki") }}'
                        }
                    }
                });
                setTimeout(() => location.reload(), 500);
                return;
            }

            // 2. Prompt user to enter a room name
            const roomName = await bootboxPromptAsync({
                title: '{{ __("Mời đặt tên cho Phòng thi đấu:") }}',
                locale: '{{ __("vi") }}',
                centerVertical: true,
                closeButton: false,
                maxlength: 32,
                buttons: {
                    confirm: {
                        label: '<i class="fas fa-check"></i> {{ __("Đặt tên") }}',
                        className: 'btn-lg btn-danger pulse-red'
                    },
                    cancel: {
                        className: 'btn-lg btn-dark text-light'
                    }
                }
            });

            // User closed or cancelled the modal
            if (roomName === null) return;

            // Validate empty input
            if (!roomName.trim()) {
                await bootboxAlertAsync({
                    message: '{{ __("Vui lòng đặt tên cho phòng!") }}',
                    size: 'small',
                    locale: '{{ __("vi") }}',
                    centerVertical: true,
                    closeButton: false,
                    buttons: {
                        ok: {
                            className: 'btn-lg btn-danger pulse-red'
                        }
                    }
                });
                $('#create-form').trigger('submit');
                return;
            }

            // 3. Request room creation
            await $.ajax({
                type: "POST",
                url: '{{ url('/api/createRoom') }}',
                data: {
                    'ma-phong': roomCode,
                    'ten-phong': roomName.trim(),
                    'FEN': 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1',
                    'pass': '',
                    'host_id': '{{ auth()->id() }}'
                },
                dataType: 'json'
            });

            // 4. Alert user of successful creation
            await bootboxAlertAsync({
                message: '{{ __("Bạn đã tạo phòng thành công.") }}',
                size: 'small',
                centerVertical: true,
                closeButton: false,
                buttons: {
                    ok: {
                        className: 'btn-lg btn-danger pulse-red',
                        label: '{{ __("Oki") }}'
                    }
                }
            });

            // 5. Redirect to the newly created room
            window.location.href = '{{ url(__('/phong/')) }}' + '/' + roomCode;

        } catch (error) {
            console.error('An error occurred while creating the room:', error);
        }
    });
    </script>
@else
<div class="alert alert-dark border-dark" role="alert">
    <a data-step="1" data-intro="{{ __("Ấn vào đây để đăng nhập vào thi đấu xếp hạng") }}" class="stopPromotion btn btn-danger" href="{{ localized_url('login') }}">{{ __("Đăng nhập") }}</a> {{ __("để tham gia thi đấu") }}
</div>
@endif
