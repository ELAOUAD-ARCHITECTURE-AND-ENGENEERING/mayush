<?php

namespace App\Observers;

use App\Models\OrderTrackingHistory;
use App\Utility\NotificationUtility;
use Illuminate\Http\Request;

class OrderTrackingHistoryObserver
{
    /**
     * Handle the OrderTrackingHistory "created" event.
     *
     * @param  \App\Models\OrderTrackingHistory  $history
     * @return void
     */
    public function created(OrderTrackingHistory $history)
    {
        $order = $history->order;
        if ($order && $order->user && get_setting('google_firebase') == 1 && $order->user->device_token != null) {
            $request = new Request();
            $request->device_token = $order->user->device_token;
            $request->title = "Order Update: " . $history->status;
            $request->text = "Your order {$order->code} status has been updated to: " . str_replace('_', ' ', $history->status);
            if ($history->location_name) {
                $request->text .= " at " . $history->location_name;
            }
            $request->type = "order_tracking";
            $request->id = $order->id;
            $request->user_id = $order->user->id;

            NotificationUtility::sendFirebaseNotification($request);
        }
    }
}
