@if(isset($_GET['loai']) && ($_GET['loai'] == 'van-da-dau' || $_GET['loai'] == 'van-dau' || $_GET['loai'] == 'co-the' || $_GET['loai'] == 'the-co'))
@if($firstPagePlayers->total() > 0)
<span style="background-color: transparent; margin-top: -15px;" class="d-block w-100 pb-5 mb-5" id="ky-thu"></span>
<div style="background-color: transparent" class="container-fluid puzzles px-0">
    <div class="container mx-auto px-3 pt-0">
        <div id="players-wrapper" class="row my-0">
            <h2 class="d-block w-100 ml-3 mb-4">
                <i class="fas fa-users"></i> {{ $firstPagePlayers->total() }} {{ __("kỳ thủ đang hoạt động, mời bạn") }} <a class="animate showPromotion" href="{{ localized_url('register') }}">{{ __("tham gia") }}</a>
            </h2>
            {{ $firstPagePlayers->links('vendor.pagination.playerVi') }}
            @foreach($firstPagePlayers as $player)
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div class="royal-grid-card h-100 d-flex flex-column text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-chess-king fa-3x" style="color: var(--royal-gold); filter: drop-shadow(0 0 10px rgba(212,175,55,0.5));"></i>
                    </div>

                    <h4 class="royal-card-title mb-1">{!! app('App\Http\Controllers\UserController')::renderName($player->id) !!}</h4>
                    <p class="mb-4" style="color: var(--royal-gold-light); font-size: 1.1rem;">
                        Elo: <span class="font-weight-bold" style="color: var(--royal-gold);">{!! app('App\Http\Controllers\UserController')::renderElo($player->id) !!}</span>
                    </p>

                    <div class="mt-auto">
                        @if (auth()->check())
                            @if (auth()->id() != $player->id)
                                <a class="btn btn-danger w-100 py-2 pulse-red" href="javascript:compete({{ $player->id }});" style="font-weight: 800;">
                                    <i class="fas fa-swords"></i> {{ __("Thách đấu") }}
                                </a>
                            @else
                                <a class="btn btn-dark w-100 py-2 disabled" style="cursor: not-allowed !important;" href="javascript:void(0);">
                                    <i class="fas fa-ban"></i> {{ __("Bản thân") }}
                                </a>
                            @endif
                        @else
                            <a class="btn btn-danger w-100 py-2 pulse-red" href="{{ localized_url('login') }}" style="font-weight: 800;">
                                <i class="fas fa-sign-in-alt"></i> {{ __("Thách đấu") }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
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
            <div id="players-wrapper" class="row my-0">
                <h2 class="d-block w-100 ml-3 mb-4">
                    <i class="fas fa-users"></i> {{ $players->total() }} {{ __("kỳ thủ đang hoạt động, mời bạn") }}  <a class="animate showPromotion" href="{{ localized_url('register') }}">{{ __("tham gia") }}</a>
                </h2>
                {{ $players->links('vendor.pagination.playerVi') }}
                @foreach($players as $player)
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-4">
                    <div class="royal-grid-card h-100 d-flex flex-column text-center p-4">
                        <div class="mb-3">
                            <i class="fas fa-chess-king fa-3x" style="color: var(--royal-gold); filter: drop-shadow(0 0 10px rgba(212,175,55,0.5));"></i>
                        </div>

                        <h4 class="royal-card-title mb-1">{!! app('App\Http\Controllers\UserController')::renderName($player->id) !!}</h4>
                        <p class="mb-4" style="color: var(--royal-gold-light); font-size: 1.1rem;">
                            Elo: <span class="font-weight-bold" style="color: var(--royal-gold);">{!! app('App\Http\Controllers\UserController')::renderElo($player->id) !!}</span>
                        </p>

                        <div class="mt-auto">
                            @if (auth()->check())
                                @if (auth()->id() != $player->id)
                                    <a class="btn btn-danger w-100 py-2 pulse-red" href="javascript:compete({{ $player->id }});" style="font-weight: 800;">
                                        <i class="fas fa-swords"></i> {{ __("Thách đấu") }}
                                    </a>
                                @else
                                    <a class="btn btn-dark w-100 py-2 disabled" style="cursor: not-allowed !important;" href="javascript:void(0);">
                                        <i class="fas fa-ban"></i> {{ __("Bản thân") }}
                                    </a>
                                @endif
                            @else
                                <a class="btn btn-danger w-100 py-2 pulse-red" href="{{ localized_url('login') }}" style="font-weight: 800;">
                                    <i class="fas fa-sign-in-alt"></i> {{ __("Thách đấu") }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
                {{ $players->links('vendor.pagination.playerVi') }}
            </div>
        </div>
    </div>
    @endif
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
                        className: 'btn-dark'
                    }
                },
                callback: function(roomName){
                if (roomName != null) {
                    if (roomName.trim().length === 0 || roomName.length === 0) {
                        bootbox.alert({
                            message: "{{ __('Vui lòng đặt tên cho phòng!') }}",
                            size: 'small', locale: '{{ __("vi") }}', centerVertical: true, closeButton: false,
                            buttons: { ok: { className: 'btn-danger' } },
                            callback: function () { $('#create-room').trigger('click'); }
                        });
                    } else {
                        $.ajax({
                            type: "POST",
                            url: '{{ url('/api/compete') }}',
                            data: {
                                'ma-phong': maPhong, 'ten-phong': roomName, 'FEN': '{{ env('INITIAL_FEN') }}',
                                'pass': '', 'host_id': '{{ auth()->id() }}', 'guest_id': guestId
                            },
                            dataType: 'text'
                        }).done(function() {
                            bootbox.alert({
                                message: "{{ __('Bạn đã tạo phòng thành công.') }}",
                                size: 'small', centerVertical: true, closeButton: false,
                                buttons: { ok: { className: 'btn-danger', label: '{{ __('Oki') }}' } },
                                callback: function(){
                                    $.ajax({
                                        type: "POST",
                                        url: '{{ url('/api') }}/competeMail',
                                        data: { 'ma-phong': maPhong, 'ten-phong': roomName, 'host_id': '{{ auth()->id() }}', 'guest_id': guestId },
                                        dataType: 'json'
                                    }).done(function(mailData) {
                                        bootbox.alert({
                                            message: mailData.message, size: 'small', centerVertical: true, closeButton: false,
                                            buttons: { ok: { className: 'btn-danger', label: '{{ __('Oki') }}' } },
                                            callback: function(){ window.location.href = '{{ url(__('/phong/')) }}' + '/' + maPhong; }
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
                message: "{{ __('Mã phòng bị trùng, vui lòng thử lại.') }}",
                size: 'small', centerVertical: true, closeButton: false,
                buttons: { ok: { className: 'btn-danger', label: '{{ __('Oki') }}' } },
                callback: function(){ setTimeout(() => { location.reload(); }, 500); }
            });
        }
    });
}
</script>
@endif

<script type="module">
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof window.Echo === 'undefined') {
            if (typeof Pusher !== 'undefined' && typeof Echo !== 'undefined') {
                // Make Pusher globally available for Echo
                window.Pusher = Pusher;

                // Initialize Echo using your Laravel .env variables
                window.Echo = new Echo({
                    broadcaster: 'pusher',
                    key: '{{ env("PUSHER_APP_KEY") }}',
                    cluster: '{{ env("PUSHER_APP_CLUSTER", "ap1") }}',
                    forceTLS: true,
                    authEndpoint: '/custom/broadcasting/auth',
                    auth: {
                        headers: {
                            'X-CSRF-Token': '{{ csrf_token() }}'
                        }
                    }
                });
            } else {
                console.warn("Pusher or Echo library is missing. Counter cannot connect.");
                return; // Stop execution if the JS libraries aren't loaded
            }
        }
        window.Echo.channel('players-channel')
            .listen('.players.updated', (e) => {
                // Fetch the exact same URL the user is currently on to grab the fresh HTML
                $.ajax({
                    url: window.location.href,
                    type: 'GET',
                    success: function(response) {
                        // Extract just the new grid and quantity wrapper
                        const newHtml = $(response).find('#players-wrapper').html();
                        if (newHtml) {
                            // Swap it into the DOM instantly
                            $('#players-wrapper').html(newHtml);
                        }
                    }
                });
            }
        );
    });
</script>
