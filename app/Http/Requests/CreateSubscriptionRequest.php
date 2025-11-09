<?php

namespace App\Http\Requests;

use App\Enums\SubscriptionStatus;
use Illuminate\Validation\Rule;

class CreateSubscriptionRequest extends SubscriptionBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'account_id' => 'required|exists:accounts,id',
            'service_id' => [
                'required',
                'exists:services,id',
                Rule::unique('subscriptions')
                    ->where(fn($query) => $query->where('account_id', $this->account_id))
                    ->ignore($this->route('subscription')),
            ],
            "status" => ['required', Rule::in(SubscriptionStatus::remindableValues())]
        ]);
    }
}
