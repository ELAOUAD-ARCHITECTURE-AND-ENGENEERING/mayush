<?php

namespace App\Http\Controllers\Api\V2;
use App\Http\Resources\V2\NotificationCollection;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function allNotification()
    {
        auth()->user()->unreadNotifications->markAsRead();
        $notifications = auth()->user()->notifications()->get();
        return new NotificationCollection($notifications);
    }

    public function unreadNotifications(){
        $notifications = auth()->user()->unreadNotifications()->get();
        return response()->json([
            'count' => $notifications->count(),
            'data' => new NotificationCollection($notifications),
        ]);
    }

    public function bulkDelete(Request $request){
        if($request->notification_ids != null){
            $idsArray = $this->notificationIds($request->notification_ids);

            auth()->user()->notifications()->whereIn('id', $idsArray)->delete();
            return $this->success(translate('Notification deleted successfully'));
        }
        return  $this->failed(translate('Something went wrong'));
    }

    public function notificationMarkAsRead(Request $request, $notificationId = null) {
        $notificationId = $notificationId ?: $request->input('notification_id');
        $notification = auth()->user()->notifications()->where('id', $notificationId)->first();

        if (!$notification) {
            return response()->json([
                'result' => false,
                'message' => translate('Notification not found'),
            ], 404);
        }

        // Notification mark as read
        $notification->markAsRead();

        return response()->json([
            'result' => true,
            'type' => $notification->type,
            'data' => $notification->data
        ]);
    }

    private function notificationIds($notificationIds): array
    {
        if (is_array($notificationIds)) {
            return array_values(array_filter($notificationIds));
        }

        $decoded = json_decode($notificationIds, true);
        if (is_array($decoded)) {
            return array_values(array_filter($decoded));
        }

        $idsString = trim((string) $notificationIds, "[] \t\n\r\0\x0B");
        return array_values(array_filter(array_map('trim', explode(',', $idsString))));
    }

}
