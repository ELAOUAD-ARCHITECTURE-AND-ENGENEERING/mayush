<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    protected $guarded = [];

    protected $casts = [
        'in_app_enabled' => 'boolean',
        'broadcast_enabled' => 'boolean',
        'email_enabled' => 'boolean',
        'sms_enabled' => 'boolean',
        'push_enabled' => 'boolean',
    ];
}
