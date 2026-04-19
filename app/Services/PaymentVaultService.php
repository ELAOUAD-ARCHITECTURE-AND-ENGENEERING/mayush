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
     * Check if the user has an active, non-expired vaulted token.
     */
    public static function hasVaultedToken($userId): bool
    {
        return \App\Models\PaymentToken::where('user_id', $userId)
            ->where('gateway', 'cmi')
            ->where('is_active', true)
            ->nonExpired()
            ->exists();
    }

    /**
     * Get the active default token. If no default, get the latest.
     * Filters out expired tokens automatically.
     */
    public static function getActiveToken($userId)
    {
        return \App\Models\PaymentToken::where('user_id', $userId)
            ->where('gateway', 'cmi')
            ->where('is_active', true)
            ->nonExpired()
            ->where('is_default', true)
            ->first()
            ?? \App\Models\PaymentToken::where('user_id', $userId)
                ->where('gateway', 'cmi')
                ->where('is_active', true)
                ->nonExpired()
                ->latest()
                ->first();
    }

    /**
     * Store a new token from the CMI callback.
     * Enforces a per-user rate limit (max 5 active tokens).
     */
    public static function storeToken(int $userId, array $cmiCallbackData)
    {
        // Require a valid TransId
        if (empty($cmiCallbackData['TransId'])) {
            return null;
        }

        // Rate limit: enforce max tokens per user
        self::enforceTokenLimit($userId);

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

        // Parse card expiry from CMI callback (ExpDate format: YYMM)
        if (!empty($cmiCallbackData['ExpDate'])) {
            $expDate = $cmiCallbackData['ExpDate'];
            if (strlen($expDate) === 4) {
                $token->card_expiry_year = 2000 + (int) substr($expDate, 0, 2);
                $token->card_expiry_month = (int) substr($expDate, 2, 2);
            }
        }

        $token->is_default = true;
        $token->is_active = true;
        $token->last_used_at = now();
        $token->save();

        return $token;
    }

    /**
     * Enforce the maximum token limit per user per gateway.
     * Deactivates the oldest tokens if the limit would be exceeded.
     */
    private static function enforceTokenLimit(int $userId): void
    {
        $maxTokens = \App\Models\PaymentToken::MAX_TOKENS_PER_USER;

        $activeCount = \App\Models\PaymentToken::where('user_id', $userId)
            ->where('gateway', 'cmi')
            ->where('is_active', true)
            ->count();

        if ($activeCount >= $maxTokens) {
            // Deactivate oldest tokens to make room for the new one
            $tokensToDeactivate = $activeCount - $maxTokens + 1;
            
            \App\Models\PaymentToken::where('user_id', $userId)
                ->where('gateway', 'cmi')
                ->where('is_active', true)
                ->orderBy('created_at', 'asc')
                ->limit($tokensToDeactivate)
                ->update(['is_active' => false]);
        }
    }
}
