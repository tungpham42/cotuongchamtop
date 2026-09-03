<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Http\Requests\UserGameRequest;

class AdminGameController extends Controller
{
    /**
     * Danh sách TẤT CẢ ván cờ trong khu quản trị.
     * Route nằm trong nhóm 'admin' (middleware ['auth', IsAdmin::class]),
     * nên chỉ admin mới vào được — không cần kiểm tra thêm ở đây, giống
     * cách AdminArticleController/AdminTournamentController đang làm.
     */
    public function index()
    {
        $games = Game::with('user')->latest()->paginate(15);
        return view('admin.games.index', compact('games'));
    }

    public function create()
    {
        $game = new Game([
            'initial_fen' => 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1',
        ]);

        return view('admin.games.form', compact('game'));
    }

    public function store(UserGameRequest $request)
    {
        $data = $request->validated();
        if (!empty($data['moves'])) {
            $data['moves'] = json_decode($data['moves'], true);
        }

        // Ghi nhận admin nào đã tạo ván cờ này (cột user_id giữ nguyên vai
        // trò "tác giả", giống featured_image/author_id bên Article).
        $data['user_id'] = auth()->id();

        Game::create($data);

        return redirect()->route('admin.games.index')
            ->with('success', 'Đã thêm ván cờ thành công!');
    }

    public function edit(Game $game)
    {
        return view('admin.games.form', compact('game'));
    }

    public function update(UserGameRequest $request, Game $game)
    {
        $data = $request->validated();
        if (!empty($data['moves'])) {
            $data['moves'] = json_decode($data['moves'], true);
        }

        $game->update($data);

        return redirect()->route('admin.games.index')
            ->with('success', 'Cập nhật ván cờ thành công!');
    }

    public function destroy(Game $game)
    {
        $game->delete();

        return redirect()->route('admin.games.index')
            ->with('success', 'Đã xóa ván cờ!');
    }
}
