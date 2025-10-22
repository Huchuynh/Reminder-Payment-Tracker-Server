<?php

namespace App\Http\Controllers\Api;

use App\Dto\QueryParamsDto;
use App\Dto\SubscriptionQueryParamsDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
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
    public function index(Request $request)
    {
        try {
            $params = new SubscriptionQueryParamsDto(
                $request->search ?? '',
                $request->sort_by,
                $request->sort_order,
                $request->limit,
                $request->account_id,
                $request->start_date,
                $request->end_date,
                $request->status
            );

            $services = $this->subscriptionService->get($params);

            return SubscriptionResource::collection($services);
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
            $result = $this->subscriptionService->create([
                "account_id" => $request->account_id,
                "service_id" => $request->service_id,
                "start_date" => $request->start_date,
                "end_date" => $request->end_date,
                "status" => $request->status,
                "plan" => $request->plan,
                "notes" => $request->notes
            ]);

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
    public function update(Request $request, string $id)
    {
        try {
            $result = $this->subscriptionService->update($id,
                [
                    "account_id" => $request->account_id,
                    "service_id" => $request->service_id,
                    "start_date" => $request->start_date,
                    "end_date" => $request->end_date,
                    "status" => $request->status,
                    "plan" => $request->plan,
                    "notes" => $request->notes
                ]
            );

            return $this->responseSuccess(
                $result,
                'Subscription successfully updated',
            );
        } catch(\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }
}
