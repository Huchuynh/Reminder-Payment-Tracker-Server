<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionHistoryService;
use App\Traits\ApiResponseTrait;

class SubscriptionHistoryController extends Controller
{
    use ApiResponseTrait;

    protected SubscriptionHistoryService $subscriptionHistoryService;

    public function __construct(SubscriptionHistoryService $subscriptionService)
    {
        $this->subscriptionHistoryService = $subscriptionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function getBySubscriptionId(string $subscription_id)
    {
        try {
            $histories = $this->subscriptionHistoryService->getBySubscriptionId($subscription_id);

            return $this->responseSuccess(
                $histories,
                'Get subscription histories successfully.'
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }
}
