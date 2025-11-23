<div class="theme-selector-wrapper mt-2">
  <div class="theme-selector-panel">
    <div class="theme-section">
      <h6 class="theme-title">
        <i class="fas fa-chess-board"></i> 보드 테마
      </h6>
      <div class="theme-options board-themes">
        <button class="theme-option" data-theme-type="board" data-theme="xiangqi-board" title="기본 보드">
          <div class="theme-preview board-preview">
            <img src="{{ url('/') }}/img/xiangqiboards/xiangqi-board.svg" alt="기본 보드" onerror="this.style.display='none'; this.parentElement.classList.add('fallback-board-default')" />
          </div>
        </button>
        <button class="theme-option" data-theme-type="board" data-theme="ban-co-go" title="밝은 나무">
          <div class="theme-preview board-preview">
            <img src="{{ url('/') }}/img/xiangqiboards/ban-co-go.svg" alt="밝은 나무" onerror="this.style.display='none'; this.parentElement.classList.add('fallback-board-wood-light')" />
          </div>
        </button>
        <button class="theme-option" data-theme-type="board" data-theme="wood-board" title="어두운 나무">
          <div class="theme-preview board-preview">
            <img src="{{ url('/') }}/img/xiangqiboards/wood-board.svg" alt="어두운 나무" onerror="this.style.display='none'; this.parentElement.classList.add('fallback-board-wood-dark')" />
          </div>
        </button>
        <button class="theme-option" data-theme-type="board" data-theme="banco" title="라이트">
          <div class="theme-preview board-preview">
            <img src="{{ url('/') }}/img/xiangqiboards/banco.svg" alt="라이트" onerror="this.style.display='none'; this.parentElement.classList.add('fallback-board-light')" />
          </div>
        </button>
      </div>
    </div>

    <div class="theme-section">
      <h6 class="theme-title">
        <i class="fas fa-chess-knight"></i> 기물 세트
      </h6>
      <div class="theme-options piece-themes">
        <button class="theme-option" data-theme-type="pieces" data-theme="wiki" title="기본 기물">
          <div class="theme-preview piece-preview">
            <img src="{{ url('/') }}/img/xiangqipieces/wiki/rK.svg" alt="기본 기물" />
          </div>
        </button>
        <button class="theme-option" data-theme-type="pieces" data-theme="tung" title="스페셜">
          <div class="theme-preview piece-preview">
            <img src="{{ url('/') }}/img/xiangqipieces/tung/rK.svg" alt="스페셜" />
          </div>
        </button>
        <button class="theme-option" data-theme-type="pieces" data-theme="do-den" title="레드 & 블랙">
          <div class="theme-preview piece-preview">
            <img src="{{ url('/') }}/img/xiangqipieces/do-den/rK.svg" alt="레드 & 블랙" />
          </div>
        </button>
        <button class="theme-option" data-theme-type="pieces" data-theme="graphic" title="서양식">
          <div class="theme-preview piece-preview">
            <img src="{{ url('/') }}/img/xiangqipieces/graphic/rK.svg" alt="서양식" />
          </div>
        </button>
        <button class="theme-option" data-theme-type="pieces" data-theme="co" title="오렌지">
          <div class="theme-preview piece-preview">
            <img src="{{ url('/') }}/img/xiangqipieces/co/rK.svg" alt="오렌지" />
          </div>
        </button>
        <button class="theme-option" data-theme-type="pieces" data-theme="wikimedia" title="어두운 노란색">
          <div class="theme-preview piece-preview">
            <img src="{{ url('/') }}/img/xiangqipieces/wikimedia/rK.svg" alt="어두운 노란색" />
          </div>
        </button>
        <button class="theme-option" data-theme-type="pieces" data-theme="quan" title="라이트">
          <div class="theme-preview piece-preview">
            <img src="{{ url('/') }}/img/xiangqipieces/quan/rK.svg" alt="라이트" />
          </div>
        </button>
        <button class="theme-option" data-theme-type="pieces" data-theme="traditional" title="전통">
          <div class="theme-preview piece-preview">
            <img src="{{ url('/') }}/img/xiangqipieces/traditional/rK.svg" alt="전통" />
          </div>
        </button>
      </div>
    </div>

    <div class="theme-section mt-3">
      <div class="text-center">
        <button type="button" class="btn btn-danger btn-sm px-4" id="apply-theme-btn">
          <i class="fas fa-check"></i> 테마 적용
        </button>
      </div>
    </div>
  </div>

  <button class="theme-toggle-btn" id="theme-toggle-btn">
    <i class="fas fa-palette"></i>
    <span class="theme-toggle-text">사용자 설정</span>
  </button>
</div>

<style>
/* Styles remain the same */
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

  // Handle theme selection (updates preview only, does not apply yet)
  themeOptions.forEach(option => {
    option.addEventListener('click', function() {
      // Update active state for visual feedback
      handleThemeClick(this);

      // Highlight apply button to let the user know confirmation is needed
      const applyBtn = document.getElementById('apply-theme-btn');
      if (applyBtn) {
        applyBtn.classList.add('btn-primary');
        applyBtn.classList.remove('btn-danger');
        applyBtn.innerHTML = '<i class="fas fa-circle"></i> 테마 적용';
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
      this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 적용 중...';
      this.disabled = true;

      // Save to server (will reload the page like the old system)
      @if(auth()->check())
      const formData = new FormData();
      formData.append('_token', '{{ csrf_token() }}');
      formData.append('current_id', '{{ auth()->user()->id }}');
      formData.append('board_theme', selectedBoardTheme);
      formData.append('pieces_theme', selectedPiecesTheme);

      fetch('{{ url('/doi-giao-dien') }}', { // Note: URL endpoint is unchanged
        method: 'POST',
        body: formData
      }).then(response => {
        if (response.ok) {
          // Success - old system would redirect, so we'll reload too for consistency
          this.classList.remove('btn-info');
          this.classList.add('btn-success');
          this.innerHTML = '<i class="fas fa-check"></i> 성공!';

          setTimeout(() => {
            location.reload(); // Reload to apply the theme like the old system
          }, 800);
        } else {
          throw new Error('저장 실패');
        }
      }).catch(error => {
        console.log('저장 오류:', error);
        this.classList.remove('btn-info');
        this.classList.add('btn-danger');
        this.innerHTML = '<i class="fas fa-times"></i> 오류!';
        this.disabled = false;

        setTimeout(() => {
          this.classList.remove('btn-danger');
          this.classList.add('btn-warning');
          this.innerHTML = '<i class="fas exclamation-triangle"></i> 테마 적용';
        }, 2000);
      });
      @else
      // Guest user - save to localStorage and apply
      localStorage.setItem('guest_board_theme', selectedBoardTheme);
      localStorage.setItem('guest_pieces_theme', selectedPiecesTheme);

      // Apply themes immediately
      applyTheme('board', selectedBoardTheme);
      applyTheme('pieces', selectedPiecesTheme);

      this.classList.remove('btn-info');
      this.classList.add('btn-success');
      this.innerHTML = '<i class="fas fa-check"></i> 적용됨!';
      this.disabled = false;

      setTimeout(() => {
        this.classList.remove('btn-success');
        this.classList.add('btn-danger');
        this.innerHTML = '<i class="fas fa-check"></i> 테마 적용';
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
    console.log('테마 적용 중:', themeType, '=', themeName);

    // For guest users, we need to be more aggressive since no page reload
    @if (!auth()->check())
    // Force immediate board recreation for guests
    console.log('게스트 사용자 - 즉시 테마 강제 적용');

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

          console.log('현재 위치:', currentPos);

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
                const isPuzzlePage = window.location.pathname === '/peojeul';
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

                console.log('게스트를 위해 새 테마로 보드를 다시 생성했습니다');
              }
            } catch (error) {
              console.log('보드 재생성 실패:', error);
              // Last resort: reload page
              location.reload();
            }
          }, 200);
        } else {
          console.log('게스트의 보드 인스턴스를 찾을 수 없어 페이지를 새로고침합니다');
          location.reload();
        }
      } catch (error) {
        console.log('게스트 테마 적용 오류:', error);
        location.reload();
      }
    }, 100);

    @else
    // Logged users - use theme manager or reload
    if (typeof window.updateBoardTheme !== 'function') {
      console.log('테마 관리자를 찾을 수 없어 페이지를 새로고침하여 테마를 적용합니다');
      setTimeout(() => {
        location.reload();
      }, 500);
    }
    @endif

    console.log('테마 적용 트리거:', themeType, '=', themeName);

    setTimeout(() => {
      if (typeof window.updateBoardTheme === 'function') {
        window.updateBoardTheme();
      }
    }, 50);
  }


});
</script>
