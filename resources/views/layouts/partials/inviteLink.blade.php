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

<style>
    /* Invite panel. The inline styles that used to live on each element are classes now,
       so light mode can restyle them. The dark look below is exactly the previous one. */
    .invite-panel { max-width: 650px; border-radius: 12px; background: var(--glass-bg-dark); backdrop-filter: var(--glass-blur); }
    .invite-lead { color: var(--royal-gold-light); font-weight: 600; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5); }
    .invite-lead i { color: var(--royal-gold); }
    #copy-url-invite { box-shadow: 0 4px 15px rgba(0, 0, 0, 0.6); border-radius: 6px; cursor: pointer; }
    #url-addon-invite { background: var(--glass-bg-dark); border: 1px solid var(--royal-gold); border-right: none; color: var(--royal-gold); }
    #url-invite { border-left: none; font-weight: 600; color: var(--royal-bg); }
    .invite-room-pill {
        background: var(--glass-bg-red);
        border: 1px solid rgba(255, 215, 0, 0.5);
        border-radius: 8px;
        color: var(--royal-gold-light);
        box-shadow: inset 0 2px 8px rgba(255, 215, 0, 0.2), 0 4px 10px rgba(0, 0, 0, 0.8);
        transition: all 0.3s ease;
    }
    .invite-room-pill i { color: var(--royal-gold); }
    .invite-room-pill strong { cursor: pointer; color: var(--royal-gold); letter-spacing: 1px; font-family: 'Noto Sans Mono', monospace; text-shadow: 0 0 10px rgba(255, 215, 0, 0.5); }
    .invite-sep { border-top: 1px solid rgba(212, 175, 55, 0.2); width: 80%; margin: 15px auto; }
    .invite-pass-label { background: transparent; border: none; color: var(--royal-gold); font-weight: 600; }
    .invite-pass-input { border-radius: 6px 0 0 6px; }
    .invite-pass-btn { border-radius: 0 6px 6px 0; border-left: none; }
    .invite-status { color: var(--royal-gold-light); font-size: 0.9em; font-weight: 600; }

    /* ---- Light mode ---- */
    :root[data-theme="light"] .invite-lead { color: #5a2f1b; text-shadow: none; }
    :root[data-theme="light"] .invite-lead i { color: #9a5b12; }
    :root[data-theme="light"] #copy-url-invite { box-shadow: 0 4px 12px rgba(107, 58, 34, 0.2); }
    :root[data-theme="light"] #url-addon-invite { background: #fff6e4; border-color: rgba(138, 74, 36, 0.5); color: #9a5b12; }
    :root[data-theme="light"] #url-invite { color: #3a1f14; }
    /* the room code pill matches the terracotta primary buttons */
    :root[data-theme="light"] .invite-room-pill {
        background: linear-gradient(180deg, #d9674c, #a8402c);
        border-color: rgba(255, 214, 160, 0.6);
        color: #fff6e4;
        box-shadow: 0 6px 14px rgba(168, 64, 44, 0.35), inset 0 1px 0 rgba(255, 236, 200, 0.45);
    }
    :root[data-theme="light"] .invite-room-pill i,
    :root[data-theme="light"] .invite-room-pill strong { color: #ffd98a; text-shadow: 0 1px 2px rgba(60, 14, 6, 0.4); }
    :root[data-theme="light"] .invite-sep { border-top-color: rgba(138, 74, 36, 0.25); }
    :root[data-theme="light"] .invite-pass-label { color: #8a2f1d; }
    :root[data-theme="light"] .invite-status { color: #5a2f1b; }
</style>

{{-- Glassmorphism Wrapper for Controls --}}
<div class="room-control-panel invite-panel card shadow-lg mb-4 mx-auto">
    @php $hasOpponent = isset($room->host_id) && isset($room->guest_id); @endphp
    <button type="button" id="invite-toggle" class="invite-toggle btn btn-dark btn-sm w-100" aria-expanded="{{ $hasOpponent ? 'false' : 'true' }}" aria-controls="invite-body">
        <i class="fad fa-user-plus"></i> {{ __('Mời bạn bè') }} · <span class="room-code-mini">{{ $roomCode }}</span>
        <i class="fas fa-chevron-down invite-chevron"></i>
    </button>
    <div id="invite-body" class="card-body p-3 text-center {{ $hasOpponent ? 'd-none' : '' }}">

        {{-- Show the invite link for any active playing role --}}
        @if ($inviteRoute)
            <p class="invite-lead w-100 text-center mt-0 mb-2">
                <i class="fad fa-external-link-alt"></i> {{ __("Mời bạn bè chơi bằng cách gửi liên kết bên dưới") }}.
            </p>

            {{-- Changed pulse-light to pulse-gold for the liquid royal theme --}}
            <div id="copy-url-invite" class="input-group mb-3 w-75 mx-auto pulse-gold" data-toggle="tooltip" data-placement="bottom" data-original-title="{{ __('Ấn để sao chép') }}">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="url-addon-invite">
                        <i class="fal fa-copy"></i>
                    </span>
                </div>
                {{-- Form control automatically inherits the gold/wood styling from CSS --}}
                <input data-step="1" data-intro="{{ __('Ấn vào đây để mời bạn bè cùng chơi') }}" type="text" class="form-control" id="url-invite" value="{{ localized_url($inviteRoute, ['code' => $roomCode]) }}" readonly>
            </div>

            {{-- Subtle QR code for the same invite link. Rendered client-side (no extra request),
                 tap/click/Enter to enlarge it. Hidden automatically if the library fails to load. --}}
            <div id="invite-qr-wrap" class="invite-qr-wrap d-none">
                <div id="invite-qr" class="invite-qr" role="button" tabindex="0" aria-pressed="false" aria-label="{{ __('Quét mã QR để vào phòng') }}" title="{{ __('Quét mã QR để vào phòng') }}">
                    <span class="invite-qr-frame">
                        <span class="invite-qr-card">
                            <span id="invite-qr-svg" class="invite-qr-svg"></span>
                        </span>
                    </span>
                </div>
                <span class="invite-qr-caption"><i class="fad fa-qrcode"></i> {{ __('Quét mã QR để vào phòng') }}</span>
            </div>
        @endif

        <p id="room-code" class="w-100 text-center mt-0 mb-2">
            {{-- Replaced generic alert-dark with a custom Liquid Glass Ruby Pill --}}
            <span data-step="{{ $role === 'host' ? '2' : '1' }}" data-intro="{{ __('Dùng mã phòng này để tìm kiếm trận đấu') }}" class="invite-room-pill d-inline-block px-4 py-2" role="alert" data-toggle="tooltip" data-placement="bottom" data-original-title="{{ __('Sao chép mã phòng này nhé') }}">
                <i class="fad fa-trophy-alt"></i> {{ __('Mã phòng') }}:
                <strong>
                    {{ $roomCode }}
                </strong>
            </span>
            <input type="hidden" id="room-code-input" value="{{ $roomCode }}">
        </p>

        {{-- Change Password Section seamlessly integrated into the panel --}}
        @if ($role === 'host' && $room['pass'] != null)
            <hr class="invite-sep">
            <div data-step="3" data-intro="{{ __('Ấn vào đây để thay đổi mật khẩu') }}" id="change-pass" class="input-group mt-3 w-75 mx-auto">
                <div class="input-group-prepend">
                    <span class="input-group-text invite-pass-label">
                        <i class="fad fa-key"></i>&nbsp;{{ __('Mật khẩu mới') }}
                    </span>
                </div>
                <input type="password" id="inputPassword" class="form-control invite-pass-input" placeholder="********" />
                <div class="input-group-append">
                    <button type="submit" class="btn btn-dark invite-pass-btn" onclick="validateForm();">{{ __('Đổi') }}</button>
                </div>
            </div>
            <div id="status" class="invite-status w-100 text-center mt-2"></div>
        @endif
    </div>
</div>
<script src="{{ asset('js/qrcode-generator.js?v=1') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inviteToggle = document.getElementById('invite-toggle');
        const inviteBody = document.getElementById('invite-body');
        if (inviteToggle && inviteBody) {
            inviteToggle.addEventListener('click', function () {
                const open = inviteBody.classList.toggle('d-none') === false;
                inviteToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        }

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

        // --- QR code for the invite link ---
        (function () {
            const wrap = document.getElementById('invite-qr-wrap');
            const holder = document.getElementById('invite-qr-svg');
            const qrBtn = document.getElementById('invite-qr');
            const link = document.getElementById('url-invite');
            if (!wrap || !holder || !qrBtn || !link || typeof qrcode !== 'function') return;

            try {
                const qr = qrcode(0, 'M');
                qr.addData(link.value);
                qr.make();

                const n = qr.getModuleCount();
                const margin = 1; // extra quiet zone, on top of the card padding
                const size = n + margin * 2;
                let rects = '';
                for (let r = 0; r < n; r++) {
                    for (let c = 0; c < n; c++) {
                        if (qr.isDark(r, c)) {
                            rects += '<rect x="' + (c + margin) + '" y="' + (r + margin) + '" width="1.02" height="1.02" rx="0.32"/>';
                        }
                    }
                }
                // Crimson -> ebony gradient keeps strong contrast on the cream card, so it stays scannable.
                holder.innerHTML =
                    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + size + ' ' + size + '" shape-rendering="geometricPrecision" role="img" aria-hidden="true">' +
                    '<defs><linearGradient id="inviteQrInk" gradientUnits="userSpaceOnUse" x1="0" y1="0" x2="' + size + '" y2="' + size + '">' +
                    '<stop offset="0" stop-color="#8a1515"/><stop offset="0.55" stop-color="#2a0a0a"/><stop offset="1" stop-color="#0b0c10"/>' +
                    '</linearGradient></defs>' +
                    '<g fill="url(#inviteQrInk)">' + rects + '</g></svg>';
                wrap.classList.remove('d-none');
            } catch (err) {
                console.error('Could not build the invite QR code: ', err);
                return;
            }

            const toggleQr = function () {
                const zoomed = qrBtn.classList.toggle('is-zoomed');
                qrBtn.setAttribute('aria-pressed', zoomed ? 'true' : 'false');
            };
            qrBtn.addEventListener('click', toggleQr);
            qrBtn.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggleQr(); }
            });
        })();

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
