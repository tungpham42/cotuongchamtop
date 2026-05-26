<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tournaments Table
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->dateTime('start_date');
            $table->enum('status', ['open', 'in_progress', 'completed'])->default('open');
            $table->integer('max_players')->default(16);
            $table->timestamps();
        });

        // 2. Tournament Registrations (Pivot Table)
        Schema::create('tournament_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // 3. Update Rooms Table for Bracket Tracking
        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('tournament_id')->nullable()->constrained('tournaments')->onDelete('cascade');
            $table->integer('tournament_round')->nullable();

            // Link to the next match in the bracket (using your string 'code' primary key)
            $table->string('next_room_code')->nullable();
        });
    }

    public function down()
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign(['tournament_id']);
            $table->dropColumn(['tournament_id', 'tournament_round', 'next_room_code']);
        });
        Schema::dropIfExists('tournament_user');
        Schema::dropIfExists('tournaments');
    }
};
