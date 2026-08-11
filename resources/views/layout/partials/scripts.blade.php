@include('common.scripts')
@desktop
<script src="{{ asset('js/xiangqiboard.js?v=34') }}"></script>
@elsedesktop
<script src="{{ asset('js/xiangqiboard_mobile.js?v=3') }}"></script>
@enddesktop
<script src="{{ asset('js/kypho.js?v=9') }}"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap4.min.js"></script>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var locale = {
        OK: '<i class="fas fa-check"></i> {{ __('Đồng ý') }}',
        CONFIRM: '<i class="fas fa-check"></i> {{ __('Chấp nhận') }}',
        CANCEL: '<i class="fas fa-times"></i> {{ __('Hủy') }}',
        piece_k: '{{ __('piece_k') }}',
        piece_a: '{{ __('piece_a') }}',
        piece_b: '{{ __('piece_b') }}',
        piece_n: '{{ __('piece_n') }}',
        piece_r: '{{ __('piece_r') }}',
        piece_c: '{{ __('piece_c') }}',
        piece_p: '{{ __('piece_p') }}',
        move_advance: '{{ __('move_advance') }}',
        move_retreat: '{{ __('move_retreat') }}',
        move_traverse: '{{ __('move_traverse') }}',
        kypho_copied: '{{ __('Đã sao chép kỳ phổ thành công!') }}',
        video_loading_lib: '{{ __('Đang tải thư viện tạo ảnh, vui lòng thử lại sau giây lát.') }}',
        video_processing: '{{ __('Đang xử lý...') }}',
        video_error: '{{ __('Có lỗi xảy ra khi tạo video.') }}',
        video_title: '{!! addslashes($room->name ?? $puzzleName ?? __('Trận Đấu Cờ Tướng')) !!}',
        video_start: '{{ __('Bắt đầu') }}',
        video_move: '{{ __('Nước') }}',
        video_footer: '{{ __('Tạo bởi nền tảng của bạn') }}',
        video_completed: '{{ __('video_completed') }}',
        video_success: '{{ __('video_success') }}',
        video_download: '{{ __('video_download') }}',
        video_share: '{{ __('video_share') }}'
    };
    bootbox.addLocale('{{ __('vi') }}', locale);

    $('#tao-phong-public').on('click auxclick', async function(e) {
        e.preventDefault();

        const roomName = await bootboxPromptAsync({
            title: "{{ __('Mời đặt tên cho Phòng công khai') }}:",
            locale: '{{ __('vi') }}',
            centerVertical: true,
            closeButton: false,
            maxlength: 32,
            buttons: {
                confirm: {
                    label: '<i class="fas fa-check"></i> {{ __('Đặt tên') }}',
                    className: 'btn-lg btn-danger pulse-red'
                },
                cancel: {
                    className: 'btn-lg btn-dark text-light'
                }
            }
        });

        if (roomName === null) return;

        if (!roomName.trim()) {
            await bootboxAlertAsync({
                message: "{{ __('Vui lòng đặt tên cho phòng!') }}",
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
            $('#tao-phong-public').trigger('click');
            return;
        }

        const maPhong = generateRoomCode();

        await $.ajax({
            type: "POST",
            url: '{{ url('/api/createRoom') }}',
            data: {
                'ma-phong': maPhong,
                'ten-phong': roomName.trim(),
                'FEN': 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1',
                'pass': ''
            },
            dataType: 'json'
        });

        window.location.href = $('#tao-phong').attr('data-url').replace(/[^\/]*$/, maPhong);
    });

    $('#tao-phong-private').on('click auxclick', async function(e) {
        e.preventDefault();

        const roomName = await bootboxPromptAsync({
            title: "{{ __('Mời đặt tên cho Phòng riêng tư') }}:",
            locale: '{{ __('vi') }}',
            centerVertical: true,
            closeButton: false,
            maxlength: 32,
            buttons: {
                confirm: {
                    label: '<i class="fas fa-check"></i> {{ __('Đặt tên') }}',
                    className: 'btn-lg btn-danger pulse-red'
                },
                cancel: {
                    className: 'btn-lg btn-dark text-light'
                }
            }
        });

        if (roomName === null) return;

        if (!roomName.trim()) {
            await bootboxAlertAsync({
                message: "{{ __('Vui lòng đặt tên cho phòng!') }}",
                size: 'small',
                locale: '{{ __('vi') }}',
                centerVertical: true,
                closeButton: false,
                buttons: {
                    ok: {
                        className: 'btn-lg btn-danger pulse-red'
                    }
                }
            });
            $('#tao-phong-private').trigger('click');
            return;
        }

        const password = await bootboxPromptAsync({
            title: '{{ __('Mời tạo mật khẩu cho Phòng') }} "' + roomName.trim() + '":',
            locale: '{{ __('vi') }}',
            centerVertical: true,
            closeButton: false,
            buttons: {
                confirm: {
                    label: '<i class="fas fa-check"></i> {{ __('Tạo') }}',
                    className: 'btn-lg btn-danger pulse-red'
                },
                cancel: {
                    className: 'btn-lg btn-dark text-light'
                }
            }
        });

        if (password === null) return;

        if (!password.trim()) {
            await bootboxAlertAsync({
                message: "{{ __('Vui lòng nhập mật khẩu. Sau đó gửi mật khẩu này cho bạn bè nhé.') }}",
                size: 'small',
                locale: '{{ __('vi') }}',
                centerVertical: true,
                closeButton: false,
                buttons: {
                    ok: {
                        className: 'btn-lg btn-danger pulse-red'
                    }
                }
            });
            $('#tao-phong-private').trigger('click');
            return;
        }

        const maPhong = generateRoomCode();

        await $.ajax({
            type: "POST",
            url: '{{ url('/api/createRoom') }}',
            data: {
                'ma-phong': maPhong,
                'ten-phong': roomName.trim(),
                'FEN': 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1',
                'pass': password.trim()
            },
            dataType: 'json'
        });

        window.location.href = $('#tao-phong').attr('data-url').replace(/[^\/]*$/, maPhong);
    });

    $('#random-room').on('click auxclick', function(e) {
        e.preventDefault();
        window.location.href = $(this).attr('href');
    });

    $('#room-list').on('click auxclick', function(e) {
        e.preventDefault();
        window.location.href = $(this).attr('href');
    });

    $('#copy-url-red').on('click', function() {
        copyToClipboard('#url-red');
        selectText('#url-red');
        $(this).tooltip('update');
    });

    $('#copy-url-black').on('click', function() {
        copyToClipboard('#url-black');
        selectText('#url-black');
        $(this).tooltip('update');
    });

    $('#room-code').on('click', function() {
        copyToClipboard('#room-code-input');
        selectText('#room-code-input');
        $(this).find('span').tooltip('update');
    });

    const nuocCo = document.getElementById("nuoc-co");
    const hetTran = document.getElementById("het-tran");

    $(function () {
        $('.dropdown-toggle').dropdown();
        if (!Modernizr.touch) {
            $('#volumeSwitch').attr('title', '{{ __('Ấn vào đây để bật/tắt âm lượng') }}');
            $('#tourBtn').attr('title', '{{ __('Ấn vào đây để được hướng dẫn sử dụng trang web') }}');
            $('[data-toggle="tooltip"]').tooltip();
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
            });
        }

        $('a:not("#dashboardDropdown"):not("#navbarDropdown") + .dropdown-menu .dropdown-item').each(function() {
            $(this).on('mouseenter mouseleave', function() {
                $(this).find('i').toggleClass('fad fas');
            });
        });

        let activeNavLinkSelectors = 'body.home header.site-header a.home, body.setup header.site-header a.setup, body.about header.site-header a.about, body.bmi header.site-header a.bmi, body.game header.site-header a.game, body.room header.site-header a.room, body.news header.site-header a.news, body.contact header.site-header a.contact';
        $(activeNavLinkSelectors).each(function() {
            $(this).find('i').removeClass('far').addClass('fas');
        });

        $('header.site-header ul.navbar-nav').on('mouseenter mouseleave', function() {
            $(activeNavLinkSelectors).each(function() {
                $(this).find('i').toggleClass('far fas');
            });
        });

        $('header.site-header li a').each(function() {
            $(this).on('mouseenter mouseleave', function() {
                $(this).find('i').toggleClass('far fas');
            });
        });
    });

    $('#tourBtn').on('click', function(){
        introJs().setOptions({"nextLabel": "{{ __('Sau') }}", "prevLabel": "{{ __('Trước') }}", "skipLabel": "{{ __('Bỏ qua') }}", "doneLabel": "{{ __('Hoàn tất') }}", "showProgress": true, "showButtons": true, "showBullets": true, "exitOnOverlayClick": true, "hidePrev": true, "hideNext": true, "disableInteraction": true}).onskip(function(){
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }).onexit(function(){
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }).start();
    });

    document.addEventListener('touchstart touchend touchmove', function(event) {
        const target = event.target;
        const isInteractive = target.closest('button, a, input, select, textarea, .dropdown-item');
        if (!isInteractive) {
            event.preventDefault();
        }
    }, {passive: false});

    document.oncontextmenu = function(e){
        const target = e.target;
        const isInteractive = target.closest('button, a, input, select, textarea, .dropdown-item');
        if (!isInteractive) {
            stopEvent(e);
        }
    }

    function stopEvent(event){
        if(event.preventDefault != undefined)
            event.preventDefault();
        if(event.stopPropagation != undefined)
            event.stopPropagation();
    }

    window.onload = () => {
        'use strict';
        if ('serviceWorker' in navigator) {
            console.log("Will the service worker register?");
            navigator.serviceWorker
                .register("{{ asset('serviceWorker.js?v=3') }}")
                .then(function(reg) {
                    console.log("Yes, it did.");
                }).catch(function(err) {
                    console.log("No it didn't. This happened:", err)
                });
        }
    }
</script>
<script src='https://platform-api.sharethis.com/js/sharethis.js#property=646aee4bd8c6d2001a06c2f8&product=sticky-share-buttons' async='async'></script>
