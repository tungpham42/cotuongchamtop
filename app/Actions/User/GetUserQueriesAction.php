<?php

namespace App\Actions\User;

use App\Models\User;

class GetUserQueriesAction
{
    public function getUserName($id)
    {
        $user = User::find($id);

        if ($user) {
            return $user->name;
        }

        return null;
    }

    public function getUserEmail($id)
    {
        $user = User::find($id);

        if ($user) {
            return $user->email;
        }

        return null;
    }

    /**
     * Get paginated users ordered by Elo.
     */
    public function getUsers()
    {
        return User::select('id', 'email', 'name', 'elo', 'last_seen_at', 'created_at')
                ->orderBy('elo', 'desc')
                ->paginate(10);
    }

    /**
     * Get top 10 match users ordered by Elo.
     */
    public function getMatchUsers()
    {
        return User::select('id', 'email', 'name', 'elo', 'last_seen_at', 'created_at')
                ->orderBy('elo', 'desc')
                ->limit(10)
                ->get();
    }

    /**
     * Get all user IDs for ranking.
     */
    public function getRankUsers()
    {
        return User::select('id')->get();
    }
}
