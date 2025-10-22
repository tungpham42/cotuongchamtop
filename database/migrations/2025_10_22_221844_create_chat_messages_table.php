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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->string('room_code', 50)->index();
            $table->string('username', 100);
            $table->text('message');
            $table->enum('type', ['message', 'enter', 'leave', 'system'])->default('message');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            
            $table->index(['room_code', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_messages');
    }
};
