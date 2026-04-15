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

        if (self::hasVaultedToken(Auth::id())) {
            return 'cmi_vault';
        }

        return 'cash_on_delivery';
    }

    /**
     * Check if the user has an active vaulted token.
     */
    public static function hasVaultedToken($userId): bool
    {
        return \App\Models\PaymentToken::where('user_id', $userId)
            ->where('gateway', 'cmi')
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get the active default token. If no default, get the latest.
     */
    public static function getActiveToken($userId)
    {
        return \App\Models\PaymentToken::where('user_id', $userId)
            ->where('gateway', 'cmi')
            ->where('is_active', true)
            ->where('is_default', true)
            ->first()
            ?? \App\Models\PaymentToken::where('user_id', $userId)
                ->where('gateway', 'cmi')
                ->where('is_active', true)
                ->latest()
                ->first();
    }

    /**
     * Store a new token from the CMI callback.
     */
    public static function storeToken(int $userId, array $cmiCallbackData)
    {
        // Require a valid TransId
        if (empty($cmiCallbackData['TransId'])) {
            return null;
        }

        // Unset old defaults for this user
        \App\Models\PaymentToken::where('user_id', $userId)
            ->where('gateway', 'cmi')
            ->update(['is_default' => false]);

        $token = new \App\Models\PaymentToken();
        $token->user_id = $userId;
        $token->gateway = 'cmi';
        $token->token = $cmiCallbackData['TransId'];

        if (!empty($cmiCallbackData['MaskedPan'])) {
            // Usually format: 411111****1111 -> take last 4
            $token->card_last_four = substr($cmiCallbackData['MaskedPan'], -4);
            
            // Derive brand simple logic (4=Visa, 5=Mastercard, etc)
            $firstChar = substr($cmiCallbackData['MaskedPan'], 0, 1);
            if ($firstChar === '4') $token->card_brand = 'Visa';
            elseif ($firstChar === '5') $token->card_brand = 'Mastercard';
            else $token->card_brand = 'Card';
        }

        $token->is_default = true;
        $token->is_active = true;
        $token->save();

        return $token;
    }
}
