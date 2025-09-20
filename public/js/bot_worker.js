importScripts("/js/xiangqi.js");

function reverseArray(array) {
  return array.slice().reverse();
}

const pEvalRed = [
  [10.0, 10.0, 10.0, 10.0, 10.0, 10.0, 10.0, 10.0, 10.0],
  [10.0, 10.0, 11.0, 15.0, 20.0, 15.0, 11.0, 10.0, 10.0],
  [8.0, 10.0, 11.0, 15.0, 15.0, 15.0, 11.0, 10.0, 8.0],
  [7.0, 9.0, 10.0, 11.0, 11.0, 11.0, 10.0, 9.0, 7.0],
  [6.0, 8.0, 9.0, 10.0, 10.0, 10.0, 9.0, 8.0, 6.0],
  [1.0, 2.0, 3.0, 4.0, 4.0, 4.0, 3.0, 2.0, 1.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
];

const pEvalBlack = reverseArray(pEvalRed);

const rEvalRed = [
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [-2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, -2.0],
];

const rEvalBlack = reverseArray(rEvalRed);

const nEvalRed = [
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, -2.0, 0.0, 0.0, 0.0, 0.0, 0.0, -2.0, 0.0],
];

const nEvalBlack = reverseArray(nEvalRed);

const cEvalRed = [
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
];

const cEvalBlack = reverseArray(cEvalRed);

const bEvalRed = [
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 2.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
];

const bEvalBlack = reverseArray(bEvalRed);

const aEvalRed = [
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 2.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
];

const aEvalBlack = reverseArray(aEvalRed);

const kEvalRed = [
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 2.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 2.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 2.0, 0.0, 0.0, 0.0, 0.0],
];

const kEvalBlack = reverseArray(kEvalRed);

const PIECE_VALUES = {
  p: { red: pEvalRed, black: pEvalBlack },
  r: { red: rEvalRed, black: rEvalBlack },
  n: { red: nEvalRed, black: nEvalBlack },
  c: { red: cEvalRed, black: cEvalBlack },
  b: { red: bEvalRed, black: bEvalBlack },
  a: { red: aEvalRed, black: aEvalBlack },
  k: { red: kEvalRed, black: kEvalBlack },
};

// Xiangqi Opening Book with moves for both Red and Black
const openingBook = {
  // Red: Central Cannon (中炮), Black: Screen Horse Defense (屏风马)
  "rnbakabnr/4c4/9/p1p1p1p1p/9/2P6/P1P1P1P1P/9/R1BAKABNR": {
    color: "r",
    from: 27, // Red Cannon moves to the center
    to: 43,
    flags: 1,
    piece: "c",
  },
  "rnbakabnr/9/4c4/p1p1p1p1p/9/2P6/P1P1P1P1P/9/R1BAKABNR": {
    color: "b",
    from: 62, // Black Horse moves to protect
    to: 47,
    flags: 1,
    piece: "n",
  },

  // Red: Central Cannon (中炮), Black: Single Horse Defense (单提马)
  "rnbakabnr/9/1c2c4/p1p1p1p1p/9/2P6/P1P1P1P1P/9/R1BAKABNR": {
    color: "r",
    from: 27, // Red Cannon moves to the center
    to: 43,
    flags: 1,
    piece: "c",
  },
  "rnbakabnr/4c4/9/p1p1p1p1p/9/2P6/P1P1P1P1P/9/R1BAKABNR": {
    color: "b",
    from: 62, // Black Horse moves forward
    to: 47,
    flags: 1,
    piece: "n",
  },

  // Red: Central Cannon (中炮), Black: Elephant Opening (象步局)
  "rnbakabnr/9/9/p1p1p1p1p/2c6/2P6/P1P1P1P1P/9/R1BAKABNR": {
    color: "r",
    from: 27, // Red Cannon moves to the center
    to: 43,
    flags: 1,
    piece: "c",
  },
  "rnbakabnr/9/9/p1p1p1p1p/2c6/2P6/P1P1P1P1P/9/R1BAKABNR": {
    color: "b",
    from: 64, // Black Elephant moves forward
    to: 82,
    flags: 1,
    piece: "e",
  },

  // Red: Right Flank Cannon (右炮), Black: Cross Palace Cannon (宫炮)
  "rnbakabnr/9/4c4/p1p1p1p1p/9/2P6/P1P1P1P1P/9/R1BAKABNR": {
    color: "r",
    from: 28, // Red Cannon moves to the right flank
    to: 43,
    flags: 1,
    piece: "c",
  },
  "rnbakabnr/4c4/9/p1p1p1p1p/9/2P6/P1P1P1P1P/9/R1BAKABNR": {
    color: "b",
    from: 27, // Black Cannon moves across the palace
    to: 43,
    flags: 1,
    piece: "c",
  },

  // Red: Pawn Opening (兵局), Black: Counter Central Cannon (反中炮)
  "rnbakabnr/9/9/p1p1p1p1p/2c6/2P6/P1P1P1P1P/9/R1BAKABNR": {
    color: "r",
    from: 48, // Red Pawn advances
    to: 67,
    flags: 1,
    piece: "p",
  },
  "rnbakabnr/9/4c4/p1p1p1p1p/9/2P6/P1P1P1P1P/9/R1BAKABNR": {
    color: "b",
    from: 27, // Black Cannon counters with Central Cannon
    to: 43,
    flags: 1,
    piece: "c",
  },

  // Red: Left Flank Cannon (左炮), Black: Horse Defends Right Flank (马护右炮)
  "rnbakabnr/9/9/p1p1p1p1p/2c6/2P6/P1P1P1P1P/9/R1BAKABNR": {
    color: "r",
    from: 29, // Red Cannon moves to the left flank
    to: 43,
    flags: 1,
    piece: "c",
  },
  "rnbakabnr/4c4/9/p1p1p1p1p/9/2P6/P1P1P1P1P/9/R1BAKABNR": {
    color: "b",
    from: 64, // Black Horse moves to defend the right flank
    to: 47,
    flags: 1,
    piece: "n",
  },

  // Red: Cannon vs. Horse Defense (马对炮), Black: Horse Advances
  "rnbakabnr/4c4/9/p1p1p1p1p/9/2P6/P1P1P1P1P/9/R1BAKABNR": {
    color: "r",
    from: 71, // Red Horse moves forward
    to: 52,
    flags: 1,
    piece: "n",
  },
  "rnbakabnr/4c4/9/p1p1p1p1p/9/2P6/P1P1P1P1P/9/R1BAKABNR": {
    color: "b",
    from: 27, // Black Cannon moves across the palace
    to: 43,
    flags: 1,
    piece: "c",
  },

  // Red: Cannon Crosses the Palace (炮进宫), Black: Right Flank Cannon (右炮)
  "rnbakabnr/4c4/9/p1p1p1p1p/9/2P6/P1P1P1P1P/9/R1BAKABNR": {
    color: "r",
    from: 27, // Red Cannon moves to cross the palace
    to: 43,
    flags: 1,
    piece: "c",
  },
  "rnbakabnr/9/4c4/p1p1p1p1p/9/2P6/P1P1P1P1P/9/R1BAKABNR": {
    color: "b",
    from: 28, // Black Cannon moves to the right flank
    to: 43,
    flags: 1,
    piece: "c",
  },

  // Red: Pawn Advances (兵进), Black: Elephant Opening (象局)
  "rnbakabnr/9/9/p1p1p1p1p/2c6/2P6/P1P1P1P1P/9/R1BAKABNR": {
    color: "r",
    from: 48, // Red Pawn advances
    to: 67,
    flags: 1,
    piece: "p",
  },
  "rnbakabnr/9/9/p1p1p1p1p/2c6/2P6/P1P1P1P1P/9/R1BAKABNR": {
    color: "b",
    from: 64, // Black Elephant moves forward
    to: 82,
    flags: 1,
    piece: "e",
  },

  // Red: Cross Cannon (交叉炮), Black: Right Flank Cannon (右炮)
  "rnbakabnr/9/4c4/p1p1p1p1p/9/2P6/P1P1P1P1P/9/R1BAKABNR": {
    color: "r",
    from: 29, // Red Cannon crosses the palace
    to: 43,
    flags: 1,
    piece: "c",
  },
  "rnbakabnr/9/4c4/p1p1p1p1p/9/2P6/P1P1P1P1P/9/R1BAKABNR": {
    color: "b",
    from: 28, // Black Cannon moves to the right flank
    to: 43,
    flags: 1,
    piece: "c",
  },

  // Add more FEN strings and corresponding moves here for both Red and Black...
};

// Function to get move from the opening book
function getOpeningMove(fen) {
  return openingBook[fen] || null;
}

function evaluateBoard(board) {
  let totalEvaluation = 0;
  for (let i = 0; i < 10; i++) {
    for (let j = 0; j < 9; j++) {
      const piece = board[i][j];
      if (piece !== null) {
        const color = piece.color === "r" ? "red" : "black";
        totalEvaluation += getPieceValue(piece.type, color, i, j);
      }
    }
  }
  return totalEvaluation;
}

function getPieceValue(type, color, x, y) {
  const pieceValue = PIECE_VALUES[type][color][x][y];
  return color === "red" ? pieceValue : -pieceValue;
}

function minimaxRoot(depth, game, isMaximisingPlayer) {
  const fen = game.fen();
  const openingMove = getOpeningMove(fen);

  if (openingMove) {
    console.log("Opening move found:", openingMove);
    return openingMove;
  }

  const newGameMoves = game.ugly_moves();
  let bestMove = -9999;
  let bestMoveFound;

  for (const newGameMove of newGameMoves) {
    game.ugly_move(newGameMove);
    const value = minimax(depth - 1, game, -10000, 10000, !isMaximisingPlayer);
    game.undo();
    if (value >= bestMove) {
      bestMove = value;
      bestMoveFound = newGameMove;
    }
  }
  console.log("bestMoveFound: " + bestMoveFound);
  return bestMoveFound;
}

function minimax(depth, game, alpha, beta, isMaximisingPlayer) {
  positionCount++;
  if (depth === 0) {
    return -evaluateBoard(game.board());
  }

  const newGameMoves = game.ugly_moves();
  let bestMove = isMaximisingPlayer ? -9999 : 9999;

  for (const newGameMove of newGameMoves) {
    game.ugly_move(newGameMove);
    const value = minimax(depth - 1, game, alpha, beta, !isMaximisingPlayer);
    game.undo();

    if (isMaximisingPlayer) {
      bestMove = Math.max(bestMove, value);
      alpha = Math.max(alpha, bestMove);
    } else {
      bestMove = Math.min(bestMove, value);
      beta = Math.min(beta, bestMove);
    }

    if (beta <= alpha) {
      return bestMove;
    }
  }
  return bestMove;
}

let positionCount;

function getBestMove(fen, depth) {
  const game = new Xiangqi();
  game.load(fen);
  positionCount = 0;
  const bestMove = minimaxRoot(depth, game, true);
  console.log(bestMove);
  return bestMove;
}

onmessage = function (e) {
  const { fen, depth } = e.data;
  const bestMove = getBestMove(fen, depth);
  console.log(bestMove);
  postMessage(bestMove);
};
