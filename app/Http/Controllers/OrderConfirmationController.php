<?php

namespace App\Http\Controllers;

use App\Models\CombinedOrder;
use App\Models\Order;
use App\Utility\EmailUtility;
use App\Utility\NotificationUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class OrderConfirmationController extends Controller
{
    /**
     * Enhanced order confirmation with session restoration
     */
    public function orderConfirmed(Request $request)
    {
        try {
            Log::info('Order confirmation accessed', [
                'session_data' => Session::all(),
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            // Try to get combined_order_id from multiple sources
            $combined_order_id = $this->getCombinedOrderId($request);
            
            if (!$combined_order_id) {
                Log::warning('No combined order ID found in order confirmation');
                flash(translate('Order information not found. Please contact support.'))->error();
                return redirect()->route('home');
            }

            // Find the combined order
            $combined_order = CombinedOrder::find($combined_order_id);
            
            if (!$combined_order) {
                Log::error('Combined order not found', ['combined_order_id' => $combined_order_id]);
                flash(translate('Order not found. Please contact support.'))->error();
                return redirect()->route('home');
            }

            // Verify the order belongs to the current user (if authenticated)
            if (auth()->check() && $combined_order->user_id != auth()->id()) {
                Log::warning('Order confirmation accessed by wrong user', [
                    'order_user_id' => $combined_order->user_id,
                    'current_user_id' => auth()->id()
                ]);
                flash(translate('Invalid order access.'))->error();
                return redirect()->route('home');
            }

            // Process the order confirmation
            return $this->processOrderConfirmation($combined_order);

        } catch (\Exception $e) {
            Log::error('Order confirmation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            flash(translate('An error occurred while processing your order confirmation.'))->error();
            return redirect()->route('home');
        }
    }

    /**
     * Try to get combined order ID from multiple sources
     */
    private function getCombinedOrderId(Request $request)
    {
        // Priority 1: Session (most common)
        if (Session::has('combined_order_id')) {
            return Session::get('combined_order_id');
        }

        // Priority 2: Request parameter (from payment gateway redirect)
        if ($request->has('combined_order_id')) {
            return $request->get('combined_order_id');
        }

        // Priority 3: Try to extract from order_id parameter (some payment gateways)
        if ($request->has('order_id')) {
            $order_id = $request->get('order_id');
            $order = Order::find($order_id);
            if ($order && $order->combined_order_id) {
                return $order->combined_order_id;
            }
        }

        // Priority 4: Check recent orders for authenticated users
        if (auth()->check()) {
            $recent_order = CombinedOrder::where('user_id', auth()->id())
                ->where('created_at', '>', now()->subMinutes(30))
                ->latest()
                ->first();
            
            if ($recent_order) {
                return $recent_order->id;
            }
        }

        return null;
    }

    /**
     * Process the order confirmation
     */
    private function processOrderConfirmation($combined_order)
    {
        // Clear session data
        Session::forget('club_point');
        Session::forget('combined_order_id');
        Session::forget('payment_type');
        Session::forget('payment_data');

        // Send notifications for unpaid orders
        foreach ($combined_order->orders as $order) {
            if ($order->notified == 0) {
                try {
                    NotificationUtility::sendOrderPlacedNotification($order);
                    $order->notified = 1;
                    $order->save();
                } catch (\Exception $e) {
                    Log::error('Failed to send order notification', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        Log::info('Order confirmation successful', ['combined_order_id' => $combined_order->id]);

        return view('frontend.order_confirmed', compact('combined_order'));
    }

    /**
     * Alternative route for payment gateways that need direct access
     */
    public function orderConfirmedWithId($combined_order_id)
    {
        Session::put('combined_order_id', $combined_order_id);
        return redirect()->route('order_confirmed');
    }

    /**
     * Simple order success page for payment gateways
     */
    public function orderSuccess(Request $request)
    {
        return view('frontend.order_confirmed', [
            'message' => translate('Your order has been placed successfully!'),
            'request_data' => $request->all()
        ]);
    }
}