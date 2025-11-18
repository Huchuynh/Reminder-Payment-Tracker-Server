<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionHistory;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Database\Seeder;

class GenerateRichSubscriptionHistoryAndPaymentsSeeder extends Seeder
{
    public function run(Faker $faker): void
    {
        // Xóa toàn bộ dữ liệu cũ
        SubscriptionHistory::truncate();
        Payment::truncate();

        $subscriptions = Subscription::whereHas('service', function ($q) {
            $q->where('is_base', true);
        })->get();

        $start = strtotime('-6 months');
        $end = time();

        foreach ($subscriptions as $sub) {
            $randomTimestamp = mt_rand($start, $end);
            $paidAt = Carbon::createFromTimestamp($randomTimestamp);

            Payment::factory(100)->create([
                'subscription_id' => $sub->id,
                'paid_at' => $paidAt,
                'created_at' => $paidAt,
                'updated_at' => $paidAt,
            ]);

            SubscriptionHistory::factory(100)->create([
                'subscription_id' => $sub->id,
                'created_at' => $paidAt,
                'updated_at' => $paidAt,
            ]);
        }
    }
}
