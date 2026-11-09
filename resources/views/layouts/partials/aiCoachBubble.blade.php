<style>
:root {
    --coach-red: #c62828;
    --coach-gold: #ffc107;
    --coach-cream: #fffdf5;
    --coach-ink: #1a1a1a;
}

/* ===== Vùng chứa cố định ===== */
#ai-coach {
    position: fixed;
    bottom: 24px;
    left: 20px;
    z-index: 1060;
    font-family: "Roboto", "Segoe UI", Arial, sans-serif;
    user-select: none;
}

/* ===== Mascot: quân Tướng 帥 ===== */
#coach-mascot {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    background: radial-gradient(circle at 30% 25%, #fff8e1 0%, #ffe082 45%, #ffb300 100%);
    border: 3px solid var(--coach-red);
    box-shadow: 0 6px 22px rgba(198, 40, 40, 0.45), 0 0 0 0 rgba(255, 193, 7, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.25s cubic-bezier(.34,1.56,.64,1);
    position: relative;
    animation: coachBreath 3.2s ease-in-out infinite;
}
#coach-mascot:hover { transform: scale(1.08) rotate(-3deg); }
#coach-mascot.thinking { animation: coachThink 0.9s ease-in-out infinite; }
#coach-mascot.happy   { animation: coachWobble 0.6s ease-in-out; }

.mascot-char {
    font-family: "KaiTi", "STKaiti", serif;
    font-size: 34px;
    font-weight: 900;
    color: var(--coach-red);
    text-shadow: 0 1px 0 #fff, 0 -1px 0 rgba(0,0,0,0.15);
    line-height: 1;
}

/* Badge đếm lượt (cho non-VIP) */
.mascot-badge {
    position: absolute;
    top: -4px;
    left: -4px;
    min-width: 22px;
    height: 22px;
    padding: 0 5px;
    border-radius: 11px;
    background: var(--coach-ink);
    color: var(--coach-gold);
    font-size: 11px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--coach-gold);
}

/* ===== Vòng nút hành động ===== */
#coach-actions {
    position: absolute;
    bottom: 74px;
    left: 8px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    opacity: 0;
    pointer-events: none;
    transform: translateY(10px);
    transition: opacity 0.22s, transform 0.22s;
}
#coach-actions.open {
    opacity: 1;
    pointer-events: auto;
    transform: translateY(0);
}

.coach-action-btn {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 2px solid var(--coach-gold);
    background: #fff;
    color: var(--coach-red);
    font-size: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: transform 0.15s, background 0.15s;
    position: relative;
}
.coach-action-btn:hover {
    transform: scale(1.12);
    background: var(--coach-cream);
}
.coach-action-btn::after {
    content: attr(data-label);
    position: absolute;
    left: 58px;
    background: var(--coach-ink);
    color: #fff;
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 12px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.15s;
}
.coach-action-btn:hover::after { opacity: 1; }

/* ===== Speech bubble ===== */
#coach-bubble {
    position: absolute;
    bottom: 82px;
    left: 0;
    width: 340px;
    background: var(--coach-cream);
    border: 2px solid var(--coach-gold);
    border-radius: 16px;
    box-shadow: 0 14px 40px rgba(0,0,0,0.28);
    overflow: hidden;
    opacity: 0;
    transform: translateY(12px) scale(0.96);
    pointer-events: none;
    transition: opacity 0.24s, transform 0.24s;
}
#coach-bubble.show {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: auto;
}
/* Đuôi bubble chỉ về mascot */
#coach-bubble::after {
    content: "";
    position: absolute;
    bottom: -9px;
    left: 28px;
    width: 16px;
    height: 16px;
    background: var(--coach-cream);
    border-left: 2px solid var(--coach-gold);
    border-bottom: 2px solid var(--coach-gold);
    transform: rotate(45deg);
}

.bubble-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(135deg, var(--coach-ink) 0%, var(--coach-red) 100%);
    color: #fff;
    padding: 9px 14px;
    font-weight: 700;
    font-size: 14px;
}
.bubble-head .tag {
    background: var(--coach-gold);
    color: var(--coach-ink);
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.5px;
}
.bubble-icons { display: flex; gap: 6px; }
.bubble-icons button {
    background: transparent;
    border: 0;
    color: #fff;
    font-size: 15px;
    cursor: pointer;
    opacity: 0.85;
}
.bubble-icons button:hover { opacity: 1; }
.bubble-icons button.active { color: var(--coach-gold); }

.bubble-body {
    padding: 14px 16px 10px;
    font-size: 14px;
    line-height: 1.55;
    color: #222;
    max-height: 220px;
    overflow-y: auto;
}
.bubble-body strong { color: var(--coach-red); }
.bubble-body em { color: #1565c0; font-style: normal; font-weight: 600; }

.bubble-foot {
    display: flex;
    gap: 8px;
    padding: 0 14px 12px;
    flex-wrap: wrap;
}
.bubble-foot button {
    background: #fff;
    border: 1px solid #e0c974;
    color: var(--coach-red);
    font-size: 12px;
    font-weight: 600;
    padding: 5px 11px;
    border-radius: 12px;
    cursor: pointer;
    transition: background 0.15s;
}
.bubble-foot button:hover { background: #fff5d6; }

/* ===== Board overlay (SVG) ===== */
.board-coach-overlay {
    position: absolute !important;
    inset: 0;
    pointer-events: none;
    z-index: 5;
}
.board-coach-overlay svg { width: 100%; height: 100%; display: block; }

/* ===== Animations ===== */
@keyframes coachBreath {
    0%,100% { box-shadow: 0 6px 22px rgba(198,40,40,0.45), 0 0 0 0 rgba(255,193,7,0.6); }
    50%     { box-shadow: 0 6px 22px rgba(198,40,40,0.45), 0 0 0 8px rgba(255,193,7,0); }
}
@keyframes coachThink {
    0%,100% { transform: rotate(0deg); }
    25%     { transform: rotate(-6deg); }
    75%     { transform: rotate(6deg); }
}
@keyframes coachWobble {
    0%,100% { transform: scale(1); }
    40%     { transform: scale(1.15) rotate(-5deg); }
    70%     { transform: scale(1.1) rotate(4deg); }
}

@media (max-width: 576px) {
    #ai-coach { bottom: 16px; left: 12px; }
    #coach-bubble { width: calc(100vw - 32px); left: -8px; }
    #coach-mascot { width: 58px; height: 58px; }
    .mascot-char { font-size: 28px; }
}
</style>

<div id="ai-coach">
    {{-- Vòng nút hành động --}}
    <div id="coach-actions">
        <button class="coach-action-btn" data-intent="hint"    data-label="{{ __('Gợi ý nước đi') }}">💡</button>
        <button class="coach-action-btn" data-intent="threats" data-label="{{ __('Mối đe dọa') }}">🛡️</button>
        <button class="coach-action-btn" data-intent="eval"    data-label="{{ __('Đánh giá thế cờ') }}">⚖️</button>
    </div>

    {{-- Speech bubble --}}
    <div id="coach-bubble" role="status" aria-live="polite">
        <div class="bubble-head">
            <span><span class="tag">{{ __('KỲ SƯ AI') }}</span> {{ __('Đồng hành cùng bạn') }}</span>
            <div class="bubble-icons">
                <button id="coach-tts" title="{{ __('Đọc thành tiếng') }}">🔊</button>
                <button id="coach-close" title="{{ __('Đóng') }}">✕</button>
            </div>
        </div>
        <div class="bubble-body" id="coach-bubble-body"></div>
        <div class="bubble-foot" id="coach-bubble-foot"></div>
    </div>

    {{-- Mascot --}}
    <div id="coach-mascot" title="{{ __('Bấm để gọi Kỳ Sư') }}">
        <span class="mascot-char">帥</span>
        @auth
            @if(!auth()->user()->isVip())
                <span class="mascot-badge" id="coach-badge">2</span>
            @endif
        @endauth
    </div>
</div>

<script>
window.COACH_CONFIG = {
    apiUrl: '{{ url('/api/chess/coach') }}',
    csrf: '{{ csrf_token() }}',
    locale: '{{ app()->getLocale() }}',
    isAuth: {{ auth()->check() ? 'true' : 'false' }},
    isVip: {{ (auth()->check() && auth()->user()->isVip()) ? 'true' : 'false' }},
    loginUrl: '{{ localized_url("login") }}?redirect=' + encodeURIComponent(window.location.href),
    vipUrl: '{{ localized_url("app.vip") ?? "/vip" }}',
    remaining: {{ auth()->check() && !auth()->user()->isVip() ? max(0, 2 - (int) \Illuminate\Support\Facades\Cache::get('ai_coach_usage_' . auth()->id() . '_' . date('Ymd'), 0)) : -1 }},
    i18n: {
        greeting:  @json(__('Chào bạn! Bấm vào các nút bên cạnh để tôi phân tích thế cờ nhé.')),
        thinking:  @json(__('Để tôi xem...')),
        loginFirst: @json(__('Bạn cần đăng nhập để dùng Kỳ Sư AI.')),
        upgradeVip: @json(__('Bạn đã dùng hết lượt miễn phí hôm nay. Nâng cấp VIP để dùng không giới hạn!')),
        errNet:    @json(__('Kết nối hơi trục trặc, thử lại nhé.')),
        hint:      @json(__('Gợi ý')),
        threats:   @json(__('Đe dọa')),
        eval:      @json(__('Đánh giá')),
        login:      @json(__('Đăng nhập')),
        upgrade:    @json(__('Nâng cấp VIP')),
        noBoard:    @json(__('Chưa tải được bàn cờ.')),
        askThreats: @json(__('Có đe dọa gì?')),
        askHint:    @json(__('Đi nước nào?')),
        evalBtn:    @json(__('Đánh giá thế cờ')),
    }
};
</script>
<script src="{{ asset('js/coachMode.js') }}?v=1"></script>
