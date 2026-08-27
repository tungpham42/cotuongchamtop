<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.bundle.min.js" integrity="sha512-igl8WEUuas9k5dtnhKqyyld6TzzRjvMqLC79jkgT3z02FvJyHAuUtyemm/P/jYSne1xwFI06ezQxEwweaiV7VA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/notify/0.4.2/notify.min.js" integrity="sha256-tSRROoGfGWTveRpDHFiWVz+UXt+xKNe90wwGn25lpw8=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js" integrity="sha256-0rguYS0qgS6L4qVzANq4kjxPLtvnp5nn2nB5G1lWRv4=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js" integrity="sha512-BkpSL20WETFylMrcirBahHfSnY++H2O1W+UnEEO4yNIl+jI2+zowyoGJpbtk6bx97fBXf++WJHSSK2MV4ghPcg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap4.min.js" integrity="sha512-OQlawZneA7zzfI6B1n1tjUuo3C5mtYuAWpQdg+iI9mkDoo7iFzTqnQHf+K5ThOWNJ9AbXL4+ZDwH7ykySPQc+A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/6.0.0/bootbox.min.js" integrity="sha512-oVbWSv2O4y1UzvExJMHaHcaib4wsBMS5tEP3/YkMP6GmkwRJAa79Jwsv+Y/w7w2Vb/98/Xhvck10LyJweB8Jsw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/2.9.3/intro.min.js" integrity="sha512-VTd65gL0pCLNPv5Bsf5LNfKbL8/odPq0bLQ4u226UNmT7SzE4xk+5ckLNMuksNTux/pDLMtxYuf0Copz8zMsSA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js" integrity="sha512-E8QSvWZ0eCLGk4km3hxSsNmGWbLtSCSUcewDQPQWZF6pEU8GlT8a5fF32wOl1i8ftdMhssTrF/OhyGWwonTcXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/transliteration@2.3.5/dist/browser/bundle.umd.min.js" integrity="sha256-WM+Q7gs+YPKhWaTZxr24xQ9DF8yT7m2WJdrKYBVdGh4=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/speakingurl/14.0.1/speakingurl.min.js" integrity="sha512-i1kgQZJBA3n0k1Ar2++6FKibz8fDlaDpZ8ZLKpCnypYznNL++R6FPxpKJP6NGwsO2sAzzX4x78XjiYrLtMWAfA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/blueimp-md5/2.19.0/js/md5.min.js" integrity="sha512-8pbzenDolL1l5OPSsoURCx9TEdMFTaeFipASVrMYKhuYtly+k3tcsQYliOEKTmuB1t7yuzAiVo+yd7SJz+ijFQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.8.4/axios.min.js" integrity="sha512-2A1+/TAny5loNGk3RBbk11FwoKXYOMfAK6R7r4CpQH7Luz4pezqEGcfphoNzB7SM4dixUoJsKkBsB6kg+dNE2g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
{{-- <script data-pace-options='{ "ajax": false }' src="https://cdnjs.cloudflare.com/ajax/libs/pace/1.2.4/pace.min.js" integrity="sha512-2cbsQGdowNDPcKuoBd2bCcsJky87Mv0LEtD/nunJUgk6MOYTgVMGihS/xCEghNf04DPhNiJ4DZw5BxDd1uyOdw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> --}}
<script src="{{ asset('js/scripts.js?v=18') }}"></script>
<script src="{{ asset('js/manipulation.js') }}"></script>
<script src="{{ asset('js/xiangqi.js?v=62') }}"></script>
{{-- @include('common.snow') --}}
{{-- @include('common.flower') --}}
<script src="https://js.pusher.com/8.3.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

<script>
    // ==========================================
    // HELPER UTILITIES
    // ==========================================

    // Dynamically generate a unique 32-character hex room code
    const generateRoomCode = () => Array.from(crypto.getRandomValues(new Uint8Array(16)))
        .map(b => b.toString(16).padStart(2, '0'))
        .join('');

    // ==========================================
    // BOOTBOX ASYNC HELPERS
    // ==========================================

    const createBootboxAsync = (method) => (options) => {
        return new Promise((resolve) => {
            // Bootbox 'prompt' uses 'title' for its main text, while others use 'message'
            const primaryKey = method === 'prompt' ? 'title' : 'message';

            // 1. Flexibility: Map a simple string to the correct property for the method
            const config = typeof options === 'string'
                ? { [primaryKey]: options }
                : { ...options };

            // 2. Safety: Only apply the fallback if the config lacks BOTH message and title
            if (!config.message && !config.title) {
                config[primaryKey] = "An unexpected event occurred.";
            }

            // 3. Execution: Call the requested Bootbox method and resolve the promise
            bootbox[method]({
                ...config,
                callback: resolve
            });
        });
    };

    // Generate your reusable, safe async functions dynamically
    const bootboxAlertAsync = createBootboxAsync('alert');
    const bootboxPromptAsync = createBootboxAsync('prompt');
    const bootboxConfirmAsync = createBootboxAsync('confirm');

    // Make Pusher globally available for Echo
    window.Pusher = Pusher;

    // ==========================================
    // KARMA NOTIFICATIONS
    // ==========================================

    // Turns a list of { amount, reason, label } entries (as returned by
    // /api/updateResult, /api/updateSideResult, or session-flashed on
    // login) into an HTML snippet for a bootbox message.
    function buildKarmaMessage(karmaEntries) {
        if (!Array.isArray(karmaEntries) || !karmaEntries.length) return '';

        const lines = karmaEntries.map((k) => `+${k.amount} {{ __('karma') }} — ${k.label}`);

        return '<hr class="my-2">' +
            '<div class="text-warning font-weight-bold">' +
            '<i class="fas fa-seedling"></i> ' +
            lines.join('<br>') +
            '</div>';
    }

    // Pops a standalone bootbox for karma earned outside of an existing
    // result dialog (e.g. the daily login bonus on page load).
    function showKarmaBootbox(karmaEntries) {
        const message = buildKarmaMessage(karmaEntries);
        if (!message) return;

        bootbox.alert({
            message: message,
            size: 'small',
            centerVertical: true,
            closeButton: false,
            buttons: { ok: { className: 'btn-danger pulse-red', label: '{{ __("Oki") }}' } }
        });
    }

    // Initialize Echo using your Laravel .env variables
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: '{{ config("broadcasting.connections.pusher.key") }}',
        cluster: '{{ config("broadcasting.connections.pusher.options.cluster", "ap1") }}',
        forceTLS: true,
        authEndpoint: '/custom/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-Token': '{{ csrf_token() }}'
            }
        }
    });
</script>
