<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionHistory;
use Illuminate\Database\Seeder;

class SubscriptionHistoryAndPaymentsSeeder extends Seeder
{
    public function run(): void
    {
        // Xóa toàn bộ dữ liệu cũ
//        SubscriptionHistory::truncate();
//        Payment::truncate();
//
        $subscriptions = Subscription::whereHas('service', function ($q) {
            $q->where('is_base', true);
        })->get();

        foreach ($subscriptions as $sub) {
            Payment::factory(500)->create([
                'subscription_id' => $sub->id,
            ]);

            SubscriptionHistory::factory(500)->create([
                'subscription_id' => $sub->id,
            ]);
        }
    }
}
