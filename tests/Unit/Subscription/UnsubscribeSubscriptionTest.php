<?php

namespace Tests\Unit\Subscription;

use App\Enums\SubscriptionHistoryAction;
use App\Enums\SubscriptionStatus;
use App\Models\Account;
use App\Models\Service;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnsubscribeSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected SubscriptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SubscriptionService();
    }

    public function test_unsubscribe_subscription_success()
    {
        $account = Account::factory()->create();
        $service = Service::factory()->create();
        $subscription = Subscription::factory()->create([
            'account_id' => $account->id,
            'service_id' => $service->id,
            'status' => SubscriptionStatus::ACTIVE,
        ]);

        $result = $this->service->unsubscribe($subscription->id);

        $this->assertEquals(SubscriptionStatus::CANCELED, $result->status);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => SubscriptionStatus::CANCELED,
        ]);

        $this->assertDatabaseHas('subscription_history', [
            'subscription_id' => $subscription->id,
            'action' => SubscriptionHistoryAction::CANCELED,
        ]);
    }

    public function test_unsubscribe_subscription_not_found()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Failed to cancel service.*/');

        $this->service->unsubscribe(9999);
    }
}
