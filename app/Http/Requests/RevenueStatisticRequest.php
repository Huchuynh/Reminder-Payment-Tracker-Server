<?php

namespace App\Http\Requests;

use App\Enums\StatisticPeriodFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RevenueStatisticRequest extends FormRequest
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
            'filter' => ['nullable', Rule::in(StatisticPeriodFilter::validateValues()), 'required_without_all:from,to'],
            'from' => ['nullable', 'date', 'required_without:filter'],
            'to' => ['nullable', 'date', 'after:from', 'required_without:filter'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
        ];
    }
}
