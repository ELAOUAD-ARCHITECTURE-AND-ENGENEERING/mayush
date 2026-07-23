<?php

namespace App\Models;

use App\Casts\SafeEncrypted;
use Illuminate\Database\Eloquent\Model;

class NotificationDevice extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'token' => SafeEncrypted::class,
        'last_seen_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];
}
