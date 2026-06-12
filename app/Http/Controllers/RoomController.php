<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Room;
use App\Models\User;
use App\Events\RoomUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\GameController;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use DataTables;
use App\Jobs\QuickMatchJob;
use App\Jobs\AnonymousQuickMatchJob;
use Illuminate\Support\Str;
use Atrox\Haikunator;
use Illuminate\Support\Facades\Schema;

class RoomController extends Controller
{
    /**
     * Shared logic for generating Room DataTables across all locales.
     * Applies Auth logic uniformly and dynamically generates localized routes.
     */
    private function getRoomsData(Request $request, string $locale)
    {
        if ($request->ajax()) {
            $rooms = Room::select(['fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at']);

            // Localized text dictionary mapped exactly to original translations
            $texts = [
                'vi' => ['public' => 'Công khai', 'private' => 'Riêng tư', 'red' => 'Đỏ', 'black' => 'Đen', 'guest_won' => 'Đen thắng', 'draw' => 'Hòa', 'host_won' => 'Đỏ thắng', 'not_started' => 'Chưa bắt đầu', 'ongoing' => 'Đang đấu', 'play' => 'Chơi', 'watch' => 'Theo dõi', 'finished_btn' => 'Đã xong', 'finished_auth' => 'Đã đấu xong', 'play_now' => 'Chơi nào', 'login' => 'Đăng nhập', 'preview' => 'Xem trước'],
                'en' => ['public' => 'Public', 'private' => 'Private', 'red' => 'Red', 'black' => 'Black', 'guest_won' => 'Guest won', 'draw' => 'Draw', 'host_won' => 'Host won', 'not_started' => 'Not started', 'ongoing' => 'Ongoing', 'play' => 'Play', 'watch' => 'Watch', 'finished_btn' => 'Finished', 'finished_auth' => 'Finished', 'play_now' => 'Play now', 'login' => 'Login', 'preview' => 'Preview'],
                'ja' => ['public' => '公衆', 'private' => '民間', 'red' => '赤', 'black' => '黒', 'guest_won' => 'ゲストが勝ちました', 'draw' => 'ドローです', 'host_won' => 'ホストが勝ちました', 'not_started' => '開始されていない', 'ongoing' => '現在進行中', 'play' => '加入', 'watch' => '見る', 'finished_btn' => '終わり', 'finished_auth' => '終わり', 'play_now' => '加入', 'login' => 'ログイン', 'preview' => 'プレビュー'],
                'ko' => ['public' => '공공의', 'private' => '사적인', 'red' => '홍', 'black' => '검', 'guest_won' => '손님이 이겼어요', 'draw' => '동점입니다', 'host_won' => '주최자가 이겼어요', 'not_started' => '아직 시작되지 않음', 'ongoing' => '진행 중인', 'play' => '참여', 'watch' => '보다', 'finished_btn' => '끝났다', 'finished_auth' => '끝났다', 'play_now' => '참여', 'login' => '로그인', 'preview' => '미리보기'],
                'zh' => ['public' => '平民的', 'private' => '私有的', 'red' => '红', 'black' => '黑', 'guest_won' => '客人赢了', 'draw' => '平局', 'host_won' => '主办方赢了', 'not_started' => '未开始', 'ongoing' => '进行中的', 'play' => '参加', 'watch' => '看', 'finished_btn' => '结束', 'finished_auth' => '结束', 'play_now' => '参加', 'login' => '登录', 'preview' => '预览'],
            ];

            $t = $texts[$locale] ?? $texts['en'];
            $routePrefix = ($locale === 'vi') ? '' : "{$locale}.";

            return Datatables::of($rooms)
                ->addColumn('code', function($row) use ($t) {
                    $roomNameRaw = (isset($row->name) && $row->name != '') ? $row->name : $row->code;
                    // Royal Theme Room Badge
                    $roomNameHtml = '<span class="badge badge-status" style="background: rgba(20, 22, 28, 0.85); border: 1px solid var(--royal-gold); color: var(--royal-gold); box-shadow: 0 0 10px rgba(212, 175, 55, 0.2);"><i class="fas fa-chess-board"></i> ' . $roomNameRaw . '</span>';

                    $iconClass = ($row->pass == '') ? 'fa-globe' : 'fa-lock';
                    $iconTooltip = ($row->pass == '') ? $t['public'] : $t['private'];
                    $iconHtml = '<i class="ml-2 far ' . $iconClass . ' text-warning" data-toggle="tooltip" data-placement="top" data-original-title="' . $iconTooltip . '"></i>';

                    if (!isset($row->host_id)) {
                        return '<a style="text-decoration: none !important;" class="disabled" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0)">' . $roomNameHtml . '</a>' . $iconHtml;
                    } else {
                        if (auth()->check()) {
                            if (isset($row->result)) {
                                $statusIcon = '<i class="ml-2 far fa-archive text-secondary" data-toggle="tooltip" data-placement="top" data-original-title="'.$t['finished_auth'].'"></i>';
                                return '<a class="showPromotion" href="javascript:void(0)" style="text-decoration: none !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'">' . $roomNameHtml . '</a>' . $statusIcon;
                            } else {
                                $statusIcon = '<i class="ml-2 far fa-mouse text-warning pulse-gold" data-toggle="tooltip" data-placement="top" data-original-title="'.$t['play_now'].'"></i>';
                                return '<a href="javascript:joinMatch(`'.$row->code.'`)" style="text-decoration: none !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'">' . $roomNameHtml . '</a>' . $statusIcon;
                            }
                        } else {
                            $statusIcon = '<i class="ml-2 far fa-sign-in text-warning" data-toggle="tooltip" data-placement="top" data-original-title="'.$t['login'].'"></i>';
                            return '<a style="text-decoration: none !important;" href="javascript:void(0)" data-fen="'.$row->fen.'" data-code="'.$row->code.'">' . $roomNameHtml . '</a>' . $statusIcon;
                        }
                    }
                })
                ->addColumn('turn', function($row) use ($t) {
                    if (str_contains($row->fen, ' r ')) {
                        // Royal Red gradient
                        return '<span class="badge badge-status" style="background: linear-gradient(to bottom, #8a1515, #5c0a0a); color: var(--royal-gold); border: 1px solid var(--royal-gold); box-shadow: 0 0 8px rgba(138, 21, 21, 0.6);"><i class="fas fa-chess-knight"></i> '.$t['red'].'</span>';
                    } else if (str_contains($row->fen, ' b ')) {
                        // Deep Obsidian gradient
                        return '<span class="badge badge-status" style="background: linear-gradient(145deg, #252a36, #121418); color: var(--royal-gold-light); border: 1px solid rgba(212, 175, 55, 0.3); box-shadow: 0 0 8px rgba(0, 0, 0, 0.8);"><i class="fas fa-chess-knight"></i> '.$t['black'].'</span>';
                    }
                    return '';
                })
                ->addColumn('result', function($row) use ($t) {
                    if (isset($row->result)) {
                        switch ($row->result) {
                            case '-1': // Đen thắng (Guest Won)
                                return '<span class="badge badge-status" style="background: linear-gradient(145deg, #252a36, #121418); color: var(--royal-gold); border: 1px solid var(--royal-gold);"><i class="fas fa-crown"></i> '.$t['guest_won'].'</span>';
                            case '0': // Hòa (Draw)
                                return '<span class="badge badge-status badge-offline"><i class="fas fa-handshake"></i> '.$t['draw'].'</span>';
                            case '1': // Đỏ thắng (Host Won)
                                return '<span class="badge badge-status" style="background: linear-gradient(to bottom, #8a1515, #5c0a0a); color: var(--royal-gold); border: 1px solid var(--royal-gold);"><i class="fas fa-crown"></i> '.$t['host_won'].'</span>';
                        }
                    } else if ($row->fen == env('INITIAL_FEN')) { // Chưa bắt đầu
                        return '<span class="badge badge-status" style="background: rgba(255,255,255,0.05); color: #aaa; border: 1px dashed rgba(212, 175, 55, 0.3);"><i class="fas fa-hourglass-start"></i> '.$t['not_started'].'</span>';
                    } else { // Đang diễn ra
                        return '<span class="badge badge-status badge-online"><i class="fas fa-circle"></i> '.$t['ongoing'].'</span>';
                    }
                    return '';
                })
               ->addColumn('action', function($row) use ($t, $locale) {
                    $urlRed   = localized_url('room.red', ['code' => $row->code], $locale);
                    $urlBlack = localized_url('room.black', ['code' => $row->code], $locale);
                    $urlWatch = localized_url('room.watch', ['code' => $row->code], $locale);
                    $urlHost  = localized_url('room.host', ['code' => $row->code], $locale);
                    $urlLogin = localized_url('login', [], $locale);

                    $actionBtn = '';

                    // Adding pulse animations to primary CTAs
                    if (!isset($row->host_id)) {
                        if ($row->fen == env('INITIAL_FEN')) {
                            if ($row->pass == '') {
                                $actionBtn = '<a class="btn btn-danger pulse-red text-light mr-1 showPromotion" style="min-width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.$urlRed.'"><i class="far fa-mouse"></i> '.$t['play'].'</a>';
                            } else {
                                $actionBtn = '<a class="btn btn-danger pulse-red text-light mr-1 showPromotion" style="min-width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.$urlHost.'"><i class="far fa-mouse"></i> '.$t['play'].'</a>';
                            }
                            if ($row->pass == '') {
                                $actionBtn .= '<a class="btn btn-light text-warning watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.$urlWatch.'" data-toggle="tooltip" data-placement="top" data-original-title="'.$t['public'].'"><i class="far fa-globe"></i> '.$t['watch'].'</a>';
                            } else {
                                $actionBtn .= '<a class="btn btn-warning text-light watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.$urlWatch.'" data-toggle="tooltip" data-placement="top" data-original-title="'.$t['private'].'"><i class="far fa-lock"></i> '.$t['watch'].'</a>';
                            }
                        } else {
                            if (isset($row->result)) {
                                if (str_contains($row->fen, ' b ') || str_contains($row->fen, ' r ')) {
                                    $actionBtn = '<a class="btn btn-dark text-light mr-1" style="min-width: 100px; cursor: not-allowed !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0);"><i class="far fa-ban"></i> '.$t['finished_btn'].'</a>';
                                }
                            } else {
                                if (str_contains($row->fen, ' b ')) {
                                    $actionBtn = '<a class="btn btn-dark text-light mr-1 showPromotion pulse-dark" style="min-width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.$urlBlack.'"><i class="far fa-mouse"></i> '.$t['play'].'</a>';
                                } else if (str_contains($row->fen, ' r ')) {
                                    $actionBtn = '<a class="btn btn-danger text-light mr-1 showPromotion pulse-red" style="min-width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.$urlRed.'"><i class="far fa-mouse"></i> '.$t['play'].'</a>';
                                }
                            }
                            if ($row->pass == '') {
                                $actionBtn .= '<a class="btn btn-light text-warning watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.$urlWatch.'" data-toggle="tooltip" data-placement="top" data-original-title="'.$t['public'].'"><i class="far fa-globe"></i> '.$t['watch'].'</a>';
                            } else {
                                $actionBtn .= '<a class="btn btn-warning text-light watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.$urlWatch.'" data-toggle="tooltip" data-placement="top" data-original-title="'.$t['private'].'"><i class="far fa-lock"></i> '.$t['watch'].'</a>';
                            }
                        }
                    } else {
                        if (auth()->check()) {
                            if (isset($row->result)) {
                                $actionBtn = '<a class="btn btn-dark text-light showPromotion" style="min-width: 200px;" href="'.$urlWatch.'"><i class="far fa-archive"></i> '.$t['finished_auth'].'</a>';
                            } else {
                                $actionBtn = '<a class="btn btn-danger text-light pulse-red" style="min-width: 200px;" href="javascript:joinMatch(`'.$row->code.'`)"><i class="far fa-mouse"></i> '.$t['play_now'].'</a>';
                            }
                        } else {
                            if (str_contains($row->fen, ' r ')) {
                                $actionBtn = '<a class="btn btn-danger text-light showPromotion pulse-red" style="min-width: 200px;" href="'.$urlLogin.'"><i class="far fa-sign-in"></i> '.$t['login'].'</a>';
                            } else if (str_contains($row->fen, ' b ')) {
                                $actionBtn = '<a class="btn btn-dark text-light showPromotion pulse-dark" style="min-width: 200px;" href="'.$urlLogin.'"><i class="far fa-sign-in"></i> '.$t['login'].'</a>';
                            } else {
                                $actionBtn = '<a class="btn btn-secondary text-light showPromotion" style="min-width: 200px;" href="'.$urlLogin.'"><i class="far fa-sign-in"></i> '.$t['login'].'</a>';
                            }
                        }
                    }

                    // Implementing the Royal Custom Eye Button
                    $actionBtn .= '<a class="ml-1 btn previewBtn"><i class="far fa-eye"></i> '.$t['preview'].'</a>';
                    return $actionBtn;
                })
                ->addColumn('time', function($row){
                    return date('Y-m-d | H:i:s', strtotime($row->modified_at));
                })
                ->escapeColumns([])
                ->orderColumn('code', 'code $1')
                ->orderColumn('result', 'result $1')
                ->orderColumn('time', 'modified_at $1')
                ->filterColumn('code', function($query, $keyword) {
                    $query->where(function($query) use ($keyword) {
                        $query->orWhere('code', 'like', '%' . $keyword . '%')
                              ->orWhere('name', 'like', '%' . $keyword . '%');
                    });
                })
                ->filterColumn('time', function($query, $keyword) {
                    $sql = "modified_at like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->rawColumns(['code', 'turn', 'result', 'action', 'time'])
                ->make(true);
        }
    }

    public function getRoomsVi(Request $request) { return $this->getRoomsData($request, 'vi'); }
    public function getRoomsEn(Request $request) { return $this->getRoomsData($request, 'en'); }
    public function getRoomsJa(Request $request) { return $this->getRoomsData($request, 'ja'); }
    public function getRoomsKo(Request $request) { return $this->getRoomsData($request, 'ko'); }
    public function getRoomsZh(Request $request) { return $this->getRoomsData($request, 'zh'); }

    public static function quickMatch()
    {
        dispatch(new QuickMatchJob());
        return response()->json([
            'code' => 1,
            'message' => 'Quick match queued successfully.'
        ]);
    }

    public static function getLatestRoom(Request $request)
    {
        $offsetNumber = $request->input('offset');
        $latestRoom = Room::where('pass', NULL)->where('host_id', NULL)->where('result', NULL)->orderBy('modified_at', 'desc')->offset($offsetNumber)->first();
        if ($latestRoom != null) {
            if (str_contains($latestRoom->fen, ' r ')) {
                return response()->json([
                    'color' => 'red',
                    'room' => $latestRoom
                ]);
            } else if (str_contains($latestRoom->fen, ' b ')) {
                return response()->json([
                    'color' => 'black',
                    'room' => $latestRoom
                ]);
            }
        } else {
            return response()->json([
                'room' => null
            ]);
        }
    }
    public static function getRandomRoom() {
        return Room::where('pass', null)
            ->where('host_id', null)
            ->where('result', '=', null)
            ->where('fen', '!=', 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1')
            ->where('fen', 'LIKE', '% b %')
            ->inRandomOrder()
            ->first();
    }
    public static function getNewRoom()
    {
        $firstRoom = Room::where('fen', env('INITIAL_FEN'))->where('pass', NULL)->where('host_id', NULL)->where('result', NULL)->orderBy('modified_at', 'desc')->first();
        return response()->json([
            'room' => $firstRoom
        ]);
    }

    public static function getRoomName($code)
    {
        $name = Room::where('code', $code)->value('name');

        return $name;
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $code    = $request->input('ma-phong');
        $name    = $request->input('ten-phong');
        $fen     = $request->input('FEN');
        $host_id = $request->input('host_id');
        $pass    = $request->input('pass');

        // Nếu phòng chưa tồn tại thì tạo mới (reset timer)
        $room = Room::firstOrCreate(
            ['code' => $code],
            [
                'fen'           => $fen,
                'moves'         => json_encode([]),
                'host_id'       => $host_id,
                'name'          => $name,
                'pass'          => $pass,
                'modified_at'   => now(),
                'black_time'    => 600,   // reset timer khi phòng mới
                'red_time'      => 600,
                'active_player' => null,
                'last_update'   => null,
            ]
        );

        // Nếu phòng đã tồn tại -> chỉ update các thông tin khác, KHÔNG reset timer
        if (!$room->wasRecentlyCreated) {
            $room->update([
                'fen'         => $fen,
                'host_id'     => $host_id,
                'name'        => $name,
                'pass'        => $pass,
                'modified_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $room->wasRecentlyCreated
                ? 'Phòng mới đã được tạo và timer reset.'
                : 'Phòng đã tồn tại, chỉ cập nhật thông tin.',
            'room' => $room,
        ]);
    }

    public function compete(Request $request)
    {
        $code = $request->input('ma-phong');
        $name = $request->input('ten-phong');
        $fen = $request->input('FEN');
        $host_id = $request->input('host_id');
        $guest_id = $request->input('guest_id');
        $pass = $request->input('pass');
        Room::updateOrInsert(
            ['code' => $code],
            [
                'fen' => $fen,
                'moves' => json_encode([]),
                'host_id' => $host_id,
                'guest_id' => $guest_id,
                'name' => $name,
                'pass' => $pass,
                'modified_at' => date('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $code = $request->input('ma-phong');
        $fen = $request->input('FEN');
        $move = $request->input('move');

        $room = Room::firstOrNew(['code' => $code]);

        // BẢO VỆ: Không cập nhật FEN nếu ván cờ đã kết thúc
        if (!is_null($room->result)) {
            return response()->json(['success' => false, 'message' => __('Game already finished')], 400);
        }

        $room->fen = $fen;
        $room->modified_at = now();

        if ($move && Schema::hasColumn('rooms', 'moves')) {
            $moves = [];
            if (!empty($room->moves)) {
                $decoded = json_decode($room->moves, true);
                if (is_array($decoded)) {
                    $moves = $decoded;
                }
            }
            $lastMove = end($moves);
            if ($lastMove !== $move) {
                $moves[] = $move;
                $room->moves = json_encode($moves);
            }
        }

        $room->save();

        // Push realtime update để hạn chế polling
        broadcast(new RoomUpdated($room->fresh()));

        return response()->json([
            'success' => true,
        ]);
    }

    public function join(Request $request)
    {
        $code = $request->input('ma-phong');
        $guest_id = $request->input('guest_id');
        Room::updateOrInsert(
            ['code' => $code],
            ['guest_id' => $guest_id, 'modified_at' => date('Y-m-d H:i:s')]
        );
    }

    public static function updateElo(Request $request)
    {
        $code = $request->input('ma-phong');
        $result = $request->input('result');

        $room = Room::select('host_id', 'guest_id')
                ->where('code', $code)
                ->first();

        if (!$room || !$room->host_id || !$room->guest_id) {
            return response()->json(['error' => 'Room or players not found'], 404);
        }

        $host = User::find($room->host_id);
        $guest = User::find($room->guest_id);

        if (!$host || !$guest) {
            return response()->json(['error' => 'Players missing'], 404);
        }

        $eloRatings = GameController::getEloRatings($host->elo, $guest->elo, $result);

        [$host->elo, $guest->elo] = $eloRatings;

        $host->save();
        $guest->save();

        return response()->json([
            'host_elo' => $host->elo,
            'guest_elo' => $guest->elo,
        ]);
    }

    public function updateResult(Request $request)
    {
        $code = $request->input('ma-phong');
        $result = $request->input('result');
        $auth_id = auth()->id() ?? $request->input('id');
        $lang = $request->input('lang');

        if ($lang) {
            app()->setLocale($lang);
        }

        $roomData = Room::select('host_id', 'guest_id', 'result', 'name', 'tournament_id', 'next_room_code', 'tournament_round')
            ->where('code', $code)
            ->first();

        $host_id = $roomData->host_id ?? null;
        $guest_id = $roomData->guest_id ?? null;

        if (!$auth_id || !in_array($auth_id, [$host_id, $guest_id])) {
            return response()->json([
                'success' => false,
                'message' => __('Bạn không có quyền cập nhật ván này.')
            ], 403);
        }

        // FIX: Only update DB and advance tournament if the result is NOT set yet.
        if ($roomData && is_null($roomData->result)) {
            Room::updateOrInsert(
                ['code' => $code],
                ['result' => $result, 'modified_at' => now()]
            );

            // --- TOURNAMENT ADVANCEMENT LOGIC ---
            $room = Room::where('code', $code)->first();

            if ($room->tournament_id && $room->next_room_code && $result !== '0') {
                $winnerId = ($result === '1') ? $room->host_id : $room->guest_id;
                $nextRoom = Room::where('code', $room->next_room_code)->first();

                if ($nextRoom->host_id !== $winnerId && $nextRoom->guest_id !== $winnerId) {
                    if (is_null($nextRoom->host_id)) {
                        $nextRoom->update(['host_id' => $winnerId]);
                    } else {
                        $nextRoom->update([
                            'guest_id' => $winnerId,
                            'name' => $room->name . " - " . __('Vòng') . " " . $nextRoom->tournament_round
                        ]);
                    }
                }
            }
        }

        $successMessages = [
            'host' => [
                '-1' => __('Chủ phòng thua! Cố lên nhé!'),
                '0'  => __('Hòa.'),
                '1'  => __('Chủ phòng thắng. Xin chúc mừng!'),
            ],
            'guest' => [
                '-1' => __('Khách thắng. Xin chúc mừng!'),
                '0'  => __('Hòa.'),
                '1'  => __('Khách thua! Cố lên nhé!'),
            ],
        ];

        if ($auth_id == $host_id) {
            $success_message = $successMessages['host'][$result] ?? '';
        } elseif ($auth_id == $guest_id) {
            $success_message = $successMessages['guest'][$result] ?? '';
        } else {
            $success_message = __('You are not authorized to update this room.');
        }

        return response()->json([
            'success' => $success_message
        ]);
    }

    public function updateSideResult(Request $request)
    {
        $code = $request->input('ma-phong');
        $result = $request->input('result');
        $lang = $request->input('lang');
        $side = $request->input('side');

        if ($lang) {
            app()->setLocale($lang);
        }

        $roomData = Room::select('result')->where('code', $code)->first();

        // FIX: Skip database update if a result already exists instead of returning 403
        if (!$roomData || is_null($roomData->result)) {
            Room::updateOrInsert(
                ['code' => $code],
                ['result' => $result, 'modified_at' => now()]
            );
        }

        $successMessages = [
            'red' => [
                '-1' => __('Red lost!'),
                '0'  => __('Draw.'),
                '1'  => __('Red won!'),
            ],
            'black' => [
                '-1' => __('Black won!'),
                '0'  => __('Draw.'),
                '1'  => __('Black lost!'),
            ],
        ];

        $success_message = $successMessages[$side][$result] ?? __('Result recorded.');

        return response()->json([
            'success' => $success_message
        ]);
    }

    public static function getHostId(Request $request)
    {
        $code = $request->input('ma-phong');
        $hostId = Room::where('code', $code)->value('host_id');

        return $hostId;
    }

    public static function getHostIdRoute($code)
    {
        $hostId = Room::where('code', $code)->value('host_id');

        return $hostId;
    }

    public static function getRoomIds(Request $request)
    {
        $code = $request->input('ma-phong');
        $roomData = Room::select('host_id', 'guest_id')
                        ->where('code', '=', $code)
                        ->first();

        return $roomData ? $roomData->toArray() : [];
    }

    public static function getMatchRooms()
    {
        $data = Room::select('fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at')
                    ->where('host_id', '!=', NULL)
                    ->orderBy('modified_at', 'desc')
                    ->paginate(10);
        return $data;
    }

    public static function getPlayingRooms()
    {
        $data = Room::select('fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at')
                    ->where('host_id', '!=', NULL)
                    ->where('result', '=', NULL)
                    ->orderBy('modified_at', 'desc')
                    ->paginate(10);
        return $data;
    }

    public static function getPlayedRooms()
    {
        $data = Room::select('fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at')
                    ->where('host_id', '!=', NULL)
                    ->where('result', '!=', NULL)
                    ->orderBy('modified_at', 'desc')
                    ->paginate(10);
        return $data;
    }

    public static function getPlayerRooms($id)
    {
        $data = Room::select('fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at')
                    ->orWhere('host_id', '=', $id)
                    ->orWhere('guest_id', '=', $id)
                    ->orderBy('modified_at', 'desc')
                    ->paginate(10);
        return $data;
    }

    public static function getBoards()
    {
        $data = Room::select('fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at')
                    ->where('host_id', '!=', NULL)
                    ->where('result', '=', NULL)
                    ->orderBy('modified_at', 'desc')
                    ->paginate(6);
        return $data;
    }

    public static function getFirstPageBoards()
    {
        $data = Room::select('fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at')
                    ->where('host_id', '!=', NULL)
                    ->where('result', '=', NULL)
                    ->orderBy('modified_at', 'desc')
                    ->paginate(6, ['*'], 'page', 1);
        return $data;
    }

    public static function getPlayedBoards()
    {
        $data = Room::select('fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at')
                    ->where('host_id', '!=', NULL)
                    ->where('result', '!=', NULL)
                    ->orderBy('modified_at', 'desc')
                    ->paginate(6);
        return $data;
    }

    public static function getFirstPagePlayedBoards()
    {
        $data = Room::select('fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at')
                    ->where('host_id', '!=', NULL)
                    ->where('result', '!=', NULL)
                    ->orderBy('modified_at', 'desc')
                    ->paginate(6, ['*'], 'page', 1);
        return $data;
    }

    public static function hasRoomcode(Request $request)
    {
        $code = $request->input('ma-phong');

        $roomcodeCount = Room::where('code', '=', $code)->count();

        if ($roomcodeCount > 0) {
            return 'yes';
        } else if ($roomcodeCount == 0) {
            return 'no';
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function show(Room $room, $code)
    {
        if (auth()->check()) {
            // Update user's online status
            auth()->user()->update(['last_seen_at' => now()]);
        }

        $fen = Room::where('code', $code)->value('fen');

        return $fen;
    }

    public function getMoves(Room $room, $code)
    {
        $moves = Room::where('code', $code)->value('moves');
        if (!$moves) {
            return response()->json([]);
        }

        $decoded = json_decode($moves, true);
        if (!is_array($decoded)) {
            $decoded = [];
        }

        return response()->json($decoded);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function getPass(Room $room, $code)
    {
        $pass = Room::where('code', $code)->value('pass');

        return $pass;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function changePass(Request $request, Room $room)
    {
        $code = $request->input('ma-phong');
        $pass = $request->input('pass');
        if (!$request->input('pass') || $pass === '') {
            echo json_encode(array('message' => 'Password cannot be empty', 'code' => 0));
            exit();
        } else {
            DB::update('update rooms set pass = ? where code = ?', [$pass, $code]);
            echo json_encode(array('message' => 'Changed password successfully!', 'code' => 1));
            exit();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function changePassJa(Request $request, Room $room)
    {
        $code = $request->input('ma-phong');
        $pass = $request->input('pass');
        if (!$request->input('pass') || $pass === '') {
            echo json_encode(array('message' => 'パスワードを空にすることはできません', 'code' => 0));
            exit();
        } else {
            DB::update('update rooms set pass = ? where code = ?', [$pass, $code]);
            echo json_encode(array('message' => 'パスワードが正常に変更されました。', 'code' => 1));
            exit();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function changePassKo(Request $request, Room $room)
    {
        $code = $request->input('ma-phong');
        $pass = $request->input('pass');
        if (!$request->input('pass') || $pass === '') {
            echo json_encode(array('message' => '암호는 비워 둘 수 없습니다.', 'code' => 0));
            exit();
        } else {
            DB::update('update rooms set pass = ? where code = ?', [$pass, $code]);
            echo json_encode(array('message' => '암호가 성공적으로 변경되었습니다!', 'code' => 1));
            exit();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function changePassZh(Request $request, Room $room)
    {
        $code = $request->input('ma-phong');
        $pass = $request->input('pass');
        if (!$request->input('pass') || $pass === '') {
            echo json_encode(array('message' => '密码不能为空', 'code' => 0));
            exit();
        } else {
            DB::update('update rooms set pass = ? where code = ?', [$pass, $code]);
            echo json_encode(array('message' => '成功更改密码！', 'code' => 1));
            exit();
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function doiPass(Request $request, Room $room)
    {
        $code = $request->input('ma-phong');
        $pass = $request->input('pass');
        if (!$request->input('pass') || $pass === '') {
            echo json_encode(array('message' => __('Mật khẩu không được để trống'), 'code' => 0));
            exit();
        } else {
            DB::update('update rooms set pass = ? where code = ?', [$pass, $code]);
            echo json_encode(array('message' => __('Đổi mật khẩu thành công!'), 'code' => 1));
            exit();
        }
    }

    public function getEventStream(Room $room, $code)
    {
        set_time_limit(0);

        $response = new StreamedResponse(function () use ($code) {
            $lastPayload = null;

            // Trong môi trường test, chỉ đẩy 1 lần để tránh treo
            $isTesting = app()->environment('testing');

            // Giới hạn vòng lặp để tránh treo request quá lâu
            $iterations = $isTesting ? 1 : 300; // ~5 phút nếu 1s/lần

            for ($i = 0; $i < $iterations; $i++) { // ~5 phút nếu 1s/lần
                $room = Room::select('fen', 'modified_at')->where('code', $code)->first();

                if (!$room) {
                    echo "event: close\n";
                    echo "data: {}\n\n";
                    ob_flush();
                    flush();
                    break;
                }

                $payload = json_encode([
                    'fen'         => $room->fen,
                    'modified_at' => $room->modified_at,
                ]);

                if ($payload !== $lastPayload) {
                    echo "data: {$payload}\n\n";
                    ob_flush();
                    flush();
                    $lastPayload = $payload;
                }

                if (connection_aborted()) {
                    break;
                }

                if ($isTesting) {
                    break;
                }

                sleep(1);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Cache-Control', 'no-cache, no-transform');

        return $response;
    }

    public static function updateRoomScores($id)
    {
        $hostWinScores = Room::where('host_id', '=', $id)
                ->where('result', '=', '1')
                ->count();
        $guestWinScores = Room::where('guest_id', '=', $id)
                ->where('result', '=', '-1')
                ->count();

        $hostDrawScores = Room::where('host_id', '=', $id)
                ->where('result', '=', '0')
                ->count();

        $guestDrawScores = Room::where('guest_id', '=', $id)
                ->where('result', '=', '0')
                ->count();

        $hostScores = $hostWinScores + 0.5 * $hostDrawScores;
        $guestScores = $guestWinScores + 0.5 * $guestDrawScores;

        Room::updateOrInsert(
            ['id' => $id],
            ['host_score' => $hostScores]
        );
        Room::updateOrInsert(
            ['id' => $id],
            ['guest_score' => $guestScores]
        );
    }

    public static function updateRoomElo($id)
    {
        $hostId = Room::find($id)->host_id;
        $guestId = Room::find($id)->guest_id;

        $hostCurrentElo = User::find($hostId)->elo;
        $guestCurrentElo = User::find($guestId)->elo;

        $hostScores = Room::find($id)->host_score;
        $guestScores = Room::find($id)->guest_score;

        $roomHostElo = GameController::calculateElo($hostCurrentElo, $guestCurrentElo, $hostScores);
        $roomGuestElo = GameController::calculateElo($guestCurrentElo, $hostCurrentElo, $guestScores);

        Room::updateOrInsert(
            ['id' => $id],
            ['host_elo' => $roomHostElo]
        );
        Room::updateOrInsert(
            ['id' => $id],
            ['guest_elo' => $roomGuestElo]
        );
    }

    /**
     * Dọn phòng cũ và đảm bảo session hiện tại luôn có phòng chờ hoặc đã ghép.
     */
    public function prepareAnonymousRoom(string $sessionId): Room
    {
        $initialFen = env('INITIAL_FEN') ?: 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1';

        // 1. CLEANUP: Throttled to reduce DB contention (e.g., runs ~10% of the time).
        // Ideally, move this to a scheduled Laravel command/cron job later.
        if (rand(1, 10) === 1) {
            Room::whereNull('result')
                ->where('modified_at', '<', now()->subMinutes(5))
                ->delete();
        }

        // 2. CHECK EXISTING: Is this user already in a room?
        $currentRoom = Room::where(function($query) use ($sessionId) {
                $query->where('host_session', $sessionId)
                    ->orWhere('guest_session', $sessionId);
            })
            ->whereNull('result')
            ->first();

        if ($currentRoom) {
            $currentRoom->update(['modified_at' => now()]);
            return $currentRoom;
        }

        // 3. MATCHMAKING: Find a room waiting for a guest (No gap locks!)
        $availableRoom = Room::whereNotNull('host_session')
            ->whereNull('guest_session')
            ->whereNull('result')
            ->whereNull('pass')
            ->where('fen', '=', $initialFen)
            ->where('modified_at', '>', now()->subSeconds(15))
            ->orderBy('modified_at', 'desc')
            ->first();

        if ($availableRoom) {
            // Atomic update to safely claim the room without race conditions
            $updated = Room::where('code', $availableRoom->code)
                ->whereNull('guest_session')
                ->update([
                    'guest_session' => $sessionId,
                    'modified_at'   => now(),
                ]);

            if ($updated) {
                return Room::where('code', $availableRoom->code)->first();
            }

            // If the update failed, someone else grabbed the room milliseconds before. Retry!
            return $this->prepareAnonymousRoom($sessionId);
        }

        // 4. CREATE: No match found, create a new room
        return Room::create([
            'code'          => md5(time() . $sessionId . uniqid('', true)), // Added true for extra entropy
            'fen'           => $initialFen,
            'name'          => Haikunator::haikunate(["tokenLength" => 0, "delimiter" => " "]),
            'host_session'  => $sessionId,
            'guest_session' => null,
            'host_id'       => null,
            'guest_id'      => null,
            'pass'          => null,
            'red_time'      => 600,
            'black_time'    => 600,
            'active_player' => null,
            'last_update'   => null,
            'modified_at'   => now(),
        ]);
    }

    /**
     * Helper method for anonymous match status checking
     */
    private function checkAnonymousMatchStatusHelper(Request $request, $sideNames = ['do', 'den'], $colorNames = ['đỏ', 'đen'])
    {
        $sessionId = $request->input('session_id');
        if (!$sessionId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Session ID required.',
            ], 400);
        }

        $room = $this->prepareAnonymousRoom($sessionId);

        if ($room->host_session && $room->guest_session) {
            $isHost = $room->host_session == $sessionId;
            $side = $isHost ? $sideNames[0] : $sideNames[1];
            $color = $isHost ? $colorNames[0] : $colorNames[1];
            return response()->json([
                'status'    => 'matched',
                'room_code' => $room->code,
                'room_name' => $room->name,
                'side'      => $side,
                'color'     => $color,
            ]);
        }

        return response()->json(['status' => 'waiting']);
    }

    public function anonymousQuickMatch(Request $request)
    {
        $sessionId = $request->session()->get('anonymous_match_id', Str::random(32));
        $request->session()->put('anonymous_match_id', $sessionId);

        $room = $this->prepareAnonymousRoom($sessionId);
        $matched = $room && $room->host_session && $room->guest_session;
        $isHost = $room->host_session === $sessionId;

        return response()->json([
            'code' => 1,
            'message' => $matched ? __('Đã tìm thấy đối thủ!') : __('Đang tìm trận...'),
            'session_id' => $sessionId,
            'matched' => $matched,
            'room_code' => $room->code,
            'room_name' => $room->name,
            'side' => $matched ? ($isHost ? 'do' : 'den') : null,
            'color' => $matched ? ($isHost ? 'đỏ' : 'đen') : null,
        ]);
    }

    public function checkAnonymousMatchStatus(Request $request)
    {
        return $this->checkAnonymousMatchStatusHelper($request, ['do', 'den'], ['đỏ', 'đen']);
    }

    public function anonymousQuickMatchEn(Request $request)
    {
        $sessionId = $request->session()->get('anonymous_match_id', Str::random(32));
        $request->session()->put('anonymous_match_id', $sessionId);

        $room = $this->prepareAnonymousRoom($sessionId);
        $matched = $room && $room->host_session && $room->guest_session;
        $isHost = $room->host_session === $sessionId;

        return response()->json([
            'code' => 1,
            'message' => $matched ? 'Opponent found!' : 'Looking for opponent...',
            'session_id' => $sessionId,
            'matched' => $matched,
            'room_code' => $room->code,
            'room_name' => $room->name,
            'side' => $matched ? ($isHost ? 'red' : 'black') : null,
            'color' => $matched ? ($isHost ? 'red' : 'black') : null,
        ]);
    }

    public function checkAnonymousMatchStatusEn(Request $request)
    {
        return $this->checkAnonymousMatchStatusHelper($request, ['red', 'black'], ['red', 'black']);
    }

    public function anonymousQuickMatchJa(Request $request)
    {
        $sessionId = $request->session()->get('anonymous_match_id', Str::random(32));
        $request->session()->put('anonymous_match_id', $sessionId);

        $room = $this->prepareAnonymousRoom($sessionId);
        $matched = $room && $room->host_session && $room->guest_session;
        $isHost = $room->host_session === $sessionId;

        return response()->json([
            'code' => 1,
            'message' => $matched ? '対戦相手が見つかりました！' : '対戦相手を探しています...',
            'session_id' => $sessionId,
            'matched' => $matched,
            'room_code' => $room->code,
            'room_name' => $room->name,
            'side' => $matched ? ($isHost ? 'aka' : 'kuro') : null,
            'color' => $matched ? ($isHost ? '赤' : '黒') : null,
        ]);
    }

    public function checkAnonymousMatchStatusJa(Request $request)
    {
        return $this->checkAnonymousMatchStatusHelper($request, ['aka', 'kuro'], ['赤', '黒']);
    }

    public function anonymousQuickMatchKo(Request $request)
    {
        $sessionId = $request->session()->get('anonymous_match_id', Str::random(32));
        $request->session()->put('anonymous_match_id', $sessionId);

        $room = $this->prepareAnonymousRoom($sessionId);
        $matched = $room && $room->host_session && $room->guest_session;
        $isHost = $room->host_session === $sessionId;

        return response()->json([
            'code' => 1,
            'message' => $matched ? '상대를 찾았습니다!' : '상대를 찾고 있습니다...',
            'session_id' => $sessionId,
            'matched' => $matched,
            'room_code' => $room->code,
            'room_name' => $room->name,
            'side' => $matched ? ($isHost ? 'ppalgan' : 'geom-eunsaeg') : null,
            'color' => $matched ? ($isHost ? '빨간색' : '검은색') : null,
        ]);
    }

    public function checkAnonymousMatchStatusKo(Request $request)
    {
        return $this->checkAnonymousMatchStatusHelper($request, ['ppalgan', 'geom-eunsaeg'], ['빨간색', '검은색']);
    }

    public function anonymousQuickMatchZh(Request $request)
    {
        $sessionId = $request->session()->get('anonymous_match_id', Str::random(32));
        $request->session()->put('anonymous_match_id', $sessionId);

        $room = $this->prepareAnonymousRoom($sessionId);
        $matched = $room && $room->host_session && $room->guest_session;
        $isHost = $room->host_session === $sessionId;

        return response()->json([
            'code' => 1,
            'message' => $matched ? '已找到对手！' : '寻找对手...',
            'session_id' => $sessionId,
            'matched' => $matched,
            'room_code' => $room->code,
            'room_name' => $room->name,
            'side' => $matched ? ($isHost ? 'hongse' : 'heise') : null,
            'color' => $matched ? ($isHost ? '红色的' : '黑色的') : null,
        ]);
    }

    public function checkAnonymousMatchStatusZh(Request $request)
    {
        return $this->checkAnonymousMatchStatusHelper($request, ['hongse', 'heise'], ['红色的', '黑色的']);
    }

    public function switchTurn(Request $request, $roomCode)
    {
        $currentPlayer = $request->input('current_player');

        if (!in_array($currentPlayer, ['red', 'black'])) {
            return response()->json(['error' => 'Invalid player'], 422);
        }

        $room = Room::where('code', $roomCode)->first();
        if (!$room) {
            return response()->json(['error' => 'Room not found'], 404);
        }

        $now = now();
        $lastUpdate = $room->last_update ? \Carbon\Carbon::parse($room->last_update) : $now;

        // Calculate exact elapsed time using milliseconds to avoid rounding drift
        $elapsed = $lastUpdate->diffInMilliseconds($now) / 1000;

        // Subtract exact elapsed time from the player who just finished their turn
        if ($elapsed > 0) {
            if ($currentPlayer === 'red') {
                $room->red_time = max(0, floatval($room->red_time) - $elapsed);
            } else {
                $room->black_time = max(0, floatval($room->black_time) - $elapsed);
            }
        }

        // Switch to the next player
        $room->active_player = $currentPlayer === 'red' ? 'black' : 'red';
        $room->last_update = $now;
        $room->modified_at = $now;
        $room->save();

        $freshRoom = $room->fresh();

        // Push the mathematically verified time to all clients
        broadcast(new RoomUpdated($freshRoom));

        return response()->json([
            'success'       => true,
            'red_time'      => round($freshRoom->red_time, 3),
            'black_time'    => round($freshRoom->black_time, 3),
            'active_player' => $freshRoom->active_player,
            'last_update'   => optional($freshRoom->last_update)->toDateTimeString(),
        ]);
    }

    public function startTimer($roomCode, $player)
    {
        $room = Room::where('code', $roomCode)->first();
        if (!$room) return response()->json(['error' => 'Room not found'], 404);

        $room->active_player = $player;
        $room->last_update = now();
        $room->save();

        broadcast(new RoomUpdated($room->fresh()));

        return response()->json(['success' => true, 'active_player' => $player]);
    }

    public function pauseTimer($roomCode, $player)
    {
        $room = Room::where('code', $roomCode)->first();
        if (!$room) return response()->json(['error' => 'Room not found'], 404);

        if ($room->active_player === $player) {
            $lastUpdate = $room->last_update ? \Carbon\Carbon::parse($room->last_update) : now();
            $elapsed = $lastUpdate->diffInMilliseconds(now()) / 1000;

            if ($player === 'red') {
                $room->red_time = max(0, floatval($room->red_time) - $elapsed);
            } else {
                $room->black_time = max(0, floatval($room->black_time) - $elapsed);
            }

            $room->active_player = null;
            $room->last_update = now();
            $room->save();

            broadcast(new RoomUpdated($room->fresh()));
        }

        return response()->json(['success' => true]);
    }

    public function getTime($roomCode)
    {
        $room = Room::where('code', $roomCode)->first();
        if (!$room) return response()->json(['error' => 'Room not found'], 404);

        $redTime = floatval($room->red_time);
        $blackTime = floatval($room->black_time);

        // If the game is actively running, mathematically determine the exact remaining time
        if ($room->active_player) {
            $lastUpdate = $room->last_update ? \Carbon\Carbon::parse($room->last_update) : now();
            $elapsed = $lastUpdate->diffInMilliseconds(now()) / 1000;

            if ($room->active_player === 'red') {
                $redTime = max(0, $redTime - $elapsed);
            } elseif ($room->active_player === 'black') {
                $blackTime = max(0, $blackTime - $elapsed);
            }
        }

        return response()->json([
            'red_time' => round($redTime, 3),
            'black_time' => round($blackTime, 3),
            'active_player' => $room->active_player,
            'last_update' => optional($room->last_update)->toDateTimeString(),
        ]);
    }

    public function saveTime(Request $request, $roomCode)
    {
        $room = Room::where('code', $roomCode)->first();
        if (!$room) {
            return response()->json(['error' => 'Room not found'], 404);
        }

        $room->red_time = max(0, $request->input('red_time', $room->red_time));
        $room->black_time = max(0, $request->input('black_time', $room->black_time));
        $room->last_update = now();
        $room->save();

        return response()->json([
            'success' => true,
            'red_time' => $room->red_time,
            'black_time' => $room->black_time,
            'last_update' => $room->last_update,
        ]);
    }

    /**
     * Unified method to find a match (handles both guests and authenticated users via Session/Client ID)
     */
    public function findMatch(Request $request)
    {
        // Use provided session_id from frontend, or fallback to Laravel session
        $sessionId = $request->input('session_id') ?: $request->session()->get('match_session_id', Str::random(32));
        $request->session()->put('match_session_id', $sessionId);

        $room = $this->prepareAnonymousRoom($sessionId);
        $matched = $room && $room->host_session && $room->guest_session;
        $isHost = $room->host_session === $sessionId;

        return response()->json([
            'code' => 1,
            // Uses Laravel's __() helper to adapt if a locale middleware is present,
            // otherwise frontend JS will override with its own localized strings.
            'message' => $matched ? __('Đã tìm thấy đối thủ!') : __('Đang tìm trận...'),
            'session_id' => $sessionId,
            'matched' => $matched,
            'room_code' => $room->code,
            'room_name' => $room->name,
            // Standardized sides
            'side' => $matched ? ($isHost ? 'red' : 'black') : null,
        ]);
    }

    /**
     * Unified method to check matchmaking status
     */
    public function checkMatchStatus(Request $request)
    {
        $sessionId = $request->input('session_id');

        if (!$sessionId) {
            return response()->json([
                'status' => 'error',
                'message' => __('Không tìm thấy phiên bản kết nối (Session ID).'),
            ], 400);
        }

        $room = $this->prepareAnonymousRoom($sessionId);

        if ($room->host_session && $room->guest_session) {
            $isHost = $room->host_session == $sessionId;

            return response()->json([
                'status'    => 'matched',
                'room_code' => $room->code,
                'room_name' => $room->name,
                // Return standard English keys for the frontend logic to parse effortlessly
                'side'      => $isHost ? 'red' : 'black',
            ]);
        }

        return response()->json(['status' => 'waiting']);
    }
}
