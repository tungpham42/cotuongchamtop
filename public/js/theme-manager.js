(function setupXiangqiBoardTracking() {
  if (window.__XIANGQI_TRACKER_READY__) {
    return;
  }

  window.__XIANGQI_TRACKER_READY__ = true;

  const registerInstance = instance => {
    if (!instance || typeof instance !== 'object') {
      return;
    }

    if (instance.__themeTrackerRegistered) {
      return;
    }

    instance.__themeTrackerRegistered = true;
    const store = window.__XIANGQI_BOARD_INSTANCES__ = window.__XIANGQI_BOARD_INSTANCES__ || [];
    store.push(instance);
  };

  const wrapConstructor = ctor => {
    if (typeof ctor !== 'function') {
      return ctor;
    }

    if (ctor.__themeTrackerPatched) {
      return ctor;
    }

    const wrapped = function () {
      const instance = ctor.apply(this, arguments);
      registerInstance(instance);
      return instance;
    };

    try {
      Object.keys(ctor).forEach(key => {
        wrapped[key] = ctor[key];
      });
    } catch (error) {
      console.debug('Board tracker static copy error:', error);
    }

    if (ctor.prototype) {
      wrapped.prototype = ctor.prototype;
    }

    wrapped.__originalConstructor = ctor;
    wrapped.__themeTrackerPatched = true;
    return wrapped;
  };

  try {
    const descriptor = Object.getOwnPropertyDescriptor(window, 'Xiangqiboard');

    if (!descriptor || descriptor.configurable) {
      Object.defineProperty(window, 'Xiangqiboard', {
        configurable: true,
        enumerable: true,
        set(value) {
          const wrapped = wrapConstructor(value);

          Object.defineProperty(window, 'Xiangqiboard', {
            configurable: true,
            enumerable: true,
            writable: true,
            value: wrapped
          });

          window.__XIANGQI_BOARD_INSTANCES__ = window.__XIANGQI_BOARD_INSTANCES__ || [];
        },
        get() {
          return undefined;
        }
      });
    }
  } catch (error) {
    console.log('Board tracker setup error:', error);
  }

  if (typeof window.Xiangqiboard === 'function') {
    window.Xiangqiboard = wrapConstructor(window.Xiangqiboard);
    window.__XIANGQI_BOARD_INSTANCES__ = window.__XIANGQI_BOARD_INSTANCES__ || [];
  }
})();

// Global function to update board theme (propagates to every tracked board instance)
window.updateBoardTheme = function () {
  try {
    let boardTheme = document.getElementById('boardTheme')?.value || 'xiangqi-board';
    let piecesTheme = document.getElementById('piecesTheme')?.value || 'wiki';
    const piecesUrlRaw = document.getElementById('piecesUrl')?.value || window.location.origin;
    const baseUrl = piecesUrlRaw.replace(/\/+$/, '');

    // For guest users, check localStorage if inputs are still default
    if (boardTheme === 'xiangqi-board' && localStorage.getItem('guest_board_theme')) {
      boardTheme = localStorage.getItem('guest_board_theme');
    }

    if (piecesTheme === 'wiki' && localStorage.getItem('guest_pieces_theme')) {
      piecesTheme = localStorage.getItem('guest_pieces_theme');
    }

    console.log('Updating board theme:', boardTheme, piecesTheme);

    const newBoardTheme = baseUrl + '/img/xiangqiboards/' + boardTheme + '.svg';
    const newPieceTheme = baseUrl + '/img/xiangqipieces/' + piecesTheme + '/{piece}.svg';

    const store = window.__XIANGQI_BOARD_INSTANCES__ = window.__XIANGQI_BOARD_INSTANCES__ || [];
    const uniqueBoards = [];
    const seenBoards = new Set();

    const addBoardInstance = instance => {
      if (!instance || typeof instance !== 'object') {
        return;
      }

      if (!instance.__themeTrackerRegistered) {
        instance.__themeTrackerRegistered = true;
        store.push(instance);
      }

      if (seenBoards.has(instance)) {
        return;
      }

      seenBoards.add(instance);
      uniqueBoards.push(instance);
    };

    if (typeof window.board !== 'undefined' && window.board) {
      addBoardInstance(window.board);
    }

    store.forEach(addBoardInstance);

    const refreshInstance = instance => {
      try {
        if (instance.config) {
          instance.config.boardTheme = newBoardTheme;
          instance.config.pieceTheme = newPieceTheme;
        }

        const currentPosition = typeof instance.position === 'function' ? instance.position() : null;

        if (typeof instance.resize === 'function') {
          instance.resize();
        } else if (currentPosition && typeof instance.position === 'function') {
          instance.position(currentPosition, false);
        }

        if (currentPosition && typeof instance.position === 'function') {
          setTimeout(() => {
            try {
              instance.position(currentPosition, false);
            } catch (restoreError) {
              console.log('Board restore error:', restoreError);
            }
          }, 100);
        }
      } catch (instanceError) {
        console.log('Board theme update error for instance:', instanceError);
      }
    };

    if (uniqueBoards.length === 0) {
      console.log('No Xiangqi boards registered yet; new theme will apply on future boards.');
    }

    uniqueBoards.forEach(refreshInstance);
  } catch (error) {
    console.log('Theme update error:', error);
  }
};

// Simple DOMContentLoaded listener - no observers to avoid conflicts
document.addEventListener('DOMContentLoaded', function () {
  console.log('Theme manager loaded');

  const ensureMainBoardTracked = () => {
    if (typeof window.board === 'undefined' || !window.board) {
      return false;
    }

    const store = window.__XIANGQI_BOARD_INSTANCES__ = window.__XIANGQI_BOARD_INSTANCES__ || [];
    if (!window.board.__themeTrackerRegistered) {
      window.board.__themeTrackerRegistered = true;
      store.push(window.board);
    }

    return true;
  };

  // Try immediately and again shortly after in case the board initializes late
  ensureMainBoardTracked();
  setTimeout(ensureMainBoardTracked, 800);

  // For guest users, automatically apply localStorage themes
  const hasGuestBoardTheme = localStorage.getItem('guest_board_theme');
  const hasGuestPiecesTheme = localStorage.getItem('guest_pieces_theme');

  if (hasGuestBoardTheme || hasGuestPiecesTheme) {
    console.log('Found guest themes in localStorage, applying...');

    // Update hidden inputs to reflect localStorage values
    if (hasGuestBoardTheme && document.getElementById('boardTheme')) {
      document.getElementById('boardTheme').value = hasGuestBoardTheme;
    }

    if (hasGuestPiecesTheme && document.getElementById('piecesTheme')) {
      document.getElementById('piecesTheme').value = hasGuestPiecesTheme;
    }

    // Wait for board to be initialized then apply theme
    setTimeout(() => {
      if (typeof window.updateBoardTheme === 'function') {
        window.updateBoardTheme();
      }
    }, 1000);
  }
});
