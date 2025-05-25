<?php

namespace App\Http\Resources\Post;

use App\Http\Resources\JsonResource;
use Illuminate\Http\Request;

class UpcomingPostsResource extends JsonResource
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
            'title' => $this->title,
            'scheduled_time' => $this->scheduled_time,
            'platforms' => $this->whenLoaded('platforms', fn() => JsonResource::noResourceCollection($this->platforms, ['id', 'name'])),
        ];
    }
}