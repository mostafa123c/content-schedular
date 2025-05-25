<?php

namespace App\Models;

use App\Models\Traits\HasFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory, HasFilter;

    protected $fillable = [
        'user_id',
        'action',
        'description'
    ];

    protected $filterCols = ['action'];

    protected $filterSort = ['created_at'];

    protected $filterBetween = ['created_at' => ['from' => 'start_date', 'to' => 'end_date']];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(int $userId, string $action, string $description)
    {
        return self::create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description
        ]);
    }
}