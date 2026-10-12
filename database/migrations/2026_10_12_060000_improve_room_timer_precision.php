<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->decimal('red_time', 12, 3)->default(600)->change();
            $table->decimal('black_time', 12, 3)->default(600)->change();
            $table->timestamp('last_update', 3)->nullable()->change();
            $table->unsignedBigInteger('timer_revision')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->integer('red_time')->default(600)->change();
            $table->integer('black_time')->default(600)->change();
            $table->timestamp('last_update')->nullable()->change();
            $table->dropColumn('timer_revision');
        });
    }
};
