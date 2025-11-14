<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
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
            'name' => $this->name,
            'provider' => $this->provider,
            'icon' => $this->icon,
            'is_base' => $this->is_base,
            'subscription_count' => $this->subscriptions_count,
            'active_count' => $this->active_count,
            'expiring_count' => $this->expiring_count,
            'expired_count' => $this->expired_count,
            'canceled_count' => $this->canceled_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
