<?php

namespace App\Utility;

use App\Mail\InvoiceEmailManager;
use App\Models\User;
use App\Models\SmsTemplate;
use App\Http\Controllers\OTPVerificationController;
use App\Models\EmailTemplate;
use Mail;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OrderNotification;
use App\Models\FirebaseNotification;
use App\Services\Notifications\NotificationDispatcher;

class NotificationUtility
{
    public static function sendOrderPlacedNotification($order, $request = null)
    {
        if (config('notifications_v2.enabled')) {
            self::sendNotification($order, 'placed');
            return;
        }

        $lock = \Illuminate\Support\Facades\Cache::lock('order_placed_notify_'.$order->id, 30);
        if (!$lock->get()) {
            \Log::info("Duplicate order notification dispatch suppressed for order {$order->id}");
            return;
        }

        try {
            //sends email to Customer, Seller and Admin with the invoice pdf attached
            $adminId = get_admin()->id;
            $userIds = array($order->seller_id);
            if($order->user->email != null){
                array_push($userIds, $order->user_id);
            }
            if ($order->seller_id != $adminId) {
                array_push($userIds, $adminId);
            }
        $users = User::findMany($userIds);
        foreach($users as $user){
            $emailIdentifier = 'order_placed_email_to_'.$user->user_type;
            $emailTemplate = EmailTemplate::whereIdentifier($emailIdentifier)->first();

            if($emailTemplate != null && $emailTemplate->status == 1){
                $emailSubject = $emailTemplate->subject;
                $emailSubject = str_replace('[[order_code]]', $order->code, $emailSubject);

                $array['view']      = 'emails.invoice';
                $array['subject']   = $emailSubject;
                $array['order']     = $order;
                if($emailTemplate->status == 1){
                    try {
                        Mail::to($user->email)->queue(new InvoiceEmailManager($array));
                    } catch (\Exception $e) {
                        \Log::error("Email sending failed (sendOrderPlacedNotification): " . $e->getMessage());
                    }
                }
            }   
        }

        if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'order_placement')->first()->status == 1) {
            try {
                $otpController = new OTPVerificationController;
                $otpController->send_order_code($order);
            } catch (\Exception $e) {

            }
        }

        //sends Notifications to user
        self::sendNotification($order, 'placed');
        if ($request !=null && get_setting('google_firebase') == 1 && $order->user->device_token != null) {
            $request->device_token = $order->user->device_token;
            $request->title = "Order placed !";
            $request->text = "An order {$order->code} has been placed";

            $request->type = "order";
            $request->id = $order->id;
            $request->user_id = $order->user->id;

            self::sendFirebaseNotification($request);
        }
        } catch (\Exception $e) {
            \Log::error("Failed to process order placed notifications: " . $e->getMessage());
        }
    }

    public static function sendNotification($order, $order_status)
    {
        if (config('notifications_v2.enabled')) {
            $admin = get_admin();
            $recipientIds = collect([
                $order->user_id,
                $order->seller_id,
                $admin?->id,
            ])->filter()->unique()->values();
            $normalized = strtolower(str_replace([' ', '-'], '_', (string) $order_status));
            $eventKey = match ($normalized) {
                'placed' => 'order.placed',
                'confirmed', 'processed', 'processing' => 'order.confirmed',
                'cancelled', 'canceled' => 'order.cancelled',
                'shipped', 'on_delivery', 'on_the_way', 'in_transit', 'out_for_delivery' => 'order.shipped',
                'delivered' => 'order.delivered',
                default => 'order.updated',
            };
            $historyId = $order->orderTrackingHistories()
                ->where('status', $order_status)
                ->latest('id')
                ->value('id');
            $occurrence = $historyId
                ? 'tracking-history:'.$historyId
                : 'order-status:'.$normalized.':'.optional($order->updated_at)->format('U.u');

            app(NotificationDispatcher::class)->dispatch(
                $eventKey,
                'order',
                $order->id,
                $occurrence,
                $recipientIds,
                [
                    'order_id' => $order->id,
                    'order_code' => $order->code,
                    'status' => $order_status,
                    'title' => ucfirst(str_replace('_', ' ', $normalized)),
                    'message' => "Order {$order->code} is now ".str_replace('_', ' ', $normalized).'.',
                ]
            );

            return;
        }

        $lock = \Illuminate\Support\Facades\Cache::lock('order_status_notify_'.$order->id.'_'.$order_status, 30);
        if (!$lock->get()) {
            \Log::info("Duplicate status notification suppressed for order {$order->id} status {$order_status}");
            return;
        }

        try {
            $adminId = get_admin()->id;
            $userIds = array($order->user->id, $order->seller_id);
        if ($order->seller_id != $adminId) {
            array_push($userIds, $adminId);
        }
        $users = User::findMany($userIds);
        
        $order_notification = array();
        $order_notification['order_id'] = $order->id;
        $order_notification['order_code'] = $order->code;
        $order_notification['user_id'] = $order->user_id;
        $order_notification['seller_id'] = $order->seller_id;
        $order_notification['status'] = $order_status;

        foreach($users as $user){
            $notificationType = get_notification_type('order_'.$order_status.'_'.$user->user_type, 'type');
            if($notificationType != null && $notificationType->status == 1){
                $order_notification['notification_type_id'] = $notificationType->id;
                Notification::send($user, new OrderNotification($order_notification));
            }
        }
        } catch (\Exception $e) {
            \Log::error("Failed to process status notification: " . $e->getMessage());
        }
    }

    public static function sendFirebaseNotification($req)
    {
        if (config('notifications_v2.enabled')) {
            return;
        }

        $url = 'https://fcm.googleapis.com/v1/projects/myproject-b5ae1/messages:send';

        $fields = array
        (
            'to' => $req->device_token,
            'notification' => [
                'body' => $req->text,
                'title' => $req->title,
                'sound' => 'default' /*Default sound*/
            ],
            'data' => [
                'item_type' => $req->type,
                'item_type_id' => $req->id,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ]
        );

        //$fields = json_encode($arrayToSend);
        $headers = array(
            'Authorization: key=' . env('FCM_SERVER_KEY'),
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        curl_close($ch);

        $firebase_notification = new FirebaseNotification;
        $firebase_notification->title = $req->title;
        $firebase_notification->text = $req->text;
        $firebase_notification->item_type = $req->type;
        $firebase_notification->item_type_id = $req->id;
        $firebase_notification->receiver_id = $req->user_id;

        $firebase_notification->save();
    }
}
