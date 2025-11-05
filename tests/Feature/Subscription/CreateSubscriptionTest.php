<?php

namespace Subscription;

use App\Models\Account;
use App\Models\Service;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_subscription(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($account, 'api');

        $service = Service::factory()->create();

        $payload = [
            'account_id' => $account->id,
            'service_id' => $service->id,
            'start_date' => now()->subDay()->toDateTimeString(),
            'end_date' => now()->addMonth()->toDateTimeString(),
            'plan' => 'premium',
            'notes' => 'Test subscription creation',
            'alert_thresholds' => [7, 3],
            'reminder_frequency' => 12,
            'reminder_channels' => ['email', 'push', 'in_app'],
            'status' => 'active',
        ];

        $response = $this->postJson('/api/subscriptions', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Subscription successfully created']);

        $subscription = Subscription::latest()->first();

        $this->assertDatabaseHas('subscriptions', [
            'account_id' => $account->id,
            'service_id' => $service->id,
            'start_date' => $payload['start_date'],
            'end_date' => $payload['end_date'],
            'plan' => $payload['plan'],
            'notes' => $payload['notes'],
            'status' => $payload['status'],
        ]);

        $this->assertEquals([7, 3], $subscription->alert_thresholds);
        $this->assertEquals(['email', 'push', 'in_app'], $subscription->reminder_channels);
    }

    #[Test]
    public function it_fails_when_account_id_is_missing(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($account, 'api');

        $service = Service::factory()->create();

        $payload = [
            'service_id' => $service->id,
            'start_date' => now()->subDay()->toDateTimeString(),
            'end_date' => now()->addMonth()->toDateTimeString(),
            'plan' => 'premium',
            'notes' => 'Test subscription creation',
            'alert_thresholds' => [7, 3],
            'reminder_frequency' => 12,
            'reminder_channels' => ['email', 'push', 'in_app'],
            'status' => 'active',
        ];

        $response = $this->postJson('/api/subscriptions', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['account_id']);

        $this->assertDatabaseCount('subscriptions', 0);
    }

    #[Test]
    public function it_fails_when_alert_thresholds_is_not_array(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($account, 'api');

        $service = Service::factory()->create();

        $payload = [
            'account_id' => $account->id,
            'service_id' => $service->id,
            'start_date' => now()->subDay()->toDateTimeString(),
            'end_date' => now()->addMonth()->toDateTimeString(),
            'plan' => 'premium',
            'notes' => 'Test subscription creation',
            'alert_thresholds' => 8,
            'reminder_frequency' => 12,
            'reminder_channels' => ['email', 'push', 'in_app'],
            'status' => 'active',
        ];

        $response = $this->postJson('/api/subscriptions', $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['alert_thresholds']);

        $this->assertDatabaseCount('subscriptions', 0);
    }

    #[Test]
    public function it_fails_when_status_is_invalid(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($account, 'api');

        $service = Service::factory()->create();

        $payload = [

            'service_id' => $service->id,
            'start_date' => now()->subDay()->toDateTimeString(),
            'end_date' => now()->addMonth()->toDateTimeString(),
            'plan' => 'premium',
            'notes' => 'Test subscription creation',
            'alert_thresholds' => 8,
            'reminder_frequency' => 12,
            'reminder_channels' => ['email', 'push', 'in_app'],
            'status' => 'this is an invalid status',
        ];

        $response = $this->postJson('/api/subscriptions', $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        $this->assertDatabaseCount('subscriptions', 0);
    }
}
