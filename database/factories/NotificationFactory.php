<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    public function definition(): array
    {
        $faker = $this->faker;

        // Lấy 1 subscription bất kỳ
        $subscription = Subscription::inRandomOrder()->first();
        $admin = Account::where('role', 'admin')->first();

        return [
            'type' => 'App\\Notifications\\SubscriptionReminderNotification', // tên notification class
            'notifiable_type' => Account::class,
            'notifiable_id' => $subscription->account_id,
            'data' => [
                'subscription_id' => $subscription->id,
                'service_name' => $subscription->service->name,
                'service_icon' => $subscription->service->icon,
                'message' => $faker->sentence(),
                'end_date' => $subscription->end_date,
            ],
            'sender_id' => $faker->boolean(70) && $admin ? $admin->id : null,
            'read_at' => $faker->boolean(50) ? Carbon::now()->subHours(rand(1, 48)) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

