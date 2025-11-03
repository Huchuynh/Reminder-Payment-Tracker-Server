<?php


namespace App\Jobs;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\SubscriptionHistory;
use App\Services\SubscriptionStatusService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class UpdateSubscriptionStatusJob implements ShouldQueue
{
    use Queueable, Dispatchable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $subscriptions = Subscription::all();

        foreach ($subscriptions as $subscription) {
            $daysLeft = now()->diffInDays($subscription->end_date, false);

            $newStatus = SubscriptionStatusService::evaluateStatus($daysLeft, config("constants.default_threshold"));

            if ($newStatus === $subscription->status) continue;

            if ($newStatus === SubscriptionStatus::EXPIRED) {
                SubscriptionHistory::create([
                    'subscription_id' => $subscription->id,
                    'action' => SubscriptionStatus::EXPIRED,
                ]);
            }

            $subscription->update(['status' => $newStatus]);
        }
    }
}
