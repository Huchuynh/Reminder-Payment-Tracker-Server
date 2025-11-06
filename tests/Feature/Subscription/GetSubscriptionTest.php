<?php

namespace Subscription;

use App\Models\Account;
use App\Models\Service;
use App\Models\Subscription;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetSubscriptionTest extends TestCase
{
    protected $account;
    private string $prefix = "/api/subscriptions";

    protected function setUp(): void
    {
        parent::setUp();
        $this->account = Account::factory()->create();
        $this->actingAs($this->account, 'api');
    }

    #[Test]
    public function it_returns_filtered_subscriptions_successfully(): void
    {
        $service_a = Service::factory()->create(['name' => 'Google Drive']);
        $service_b = Service::factory()->create(['name' => 'Dropbox']);

        Subscription::factory()->create([
            'account_id' => $this->account->id,
            'service_id' => $service_a->id,
            'status' => 'active',
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(10),
        ]);

        Subscription::factory()->create([
            'account_id' => $this->account->id,
            'service_id' => $service_b->id,
            'status' => 'canceled',
        ]);

        $payload = [
            'account_id' => $this->account->id,
            'search' => 'Google',
            'status' => 'active',
            'sort_by' => 'start_date',
            'sort_order' => 'desc',
            'limit' => 10,
        ];

        $response = $this->getJson($this->prefix . '?' . http_build_query($payload));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'service' => [
                            'id',
                            'name',
                            'provider',
                            'icon'
                        ],
                        'status',
                        'start_date',
                        'end_date',
                        'service',
                        'plan',
                        'notes'
                    ],
                ],
                'links',
                'meta'
            ]);

        $response->assertJsonFragment(['status' => 'active']);
        $response->assertJsonMissing(['status' => 'canceled']);
    }

    #[Test]
    public function it_filters_by_date_range(): void
    {
        $service = Service::factory()->create(['name' => 'Google Drive']);

        Subscription::factory()->create([
            'account_id' => $this->account->id,
            'service_id' => $service->id,
            'status' => 'active',
            'start_date' => '2025-10-01 12:00:00',
            'end_date' => '2025-10-30 12:00:00',
        ]);

        $payload = [
            'account_id' => $this->account->id,
            'status' => 'active',
            'search' => 'Google',
            'sort_by' => 'created_at',
            'sort_order' => 'desc',
            'start_date' => '2025-09-01',
            'end_date' => '2025-11-01',
            'limit' => 10,
        ];

        $response = $this->getJson($this->prefix . '?' . http_build_query($payload));
        $response->assertOk()->assertJsonCount(1, 'data');
    }

    #[Test]
    public function it_returns_validation_error_if_account_id_or_status_missing(): void
    {
        $response = $this->getJson($this->prefix . '?' . http_build_query([
                'status' => 'active',
            ]));

        $response->assertStatus(422)->assertJsonValidationErrors(['account_id']);

        $response = $this->getJson($this->prefix . '?' . http_build_query([
                'account_id' => $this->account->id,
            ]));

        $response->assertStatus(422)->assertJsonValidationErrors(['status']);
    }

    #[Test]
    public function it_returns_subscription_when_found(): void
    {
        $subscription = Subscription::factory()
            ->for($this->account)
            ->for(Service::factory())
            ->create();

        $response = $this->getJson("{$this->prefix}/{$subscription->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Subscription successfully found',
                'data' => [
                    'id' => $subscription->id,
                    'service' => [
                        'id' => $subscription->service->id,
                    ]
                ]
            ]);
    }

    #[Test]
    public function it_returns_not_found_when_subscription_does_not_exist(): void
    {
        $response = $this->getJson("{$this->prefix}/999");
        $response->assertNotFound()
            ->assertJsonStructure([
                'success',
                'message',
            ]);
    }
}
