<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class RevenueStatisticRequest extends StatisticBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'type' => ['required', 'string', Rule::in(['day', 'month'])],
            'service_id' => ['required', 'integer', 'exists:services,id'],
        ]);
    }
}
