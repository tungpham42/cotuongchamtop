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
            // Thêm cột status để quản lý trạng thái phòng (mặc định là 'waiting')
            $table->string('status')->default('waiting')->after('name'); // Bạn có thể đổi 'name' thành cột bạn muốn đứng trước

            // Thêm 2 cột quản lý ID người chơi vãng lai
            $table->string('anonymous_red_id')->nullable()->after('status');
            $table->string('anonymous_black_id')->nullable()->after('anonymous_red_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            // Xóa cả 3 cột nếu rollback
            $table->dropColumn(['status', 'anonymous_red_id', 'anonymous_black_id']);
        });
    }
};
