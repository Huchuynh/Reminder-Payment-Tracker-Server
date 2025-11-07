<?php

namespace App\Services;

use App\Models\SubscriptionHistory;

class SubscriptionHistoryService
{
    public function getBySubscriptionId(string $subscription_id)
    {
        try {
            return SubscriptionHistory::where('subscription_id', $subscription_id)->get();
        } catch (\Throwable $e) {
            \Log::error("Fail to get service: " . $e->getMessage());
            throw new \Exception("Failed to get service: " . $e->getMessage());
        }
    }
}
