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
        Schema::table('subscription_history', function (Blueprint $table) {
            $table->dropColumn('action');
        });

        DB::statement("
            DO $$
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'subscription_history_action_enum') THEN
                    CREATE TYPE subscription_history_action_enum AS ENUM ('expired', 'canceled', 'renewed');
                END IF;
            END$$;
        ");

        Schema::table('subscription_history', function (Blueprint $table) {
            $table->enum('action', ['expired', 'canceled', 'renewed'])
                ->nullable()
                ->after('subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_history', function (Blueprint $table) {
            $table->dropColumn('action');
        });

        DB::statement("CREATE TYPE subscription_history_action_enum AS ENUM ('expired', 'renewed')");

        Schema::table('subscription_history', function (Blueprint $table) {
            $table->enum('action', ['expired', 'renewed'])
                ->nullable()
                ->after('subscription_id');
        });
    }
};
