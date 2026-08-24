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
        $defaultLocale = config('locales.default', 'vi');

        // Query danh sách bài viết
        $articles = Article::query()
            // ->where('status', 'published') // Bỏ comment nếu bảng articles của bạn có cột status
            ->when($search, function ($query) use ($search, $locale, $defaultLocale) {
                // 'title' không nằm trên bảng articles mà nằm trên article_translations,
                // nên phải tìm qua quan hệ translations() thay vì where('title', ...) trực tiếp.
                // Tìm ở locale hiện tại HOẶC locale mặc định để không bỏ sót bài viết
                // chưa có bản dịch cho ngôn ngữ đang xem.
                $query->whereHas('translations', function ($q) use ($search, $locale, $defaultLocale) {
                    $q->whereIn('locale', array_unique([$locale, $defaultLocale]))
                      ->where('title', 'like', "%{$search}%");
                });
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
        $defaultLocale = config('locales.default', 'vi');
        $supportedLocales = config('locales.supported', [$defaultLocale]);

        // 'slug' không nằm trên bảng articles mà nằm trên article_translations,
        // nên phải tìm qua quan hệ translations() thay vì where('slug', ...) trực tiếp
        // (cách cũ sẽ vỡ vì cột 'slug' không tồn tại trên bảng articles).
        $article = Article::query()
            // ->where('status', 'published')
            ->whereHas('translations', function ($q) use ($slug, $locale, $defaultLocale) {
                $q->whereIn('locale', array_unique([$locale, $defaultLocale]))
                  ->where('slug', $slug);
            })
            // Cần load toàn bộ bản dịch (không chỉ bản dịch của locale hiện tại)
            // để build link chuyển ngôn ngữ / hreflang trỏ đúng slug của từng locale.
            ->with('translations')
            ->firstOrFail();

        // Tăng lượt view (Nếu site traffic lớn, cân nhắc chuyển đoạn này vào Queue/Job để tránh lock DB)
        $article->increment('views');

        // Mỗi locale có slug riêng (ArticleTranslation::slug). Build map slug
        // theo từng locale để link đổi ngôn ngữ / thẻ hreflang trỏ đúng bài viết
        // ở locale đó, thay vì dùng lại slug của locale hiện tại (dễ vỡ 404 vì
        // slug đó chưa chắc tồn tại ở locale khác).
        $slugsByLocale = $article->slugsByLocale();
        $parametersByLocale = [];
        foreach ($supportedLocales as $loc) {
            $parametersByLocale[$loc] = [
                'slug' => $slugsByLocale[$loc] ?? $slugsByLocale[$defaultLocale] ?? $slug,
            ];
        }

        $data = [
            'headTitle' => $article->title ?? __('Chi tiết bài viết'),
            'bodyClass' => 'article-detail',
            'article' => $article,

            // Bắt buộc phải có để main layout không bị lỗi undefined variable
            'randomRoom' => app(GetRandomRoomAction::class)->execute(),
            'roomCode' => '',
            'cdnUrl' => url(''),
        ];

        return view('articles.show', localized_page_data(
            'article.show',
            $locale,
            $data,
            [],
            $parametersByLocale
        ));
    }
}
