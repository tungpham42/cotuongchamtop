<?php

use App\Models\Room;
use App\Models\User;
use App\Models\Puzzle;
use App\Models\Tournament;
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

// ==========================================
// TOURNAMENT ROUTES (Unified Localized Setup)
// ==========================================

// 1. Register an inline middleware to verify tournament ownership
Route::aliasMiddleware('tournament.creator', function ($request, $next) {
    $slug = $request->route('slug');
    $tournament = Tournament::where('slug', $slug)->firstOrFail();

    // Check if the authenticated user is the owner.
    // IMPORTANT: Change 'user_id' below to match your database column (e.g., 'host_id' or 'creator_id')
    if ($tournament->user_id !== auth()->id() && !auth()->user()->is_admin) {
        abort(403, __('Bạn không có quyền quản lý giải đấu này.'));
    }

    return $next($request);
});

// ==========================================
// TOURNAMENT ROUTES (Unified Localized Setup)
// ==========================================
$localizedTournamentPages = [
    // --- PUBLIC ROUTES ---
    'tournaments.index' => [
        'action' => [TournamentController::class, 'index'],
        'params' => [],
        'methods' => ['get'],
        'middleware' => [],
        'titles' => [
            'vi' => 'Danh sách Giải đấu', 'en' => 'Tournament List', 'ja' => 'トーナメント一覧', 'ko' => '토너먼트 목록', 'zh' => '锦标赛列表',
        ],
    ],
    'tournaments.show' => [
        'action' => [TournamentController::class, 'show'],
        'params' => ['slug' => '{slug}'],
        'methods' => ['get'],
        'middleware' => [],
        'titles' => [
            'vi' => 'Chi tiết Giải đấu', 'en' => 'Tournament Details', 'ja' => 'トーナメントの詳細', 'ko' => '토너먼트 세부 정보', 'zh' => '锦标赛详情',
        ],
    ],

    // --- AUTHENTICATED ROUTES (Player Actions) ---
    'tournaments.join' => [
        'action' => [TournamentController::class, 'join'],
        'params' => ['slug' => '{slug}'],
        'methods' => ['post'],
        'middleware' => ['auth'],
    ],

    // --- AUTHENTICATED ROUTES (Admin Actions) ---
    'tournaments.generate' => [
        'action' => [TournamentController::class, 'generateBracket'],
        'params' => ['slug' => '{slug}'],
        'methods' => ['post'],
        'middleware' => ['auth', 'tournament.creator'], // Protected
    ],
    'tournaments.create' => [
        'action' => [TournamentController::class, 'create'],
        'params' => [],
        'methods' => ['get'],
        'middleware' => ['auth'], // Any auth user can view create form
        'titles' => [
            'vi' => 'Tạo Giải đấu', 'en' => 'Create Tournament', 'ja' => 'トーナメント作成', 'ko' => '토너먼트 만들기', 'zh' => '创建锦标赛',
        ],
    ],
    'tournaments.store' => [
        'action' => [TournamentController::class, 'store'],
        'params' => [],
        'methods' => ['post'],
        'middleware' => ['auth'], // Any auth user can submit new tournament
    ],
    'tournaments.edit' => [
        'action' => [TournamentController::class, 'edit'],
        'params' => ['slug' => '{slug}'],
        'methods' => ['get'],
        'middleware' => ['auth', 'tournament.creator'], // Protected
        'titles' => [
            'vi' => 'Sửa Giải đấu', 'en' => 'Edit Tournament', 'ja' => 'トーナメント編集', 'ko' => '토너먼트 편집', 'zh' => '编辑锦标赛',
        ],
    ],
    'tournaments.update' => [
        'action' => [TournamentController::class, 'update'],
        'params' => ['slug' => '{slug}'],
        'methods' => ['put'],
        'middleware' => ['auth', 'tournament.creator'], // Protected
    ],
    'tournaments.destroy' => [
        'action' => [TournamentController::class, 'destroy'],
        'params' => ['slug' => '{slug}'],
        'methods' => ['delete'],
        'middleware' => ['auth', 'tournament.creator'], // Protected
    ],
];

foreach ($localizedTournamentPages as $pageKey => $page) {
    foreach (config('locales.supported', []) as $locale) {

        $methods = $page['methods'] ?? ['get'];

        $route = Route::match($methods, localized_path($pageKey, $page['params'], $locale), $page['action'])
            ->middleware("locale:{$locale}");

        // Inject the translated head title if applicable
        if (isset($page['titles'])) {
            $headTitle = $page['titles'][$locale] ?? $page['titles']['vi'];
            $route->defaults('headTitle', $headTitle);
        }

        // Apply specific middleware (like 'auth')
        if (!empty($page['middleware'])) {
            $route->middleware($page['middleware']);
        }

        // Retain original route names for the default locale to prevent component breaks.
        if ($locale === config('locales.default', 'vi')) {
            $route->name($pageKey);
        } else {
            $route->name("{$locale}.{$pageKey}");
        }
    }
}

Route::post('/startTimer/{roomCode}/{player}', [RoomController::class, 'startTimer']);
Route::post('/pauseTimer/{roomCode}/{player}', [RoomController::class, 'pauseTimer']);
Route::post('/switchTurn/{roomCode}', [RoomController::class, 'switchTurn']);
Route::get('/getTime/{roomCode}', [RoomController::class, 'getTime']);
Route::post('/saveTime/{roomCode}', [RoomController::class, 'saveTime']);

// ==========================================
// UNIFIED MATCHMAKING ROUTES (Guests & Auth)
// ==========================================
Route::post('/match/find', [RoomController::class, 'findMatch'])->name('match.find');
Route::get('/match/status', [RoomController::class, 'checkMatchStatus'])->name('match.status');

Route::get('/terms-and-conditions', function () {
  return view('terms', localized_page_data('terms', app()->getLocale(), ['headTitle' => 'Terms and Conditions', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url('')]));
});
Route::get('/privacy-policy', function () {
  return view('privacy', localized_page_data('privacy', app()->getLocale(), ['headTitle' => 'Privacy Policy', 'bodyClass' => 'home', 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url('')]));
});

Route::get('/getUserPuzzlesTemplate', function(){
  return view('layout.partials.userPuzzles')->render();
});

Route::get('/rooms', function () {
  return redirect('/lobby', 301);
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

      // --- ANTI-CHEAT: Prevent re-entering finished rooms as a player ---
      if (!is_null($room->result)) {
        if (in_array($pageKey, ['room.host', 'room.guest', 'room.red', 'room.black'])) {
          return redirect()->to(localized_path('room.watch', ['code' => $code], $locale));
        }
      }

      // --- ANTI-CHEAT: Prevent re-entering finished tournament rooms as a player ---
      if (!is_null($room->tournament_id) && !is_null($room->result)) {
        if (in_array($pageKey, ['room.host', 'room.guest', 'room.red', 'room.black'])) {
          abort(403, __('Trận đấu giải này đã kết thúc. Bạn chỉ có thể xem lại ở chế độ theo dõi.'));
        }
      }

      // --- ANTI-CHEAT: Prevent URL manipulation to join specific sides ---
      if (isset($room->host_id)) {
        // Block unauthorized access to the Red/Host side
        if (in_array($pageKey, ['room.host', 'room.red'])) {
          if (!auth()->check() || auth()->id() != $room->host_id) {
            abort(403, __('Bạn không có quyền truy cập vào phe Đỏ / Chủ phòng.'));
          }
        }
        // Block unauthorized access to the Black/Guest side
        elseif (in_array($pageKey, ['room.guest', 'room.black'])) {
          if (!auth()->check() || auth()->id() != $room->guest_id) {
            abort(403, __('Bạn không có quyền truy cập vào phe Đen / Khách.'));
          }
        }
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

      return view($view, localized_page_data($pageKey, $locale, $data, ['code' => $code]));
    })->middleware("locale:{$locale}");
  }
}



// Route::group(['prefix' => 'admin'], function () {
//     Voyager::routes();
// });

// Loop through supported locales to dynamically generate DataTables endpoints
foreach (['vi', 'en', 'ja', 'ko', 'zh'] as $locale) {
    $ucLocale = ucfirst($locale);
    Route::get("/puzzles/{$locale}", [PuzzleController::class, "getPuzzles{$ucLocale}"])->name("puzzles{$ucLocale}.list");
    Route::get("/users/{$locale}", [UserController::class, "getUsers{$ucLocale}"])->name("users{$ucLocale}.list");
    Route::get("/rooms/{$locale}", [RoomController::class, "getRooms{$ucLocale}"])->name("rooms{$ucLocale}.list");
}
// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/home', function () {
  return redirect('/thi-dau', 301);
});

// ==========================================
// LOCALIZED APP PAGES (Dashboard, History, Profile, etc.)
// ==========================================
$localizedAppPages = [
    'app.dashboard' => [
        'view' => 'app/home',
        'middleware' => [],
        'titles' => ['vi' => 'Thi đấu', 'en' => 'Compete', 'ja' => '競技', 'ko' => '경쟁', 'zh' => '竞争'],
        // We use closures for data so DB queries don't run on route registration
        'data' => fn() => [
            'bodyClass' => 'dashboard',
            'matchUsers' => UserController::getMatchUsers(),
            'matchRooms' => RoomController::getMatchRooms(),
            'playingRooms' => RoomController::getPlayingRooms(),
            'playedRooms' => RoomController::getPlayedRooms(),
            'rankUsers' => UserController::getRankUsers(),
            'onlinePlayers' => UserController::onlinePlayers()
        ]
    ],
    'app.history' => [
        'view' => 'app/history',
        'middleware' => [],
        'titles' => ['vi' => 'Lịch sử thi đấu', 'en' => 'Match History', 'ja' => '対戦履歴', 'ko' => '경기 기록', 'zh' => '比赛历史'],
        'data' => fn() => [
            'bodyClass' => 'dashboard',
            'matchUsers' => UserController::getMatchUsers(),
            'matchRooms' => RoomController::getMatchRooms(),
            'playingRooms' => RoomController::getPlayingRooms(),
            'playedRooms' => RoomController::getPlayedRooms(),
            'rankUsers' => UserController::getRankUsers()
        ]
    ],
    'app.ranking' => [
        'view' => 'app/ranking',
        'middleware' => [],
        'titles' => ['vi' => 'Bảng xếp hạng', 'en' => 'Ranking', 'ja' => 'ランキング', 'ko' => '순위표', 'zh' => '排行榜'],
        'data' => fn() => [
            'bodyClass' => 'dashboard',
            'users' => UserController::getUsers(),
            'matchRooms' => RoomController::getMatchRooms(),
            'rankUsers' => UserController::getRankUsers()
        ]
    ],
    'app.password' => [
        'view' => 'app/changePassword',
        'middleware' => ['auth'],
        'titles' => ['vi' => 'Đổi mật khẩu', 'en' => 'Change Password', 'ja' => 'パスワード変更', 'ko' => '비밀번호 변경', 'zh' => '更改密码'],
        'data' => fn() => [
            'bodyClass' => 'player profile',
            'player' => Auth::user(),
            'users' => UserController::getUsers(),
            'matchRooms' => RoomController::getMatchRooms(),
            'rankUsers' => UserController::getRankUsers(),
            'playerRooms' => RoomController::getPlayerRooms(Auth::user()->id)
        ]
    ],
    'app.name' => [
        'view' => 'app/changeName',
        'middleware' => ['auth'],
        'titles' => ['vi' => 'Đổi tên', 'en' => 'Change Name', 'ja' => '名前変更', 'ko' => '이름 변경', 'zh' => '更改名称'],
        'data' => fn() => [
            'bodyClass' => 'player profile',
            'player' => Auth::user(),
            'users' => UserController::getUsers(),
            'matchRooms' => RoomController::getMatchRooms(),
            'rankUsers' => UserController::getRankUsers(),
            'playerRooms' => RoomController::getPlayerRooms(Auth::user()->id)
        ]
    ],
    'app.ui' => [
        'view' => 'app/changeUi',
        'middleware' => ['auth'],
        'titles' => ['vi' => 'Đổi giao diện', 'en' => 'Change UI', 'ja' => 'UI変更', 'ko' => 'UI 변경', 'zh' => '更改界面'],
        'data' => fn() => [
            'bodyClass' => 'player profile',
            'player' => Auth::user(),
            'users' => UserController::getUsers(),
            'matchRooms' => RoomController::getMatchRooms(),
            'rankUsers' => UserController::getRankUsers(),
            'playerRooms' => RoomController::getPlayerRooms(Auth::user()->id)
        ]
    ],
    'app.profile' => [
        'view' => 'app/player',
        'middleware' => ['auth'],
        'titles' => ['vi' => 'Hồ sơ của tôi', 'en' => 'My Profile', 'ja' => 'マイプロフィール', 'ko' => '내 프로필', 'zh' => '我的资料'],
        'data' => fn() => [
            'bodyClass' => 'player profile',
            'player' => Auth::user(),
            'users' => UserController::getUsers(),
            'matchRooms' => RoomController::getMatchRooms(),
            'rankUsers' => UserController::getRankUsers(),
            'playerRooms' => RoomController::getPlayerRooms(Auth::user()->id)
        ]
    ],
    'search' => [
        'view' => 'app.search',
        'middleware' => [],
        'titles' => ['vi' => 'Tìm kiếm', 'en' => 'Search', 'ja' => '検索', 'ko' => '검색', 'zh' => '搜索'],
        'data' => fn() => [
            'bodyClass' => 'search',
            'matchRooms' => RoomController::getMatchRooms(),
            'rankUsers' => UserController::getRankUsers(),
            'results' => request('query') 
                ? User::where('name', 'LIKE', '%'.request('query').'%')
                    ->orWhere('email', 'LIKE', '%'.request('query').'%')
                    ->paginate(10)
                    ->appends(['query' => request('query')])
                : null
        ]
    ],
];

// 1. Loop through static unparameterized app pages
foreach ($localizedAppPages as $pageKey => $pageSettings) {
    foreach (config('locales.supported', []) as $locale) {
        $route = Route::match(['get', 'post'], localized_path($pageKey, [], $locale), function () use ($pageKey, $locale, $pageSettings) {

            $data = $pageSettings['data'](); // Execute closure for fresh DB results
            $data['headTitle'] = $pageSettings['titles'][$locale] ?? $pageSettings['titles']['vi'];

            return view($pageSettings['view'], localized_page_data($pageKey, $locale, $data));
        })->middleware("locale:{$locale}");

        if (!empty($pageSettings['middleware'])) {
            $route->middleware($pageSettings['middleware']);
        }
    }
}

// 2. Localized Parameterized Route (Player Profile by ID)
$localizedPlayerPages = [
    'app.player' => [
        'view' => 'app/player',
        'titles' => [
            'vi' => fn($id) => 'Hồ sơ kỳ thủ "' . UserController::getUserName($id) . '"',
            'en' => fn($id) => 'Player Profile "' . UserController::getUserName($id) . '"',
            'ja' => fn($id) => 'プレイヤープロフィール "' . UserController::getUserName($id) . '"',
            'ko' => fn($id) => '플레이어 프로필 "' . UserController::getUserName($id) . '"',
            'zh' => fn($id) => '玩家资料 "' . UserController::getUserName($id) . '"',
        ],
    ]
];

foreach (config('locales.supported', []) as $locale) {
    Route::match(['get', 'post'], localized_path('app.player', ['id' => '{id}'], $locale), function ($id) use ($locale, $localizedPlayerPages) {

        $pageSettings = $localizedPlayerPages['app.player'];
        $data = [
            'headTitle' => $pageSettings['titles'][$locale]($id),
            'bodyClass' => 'player',
            'player' => User::firstWhere('id', $id),
            'users' => UserController::getUsers(),
            'matchRooms' => RoomController::getMatchRooms(),
            'rankUsers' => UserController::getRankUsers(),
            'playerRooms' => RoomController::getPlayerRooms($id)
        ];

        return view($pageSettings['view'], localized_page_data('app.player', $locale, $data, ['id' => $id]));
    })->middleware("locale:{$locale}");
}

// Ensure this single standalone route stays as it acts as an internal API/Partial endpoint
Route::get('/rankTableHtml', function() {
  return view('layout/partials/app/rankTableHtml', ['users' => UserController::getUsers(), 'matchRooms' => RoomController::getMatchRooms(), 'rankUsers' => UserController::getRankUsers()]);
});

// ==========================================
// LOCALIZED AUTH PAGES
// ==========================================
$localizedAuthPages = [
    'login' => [
        'action' => 'Auth\LoginController@showLoginForm',
        'params' => [],
        'titles' => [
            'vi' => 'Đăng nhập',
            'en' => 'Login',
            'ja' => 'ログイン',
            'ko' => '로그인',
            'zh' => '登录',
        ],
    ],
    'register' => [
        'action' => 'Auth\RegisterController@showRegistrationForm',
        'params' => [],
        'titles' => [
            'vi' => 'Đăng ký',
            'en' => 'Register',
            'ja' => '登録',
            'ko' => '회원가입',
            'zh' => '注册',
        ],
    ],
    'password.request' => [
        'action' => 'Auth\ForgotPasswordController@showLinkRequestForm',
        'params' => [],
        'titles' => [
            'vi' => 'Quên mật khẩu',
            'en' => 'Forgot Password',
            'ja' => 'パスワードを忘れた場合',
            'ko' => '비밀번호 찾기',
            'zh' => '忘记密码',
        ],
    ],
    'password.create' => [
        'action' => 'Auth\ForgotPasswordController@showLinkRequestForm',
        'params' => [],
        'titles' => [
            'vi' => 'Tạo mật khẩu',
            'en' => 'Create Password',
            'ja' => 'パスワード作成',
            'ko' => '비밀번호 생성',
            'zh' => '创建密码',
        ],
    ],
    'password.reset' => [
        'action' => 'Auth\ResetPasswordController@showResetForm',
        'params' => ['token' => '{token}'],
        'titles' => [
            'vi' => 'Đặt lại mật khẩu',
            'en' => 'Reset Password',
            'ja' => 'パスワードリセット',
            'ko' => '비밀번호 재설정',
            'zh' => '重置密码',
        ],
    ],
];

foreach ($localizedAuthPages as $pageKey => $page) {
    foreach (config('locales.supported', []) as $locale) {
        $headTitle = $page['titles'][$locale] ?? $page['titles']['vi'];

        $route = Route::get(localized_path($pageKey, $page['params'], $locale), $page['action'])
            ->middleware("locale:{$locale}")
            ->defaults('headTitle', $headTitle);

        // Retain original route names for the default locale to prevent Auth component breaks.
        // For localized versions, prefix them with the locale.
        if ($locale === config('locales.default', 'vi')) {
            $route->name($pageKey);
        } else {
            $route->name("{$locale}.{$pageKey}");
        }
    }
}

Route::post('dang-xuat', [LoginController::class, 'logout'])->name('logout');
// Route::get('dang-nhap', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('dang-nhap', 'Auth\LoginController@login');

// Route::get('dang-ky', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('dang-ky', 'Auth\RegisterController@register');

// Route::get('quen-mat-khau', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
// Route::get('tao-mat-khau', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.create');
Route::post('gui-duong-dan-tao-mat-khau', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
// Route::get('dat-lai-mat-khau/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('quen-mat-khau', 'Auth\ResetPasswordController@reset')->name('password.update');

Route::middleware('auth')->post('/payos/standard', [PayOSController::class, 'createStandard'])->name('payos.standard');
Route::get('/payos/return', [PayOSController::class, 'handleReturn'])->name('payos.return');
Route::get('/payos/cancel', [PayOSController::class, 'handleCancel'])->name('payos.cancel');
Route::post('/payos/webhook', [PayOSController::class, 'webhook'])->name('payos.webhook');

// ==========================================
// LOCALIZED SETTING PAGES (Form Actions)
// ==========================================
$localizedSettingPages = [
    'change.password' => [
        'action' => [UserController::class, 'changePassword'],
    ],
    'change.name' => [
        'action' => [UserController::class, 'changeName'],
    ],
    'change.ui' => [
        'action' => [UserController::class, 'changeUserInterface'],
    ],
];

foreach ($localizedSettingPages as $pageKey => $page) {
    foreach (config('locales.supported', []) as $locale) {
        // We use POST since these are form submission actions
        $route = Route::post(localized_path($pageKey, [], $locale), $page['action'])
            ->middleware("locale:{$locale}");

        // Retain original route names for the default locale to prevent blade component breaks
        if ($locale === config('locales.default', 'vi')) {
            $route->name($pageKey);
        } else {
            $route->name("{$locale}.{$pageKey}");
        }
    }
}

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

// ==========================================
// LOCALIZED HUMAN PAGES (Play alone)
// ==========================================
$localizedHumanPages = [
    'human.play' => [
        'bodyClass' => 'home',
        'titles' => [
            'vi' => 'Chơi một mình',
            'en' => 'Play alone',
            'ja' => '一人で遊ぶ',
            'ko' => '혼자 놀다',
            'zh' => '独处',
        ]
    ]
];

foreach ($localizedHumanPages['human.play']['titles'] as $locale => $title) {
    Route::match(['get', 'post'], localized_path('human.play', [], $locale), function () use ($locale, $title) {
        return view('human', localized_page_data('human.play', $locale, [
            'headTitle' => $title,
            'bodyClass' => 'home',
            'randomRoom' => RoomController::getRandomRoom(),
            'roomCode' => '',
            'cdnUrl' => url(''),
        ]));
    })->middleware("locale:{$locale}");
}

Route::match(['get', 'post'], '/thach-dau/{board}', function ($board) {
return view('puzzleCompete', ['headTitle' => 'Thách đấu', 'bodyClass' => 'puzzle', 'board' => $board, 'randomRoom' => RoomController::getRandomRoom(), 'roomCode' => '', 'cdnUrl' => url(''), 'langViUrl' => '/', 'langEnUrl' => '/en', 'langJaUrl' => '/ja', 'langKoUrl' => '/ko', 'langZhUrl' => '/zh', 'canonicalUrl' => '/thach-dau/'.$board]);
})->where(['board' => $fenRegex]);

// Define the titles for your puzzle rating routes
$localizedPuzzleRatingPages = [
    'puzzle.rating' => [
        'titles' => [
            'vi' => 'Thế cờ',
            'en' => 'Puzzle',
            'ja' => 'パズル',
            'ko' => '퍼즐',
            'zh' => '谜',
        ]
    ]
];

foreach ($localizedPuzzleRatingPages['puzzle.rating']['titles'] as $locale => $title) {
    Route::match(['get', 'post'], localized_path('puzzle.rating', ['slug' => '{slug}'], $locale), function ($slug) use ($locale, $title) {
        $puzzle = Puzzle::where('slug', $slug)->firstOrFail();

        $headTitle = $puzzle->name ? $title . ' "' . $puzzle->name . '"' : $title;

        return view('puzzleRating', localized_page_data('puzzle.rating', $locale, [
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
            'cdnUrl' => url(''), // Retained here as it relies on the dynamic url() helper
        ], ['slug' => $puzzle->slug]));
    })->middleware("locale:{$locale}");
}

Route::match(['get', 'post'], '/getUserPuzzlesTemplate', function(){
return view('layout.partials.userPuzzles')->render();
});

// ==========================================
// LOCALIZED PUZZLE PAGES (Setup & Board)
// ==========================================
$localizedPuzzlePages = [
    'puzzle.setup' => [
        'bodyClass' => 'puzzle setup',
        'titles' => [
            'vi' => 'Xếp bàn cờ thế',
            'en' => 'Set up the puzzle',
            'ja' => 'パズルを組み立てる',
            'ko' => '퍼즐',
            'zh' => '谜',
        ]
    ],
    'puzzle.board' => [
        'bodyClass' => 'puzzle',
        'titles' => [
            'vi' => 'Bàn cờ thế',
            'en' => 'Puzzle',
            'ja' => 'パズル',
            'ko' => '퍼즐',
            'zh' => '谜',
        ]
    ]
];

// 1. Puzzle Setup (Empty Board)
foreach ($localizedPuzzlePages['puzzle.setup']['titles'] as $locale => $title) {
    Route::match(['get', 'post'], localized_path('puzzle.setup', [], $locale), function () use ($locale, $title) {
        return view('puzzle', localized_page_data('puzzle.setup', $locale, [
            'headTitle' => $title,
            'bodyClass' => 'puzzle setup',
            'board' => '',
            'randomRoom' => RoomController::getRandomRoom(),
            'roomCode' => '',
            'cdnUrl' => url(''),
        ]));
    })->middleware("locale:{$locale}");
}

// 2. Puzzle Board (With FEN/Board string)
foreach ($localizedPuzzlePages['puzzle.board']['titles'] as $locale => $title) {
    Route::match(['get', 'post'], localized_path('puzzle.board', ['board' => '{board}'], $locale), function ($board) use ($locale, $title) {
        return view('puzzle', localized_page_data('puzzle.board', $locale, [
            'headTitle' => $title,
            'bodyClass' => 'puzzle',
            'board' => $board,
            'randomRoom' => RoomController::getRandomRoom(),
            'roomCode' => '',
            'cdnUrl' => url(''),
        ], ['board' => $board]));
    })->where(['board' => $fenRegex])->middleware("locale:{$locale}");
}

// ==========================================
// LOCALIZED FEN BOARD & BOARD AI PAGES
// ==========================================
$localizedBoardPages = [
    'board.self' => [
        'view' => 'board',
        'titles' => [
            'vi' => 'Bàn cờ tự giải',
            'en' => 'Board',
            'ja' => 'ボード',
            'ko' => '보드',
            'zh' => '板',
        ],
    ],
    'board.ai.easiest' => [
        'view' => 'boardAi',
        'level' => '1',
        'titles' => [
            'vi' => ['title' => 'Bàn cờ dễ nhất', 'levelTxt' => 'Dễ nhất'],
            'en' => ['title' => 'Easiest board', 'levelTxt' => 'Easiest'],
            'ja' => ['title' => '最も簡単なボード', 'levelTxt' => '最も簡単'],
            'ko' => ['title' => '가장 쉬운 보드', 'levelTxt' => '가장 쉬운'],
            'zh' => ['title' => '最简单的板', 'levelTxt' => '最容易的'],
        ],
    ],
    'board.ai.newbie' => [
        'view' => 'boardAi',
        'level' => '1',
        'titles' => [
            'vi' => ['title' => 'Bàn cờ mới chơi', 'levelTxt' => 'Mới chơi'],
            'en' => ['title' => 'Newbie board', 'levelTxt' => 'Newbie'],
            'ja' => ['title' => '初心者ボード', 'levelTxt' => '初心者'],
            'ko' => ['title' => '뉴비 보드', 'levelTxt' => '뉴비'],
            'zh' => ['title' => '新手板', 'levelTxt' => '新手'],
        ],
    ],
    'board.ai.easy' => [
        'view' => 'boardAi',
        'level' => '2',
        'titles' => [
            'vi' => ['title' => 'Bàn cờ dễ', 'levelTxt' => 'Dễ'],
            'en' => ['title' => 'Easy board', 'levelTxt' => 'Easy'],
            'ja' => ['title' => 'イージーボード', 'levelTxt' => '簡単'],
            'ko' => ['title' => '이지보드', 'levelTxt' => '쉬운'],
            'zh' => ['title' => '简易板', 'levelTxt' => '容易的'],
        ],
    ],
    'board.ai.normal' => [
        'view' => 'boardAi',
        'level' => '3',
        'titles' => [
            'vi' => ['title' => 'Bàn cờ bình thường', 'levelTxt' => 'Bình thường'],
            'en' => ['title' => 'Normal board', 'levelTxt' => 'Normal'],
            'ja' => ['title' => '通常ボード', 'levelTxt' => 'ツジョ'],
            'ko' => ['title' => '노멀 보드', 'levelTxt' => '노멀'],
            'zh' => ['title' => '普通板', 'levelTxt' => '典型的'],
        ],
    ],
    'board.ai.hard' => [
        'view' => 'boardAi',
        'level' => '4',
        'titles' => [
            'vi' => ['title' => 'Bàn cờ khó', 'levelTxt' => 'Khó'],
            'en' => ['title' => 'Hard board', 'levelTxt' => 'Hard'],
            'ja' => ['title' => 'ハードボード', 'levelTxt' => 'ハード'],
            'ko' => ['title' => '하드보드', 'levelTxt' => '하드'],
            'zh' => ['title' => '硬板', 'levelTxt' => '坚固的'],
        ],
    ],
    'board.ai.hardest' => [
        'view' => 'boardAi',
        'level' => '5',
        'titles' => [
            'vi' => ['title' => 'Bàn cờ khó nhất', 'levelTxt' => 'Khó nhất'],
            'en' => ['title' => 'Hardest board', 'levelTxt' => 'Hardest'],
            'ja' => ['title' => '最も難しいボード', 'levelTxt' => '最も難しい'],
            'ko' => ['title' => '가장 단단한 보드', 'levelTxt' => '가장 단단한'],
            'zh' => ['title' => '最难的', 'levelTxt' => '最难的'],
        ],
    ],
    'puzzle.ai.solve' => [
        'view' => 'puzzleAi',
        'level' => '6',
        'titles' => [
            'vi' => ['title' => 'Giải cờ thế', 'levelTxt' => 'Khó nhất'],
            'en' => ['title' => 'Solve puzzle', 'levelTxt' => 'Hardest'],
            'ja' => ['title' => 'パズルを解く', 'levelTxt' => '最も難しい'],
            'ko' => ['title' => '퍼즐을 풀다', 'levelTxt' => '가장 단단한'],
            'zh' => ['title' => '解决难题', 'levelTxt' => '最难的'],
        ],
    ]
];

foreach ($localizedBoardPages as $pageKey => $pageData) {
    foreach ($pageData['titles'] as $locale => $localeData) {
        Route::match(['get', 'post'], localized_path($pageKey, ['fen' => '{fen}'], $locale), function ($fen) use ($pageKey, $pageData, $locale, $localeData) {

            // 1. Setup base view data
            $viewData = [
                'bodyClass' => ($pageData['view'] === 'puzzleAi') ? 'puzzle' : 'home',
                'fen' => $fen,
                'randomRoom' => RoomController::getRandomRoom(),
                'roomCode' => '',
                'cdnUrl' => url(''),
            ];

            // 2. Parse differences between standard board and AI boards
            if ($pageData['view'] === 'board') {
                $viewData['headTitle'] = $localeData;
            } else {
                $baseTitle = $localeData['title'];

                // Inject dynamic puzzle name specifically for puzzleAi
                if ($pageData['view'] === 'puzzleAi') {
                    $puzzleName = PuzzleController::getNameByFen($fen);
                    $viewData['headTitle'] = $puzzleName ? $baseTitle . ' "' . $puzzleName . '"' : $baseTitle;
                } else {
                    $viewData['headTitle'] = $baseTitle;
                }

                $viewData['level'] = $pageData['level'];
                $viewData['levelTxt'] = $localeData['levelTxt'];
            }

            return view($pageData['view'], localized_page_data($pageKey, $locale, $viewData, ['fen' => $fen]));
        })->where(['fen' => $fenRegex])->middleware("locale:{$locale}");
    }
}

$localizedLevelPages = [
    'ai.home' => [
        'vi' => ['title' => 'Trang chủ', 'level' => '3', 'levelTxt' => 'Bình thường'],
        'en' => ['title' => 'Home', 'level' => '3', 'levelTxt' => 'Normal'],
        'ja' => ['title' => 'ホームページ', 'level' => '3', 'levelTxt' => 'ツジョ'],
        'ko' => ['title' => '홈페이지', 'level' => '3', 'levelTxt' => '노멀'],
        'zh' => ['title' => '主页', 'level' => '3', 'levelTxt' => '典型的'],
    ],
    'ai.easiest' => [
        'vi' => ['title' => 'Dễ nhất', 'level' => '1', 'levelTxt' => 'Dễ nhất'],
        'en' => ['title' => 'Easiest', 'level' => '1', 'levelTxt' => 'Easiest'],
        'ja' => ['title' => '最も簡単', 'level' => '1', 'levelTxt' => '最も簡単'],
        'ko' => ['title' => '가장 쉬운', 'level' => '1', 'levelTxt' => '가장 쉬운'],
        'zh' => ['title' => '最容易的', 'level' => '1', 'levelTxt' => '最容易的'],
    ],
    'ai.newbie' => [
        'vi' => ['title' => 'Mới chơi', 'level' => '1', 'levelTxt' => 'Mới chơi'],
        'en' => ['title' => 'Newbie', 'level' => '1', 'levelTxt' => 'Newbie'],
        'ja' => ['title' => '初心者', 'level' => '1', 'levelTxt' => '初心者'],
        'ko' => ['title' => '뉴비', 'level' => '1', 'levelTxt' => '뉴비'],
        'zh' => ['title' => '新手', 'level' => '1', 'levelTxt' => '新手'],
    ],
    'ai.easy' => [
        'vi' => ['title' => 'Dễ', 'level' => '2', 'levelTxt' => 'Dễ'],
        'en' => ['title' => 'Easy', 'level' => '2', 'levelTxt' => 'Easy'],
        'ja' => ['title' => '簡単', 'level' => '2', 'levelTxt' => '簡単'],
        'ko' => ['title' => '쉬운', 'level' => '2', 'levelTxt' => '쉬운'],
        'zh' => ['title' => '容易的', 'level' => '2', 'levelTxt' => '容易的'],
    ],
    'ai.normal' => [
        'vi' => ['title' => 'Bình thường', 'level' => '3', 'levelTxt' => 'Bình thường'],
        'en' => ['title' => 'Normal', 'level' => '3', 'levelTxt' => 'Normal'],
        'ja' => ['title' => 'ツジョ', 'level' => '3', 'levelTxt' => 'ツジョ'],
        'ko' => ['title' => '노멀', 'level' => '3', 'levelTxt' => '노멀'],
        'zh' => ['title' => '典型的', 'level' => '3', 'levelTxt' => '典型的'],
    ],
    'ai.hard' => [
        'vi' => ['title' => 'Khó', 'level' => '4', 'levelTxt' => 'Khó'],
        'en' => ['title' => 'Hard', 'level' => '4', 'levelTxt' => 'Hard'],
        'ja' => ['title' => 'ハード', 'level' => '4', 'levelTxt' => 'ハード'],
        'ko' => ['title' => '하드', 'level' => '4', 'levelTxt' => '하드'],
        'zh' => ['title' => '坚固的', 'level' => '4', 'levelTxt' => '坚固的'],
    ],
    'ai.hardest' => [
        'vi' => ['title' => 'Khó nhất', 'level' => '5', 'levelTxt' => 'Khó nhất'],
        'en' => ['title' => 'Hardest', 'level' => '5', 'levelTxt' => 'Hardest'],
        'ja' => ['title' => '最も難しい', 'level' => '5', 'levelTxt' => '最も難しい'],
        'ko' => ['title' => '가장 단단한', 'level' => '5', 'levelTxt' => '가장 단단한'],
        'zh' => ['title' => '最难的', 'level' => '5', 'levelTxt' => '最难的'],
    ],
    'ai.master' => [
        'vi' => ['title' => 'Kiện tướng', 'level' => '8', 'levelTxt' => 'Kiện tướng'],
        'en' => ['title' => 'Master', 'level' => '8', 'levelTxt' => 'Master'],
        'ja' => ['title' => 'マスター', 'level' => '8', 'levelTxt' => 'マスター'],
        'ko' => ['title' => '마스터', 'level' => '8', 'levelTxt' => '마스터'],
        'zh' => ['title' => '大师级', 'level' => '8', 'levelTxt' => '大师级'],
    ],
];

foreach ($localizedLevelPages as $pageKey => $localizedPages) {
    foreach ($localizedPages as $locale => $page) {
        Route::match(['get', 'post'], localized_path($pageKey, [], $locale), function () use ($pageKey, $locale, $page) {
            return view('ai', localized_page_data($pageKey, $locale, [
                'headTitle' => $page['title'],
                'bodyClass' => 'home',
                'randomRoom' => RoomController::getRandomRoom(),
                'roomCode' => '',
                'cdnUrl' => url(''),
                'level' => $page['level'],
                'levelTxt' => $page['levelTxt'],
            ]));
        })->middleware("locale:{$locale}");
    }
}

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
  'puzzle.list' => [
    'vi' => ['view' => 'puzzleList', 'title' => 'Tất cả thế cờ'],
    'en' => ['view' => 'puzzleList', 'title' => 'All puzzles'],
    'ja' => ['view' => 'puzzleList', 'title' => 'すべてのパズル'],
    'ko' => ['view' => 'puzzleList', 'title' => '모든 퍼즐'],
    'zh' => ['view' => 'puzzleList', 'title' => '所有谜题'],
  ],
  'user.list' => [
    'vi' => ['view' => 'userList', 'title' => 'Tất cả kỳ thủ'],
    'en' => ['view' => 'userList', 'title' => 'All players'],
    'ja' => ['view' => 'userList', 'title' => 'すべてのプレイヤー'],
    'ko' => ['view' => 'userList', 'title' => '모든 플레이어'],
    'zh' => ['view' => 'userList', 'title' => '所有玩家'],
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
  'en' => ['view' => 'roomList', 'title' => "Lobby"],
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

    return view($page['view'], localized_page_data('room.list', $locale, $data));
  })->middleware("locale:{$locale}");
}
