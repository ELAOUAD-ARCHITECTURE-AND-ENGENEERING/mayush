<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class NotificationType extends Model
{
    protected $fillable = ['type', 'name', 'image', 'status', 'default_text'];

    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang ?: App::getLocale();
        $notification_type_translation = $this->notificationTypeTranslations->where('lang', $lang)->first();
        if ($notification_type_translation != null && $notification_type_translation->$field !== null && $notification_type_translation->$field !== $this->$field) {
            return $notification_type_translation->$field;
        }

        return $notification_type_translation != null ? $notification_type_translation->$field : $this->$field;
    }

    public function notificationTypeTranslations()
    {
        return $this->hasMany(NotificationTypeTranslation::class);
    }
}
