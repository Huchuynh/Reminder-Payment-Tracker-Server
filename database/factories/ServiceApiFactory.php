<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceApiFactory extends Factory
{
    public function definition(): array
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE'];

        return [
            'service_id' => Service::factory(),
            'base_url' => fake()->url(),
            'method' => fake()->randomElement($methods),
            'token' => fake()->regexify('[A-Za-z0-9]{32}'),
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    public function get(): static
    {
        return $this->state(fn () => ['method' => 'GET']);
    }

    public function post(): static
    {
        return $this->state(fn () => ['method' => 'POST']);
    }

    public function put(): static
    {
        return $this->state(fn () => ['method' => 'PUT']);
    }

    public function delete(): static
    {
        return $this->state(fn () => ['method' => 'DELETE']);
    }
}
