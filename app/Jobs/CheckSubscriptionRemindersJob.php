<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Notifications\SubscriptionReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckSubscriptionRemindersJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $subscriptions = Subscription::with('accounts')
            ->where('status', 'active')
            ->get();

        foreach ($subscriptions as $subscription) {
            $account = $subscription->accounts;
            if (!$account || !$account->fcm_token) continue;


        }
    }

    protected function notify($account, $subscription, $message)
    {
        $account->notify(new SubscriptionReminderNotification($account, $subscription, $message));
    }
}
