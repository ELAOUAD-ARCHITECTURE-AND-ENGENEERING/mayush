<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class NotificationCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                if (($data->data['schema_version'] ?? null) === 1) {
                    return app(\App\Services\Notifications\NotificationPresenter::class)->present($data);
                }

                $notificationType = $data->notification_type_id
                    ? get_notification_type($data->notification_type_id, 'id')
                    : null;
                $notifyContent = $notificationType?->getTranslation('default_text')
                    ?: \Illuminate\Support\Str::headline(class_basename($data->type ?: 'Notification'));
                if ($data->type == 'App\Notifications\OrderNotification'){
                    $notifyContent = str_replace('[[order_code]]', $data->data['order_code'], $notifyContent);
                }
                return [
                    'id' => $data->id,
                    "isChecked" => false,
                    'type' => $data->type,
                    'data' => $data->data,
                    'notification_text' => $notifyContent,
                    'image' => $notificationType?->image ? uploaded_asset($notificationType->image) : null,
                    'date' => date("F j Y, g:i a", strtotime($data->created_at))
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
