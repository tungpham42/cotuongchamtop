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
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('host_session', 32)->nullable()->after('host_id');
            $table->string('guest_session', 32)->nullable()->after('guest_id');
            $table->index(['host_session', 'guest_session']);
            $table->index('host_session');
            $table->index('guest_session');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex(['host_session', 'guest_session']);
            $table->dropIndex(['host_session']);
            $table->dropIndex(['guest_session']);
            $table->dropColumn(['host_session', 'guest_session']);
        });
    }
};
