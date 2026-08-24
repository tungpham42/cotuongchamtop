<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait Translatable
{
    // Liên kết đến tất cả bản dịch
    public function translations(): HasMany
    {
        return $this->hasMany($this->getTranslationModelName());
    }

    // Lấy bản dịch theo ngôn ngữ hiện tại
    public function translation(): HasOne
    {
        $locale = app()->getLocale();
        // Fallback về ngôn ngữ mặc định (vi) nếu không tìm thấy
        $defaultLocale = config('locales.default', 'vi');

        return $this->hasOne($this->getTranslationModelName())
            ->where('locale', $locale)
            ->withDefault(function ($translation, $parent) use ($defaultLocale) {
                $fallback = $parent->translations()->where('locale', $defaultLocale)->first();
                if ($fallback) {
                    $translation->fill($fallback->toArray());
                }
            });
    }

    // Helper: Tự động lấy attribute của bản dịch (VD: $article->title thay vì $article->translation->title)
    public function __get($key)
    {
        if (in_array($key, $this->translatedAttributes ?? [])) {
            return $this->translation ? $this->translation->$key : null;
        }
        return parent::__get($key);
    }

    /**
     * QUAN TRỌNG: toán tử `??` và hàm isset() KHÔNG gọi __get() — chúng gọi
     * __isset(). Nếu không override __isset() ở đây, `$article->title ?? '...'`
     * sẽ rơi về __isset() gốc của Eloquent, tức là offsetExists() ->
     * getAttribute('title'). Vì 'title' không phải cột thật trên bảng
     * articles, getAttribute() luôn trả về null, nên isset() luôn luôn
     * false — kết quả là fallback text hiển thị ngay cả khi bản dịch
     * THỰC SỰ tồn tại trong DB. Đây chính là nguyên nhân bug "(Chưa có bản
     * dịch cho VI)" xuất hiện dù locale đúng và bản dịch đã tồn tại.
     */
    public function __isset($key)
    {
        if (in_array($key, $this->translatedAttributes ?? [])) {
            return $this->translation && ! is_null($this->translation->$key);
        }
        return parent::__isset($key);
    }

    protected function getTranslationModelName(): string
    {
        return get_class($this) . 'Translation';
    }
}
