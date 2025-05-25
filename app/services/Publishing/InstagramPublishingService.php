<?php

namespace App\Services\Publishing;

use App\Interfaces\PublishingServiceInterface;
use App\Models\Post;
use Illuminate\Support\Facades\Log;

class InstagramPublishingService implements PublishingServiceInterface
{
    public function publishPost(Post $post): bool
    {
        Log::info('Publishing post to Instagram', ['post' => $post]);
        return true;
    }

    public function getPlatformName(): string
    {
        return 'instagram';
    }
}