(function() {
  'use strict';

  if (typeof Xiangqi === 'undefined') {
    return;
  }

  var PIECE_LABELS = {
    k: 'T',
    a: 'S',
    b: 'tg',
    n: 'M',
    r: 'X',
    c: 'P',
    p: 'B'
  };

  function formatMove(move) {
    if (!move) {
      return '';
    }
    var piece = PIECE_LABELS[move.piece] || String(move.piece || '').toUpperCase();
    var capture = move.flags && move.flags.indexOf('c') !== -1 ? 'x' : '-';
    return piece + move.from + capture + move.to;
  }

  function buildFromMoves(startFen, moves) {
    var tempGame;
    try {
      tempGame = new Xiangqi(startFen);
    } catch (error) {
      tempGame = new Xiangqi();
    }

    var fens = [tempGame.fen()];
    var prettyMoves = [];

    for (var i = 0; i < moves.length; i++) {
      var moveStr = moves[i];
      var move = tempGame.move(moveStr);
      if (!move) {
        continue;
      }
      prettyMoves.push(move);
      fens.push(tempGame.fen());
    }

    return {
      fens: fens,
      prettyMoves: prettyMoves
    };
  }

  function createKyPho(options) {
    var listEl = document.getElementById('kypho-list');
    var prevBtn = document.getElementById('kypho-prev');
    var nextBtn = document.getElementById('kypho-next');
    var playBtn = document.getElementById('kypho-play');
    var pauseBtn = document.getElementById('kypho-pause');
    var panelEl = document.getElementById('kypho-panel');

    if (!listEl || !prevBtn || !nextBtn || !playBtn || !pauseBtn || !panelEl) {
      return null;
    }

    (function placePanelNearTheme() {
      var themeWrapper = document.querySelector('.theme-selector-wrapper');
      if (!themeWrapper || !themeWrapper.parentNode) {
        return;
      }
      if (panelEl.parentNode !== themeWrapper.parentNode) {
        themeWrapper.parentNode.insertBefore(panelEl, themeWrapper.nextSibling);
      } else if (panelEl.previousElementSibling !== themeWrapper) {
        themeWrapper.parentNode.insertBefore(panelEl, themeWrapper.nextSibling);
      }
    })();

    var state = {
      board: options.board,
      startFen: options.startFen,
      moves: [],
      fens: [],
      prettyMoves: [],
      currentIndex: 0,
      isPlaying: false,
      playTimer: null,
      playDelay: options.playDelay || 800,
      isLive: options.isLive,
      roomCode: options.roomCode || null
    };

    function renderMoves() {
      listEl.innerHTML = '';
      if (!state.prettyMoves.length) {
        return;
      }
      for (var i = 0; i < state.prettyMoves.length; i++) {
        var moveNumber = Math.floor(i / 2) + 1;
        var moveText = formatMove(state.prettyMoves[i]);
        var moveEl = document.createElement('span');
        moveEl.className = 'kypho-move ' + (i % 2 === 0 ? 'red' : 'black');
        moveEl.setAttribute('data-kypho-index', i);

        if (i % 2 === 0) {
          var numberEl = document.createElement('span');
          numberEl.className = 'kypho-move-number';
          numberEl.textContent = moveNumber + '.';
          moveEl.appendChild(numberEl);
          moveEl.appendChild(document.createTextNode(' '));
        }

        moveEl.appendChild(document.createTextNode(moveText));
        listEl.appendChild(moveEl);
      }
    }

    function highlightMove(index) {
      var moves = listEl.querySelectorAll('.kypho-move');
      for (var i = 0; i < moves.length; i++) {
        moves[i].classList.remove('kypho-current');
      }
      if (index < 0) {
        return;
      }
      var active = listEl.querySelector('[data-kypho-index="' + index + '"]');
      if (active) {
        active.classList.add('kypho-current');
      }
    }

    function stopPlayback() {
      if (state.playTimer) {
        clearInterval(state.playTimer);
        state.playTimer = null;
      }
      state.isPlaying = false;
    }

    function updateControls() {
      var live = typeof state.isLive === 'function' ? state.isLive() : false;
      var hasMoves = state.moves.length > 0;
      var enabled = !live && hasMoves;

      prevBtn.disabled = !enabled || state.isPlaying;
      nextBtn.disabled = !enabled || state.isPlaying;
      playBtn.disabled = !enabled || state.isPlaying;
      pauseBtn.disabled = !enabled || !state.isPlaying;

      if (live && state.isPlaying) {
        stopPlayback();
      }
    }

    function setIndex(index) {
      if (!state.fens.length) {
        return;
      }
      if (index < 0) {
        index = 0;
      }
      if (index > state.fens.length - 1) {
        index = state.fens.length - 1;
      }
      state.currentIndex = index;
      if (state.board) {
        state.board.position(state.fens[state.currentIndex], true);
      }
      highlightMove(state.currentIndex - 1);
    }

    function stepNext() {
      if (state.currentIndex >= state.fens.length - 1) {
        stopPlayback();
        updateControls();
        return;
      }
      setIndex(state.currentIndex + 1);
    }

    function stepPrev() {
      setIndex(state.currentIndex - 1);
    }

    function startPlayback() {
      var live = typeof state.isLive === 'function' ? state.isLive() : false;
      if (live || !state.moves.length) {
        return;
      }
      if (state.currentIndex >= state.fens.length - 1) {
        setIndex(0);
      }
      state.isPlaying = true;
      updateControls();
      state.playTimer = setInterval(function() {
        stepNext();
      }, state.playDelay);
    }

    prevBtn.addEventListener('click', function() {
      stopPlayback();
      stepPrev();
      updateControls();
    });

    nextBtn.addEventListener('click', function() {
      stopPlayback();
      stepNext();
      updateControls();
    });

    playBtn.addEventListener('click', function() {
      startPlayback();
    });

    pauseBtn.addEventListener('click', function() {
      stopPlayback();
      updateControls();
    });

    function setMoves(moves) {
      state.moves = moves.slice();
      var built = buildFromMoves(state.startFen, state.moves);
      state.fens = built.fens;
      state.prettyMoves = built.prettyMoves;
      renderMoves();
      if (!state.isPlaying) {
        setIndex(state.fens.length - 1);
      }
      updateControls();
    }

    function recordMove(moveInput) {
      if (!moveInput) {
        return;
      }
      var moveStr = typeof moveInput === 'string' ? moveInput : moveInput.iccs;
      if (!moveStr) {
        return;
      }
      var last = state.moves[state.moves.length - 1];
      if (last === moveStr) {
        return;
      }
      var nextMoves = state.moves.slice();
      nextMoves.push(moveStr);
      setMoves(nextMoves);
    }

    function syncMoves(url) {
      if (!url) {
        return;
      }
      fetch(url, { credentials: 'same-origin' })
        .then(function(response) {
          if (!response.ok) {
            throw new Error('Failed to fetch moves');
          }
          return response.json();
        })
        .then(function(data) {
          if (!Array.isArray(data)) {
            return;
          }
          if (data.length === state.moves.length && data[data.length - 1] === state.moves[state.moves.length - 1]) {
            updateControls();
            return;
          }
          setMoves(data);
        })
        .catch(function() {
          updateControls();
        });
    }

    updateControls();

    return {
      setMoves: setMoves,
      recordMove: recordMove,
      syncMoves: syncMoves,
      setIndex: setIndex,
      updateControls: updateControls,
      stopPlayback: stopPlayback
    };
  }

  window.KyPho = {
    initLocal: function(options) {
      return createKyPho(options);
    },
    initRoom: function(options) {
      return createKyPho(options);
    }
  };
})();
