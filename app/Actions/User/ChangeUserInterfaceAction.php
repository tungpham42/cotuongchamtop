<?php

namespace App\Actions\User;

use App\Models\User;

class ChangeUserInterfaceAction
{
    public function execute(User $user, string $boardTheme, string $piecesTheme): void
    {
        $user->board_theme = $boardTheme;
        $user->pieces_theme = $piecesTheme;
        $user->save();
    }
}
