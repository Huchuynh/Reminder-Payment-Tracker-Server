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
            'price' => $this->price,
            'service' => $this->whenLoaded('service', function () {
                return [
                    'id' => $this->service->id,
                    'name' => $this->service->name,
                    'provider' => $this->service->provider,
                    'icon' => $this->service->icon,
                    'is_base' => $this->service->is_base,
                ];
            }),
            'account' => $this->whenLoaded('account', function () {
                return [
                    'id' => $this->account->id,
                    'full_name' => $this->account->full_name,
                    'email' => $this->account->email,
                    'avatar' => $this->account->avatar,
                ];
            }),
        ];
    }
}
