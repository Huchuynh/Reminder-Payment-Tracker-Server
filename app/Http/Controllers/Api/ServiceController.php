<?php

namespace App\Http\Controllers\Api;

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
        $limit = (int) $request->query('limit', 5);
        $search = $request->query('search');
        $sort_by = $request->query('sort_by', 'created_at');
        $sort_order = $request->query('sort_order', 'desc');

        $services = $this->serviceService->getAll($limit, $search, $sort_by, $sort_order);

        return ServiceResource::collection($services);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServiceRequest $request)
    {
        $result = $this->serviceService->create([
            "name" => $request->name,
            "provider" => $request->provider,
            "icon" => $request->icon
        ]);

        if(isset($result['error'])){
            return $this->responseError(
                $result['message'],
                Response::HTTP_BAD_REQUEST,
                $result['error']
            );
        }

        return $this->responseSuccess(
            $result,
            'Service successfully created',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $result = $this->serviceService->findById($id);

        if(isset($result['error'])){
            return $this->responseError(
                $result['message'],
                Response::HTTP_BAD_REQUEST,
                $result['error']
            );
        }

        return $this->responseSuccess(
            $result,
            'Service successfully found',
            Response::HTTP_OK
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ServiceRequest $request, string $id)
    {
        $result = $this->serviceService->update($id,
            [
                'name' => $request->name,
                'provider' => $request->provider,
                'icon' => $request->icon,
            ]
        );

        if(isset($result['error'])){
            return $this->responseError(
                $result['message'],
                Response::HTTP_BAD_REQUEST,
                $result['error']
            );
        }

        return $this->responseSuccess(
            $result,
            'Service successfully updated',
            Response::HTTP_OK
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $result = $this->serviceService->delete($id);

        if(isset($result['error'])){
            return $this->responseError(
                $result['message'],
                Response::HTTP_BAD_REQUEST,
                $result['error']
            );
        }

        return $this->responseSuccess(
            $result,
            'Service successfully deleted',
            Response::HTTP_OK
        );
    }
}
