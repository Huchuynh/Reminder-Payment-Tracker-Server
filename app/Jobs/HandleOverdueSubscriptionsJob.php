<?php


namespace App\Jobs;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\SubscriptionHistory;
use App\Notifications\SubscriptionReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class HandleOverdueSubscriptionsJob implements ShouldQueue
{
    use Queueable, Dispatchable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $subscriptions = Subscription::with('account')
            ->where('status', SubscriptionStatus::OVERDUE)
            ->get();

        foreach ($subscriptions as $subscription) {
            if (!$subscription->isDueForReminder()) continue;

            $subscription->account->notify(new SubscriptionReminderNotification($subscription, SubscriptionStatus::OVERDUE));

            $subscription->update(["last_reminded_at" => now()]);

            SubscriptionHistory::create([
                'subscription_id' => $subscription->id,
                'action' => SubscriptionStatus::OVERDUE,
            ]);

            \Log::info("Sent overdue reminder for subscription ID {$subscription->id}");
        }
    }
}
