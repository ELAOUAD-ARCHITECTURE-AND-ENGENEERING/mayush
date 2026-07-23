<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class NotificationEvent extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Notification audit events are immutable.'));
        static::deleting(fn () => throw new LogicException('Notification audit events cannot be deleted.'));
    }

    public function deliveries()
    {
        return $this->hasMany(NotificationDelivery::class, 'event_id');
    }
}
