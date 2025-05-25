<?php

namespace Tests\Feature\Analytics;

use App\Models\Platform;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_analytics_endpoint_requires_authentication()
    {
        $response = $this->getJson('/api/v1/analytics');
        $response->assertStatus(401);
    }

    public function test_analytics_returns_correct_structure()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/analytics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'posts_count',
                'posts_by_status' => ['draft', 'scheduled', 'published'],
                'posts_per_platform',
                'upcoming_posts',
                'publish_success_rate'
            ]);
    }

    public function test_analytics_counts_are_accurate()
    {
        Post::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'status' => Post::STATUS_DRAFT
        ]);

        Post::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => Post::STATUS_SCHEDULED
        ]);

        Post::factory()->count(4)->create([
            'user_id' => $this->user->id,
            'status' => Post::STATUS_PUBLISHED
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/analytics');

        $response->assertStatus(200)
            ->assertJson([
                'posts_count' => 9,
                'posts_by_status' => [
                    'draft' => 3,
                    'scheduled' => 2,
                    'published' => 4
                ],
                'publish_success_rate' => round((4 / 9) * 100, 2)
            ]);
    }

    public function test_analytics_shows_only_user_posts()
    {
        Post::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $anotherUser = User::factory()->create();
        Post::factory()->count(5)->create([
            'user_id' => $anotherUser->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/analytics');

        $response->assertStatus(200)
            ->assertJson([
                'posts_count' => 3
            ]);
    }

    public function test_analytics_are_cached()
    {
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/analytics');

        $cacheKey = "user_analytics_{$this->user->id}";
        $this->assertTrue(Cache::has($cacheKey));
    }

    public function test_cache_is_cleared_when_post_is_created()
    {
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/analytics');

        Post::factory()->create([
            'user_id' => $this->user->id
        ]);

        $cacheKey = "user_analytics_{$this->user->id}";
        $this->assertFalse(Cache::has($cacheKey));
    }
}