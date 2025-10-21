<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Xóa cột is_deleted nếu tồn tại
            if (Schema::hasColumn('services', 'is_deleted')) {
                $table->dropColumn('is_deleted');
            }

            // Thêm cột deleted_at cho SoftDeletes
            $table->softDeletes(); // tạo cột deleted_at (nullable timestamp)
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Khôi phục lại cột is_deleted nếu rollback
            $table->boolean('is_deleted')->default(false);

            // Xóa cột deleted_at
            $table->dropSoftDeletes();
        });
    }
};
