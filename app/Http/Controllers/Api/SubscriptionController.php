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
}
