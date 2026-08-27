<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karma_logs', function (Blueprint $table) {
            // Stores the id of the source record (e.g. rooms.id) so a
            // karma award can be checked for uniqueness per reason+reference,
            // preventing double-awarding if a match-completion handler
            // ever runs twice for the same match.
            $table->unsignedBigInteger('reference_id')->nullable()->after('reason');
            $table->index(['user_id', 'reason', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::table('karma_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'reason', 'reference_id']);
            $table->dropColumn('reference_id');
        });
    }
};
