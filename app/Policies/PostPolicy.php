<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{

    public function view(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    public function repost(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id && $post->status !== Post::STATUS_PUBLISHED;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}
