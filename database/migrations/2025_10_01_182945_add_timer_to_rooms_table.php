<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->integer('black_time')->default(600); // 10 phút
            $table->integer('red_time')->default(600);   // 10 phút
            $table->string('active_player')->nullable(); // "black" hoặc "red"
            $table->timestamp('last_update')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['black_time', 'red_time', 'active_player', 'last_update']);
        });
    }
};
