<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ChangePasswordAction
{
    /**
     * @throws ValidationException when the current password doesn't match.
     */
    public function execute(User $user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('Mật khẩu hiện tại không khớp.'),
            ]);
        }

        $user->password = Hash::make($newPassword);
        $user->save();
    }
}
