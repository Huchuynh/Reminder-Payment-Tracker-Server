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
        return [
            'name' => $this->faker->unique()->word() . ' Service',
            'provider' => $this->faker->company(),
            'icon' => $this->faker->imageUrl(128, 128, 'business', true, 'Icon'),
            'account_id' => Account::factory(),
            'is_base' => $this->faker->boolean(30),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
