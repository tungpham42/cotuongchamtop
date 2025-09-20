importScripts('/js/xiangqi.js');

function minimaxRoot(depth, game, isMaximisingPlayer) {
  var newGameMoves = game.ugly_moves();
  var bestMove = -9999;
  var bestMoveFound;

  for(var i = 0; i < newGameMoves.length; i++) {
    var newGameMove = newGameMoves[i]
    game.ugly_move(newGameMove);
    var value = minimax(depth - 1, game, -10000, 10000, !isMaximisingPlayer);
    game.undo();
    if(value >= bestMove) {
      bestMove = value;
      bestMoveFound = newGameMove;
    }
  }
  return bestMoveFound;
}

function minimax(depth, game, alpha, beta, isMaximisingPlayer) {
  positionCount++;
  if (depth === 0) {
    return -evaluateBoard(game.board());
  }

  var newGameMoves = game.ugly_moves();

  if (isMaximisingPlayer) {
    var bestMove = -9999;
    for (var i = 0; i < newGameMoves.length; i++) {
      game.ugly_move(newGameMoves[i]);
      bestMove = Math.max(bestMove, minimax(depth - 1, game, alpha, beta, !isMaximisingPlayer));
      game.undo();
      alpha = Math.max(alpha, bestMove);
      if (beta <= alpha) {
        return bestMove;
      }
    }
    return bestMove;
  } else {
    var bestMove = 9999;
    for (var i = 0; i < newGameMoves.length; i++) {
      game.ugly_move(newGameMoves[i]);
      bestMove = Math.min(bestMove, minimax(depth - 1, game, alpha, beta, !isMaximisingPlayer));
      game.undo();
      beta = Math.min(beta, bestMove);
      if (beta <= alpha) {
        return bestMove;
      }
    }
    return bestMove;
  }
}

function evaluateBoard(board) {
  var totalEvaluation = 0;
  for (var i = 0; i < 10; i++) {
    for (var j = 0; j < 9; j++) {
      totalEvaluation = totalEvaluation + getPieceValue(board[i][j], i ,j);
    }
  }
  return totalEvaluation;
}

function reverseArray(array) {
  return array.slice().reverse();
}

var pEvalRed =
[
[10.0,  10.0,  10.0,  10.0,  10.0,  10.0,  10.0,  10.0, 10.0],
[10.0,  10.0,  11.0,  15.0,  20.0,  15.0,  11.0,  10.0, 10.0],
[8.0,  10.0,  11.0,  15.0,  15.0,  15.0,  11.0,  10.0, 8.0],
[7.0,  9.0,  10.0,  11.0,  11.0,  11.0,  10.0,  9.0, 7.0],
[6.0,  8.0,  9.0,  10.0,  10.0,  10.0,  9.0,  8.0, 6.0],
[1.0,  2.0,  3.0,  4.0,  4.0,  4.0,  3.0,  2.0, 1.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0]
];

var pEvalBlack = reverseArray(pEvalRed);

var rEvalRed =
[
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[-2.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, -2.0],

];

var rEvalBlack = reverseArray(rEvalRed);

var nEvalRed =
[
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  -2.0,  0.0,  0.0,  0.0,  0.0,  0.0,  -2.0, 0.0],

];

var nEvalBlack = reverseArray(nEvalRed);

var cEvalRed =
[
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],

];

var cEvalBlack = reverseArray(cEvalRed);

var bEvalRed =
[
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  2.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],

];

var bEvalBlack = reverseArray(bEvalRed);

var aEvalRed =
[
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  2.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],

];

var aEvalBlack = reverseArray(aEvalRed);

var kEvalRed =
[
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  2.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  2.0,  0.0,  0.0,  0.0, 0.0],
[0.0,  0.0,  0.0,  0.0,  2.0,  0.0,  0.0,  0.0, 0.0],

];

var kEvalBlack = reverseArray(kEvalRed);

function getPieceValue(piece, x, y) {
  if (piece === null) {
    return 0;
  }
  var getAbsoluteValue = function (piece, isRed, x ,y) {
    if (piece.type === 'p') { //PAWN
      return 100 + ( isRed ? pEvalRed[x][y] : pEvalBlack[x][y] );
    } else if (piece.type === 'r') { //ROOK/CHARIOT
      return 1000 +( isRed ? rEvalRed[x][y] : rEvalBlack[x][y] );
    } else if (piece.type === 'c') { //CANNON
      return 400 +( isRed ? cEvalRed[x][y] : cEvalBlack[x][y] );
    } else if (piece.type === 'n') { // HORSE
      return 500 +( isRed ? nEvalRed[x][y] : nEvalBlack[x][y] );
    } else if (piece.type === 'b') { // ELEPHANT
      return 300 +( isRed ? bEvalRed[x][y] : bEvalBlack[x][y] );
    } else if (piece.type === 'a') { // ADVISOR
      return 800 +( isRed ? aEvalRed[x][y] : aEvalBlack[x][y] );
    } else if (piece.type === 'k') { // KING
      return 10000000 +( isRed ? kEvalRed[x][y] : kEvalBlack[x][y] );
    }
    throw "Unknown piece type: " + piece.type;
  };

  var absoluteValue = getAbsoluteValue(piece, piece.color === 'r', x ,y);
  return piece.color === 'r' ? absoluteValue : -absoluteValue;
}
  
var positionCount;
function getBestMove(fen, depth) {
    var game = new Xiangqi();
    game.load(fen);
    positionCount = 0;
    var bestMove = minimaxRoot(depth, game, true);
    console.log(bestMove);
    return bestMove;
}

onmessage = function (e) {
    fen = e.data.fen;
    depth = e.data.depth;
    var bestMove = getBestMove(fen, depth);
    console.log(bestMove);
    postMessage(bestMove);
}