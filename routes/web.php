<?php

use App\Models\Room;
use App\Models\User;
use App\Models\Puzzle;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PuzzleController;
use App\Http\Controllers\TimerController;
use App\Http\Controllers\PayOSController;
use App\Http\Controllers\TournamentController;
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
$cdnUrl = "https://cotuong.r.worldssl.net"; // url('')
$fenRegex = "[a-zA-Z0-9\-\/\s|&nbsp;]+";
// Common function to get random room
Route::get('/test-engine', function() {
  try {
    echo "<h1>Pikafish Engine Test</h1>";

    // Check if files exist
    $enginePath = storage_path('engines/pikafish_vps');
    $networkPath = storage_path('engines/pikafish.nnue');

    echo "<p>Engine exists: " . (file_exists($enginePath) ? 'YES' : 'NO') . "</p>";
    echo "<p>Network exists: " . (file_exists($networkPath) ? 'YES' : 'NO') . "</p>";

    if (file_exists($enginePath)) {
      echo "<p>Engine executable: " . (is_executable($enginePath) ? 'YES' : 'NO') . "</p>";
    }

    if (file_exists($networkPath)) {
      echo "<p>Network size: " . filesize($networkPath) . " bytes</p>";
    }

    // Test engine
    $engine = new \App\Services\XiangqiEngineService();

    echo "<p>Engine initialized: " . ($engine->isReady() ? 'YES' : 'NO') . "</p>";

    if ($engine->isReady()) {
      $fen = 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1';
      $bestMove = $engine->getBestMove($fen, 3000);

      echo "<p>Best move: <strong>" . ($bestMove ?? 'NOT FOUND') . "</strong></p>";
      echo "<p>FEN: " . $fen . "</p>";

      // Test API endpoint
      echo "<h2>API Test</h2>";
      $client = new \GuzzleHttp\Client();
      $response = $client->post(url('/api/xiangqi/best-move'), [
        'form_params' => [
          'fen' => $fen,
          'timeout' => 2000,
          'level' => 3,
          '_token' => csrf_token()
        ]
      ]);

      $result = json_decode($response->getBody(), true);
      echo "<p>API Response: " . json_encode($result) . "</p>";
    }
  } catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
  }
});
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

Route::middleware('auth')->group(function () {
    Route::post('/tournaments/{id}/join', [TournamentController::class, 'join'])->name('tournaments.join');
    Route::post('/tournaments/{id}/generate', [TournamentController::class, 'generateBracket'])->name('tournaments.generate');

    // You will need a simple GET route to render the views for the tournaments list and bracket
    Route::get('/giai-dau', [TournamentController::class, 'index'])->name('tournaments.index');
    Route::get('/giai-dau/{id}', [TournamentController::class, 'show'])->name('tournaments.show');
});

Route::post('/startTimer/{roomCode}/{player}', [RoomController::class, 'startTimer']);
Route::post('/pauseTimer/{roomCode}/{player}', [RoomController::class, 'pauseTimer']);
Route::post('/switchTurn/{roomCode}', [RoomController::class, 'switchTurn']);
Route::get('/getTime/{roomCode}', [RoomController::class, 'getTime']);
Route::post('/saveTime/{roomCode}', [RoomController::class, 'saveTime']);

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
  return view('terms', localized_page_data('terms', app()->getLocale(), ['headTitle' => 'Terms and Conditions', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url('')]));
});
Route::get('/privacy-policy', function () {
  return view('privacy', localized_page_data('privacy', app()->getLocale(), ['headTitle' => 'Privacy Policy', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url('')]));
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

$localizedRoomPages = [
  'room.host' => [
    'view' => 'roomHost',
    'titles' => [
      'vi' => fn($code) => ((null !== RoomController::getHostIdRoute($code)) ? 'Thi đấu - ' : '').'Chủ phòng - Phòng: '.RoomController::getRoomName($code),
      'en' => fn($code) => 'Host - Room: '.RoomController::getRoomName($code),
      'ja' => fn($code) => 'ホスト - ルーム: '.RoomController::getRoomName($code),
      'ko' => fn($code) => '주인 - 방: '.RoomController::getRoomName($code),
      'zh' => fn($code) => '主办 - 房间：'.RoomController::getRoomName($code),
    ],
  ],
  'room.guest' => [
    'view' => 'roomGuest',
    'titles' => [
      'vi' => fn($code) => ((null !== RoomController::getHostIdRoute($code)) ? 'Thi đấu - ' : '').'Khách - Phòng: '.RoomController::getRoomName($code),
      'en' => fn($code) => 'Guest - Room: '.RoomController::getRoomName($code),
      'ja' => fn($code) => 'ゲスト - ルーム: '.RoomController::getRoomName($code),
      'ko' => fn($code) => '손님 - 방: '.RoomController::getRoomName($code),
      'zh' => fn($code) => '客人 - 房间：'.RoomController::getRoomName($code),
    ],
  ],
  'room.random' => [
    'view' => 'roomRandom',
    'titles' => [
      'vi' => fn($code) => 'Ngẫu nhiên - Phòng: '.RoomController::getRoomName($code),
      'en' => fn($code) => 'Random - Room: '.RoomController::getRoomName($code),
      'ja' => fn($code) => 'ランダム - ルーム: '.RoomController::getRoomName($code),
      'ko' => fn($code) => '무작위의 - 방: '.RoomController::getRoomName($code),
      'zh' => fn($code) => '随机的 - 房间：'.RoomController::getRoomName($code),
    ],
  ],
  'room.watch' => [
    'view' => 'roomWatch',
    'titles' => [
      'vi' => fn($code) => ((null !== RoomController::getHostIdRoute($code)) ? 'Thi đấu - ' : '').'Theo dõi - Phòng: '.RoomController::getRoomName($code),
      'en' => fn($code) => 'Watch - Room: '.RoomController::getRoomName($code),
      'ja' => fn($code) => '見る - ルーム: '.RoomController::getRoomName($code),
      'ko' => fn($code) => '보다 - 방: '.RoomController::getRoomName($code),
      'zh' => fn($code) => '看 - 房间：'.RoomController::getRoomName($code),
    ],
  ],
  'room.red' => [
    'view' => 'roomRed',
    'titles' => [
      'vi' => fn($code) => 'Bên đỏ - Phòng: '.RoomController::getRoomName($code),
      'en' => fn($code) => 'Red side - Room: '.RoomController::getRoomName($code),
      'ja' => fn($code) => '赤 - ルーム: '.RoomController::getRoomName($code),
      'ko' => fn($code) => '빨간 - 방: '.RoomController::getRoomName($code),
      'zh' => fn($code) => '红方 - 房间：'.RoomController::getRoomName($code),
    ],
  ],
  'room.black' => [
    'view' => 'roomBlack',
    'titles' => [
      'vi' => fn($code) => 'Bên đen - Phòng: '.RoomController::getRoomName($code),
      'en' => fn($code) => 'Black side - Room: '.RoomController::getRoomName($code),
      'ja' => fn($code) => '黒 - ルーム: '.RoomController::getRoomName($code),
      'ko' => fn($code) => '검은색 - 방: '.RoomController::getRoomName($code),
      'zh' => fn($code) => '黑边 - 房间：'.RoomController::getRoomName($code),
    ],
  ],
];

foreach ($localizedRoomPages as $pageKey => $roomPage) {
  foreach (config('locales.supported', []) as $locale) {
    Route::match(['get', 'post'], localized_path($pageKey, ['code' => '{code}'], $locale), function($code) use ($pageKey, $roomPage, $locale) {
      $room = Room::firstWhere('code', $code);
      if (!$room) {
        abort(404);
      }

      $view = $roomPage['view'];
      $data = [
        'headTitle' => $roomPage['titles'][$locale]($code),
        'bodyClass' => 'room',
        'randomRoom' => RoomController::getRandomRoom(),
        'roomCode' => $code,
        'room' => $room,
        'cdnUrl' => url(''),
      ];

      $data = array_merge($data, [
          'userPuzzles' => PuzzleController::getUserPuzzles(),
          'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(),
          'boards' => RoomController::getBoards(),
          'firstPageBoards' => RoomController::getFirstPageBoards(),
          'playedBoards' => RoomController::getPlayedBoards(),
          'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(),
          'players' => UserController::getPlayers(),
          'firstPagePlayers' => UserController::getFirstPagePlayers(),
        ]);

      return view($view, localized_page_data($pageKey, $locale, $data, ['code' => $code]));
    })->middleware("locale:{$locale}");
  }
}



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

Route::middleware('auth')->post('/payos/standard', [PayOSController::class, 'createStandard'])->name('payos.standard');
Route::get('/payos/return', [PayOSController::class, 'handleReturn'])->name('payos.return');
Route::get('/payos/cancel', [PayOSController::class, 'handleCancel'])->name('payos.cancel');
Route::post('/payos/webhook', [PayOSController::class, 'webhook'])->name('payos.webhook');

Route::post('doi-mat-khau', [UserController::class, 'changePassword'])->name('change.password');
Route::post('doi-ten', [UserController::class, 'changeName'])->name('change.name');
Route::post('doi-giao-dien', [UserController::class, 'changeUserInterface'])->name('change.ui');

Route::get('tim-kiem', 'UserController@searchPlayers')->name('searchPlayers');

Route::post('auth/google/onetap', [AuthController::class, 'handleOneTapCallback'])->name('login.google.onetap');

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
  return view('human', ['headTitle' => 'Chơi một mình', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/choi-mot-minh', 'langEnUrl' => '/play-alone', 'langJaUrl' => '/ichi-nin-de-asobu', 'langKoUrl' => '/honja-nolda', 'langZhUrl' => '/duchu', 'canonicalUrl' => '/choi-mot-minh', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:vi');
Route::match(['get', 'post'], '/play-alone', function () {
  return view('human', ['headTitle' => 'Play alone', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/choi-mot-minh', 'langEnUrl' => '/play-alone', 'langJaUrl' => '/ichi-nin-de-asobu', 'langKoUrl' => '/honja-nolda', 'langZhUrl' => '/duchu', 'canonicalUrl' => '/play-alone', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:en');
Route::match(['get', 'post'], '/ichi-nin-de-asobu', function () {
  return view('human', ['headTitle' => '一人で遊ぶ', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/choi-mot-minh', 'langEnUrl' => '/play-alone', 'langJaUrl' => '/ichi-nin-de-asobu', 'langKoUrl' => '/honja-nolda', 'langZhUrl' => '/duchu', 'canonicalUrl' => '/ichi-nin-de-asobu', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:ja');
Route::match(['get', 'post'], '/honja-nolda', function () {
  return view('human', ['headTitle' => '혼자 놀다', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/choi-mot-minh', 'langEnUrl' => '/play-alone', 'langJaUrl' => '/ichi-nin-de-asobu', 'langKoUrl' => '/honja-nolda', 'langZhUrl' => '/duchu', 'canonicalUrl' => '/honja-nolda', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:ko');
Route::match(['get', 'post'], '/duchu', function () {
  return view('human', ['headTitle' => '独处', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/choi-mot-minh', 'langEnUrl' => '/play-alone', 'langJaUrl' => '/ichi-nin-de-asobu', 'langKoUrl' => '/honja-nolda', 'langZhUrl' => '/duchu', 'canonicalUrl' => '/duchu', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:zh');

Route::match(['get', 'post'], '/co-the', function () {
return view('puzzle', ['headTitle' => 'Xếp bàn cờ thế', 'bodyClass' => 'puzzle setup', 'board' => '', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/co-the', 'langEnUrl' => '/puzzle', 'langJaUrl' => '/pazuru', 'langKoUrl' => '/peojeul', 'langZhUrl' => '/mi', 'canonicalUrl' => '/co-the', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:vi');
Route::match(['get', 'post'], '/puzzle', function () {
return view('puzzle', ['headTitle' => 'Set up the puzzle', 'bodyClass' => 'puzzle setup', 'board' => '', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/co-the', 'langEnUrl' => '/puzzle', 'langJaUrl' => '/pazuru', 'langKoUrl' => '/peojeul', 'langZhUrl' => '/mi', 'canonicalUrl' => '/puzzle', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:en');
Route::match(['get', 'post'], '/pazuru', function () {
return view('puzzle', ['headTitle' => 'パズルを組み立てる', 'bodyClass' => 'puzzle setup', 'board' => '', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/co-the', 'langEnUrl' => '/puzzle', 'langJaUrl' => '/pazuru', 'langKoUrl' => '/peojeul', 'langZhUrl' => '/mi', 'canonicalUrl' => '/pazuru', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:ja');
Route::match(['get', 'post'], '/peojeul', function () {
return view('puzzle', ['headTitle' => '퍼즐', 'bodyClass' => 'puzzle setup', 'board' => '', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/co-the', 'langEnUrl' => '/puzzle', 'langJaUrl' => '/pazuru', 'langKoUrl' => 'peojeul', 'langZhUrl' => '/mi', 'canonicalUrl' => '/peojeul', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:ko');
Route::match(['get', 'post'], '/mi', function () {
return view('puzzle', ['headTitle' => '谜', 'bodyClass' => 'puzzle setup', 'board' => '', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/co-the', 'langEnUrl' => '/puzzle', 'langJaUrl' => '/pazuru', 'langKoUrl' => '/peojeul', 'langZhUrl' => '/mi', 'canonicalUrl' => '/mi', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:zh');

Route::match(['get', 'post'], '/thach-dau/{board}', function ($board) {
return view('puzzleCompete', ['headTitle' => 'Thách đấu', 'bodyClass' => 'puzzle', 'board' => $board, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/', 'langEnUrl' => '/en', 'langJaUrl' => '/ja', 'langKoUrl' => '/ko', 'langZhUrl' => '/zh', 'canonicalUrl' => '/thach-dau/'.$board, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['board' => $fenRegex]);

$puzzleRatingRoutes = [
    'vi' => ['prefix' => '/the-co/{slug}', 'title' => 'Thế cờ'],
    'en' => ['prefix' => '/puzzle-record/{slug}', 'title' => 'Puzzle'],
    'ja' => ['prefix' => '/pazuru-kiroku/{slug}', 'title' => 'パズル'],
    'ko' => ['prefix' => '/peojeul-girog/{slug}', 'title' => '퍼즐'],
    'zh' => ['prefix' => '/mi-jilu/{slug}', 'title' => '谜'],
];

foreach ($puzzleRatingRoutes as $locale => $routeInfo) {
    Route::match(['get', 'post'], $routeInfo['prefix'], function ($slug) use ($locale, $routeInfo) {
        $puzzle = Puzzle::where('slug', $slug)->firstOrFail();
        $headTitle = $puzzle->name ? $routeInfo['title'] . ' "' . $puzzle->name . '"' : $routeInfo['title'];

        // Dynamically replace {slug} with the actual slug for the canonical URL
        $canonicalUrl = str_replace('{slug}', $puzzle->slug, $routeInfo['prefix']);

        return view('puzzleRating', [
            'headTitle' => $headTitle,
            'bodyClass' => 'puzzle',
            'puzzle' => $puzzle,
            'name' => $puzzle->name,
            'slug' => $puzzle->slug,
            'fen' => $puzzle->fen,
            'description' => $puzzle->description,
            'isPublic' => $puzzle->is_public,
            'reactions' => [
                'likes' => $puzzle->likes_count,
                'hard' => $puzzle->hard_count,
                'unsolved' => $puzzle->unsolved_count,
                'rating' => $puzzle->rating,
            ],
            'randomRoom' => RoomController::getRandomRoom(),
            'roomCode' => '',
            'cdnUrl' => url(''),
            'langViUrl' => '/the-co/' . $puzzle->slug,
            'langEnUrl' => '/puzzle-record/' . $puzzle->slug,
            'langJaUrl' => '/pazuru-kiroku/' . $puzzle->slug,
            'langKoUrl' => '/peojeul-girog/' . $puzzle->slug,
            'langZhUrl' => '/mi-jilu/' . $puzzle->slug,
            'canonicalUrl' => $canonicalUrl,
            'userPuzzles' => PuzzleController::getUserPuzzles(),
            'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(),
            'boards' => RoomController::getBoards(),
            'firstPageBoards' => RoomController::getFirstPageBoards(),
            'playedBoards' => RoomController::getPlayedBoards(),
            'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(),
            'players' => UserController::getPlayers(),
            'firstPagePlayers' => UserController::getFirstPagePlayers(),
        ]);
    })->middleware("locale:{$locale}");
}

Route::match(['get', 'post'], '/getUserPuzzlesTemplate', function(){
return view('layout.partials.userPuzzles', ['userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()])->render();
});

Route::match(['get', 'post'], '/co-the/{board}', function ($board) {
return view('puzzle', ['headTitle' => 'Bàn cờ thế', 'bodyClass' => 'puzzle', 'board' => $board, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/co-the/'.$board, 'langEnUrl' => '/puzzle/'.$board, 'langJaUrl' => '/pazuru/'.$board, 'langKoUrl' => '/peojeul/'.$board, 'langZhUrl' => '/mi/'.$board, 'canonicalUrl' => '/co-the/'.$board, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['board' => $fenRegex]);
Route::match(['get', 'post'], '/puzzle/{board}', function ($board) {
return view('puzzle', ['headTitle' => 'Puzzle', 'bodyClass' => 'puzzle', 'board' => $board, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/co-the/'.$board, 'langEnUrl' => '/puzzle/'.$board, 'langJaUrl' => '/pazuru/'.$board, 'langKoUrl' => '/peojeul/'.$board, 'langZhUrl' => '/mi/'.$board, 'canonicalUrl' => '/puzzle/'.$board, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['board' => $fenRegex]);
Route::match(['get', 'post'], '/pazuru/{board}', function ($board) {
return view('puzzle', ['headTitle' => 'パズル', 'bodyClass' => 'puzzle', 'board' => $board, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/co-the/'.$board, 'langEnUrl' => '/puzzle/'.$board, 'langJaUrl' => '/pazuru/'.$board, 'langKoUrl' => '/peojeul/'.$board, 'langZhUrl' => '/mi/'.$board, 'canonicalUrl' => '/pazuru/'.$board, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['board' => $fenRegex]);
Route::match(['get', 'post'], '/peojeul/{board}', function ($board) {
return view('puzzle', ['headTitle' => '퍼즐', 'bodyClass' => 'puzzle', 'board' => $board, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/co-the/'.$board, 'langEnUrl' => '/puzzle/'.$board, 'langJaUrl' => '/pazuru/'.$board, 'langKoUrl' => '/peojeul/'.$board, 'langZhUrl' => '/mi/'.$board, 'canonicalUrl' => '/peojeul/'.$board, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['board' => $fenRegex]);
Route::match(['get', 'post'], '/mi/{board}', function ($board) {
return view('puzzle', ['headTitle' => '谜', 'bodyClass' => 'puzzle', 'board' => $board, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/co-the/'.$board, 'langEnUrl' => '/puzzle/'.$board, 'langJaUrl' => '/pazuru/'.$board, 'langKoUrl' => '/peojeul/'.$board, 'langZhUrl' => '/mi/'.$board, 'canonicalUrl' => '/mi/'.$board, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['board' => $fenRegex]);

Route::match(['get', 'post'], '/ban-co/{fen}', function ($fen) {
return view('board', ['headTitle' => 'Bàn cờ tự giải', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co/'.$fen, 'langEnUrl' => '/board/'.$fen, 'langJaUrl' => '/bodo/'.$fen, 'langKoUrl' => '/bodeu/'.$fen, 'langZhUrl' => '/ban/'.$fen, 'canonicalUrl' => '/ban-co/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:vi');

Route::match(['get', 'post'], '/ban-co-de-nhat/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => 'Bàn cờ dễ nhất', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '1', 'levelTxt' => 'Dễ nhất', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-de-nhat/'.$fen, 'langEnUrl' => '/easiest-board/'.$fen, 'langJaUrl' => '/mottomo-kantanna-bodo/'.$fen, 'langKoUrl' => '/gajang-swiun-bodeu/'.$fen, 'langZhUrl' => '/zuijiandandeban/'.$fen, 'canonicalUrl' => '/ban-co-de-nhat/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:vi');
Route::match(['get', 'post'], '/ban-co-moi-choi/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => 'Bàn cờ mới chơi', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '1', 'levelTxt' => 'Mới chơi', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-moi-choi/'.$fen, 'langEnUrl' => '/newbie-board/'.$fen, 'langJaUrl' => '/shoshinsha-bodo/'.$fen, 'langKoUrl' => '/nyubi-bodeu/'.$fen, 'langZhUrl' => '/xinshouban/'.$fen, 'canonicalUrl' => '/ban-co-moi-choi/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:vi');
Route::match(['get', 'post'], '/ban-co-de/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => 'Bàn cờ dễ', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '2', 'levelTxt' => 'Dễ', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-de/'.$fen, 'langEnUrl' => '/easy-board/'.$fen, 'langJaUrl' => '/kantan-bodo/'.$fen, 'langKoUrl' => '/iji-bodeu/'.$fen, 'langZhUrl' => '/jianyiban/'.$fen, 'canonicalUrl' => '/ban-co-de/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:vi');
Route::match(['get', 'post'], '/ban-co-binh-thuong/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => 'Bàn cờ bình thường', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '3', 'levelTxt' => 'Bình thường', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-binh-thuong/'.$fen, 'langEnUrl' => '/normal-board/'.$fen, 'langJaUrl' => '/tsujo-bodo/'.$fen, 'langKoUrl' => '/nomol-bodeu/'.$fen, 'langZhUrl' => '/putongban/'.$fen, 'canonicalUrl' => '/ban-co-binh-thuong/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:vi');
Route::match(['get', 'post'], '/ban-co-kho/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => 'Bàn cờ khó', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => 'Khó', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-kho/'.$fen, 'langEnUrl' => '/hard-board/'.$fen, 'langJaUrl' => '/hado-bodo/'.$fen, 'langKoUrl' => '/hadeu-bodeu/'.$fen, 'langZhUrl' => '/yingban/'.$fen, 'canonicalUrl' => '/ban-co-kho/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:vi');
Route::match(['get', 'post'], '/ban-co-kho-nhat/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => 'Bàn cờ khó nhất', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => 'Khó nhất', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-kho-nhat/'.$fen, 'langEnUrl' => '/hardest-board/'.$fen, 'langJaUrl' => '/mottomo-muzukashi-bodo/'.$fen, 'langKoUrl' => '/gajang-dandanhan-bodeu/'.$fen, 'langZhUrl' => '/zuiyingban/'.$fen, 'canonicalUrl' => '/ban-co-kho-nhat/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:vi');
Route::match(['get', 'post'], '/giai-co-the/{fen}', function ($fen) {
    $puzzleName = PuzzleController::getNameByFen($fen);
    $headTitle = $puzzleName ? 'Giải cờ thế "' . $puzzleName . '"' : 'Giải cờ thế';
return view('puzzleAi', ['headTitle' => $headTitle, 'bodyClass' => 'puzzle', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '5', 'levelTxt' => 'Khó nhất', 'cdnUrl' => url(''), 'langViUrl' => '/giai-co-the/'.$fen, 'langEnUrl' => '/solve-puzzle/'.$fen, 'langJaUrl' => '/pazuru-o-toku/'.$fen, 'langKoUrl' => '/pojeureul-pulda/'.$fen, 'langZhUrl' => '/jiejuenanti/'.$fen, 'canonicalUrl' => '/giai-co-the/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:vi');

Route::match(['get', 'post'], '/board/{fen}', function ($fen) {
  return view('board', ['headTitle' => 'Board', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co/'.$fen, 'langEnUrl' => '/board/'.$fen, 'langJaUrl' => '/bodo/'.$fen, 'langKoUrl' => '/bodeu/'.$fen, 'langZhUrl' => '/ban/'.$fen, 'canonicalUrl' => '/board/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:en');

Route::match(['get', 'post'], '/easiest-board/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => 'Easiest board', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '1', 'levelTxt' => 'Easiest', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-de-nhat/'.$fen, 'langEnUrl' => '/easiest-board/'.$fen, 'langJaUrl' => '/mottomo-kantanna-bodo/'.$fen, 'langKoUrl' => '/gajang-swiun-bodeu/'.$fen, 'langZhUrl' => '/zuijiandandeban/'.$fen, 'canonicalUrl' => '/easiest-board/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:en');
Route::match(['get', 'post'], '/newbie-board/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => 'Newbie board', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '1', 'levelTxt' => 'Newbie', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-moi-choi/'.$fen, 'langEnUrl' => '/newbie-board/'.$fen, 'langJaUrl' => '/shoshinsha-bodo/'.$fen, 'langKoUrl' => '/nyubi-bodeu/'.$fen, 'langZhUrl' => '/xinshouban/'.$fen, 'canonicalUrl' => '/newbie-board/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:en');
Route::match(['get', 'post'], '/easy-board/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => 'Easy board', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '2', 'levelTxt' => 'Easy', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-de/'.$fen, 'langEnUrl' => '/easy-board/'.$fen, 'langJaUrl' => '/kantan-bodo/'.$fen, 'langKoUrl' => '/iji-bodeu/'.$fen, 'langZhUrl' => '/jianyiban/'.$fen, 'canonicalUrl' => '/easy-board/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:en');
Route::match(['get', 'post'], '/normal-board/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => 'Normal board', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '3', 'levelTxt' => 'Normal', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-binh-thuong/'.$fen, 'langEnUrl' => '/normal-board/'.$fen, 'langJaUrl' => '/tsujo-bodo/'.$fen, 'langKoUrl' => '/nomol-bodeu/'.$fen, 'langZhUrl' => '/putongban/'.$fen, 'canonicalUrl' => '/normal-board/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:en');
Route::match(['get', 'post'], '/hard-board/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => 'Hard board', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => 'Hard', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-kho/'.$fen, 'langEnUrl' => '/hard-board/'.$fen, 'langJaUrl' => '/hado-bodo/'.$fen, 'langKoUrl' => '/hadeu-bodeu/'.$fen, 'langZhUrl' => '/yingban/'.$fen, 'canonicalUrl' => '/hard-board/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:en');
Route::match(['get', 'post'], '/hardest-board/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => 'Hardest board', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => 'Hardest', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-kho-nhat/'.$fen, 'langEnUrl' => '/hardest-board/'.$fen, 'langJaUrl' => '/mottomo-muzukashi-bodo/'.$fen, 'langKoUrl' => '/gajang-dandanhan-bodeu/'.$fen, 'langZhUrl' => '/zuiyingban/'.$fen, 'canonicalUrl' => '/hardest-board/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:en');
Route::match(['get', 'post'], '/solve-puzzle/{fen}', function ($fen) {
    $puzzleName = PuzzleController::getNameByFen($fen);
    $headTitle = $puzzleName ? 'Solve puzzle "' . $puzzleName . '"' : 'Solve puzzle';
return view('puzzleAi', ['headTitle' => $headTitle, 'bodyClass' => 'puzzle', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '5', 'levelTxt' => 'Hardest', 'cdnUrl' => url(''), 'langViUrl' => '/giai-co-the/'.$fen, 'langEnUrl' => '/solve-puzzle/'.$fen, 'langJaUrl' => '/pazuru-o-toku/'.$fen, 'langKoUrl' => '/pojeureul-pulda/'.$fen, 'langZhUrl' => '/jiejuenanti/'.$fen, 'canonicalUrl' => '/solve-puzzle/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:en');

Route::match(['get', 'post'], '/bodo/{fen}', function ($fen) {
  return view('board', ['headTitle' => 'ボード', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co/'.$fen, 'langEnUrl' => '/board/'.$fen, 'langJaUrl' => '/bodo/'.$fen, 'langKoUrl' => '/bodeu/'.$fen, 'langZhUrl' => '/ban/'.$fen, 'canonicalUrl' => '/bodo/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:ja');

Route::match(['get', 'post'], '/mottomo-kantanna-bodo/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => '最も簡単なボード', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '1', 'levelTxt' => '最も簡単', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-de-nhat/'.$fen, 'langEnUrl' => '/easiest-board/'.$fen, 'langJaUrl' => '/mottomo-kantanna-bodo/'.$fen, 'langKoUrl' => '/gajang-swiun-bodeu/'.$fen, 'langZhUrl' => '/zuijiandandeban/'.$fen, 'canonicalUrl' => '/mottomo-kantanna-bodo/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:ja');
Route::match(['get', 'post'], '/shoshinsha-bodo/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => '初心者ボード', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '1', 'levelTxt' => '初心者', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-moi-choi/'.$fen, 'langEnUrl' => '/newbie-board/'.$fen, 'langJaUrl' => '/shoshinsha-bodo/'.$fen, 'langKoUrl' => '/nyubi-bodeu/'.$fen, 'langZhUrl' => '/xinshouban/'.$fen, 'canonicalUrl' => '/shoshinsha-bodo/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:ja');
Route::match(['get', 'post'], '/kantan-bodo/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => 'イージーボード', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '2', 'levelTxt' => '簡単', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-de/'.$fen, 'langEnUrl' => '/easy-board/'.$fen, 'langJaUrl' => '/kantan-bodo/'.$fen, 'langKoUrl' => '/iji-bodeu/'.$fen, 'langZhUrl' => '/jianyiban/'.$fen, 'canonicalUrl' => '/kantan-bodo/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:ja');
Route::match(['get', 'post'], '/tsujo-bodo/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => '通常ボード', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '3', 'levelTxt' => 'ツジョ', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-binh-thuong/'.$fen, 'langEnUrl' => '/normal-board/'.$fen, 'langJaUrl' => '/tsujo-bodo/'.$fen, 'langKoUrl' => '/nomol-bodeu/'.$fen, 'langZhUrl' => '/putongban/'.$fen, 'canonicalUrl' => '/tsujo-bodo/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:ja');
Route::match(['get', 'post'], '/hado-bodo/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => 'ハードボード', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => 'ハード', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-kho/'.$fen, 'langEnUrl' => '/hard-board/'.$fen, 'langJaUrl' => '/hado-bodo/'.$fen, 'langKoUrl' => '/hadeu-bodeu/'.$fen, 'langZhUrl' => '/yingban/'.$fen, 'canonicalUrl' => '/hado-bodo/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:ja');
Route::match(['get', 'post'], '/mottomo-muzukashi-bodo/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => '最も難しいボード', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => '最も難しい', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-kho-nhat/'.$fen, 'langEnUrl' => '/hardest-board/'.$fen, 'langJaUrl' => '/mottomo-muzukashi-bodo/'.$fen, 'langKoUrl' => '/gajang-dandanhan-bodeu/'.$fen, 'langZhUrl' => '/zuiyingban/'.$fen, 'canonicalUrl' => '/mottomo-muzukashi-bodo/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:ja');
Route::match(['get', 'post'], '/pazuru-o-toku/{fen}', function ($fen) {
    $puzzleName = PuzzleController::getNameByFen($fen);
    $headTitle = $puzzleName ? 'パズルを解く "' . $puzzleName . '"' : 'パズルを解く';
return view('puzzleAi', ['headTitle' => $headTitle, 'bodyClass' => 'puzzle', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '5', 'levelTxt' => '最も難しい', 'cdnUrl' => url(''), 'langViUrl' => '/giai-co-the/'.$fen, 'langEnUrl' => '/solve-puzzle/'.$fen, 'langJaUrl' => '/pazuru-o-toku/'.$fen, 'langKoUrl' => '/pojeureul-pulda/'.$fen, 'langZhUrl' => '/jiejuenanti/'.$fen, 'canonicalUrl' => '/pazuru-o-toku/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:ja');

Route::match(['get', 'post'], '/bodeu/{fen}', function ($fen) {
return view('board', ['headTitle' => '보드', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co/'.$fen, 'langEnUrl' => '/board/'.$fen, 'langJaUrl' => '/bodo/'.$fen, 'langKoUrl' => '/bodeu/'.$fen, 'langZhUrl' => '/ban/'.$fen, 'canonicalUrl' => '/bodeu/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:ko');

Route::match(['get', 'post'], '/gajang-swiun-bodeu/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => '가장 쉬운 보드', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '1', 'levelTxt' => '가장 쉬운', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-de-nhat/'.$fen, 'langEnUrl' => '/easiest-board/'.$fen, 'langJaUrl' => '/mottomo-kantanna-bodo/'.$fen, 'langKoUrl' => '/gajang-swiun-bodeu/'.$fen, 'langZhUrl' => '/zuijiandandeban/'.$fen, 'canonicalUrl' => '/gajang-swiun-bodeu/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:ko');
Route::match(['get', 'post'], '/nyubi-bodeu/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => '뉴비 보드', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '1', 'levelTxt' => '뉴비', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-moi-choi/'.$fen, 'langEnUrl' => '/newbie-board/'.$fen, 'langJaUrl' => '/shoshinsha-bodo/'.$fen, 'langKoUrl' => '/nyubi-bodeu/'.$fen, 'langZhUrl' => '/xinshouban/'.$fen, 'canonicalUrl' => '/nyubi-bodeu/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:ko');
Route::match(['get', 'post'], '/iji-bodeu/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => '이지보드', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '2', 'levelTxt' => '쉬운', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-de/'.$fen, 'langEnUrl' => '/easy-board/'.$fen, 'langJaUrl' => '/kantan-bodo/'.$fen, 'langKoUrl' => '/iji-bodeu/'.$fen, 'langZhUrl' => '/jianyiban/'.$fen, 'canonicalUrl' => '/iji-bodeu/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:ko');
Route::match(['get', 'post'], '/nomol-bodeu/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => '노멀 보드', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '3', 'levelTxt' => '노멀', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-binh-thuong/'.$fen, 'langEnUrl' => '/normal-board/'.$fen, 'langJaUrl' => '/tsujo-bodo/'.$fen, 'langKoUrl' => '/nomol-bodeu/'.$fen, 'langZhUrl' => '/putongban/'.$fen, 'canonicalUrl' => '/nomol-bodeu/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:ko');
Route::match(['get', 'post'], '/hadeu-bodeu/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => '하드보드', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => '하드', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-kho/'.$fen, 'langEnUrl' => '/hard-board/'.$fen, 'langJaUrl' => '/hado-bodo/'.$fen, 'langKoUrl' => '/hadeu-bodeu/'.$fen, 'langZhUrl' => '/yingban/'.$fen, 'canonicalUrl' => '/hadeu-bodeu/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:ko');
Route::match(['get', 'post'], '/gajang-dandanhan-bodeu/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => '가장 단단한 보드', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => '가장 단단한', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-kho-nhat/'.$fen, 'langEnUrl' => '/hardest-board/'.$fen, 'langJaUrl' => '/mottomo-muzukashi-bodo/'.$fen, 'langKoUrl' => '/gajang-dandanhan-bodeu/'.$fen, 'langZhUrl' => '/zuiyingban/'.$fen, 'canonicalUrl' => '/gajang-dandanhan-bodeu/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:ko');
Route::match(['get', 'post'], '/pojeureul-pulda/{fen}', function ($fen) {
    $puzzleName = PuzzleController::getNameByFen($fen);
    $headTitle = $puzzleName ? '퍼즐을 풀다 "' . $puzzleName . '"' : '퍼즐을 풀다';
return view('puzzleAi', ['headTitle' => $headTitle, 'bodyClass' => 'puzzle', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '5', 'levelTxt' => '가장 단단한', 'cdnUrl' => url(''), 'langViUrl' => '/giai-co-the/'.$fen, 'langEnUrl' => '/solve-puzzle/'.$fen, 'langJaUrl' => '/pazuru-o-toku/'.$fen, 'langKoUrl' => '/pojeureul-pulda/'.$fen, 'langZhUrl' => '/jiejuenanti/'.$fen, 'canonicalUrl' => '/pojeureul-pulda/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:ko');

Route::match(['get', 'post'], '/ban/{fen}', function ($fen) {
  return view('board', ['headTitle' => '板', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co/'.$fen, 'langEnUrl' => '/board/'.$fen, 'langJaUrl' => '/bodo/'.$fen, 'langKoUrl' => '/bodeu/'.$fen, 'langZhUrl' => '/ban/'.$fen, 'canonicalUrl' => '/ban/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:zh');

Route::match(['get', 'post'], '/zuijiandandeban/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => '最简单的板', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '1', 'levelTxt' => '最容易的', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-de-nhat/'.$fen, 'langEnUrl' => '/easiest-board/'.$fen, 'langJaUrl' => '/mottomo-kantanna-bodo/'.$fen, 'langKoUrl' => '/gajang-swiun-bodeu/'.$fen, 'langZhUrl' => '/zuijiandandeban/'.$fen, 'canonicalUrl' => '/zuijiandandeban/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:zh');
Route::match(['get', 'post'], '/xinshouban/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => '新手板', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '1', 'levelTxt' => '新手', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-moi-choi/'.$fen, 'langEnUrl' => '/newbie-board/'.$fen, 'langJaUrl' => '/shoshinsha-bodo/'.$fen, 'langKoUrl' => '/nyubi-bodeu/'.$fen, 'langZhUrl' => '/xinshouban/'.$fen, 'canonicalUrl' => '/xinshouban/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:zh');
Route::match(['get', 'post'], '/jianyiban/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => '简易板', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '2', 'levelTxt' => '容易的', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-de/'.$fen, 'langEnUrl' => '/easy-board/'.$fen, 'langJaUrl' => '/kantan-bodo/'.$fen, 'langKoUrl' => '/iji-bodeu/'.$fen, 'langZhUrl' => '/jianyiban/'.$fen, 'canonicalUrl' => '/jianyiban/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:zh');
Route::match(['get', 'post'], '/putongban/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => '普通板', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '3', 'levelTxt' => '典型的', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-binh-thuong/'.$fen, 'langEnUrl' => '/normal-board/'.$fen, 'langJaUrl' => '/tsujo-bodo/'.$fen, 'langKoUrl' => '/nomol-bodeu/'.$fen, 'langZhUrl' => '/putongban/'.$fen, 'canonicalUrl' => '/putongban/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:zh');
Route::match(['get', 'post'], '/yingban/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => '硬板', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => '坚固的', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-kho/'.$fen, 'langEnUrl' => '/hard-board/'.$fen, 'langJaUrl' => '/hado-bodo/'.$fen, 'langKoUrl' => '/hadeu-bodeu/'.$fen, 'langZhUrl' => '/yingban/'.$fen, 'canonicalUrl' => '/yingban/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:zh');
Route::match(['get', 'post'], '/zuiyingban/{fen}', function ($fen) {
return view('boardAi', ['headTitle' => '最难的', 'bodyClass' => 'home', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '4', 'levelTxt' => '最难的', 'cdnUrl' => url(''), 'langViUrl' => '/ban-co-kho-nhat/'.$fen, 'langEnUrl' => '/hardest-board/'.$fen, 'langJaUrl' => '/mottomo-muzukashi-bodo/'.$fen, 'langKoUrl' => '/gajang-dandanhan-bodeu/'.$fen, 'langZhUrl' => '/zuiyingban/'.$fen, 'canonicalUrl' => '/zuiyingban/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:zh');
Route::match(['get', 'post'], '/jiejuenanti/{fen}', function ($fen) {
    $puzzleName = PuzzleController::getNameByFen($fen);
    $headTitle = $puzzleName ? '解决难题 "' . $puzzleName . '"' : '解决难题';
return view('puzzleAi', ['headTitle' => $headTitle, 'bodyClass' => 'puzzle', 'fen' => $fen, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'level' => '5', 'levelTxt' => '最难的', 'cdnUrl' => url(''), 'langViUrl' => '/giai-co-the/'.$fen, 'langEnUrl' => '/solve-puzzle/'.$fen, 'langJaUrl' => '/pazuru-o-toku/'.$fen, 'langKoUrl' => '/pojeureul-pulda/'.$fen, 'langZhUrl' => '/jiejuenanti/'.$fen, 'canonicalUrl' => '/jiejuenanti/'.$fen, 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->where(['fen' => $fenRegex])->middleware('locale:zh');

Route::match(['get', 'post'], '', function () {
return view('ai', ['headTitle' => 'Trang chủ', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '', 'langEnUrl' => '/en', 'langJaUrl' => '/ja', 'langKoUrl' => '/ko', 'langZhUrl' => '/zh', 'level' => '3', 'levelTxt' => 'Bình thường', 'canonicalUrl' => '', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:vi');
Route::match(['get', 'post'], '/de-nhat', function () {
return view('ai', ['headTitle' => 'Dễ nhất', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/de-nhat', 'langEnUrl' => '/easiest', 'langJaUrl' => '/mottomo-kantan', 'langKoUrl' => '/gajang-swiun', 'langZhUrl' => '/zuirongyide', 'level' => '1', 'levelTxt' => 'Dễ nhất', 'canonicalUrl' => '/de-nhat', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:vi');
Route::match(['get', 'post'], '/moi-choi', function () {
  return view('ai', ['headTitle' => 'Mới chơi', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/moi-choi', 'langEnUrl' => '/newbie', 'langJaUrl' => '/shoshinsha', 'langKoUrl' => '/nyubi', 'langZhUrl' => '/xinshou', 'level' => '1', 'levelTxt' => 'Mới chơi', 'canonicalUrl' => '/moi-choi', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:vi');
Route::match(['get', 'post'], '/de', function () {
return view('ai', ['headTitle' => 'Dễ', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/de', 'langEnUrl' => '/easy', 'langJaUrl' => '/kantan', 'langKoUrl' => '/iji', 'langZhUrl' => '/rongyide', 'level' => '2', 'levelTxt' => 'Dễ', 'canonicalUrl' => '/de', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:vi');
Route::match(['get', 'post'], '/binh-thuong', function () {
return view('ai', ['headTitle' => 'Bình thường', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/binh-thuong', 'langEnUrl' => '/normal', 'langJaUrl' => '/tsujo', 'langKoUrl' => '/nomol', 'langZhUrl' => '/dianxingde', 'level' => '3', 'levelTxt' => 'Bình thường', 'canonicalUrl' => '/binh-thuong', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:vi');
Route::match(['get', 'post'], '/kho', function () {
return view('ai', ['headTitle' => 'Khó', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/kho', 'langEnUrl' => '/hard', 'langJaUrl' => '/muzukashi', 'langKoUrl' => '/hadeu', 'langZhUrl' => '/jiangude', 'level' => '4', 'levelTxt' => 'Khó', 'canonicalUrl' => '/kho', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:vi');
Route::match(['get', 'post'], '/kho-nhat', function () {
return view('ai', ['headTitle' => 'Khó nhất', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/kho-nhat', 'langEnUrl' => '/hardest', 'langJaUrl' => '/mottomo-muzukashi', 'langKoUrl' => '/gajang-dandanhan', 'langZhUrl' => '/zuinande', 'level' => '5', 'levelTxt' => 'Khó nhất', 'canonicalUrl' => '/kho-nhat', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:vi');
Route::match(['get', 'post'], '/en', function () {
return view('ai', ['headTitle' => 'Home', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '', 'langEnUrl' => '/en', 'langJaUrl' => '/ja', 'langKoUrl' => '/ko', 'langZhUrl' => '/zh', 'level' => '3', 'levelTxt' => 'Normal', 'canonicalUrl' => '/en', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:en');
Route::match(['get', 'post'], '/easiest', function () {
return view('ai', ['headTitle' => 'Easiest', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/de-nhat', 'langEnUrl' => '/easiest', 'langJaUrl' => '/mottomo-kantan', 'langKoUrl' => '/gajang-swiun', 'langZhUrl' => '/zuirongyide', 'level' => '1', 'levelTxt' => 'Easiest', 'canonicalUrl' => '/easiest', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:en');
Route::match(['get', 'post'], '/newbie', function () {
  return view('ai', ['headTitle' => 'Newbie', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/moi-choi', 'langEnUrl' => '/newbie', 'langJaUrl' => '/shoshinsha', 'langKoUrl' => '/nyubi', 'langZhUrl' => '/xinshou', 'level' => '1', 'levelTxt' => 'Newbie', 'canonicalUrl' => '/newbie', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:en');
Route::match(['get', 'post'], '/easy', function () {
return view('ai', ['headTitle' => 'Easy', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/de', 'langEnUrl' => '/easy', 'langJaUrl' => '/kantan', 'langKoUrl' => '/iji', 'langZhUrl' => '/rongyide', 'level' => '2', 'levelTxt' => 'Easy', 'canonicalUrl' => '/easy', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:en');
Route::match(['get', 'post'], '/normal', function () {
return view('ai', ['headTitle' => 'Normal', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/binh-thuong', 'langEnUrl' => '/normal', 'langJaUrl' => '/tsujo', 'langKoUrl' => '/nomol', 'langZhUrl' => '/dianxingde', 'level' => '3', 'levelTxt' => 'Normal', 'canonicalUrl' => '/normal', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:en');
Route::match(['get', 'post'], '/hard', function () {
return view('ai', ['headTitle' => 'Hard', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/kho', 'langEnUrl' => '/hard', 'langJaUrl' => '/muzukashi', 'langKoUrl' => '/hadeu', 'langZhUrl' => '/jiangude', 'level' => '4', 'levelTxt' => 'Hard', 'canonicalUrl' => '/hard', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:en');
Route::match(['get', 'post'], '/hardest', function () {
return view('ai', ['headTitle' => 'Hardest', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/kho-nhat', 'langEnUrl' => '/hardest', 'langJaUrl' => '/mottomo-muzukashi', 'langKoUrl' => '/gajang-dandanhan', 'langZhUrl' => '/zuinande', 'level' => '5', 'levelTxt' => 'Hardest', 'canonicalUrl' => '/hardest', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:en');
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
return view('ai', ['headTitle' => 'ホームページ', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '', 'langEnUrl' => '/en', 'langJaUrl' => '/ja', 'langKoUrl' => '/ko', 'langZhUrl' => '/zh', 'level' => '3', 'levelTxt' => 'ツジョ', 'canonicalUrl' => '/ja', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:ja');
Route::match(['get', 'post'], '/mottomo-kantan', function () {
return view('ai', ['headTitle' => '最も簡単', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/de-nhat', 'langEnUrl' => '/easiest', 'langJaUrl' => '/mottomo-kantan', 'langKoUrl' => '/gajang-swiun', 'langZhUrl' => '/zuirongyide', 'level' => '1', 'levelTxt' => '最も簡単', 'canonicalUrl' => '/mottomo-kantan', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:ja');
Route::match(['get', 'post'], '/shoshinsha', function () {
  return view('ai', ['headTitle' => '初心者', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/moi-choi', 'langEnUrl' => '/newbie', 'langJaUrl' => '/shoshinsha', 'langKoUrl' => '/nyubi', 'langZhUrl' => '/xinshou', 'level' => '1', 'levelTxt' => '初心者', 'canonicalUrl' => '/shoshinsha', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:ja');
Route::match(['get', 'post'], '/kantan', function () {
return view('ai', ['headTitle' => '簡単', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/de', 'langEnUrl' => '/easy', 'langJaUrl' => '/kantan', 'langKoUrl' => '/iji', 'langZhUrl' => '/rongyide', 'level' => '2', 'levelTxt' => '簡単', 'canonicalUrl' => '/kantan', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:ja');
Route::match(['get', 'post'], '/tsujo', function () {
return view('ai', ['headTitle' => 'ツジョ', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/binh-thuong', 'langEnUrl' => '/normal', 'langJaUrl' => '/tsujo', 'langKoUrl' => '/nomol', 'langZhUrl' => '/dianxingde', 'level' => '3', 'levelTxt' => 'ツジョ', 'canonicalUrl' => '/tsujo', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:ja');
Route::match(['get', 'post'], '/hado', function () {
return view('ai', ['headTitle' => 'ハード', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/kho', 'langEnUrl' => '/hard', 'langJaUrl' => '/muzukashi', 'langKoUrl' => '/hadeu', 'langZhUrl' => '/jiangude', 'level' => '4', 'levelTxt' => 'ハード', 'canonicalUrl' => '/muzukashi', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:ja');
Route::match(['get', 'post'], '/mottomo-muzukashi', function () {
return view('ai', ['headTitle' => '最も難しい', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/kho-nhat', 'langEnUrl' => '/hardest', 'langJaUrl' => '/mottomo-muzukashi', 'langKoUrl' => '/gajang-dandanhan', 'langZhUrl' => '/zuinande', 'level' => '5', 'levelTxt' => '最も難しい', 'canonicalUrl' => '/mottomo-muzukashi', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:ja');

Route::match(['get', 'post'], '/ko', function () {
return view('ai', ['headTitle' => '홈페이지', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '', 'langEnUrl' => '/en', 'langJaUrl' => '/ja', 'langKoUrl' => '/ko', 'langZhUrl' => '/zh', 'level' => '3', 'levelTxt' => '노멀', 'canonicalUrl' => '/ko', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:ko');
Route::match(['get', 'post'], '/gajang-swiun', function () {
return view('ai', ['headTitle' => '가장 쉬운', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/de-nhat', 'langEnUrl' => '/easiest', 'langJaUrl' => '/mottomo-kantan', 'langKoUrl' => '/gajang-swiun', 'langZhUrl' => '/zuirongyide', 'level' => '1', 'levelTxt' => '가장 쉬운', 'canonicalUrl' => '/gajang-swiun', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:ko');
Route::match(['get', 'post'], '/nyubi', function () {
  return view('ai', ['headTitle' => '뉴비', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/moi-choi', 'langEnUrl' => '/newbie', 'langJaUrl' => '/shoshinsha', 'langKoUrl' => '/nyubi', 'langZhUrl' => '/xinshou', 'level' => '1', 'levelTxt' => '뉴비', 'canonicalUrl' => '/nyubi', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:ko');
Route::match(['get', 'post'], '/iji', function () {
return view('ai', ['headTitle' => '쉬운', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/de', 'langEnUrl' => '/easy', 'langJaUrl' => '/kantan', 'langKoUrl' => '/iji', 'langZhUrl' => '/rongyide', 'level' => '2', 'levelTxt' => '쉬운', 'canonicalUrl' => '/iji', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:ko');
Route::match(['get', 'post'], '/nomol', function () {
return view('ai', ['headTitle' => '노멀', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/binh-thuong', 'langEnUrl' => '/normal', 'langJaUrl' => '/tsujo', 'langKoUrl' => '/nomol', 'langZhUrl' => '/dianxingde', 'level' => '3', 'levelTxt' => '노멀', 'canonicalUrl' => '/nomol', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:ko');
Route::match(['get', 'post'], '/hadeu', function () {
return view('ai', ['headTitle' => '하드', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/kho', 'langEnUrl' => '/hard', 'langJaUrl' => '/muzukashi', 'langKoUrl' => '/hadeu', 'langZhUrl' => '/jiangude', 'level' => '4', 'levelTxt' => '하드', 'canonicalUrl' => '/hadeu', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:ko');
Route::match(['get', 'post'], '/gajang-dandanhan', function () {
return view('ai', ['headTitle' => '가장 단단한', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/kho-nhat', 'langEnUrl' => '/hardest', 'langJaUrl' => '/mottomo-muzukashi', 'langKoUrl' => '/gajang-dandanhan', 'langZhUrl' => '/zuinande', 'level' => '5', 'levelTxt' => '가장 단단한', 'canonicalUrl' => '/gajang-dandanhan', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:ko');


Route::match(['get', 'post'], '/zh', function () {
return view('ai', ['headTitle' => '主页', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '', 'langEnUrl' => '/en', 'langJaUrl' => '/ja', 'langKoUrl' => '/ko', 'langZhUrl' => '/zh', 'level' => '3', 'levelTxt' => '典型的', 'canonicalUrl' => '/zh', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:zh');
Route::match(['get', 'post'], '/zuirongyide', function () {
return view('ai', ['headTitle' => '最容易的', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/de-nhat', 'langEnUrl' => '/easiest', 'langJaUrl' => '/mottomo-kantan', 'langKoUrl' => '/gajang-swiun', 'langZhUrl' => '/zuirongyide', 'level' => '1', 'levelTxt' => '最容易的', 'canonicalUrl' => '/zuirongyide', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:zh');
Route::match(['get', 'post'], '/xinshou', function () {
  return view('ai', ['headTitle' => '新手', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/moi-choi', 'langEnUrl' => '/newbie', 'langJaUrl' => '/shoshinsha', 'langKoUrl' => '/nyubi', 'langZhUrl' => '/xinshou', 'level' => '1', 'levelTxt' => '新手', 'canonicalUrl' => '/xinshou', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:zh');
Route::match(['get', 'post'], '/rongyide', function () {
return view('ai', ['headTitle' => '容易的', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/de', 'langEnUrl' => '/easy', 'langJaUrl' => '/kantan', 'langKoUrl' => '/iji', 'langZhUrl' => '/rongyide', 'level' => '2', 'levelTxt' => '容易的', 'canonicalUrl' => '/rongyide', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:zh');
Route::match(['get', 'post'], '/dianxingde', function () {
return view('ai', ['headTitle' => '典型的', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/binh-thuong', 'langEnUrl' => '/normal', 'langJaUrl' => '/tsujo', 'langKoUrl' => '/nomol', 'langZhUrl' => '/dianxingde', 'level' => '3', 'levelTxt' => '典型的', 'canonicalUrl' => '/dianxingde', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:zh');
Route::match(['get', 'post'], '/jiangude', function () {
return view('ai', ['headTitle' => '坚固的', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/kho', 'langEnUrl' => '/hard', 'langJaUrl' => '/muzukashi', 'langKoUrl' => '/hadeu', 'langZhUrl' => '/jiangude', 'level' => '4', 'levelTxt' => '坚固的', 'canonicalUrl' => '/jiangude', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:zh');
Route::match(['get', 'post'], '/zuinande', function () {
return view('ai', ['headTitle' => '最难的', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/kho-nhat', 'langEnUrl' => '/hardest', 'langJaUrl' => '/mottomo-muzukashi', 'langKoUrl' => '/gajang-dandanhan', 'langZhUrl' => '/zuinande', 'level' => '5', 'levelTxt' => '最难的', 'canonicalUrl' => '/zuinande', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
})->middleware('locale:zh');

$localizedStaticPages = [
  'about' => [
    'vi' => ['view' => 'about', 'title' => 'Giới thiệu'],
    'en' => ['view' => 'about', 'title' => 'About us'],
    'ja' => ['view' => 'about', 'title' => '約'],
    'ko' => ['view' => 'about', 'title' => '우리에 대해'],
    'zh' => ['view' => 'about', 'title' => '关于我们'],
  ],
  'contact' => [
    'vi' => ['view' => 'contact', 'title' => 'Liên hệ'],
    'en' => ['view' => 'contact', 'title' => 'Contact us'],
    'ja' => ['view' => 'contact', 'title' => 'コンタクト'],
    'ko' => ['view' => 'contact', 'title' => '문의하기'],
    'zh' => ['view' => 'contact', 'title' => '联系我们'],
  ],
];

foreach ($localizedStaticPages as $pageKey => $localizedPages) {
  foreach ($localizedPages as $locale => $page) {
    Route::match(['get', 'post'], localized_path($pageKey, [], $locale), function () use ($pageKey, $locale, $page) {
      return view($page['view'], localized_page_data($pageKey, $locale, [
        'headTitle' => $page['title'],
        'bodyClass' => $pageKey,
        'randomRoom' => RoomController::getRandomRoom(),
        'roomCode' => '',
        'cdnUrl' => url(''),
      ]));
    })->middleware("locale:{$locale}");
  }
}

$localizedRoomListPages = [
  'vi' => ['view' => 'roomList', 'title' => 'Sảnh chờ'],
  'en' => ['view' => 'roomList', 'title' => "Rooms' list"],
  'ja' => ['view' => 'roomList', 'title' => '部屋一覧'],
  'ko' => ['view' => 'roomList', 'title' => '방 목록'],
  'zh' => ['view' => 'roomList', 'title' => '房间列表'],
];

foreach ($localizedRoomListPages as $locale => $page) {
  Route::match(['get', 'post'], localized_path('room.list', [], $locale), function () use ($locale, $page) {
    $data = [
      'headTitle' => $page['title'],
      'bodyClass' => 'room',
      'rooms' => Room::all(),
      'roomCode' => '',
      'randomRoom' => RoomController::getRandomRoom(),
      'cdnUrl' => url(''),
    ];

    $data = array_merge($data, [
        'userPuzzles' => PuzzleController::getUserPuzzles(),
        'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(),
        'boards' => RoomController::getBoards(),
        'firstPageBoards' => RoomController::getFirstPageBoards(),
        'playedBoards' => RoomController::getPlayedBoards(),
        'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(),
        'players' => UserController::getPlayers(),
        'firstPagePlayers' => UserController::getFirstPagePlayers(),
      ]);

    return view($page['view'], localized_page_data('room.list', $locale, $data));
  })->middleware("locale:{$locale}");
}
Route::match(['get', 'post'], '/tat-ca-the-co', function () {
  return view('puzzleList', ['headTitle' => 'Tất cả thế cờ', 'bodyClass' => 'puzzle setup', 'rooms' => Room::all(), 'roomCode' => '', 'randomRoom' => RoomController::getRandomRoom(), 'cdnUrl' => url(''), 'langViUrl' => '/tat-ca-the-co', 'langEnUrl' => '/en', 'langJaUrl' => '/ja', 'langKoUrl' => '/ko', 'langZhUrl' => '/zh', 'canonicalUrl' => '/tat-ca-the-co', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
});
Route::match(['get', 'post'], '/thanh-vien', function () {
  return view('userList', ['headTitle' => 'Tất cả kỳ thủ', 'bodyClass' => 'room', 'rooms' => Room::all(), 'roomCode' => '', 'randomRoom' => RoomController::getRandomRoom(), 'cdnUrl' => url(''), 'langViUrl' => '/thanh-vien', 'langEnUrl' => '/en', 'langJaUrl' => '/ja', 'langKoUrl' => '/ko', 'langZhUrl' => '/zh', 'canonicalUrl' => '/thanh-vien', 'userPuzzles' => PuzzleController::getUserPuzzles(), 'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(), 'boards' => RoomController::getBoards(), 'firstPageBoards' => RoomController::getFirstPageBoards(), 'playedBoards' => RoomController::getPlayedBoards(), 'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(), 'players' => UserController::getPlayers(), 'firstPagePlayers' => UserController::getFirstPagePlayers()]);
});
