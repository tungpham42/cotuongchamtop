<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Translatable;

class Article extends Model
{
    use Translatable;

    protected $fillable = ['author_id', 'status', 'views'];

    // Khai báo các trường được dịch để Trait có thể bắt qua Magic Method (__get)
    protected $translatedAttributes = ['title', 'slug', 'content'];

    // Eager load mặc định để tránh lỗi N+1 query khi lặp danh sách
    protected $with = ['translation'];
}
