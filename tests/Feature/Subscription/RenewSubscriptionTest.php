<?php

namespace Tests\Feature\Subscription;

use App\Enums\SubscriptionHistoryAction;
use App\Enums\SubscriptionStatus;
use App\Models\Account;
use App\Models\Service;
use App\Models\Subscription;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RenewSubscriptionTest extends TestCase
{
    protected $account;
    private string $prefix = "/api/subscriptions/";

    protected function setUp(): void
    {
        parent::setUp();
        $this->account = Account::factory()->create();
        $this->actingAs($this->account, 'api');
    }

    #[Test]
    public function it_renew_a_subscription_successfully(): void
    {
        $subscription = Subscription::factory()
            ->for($this->account)
            ->for(Service::factory())
            ->create([
                "status" => SubscriptionStatus::OVERDUE,
            ]);

        $payload = ["end_date" => now()->addMonth()->format("Y-m-d H:i:s")];

        $response = $this->postJson("{$this->prefix}{$subscription->id}/renew", $payload);

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Service successfully renewed'])
            ->assertJsonFragment(['status' => SubscriptionStatus::ACTIVE]);

        $this->assertDatabaseHas('subscriptions', [
            "id" => $subscription->id,
            "end_date" => $payload["end_date"],
            "status" => SubscriptionStatus::ACTIVE,
        ]);

        $this->assertDatabaseHas('subscription_history', [
            "subscription_id" => $subscription->id,
            "action" => SubscriptionHistoryAction::RENEWED,
        ]);
    }

    #[Test]
    public function it_fails_to_renew_without_permission(): void
    {
        $subscription = Subscription::factory()
            ->for(Account::factory())
            ->for(Service::factory())
            ->create();

        $payload = ["end_date" => now()->addMonth()->format('Y-m-d H:i:s')];
        $response = $this->postJson("{$this->prefix}{$subscription->id}/renew", $payload);

        $response->assertForbidden();
    }

    #[Test]
    public function it_fails_to_renew_with_invalid_data(): void
    {
        $subscription = Subscription::factory()
            ->for($this->account)
            ->for(Service::factory())
            ->create();

        $payload = [];

        $response = $this->postJson("{$this->prefix}{$subscription->id}/renew", $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['end_date']);
    }
}
