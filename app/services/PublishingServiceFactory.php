<?php

namespace App\Services;

use App\Interfaces\PublishingServiceInterface;
use App\Models\PlatformPost;
use App\Models\Post;
use App\Services\Publishing\InstagramPublishingService;
use App\Services\Publishing\LinkedInPublishingService;
use App\Services\Publishing\TwitterPublishingService;
use Exception;

class PublishingServiceFactory
{

    public static function getPublishingService(string $platformName): PublishingServiceInterface
    {
        return match ($platformName) {
            'twitter' => new TwitterPublishingService(),
            'instagram' => new InstagramPublishingService(),
            'linkedin' => new LinkedInPublishingService(),
            default => throw new Exception('Invalid platform name'),
        };
    }

    public static function getSupportedPlatforms(): array
    {
        return ['twitter', 'instagram', 'linkedin'];
    }
}