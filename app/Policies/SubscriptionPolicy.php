<?php

namespace App\Policies;

use App\Enums\AccountRole;
use App\Models\Account;
use App\Models\Subscription;

class SubscriptionPolicy
{
    public function manage(Account $account, Subscription $subscription): bool
    {
        return $subscription->account_id === $account->id;
    }

    public function renew(Account $account, Subscription $subscription): bool
    {
        return $this->manage($account, $subscription);
    }

    public function unsubscribe(Account $account, Subscription $subscription): bool
    {
        return $this->manage($account, $subscription);
    }

    public function paid(Account $account, Subscription $subscription): bool
    {
        return $this->manage($account, $subscription);
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Account $account): bool
    {
        return $account->role === AccountRole::USER;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Account $account, Subscription $subscription): bool
    {
        return $this->manage($account, $subscription);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Account $account): bool
    {
        return $account->role === AccountRole::USER;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Account $account, Subscription $subscription): bool
    {
        return $this->manage($account, $subscription);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Account $account, Subscription $subscription): bool
    {
        return $this->manage($account, $subscription);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Account $account, Subscription $subscription): bool
    {
        return $this->manage($account, $subscription);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Account $account, Subscription $subscription): bool
    {
        return $this->manage($account, $subscription);
    }
}
