<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_id' => 'required|exists:accounts,id',
            'service_id' => [
                'required',
                'exists:services,id',
                Rule::unique('subscriptions')
                    ->where(fn ($query) => $query->where('account_id', $this->account_id))
                    ->ignore($this->route('subscription')),
            ],
            "start_date" => "required|date|date_format:Y-m-d H:i:s",
            "end_date" => "required|date|date_format:Y-m-d H:i:s|after:start_date",
            "status" => "required|in:active,expiring,expired,canceled",
            "plan" => "required|string",
            "notes" => "nullable|string",
        ];
    }
}
