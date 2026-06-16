<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\Room;
use App\Models\User;
use App\Models\Puzzle;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PuzzleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TournamentController extends Controller
{
    private function getRouteName($key)
    {
        $locale = app()->getLocale();
        $defaultLocale = config('locales.default', 'vi');
        return $locale === $defaultLocale ? $key : "{$locale}.{$key}";
    }

    private function checkAuth()
    {
        if (!auth()->check()) {
            abort(403, __('Bạn không có quyền truy cập trang này.'));
        }
    }

    private function authorizeCreator(Tournament $tournament)
    {
        if ($tournament->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403, __('Bạn không có quyền quản lý giải đấu này.'));
        }
    }

    public function join(Request $request, $slug)
    {
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

    public function generateBracket($slug)
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

    private function createBracketNodes(Tournament $tournament, $players)
    {
        $totalPlayers = $players->count();
        if ($totalPlayers < 2) return;

        $totalRounds = max(1, log($totalPlayers, 2));
        $previousRoundRooms = [];

        for ($round = $totalRounds; $round >= 1; $round--) {
            $matchesInRound = $totalPlayers / pow(2, $round);
            $currentRoundRooms = [];

            for ($i = 0; $i < $matchesInRound; $i++) {
                $room = Room::create([
                    'code' => md5(time() . uniqid()),
                    'fen' => env('INITIAL_FEN'),
                    'tournament_id' => $tournament->id,
                    'tournament_round' => $round,
                    'red_time' => 600,
                    'black_time' => 600,
                    'modified_at' => now(),
                    'next_room_code' => $round == $totalRounds ? null : $previousRoundRooms[floor($i / 2)]->code
                ]);

                if ($round == 1) {
                    $p1 = $players->pop();
                    $p2 = $players->pop();

                    $room->update([
                        'host_id' => $p1 ? $p1->id : null,
                        'guest_id' => $p2 ? $p2->id : null,
                        'name' => ($p1 && $p2) ? "{$p1->name} vs {$p2->name}" : __('TBD')
                    ]);
                }

                $currentRoundRooms[] = $room;
            }
            $previousRoundRooms = $currentRoundRooms;
        }
    }

    public function index(Request $request)
    {
        $tournaments = Tournament::withCount('users')->orderBy('start_date', 'desc')->paginate(10);

        return view('tournaments.index', localized_page_data('tournaments.index', app()->getLocale(), [
            'headTitle' => $request->route('headTitle'),
            'bodyClass' => 'dashboard',
            'tournaments' => $tournaments,
            'randomRoom' => RoomController::getRandomRoom(),
            'roomCode' => '',
            'cdnUrl' => url(''),
        ]));
    }

    public function show(Request $request, $slug)
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
            'randomRoom' => RoomController::getRandomRoom(),
            'roomCode' => '',
            'cdnUrl' => url(''),
        ], ['slug' => $slug]));
    }

    public function create(Request $request)
    {
        $this->checkAuth();
        return view('tournaments.create', localized_page_data('tournaments.create', app()->getLocale(), [
            'headTitle' => $request->route('headTitle'),
            'bodyClass' => 'dashboard',
            'randomRoom' => RoomController::getRandomRoom(),
            'roomCode' => '',
            'cdnUrl' => url(''),
        ]));
    }

    public function store(Request $request)
    {
        $this->checkAuth();
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
            'start_date' => 'required|date',
            'status' => 'required|in:open,in_progress,completed,cancelled',
            'max_players' => 'required|integer|min:2',
        ]);

        $data = $request->all();
        $data['user_id'] = auth()->id();

        if ($request->hasFile('cover_photo')) {
            $data['cover_photo'] = $request->file('cover_photo')->store('tournaments', 'public');
        }

        Tournament::create($data);
        return redirect()->route($this->getRouteName('tournaments.index'))->with('success', __('Tạo giải đấu thành công!'));
    }

    public function edit(Request $request, $slug)
    {
        $this->checkAuth();
        $tournament = Tournament::where('slug', $slug)->firstOrFail();
        $this->authorizeCreator($tournament);

        return view('tournaments.edit', localized_page_data('tournaments.edit', app()->getLocale(), [
            'headTitle' => $request->route('headTitle') . ': ' . $tournament->name,
            'bodyClass' => 'dashboard',
            'tournament' => $tournament,
            'randomRoom' => RoomController::getRandomRoom(),
            'roomCode' => '',
            'cdnUrl' => url(''),
        ], ['slug' => $slug]));
    }

    public function update(Request $request, $slug)
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
            'max_players' => 'required|integer|min:2',
        ]);

        $data = $request->all();

        if ($request->hasFile('cover_photo')) {
            if ($tournament->cover_photo) {
                Storage::disk('public')->delete($tournament->cover_photo);
            }
            $data['cover_photo'] = $request->file('cover_photo')->store('tournaments', 'public');
        }

        // Clear bracket if status is reverted/updated back to 'open'
        if (isset($data['status']) && $data['status'] === 'open' && $tournament->status !== 'open') {
            $tournament->rooms()->delete();
        }

        $tournament->update($data);
        return redirect()->route($this->getRouteName('tournaments.show'), $tournament->slug)->with('success', __('Cập nhật giải đấu thành công!'));
    }

    // FUNCTION MỚI: Xử lý Hủy giải đấu
    public function cancel($slug)
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

    // CẬP NHẬT: Thêm logic chặn xóa vĩnh viễn
    public function destroy($slug)
    {
        $this->checkAuth();
        $tournament = Tournament::where('slug', $slug)->firstOrFail();
        $this->authorizeCreator($tournament);

        // Chặn xóa nếu giải đang diễn ra hoặc đã kết thúc
        if ($tournament->status === 'in_progress' || $tournament->status === 'completed') {
            return back()->with('error', __('Không thể xóa giải đấu đang diễn ra hoặc đã kết thúc. Dữ liệu này cần được lưu trữ.'));
        }

        // Bắt buộc dùng nút "Hủy" nếu có người tham gia (trừ khi đã Hủy từ trước đó)
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
