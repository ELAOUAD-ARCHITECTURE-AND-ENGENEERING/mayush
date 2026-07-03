<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Address;
use Auth;
use Log;

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

        // Deduplication: Check if this card (last 4 + brand) already exists for this user
        $last4 = !empty($cmiCallbackData['MaskedPan']) ? substr($cmiCallbackData['MaskedPan'], -4) : null;
        $brand = 'Card';
        if (!empty($cmiCallbackData['MaskedPan'])) {
            $firstChar = substr($cmiCallbackData['MaskedPan'], 0, 1);
            if ($firstChar === '4') $brand = 'Visa';
            elseif ($firstChar === '5') $brand = 'Mastercard';
        }
        $fingerprint = self::cardFingerprint($brand, $last4);

        $existingToken = null;
        if ($fingerprint) {
            $existingToken = \App\Models\PaymentToken::where('user_id', $userId)
                ->where('gateway', 'cmi')
                ->where('card_fingerprint', $fingerprint)
                ->where('is_active', true)
                ->first();
        }

        if ($existingToken) {
            $token = $existingToken;
            Log::info('CMI Vault: Updating existing token', ['token_id' => $token->id, 'new_transId' => $cmiCallbackData['TransId']]);
        } else {
            // Rate limit: enforce max tokens per user (only for new tokens)
            self::enforceTokenLimit($userId);
            $token = new \App\Models\PaymentToken();
            $token->user_id = $userId;
            $token->gateway = 'cmi';
            Log::info('CMI Vault: Creating new token', ['transId' => $cmiCallbackData['TransId']]);
        }

        $token->token = $cmiCallbackData['TransId'];
        $token->card_last_four = $last4;
        $token->card_brand = $brand;
        $token->card_fingerprint = $fingerprint;

        // Parse card expiry from CMI callback (ExpDate format: YYMM)
        if (!empty($cmiCallbackData['ExpDate'])) {
            $expDate = $cmiCallbackData['ExpDate'];
            if (strlen($expDate) === 4) {
                $token->card_expiry_year = 2000 + (int) substr($expDate, 0, 2);
                $token->card_expiry_month = (int) substr($expDate, 2, 2);
            }
        }

        // Unset old defaults for this user
        \App\Models\PaymentToken::where('user_id', $userId)
            ->where('gateway', 'cmi')
            ->update(['is_default' => false]);

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

    private static function cardFingerprint(?string $brand, ?string $last4): ?string
    {
        if (!$last4) {
            return null;
        }

        return hash('sha256', strtolower((string) $brand) . '|' . $last4);
    }
}
