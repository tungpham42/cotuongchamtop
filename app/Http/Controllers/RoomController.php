<?php

namespace App\Http\Controllers;

use DB;
use Exception;
use App\Models\Room;
use App\Models\User;
use App\Events\RoomUpdated;
use App\Actions\Room\UpdateRoomEloAction;
use App\Actions\User\AwardMatchPlayedKarmaAction;
use App\Actions\User\AwardMatchWinKarmaAction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Atrox\Haikunator;
use DataTables;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Jobs\QuickMatchJob;
use App\Presenters\RoomDataTablePresenter;
use App\Events\WebRtcSignal;

class RoomController extends Controller
{
    public const INITIAL_FEN = 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1';

    /**
     * Unified logic for generating Room DataTables using the app's active locale.
     */
    public function getRoomsData(Request $request): JsonResponse
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

        return response()->json([]);
    }

    public static function quickMatch(): JsonResponse
    {
        dispatch(new QuickMatchJob());
        return response()->json([
            'code' => 1,
            'message' => 'Quick match queued successfully.'
        ]);
    }

    public static function getLatestRoom(Request $request): JsonResponse
    {
        $latestRoom = Room::whereNull('pass')
            ->whereNull('host_id')
            ->whereNull('result')
            ->orderBy('modified_at', 'desc')
            ->offset((int) $request->input('offset'))
            ->first();

        if ($latestRoom) {
            $color = str_contains($latestRoom->fen, ' r ') ? 'red' : (str_contains($latestRoom->fen, ' b ') ? 'black' : null);
            if ($color) return response()->json(['color' => $color, 'room' => $latestRoom]);
        }
        return response()->json(['room' => null]);
    }

    public static function getNewRoom(): JsonResponse
    {
        $firstRoom = Room::where('fen', self::INITIAL_FEN)
            ->whereNull('pass')
            ->whereNull('host_id')
            ->whereNull('result')
            ->orderBy('modified_at', 'desc')
            ->first();

        return response()->json(['room' => $firstRoom]);
    }

    public function create(Request $request): JsonResponse
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

    public function compete(Request $request): void
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

    public function store(Request $request): JsonResponse
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

    public function join(Request $request): void
    {
        Room::updateOrInsert(
            ['code' => $request->input('ma-phong')],
            ['guest_id' => $request->input('guest_id'), 'modified_at' => now()]
        );
    }

    public function updateElo(Request $request, UpdateRoomEloAction $updateRoomEloAction): JsonResponse
    {
        try {
            return response()->json($updateRoomEloAction->execute($request->input('ma-phong'), $request->input('result')));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update Elo ratings'], 500);
        }
    }

    public function updateResult(
        Request $request,
        AwardMatchPlayedKarmaAction $playedKarma,
        AwardMatchWinKarmaAction $winKarma
    ): JsonResponse {
        $code = (string) $request->input('ma-phong');
        $result = (string) $request->input('result');
        $auth_id = (int) (auth()->id() ?? $request->input('id'));

        if ($request->has('lang')) app()->setLocale((string) $request->input('lang'));

        try {
            return DB::transaction(function () use ($code, $result, $auth_id, $playedKarma, $winKarma) {
                $room = Room::lockForUpdate()->where('code', $code)->firstOrFail();

                if (!in_array($auth_id, [$room->host_id, $room->guest_id])) {
                    throw new Exception(__('Bạn không có quyền cập nhật ván này.'));
                }

                if (is_null($room->result)) {
                    $room->update(['result' => $result, 'modified_at' => now()]);
                    $this->advanceTournament($room, $result);
                    $this->awardMatchKarma($room, $result, $playedKarma, $winKarma);
                }

                $messageKey = $this->getResultMessageKey($auth_id === $room->host_id, $result);
                return response()->json(['success' => __($messageKey)]);
            });
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }
    }

    /**
     * Award karma for a finished match: everyone who was a registered
     * (non-guest) participant gets the "played" karma, and the winner
     * additionally gets the "win" karma. Draws get no win bonus.
     * Safe to call for guest-session matches, since User::find() on a
     * null/non-existent id simply returns null and is skipped.
     */
    private function awardMatchKarma(
        Room $room,
        string $result,
        AwardMatchPlayedKarmaAction $playedKarma,
        AwardMatchWinKarmaAction $winKarma
    ): void {
        $host = $room->host_id ? User::find($room->host_id) : null;
        $guest = $room->guest_id ? User::find($room->guest_id) : null;

        if ($host) {
            $playedKarma->execute($host, $room->id);
        }
        if ($guest) {
            $playedKarma->execute($guest, $room->id);
        }

        if ($result === '1' && $host) {
            $winKarma->execute($host, $room->id);
        } elseif ($result === '-1' && $guest) {
            $winKarma->execute($guest, $room->id);
        }
    }

    private function getResultMessageKey(bool $isHost, string $result): string
    {
        if ($result === '0') return 'Hòa.';
        if ($isHost) return $result === '1' ? 'Chủ phòng thắng. Xin chúc mừng!' : 'Chủ phòng thua! Cố lên nhé!';
        return $result === '-1' ? 'Khách thắng. Xin chúc mừng!' : 'Khách thua! Cố lên nhé!';
    }

    private function advanceTournament(Room $room, string $result): void
    {
        if (!$room->tournament_id || !$room->next_room_code || $result === '0') return;

        $winnerId = ($result === '1') ? $room->host_id : $room->guest_id;
        $winner = User::find($winnerId);

        if ($winner) {
            app(TournamentController::class)->handleTournamentAdvancement($room, $winner);
        }
    }

    public function updateSideResult(
        Request $request,
        AwardMatchPlayedKarmaAction $playedKarma,
        AwardMatchWinKarmaAction $winKarma
    ): JsonResponse {
        if ($request->has('lang')) app()->setLocale((string) $request->input('lang'));

        $room = Room::where('code', $request->input('ma-phong'))->first();

        if ($room && is_null($room->result)) {
            $result = (string) $request->input('result');
            $room->update(['result' => $result, 'modified_at' => now()]);
            $this->awardMatchKarma($room, $result, $playedKarma, $winKarma);
        }

        $successMessages = [
            'red' => ['-1' => __('Red lost!'), '0' => __('Draw.'), '1' => __('Red won!')],
            'black' => ['-1' => __('Black won!'), '0' => __('Draw.'), '1' => __('Black lost!')],
        ];

        return response()->json(['success' => $successMessages[$request->input('side')][$request->input('result')] ?? __('Result recorded.')]);
    }

    public function show(Room $room, string $code): ?string
    {
        if (auth()->check()) auth()->user()->update(['last_seen_at' => now()]);
        return Room::where('code', $code)->value('fen');
    }

    public function getMoves(Room $room, string $code): JsonResponse
    {
        $moves = Room::where('code', $code)->value('moves');
        $decoded = $moves ? json_decode($moves, true) : [];
        return response()->json(is_array($decoded) ? $decoded : []);
    }

    public function getPass(Room $room, string $code): ?string
    {
        return Room::where('code', $code)->value('pass');
    }

    public function changePass(Request $request): JsonResponse
    {
        $pass = $request->input('pass');

        if (empty($pass)) {
            return response()->json(['message' => __('Password cannot be empty'), 'code' => 0]);
        }

        Room::where('code', $request->input('ma-phong'))->update(['pass' => $pass]);
        return response()->json(['message' => __('Changed password successfully!'), 'code' => 1]);
    }

    public function getEventStream(Room $room, string $code): StreamedResponse
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

    /**
     * Core matchmaking logic, shared by guest (session-based) and
     * authenticated (user-based) matchmaking.
     *
     * Guests are paired via host_session/guest_session; logged-in users are
     * paired via host_id/guest_id. Because a guest room never touches the
     * *_id columns and a logged-in room never touches the *_session columns,
     * the two pools never mix automatically - a guest can never be matched
     * into a logged-in user's room and vice versa.
     */
    private function prepareMatchRoom(int|string $identifier, bool $isAuthenticated): Room
    {
        $initialFen = self::INITIAL_FEN;
        $hostColumn = $isAuthenticated ? 'host_id' : 'host_session';
        $guestColumn = $isAuthenticated ? 'guest_id' : 'guest_session';

        return DB::transaction(function () use ($identifier, $initialFen, $isAuthenticated, $hostColumn, $guestColumn) {
            /*
             * A matchmaking identity (guest session or user id) can belong to
             * exactly one room. Check this before looking for another room so
             * repeated requests from the same browser/user never consume
             * another seat.
             *
             * Do NOT use Room::ongoing() here because a waiting room has only
             * a host and no guest yet, so it is not considered "ongoing" by
             * that scope.
             */
            $currentRoom = Room::whereNull('result')
                ->whereNull('pass')
                ->where('fen', $initialFen)
                ->where(function ($query) use ($identifier, $hostColumn, $guestColumn) {
                    $query->where($hostColumn, $identifier)
                        ->orWhere($guestColumn, $identifier);
                })
                ->lockForUpdate()
                ->orderByDesc('modified_at')
                ->first();

            if ($currentRoom) {
                return $currentRoom;
            }

            /*
             * Match against the newest room that has exactly ONE player in
             * the same pool (guest or logged-in).
             *
             * This creates the required queueing behaviour:
             *   room A: player 1 + player 2 -> full, never selected again
             *   room B: player 3 waits      -> selected for player 4
             *   room C: player 5 waits      -> selected for player 6
             *
             * Only rooms with the initial FEN participate in this automatic
             * matchmaking pool. A finished/full/in-progress room is ignored.
             */
            $availableRoom = Room::where('fen', $initialFen)
                ->whereNull('pass')
                ->whereNull('result')
                ->whereNotNull($hostColumn)
                ->whereNull($guestColumn)
                ->when($isAuthenticated, fn ($query) => $query->where('host_id', '!=', $identifier))
                ->orderByDesc('modified_at')
                ->lockForUpdate()
                ->first();

            if ($availableRoom) {
                // The row is locked, so concurrent requests cannot take the
                // same second seat and turn this into a 3-player room.
                $availableRoom->update([
                    $guestColumn  => $identifier,
                    'modified_at' => now(),
                ]);

                return $availableRoom->fresh();
            }

            /*
             * No one-player room exists in this pool: create a brand-new
             * waiting room. The creator is always the red/host player; the
             * next identity assigned to this row becomes black/guest.
             */
            return Room::create([
                'code'        => md5(time() . $identifier . uniqid('', true)),
                'fen'         => $initialFen,
                'name'        => Haikunator::haikunate(["tokenLength" => 0, "delimiter" => " "]),
                $hostColumn   => $identifier,
                'red_time'    => 600,
                'black_time'  => 600,
                'modified_at' => now(),
            ]);
        });
    }

    /**
     * Kept for the existing anonymous-only endpoints below.
     */
    public function prepareAnonymousRoom(string $sessionId): Room
    {
        return $this->prepareMatchRoom($sessionId, false);
    }

    /**
     * Unified Anonymous Quick Match utilizing App Locale
     */
    public function anonymousQuickMatch(Request $request): JsonResponse
    {
        $sessionId = (string) $request->session()->get('anonymous_match_id', Str::random(32));
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
    public function checkAnonymousMatchStatus(Request $request): JsonResponse
    {
        $sessionId = $request->input('session_id');
        if (!$sessionId) return response()->json(['status' => 'error', 'message' => __('Session ID required.')], 400);

        $room = $this->prepareAnonymousRoom((string) $sessionId);

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

    public function switchTurn(Request $request, string $roomCode): JsonResponse
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

    public function startTimer(string $roomCode, string $player): JsonResponse
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

    public function getTime(string $roomCode): JsonResponse
    {
        $room = Room::where('code', $roomCode)->firstOrFail();
        $times = $room->getCalculatedTimes();

        return response()->json(array_merge($times, [
            'last_update' => optional($room->last_update)->toDateTimeString(),
        ]));
    }

    public function saveTime(Request $request, string $roomCode): JsonResponse
    {
        $room = Room::where('code', $roomCode)->firstOrFail();

        $room->red_time = max(0, (int) $request->input('red_time', $room->red_time));
        $room->black_time = max(0, (int) $request->input('black_time', $room->black_time));
        $room->last_update = now();
        $room->save();

        return response()->json([
            'success' => true,
            'red_time' => $room->red_time,
            'black_time' => $room->black_time,
            'last_update' => $room->last_update,
        ]);
    }

    public function findMatch(Request $request): JsonResponse
    {
        $user = auth()->user();

        if ($user) {
            return $this->findMatchForUser($user);
        }

        return $this->findMatchForGuest($request);
    }

    /**
     * Match a logged-in user against another logged-in user, using their
     * user id (host_id/guest_id) rather than a browser session.
     */
    private function findMatchForUser($user): JsonResponse
    {
        $room = $this->prepareMatchRoom($user->id, true);
        $matched = $room && $room->host_id && $room->guest_id;
        $isHost = (int) $room->host_id === (int) $user->id;

        $opponentId = $matched ? ($isHost ? $room->guest_id : $room->host_id) : null;
        $opponentName = $opponentId ? User::find($opponentId)?->name : null;

        return response()->json([
            'code' => 1,
            'message' => $matched ? __('Đã tìm thấy đối thủ!') : __('Đang tìm trận...'),
            'matched' => $matched,
            'room_code' => $room->code,
            'room_name' => $room->name,
            'side' => $matched ? ($isHost ? 'red' : 'black') : null,
            'opponent_name' => $opponentName,
        ]);
    }

    /**
     * Match a guest against another guest, using their browser session id,
     * exactly as before.
     */
    private function findMatchForGuest(Request $request): JsonResponse
    {
        $sessionId = (string) ($request->input('session_id') ?: $request->session()->get('match_session_id', Str::random(32)));
        $request->session()->put('match_session_id', $sessionId);

        $room = $this->prepareMatchRoom($sessionId, false);
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

    public function checkMatchStatus(Request $request): JsonResponse
    {
        $user = auth()->user();

        if ($user) {
            $room = $this->prepareMatchRoom($user->id, true);

            if ($room->host_id && $room->guest_id) {
                $isHost = (int) $room->host_id === (int) $user->id;
                return response()->json([
                    'status'    => 'matched',
                    'room_code' => $room->code,
                    'room_name' => $room->name,
                    'side'      => $isHost ? 'red' : 'black',
                ]);
            }
            return response()->json(['status' => 'waiting']);
        }

        $sessionId = $request->input('session_id');
        if (!$sessionId) return response()->json(['status' => 'error', 'message' => __('Không tìm thấy phiên bản kết nối (Session ID).')], 400);

        $room = $this->prepareMatchRoom((string) $sessionId, false);

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

    public function startMatch(string $roomCode): JsonResponse
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

    /**
     * Get host ID of a room.
     */
    public function getHostId(Request $request): JsonResponse
    {
        $code = $request->input('ma-phong') ?? $request->input('code');
        $hostId = Room::where('code', $code)->value('host_id');

        return response()->json(['host_id' => $hostId]);
    }

    /**
     * Get both host and guest IDs of a room.
     */
    public function getRoomIds(Request $request): JsonResponse
    {
        $code = $request->input('ma-phong') ?? $request->input('code');
        $roomData = Room::select('host_id', 'guest_id')->where('code', $code)->first();

        return response()->json($roomData ? $roomData->toArray() : []);
    }

    /**
     * Check if a room code exists.
     */
    public function hasRoomcode(Request|string|null $request = null): JsonResponse|bool
    {
        // If called directly with a string (e.g., RoomController::hasRoomCode('12345'))
        if (is_string($request)) {
            return Room::where('code', $request)->exists();
        }

        // Resolve current request if called via HTTP route without injected parameter
        $request = $request ?? request();
        $code = $request->input('ma-phong') ?? $request->input('code');

        return response()->json([
            'exists' => Room::where('code', $code)->exists(),
        ]);
    }

    /**
     * Broadcast WebRTC signaling messages (offers, answers, ICE candidates)
     */
    public function sendSignal(Request $request, string $code): JsonResponse
    {
        $payload = $request->input('payload');
        $senderId = auth()->id() ?? $request->input('sender_id') ?? $request->session()->getId();

        if (empty($payload)) {
            return response()->json(['error' => 'Payload is required'], 422);
        }

        broadcast(new WebRtcSignal($code, $payload, $senderId))->toOthers();

        return response()->json(['success' => true]);
    }
}
