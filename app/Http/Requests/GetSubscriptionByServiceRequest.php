<?php

namespace App\Http\Requests;

class GetSubscriptionByServiceRequest extends QueryParamsRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'status' => 'string|required',
        ]);
    }
}
