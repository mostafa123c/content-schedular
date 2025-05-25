<?php

namespace Database\Factories;

use App\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Platform>
 */
class PlatformFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $platformTypes = ['twitter', 'linkedin', 'instagram'];
        $type = $this->faker->randomElement($platformTypes);

        return [
            'name' => ucfirst($type),
            'type' => $type,
            'character_limit' => $this->faker->numberBetween(100, 1000),
            'is_active' => 1,
            'requirements' => [
                'support_link' => $this->faker->boolean,
                'image_required' => $this->faker->boolean,
            ],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}