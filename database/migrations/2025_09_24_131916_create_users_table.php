<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->float('elo')->default(1600);
            $table->bigInteger('points')->default(0);
            $table->float('scores')->default(0);
            $table->string('email')->default('')->unique();
            $table->string('phone')->default('');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->boolean('active_status')->default(0);
            $table->string('avatar')->default('avatar.png');
            $table->boolean('dark_mode')->default(0);
            $table->string('messenger_color')->nullable();
            $table->string('board_theme')->default('xiangqi-board');
            $table->string('pieces_theme')->default('wiki');
        });
    }

    public function down(): void {
        Schema::dropIfExists('users');
    }
};
