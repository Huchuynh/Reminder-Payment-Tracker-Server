<?php

namespace App\Http\Controllers\Api;

use App\Dto\QueryParamsDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\QueryParamsRequest;
use App\Http\Requests\ServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Services\ServiceService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServiceController extends Controller
{
    use ApiResponseTrait;
    protected $serviceService;

    public function __construct(ServiceService $serviceService){
        $this->serviceService = $serviceService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(QueryParamsRequest $request)
    {
        try {
            $services = $this->serviceService->get($request->validated());

            return ServiceResource::collection($services);
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    public function getBase() {
        try {
            $services = $this->serviceService->getBase();

            return $this->responseSuccess(
                $services,
                "Get base service successfully."
            );
        } catch(\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServiceRequest $request)
    {
        try {
            $result = $this->serviceService->create($request->validated());

            return $this->responseCreateSuccess(
                $result,
                'Service successfully created'
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
            $result = $this->serviceService->findById($id);

            return $this->responseSuccess(
                $result,
                'Service successfully found'
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ServiceRequest $request, string $id)
    {
        try {
            $result = $this->serviceService->update($id, $request->validated());

            return $this->responseSuccess(
                $result,
                'Service successfully updated',
            );
        } catch(\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $result = $this->serviceService->delete($id);

            return $this->responseSuccess(
                null,
                $result["message"],
            );
        } catch(\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }
}
