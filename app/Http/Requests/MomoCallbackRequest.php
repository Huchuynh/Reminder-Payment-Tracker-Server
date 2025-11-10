<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MomoCallbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "amount" => "required|integer",
            "extraData" => "required|string",
            "message" => "required|string",
            "orderId" => "required|string",
            "orderInfo" => "required|string",
            'orderType' => "required|string",
            "partnerCode" => "required|string",
            "payType" => "required|string",
            "requestId" => "required|string",
            "responseTime" => "required|string",
            "resultCode" => "required|string",
            "transId" => "required|string",
            "signature" => "required|string",
        ];
    }
}
