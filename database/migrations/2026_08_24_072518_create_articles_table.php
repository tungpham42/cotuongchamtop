<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bảng chính: Dữ liệu không phụ thuộc ngôn ngữ
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('published');
            $table->integer('views')->default(0);
            $table->timestamps();
        });

        // Bảng dịch: Dữ liệu ngôn ngữ
        Schema::create('article_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->string('locale', 2)->index(); // Chứa các giá trị vi, en, zh, ja, ko
            $table->string('title');
            $table->string('slug');
            $table->longText('content')->nullable();
            $table->timestamps();

            // Đảm bảo 1 bài viết chỉ có 1 bản dịch cho mỗi ngôn ngữ
            $table->unique(['article_id', 'locale']);
            // Đảm bảo slug là duy nhất trong cùng 1 ngôn ngữ
            $table->unique(['locale', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_translations');
        Schema::dropIfExists('articles');
    }
};
