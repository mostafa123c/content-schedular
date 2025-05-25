<?php

namespace Tests\Feature\Publishing;

use App\Jobs\PublishPostsJob;
use App\Models\Platform;
use App\Models\PlatformPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PostPublishingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Platform $platform;
    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->user = User::factory()->create();

        $this->platform = Platform::factory()->create([
            'name' => 'Twitter',
            'type' => 'twitter',
            'is_active' => true
        ]);

        $this->post = Post::factory()->create([
            'user_id' => $this->user->id,
            'status' => Post::STATUS_SCHEDULED,
            'scheduled_time' => now()->addHour()
        ]);

        $this->post->platforms()->attach($this->platform->id);
    }

    public function test_scheduled_post_is_queued_for_publishing_and_logged()
    {
        $this->post->update(['scheduled_time' => now()->subMinute()]);

        $this->artisan('process:due-posts');

        Queue::assertPushed(PublishPostsJob::class, function ($job) {
            return $job->post->id === $this->post->id;
        });
    }

    public function test_future_scheduled_post_is_not_queued()
    {
        $this->post->update(['scheduled_time' => now()->addHour()]);

        $this->artisan('process:due-posts');

        Queue::assertNotPushed(PublishPostsJob::class);
    }

    public function test_publishing_fails_if_platform_character_limits_are_exceeded()
    {
        $this->platform->update(['character_limit' => 10]);

        $this->post->update(['content' => 'This content is too long for the platform']);

        $job = new PublishPostsJob($this->post);
        $job->handle(app()->make('App\Services\PlatformPostValidatorService'));

        $this->post->refresh();

        $platformPost = $this->post->platforms->first()->pivot;
        $this->assertEquals(PlatformPost::STATUS_FAILED, $platformPost->status);
    }
}
