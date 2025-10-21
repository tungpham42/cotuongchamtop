<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('puzzles', function (Blueprint $table) {
            $table->text('description')->nullable()->after('slug');
            $table->boolean('is_public')->default(true)->after('description');
            $table->unsignedInteger('likes_count')->default(0)->after('rating');
            $table->unsignedInteger('hard_count')->default(0)->after('likes_count');
            $table->unsignedInteger('unsolved_count')->default(0)->after('hard_count');
            if (!Schema::hasColumn('puzzles', 'created_at')) {
                $table->timestamp('created_at')->useCurrent()->after('unsolved_count');
            }
        });
    }

    public function down(): void {
        Schema::table('puzzles', function (Blueprint $table) {
            if (Schema::hasColumn('puzzles', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('puzzles', 'is_public')) {
                $table->dropColumn('is_public');
            }
            if (Schema::hasColumn('puzzles', 'likes_count')) {
                $table->dropColumn('likes_count');
            }
            if (Schema::hasColumn('puzzles', 'hard_count')) {
                $table->dropColumn('hard_count');
            }
            if (Schema::hasColumn('puzzles', 'unsolved_count')) {
                $table->dropColumn('unsolved_count');
            }
            if (Schema::hasColumn('puzzles', 'created_at')) {
                $table->dropColumn('created_at');
            }
        });
    }
};
