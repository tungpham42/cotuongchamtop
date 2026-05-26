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
use Illuminate\Support\Str;
use Carbon\Carbon;

class TournamentController extends Controller
{
    // Register a user for the tournament
    public function join(Request $request, $id)
    {
        $tournament = Tournament::findOrFail($id);
        $userId = auth()->id();

        if ($tournament->status !== 'open') {
            return back()->with('error', 'Registration is closed.');
        }

        if ($tournament->users()->count() >= $tournament->max_players) {
            return back()->with('error', 'Tournament is full.');
        }

        if (!$tournament->users()->where('user_id', $userId)->exists()) {
            $tournament->users()->attach($userId);
        }

        return back()->with('success', 'You have successfully joined the tournament!');
    }

    // Generate Single Elimination Bracket
    public function generateBracket($id)
    {
        $tournament = Tournament::findOrFail($id);

        if ($tournament->status !== 'open') {
            return back()->with('error', 'Tournament has already started.');
        }

        $players = $tournament->users()->inRandomOrder()->get();
        // Note: For simplicity, this assumes a perfect power of 2 (4, 8, 16).
        // You will need bye-logic for uneven player counts.

        $tournament->update(['status' => 'in_progress']);

        // Generate rooms recursively or iteratively to map the bracket tree
        $this->createBracketNodes($tournament, $players);

        return back()->with('success', 'Bracket generated successfully!');
    }

    private function createBracketNodes(Tournament $tournament, $players)
    {
        $totalPlayers = $players->count();
        $totalRounds = log($totalPlayers, 2);

        $previousRoundRooms = [];

        // Loop backwards from Final (Round N) down to Round 1
        for ($round = $totalRounds; $round >= 1; $round--) {
            $matchesInRound = $totalPlayers / pow(2, $round);
            $currentRoundRooms = [];

            for ($i = 0; $i < $matchesInRound; $i++) {
                $room = Room::create([
                    'code' => md5(time() . uniqid()), // Leveraging your existing unique code logic
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
                        'host_id' => $p1->id,
                        'guest_id' => $p2->id,
                        'name' => "{$p1->name} vs {$p2->name}"
                    ]);
                }

                $currentRoundRooms[] = $room;
            }
            $previousRoundRooms = $currentRoundRooms;
        }
    }

    // List all tournaments
    public function index()
    {
        $tournaments = Tournament::withCount('users')->orderBy('start_date', 'desc')->paginate(10);

        return view('tournaments.index', [
            'headTitle' => 'Danh sách Giải đấu',
            'bodyClass' => 'dashboard',
            'tournaments' => $tournaments,

            // Bổ sung thêm dòng này
            'canonicalUrl' => '/giai-dau',

            // Các biến đã thêm từ trước
            'randomRoom' => RoomController::getRandomRoom(),
            'roomCode' => '',
            'cdnUrl' => url(''),
            'userPuzzles' => PuzzleController::getUserPuzzles(),
            'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(),
            'boards' => RoomController::getBoards(),
            'firstPageBoards' => RoomController::getFirstPageBoards(),
            'playedBoards' => RoomController::getPlayedBoards(),
            'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(),
            'players' => UserController::getPlayers(),
            'firstPagePlayers' => UserController::getFirstPagePlayers(),
        ]);
    }

    public function show($id)
    {
        $tournament = Tournament::with(['users', 'rooms.host', 'rooms.guest'])->findOrFail($id);
        $rounds = $tournament->rooms->groupBy('tournament_round')->sortKeys();

        return view('tournaments.show', [
            'headTitle' => 'Giải đấu - ' . $tournament->name,
            'bodyClass' => 'dashboard',
            'tournament' => $tournament,
            'rounds' => $rounds,

            // Bổ sung thêm dòng này (trỏ đích danh vào ID giải đấu)
            'canonicalUrl' => '/giai-dau/' . $tournament->id,

            // Các biến đã thêm từ trước
            'randomRoom' => RoomController::getRandomRoom(),
            'roomCode' => '',
            'cdnUrl' => url(''),
            'userPuzzles' => PuzzleController::getUserPuzzles(),
            'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(),
            'boards' => RoomController::getBoards(),
            'firstPageBoards' => RoomController::getFirstPageBoards(),
            'playedBoards' => RoomController::getPlayedBoards(),
            'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(),
            'players' => UserController::getPlayers(),
            'firstPagePlayers' => UserController::getFirstPagePlayers(),
        ]);
    }
}
