<?php

namespace Tests\Feature\Subscription;

use App\Models\Account;
use App\Models\Service;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected $account;
    private string $prefix = "/api/subscriptions/";

    protected function setUp(): void
    {
        parent::setUp();
        $this->account = Account::factory()->create();
        $this->actingAs($this->account, 'api');
    }

    #[Test]
    public function it_updates_subscription_successfully(): void
    {
        $subscription = Subscription::factory()
            ->for($this->account)
            ->for(Service::factory())
            ->create([
                "plan" => "Basic",
                "notes" => "Before Update",
            ]);

        $payload = [
            "plan" => "Pro",
            "notes" => "After Update",
        ];

        $response = $this->putJson("{$this->prefix}{$subscription->id}", $payload);

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Subscription successfully updated'])
            ->assertJsonFragment(['plan' => 'Pro'])
            ->assertJsonFragment(['notes' => 'After Update']);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'plan' => 'Pro',
            'notes' => 'After Update',
        ]);
    }

    #[Test]
    public function it_fails_validation_when_required_fields_missing(): void
    {
        $subscription = Subscription::factory()
            ->for($this->account)
            ->for(Service::factory())
            ->create();

        $response = $this->putJson("{$this->prefix}{$subscription->id}", [
            'plan' => '',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['plan']);
    }

    #[Test]
    public function it_returns_forbidden_if_user_cannot_update()
    {
        $subscription = Subscription::factory()
            ->for(Account::factory())
            ->for(Service::factory())
            ->create();

        $response = $this->putJson("/api/subscriptions/{$subscription->id}", [
            'plan' => 'Pro',
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function it_returns_not_found_if_subscription_missing()
    {
        $response = $this->putJson("{$this->prefix}99999", [
            'plan' => 'Pro',
        ]);

        $response->assertNotFound();
    }
}
