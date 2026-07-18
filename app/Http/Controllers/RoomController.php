<?php

namespace App\Http\Controllers;

use DB;
use Exception;
use App\Models\Room;
use App\Models\User;
use App\Events\RoomUpdated;
use App\Actions\Room\UpdateRoomEloAction;
use App\Http\Controllers\TournamentController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Atrox\Haikunator;
use Carbon\Carbon;
use DataTables;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Jobs\QuickMatchJob;

class RoomController extends Controller
{
    /**
     * Shared logic for generating Room DataTables across all locales.
     */
    private function getRoomsData(Request $request, string $locale)
    {
        if ($request->ajax()) {
            // Fetch ongoing rooms and trigger timeouts via model logic
            $ongoingRooms = Room::ongoing()->get();
            foreach ($ongoingRooms as $r) {
                if ($r->hasTimedOut()) {
                    $r->processTimeout();
                }
            }

            $rooms = Room::select(['fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at']);

            $texts = [
                'vi' => ['public' => 'Công khai', 'private' => 'Riêng tư', 'red' => 'Đỏ', 'black' => 'Đen', 'guest_won' => 'Đen thắng', 'draw' => 'Hòa', 'host_won' => 'Đỏ thắng', 'not_started' => 'Chưa bắt đầu', 'ongoing' => 'Đang đấu', 'play' => 'Chơi', 'watch' => 'Theo dõi', 'finished_btn' => 'Đã xong', 'finished_auth' => 'Đã đấu xong', 'play_now' => 'Chơi nào', 'login' => 'Đăng nhập', 'preview' => 'Xem trước'],
                'en' => ['public' => 'Public', 'private' => 'Private', 'red' => 'Red', 'black' => 'Black', 'guest_won' => 'Guest won', 'draw' => 'Draw', 'host_won' => 'Host won', 'not_started' => 'Not started', 'ongoing' => 'Ongoing', 'play' => 'Play', 'watch' => 'Watch', 'finished_btn' => 'Finished', 'finished_auth' => 'Finished', 'play_now' => 'Play now', 'login' => 'Login', 'preview' => 'Preview'],
                'ja' => ['public' => '公衆', 'private' => '民間', 'red' => '赤', 'black' => '黒', 'guest_won' => 'ゲストが勝ちました', 'draw' => 'ドローです', 'host_won' => 'ホストが勝ちました', 'not_started' => '開始されていない', 'ongoing' => '現在進行中', 'play' => '加入', 'watch' => '見る', 'finished_btn' => '終わり', 'finished_auth' => '終わり', 'play_now' => '加入', 'login' => 'ログイン', 'preview' => 'プレビュー'],
                'ko' => ['public' => '공공의', 'private' => '사적인', 'red' => '홍', 'black' => '검', 'guest_won' => '손님이 이겼어요', 'draw' => '동점입니다', 'host_won' => '주최자가 이겼어요', 'not_started' => '아직 시작되지 않음', 'ongoing' => '진행 중인', 'play' => '참여', 'watch' => '보다', 'finished_btn' => '끝났다', 'finished_auth' => '끝났다', 'play_now' => '참여', 'login' => '로그인', 'preview' => '미리보기'],
                'zh' => ['public' => '平民的', 'private' => '私有的', 'red' => '红', 'black' => '黑', 'guest_won' => '客人赢了', 'draw' => '平局', 'host_won' => '主办方赢了', 'not_started' => '未开始', 'ongoing' => '进行中的', 'play' => '参加', 'watch' => '看', 'finished_btn' => '结束', 'finished_auth' => '结束', 'play_now' => '参加', 'login' => '登录', 'preview' => '预览'],
            ];

            $t = $texts[$locale] ?? $texts['en'];

            return Datatables::of($rooms)
                ->addColumn('code', function($row) use ($t) {
                    $roomNameRaw = (isset($row->name) && $row->name != '') ? $row->name : $row->code;
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
                        return '<span class="badge badge-status" style="background: linear-gradient(to bottom, #8a1515, #5c0a0a); color: var(--royal-gold); border: 1px solid var(--royal-gold); box-shadow: 0 0 8px rgba(138, 21, 21, 0.6);"><i class="fas fa-chess-knight"></i> '.$t['red'].'</span>';
                    } else if (str_contains($row->fen, ' b ')) {
                        return '<span class="badge badge-status" style="background: linear-gradient(145deg, #252a36, #121418); color: var(--royal-gold-light); border: 1px solid rgba(212, 175, 55, 0.3); box-shadow: 0 0 8px rgba(0, 0, 0, 0.8);"><i class="fas fa-chess-knight"></i> '.$t['black'].'</span>';
                    }
                    return '';
                })
                ->addColumn('result', function($row) use ($t) {
                    if (isset($row->result)) {
                        switch ($row->result) {
                            case '-1': return '<span class="badge badge-status" style="background: linear-gradient(145deg, #252a36, #121418); color: var(--royal-gold); border: 1px solid var(--royal-gold);"><i class="fas fa-crown"></i> '.$t['guest_won'].'</span>';
                            case '0': return '<span class="badge badge-status badge-offline"><i class="fas fa-handshake"></i> '.$t['draw'].'</span>';
                            case '1': return '<span class="badge badge-status" style="background: linear-gradient(to bottom, #8a1515, #5c0a0a); color: var(--royal-gold); border: 1px solid var(--royal-gold);"><i class="fas fa-crown"></i> '.$t['host_won'].'</span>';
                        }
                    } else if ($row->fen == env('INITIAL_FEN')) {
                        return '<span class="badge badge-status" style="background: rgba(255,255,255,0.05); color: #aaa; border: 1px dashed rgba(212, 175, 55, 0.3);"><i class="fas fa-hourglass-start"></i> '.$t['not_started'].'</span>';
                    } else {
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
                return response()->json(['color' => 'red', 'room' => $latestRoom]);
            } else if (str_contains($latestRoom->fen, ' b ')) {
                return response()->json(['color' => 'black', 'room' => $latestRoom]);
            }
        }
        return response()->json(['room' => null]);
    }

    public static function getRandomRoom() {
        return Room::where('pass', null)
            ->where('host_id', null)
            ->where('result', '=', null)
            ->where('fen', '!=', env('INITIAL_FEN', 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1'))
            ->where('fen', 'LIKE', '% b %')
            ->inRandomOrder()
            ->first();
    }

    public static function getNewRoom()
    {
        $firstRoom = Room::where('fen', env('INITIAL_FEN'))->where('pass', NULL)->where('host_id', NULL)->where('result', NULL)->orderBy('modified_at', 'desc')->first();
        return response()->json(['room' => $firstRoom]);
    }

    public static function getRoomName($code)
    {
        return Room::where('code', $code)->value('name');
    }

    public function create(Request $request)
    {
        $code    = $request->input('ma-phong');
        $name    = $request->input('ten-phong');
        $fen     = $request->input('FEN');
        $host_id = $request->input('host_id');
        $pass    = $request->input('pass');

        $room = Room::firstOrCreate(
            ['code' => $code],
            [
                'fen'           => $fen,
                'moves'         => json_encode([]),
                'host_id'       => $host_id,
                'name'          => $name,
                'pass'          => $pass,
                'modified_at'   => now(),
                'black_time'    => 600,
                'red_time'      => 600,
                'active_player' => null,
                'last_update'   => null,
            ]
        );

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
            'message' => $room->wasRecentlyCreated ? 'Phòng mới đã được tạo và timer reset.' : 'Phòng đã tồn tại, chỉ cập nhật thông tin.',
            'room' => $room,
        ]);
    }

    public function compete(Request $request)
    {
        Room::updateOrInsert(
            ['code' => $request->input('ma-phong')],
            [
                'fen' => $request->input('FEN'),
                'moves' => json_encode([]),
                'host_id' => $request->input('host_id'),
                'guest_id' => $request->input('guest_id'),
                'name' => $request->input('ten-phong'),
                'pass' => $request->input('pass'),
                'modified_at' => now(),
            ]
        );
    }

    public function store(Request $request)
    {
        $code = $request->input('ma-phong');
        $fen = $request->input('FEN');
        $move = $request->input('move');

        $room = Room::firstOrNew(['code' => $code]);

        if (!is_null($room->result)) {
            return response()->json(['success' => false, 'message' => __('Game already finished')], 400);
        }

        $room->fen = $fen;
        $room->modified_at = now();

        if ($move && Schema::hasColumn('rooms', 'moves')) {
            $moves = !empty($room->moves) ? json_decode($room->moves, true) : [];
            if (is_array($moves) && end($moves) !== $move) {
                $moves[] = $move;
                $room->moves = json_encode($moves);
            }
        }

        $room->save();
        broadcast(new RoomUpdated($room->fresh()));

        return response()->json(['success' => true]);
    }

    public function join(Request $request)
    {
        Room::updateOrInsert(
            ['code' => $request->input('ma-phong')],
            ['guest_id' => $request->input('guest_id'), 'modified_at' => now()]
        );
    }

    public function updateElo(Request $request, UpdateRoomEloAction $updateRoomEloAction)
    {
        $code = $request->input('ma-phong');
        $result = $request->input('result');

        try {
            // Replaced static logic with Action execution
            $newElos = $updateRoomEloAction->execute($code, $result);
            return response()->json($newElos);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update Elo ratings'], 500);
        }
    }

    public function updateResult(Request $request)
    {
        $code = $request->input('ma-phong');
        $result = $request->input('result');
        $auth_id = auth()->id() ?? $request->input('id');

        if ($request->has('lang')) app()->setLocale($request->input('lang'));

        try {
            return DB::transaction(function () use ($code, $result, $auth_id) {
                $room = Room::lockForUpdate()->where('code', $code)->firstOrFail();

                if (!in_array($auth_id, [$room->host_id, $room->guest_id])) {
                    throw new Exception(__('Bạn không có quyền cập nhật ván này.'));
                }

                if (is_null($room->result)) {
                    $room->update(['result' => $result, 'modified_at' => now()]);
                    $this->advanceTournament($room, $result);
                }

                $isHost = $auth_id == $room->host_id;
                $messageKey = $this->getResultMessageKey($isHost, $result);

                return response()->json(['success' => __($messageKey)]);
            });
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }
    }

    private function getResultMessageKey($isHost, $result)
    {
        if ($result === '0') return 'Hòa.';
        if ($isHost) return $result === '1' ? 'Chủ phòng thắng. Xin chúc mừng!' : 'Chủ phòng thua! Cố lên nhé!';
        return $result === '-1' ? 'Khách thắng. Xin chúc mừng!' : 'Khách thua! Cố lên nhé!';
    }

    private function advanceTournament(Room $room, $result)
    {
        // Do not advance if there is no tournament, no next room, or if the result is a draw ('0')
        if (!$room->tournament_id || !$room->next_room_code || $result === '0') return;

        // Determine the winner's ID based on the result
        $winnerId = ($result === '1') ? $room->host_id : $room->guest_id;

        // Fetch the User object for the winner, as required by handleTournamentAdvancement[cite: 1, 2]
        $winner = User::find($winnerId);

        if ($winner) {
            // Call the method from TournamentController to handle the advancement and email notifications
            app(TournamentController::class)->handleTournamentAdvancement($room, $winner);
        }
    }

    public function updateSideResult(Request $request)
    {
        $code = $request->input('ma-phong');
        $result = $request->input('result');
        $side = $request->input('side');
        if ($request->has('lang')) app()->setLocale($request->input('lang'));

        $room = Room::where('code', $code)->first();

        if ($room && is_null($room->result)) {
            $room->update(['result' => $result, 'modified_at' => now()]);
        }

        $successMessages = [
            'red' => ['-1' => __('Red lost!'), '0' => __('Draw.'), '1' => __('Red won!')],
            'black' => ['-1' => __('Black won!'), '0' => __('Draw.'), '1' => __('Black lost!')],
        ];

        return response()->json(['success' => $successMessages[$side][$result] ?? __('Result recorded.')]);
    }

    public static function getHostId(Request $request) { return Room::where('code', $request->input('ma-phong'))->value('host_id'); }
    public static function getHostIdRoute($code) { return Room::where('code', $code)->value('host_id'); }
    public static function getRoomIds(Request $request)
    {
        $roomData = Room::select('host_id', 'guest_id')->where('code', '=', $request->input('ma-phong'))->first();
        return $roomData ? $roomData->toArray() : [];
    }

    public static function getMatchRooms() { return Room::whereNotNull('host_id')->orderBy('modified_at', 'desc')->paginate(10); }
    public static function getPlayingRooms() { return Room::whereNotNull('host_id')->whereNull('result')->orderBy('modified_at', 'desc')->paginate(10); }
    public static function getPlayedRooms() { return Room::whereNotNull('host_id')->whereNotNull('result')->orderBy('modified_at', 'desc')->paginate(10); }
    public static function getPlayerRooms($id)
    {
        return Room::where('host_id', $id)->orWhere('guest_id', $id)->orderBy('modified_at', 'desc')->paginate(10);
    }
    public static function getBoards() { return Room::whereNotNull('host_id')->whereNull('result')->orderBy('modified_at', 'desc')->paginate(6); }
    public static function getFirstPageBoards() { return Room::whereNotNull('host_id')->whereNull('result')->orderBy('modified_at', 'desc')->paginate(6, ['*'], 'page', 1); }
    public static function getPlayedBoards() { return Room::whereNotNull('host_id')->whereNotNull('result')->orderBy('modified_at', 'desc')->paginate(6); }
    public static function getFirstPagePlayedBoards() { return Room::whereNotNull('host_id')->whereNotNull('result')->orderBy('modified_at', 'desc')->paginate(6, ['*'], 'page', 1); }

    public static function hasRoomcode(Request $request)
    {
        return Room::where('code', $request->input('ma-phong'))->exists() ? 'yes' : 'no';
    }

    public function show(Room $room, $code)
    {
        if (auth()->check()) auth()->user()->update(['last_seen_at' => now()]);
        return Room::where('code', $code)->value('fen');
    }

    public function getMoves(Room $room, $code)
    {
        $moves = Room::where('code', $code)->value('moves');
        $decoded = $moves ? json_decode($moves, true) : [];
        return response()->json(is_array($decoded) ? $decoded : []);
    }

    public function getPass(Room $room, $code)
    {
        return Room::where('code', $code)->value('pass');
    }

    public function changePass(Request $request)
    {
        $code = $request->input('ma-phong');
        $pass = $request->input('pass');
        $locale = $request->input('lang', app()->getLocale());

        $messages = [
            'vi' => ['empty' => 'Mật khẩu không được để trống.', 'success' => 'Đổi mật khẩu thành công!'],
            'en' => ['empty' => 'Password cannot be empty', 'success' => 'Changed password successfully!'],
            'ja' => ['empty' => 'パスワードを空にすることはできません', 'success' => 'パスワードが正常に変更されました。'],
            'ko' => ['empty' => '암호는 비워 둘 수 없습니다.', 'success' => '암호가 성공적으로 변경되었습니다!'],
            'zh' => ['empty' => '密码不能为空', 'success' => '成功更改密码！']
        ];
        $localized = $messages[$locale] ?? $messages['en'];

        if (!$pass || $pass === '') return response()->json(['message' => $localized['empty'], 'code' => 0]);

        DB::update('update rooms set pass = ? where code = ?', [$pass, $code]);
        return response()->json(['message' => $localized['success'], 'code' => 1]);
    }

    public function getEventStream(Room $room, $code)
    {
        set_time_limit(0);

        $response = new StreamedResponse(function () use ($code) {
            $lastPayload = null;
            $isTesting = app()->environment('testing');
            $iterations = $isTesting ? 1 : 300;

            for ($i = 0; $i < $iterations; $i++) {
                $r = Room::select('fen', 'modified_at')->where('code', $code)->first();

                if (!$r) {
                    echo "event: close\ndata: {}\n\n";
                    ob_flush(); flush(); break;
                }

                $payload = json_encode(['fen' => $r->fen, 'modified_at' => $r->modified_at]);

                if ($payload !== $lastPayload) {
                    echo "data: {$payload}\n\n";
                    ob_flush(); flush();
                    $lastPayload = $payload;
                }

                if (connection_aborted() || $isTesting) break;
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
        $hostWin = Room::where('host_id', $id)->where('result', '1')->count();
        $guestWin = Room::where('guest_id', $id)->where('result', '-1')->count();
        $hostDraw = Room::where('host_id', $id)->where('result', '0')->count();
        $guestDraw = Room::where('guest_id', $id)->where('result', '0')->count();

        Room::updateOrInsert(['id' => $id], ['host_score' => $hostWin + 0.5 * $hostDraw]);
        Room::updateOrInsert(['id' => $id], ['guest_score' => $guestWin + 0.5 * $guestDraw]);
    }

    public function prepareAnonymousRoom(string $sessionId): Room
    {
        $initialFen = env('INITIAL_FEN') ?: 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1';

        $currentRoom = Room::ongoing()
            ->where(fn($q) => $q->where('host_session', $sessionId)->orWhere('guest_session', $sessionId))
            ->first();

        if ($currentRoom) {
            $currentRoom->touch('modified_at');
            return $currentRoom;
        }

        $availableRoom = Room::availableForAnonymousMatch($initialFen)->first();

        if ($availableRoom) {
            $updated = Room::where('code', $availableRoom->code)
                ->whereNull('guest_session')
                ->update(['guest_session' => $sessionId, 'modified_at' => now()]);

            if ($updated) return Room::where('code', $availableRoom->code)->first();
            return $this->prepareAnonymousRoom($sessionId);
        }

        return Room::create([
            'code'          => md5(time() . $sessionId . uniqid('', true)),
            'fen'           => $initialFen,
            'name'          => Haikunator::haikunate(["tokenLength" => 0, "delimiter" => " "]),
            'host_session'  => $sessionId,
            'red_time'      => 600,
            'black_time'    => 600,
            'modified_at'   => now(),
        ]);
    }

    private function handleAnonymousMatch(Request $request, $sideNames, $colorNames, $messages)
    {
        $sessionId = $request->session()->get('anonymous_match_id', Str::random(32));
        $request->session()->put('anonymous_match_id', $sessionId);

        $room = $this->prepareAnonymousRoom($sessionId);
        $matched = $room && $room->host_session && $room->guest_session;
        $isHost = $room->host_session === $sessionId;

        return response()->json([
            'code' => 1,
            'message' => $matched ? $messages['matched'] : $messages['waiting'],
            'session_id' => $sessionId,
            'matched' => $matched,
            'room_code' => $room->code,
            'room_name' => $room->name,
            'side' => $matched ? ($isHost ? $sideNames[0] : $sideNames[1]) : null,
            'color' => $matched ? ($isHost ? $colorNames[0] : $colorNames[1]) : null,
        ]);
    }

    private function handleCheckMatchStatus(Request $request, $sideNames, $colorNames)
    {
        $sessionId = $request->input('session_id');
        if (!$sessionId) return response()->json(['status' => 'error', 'message' => 'Session ID required.'], 400);

        $room = $this->prepareAnonymousRoom($sessionId);

        if ($room->host_session && $room->guest_session) {
            $isHost = $room->host_session == $sessionId;
            return response()->json([
                'status'    => 'matched',
                'room_code' => $room->code,
                'room_name' => $room->name,
                'side'      => $isHost ? $sideNames[0] : $sideNames[1],
                'color'     => $isHost ? $colorNames[0] : $colorNames[1],
            ]);
        }
        return response()->json(['status' => 'waiting']);
    }

    public function anonymousQuickMatch(Request $request) { return $this->handleAnonymousMatch($request, ['do', 'den'], ['đỏ', 'đen'], ['matched' => __('Đã tìm thấy đối thủ!'), 'waiting' => __('Đang tìm trận...')]); }
    public function checkAnonymousMatchStatus(Request $request) { return $this->handleCheckMatchStatus($request, ['do', 'den'], ['đỏ', 'đen']); }

    public function anonymousQuickMatchEn(Request $request) { return $this->handleAnonymousMatch($request, ['red', 'black'], ['red', 'black'], ['matched' => 'Opponent found!', 'waiting' => 'Looking for opponent...']); }
    public function checkAnonymousMatchStatusEn(Request $request) { return $this->handleCheckMatchStatus($request, ['red', 'black'], ['red', 'black']); }

    public function anonymousQuickMatchJa(Request $request) { return $this->handleAnonymousMatch($request, ['aka', 'kuro'], ['赤', '黒'], ['matched' => '対戦相手が見つかりました！', 'waiting' => '対戦相手を探しています...']); }
    public function checkAnonymousMatchStatusJa(Request $request) { return $this->handleCheckMatchStatus($request, ['aka', 'kuro'], ['赤', '黒']); }

    public function anonymousQuickMatchKo(Request $request) { return $this->handleAnonymousMatch($request, ['ppalgan', 'geom-eunsaeg'], ['빨간색', '검은색'], ['matched' => '상대를 찾았습니다!', 'waiting' => '상대를 찾고 있습니다...']); }
    public function checkAnonymousMatchStatusKo(Request $request) { return $this->handleCheckMatchStatus($request, ['ppalgan', 'geom-eunsaeg'], ['빨간색', '검은색']); }

    public function anonymousQuickMatchZh(Request $request) { return $this->handleAnonymousMatch($request, ['hongse', 'heise'], ['红色的', '黑色的'], ['matched' => '已找到对手！', 'waiting' => '寻找对手...']); }
    public function checkAnonymousMatchStatusZh(Request $request) { return $this->handleCheckMatchStatus($request, ['hongse', 'heise'], ['红色的', '黑色的']); }

    public function switchTurn(Request $request, $roomCode)
    {
        $currentPlayer = $request->input('current_player');
        if (!in_array($currentPlayer, ['red', 'black'])) return response()->json(['error' => 'Invalid player'], 422);

        $room = Room::where('code', $roomCode)->firstOrFail();
        $room->switchTurn($currentPlayer);

        $freshRoom = $room->fresh();
        broadcast(new RoomUpdated($freshRoom));

        $times = $freshRoom->getCalculatedTimes();

        return response()->json([
            'success'       => true,
            'red_time'      => $times['red_time'],
            'black_time'    => $times['black_time'],
            'active_player' => $times['active_player'],
            'move_elapsed'  => 0,
            'last_update'   => optional($freshRoom->last_update)->toDateTimeString(),
        ]);
    }

    public function pauseTimer($roomCode, $player)
    {
        return response()->json(['success' => true]);
    }

    public function startTimer($roomCode, $player)
    {
        $room = Room::where('code', $roomCode)->first();
        if (!$room) return response()->json(['error' => 'Room not found'], 404);

        if ($room->active_player === "paused:{$player}") {
            $room->active_player = $player;
            $room->save();
            broadcast(new RoomUpdated($room->fresh()));
        }

        return response()->json(['success' => true, 'active_player' => $player]);
    }

    public function getTime($roomCode)
    {
        $room = Room::where('code', $roomCode)->firstOrFail();
        $times = $room->getCalculatedTimes();

        return response()->json(array_merge($times, [
            'last_update' => optional($room->last_update)->toDateTimeString(),
        ]));
    }

    public function saveTime(Request $request, $roomCode)
    {
        $room = Room::where('code', $roomCode)->firstOrFail();

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

    public function findMatch(Request $request)
    {
        $sessionId = $request->input('session_id') ?: $request->session()->get('match_session_id', Str::random(32));
        $request->session()->put('match_session_id', $sessionId);

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
            'side' => $matched ? ($isHost ? 'red' : 'black') : null,
        ]);
    }

    public function checkMatchStatus(Request $request)
    {
        $sessionId = $request->input('session_id');
        if (!$sessionId) return response()->json(['status' => 'error', 'message' => __('Không tìm thấy phiên bản kết nối (Session ID).')], 400);

        $room = $this->prepareAnonymousRoom($sessionId);

        if ($room->host_session && $room->guest_session) {
            $isHost = $room->host_session == $sessionId;
            return response()->json([
                'status'    => 'matched',
                'room_code' => $room->code,
                'room_name' => $room->name,
                'side'      => $isHost ? 'red' : 'black',
            ]);
        }
        return response()->json(['status' => 'waiting']);
    }

    public function startMatch($roomCode)
    {
        $room = Room::where('code', $roomCode)->first();

        if ($room && $room->active_player === 'waiting') {
            $room->active_player = 'red';
            $room->last_update = now();
            $room->modified_at = now();
            $room->save();

            broadcast(new RoomUpdated($room->fresh()));
        }

        return response()->json(['success' => true]);
    }
}
