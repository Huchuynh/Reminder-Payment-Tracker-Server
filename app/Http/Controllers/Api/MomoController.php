<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MomoPaymentRequest;
use App\Services\MomoService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class MomoController extends Controller
{
    use ApiResponseTrait;

    protected MomoService $momoService;

    public function __construct(MomoService $momoService)
    {
        $this->momoService = $momoService;
    }

    public function pay(MomoPaymentRequest $request)
    {
        try {

            $result = $this->momoService->createPayment($request->validated());

            \Log::info($result);

            return $this->responseSuccess(
                $result['payUrl'],
                $result['message']
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    public function notify(Request $request, string $id)
    {
        try {
            if (!$this->momoService->verifySignature($request->all())) {
                return $this->responseBadRequest('Invalid signature');
            }

            $response = $this->momoService->notify($request->all(), $id);

            return $this->responseSuccess(
                $response,
                'Received payment successfully'
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }
}
