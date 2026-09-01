<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityEvent extends Model
{
    protected $fillable = [
        'user_id',
        'event_type',
        'ip_address',
        'device_id',
        'user_agent',
        'ip_country',
        'ip_city',
        'flagged',
        'flag_reason',
        'metadata',
    ];

    protected $casts = [
        'flagged' => 'boolean',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
