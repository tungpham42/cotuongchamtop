importScripts("/js/xiangqi.js");
importScripts("/js/ai/openingBook.js");
importScripts("/js/ai/midgameBook.js");
importScripts("/js/ai/endgameDatabase.js");

// ==================== CONSTANTS AND CONFIG ====================
const PHASE_WEIGHTS = {
  opening: { material: 0.6, positional: 0.4, tactical: 0.2 },
  midgame: { material: 0.4, positional: 0.3, tactical: 0.3 },
  endgame: { material: 0.3, positional: 0.2, tactical: 0.5 },
};

const PIECE_VALUE_MULTIPLIERS = {
  p: { opening: 1.0, midgame: 1.0, endgame: 1.8 }, // Pawn
  n: { opening: 0.8, midgame: 1.2, endgame: 1.0 }, // Knight
  c: { opening: 1.0, midgame: 1.3, endgame: 1.0 }, // Cannon
  r: { opening: 1.0, midgame: 1.0, endgame: 1.5 }, // Rook
};

// ==================== PIECE EVALUATION TABLES ====================
const PIECE_EVAL = {
  pawn: {
    red: [
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
    ],
    black: null, // Initialized later
  },
  rook: {
    red: [
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
    ],
    black: null,
  },
  knight: {
    red: [
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
    ],
    black: null,
  },
  cannon: {
    red: [
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
    ],
    black: null,
  },
  bishop: {
    red: [
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
    ],
    black: null,
  },
  advisor: {
    red: [
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
    ],
    black: null,
  },
  king: {
    red: [
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
    ],
    black: null,
  },
};

// Utility function to reverse arrays
function reverseArray(array) {
  return array.slice().reverse();
}

// Initialize black evaluation tables
function initializeBlackTables() {
  Object.keys(PIECE_EVAL).forEach((piece) => {
    PIECE_EVAL[piece].black = reverseArray(PIECE_EVAL[piece].red);
  });
}

// ==================== GAME STATE MANAGEMENT ====================
class GameAnalyzer {
  constructor() {
    this.evaluationCache = new Map();
    this.positionCount = 0;
    this.transpositionTable = new Map();
  }

  resetCounters() {
    this.positionCount = 0;
  }

  recordEvaluation(fen, score) {
    this.evaluationCache.set(fen, score);
  }

  getCachedEvaluation(fen) {
    return this.evaluationCache.get(fen);
  }
}

// ==================== BOARD EVALUATION ====================
class BoardEvaluator {
  static countPieces(board) {
    let count = 0;
    for (let row of board) {
      for (let piece of row) {
        if (piece !== null) count++;
      }
    }
    return count;
  }

  static determineGamePhase(pieceCount) {
    if (pieceCount > 28) return "opening";
    if (pieceCount > 15) return "midgame";
    return "endgame";
  }

  static evaluateKingSafety(board) {
    let safetyScore = 0;

    board.forEach((row, i) => {
      row.forEach((piece, j) => {
        if (piece?.type === "k") {
          const rankFactor = piece.color === "r" ? -i * 0.5 : (9 - i) * 0.5;
          safetyScore += rankFactor;
        }
      });
    });

    return safetyScore;
  }

  static getPieceValue(type, color, x, y, gamePhase) {
    const pieceTypeMap = {
      p: "pawn",
      r: "rook",
      n: "knight",
      c: "cannon",
      b: "bishop",
      a: "advisor",
      k: "king",
    };

    const mappedType = pieceTypeMap[type];
    const table = PIECE_EVAL[mappedType][color === "r" ? "red" : "black"];
    const baseValue = table[x][y];
    const phaseMultiplier = PIECE_VALUE_MULTIPLIERS[type]?.[gamePhase] || 1;

    return baseValue * phaseMultiplier * (color === "r" ? 1 : -1);
  }

  static evaluateBoard(board, game, analyzer) {
    const fen = game.fen();
    const cachedScore = analyzer.getCachedEvaluation(fen);
    if (cachedScore !== undefined) return cachedScore;

    const pieceCount = this.countPieces(board);
    const gamePhase = this.determineGamePhase(pieceCount);
    const weights = PHASE_WEIGHTS[gamePhase];

    let materialScore = 0;
    let positionalScore = 0;
    let tacticalScore = this.evaluateKingSafety(board);

    board.forEach((row, i) => {
      row.forEach((piece, j) => {
        if (piece) {
          materialScore += this.getPieceValue(
            piece.type,
            piece.color,
            i,
            j,
            gamePhase
          );
          // positionalScore could be expanded here
        }
      });
    });

    const totalScore =
      materialScore * weights.material +
      positionalScore * weights.positional +
      tacticalScore * weights.tactical;

    analyzer.recordEvaluation(fen, totalScore);
    return totalScore;
  }
}

// ==================== SEARCH ALGORITHMS ====================
class GameSearcher {
  static minimaxRoot(depth, game, isMaximizing, analyzer) {
    const fen = game.fen().split(" ")[0];
    const pieceCount = BoardEvaluator.countPieces(game.board());

    // Check opening book first
    const openingMove = getOpeningMove(fen);
    if (openingMove) return openingMove;

    // Check midgame book if in midgame phase
    if (pieceCount <= 28 && pieceCount > 15) {
      const midgameMove = getMidgameMove(fen);
      if (midgameMove) return midgameMove;
    }

    // Check endgame database
    const endgameMove = getEndgameMove(fen);
    if (endgameMove) return endgameMove;

    // Regular search if no book moves found
    const moves = game.ugly_moves();
    let bestMove = null;
    let bestValue = -Infinity;

    for (const move of moves) {
      game.ugly_move(move);
      const value = this.minimax(
        depth - 1,
        game,
        -Infinity,
        Infinity,
        !isMaximizing,
        analyzer
      );
      game.undo();

      if (value > bestValue) {
        bestValue = value;
        bestMove = move;
      }
    }

    return bestMove;
  }

  static minimax(depth, game, alpha, beta, isMaximizing, analyzer) {
    analyzer.positionCount++;

    if (depth === 0) {
      return -BoardEvaluator.evaluateBoard(game.board(), game, analyzer);
    }

    const moves = game.ugly_moves();
    let bestValue = isMaximizing ? -Infinity : Infinity;

    for (const move of moves) {
      game.ugly_move(move);
      const value = this.minimax(
        depth - 1,
        game,
        alpha,
        beta,
        !isMaximizing,
        analyzer
      );
      game.undo();

      if (isMaximizing) {
        bestValue = Math.max(bestValue, value);
        alpha = Math.max(alpha, bestValue);
      } else {
        bestValue = Math.min(bestValue, value);
        beta = Math.min(beta, bestValue);
      }

      if (beta <= alpha) break;
    }

    return bestValue;
  }
}

// ==================== UTILITY FUNCTIONS ====================
function reverseArray(array) {
  return array.slice().reverse();
}

// ==================== MAIN AI CONTROLLER ====================
class XiangqiAI {
  constructor() {
    this.analyzer = new GameAnalyzer();
    initializeBlackTables();
  }

  getBestMove(fen, depth) {
    const game = new Xiangqi();
    game.load(fen);
    this.analyzer.resetCounters();

    // Check for book moves first
    const simplifiedFen = fen.split(" ")[0];
    const pieceCount = BoardEvaluator.countPieces(game.board());

    // Opening book
    const openingMove = getOpeningMove(simplifiedFen);
    if (openingMove) return openingMove;

    // Midgame book
    if (pieceCount <= 28 && pieceCount > 15) {
      const midgameMove = getMidgameMove(simplifiedFen);
      if (midgameMove) return midgameMove;
    }

    // Endgame database
    const endgameMove = getEndgameMove(simplifiedFen);
    if (endgameMove) return endgameMove;

    // Perform search if no book moves found
    return GameSearcher.minimaxRoot(depth, game, true, this.analyzer);
  }
}

// ==================== WORKER INITIALIZATION ====================
const aiEngine = new XiangqiAI();

onmessage = function (e) {
  const { fen, depth } = e.data;
  const bestMove = aiEngine.getBestMove(fen, depth);
  postMessage(bestMove);
};
