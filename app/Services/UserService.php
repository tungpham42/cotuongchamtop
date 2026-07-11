<?php

namespace App\Services;

use App\Models\User;
use App\Models\Session as DbSession;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class UserService
{
    public function getPlayers(int $perPage = 12): LengthAwarePaginator
    {
        $activeUserIds = DbSession::whereNotNull('user_id')->pluck('user_id')->unique();

        return User::select('id', 'name', 'email', 'elo', 'points', 'last_seen_at', 'created_at', 'updated_at')
            ->whereIn('id', $activeUserIds)
            ->orderBy('elo', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getFirstPagePlayers(int $perPage = 12): LengthAwarePaginator
    {
        $activeUserIds = DbSession::whereNotNull('user_id')->pluck('user_id')->unique();

        return User::select('id', 'name', 'email', 'elo', 'points', 'last_seen_at', 'created_at', 'updated_at')
            ->whereIn('id', $activeUserIds)
            ->orderBy('elo', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', 1);
    }

    public function getOnlinePlayersCount(): int
    {
        return Cache::remember('usersOnline', 60, function () {
            return DbSession::whereNotNull('user_id')->pluck('user_id')->unique()->count();
        });
    }

    public function getUsers(int $perPage = 10): LengthAwarePaginator
    {
        return User::select('id', 'email', 'name', 'elo', 'last_seen_at', 'created_at')
            ->orderBy('elo', 'desc')
            ->paginate($perPage);
    }

    public function updatePlayerStatus(int $id): void
    {
        User::updateOrInsert(
            ['id' => $id],
            ['last_seen_at' => Carbon::now()]
        );
    }

    public function isOnline(int $id): bool
    {
        return DbSession::where('user_id', $id)->exists();
    }
}
