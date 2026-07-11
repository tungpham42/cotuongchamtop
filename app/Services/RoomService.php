<?php

namespace App\Services;

use App\Models\Room;
use App\Models\User;
use App\Http\Controllers\GameController;
use Illuminate\Pagination\LengthAwarePaginator;

class RoomService
{
    public function getBoards(int $perPage = 6): LengthAwarePaginator
    {
        return Room::whereNotNull('host_id')
            ->whereNull('result')
            ->orderBy('modified_at', 'desc')
            ->paginate($perPage);
    }

    public function getFirstPageBoards(int $perPage = 6): LengthAwarePaginator
    {
        return Room::whereNotNull('host_id')
            ->whereNull('result')
            ->orderBy('modified_at', 'desc')
            ->paginate($perPage, ['*'], 'page', 1);
    }

    public function getPlayedBoards(int $perPage = 6): LengthAwarePaginator
    {
        return Room::whereNotNull('host_id')
            ->whereNotNull('result')
            ->orderBy('modified_at', 'desc')
            ->paginate($perPage);
    }

    public function getFirstPagePlayedBoards(int $perPage = 6): LengthAwarePaginator
    {
        return Room::whereNotNull('host_id')
            ->whereNotNull('result')
            ->orderBy('modified_at', 'desc')
            ->paginate($perPage, ['*'], 'page', 1);
    }

    public function getMatchRooms(int $perPage = 10): LengthAwarePaginator
    {
        return Room::whereNotNull('host_id')->orderBy('modified_at', 'desc')->paginate($perPage);
    }

    public function getPlayingRooms(int $perPage = 10): LengthAwarePaginator
    {
        return Room::whereNotNull('host_id')->whereNull('result')->orderBy('modified_at', 'desc')->paginate($perPage);
    }

    public function getPlayedRooms(int $perPage = 10): LengthAwarePaginator
    {
        return Room::whereNotNull('host_id')->whereNotNull('result')->orderBy('modified_at', 'desc')->paginate($perPage);
    }

    public function getPlayerRooms(int $id, int $perPage = 10): LengthAwarePaginator
    {
        return Room::where('host_id', $id)->orWhere('guest_id', $id)->orderBy('modified_at', 'desc')->paginate($perPage);
    }

    public function updateRoomScores(int $id): void
    {
        $hostWin = Room::where('host_id', $id)->where('result', '1')->count();
        $guestWin = Room::where('guest_id', $id)->where('result', '-1')->count();
        $hostDraw = Room::where('host_id', $id)->where('result', '0')->count();
        $guestDraw = Room::where('guest_id', $id)->where('result', '0')->count();

        Room::updateOrInsert(['id' => $id], ['host_score' => $hostWin + 0.5 * $hostDraw]);
        Room::updateOrInsert(['id' => $id], ['guest_score' => $guestWin + 0.5 * $guestDraw]);
    }
}
