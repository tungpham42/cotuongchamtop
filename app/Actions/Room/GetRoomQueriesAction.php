<?php

namespace App\Actions\Room;

use App\Models\Room;

class GetRoomQueriesAction
{
    /**
     * Get the name of a room by its code.
     */
    public function getRoomName(string $code): ?string
    {
        return Room::where('code', $code)->value('name');
    }

    /**
     * Get the host ID of a room by its code.
     */
    public function getHostId(string $code): ?int
    {
        return Room::where('code', $code)->value('host_id');
    }

    /**
     * Get both host and guest IDs of a room by its code.
     */
    public function getRoomIds(string $code): array
    {
        $roomData = Room::select('host_id', 'guest_id')->where('code', $code)->first();
        return $roomData ? $roomData->toArray() : [];
    }

    /**
     * Check if a room code exists.
     */
    public function hasRoomCode(string $code): bool
    {
        return Room::where('code', $code)->exists();
    }

    /**
     * Get rooms that have a host, ordered by recent modifications.
     */
    public function getMatchRooms()
    {
        return Room::whereNotNull('host_id')
            ->orderBy('modified_at', 'desc')
            ->paginate(10);
    }

    /**
     * Get currently active/playing rooms (no result yet).
     */
    public function getPlayingRooms()
    {
        return Room::whereNotNull('host_id')
            ->whereNull('result')
            ->orderBy('modified_at', 'desc')
            ->paginate(10);
    }

    /**
     * Get finished rooms (have a result).
     */
    public function getPlayedRooms()
    {
        return Room::whereNotNull('host_id')
            ->whereNotNull('result')
            ->orderBy('modified_at', 'desc')
            ->paginate(10);
    }

    /**
     * Get all rooms for a specific player (host or guest).
     */
    public function getPlayerRooms(int $id)
    {
        return Room::where('host_id', $id)
            ->orWhere('guest_id', $id)
            ->orderBy('modified_at', 'desc')
            ->paginate(10);
    }

    /**
     * Get active boards for display (paginated by 6).
     */
    public function getBoards()
    {
        return Room::whereNotNull('host_id')
            ->whereNull('result')
            ->orderBy('modified_at', 'desc')
            ->paginate(6);
    }

    /**
     * Get the first page of active boards.
     */
    public function getFirstPageBoards()
    {
        return Room::whereNotNull('host_id')
            ->whereNull('result')
            ->orderBy('modified_at', 'desc')
            ->paginate(6, ['*'], 'page', 1);
    }

    /**
     * Get played boards for display (paginated by 6).
     */
    public function getPlayedBoards()
    {
        return Room::whereNotNull('host_id')
            ->whereNotNull('result')
            ->orderBy('modified_at', 'desc')
            ->paginate(6);
    }

    /**
     * Get the first page of played boards.
     */
    public function getFirstPagePlayedBoards()
    {
        return Room::whereNotNull('host_id')
            ->whereNotNull('result')
            ->orderBy('modified_at', 'desc')
            ->paginate(6, ['*'], 'page', 1);
    }
}
