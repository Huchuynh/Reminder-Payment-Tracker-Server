<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\SubscriptionHistoryAction;
use App\Models\Payment;
use App\Models\Service;
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

            $services = Service::whereIn('id', $data['service_ids'])->pluck('name', 'id');

            $histories = $this->getHistoriesRateByPeriod($params, $data['service_ids']);

            return $this->fillMissingPeriodsByRenewCancelRate(
                $params,
                $histories,
                $services
            );

        } catch (\Throwable $e) {
            \Log::error('Fail to get renew and cancel statistic: ' . $e->getMessage());
            throw new \Exception('Fail to get renew and cancel statistic: ' . $e->getMessage());
        }
    }

    private function getHistoriesRateByPeriod(array $data, array $service_ids)
    {
        $format = self::PERIOD_CONFIG[$data['type']]['db_format'];

        $renewed = SubscriptionHistoryAction::RENEWED->value;
        $canceled = SubscriptionHistoryAction::CANCELED->value;
        $expired = SubscriptionHistoryAction::EXPIRED->value;
        return SubscriptionHistory::query()
            ->selectRaw("
                    TO_CHAR(subscription_history.created_at, '{$format}') AS period,
                    subscriptions.service_id,
                    COUNT(DISTINCT subscription_history.subscription_id) FILTER (WHERE action = '{$renewed}') AS renewed_count,
                    COUNT(DISTINCT subscription_history.subscription_id) FILTER (WHERE action = '{$canceled}' OR action = '{$expired}') AS canceled_count
            ")
            ->join('subscriptions', 'subscriptions.id', '=', 'subscription_history.subscription_id')
            ->whereBetween('subscription_history.created_at', [$data['start_date'], $data['end_date']])
            ->whereIn('subscriptions.service_id', $service_ids)
            ->groupByRaw("TO_CHAR(subscription_history.created_at, '{$format}'), subscriptions.service_id")
            ->orderBy('period')
            ->get()
            ->groupBy(['period', 'service_id']);
    }

    private function getTotalSubscriptionDue(array $data, int $service_id)
    {
        return Subscription::query()
            ->where('service_id', $service_id)
            ->whereBetween('end_date', [$data['start_date'], $data['end_date']])
            ->count();
    }

    private function fillMissingPeriodsByRenewCancelRate(array $filter, $histories, $services)
    {
        [$period, $config] = $this->getMissingPeriods($filter);

        return collect($period)->map(function ($date) use ($config, $filter, $histories, $services) {
            $key = $date->format($config['carbon_format']);
            $row = ['period' => $key];
            [$start, $end] = $this->getStartEndDate($key, $filter['type']);

            foreach ($services as $id => $name) {
                $history = $histories[$key][$id][0] ?? null;
                $renewed = $history->renewed_count ?? 0;
                $canceled = $history->canceled_count ?? 0;
                $total = $this->getTotalSubscriptionDue([
                    'start_date' => $start,
                    'end_date' => $end,
                ], $id);

                $row["{$name}_renew"] = $total === 0 ? 0 : round(($renewed / $total) * 100, 2);
                $row["{$name}_cancel"] = $total === 0 ? 0 : round(($canceled / $total) * 100, 2);
            }

            return $row;
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

            $services = Service::whereIn('id', $data['service_ids'])->pluck('name', 'id');

            $payments = $this->getPaymentsByPeriod($params, $data['service_ids']);

            return $this->fillMissingPeriods($params, $payments, $services);
        } catch (\Throwable $e) {
            \Log::error('Fail to get revenue statistic: ' . $e->getMessage());
            report($e);
            throw new \Exception('Fail to get revenue statistic: ' . $e->getMessage());
        }
    }

    private function getPaymentsByPeriod(array $data, array $service_ids)
    {
        $format = self::PERIOD_CONFIG[$data['type']]['db_format'];

        return Payment::query()
            ->selectRaw("
                TO_CHAR(paid_at, '{$format}') AS period,
                subscriptions.service_id,
                SUM(amount) AS total")
            ->join('subscriptions', 'subscriptions.id', '=', 'payments.subscription_id')
            ->whereBetween('paid_at', [$data['start_date'], $data['end_date']])
            ->whereIn('subscriptions.service_id', $service_ids)
            ->where('payments.status', PaymentStatus::SUCCESS)
            ->groupByRaw("TO_CHAR(paid_at, '{$format}'), subscriptions.service_id")
            ->orderBy('period')
            ->get()
            ->groupBy('period');
    }

    private function fillMissingPeriods(array $filter, $payment, $services)
    {
        [$period, $config] = $this->getMissingPeriods($filter);

        return collect($period)->map(function ($date) use ($config, $payment, $services) {
            $period_key = $date->format($config['carbon_format']);
            $period_data = collect($payment[$period_key] ?? []);
            $row = ['period' => $period_key];

            foreach ($services as $id => $name) {
                $total = $period_data->firstWhere('service_id', $id)->total ?? 0;
                $row[$name] = (float)$total;
            }

            return $row;
        });
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

    private function getStartEndDate(string $time, string $type)
    {
        $config = self::PERIOD_CONFIG[$type];
        $date = Carbon::createFromFormat($config['carbon_format'], $time);
        if ($type === 'day') {
            $start = $date->startOfDay()->toDateTimeString();
            $end = $date->endOfDay()->toDateTimeString();
        } else {
            $start = $date->startOfMonth()->toDateTimeString();
            $end = $date->endOfMonth()->toDateTimeString();
        }
        return [$start, $end];
    }
}
