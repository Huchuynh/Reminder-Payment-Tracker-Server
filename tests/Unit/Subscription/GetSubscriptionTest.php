<?php

namespace Tests\Unit\Subscription;

use App\Models\Account;
use App\Models\Service;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected SubscriptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SubscriptionService();
    }

    public function test_get_returns_filtered_subscriptions()
    {
        $serviceModel = Service::factory()->create(['name' => 'Netflix']);
        $account = Account::factory()->create();

        Subscription::factory()->create([
            'account_id' => $account->id,
            'service_id' => $serviceModel->id,
            'status' => 'active',
        ]);

        $params = [
            'search' => 'netflix',
            'sort_order' => 'asc',
            'sort_by' => 'id',
            'limit' => 10,
            'account_id' => $account->id,
            'status' => 'active'
        ];

        $result = $this->service->get($params);
        $this->assertEquals(1, $result->total());
    }

    public function test_it_throws_exception_when_query_fails()
    {
        $this->expectException(\Exception::class);

        $this->service->get([
            'is_active' => 56,
            'search' => '',
            'limit' => 10
        ]);
    }

    public function test_get_subscription_by_service_id_returns_filtered_subscriptions()
    {
        $account = Account::factory()->create([
            'is_active' => true,
            'full_name' => 'Alice Johnson',
            'email' => 'alice@test.com'
        ]);

        $serviceModel = Service::factory()->create();

        $subscription = Subscription::factory()->create([
            'account_id' => $account->id,
            'service_id' => $serviceModel->id,
            'status' => 'active',
        ]);

        $params = [
            'search' => 'Alice',
            'status' => 'active',
            'limit' => 10
        ];

        $result = $this->service->getSubscriptionByServiceId($params, $serviceModel->id);

        $this->assertEquals(1, $result->total());
        $this->assertEquals($subscription->id, $result->first()->id);
    }

    public function test_get_subscription_by_service_id_throws_exception_when_query_fails()
    {
        $this->expectException(\Exception::class);

        $this->service->getSubscriptionByServiceId([], '123');
    }

    public function test_find_by_id_success()
    {
        $account = Account::factory()->create();
        $service = Service::factory()->create();
        $subscription = Subscription::factory()->create([
            'account_id' => $account->id,
            'service_id' => $service->id
        ]);

        $result = $this->service->findById($subscription->id);

        $this->assertEquals($subscription->id, $result->id);
        $this->assertTrue($result->relationLoaded('service'));
        $this->assertEquals($service->id, $result->service->id);
    }

    public function test_find_by_id_throws_model_not_found_exception()
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->findById(9999);
    }
}
