<?php

namespace App\Actions\Puzzle;

use App\Models\Puzzle;

class GetPuzzleRankAction
{
    public function execute(int $id): ?int
    {
        $puzzle = Puzzle::find($id);
        if (!$puzzle) {
            return null;
        }

        return Puzzle::public()
                ->where('likes_count', '>', $puzzle->likes_count)
                ->count() + 1;
    }
}
