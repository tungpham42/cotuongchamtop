<?php

namespace App\Services;

use App\Contracts\BracketGeneratorInterface;
use App\Models\Tournament;
use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;

class SingleEliminationBracketGenerator implements BracketGeneratorInterface
{
    public function generate(Tournament $tournament, Collection $players): void
    {
        $totalPlayers = $players->count();
        if ($totalPlayers < 2) return; // Need at least 2 players

        $totalRounds = max(1, (int) log($totalPlayers, 2)); // Calculate rounds based on powers of 2[cite: 1]
        $previousRoundRooms = [];

        for ($round = $totalRounds; $round >= 1; $round--) {
            $matchesInRound = $totalPlayers / pow(2, $round);
            $currentRoundRooms = [];

            for ($i = 0; $i < $matchesInRound; $i++) {
                $room = Room::create([
                    'code' => md5(time() . uniqid()), // Generate unique room code[cite: 1]
                    'fen' => Room::INITIAL_FEN,
                    'tournament_id' => $tournament->id,
                    'tournament_round' => $round,
                    'red_time' => 600,
                    'black_time' => 600,
                    'active_player' => 'waiting',
                    'modified_at' => now(),
                    'next_room_code' => $round == $totalRounds ? null : $previousRoundRooms[floor($i / 2)]->code // Link to next room[cite: 1]
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
}
