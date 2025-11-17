<?php

namespace Tests\Unit\Subscription;

use App\Enums\SubscriptionStatus;
use App\Models\Account;
use App\Models\Service;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected SubscriptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SubscriptionService();
    }

    public function test_update_subscription_success()
    {
        $account = Account::factory()->create();
        $service = Service::factory()->create();
        $subscription = Subscription::factory()->create([
            'account_id' => $account->id,
            'service_id' => $service->id,
            'status' => 'active',
        ]);

        $data = [
            'end_date' => now()->addDays(5),
            'plan' => 'Updated Plan',
            'price' => 300000,
            'alert_thresholds' => 5
        ];

        $updated = $this->service->update($subscription->id, $data);

        $this->assertEquals(SubscriptionStatus::EXPIRING, $updated->status);
        $this->assertEquals('Updated Plan', $updated->plan);
        $this->assertEquals(300000, $updated->price);
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'plan' => 'Updated Plan',
            'price' => 300000,
            'status' => 'expiring'
        ]);
    }

    public function test_update_subscription_not_found()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Failed to update subscription\. No query results for model/');

        $data = [
            'end_date' => now()->addDays(5),
            'plan' => 'Updated Plan',
            'price' => 300000,
            'alert_thresholds' => 5
        ];

        $this->service->update(9999, $data);
    }


    public function test_update_subscription_with_end_date_before_start_date()
    {
        $account = Account::factory()->create();
        $service = Service::factory()->create();
        $subscription = Subscription::factory()->create([
            'account_id' => $account,
            'service_id' => $service,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
        ]);


        $data = [
            'end_date' => now(),
            'plan' => 'Updated Plan',
            'price' => 300000,
            'alert_thresholds' => 5
        ];

        $updated = $this->service->update($subscription->id, $data);

        $this->assertEquals(SubscriptionStatus::EXPIRING, $updated->status);
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => 'expiring'
        ]);
    }

    public function test_update_subscription_optional_fields_only()
    {
        $account = Account::factory()->create();
        $service = Service::factory()->create();
        $subscription = Subscription::factory()->create([
            'account_id' => $account->id,
            'service_id' => $service->id,
            'plan' => 'Old Plan',
            'price' => 200000
        ]);

        $data = [
            'notes' => 'Updated notes',
            'alert_thresholds' => 5,
            'reminder_frequency' => 3,
            'reminder_channels' => ['email'],
            'last_reminded_at' => now()->format('Y-m-d H:i:s'),
            'end_date' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'plan' => 'Old Plan',    // giữ nguyên plan
            'price' => 200000        // giữ nguyên price
        ];

        $updated = $this->service->update($subscription->id, $data);

        $this->assertEquals('Old Plan', $updated->plan);
        $this->assertEquals(200000, $updated->price);
        $this->assertEquals('Updated notes', $updated->notes);
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'notes' => 'Updated notes',
            'alert_thresholds' => 5
        ]);
    }
}
