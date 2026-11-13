{{--
    Room action row (join / login / back-to-lobby + volume + tour buttons).
    Shared by gamelayout and playlayout.
    Params: $roomCode, optional $skipJoinForTournament (bool, playlayout)
    Requires xqHostRoomUrl() / xqGuestRoomUrl() to be defined by the layout.
--}}
@if ( $roomCode != '' )
    <p class="w-100 text-center my-3 d-flex justify-content-center align-items-center flex-wrap gap-2">
        @if (!isset($room->result) && isset($room->host_id))
            @if (auth()->check())
                @if (empty($skipJoinForTournament) || !isset($room->tournament_id))
                    <a id="choi" class="btn btn-danger text-light btn-lg showPromotion mx-2" href="javascript:joinMatch('{{ $roomCode }}')"><i class="fad fa-mouse"></i> {{ __('Chơi') }}</a>
                @endif
                <script>
                    async function joinMatch(roomCode) {
                        const currentUserId = Number('{{ auth()->id() }}');
                        const hostUrl = xqHostRoomUrl(roomCode);
                        const guestUrl = xqGuestRoomUrl(roomCode);

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
