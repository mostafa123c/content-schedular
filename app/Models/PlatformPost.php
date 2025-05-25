<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PlatformPost extends Model
{
    use HasFactory;

    const STATUS_PENDING = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_FAILED = 2;

    protected $fillable = [
        'post_id',
        'platform_id',
        'status',
        'scheduled_at',
        'published_at'
    ];

    protected static function booted()
    {
        static::created(fn() => Cache::forget('platforms_list'));
        static::updated(fn() => Cache::forget('platforms_list'));
        static::deleted(fn() => Cache::forget('platforms_list'));
    }


    public function getStatusAttribute($value)
    {
        return match ($value) {
            self::STATUS_PENDING => 'pending',
            self::STATUS_PUBLISHED => 'published',
            self::STATUS_FAILED => 'failed',
        };
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }
}
