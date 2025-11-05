<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            'notifiable' => $this->notifiable,
            'notification_data' => $this->data,
            'read_at' => $this->read_at,
            'sent_at' => $this->created_at,
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
