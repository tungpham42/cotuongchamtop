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
        Schema::table('puzzle_comments', function (Blueprint $table) {
            if (!Schema::hasColumn('puzzle_comments', 'parent_id')) {
                $table->foreignId('parent_id')
                    ->nullable()
                    ->after('puzzle_id')
                    ->constrained('puzzle_comments')
                    ->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('puzzle_comments', function (Blueprint $table) {
            if (Schema::hasColumn('puzzle_comments', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            }
        });
    }
};
