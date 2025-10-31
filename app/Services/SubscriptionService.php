<?php
namespace App\Services;

use App\Dto\QueryParamsDto;
use App\Dto\SubscriptionQueryParamsDto;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function get(array $params) {
        try {
            $query = Subscription::query()->with("service");

            $query->where('account_id', $params['account_id']);
//
            $query->whereHas('service', function($query) use($params) {
                $query->where('name', 'ilike', "%{$params['search']}%");
            });
//
            $query->where('status', $params['status']);
//
            if(isset($params['start_date']) && isset($params['end_date']))
                $query->where('start_date','<=' , $params['end_date'])
                ->where('end_date','>=' , $params['start_date']);
//
            $query->orderBy($params['sort_by'], $params['sort_order']);

            return $query->paginate($params['limit']);
        } catch (\Throwable $e) {
            \Log::error("Fail to get service: " . $e->getMessage());
            throw new \Exception("Failed to get service: " . $e->getMessage());
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
                $newSubscription = Subscription::create($data);
                return $newSubscription;
            });
        } catch (\Throwable $e) {
            \Log::error("Failed to create subscription: " . $e->getMessage());
            throw new \Exception("Failed to create subscription. " . $e->getMessage());
        }

    }

    public function update(int $id, array $data)
    {
        try {
            $subscription = $this->findById($id);
            $subscription->update($data);
            return $subscription;
        } catch (\Throwable $e) {
            \Log::error("Failed to update subscription: " . $e->getMessage());
            throw new \Exception("Failed to update subscription. " . $e->getMessage());
        }
    }
}
