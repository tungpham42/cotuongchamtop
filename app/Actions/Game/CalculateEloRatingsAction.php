<?php

namespace App\Actions\Game;

use EloRating\Player;
use EloRating\Game;

class CalculateEloRatingsAction
{
    /**
     * Calculate and return the new Elo ratings for two players.
     *
     * @param float|int $player1Elo
     * @param float|int $player2Elo
     * @param int|string $result (1 for P1 win, -1 for P2 win, 0 for draw)
     * @return array [player1NewElo, player2NewElo]
     */
    public function execute($player1Elo, $player2Elo, $result): array
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
