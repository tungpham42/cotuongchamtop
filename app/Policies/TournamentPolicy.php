<?php

namespace App\Policies;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TournamentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        // Replaces checkAuth() - if they reach here, they are authenticated.
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tournament $tournament)
    {
        // Replaces authorizeCreator()
        return $user->id === $tournament->user_id || $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tournament $tournament)
    {
        // Replaces authorizeCreator() for deletion
        return $user->id === $tournament->user_id || $user->is_admin;
    }
}
