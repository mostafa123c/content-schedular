<?php

namespace Database\Factories;

use App\Models\Platform;
use App\Models\PlatformPost;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlatformPost>
 */
class PlatformPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'platform_id' => Platform::factory(),
            'status' => PlatformPost::STATUS_PENDING,
            'response' => null,
            'published_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => PlatformPost::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => PlatformPost::STATUS_FAILED,
            'response' => json_encode(['error' => 'Failed to publish']),
        ]);
    }
}