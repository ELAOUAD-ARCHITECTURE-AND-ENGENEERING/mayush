<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class NotificationType extends Model
{
    protected $fillable = [
        'type',
        'name',
        'image',
        'status',
        'default_text',
        'user_type',
        'addon',
        'category',
        'severity',
        'mandatory_inbox',
        'default_in_app',
        'default_broadcast',
        'default_email',
        'default_sms',
        'default_push',
        'deactivated_at',
    ];

    protected $casts = [
        'mandatory_inbox' => 'boolean',
        'default_in_app' => 'boolean',
        'default_broadcast' => 'boolean',
        'default_email' => 'boolean',
        'default_sms' => 'boolean',
        'default_push' => 'boolean',
        'deactivated_at' => 'datetime',
    ];

    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang ?: App::getLocale();
        $notification_type_translation = $this->notificationTypeTranslations->where('lang', $lang)->first();
        if ($notification_type_translation != null && $notification_type_translation->$field !== null && $notification_type_translation->$field !== $this->$field) {
            return in_array($field, ['name', 'title']) ? translate($notification_type_translation->$field, $lang) : $notification_type_translation->$field;
        }

        return $notification_type_translation != null ? $notification_type_translation->$field : $this->$field;
    }

    public function notificationTypeTranslations()
    {
        return $this->hasMany(NotificationTypeTranslation::class);
    }
}
