<?php

namespace App\Services;

use App\Models\Account;

class AccountService
{
    public function getAccountWithSubscription(array $params)
    {
        try {
            $query = Account::query();

            $query->select([
                'id',
                'full_name',
                'email',
                'phone',
                'avatar',
                'created_at',
                'updated_at'
            ])
                ->withCount('subscriptions')
                ->with([
                    'subscriptions' => function ($q) {
                        $q->select([
                            'id',
                            'account_id',
                            'service_id',
                            'start_date',
                            'end_date',
                            'status',
                            'plan',
                            'created_at',
                            'updated_at'
                        ])
                            ->with([
                                'service' => function ($s) {
                                    $s->select([
                                        'id',
                                        'name',
                                        'provider',
                                        'icon',
                                        'created_at',
                                        'updated_at',
                                        'deleted_at'
                                    ]);
                                }
                            ]);
                    }
                ]);

            $query->where('role', 'user')
                ->where(function ($q) use ($params) {
                    $q->where('full_name', 'ilike', "%{$params['search']}%")
                        ->orWhere('email', 'ilike', "%{$params['search']}%");
                });

            $query->whereHas('subscriptions', function ($q) use ($params) {
                $q->where('status', $params['status']);
            });

            return $query->paginate($params['limit']);
        } catch (\Throwable $e) {
            \Log::error("Fail to get accounts: " . $e->getMessage());
            throw new \Exception("Failed to get accounts: " . $e->getMessage());
        }
    }


    public function findById(int $id)
    {

    }

}
