<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

/**
 * A graceful version of Laravel's 'encrypted' cast.
 * Returns null instead of throwing DecryptException for data
 * encrypted with a different APP_KEY.
 */
class SafeEncrypted implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            // The value was encrypted with a different key or is plain text.
            // Return null to prevent the page from crashing.
            return null;
        }
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        return Crypt::encryptString($value);
    }
}
