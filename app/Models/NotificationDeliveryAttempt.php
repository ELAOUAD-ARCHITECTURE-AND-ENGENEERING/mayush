<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class NotificationDeliveryAttempt extends Model
{
    protected $guarded = [];

    protected $casts = [
        'retry_at' => 'datetime',
        'occurred_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Notification delivery attempts are append-only.'));
        static::deleting(fn () => throw new LogicException('Notification delivery attempts cannot be deleted.'));
    }

    public function delivery()
    {
        return $this->belongsTo(NotificationDelivery::class, 'delivery_id');
    }
}
