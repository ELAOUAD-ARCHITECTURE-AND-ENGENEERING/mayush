<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MoroccanPhoneNumber implements Rule
{
    public function passes($attribute, $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return preg_match(
            '/^(?:0[5-7](?:[ .\-]?[0-9]){8}|\+212[ .\-]?[5-7](?:[ .\-]?[0-9]){8})$/',
            trim($value)
        ) === 1;
    }

    public function message(): string
    {
        return translate('Please enter a valid Moroccan phone number (for example, +212 6 12 34 56 78).');
    }
}
