<?php

namespace App\Services;

use App\Enums\AccountRole;
use App\Models\Account;

class AccountService
{
    public function getAccountPaginated(array $params)
    {
        try {
            $query = Account::query();

            $query->where('role', 'user')
                ->where('is_active', $params['is_active'])
                ->where(function ($q) use ($params) {
                    $q->where('full_name', 'ilike', "%{$params['search']}%")
                        ->orWhere('email', 'ilike', "%{$params['search']}%");
                });

            if (isset($params['inactive_days']))
                $query->whereDate('last_active_at', '<=', now()->subDays($params['inactive_days'])->toDateString());

            return $query->paginate($params['limit']);
        } catch (\Throwable $e) {
            \Log::error('Fail to get accounts: ' . $e->getMessage());
            throw new \Exception('Failed to get accounts: ' . $e->getMessage());
        }
    }

    public function getSelectableAccounts()
    {
        return Account::query()
            ->select(['id', 'full_name', 'email'])
            ->where('role', AccountRole::USER)
            ->get();
    }

    public function updateAccountActiveState(array $data)
    {
        try {
            Account::whereIn('id', $data['account_ids'])
                ->update(['is_active' => $data['is_active']]);
        } catch (\Throwable $e) {
            \Log::error('Fail to update account active status: ' . $e->getMessage());
            throw new \Exception('Failed to update account active status: ' . $e->getMessage());
        }
    }
}
