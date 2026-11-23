<div class="theme-selector-wrapper mt-2">
    <div class="theme-selector-panel" id="theme-selector-panel" role="dialog" aria-label="{{ __("Đổi giao diện") }}">
        {{-- Appearance: dark / light. Shares the "xq-theme" preference with the header switch. --}}
        <div class="theme-section">
            <h6 class="theme-title">
                <i class="fas fa-adjust"></i> {{ __("Đổi giao diện") }}
            </h6>
            <div class="ts-mode" role="radiogroup" aria-label="{{ __("Đổi giao diện") }}">
                <span class="ts-mode-thumb" aria-hidden="true"></span>
                <button type="button" class="ts-mode-btn" data-mode="light" role="radio" aria-checked="false"
                        title="{{ __("Chuyển sang giao diện sáng") }}">
                    <i class="fas fa-sun" aria-hidden="true"></i> <span>{{ __("Sáng") }}</span>
                </button>
                <button type="button" class="ts-mode-btn" data-mode="dark" role="radio" aria-checked="true"
                        title="{{ __("Chuyển sang giao diện tối") }}">
                    <i class="fas fa-moon" aria-hidden="true"></i> <span>{{ __("Tối") }}</span>
                </button>
            </div>
        </div>

        <div class="theme-section">
            <h6 class="theme-title">
                <i class="fas fa-chess-board"></i> {{ __("Màu bàn cờ") }}
            </h6>
            <div class="theme-options board-themes">
                <button type="button" class="theme-option" aria-pressed="false" data-theme-type="board" data-theme="xiangqi-board" title="{{ __("Bàn cờ mặc định") }}">
                    <div class="theme-preview board-preview">
                        <img src="{{ asset('img/xiangqiboards/xiangqi-board.svg') }}" alt="{{ __("Bàn cờ mặc định") }}" onerror="this.style.display='none'; this.parentElement.classList.add('fallback-board-default')" />
                    </div>
                </button>
                <button type="button" class="theme-option" aria-pressed="false" data-theme-type="board" data-theme="ban-co-go" title="{{ __("Gỗ nhạt") }}">
                    <div class="theme-preview board-preview">
                        <img src="{{ asset('img/xiangqiboards/ban-co-go.svg') }}" alt="{{ __("Gỗ nhạt") }}" onerror="this.style.display='none'; this.parentElement.classList.add('fallback-board-wood-light')" />
                    </div>
                </button>
                <button type="button" class="theme-option" aria-pressed="false" data-theme-type="board" data-theme="wood-board" title="{{ __("Gỗ đậm") }}">
                    <div class="theme-preview board-preview">
                        <img src="{{ asset('img/xiangqiboards/wood-board.svg') }}" alt="{{ __("Gỗ đậm") }}" onerror="this.style.display='none'; this.parentElement.classList.add('fallback-board-wood-dark')" />
                    </div>
                </button>
                <button type="button" class="theme-option" aria-pressed="false" data-theme-type="board" data-theme="banco" title="{{ __("Sáng") }}">
                    <div class="theme-preview board-preview">
                        <img src="{{ asset('img/xiangqiboards/banco.svg') }}" alt="{{ __("Sáng") }}" onerror="this.style.display='none'; this.parentElement.classList.add('fallback-board-light')" />
                    </div>
                </button>
            </div>
        </div>

        <div class="theme-section">
            <h6 class="theme-title">
                <i class="fas fa-chess-knight"></i> {{ __("Kiểu quân cờ") }}
            </h6>
            <div class="theme-options piece-themes">
                <button type="button" class="theme-option" aria-pressed="false" data-theme-type="pieces" data-theme="wiki" title="{{ __("Quân cờ mặc định") }}">
                    <div class="theme-preview piece-preview">
                        <img src="{{ asset('img/xiangqipieces/wiki/rK.svg') }}" alt="{{ __("Quân cờ mặc định") }}" />
                    </div>
                </button>
                <button type="button" class="theme-option" aria-pressed="false" data-theme-type="pieces" data-theme="tung" title="{{ __("Đặc biệt") }}">
                    <div class="theme-preview piece-preview">
                        <img src="{{ asset('img/xiangqipieces/tung/rK.svg') }}" alt="{{ __("Đặc biệt") }}" />
                    </div>
                </button>
                <button type="button" class="theme-option" aria-pressed="false" data-theme-type="pieces" data-theme="do-den" title="{{ __("Đỏ đen") }}">
                    <div class="theme-preview piece-preview">
                        <img src="{{ asset('img/xiangqipieces/do-den/rK.svg') }}" alt="{{ __("Đỏ đen") }}" />
                    </div>
                </button>
                <button type="button" class="theme-option" aria-pressed="false" data-theme-type="pieces" data-theme="graphic" title="{{ __("Phương Tây") }}">
                    <div class="theme-preview piece-preview">
                        <img src="{{ asset('img/xiangqipieces/graphic/rK.svg') }}" alt="{{ __("Phương Tây") }}" />
                    </div>
                </button>
                <button type="button" class="theme-option" aria-pressed="false" data-theme-type="pieces" data-theme="co" title="{{ __("Cam") }}">
                    <div class="theme-preview piece-preview">
                        <img src="{{ asset('img/xiangqipieces/co/rK.svg') }}" alt="{{ __("Cam") }}" />
                    </div>
                </button>
                <button type="button" class="theme-option" aria-pressed="false" data-theme-type="pieces" data-theme="wikimedia" title="{{ __("Vàng đậm") }}">
                    <div class="theme-preview piece-preview">
                        <img src="{{ asset('img/xiangqipieces/wikimedia/rK.svg') }}" alt="{{ __("Vàng đậm") }}" />
                    </div>
                </button>
                <button type="button" class="theme-option" aria-pressed="false" data-theme-type="pieces" data-theme="quan" title="{{ __("Sáng") }}">
                    <div class="theme-preview piece-preview">
                        <img src="{{ asset('img/xiangqipieces/quan/rK.svg') }}" alt="{{ __("Sáng") }}" />
                    </div>
                </button>
                <button type="button" class="theme-option" aria-pressed="false" data-theme-type="pieces" data-theme="traditional" title="{{ __("Truyền thống") }}">
                    <div class="theme-preview piece-preview">
                        <img src="{{ asset('img/xiangqipieces/traditional/rK.svg') }}" alt="{{ __("Truyền thống") }}" />
                    </div>
                </button>
            </div>
        </div>

        <div class="theme-section mt-3">
            <div class="text-center">
                <button type="button" class="btn btn-danger btn-sm px-4" id="apply-theme-btn">
                    <i class="fas fa-check"></i> {{ __("Áp dụng Theme") }}
                </button>
            </div>
        </div>
    </div>

    <button type="button" class="theme-toggle-btn btn btn-dark" id="theme-toggle-btn"
            aria-haspopup="dialog" aria-expanded="false" aria-controls="theme-selector-panel">
        <i class="fas fa-palette"></i>
        <span class="theme-toggle-text">{{ __("Tùy chỉnh") }}</span>
    </button>
    @auth
        <form id="theme-apply-form" method="POST" action="{{ localized_url('change.ui') }}" style="display: none;">
            @csrf
            <input type="hidden" name="board_theme" id="boardTheme" value="{{ auth()->user()->board_theme ?: 'xiangqi-board' }}">
            <input type="hidden" name="pieces_theme" id="piecesTheme" value="{{ auth()->user()->pieces_theme ?: 'wiki' }}">
        </form>
    @endauth
</div>

<style>
/* ==========================================================================
   THEME SELECTOR — design tokens
   Dark values are the original look. The light palette mirrors theme-light.css
   ("Quán trà buổi sáng": cream paper, ink text, terracotta + honey accents) and
   follows <html data-theme="light|dark">, which the header switch and the
   Sáng / Tối control inside this panel both drive.
   ========================================================================== */
.theme-selector-wrapper {
    --ts-panel-bg: rgba(18, 20, 24, 0.85);
    --ts-panel-border: 1px solid rgba(212, 175, 55, 0.2);
    --ts-panel-shadow: 0 20px 50px rgba(0, 0, 0, 0.9), 0 0 30px rgba(212, 175, 55, 0.05);
    --ts-title: #fff2cc;
    --ts-title-shadow: 0 0 10px rgba(212, 175, 55, 0.3);

    --ts-btn-bg: linear-gradient(145deg, #252a36, #121418);
    --ts-btn-bg-hover: linear-gradient(145deg, #2c3240, #1a1c23);
    --ts-btn-text: #fff2cc;
    --ts-btn-text-hover: #fff;
    --ts-btn-border: rgba(212, 175, 55, 0.4);
    --ts-btn-border-hover: rgba(212, 175, 55, 0.9);
    --ts-btn-shadow: 0 8px 20px rgba(0, 0, 0, 0.6), 0 0 15px rgba(212, 175, 55, 0.15);
    --ts-btn-shadow-hover: 0 12px 25px rgba(0, 0, 0, 0.8), 0 0 25px rgba(212, 175, 55, 0.4);

    --ts-opt-bg: rgba(255, 255, 255, 0.03);
    --ts-opt-border: rgba(255, 255, 255, 0.1);
    --ts-opt-hover-bg: rgba(212, 175, 55, 0.05);
    --ts-opt-hover-border: rgba(212, 175, 55, 0.5);
    --ts-opt-hover-shadow: 0 10px 20px rgba(0, 0, 0, 0.6);
    --ts-opt-active-bg: rgba(212, 175, 55, 0.15);
    --ts-opt-active-border: #ffd700;
    --ts-opt-active-shadow: inset 0 0 15px rgba(212, 175, 55, 0.4), 0 0 20px rgba(212, 175, 55, 0.6);
    --ts-check-bg: #ffd700;
    --ts-check-tick: #2a1a05;
    --ts-img-shadow: rgba(0, 0, 0, 0.8);
    --ts-focus: #ffd700;

    --ts-seg-bg: rgba(255, 255, 255, 0.05);
    --ts-seg-border: rgba(212, 175, 55, 0.25);
    --ts-seg-text: rgba(255, 242, 204, 0.72);
    --ts-seg-text-hover: #fff2cc;
    --ts-thumb-bg: linear-gradient(145deg, #ffd98a, #d4af37);
    --ts-thumb-text: #2a1a05;
    --ts-thumb-shadow: 0 2px 8px rgba(212, 175, 55, 0.45);

    --ts-apply-bg: linear-gradient(to right, #8a1515, #e63946);
    --ts-apply-bg-hover: linear-gradient(to right, #e63946, #ff5252);
    --ts-apply-border: #ff5252;
    --ts-apply-shadow: 0 4px 15px rgba(230, 57, 70, 0.4);
    --ts-apply-shadow-hover: 0 6px 20px rgba(230, 57, 70, 0.6), 0 0 15px rgba(255, 255, 255, 0.2);
    --ts-apply-shadow-active: 0 2px 5px rgba(230, 57, 70, 0.4);
}

:root[data-theme="light"] .theme-selector-wrapper {
    --ts-panel-bg: linear-gradient(180deg, rgba(255, 250, 238, 0.97), rgba(247, 231, 198, 0.97));
    --ts-panel-border: 1px solid rgba(138, 74, 36, 0.3);
    --ts-panel-shadow: 0 20px 44px rgba(107, 58, 34, 0.32), 0 0 28px rgba(255, 190, 90, 0.2);
    --ts-title: #8a2f1d;
    --ts-title-shadow: none;

    --ts-btn-bg: linear-gradient(145deg, #d9674c, #b4472f);
    --ts-btn-bg-hover: linear-gradient(145deg, #e0765a, #c4513a);
    --ts-btn-text: #fff6e4;
    --ts-btn-text-hover: #fff;
    --ts-btn-border: rgba(255, 200, 97, 0.75);
    --ts-btn-border-hover: #ffc861;
    --ts-btn-shadow: 0 8px 20px rgba(107, 58, 34, 0.35), 0 0 15px rgba(255, 190, 90, 0.3);
    --ts-btn-shadow-hover: 0 12px 25px rgba(107, 58, 34, 0.45), 0 0 25px rgba(255, 190, 90, 0.5);

    --ts-opt-bg: rgba(255, 253, 246, 0.7);
    --ts-opt-border: rgba(138, 74, 36, 0.25);
    --ts-opt-hover-bg: rgba(196, 81, 58, 0.08);
    --ts-opt-hover-border: #c4513a;
    --ts-opt-hover-shadow: 0 10px 20px rgba(107, 58, 34, 0.25);
    --ts-opt-active-bg: rgba(255, 200, 97, 0.35);
    --ts-opt-active-border: #c4513a;
    --ts-opt-active-shadow: inset 0 0 12px rgba(255, 190, 90, 0.45), 0 0 0 3px rgba(196, 81, 58, 0.25), 0 6px 16px rgba(107, 58, 34, 0.3);
    --ts-check-bg: #c4513a;
    --ts-check-tick: #fff6e4;
    --ts-img-shadow: rgba(107, 58, 34, 0.35);
    --ts-focus: #a8402c;

    --ts-seg-bg: rgba(138, 74, 36, 0.1);
    --ts-seg-border: rgba(138, 74, 36, 0.28);
    --ts-seg-text: #5a2f1b;
    --ts-seg-text-hover: #8a2f1d;
    --ts-thumb-bg: linear-gradient(145deg, #d9674c, #b4472f);
    --ts-thumb-text: #fff6e4;
    --ts-thumb-shadow: 0 2px 8px rgba(180, 71, 47, 0.45);

    --ts-apply-bg: linear-gradient(to right, #b4472f, #d9674c);
    --ts-apply-bg-hover: linear-gradient(to right, #c4513a, #e0765a);
    --ts-apply-border: #8a2f1d;
    --ts-apply-shadow: 0 4px 15px rgba(180, 71, 47, 0.4);
    --ts-apply-shadow-hover: 0 6px 20px rgba(180, 71, 47, 0.55), 0 0 15px rgba(255, 200, 97, 0.35);
    --ts-apply-shadow-active: 0 2px 5px rgba(180, 71, 47, 0.4);
}

/* ==========================================================================
   THEME SELECTOR WRAPPER & PANEL
   ========================================================================== */
.theme-selector-wrapper {
    position: relative;
    display: block;
    width: 100%;
    max-width: 520px;
    margin: 0 auto;
    text-align: center;
    z-index: 10;
    pointer-events: none;
}

.theme-selector-panel {
    position: absolute;
    bottom: 60px;
    left: 50%;
    transform: translateX(-50%);

    /* Glassmorphism panel (lacquer-dark by default, cream paper in light mode) */
    background: var(--ts-panel-bg);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: var(--ts-panel-border);
    border-radius: 6px;
    padding: 20px;
    display: none;
    min-width: 340px;
    max-width: 420px;
    max-height: min(75vh, 620px);
    overflow-y: auto;

    box-shadow: var(--ts-panel-shadow);
    z-index: 1000;
    pointer-events: all;
}

:root[data-theme="light"] .theme-selector-panel {
    border-top: 2px solid #c4513a;
}

.theme-selector-panel.show {
    display: block;
    animation: floatUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.theme-selector-panel .theme-section + .theme-section {
    margin-top: 16px;
}

@keyframes floatUp {
    from { opacity: 0; transform: translate(-50%, 20px) scale(0.95); }
    to { opacity: 1; transform: translate(-50%, 0) scale(1); }
}

/* Titles */
.theme-title {
    color: var(--ts-title);
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    text-shadow: var(--ts-title-shadow);
}

/* ==========================================================================
   APPEARANCE SWITCH (Sáng / Tối)
   Visuals follow <html data-theme> directly, so the control is correct before
   any script runs; JS only maintains aria-checked and handles input.
   ========================================================================== */
.ts-mode {
    position: relative;
    display: grid;
    grid-template-columns: 1fr 1fr;
    padding: 3px;
    background: var(--ts-seg-bg);
    border: 1px solid var(--ts-seg-border);
    border-radius: 8px;
    isolation: isolate;
}

.ts-mode-thumb {
    position: absolute;
    top: 3px;
    bottom: 3px;
    left: 3px;
    width: calc(50% - 3px);
    border-radius: 6px;
    background: var(--ts-thumb-bg);
    box-shadow: var(--ts-thumb-shadow);
    transform: translateX(100%); /* dark = right */
    transition: transform 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    z-index: -1;
}
:root[data-theme="light"] .ts-mode-thumb {
    transform: none; /* light = left */
}

.ts-mode-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 9px 12px;
    border: 0;
    border-radius: 6px;
    background: transparent;
    color: var(--ts-seg-text);
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    cursor: pointer;
    transition: color 0.25s ease;
    -webkit-tap-highlight-color: transparent;
}
.ts-mode-btn:hover {
    color: var(--ts-seg-text-hover);
}
:root[data-theme="light"] .ts-mode-btn[data-mode="light"],
:root:not([data-theme="light"]) .ts-mode-btn[data-mode="dark"] {
    color: var(--ts-thumb-text);
}
.ts-mode-btn:focus-visible,
.theme-option:focus-visible,
.theme-toggle-btn:focus-visible,
#apply-theme-btn:focus-visible {
    outline: 2px solid var(--ts-focus);
    outline-offset: 2px;
}

/* ==========================================================================
   THE THEME TOGGLE BUTTON (.theme-toggle-btn)
   ========================================================================== */
.theme-toggle-btn {
    background: var(--ts-btn-bg);
    color: var(--ts-btn-text);
    border: 1px solid var(--ts-btn-border);
    border-radius: 6px;
    padding: 10px 24px;
    font-size: 15px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin: 0 auto;
    pointer-events: all;

    box-shadow: var(--ts-btn-shadow);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.theme-toggle-btn:hover,
.theme-toggle-btn:focus-visible {
    background: var(--ts-btn-bg-hover);
    border-color: var(--ts-btn-border-hover);
    color: var(--ts-btn-text-hover);
    transform: translateY(-4px) scale(1.02);
    box-shadow: var(--ts-btn-shadow-hover);
}

/* ==========================================================================
   THE THEME THUMBNAILS (.theme-option)
   ========================================================================== */
.theme-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(65px, 1fr));
    gap: 12px;
    justify-items: center;
}

.theme-option {
    width: 65px;
    height: 65px;
    background: var(--ts-opt-bg);
    border: 2px solid var(--ts-opt-border);
    border-radius: 6px;
    cursor: pointer;
    position: relative;
    padding: 5px;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.theme-option:hover {
    border-color: var(--ts-opt-hover-border);
    background: var(--ts-opt-hover-bg);
    transform: translateY(-4px) scale(1.1);
    box-shadow: var(--ts-opt-hover-shadow);
}

.theme-option.active {
    border-color: var(--ts-opt-active-border);
    background: var(--ts-opt-active-bg);
    transform: scale(1.05);
    box-shadow: var(--ts-opt-active-shadow);
}

/* Selected badge: a CSS-drawn tick, so the state never relies on colour alone. */
.theme-option.active::after {
    content: "";
    position: absolute;
    top: -6px;
    right: -6px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: var(--ts-check-bg);
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.35);
}
.theme-option.active::before {
    content: "";
    position: absolute;
    top: -1px;
    right: 1px;
    width: 4px;
    height: 8px;
    border: solid var(--ts-check-tick);
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
    z-index: 1;
}

.theme-preview {
    width: 100%;
    height: 100%;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.board-preview img, .piece-preview img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 2px 4px var(--ts-img-shadow));
    transition: transform 0.3s ease;
}

.theme-option:hover img {
    transform: scale(1.15);
}

/* ==========================================================================
   THE APPLY BUTTON (#apply-theme-btn)
   ========================================================================== */
#apply-theme-btn {
    background: var(--ts-apply-bg);
    color: #fff;
    border: 1px solid var(--ts-apply-border);
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    border-radius: 6px;
    padding: 10px 30px;
    box-shadow: var(--ts-apply-shadow);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

#apply-theme-btn:hover {
    background: var(--ts-apply-bg-hover);
    transform: translateY(-2px);
    box-shadow: var(--ts-apply-shadow-hover);
}

#apply-theme-btn:active {
    transform: translateY(1px);
    box-shadow: var(--ts-apply-shadow-active);
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .theme-selector-panel {
        min-width: 300px;
        padding: 15px;
        bottom: 55px;
    }
    .theme-option {
        width: 55px;
        height: 55px;
        padding: 4px;
    }
    .theme-toggle-btn {
        padding: 8px 18px;
        font-size: 13px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .theme-selector-panel.show { animation: none; }
    .ts-mode-thumb,
    .theme-option,
    .theme-toggle-btn,
    #apply-theme-btn { transition: none; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('theme-toggle-btn');
    const panel = document.querySelector('.theme-selector-panel');
    const themeOptions = document.querySelectorAll('.theme-option');
    const boardInput = document.getElementById('boardTheme');
    const piecesInput = document.getElementById('piecesTheme');

    // 1. Establish user context using Blade injection
    const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
    const dbBoardTheme = "{{ auth()->check() ? auth()->user()->board_theme : '' }}";
    const dbPiecesTheme = "{{ auth()->check() ? auth()->user()->pieces_theme : '' }}";

    // Panel visibility (keeps aria-expanded truthful for assistive tech)
    function setPanelOpen(open) {
        panel.classList.toggle('show', open);
        toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    toggleBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        setPanelOpen(!panel.classList.contains('show'));
    });

    // Close panel when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.theme-selector-wrapper')) {
            setPanelOpen(false);
        }
    });

    // Escape closes the panel and returns focus to its button
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && panel.classList.contains('show')) {
            setPanelOpen(false);
            toggleBtn.focus();
        }
    });

    // ---- Appearance (Sáng / Tối) -------------------------------------------
    // One preference, shared with the header switch: <html data-theme> + localStorage "xq-theme".
    // When the header switch is on the page we click it (single source of truth: it also animates,
    // updates <meta theme-color> and syncs other tabs). On layouts without a header we do the same ourselves.
    const MODE_KEY = 'xq-theme';
    const rootEl = document.documentElement;
    const modeGroup = document.querySelector('.ts-mode');
    const modeBtns = Array.from(document.querySelectorAll('.ts-mode-btn'));
    const MODE_COLORS = { dark: '#7a291c', light: '#b4472f' };

    const currentMode = () => rootEl.getAttribute('data-theme') === 'light' ? 'light' : 'dark';

    function syncMode() {
        const mode = currentMode();
        modeBtns.forEach(btn => {
            const on = btn.dataset.mode === mode;
            btn.setAttribute('aria-checked', on ? 'true' : 'false');
            btn.tabIndex = on ? 0 : -1; // roving tabindex for the radio group
        });
    }

    function setMode(mode) {
        if (mode !== 'light' && mode !== 'dark') return;
        if (mode === currentMode()) return;

        const headerSwitch = document.getElementById('czThemeToggle');
        if (headerSwitch) {
            headerSwitch.click(); // fires "themechange" -> syncMode()
            return;
        }

        rootEl.classList.add('theme-switching');
        window.setTimeout(() => rootEl.classList.remove('theme-switching'), 450);
        rootEl.setAttribute('data-theme', mode);
        const metaColor = document.querySelector('meta[name="theme-color"]');
        if (metaColor) metaColor.setAttribute('content', MODE_COLORS[mode]);
        try { localStorage.setItem(MODE_KEY, mode); } catch (err) {}
        document.dispatchEvent(new CustomEvent('themechange', { detail: { theme: mode } }));
    }

    modeBtns.forEach(btn => {
        btn.addEventListener('click', () => setMode(btn.dataset.mode));
    });

    if (modeGroup) {
        modeGroup.addEventListener('keydown', function(e) {
            const keys = ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'];
            if (!keys.includes(e.key)) return;
            e.preventDefault();
            const next = modeBtns.find(b => b.dataset.mode !== currentMode());
            if (next) { setMode(next.dataset.mode); next.focus(); }
        });
    }

    document.addEventListener('themechange', syncMode);
    // Layouts without the header switch still follow other tabs.
    window.addEventListener('storage', function(e) {
        if (e.key === MODE_KEY && (e.newValue === 'light' || e.newValue === 'dark') && !document.getElementById('czThemeToggle')) {
            rootEl.setAttribute('data-theme', e.newValue);
            syncMode();
        }
    });
    syncMode();

    // 2. Initialize preferences for BOTH logged-in users and guests
    let currentBoardTheme = 'xiangqi-board';
    let currentPiecesTheme = 'wiki';

    if (isAuthenticated && dbBoardTheme) {
        currentBoardTheme = dbBoardTheme;
        currentPiecesTheme = dbPiecesTheme || 'wiki';
    } else {
        currentBoardTheme = localStorage.getItem('guest_board_theme') || 'xiangqi-board';
        currentPiecesTheme = localStorage.getItem('guest_pieces_theme') || 'wiki';
    }

    // Sync initialization to the hidden form inputs (logged-in users only —
    // they don't exist in the DOM for guests, since there's nothing to POST)
    if (boardInput) boardInput.value = currentBoardTheme;
    if (piecesInput) piecesInput.value = currentPiecesTheme;

    // 3. Set active theme visual state on page load
    updateActiveThemes(currentBoardTheme, currentPiecesTheme);

    // Handle theme selection (update active state and button style)
    themeOptions.forEach(option => {
        option.addEventListener('click', function() {
            handleThemeClick(this);

            const applyBtn = document.getElementById('apply-theme-btn');
            if (applyBtn) {
                applyBtn.classList.remove('btn-danger');
                applyBtn.classList.add('btn-primary');
                applyBtn.innerHTML = '<i class="fas fa-circle"></i> ' + '{{ __("Áp dụng Theme") }}';
            }
        });
    });

    // Handle apply theme button click
    const applyBtn = document.getElementById('apply-theme-btn');
    if (applyBtn) {
        applyBtn.addEventListener('click', async function() {
            const selectedBoardTheme = document.querySelector('.theme-option[data-theme-type="board"].active')?.dataset.theme || 'xiangqi-board';
            const selectedPiecesTheme = document.querySelector('.theme-option[data-theme-type="pieces"].active')?.dataset.theme || 'wiki';

            // Always save to LocalStorage as a guest-mode fallback / cache
            localStorage.setItem('guest_board_theme', selectedBoardTheme);
            localStorage.setItem('guest_pieces_theme', selectedPiecesTheme);

            // Change only visual assets; keep the live board, game and timers intact.
            document.querySelectorAll('#boardTheme').forEach(input => input.value = selectedBoardTheme);
            document.querySelectorAll('#piecesTheme').forEach(input => input.value = selectedPiecesTheme);
            window.updateBoardTheme();
            this.classList.remove('btn-danger', 'btn-primary', 'btn-warning', 'btn-info');
            this.classList.add('btn-success');
            this.innerHTML = '<i class="fas fa-check"></i> ' + '{{ __("Đã áp dụng") }}';

            const themeForm = document.getElementById('theme-apply-form');
            if (isAuthenticated && themeForm) {
                this.disabled = true;
                try {
                    const response = await fetch(themeForm.action, {
                        method: 'POST',
                        body: new FormData(themeForm),
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!response.ok) throw new Error('Theme save failed');
                } catch (error) {
                    this.classList.remove('btn-success');
                    this.classList.add('btn-warning');
                    this.innerHTML = '{{ __("Đã áp dụng trên máy này; chưa lưu tài khoản") }}';
                } finally {
                    this.disabled = false;
                }
            }
        });
    }

    // Helper to sync visual active states
    function updateActiveThemes(boardTheme, piecesTheme) {
        themeOptions.forEach(option => {
            const themeType = option.dataset.themeType;
            const themeName = option.dataset.theme;

            option.classList.remove('active');

            if ((themeType === 'board' && themeName === boardTheme) ||
                (themeType === 'pieces' && themeName === piecesTheme)) {
                option.classList.add('active');
            }
            option.setAttribute('aria-pressed', option.classList.contains('active') ? 'true' : 'false');
        });
    }

    // Handle theme option clicks to toggle visual active state
    function handleThemeClick(clickedOption) {
        const themeType = clickedOption.dataset.themeType;

        themeOptions.forEach(option => {
            if (option.dataset.themeType === themeType) {
                option.classList.remove('active');
                option.setAttribute('aria-pressed', 'false');
            }
        });

        clickedOption.classList.add('active');
        clickedOption.setAttribute('aria-pressed', 'true');
    }
});
</script>
