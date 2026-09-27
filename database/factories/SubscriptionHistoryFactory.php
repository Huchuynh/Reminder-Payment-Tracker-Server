<?php

namespace Database\Factories;

use App\Enums\SubscriptionHistoryAction;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionHistoryFactory extends Factory
{
    public function definition(): array
    {
        $createAt = $this->faker->optional()->dateTimeBetween('-6 months', 'now');

        return [
            'subscription_id' => Subscription::factory(),
            'action' => fake()->randomElement(SubscriptionHistoryAction::cases())->value,
            'created_at' => $createAt,
            'updated_at' => $createAt,
        ];
    }
}
