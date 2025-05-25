<?php

namespace Tests\Feature\Post;

use App\Models\Platform;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'daily_posts_limit' => 10
        ]);
        $this->token = $this->user->createToken('test-token')->plainTextToken;

        Platform::factory()->create([
            'name' => 'Twitter',
            'type' => 'twitter',
            'character_limit' => 280,
            'is_active' => 1
        ]);

        Platform::factory()->create([
            'name' => 'LinkedIn',
            'type' => 'linkedin',
            'character_limit' => 700,
            'is_active' => 1
        ]);
    }

    public function test_user_can_create_post()
    {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('test-image.jpg');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/posts', [
            'title' => 'Test Post',
            'content' => 'This is a test post content',
            'image_url' => $image,
            'scheduled_time' => now()->addHour()->toDateTimeString(),
            'status' => Post::STATUS_SCHEDULED,
            'platforms' => [1, 2]
        ]);

        $response->assertStatus(200);
        $post = $response->json();
        $this->assertArrayHasKey('id', $post);
        $this->assertArrayHasKey('title', $post);
        $this->assertArrayHasKey('content', $post);
        $this->assertArrayHasKey('image_url', $post);
        $this->assertArrayHasKey('scheduled_time', $post);
        $this->assertArrayHasKey('status', $post);
        $this->assertArrayHasKey('platforms', $post);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'content' => 'This is a test post content',
            'user_id' => $this->user->id,
            'status' => Post::STATUS_SCHEDULED
        ]);

        $postId = $post['id'];
        $this->assertDatabaseHas('platform_post', [
            'post_id' => $postId,
            'platform_id' => 1
        ]);
        $this->assertDatabaseHas('platform_post', [
            'post_id' => $postId,
            'platform_id' => 2
        ]);
    }

    public function test_user_can_get_posts_list()
    {
        Post::factory()->count(5)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/user/posts');

        $response->assertStatus(200);
        $responseData = $response->json();
        $this->assertArrayHasKey('items', $responseData);
        $posts = $responseData['items'];
        $this->assertIsArray($posts);
        $this->assertGreaterThan(0, count($posts));
    }

    public function test_user_can_get_post_details()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id
        ]);

        $post->platforms()->attach([1, 2]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/posts/' . $post->id);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('content', $data);
        $this->assertArrayHasKey('image_url', $data);
        $this->assertArrayHasKey('scheduled_time', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('platforms', $data);
        $this->assertEquals($post->id, $data['id']);
    }

    public function test_user_can_update_post()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Original Title',
            'content' => 'Original Content',
            'status' => Post::STATUS_DRAFT
        ]);

        $post->platforms()->attach([1]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson('/api/v1/posts/' . $post->id, [
            'title' => 'Updated Title',
            'content' => 'Updated Content',
            'scheduled_time' => now()->addHours(2)->toDateTimeString(),
            'status' => Post::STATUS_SCHEDULED,
            'platforms' => [1, 2]
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'content' => 'Updated Content',
            'status' => Post::STATUS_SCHEDULED
        ]);

        $this->assertDatabaseHas('platform_post', [
            'post_id' => $post->id,
            'platform_id' => 2
        ]);
    }

    public function test_user_cannot_update_published_post()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Original Title',
            'content' => 'Original Content',
            'status' => Post::STATUS_PUBLISHED
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson('/api/v1/posts/' . $post->id, [
            'title' => 'Updated Title',
            'content' => 'Updated Content',
            'scheduled_time' => now()->addHours(2)->toDateTimeString(),
            'status' => Post::STATUS_SCHEDULED,
            'platforms' => [1, 2]
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Original Title',
            'content' => 'Original Content',
            'status' => Post::STATUS_PUBLISHED
        ]);
    }

    public function test_user_can_delete_post()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson('/api/v1/posts/' . $post->id);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id
        ]);
    }

    public function test_user_cannot_access_other_users_posts()
    {
        $anotherUser = User::factory()->create();
        $anotherPost = Post::factory()->create([
            'user_id' => $anotherUser->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/posts/' . $anotherPost->id);

        $response->assertStatus(403);
    }
}