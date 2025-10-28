<?php

namespace App\Http\Controllers\Api;

use App\Dto\QueryParamsDto;
use App\Dto\SubscriptionQueryParamsDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubscriptionQueryRequest;
use App\Http\Requests\SubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Services\SubscriptionService;
use App\Traits\ApiResponseTrait;

class SubscriptionController extends Controller
{
    use ApiResponseTrait;

    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(SubscriptionQueryRequest $request)
    {
        try {
            $subscriptions = $this->subscriptionService->get($request->validated());

            return SubscriptionResource::collection($subscriptions);
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SubscriptionRequest $request)
    {
        try {
            $result = $this->subscriptionService->create($request->validated());

            return $this->responseCreateSuccess(
                $result,
                'Subscription successfully created'
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $result = $this->subscriptionService->findById($id);

            return $this->responseSuccess(
                $result,
                'Subscription successfully found'
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SubscriptionRequest $request, string $id)
    {
        try {
            $result = $this->subscriptionService->update($id, $request->validated());

            return $this->responseSuccess(
                $result,
                'Subscription successfully updated',
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    public function getInquiryYoutubeServiceData(string $customer_id)
    {

        $youtubeSamples = [
            "YT0987654321" => [
                "customer_id" => 101,
                "customer_name" => "Michael Nguyen",
                "service_name" => "YouTube Premium",
                "provider" => "Google LLC",
                "start_date" => "2025-01-01 00:00:00",
                "end_date" => "2025-12-31 23:59:59",
                "status" => "active",
                "plan" => "Family",
                "note" => ""
            ],
            "YT1029384756" => [
                "customer_id" => 102,
                "customer_name" => "Linh Tran",
                "service_name" => "YouTube Premium",
                "provider" => "Google LLC",
                "start_date" => "2025-06-15 09:00:00",
                "end_date" => "2026-06-14 23:59:59",
                "status" => "active",
                "plan" => "Individual",
                "note" => "auto-renew on; card ending 4242"
            ],
            "YT5647382910" => [
                "customer_id" => 103,
                "customer_name" => "Anh Pham",
                "service_name" => "YouTube Premium",
                "provider" => "Google LLC",
                "start_date" => "2024-12-01 00:00:00",
                "end_date" => "2025-11-30 23:59:59",
                "status" => "expired",
                "plan" => "Student",
                "note" => "student verification expired 2025-11-29"
            ],
            "YT0011223344" => [
                "customer_id" => 104,
                "customer_name" => "Hoa Le",
                "service_name" => "YouTube Premium",
                "provider" => "Google LLC",
                "start_date" => "2025-09-01 12:00:00",
                "end_date" => "2025-09-30 23:59:59",
                "status" => "cancelled",
                "plan" => "Individual",
                "note" => "user cancelled during free month"
            ],
            "YT7776665554" => [
                "customer_id" => 105,
                "customer_name" => "Quang Vu",
                "service_name" => "YouTube Premium",
                "provider" => "Google LLC",
                "start_date" => "2025-10-01 00:00:00",
                "end_date" => "2026-09-30 23:59:59",
                "status" => "active",
                "plan" => "Individual",
                "note" => "7-day trial started 2025-10-01"
            ],
            "YT9090909090" => [
                "customer_id" => 106,
                "customer_name" => "Minh Ho",
                "service_name" => "YouTube Premium",
                "provider" => "Google LLC",
                "start_date" => "2023-11-01 00:00:00",
                "end_date" => "2024-10-31 23:59:59",
                "status" => "expired",
                "plan" => "Family",
                "note" => "family manager moved to another account"
            ],
            "YT2468135790" => [
                "customer_id" => 107,
                "customer_name" => "Trang Nguyen",
                "service_name" => "YouTube Premium",
                "provider" => "Google LLC",
                "start_date" => "2025-03-20 08:30:00",
                "end_date" => "2026-03-19 23:59:59",
                "status" => "active",
                "plan" => "Family",
                "note" => "includes 4 family members"
            ],
            "YT1357924680" => [
                "customer_id" => 108,
                "customer_name" => "Bao Ly",
                "service_name" => "YouTube Premium",
                "provider" => "Google LLC",
                "start_date" => "2025-02-01 00:00:00",
                "end_date" => "2025-08-01 23:59:59",
                "status" => "expired",
                "plan" => "Individual",
                "note" => "payment failed since 2025-05-10"
            ],
            "YT3141592653" => [
                "customer_id" => 109,
                "customer_name" => "Huyen Do",
                "service_name" => "YouTube Premium",
                "provider" => "Google LLC",
                "start_date" => "2025-07-01 00:00:00",
                "end_date" => "2026-06-30 23:59:59",
                "status" => "active",
                "plan" => "Student",
                "note" => "verified student until 2026-06-01"
            ],
            "YT8080808080" => [
                "customer_id" => 110,
                "customer_name" => "Tu Nguyen",
                "service_name" => "YouTube Premium",
                "provider" => "Google LLC",
                "start_date" => "2024-05-10 10:00:00",
                "end_date" => "2025-05-09 23:59:59",
                "status" => "cancelled",
                "plan" => "Family",
                "note" => "cancelled due to moving abroad"
            ],
            "YT5554443332" => [
                "customer_id" => 111,
                "customer_name" => "Khanh Vu",
                "service_name" => "YouTube Premium",
                "provider" => "Google LLC",
                "start_date" => "2025-10-10 14:45:00",
                "end_date" => "2026-10-09 23:59:59",
                "status" => "active",
                "plan" => "Family",
                "note" => "promo: NEWYEAR2025 applied"
            ],
            "YT1010101010" => [
                "customer_id" => 112,
                "customer_name" => "Nga Phan",
                "service_name" => "YouTube Premium",
                "provider" => "Google LLC",
                "start_date" => "2025-04-01 00:00:00",
                "end_date" => "2025-04-30 23:59:59",
                "status" => "expired",
                "plan" => "Individual",
                "note" => "14-day trial"
            ]
        ];

        if (!array_key_exists($customer_id, $youtubeSamples))
            return $this->responseNotFound("Customer not found");

        return $this->responseSuccess(
            $youtubeSamples[$customer_id],
            "Service found successfully"
        );
    }
}
