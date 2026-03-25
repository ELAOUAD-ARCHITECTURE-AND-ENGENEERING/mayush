<?php

use App\Models\User;
use Illuminate\Support\Facades\Crypt;

/**
 * Security Evolution: PII Encryption Migration
 * 
 * Run this script ONCE to encrypt existing plaintext data 
 * after enabling the 'encrypted' casts in the User model.
 */

$users = User::all();
$count = 0;

foreach ($users as $user) {
    $dirty = false;
    
    // Check if fields are actually plaintext (not double encrypted)
    // Laravel's encryption starts with a specific prefix/format.
    // We attempt to decrypt; if it fails, it's plaintext.
    
    foreach (['phone', 'address', 'postal_code'] as $field) {
        $value = $user->getRawOriginal($field);
        
        if ($value && !is_encrypted($value)) {
            $user->$field = $value; // Setting it triggers the cast's encryption
            $dirty = true;
        }
    }

    if ($dirty) {
        $user->save();
        $count++;
    }
}

echo "Successfully encrypted PII for $count users.\n";

function is_encrypted($value) {
    try {
        Crypt::decryptString($value);
        return true;
    } catch (\Exception $e) {
        return false;
    }
}
