<?php

namespace App\Services;


use App\Enums\SubscriptionHistoryAction;
use App\Models\Service;
use App\Models\SubscriptionHistory;

class StatisticService
{
    public function getRenewCancelStatisticByService(array $params)
    {
        try {
            $subscriptionIds = (int)$params['service_id'] === 0
                ? $this->getSubscriptionIdsByService($params, false)
                : $this->getSubscriptionIdsByService($params, true);

            return $subscriptionIds->isEmpty() ?
                $this->statisticResponse(
                    "No data found for this service",
                    null
                )
                : $this->calculateRates($subscriptionIds, $params['service_id']);
        } catch (\Throwable $e) {
            \Log::error("Fail to get renew and cancel statistic: " . $e->getMessage());
            throw new \Exception("Fail to get renew and cancel statistic: " . $e->getMessage());
        }
    }

    private function getSubscriptionIdsByService(array $data, bool $is_base)
    {
        return SubscriptionHistory::query()
            ->whereBetween('created_at', [$data['from'], $data['to']])
            ->whereHas('subscription.service', function ($query) use ($data, $is_base) {
                $query->when($is_base, function ($q) use ($data) {
                    $q->where('id', $data['service_id']);
                }, function ($q) {
                    $q->where("is_base", false);
                });
            })
            ->distinct('subscription_id')
            ->pluck('subscription_id');
    }

    private function calculateRates($subscriptionIds, int $service_id)
    {
        $renewed = $this->countAction($subscriptionIds, SubscriptionHistoryAction::RENEWED);
        $canceled = $this->countAction($subscriptionIds, SubscriptionHistoryAction::CANCELED);
        $total = $renewed + $canceled;

        return $this->statisticResponse(
            "Get statistic data successfully.",
            [
                "service_group" => $this->getServiceGroupName($service_id),
                "renewed" => $renewed,
                "canceled" => $canceled,
                "renewed_rate" => $total ? round(($renewed / $total) * 100, 2) : 0,
                "canceled_rate" => $total ? round(($canceled / $total) * 100, 2) : 0,
            ]
        );
    }

    private function countAction($subscriptionIds, SubscriptionHistoryAction $action)
    {
        return SubscriptionHistory::whereIn('subscription_id', $subscriptionIds)
            ->where('action', $action)
            ->count();
    }

    private function getServiceGroupName(int $service_id)
    {
        return $service_id === 0 ? "Others" : Service::findOrFail($service_id)->name;
    }

    private function statisticResponse(string $message, $data)
    {
        return [
            "message" => $message,
            "data" => $data
        ];
    }
}
