<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\PuzzleController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserController;

class ViewServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Define an array of all the views that need this shared data.
        // Alternatively, use '*' to attach it to EVERY view, though specifying is usually better for performance.
        $viewsWithSharedData = [
            'layout.partials.userPuzzles',
            'room*', // You can use wildcards if your view names share a prefix
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
            'tournaments.*'
        ];

        View::composer($viewsWithSharedData, function ($view) {
            $view->with([
                'userPuzzles' => PuzzleController::getUserPuzzles(),
                'firstUserPuzzles' => PuzzleController::getFirstUserPuzzles(),
                'boards' => RoomController::getBoards(),
                'firstPageBoards' => RoomController::getFirstPageBoards(),
                'playedBoards' => RoomController::getPlayedBoards(),
                'firstPagePlayedBoards' => RoomController::getFirstPagePlayedBoards(),
                'players' => UserController::getPlayers(),
                'firstPagePlayers' => UserController::getFirstPagePlayers(),
            ]);
        });
    }
}
