<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Notifications\SubscriptionReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use SubscriptionStatus;

class CheckSubscriptionRemindersJob implements ShouldQueue
{
    use Queueable, Dispatchable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $subscriptions = Subscription::with('accounts')
            ->where('status', SubscriptionStatus::ACTIVE)
            ->get();

        foreach ($subscriptions as $subscription) {
            $daysLeft = now()->diffInDays($subscription->end_date, false);
            $threshold = $subscription->alert_thresholds ?? [];

            $reminderType = $this->getReminderType($daysLeft, $threshold);

            if (!$subscription->isDueForReminder() || !$reminderType) continue;

            $subscription->account->notify(new SubscriptionReminderNotification($subscription, $reminderType));

            $subscription->update(["last_reminded_at" => now()]);

            \Log::info("Reminder sent for subscription ID {$subscription->id} ({$reminderType->value})");
        }
    }

    private function getReminderType(int $daysLeft, array $threshold): ?SubscriptionStatus
    {
        if (in_array($daysLeft, $threshold))
            return SubscriptionStatus::EXPIRING;
        if ($daysLeft == 0)
            return SubscriptionStatus::EXPIRED;
        if ($daysLeft < 0)
            return SubscriptionStatus::OVERDUE;

        return null;
    }
}
