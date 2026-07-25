<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\Room;
use App\Models\User;
use App\Models\Puzzle;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PuzzleController;
use App\Http\Controllers\MailController; // Added for email notifications
use App\Actions\Room\GetRandomRoomAction; // Imported the action
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TournamentController extends Controller
{
    private function getRouteName(string $key): string
    {
        $locale = app()->getLocale();
        $defaultLocale = config('locales.default', 'vi');
        return $locale === $defaultLocale ? $key : "{$locale}.{$key}";
    }

    private function checkAuth(): void
    {
        if (!auth()->check()) {
            abort(403, __('Bạn không có quyền truy cập trang này.'));
        }
    }

    private function authorizeCreator(Tournament $tournament): void
    {
        if ($tournament->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403, __('Bạn không có quyền quản lý giải đấu này.'));
        }
    }

    public function join(Request $request, string $slug): RedirectResponse
    {
        // Add this missing authorization check
        $this->checkAuth();

        $tournament = Tournament::where('slug', $slug)->firstOrFail();
        $userId = auth()->id();

        if ($tournament->status !== 'open') {
            return back()->with('error', __('Đăng ký đã đóng hoặc giải đấu không ở trạng thái Mở.'));
        }

        if ($tournament->users()->count() >= $tournament->max_players) {
            return back()->with('error', __('Giải đấu đã đủ người tham gia.'));
        }

        if (!$tournament->users()->where('user_id', $userId)->exists()) {
            $tournament->users()->attach($userId);
        }

        return back()->with('success', __('Bạn đã đăng ký tham gia giải đấu thành công!'));
    }

    public function generateBracket(string $slug): RedirectResponse
    {
        $this->checkAuth();
        $tournament = Tournament::where('slug', $slug)->firstOrFail();
        $this->authorizeCreator($tournament);

        if ($tournament->users()->count() < 2) {
            return back()->with('error', __('Chưa đủ người chơi để tiến hành bốc thăm.'));
        }

        if ($tournament->status !== 'open') {
            return back()->with('error', __('Giải đấu đã bắt đầu hoặc đã bị hủy.'));
        }

        $tournament->rooms()->delete();
        $players = $tournament->users()->inRandomOrder()->get();
        $tournament->update(['status' => 'in_progress']);
        $this->createBracketNodes($tournament, $players);

        return back()->with('success', __('Đã bốc thăm và tạo sơ đồ thi đấu thành công!'));
    }

    private function createBracketNodes(Tournament $tournament, Collection|EloquentCollection $players): void
    {
        $totalPlayers = $players->count();
        if ($totalPlayers < 2) return;

        // Calculate the total rounds needed (e.g., 3 players = 2 rounds, 5 players = 3 rounds)
        $totalRounds = (int) ceil(log($totalPlayers, 2));
        $powerOf2 = pow(2, $totalRounds);

        $roomsByRound = [];
        $previousRoundRooms = [];

        // Build the full empty bracket from the Final (highest round) down to Round 1
        for ($round = $totalRounds; $round >= 1; $round--) {
            $matchesInRound = pow(2, $totalRounds - $round);
            $currentRoundRooms = [];

            for ($i = 0; $i < $matchesInRound; $i++) {
                $nextRoomCode = null;
                if ($round < $totalRounds) {
                    $nextRoomIndex = (int) floor($i / 2);
                    $nextRoomCode = $previousRoundRooms[$nextRoomIndex]->code;
                }

                $room = Room::create([
                    'code' => md5(time() . uniqid() . $round . $i),
                    'fen' => env('INITIAL_FEN', 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1'),
                    'tournament_id' => $tournament->id,
                    'tournament_round' => $round,
                    'red_time' => 600,
                    'black_time' => 600,
                    'active_player' => 'waiting',
                    'modified_at' => now(),
                    'next_room_code' => $nextRoomCode
                ]);

                $currentRoundRooms[] = $room;
            }
            $roomsByRound[$round] = $currentRoundRooms;
            $previousRoundRooms = $currentRoundRooms;
        }

        // Distribute players in Round 1 handling "byes" for odd/non-power-of-2 numbers
        $round1Rooms = $roomsByRound[1];

        // Calculate how many actual 2-player matches happen in round 1
        $matchesToPlay = $totalPlayers - ($powerOf2 / 2);

        $playersQueue = $players->values();
        $playerIndex = 0;

        foreach ($round1Rooms as $index => $room) {
            if ($index < $matchesToPlay) {
                // Regular match with 2 players
                $p1 = $playersQueue[$playerIndex++];
                $p2 = $playersQueue[$playerIndex++];

                $room->update([
                    'host_id' => $p1->id,
                    'guest_id' => $p2->id,
                    'name' => "{$p1->name} vs {$p2->name}"
                ]);

                $this->notifyMatchPlayers($p1, $p2, $room);
            } else {
                // Bye: Only 1 player in this branch, move them directly to Round 2
                $p1 = $playersQueue[$playerIndex++];

                if ($room->next_room_code) {
                    $nextRoom = Room::where('code', $room->next_room_code)->first();
                    if ($nextRoom) {
                        if (empty($nextRoom->host_id)) {
                            $nextRoom->update(['host_id' => $p1->id]);
                        } else {
                            $nextRoom->update(['guest_id' => $p1->id]);

                            // If this Round 2 match is now full from two byes, notify them
                            $host = User::find($nextRoom->host_id);
                            $guest = User::find($nextRoom->guest_id);

                            $nextRoom->update(['name' => "{$host->name} vs {$guest->name}"]);
                            $this->notifyMatchPlayers($host, $guest, $nextRoom);
                        }
                    }
                }

                // Delete the unused Round 1 room to keep the database clean
                $room->delete();
            }
        }
    }

    /**
     * Send email notifications to both players of a newly scheduled room.
     */
    public function notifyMatchPlayers(User $player1, User $player2, Room $room): void
    {
        $mailController = app(MailController::class);
        $lang = app()->getLocale();

        // Generate separate URLs for Red (Player 1) and Black (Player 2)
        $roomUrlRed = localized_url('room.red', ['code' => $room->code]);
        $roomUrlBlack = localized_url('room.black', ['code' => $room->code]);

        // Send to Player 1 (Red)
        if (isset($player1->email)) {
            $emailDataP1 = $this->getTournamentEmailContent($lang, $player2->name, $player1->name, $room->name, $roomUrlRed);
            $mailController->sendSmtpMail($player1->email, $emailDataP1['subject'], $emailDataP1['content'], $emailDataP1['smtp_messages']);
        }

        // Send to Player 2 (Black)
        if (isset($player2->email)) {
            $emailDataP2 = $this->getTournamentEmailContent($lang, $player1->name, $player2->name, $room->name, $roomUrlBlack);
            $mailController->sendSmtpMail($player2->email, $emailDataP2['subject'], $emailDataP2['content'], $emailDataP2['smtp_messages']);
        }
    }

    /**
     * Translated templates for tournament match notifications in 5 languages.
     */
    private function getTournamentEmailContent(string $lang, string $opponentName, string $playerName, string $roomName, string $roomUrl): array
    {
        $translations = [
            'en' => [
                'subject' => "Tournament Match Scheduled: You vs {$opponentName}",
                'content' => "<p>Hi {$playerName},</p>
                              <p>Your tournament match against <strong>{$opponentName}</strong> is ready!</p>
                              <p>Please join your match room \"{$roomName}\" here: <a target=\"_blank\" href=\"{$roomUrl}\">{$roomUrl}</a></p>
                              <p>Good luck!</p>",
                'smtp_messages' => [
                    "Notification to {$playerName} failed",
                    "Notification to {$playerName} sent",
                    "Message could not be sent"
                ]
            ],

            'vi' => [
                'subject' => "Lịch Thi Đấu Giải: Bạn vs {$opponentName}",
                'content' => "<p>Chào {$playerName},</p>
                              <p>Trận đấu giải của bạn với <strong>{$opponentName}</strong> đã sẵn sàng!</p>
                              <p>Vui lòng tham gia phòng thi đấu \"{$roomName}\" tại đây: <a target=\"_blank\" href=\"{$roomUrl}\">{$roomUrl}</a></p>
                              <p>Chúc bạn may mắn!</p>",
                'smtp_messages' => [
                    "Gửi thông báo cho {$playerName} thất bại",
                    "Gửi thông báo cho {$playerName} thành công",
                    "Tin nhắn không gửi được"
                ]
            ],

            'ja' => [
                'subject' => "トーナメント対局予定：あなた vs {$opponentName}",
                'content' => "<p>{$playerName}さん、こんにちは。</p>
                              <p><strong>{$opponentName}</strong>とのトーナメント対局の準備が整いました！</p>
                              <p>こちらの対局ルーム「{$roomName}」にご参加ください：<a target=\"_blank\" href=\"{$roomUrl}\">{$roomUrl}</a></p>
                              <p>頑張ってください！</p>",
                'smtp_messages' => [
                    "{$playerName}への通知に失敗しました",
                    "{$playerName}への通知を送信しました",
                    "メッセージを送信できませんでした"
                ]
            ],

            'ko' => [
                'subject' => "토너먼트 경기 예정: 귀하 vs {$opponentName}",
                'content' => "<p>안녕하세요 {$playerName}님,</p>
                              <p><strong>{$opponentName}</strong>님과의 토너먼트 경기가 준비되었습니다!</p>
                              <p>여기 \"{$roomName}\" 경기 방에 참여해 주세요: <a target=\"_blank\" href=\"{$roomUrl}\">{$roomUrl}</a></p>
                              <p>행운을 빕니다!</p>",
                'smtp_messages' => [
                    "{$playerName}님에게 알림 전송 실패",
                    "{$playerName}님에게 알림이 전송되었습니다",
                    "메시지를 보내지 못했습니다"
                ]
            ],

            'zh' => [
                'subject' => "锦标赛比赛已安排：你 vs {$opponentName}",
                'content' => "<p>你好 {$playerName}，</p>
                              <p>你与 <strong>{$opponentName}</strong> 的锦标赛比赛已经准备就绪！</p>
                              <p>请点击此处加入你的比赛房间“{$roomName}”：<a target=\"_blank\" href=\"{$roomUrl}\">{$roomUrl}</a></p>
                              <p>祝你好运！</p>",
                'smtp_messages' => [
                    "向 {$playerName} 发送通知失败",
                    "已向 {$playerName} 发送通知",
                    "无法发送消息"
                ]
            ],
        ];

        return $translations[$lang] ?? $translations['vi'];
    }

    /**
     * Call this method from wherever your match finishes (e.g., RoomController or WebSockets)
     * to advance the winner to the next round and notify both players once the room is full.
     */
    public function handleTournamentAdvancement(Room $currentRoom, User $winner): void
    {
        if ($currentRoom->next_room_code) {
            $nextRoom = Room::where('code', $currentRoom->next_room_code)->first();

            if ($nextRoom) {
                if (empty($nextRoom->host_id)) {
                    $nextRoom->update(['host_id' => $winner->id]);
                } else {
                    $nextRoom->update(['guest_id' => $winner->id]);

                    $host = User::find($nextRoom->host_id);
                    $guest = User::find($nextRoom->guest_id);

                    $nextRoom->update(['name' => "{$host->name} vs {$guest->name}"]);

                    $this->notifyMatchPlayers($host, $guest, $nextRoom);
                }
            }
        }
    }

    public function index(Request $request): View
    {
        $tournaments = Tournament::withCount('users')->orderBy('start_date', 'desc')->paginate(10);

        return view('tournaments.index', localized_page_data('tournaments.index', app()->getLocale(), [
            'headTitle' => $request->route('headTitle'),
            'bodyClass' => 'dashboard',
            'tournaments' => $tournaments,
            'randomRoom' => app(GetRandomRoomAction::class)->execute(),
            'roomCode' => '',
            'cdnUrl' => url(''),
        ]));
    }

    public function show(Request $request, string $slug): View
    {
        $tournament = Tournament::with(['creator', 'users', 'rooms.host', 'rooms.guest'])
            ->where('slug', $slug)
            ->firstOrFail();

        $rounds = $tournament->rooms->groupBy('tournament_round')->sortKeys();

        return view('tournaments.show', localized_page_data('tournaments.show', app()->getLocale(), [
            'headTitle'  => $tournament->name,
            'bodyClass' => 'dashboard',
            'tournament' => $tournament,
            'rounds' => $rounds,
            'randomRoom' => app(GetRandomRoomAction::class)->execute(),
            'roomCode' => '',
            'cdnUrl' => url(''),
        ], ['slug' => $slug]));
    }

    public function create(Request $request): View
    {
        $this->checkAuth();
        return view('tournaments.create', localized_page_data('tournaments.create', app()->getLocale(), [
            'headTitle' => $request->route('headTitle'),
            'bodyClass' => 'dashboard',
            'randomRoom' => app(GetRandomRoomAction::class)->execute(),
            'roomCode' => '',
            'cdnUrl' => url(''),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->checkAuth();
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
            'start_date' => 'required|date',
            'status' => 'required|in:open,in_progress,completed,cancelled',
            'max_players' => 'required|in:2,4,8,16',
        ]);

        $data = $request->all();
        $data['user_id'] = auth()->id();

        if ($request->hasFile('cover_photo')) {
            $data['cover_photo'] = $request->file('cover_photo')->store('tournaments', 'public');
        }

        Tournament::create($data);
        return redirect()->route($this->getRouteName('tournaments.index'))->with('success', __('Tạo giải đấu thành công!'));
    }

    public function edit(Request $request, string $slug): View
    {
        $this->checkAuth();
        $tournament = Tournament::where('slug', $slug)->firstOrFail();
        $this->authorizeCreator($tournament);

        return view('tournaments.edit', localized_page_data('tournaments.edit', app()->getLocale(), [
            'headTitle' => $request->route('headTitle') . ': ' . $tournament->name,
            'bodyClass' => 'dashboard',
            'tournament' => $tournament,
            'randomRoom' => app(GetRandomRoomAction::class)->execute(),
            'roomCode' => '',
            'cdnUrl' => url(''),
        ], ['slug' => $slug]));
    }

    public function update(Request $request, string $slug): RedirectResponse
    {
        $this->checkAuth();
        $tournament = Tournament::where('slug', $slug)->firstOrFail();
        $this->authorizeCreator($tournament);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
            'start_date' => 'required|date',
            'status' => 'required|in:open,in_progress,completed,cancelled',
            'max_players' => 'required|in:2,4,8,16',
        ]);

        $data = $request->all();

        if ($request->hasFile('cover_photo')) {
            if ($tournament->cover_photo) {
                Storage::disk('public')->delete($tournament->cover_photo);
            }
            $data['cover_photo'] = $request->file('cover_photo')->store('tournaments', 'public');
        }

        if (isset($data['status']) && $data['status'] === 'open' && $tournament->status !== 'open') {
            $tournament->rooms()->delete();
        }

        $tournament->update($data);
        return redirect()->route($this->getRouteName('tournaments.show'), $tournament->slug)->with('success', __('Cập nhật giải đấu thành công!'));
    }

    public function cancel(string $slug): RedirectResponse
    {
        $this->checkAuth();
        $tournament = Tournament::where('slug', $slug)->firstOrFail();
        $this->authorizeCreator($tournament);

        if ($tournament->status !== 'open') {
            return back()->with('error', __('Chỉ có thể hủy giải đấu khi giải đang ở trạng thái Mở đăng ký.'));
        }

        $tournament->update(['status' => 'cancelled']);
        return back()->with('success', __('Giải đấu đã được chuyển sang trạng thái Đã Hủy.'));
    }

    public function destroy(string $slug): RedirectResponse
    {
        $this->checkAuth();
        $tournament = Tournament::where('slug', $slug)->firstOrFail();
        $this->authorizeCreator($tournament);

        if ($tournament->status === 'in_progress' || $tournament->status === 'completed') {
            return back()->with('error', __('Không thể xóa giải đấu đang diễn ra hoặc đã kết thúc. Dữ liệu này cần được lưu trữ.'));
        }

        if ($tournament->status === 'open' && $tournament->users()->count() > 1) {
             return back()->with('error', __('Giải đấu đã có người đăng ký. Bạn chỉ có thể Hủy giải đấu để thông báo cho người tham gia, không được xóa vĩnh viễn.'));
        }

        if ($tournament->cover_photo) {
            Storage::disk('public')->delete($tournament->cover_photo);
        }

        $tournament->delete();
        return redirect()->route($this->getRouteName('tournaments.index'))->with('success', __('Đã xóa vĩnh viễn giải đấu!'));
    }
}
