<?php

namespace Tests\Unit\Subscription;

use App\Models\Account;
use App\Models\Service;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected SubscriptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SubscriptionService();
    }

    public function test_create_subscription_success()
    {
        $account = Account::factory()->create();
        $serviceModel = Service::factory()->create();

        $data = [
            'account_id' => $account->id,
            'service_id' => $serviceModel->id,
            'status' => 'active',
            'start_date' => now()->format('Y-m-d H:i:s'),
            'end_date' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'plan' => 'Standard Plan',
            'price' => 220000,
            'notes' => 'Test notes',
            'alert_thresholds' => 3,
            'reminder_frequency' => 7,
            'reminder_channels' => ['email'], // giả sử hợp lệ
            'last_reminded_at' => null
        ];

        $subscription = $this->service->create($data);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'account_id' => $account->id,
            'service_id' => $serviceModel->id,
            'status' => 'active',
            'plan' => 'Standard Plan',
            'price' => 220000
        ]);
    }

    public function test_create_subscription_fails_with_invalid_account_id()
    {
        $serviceModel = Service::factory()->create();

        $data = [
            'account_id' => 9999,
            'service_id' => $serviceModel->id,
            'status' => 'active',
            'start_date' => now()->format('Y-m-d H:i:s'),
            'end_date' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'plan' => 'Standard Plan',
            'price' => 220000,
            'alert_thresholds' => 3,
            'reminder_channels' => ['email']
        ];

        $this->expectException(\Exception::class);

        $this->expectExceptionMessageMatches('/account_id/');

        $this->service->create($data);
    }
}
