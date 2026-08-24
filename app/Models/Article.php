<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Traits\Translatable;

class Article extends Model
{
    use Translatable;

    protected $fillable = ['author_id', 'status', 'views', 'featured_image'];

    // Khai báo các trường được dịch để Trait có thể bắt qua Magic Method (__get)
    protected $translatedAttributes = ['title', 'slug', 'content'];

    // Eager load mặc định để tránh lỗi N+1 query khi lặp danh sách
    protected $with = ['translation'];

    /**
     * Map of locale => slug, built from the (plural) `translations` relation.
     *
     * Note this is a *different* relation from the default-eager-loaded
     * `translation` (singular, current-locale) above — callers must
     * `->with('translations')` explicitly before using this, otherwise it
     * triggers an N+1 query per call.
     */
    public function slugsByLocale(): array
    {
        return $this->translations->pluck('slug', 'locale')->toArray();
    }

    /**
     * URL công khai của ảnh đại diện (null nếu bài viết chưa có ảnh).
     * Truy cập qua $article->featured_image_url — tránh việc mỗi view phải
     * tự gọi Storage::url() và biết disk nào đang được dùng.
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->featured_image
            ? Storage::disk('public')->url($this->featured_image)
            : null;
    }
}
