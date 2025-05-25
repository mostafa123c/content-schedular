<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use App\Models\Platform;
use App\Models\PlatformPost;
use App\Models\Post;
use App\Services\PlatformPostValidatorService;
use App\Services\PublishingServiceFactory;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class PublishPostsJob implements ShouldQueue
{
    public $tries = 1;
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Post $post) {}

    /**
     * Execute the job.
     */
    public function handle(PlatformPostValidatorService $validator): void
    {
        $platforms = $this->post->platforms->pluck('type')->toArray();
        $successCount = 0;

        foreach ($platforms as $platform) {
            try {
                $validator->validate($this->post, $platform);

                $service = PublishingServiceFactory::getPublishingService($platform);
                $success = $service->publishPost($this->post);

                $this->updatePostPlatformStatus(
                    $platform,
                    $success ? PlatformPost::STATUS_PUBLISHED : PlatformPost::STATUS_FAILED
                );

                if ($success) {
                    $successCount++;
                }

                ActivityLog::log($this->post->user_id, 'publish_post', 'Post ' . $this->post->title . ' published on ' . $platform);
            } catch (Exception $e) {
                ActivityLog::log($this->post->user_id, 'publish_post', 'Post ' . $this->post->title . ' failed to publish on ' . $platform . ' as ' . $e->getMessage() . ' update post to publish it later');

                $this->updatePostPlatformStatus($platform, PlatformPost::STATUS_FAILED);
            }
        }

        $this->updatePostStatus($successCount, count($platforms));
    }

    private function updatePostPlatformStatus(string $platform, string $status): void
    {
        $this->post->platforms()->where('type', $platform)->first()
            ->pivot
            ->update(['status' => $status, 'published_at' => $status === PlatformPost::STATUS_PUBLISHED ? now() : null]);
    }

    private function updatePostStatus(int $successCount, int $totalPlatforms): void
    {
        $this->post->update([
            'status' => $successCount === $totalPlatforms ? Post::STATUS_PUBLISHED : Post::STATUS_DRAFT,
            'published_at' => $successCount === $totalPlatforms ? now() : null
        ]);
    }
}
