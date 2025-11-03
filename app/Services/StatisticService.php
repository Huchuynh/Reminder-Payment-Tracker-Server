<?php

namespace App\Services;


use App\Enums\SubscriptionHistoryAction;
use App\Models\SubscriptionHistory;

class StatisticService
{
    public function getRenewCancelStatistic(array $params)
    {
        try {
            $query = SubscriptionHistory::whereBetween('created_at', [$params['from'], $params['to']]);

            $renewed = (clone $query)->where('action', SubscriptionHistoryAction::RENEWED)->count();
            $canceled = (clone $query)->where('action', SubscriptionHistoryAction::CANCELED)->count();

            $total = $renewed + $canceled;

            return [
                'total_actions' => $total,
                'renewed' => $renewed,
                'canceled' => $canceled,
                'renewed_rate' => round(($renewed / $total) * 100),
                'canceled_rate' => round(($canceled / $total) * 100),
            ];
        } catch (\Throwable $e) {
            \Log::error("Fail to get renew and cancel statistic: " . $e->getMessage());
            throw new \Exception("Fail to get renew and cancel statistic: " . $e->getMessage());
        }
    }
}
