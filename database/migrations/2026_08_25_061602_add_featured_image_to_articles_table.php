<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Ảnh đại diện bài viết. Đặt trên bảng articles (không phải
            // article_translations) vì ảnh dùng chung cho mọi locale, không
            // phụ thuộc ngôn ngữ. Chỉ lưu path tương đối trong disk 'public'
            // (vd: articles/xxxx.jpg), URL đầy đủ được build qua accessor
            // Article::getFeaturedImageUrlAttribute().
            $table->string('featured_image')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('featured_image');
        });
    }
};
