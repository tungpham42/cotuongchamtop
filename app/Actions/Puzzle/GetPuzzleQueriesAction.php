<?php

namespace App\Actions\Puzzle;

use App\Models\Puzzle;

class GetPuzzleQueriesAction
{
    public function getFen(string $slug): ?string
    {
        return optional($this->findBySlug($slug))->fen;
    }

    public function findBySlug(string $slug): ?Puzzle
    {
        return Puzzle::where('slug', $slug)->first();
    }

    public function getNameByFen(string $fen): ?string
    {
        $fenBase = trim(explode(' ', $fen)[0]);
        $puzzle = Puzzle::where('fen', $fenBase)->first() ?? Puzzle::where('fen', $fen)->first();

        return $puzzle ? $puzzle->name : null;
    }

    public function getSitemapPuzzles()
    {
        return Puzzle::public()
            ->select('name', 'slug', 'fen', 'rating', 'likes_count', 'hard_count', 'unsolved_count', 'description', 'updated_at')
            ->orderByDesc('updated_at')
            ->paginate(4096);
    }
}
