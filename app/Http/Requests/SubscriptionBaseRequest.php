<?php

namespace App\Http\Requests;

use App\Enums\AlertChannels;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscriptionBaseRequest extends FormRequest
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
            'start_date' => 'required|date|date_format:Y-m-d H:i:s',
            'end_date' => 'required|date|date_format:Y-m-d H:i:s|after:start_date',
            'plan' => 'required|string',
            'notes' => 'nullable|string',
            'price' => 'required|numeric',
            'alert_thresholds' => 'nullable|integer',
            'reminder_frequency' => 'nullable|integer',
            'reminder_channels' => 'nullable|array',
            'reminder_channels.*' => Rule::in(AlertChannels::validateValues()),
            'last_reminded_at' => 'nullable|date|date_format:Y-m-d H:i:s',
        ];
    }
}
