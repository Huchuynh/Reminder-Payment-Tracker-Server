<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $serviceTypes = [
            'Payment', 'Top-up', 'Transfer', 'Withdraw', 'Shopping',
            'Entertainment', 'Education', 'Health', 'Travel', 'Booking',
            'Ride', 'Bill Payment', 'Internet', 'Mobile', 'TV',
            'Insurance', 'Savings', 'Loan', 'Investment', 'Gift',
            'Game', 'Streaming', 'Ticket', 'Food Delivery', 'Restaurant',
            'Supermarket', 'Utilities', 'Tax', 'Flight', 'Movie',
            'Books', 'Music', 'App', 'Bank Card', 'Pet Care'
        ];

        $serviceModifiers = [
            'Fast', 'Easy', 'Online', 'Auto', 'Secure',
            'Family', 'Student', 'Business', 'Personal', 'Monthly',
            'Weekly', 'Simple', 'Smart', 'Hot', 'Special',
            'Premium', 'New', 'VIP', 'Quick', 'Optimal',
            'Intelligent', 'High-speed', 'Prebook', 'Discount', 'Protected',
            'Unlimited', 'Pro', 'Plus', 'Express', 'Standard',
            'Mini', 'Max', 'Eco', 'Flex', 'Classic',
            'Prime', 'Super', 'Extra', 'Ultimate'
        ];

        return [
            'name' => $serviceTypes[array_rand($serviceTypes)] . ' ' . $serviceModifiers[array_rand($serviceModifiers)],
            'provider' => $this->faker->company(),
            'icon' => $this->faker->imageUrl(128, 128, 'business', true, 'Icon'),
            'account_id' => Account::factory(),
            'is_base' => $this->faker->boolean(30),
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
