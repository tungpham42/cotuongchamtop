<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    // Cấu hình các ngôn ngữ hỗ trợ
    protected $supportedLocales = ['vi', 'en', 'ja', 'ko', 'zh'];

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
        $request->validate([
            'status' => 'required|in:published,draft',
            'translations' => 'required|array',
            // Ràng buộc ít nhất ngôn ngữ mặc định (VD: vi) phải có tiêu đề
            'translations.vi.title' => 'required|string|max:255',
        ], [
            'translations.vi.title.required' => 'Tiêu đề tiếng Việt là bắt buộc.'
        ]);

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
                        // Tự động tạo slug nếu để trống
                        'slug'    => !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['title']),
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
        $request->validate([
            'status' => 'required|in:published,draft',
            'translations' => 'required|array',
            'translations.vi.title' => 'required|string|max:255',
        ]);

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
                            'slug'    => !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['title']),
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
}
