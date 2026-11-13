{{--
    Board tooling shared by gamelayout and playlayout (non-puzzle pages):
    analyze, create room, responsive board sizing, share board.
    Requires xqHostRoomUrl() to be defined by the layout.
--}}
    <script>
        // --- Shared helpers ---
        // Resolves to the current FEN, or null (after optionally alerting) when the board isn't ready.
        async function getCurrentFenOrAlert(alertWhenMissing = true) {
            if (typeof game === 'undefined' || typeof game.fen !== 'function') {
                await bootboxAlertAsync("{{ __('Chưa tải được dữ liệu bàn cờ.') }}");
                return null;
            }

            const fen = game.fen();
            if (!fen && alertWhenMissing) {
                await bootboxAlertAsync("{{ __('Không lấy được mã FEN.') }}");
            }
            return fen || null;
        }

        // Small red-confirm alert used by the room flow.
        function dangerAlert(message, extraOptions = {}) {
            return bootboxAlertAsync({
                message: message,
                size: 'small',
                centerVertical: true,
                closeButton: false,
                buttons: { ok: { className: 'btn-danger', label: '{{ __("Oki") }}' } },
                ...extraOptions
            });
        }

        // --- 1. Analyze Board Event Handler ---
        $('#btn-analyze').on('click', async function(e) {
            e.preventDefault();

            const currentFen = await getCurrentFenOrAlert();
            if (!currentFen) return;

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
                    await dangerAlert("{{ __('Mã phòng bị trùng, vui lòng thử lại.') }}");
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
                    await dangerAlert("{{ __('Vui lòng đặt tên cho phòng!') }}", { locale: '{{ __("vi") }}' });
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
                await dangerAlert("{{ __('Bạn đã tạo phòng thành công.') }}");

                window.location.href = xqHostRoomUrl(maPhong);

            } catch (error) {
                console.error('An error occurred while creating the room:', error);
            }
        }

        // --- 3. Board Resizing Logic ---
        const $boardContainer = $('#ban-co');
        const initialWidth = $boardContainer.width() || 1;
        const initialHeight = $boardContainer.height() || 1;
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

            $boardContainer.css({ 'width': targetWidth });

            if (typeof board !== 'undefined' && typeof board.resize === 'function') {
                board.resize();
            }
        }

        // --- 4. Share Board Event Handler ---
        $('#share-board').on('click auxclick', async function(e) {
            e.preventDefault();

            const currentFen = await getCurrentFenOrAlert(false);
            if (!currentFen) return;

            window.location.href = $(this).attr('href') + '/' + currentFen;
        });
    </script>
    @include('common.volume')
