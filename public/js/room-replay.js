(function (window, document) {
  'use strict';

  const config = window.__ROOM_REPLAY_CONFIG__ || null;
  if (!config || !config.code) {
    return;
  }

  const MAX_DEPENDENCY_ATTEMPTS = 40;
  const DEPENDENCY_WAIT_MS = 200;

  const state = {
    initialized: false,
    history: Array.isArray(config.history) ? config.history.map(cloneMove) : [],
    currentIndex: 0,
    pendingMove: null,
    fetching: false,
    replayGame: null,
    replayBoard: null,
    elements: {},
    lastFetchedFen: null,
  };

  function cloneMove(move) {
    if (!move || typeof move !== 'object') {
      return {};
    }
    return JSON.parse(JSON.stringify(move));
  }

  function resolveGlobal(name) {
    if (typeof window[name] !== 'undefined') {
      return window[name];
    }
    try {
      // eslint-disable-next-line no-new-func
      return Function('return typeof ' + name + ' !== "undefined" ? ' + name + ' : undefined;')();
    } catch (err) {
      return undefined;
    }
  }

  function getGameInstance() {
    return resolveGlobal('game') || null;
  }

  function getBoardInstance() {
    return resolveGlobal('board') || null;
  }

  function dependencyReadyCheck() {
    return (
      typeof window.jQuery !== 'undefined' &&
      typeof window.Xiangqiboard === 'function' &&
      typeof window.Xiangqi === 'function'
    );
  }

  function waitForDependencies(attempt) {
    if (dependencyReadyCheck()) {
      start(window.jQuery);
      return;
    }
    if (attempt >= MAX_DEPENDENCY_ATTEMPTS) {
      return;
    }
    setTimeout(function () {
      waitForDependencies(attempt + 1);
    }, DEPENDENCY_WAIT_MS);
  }

  function waitForBoard($, attempt) {
    const game = getGameInstance();
    const board = getBoardInstance();
    if (game && board) {
      initialize($, game);
      return;
    }
    if (attempt >= MAX_DEPENDENCY_ATTEMPTS) {
      return;
    }
    setTimeout(function () {
      waitForBoard($, attempt + 1);
    }, DEPENDENCY_WAIT_MS);
  }

  function start($) {
    $(function () {
      waitForBoard($, 0);
    });
  }

  function initialize($, game) {
    if (state.initialized) {
      return;
    }
    state.initialized = true;
    injectStyles();
    prepareState();
    setupReplayBoard($);
    hookGameMove(game);
    hookAjax($);
    hookFenPolling($);
    updateStatusUI();
    watchGameStatus($);
  }

  function prepareState() {
    state.currentIndex = state.history.length;
    state.replayGame = new window.Xiangqi();
  }

  function injectStyles() {
    if (document.getElementById('room-replay-style')) {
      return;
    }
    const style = document.createElement('style');
    style.id = 'room-replay-style';
    style.textContent = `
      #room-replay-panel {
        background-color: rgba(24, 26, 27, 0.65);
        border-radius: 12px;
        padding: 12px;
        margin-top: 16px;
      }
      #room-replay-panel .room-replay-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #f8f9fa;
      }

      #room-replay-controls .btn {
        min-width: 42px;
      }
      .room-replay-move-list {
        margin-top: 12px;
        max-height: 160px;
        overflow-y: auto;
        font-size: 0.9rem;
      }
      .room-replay-move {
        cursor: pointer;
        display: inline-block;
        margin: 2px 4px;
        padding: 2px 6px;
        border-radius: 4px;
        color: #f8f9fa;
        background-color: rgba(255, 255, 255, 0.08);
      }
      .room-replay-move.red {
        border: 1px solid rgba(220, 53, 69, 0.6);
      }
      .room-replay-move.black {
        border: 1px solid rgba(52, 58, 64, 0.6);
      }
      .room-replay-move.active {
        background-color: rgba(220, 53, 69, 0.65);
        font-weight: 600;
      }
      .room-replay-empty {
        color: rgba(248, 249, 250, 0.65);
        font-style: italic;
      }
    `;
    document.head.appendChild(style);
  }

  function setupReplayBoard() {
    if (!state.history || !state.history.length) {
      console.log('No game history available for replay');
      return;
    }

    // Kiểm tra xem có phải đang ở chế độ xem lại không
    const isReplayMode = window.location.hash.includes('replay') || 
                        window.location.pathname.includes('replay') ||
                        $('.replay-mode-indicator').length > 0;

    // Kiểm tra trạng thái game từ DOM
    const gameStatusElement = $('.game-status, .room-status, [data-game-status]');
    const gameStatusText = gameStatusElement.text() || '';
    
    // Game đang active nếu có text cho thấy đang chơi
    const isGameActive = gameStatusText.includes('đang đánh') || 
                        gameStatusText.includes('playing') ||
                        gameStatusText.includes('轮到') ||
                        gameStatusText.includes('lượt của') ||
                        (gameStatusText.includes('Trắng') || gameStatusText.includes('Đỏ')) && 
                        !gameStatusText.includes('thắng') && 
                        !gameStatusText.includes('thua');

    console.log('Replay setup check:', {
      isReplayMode,
      isGameActive, 
      gameStatusText: gameStatusText.trim(),
      shouldCreateReplay: isReplayMode || !isGameActive
    });

    // TRƯỜNG HỢP 1: Đang đánh game - KHÔNG tạo kỳ phổ
    if (!isReplayMode && isGameActive) {
      console.log('🚫 Game is active - NO replay elements created');
      return;
    }

    // TRƯỜNG HỢP 2: Xem lại HOẶC game đã kết thúc - TạO kỳ phổ
    console.log('✅ Creating replay panel - either in replay mode or game finished');

    const panel = $(`
      <section id="room-replay-panel" style="">
        <div class="room-replay-header">
          <span><i class="fad fa-book-open"></i> Kỳ phổ</span>
          <span id="room-replay-status" class="small text-muted"></span>
        </div>
        <div id="room-replay-controls" class="btn-group btn-group-sm d-flex justify-content-center" role="group">
          <button type="button" class="btn btn-outline-light" data-replay-action="first" title="Về đầu"><i class="fal fa-angle-double-left"></i></button>
          <button type="button" class="btn btn-outline-light" data-replay-action="prev" title="Lùi 1 nước"><i class="fal fa-angle-left"></i></button>
          <button type="button" class="btn btn-outline-light" data-replay-action="next" title="Tiến 1 nước"><i class="fal fa-angle-right"></i></button>
          <button type="button" class="btn btn-outline-light" data-replay-action="last" title="Đến cuối"><i class="fal fa-angle-double-right"></i></button>
          <button type="button" class="btn btn-outline-light" data-replay-action="play" title="Tự động phát"><i class="fal fa-play"></i></button>
        </div>
        <div class="room-replay-move-list mt-2" id="room-replay-move-list"></div>
      </section>
    `);

    $('#ban-co').after(panel);

    // Thêm nút toggle kỳ phổ - chỉ hiện khi game đã kết thúc
    const toggleButton = $(`
      <div class="text-center mt-2" id="toggle-replay-container">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="toggle-replay-panel">
          <i class="fal fa-book-open"></i> <span class="toggle-text">Ẩn Kỳ phổ</span>
        </button>
      </div>
    `);
    
    $('#ban-co').after(toggleButton);
    
    // Sự kiện click cho nút toggle
    $('#toggle-replay-panel').on('click', function() {
      const panel = $('#room-replay-panel');
      const button = $(this);
      const text = button.find('.toggle-text');
      
      if (panel.is(':visible')) {
        panel.slideUp(300);
        text.text('Hiện Kỳ phổ');
      } else {
        panel.slideDown(300);
        text.text('Ẩn Kỳ phổ');
      }
    });

    // Không tạo replay board vì sẽ sử dụng bàn cờ chính
    // state.replayBoard = window.Xiangqiboard('room-replay-board', {
    //   draggable: false,
    //   position: config.initialFen,
    //   showNotation: true,
    // });

    state.elements.status = $('#room-replay-status');
    state.elements.moveList = $('#room-replay-move-list');
    state.elements.playButton = panel.find('[data-replay-action="play"]');

    panel.find('[data-replay-action]').on('click', handleControlClick);

    renderMoveList();
    setIndex(state.history.length, { silent: true });
    syncReplayBoard();
  }

  function handleControlClick(event) {
    event.preventDefault();
    const action = event.currentTarget.getAttribute('data-replay-action');
    switch (action) {
      case 'first':
        setIndex(0);
        break;
      case 'prev':
        setIndex(state.currentIndex - 1);
        break;
      case 'next':
        setIndex(state.currentIndex + 1);
        break;
      case 'last':
        setIndex(state.history.length);
        break;
      case 'play':
        toggleAutoplay();
        break;
      default:
        break;
    }
  }

  let autoplayTimer = null;
  function toggleAutoplay() {
    if (!state.elements.playButton) {
      return;
    }
    if (autoplayTimer) {
      clearInterval(autoplayTimer);
      autoplayTimer = null;
      state.elements.playButton.html('<i class="fal fa-play"></i>');
      return;
    }
    state.elements.playButton.html('<i class="fal fa-pause"></i>');
    autoplayTimer = setInterval(function () {
      if (state.currentIndex >= state.history.length) {
        clearInterval(autoplayTimer);
        autoplayTimer = null;
        if (state.elements.playButton) {
          state.elements.playButton.html('<i class="fal fa-play"></i>');
        }
        return;
      }
      setIndex(state.currentIndex + 1);
    }, 1200);
  }

  function setIndex(index, options) {
    const nextIndex = Math.max(0, Math.min(index, state.history.length));
    state.currentIndex = nextIndex;
    if (!options || !options.silent) {
      syncReplayBoard();
    }
    updateStatusUI();
    highlightActiveMove();
  }

  function syncReplayBoard() {
    if (!state.replayGame) {
      return;
    }
    const fen = getFenForIndex(state.currentIndex);
    try {
      state.replayGame.load(fen);
      
      // Sử dụng bàn cờ chính thay vì tạo bàn cờ riêng
      const mainBoard = getBoardInstance();
      if (mainBoard && typeof mainBoard.position === 'function') {
        mainBoard.position(fen, true);
      }
    } catch (err) {
      // Ignore invalid FEN
    }
  }

  function getFenForIndex(index) {
    if (index <= 0) {
      return config.initialFen;
    }
    const move = state.history[index - 1] || {};
    return move.fen || config.initialFen;
  }

  function formatMoveLabel(move, idx) {
    if (!move) {
      return '';
    }
    
    // Chuyển đổi sang ký hiệu cờ tướng truyền thống
    const xiangqiNotation = convertToXiangqiNotation(move);
    const display = xiangqiNotation || move.san || move.iccs || (move.from && move.to ? move.from + '-' + move.to : '');
    return display ? (idx + 1) + '. ' + display : (idx + 1) + '.';
  }

  function convertToXiangqiNotation(move) {
    if (!move.from || !move.to) {
      return null;
    }

    // Bản đồ quân cờ viết tắt tiếng Việt
    const pieceSymbols = {
      'r': 'X', 'R': 'X', // Xe
      'h': 'M', 'H': 'M', 'n': 'M', 'N': 'M', // Mã
      'b': 'T', 'B': 'T', 'e': 'T', 'E': 'T', // Tượng/Tịnh
      'a': 'S', 'A': 'S', // Sĩ
      'k': 'G', 'K': 'G', // Tướng (General)
      'c': 'P', 'C': 'P', // Pháo
      'p': 'C', 'P': 'C'  // Chốt/Tốt
    };

    // Chuyển đổi vị trí từ ký hiệu sang số
    function parsePosition(pos) {
      const file = pos.charCodeAt(0) - 97; // a=0, b=1, etc
      const rank = parseInt(pos[1]) - 1;
      return { file, rank };
    }

    const fromPos = parsePosition(move.from);
    const toPos = parsePosition(move.to);
    
    const piece = pieceSymbols[move.piece] || move.piece || '';
    const isRed = move.color === 'r';
    
    // Tính toán hướng di chuyển
    const fileDiff = toPos.file - fromPos.file;
    const rankDiff = toPos.rank - fromPos.rank;
    
    let directionSymbol = '';
    let target = '';
    
    if (rankDiff === 0) {
      // Di chuyển ngang (bình)
      directionSymbol = '.';
      target = 9 - toPos.file; // Cột đích
    } else if (fileDiff === 0) {
      // Di chuyển thẳng
      if ((isRed && rankDiff > 0) || (!isRed && rankDiff < 0)) {
        directionSymbol = '+'; // Tiến
      } else {
        directionSymbol = '-'; // Thoái
      }
      target = Math.abs(rankDiff); // Số ô di chuyển
    } else {
      // Di chuyển chéo (Tượng, Mã)
      if ((isRed && rankDiff > 0) || (!isRed && rankDiff < 0)) {
        directionSymbol = '+'; // Tiến
      } else {
        directionSymbol = '-'; // Thoái
      }
      target = 9 - toPos.file; // Cột đích
    }

    // Số cột xuất phát (1-9 từ trái qua phải)
    const fromColumn = 9 - fromPos.file;
    
    return `${piece}${fromColumn}${directionSymbol}${target}`;
  }

  function renderMoveList() {
    if (!state.elements.moveList) {
      return;
    }
    const listEl = state.elements.moveList;
    listEl.empty();

    if (!state.history.length) {
      listEl.append('<span class="room-replay-empty">Chưa có nước đi nào.</span>');
      return;
    }

    state.history.forEach(function (move, idx) {
      const label = formatMoveLabel(move, idx);
      const item = $('<span>')
        .addClass('room-replay-move')
        .addClass(move.color === 'r' ? 'red' : 'black')
        .attr('data-move-index', idx + 1)
        .text(label);

      item.on('click', function () {
        if (autoplayTimer) {
          toggleAutoplay();
        }
        setIndex(idx + 1);
      });

      listEl.append(item);
    });

    highlightActiveMove();
  }

  function highlightActiveMove() {
    if (!state.elements.moveList) {
      return;
    }
    state.elements.moveList.find('.room-replay-move').removeClass('active');
    if (state.currentIndex === 0) {
      return;
    }
    const active = state.elements.moveList.find('[data-move-index="' + state.currentIndex + '"]');
    active.addClass('active');
    ensureMoveVisible(active);
  }

  function ensureMoveVisible($element) {
    if (!$element || !$element.length) {
      return;
    }
    const container = state.elements.moveList.get(0);
    if (!container) {
      return;
    }
    const elementTop = $element.position().top;
    const elementBottom = elementTop + $element.outerHeight();
    if (elementTop < 0) {
      container.scrollTop += elementTop;
    } else if (elementBottom > container.clientHeight) {
      container.scrollTop += elementBottom - container.clientHeight;
    }
  }

  function updateStatusUI() {
    if (!state.elements.status) {
      return;
    }
    const total = state.history.length;
    const current = state.currentIndex;
    state.elements.status.text(current + ' / ' + total + ' nước');
  }

  function hookGameMove(game) {
    if (!game || typeof game.move !== 'function' || state.moveHooked) {
      return;
    }
    const originalMove = game.move.bind(game);
    game.move = function () {
      const fenBefore = game.fen();
      const result = originalMove.apply(game, arguments);
      if (result) {
        state.pendingMove = {
          fen_before: fenBefore || config.initialFen,
          move: cloneMove(result),
        };
      } else {
        state.pendingMove = null;
      }
      return result;
    };
    state.moveHooked = true;
  }

  function buildMoveEntry(game) {
    if (!state.pendingMove || !game) {
      return null;
    }

    const move = state.pendingMove.move || {};
    const fenBefore =
      state.pendingMove.fen_before ||
      (state.history.length ? state.history[state.history.length - 1].fen : config.initialFen);

    const verboseHistory = game.history({ verbose: true }) || [];
    const historyLength = verboseHistory.length;
    const lastMove = verboseHistory.length ? verboseHistory[verboseHistory.length - 1] : move;

    const entry = {
      ply: historyLength || (state.history.length + 1),
      san: lastMove.san || move.san || null,
      iccs: lastMove.iccs || move.iccs || (move.from && move.to ? move.from + move.to : null),
      from: lastMove.from || move.from || null,
      to: lastMove.to || move.to || null,
      piece: lastMove.piece || move.piece || null,
      captured: lastMove.captured || move.captured || null,
      color: lastMove.color || move.color || null,
      flags: lastMove.flags || move.flags || null,
      fen_before: fenBefore,
      fen: game.fen(),
      created_at: new Date().toISOString(),
    };

    state.pendingMove = null;
    return entry;
  }

  function appendMove(entry, options) {
    if (!entry || !entry.fen) {
      return;
    }
    const last = state.history[state.history.length - 1];
    if (last && last.fen === entry.fen) {
      return;
    }
    const wasAtEnd = state.currentIndex === state.history.length;
    state.history.push(entry);

    if (!options || !options.skipRender) {
      renderMoveList();
    }

    if (!options || !options.skipStatus) {
      updateStatusUI();
    }

    if (!options || !options.silent) {
      if (wasAtEnd) {
        setIndex(state.history.length);
      } else {
        highlightActiveMove();
      }
    } else if (!options || !options.skipRender) {
      highlightActiveMove();
    }
  }

  function hookAjax($) {
    $.ajaxPrefilter(function (options, originalOptions) {
      if (!options || !options.url) {
        return;
      }
      const url = options.url;
      if (!/\/updateFEN\b/.test(url)) {
        return;
      }
      const game = getGameInstance();
      const entry = buildMoveEntry(game);
      if (!entry) {
        return;
      }

      const payload = JSON.stringify(entry);

      if (options.processData === false && options.data instanceof FormData) {
        options.data.append('move', payload);
      } else if (typeof options.data === 'string') {
        options.data += (options.data.length ? '&' : '') + 'move=' + encodeURIComponent(payload);
      } else {
        options.data = $.extend({}, options.data || {}, { move: payload });
      }

      const originalSuccess = options.success;
      options.success = function (data, status, xhr) {
        appendMove(entry);
        if (typeof originalSuccess === 'function') {
          originalSuccess.call(this, data, status, xhr);
        }
      };
    });
  }

  function hookFenPolling($) {
    $(document).ajaxComplete(function (event, xhr, settings) {
      if (!settings || !settings.url) {
        return;
      }
      if (!/\/readFEN\//.test(settings.url)) {
        return;
      }
      const fen = (xhr && xhr.responseText ? xhr.responseText.trim() : '') || '';
      if (!fen || fen === state.lastFetchedFen) {
        return;
      }
      state.lastFetchedFen = fen;
      const lastHistoryFen = state.history.length ? state.history[state.history.length - 1].fen : config.initialFen;
      if (fen !== lastHistoryFen) {
        requestLatestMoves();
      }
    });
  }

  function requestLatestMoves() {
    if (state.fetching) {
      return;
    }
    state.fetching = true;
    const after = state.history.length;
    const url = '/api/rooms/' + encodeURIComponent(config.code) + '/moves';
    window.jQuery
      .getJSON(url, { after: after })
      .done(function (data) {
        if (!data || !Array.isArray(data.moves) || !data.moves.length) {
          return;
        }
        const atEnd = state.currentIndex === state.history.length;
        data.moves.forEach(function (move) {
          appendMove(cloneMove(move), { silent: true, skipRender: true, skipStatus: true });
        });
        updateStatusUI();
        renderMoveList();
        if (atEnd) {
          setIndex(state.history.length);
        } else {
          highlightActiveMove();
        }
      })
      .always(function () {
        state.fetching = false;
      });
  }

  function watchGameStatus($) {
    // Theo dõi các thay đổi trong DOM để phát hiện khi có thể hiện kỳ phổ
    const checkGameStatus = function() {
      // Kiểm tra các dấu hiệu game đã kết thúc
      const gameOverVisible = $('#game-over:visible').length > 0;
      const gameResultVisible = $('.game-result:visible').length > 0;
      const statusText = $('#game-status').text().toLowerCase();
      const hasWinLoseText = statusText.includes('thắng') || 
                            statusText.includes('thua') || 
                            statusText.includes('hòa') || 
                            statusText.includes('kết thúc');
      
      // Kiểm tra có history move không
      const hasHistory = state.history && state.history.length > 0;
      
      // Kiểm tra URL xem có phải đang xem replay không
      const isViewingReplay = window.location.pathname.includes('/phong/') || 
                             window.location.pathname.includes('/room/') ||
                             window.location.pathname.includes('/bang/') ||
                             window.location.pathname.includes('/rumu/') ||
                             window.location.pathname.includes('/fangjian/');
      
      // Hiển thị kỳ phổ nếu:
      // 1. Game đã kết thúc rõ ràng, HOẶC
      // 2. Đang xem replay và có history
      const canShowReplay = gameOverVisible || gameResultVisible || hasWinLoseText || 
                           (isViewingReplay && hasHistory);
      
      // Debug log để kiểm tra
      console.log('Replay status check:', {
        gameOverVisible,
        gameResultVisible, 
        hasWinLoseText,
        statusText,
        hasHistory,
        historyLength: state.history ? state.history.length : 0,
        isViewingReplay,
        currentURL: window.location.pathname,
        canShowReplay
      });
      
      const panel = $('#room-replay-panel');
      const toggleButton = $('#toggle-replay-panel .toggle-text');
      const toggleContainer = $('#toggle-replay-container');
      
      if (panel.length) {
        if (canShowReplay) {
          // Game đã kết thúc - hiện kỳ phổ và nút toggle
          panel.slideDown(300);
          toggleContainer.slideDown(300);
          if (toggleButton.length) {
            toggleButton.text('Ẩn Kỳ phổ');
          }
          console.log('Showing replay panel - Game finished');
        } else {
          // Game đang diễn ra - ẩn kỳ phổ và nút toggle
          panel.slideUp(300);
          toggleContainer.slideUp(300);
          if (toggleButton.length) {
            toggleButton.text('Hiện Kỳ phổ');
          }
          console.log('Hiding replay panel - Game is still active');
        }
      }
    };

    // Kiểm tra ngay lập tức
    setTimeout(checkGameStatus, 500);
    
    // Theo dõi thay đổi trong game-status
    if ($('#game-status').length) {
      const observer = new MutationObserver(checkGameStatus);
      observer.observe($('#game-status')[0], {
        childList: true,
        subtree: true,
        characterData: true
      });
    }

    // Theo dõi thay đổi trong game-over
    if ($('#game-over').length) {
      const observer = new MutationObserver(checkGameStatus);
      observer.observe($('#game-over')[0], {
        attributes: true,
        attributeFilter: ['style', 'class']
      });
    }

    // Kiểm tra định kỳ mỗi 3 giây
    setInterval(checkGameStatus, 3000);
  }

  waitForDependencies(0);
})(window, document);
