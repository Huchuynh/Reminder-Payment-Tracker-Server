<?php

namespace App\Jobs;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Notifications\SubscriptionReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class HandleOverdueSubscriptionsJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $subscriptions = Subscription::with('account')
            ->where('status', SubscriptionStatus::EXPIRED)
            ->whereRaw('EXTRACT(EPOCH FROM (NOW() - last_reminded_at)) / 3600 >= reminder_frequency')
            ->get();

        $expiredSubscriptionIds = $subscriptions->pluck('id')->toArray();

        foreach ($subscriptions as $subscription) {
            $subscription->account->notify(new SubscriptionReminderNotification($subscription));

            \Log::info("Sent overdue reminder for subscription ID {$subscription->id}");
        }

        $this->updateSubscriptionLastReminder($expiredSubscriptionIds);
    }

    public function updateSubscriptionLastReminder($subscriptionIds): void
    {
        Subscription::whereIn('id', $subscriptionIds)
            ->update(['last_reminded_at' => now()]);
    }
}
