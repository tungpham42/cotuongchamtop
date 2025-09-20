<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\RoomController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
// Route::resource('rooms', 'RoomCrudController');
Route::post('/game/update-ratings', [GameController::class, 'updateRatings']);
Route::post('/fetchTitle', [
    "uses" => 'TitleController@fetchTitle',
    "as" => 'fetchTitle'
]);
Route::post('/getNewRoom', [
    "uses" => 'RoomController@getNewRoom',
    "as" => 'getNewRoom'
]);
Route::post('/getLatestRoom', [
    "uses" => 'RoomController@getLatestRoom',
    "as" => 'getLatestRoom'
]);
Route::post('/createRoom', [
    "uses" => 'RoomController@create',
    "as" => 'create'
]);
Route::post('/compete', [
    "uses" => 'RoomController@compete',
    "as" => 'compete'
]);
Route::post('/competeMail', [
    "uses" => 'MailController@competeMail',
    "as" => 'competeMail'
]);
Route::post('/joinRoom', [
    "uses" => 'RoomController@join',
    "as" => 'join'
]);
Route::post('/updateResult', [
    "uses" => 'RoomController@updateResult',
    "as" => 'updateResult'
]);
Route::post('/updateElo', [
    "uses" => 'RoomController@updateElo',
    "as" => 'updateElo'
]);
Route::post('/updateSideResult', [
    "uses" => 'RoomController@updateSideResult',
    "as" => 'updateSideResult'
]);
Route::post('/quickMatch', [
    "uses" => 'RoomController@quickMatch',
    "as" => 'quickMatch'
]);
Route::post('/getRoomIds', [
    "uses" => 'RoomController@getRoomIds',
    "as" => 'getRoomIds'
]);
Route::post('/updateOnlineStatus', [
    "uses" => 'UserController@updateOnlineStatus',
    "as" => 'updateOnlineStatus'
]);
Route::post('/renderPlayersTitle', [
    "uses" => 'UserController@renderPlayersTitle',
    "as" => 'renderPlayersTitle'
]);
Route::post('/updatePlayersStatus', [
    "uses" => 'UserController@updatePlayersStatus',
    "as" => 'updatePlayersStatus'
]);
Route::post('/getName', [
    "uses" => 'UserController@getName',
    "as" => 'getName'
]);
Route::post('/getNameEmail', [
    "uses" => 'UserController@getNameEmail',
    "as" => 'getNameEmail'
]);
Route::post('/getPoints', [
    "uses" => 'UserController@getPoints',
    "as" => 'getPoints'
]);
Route::post('/getWinMatchPoints', [
    "uses" => 'UserController@getWinMatchPoints',
    "as" => 'getWinMatchPoints'
]);
Route::post('/getLoseMatchPoints', [
    "uses" => 'UserController@getLoseMatchPoints',
    "as" => 'getLoseMatchPoints'
]);
Route::post('/getDrawMatchPoints', [
    "uses" => 'UserController@getDrawMatchPoints',
    "as" => 'getDrawMatchPoints'
]);
Route::post('/getTotalMatchPoints', [
    "uses" => 'UserController@getTotalMatchPoints',
    "as" => 'getTotalMatchPoints'
]);
Route::post('/updatePoints', [
    "uses" => 'UserController@updatePoints',
    "as" => 'updatePoints'
]);
Route::post('/getHostId', [
    "uses" => 'RoomController@getHostId',
    "as" => 'getHostId'
]);
Route::post('/hasRoomcode', [
    "uses" => 'RoomController@hasRoomcode',
    "as" => 'hasRoomcode'
]);
Route::get('/getPass/{code}', [
    "uses" => 'RoomController@getPass',
    "as" => 'getPass'
]);
Route::post('/changePass', [
    "uses" => 'RoomController@changePass',
    "as" => 'changePass'
]);
Route::post('/changePassJa', [
    "uses" => 'RoomController@changePassJa',
    "as" => 'changePassJa'
]);
Route::post('/changePassKo', [
    "uses" => 'RoomController@changePassKo',
    "as" => 'changePassKo'
]);
Route::post('/changePassZh', [
    "uses" => 'RoomController@changePassZh',
    "as" => 'changePassZh'
]);
Route::post('/doiPass', [
    "uses" => 'RoomController@doiPass',
    "as" => 'doiPass'
]);
Route::post('/updateFEN', [
    "uses" => 'RoomController@store',
    "as" => 'store'
]);
Route::get('/readFEN/{code}', [
    "uses" => 'RoomController@show',
    "as" => 'show'
]);
Route::get('/getFEN/{code}', [
    "uses" => 'RoomController@getEventStream',
    "as" => 'getEventStream'
]);
Route::post('/processMail', [
    "uses" => 'MailController@send',
    "as" => 'send'
]);
Route::post('/processMailJa', [
    "uses" => 'MailController@sendJa',
    "as" => 'sendJa'
]);
Route::post('/processMailKo', [
    "uses" => 'MailController@sendKo',
    "as" => 'sendKo'
]);
Route::post('/processMailZh', [
    "uses" => 'MailController@sendZh',
    "as" => 'sendZh'
]);
Route::post('/xulyMail', [
    "uses" => 'MailController@gui',
    "as" => 'gui'
]);
Route::post('/postChat', [
  "uses" => 'ChatController@post',
  "as" => 'post'
]);
Route::post('/postChatJa', [
  "uses" => 'ChatController@postJa',
  "as" => 'postJa'
]);
Route::post('/postChatKo', [
  "uses" => 'ChatController@postKo',
  "as" => 'postKo'
]);
Route::post('/postChatZh', [
  "uses" => 'ChatController@postZh',
  "as" => 'postZh'
]);
Route::post('/dangChat', [
  "uses" => 'ChatController@dang',
  "as" => 'dang'
]);

Route::post('/createPuzzle', [
    "uses" => 'PuzzleController@create',
    "as" => 'createPuzzle'
]);
Route::post('/checkUniqueName', [
    "uses" => 'PuzzleController@checkUniqueName',
    "as" => 'checkUniqueName'
]);
Route::post('/upvote', [
    "uses" => 'PuzzleController@upvote',
    "as" => 'upvote'
]);
Route::post('/downvote', [
    "uses" => 'PuzzleController@downvote',
    "as" => 'downvote'
]);
Route::post('/totalRating', [
    "uses" => 'PuzzleController@totalRating',
    "as" => 'totalRating'
]);