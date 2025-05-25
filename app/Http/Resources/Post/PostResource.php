<?php

namespace App\Http\Resources\Post;

use App\Http\Resources\JsonResource;
use App\Models\PlatformPost;
use App\Models\Post;

class PostResource extends JsonResource
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
            'content' => $this->content,
            'image_url' => $this->image_url,
            'scheduled_time' => $this->scheduled_time,
            'status' => $this->getPostStatus($this->status),
            'user' => $this->whenLoaded('user', fn() => JsonResource::noResourceItem($this->user, ['id', 'name'])),
            'platforms' => $this->whenLoaded('platforms', fn() => JsonResource::noResourceCollection($this->platforms, ['id', 'name'])),
            'remaining_daily_posts' => $this->remaining_daily_posts,
            'created_at' => $this->created_at,
        ];
    }

    protected function getPostStatus($status)
    {
        return match ($status) {
            Post::STATUS_DRAFT => 'Draft',
            Post::STATUS_SCHEDULED => 'Scheduled',
            Post::STATUS_PUBLISHED => 'Published',
        };
    }
}