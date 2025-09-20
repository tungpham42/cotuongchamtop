<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Room;
use EloRating\Player;
use EloRating\Game;

class GameController extends Controller
{
    public static function getEloRatings($player1Elo, $player2Elo, $result)
    {
        $player1 = new Player($player1Elo);
        $player2 = new Player($player2Elo);

        $match = new Game($player1, $player2);
        $match->setK(20);

        $scoreMapping = [
            1 => [1, 0],
            -1 => [0, 1],
            0 => [0.5, 0.5]
        ];

        $scores = $scoreMapping[$result] ?? [0.5, 0.5];
        $match->setScore(...$scores)->count();

        return [
            $player1->getRating(),
            $player2->getRating()
        ];
    }
}
