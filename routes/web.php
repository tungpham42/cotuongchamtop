<?php

use App\Models\Room;
use App\Models\User;
use App\Models\Puzzle;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PuzzleController;
use App\Http\Controllers\TimerController;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Sitemap;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Route::view('/admin{any}', 'admin')
//     ->where('any', '.*');
$cdnUrl = "https://cotuong.r.worldssl.net"; // URL::to('')
$fenRegex = "[a-zA-Z0-9\-\/\s|&nbsp;]+";
// Common function to get random room

Route::get('/sitemap', function () {
  $sitemap = Sitemap::create();

  // Add all named routes to the sitemap except those with '/api' prefix and parameters
  collect(Route::getRoutes())->each(function ($route) use ($sitemap) {
    if ($route->getName() && strpos($route->uri(), '/api') !== 0 && count($route->signatureParameters()) === 0) {
      $sitemap->add(route($route->getName()));
    }
  });

  $sitemap->writeToFile(public_path('sitemap.xml'));
  
  return 'Sitemap generated!';
});
Route::get('/sitemap-the-co.xml', function() {
  return response()->view('sitemap-puzzle')->header('Content-Type', 'text/xml');
});
Route::post('/anonymous-quick-match', [RoomController::class, 'anonymousQuickMatch'])->name('anonymous-quick-match');
Route::get('/check-anonymous-match-status', [RoomController::class, 'checkAnonymousMatchStatus'])->name('check-anonymous-match-status');

Route::post('/anonymous-quick-match/en', [RoomController::class, 'anonymousQuickMatchEn'])->name('anonymous-quick-match-en');
Route::get('/check-anonymous-match-status/en', [RoomController::class, 'checkAnonymousMatchStatusEn'])->name('check-anonymous-match-status-en');

Route::post('/anonymous-quick-match/ja', [RoomController::class, 'anonymousQuickMatchJa'])->name('anonymous-quick-match-ja');
Route::get('/check-anonymous-match-status/ja', [RoomController::class, 'checkAnonymousMatchStatusJa'])->name('check-anonymous-match-status-ja');

Route::post('/anonymous-quick-match/ko', [RoomController::class, 'anonymousQuickMatchKo'])->name('anonymous-quick-match-ko');
Route::get('/check-anonymous-match-status/ko', [RoomController::class, 'checkAnonymousMatchStatusKo'])->name('check-anonymous-match-status-ko');

Route::post('/anonymous-quick-match/zh', [RoomController::class, 'anonymousQuickMatchZh'])->name('anonymous-quick-match-zh');
Route::get('/check-anonymous-match-status/zh', [RoomController::class, 'checkAnonymousMatchStatusZh'])->name('check-anonymous-match-status-zh');

// Test routes (no CSRF required)
Route::post('/test-anonymous-quick-match', [RoomController::class, 'anonymousQuickMatch'])->name('test-anonymous-quick-match');
Route::post('/test-check-anonymous-match-status', [RoomController::class, 'checkAnonymousMatchStatus'])->name('test-check-anonymous-match-status');

Route::get('/terms-and-conditions', function () {
  return view('terms', ['headTitle' => 'Terms and Conditions', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '', 'langEnUrl' => '/en', 'langJaUrl' => '/ja', 'langKoUrl' => '/ko', 'langZhUrl' => '/zh', 'canonicalUrl' => '/terms-and-conditions']);
});
Route::get('/privacy-policy', function () {
  return view('privacy', ['headTitle' => 'Privacy Policy', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '', 'langEnUrl' => '/en', 'langJaUrl' => '/ja', 'langKoUrl' => '/ko', 'langZhUrl' => '/zh', 'canonicalUrl' => '/privacy-policy']);
});

Route::get('/getUserPuzzlesTemplate', function(){
  return view('layout.partials.userPuzzles', ['userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()])->render();
});

Route::get('/forum', function () {
  return redirect('/sanh-cho', 301);
});
Route::get('/forum/', function () {
  return redirect('/sanh-cho', 301);
});
Route::get('/forum/{wildcard}', function ($wildcard) {
  return redirect('/sanh-cho', 301);
})->where(['wildcard' => ".*"]);

Route::get('/choi-co-tuong', function () {
  return redirect('', 301);
});
Route::get('/wp-admin', function () {
  return redirect('', 301);
});
Route::get('/wp-json', function () {
  return redirect('', 301);
});
Route::get('/cothe', function () {
  return redirect('/co-the', 301);
});
Route::get('/danh-sach-phong', function () {
  return redirect('/sanh-cho', 301);
});
Route::get('/phong', function () {
  return redirect('/sanh-cho', 301);
});
Route::get('/danh-sach', function () {
  return redirect('/sanh-cho', 301);
});
Route::get('/danhsach', function () {
  return redirect('/sanh-cho', 301);
});
Route::get('/setup', function () {
  return redirect('/puzzle', 301);
});
Route::get('/set-up', function () {
  return redirect('/puzzle', 301);
});
Route::get('/choi-voi-may/de-nhat', function () {
  return redirect('/de-nhat', 301);
});
Route::get('/choi-voi-may/moi-choi', function () {
  return redirect('/moi-choi', 301);
});
Route::get('/choi-voi-may/de', function () {
  return redirect('/de', 301);
});
Route::get('/choi-voi-may/binh-thuong', function () {
  return redirect('/binh-thuong', 301);
});
Route::get('/choi-voi-may/kho', function () {
  return redirect('/kho', 301);
});
Route::get('/choi-voi-may/kho-nhat', function () {
  return redirect('/kho-nhat', 301);
});
Route::get('/play-with-ai', function () {
  return redirect('/en', 301);
});
Route::get('/play-with-ai/easiest', function () {
  return redirect('/easiest', 301);
});
Route::get('/play-with-ai/newbie', function () {
  return redirect('/newbie', 301);
});
Route::get('/play-with-ai/easy', function () {
  return redirect('/easy', 301);
});
Route::get('/play-with-ai/normal', function () {
  return redirect('/normal', 301);
});
Route::get('/play-with-ai/hard', function () {
  return redirect('/hard', 301);
});
Route::get('/play-with-ai/hardest', function () {
  return redirect('/hardest', 301);
});

Route::match(['get', 'post'], '/phong/{code}', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('roomHost', ['headTitle' => ((null !== RoomController::getHostIdRoute($code)) ? 'Thi đấu - ' : '').'Chủ phòng - Phòng: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code, 'langEnUrl' => '/room/'.$code, 'langJaUrl' => '/rumu/'.$code, 'langKoUrl' => '/bang/'.$code, 'langZhUrl' => '/fangjian/'.$code, 'canonicalUrl' => '/phong/'.$code, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
});
Route::match(['get', 'post'], '/phong/{code}/khach', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('roomGuest', ['headTitle' => ((null !== RoomController::getHostIdRoute($code)) ? 'Thi đấu - ' : '').'Khách - Phòng: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/khach', 'langEnUrl' => '/room/'.$code.'/guest', 'langJaUrl' =>  '/rumu/'.$code.'/geesuto', 'langKoUrl' => '/bang/'.$code.'/bangmun', 'langZhUrl' => '/fangjian/'.$code.'/zhuke', 'canonicalUrl' => '/phong/'.$code.'/khach', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
});
Route::match(['get', 'post'], '/phong/{code}/ngau-nhien', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('roomRandom', ['headTitle' => 'Ngẫu nhiên - Phòng: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/ngau-nhien', 'langEnUrl' => '/room/'.$code.'/random', 'langJaUrl' =>  '/rumu/'.$code.'/randamu', 'langKoUrl' => '/bang/'.$code.'/mujag-wiui', 'langZhUrl' => '/fangjian/'.$code.'/suijide', 'canonicalUrl' => '/phong/'.$code.'/ngau-nhien', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
});
Route::match(['get', 'post'], '/phong/{code}/theo-doi', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('roomWatch', ['headTitle' => ((null !== RoomController::getHostIdRoute($code)) ? 'Thi đấu - ' : '').'Theo dõi - Phòng: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/theo-doi', 'langEnUrl' => '/room/'.$code.'/watch', 'langJaUrl' => '/rumu/'.$code.'/miru', 'langKoUrl' => '/bang/'.$code.'/boda', 'langZhUrl' => '/fangjian/'.$code.'/kan', 'canonicalUrl' => '/phong/'.$code.'/theo-doi', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
});
Route::match(['get', 'post'], '/phong/{code}/do', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('roomRed', ['headTitle' => 'Bên đỏ - Phòng: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/do', 'langEnUrl' => '/room/'.$code.'/red', 'langJaUrl' => '/rumu/'.$code.'/aka', 'langKoUrl' => '/bang/'.$code.'/ppalgan', 'langZhUrl' => '/fangjian/'.$code.'/hongse', 'canonicalUrl' => '/phong/'.$code.'/do', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
});
Route::match(['get', 'post'], '/phong/{code}/den', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('roomBlack', ['headTitle' => 'Bên đen - Phòng: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/den', 'langEnUrl' => '/room/'.$code.'/black', 'langJaUrl' => '/rumu/'.$code.'/kuro', 'langKoUrl' => '/bang/'.$code.'/geom-eunsaeg', 'langZhUrl' => '/fangjian/'.$code.'/heise', 'canonicalUrl' => '/phong/'.$code.'/den', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
});

Route::match(['get', 'post'], '/room/{code}', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('en/roomHost', ['headTitle' => 'Host - Room: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code, 'langEnUrl' => '/room/'.$code, 'langJaUrl' => '/rumu/'.$code, 'langKoUrl' => '/bang/'.$code, 'langZhUrl' => '/fangjian/'.$code, 'canonicalUrl' => '/room/'.$code]);
});
Route::match(['get', 'post'], '/room/{code}/guest', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('en/roomGuest', ['headTitle' => 'Guest - Room: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/khach', 'langEnUrl' => '/room/'.$code.'/guest', 'langJaUrl' =>  '/rumu/'.$code.'/geesuto', 'langKoUrl' => '/bang/'.$code.'/bangmun', 'langZhUrl' => '/fangjian/'.$code.'/zhuke', 'canonicalUrl' => '/room/'.$code.'/guest']);
});
Route::match(['get', 'post'], '/room/{code}/random', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('en/roomRandom', ['headTitle' => 'Random - Room: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/ngau-nhien', 'langEnUrl' => '/room/'.$code.'/random', 'langJaUrl' =>  '/rumu/'.$code.'/randamu', 'langKoUrl' => '/bang/'.$code.'/mujag-wiui', 'langZhUrl' => '/fangjian/'.$code.'/suijide', 'canonicalUrl' => '/room/'.$code.'/random']);
});
Route::match(['get', 'post'], '/room/{code}/watch', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('en/roomWatch', ['headTitle' => 'Watch - Room: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/theo-doi', 'langEnUrl' => '/room/'.$code.'/watch', 'langJaUrl' => '/rumu/'.$code.'/miru', 'langKoUrl' => '/bang/'.$code.'/boda', 'langZhUrl' => '/fangjian/'.$code.'/kan', 'canonicalUrl' => '/room/'.$code.'/watch']);
});
Route::match(['get', 'post'], '/room/{code}/red', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('en/roomRed', ['headTitle' => 'Red side - Room: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/do', 'langEnUrl' => '/room/'.$code.'/red', 'langJaUrl' => '/rumu/'.$code.'/aka', 'langKoUrl' => '/bang/'.$code.'/ppalgan', 'langZhUrl' => '/fangjian/'.$code.'/hongse', 'canonicalUrl' => '/room/'.$code.'/red']);
});
Route::match(['get', 'post'], '/room/{code}/black', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('en/roomBlack', ['headTitle' => 'Black side - Room: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/den', 'langEnUrl' => '/room/'.$code.'/black', 'langJaUrl' => '/rumu/'.$code.'/kuro', 'langKoUrl' => '/bang/'.$code.'/geom-eunsaeg', 'langZhUrl' => '/fangjian/'.$code.'/heise', 'canonicalUrl' => '/room/'.$code.'/black']);
});

Route::match(['get', 'post'], '/rumu/{code}', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('ja/roomHost', ['headTitle' => 'ホスト - ルーム: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code, 'langEnUrl' => '/room/'.$code, 'langJaUrl' => '/rumu/'.$code, 'langKoUrl' => '/bang/'.$code, 'langZhUrl' => '/fangjian/'.$code, 'canonicalUrl' => '/rumu/'.$code]);
});
Route::match(['get', 'post'], '/rumu/{code}/geesuto', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('ja/roomGuest', ['headTitle' => 'ゲスト - ルーム: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/khach', 'langEnUrl' => '/room/'.$code.'/guest', 'langJaUrl' =>  '/rumu/'.$code.'/geesuto', 'langKoUrl' => '/bang/'.$code.'/bangmun', 'langZhUrl' => '/fangjian/'.$code.'/zhuke', 'canonicalUrl' => '/rumu/'.$code.'/geesuto']);
});
Route::match(['get', 'post'], '/rumu/{code}/randamu', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('ja/roomRandom', ['headTitle' => 'ランダム - ルーム: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/ngau-nhien', 'langEnUrl' => '/room/'.$code.'/random', 'langJaUrl' =>  '/rumu/'.$code.'/randamu', 'langKoUrl' => '/bang/'.$code.'/mujag-wiui', 'langZhUrl' => '/fangjian/'.$code.'/suijide', 'canonicalUrl' => '/rumu/'.$code.'/randamu']);
});
Route::match(['get', 'post'], '/rumu/{code}/miru', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('ja/roomWatch', ['headTitle' => '見る - ルーム: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/theo-doi', 'langEnUrl' => '/room/'.$code.'/watch', 'langJaUrl' => '/rumu/'.$code.'/miru', 'langKoUrl' => '/bang/'.$code.'/boda', 'langZhUrl' => '/fangjian/'.$code.'/kan', 'canonicalUrl' => '/rumu/'.$code.'/miru']);
});
Route::match(['get', 'post'], '/rumu/{code}/aka', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('ja/roomRed', ['headTitle' => '赤 - ルーム: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/do', 'langEnUrl' => '/room/'.$code.'/red', 'langJaUrl' => '/rumu/'.$code.'/aka', 'langKoUrl' => '/bang/'.$code.'/ppalgan', 'langZhUrl' => '/fangjian/'.$code.'/hongse', 'canonicalUrl' => '/rumu/'.$code.'/aka']);
});
Route::match(['get', 'post'], '/rumu/{code}/kuro', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('ja/roomBlack', ['headTitle' => '黒 - ルーム: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/den', 'langEnUrl' => '/room/'.$code.'/black', 'langJaUrl' => '/rumu/'.$code.'/kuro', 'langKoUrl' => '/bang/'.$code.'/geom-eunsaeg', 'langZhUrl' => '/fangjian/'.$code.'/heise', 'canonicalUrl' => '/rumu/'.$code.'/kuro']);
});

Route::match(['get', 'post'], '/bang/{code}', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('ko/roomHost', ['headTitle' => '주인 - 방: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code, 'langEnUrl' => '/room/'.$code, 'langJaUrl' => '/rumu/'.$code, 'langKoUrl' => '/bang/'.$code, 'langZhUrl' => '/fangjian/'.$code, 'canonicalUrl' => '/bang/'.$code]);
});
Route::match(['get', 'post'], '/bang/{code}/bangmun', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('ko/roomGuest', ['headTitle' => '손님 - 방: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/khach', 'langEnUrl' => '/room/'.$code.'/guest', 'langJaUrl' =>  '/rumu/'.$code.'/geesuto', 'langKoUrl' => '/bang/'.$code.'/bangmun', 'langZhUrl' => '/fangjian/'.$code.'/zhuke', 'canonicalUrl' => '/bang/'.$code.'/bangmun']);
});
Route::match(['get', 'post'], '/bang/{code}/mujag-wiui', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('ko/roomRandom', ['headTitle' => '무작위의 - 방: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/ngau-nhien', 'langEnUrl' => '/room/'.$code.'/random', 'langJaUrl' =>  '/rumu/'.$code.'/randamu', 'langKoUrl' => '/bang/'.$code.'/mujag-wiui', 'langZhUrl' => '/fangjian/'.$code.'/suijide', 'canonicalUrl' => '/bang/'.$code.'/mujag-wiui']);
});
Route::match(['get', 'post'], '/bang/{code}/boda', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('ko/roomWatch', ['headTitle' => '보다 - 방: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/theo-doi', 'langEnUrl' => '/room/'.$code.'/watch', 'langJaUrl' => '/rumu/'.$code.'/miru', 'langKoUrl' => '/bang/'.$code.'/boda', 'langZhUrl' => '/fangjian/'.$code.'/kan', 'canonicalUrl' => '/bang/'.$code.'/boda']);
});
Route::match(['get', 'post'], '/bang/{code}/ppalgan', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('ko/roomRed', ['headTitle' => '빨간 - 방: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/do', 'langEnUrl' => '/room/'.$code.'/red', 'langJaUrl' => '/rumu/'.$code.'/aka', 'langKoUrl' => '/bang/'.$code.'/ppalgan', 'langZhUrl' => '/fangjian/'.$code.'/hongse', 'canonicalUrl' => '/bang/'.$code.'/ppalgan']);
});
Route::match(['get', 'post'], '/bang/{code}/geom-eunsaeg', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('ko/roomBlack', ['headTitle' => '검은색 - 방: '.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/den', 'langEnUrl' => '/room/'.$code.'/black', 'langJaUrl' => '/rumu/'.$code.'/kuro', 'langKoUrl' => '/bang/'.$code.'/geom-eunsaeg', 'langZhUrl' => '/fangjian/'.$code.'/heise', 'canonicalUrl' => '/bang/'.$code.'/geom-eunsaeg']);
});

Route::match(['get', 'post'], '/fangjian/{code}', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('zh/roomHost', ['headTitle' => '主办 - 房间：'.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code, 'langEnUrl' => '/room/'.$code, 'langJaUrl' => '/rumu/'.$code, 'langKoUrl' => '/bang/'.$code, 'langZhUrl' => '/fangjian/'.$code, 'canonicalUrl' => '/fangjian/'.$code]);
});
Route::match(['get', 'post'], '/fangjian/{code}/zhuke', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('zh/roomGuest', ['headTitle' => '客人 - 房间：'.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/khach', 'langEnUrl' => '/room/'.$code.'/guest', 'langJaUrl' =>  '/rumu/'.$code.'/geesuto', 'langKoUrl' => '/bang/'.$code.'/bangmun', 'langZhUrl' => '/fangjian/'.$code.'/zhuke', 'canonicalUrl' => '/fangjian/'.$code.'/zhuke']);
});
Route::match(['get', 'post'], '/fangjian/{code}/suijide', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('zh/roomRandom', ['headTitle' => '随机的 - 房间：'.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/ngau-nhien', 'langEnUrl' => '/room/'.$code.'/random', 'langJaUrl' =>  '/rumu/'.$code.'/randamu', 'langKoUrl' => '/bang/'.$code.'/mujag-wiui', 'langZhUrl' => '/fangjian/'.$code.'/suijide', 'canonicalUrl' => '/fangjian/'.$code.'/suijide']);
});
Route::match(['get', 'post'], '/fangjian/{code}/kan', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('zh/roomWatch', ['headTitle' => '看 - 房间：'.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/theo-doi', 'langEnUrl' => '/room/'.$code.'/watch', 'langJaUrl' => '/rumu/'.$code.'/miru', 'langKoUrl' => '/bang/'.$code.'/boda', 'langZhUrl' => '/fangjian/'.$code.'/kan', 'canonicalUrl' => '/fangjian/'.$code.'/kan']);
});
Route::match(['get', 'post'], '/fangjian/{code}/hongse', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('zh/roomRed', ['headTitle' => '红方 - 房间：'.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/do', 'langEnUrl' => '/room/'.$code.'/red', 'langJaUrl' => '/rumu/'.$code.'/aka', 'langKoUrl' => '/bang/'.$code.'/ppalgan', 'langZhUrl' => '/fangjian/'.$code.'/hongse', 'canonicalUrl' => '/fangjian/'.$code.'/hongse']);
});
Route::match(['get', 'post'], '/fangjian/{code}/heise', function($code) {
  $room = Room::firstWhere('code', $code);
  if (!$room) {
    abort(404);
  }
  return view('zh/roomBlack', ['headTitle' => '黑边 - 房间：'.RoomController::getRoomName($code), 'bodyClass' => 'room', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => $code, 'room' => $room, 'cdnUrl' => URL::to(''), 'langViUrl' => '/phong/'.$code.'/den', 'langEnUrl' => '/room/'.$code.'/black', 'langJaUrl' => '/rumu/'.$code.'/kuro', 'langKoUrl' => '/bang/'.$code.'/geom-eunsaeg', 'langZhUrl' => '/fangjian/'.$code.'/heise', 'canonicalUrl' => '/fangjian/'.$code.'/heise']);
});



// Route::group(['prefix' => 'admin'], function () {
//     Voyager::routes();
// });

Route::get('/puzzles/vi', [PuzzleController::class, 'getPuzzlesVi'])->name('puzzlesVi.list');
Route::get('/users/vi', [UserController::class, 'getUsersVi'])->name('usersVi.list');
Route::get('/rooms/vi', [RoomController::class, 'getRoomsVi'])->name('roomsVi.list');
Route::get('/rooms/en', [RoomController::class, 'getRoomsEn'])->name('roomsEn.list');
Route::get('/rooms/ja', [RoomController::class, 'getRoomsJa'])->name('roomsJa.list');
Route::get('/rooms/ko', [RoomController::class, 'getRoomsKo'])->name('roomsKo.list');
Route::get('/rooms/zh', [RoomController::class, 'getRoomsZh'])->name('roomsZh.list');
// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/home', function () {
  return redirect('/thi-dau', 301);
});
Route::get('/thi-dau', function() {
  return view('app/home', ['bodyClass' => 'dashboard', 'matchUsers' => UserController::getMatchUsers(), 'matchRooms' => RoomController::getMatchRooms(), 'playingRooms' => RoomController::getPlayingRooms(), 'playedRooms' => RoomController::getPlayedRooms(), 'rankUsers' => UserController::getRankUsers(), 'onlinePlayers' => UserController::onlinePlayers()]);
});
Route::get('/lich-su', function() {
  return view('app/history', ['headTitle' => 'Lịch sử thi đấu', 'bodyClass' => 'dashboard', 'matchUsers' => UserController::getMatchUsers(), 'matchRooms' => RoomController::getMatchRooms(), 'playingRooms' => RoomController::getPlayingRooms(), 'playedRooms' => RoomController::getPlayedRooms(), 'rankUsers' => UserController::getRankUsers()]);
});
Route::get('/bang-xep-hang', function() {
  return view('app/ranking', ['headTitle' => 'Bảng xếp hạng', 'bodyClass' => 'dashboard', 'users' => UserController::getUsers(), 'matchRooms' => RoomController::getMatchRooms(), 'rankUsers' => UserController::getRankUsers()]);
});
Route::get('/rankTableHtml', function() {
  return view('layout/partials/app/rankTableHtml', ['users' => UserController::getUsers(), 'matchRooms' => RoomController::getMatchRooms(), 'rankUsers' => UserController::getRankUsers()]);
});
Route::get('/doi-mat-khau', function() {
  return view('app/changePassword', ['headTitle' => 'Đổi mật khẩu', 'bodyClass' => 'player profile', 'player' => Auth::user(), 'users' => UserController::getUsers(), 'matchRooms' => RoomController::getMatchRooms(), 'rankUsers' => UserController::getRankUsers(), 'playerRooms' => RoomController::getPlayerRooms(Auth::user()->id)]);
})->middleware('auth');
Route::get('/doi-ten', function() {
  return view('app/changeName', ['headTitle' => 'Đổi tên', 'bodyClass' => 'player profile', 'player' => Auth::user(), 'users' => UserController::getUsers(), 'matchRooms' => RoomController::getMatchRooms(), 'rankUsers' => UserController::getRankUsers(), 'playerRooms' => RoomController::getPlayerRooms(Auth::user()->id)]);
})->middleware('auth');
Route::get('/doi-giao-dien', function() {
  return view('app/changeUi', ['headTitle' => 'Đổi giao diện', 'bodyClass' => 'player profile', 'player' => Auth::user(), 'users' => UserController::getUsers(), 'matchRooms' => RoomController::getMatchRooms(), 'rankUsers' => UserController::getRankUsers(), 'playerRooms' => RoomController::getPlayerRooms(Auth::user()->id)]);
})->middleware('auth');
Route::get('/ho-so-cua-toi', function() {
  return view('app/player', ['headTitle' => 'Hồ sơ của tôi', 'bodyClass' => 'player profile', 'player' => Auth::user(), 'users' => UserController::getUsers(), 'matchRooms' => RoomController::getMatchRooms(), 'rankUsers' => UserController::getRankUsers(), 'playerRooms' => RoomController::getPlayerRooms(Auth::user()->id)]);
})->middleware('auth');
Route::get('/ky-thu/{id}', function($id) {
  return view('app/player', ['headTitle' => 'Hồ sơ kỳ thủ' . ' "' . UserController::getUserName($id) . '"', 'bodyClass' => 'player', 'player' => User::firstWhere('id', $id), 'users' => UserController::getUsers(), 'matchRooms' => RoomController::getMatchRooms(), 'rankUsers' => UserController::getRankUsers(), 'playerRooms' => RoomController::getPlayerRooms($id)]);
});
Route::post('dang-xuat', [LoginController::class, 'logout'])->name('logout');
Route::get('dang-nhap', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('dang-nhap', 'Auth\LoginController@login');

Route::get('dang-ky', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('dang-ky', 'Auth\RegisterController@register');

Route::get('quen-mat-khau', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::get('tao-mat-khau', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.create');
Route::post('gui-duong-dan-tao-mat-khau', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('dat-lai-mat-khau/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('quen-mat-khau', 'Auth\ResetPasswordController@reset')->name('password.update');

Route::post('doi-mat-khau', [UserController::class, 'changePassword'])->name('change.password');
Route::post('doi-ten', [UserController::class, 'changeName'])->name('change.name');
Route::post('doi-giao-dien', [UserController::class, 'changeUserInterface'])->name('change.ui');

Route::get('tim-kiem', 'UserController@searchPlayers')->name('searchPlayers');

Route::get('/auth/facebook', 'Auth\LoginController@redirectToFacebook');
Route::get('/auth/facebook/callback', 'Auth\LoginController@handleFacebookCallback');

Route::get('/auth/google', 'Auth\LoginController@redirectToGoogle');
Route::get('/auth/google/callback', 'Auth\LoginController@handleGoogleCallback');

Route::get('/auth/github', 'Auth\LoginController@redirectToGithub');
Route::get('/auth/github/callback', 'Auth\LoginController@handleGithubCallback');

Route::get('/auth/linkedin', 'Auth\LoginController@redirectToLinkedin');
Route::get('/auth/linkedin/callback', 'Auth\LoginController@handleLinkedinCallback');

Route::get('/auth/gitlab', 'Auth\LoginController@redirectToGitlab');
Route::get('/auth/gitlab/callback', 'Auth\LoginController@handleGitlabCallback');

Route::get('/auth/bitbucket', 'Auth\LoginController@redirectToBitbucket');
Route::get('/auth/bitbucket/callback', 'Auth\LoginController@handleBitbucketCallback');

Route::get('/auth/zalo', 'Auth\LoginController@redirectToZalo');
Route::get('/auth/zalo/callback', 'Auth\LoginController@handleZaloCallback');

Route::match(['get', 'post'], '/timer', [TimerController::class, 'update'])->name('timer.update');

Route::match(['get', 'post'], '/choi-mot-minh', function () {
  return view('human', ['headTitle' => 'Chơi một mình', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/choi-mot-minh', 'langEnUrl' => '/play-alone', 'langJaUrl' => '/ichi-nin-de-asobu', 'langKoUrl' => '/honja-nolda', 'langZhUrl' => '/duchu', 'canonicalUrl' => '/choi-mot-minh', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
});
Route::match(['get', 'post'], '/play-alone', function () {
  return view('en/human', ['headTitle' => 'Play alone', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/choi-mot-minh', 'langEnUrl' => '/play-alone', 'langJaUrl' => '/ichi-nin-de-asobu', 'langKoUrl' => '/honja-nolda', 'langZhUrl' => '/duchu', 'canonicalUrl' => '/play-alone']);
});
Route::match(['get', 'post'], '/ichi-nin-de-asobu', function () {
  return view('ja/human', ['headTitle' => '一人で遊ぶ', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/choi-mot-minh', 'langEnUrl' => '/play-alone', 'langJaUrl' => '/ichi-nin-de-asobu', 'langKoUrl' => '/honja-nolda', 'langZhUrl' => '/duchu', 'canonicalUrl' => '/ichi-nin-de-asobu']);
});
Route::match(['get', 'post'], '/honja-nolda', function () {
  return view('ko/human', ['headTitle' => '혼자 놀다', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/choi-mot-minh', 'langEnUrl' => '/play-alone', 'langJaUrl' => '/ichi-nin-de-asobu', 'langKoUrl' => '/honja-nolda', 'langZhUrl' => '/duchu', 'canonicalUrl' => '/honja-nolda']);
});
Route::match(['get', 'post'], '/duchu', function () {
  return view('zh/human', ['headTitle' => '独处', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/choi-mot-minh', 'langEnUrl' => '/play-alone', 'langJaUrl' => '/ichi-nin-de-asobu', 'langKoUrl' => '/honja-nolda', 'langZhUrl' => '/duchu', 'canonicalUrl' => '/duchu']);
});

Route::match(['get', 'post'], '/co-the', function () {
return view('puzzle', ['headTitle' => 'Xếp bàn cờ thế', 'bodyClass' => 'puzzle setup', 'board' => '', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/co-the', 'langEnUrl' => '/puzzle', 'langJaUrl' => '/pazuru', 'langKoUrl' => '/peojeul', 'langZhUrl' => '/mi', 'canonicalUrl' => '/co-the', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
});
Route::match(['get', 'post'], '/puzzle', function () {
return view('en/puzzle', ['headTitle' => 'Set up the puzzle', 'bodyClass' => 'puzzle', 'board' => '', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/co-the', 'langEnUrl' => '/puzzle', 'langJaUrl' => '/pazuru', 'langKoUrl' => '/peojeul', 'langZhUrl' => '/mi', 'canonicalUrl' => '/puzzle']);
});
Route::match(['get', 'post'], '/pazuru', function () {
return view('ja/puzzle', ['headTitle' => 'パズルを組み立てる', 'bodyClass' => 'puzzle', 'board' => '', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/co-the', 'langEnUrl' => '/puzzle', 'langJaUrl' => '/pazuru', 'langKoUrl' => '/peojeul', 'langZhUrl' => '/mi', 'canonicalUrl' => '/pazuru']);
});
Route::match(['get', 'post'], '/peojeul', function () {
return view('ko/puzzle', ['headTitle' => '퍼즐', 'bodyClass' => 'puzzle', 'board' => '', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/co-the', 'langEnUrl' => '/puzzle', 'langJaUrl' => '/pazuru', 'langKoUrl' => 'peojeul', 'langZhUrl' => '/mi', 'canonicalUrl' => '/peojeul']);
});
Route::match(['get', 'post'], '/mi', function () {
return view('zh/puzzle', ['headTitle' => '谜', 'bodyClass' => 'puzzle', 'board' => '', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/co-the', 'langEnUrl' => '/puzzle', 'langJaUrl' => '/pazuru', 'langKoUrl' => '/peojeul', 'langZhUrl' => '/mi', 'canonicalUrl' => '/mi']);
});

Route::match(['get', 'post'], '/thach-dau/{board}', function ($board) {
return view('puzzleCompete', ['headTitle' => 'Thách đấu', 'bodyClass' => 'puzzle', 'board' => $board, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/', 'langEnUrl' => '/en', 'langJaUrl' => '/ja', 'langKoUrl' => '/ko', 'langZhUrl' => '/zh', 'canonicalUrl' => '/thach-dau/'.$board, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['board' => $fenRegex]);

Route::match(['get', 'post'], '/the-co/{slug}', function ($slug) {
return view('puzzleRating', ['headTitle' => 'Thế cờ "'.PuzzleController::getName($slug).'"', 'bodyClass' => 'puzzle', 'name' => PuzzleController::getName($slug), 'slug' => $slug, 'fen' => PuzzleController::getFen($slug) , 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/', 'langEnUrl' => '/en', 'langJaUrl' => '/ja', 'langKoUrl' => '/ko', 'langZhUrl' => '/zh', 'canonicalUrl' => '/the-co/'.$slug, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
});

Route::match(['get', 'post'], '/getUserPuzzlesTemplate', function(){
return view('layout.partials.userPuzzles', ['userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()])->render();
});

Route::match(['get', 'post'], '/co-the/{board}', function ($board) {
return view('puzzle', ['headTitle' => 'Bàn cờ thế', 'bodyClass' => 'puzzle', 'board' => $board, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/co-the/'.$board, 'langEnUrl' => '/puzzle/'.$board, 'langJaUrl' => '/pazuru/'.$board, 'langKoUrl' => '/peojeul/'.$board, 'langZhUrl' => '/mi/'.$board, 'canonicalUrl' => '/co-the/'.$board, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['board' => $fenRegex]);
Route::match(['get', 'post'], '/puzzle/{board}', function ($board) {
return view('en/puzzle', ['headTitle' => 'Puzzle', 'bodyClass' => 'puzzle', 'board' => $board, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/co-the/'.$board, 'langEnUrl' => '/puzzle/'.$board, 'langJaUrl' => '/pazuru/'.$board, 'langKoUrl' => '/peojeul/'.$board, 'langZhUrl' => '/mi/'.$board, 'canonicalUrl' => '/puzzle/'.$board]);
})->where(['board' => $fenRegex]);
Route::match(['get', 'post'], '/pazuru/{board}', function ($board) {
return view('ja/puzzle', ['headTitle' => 'パズル', 'bodyClass' => 'puzzle', 'board' => $board, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/co-the/'.$board, 'langEnUrl' => '/puzzle/'.$board, 'langJaUrl' => '/pazuru/'.$board, 'langKoUrl' => '/peojeul/'.$board, 'langZhUrl' => '/mi/'.$board, 'canonicalUrl' => '/pazuru/'.$board]);
})->where(['board' => $fenRegex]);
Route::match(['get', 'post'], '/peojeul/{board}', function ($board) {
return view('ko/puzzle', ['headTitle' => '퍼즐', 'bodyClass' => 'puzzle', 'board' => $board, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/co-the/'.$board, 'langEnUrl' => '/puzzle/'.$board, 'langJaUrl' => '/pazuru/'.$board, 'langKoUrl' => '/peojeul/'.$board, 'langZhUrl' => '/mi/'.$board, 'canonicalUrl' => '/peojeul/'.$board]);
})->where(['board' => $fenRegex]);
Route::match(['get', 'post'], '/mi/{board}', function ($board) {
return view('zh/puzzle', ['headTitle' => '谜', 'bodyClass' => 'puzzle', 'board' => $board, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/co-the/'.$board, 'langEnUrl' => '/puzzle/'.$board, 'langJaUrl' => '/pazuru/'.$board, 'langKoUrl' => '/peojeul/'.$board, 'langZhUrl' => '/mi/'.$board, 'canonicalUrl' => '/mi/'.$board]);
})->where(['board' => $fenRegex]);

Route::match(['get', 'post'], '/ban-co/{fen}', function ($fen) {
return view('board', ['headTitle' => 'Bàn cờ tự giải', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co/'.$fen, 'langEnUrl' => '/board/'.$fen, 'langJaUrl' => '/bodo/'.$fen, 'langKoUrl' => '/bodeu/'.$fen, 'langZhUrl' => '/ban/'.$fen, 'canonicalUrl' => '/ban-co/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex]);

Route::match(['get', 'post'], '/ban-co-de-nhat/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => 'Bàn cờ dễ nhất', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '1', 'levelTxt' => 'Dễ nhất', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-de-nhat/'.$fen, 'langEnUrl' => '/easiest-board/'.$fen, 'langJaUrl' => '/mottomo-kantanna-bodo/'.$fen, 'langKoUrl' => '/gajang-swiun-bodeu/'.$fen, 'langZhUrl' => '/zuijiandandeban/'.$fen, 'canonicalUrl' => '/ban-co-de-nhat/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/ban-co-moi-choi/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => 'Bàn cờ mới chơi', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '1', 'levelTxt' => 'Mới chơi', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-moi-choi/'.$fen, 'langEnUrl' => '/newbie-board/'.$fen, 'langJaUrl' => '/shoshinsha-bodo/'.$fen, 'langKoUrl' => '/nyubi-bodeu/'.$fen, 'langZhUrl' => '/xinshouban/'.$fen, 'canonicalUrl' => '/ban-co-moi-choi/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/ban-co-de/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => 'Bàn cờ dễ', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '2', 'levelTxt' => 'Dễ', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-de/'.$fen, 'langEnUrl' => '/easy-board/'.$fen, 'langJaUrl' => '/kantan-bodo/'.$fen, 'langKoUrl' => '/iji-bodeu/'.$fen, 'langZhUrl' => '/jianyiban/'.$fen, 'canonicalUrl' => '/ban-co-de/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/ban-co-binh-thuong/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => 'Bàn cờ bình thường', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '3', 'levelTxt' => 'Bình thường', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-binh-thuong/'.$fen, 'langEnUrl' => '/normal-board/'.$fen, 'langJaUrl' => '/tsujo-bodo/'.$fen, 'langKoUrl' => '/nomol-bodeu/'.$fen, 'langZhUrl' => '/putongban/'.$fen, 'canonicalUrl' => '/ban-co-binh-thuong/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/ban-co-kho/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => 'Bàn cờ khó', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => 'Khó', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-kho/'.$fen, 'langEnUrl' => '/hard-board/'.$fen, 'langJaUrl' => '/hado-bodo/'.$fen, 'langKoUrl' => '/hadeu-bodeu/'.$fen, 'langZhUrl' => '/yingban/'.$fen, 'canonicalUrl' => '/ban-co-kho/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/ban-co-kho-nhat/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => 'Bàn cờ khó nhất', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => 'Khó nhất', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-kho-nhat/'.$fen, 'langEnUrl' => '/hardest-board/'.$fen, 'langJaUrl' => '/mottomo-muzukashi-bodo/'.$fen, 'langKoUrl' => '/gajang-dandanhan-bodeu/'.$fen, 'langZhUrl' => '/zuiyingban/'.$fen, 'canonicalUrl' => '/ban-co-kho-nhat/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/giai-co-the/{fen}', function ($fen) {
return view('puzzleAi', ['headTitle' => 'Giải cờ thế', 'bodyClass' => 'puzzle', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => 'Khó nhất', 'cdnUrl' => URL::to(''), 'langViUrl' => '/giai-co-the/'.$fen, 'langEnUrl' => '/solve-puzzle/'.$fen, 'langJaUrl' => '/pazuru-o-toku/'.$fen, 'langKoUrl' => '/pojeureul-pulda/'.$fen, 'langZhUrl' => '/jiejuenanti/'.$fen, 'canonicalUrl' => '/giai-co-the/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex]);

Route::match(['get', 'post'], '/board/{fen}', function ($fen) {
  return view('en/board', ['headTitle' => 'Board', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co/'.$fen, 'langEnUrl' => '/board/'.$fen, 'langJaUrl' => '/bodo/'.$fen, 'langKoUrl' => '/bodeu/'.$fen, 'langZhUrl' => '/ban/'.$fen, 'canonicalUrl' => '/board/'.$fen]);
})->where(['fen' => $fenRegex]);

Route::match(['get', 'post'], '/easiest-board/{fen}', function ($fen) {
return view('en/boardAi', ['headTitle' => 'Easiest board', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '1', 'levelTxt' => 'Easiest', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-de-nhat/'.$fen, 'langEnUrl' => '/easiest-board/'.$fen, 'langJaUrl' => '/mottomo-kantanna-bodo/'.$fen, 'langKoUrl' => '/gajang-swiun-bodeu/'.$fen, 'langZhUrl' => '/zuijiandandeban/'.$fen, 'canonicalUrl' => '/easiest-board/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/newbie-board/{fen}', function ($fen) {
return view('en/boardAi', ['headTitle' => 'Newbie board', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '1', 'levelTxt' => 'Newbie', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-moi-choi/'.$fen, 'langEnUrl' => '/newbie-board/'.$fen, 'langJaUrl' => '/shoshinsha-bodo/'.$fen, 'langKoUrl' => '/nyubi-bodeu/'.$fen, 'langZhUrl' => '/xinshouban/'.$fen, 'canonicalUrl' => '/newbie-board/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/easy-board/{fen}', function ($fen) {
return view('en/boardAi', ['headTitle' => 'Easy board', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '2', 'levelTxt' => 'Easy', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-de/'.$fen, 'langEnUrl' => '/easy-board/'.$fen, 'langJaUrl' => '/kantan-bodo/'.$fen, 'langKoUrl' => '/iji-bodeu/'.$fen, 'langZhUrl' => '/jianyiban/'.$fen, 'canonicalUrl' => '/easy-board/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/normal-board/{fen}', function ($fen) {
return view('en/boardAi', ['headTitle' => 'Normal board', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '3', 'levelTxt' => 'Normal', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-binh-thuong/'.$fen, 'langEnUrl' => '/normal-board/'.$fen, 'langJaUrl' => '/tsujo-bodo/'.$fen, 'langKoUrl' => '/nomol-bodeu/'.$fen, 'langZhUrl' => '/putongban/'.$fen, 'canonicalUrl' => '/normal-board/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/hard-board/{fen}', function ($fen) {
return view('en/boardAi', ['headTitle' => 'Hard board', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => 'Hard', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-kho/'.$fen, 'langEnUrl' => '/hard-board/'.$fen, 'langJaUrl' => '/hado-bodo/'.$fen, 'langKoUrl' => '/hadeu-bodeu/'.$fen, 'langZhUrl' => '/yingban/'.$fen, 'canonicalUrl' => '/hard-board/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/hardest-board/{fen}', function ($fen) {
return view('en/boardAi', ['headTitle' => 'Hardest board', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => 'Hardest', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-kho-nhat/'.$fen, 'langEnUrl' => '/hardest-board/'.$fen, 'langJaUrl' => '/mottomo-muzukashi-bodo/'.$fen, 'langKoUrl' => '/gajang-dandanhan-bodeu/'.$fen, 'langZhUrl' => '/zuiyingban/'.$fen, 'canonicalUrl' => '/hardest-board/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/solve-puzzle/{fen}', function ($fen) {
return view('en/puzzleAi', ['headTitle' => 'Solve puzzle', 'bodyClass' => 'puzzle', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => 'Hardest', 'cdnUrl' => URL::to(''), 'langViUrl' => '/giai-co-the/'.$fen, 'langEnUrl' => '/solve-puzzle/'.$fen, 'langJaUrl' => '/pazuru-o-toku/'.$fen, 'langKoUrl' => '/pojeureul-pulda/'.$fen, 'langZhUrl' => '/jiejuenanti/'.$fen, 'canonicalUrl' => '/solve-puzzle/'.$fen]);
})->where(['fen' => $fenRegex]);

Route::match(['get', 'post'], '/bodo/{fen}', function ($fen) {
  return view('ja/board', ['headTitle' => 'ボード', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co/'.$fen, 'langEnUrl' => '/board/'.$fen, 'langJaUrl' => '/bodo/'.$fen, 'langKoUrl' => '/bodeu/'.$fen, 'langZhUrl' => '/ban/'.$fen, 'canonicalUrl' => '/bodo/'.$fen]);
})->where(['fen' => $fenRegex]);

Route::match(['get', 'post'], '/mottomo-kantanna-bodo/{fen}', function ($fen) {
return view('ja/boardAi', ['headTitle' => '最も簡単なボード', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '1', 'levelTxt' => '最も簡単', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-de-nhat/'.$fen, 'langEnUrl' => '/easiest-board/'.$fen, 'langJaUrl' => '/mottomo-kantanna-bodo/'.$fen, 'langKoUrl' => '/gajang-swiun-bodeu/'.$fen, 'langZhUrl' => '/zuijiandandeban/'.$fen, 'canonicalUrl' => '/mottomo-kantanna-bodo/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/shoshinsha-bodo/{fen}', function ($fen) {
return view('ja/boardAi', ['headTitle' => '初心者ボード', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '1', 'levelTxt' => '初心者', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-moi-choi/'.$fen, 'langEnUrl' => '/newbie-board/'.$fen, 'langJaUrl' => '/shoshinsha-bodo/'.$fen, 'langKoUrl' => '/nyubi-bodeu/'.$fen, 'langZhUrl' => '/xinshouban/'.$fen, 'canonicalUrl' => '/shoshinsha-bodo/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/kantan-bodo/{fen}', function ($fen) {
return view('ja/boardAi', ['headTitle' => 'イージーボード', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '2', 'levelTxt' => '簡単', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-de/'.$fen, 'langEnUrl' => '/easy-board/'.$fen, 'langJaUrl' => '/kantan-bodo/'.$fen, 'langKoUrl' => '/iji-bodeu/'.$fen, 'langZhUrl' => '/jianyiban/'.$fen, 'canonicalUrl' => '/kantan-bodo/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/tsujo-bodo/{fen}', function ($fen) {
return view('ja/boardAi', ['headTitle' => '通常ボード', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '3', 'levelTxt' => 'ツジョ', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-binh-thuong/'.$fen, 'langEnUrl' => '/normal-board/'.$fen, 'langJaUrl' => '/tsujo-bodo/'.$fen, 'langKoUrl' => '/nomol-bodeu/'.$fen, 'langZhUrl' => '/putongban/'.$fen, 'canonicalUrl' => '/tsujo-bodo/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/hado-bodo/{fen}', function ($fen) {
return view('ja/boardAi', ['headTitle' => 'ハードボード', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => 'ハード', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-kho/'.$fen, 'langEnUrl' => '/hard-board/'.$fen, 'langJaUrl' => '/hado-bodo/'.$fen, 'langKoUrl' => '/hadeu-bodeu/'.$fen, 'langZhUrl' => '/yingban/'.$fen, 'canonicalUrl' => '/hado-bodo/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/mottomo-muzukashi-bodo/{fen}', function ($fen) {
return view('ja/boardAi', ['headTitle' => '最も難しいボード', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => '最も難しい', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-kho-nhat/'.$fen, 'langEnUrl' => '/hardest-board/'.$fen, 'langJaUrl' => '/mottomo-muzukashi-bodo/'.$fen, 'langKoUrl' => '/gajang-dandanhan-bodeu/'.$fen, 'langZhUrl' => '/zuiyingban/'.$fen, 'canonicalUrl' => '/mottomo-muzukashi-bodo/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/pazuru-o-toku/{fen}', function ($fen) {
return view('ja/puzzleAi', ['headTitle' => 'パズルを解く', 'bodyClass' => 'puzzle', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => '最も難しい', 'cdnUrl' => URL::to(''), 'langViUrl' => '/giai-co-the/'.$fen, 'langEnUrl' => '/solve-puzzle/'.$fen, 'langJaUrl' => '/pazuru-o-toku/'.$fen, 'langKoUrl' => '/pojeureul-pulda/'.$fen, 'langZhUrl' => '/jiejuenanti/'.$fen, 'canonicalUrl' => '/pazuru-o-toku/'.$fen]);
})->where(['fen' => $fenRegex]);

Route::match(['get', 'post'], '/bodeu/{fen}', function ($fen) {
return view('ko/board', ['headTitle' => '보드', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co/'.$fen, 'langEnUrl' => '/board/'.$fen, 'langJaUrl' => '/bodo/'.$fen, 'langKoUrl' => '/bodeu/'.$fen, 'langZhUrl' => '/ban/'.$fen, 'canonicalUrl' => '/bodeu/'.$fen]);
})->where(['fen' => $fenRegex]);

Route::match(['get', 'post'], '/gajang-swiun-bodeu/{fen}', function ($fen) {
return view('ko/boardAi', ['headTitle' => '가장 쉬운 보드', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '1', 'levelTxt' => '가장 쉬운', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-de-nhat/'.$fen, 'langEnUrl' => '/easiest-board/'.$fen, 'langJaUrl' => '/mottomo-kantanna-bodo/'.$fen, 'langKoUrl' => '/gajang-swiun-bodeu/'.$fen, 'langZhUrl' => '/zuijiandandeban/'.$fen, 'canonicalUrl' => '/gajang-swiun-bodeu/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/nyubi-bodeu/{fen}', function ($fen) {
return view('ko/boardAi', ['headTitle' => '뉴비 보드', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '1', 'levelTxt' => '뉴비', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-moi-choi/'.$fen, 'langEnUrl' => '/newbie-board/'.$fen, 'langJaUrl' => '/shoshinsha-bodo/'.$fen, 'langKoUrl' => '/nyubi-bodeu/'.$fen, 'langZhUrl' => '/xinshouban/'.$fen, 'canonicalUrl' => '/nyubi-bodeu/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/iji-bodeu/{fen}', function ($fen) {
return view('ko/boardAi', ['headTitle' => '이지보드', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '2', 'levelTxt' => '쉬운', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-de/'.$fen, 'langEnUrl' => '/easy-board/'.$fen, 'langJaUrl' => '/kantan-bodo/'.$fen, 'langKoUrl' => '/iji-bodeu/'.$fen, 'langZhUrl' => '/jianyiban/'.$fen, 'canonicalUrl' => '/iji-bodeu/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/nomol-bodeu/{fen}', function ($fen) {
return view('ko/boardAi', ['headTitle' => '노멀 보드', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '3', 'levelTxt' => '노멀', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-binh-thuong/'.$fen, 'langEnUrl' => '/normal-board/'.$fen, 'langJaUrl' => '/tsujo-bodo/'.$fen, 'langKoUrl' => '/nomol-bodeu/'.$fen, 'langZhUrl' => '/putongban/'.$fen, 'canonicalUrl' => '/nomol-bodeu/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/hadeu-bodeu/{fen}', function ($fen) {
return view('ko/boardAi', ['headTitle' => '하드보드', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => '하드', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-kho/'.$fen, 'langEnUrl' => '/hard-board/'.$fen, 'langJaUrl' => '/hado-bodo/'.$fen, 'langKoUrl' => '/hadeu-bodeu/'.$fen, 'langZhUrl' => '/yingban/'.$fen, 'canonicalUrl' => '/hadeu-bodeu/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/gajang-dandanhan-bodeu/{fen}', function ($fen) {
return view('ko/boardAi', ['headTitle' => '가장 단단한 보드', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => '가장 단단한', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-kho-nhat/'.$fen, 'langEnUrl' => '/hardest-board/'.$fen, 'langJaUrl' => '/mottomo-muzukashi-bodo/'.$fen, 'langKoUrl' => '/gajang-dandanhan-bodeu/'.$fen, 'langZhUrl' => '/zuiyingban/'.$fen, 'canonicalUrl' => '/gajang-dandanhan-bodeu/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/pojeureul-pulda/{fen}', function ($fen) {
return view('ko/puzzleAi', ['headTitle' => '퍼즐을 풀다', 'bodyClass' => 'puzzle', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => '가장 단단한', 'cdnUrl' => URL::to(''), 'langViUrl' => '/giai-co-the/'.$fen, 'langEnUrl' => '/solve-puzzle/'.$fen, 'langJaUrl' => '/pazuru-o-toku/'.$fen, 'langKoUrl' => '/pojeureul-pulda/'.$fen, 'langZhUrl' => '/jiejuenanti/'.$fen, 'canonicalUrl' => '/pojeureul-pulda/'.$fen]);
})->where(['fen' => $fenRegex]);

Route::match(['get', 'post'], '/ban/{fen}', function ($fen) {
  return view('zh/board', ['headTitle' => '板', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co/'.$fen, 'langEnUrl' => '/board/'.$fen, 'langJaUrl' => '/bodo/'.$fen, 'langKoUrl' => '/bodeu/'.$fen, 'langZhUrl' => '/ban/'.$fen, 'canonicalUrl' => '/ban/'.$fen]);
})->where(['fen' => $fenRegex]);

Route::match(['get', 'post'], '/zuijiandandeban/{fen}', function ($fen) {
return view('zh/boardAi', ['headTitle' => '最简单的板', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '1', 'levelTxt' => '最容易的', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-de-nhat/'.$fen, 'langEnUrl' => '/easiest-board/'.$fen, 'langJaUrl' => '/mottomo-kantanna-bodo/'.$fen, 'langKoUrl' => '/gajang-swiun-bodeu/'.$fen, 'langZhUrl' => '/zuijiandandeban/'.$fen, 'canonicalUrl' => '/zuijiandandeban/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/xinshouban/{fen}', function ($fen) {
return view('zh/boardAi', ['headTitle' => '新手板', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '1', 'levelTxt' => '新手', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-moi-choi/'.$fen, 'langEnUrl' => '/newbie-board/'.$fen, 'langJaUrl' => '/shoshinsha-bodo/'.$fen, 'langKoUrl' => '/nyubi-bodeu/'.$fen, 'langZhUrl' => '/xinshouban/'.$fen, 'canonicalUrl' => '/xinshouban/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/jianyiban/{fen}', function ($fen) {
return view('zh/boardAi', ['headTitle' => '简易板', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '2', 'levelTxt' => '容易的', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-de/'.$fen, 'langEnUrl' => '/easy-board/'.$fen, 'langJaUrl' => '/kantan-bodo/'.$fen, 'langKoUrl' => '/iji-bodeu/'.$fen, 'langZhUrl' => '/jianyiban/'.$fen, 'canonicalUrl' => '/jianyiban/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/putongban/{fen}', function ($fen) {
return view('zh/boardAi', ['headTitle' => '普通板', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '3', 'levelTxt' => '典型的', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-binh-thuong/'.$fen, 'langEnUrl' => '/normal-board/'.$fen, 'langJaUrl' => '/tsujo-bodo/'.$fen, 'langKoUrl' => '/nomol-bodeu/'.$fen, 'langZhUrl' => '/putongban/'.$fen, 'canonicalUrl' => '/putongban/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/yingban/{fen}', function ($fen) {
return view('zh/boardAi', ['headTitle' => '硬板', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => '坚固的', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-kho/'.$fen, 'langEnUrl' => '/hard-board/'.$fen, 'langJaUrl' => '/hado-bodo/'.$fen, 'langKoUrl' => '/hadeu-bodeu/'.$fen, 'langZhUrl' => '/yingban/'.$fen, 'canonicalUrl' => '/yingban/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/zuiyingban/{fen}', function ($fen) {
return view('zh/boardAi', ['headTitle' => '最难的', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => '最难的', 'cdnUrl' => URL::to(''), 'langViUrl' => '/ban-co-kho-nhat/'.$fen, 'langEnUrl' => '/hardest-board/'.$fen, 'langJaUrl' => '/mottomo-muzukashi-bodo/'.$fen, 'langKoUrl' => '/gajang-dandanhan-bodeu/'.$fen, 'langZhUrl' => '/zuiyingban/'.$fen, 'canonicalUrl' => '/zuiyingban/'.$fen]);
})->where(['fen' => $fenRegex]);
Route::match(['get', 'post'], '/jiejuenanti/{fen}', function ($fen) {
return view('zh/puzzleAi', ['headTitle' => '解决难题', 'bodyClass' => 'puzzle', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => '最难的', 'cdnUrl' => URL::to(''), 'langViUrl' => '/giai-co-the/'.$fen, 'langEnUrl' => '/solve-puzzle/'.$fen, 'langJaUrl' => '/pazuru-o-toku/'.$fen, 'langKoUrl' => '/pojeureul-pulda/'.$fen, 'langZhUrl' => '/jiejuenanti/'.$fen, 'canonicalUrl' => '/jiejuenanti/'.$fen]);
})->where(['fen' => $fenRegex]);

Route::match(['get', 'post'], '', function () {
return view('ai', ['headTitle' => 'Trang chủ', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '', 'langEnUrl' => '/en', 'langJaUrl' => '/ja', 'langKoUrl' => '/ko', 'langZhUrl' => '/zh', 'level' => '3', 'levelTxt' => 'Bình thường', 'canonicalUrl' => '', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
});
Route::match(['get', 'post'], '/de-nhat', function () {
return view('ai', ['headTitle' => 'Dễ nhất', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/de-nhat', 'langEnUrl' => '/easiest', 'langJaUrl' => '/mottomo-kantan', 'langKoUrl' => '/gajang-swiun', 'langZhUrl' => '/zuirongyide', 'level' => '1', 'levelTxt' => 'Dễ nhất', 'canonicalUrl' => '/de-nhat', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
});
Route::match(['get', 'post'], '/moi-choi', function () {
  return view('ai', ['headTitle' => 'Mới chơi', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/moi-choi', 'langEnUrl' => '/newbie', 'langJaUrl' => '/shoshinsha', 'langKoUrl' => '/nyubi', 'langZhUrl' => '/xinshou', 'level' => '1', 'levelTxt' => 'Mới chơi', 'canonicalUrl' => '/moi-choi', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
});
Route::match(['get', 'post'], '/de', function () {
return view('ai', ['headTitle' => 'Dễ', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/de', 'langEnUrl' => '/easy', 'langJaUrl' => '/kantan', 'langKoUrl' => '/iji', 'langZhUrl' => '/rongyide', 'level' => '2', 'levelTxt' => 'Dễ', 'canonicalUrl' => '/de', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
});
Route::match(['get', 'post'], '/binh-thuong', function () {
return view('ai', ['headTitle' => 'Bình thường', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/binh-thuong', 'langEnUrl' => '/normal', 'langJaUrl' => '/tsujo', 'langKoUrl' => '/nomol', 'langZhUrl' => '/dianxingde', 'level' => '3', 'levelTxt' => 'Bình thường', 'canonicalUrl' => '/binh-thuong', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
});
Route::match(['get', 'post'], '/kho', function () {
return view('ai', ['headTitle' => 'Khó', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/kho', 'langEnUrl' => '/hard', 'langJaUrl' => '/muzukashi', 'langKoUrl' => '/hadeu', 'langZhUrl' => '/jiangude', 'level' => '4', 'levelTxt' => 'Khó', 'canonicalUrl' => '/kho', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
});
Route::match(['get', 'post'], '/kho-nhat', function () {
return view('ai', ['headTitle' => 'Khó nhất', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/kho-nhat', 'langEnUrl' => '/hardest', 'langJaUrl' => '/mottomo-muzukashi', 'langKoUrl' => '/gajang-dandanhan', 'langZhUrl' => '/zuinande', 'level' => '4', 'levelTxt' => 'Khó nhất', 'canonicalUrl' => '/kho-nhat', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
});
Route::match(['get', 'post'], '/en', function () {
return view('en/ai', ['headTitle' => 'Home', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '', 'langEnUrl' => '/en', 'langJaUrl' => '/ja', 'langKoUrl' => '/ko', 'langZhUrl' => '/zh', 'level' => '3', 'levelTxt' => 'Normal', 'canonicalUrl' => '/en']);
});
Route::match(['get', 'post'], '/easiest', function () {
return view('en/ai', ['headTitle' => 'Easiest', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/de-nhat', 'langEnUrl' => '/easiest', 'langJaUrl' => '/mottomo-kantan', 'langKoUrl' => '/gajang-swiun', 'langZhUrl' => '/zuirongyide', 'level' => '1', 'levelTxt' => 'Easiest', 'canonicalUrl' => '/easiest']);
});
Route::match(['get', 'post'], '/newbie', function () {
  return view('en/ai', ['headTitle' => 'Newbie', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/moi-choi', 'langEnUrl' => '/newbie', 'langJaUrl' => '/shoshinsha', 'langKoUrl' => '/nyubi', 'langZhUrl' => '/xinshou', 'level' => '1', 'levelTxt' => 'Newbie', 'canonicalUrl' => '/newbie']);
});
Route::match(['get', 'post'], '/easy', function () {
return view('en/ai', ['headTitle' => 'Easy', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/de', 'langEnUrl' => '/easy', 'langJaUrl' => '/kantan', 'langKoUrl' => '/iji', 'langZhUrl' => '/rongyide', 'level' => '2', 'levelTxt' => 'Easy', 'canonicalUrl' => '/easy']);
});
Route::match(['get', 'post'], '/normal', function () {
return view('en/ai', ['headTitle' => 'Normal', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/binh-thuong', 'langEnUrl' => '/normal', 'langJaUrl' => '/tsujo', 'langKoUrl' => '/nomol', 'langZhUrl' => '/dianxingde', 'level' => '3', 'levelTxt' => 'Normal', 'canonicalUrl' => '/normal']);
});
Route::match(['get', 'post'], '/hard', function () {
return view('en/ai', ['headTitle' => 'Hard', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/kho', 'langEnUrl' => '/hard', 'langJaUrl' => '/muzukashi', 'langKoUrl' => '/hadeu', 'langZhUrl' => '/jiangude', 'level' => '4', 'levelTxt' => 'Hard', 'canonicalUrl' => '/hard']);
});
Route::match(['get', 'post'], '/hardest', function () {
return view('en/ai', ['headTitle' => 'Hardest', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/kho-nhat', 'langEnUrl' => '/hardest', 'langJaUrl' => '/mottomo-muzukashi', 'langKoUrl' => '/gajang-dandanhan', 'langZhUrl' => '/zuinande', 'level' => '4', 'levelTxt' => 'Hardest', 'canonicalUrl' => '/hardest']);
});
Route::match(['get', 'post'], '/play-with-ai', function () {
return redirect('/en', 301);
});
Route::match(['get', 'post'], '/play-with-ai/easiest', function () {
return redirect('/easiest', 301);
});
Route::match(['get', 'post'], '/play-with-ai/newbie', function () {
return redirect('/newbie', 301);
});
Route::match(['get', 'post'], '/play-with-ai/easy', function () {
return redirect('/easy', 301);
});
Route::match(['get', 'post'], '/play-with-ai/normal', function () {
return redirect('/normal', 301);
});
Route::match(['get', 'post'], '/play-with-ai/hard', function () {
return redirect('/hard', 301);
});
Route::match(['get', 'post'], '/play-with-ai/hardest', function () {
return redirect('/hardest', 301);
});

Route::match(['get', 'post'], '/ja', function () {
return view('ja/ai', ['headTitle' => 'ホームページ', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '', 'langEnUrl' => '/en', 'langJaUrl' => '/ja', 'langKoUrl' => '/ko', 'langZhUrl' => '/zh', 'level' => '3', 'levelTxt' => 'ツジョ', 'canonicalUrl' => '/ja']);
});
Route::match(['get', 'post'], '/mottomo-kantan', function () {
return view('ja/ai', ['headTitle' => '最も簡単', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/de-nhat', 'langEnUrl' => '/easiest', 'langJaUrl' => '/mottomo-kantan', 'langKoUrl' => '/gajang-swiun', 'langZhUrl' => '/zuirongyide', 'level' => '1', 'levelTxt' => '最も簡単', 'canonicalUrl' => '/mottomo-kantan']);
});
Route::match(['get', 'post'], '/shoshinsha', function () {
  return view('ja/ai', ['headTitle' => '初心者', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/moi-choi', 'langEnUrl' => '/newbie', 'langJaUrl' => '/shoshinsha', 'langKoUrl' => '/nyubi', 'langZhUrl' => '/xinshou', 'level' => '1', 'levelTxt' => '初心者', 'canonicalUrl' => '/shoshinsha']);
});
Route::match(['get', 'post'], '/kantan', function () {
return view('ja/ai', ['headTitle' => '簡単', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/de', 'langEnUrl' => '/easy', 'langJaUrl' => '/kantan', 'langKoUrl' => '/iji', 'langZhUrl' => '/rongyide', 'level' => '2', 'levelTxt' => '簡単', 'canonicalUrl' => '/kantan']);
});
Route::match(['get', 'post'], '/tsujo', function () {
return view('ja/ai', ['headTitle' => 'ツジョ', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/binh-thuong', 'langEnUrl' => '/normal', 'langJaUrl' => '/tsujo', 'langKoUrl' => '/nomol', 'langZhUrl' => '/dianxingde', 'level' => '3', 'levelTxt' => 'ツジョ', 'canonicalUrl' => '/tsujo']);
});
Route::match(['get', 'post'], '/hado', function () {
return view('ja/ai', ['headTitle' => 'ハード', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/kho', 'langEnUrl' => '/hard', 'langJaUrl' => '/muzukashi', 'langKoUrl' => '/hadeu', 'langZhUrl' => '/jiangude', 'level' => '4', 'levelTxt' => 'ハード', 'canonicalUrl' => '/muzukashi']);
});
Route::match(['get', 'post'], '/mottomo-muzukashi', function () {
return view('ja/ai', ['headTitle' => '最も難しい', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/kho-nhat', 'langEnUrl' => '/hardest', 'langJaUrl' => '/mottomo-muzukashi', 'langKoUrl' => '/gajang-dandanhan', 'langZhUrl' => '/zuinande', 'level' => '4', 'levelTxt' => '最も難しい', 'canonicalUrl' => '/mottomo-muzukashi']);
});

Route::match(['get', 'post'], '/ko', function () {
return view('ko/ai', ['headTitle' => '홈페이지', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '', 'langEnUrl' => '/en', 'langJaUrl' => '/ja', 'langKoUrl' => '/ko', 'langZhUrl' => '/zh', 'level' => '3', 'levelTxt' => '노멀', 'canonicalUrl' => '/ko']);
});
Route::match(['get', 'post'], '/gajang-swiun', function () {
return view('ko/ai', ['headTitle' => '가장 쉬운', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/de-nhat', 'langEnUrl' => '/easiest', 'langJaUrl' => '/mottomo-kantan', 'langKoUrl' => '/gajang-swiun', 'langZhUrl' => '/zuirongyide', 'level' => '1', 'levelTxt' => '가장 쉬운', 'canonicalUrl' => '/gajang-swiun']);
});
Route::match(['get', 'post'], '/nyubi', function () {
  return view('ko/ai', ['headTitle' => '뉴비', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/moi-choi', 'langEnUrl' => '/newbie', 'langJaUrl' => '/shoshinsha', 'langKoUrl' => '/nyubi', 'langZhUrl' => '/xinshou', 'level' => '1', 'levelTxt' => '뉴비', 'canonicalUrl' => '/nyubi']);
});
Route::match(['get', 'post'], '/iji', function () {
return view('ko/ai', ['headTitle' => '쉬운', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/de', 'langEnUrl' => '/easy', 'langJaUrl' => '/kantan', 'langKoUrl' => '/iji', 'langZhUrl' => '/rongyide', 'level' => '2', 'levelTxt' => '쉬운', 'canonicalUrl' => '/iji']);
});
Route::match(['get', 'post'], '/nomol', function () {
return view('ko/ai', ['headTitle' => '노멀', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/binh-thuong', 'langEnUrl' => '/normal', 'langJaUrl' => '/tsujo', 'langKoUrl' => '/nomol', 'langZhUrl' => '/dianxingde', 'level' => '3', 'levelTxt' => '노멀', 'canonicalUrl' => '/nomol']);
});
Route::match(['get', 'post'], '/hadeu', function () {
return view('ko/ai', ['headTitle' => '하드', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/kho', 'langEnUrl' => '/hard', 'langJaUrl' => '/muzukashi', 'langKoUrl' => '/hadeu', 'langZhUrl' => '/jiangude', 'level' => '4', 'levelTxt' => '하드', 'canonicalUrl' => '/hadeu']);
});
Route::match(['get', 'post'], '/gajang-dandanhan', function () {
return view('ko/ai', ['headTitle' => '가장 단단한', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/kho-nhat', 'langEnUrl' => '/hardest', 'langJaUrl' => '/mottomo-muzukashi', 'langKoUrl' => '/gajang-dandanhan', 'langZhUrl' => '/zuinande', 'level' => '4', 'levelTxt' => '가장 단단한', 'canonicalUrl' => '/gajang-dandanhan']);
});


Route::match(['get', 'post'], '/zh', function () {
return view('zh/ai', ['headTitle' => '主页', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '', 'langEnUrl' => '/en', 'langJaUrl' => '/ja', 'langKoUrl' => '/ko', 'langZhUrl' => '/zh', 'level' => '3', 'levelTxt' => '典型的', 'canonicalUrl' => '/zh']);
});
Route::match(['get', 'post'], '/zuirongyide', function () {
return view('zh/ai', ['headTitle' => '最容易的', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/de-nhat', 'langEnUrl' => '/easiest', 'langJaUrl' => '/mottomo-kantan', 'langKoUrl' => '/gajang-swiun', 'langZhUrl' => '/zuirongyide', 'level' => '1', 'levelTxt' => '最容易的', 'canonicalUrl' => '/zuirongyide']);
});
Route::match(['get', 'post'], '/xinshou', function () {
  return view('zh/ai', ['headTitle' => '新手', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/moi-choi', 'langEnUrl' => '/newbie', 'langJaUrl' => '/shoshinsha', 'langKoUrl' => '/nyubi', 'langZhUrl' => '/xinshou', 'level' => '1', 'levelTxt' => '新手', 'canonicalUrl' => '/xinshou']);
});
Route::match(['get', 'post'], '/rongyide', function () {
return view('zh/ai', ['headTitle' => '容易的', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/de', 'langEnUrl' => '/easy', 'langJaUrl' => '/kantan', 'langKoUrl' => '/iji', 'langZhUrl' => '/rongyide', 'level' => '2', 'levelTxt' => '容易的', 'canonicalUrl' => '/rongyide']);
});
Route::match(['get', 'post'], '/dianxingde', function () {
return view('zh/ai', ['headTitle' => '典型的', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/binh-thuong', 'langEnUrl' => '/normal', 'langJaUrl' => '/tsujo', 'langKoUrl' => '/nomol', 'langZhUrl' => '/dianxingde', 'level' => '3', 'levelTxt' => '典型的', 'canonicalUrl' => '/dianxingde']);
});
Route::match(['get', 'post'], '/jiangude', function () {
return view('zh/ai', ['headTitle' => '坚固的', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/kho', 'langEnUrl' => '/hard', 'langJaUrl' => '/muzukashi', 'langKoUrl' => '/hadeu', 'langZhUrl' => '/jiangude', 'level' => '4', 'levelTxt' => '坚固的', 'canonicalUrl' => '/jiangude']);
});
Route::match(['get', 'post'], '/zuinande', function () {
return view('zh/ai', ['headTitle' => '最难的', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/kho-nhat', 'langEnUrl' => '/hardest', 'langJaUrl' => '/mottomo-muzukashi', 'langKoUrl' => '/gajang-dandanhan', 'langZhUrl' => '/zuinande', 'level' => '4', 'levelTxt' => '最难的', 'canonicalUrl' => '/zuinande']);
});

Route::match(['get', 'post'], '/gioi-thieu', function () {
  return view('about', ['headTitle' => 'Giới thiệu', 'bodyClass' => 'about', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/gioi-thieu', 'langEnUrl' => '/about-us', 'langJaUrl' => '/yaku', 'langKoUrl' => '/urie-daehae', 'langZhUrl' => '/guanyuwomens', 'canonicalUrl' => '/gioi-thieu']);
});
Route::match(['get', 'post'], '/about-us', function () {
  return view('en/about', ['headTitle' => 'About us', 'bodyClass' => 'about', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/gioi-thieu', 'langEnUrl' => '/about-us', 'langJaUrl' => '/yaku', 'langKoUrl' => '/urie-daehae', 'langZhUrl' => '/guanyuwomens', 'canonicalUrl' => '/about-us']);
});
Route::match(['get', 'post'], '/yaku', function () {
  return view('ja/about', ['headTitle' => '約', 'bodyClass' => 'about', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/gioi-thieu', 'langEnUrl' => '/about-us', 'langJaUrl' => '/yaku', 'langKoUrl' => '/urie-daehae', 'langZhUrl' => '/guanyuwomens', 'canonicalUrl' => '/yaku']);
});
Route::match(['get', 'post'], '/urie-daehae', function () {
  return view('ko/about', ['headTitle' => '우리에 대해', 'bodyClass' => 'about', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/gioi-thieu', 'langEnUrl' => '/about-us', 'langJaUrl' => '/yaku', 'langKoUrl' => '/urie-daehae', 'langZhUrl' => '/guanyuwomens', 'canonicalUrl' => '/urie-daehae']);
});
Route::match(['get', 'post'], '/guanyuwomens', function () {
  return view('zh/about', ['headTitle' => '关于我们', 'bodyClass' => 'about', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/gioi-thieu', 'langEnUrl' => '/about-us', 'langJaUrl' => '/yaku', 'langKoUrl' => '/urie-daehae', 'langZhUrl' => '/guanyuwomens', 'canonicalUrl' => '/guanyuwomens']);
});
Route::match(['get', 'post'], '/lien-he', function () {
  return view('contact', ['headTitle' => 'Liên hệ', 'bodyClass' => 'contact', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/lien-he', 'langEnUrl' => '/contact-us', 'langJaUrl' => '/kontakuto', 'langKoUrl' => '/mun-uihagi', 'langZhUrl' => '/lianxiwomen', 'canonicalUrl' => '/lien-he']);
});
Route::match(['get', 'post'], '/contact-us', function () {
  return view('en/contact', ['headTitle' => 'Contact us', 'bodyClass' => 'contact', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/lien-he', 'langEnUrl' => '/contact-us', 'langJaUrl' => '/kontakuto', 'langKoUrl' => '/mun-uihagi', 'langZhUrl' => '/lianxiwomen', 'canonicalUrl' => '/contact-us']);
});
Route::match(['get', 'post'], '/kontakuto', function () {
  return view('ja/contact', ['headTitle' => 'コンタクト', 'bodyClass' => 'contact', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/lien-he', 'langEnUrl' => '/contact-us', 'langJaUrl' => '/kontakuto', 'langKoUrl' => '/mun-uihagi', 'langZhUrl' => '/lianxiwomen', 'canonicalUrl' => '/kontakuto']);
});
Route::match(['get', 'post'], '/mun-uihagi', function () {
  return view('ko/contact', ['headTitle' => '문의하기', 'bodyClass' => 'contact', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/lien-he', 'langEnUrl' => '/contact-us', 'langJaUrl' => '/kontakuto', 'langKoUrl' => '/mun-uihagi', 'langZhUrl' => '/lianxiwomen', 'canonicalUrl' => '/mun-uihagi']);
});
Route::match(['get', 'post'], '/lianxiwomen', function () {
  return view('zh/contact', ['headTitle' => '联系我们', 'bodyClass' => 'contact', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => URL::to(''), 'langViUrl' => '/lien-he', 'langEnUrl' => '/contact-us', 'langJaUrl' => '/kontakuto', 'langKoUrl' => '/mun-uihagi', 'langZhUrl' => '/lianxiwomen', 'canonicalUrl' => '/lianxiwomen']);
});

Route::match(['get', 'post'], '/sanh-cho', function () {
return view('roomList', ['headTitle' => 'Sảnh chờ', 'bodyClass' => 'room', 'rooms' => Room::all(), 'roomCode' => '', 'randomRoom' => RoomController::getRandomRoom(), 'cdnUrl' => URL::to(''), 'langViUrl' => '/sanh-cho', 'langEnUrl' => '/rooms', 'langJaUrl' => '/heya-ichiran', 'langKoUrl' => '/bang-moglog', 'langZhUrl' => '/fangjianliebiao', 'canonicalUrl' => '/sanh-cho', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
});
Route::match(['get', 'post'], '/rooms', function () {
return view('en/roomList', ['headTitle' => 'Rooms\' list', 'bodyClass' => 'room', 'rooms' => Room::all(), 'roomCode' => '', 'randomRoom' => RoomController::getRandomRoom(), 'cdnUrl' => URL::to(''), 'langViUrl' => '/sanh-cho', 'langEnUrl' => '/rooms', 'langJaUrl' => '/heya-ichiran', 'langKoUrl' => '/bang-moglog', 'langZhUrl' => '/fangjianliebiao', 'canonicalUrl' => '/rooms']);
});
Route::match(['get', 'post'], '/heya-ichiran', function () {
return view('ja/roomList', ['headTitle' => '部屋一覧', 'bodyClass' => 'room', 'rooms' => Room::all(), 'roomCode' => '', 'randomRoom' => RoomController::getRandomRoom(), 'cdnUrl' => URL::to(''), 'langViUrl' => '/sanh-cho', 'langEnUrl' => '/rooms', 'langJaUrl' => '/heya-ichiran', 'langKoUrl' => '/bang-moglog', 'langZhUrl' => '/fangjianliebiao', 'canonicalUrl' => '/heya-ichiran']);
});
Route::match(['get', 'post'], '/bang-moglog', function () {
return view('ko/roomList', ['headTitle' => '방 목록', 'bodyClass' => 'room', 'rooms' => Room::all(), 'roomCode' => '', 'randomRoom' => RoomController::getRandomRoom(), 'cdnUrl' => URL::to(''), 'langViUrl' => '/sanh-cho', 'langEnUrl' => '/rooms', 'langJaUrl' => '/heya-ichiran', 'langKoUrl' => '/bang-moglog', 'langZhUrl' => '/fangjianliebiao', 'canonicalUrl' => '/bang-moglog']);
});
Route::match(['get', 'post'], '/fangjianliebiao', function () {
return view('zh/roomList', ['headTitle' => '房间列表', 'bodyClass' => 'room', 'rooms' => Room::all(), 'roomCode' => '', 'randomRoom' => RoomController::getRandomRoom(), 'cdnUrl' => URL::to(''), 'langViUrl' => '/sanh-cho', 'langEnUrl' => '/rooms', 'langJaUrl' => '/heya-ichiran', 'langKoUrl' => '/bang-moglog', 'langZhUrl' => '/fangjianliebiao', 'canonicalUrl' => '/fangjianliebiao']);
});
Route::match(['get', 'post'], '/tat-ca-the-co', function () {
  return view('puzzleList', ['headTitle' => 'Tất cả thế cờ', 'bodyClass' => 'puzzle setup', 'rooms' => Room::all(), 'roomCode' => '', 'randomRoom' => RoomController::getRandomRoom(), 'cdnUrl' => URL::to(''), 'langViUrl' => '/tat-ca-the-co', 'langEnUrl' => '/en', 'langJaUrl' => '/ja', 'langKoUrl' => '/ko', 'langZhUrl' => '/zh', 'canonicalUrl' => '/tat-ca-the-co', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
});
Route::match(['get', 'post'], '/thanh-vien', function () {
  return view('userList', ['headTitle' => 'Tất cả kỳ thủ', 'bodyClass' => 'room', 'rooms' => Room::all(), 'roomCode' => '', 'randomRoom' => RoomController::getRandomRoom(), 'cdnUrl' => URL::to(''), 'langViUrl' => '/thanh-vien', 'langEnUrl' => '/en', 'langJaUrl' => '/ja', 'langKoUrl' => '/ko', 'langZhUrl' => '/zh', 'canonicalUrl' => '/thanh-vien', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
});