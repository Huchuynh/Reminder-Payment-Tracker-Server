<?php

namespace App\Http\Requests;

class AccountQueryRequest extends QueryParamsRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'inactive_days' => 'nullable|integer',
            'is_active' => 'required|boolean'
        ]);
    }
}
