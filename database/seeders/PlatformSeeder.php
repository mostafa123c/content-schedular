<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platforms = [
            [
                'name' => 'Twitter',
                'type' => 'twitter',
                'character_limit' => 50,
                'requirements' => [
                    'image_required' => false,
                    'support_link' => false,
                ],
            ],
            [
                'name' => 'Instagram',
                'type' => 'instagram',
                'character_limit' => 30,
                'requirements' => [
                    'image_required' => true,
                    'support_link' => false,
                ],
            ],
            [
                'name' => 'LinkedIn',
                'type' => 'linkedin',
                'character_limit' => 60,
                'requirements' => [
                    'image_required' => false,
                    'support_link' => true,
                ],
            ]
        ];

        foreach ($platforms as $platform) {
            Platform::updateOrCreate(
                ['type' => $platform['type']],
                $platform
            );
        }
    }
}
