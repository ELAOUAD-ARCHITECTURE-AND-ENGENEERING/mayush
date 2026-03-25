<?php

use App\Models\Shop;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class EncryptExistingFinancialData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $count = 0;
        $fields = ['bank_name', 'bank_info', 'business_info', 'verification_info'];

        // High Speed & Deep Security: Bypassing Eloquent to avoid DecryptException on save()
        // when APP_KEY has changed (which causes original attribute decryption to fail).
        foreach (Shop::query()->cursor() as $shop) {
            $updates = [];
            foreach ($fields as $field) {
                $value = $shop->getRawOriginal($field);
                
                if ($value && !$this->isValidlyEncrypted($value)) {
                    try {
                        // If it's plain text (or invalid ciphertext), encrypt it with the current key
                        $updates[$field] = Crypt::encryptString($value);
                    } catch (\Exception $e) {
                        // Should not happen for encryption
                    }
                }
            }

            if (!empty($updates)) {
                DB::table('shops')->where('id', $shop->id)->update($updates);
                $count++;
            }
        }

        echo "Successfully encrypted financial data for $count shops.\n";
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // One-way migration
    }

    /**
     * Checks if a value is already validly encrypted with the current APP_KEY.
     */
    private function isValidlyEncrypted($value)
    {
        if (empty($value)) return true;
        try {
            Crypt::decryptString($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
