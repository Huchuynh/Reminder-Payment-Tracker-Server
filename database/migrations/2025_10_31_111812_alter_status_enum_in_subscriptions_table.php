<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        DB::statement("
            DO $$
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'subscription_status_enum') THEN
                    CREATE TYPE subscription_status_enum AS ENUM ('active', 'expiring', 'expired', 'canceled', 'overdue');
                END IF;
            END$$;
        ");

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('status', ['active', 'expiring', 'expired', 'canceled', 'overdue'])
                ->default('active')
                ->after('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        DB::statement("CREATE TYPE subscription_status_enum AS ENUM ('active', 'expiring', 'expired', 'canceled')");

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('status', ['active', 'expiring', 'expired', 'canceled'])
                ->default('active')
                ->after('end_date');
        });

    }
};
