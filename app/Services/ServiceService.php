<?php
namespace App\Services;

use App\Dto\QueryParamsDto;
use App\Models\Service;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class ServiceService
{
    public function get(array $params) {
        try {
            $query = Service::query();

            $query->where(function ($query) use ($params) {
                $query->where('name', 'ilike', "%{$params['search']}%")
                    ->orWhere('provider', 'ilike', "%{$params['search']}%");
            });

            $query->orderBy($params['sort_by'], $params['sort_order']);

            return $query->paginate($params['limit']);
        } catch (\Throwable $e) {
            \Log::error("Fail to get service: " . $e->getMessage());
            throw new \Exception("Failed to get service: " . $e->getMessage());
        }
    }

    public function getBase(){
        return Service::where('is_base', true)->get()->load('service_apis');
    }

    public function findById(int $id)
    {
        return Service::findOrFail($id);
    }

    public function create(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {
                $newService = Service::create($data);
                return $newService;
            });
        } catch (\Throwable $e) {
            \Log::error("Failed to create service: " . $e->getMessage());
            throw new \Exception("Failed to create service. " . $e->getMessage());
        }

    }

    public function update(int $id, array $data)
    {
        try {
            $service = $this->findById($id);
            $service->update($data);
            return $service;
        } catch (\Throwable $e) {
            \Log::error("Failed to update service: " . $e->getMessage());
            throw new \Exception("Failed to update service. " . $e->getMessage());
        }
    }

    public function delete(int $id)
    {
        try {
            $this->findById($id)->delete();
            return ['message' => 'Service successfully deleted'];
        } catch (\Throwable $e) {
            \Log::error("Failed to delete service: " . $e->getMessage());
            throw new \Exception("Failed to delete service. " . $e->getMessage());
        }
    }
}
