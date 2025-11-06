<?php

namespace App\Http\Requests;

class SubscriptionQueryRequest extends QueryParamsRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'account_id' => 'integer|required|exists:accounts,id',
            'start_date' => 'date|nullable',
            'end_date' => 'date|nullable',
            'status' => 'string|required',
        ]);
    }
}
