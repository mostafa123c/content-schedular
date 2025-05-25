<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\JsonResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'remaining_daily_posts' => $this->getRemainingDailyPosts(),
            'created_at' => $this->created_at,
        ];
    }
}