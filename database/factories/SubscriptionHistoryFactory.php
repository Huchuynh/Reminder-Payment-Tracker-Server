<?php

namespace Database\Factories;

use App\Enums\SubscriptionHistoryAction;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subscription_id' => Subscription::factory(),
            'action' => fake()->randomElement(SubscriptionHistoryAction::cases())->value,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
