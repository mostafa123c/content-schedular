<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformSettingskey extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform_id',
        'key',
        'type',
        'required',
    ];

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }
}
