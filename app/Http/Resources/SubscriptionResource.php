<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'plan' => $this->plan,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'notes' => $this->notes,

            // load service relationship
            'service' => [
                'id' => $this->service->id ?? null,
                'name' => $this->service->name ?? null,
                'provider' => $this->service->provider ?? null,
                'icon' => $this->service->icon ?? null,
            ]
        ];
    }
}
