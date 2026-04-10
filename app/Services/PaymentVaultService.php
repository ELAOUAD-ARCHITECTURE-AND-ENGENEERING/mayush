<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Address;
use Auth;

class PaymentVaultService
{
    /**
     * Check if the user is eligible for 1-click purchase via Vault.
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

        // 2. Must have a valid vaulted token
        return self::hasVaultedToken($user->id);
    }

    /**
     * Get the user's preferred payment method.
     */
    public static function getPreferredPaymentMethod()
    {
        if (!Auth::check()) {
            return null;
        }

        // Return the mocked token method
        if (self::hasVaultedToken(Auth::id())) {
            return 'cmi_vault';
        }

        return 'cash_on_delivery';
    }

    /**
     * Mock sandbox method checking for tokenized CMI card.
     * In production, this will query a PaymentVault token table.
     */
    public static function hasVaultedToken($userId)
    {
        // SANDBOX MOCK: We simulate a vault token existing if they have any past paid order.
        // This unblocks the CMI Sandbox testing flow without storing PCI data locally.
        $lastOrder = Order::where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->latest()
            ->first();

        return ($lastOrder !== null);
    }
}
