<?php

namespace App\Http\Resources\Post;

use App\Http\Resources\JsonResource;
use App\Models\Post;
use Illuminate\Http\Request;

class PostsPerPlatformResource extends JsonResource
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
            'posts_count' => $this->posts_count,
            'posts' => $this->whenLoaded('posts', fn() => JsonResource::noResourceCollection($this->posts, ['id', 'title', 'scheduled_time', 'status_text'])),
        ];
    }
}