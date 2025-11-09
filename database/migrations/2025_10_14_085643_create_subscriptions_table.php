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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('plan')->nullable();
            $table->enum('status', ['active', 'expiring', 'expired', 'canceled', 'paid'])->default('active');
            $table->text('notes')->nullable();
            $table->integer('alert_thresholds')->nullable();
            $table->integer('reminder_frequency')->nullable();
            $table->json('reminder_channels')->nullable();
            $table->timestamp('last_reminded_at')->nullable();
            $table->timestamps();
            $table->unique(['account_id', 'service_id'], 'unique_account_service');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
