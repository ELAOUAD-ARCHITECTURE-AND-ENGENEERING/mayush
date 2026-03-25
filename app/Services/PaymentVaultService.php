<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Address;
use Auth;

class PaymentVaultService
{
    /**
     * Check if the user is eligible for 1-click purchase.
     */
    public static function isEligible()
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        // 1. Must have a default address
        $defaultAddress = Address::where('user_id', $user->id)->where('set_default', 1)->first();
        if (!$defaultAddress) {
            return false;
        }

        // 2. Must have a previous successful order to determine payment preference
        $lastOrder = Order::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->latest()
            ->first();

        return ($lastOrder !== null);
    }

    /**
     * Get the user's preferred payment method.
     */
    public static function getPreferredPaymentMethod()
    {
        if (!Auth::check()) {
            return null;
        }

        $lastOrder = Order::where('user_id', Auth::id())
            ->where('payment_status', 'paid')
            ->latest()
            ->first();

        return $lastOrder ? $lastOrder->payment_type : 'cash_on_delivery';
    }
}
