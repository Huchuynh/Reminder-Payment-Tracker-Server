<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Xóa constraint cũ nếu có
        DB::statement("ALTER TABLE subscriptions DROP CONSTRAINT IF EXISTS subscriptions_status_check");

        // Tạo constraint mới bao gồm 'paid'
        DB::statement("
            ALTER TABLE subscriptions
            ADD CONSTRAINT subscriptions_status_check
            CHECK (status IN ('active', 'expiring', 'expired', 'canceled', 'overdue', 'paid'))
        ");
    }

    public function down(): void
    {
        // Rollback về danh sách cũ (không có 'paid')
        DB::statement("ALTER TABLE subscriptions DROP CONSTRAINT IF EXISTS subscriptions_status_check");

        DB::statement("
            ALTER TABLE subscriptions
            ADD CONSTRAINT subscriptions_status_check
            CHECK (status IN ('active', 'expiring', 'expired', 'canceled', 'overdue'))
        ");
    }
};

