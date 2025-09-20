// bot_worker.js - Enhanced Xiangqi AI with Opening Book and Endgame Database
importScripts("/js/xiangqi.js");
importScripts("/js/openingBook.js");
importScripts("/js/endgameDatabase.js");

function reverseArray(array) {
  return array.slice().reverse();
}

// Enhanced Piece Evaluation Tables
const pEvalRed = [
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [5.0, 5.0, 5.0, 5.0, 5.0, 5.0, 5.0, 5.0, 5.0],
  [6.0, 6.0, 7.0, 8.0, 10.0, 8.0, 7.0, 6.0, 6.0],
  [7.0, 7.0, 8.0, 9.0, 12.0, 9.0, 8.0, 7.0, 7.0],
  [8.0, 8.0, 9.0, 10.0, 15.0, 10.0, 9.0, 8.0, 8.0],
  [10.0, 10.0, 12.0, 15.0, 20.0, 15.0, 12.0, 10.0, 10.0],
  [15.0, 15.0, 18.0, 20.0, 25.0, 20.0, 18.0, 15.0, 15.0],
  [20.0, 20.0, 22.0, 25.0, 30.0, 25.0, 22.0, 20.0, 20.0],
  [25.0, 25.0, 27.0, 30.0, 35.0, 30.0, 27.0, 25.0, 25.0],
  [30.0, 30.0, 32.0, 35.0, 40.0, 35.0, 32.0, 30.0, 30.0],
];

const pEvalBlack = reverseArray(pEvalRed);

const rEvalRed = [
  [9.0, 8.0, 7.0, 6.0, 5.0, 6.0, 7.0, 8.0, 9.0],
  [9.5, 8.5, 7.5, 6.5, 5.5, 6.5, 7.5, 8.5, 9.5],
  [10.0, 9.0, 8.0, 7.0, 6.0, 7.0, 8.0, 9.0, 10.0],
  [10.5, 9.5, 8.5, 7.5, 6.5, 7.5, 8.5, 9.5, 10.5],
  [11.0, 10.0, 9.0, 8.0, 7.0, 8.0, 9.0, 10.0, 11.0],
  [11.5, 10.5, 9.5, 8.5, 7.5, 8.5, 9.5, 10.5, 11.5],
  [12.0, 11.0, 10.0, 9.0, 8.0, 9.0, 10.0, 11.0, 12.0],
  [12.5, 11.5, 10.5, 9.5, 8.5, 9.5, 10.5, 11.5, 12.5],
  [13.0, 12.0, 11.0, 10.0, 9.0, 10.0, 11.0, 12.0, 13.0],
  [13.5, 12.5, 11.5, 10.5, 9.5, 10.5, 11.5, 12.5, 13.5],
];

const rEvalBlack = reverseArray(rEvalRed);

const nEvalRed = [
  [4.0, 5.0, 6.0, 5.0, 4.0, 5.0, 6.0, 5.0, 4.0],
  [5.0, 6.0, 7.0, 6.0, 5.0, 6.0, 7.0, 6.0, 5.0],
  [6.0, 7.0, 8.0, 7.0, 6.0, 7.0, 8.0, 7.0, 6.0],
  [5.0, 6.0, 7.0, 8.0, 7.0, 8.0, 7.0, 6.0, 5.0],
  [4.0, 5.0, 6.0, 7.0, 8.0, 7.0, 6.0, 5.0, 4.0],
  [3.0, 4.0, 5.0, 6.0, 7.0, 6.0, 5.0, 4.0, 3.0],
  [2.0, 3.0, 4.0, 5.0, 6.0, 5.0, 4.0, 3.0, 2.0],
  [1.0, 2.0, 3.0, 4.0, 5.0, 4.0, 3.0, 2.0, 1.0],
  [0.0, 1.0, 2.0, 3.0, 4.0, 3.0, 2.0, 1.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
];

const nEvalBlack = reverseArray(nEvalRed);

const cEvalRed = [
  [7.0, 7.0, 7.0, 7.0, 7.0, 7.0, 7.0, 7.0, 7.0],
  [7.0, 8.0, 8.0, 8.0, 8.0, 8.0, 8.0, 8.0, 7.0],
  [7.0, 8.0, 9.0, 9.0, 9.0, 9.0, 9.0, 8.0, 7.0],
  [7.0, 8.0, 9.0, 10.0, 10.0, 10.0, 9.0, 8.0, 7.0],
  [7.0, 8.0, 9.0, 10.0, 11.0, 10.0, 9.0, 8.0, 7.0],
  [6.0, 7.0, 8.0, 9.0, 10.0, 9.0, 8.0, 7.0, 6.0],
  [5.0, 6.0, 7.0, 8.0, 9.0, 8.0, 7.0, 6.0, 5.0],
  [4.0, 5.0, 6.0, 7.0, 8.0, 7.0, 6.0, 5.0, 4.0],
  [3.0, 4.0, 5.0, 6.0, 7.0, 6.0, 5.0, 4.0, 3.0],
  [2.0, 3.0, 4.0, 5.0, 6.0, 5.0, 4.0, 3.0, 2.0],
];

const cEvalBlack = reverseArray(cEvalRed);

const bEvalRed = [
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 3.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 4.0, 0.0, 0.0, 0.0, 0.0],
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
  [0.0, 0.0, 0.0, 0.0, 5.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 6.0, 0.0, 0.0, 0.0, 0.0],
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
  [0.0, 0.0, 0.0, 0.0, 10.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 15.0, 0.0, 0.0, 0.0, 0.0],
  [0.0, 0.0, 0.0, 0.0, 20.0, 0.0, 0.0, 0.0, 0.0],
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

// Evaluation cache
const evaluationCache = new Map();

// Game phase detection
function countPieces(board) {
  let count = 0;
  for (let i = 0; i < 10; i++) {
    for (let j = 0; j < 9; j++) {
      if (board[i][j] !== null) count++;
    }
  }
  return count;
}

function determineGamePhase(pieceCount) {
  if (pieceCount > 28) return "opening";
  if (pieceCount > 15) return "midgame";
  return "endgame";
}

// Enhanced evaluation functions
function evaluatePinsAndForks(game) {
  // Simplified implementation - expand with actual pin/fork detection
  return 0;
}

function evaluatePawnStructure(board) {
  let score = 0;
  // Add pawn structure evaluation logic
  return score;
}

function evaluateKingSafety(board) {
  let redKingSafety = 0;
  let blackKingSafety = 0;

  for (let i = 0; i < 10; i++) {
    for (let j = 0; j < 9; j++) {
      const piece = board[i][j];
      if (piece && piece.type === "k") {
        if (piece.color === "r") {
          redKingSafety -= i * 0.5;
        } else {
          blackKingSafety += (9 - i) * 0.5;
        }
      }
    }
  }

  return redKingSafety + blackKingSafety;
}

function getPositionalBonus(type, color, x, y, gamePhase) {
  // Add specific positional bonuses
  return 0;
}

function getPieceValue(type, color, x, y, gamePhase) {
  const baseValue = PIECE_VALUES[type][color][x][y];
  let phaseMultiplier = 1;

  switch (type) {
    case "p":
      phaseMultiplier = gamePhase === "endgame" ? 1.8 : 1.0;
      break;
    case "n":
      phaseMultiplier =
        gamePhase === "opening" ? 0.8 : gamePhase === "midgame" ? 1.2 : 1.0;
      break;
    case "c":
      phaseMultiplier = gamePhase === "midgame" ? 1.3 : 1.0;
      break;
    case "r":
      phaseMultiplier = gamePhase === "endgame" ? 1.5 : 1.0;
      break;
  }

  return baseValue * phaseMultiplier * (color === "red" ? 1 : -1);
}

function evaluateBoard(board, game) {
  const fen = game.fen();
  if (evaluationCache.has(fen)) {
    return evaluationCache.get(fen);
  }

  const pieceCount = countPieces(board);
  const gamePhase = determineGamePhase(pieceCount);

  let materialScore = 0;
  let positionalScore = 0;
  let tacticalScore = 0;

  for (let i = 0; i < 10; i++) {
    for (let j = 0; j < 9; j++) {
      const piece = board[i][j];
      if (piece !== null) {
        const color = piece.color === "r" ? "red" : "black";
        materialScore += getPieceValue(piece.type, color, i, j, gamePhase);
        positionalScore += getPositionalBonus(
          piece.type,
          color,
          i,
          j,
          gamePhase
        );
      }
    }
  }

  tacticalScore += evaluatePinsAndForks(game);
  tacticalScore += evaluatePawnStructure(board);
  tacticalScore += evaluateKingSafety(board);

  let totalScore = 0;
  if (gamePhase === "opening") {
    totalScore =
      materialScore * 0.6 + positionalScore * 0.4 + tacticalScore * 0.2;
  } else if (gamePhase === "midgame") {
    totalScore =
      materialScore * 0.4 + positionalScore * 0.3 + tacticalScore * 0.3;
  } else {
    totalScore =
      materialScore * 0.3 + positionalScore * 0.2 + tacticalScore * 0.5;
  }

  evaluationCache.set(fen, totalScore);
  return totalScore;
}

// Minimax algorithm with alpha-beta pruning
let positionCount;

function minimaxRoot(depth, game, isMaximisingPlayer) {
  const fen = game.fen().split(" ")[0];

  const openingMove = getOpeningMove(fen);
  if (openingMove) {
    console.log("Using opening book move");
    return openingMove;
  }

  const endgameMove = getEndgameMove(fen);
  if (endgameMove) {
    console.log("Using endgame database move");
    return endgameMove;
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

  return bestMoveFound;
}

function minimax(depth, game, alpha, beta, isMaximisingPlayer) {
  positionCount++;
  if (depth === 0) {
    return -evaluateBoard(game.board(), game);
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

// Main function to get the best move
function getBestMove(fen, depth) {
  const game = new Xiangqi();
  game.load(fen);
  positionCount = 0;

  const simplifiedFen = fen.split(" ")[0];
  const openingMove = getOpeningMove(simplifiedFen);
  if (openingMove) return openingMove;

  const endgameMove = getEndgameMove(simplifiedFen);
  if (endgameMove) return endgameMove;

  const bestMove = minimaxRoot(depth, game, true);
  return bestMove;
}

// Web Worker message handler
onmessage = function (e) {
  const { fen, depth } = e.data;
  const bestMove = getBestMove(fen, depth);
  postMessage(bestMove);
};
