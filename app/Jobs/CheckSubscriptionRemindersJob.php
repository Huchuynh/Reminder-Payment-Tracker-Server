<?php

namespace App\Jobs;

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
        $subscriptions = Subscription::with('accounts')
            ->where('status', 'active')
            ->get();

        foreach ($subscriptions as $subscription) {
            $daysDiff = now()->diffInDays($subscription->end_date, false);
            $threshold = $subscription->alert_thresholds ?? [];

            if (in_array($daysDiff, $threshold) && $subscription->isDueForReminder())
                $type = "expiring";
            else if ($daysDiff == 0 && $subscription->isDueForReminder())
                $type = "expired";
            else if ($daysDiff < 0 && $subscription->isDueForReminder())
                $type = "overdue";
            else continue;

            $subscription->account->notify(new SubscriptionReminderNotification($subscription, $type));

            $subscription->update(["last_reminded_at" => now()]);

            \Log::info("Reminder sent for subscription ID {$subscription->id} ({$type})");
        }
    }
}
