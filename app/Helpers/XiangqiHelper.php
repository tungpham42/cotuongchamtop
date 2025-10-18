<?php

namespace App\Helpers;

class XiangqiHelper
{
    public const STANDARD_START_FEN = 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1';

    public static function validateFen(string $fen): bool
    {
        if (empty($fen)) {
            return false;
        }

        $parts = explode(' ', $fen);

        // Basic FEN validation for Xiangqi
        if (count($parts) < 4) {
            return false;
        }

        // Check board position part
        $rows = explode('/', $parts[0]);
        if (count($rows) !== 10) {
            return false;
        }

        // Validate each row
        foreach ($rows as $row) {
            $sum = 0;
            for ($i = 0; $i < strlen($row); $i++) {
                $char = $row[$i];
                if (ctype_digit($char)) {
                    $sum += (int)$char;
                } elseif (in_array($char, ['r', 'n', 'b', 'a', 'k', 'c', 'p', 'R', 'N', 'B', 'A', 'K', 'C', 'P'])) {
                    $sum += 1;
                } else {
                    return false; // Invalid character
                }
            }
            if ($sum !== 9) {
                return false; // Row must sum to 9
            }
        }

        // Validate active color
        if (!in_array($parts[1], ['r', 'b'])) {
            return false;
        }

        return true;
    }

    public static function normalizeMove(string $move): string
    {
        // Convert Pikafish move format to standard format
        // Pikafish uses format like "h2e2" or "h0e2" etc.
        $move = trim($move);

        // Remove any unwanted characters
        $move = preg_replace('/[^a-i0-9]/', '', $move);

        // Ensure the move is 4 characters long (e.g., "h2e2")
        if (strlen($move) === 4) {
            return $move;
        }

        return $move;
    }

    public static function getActiveColor(string $fen): string
    {
        $parts = explode(' ', $fen);
        return $parts[1] === 'r' ? 'red' : 'black';
    }

    public static function getActiveColorCode(string $fen): string
    {
        $parts = explode(' ', $fen);
        return $parts[1];
    }

    public static function switchActiveColor(string $fen): string
    {
        $parts = explode(' ', $fen);
        $parts[1] = $parts[1] === 'r' ? 'b' : 'r';
        return implode(' ', $parts);
    }

    public static function getValidPieces(): array
    {
        return ['r', 'n', 'b', 'a', 'k', 'c', 'p', 'R', 'N', 'B', 'A', 'K', 'C', 'P'];
    }

    public static function getPieceName(string $piece): string
    {
        $names = [
            'r' => 'red chariot', 'R' => 'black chariot',
            'n' => 'red horse', 'N' => 'black horse',
            'b' => 'red elephant', 'B' => 'black elephant',
            'a' => 'red advisor', 'A' => 'black advisor',
            'k' => 'red king', 'K' => 'black king',
            'c' => 'red cannon', 'C' => 'black cannon',
            'p' => 'red pawn', 'P' => 'black pawn'
        ];

        return $names[$piece] ?? 'unknown';
    }

    public static function getPieceColor(string $piece): string
    {
        return ctype_lower($piece) ? 'red' : 'black';
    }

    public static function formatMove(string $move): string
    {
        return self::normalizeMove($move);
    }
}
