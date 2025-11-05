<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TYPE subscription_status_enum ADD VALUE IF NOT EXISTS 'paid'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("CREATE TYPE subscription_status_enum_new AS ENUM ('active', 'expiring', 'expired', 'canceled', 'overdue')");
        DB::statement("ALTER TABLE subscriptions ALTER COLUMN status TYPE subscription_status_enum_new USING status::text::subscription_status_enum_new");
        DB::statement("DROP TYPE subscription_status_enum");
        DB::statement("ALTER TYPE subscription_status_enum_new RENAME TO subscription_status_enum");
    }
};
