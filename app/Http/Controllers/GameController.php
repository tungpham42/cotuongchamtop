<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Actions\Room\GetRandomRoomAction;

class GameController extends Controller
{
    /**
     * Thư viện ván cờ công khai (tìm kiếm + sắp xếp + phân trang).
     * Việc thêm/sửa/xoá ván cờ vẫn thuộc AdminGameController — controller
     * này chỉ phục vụ trang xem công khai, giống cách ArticleController
     * tách khỏi AdminArticleController.
     */
    public function library(Request $request): View
    {
        $locale = app()->getLocale();
        $search = $request->input('search');

        $query = Game::with('user');

        if ($search) {
            $query->where(function ($q) use ($search) {
                // Tìm kiếm theo tiêu đề hoặc mô tả
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  // Tìm kiếm theo tên người dùng (kỳ thủ)
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $sort = $request->input('sort', 'latest');

        match ($sort) {
            'oldest'     => $query->oldest(),
            'views_desc' => $query->orderBy('views', 'desc'),
            'views_asc'  => $query->orderBy('views', 'asc'),
            'alpha_asc'  => $query->orderBy('title', 'asc'),
            'alpha_desc' => $query->orderBy('title', 'desc'),
            default      => $query->latest(), // 'latest' là mặc định
        };

        // withQueryString() giúp giữ lại các tham số 'search' và 'sort' khi chuyển trang
        $games = $query->paginate(12)->withQueryString();

        $data = [
            'headTitle' => __('Thư viện ván cờ'),
            'bodyClass' => 'games-list library',
            'games' => $games,
            'search' => $search,
            'sort' => $sort,

            // Inject các biến dùng chung cho master layout (kế thừa từ logic của web.php hiện tại)
            'randomRoom' => app(GetRandomRoomAction::class)->execute(),
            'roomCode' => '',
            'cdnUrl' => url(''),
        ];

        return view('games.library', localized_page_data('games.library', $locale, $data));
    }

    /**
     * Hiển thị chi tiết một ván cờ dựa vào slug.
     */
    public function show(string $slug): View
    {
        $locale = app()->getLocale();

        // Khác với Article (slug nằm trên bảng translations), Game có cột
        // 'slug' trực tiếp và không dịch theo locale, nên không cần
        // $parametersByLocale / slugsByLocale() như ArticleController::show —
        // slug này dùng chung cho mọi locale.
        $gameModel = Game::with('user')
            ->where('slug', $slug)
            ->firstOrFail();

        // Tăng lượt view (nếu site traffic lớn, cân nhắc chuyển đoạn này vào
        // Queue/Job để tránh lock DB — cùng lưu ý như ArticleController::show)
        $gameModel->increment('views');

        $data = [
            'headTitle' => $gameModel->title ?? __('Chi tiết ván cờ'),
            'bodyClass' => 'game-detail library',
            'game' => $gameModel,

            // Bắt buộc phải có để main layout không bị lỗi undefined variable
            'randomRoom' => app(GetRandomRoomAction::class)->execute(),
            'roomCode' => '',
            'cdnUrl' => url(''),
        ];

        return view('games.show', localized_page_data(
            'games.show',
            $locale,
            $data,
            ['slug' => $gameModel->slug]
        ));
    }
}
