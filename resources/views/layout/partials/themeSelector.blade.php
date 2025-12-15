<!-- Theme Selector -->
<div class="theme-selector-wrapper mt-2">
  <div class="theme-selector-panel">
    <div class="theme-section">
      <h6 class="theme-title">
        <i class="fas fa-chess-board"></i> Màu bàn cờ
      </h6>
      <div class="theme-options board-themes">
        <button class="theme-option" data-theme-type="board" data-theme="xiangqi-board" title="Bàn cờ mặc định">
          <div class="theme-preview board-preview">
            <img src="{{ url('/') }}/img/xiangqiboards/xiangqi-board.svg" alt="Bàn cờ mặc định" onerror="this.style.display='none'; this.parentElement.classList.add('fallback-board-default')" />
          </div>
        </button>
        <button class="theme-option" data-theme-type="board" data-theme="ban-co-go" title="Gỗ nhạt">
          <div class="theme-preview board-preview">
            <img src="{{ url('/') }}/img/xiangqiboards/ban-co-go.svg" alt="Gỗ nhạt" onerror="this.style.display='none'; this.parentElement.classList.add('fallback-board-wood-light')" />
          </div>
        </button>
        <button class="theme-option" data-theme-type="board" data-theme="wood-board" title="Gỗ đậm">
          <div class="theme-preview board-preview">
            <img src="{{ url('/') }}/img/xiangqiboards/wood-board.svg" alt="Gỗ đậm" onerror="this.style.display='none'; this.parentElement.classList.add('fallback-board-wood-dark')" />
          </div>
        </button>
        <button class="theme-option" data-theme-type="board" data-theme="banco" title="Sáng">
          <div class="theme-preview board-preview">
            <img src="{{ url('/') }}/img/xiangqiboards/banco.svg" alt="Sáng" onerror="this.style.display='none'; this.parentElement.classList.add('fallback-board-light')" />
          </div>
        </button>
      </div>
    </div>

    <div class="theme-section">
      <h6 class="theme-title">
        <i class="fas fa-chess-knight"></i> Kiểu quân cờ
      </h6>
      <div class="theme-options piece-themes">
        <button class="theme-option" data-theme-type="pieces" data-theme="wiki" title="Quân cờ mặc định">
          <div class="theme-preview piece-preview">
            <img src="{{ url('/') }}/img/xiangqipieces/wiki/rK.svg" alt="Quân cờ mặc định" />
          </div>
        </button>
        <button class="theme-option" data-theme-type="pieces" data-theme="tung" title="Đặc biệt">
          <div class="theme-preview piece-preview">
            <img src="{{ url('/') }}/img/xiangqipieces/tung/rK.svg" alt="Đặc biệt" />
          </div>
        </button>
        <button class="theme-option" data-theme-type="pieces" data-theme="do-den" title="Đỏ đen">
          <div class="theme-preview piece-preview">
            <img src="{{ url('/') }}/img/xiangqipieces/do-den/rK.svg" alt="Đỏ đen" />
          </div>
        </button>
        <button class="theme-option" data-theme-type="pieces" data-theme="graphic" title="Phương Tây">
          <div class="theme-preview piece-preview">
            <img src="{{ url('/') }}/img/xiangqipieces/graphic/rK.svg" alt="Phương Tây" />
          </div>
        </button>
        <button class="theme-option" data-theme-type="pieces" data-theme="co" title="Cam">
          <div class="theme-preview piece-preview">
            <img src="{{ url('/') }}/img/xiangqipieces/co/rK.svg" alt="Cam" />
          </div>
        </button>
        <button class="theme-option" data-theme-type="pieces" data-theme="wikimedia" title="Vàng đậm">
          <div class="theme-preview piece-preview">
            <img src="{{ url('/') }}/img/xiangqipieces/wikimedia/rK.svg" alt="Vàng đậm" />
          </div>
        </button>
        <button class="theme-option" data-theme-type="pieces" data-theme="quan" title="Sáng">
          <div class="theme-preview piece-preview">
            <img src="{{ url('/') }}/img/xiangqipieces/quan/rK.svg" alt="Sáng" />
          </div>
        </button>
        <button class="theme-option" data-theme-type="pieces" data-theme="traditional" title="Truyền thống">
          <div class="theme-preview piece-preview">
            <img src="{{ url('/') }}/img/xiangqipieces/traditional/rK.svg" alt="Truyền thống" />
          </div>
        </button>
      </div>
    </div>

    <div class="theme-section mt-3">
      <div class="text-center">
        <button type="button" class="btn btn-danger btn-sm px-4" id="apply-theme-btn">
          <i class="fas fa-check"></i> Áp dụng Theme
        </button>
      </div>
    </div>
  </div>

  <button class="theme-toggle-btn" id="theme-toggle-btn">
    <i class="fas fa-palette"></i>
    <span class="theme-toggle-text">Tùy chỉnh</span>
  </button>
</div>

<style>
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

.theme-toggle-btn {
  background: rgba(0, 0, 0, 0.8);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 20px;
  padding: 8px 16px;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin: 0 auto;
  flex-shrink: 0;
  pointer-events: all;
}

.theme-toggle-btn:hover {
  background: rgba(0, 0, 0, 0.9);
  border-color: rgba(255, 255, 255, 0.5);
  transform: translateY(-2px);
}

.theme-selector-panel {
  position: absolute;
  bottom: 45px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  padding: 16px;
  display: none;
  min-width: 320px;
  max-width: 400px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
  z-index: 1000;
  pointer-events: all;
}

.theme-selector-panel.show {
  display: block;
  animation: fadeInUp 0.3s ease;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateX(-50%) translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
  }
}

.theme-section {
  margin-bottom: 16px;
}

.theme-section:last-child {
  margin-bottom: 0;
}

.theme-title {
  color: #f8f9fa;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.theme-options {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(60px, 1fr));
  gap: 8px;
  justify-items: center;
}

.theme-option {
  width: 60px;
  height: 60px;
  border: 2px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  background: transparent;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  overflow: hidden;
  padding: 4px;
}

.theme-option:hover {
  border-color: rgba(255, 255, 255, 0.5);
  transform: scale(1.05);
}

.theme-option.active {
  border-color: #dc3545;
  box-shadow: 0 0 10px rgba(220, 53, 69, 0.5);
}

.theme-preview {
  width: 100%;
  height: 100%;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.board-preview img, .piece-preview img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  filter: drop-shadow(0 1px 2px rgba(0,0,0,0.3));
}

@media (max-width: 768px) {
  .theme-selector-panel {
    min-width: 280px;
    padding: 12px;
  }

  .theme-option {
    width: 50px;
    height: 50px;
    padding: 3px;
  }

  .theme-toggle-btn {
    padding: 6px 12px;
    font-size: 12px;
  }

  .theme-options {
    grid-template-columns: repeat(auto-fit, minmax(50px, 1fr));
    gap: 6px;
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

  // Load guest theme preferences first (if not logged in)
  @if (!auth()->check())
  const savedBoardTheme = localStorage.getItem('guest_board_theme');
  const savedPiecesTheme = localStorage.getItem('guest_pieces_theme');

  if (savedBoardTheme) {
    const boardInput = document.getElementById('boardTheme');
    if (boardInput) boardInput.value = savedBoardTheme;
  }

  if (savedPiecesTheme) {
    const piecesInput = document.getElementById('piecesTheme');
    if (piecesInput) piecesInput.value = savedPiecesTheme;
  }
  @endif

  // Set active theme on page load
  updateActiveThemes();

  // Handle theme selection (chỉ update preview, chưa apply)
  themeOptions.forEach(option => {
    option.addEventListener('click', function() {
      // Update active state for visual feedback
      handleThemeClick(this);

      // Highlight apply button để user biết cần confirm
      const applyBtn = document.getElementById('apply-theme-btn');
      if (applyBtn) {
        applyBtn.classList.add('btn-primary');
        applyBtn.classList.remove('btn-danger');
        applyBtn.innerHTML = '<i class="fas fa-circle"></i> Áp dụng Theme';
      }
    });
  });

  // Handle apply theme button
  const applyBtn = document.getElementById('apply-theme-btn');
  if (applyBtn) {
    applyBtn.addEventListener('click', function() {
      // Get selected themes
      const selectedBoardTheme = document.querySelector('.theme-option[data-theme-type="board"].active')?.dataset.theme || 'xiangqi-board';
      const selectedPiecesTheme = document.querySelector('.theme-option[data-theme-type="pieces"].active')?.dataset.theme || 'wiki';

      // Update hidden inputs
      const boardInput = document.getElementById('boardTheme');
      const piecesInput = document.getElementById('piecesTheme');

      if (boardInput) boardInput.value = selectedBoardTheme;
      if (piecesInput) piecesInput.value = selectedPiecesTheme;

      // Show loading state
      this.classList.remove('btn-warning');
      this.classList.add('btn-info');
      this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang áp dụng...';
      this.disabled = true;

      // Save to server (sẽ reload page như system cũ)
      @if(auth()->check())
      const formData = new FormData();
      formData.append('_token', '{{ csrf_token() }}');
      formData.append('current_id', '{{ auth()->user()->id }}');
      formData.append('board_theme', selectedBoardTheme);
      formData.append('pieces_theme', selectedPiecesTheme);

      fetch('{{ url('/doi-giao-dien') }}', {
        method: 'POST',
        body: formData
      }).then(response => {
        if (response.ok) {
          // Success - system cũ sẽ redirect, ta cũng reload để consistent
          this.classList.remove('btn-info');
          this.classList.add('btn-success');
          this.innerHTML = '<i class="fas fa-check"></i> Thành công!';

          setTimeout(() => {
            location.reload(); // Reload để apply theme như system cũ
          }, 800);
        } else {
          throw new Error('Save failed');
        }
      }).catch(error => {
        console.log('Save error:', error);
        this.classList.remove('btn-info');
        this.classList.add('btn-danger');
        this.innerHTML = '<i class="fas fa-times"></i> Lỗi!';
        this.disabled = false;

        setTimeout(() => {
          this.classList.remove('btn-danger');
          this.classList.add('btn-warning');
          this.innerHTML = '<i class="fas exclamation-triangle"></i> Áp dụng Theme';
        }, 2000);
      });
      @else
      // Guest user - save to localStorage và apply
      localStorage.setItem('guest_board_theme', selectedBoardTheme);
      localStorage.setItem('guest_pieces_theme', selectedPiecesTheme);

      // Apply themes immediately
      applyTheme('board', selectedBoardTheme);
      applyTheme('pieces', selectedPiecesTheme);

      this.classList.remove('btn-info');
      this.classList.add('btn-success');
      this.innerHTML = '<i class="fas fa-check"></i> Đã áp dụng!';
      this.disabled = false;

      setTimeout(() => {
        this.classList.remove('btn-success');
        this.classList.add('btn-danger');
        this.innerHTML = '<i class="fas fa-check"></i> Áp dụng Theme';
        panel.classList.remove('show');
      }, 2000);
      @endif
    });
  }

  function updateActiveThemes() {
    // On page load, read from hidden inputs
    const boardTheme = document.getElementById('boardTheme')?.value || 'xiangqi-board';
    const piecesTheme = document.getElementById('piecesTheme')?.value || 'wiki';

    themeOptions.forEach(option => {
      const themeType = option.dataset.themeType;
      const themeName = option.dataset.theme;

      // Remove all active states first
      option.classList.remove('active');

      // Set active based on current values
      if ((themeType === 'board' && themeName === boardTheme) ||
          (themeType === 'pieces' && themeName === piecesTheme)) {
        option.classList.add('active');
      }
    });
  }

  // Handle theme option clicks to toggle active state
  function handleThemeClick(clickedOption) {
    const themeType = clickedOption.dataset.themeType;

    // Remove active from all options of this type
    themeOptions.forEach(option => {
      if (option.dataset.themeType === themeType) {
        option.classList.remove('active');
      }
    });

    // Add active to clicked option
    clickedOption.classList.add('active');
  }

  function applyTheme(themeType, themeName) {
    console.log('Applying theme:', themeType, '=', themeName);

    // For guest users, we need to be more aggressive since no page reload
    @if (!auth()->check())
    // Force immediate board recreation for guests
    console.log('Guest user - forcing immediate theme apply');

    setTimeout(() => {
      try {
        // Try to get board instance
        let boardInstance = null;
        if (typeof board !== 'undefined') {
          boardInstance = board;
        } else if (typeof window.board !== 'undefined') {
          boardInstance = window.board;
        }

        if (boardInstance) {
          // Get current position
          const currentPos = typeof boardInstance.position === 'function' ?
            boardInstance.position() : 'start';

          console.log('Current position:', currentPos);

          // Destroy and recreate board with new theme
          if (typeof boardInstance.destroy === 'function') {
            boardInstance.destroy();
          }

          // Wait then recreate
          setTimeout(() => {
            try {
              const boardElement = document.getElementById('ban-co');
              if (boardElement && typeof Xiangqiboard === 'function') {
                // Create new board instance with updated theme values
                if (typeof window.board !== 'undefined') {
                  delete window.board;
                }
                const isPuzzlePage = window.location.pathname === '/co-the';
                window.board = Xiangqiboard('ban-co', {
                  draggable: true,
                  position: currentPos,
                  sparePieces: isPuzzlePage,
                  showNotation: true
                });

                // Update global board reference if needed
                if (typeof board === 'undefined') {
                  window.board = window.board;
                }

                console.log('Board recreated for guest with new theme');
              }
            } catch (error) {
              console.log('Board recreation failed:', error);
              // Last resort: reload page
              location.reload();
            }
          }, 200);
        } else {
          console.log('Board instance not found for guest, reloading page');
          location.reload();
        }
      } catch (error) {
        console.log('Guest theme apply error:', error);
        location.reload();
      }
    }, 100);

    @else
    // Logged users - use theme manager or reload
    if (typeof window.updateBoardTheme !== 'function') {
      console.log('Theme manager not found, reloading page to apply theme');
      setTimeout(() => {
        location.reload();
      }, 500);
    }
    @endif

    console.log('Theme apply triggered for:', themeType, '=', themeName);

    setTimeout(() => {
      if (typeof window.updateBoardTheme === 'function') {
        window.updateBoardTheme();
      }
    }, 50);
  }


});
</script>
