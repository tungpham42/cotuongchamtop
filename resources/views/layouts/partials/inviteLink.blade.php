@php
    // Dynamically map the current role to the opposite room route[cite: 2]
    $inviteRoute = null;
    if ($role === 'host') {
        $inviteRoute = 'room.guest';
    } elseif ($role === 'guest') {
        $inviteRoute = 'room.host';
    } elseif ($role === 'red') {
        $inviteRoute = 'room.black';
    } elseif ($role === 'black') {
        $inviteRoute = 'room.red';
    }
@endphp

{{-- Glassmorphism Wrapper for Controls --}}
<div class="room-control-panel card shadow-lg mb-4 mx-auto" style="max-width: 650px; border-radius: 12px; background: var(--glass-bg-dark); backdrop-filter: var(--glass-blur);">
    <div class="card-body p-3 text-center">

        {{-- Show the invite link for any active playing role --}}
        @if ($inviteRoute)
            <p class="w-100 text-center mt-0 mb-2" style="color: var(--royal-gold-light); font-weight: 600; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                <i class="fad fa-external-link-alt" style="color: var(--royal-gold);"></i> {{ __("Mời bạn bè chơi bằng cách gửi liên kết bên dưới") }}.
            </p>

            {{-- Changed pulse-light to pulse-gold for the liquid royal theme --}}
            <div id="copy-url-invite" class="input-group mb-3 w-75 mx-auto pulse-gold" data-toggle="tooltip" data-placement="bottom" data-original-title="{{ __('Ấn để sao chép') }}" style="box-shadow: 0 4px 15px rgba(0,0,0,0.6); border-radius: 6px; cursor: pointer;">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="url-addon-invite" style="background: var(--glass-bg-dark); border: 1px solid var(--royal-gold); border-right: none; color: var(--royal-gold);">
                        <i class="fal fa-copy"></i>
                    </span>
                </div>
                {{-- Form control automatically inherits the gold/wood styling from CSS --}}
                <input data-step="1" data-intro="{{ __('Ấn vào đây để mời bạn bè cùng chơi') }}" type="text" class="form-control" id="url-invite" value="{{ localized_url($inviteRoute, ['code' => $roomCode]) }}" style="border-left: none; font-weight: 600; color: var(--royal-bg);" readonly>
            </div>
        @endif

        <p id="room-code" class="w-100 text-center mt-0 mb-2">
            {{-- Replaced generic alert-dark with a custom Liquid Glass Ruby Pill --}}
            <span data-step="{{ $role === 'host' ? '2' : '1' }}" data-intro="{{ __('Dùng mã phòng này để tìm kiếm trận đấu') }}" class="d-inline-block px-4 py-2" role="alert" data-toggle="tooltip" data-placement="bottom" data-original-title="{{ __('Sao chép mã phòng này nhé') }}" style="background: var(--glass-bg-red); border: 1px solid rgba(255, 215, 0, 0.5); border-radius: 8px; color: var(--royal-gold-light); box-shadow: inset 0 2px 8px rgba(255, 215, 0, 0.2), 0 4px 10px rgba(0,0,0,0.8); transition: all 0.3s ease;">
                <i class="fad fa-trophy-alt" style="color: var(--royal-gold);"></i> {{ __('Mã phòng') }}:
                <strong style="cursor: pointer; color: var(--royal-gold); letter-spacing: 1px; font-family: 'Noto Sans Mono', monospace; text-shadow: 0 0 10px rgba(255,215,0,0.5);">
                    {{ $roomCode }}
                </strong>
            </span>
            <input type="hidden" id="room-code-input" value="{{ $roomCode }}">
        </p>

        {{-- Change Password Section seamlessly integrated into the panel --}}
        @if ($role === 'host' && $room['pass'] != null)
            <hr style="border-top: 1px solid rgba(212, 175, 55, 0.2); width: 80%; margin: 15px auto;">
            <div data-step="3" data-intro="{{ __('Ấn vào đây để thay đổi mật khẩu') }}" id="change-pass" class="input-group mt-3 w-75 mx-auto">
                <div class="input-group-prepend">
                    <span class="input-group-text" style="background: transparent; border: none; color: var(--royal-gold); font-weight: 600;">
                        <i class="fad fa-key"></i>&nbsp;{{ __('Mật khẩu mới') }}
                    </span>
                </div>
                <input type="password" id="inputPassword" class="form-control" style="border-radius: 6px 0 0 6px;" placeholder="********" />
                <div class="input-group-append">
                    <button type="submit" class="btn btn-dark" onclick="validateForm();" style="border-radius: 0 6px 6px 0; border-left: none;">{{ __('Đổi') }}</button>
                </div>
            </div>
            <div id="status" class="w-100 text-center mt-2" style="color: var(--royal-gold-light); font-size: 0.9em; font-weight: 600;"></div>
        @endif
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // --- 1. Copy Invite Link Mechanism ---
        const copyUrlContainer = document.getElementById('copy-url-invite');
        const urlInput = document.getElementById('url-invite');

        if (copyUrlContainer && urlInput) {
            // Add pointer cursor for better UX
            copyUrlContainer.style.cursor = 'pointer';

            copyUrlContainer.addEventListener('click', function () {
                // Select the text field (helpful for mobile viewports)
                urlInput.select();
                urlInput.setSelectionRange(0, 99999);

                // Copy to clipboard
                navigator.clipboard.writeText(urlInput.value).then(function() {
                    // Update Bootstrap tooltip to show success feedback
                    if (typeof $ !== 'undefined') {
                        $(copyUrlContainer).attr('data-original-title', '{{ __("Đã sao chép!") }}').tooltip('show');

                        // Revert tooltip text after 2 seconds
                        setTimeout(function() {
                            $(copyUrlContainer).attr('data-original-title', '{{ __("Ấn để sao chép") }}');
                        }, 2000);
                    }
                }).catch(function(err) {
                    console.error('Failed to copy the link: ', err);
                });
            });
        }

        // --- 2. Copy Room Code Mechanism ---
        const roomCodeSpan = document.querySelector('#room-code span');
        const roomCodeInput = document.getElementById('room-code-input');

        if (roomCodeSpan && roomCodeInput) {
            // Add pointer cursor to indicate clickability
            roomCodeSpan.style.cursor = 'pointer';

            roomCodeSpan.addEventListener('click', function () {
                // Copy hidden input value to clipboard
                navigator.clipboard.writeText(roomCodeInput.value).then(function() {
                    // Update Bootstrap tooltip to show success feedback
                    if (typeof $ !== 'undefined') {
                        $(roomCodeSpan).attr('data-original-title', '{{ __("Đã sao chép!") }}').tooltip('show');

                        // Revert tooltip text after 2 seconds
                        setTimeout(function() {
                            $(roomCodeSpan).attr('data-original-title', '{{ __("Sao chép mã phòng này nhé") }}');
                        }, 2000);
                    }
                }).catch(function(err) {
                    console.error('Failed to copy the room code: ', err);
                });
            });
        }
    });
</script>
