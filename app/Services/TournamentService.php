<?php

namespace App\Services;

use App\Models\Tournament;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class TournamentService
{
    public function joinTournament(Tournament $tournament, int $userId): void
    {
        if (!$tournament->users()->where('user_id', $userId)->exists()) {
            $tournament->users()->attach($userId); // Attach user if not already joined
        }
    }

    public function handleCoverPhotoUpload(?UploadedFile $file, ?string $oldFilePath = null): ?string
    {
        if (!$file) {
            return $oldFilePath;
        }

        if ($oldFilePath) {
            Storage::disk('public')->delete($oldFilePath); // Delete old photo
        }

        return $file->store('tournaments', 'public'); // Store new photo in public/tournaments
    }

    public function clearBracket(Tournament $tournament): void
    {
        $tournament->rooms()->delete(); // Remove associated rooms
    }
}
