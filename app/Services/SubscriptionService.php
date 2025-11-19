<?php

namespace App\Services;

use App\Enums\SubscriptionHistoryAction;
use App\Enums\SubscriptionStatus;
use App\Models\Account;
use App\Models\Subscription;
use App\Models\SubscriptionHistory;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function get(array $params)
    {
        try {
            $query = Subscription::query()->with('service');

            $query->where('account_id', $params['account_id']);

            $query->whereHas('service', function ($query) use ($params) {
                $query->where('name', 'ilike', "%{$params['search']}%");
            });

            $query->where('status', $params['status']);

            if (isset($params['start_date']) && isset($params['end_date'])) {
                $query->where('start_date', '>=', $params['start_date'])
                    ->where('end_date', '<=', $params['end_date']);
            }

            $query->orderBy($params['sort_by'], $params['sort_order']);

            Account::updateLastActiveAt();

            return $query->paginate($params['limit']);
        } catch (\Throwable $e) {
            \Log::error('Fail to get service: ' . $e->getMessage());
            throw new \Exception('Failed to get service: ' . $e->getMessage());
        }
    }

    public function getSubscriptionByServiceId(array $params, int $service_id)
    {
        try {
            $query = Subscription::query()->with('account');

            $query->where('service_id', $service_id);

            $query->whereHas('account', function ($query) use ($params) {
                $query->where('is_active', true);
                $query->where('full_name', 'ilike', "%{$params['search']}%")
                    ->orWhere('email', 'ilike', "%{$params['search']}%");
            });

            $query->where('status', $params['status']);

            return $query->paginate($params['limit']);
        } catch (\Throwable $e) {
            \Log::error('Fail to get service: ' . $e->getMessage());
            throw new \Exception('Failed to get service: ' . $e->getMessage());
        }
    }

    public function findById(int $id)
    {
        return Subscription::findOrFail($id)->load('service');
    }

    public function create(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {
                $dayLeft = now()->diffInDays($data['end_date']);
                $newData = array_merge($data, [
                    'status' => SubscriptionStatusService::evaluateStatus($dayLeft, $data['alert_thresholds'])
                ]);

                $newSubscription = Subscription::create($newData);
                return $newSubscription;
            });
        } catch (\Throwable $e) {
            \Log::error('Failed to create subscription: ' . $e->getMessage());
            throw new \Exception('Failed to create subscription. ' . $e->getMessage());
        }

    }

    public function update(string $id, array $data)
    {
        try {
            $dayLeft = now()->diffInDays($data['end_date']);
            $newData = array_merge($data, [
                'status' => SubscriptionStatusService::evaluateStatus($dayLeft, $data['alert_thresholds']),
            ]);

            $subscription = Subscription::findOrFail($id);
            $subscription->update($newData);

            return $subscription;
        } catch (\Throwable $e) {
            \Log::error('Failed to update subscription: ' . $e->getMessage());
            throw new \Exception('Failed to update subscription. ' . $e->getMessage());
        }
    }

    public function unsubscribe(string $id)
    {
        try {
            $subscription = Subscription::findOrFail($id);
            $subscription->update(['status' => SubscriptionStatus::CANCELED]);

            SubscriptionHistory::create([
                'subscription_id' => $subscription->id,
                'action' => SubscriptionHistoryAction::CANCELED,
            ]);

            return $subscription;
        } catch (\Throwable $e) {
            \Log::error('Failed to cancel service: ' . $e->getMessage());
            throw new \Exception('Failed to cancel service. ' . $e->getMessage());
        }
    }

    public function mark_as_paid(string $id)
    {
        try {
            $subscription = Subscription::findOrFail($id);
            $subscription->update(['status' => SubscriptionStatus::PAID]);

            return $subscription;
        } catch (\Throwable $e) {
            \Log::error('Failed to mark as paid service: ' . $e->getMessage());
            throw new \Exception('Failed to mark as paid service. ' . $e->getMessage());
        }
    }
}
