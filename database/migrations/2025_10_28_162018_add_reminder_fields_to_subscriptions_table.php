<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->json('alert_thresholds')->nullable()->after('status');
            $table->integer('reminder_frequency')->default(24)->after('alert_thresholds');
            $table->json('reminder_channels')->nullable()->after('reminder_frequency');
            $table->timestamp('last_reminded_at')->nullable()->after('reminder_channels');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'alert_thresholds',
                'reminder_frequency',
                'reminder_channels',
                'last_reminded_at',
            ]);
        });
    }
};
