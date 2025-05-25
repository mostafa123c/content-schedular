<?php

namespace Tests\Feature\Platform;

use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PlatformTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private string $token;
    private Platform $platform;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;

        $this->platform = Platform::factory()->create([
            'name' => 'Twitter',
            'type' => 'twitter',
            'character_limit' => 280,
            'requirements' => [
                'support_link' => true,
                'image_required' => false
            ],
            'is_active' => 1
        ]);
    }

    public function test_user_can_get_platforms_list()
    {
        Platform::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/platforms');

        $response->assertStatus(200);
        $platforms = $response->json();
        $this->assertIsArray($platforms);
        $this->assertGreaterThan(0, count($platforms));
        foreach ($platforms as $platform) {
            $this->assertArrayHasKey('id', $platform);
            $this->assertArrayHasKey('name', $platform);
            $this->assertArrayHasKey('type', $platform);
            $this->assertArrayHasKey('character_limit', $platform);
            $this->assertArrayHasKey('requirements', $platform);
        }
    }

    public function test_user_can_get_their_platforms()
    {
        $this->user->platforms()->attach($this->platform->id);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/user/platforms');

        $response->assertStatus(200);
        $platforms = $response->json();
        $this->assertIsArray($platforms);
        $this->assertGreaterThan(0, count($platforms));
        foreach ($platforms as $platform) {
            $this->assertArrayHasKey('id', $platform);
            $this->assertArrayHasKey('name', $platform);
            $this->assertArrayHasKey('type', $platform);
            $this->assertArrayHasKey('is_active_for_user', $platform);
        }
    }

    public function test_user_can_toggle_platform()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/platforms/' . $this->platform->id . '/toggle');

        $response->assertStatus(200);

        $this->assertDatabaseHas('platform_user', [
            'user_id' => $this->user->id,
            'platform_id' => $this->platform->id,
            'is_active' => 1
        ]);
    }

    public function test_user_can_update_platform_settings()
    {
        $this->user->platforms()->attach($this->platform->id, [
            'settings' => json_encode([
                'api_key' => 'old-api-key',
                'api_secret' => 'old-api-secret'
            ])
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson('/api/v1/platforms/' . $this->platform->id . '/settings', [
            'settings' => [
                'api_key' => 'new-api-key',
                'api_secret' => 'new-api-secret'
            ]
        ]);

        $response->assertStatus(200);

        $updatedSettings = json_decode($this->user->platforms()->where('platform_id', $this->platform->id)->first()->pivot->settings, true);
        $this->assertEquals('new-api-key', $updatedSettings['api_key']);
    }
}