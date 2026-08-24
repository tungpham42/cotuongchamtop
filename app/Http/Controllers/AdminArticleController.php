<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminArticleController extends Controller
{
    // Cấu hình các ngôn ngữ hỗ trợ
    protected $supportedLocales = ['vi', 'en', 'ja', 'ko', 'zh'];

    // Ngôn ngữ mặc định — bắt buộc phải có tiêu đề. Đổi giá trị này ở một
    // chỗ duy nhất thay vì sửa chuỗi 'vi' rải rác trong các rule validate.
    protected $defaultLocale = 'vi';

    /**
     * Danh sách bài viết
     */
    public function index()
    {
        $articles = Article::latest()->paginate(15);
        return view('admin.articles.index', compact('articles'));
    }

    /**
     * Giao diện thêm mới
     */
    public function create()
    {
        $locales = $this->supportedLocales;
        return view('admin.articles.create', compact('locales'));
    }

    /**
     * Xử lý lưu bài viết mới
     */
    public function store(Request $request)
    {
        $request->validate($this->translationRules(), $this->translationMessages());

        DB::beginTransaction();
        try {
            // 1. Tạo bài viết gốc
            $article = Article::create([
                'author_id' => auth()->id(),
                'status' => $request->status,
                'views' => 0,
            ]);

            // 2. Lưu các bản dịch
            foreach ($request->translations as $locale => $data) {
                // Chỉ lưu nếu người dùng có nhập tiêu đề
                if (!empty($data['title'])) {
                    $article->translations()->create([
                        'locale'  => $locale,
                        'title'   => $data['title'],
                        'slug'    => $this->resolveSlug($data['slug'] ?? null, $data['title'], $locale),
                        'content' => $data['content'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.articles.index')->with('success', 'Thêm bài viết thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Giao diện chỉnh sửa
     */
    public function edit(Article $article)
    {
        // Load sẵn các bản dịch để điền vào form
        $article->load('translations');
        $locales = $this->supportedLocales;

        return view('admin.articles.edit', compact('article', 'locales'));
    }

    /**
     * Xử lý cập nhật
     */
    public function update(Request $request, Article $article)
    {
        $request->validate($this->translationRules(), $this->translationMessages());

        DB::beginTransaction();
        try {
            // 1. Cập nhật bảng chính
            $article->update([
                'status' => $request->status,
            ]);

            // 2. Cập nhật hoặc tạo mới bản dịch (updateOrCreate)
            foreach ($request->translations as $locale => $data) {
                if (!empty($data['title'])) {
                    $article->translations()->updateOrCreate(
                        ['locale' => $locale], // Điều kiện tìm kiếm
                        [                      // Dữ liệu cần cập nhật/thêm mới
                            'title'   => $data['title'],
                            'slug'    => $this->resolveSlug($data['slug'] ?? null, $data['title'], $locale),
                            'content' => $data['content'] ?? null,
                        ]
                    );
                } else {
                    // Nếu title rỗng, xoá bản dịch của ngôn ngữ đó (nếu đã từng tồn tại)
                    $article->translations()->where('locale', $locale)->delete();
                }
            }

            DB::commit();
            return redirect()->route('admin.articles.index')->with('success', 'Cập nhật bài viết thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý xoá bài viết
     */
    public function destroy(Article $article)
    {
        // CascadeOnDelete trong migration sẽ tự động xoá dữ liệu bảng article_translations
        $article->delete();

        return redirect()->route('admin.articles.index')->with('success', 'Đã xoá bài viết thành công.');
    }

    /**
     * Validation rules dùng chung cho store() và update(), lấy ngôn ngữ mặc
     * định từ $this->defaultLocale thay vì hardcode 'vi' ở nhiều nơi — vừa
     * tránh lệch giá trị nếu $defaultLocale đổi, vừa validate đúng khi
     * $defaultLocale không nằm trong $supportedLocales.
     */
    protected function translationRules(): array
    {
        return [
            'status' => 'required|in:published,draft',
            'translations' => 'required|array',
            "translations.{$this->defaultLocale}.title" => 'required|string|max:255',
        ];
    }

    protected function translationMessages(): array
    {
        return [
            "translations.{$this->defaultLocale}.title.required" =>
                'Tiêu đề ngôn ngữ mặc định (' . strtoupper($this->defaultLocale) . ') là bắt buộc.',
        ];
    }

    /**
     * Quyết định slug cuối cùng cho một bản dịch: dùng slug người dùng nhập
     * nếu có, ngược lại tự sinh từ tiêu đề. Luôn đi qua makeSlug() để xử lý
     * đúng các ngôn ngữ không dùng chữ Latin (ja/ko/zh).
     */
    protected function resolveSlug(?string $manualSlug, string $title, string $locale): string
    {
        $source = !empty($manualSlug) ? $manualSlug : $title;

        return $this->makeSlug($source, $locale);
    }

    /**
     * Str::slug() chỉ bỏ dấu cho chữ Latin — bảng ASCII map dựng sẵn của
     * Laravel không có ký tự Hán/Nhật/Hàn, nên với tiêu đề tiếng Nhật, Hàn,
     * Trung, toàn bộ ký tự bị loại bỏ và slug ra chuỗi rỗng (rồi lưu rỗng
     * hoặc vỡ ràng buộc unique).
     *
     * Cách xử lý: nếu có ext-intl, phiên âm sang Latin trước (Hán → Pinyin,
     * Nhật → romaji gần đúng, Hàn → Latin hoá Hangul) rồi mới slug hoá bình
     * thường. Nếu không có ext-intl, hoặc kết quả phiên âm vẫn rỗng, dùng
     * slug ngẫu nhiên có tiền tố locale để không bao giờ lưu slug rỗng.
     */
    protected function makeSlug(string $source, string $locale): string
    {
        if (function_exists('transliterator_transliterate')) {
            $latin = @transliterator_transliterate('Any-Latin; Latin-ASCII', $source);
            if (!empty($latin)) {
                $source = $latin;
            }
        }

        $slug = Str::slug($source);

        if (empty($slug)) {
            $slug = $locale . '-' . Str::random(8);
        }

        return $slug;
    }
}
