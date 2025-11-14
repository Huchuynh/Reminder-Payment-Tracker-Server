<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Subscription;
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
    public function getRenewCancelStatisticByPeriod(array $data)
    {
        try {
            $params = [
                'start_date' => Carbon::parse($data['start_date'])->startOfDay(),
                'end_date' => Carbon::parse($data['end_date'])->endOfDay(),
                'type' => $data['type'],
            ];

            $histories = $this->getHistoriesRateByPeriod($params, $data['service_id']);

            $total_subscription_due = $this->getTotalSubscriptionDue($params, $data['service_id']);

            return $this->fillMissingPeriodsByRenewCancelRate(
                $params,
                $histories,
                $total_subscription_due,
            );

        } catch (\Throwable $e) {
            \Log::error('Fail to get renew and cancel statistic: ' . $e->getMessage());
            throw new \Exception('Fail to get renew and cancel statistic: ' . $e->getMessage());
        }
    }

    private function getHistoriesRateByPeriod(array $data, int $service_id)
    {
        $format = self::PERIOD_CONFIG[$data['type']]['db_format'];

        return SubscriptionHistory::query()
//            ->selectRaw("
//                    TO_CHAR(subscription_history.created_at, '{$format}') AS period,
//                    COUNT(DISTINCT subscription_history.subscription_id) FILTER (WHERE action = 'renewed') AS renewed_count,
//                    COUNT(DISTINCT subscription_history.subscription_id) FILTER (WHERE action = 'canceled' OR action = 'expired') AS canceled_count
//              ")
            ->selectRaw("
                    TO_CHAR(subscription_history.created_at, '{$format}') AS period,
                    SUM(CASE WHEN action = 'renewed' THEN 1 ELSE 0 END) AS renewed_count,
                    SUM(CASE WHEN action = 'canceled' OR action = 'expired' THEN 1 ELSE 0 END) AS canceled_count
              ")
            ->join('subscriptions', 'subscriptions.id', '=', 'subscription_history.subscription_id')
            ->whereBetween('subscription_history.created_at', [$data['start_date'], $data['end_date']])
            ->where('subscriptions.service_id', $service_id)
            ->groupByRaw("TO_CHAR(subscription_history.created_at, '{$format}')")
            ->orderBy('period')
            ->get()
            ->keyBy('period');
    }

    private function getTotalSubscriptionDue(array $data, int $service_id)
    {
        return Subscription::query()
            ->where('service_id', $service_id)
            ->whereBetween('end_date', [$data['start_date'], $data['end_date']])
            ->count();
    }

    private function fillMissingPeriodsByRenewCancelRate(array $filter, $histories, $total)
    {
        [$period, $config] = $this->getMissingPeriods($filter);

        return collect($period)->map(function ($date) use ($config, $histories, $total) {
            $key = $date->format($config['carbon_format']);

            $renewed = $histories[$key]->renewed_count ?? 0;
            $canceled = $histories[$key]->canceled_count ?? 0;

            return [
                'period' => $key,
                'renew_percent' => $total === 0 ? 100 : round(($renewed / $total) * 100, 2),
                'cancel_percent' => $total === 0 ? 100 : round(($canceled / $total) * 100, 2),
            ];
        });
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
        [$period, $config] = $this->getMissingPeriods($filter);

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

    /*Helper Functions*/
    private function getMissingPeriods(array $data)
    {
        $config = self::PERIOD_CONFIG[$data['type']];

        $interval = new DateInterval($config['interval']);

        $period = new DatePeriod(
            $data['start_date'],
            $interval,
            $data['end_date']
        );

        return [$period, $config];
    }
}
