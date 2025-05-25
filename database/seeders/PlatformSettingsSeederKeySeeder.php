<?php

namespace Database\Seeders;

use App\Models\Platform;
use App\Models\PlatformSettingskey;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlatformSettingsSeederKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $keys = [
            'twitter' => [
                ['key' => 'api_key', 'type' => 'text', 'required' => true],
                ['key' => 'api_secret', 'type' => 'text', 'required' => true],
                ['key' => 'twitter_handle', 'type' => 'text', 'required' => false],
            ],
            'instagram' => [
                ['key' => 'api_key', 'type' => 'text', 'required' => true],
                ['key' => 'api_secret', 'type' => 'text', 'required' => true],
                ['key' => 'username', 'type' => 'text', 'required' => false],
            ],
            'linkedin' => [
                ['key' => 'api_key', 'type' => 'text', 'required' => true],
                ['key' => 'api_secret', 'type' => 'text', 'required' => true],
                ['key' => 'profile_url', 'type' => 'url', 'required' => false],
            ]
        ];

        foreach ($keys as $platformType => $platformKeys) {
            $platform = Platform::where('type', $platformType)->first();

            if (!$platform) {
                continue;
            }

            foreach ($platformKeys as $keyData) {
                PlatformSettingsKey::updateOrCreate(
                    [
                        'platform_id' => $platform->id,
                        'key' => $keyData['key'],
                    ],
                    [
                        'type' => $keyData['type'],
                        'required' => $keyData['required'],
                    ]
                );
            }
        }
    }
}
