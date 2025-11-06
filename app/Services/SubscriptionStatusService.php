<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;

class SubscriptionStatusService
{
    public static function evaluateStatus(int $daysLeft, array $threshold): SubscriptionStatus
    {
        if (in_array($daysLeft, $threshold))
            return SubscriptionStatus::EXPIRING;
        if ($daysLeft <= 0)
            return SubscriptionStatus::EXPIRED;

        return SubscriptionStatus::ACTIVE;
    }
}
