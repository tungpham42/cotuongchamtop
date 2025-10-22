<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('room_comment_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_comment_id')->constrained('room_comments')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('identifier', 128);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->unique(['room_comment_id', 'identifier']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('room_comment_likes');
    }
};
