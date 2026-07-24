<?php

namespace App\Http\Controllers;

use DB;
use Exception;
use App\Models\Room;
use App\Models\User;
use App\Events\RoomUpdated;
use App\Actions\Room\UpdateRoomEloAction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Atrox\Haikunator;
use DataTables;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Jobs\QuickMatchJob;
use App\Presenters\RoomDataTablePresenter;

class RoomController extends Controller
{
    public const INITIAL_FEN = 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1';

    /**
     * Unified logic for generating Room DataTables using the app's active locale.
     */
    public function getRoomsData(Request $request)
    {
        if ($request->ajax()) {
            $ongoingRooms = Room::ongoing()->get();
            foreach ($ongoingRooms as $r) {
                if ($r->hasTimedOut()) {
                    $r->processTimeout();
                }
            }

            $rooms = Room::select(['fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at']);

            // Instantiate the presenter dynamically using the active app locale
            $presenter = new RoomDataTablePresenter(app()->getLocale());

            return Datatables::of($rooms)
                ->addColumn('code', fn($row) => $presenter->formatCode($row))
                ->addColumn('turn', fn($row) => $presenter->formatTurn($row))
                ->addColumn('result', fn($row) => $presenter->formatResult($row))
                ->addColumn('action', fn($row) => $presenter->formatAction($row))
                ->addColumn('time', fn($row) => $presenter->formatTime($row))
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
                    $query->whereRaw("modified_at like ?", ["%{$keyword}%"]);
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
        $latestRoom = Room::whereNull('pass')
            ->whereNull('host_id')
            ->whereNull('result')
            ->orderBy('modified_at', 'desc')
            ->offset($request->input('offset'))
            ->first();

        if ($latestRoom) {
            $color = str_contains($latestRoom->fen, ' r ') ? 'red' : (str_contains($latestRoom->fen, ' b ') ? 'black' : null);
            if ($color) return response()->json(['color' => $color, 'room' => $latestRoom]);
        }
        return response()->json(['room' => null]);
    }

    public static function getNewRoom()
    {
        $firstRoom = Room::where('fen', env('INITIAL_FEN', self::INITIAL_FEN))
            ->whereNull('pass')
            ->whereNull('host_id')
            ->whereNull('result')
            ->orderBy('modified_at', 'desc')
            ->first();

        return response()->json(['room' => $firstRoom]);
    }

    public function create(Request $request)
    {
        $room = Room::firstOrCreate(
            ['code' => $request->input('ma-phong')],
            [
                'fen'           => $request->input('FEN'),
                'moves'         => json_encode([]),
                'host_id'       => $request->input('host_id'),
                'name'          => $request->input('ten-phong'),
                'pass'          => $request->input('pass'),
                'modified_at'   => now(),
                'black_time'    => 600,
                'red_time'      => 600,
                'active_player' => null,
                'last_update'   => null,
            ]
        );

        if (!$room->wasRecentlyCreated) {
            $room->update([
                'fen'         => $request->input('FEN'),
                'host_id'     => $request->input('host_id'),
                'name'        => $request->input('ten-phong'),
                'pass'        => $request->input('pass'),
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
        $room = Room::firstOrNew(['code' => $request->input('ma-phong')]);

        if (!is_null($room->result)) {
            return response()->json(['success' => false, 'message' => __('Game already finished')], 400);
        }

        $room->fen = $request->input('FEN');
        $room->modified_at = now();
        $move = $request->input('move');

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
        try {
            return response()->json($updateRoomEloAction->execute($request->input('ma-phong'), $request->input('result')));
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

                $messageKey = $this->getResultMessageKey($auth_id == $room->host_id, $result);
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
        if (!$room->tournament_id || !$room->next_room_code || $result === '0') return;

        $winnerId = ($result === '1') ? $room->host_id : $room->guest_id;
        $winner = User::find($winnerId);

        if ($winner) {
            app(TournamentController::class)->handleTournamentAdvancement($room, $winner);
        }
    }

    public function updateSideResult(Request $request)
    {
        if ($request->has('lang')) app()->setLocale($request->input('lang'));

        $room = Room::where('code', $request->input('ma-phong'))->first();

        if ($room && is_null($room->result)) {
            $room->update(['result' => $request->input('result'), 'modified_at' => now()]);
        }

        $successMessages = [
            'red' => ['-1' => __('Red lost!'), '0' => __('Draw.'), '1' => __('Red won!')],
            'black' => ['-1' => __('Black won!'), '0' => __('Draw.'), '1' => __('Black lost!')],
        ];

        return response()->json(['success' => $successMessages[$request->input('side')][$request->input('result')] ?? __('Result recorded.')]);
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

    public function getPass(Room $room, $code) { return Room::where('code', $code)->value('pass'); }

    public function changePass(Request $request)
    {
        $pass = $request->input('pass');

        if (empty($pass)) {
            return response()->json(['message' => __('Password cannot be empty'), 'code' => 0]);
        }

        Room::where('code', $request->input('ma-phong'))->update(['pass' => $pass]);
        return response()->json(['message' => __('Changed password successfully!'), 'code' => 1]);
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

    public function prepareAnonymousRoom(string $sessionId): Room
    {
        $initialFen = env('INITIAL_FEN', self::INITIAL_FEN);

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

    /**
     * Unified Anonymous Quick Match utilizing App Locale
     */
    public function anonymousQuickMatch(Request $request)
    {
        $sessionId = $request->session()->get('anonymous_match_id', Str::random(32));
        $request->session()->put('anonymous_match_id', $sessionId);

        $room = $this->prepareAnonymousRoom($sessionId);
        $matched = $room && $room->host_session && $room->guest_session;
        $isHost = $room->host_session === $sessionId;

        return response()->json([
            'code' => 1,
            'message' => $matched ? __('Opponent found!') : __('Looking for opponent...'),
            'session_id' => $sessionId,
            'matched' => $matched,
            'room_code' => $room->code,
            'room_name' => $room->name,
            'side' => $matched ? ($isHost ? 'red' : 'black') : null,
            'color' => $matched ? ($isHost ? __('red') : __('black')) : null,
        ]);
    }

    /**
     * Unified Check Anonymous Match Status utilizing App Locale
     */
    public function checkAnonymousMatchStatus(Request $request)
    {
        $sessionId = $request->input('session_id');
        if (!$sessionId) return response()->json(['status' => 'error', 'message' => __('Session ID required.')], 400);

        $room = $this->prepareAnonymousRoom($sessionId);

        if ($room->host_session && $room->guest_session) {
            $isHost = $room->host_session == $sessionId;
            return response()->json([
                'status'    => 'matched',
                'room_code' => $room->code,
                'room_name' => $room->name,
                'side'      => $isHost ? 'red' : 'black',
                'color'     => $isHost ? __('red') : __('black'),
            ]);
        }
        return response()->json(['status' => 'waiting']);
    }

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
