<?php

namespace App\Http\Controllers\Api;

use App\Dto\QueryParamsDto;
use App\Http\Controllers\Controller;
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
    public function index(Request $request)
    {
        try {
            $queryParamsDto = new QueryParamsDto(
                $request->search,
                $request->sort_by,
                $request->sort_order,
                $request->limit
            );

            $services = $this->serviceService->get($queryParamsDto);

            return ServiceResource::collection($services);
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServiceRequest $request)
    {
        try {
            $result = $this->serviceService->create([
                "name" => $request->name,
                "provider" => $request->provider,
                "icon" => $request->icon
            ]);

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
            $result = $this->serviceService->update($id,
                [
                    'name' => $request->name,
                    'provider' => $request->provider,
                    'icon' => $request->icon,
                ]
            );

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
