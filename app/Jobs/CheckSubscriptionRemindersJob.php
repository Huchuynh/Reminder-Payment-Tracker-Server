<?php

namespace App\Jobs;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Notifications\SubscriptionReminderNotification;
use App\Services\SubscriptionStatusService;
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
            ->whereIn('status', [
                SubscriptionStatus::ACTIVE,
                SubscriptionStatus::EXPIRING
            ])
            ->get();

        foreach ($subscriptions as $subscription) {
            $daysLeft = now()->diffInDays($subscription->end_date, false);
            $threshold = $subscription->alert_thresholds ?? [];

            $reminderType = SubscriptionStatusService::evaluateStatus($daysLeft, $threshold);

            if (!$subscription->isDueForReminder() || $reminderType === SubscriptionStatus::ACTIVE) continue;

            $subscription->account->notify(new SubscriptionReminderNotification($subscription, $reminderType));

            $subscription->update(["last_reminded_at" => now()]);

            \Log::info("Reminder sent for subscription ID {$subscription->id} ({$reminderType->value})");
        }
    }
}
