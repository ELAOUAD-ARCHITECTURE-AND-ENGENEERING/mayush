<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotificationSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'quiet_hours_enabled' => 'boolean',
        'in_app_enabled' => 'boolean',
        'broadcast_enabled' => 'boolean',
        'email_enabled' => 'boolean',
        'sms_enabled' => 'boolean',
        'push_enabled' => 'boolean',
    ];
}
