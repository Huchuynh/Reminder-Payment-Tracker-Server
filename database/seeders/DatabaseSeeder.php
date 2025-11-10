<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServiceApi;
use App\Models\Subscription;
use App\Models\SubscriptionHistory;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        $admin = Account::factory()->create([
            'role' => 'admin',
            'full_name' => 'Chris Mach',
            'email' => 'chris.mach.gos@gmail.com',
        ]);

        $users = Account::factory(5)->create();

        $servicesPerUser = 1000;
        $servicesForAdmin = 10;

        for ($i = 0; $i < $servicesForAdmin; $i++) {
            $service = Service::factory()->create([
                'account_id' => $admin->id,
                'is_base' => true,
            ]);

            ServiceApi::factory(rand(1, 3))->create([
                'service_id' => $service->id,
            ]);

        }

        foreach ($users as $user) {
            $faker->unique(true);

            for ($i = 0; $i < $servicesPerUser; $i++) {
                $service = Service::factory()->create([
                    'account_id' => $user->id,
                    'is_base' => false,
                ]);

                ServiceApi::factory(rand(1, 3))->create([
                    'service_id' => $service->id,
                ]);

                $subscription = Subscription::factory()->create([
                    'account_id' => $user->id,
                    'service_id' => $service->id,
                ]);

                Payment::factory(rand(1, 100))->create([
                    'subscription_id' => $subscription->id,
                ]);

                SubscriptionHistory::factory(rand(1, 100))->create([
                    'subscription_id' => $subscription->id,
                ]);
            }

            for ($n = 0; $n < 100; $n++) {
                Notification::factory()->create([
                    'sender_id' => rand(0, 1) ? $admin->id : null,
                    'notifiable_id' => $user->id, // nếu bạn có trường recipient_id
                ]);
            }
        }

        $this->command->info("Seeding completed: admin {$servicesForAdmin} services, " .
            count($users) . " users x {$servicesPerUser} services each.");
    }
}
