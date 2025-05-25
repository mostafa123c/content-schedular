<?php

namespace App\Http\Resources\Platform;

use App\Http\Resources\JsonResource;

class UserPlatformResource extends JsonResource
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
            'name' => $this->name,
            'type' => $this->type,
            'is_active_for_user' => $this->is_active_for_user,
            'settingskeys' => $this->whenLoaded('settingskeys', fn() => JsonResource::noResourceCollection($this->settingskeys, ['id', 'key', 'type', 'required'])),
        ];
    }
}
