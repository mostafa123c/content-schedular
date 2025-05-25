<?php

namespace App\Services;

use App\Models\Platform;
use App\Models\Post;
use Illuminate\Validation\ValidationException;

class PlatformPostValidatorService
{
    public function validate(Post $post, $platform): void
    {
        $platformModel = Platform::where('type', $platform)->first();
        $requirements = $platformModel->requirements ?? [];
        $characterLimit = $platformModel->character_limit;

        if (mb_strlen($post->content) > $characterLimit) {
            throw ValidationException::withMessages([
                'content' => "Content exceeds character limit of $characterLimit for {$platformModel->name}."
            ]);
        }

        if (($requirements['image_required'] ?? false) && empty($post->image_url)) {
            throw ValidationException::withMessages([
                'image_url' => "{$platformModel->name} requires an image."
            ]);
        }


        if (!($requirements['support_link'] ?? true) && preg_match('/https?:\/\//', $post->content)) {
            throw ValidationException::withMessages([
                'content' => "{$platformModel->name} does not support links in content."
            ]);
        }
    }
}