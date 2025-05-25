<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'image_url' => 'uploads/posts/' . $this->faker->uuid() . '.jpg',
            'scheduled_time' => $this->faker->dateTimeBetween('-1 week', '+1 week'),
            'status' => $this->faker->randomElement([
                Post::STATUS_DRAFT,
                Post::STATUS_SCHEDULED,
                Post::STATUS_PUBLISHED
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Post::STATUS_DRAFT,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Post::STATUS_SCHEDULED,
            'scheduled_time' => $this->faker->dateTimeBetween('+1 hour', '+1 week'),
        ]);
    }


    public function published(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Post::STATUS_PUBLISHED,
            'scheduled_time' => $this->faker->dateTimeBetween('-1 week', '-1 hour'),
        ]);
    }
}