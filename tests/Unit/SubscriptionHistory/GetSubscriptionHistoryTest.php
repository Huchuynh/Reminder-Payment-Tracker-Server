<?php

namespace Tests\Unit\SubscriptionHistory;

use App\Enums\SubscriptionHistoryAction;
use App\Models\Account;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\SubscriptionHistory;
use App\Services\SubscriptionHistoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetSubscriptionHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected SubscriptionHistoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SubscriptionHistoryService();
    }

    public function test_get_by_subscription_id_returns_histories()
    {
        $subscription = Subscription::factory()->create([
            'account_id' => Account::factory()->create(),
            'service_id' => Service::factory()->create(),
        ]);
        $history1 = SubscriptionHistory::factory()->create([
            'subscription_id' => $subscription->id,
            'action' => SubscriptionHistoryAction::RENEWED,
        ]);
        $history2 = SubscriptionHistory::factory()->create([
            'subscription_id' => $subscription->id,
            'action' => SubscriptionHistoryAction::CANCELED,
        ]);

        $histories = $this->service->getBySubscriptionId($subscription->id);

        $this->assertCount(2, $histories);
        $this->assertEquals([SubscriptionHistoryAction::RENEWED, SubscriptionHistoryAction::CANCELED], $histories->pluck('action')->toArray());
    }

    public function test_get_by_subscription_id_throws_exception_when_query_fails()
    {
        $this->expectException(\Exception::class);

        $this->service->getBySubscriptionId('any-id');
    }
}
