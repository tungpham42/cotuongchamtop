<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            // Ảnh đại diện là optional, lưu trên disk 'public' (chạy
            // `php artisan storage:link` nếu chưa link). Path tương đối được
            // lưu vào DB, URL đầy đủ build qua Article::featured_image_url.
            $featuredImagePath = $request->hasFile('featured_image')
                ? $request->file('featured_image')->store('articles', 'public')
                : null;

            // 1. Tạo bài viết gốc
            $article = Article::create([
                'author_id' => auth()->id(),
                'status' => $request->status,
                'views' => 0,
                'featured_image' => $featuredImagePath,
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
            $mainData = ['status' => $request->status];

            if ($request->hasFile('featured_image')) {
                // Có ảnh mới upload: xoá ảnh cũ trước để không rác storage,
                // rồi lưu ảnh mới.
                if ($article->featured_image) {
                    Storage::disk('public')->delete($article->featured_image);
                }
                $mainData['featured_image'] = $request->file('featured_image')->store('articles', 'public');
            } elseif ($request->boolean('remove_featured_image')) {
                // Không upload ảnh mới, nhưng người dùng tick "Xoá ảnh hiện tại"
                if ($article->featured_image) {
                    Storage::disk('public')->delete($article->featured_image);
                }
                $mainData['featured_image'] = null;
            }

            $article->update($mainData);

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
        // Xoá file ảnh đại diện khỏi storage trước, vì xoá record DB không
        // tự động dọn file vật lý.
        if ($article->featured_image) {
            Storage::disk('public')->delete($article->featured_image);
        }

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
            // Ratio 1200/630 chấp nhận mọi kích thước cùng tỉ lệ (vd 1200x630,
            // 2400x1260...), không bắt buộc đúng pixel để không quá khắt khe
            // với ảnh người dùng upload.
            'featured_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096|dimensions:ratio=1200/630',
        ];
    }

    protected function translationMessages(): array
    {
        return [
            "translations.{$this->defaultLocale}.title.required" =>
                'Tiêu đề ngôn ngữ mặc định (' . strtoupper($this->defaultLocale) . ') là bắt buộc.',
            'featured_image.dimensions' => 'Ảnh đại diện phải có tỉ lệ 1200x630 (vd: 1200x630, 1600x840...).',
            'featured_image.image' => 'File tải lên phải là hình ảnh.',
            'featured_image.max' => 'Ảnh đại diện không được vượt quá 4MB.',
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
