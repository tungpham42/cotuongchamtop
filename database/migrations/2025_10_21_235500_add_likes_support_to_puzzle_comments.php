<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('puzzle_comments', function (Blueprint $table) {
            if (!Schema::hasColumn('puzzle_comments', 'likes_count')) {
                $table->unsignedInteger('likes_count')->default(0)->after('content');
            }
        });

        Schema::create('puzzle_comment_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('puzzle_comment_id')
                ->constrained('puzzle_comments')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('identifier', 191);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->unique(['puzzle_comment_id', 'identifier'], 'puzzle_comment_like_unique');
            $table->index(['puzzle_comment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('puzzle_comment_likes');

        Schema::table('puzzle_comments', function (Blueprint $table) {
            if (Schema::hasColumn('puzzle_comments', 'likes_count')) {
                $table->dropColumn('likes_count');
            }
        });
    }
};
