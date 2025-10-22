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
      #room-replay-board {
        width: 100%;
        max-width: 420px;
        margin: 12px auto;
      }
      #room-replay-board .xiangqiboard-8ddcb {
        margin: auto;
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

  function setupReplayBoard($) {
    if (!$('#ban-co').length || $('#room-replay-panel').length) {
      return;
    }

    // Kiểm tra xem ván đấu có đang diễn ra không
    // Nếu có element game-over hoặc result, có nghĩa là ván đã kết thúc
    const gameOver = $('#game-over:visible').length > 0 || 
                     $('.game-result:visible').length > 0 ||
                     $('#game-status').text().toLowerCase().includes('thắng') ||
                     $('#game-status').text().toLowerCase().includes('thua') ||
                     $('#game-status').text().toLowerCase().includes('hòa');

    const panel = $(`
      <section id="room-replay-panel" style="${gameOver ? '' : 'display: none;'}">
        <div class="room-replay-header">
          <span><i class="fad fa-book-open"></i> Kỳ phổ</span>
          <span id="room-replay-status" class="small text-muted"></span>
        </div>
        <div id="room-replay-board"></div>
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

    state.replayBoard = window.Xiangqiboard('room-replay-board', {
      draggable: false,
      position: config.initialFen,
      showNotation: true,
    });

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
    if (!state.replayBoard || !state.replayGame) {
      return;
    }
    const fen = getFenForIndex(state.currentIndex);
    try {
      state.replayGame.load(fen);
      state.replayBoard.position(fen, true);
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
    const display = move.san || move.iccs || (move.from && move.to ? move.from + '-' + move.to : '');
    return display ? (idx + 1) + '. ' + display : (idx + 1) + '.';
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
    // Theo dõi các thay đổi trong DOM để phát hiện khi game kết thúc
    const checkGameStatus = function() {
      const gameOver = $('#game-over:visible').length > 0 || 
                       $('.game-result:visible').length > 0 ||
                       $('#game-status').text().toLowerCase().includes('thắng') ||
                       $('#game-status').text().toLowerCase().includes('thua') ||
                       $('#game-status').text().toLowerCase().includes('hòa') ||
                       $('#game-status').text().toLowerCase().includes('kết thúc');
      
      const panel = $('#room-replay-panel');
      if (panel.length) {
        if (gameOver) {
          panel.slideDown(300);
        } else {
          panel.slideUp(300);
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
