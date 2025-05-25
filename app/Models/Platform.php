<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Platform extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'character_limit',
        'requirements',
        'is_active',
    ];


    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected $casts = [
        'requirements' => 'array',
    ];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'platform_post')
            ->withPivot('status', 'response', 'published_at')
            ->withTimestamps();
    }

    public function settingskeys(): HasMany
    {
        return $this->hasMany(PlatformSettingskey::class);
    }


    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'platform_user')
            ->withPivot('is_active', 'settings')
            ->withTimestamps();
    }
}
