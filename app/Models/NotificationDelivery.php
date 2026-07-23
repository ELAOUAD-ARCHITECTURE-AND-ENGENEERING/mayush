<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationDelivery extends Model
{
    protected $guarded = [];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(NotificationEvent::class, 'event_id');
    }

    public function attempts()
    {
        return $this->hasMany(NotificationDeliveryAttempt::class, 'delivery_id');
    }
}
