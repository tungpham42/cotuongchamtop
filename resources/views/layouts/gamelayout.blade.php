<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('layouts.partials.head')

    <style>

        /* Khung bao quanh Bàn cờ */
        #ban-co {
            background-color: #252a36;
            border-radius: 8px;
            padding: 5px;
            margin-bottom: 15px;
        }
        /* Tùy chỉnh Alerts cho Dark Theme */
        .alert {
            border-radius: 8px;
            border: none;
            backdrop-filter: blur(5px);
            margin-top: 15px;
        }
        .alert-success {
            background-color: rgba(46, 125, 50, 0.2);
            color: #81c784;
            border-left: 4px solid #4caf50;
        }
        .alert-warning {
            background-color: rgba(245, 124, 0, 0.2);
            color: #ffb74d;
            border-left: 4px solid #ff9800;
        }

        /* Dòng chữ "Chiếu!" */
        #checkmateText {
            font-size: 2.5rem;
            font-weight: 800;
            color: #ff5252;
            text-shadow: 0 0 15px rgba(255, 82, 82, 0.8), 0 0 5px rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
            letter-spacing: 3px;
            display: none;
            text-align: center;
            margin-bottom: 15px;
        }

        /* Tùy chỉnh thanh cuộn (Scrollbar) cho đồng bộ Dark theme */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #121418; }
        ::-webkit-scrollbar-thumb { background: #3a3f4c; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #505769; }

        /* Cân bằng các khoảng trống section bên dưới */
        .mt-lg-0.mt-md-5 {
            margin-top: 2rem !important;
        }
    </style>
</head>
<body class="{{ $bodyClass }}">
    @include('common.afterBody')
    @include('common.adsenseTop')
    @include('layouts.partials.header')
    @include('layouts.partials.analysisModal')
    @if (session('status'))
        <div class="container">
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        </div>
    @endif
    @if (session('success'))
        <div class="container">
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if ($errors->any())
        <div class="container">
            <div class="alert alert-warning">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    <main>
        @include('common.xiangqiBanner')
        <span id="checkmateText">{{ __('Chiếu!') }}</span>
        <div class="container-fluid game px-0" itemscope itemtype="http://schema.org/Game">
            <div class="container {{ isset($board) ? 'px-3 pb-0 pt-3' : 'p-3' }}">
                <audio id="nuoc-co">
                    <source src="{{ $cdnUrl }}/sound/nuocCo.mp3" type="audio/mpeg">
                    <source src="{{ $cdnUrl }}/sound/nuocCo.wav" type="audio/wav">
                    Your browser does not support the audio element.
                </audio>
                <audio id="het-tran">
                    <source src="{{ $cdnUrl }}/sound/hetTran.mp3" type="audio/mpeg">
                    <source src="{{ $cdnUrl }}/sound/hetTran.wav" type="audio/wav">
                    Your browser does not support the audio element.
                </audio>
                {{-- @include('common.hero') --}}
                @include('common.articleCarousel', ['articles' => $articles ?? collect()])
                <div class="row">
                    @include('layouts.partials.findMatch')
                </div>
                <div class="row">
                    <div class="col-12 text-center mb-3">
                        @yield('aboveBoard')
                    </div>
                </div>
                @if ( !$roomCode && !isset($slug) )
                    <div class="row">
                        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 my-1">
                            <div id="ban-co" class="mx-auto mr-lg-0 h-auto"></div>
                            @include('layouts.partials.themeSelector')
                            {{-- @include('layouts.partials.analyzeBtn') --}}
                        </div>
                        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 my-auto">
                            @yield('rightSide')
                        </div>
                    </div>
                @elseif ( !$roomCode && isset($slug) )
                    <div class="puzzle-layout-wrapper">
                        <div class="puzzle-layout-board">
                            <div id="ban-co" class="mx-auto mr-lg-0 h-auto"></div>
                            @include('layouts.partials.themeSelector')
                            {{-- @include('layouts.partials.analyzeBtn') --}}
                            @yield('belowBoardExtras')
                        </div>
                        <div class="puzzle-layout-panel">
                            @yield('rightSide')
                        </div>
                    </div>
                @else
                    <div class="row">
                        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                            <div id="ban-co" class="mx-auto mr-lg-0 h-auto"></div>
                            @include('layouts.partials.themeSelector')
                            {{-- @include('layouts.partials.analyzeBtn') --}}
                        </div>
                        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 mt-lg-0 mt-md-5 mt-sm-5 mt-xs-5">
                            @include('layouts.partials.comments')
                            {{-- @include('common.sideAds') --}}
                        </div>
                    </div>
                @endif
                @include('common.adsenseBetween')
                <div class="row">
                    <div class="col-12">
                        @if ( $roomCode != '' )
                            <p class="w-100 text-center my-3 d-flex justify-content-center align-items-center flex-wrap gap-2">
                                @if (!isset($room->result) && isset($room->host_id))
                                    @if (auth()->check())
                                        <a id="choi" class="btn btn-danger text-light btn-lg showPromotion mx-2" href="javascript:joinMatch('{{ $roomCode }}')"><i class="fad fa-mouse"></i> {{ __('Chơi') }}</a>
                                        <script>
                                            async function joinMatch(roomCode) {
                                                const currentUserId = Number('{{ auth()->id() }}');
                                                const hostUrl = '{{ url(__('/phong/')) }}/' + roomCode;
                                                const guestUrl = '{{ url(__('/phong/')) }}/' + roomCode + '{{ __('/khach') }}';

                                                try {
                                                    // 1. Get host and guest IDs for the specified room
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

                                                    // 2. Determine room status and action for current user
                                                    if (hostId === currentUserId) {
                                                        alertMessage = "{{ __('Mời bạn vào lại phòng của mình!') }}";
                                                        targetUrl = hostUrl;
                                                    } else if (guestId === currentUserId) {
                                                        alertMessage = "{{ __('Mời bạn quay lại phòng!') }}";
                                                        targetUrl = guestUrl;
                                                    } else {
                                                        // New guest joining
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

                                                    // 3. Display notification and redirect after confirmation
                                                    await bootboxAlertAsync(alertMessage);
                                                    window.location.href = targetUrl;

                                                } catch (error) {
                                                    console.error('An error occurred while attempting to join the match:', error);
                                                }
                                            }
                                        </script>
                                    @else
                                        <a class="btn btn-danger text-light btn-lg showPromotion thi-dau mx-2" href="{{ localized_url('login') }}" data-toggle="tooltip" data-placement="top" title="{{ __('Đăng nhập để thi đấu') }}"><i class="fad fa-sign-in"></i> {{ __("Đăng nhập") }}</a>
                                    @endif
                                @else
                                    <a class="btn btn-danger text-light btn-lg showPromotion mx-2 rooms-list" href="{{ localized_url('room.list') }}"><i class="fad fa-chevron-circle-left"></i> {{ __("Quay lại sảnh chờ") }}</a>
                                @endif
                                @include('common.volumeBtn')
                                @include('common.tourBtn')
                            </p>
                        @endif

                        <div class="mt-3">
                            @include('common.ads')
                        </div>

                        @yield('aboveContent')
                        <div class="row">
                            <input type="hidden" name="FEN" id="FEN" >
                            <input type="hidden" name="piecesUrl" id="piecesUrl" value="{{ url('/') }}" >
                            @include('common.themes')
                            @include('layouts.partials.scripts')
                            @if ( !isset($board) )
                                @include('layouts.partials.rules')
                            @endif
                            @yield('belowContent')
                            @if ( !isset($board) )
                                <script>
                                    // --- 1. Analyze Board Event Handler ---
                                    $('#btn-analyze').on('click', async function(e) {
                                        e.preventDefault();

                                        if (typeof game === 'undefined' || typeof game.fen !== 'function') {
                                            await bootboxAlertAsync("{{ __('Chưa tải được dữ liệu bàn cờ.') }}");
                                            return;
                                        }

                                        const currentFen = game.fen();
                                        if (!currentFen) {
                                            await bootboxAlertAsync("{{ __('Không lấy được mã FEN.') }}");
                                            return;
                                        }

                                        // openAnalysisModal is defined in analysisModal.blade.php
                                        openAnalysisModal(currentFen);
                                    });

                                    // --- 2. Create Room Function ---
                                    async function createRoom() {
                                        const maPhong = generateRoomCode();

                                        try {
                                            // Step 1: Check room code availability
                                            const checkRes = await $.ajax({
                                                type: "POST",
                                                url: '{{ route('hasRoomcode') }}',
                                                data: { 'ma-phong': maPhong },
                                                dataType: 'json'
                                            });

                                            if (checkRes && checkRes.exists) {
                                                await bootboxAlertAsync({
                                                    message: "{{ __('Mã phòng bị trùng, vui lòng thử lại.') }}",
                                                    size: 'small',
                                                    centerVertical: true,
                                                    closeButton: false,
                                                    buttons: { ok: { className: 'btn-danger', label: '{{ __("Oki") }}' } }
                                                });
                                                setTimeout(() => location.reload(), 500);
                                                return;
                                            }

                                            // Step 2: Prompt user for room name
                                            const roomName = await bootboxPromptAsync({
                                                title: "{{ __('Mời đặt tên cho Phòng thi đấu:') }}",
                                                locale: '{{ __("vi") }}',
                                                centerVertical: true,
                                                closeButton: false,
                                                maxlength: 32,
                                                buttons: {
                                                    confirm: {
                                                        label: '<i class="fas fa-check"></i> {{ __("Đặt tên") }}',
                                                        className: 'btn-danger'
                                                    }
                                                }
                                            });

                                            if (roomName === null) return; // User canceled prompt

                                            if (!roomName.trim()) {
                                                await bootboxAlertAsync({
                                                    message: "{{ __('Vui lòng đặt tên cho phòng!') }}",
                                                    size: 'small',
                                                    locale: '{{ __("vi") }}',
                                                    centerVertical: true,
                                                    closeButton: false,
                                                    buttons: { ok: { className: 'btn-danger', label: '{{ __("Oki") }}' } }
                                                });
                                                $('#create-room').trigger('click');
                                                return;
                                            }

                                            // Step 3: Call create room API
                                            await $.ajax({
                                                type: "POST",
                                                url: '{{ url('/api/createRoom') }}',
                                                data: {
                                                    'ma-phong': maPhong,
                                                    'ten-phong': roomName.trim(),
                                                    'FEN': '{{ config('xiangqi.initial_fen') }}',
                                                    'pass': '',
                                                    'host_id': '{{ auth()->id() }}'
                                                },
                                                dataType: 'json'
                                            });

                                            // Step 4: Notify success and redirect
                                            await bootboxAlertAsync({
                                                message: "{{ __('Bạn đã tạo phòng thành công.') }}",
                                                size: 'small',
                                                centerVertical: true,
                                                closeButton: false,
                                                buttons: { ok: { className: 'btn-danger', label: '{{ __("Oki") }}' } }
                                            });

                                            window.location.href = '{{ url(__('/phong/')) }}/' + maPhong;

                                        } catch (error) {
                                            console.error('An error occurred while creating the room:', error);
                                        }
                                    }

                                    // --- 3. Board Resizing Logic ---
                                    const $board = $('#ban-co');
                                    const initialWidth = $board.width() || 1;
                                    const initialHeight = $board.height() || 1;
                                    const ratio = initialHeight / initialWidth;

                                    function adjustBoard() {
                                        const $window = $(window);
                                        const windowWidth = $window.width();
                                        const windowHeight = $window.height();
                                        const containerWidth = $('header > .container').width() || windowWidth;

                                        let targetWidth = (windowHeight - 192) / ratio;

                                        if (windowWidth >= windowHeight && windowHeight < 992) {
                                            targetWidth = (windowHeight - 50) / ratio;
                                        }

                                        targetWidth = Math.min(targetWidth, containerWidth);

                                        $board.css({ 'width': targetWidth });

                                        if (typeof board !== 'undefined' && typeof board.resize === 'function') {
                                            board.resize();
                                        }
                                    }

                                    // --- 4. Share Board Event Handler ---
                                    $('#share-board').on('click auxclick', async function(e) {
                                        e.preventDefault();

                                        if (typeof game === 'undefined' || typeof game.fen !== 'function') {
                                            await bootboxAlertAsync("{{ __('Chưa tải được dữ liệu bàn cờ.') }}");
                                            return;
                                        }

                                        const currentFen = game.fen();
                                        if (!currentFen) return;

                                        window.location.href = $(this).attr('href') + '/' + currentFen;
                                    });
                                </script>
                                @include('common.volume')
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- @include('layouts.partials.fb') --}}
    </main>
    {{-- @include('layouts.partials.aiChatWidget') --}}
    @include('layouts.partials.footer')
    @include('common.contactBtn')
    @include('common.onlineCounter')
    @if (session('karma_earned'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof showKarmaBootbox === 'function') {
                    showKarmaBootbox(@json(session('karma_earned')));
                }
            });
        </script>
    @endif
</body>
</html>
