<?php

namespace App\Jobs;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Notifications\SubscriptionReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class CheckSubscriptionRemindersJob implements ShouldQueue
{
    use Queueable, Dispatchable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $subscriptions = Subscription::with('account')
            ->where('status', SubscriptionStatus::EXPIRING)
            ->get();

        $expiringSubscriptionIds = $subscriptions->pluck('id')->toArray();

        foreach ($subscriptions as $subscription) {
            if (!$subscription->isDueForReminder()) continue;

            $subscription->account->notify(new SubscriptionReminderNotification($subscription));

            \Log::info("Sent reminder for subscription ID {$subscription->id}");
        }

        $this->updateSubscriptionLastReminder($expiringSubscriptionIds);
    }

    public function updateSubscriptionLastReminder($subscriptionIds): void
    {
        Subscription::whereIn('id', $subscriptionIds)
            ->update(['last_reminded_at' => now()]);
    }
}
