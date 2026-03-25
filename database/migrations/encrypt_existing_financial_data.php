<?php

use App\Models\Shop;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Security Evolution: Financial Data Encryption Migration
 * 
 * Run this script ONCE to encrypt existing plaintext financial info
 * after enabling the 'encrypted' casts in the Shop model.
 * 
 * NOTE: Uses raw DB updates to bypass Eloquent casts, which prevents 
 * DecryptException crashes when the APP_KEY has changed.
 */

$shops = Shop::all();
$count = 0;
$fields = ['bank_name', 'bank_info', 'business_info', 'verification_info'];

foreach ($shops as $shop) {
    $updates = [];
    foreach ($fields as $field) {
        $value = $shop->getRawOriginal($field);
        
        if ($value && !is_already_encrypted($value)) {
            try {
                // If it's plain text (or invalid ciphertext), encrypt it with the current key
                $updates[$field] = Crypt::encryptString($value);
            } catch (\Exception $e) {
                // Should not happen for encryption
            }
        }
    }

    if (!empty($updates)) {
        // High Speed & Deep Security: Bypassing Eloquent to avoid DecryptException on save()
        // when APP_KEY has changed (which causes original attribute decryption to fail).
        DB::table('shops')->where('id', $shop->id)->update($updates);
        $count++;
    }
}

echo "Successfully encrypted financial data for $count shops.\n";

function is_already_encrypted($value) {
    try {
        Crypt::decryptString($value);
        return true;
    } catch (\Exception $e) {
        return false;
    }
}
