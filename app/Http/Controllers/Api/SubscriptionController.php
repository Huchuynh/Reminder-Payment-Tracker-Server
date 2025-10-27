<?php

namespace App\Http\Controllers\Api;

use App\Dto\QueryParamsDto;
use App\Dto\SubscriptionQueryParamsDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
use App\Http\Requests\SubscriptionQueryRequest;
use App\Http\Requests\SubscriptionRequest;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Services\ServiceService;
use App\Services\SubscriptionService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller
{
    use ApiResponseTrait;
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService){
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
        } catch(\Throwable $e) {
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
            $result = $this->subscriptionService->update($id,$request->validated());

            return $this->responseSuccess(
                $result,
                'Subscription successfully updated',
            );
        } catch(\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    public function getInquiryServiceData(string $customer_id) {

        $fakeServices = [
            "YT0987654321" => [
                "customer_id" => $customer_id,
                "customer_name" => "Michael Nguyen",
                "service_name" => "YouTube Premium",
                "provider" => "Google LLC",
                "start_date" => "2025-01-01 00:00:00",
                "end_date" => "2025-12-31 23:59:59",
                "status" => "active",
                "plan" => "Family",
                "note" => ""
            ],
            "NF1122334455" => [
                "customer_id" => $customer_id,
                "customer_name" => "Sophia Tran",
                "service_name" => "Netflix",
                "provider" => "Netflix Singapore Pte. Ltd.",
                "start_date" => "2025-03-10 00:00:00",
                "end_date" => "2026-03-09 23:59:59",
                "status" => "active",
                "plan" => "Premium",
                "note" => "Auto-renew enabled"
            ],
            "SP7766554433" => [
                "customer_id" => $customer_id,
                "customer_name" => "David Pham",
                "service_name" => "Spotify Premium",
                "provider" => "Spotify AB",
                "start_date" => "2025-04-01 00:00:00",
                "end_date" => "2026-04-01 00:00:00",
                "status" => "active",
                "plan" => "Individual",
                "note" => "Linked with Apple ID"
            ],
            "EVN0011223344" => [
                "customer_id" => $customer_id,
                "customer_name" => "Nam Pham",
                "service_name" => "Electricity Bill",
                "provider" => "PowerGrid Energy",
                "start_date" => "2025-10-01 00:00:00",
                "end_date" => "2025-10-31 23:59:59",
                "status" => "active",
                "plan" => "Residential",
                "note" => "Monthly usage: 312 kWh"
            ],
            "WT6677889900" => [
                "customer_id" => $customer_id,
                "customer_name" => "Linh Le",
                "service_name" => "Water Supply",
                "provider" => "AquaFlow Utilities",
                "start_date" => "2025-09-01 00:00:00",
                "end_date" => "2025-09-30 23:59:59",
                "status" => "expired",
                "plan" => "Standard",
                "note" => "Used 12 cubic meters"
            ],
        ];

        if(!array_key_exists($customer_id, $fakeServices))
            return $this->responseNotFound("Customer not found");

        return $this->responseSuccess(
            $fakeServices[$customer_id],
            "Service found successfully"
        ) ;
    }
}
