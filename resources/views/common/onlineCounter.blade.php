<div id="live-online-counter" class="online-counter-badge">
    <span class="pulsing-gem"></span>
    <div class="counter-text">
        <span id="online-count">...</span> <span class="counter-label">{{ __('Online') }}</span>
    </div>
</div>

<style>
    .online-counter-badge {
        position: fixed;
        bottom: 20px;
        right: 80px;

        /* Liquid Glass Theme Integration */
        background: var(--glass-bg-dark, rgba(11, 12, 16, 0.85));
        border: 1px solid var(--glass-border, rgba(255, 215, 0, 0.55));
        box-shadow:
            var(--liquid-shadow, 0 8px 32px 0 rgba(0, 0, 0, 0.8)),
            inset 0 0 15px rgba(212, 175, 55, 0.15), /* Inner royal gold glow */
            inset 0 1px 1px var(--liquid-highlight, rgba(255, 255, 255, 0.4)); /* Top glossy lip */

        color: var(--royal-gold, #ffd700);
        padding: 8px 18px;
        font-size: 0.9rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        z-index: 1050;

        /* Frosted Glass Blur */
        backdrop-filter: var(--glass-blur, blur(20px));
        -webkit-backdrop-filter: var(--glass-blur, blur(20px));

        display: flex;
        align-items: center;
        gap: 12px;
        border-radius: 50px;
        pointer-events: none;

        /* Typography Glow */
        text-shadow: 0 0 8px rgba(255, 215, 0, 0.5);
    }

    .counter-text {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Upgraded from a flat dot to a 3D Jade Gem */
    .pulsing-gem {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: radial-gradient(circle at 30% 30%, #4aff9a, #00b359);
        box-shadow:
            0 0 8px #00b359,
            inset 0 -2px 4px rgba(0, 0, 0, 0.5), /* Bottom shadow for 3D sphere effect */
            inset 0 2px 4px rgba(255, 255, 255, 0.8); /* Top highlight */
        animation: pulse-jade 2s infinite cubic-bezier(0.66, 0, 0, 1);
    }

    @keyframes pulse-jade {
        0% {
            box-shadow: 0 0 0 0 rgba(0, 250, 154, 0.6), inset 0 -2px 4px rgba(0,0,0,0.5), inset 0 2px 4px rgba(255, 255, 255, 0.8);
        }
        70% {
            box-shadow: 0 0 0 12px rgba(0, 250, 154, 0), inset 0 -2px 4px rgba(0,0,0,0.5), inset 0 2px 4px rgba(255, 255, 255, 0.8);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(0, 250, 154, 0), inset 0 -2px 4px rgba(0,0,0,0.5), inset 0 2px 4px rgba(255, 255, 255, 0.8);
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Fallback Initialization
        if (typeof window.Echo === 'undefined') {
            if (typeof Pusher !== 'undefined' && typeof Echo !== 'undefined') {
                // Make Pusher globally available for Echo
                window.Pusher = Pusher;

                // Initialize Echo using your Laravel .env variables
                window.Echo = new Echo({
                    broadcaster: 'reverb',
                    key: '{{ env("REVERB_APP_KEY") }}',
                    wsHost: '{{ env("REVERB_HOST") }}',
                    wsPort: {{ env("REVERB_PORT", 8080) }},
                    wssPort: {{ env("REVERB_PORT", 443) }},
                    forceTLS: ( '{{ env("REVERB_SCHEME", "https") }}' === 'https' ),
                    enabledTransports: ['ws', 'wss'],
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

        const countElement = document.getElementById('online-count');

        window.Echo.join('online')
            .here((users) => {
                countElement.innerText = users.length;
            })
            .joining((user) => {
                let currentCount = parseInt(countElement.innerText) || 0;
                countElement.innerText = currentCount + 1;
            })
            .leaving((user) => {
                let currentCount = parseInt(countElement.innerText) || 1;
                countElement.innerText = currentCount > 0 ? currentCount - 1 : 0;
            })
            .error((error) => {
                console.error('Pusher auth error:', error);
            });
    });
</script>
