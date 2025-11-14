<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\SubscriptionHistoryAction;
use App\Models\Payment;
use App\Models\Service;
use App\Models\SubscriptionHistory;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;

class StatisticService
{
    private const PERIOD_CONFIG = [
        'day' => [
            'db_format' => 'YYYY-MM-DD',
            'carbon_format' => 'Y-m-d',
            'interval' => 'P1D',
        ],
        'month' => [
            'db_format' => 'YYYY-MM',
            'carbon_format' => 'Y-m',
            'interval' => 'P1M',
        ],
    ];

    /*Renew Cancel Rate Statistic By Service*/
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

    /*Revenue Statistic By Period*/
    public function getRevenueStatisticByPeriod(array $data)
    {
        try {
            $params = [
                'start_date' => Carbon::parse($data['start_date'])->startOfDay(),
                'end_date' => Carbon::parse($data['end_date'])->endOfDay(),
                'type' => $data['type'],
            ];

            $payments = $this->getPaymentsByPeriod($params, $data['service_id']);

            return $this->fillMissingPeriods($params, $payments);
        } catch (\Throwable $e) {
            \Log::error('Fail to get revenue statistic: ' . $e->getMessage());
            report($e);
            throw new \Exception('Fail to get revenue statistic: ' . $e->getMessage());
        }
    }

    private function getPaymentsByPeriod(array $data, int $service_id)
    {
        $format = self::PERIOD_CONFIG[$data['type']]['db_format'];

        return Payment::query()
            ->selectRaw("TO_CHAR(paid_at, '{$format}') AS period, SUM(amount) AS total")
            ->join('subscriptions', 'subscriptions.id', '=', 'payments.subscription_id')
            ->whereBetween('paid_at', [$data['start_date'], $data['end_date']])
            ->where('subscriptions.service_id', $service_id)
            ->where('payments.status', PaymentStatus::SUCCESS)
            ->groupByRaw("TO_CHAR(paid_at, '{$format}')")
            ->orderBy('period')
            ->get()
            ->keyBy('period');
    }

    private function fillMissingPeriods(array $filter, $payment)
    {
        $config = self::PERIOD_CONFIG[$filter['type']];

        $period = new DatePeriod(
            $filter['start_date'],
            new DateInterval($config['interval']),
            $filter['end_date']
        );

        return collect($period)->map(function ($date) use ($config, $payment) {
            $key = $date->format($config['carbon_format']);
            return [
                'period' => $key,
                'total' => (float)($payment[$key]->total ?? 0),
            ];
        });
    }

    /*Revenue Statistic By Top Service*/
    public function getRevenueStatisticByTopService(array $data)
    {
        try {
            $params = [
                'start_date' => Carbon::parse($data['start_date'])->startOfDay(),
                'end_date' => Carbon::parse($data['end_date'])->endOfDay()
            ];

            $top_service_data = $this->getTopServiceRevenue($params, 2);

            $total_revenue = $this->getTotalRevenue($params);

            $result = $this->calculateOtherGroup($top_service_data, $total_revenue);

            return $result->map(function ($item) {
                return [
                    'service_name' => $item->service_name,
                    'total_revenue' => (float)$item->total_revenue,
                ];
            });
        } catch (\Throwable $e) {
            \Log::error('Fail to get revenue statistic: ' . $e->getMessage());
            report($e);
            throw new \Exception('Fail to get revenue statistic: ' . $e->getMessage());
        }
    }

    private function getTopServiceRevenue(array $data, int $top)
    {
        $query = Payment::query()
            ->selectRaw('
                services.name as service_name,
                SUM(payments.amount) as total_revenue
            ')
            ->join('subscriptions', 'subscriptions.id', '=', 'payments.subscription_id')
            ->join('services', 'services.id', '=', 'subscriptions.service_id')
            ->where('services.is_base', true)
            ->whereBetween('payments.paid_at', [$data['start_date'], $data['end_date']])
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('total_revenue')
            ->limit($top)
            ->get();

        return [
            $query,
            $query->sum('total_revenue'),
        ];
    }

    private function getTotalRevenue(array $data)
    {
        return Payment::join('subscriptions', 'subscriptions.id', '=', 'payments.subscription_id')
            ->join('services', 'services.id', '=', 'subscriptions.service_id')
            ->where('services.is_base', true)
            ->whereBetween('payments.paid_at', [$data['start_date'], $data['end_date']])
            ->sum('payments.amount');
    }

    private function calculateOtherGroup($top_service_data, $total_revenue)
    {
        [$top_service, $top_revenue] = $top_service_data;

        $top_service->push((object)[
            'service_name' => 'Others',
            'total_revenue' => $total_revenue - $top_revenue,
        ]);

        return $top_service;
    }
}
