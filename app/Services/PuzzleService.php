<?php

namespace App\Services;

use App\Models\Puzzle;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class PuzzleService
{
    public function getUserPuzzles(int $perPage = 6): LengthAwarePaginator
    {
        return Puzzle::public()
            ->select('name', 'slug', 'fen', 'rating', 'likes_count', 'hard_count', 'unsolved_count', 'description', 'updated_at')
            ->orderByDesc('updated_at')
            ->paginate($perPage);
    }

    public function getFirstUserPuzzles(int $perPage = 6): LengthAwarePaginator
    {
        return Puzzle::public()
            ->select('name', 'slug', 'fen', 'rating', 'likes_count', 'hard_count', 'unsolved_count', 'description', 'updated_at')
            ->orderByDesc('updated_at')
            ->paginate($perPage, ['*'], 'page', 1);
    }

    public function getSitemapPuzzles(): LengthAwarePaginator
    {
        return Puzzle::public()
            ->select('name', 'slug', 'fen', 'rating', 'likes_count', 'hard_count', 'unsolved_count', 'description', 'updated_at')
            ->orderByDesc('updated_at')
            ->paginate(4096);
    }

    public function findBySlug(string $slug): ?Puzzle
    {
        return Puzzle::where('slug', $slug)->first();
    }

    public function getFen(string $slug): ?string
    {
        return optional($this->findBySlug($slug))->fen;
    }

    public function getName(string $slug): ?string
    {
        return optional($this->findBySlug($slug))->name;
    }

    public function getPuzzleRank(int $id): ?int
    {
        $puzzle = Puzzle::find($id);
        if (!$puzzle) {
            return null;
        }

        return Puzzle::public()
                ->where('likes_count', '>', $puzzle->likes_count)
                ->count() + 1;
    }

    public function getNameByFen(string $fen): ?string
    {
        $fenBase = trim(explode(' ', $fen)[0]);
        $puzzle = Puzzle::where('fen', $fenBase)->first() ?? Puzzle::where('fen', $fen)->first();
        return $puzzle ? $puzzle->name : null;
    }
}

