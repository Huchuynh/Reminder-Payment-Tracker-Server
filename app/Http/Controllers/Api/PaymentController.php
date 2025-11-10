<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponseTrait;

    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function handleCallbackFromYoutube(string $id, Request $request)
    {
        \Log::info("callback: " . $id);
        try {
            $validated = $request->validate([
                "amount" => "required|numeric",
                "payment_date" => "required|date",
                "status" => "required|string",
            ]);
            $payment = $this->paymentService->handleCallbackFromYoutube($validated, $id);

            return $this->responseSuccess(
                $payment,
                "Payment successful",
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }
}
