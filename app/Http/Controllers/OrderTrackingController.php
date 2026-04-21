<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderTrackingHistory;
use App\Services\Logistics\CarrierTrackingManager;
use Auth;

class OrderTrackingController extends Controller
{
    /**
     * Unified tracking handler supporting RBAC out of the box.
     */
    public function show($id)
    {
        $order = Order::findOrFail(decrypt($id));

        // RBAC: Reject unauthorized access
        $user = Auth::user();

        if ($user->user_type == 'customer') {
            if ($order->user_id != $user->id) {
                abort(403);
            }
        } elseif ($user->user_type == 'seller') {
            // Can only view if order contains their products
            if ($order->seller_id != $user->id) {
                abort(403);
            }
        } elseif (in_array($user->user_type, ['admin', 'staff'])) {
            // Admins can view all orders
        } else {
            abort(403);
        }

        // Fetch histories ordered by creation
        $tracking_histories = $order->orderTrackingHistories()->orderBy('created_at', 'asc')->get();

        return view('frontend.tracking.show', compact('order', 'tracking_histories'));
    }

    /**
     * Webhook endpoint for carriers to push status updates.
     */
    public function webhookUpdate(Request $request)
    {
        // Add basic security token/secret check here in a real scenario
        $tracking_code = $request->input('tracking_code');
        $order = Order::where('tracking_code', $tracking_code)->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Delegate parsing to a tracking manager or directly create a new entry
        $manager = new CarrierTrackingManager();
        $carrier = $manager->resolveCarrier($order->carrier_id);

        // Fetch fresh info based on standard integration mapping
        $info = $carrier->fetchTrackingInfo($tracking_code);

        OrderTrackingHistory::create([
            'order_id' => $order->id,
            'status' => $info['status'] ?? 'processing',
            'location_name' => $info['location_name'] ?? null,
            'latitude' => $info['latitude'] ?? null,
            'longitude' => $info['longitude'] ?? null,
            'notes' => $info['notes'] ?? null,
            'expected_delivery_date' => $info['expected_delivery_date'] ?? null,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Helper to manually sync an order's tracking status using external APIs
     */
    public function syncTracking($id)
    {
        $order = Order::findOrFail(decrypt($id));
        $user = Auth::user();

        if ($user->user_type == 'customer' && $order->user_id != $user->id) abort(403);
        if ($user->user_type == 'seller' && $order->seller_id != $user->id) abort(403);

        if (!$order->tracking_code) {
             flash(translate('No active tracking code found for this order.'))->warning();
             return back();
        }

        $manager = new CarrierTrackingManager();
        $carrier = $manager->resolveCarrier($order->carrier_id);
        $info = $carrier->fetchTrackingInfo($order->tracking_code);

        OrderTrackingHistory::create([
            'order_id' => $order->id,
            'status' => $info['status'] ?? 'processing',
            'location_name' => $info['location_name'] ?? null,
            'latitude' => $info['latitude'] ?? null,
            'longitude' => $info['longitude'] ?? null,
            'notes' => $info['notes'] ?? null,
            'expected_delivery_date' => $info['expected_delivery_date'] ?? null,
        ]);

        flash(translate('Tracking data synced successfully.'))->success();
        return back();
    }
}
