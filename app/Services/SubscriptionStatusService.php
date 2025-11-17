<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;

class SubscriptionStatusService
{
    public static function evaluateStatus(int $daysLeft, ?int $threshold = null): SubscriptionStatus
    {
        $threshold = $threshold ?? 7;
        
        if ($daysLeft <= $threshold) {
            return SubscriptionStatus::EXPIRING;
        }
        if ($daysLeft <= 0) {
            return SubscriptionStatus::EXPIRED;
        }

        return SubscriptionStatus::ACTIVE;
    }
}
