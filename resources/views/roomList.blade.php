@extends('layout.mainlayout')

@section('aboveContent')
    <div class="container-fluid game px-0">
        <div class="container p-3">
            <div class="row">
                @include('layout.partials.findMatch')
            </div>
            <h2 class="h1-responsivefooter text-center my-4">{{ __("Sảnh chờ") }}</h2>
            <div class="dropdown mx-auto text-center mb-3">
                <button data-step="1" data-intro="{{ __('Ấn vào đây để tham gia thi đấu với các kỳ thủ khác') }}" class="btn btn-danger btn-lg dropdown-toggle pulse-red" type="button" id="hostDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span data-toggle="tooltip" data-placement="top" title="{{ __("Đấu với bạn bè trong phòng") }}"><i class="fad fa-gamepad-alt"></i> {{ __("Chơi online") }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="hostDropdown" id="tao-phong" data-phong="{{ md5(time()) }}" data-url="{{ localized_url('room.host', ['code' => md5(time())]) }}">
                    @if (!auth()->check())
                        <a data-toggle="tooltip" data-placement="bottom" title="{{ __("Đăng nhập để tham gia thi đấu") }}" class="dropdown-item thi-dau" style="cursor: pointer !important;" href="{{ localized_url('login') }}"><i class="fas fa-sign-in text-dark"></i> {{ __("Đăng nhập") }}</a>
                    @else
                        <a id="create-room" data-toggle="tooltip" data-placement="bottom" title="{{ __("Thi đấu tính điểm và xếp hạng") }}" class="dropdown-item thi-dau" style="cursor: pointer !important;" href="javascript:createRoom();"><i class="fas fa-trophy-alt text-dark"></i> {{ __("Thi đấu") }}</a>
                    @endif
                    <a data-toggle="tooltip" data-placement="bottom" title="{{ __('Chơi cần mật khẩu') }}" id="tao-phong-private" class="dropdown-item" style="cursor: pointer !important;"><i class="fas fa-lock text-dark"></i> {{ __("Riêng tư") }}</a>
                    @if ($randomRoom != null)
                        <a data-toggle="tooltip" data-placement="bottom" title="{{ __('Chơi trong phòng Công khai ngẫu nhiên') }}" id="random-room" class="dropdown-item" style="cursor: pointer !important;" href="{{ localized_url('room.random', ['code' => $randomRoom['code'] ]) }}"><i class="fas fa-random text-dark"></i> {{ __("Ngẫu nhiên") }}</a>
                    @endif
                </div>
                @include('common.tourBtn')
            </div>
            <div data-step="2" data-intro="{{ __("Danh sách tất cả các trận đấu") }}" class="card shadow-lg mb-4">
                <div class="card-body p-0">
                    <table id="danh-sach-phong" class="table table-hover table-sm mb-0 dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th class="text-center" scope="col">{{ __("Tên phòng") }}</th>
                                <th class="text-center" scope="col">{{ __("Tới lượt") }}</th>
                                <th class="text-center" scope="col">{{ __("Kết quả") }}</th>
                                <th class="text-center" scope="col">{{ __("Hành động") }}</th>
                                <th class="text-center" scope="col">{{ __("Lần cuối chơi") }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate this natively utilizing the theme's text colors -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('belowContent')
    <div class="modal fade" id="HoveredBoardModal" tabindex="-1" role="dialog" aria-label="HoveredBoard" aria-hidden="true" data-backdrop="static" data-keyboard="false" data-url="">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 320px; margin: auto;">
            <div class="modal-content shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="far fa-eye"></i> {{ __("Xem trước \"") }}<span></span>"</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div id="HoveredBoardBody"></div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            console.log('List URL: ' + '{{ route(__('rooms_list_route')) }}');
            var table = $('#danh-sach-phong').DataTable({
                processing: true,
                responsive: true,
                serverSide: true,
                ordering: true,
                searching: true,
                ajax: {
                    url: "{{ route(__('rooms_list_route')) }}"
                },
                deferRender: true,
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 3 },
                    { responsivePriority: 3, targets: 1 },
                    { responsivePriority: 4, targets: 2 },
                    { responsivePriority: 5, targets: 4 }
                ],
                columns: [
                    {
                        data: 'code',
                        name: 'code',
                        orderable: true,
                        searchable: true,
                        className: 'text-center room-code'
                    },
                    {
                        data: 'turn',
                        name: 'turn',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'result',
                        name: 'result',
                        orderable: true,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center room-action'
                    },
                    {
                        data: 'time',
                        name: 'time',
                        orderable: true,
                        searchable: true,
                        className: 'text-right room-time'
                    }
                ],
                'language': {
                    'url': '{{ url('/') }}{{ __('table_lang_url') }}',
                    processing: '{{ __("Đang tải") }} <span class="indicator"></span><span class="indicator"></span><span class="indicator"></span>'
                },
                'createdRow': function(row, data, dataIndex) {
                    var selectedFen = $(row).find('td.room-code > a').attr('data-fen');
                    var selectedName = $(row).find('td.room-code > a').text();
                    $(row).attr('data-fen', selectedFen);
                    $(row).attr('data-name', selectedName);
                },
                'order': [[ 4, 'desc' ]],
                'drawCallback': function() {
                    $('.tooltip').remove();
                    $('[data-toggle="tooltip"]').tooltip(function() {
                        html : true
                    });
                    $('#danh-sach-phong .stopPromotion').each(function(){
                        $(this).on('click auxclick', function(e){
                            e.preventDefault();
                            $('#AdSenseModal').attr('data-url', $(this).attr('href')).modal('show');
                            $('#adModalCloseBtn').attr('data-original-title', $('#AdSenseModal').attr('data-url'));
                            $('#adModalCloseBtn').tooltip();
                        });
                    });
                    $('#danh-sach-phong > tbody > tr').each(function(index){
                        var fenCode = $(this).attr('data-fen');
                        var roomName = $(this).attr('data-name');
                        $(this).children('td.room-action').find('.previewBtn').on('click', function(){
                            $('#HoveredBoardModal').on('shown.bs.modal', function() {
                                var container = $('#HoveredBoardBody');
                                container.empty();
                                var boardId = 'hoveredBoardId_' + index;
                                var boardDiv = $('<div class="innerBoard">').attr('id', boardId);
                                container.html(boardDiv);
                                let boardConfig = {
                                    position: fenCode
                                };
                                if (fenCode.includes(' r ')) {
                                    boardConfig.orientation = 'red';
                                } else if (fenCode.includes(' b ')) {
                                    boardConfig.orientation = 'black';
                                }
                                var hoveredBoardDiv = Xiangqiboard(boardId, boardConfig);
                                $('#HoveredBoardModal .modal-title > span').text(roomName);
                            });
                            $('#HoveredBoardModal').modal('show');
                        });
                    });
                    $('.watch-btn').each(function() {
                        $(this).on('mouseenter mouseleave', function() {
                            if ($(this).find('i').hasClass('fa-lock')) {
                                $(this).find('i').removeClass('fa-lock').addClass('fa-unlock');
                            } else if ($(this).find('i').hasClass('fa-unlock')) {
                                $(this).find('i').removeClass('fa-unlock').addClass('fa-lock');
                            }
                            if ($(this).hasClass('btn-light')) {
                                $(this).removeClass('btn-light').addClass('btn-warning');
                            } else if ($(this).hasClass('btn-warning')) {
                                $(this).removeClass('btn-warning').addClass('btn-light');
                            }
                            if ($(this).hasClass('text-light')) {
                                $(this).removeClass('text-light').addClass('text-warning');
                            } else if ($(this).hasClass('text-warning')) {
                                $(this).removeClass('text-warning').addClass('text-light');
                            }
                        });
                    });
                    $('.room-code, #danh-sach-phong .btn').each(function(){
                        $(this).on('mouseenter mouseleave', function() {
                            if ($(this).find('i').hasClass('far')) {
                                $(this).find('i').removeClass('far').addClass('fas');
                            } else if ($(this).find('i').hasClass('fas')) {
                                $(this).find('i').removeClass('fas').addClass('far');
                            }
                        });
                    });
                }
            });
            $(window).on('resize', function () {
                table.columns.adjust();
            });
            setInterval( function () {
                table.ajax.reload( null, false );
            }, 15000 );
            $('.dataTables_length').addClass('bs-select');
        });

        @if (auth()->check())
        // ==========================================
        // 1. HELPER UTILITIES
        // ==========================================

        // Dynamically generate a unique 32-character hex room code
        const generateRoomCode = () => Array.from(crypto.getRandomValues(new Uint8Array(16)))
            .map(b => b.toString(16).padStart(2, '0'))
            .join('');

        // Promise wrappers to make Bootbox modals awaitable
        const bootboxAlertAsync = (options) => new Promise(resolve => {
            bootbox.alert({ ...options, callback: resolve });
        });

        const bootboxPromptAsync = (options) => new Promise(resolve => {
            bootbox.prompt({ ...options, callback: resolve });
        });

        // ==========================================
        // 2. REFACTORED FUNCTIONS
        // ==========================================

        async function createRoom() {
            const maPhong = generateRoomCode();

            try {
                // Step 1: Check if the room code already exists
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
                        buttons: { ok: { className: 'btn-danger pulse-red', label: 'Oki' } }
                    });
                    setTimeout(() => location.reload(), 500);
                    return;
                }

                // Step 2: Prompt user for the room name
                const roomName = await bootboxPromptAsync({
                    title: "{{ __('Mời đặt tên cho Phòng thi đấu:') }}",
                    locale: '{{ __("vi") }}',
                    centerVertical: true,
                    closeButton: false,
                    maxlength: 32,
                    buttons: {
                        confirm: {
                            label: '<i class="fas fa-check"></i> {{ __("Đặt tên") }}',
                            className: 'btn-danger pulse-red'
                        }
                    }
                });

                // User canceled the prompt
                if (roomName === null) return;

                // Validation check for empty name
                if (!roomName.trim()) {
                    await bootboxAlertAsync({
                        message: "{{ __('Vui lòng đặt tên cho phòng!') }}",
                        size: 'small',
                        locale: '{{ __("vi") }}',
                        centerVertical: true,
                        closeButton: false,
                        buttons: { ok: { className: 'btn-danger pulse-red' } }
                    });
                    $('#create-room').trigger('click');
                    return;
                }

                // Step 3: Call API to create the room
                await $.ajax({
                    type: "POST",
                    url: '{{ url('/api/createRoom') }}',
                    data: {
                        'ma-phong': maPhong,
                        'ten-phong': roomName.trim(),
                        'FEN': '{{ env('INITIAL_FEN') }}',
                        'pass': '',
                        'host_id': '{{ auth()->id() }}'
                    },
                    dataType: 'json'
                });

                // Step 4: Show success alert and redirect
                await bootboxAlertAsync({
                    message: "{{ __('Bạn đã tạo phòng thành công.') }}",
                    size: 'small',
                    centerVertical: true,
                    closeButton: false,
                    buttons: { ok: { className: 'btn-danger pulse-red', label: 'Oki' } }
                });

                window.location.href = '{{ url(__('/phong/')) }}/' + maPhong;

            } catch (error) {
                console.error('An error occurred while creating the room:', error);
            }
        }

        async function joinMatch(roomCode) {
            const currentUserId = Number('{{ auth()->id() }}');
            const hostUrl = '{{ url(__('/phong/')) }}/' + roomCode;
            const guestUrl = '{{ url(__('/phong/')) }}/' + roomCode + '{{ __('/khach') }}';

            try {
                // Step 1: Fetch existing host and guest IDs for the room
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

                // Step 2: Evaluate the logic based on the user's role
                if (hostId === currentUserId) {
                    alertMessage = "{{ __('Mời bạn vào lại phòng của mình!') }}";
                    targetUrl = hostUrl;
                } else if (guestId === currentUserId) {
                    alertMessage = "{{ __('Mời bạn quay lại phòng!') }}";
                    targetUrl = guestUrl;
                } else {
                    // New user joining as the guest
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

                // Step 3: Trigger the unified Bootbox alert and navigate
                await bootboxAlertAsync({
                    message: alertMessage,
                    size: 'small',
                    centerVertical: true,
                    closeButton: false,
                    buttons: {
                        ok: {
                            className: 'btn-danger pulse-red',
                            label: 'Oki'
                        }
                    }
                });

                window.location.href = targetUrl;

            } catch (error) {
                console.error('An error occurred while attempting to join the match:', error);
            }
        }
        @endif
    </script>
    <input type="hidden" name="piecesUrl" id="piecesUrl" value="{{ url('/') }}" >
    @include('layout.partials.players')
    @include('layout.partials.boards')
    @include('layout.partials.playedBoards')
@endsection
