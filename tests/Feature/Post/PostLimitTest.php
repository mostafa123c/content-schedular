<?php

namespace Tests\Feature\Post;

use App\Models\Platform;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostLimitTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'daily_posts_limit' => 3
        ]);

        $this->token = $this->user->createToken('test-token')->plainTextToken;

        Platform::factory()->create([
            'name' => 'Twitter',
            'type' => 'twitter',
            'is_active' => true
        ]);
    }

    public function test_user_can_create_post_within_limit()
    {
        Post::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'created_at' => now()
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/posts', [
            'title' => 'Test Post',
            'content' => 'Test content',
            'scheduled_time' => now()->addHour()->toDateTimeString(),
            'status' => Post::STATUS_SCHEDULED,
            'platforms' => [1]
        ]);

        $response->assertStatus(200);
        $this->assertEquals(3, Post::where('user_id', $this->user->id)->count());
    }

    public function test_user_cannot_exceed_daily_limit()
    {
        Post::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'created_at' => now()
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/posts', [
            'title' => 'Test Post',
            'content' => 'Test content',
            'scheduled_time' => now()->addHour()->toDateTimeString(),
            'status' => Post::STATUS_SCHEDULED,
            'platforms' => [1]
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'You have reached your daily posts limit',
                'remaining' => 0
            ]);
    }

    public function test_limit_resets_next_day()
    {
        Post::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDay()
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/posts', [
            'title' => 'Test Post',
            'content' => 'Test content',
            'scheduled_time' => now()->addHour()->toDateTimeString(),
            'status' => Post::STATUS_SCHEDULED,
            'platforms' => [1]
        ]);

        $response->assertStatus(200);
    }

    public function test_limit_considers_only_current_day()
    {
        Post::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDay()
        ]);

        Post::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'created_at' => now()
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/posts', [
            'title' => 'Test Post',
            'content' => 'Test content',
            'scheduled_time' => now()->addHour()->toDateTimeString(),
            'status' => Post::STATUS_SCHEDULED,
            'platforms' => [1]
        ]);

        $response->assertStatus(200);

        $this->assertEquals(5, Post::where('user_id', $this->user->id)->count());
        $this->assertEquals(3, Post::where('user_id', $this->user->id)
            ->whereDate('created_at', now())
            ->count());
    }

    public function test_remaining_posts_count_is_correct()
    {
        Post::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'created_at' => now()
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/posts', [
            'title' => 'Test Post',
            'content' => 'Test content',
            'scheduled_time' => now()->addHour()->toDateTimeString(),
            'status' => Post::STATUS_SCHEDULED,
            'platforms' => [1]
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('remaining_daily_posts', 0);
    }
}