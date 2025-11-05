<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSubscriptionRequest;
use App\Http\Requests\RenewSubscriptionRequest;
use App\Http\Requests\SubscriptionQueryRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Gate;

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
            $subscription = Subscription::findOrFail($id);

            Gate::authorize('update', $subscription);

            $result = $this->subscriptionService->update($subscription, $request->validated());

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
            $subscription = Subscription::findOrFail($id);

            Gate::authorize('renew', $subscription);

            $result = $this->subscriptionService->renew($subscription, $request->validated());

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
            $subscription = Subscription::findOrFail($id);

            Gate::authorize('unsubscribe', $subscription);

            $result = $this->subscriptionService->unsubscribe($subscription);

            return $this->responseSuccess(
                $result,
                'Service successfully canceled',
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    public function pay(string $id)
    {
        try {
            $subscription = Subscription::findOrFail($id);

            Gate::authorize('paid', $subscription);

            $result = $this->subscriptionService->pay($subscription);

            return $this->responseSuccess(
                $result,
                'Service successfully paid',
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }
}
