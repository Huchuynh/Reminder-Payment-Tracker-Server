<?php
namespace App\Services;

use App\Models\Service;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class ServiceService
{
    public function getAll(
        int $limit,
        ?string $search = null,
        string $sort_by = 'created_at',
        string $sort_order = 'desc'
    ) {
        $query = Service::query()->whereNull('deleted_at');

        // search insensitive-case
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'ilike', "%{$search}%")
                    ->orWhere('provider', 'ilike', "%{$search}%");
            });
        }

        // validate sort column
        $allowedSorts = ['created_at', 'updated_at'];
        if (!in_array($sort_by, $allowedSorts)) {
            $sort_by = 'created_at';
        }

        // validate sort order
        $sort_order = strtolower($sort_order) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sort_by, $sort_order);

        return $query->paginate($limit);
    }


    public function findById(int $id)
    {
        try {
            return Service::findOrFail($id);
        } catch (\Throwable $e) {
            \Log::error("Service not found: " . $e->getMessage());
            return [
                "message" => "Service not found",
                'error' => $e->getMessage()
            ];
        }
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
            return [
                'message' => 'Failed to create service',
                'error' => $e->getMessage()
            ];
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
            return [
                'message' => 'Failed to update service',
                'error' => $e->getMessage()
            ];
        }
    }

    public function delete(int $id)
    {
        try {
            $service = $this->findById($id);
            $service->delete();
            return $service;
        } catch (\Throwable $e) {
            \Log::error("Failed to delete service: " . $e->getMessage());
            return [
                'message' => 'Failed to delete service',
                'error' => $e->getMessage()
            ];
        }
    }
}
