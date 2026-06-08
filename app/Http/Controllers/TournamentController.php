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
    // Helper method to resolve localized route names for redirects
    private function getRouteName($key)
    {
        $locale = app()->getLocale();
        $defaultLocale = config('locales.default', 'vi');
        return $locale === $defaultLocale ? $key : "{$locale}.{$key}";
    }

    // Check Auth
    private function checkAuth()
    {
        if (!auth()->check()) {
            abort(403, __('Bạn không có quyền truy cập trang này.'));
        }
    }

    // NEW: Helper method to verify tournament ownership in the controller
    private function authorizeCreator(Tournament $tournament)
    {
        if ($tournament->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403, __('Bạn không có quyền quản lý giải đấu này.'));
        }
    }

    // Register a user for the tournament
    public function join(Request $request, $slug)
    {
        $tournament = Tournament::where('slug', $slug)->firstOrFail();
        $userId = auth()->id();

        if ($tournament->status !== 'open') {
            return back()->with('error', __('Registration is closed.'));
        }

        if ($tournament->users()->count() >= $tournament->max_players) {
            return back()->with('error', __('Tournament is full.'));
        }

        if (!$tournament->users()->where('user_id', $userId)->exists()) {
            $tournament->users()->attach($userId);
        }

        return back()->with('success', __('You have successfully joined the tournament!'));
    }

    // Generate Single Elimination Bracket
    public function generateBracket($slug)
    {
        $this->checkAuth();

        $tournament = Tournament::where('slug', $slug)->firstOrFail();

        // Double-check ownership
        $this->authorizeCreator($tournament);

        if ($tournament->users()->count() < 2) {
            return back()->with('error', __('Not enough players to generate a bracket.'));
        }

        if ($tournament->status !== 'open') {
            return back()->with('error', __('Tournament has already started.'));
        }

        // FIX: Delete existing rooms to prevent duplication when regenerating the bracket
        $tournament->rooms()->delete();

        $players = $tournament->users()->inRandomOrder()->get();

        $tournament->update(['status' => 'in_progress']);

        $this->createBracketNodes($tournament, $players);

        return back()->with('success', __('Bracket generated successfully!'));
    }

    private function createBracketNodes(Tournament $tournament, $players)
    {
        $totalPlayers = $players->count();
        if ($totalPlayers < 2) return;

        $totalRounds = max(1, log($totalPlayers, 2));

        $previousRoundRooms = [];

        // Loop backwards from Final (Round N) down to Round 1
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
                    // Link to the parent match in the bracket tree (if not the final)
                    'next_room_code' => $round == $totalRounds ? null : $previousRoundRooms[floor($i / 2)]->code
                ]);

                // If this is the first round, populate the players
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

    // List all tournaments
    public function index(Request $request)
    {
        $tournaments = Tournament::withCount('users')->orderBy('start_date', 'desc')->paginate(10);

        return view('tournaments.index', [
            'headTitle' => $request->route('headTitle'),
            'bodyClass' => 'dashboard',
            'tournaments' => $tournaments,
            // 'canonicalUrl' => $request->url(),
            'randomRoom' => RoomController::getRandomRoom(),
            'roomCode' => '',
            'cdnUrl' => url(''),
        ]);
    }

    public function show(Request $request, $slug)
    {
        // Thêm 'creator' vào mảng with()
        $tournament = Tournament::with(['creator', 'users', 'rooms.host', 'rooms.guest'])
            ->where('slug', $slug)
            ->firstOrFail();

        $rounds = $tournament->rooms->groupBy('tournament_round')->sortKeys();

        return view('tournaments.show', [
            'headTitle'  => $tournament->name,
            'bodyClass' => 'dashboard',
            'tournament' => $tournament,
            'rounds' => $rounds,
            // 'canonicalUrl' => $request->url(),
            'randomRoom' => RoomController::getRandomRoom(),
            'roomCode' => '',
            'cdnUrl' => url(''),
        ]);
    }

    // 1. Giao diện Tạo mới
    public function create(Request $request)
    {
        $this->checkAuth();

        return view('tournaments.create', [
            'headTitle' => $request->route('headTitle'),
            'bodyClass' => 'dashboard',
            // 'canonicalUrl' => $request->url(),
            'randomRoom' => RoomController::getRandomRoom(),
            'roomCode' => '',
            'cdnUrl' => url(''),
        ]);
    }

    // 2. Xử lý lưu Tạo mới
    public function store(Request $request)
    {
        $this->checkAuth();

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
            'start_date' => 'required|date',
            'status' => 'required|in:open,in_progress,completed',
            'max_players' => 'required|integer|min:2',
        ]);

        $data = $request->all();

        // INTEGRATE USER_ID: Assign the currently authenticated user as the creator
        $data['user_id'] = auth()->id();

        if ($request->hasFile('cover_photo')) {
            $data['cover_photo'] = $request->file('cover_photo')->store('tournaments', 'public');
        }

        Tournament::create($data);

        return redirect()->route($this->getRouteName('tournaments.index'))->with('success', __('Tạo giải đấu thành công!'));
    }

    // 3. Giao diện Sửa
    public function edit(Request $request, $slug)
    {
        $this->checkAuth();
        $tournament = Tournament::where('slug', $slug)->firstOrFail();

        // Double-check ownership
        $this->authorizeCreator($tournament);

        return view('tournaments.edit', [
            'headTitle' => $request->route('headTitle') . ': ' . $tournament->name,
            'bodyClass' => 'dashboard',
            'tournament' => $tournament,
            // 'canonicalUrl' => $request->url(),
            'randomRoom' => RoomController::getRandomRoom(),
            'roomCode' => '',
            'cdnUrl' => url(''),
        ]);
    }

    // 4. Xử lý cập nhật Sửa
    public function update(Request $request, $slug)
    {
        $this->checkAuth();
        $tournament = Tournament::where('slug', $slug)->firstOrFail();

        // Double-check ownership
        $this->authorizeCreator($tournament);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
            'start_date' => 'required|date',
            'status' => 'required|in:open,in_progress,completed',
            'max_players' => 'required|integer|min:2',
        ]);

        $data = $request->all();

        if ($request->hasFile('cover_photo')) {
            if ($tournament->cover_photo) {
                Storage::disk('public')->delete($tournament->cover_photo);
            }
            $data['cover_photo'] = $request->file('cover_photo')->store('tournaments', 'public');
        }

        $tournament->update($data);

        return redirect()->route($this->getRouteName('tournaments.show'), $tournament->slug)->with('success', __('Cập nhật giải đấu thành công!'));
    }

    // 5. Xử lý Xóa
    public function destroy($slug)
    {
        $this->checkAuth();
        $tournament = Tournament::where('slug', $slug)->firstOrFail();

        // Double-check ownership
        $this->authorizeCreator($tournament);

        if ($tournament->cover_photo) {
            Storage::disk('public')->delete($tournament->cover_photo);
        }

        $tournament->delete();

        return redirect()->route($this->getRouteName('tournaments.index'))->with('success', __('Đã xóa giải đấu!'));
    }
}
