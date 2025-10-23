<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QueryParamsRequest extends FormRequest
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
            'search' => 'string|nullable',
            'sort_by' => 'string|nullable',
            'sort_order' => 'string|nullable',
            'limit' => 'integer|nullable',
        ];
    }
}
