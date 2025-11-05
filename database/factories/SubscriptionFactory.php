<?php

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Models\Account;
use App\Models\Service;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'service_id' => Service::factory(),
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'status' => SubscriptionStatus::ACTIVE,
            'plan' => $this->faker->randomElement(['basic', 'standard', 'premium']),
            'notes' => $this->faker->paragraph(),
            'alert_thresholds' => json_encode([7, 3, 1]),
            'reminder_frequency' => $this->faker->randomElement([6, 12, 24]),
            'reminder_channels' => json_encode(['email', 'push', 'in_app']),
            'last_reminded_at' => $this->faker->optional()->dateTimeBetween('-7 days', 'now'),
        ];
    }
}
