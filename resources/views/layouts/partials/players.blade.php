@php
    $isFiltered = isset($_GET['loai']) && in_array($_GET['loai'], ['van-da-dau', 'van-dau', 'co-the', 'the-co']);
    $playerCollection = $isFiltered ? $firstPagePlayers : $players;
    $shouldDisplay = $playerCollection->total() > 0 && ($isFiltered || Request::get('page') <= ceil($players->total() / max($players->perPage(), 1)));
@endphp

@if($shouldDisplay)
<span style="background-color: transparent; margin-top: -15px;" class="d-block w-100 pb-5 mb-5" id="ky-thu"></span>
<div style="background-color: transparent" class="container-fluid puzzles px-0">
    <div class="container mx-auto px-3 pt-0">
        <div class="row my-0">
            <h2 class="d-block w-100 ml-3 mb-4">
                <i class="fas fa-users"></i> {{ $playerCollection->total() }} {{ __("kỳ thủ đang hoạt động, mời bạn") }} <a class="animate showPromotion" href="{{ localized_url('register') }}">{{ __("tham gia") }}</a>
            </h2>
            {{ $playerCollection->links('vendor.pagination.playerVi') }}

            @foreach($playerCollection as $player)
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div class="royal-grid-card h-100 d-flex flex-column text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-user fa-3x" style="color: var(--royal-gold); filter: drop-shadow(0 0 10px rgba(212,175,55,0.5));"></i>
                    </div>

                    <h4 class="royal-card-title mb-1">{!! $userPresenter->renderPlayerName($player->id, false, true) !!}</h4>
                    <p class="mb-4" style="color: var(--royal-gold-light); font-size: 1.1rem;">
                        Elo: <span class="font-weight-bold" style="color: var(--royal-gold);">{!! $userPresenter->renderElo($player->id) !!}</span>
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

            {{ $playerCollection->links('vendor.pagination.playerVi') }}
        </div>
    </div>
</div>

@if (auth()->check())
<script>

async function compete(guestId) {
    // Dynamically generate a unique room code per call (32-character hex)
    const maPhong = Array.from(crypto.getRandomValues(new Uint8Array(16)))
        .map(b => b.toString(16).padStart(2, '0'))
        .join('');

    try {
        // 1. Check room code availability
        const checkRes = await $.ajax({
            type: "POST",
            url: '{{ url('/api/hasRoomcode') }}',
            data: { 'ma-phong': maPhong },
            dataType: 'json'
        });

        if (checkRes.exists) {
            await bootboxAlertAsync({
                message: "{{ __('Mã phòng bị trùng, vui lòng thử lại.') }}",
                size: 'small',
                centerVertical: true,
                closeButton: false,
                buttons: { ok: { className: 'btn-danger', label: '{{ __('Oki') }}' } }
            });
            setTimeout(() => location.reload(), 500);
            return;
        }

        // 2. Prompt user for room name
        const roomName = await bootboxPromptAsync({
            title: "{{ __('Mời đặt tên cho Phòng thi đấu:') }}",
            locale: '{{ __("vi") }}',
            centerVertical: true,
            closeButton: false,
            maxlength: 32,
            buttons: {
                confirm: { label: '<i class="fas fa-check"></i> {{ __("Đặt tên") }}', className: 'btn-danger' },
                cancel: { className: 'btn-dark' }
            }
        });

        // User cancelled the prompt
        if (roomName === null) return;

        // Validation check
        if (!roomName.trim()) {
            await bootboxAlertAsync({
                message: "{{ __('Vui lòng đặt tên cho phòng!') }}",
                size: 'small',
                locale: '{{ __("vi") }}',
                centerVertical: true,
                closeButton: false,
                buttons: { ok: { className: 'btn-danger' } }
            });
            $('#create-room').trigger('click');
            return;
        }

        // 3. Create the room
        await $.ajax({
            type: "POST",
            url: '{{ url('/api/compete') }}',
            data: {
                'ma-phong': maPhong,
                'ten-phong': roomName.trim(),
                'FEN': '{{ env('INITIAL_FEN') }}',
                'pass': '',
                'host_id': '{{ auth()->id() }}',
                'guest_id': guestId
            },
            dataType: 'text'
        });

        await bootboxAlertAsync({
            message: "{{ __('Bạn đã tạo phòng thành công.') }}",
            size: 'small',
            centerVertical: true,
            closeButton: false,
            buttons: { ok: { className: 'btn-danger', label: '{{ __('Oki') }}' } }
        });

        // 4. Send email notification
        const mailData = await $.ajax({
            type: "POST",
            url: '{{ url('/api/competeMail') }}',
            data: {
                'ma-phong': maPhong,
                'ten-phong': roomName.trim(),
                'host_id': '{{ auth()->id() }}',
                'guest_id': guestId,
                'lang': '{{ app()->getLocale() }}'
            },
            dataType: 'json'
        });

        await bootboxAlertAsync({
            message: mailData.message,
            size: 'small',
            centerVertical: true,
            closeButton: false,
            buttons: { ok: { className: 'btn-danger', label: '{{ __('Oki') }}' } }
        });

        // 5. Redirect to the newly created room
        window.location.href = '{{ url(__('/phong/')) }}' + '/' + maPhong;

    } catch (error) {
        console.error('An error occurred during compete execution:', error);
    }
}
</script>
@endif
@endif
