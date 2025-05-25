<?php

namespace App\Http\Resources\ActivityLog;

use App\Http\Resources\JsonResource;

class ActivityLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'description' => $this->description,
            'user' => $this->whenLoaded('user', fn($user) => JsonResource::noResourceItem($user, ['id', 'name'])),
            'created_at' => $this->created_at,
        ];
    }
}