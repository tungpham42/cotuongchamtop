<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('puzzle_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('puzzle_id')->constrained('puzzles')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('puzzle_comments')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('author_name')->nullable();
            $table->text('content');
            $table->boolean('is_public')->default(true);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['puzzle_id', 'is_public']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('puzzle_comments');
    }
};
