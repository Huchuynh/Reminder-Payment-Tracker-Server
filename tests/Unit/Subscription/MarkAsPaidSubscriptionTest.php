<?php

namespace Tests\Unit\Subscription;

use App\Enums\SubscriptionStatus;
use App\Models\Account;
use App\Models\Service;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkAsPaidSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected SubscriptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SubscriptionService();
    }

    public function test_mark_as_paid_subscription_success()
    {
        $account = Account::factory()->create();
        $service = Service::factory()->create();
        $subscription = Subscription::factory()->create([
            'account_id' => $account->id,
            'service_id' => $service->id,
            'status' => SubscriptionStatus::ACTIVE,
        ]);

        $result = $this->service->mark_as_paid($subscription->id);

        $this->assertEquals(SubscriptionStatus::PAID, $result->status);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => SubscriptionStatus::PAID,
        ]);
    }

    public function test_mark_as_paid_subscription_not_found()
    {
        $this->expectException(\Exception::class);

        $this->service->unsubscribe('abc');
    }
}
