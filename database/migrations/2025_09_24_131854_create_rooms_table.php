<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name')->nullable();
            $table->string('player_turn')->nullable();
            $table->integer('host_id')->nullable();
            $table->string('host_session', 32)->nullable();
            $table->integer('guest_id')->nullable();
            $table->string('guest_session', 32)->nullable();
            $table->integer('result')->nullable();
            $table->float('host_score')->default(0);
            $table->float('guest_score')->default(0);
            $table->float('host_elo')->default(0);
            $table->float('guest_elo')->default(0);
            $table->string('fen')->default('');
            $table->string('pass')->nullable();
            $table->bigInteger('host_time_remaining')->nullable();
            $table->bigInteger('guest_time_remaining')->nullable();
            $table->timestamp('modified_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->index(['host_session', 'guest_session'], 'rooms_host_session_guest_session_index');
            $table->index('host_session', 'rooms_host_session_index');
            $table->index('guest_session', 'rooms_guest_session_index');
        });
    }

    public function down(): void {
        Schema::dropIfExists('rooms');
    }
};
