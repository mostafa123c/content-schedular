<?php

namespace App\Models;

use App\Http\Controllers\API\AnalyticsController;
use App\Models\Traits\HasFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory, HasFilter;

    const STATUS_DRAFT = 0;
    const STATUS_SCHEDULED = 1;
    const STATUS_PUBLISHED = 2;

    protected $fillable = [
        'title',
        'content',
        'image_url',
        'scheduled_time',
        'status',
        'user_id',
    ];

    protected static function booted(): void
    {
        static::created(function (Post $post) {
            ActivityLog::log($post->user_id, 'post_created', 'New Post created successfully');
            AnalyticsController::clearCache($post->user_id);
        });

        static::updated(function (Post $post) {
            ActivityLog::log($post->user_id, 'post_updated', 'Post updated successfully');
            AnalyticsController::clearCache($post->user_id);
        });

        static::deleted(function (Post $post) {
            ActivityLog::log($post->user_id, 'post_deleted', 'Post deleted successfully');
            AnalyticsController::clearCache($post->user_id);
        });
    }

    protected $casts = ['scheduled_time' => 'datetime', 'status' => 'integer'];

    protected $filterCols = ['status'];

    protected $filterSort = ['created_at', 'scheduled_time',];

    protected $filterBetween = ['scheduled_time' => ['from' => 'start_date', 'to' => 'end_date']];


    public function getStatusTextAttribute(): string
    {
        return match ((int) $this->status) {
            self::STATUS_DRAFT => 'draft',
            self::STATUS_SCHEDULED => 'scheduled',
            self::STATUS_PUBLISHED => 'published',
            default => 'draft',
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function platforms(): BelongsToMany
    {
        return $this->belongsToMany(Platform::class, 'platform_post')
            ->withPivot('status', 'response', 'published_at')
            ->withTimestamps();
    }

    public function platformPosts(): HasMany
    {
        return $this->hasMany(PlatformPost::class);
    }

    public function pendingPlatformPosts(): HasMany
    {
        return $this->hasMany(PlatformPost::class)
            ->where('status', PlatformPost::STATUS_PENDING);
    }

    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED)
            ->where('scheduled_time', '>=', now());
    }

    public function scopeDuePosts($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED)
            ->where('scheduled_time', '<=', now());
    }
}
