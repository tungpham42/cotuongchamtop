<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Actions\Room\GetRandomRoomAction;

class ArticleController extends Controller
{
    /**
     * Hiển thị danh sách bài viết (Có hỗ trợ tìm kiếm và phân trang)
     */
    public function index(Request $request): View
    {
        $locale = app()->getLocale();
        $search = $request->input('query');

        // Query danh sách bài viết
        $articles = Article::query()
            // ->where('status', 'published') // Bỏ comment nếu bảng articles của bạn có cột status
            ->when($search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->latest('created_at')
            ->paginate(12)
            ->withQueryString(); // Giữ lại query string khi chuyển trang

        $data = [
            'headTitle' => __('Danh sách bài viết'),
            'bodyClass' => 'article-list',
            'articles' => $articles,
            'search' => $search,

            // Inject các biến dùng chung cho master layout (kế thừa từ logic của web.php hiện tại)
            'randomRoom' => app(GetRandomRoomAction::class)->execute(),
            'roomCode' => '',
            'cdnUrl' => url(''),
        ];

        return view('articles.index', localized_page_data('article.index', $locale, $data));
    }

    /**
     * Hiển thị chi tiết bài viết dựa vào slug
     */
    public function show(string $slug): View
    {
        $locale = app()->getLocale();

        // Lấy bài viết, throw 404 nếu không tìm thấy
        $article = Article::query()
            ->where('slug', $slug)
            // ->where('status', 'published')
            ->firstOrFail();

        // Tăng lượt view (Nếu site traffic lớn, cân nhắc chuyển đoạn này vào Queue/Job để tránh lock DB)
        $article->increment('views');

        $data = [
            'headTitle' => $article->title ?? __('Chi tiết bài viết'),
            'bodyClass' => 'article-detail',
            'article' => $article,

            // Bắt buộc phải có để main layout không bị lỗi undefined variable
            'randomRoom' => app(GetRandomRoomAction::class)->execute(),
            'roomCode' => '',
            'cdnUrl' => url(''),
        ];

        return view('articles.show', localized_page_data('article.show', $locale, $data, ['slug' => $slug]));
    }
}
