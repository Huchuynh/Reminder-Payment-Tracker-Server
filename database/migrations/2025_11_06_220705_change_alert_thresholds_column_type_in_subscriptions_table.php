<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Xoá cột cũ
            $table->dropColumn('alert_thresholds');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            // Tạo lại với kiểu int
            $table->integer('alert_thresholds')->nullable()->after('reminder_frequency');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Rollback lại kiểu json nếu cần
            $table->dropColumn('alert_thresholds');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->json('alert_thresholds')->nullable()->after('reminder_frequency');
        });
    }
};
