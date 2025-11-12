<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\StatisticPeriodFilter;
use App\Enums\SubscriptionHistoryAction;
use App\Models\Payment;
use App\Models\Service;
use App\Models\SubscriptionHistory;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;

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
                    'No data found for this service',
                    null
                )
                : $this->calculateRates($subscriptionIds, $params['service_id']);
        } catch (\Throwable $e) {
            \Log::error('Fail to get renew and cancel statistic: ' . $e->getMessage());
            throw new \Exception('Fail to get renew and cancel statistic: ' . $e->getMessage());
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
                    $q->where('is_base', false);
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
            'Get statistic data successfully.',
            [
                'service_group' => $this->getServiceGroupName($service_id),
                'renewed' => $renewed,
                'canceled' => $canceled,
                'renewed_rate' => $total ? round(($renewed / $total) * 100, 2) : 0,
                'canceled_rate' => $total ? round(($canceled / $total) * 100, 2) : 0,
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
        return $service_id === 0 ? 'Others' : Service::findOrFail($service_id)->name;
    }

    private function statisticResponse(string $message, $data)
    {
        return [
            'message' => $message,
            'data' => $data,
        ];
    }

    public function getRevenueStatisticByPeriod(array $data)
    {
        try {
            [$from, $to] = $this->resolveDateRange($data);

            $payments = Payment::query()
                ->selectRaw('CAST(paid_at AS DATE) AS date, SUM(amount) AS total')
                ->join('subscriptions', 'subscriptions.id', '=', 'payments.subscription_id')
                ->whereBetween('paid_at', [$from, $to])
                ->where('subscriptions.service_id', $data['service_id'])
                ->where('payments.status', PaymentStatus::SUCCESS)
                ->groupByRaw('CAST(paid_at AS DATE)')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            \Log::info($payments->toArray());

            return $this->filMissingDates($from, $to, $payments);
        } catch (\Throwable $e) {
            \Log::error('Fail to get revenue statistic: ' . $e->getMessage());
            report($e);
            throw new \Exception('Fail to get revenue statistic: ' . $e->getMessage());
        }
    }

    private function resolveDateRange(array $data): array
    {
        $filter = $data['filter'] ?? null;
        $from = $data['from'] ?? null;
        $to = $data['to'] ?? null;

        if ($from && $to)
            return [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay(),
            ];

        return match ($filter) {
            StatisticPeriodFilter::THIS_MONTH->value => [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ],
            StatisticPeriodFilter::LAST_MONTH->value => [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ],
            default => [now()->startOfDay(), now()->endOfDay()],
        };
    }

    private function filMissingDates(Carbon $from, Carbon $to, $payment)
    {
        $period = new DatePeriod($from, new DateInterval('P1D'), $to);

        return collect($period)->map(function ($date) use ($from, $to, $payment) {
            $key = $date->format('Y-m-d');
            return [
                'date' => $key,
                'total' => (float)($payment[$key]->total ?? 0),
            ];
        });
    }
}
