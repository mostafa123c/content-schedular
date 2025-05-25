<?php

namespace App\Interfaces;

use App\Models\Post;

interface PublishingServiceInterface
{
    public function publishPost(Post $post): bool;

    public function getPlatformName(): string;
}