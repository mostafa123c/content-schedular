<?php

namespace App\Http\Resources\Platform;

use App\Http\Resources\JsonResource;

class PlatformResource extends JsonResource
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
            'character_limit' => $this->character_limit,
            'requirements' => $this->requirements,
        ];
    }
}
