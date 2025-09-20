<?php

namespace App\Services;

class EloRatingService
{
    protected $kFactor;

    public function __construct($kFactor = 32)
    {
        $this->kFactor = $kFactor; // Default K-Factor
    }

    public function calculateNewRatings($player1Rating, $player2Rating, $player1Score)
    {
        // Player 1: 1 = win, 0 = loss, 0.5 = draw
        $player1Expected = $this->expectedScore($player1Rating, $player2Rating);
        $player2Expected = $this->expectedScore($player2Rating, $player1Rating);

        $newPlayer1Rating = $this->calculateNewRating($player1Rating, $player1Score, $player1Expected);
        $newPlayer2Rating = $this->calculateNewRating($player2Rating, 1 - $player1Score, $player2Expected);

        return [
            'player1' => $newPlayer1Rating,
            'player2' => $newPlayer2Rating,
        ];
    }

    protected function expectedScore($ratingA, $ratingB)
    {
        return 1 / (1 + pow(10, ($ratingB - $ratingA) / 400));
    }

    protected function calculateNewRating($currentRating, $actualScore, $expectedScore)
    {
        return $currentRating + $this->kFactor * ($actualScore - $expectedScore);
    }
}
