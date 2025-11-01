<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'move_history')) {
                $table->longText('move_history')->nullable()->after('fen');
            }
            if (!Schema::hasColumn('rooms', 'game_started_at')) {
                $table->timestamp('game_started_at')->nullable()->after('modified_at');
            }
            if (!Schema::hasColumn('rooms', 'game_finished_at')) {
                $table->timestamp('game_finished_at')->nullable()->after('game_started_at');
            }
            if (!Schema::hasColumn('rooms', 'last_move_at')) {
                $table->timestamp('last_move_at')->nullable()->after('game_finished_at');
            }
        });
    }

    public function down(): void {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'last_move_at')) {
                $table->dropColumn('last_move_at');
            }
            if (Schema::hasColumn('rooms', 'game_finished_at')) {
                $table->dropColumn('game_finished_at');
            }
            if (Schema::hasColumn('rooms', 'game_started_at')) {
                $table->dropColumn('game_started_at');
            }
            if (Schema::hasColumn('rooms', 'move_history')) {
                $table->dropColumn('move_history');
            }
        });
    }
};

