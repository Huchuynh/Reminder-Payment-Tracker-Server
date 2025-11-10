<?php


namespace App\Jobs;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\SubscriptionHistory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class UpdateSubscriptionStatusJob implements ShouldQueue
{
    use Queueable, Dispatchable;
    
    public function handle(): void
    {
        $this->handleExpiringSubscription();
        $this->handleExpiredSubscription();
    }

    public function handleExpiringSubscription(): void
    {
        Subscription::where('status', SubscriptionStatus::ACTIVE)
            ->whereRaw("end_date <= (NOW() + (alert_thresholds * INTERVAL '1 day'))")
            ->update(['status' => SubscriptionStatus::EXPIRING]);
    }

    public function handleExpiredSubscription(): void
    {
        $expiredSubscriptionIds = Subscription::where('status', SubscriptionStatus::EXPIRING)
            ->where('end_date', '<', now())
            ->pluck('id');

        Subscription::whereIn('id', $expiredSubscriptionIds)
            ->update(['status' => SubscriptionStatus::EXPIRED]);

        $histories = $expiredSubscriptionIds->map(fn($id) => [
            'subscription_id' => $id,
            'action' => 'expired',
        ])->toArray();

        SubscriptionHistory::insert($histories);
    }
}
