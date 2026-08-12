<?php

namespace App\Actions\User;

use App\Models\User;

class ChangeNameAction
{
    public function execute(User $user, string $newName): void
    {
        $user->name = $newName;
        $user->save();
    }
}
