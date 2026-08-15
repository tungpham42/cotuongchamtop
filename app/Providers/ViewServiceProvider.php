<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Room;
use App\Services\PuzzleService;
use App\Services\RoomService;
use App\Services\UserService;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(
        PuzzleService $puzzleService,
        RoomService $roomService,
        UserService $userService
    ) {
        $viewsWithSharedData = [
            'layouts.partials.userPuzzles',
            'room*',
            'puzzle',
            'puzzleAi',
            'puzzleList',
            'puzzleCompete',
            'puzzleRating',
            'board',
            'boardAi',
            'ai',
            'human',
            'userList',
            'tournaments.*',
            'app.*',
        ];

        View::composer($viewsWithSharedData, function ($view) use ($puzzleService, $roomService, $userService) {
            $view->with([
                'userPuzzles' => $puzzleService->getUserPuzzles(),
                'firstUserPuzzles' => $puzzleService->getFirstUserPuzzles(),
                'boards' => $roomService->getBoards(),
                'firstPageBoards' => $roomService->getFirstPageBoards(),
                'playedBoards' => $roomService->getPlayedBoards(),
                'firstPagePlayedBoards' => $roomService->getFirstPagePlayedBoards(),
                'players' => $userService->getPlayers(),
                'firstPagePlayers' => $userService->getFirstPagePlayers(),
                'initialFen' => Room::INITIAL_FEN,
            ]);
        });
    }
}
