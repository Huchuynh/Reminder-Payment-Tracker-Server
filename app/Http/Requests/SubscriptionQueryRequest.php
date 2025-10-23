<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'account_id' => 'integer|required',
            'start_date' => 'date|nullable',
            'end_date' => 'date|nullable',
            'status' => 'string|required',
        ]);
    }
}
