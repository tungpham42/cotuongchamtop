<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->string('initial_fen');
            $table->json('moves')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('views');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
