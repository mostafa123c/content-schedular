<?php

namespace App\Services\Publishing;

use App\Interfaces\PublishingServiceInterface;
use App\Models\Post;
use Illuminate\Support\Facades\Log;

class LinkedInPublishingService implements PublishingServiceInterface
{
    public function publishPost(Post $post): bool
    {
        Log::info('Publishing post to LinkedIn', ['post' => $post]);
        return true;
    }

    public function getPlatformName(): string
    {
        return 'linkedin';
    }
}