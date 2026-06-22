<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\XiangqiController;
use App\Http\Controllers\PuzzleController;
use App\Http\Controllers\Api\ChessAnalysisController;
use App\Http\Controllers\TitleController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChatController;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    // Trả về đúng định dạng JSON mà Flarum dễ đọc nhất
    return response()->json([
        'id' => $request->user()->id,
        'name' => $request->user()->name,
        'email' => $request->user()->email,
    ]);
});

Route::controller(ChessAnalysisController::class)->prefix('chess')->group(function () {
    Route::post('/analyze', 'analyze');
    Route::post('/chat', 'chat');
});

Route::controller(XiangqiController::class)->prefix('xiangqi')->group(function () {
    Route::post('/best-move', 'getBestMove');
    Route::post('/analyze', 'analyzePosition');
    Route::get('/status', 'getEngineStatus');
    Route::get('/health', 'healthCheck');
});

Route::post('/game/update-ratings', [GameController::class, 'updateRatings']);
Route::post('/fetchTitle', [TitleController::class, 'fetchTitle'])->name('fetchTitle');

Route::controller(RoomController::class)->group(function () {
    Route::post('/getNewRoom', 'getNewRoom')->name('getNewRoom');
    Route::post('/getLatestRoom', 'getLatestRoom')->name('getLatestRoom');
    Route::post('/createRoom', 'create')->name('create');
    Route::post('/compete', 'compete')->name('compete');
    Route::post('/joinRoom', 'join')->name('join');
    Route::post('/updateResult', 'updateResult')->name('updateResult');
    Route::post('/updateElo', 'updateElo')->name('updateElo');
    Route::post('/updateSideResult', 'updateSideResult')->name('updateSideResult');
    Route::post('/quickMatch', 'quickMatch')->name('quickMatch');
    Route::post('/getRoomIds', 'getRoomIds')->name('getRoomIds');
    Route::post('/getHostId', 'getHostId')->name('getHostId');
    Route::post('/hasRoomcode', 'hasRoomcode')->name('hasRoomcode');
    Route::get('/getPass/{code}', 'getPass')->name('getPass');
    Route::post('/changePass', 'changePass')->name('changePass');
    Route::post('/updateFEN', 'store')->name('store');
    Route::get('/readFEN/{code}', 'show')->name('show');
    Route::get('/readMoves/{code}', 'getMoves')->name('getMoves');
    Route::get('/getFEN/{code}', 'getEventStream')->name('getEventStream');
});

Route::controller(UserController::class)->group(function () {
    Route::post('/updateOnlineStatus', 'updateOnlineStatus')->name('updateOnlineStatus');
    Route::post('/renderPlayersTitle', 'renderPlayersTitle')->name('renderPlayersTitle');
    Route::post('/updatePlayersStatus', 'updatePlayersStatus')->name('updatePlayersStatus');
    Route::post('/getName', 'getName')->name('getName');
    Route::post('/getNameEmail', 'getNameEmail')->name('getNameEmail');
    Route::post('/getPoints', 'getPoints')->name('getPoints');
    Route::post('/getWinMatchPoints', 'getWinMatchPoints')->name('getWinMatchPoints');
    Route::post('/getLoseMatchPoints', 'getLoseMatchPoints')->name('getLoseMatchPoints');
    Route::post('/getDrawMatchPoints', 'getDrawMatchPoints')->name('getDrawMatchPoints');
    Route::post('/getTotalMatchPoints', 'getTotalMatchPoints')->name('getTotalMatchPoints');
    Route::post('/updatePoints', 'updatePoints')->name('updatePoints');
});

Route::controller(MailController::class)->group(function () {
    Route::post('/competeMail', 'competeMail')->name('competeMail');
    Route::post('/processMailEn', 'sendEn')->name('sendEn');
    Route::post('/processMailJa', 'sendJa')->name('sendJa');
    Route::post('/processMailKo', 'sendKo')->name('sendKo');
    Route::post('/processMailZh', 'sendZh')->name('sendZh');
    Route::post('/processMailVi', 'sendVi')->name('sendVi');
});

Route::controller(ChatController::class)->group(function () {
    Route::post('/postChatEn', 'postEn')->name('postEn');
    Route::post('/postChatJa', 'postJa')->name('postJa');
    Route::post('/postChatKo', 'postKo')->name('postKo');
    Route::post('/postChatZh', 'postZh')->name('postZh');
    Route::post('/postChatVi', 'postVi')->name('postVi');
});

Route::controller(PuzzleController::class)->group(function () {
    Route::post('/createPuzzle', 'create')->name('createPuzzle');
    Route::post('/checkUniqueName', 'checkUniqueName')->name('checkUniqueName');
    Route::post('/upvote', 'upvote')->name('upvote');
    Route::post('/downvote', 'downvote')->name('downvote');
    Route::post('/totalRating', 'totalRating')->name('totalRating');

    Route::prefix('puzzles')->group(function () {
        Route::post('/', 'create')->name('puzzles.create');
        Route::post('/check-name', 'checkUniqueName')->name('puzzles.check-name');
        Route::get('/{slug}/reactions', 'getReactions')->name('puzzles.reactions.show');
        Route::post('/{slug}/reactions', 'react')->name('puzzles.reactions.store');
        Route::get('/{slug}/comments', 'comments')->name('puzzles.comments.index');
        Route::post('/{slug}/comments', 'addComment')->name('puzzles.comments.store');
        Route::post('/{slug}/comments/{comment}/like', 'likeComment')->name('puzzles.comments.like');
    });
});
