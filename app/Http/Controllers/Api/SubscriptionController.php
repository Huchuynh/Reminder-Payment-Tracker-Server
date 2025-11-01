<?php

namespace App\Http\Controllers\Api;

use App\Dto\QueryParamsDto;
use App\Dto\SubscriptionQueryParamsDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSubscriptionRequest;
use App\Http\Requests\RenewSubscriptionRequest;
use App\Http\Requests\SubscriptionQueryRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
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
    public function store(CreateSubscriptionRequest $request)
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
    public function update(UpdateSubscriptionRequest $request, string $id)
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

    public function renew(RenewSubscriptionRequest $request, string $id)
    {
        try {
            $result = $this->subscriptionService->renew($id, $request->validated());

            return $this->responseSuccess(
                $result,
                'Service successfully renewed',
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    public function unsubscribe(string $id)
    {
        try {
            $result = $this->subscriptionService->unsubscribe($id);

            return $this->responseSuccess(
                $result,
                'Service successfully canceled',
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }
}
