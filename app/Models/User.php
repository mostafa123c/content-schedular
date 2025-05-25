<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'daily_posts_limit',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            ActivityLog::log($user->id, 'user_registered', 'User registered successfully');
        });

        static::updated(function (User $user) {
            if ($user->wasChanged('email') || $user->wasChanged('name')) {
                ActivityLog::log($user->id, 'user_updated', 'User updated his profile successfully');
            } else if ($user->isDirty('password')) {
                ActivityLog::log($user->id, 'user_updated', 'User updated his password successfully');
            }
        });
    }


    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function platforms(): BelongsToMany
    {
        return $this->belongsToMany(Platform::class)
            ->withPivot('is_active', 'settings')
            ->withTimestamps();
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }


    public function getRemainingDailyPosts(): int
    {
        $today = now()->startOfDay();
        $postsCount = $this->posts()
            ->where('created_at', '>=', $today)
            ->count();

        return max(0, $this->daily_posts_limit - $postsCount);
    }
}