<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Notification;
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

        // 1️⃣ Tạo admin
        $admin = Account::factory()->create([
            'role' => 'admin',
            'email' => 'tuanhaomach@gmail.com'
        ]);

        // 2️⃣ Tạo 9 user
        $users = Account::factory(4)->create();

        $allAccounts = $users->push($admin);

        foreach ($allAccounts as $account) {

            $faker->unique(true); // reset unique cho mỗi account
            $services = [];

            // 3️⃣ Tạo 1000 service cho mỗi account
            for ($i = 0; $i < 1000; $i++) {
                $service = Service::factory()->create([
                    'account_id' => $account->id,
                    'is_base' => $account->role === 'admin' ? (rand(0, 1) ? true : false) : false,
                ]);

                // Tạo 1-3 service_api cho service
                ServiceApi::factory(rand(1, 3))->create([
                    'service_id' => $service->id,
                ]);

                $services[] = $service;
            }

            // 4️⃣ Nếu là user, tạo subscription
            if ($account->role !== 'admin') {
                foreach ($services as $service) {


                    $subscription = Subscription::factory()->create([
                        'account_id' => $account->id,
                        'service_id' => $service->id,
                    ]);

                    // 5️⃣ Subscription history 1-3 bản
                    SubscriptionHistory::factory(rand(1, 3))->create([
                        'subscription_id' => $subscription->id,
                    ]);
                }
            }

            // 6️⃣ Notifications
            for ($n = 0; $n < 50; $n++) {
                Notification::factory()->create([
                    'sender_id' => rand(0, 1) ? $admin->id : null,
                ]);
            }
        }
    }
}
