<div class="theme-selector-wrapper mt-2">
    <div class="theme-selector-panel">
        <div class="theme-section">
            <h6 class="theme-title">
                <i class="fas fa-chess-board"></i> {{ __("Màu bàn cờ") }}
            </h6>
            <div class="theme-options board-themes">
                <button class="theme-option" data-theme-type="board" data-theme="xiangqi-board" title="{{ __("Bàn cờ mặc định") }}">
                    <div class="theme-preview board-preview">
                        <img src="{{ asset('img/xiangqiboards/xiangqi-board.svg') }}" alt="{{ __("Bàn cờ mặc định") }}" onerror="this.style.display='none'; this.parentElement.classList.add('fallback-board-default')" />
                    </div>
                </button>
                <button class="theme-option" data-theme-type="board" data-theme="ban-co-go" title="{{ __("Gỗ nhạt") }}">
                    <div class="theme-preview board-preview">
                        <img src="{{ asset('img/xiangqiboards/ban-co-go.svg') }}" alt="{{ __("Gỗ nhạt") }}" onerror="this.style.display='none'; this.parentElement.classList.add('fallback-board-wood-light')" />
                    </div>
                </button>
                <button class="theme-option" data-theme-type="board" data-theme="wood-board" title="{{ __("Gỗ đậm") }}">
                    <div class="theme-preview board-preview">
                        <img src="{{ asset('img/xiangqiboards/wood-board.svg') }}" alt="{{ __("Gỗ đậm") }}" onerror="this.style.display='none'; this.parentElement.classList.add('fallback-board-wood-dark')" />
                    </div>
                </button>
                <button class="theme-option" data-theme-type="board" data-theme="banco" title="{{ __("Sáng") }}">
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
                <button class="theme-option" data-theme-type="pieces" data-theme="wiki" title="{{ __("Quân cờ mặc định") }}">
                    <div class="theme-preview piece-preview">
                        <img src="{{ asset('img/xiangqipieces/wiki/rK.svg') }}" alt="{{ __("Quân cờ mặc định") }}" />
                    </div>
                </button>
                <button class="theme-option" data-theme-type="pieces" data-theme="tung" title="{{ __("Đặc biệt") }}">
                    <div class="theme-preview piece-preview">
                        <img src="{{ asset('img/xiangqipieces/tung/rK.svg') }}" alt="{{ __("Đặc biệt") }}" />
                    </div>
                </button>
                <button class="theme-option" data-theme-type="pieces" data-theme="do-den" title="{{ __("Đỏ đen") }}">
                    <div class="theme-preview piece-preview">
                        <img src="{{ asset('img/xiangqipieces/do-den/rK.svg') }}" alt="{{ __("Đỏ đen") }}" />
                    </div>
                </button>
                <button class="theme-option" data-theme-type="pieces" data-theme="graphic" title="{{ __("Phương Tây") }}">
                    <div class="theme-preview piece-preview">
                        <img src="{{ asset('img/xiangqipieces/graphic/rK.svg') }}" alt="{{ __("Phương Tây") }}" />
                    </div>
                </button>
                <button class="theme-option" data-theme-type="pieces" data-theme="co" title="{{ __("Cam") }}">
                    <div class="theme-preview piece-preview">
                        <img src="{{ asset('img/xiangqipieces/co/rK.svg') }}" alt="{{ __("Cam") }}" />
                    </div>
                </button>
                <button class="theme-option" data-theme-type="pieces" data-theme="wikimedia" title="{{ __("Vàng đậm") }}">
                    <div class="theme-preview piece-preview">
                        <img src="{{ asset('img/xiangqipieces/wikimedia/rK.svg') }}" alt="{{ __("Vàng đậm") }}" />
                    </div>
                </button>
                <button class="theme-option" data-theme-type="pieces" data-theme="quan" title="{{ __("Sáng") }}">
                    <div class="theme-preview piece-preview">
                        <img src="{{ asset('img/xiangqipieces/quan/rK.svg') }}" alt="{{ __("Sáng") }}" />
                    </div>
                </button>
                <button class="theme-option" data-theme-type="pieces" data-theme="traditional" title="{{ __("Truyền thống") }}">
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

    <button class="theme-toggle-btn btn btn-dark" id="theme-toggle-btn">
        <i class="fas fa-palette"></i>
        <span class="theme-toggle-text">{{ __("Tùy chỉnh") }}</span>
    </button>
</div>

<style>
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

    /* Glassmorphism Panel */
    background: rgba(18, 20, 24, 0.85);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(212, 175, 55, 0.2);
    border-radius: 6px;
    padding: 20px;
    display: none;
    min-width: 340px;
    max-width: 420px;

    /* Deep cinematic shadow */
    box-shadow:
        0 20px 50px rgba(0, 0, 0, 0.9),
        0 0 30px rgba(212, 175, 55, 0.05);
    z-index: 1000;
    pointer-events: all;
}

.theme-selector-panel.show {
    display: block;
    animation: floatUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes floatUp {
    from { opacity: 0; transform: translate(-50%, 20px) scale(0.95); }
    to { opacity: 1; transform: translate(-50%, 0) scale(1); }
}

/* Titles */
.theme-title {
    color: #fff2cc;
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    text-shadow: 0 0 10px rgba(212, 175, 55, 0.3);
}

/* ==========================================================================
   THE THEME TOGGLE BUTTON (.theme-toggle-btn)
   ========================================================================== */
.theme-toggle-btn {
    background: linear-gradient(145deg, #252a36, #121418);
    color: var(--royal-gold-light, #fff2cc);
    border: 1px solid rgba(212, 175, 55, 0.4);
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

    box-shadow:
        0 8px 20px rgba(0, 0, 0, 0.6),
        0 0 15px rgba(212, 175, 55, 0.15);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.theme-toggle-btn:hover {
    background: linear-gradient(145deg, #2c3240, #1a1c23);
    border-color: rgba(212, 175, 55, 0.9);
    color: #fff;
    transform: translateY(-4px) scale(1.02);
    box-shadow:
        0 12px 25px rgba(0, 0, 0, 0.8),
        0 0 25px rgba(212, 175, 55, 0.4);
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
    background: rgba(255, 255, 255, 0.03);
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-radius: 6px;
    cursor: pointer;
    position: relative;
    padding: 5px;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.theme-option:hover {
    border-color: rgba(212, 175, 55, 0.5);
    background: rgba(212, 175, 55, 0.05);
    transform: translateY(-4px) scale(1.1);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.6);
}

.theme-option.active {
    border-color: #ffd700;
    background: rgba(212, 175, 55, 0.15);
    transform: scale(1.05);
    box-shadow:
        inset 0 0 15px rgba(212, 175, 55, 0.4),
        0 0 20px rgba(212, 175, 55, 0.6);
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
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.8));
    transition: transform 0.3s ease;
}

.theme-option:hover img {
    transform: scale(1.15);
}

/* ==========================================================================
   THE APPLY BUTTON (#apply-theme-btn)
   ========================================================================== */
#apply-theme-btn {
    background: linear-gradient(to right, #8a1515, #e63946);
    color: #fff;
    border: 1px solid #ff5252;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    border-radius: 6px;
    padding: 10px 30px;
    box-shadow: 0 4px 15px rgba(230, 57, 70, 0.4);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

#apply-theme-btn:hover {
    background: linear-gradient(to right, #e63946, #ff5252);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(230, 57, 70, 0.6), 0 0 15px rgba(255, 255, 255, 0.2);
}

#apply-theme-btn:active {
    transform: translateY(1px);
    box-shadow: 0 2px 5px rgba(230, 57, 70, 0.4);
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('theme-toggle-btn');
    const panel = document.querySelector('.theme-selector-panel');
    const themeOptions = document.querySelectorAll('.theme-option');

    // Toggle panel visibility
    toggleBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        panel.classList.toggle('show');
    });

    // Close panel when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.theme-selector-wrapper')) {
            panel.classList.remove('show');
        }
    });

    // 1. Initialize preferences based on login state
    let currentBoardTheme = 'xiangqi-board';
    let currentPiecesTheme = 'wiki';

    @if(auth()->check())
        // Logged-in user: Pull existing preferences directly from the user's database record
        // Use ?: to fallback safely if the value is an empty string
        currentBoardTheme = '{{ auth()->user()->board_theme ?: "xiangqi-board" }}';
        currentPiecesTheme = '{{ auth()->user()->pieces_theme ?: "wiki" }}';
    @else
        // Guest user: Pull from LocalStorage
        currentBoardTheme = localStorage.getItem('guest_board_theme') || 'xiangqi-board';
        currentPiecesTheme = localStorage.getItem('guest_pieces_theme') || 'wiki';
    @endif

    // Sync initialization to hidden inputs if they exist elsewhere on the page
    const boardInput = document.getElementById('boardTheme');
    const piecesInput = document.getElementById('piecesTheme');
    if (boardInput) boardInput.value = currentBoardTheme;
    if (piecesInput) piecesInput.value = currentPiecesTheme;

    // 2. Set active theme visual state on page load
    updateActiveThemes(currentBoardTheme, currentPiecesTheme);

    // Handle theme selection (update active state and button style)
    themeOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Update active state for visual feedback
            handleThemeClick(this);

            // Highlight apply button to indicate confirmation is needed
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
        applyBtn.addEventListener('click', function() {
            // Get currently selected themes from the DOM (active classes)
            const selectedBoardTheme = document.querySelector('.theme-option[data-theme-type="board"].active')?.dataset.theme || 'xiangqi-board';
            const selectedPiecesTheme = document.querySelector('.theme-option[data-theme-type="pieces"].active')?.dataset.theme || 'wiki';

            // Update hidden inputs if they exist
            if (boardInput) boardInput.value = selectedBoardTheme;
            if (piecesInput) piecesInput.value = selectedPiecesTheme;

            // Show loading state on button
            this.classList.remove('btn-danger', 'btn-primary', 'btn-warning');
            this.classList.add('btn-info');
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + '{{ __("Đang tải lại...") }}';
            this.disabled = true;

            // Logic: Save preference then Reload Page
            @if(auth()->check())
                // AUTH USER: Save to DB via AJAX
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('current_id', '{{ auth()->user()->id }}');
                formData.append('board_theme', selectedBoardTheme);
                formData.append('pieces_theme', selectedPiecesTheme);

                // FIXED: Using localized_url for localized routing setup
                fetch('{{ localized_url('change.ui') }}', {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    if (response.ok) {
                        // Success -> Reload page
                        location.reload();
                    } else {
                        throw new Error('Server returned error');
                    }
                }).catch(error => {
                    console.error('Save error:', error);
                    this.innerHTML = '<i class="fas fa-times"></i> ' + '{{ __("Lỗi kết nối!") }}';
                    this.classList.remove('btn-info');
                    this.classList.add('btn-danger');
                    this.disabled = false;
                });

            @else
                // GUEST USER: Save to LocalStorage -> Reload
                localStorage.setItem('guest_board_theme', selectedBoardTheme);
                localStorage.setItem('guest_pieces_theme', selectedPiecesTheme);

                // Slight delay just to let the user see the button click, then reload
                setTimeout(() => {
                    location.reload();
                }, 100);
            @endif
        });
    }

    // Helper to sync visual active states
    function updateActiveThemes(boardTheme, piecesTheme) {
        themeOptions.forEach(option => {
            const themeType = option.dataset.themeType;
            const themeName = option.dataset.theme;

            // Remove all active states first
            option.classList.remove('active');

            // Set active based on initialized values
            if ((themeType === 'board' && themeName === boardTheme) ||
                (themeType === 'pieces' && themeName === piecesTheme)) {
                option.classList.add('active');
            }
        });
    }

    // Handle theme option clicks to toggle visual active state
    function handleThemeClick(clickedOption) {
        const themeType = clickedOption.dataset.themeType;

        // Remove active from all options of this specific type
        themeOptions.forEach(option => {
            if (option.dataset.themeType === themeType) {
                option.classList.remove('active');
            }
        });

        // Add active to the clicked option
        clickedOption.classList.add('active');
    }

});
</script>
