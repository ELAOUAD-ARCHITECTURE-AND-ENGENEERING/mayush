<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\CombinedOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CombinedOrderController extends Controller
{
    /**
     * Reconcile authoritative payment and order status for a combined order.
     */
    public function show($id): JsonResponse
    {
        $userId = auth()->id();
        $combinedOrder = CombinedOrder::with('orders')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$combinedOrder) {
            return response()->json([
                'result' => false,
                'message' => translate('Combined order not found or access denied.'),
            ], 404);
        }

        $allPaid = $combinedOrder->orders->isNotEmpty() && $combinedOrder->orders->every(fn($order) => $order->payment_status === 'paid');
        $paymentStatus = $allPaid ? 'paid' : 'unpaid';

        return response()->json([
            'result' => true,
            'combined_order_id' => (int) $combinedOrder->id,
            'grand_total' => (float) $combinedOrder->grand_total,
            'payment_type' => $combinedOrder->orders->first()?->payment_type ?? 'cmi',
            'payment_status' => $paymentStatus,
            'orders' => $combinedOrder->orders->map(function ($order) {
                return [
                    'id' => (int) $order->id,
                    'code' => (string) $order->code,
                    'payment_type' => (string) $order->payment_type,
                    'payment_status' => (string) $order->payment_status,
                    'delivery_status' => (string) $order->delivery_status,
                    'grand_total' => (float) $order->grand_total,
                ];
            })->values(),
        ]);
    }

    /**
     * Create a short-lived (60-second) single-use session for CMI hosted mobile payment.
     */
    public function createPaymentSession($id): JsonResponse
    {
        $userId = auth()->id();
        $combinedOrder = CombinedOrder::with('orders')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$combinedOrder) {
            return response()->json([
                'result' => false,
                'message' => translate('Combined order not found or access denied.'),
            ], 404);
        }

        $allPaid = $combinedOrder->orders->isNotEmpty() && $combinedOrder->orders->every(fn($order) => $order->payment_status === 'paid');
        if ($allPaid) {
            return response()->json([
                'result' => false,
                'message' => translate('This order is already paid.'),
            ], 400);
        }

        $token = bin2hex(random_bytes(32));
        Cache::put('cmi_mobile_bridge_' . $token, [
            'combined_order_id' => (int) $combinedOrder->id,
            'user_id' => (int) $userId,
        ], 60);

        return response()->json([
            'result' => true,
            'token' => $token,
            'session_url' => route('cmi.mobile.launch', ['token' => $token]),
            'expires_in_seconds' => 60,
        ]);
    }
}
