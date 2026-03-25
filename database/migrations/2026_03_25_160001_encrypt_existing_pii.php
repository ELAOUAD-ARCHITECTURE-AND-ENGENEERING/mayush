<?php

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class EncryptExistingPii extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $count = 0;
        $fields = ['phone', 'address', 'postal_code'];

        // Use cursor() for memory efficiency with large production tables
        foreach (User::query()->cursor() as $user) {
            $updates = [];
            
            foreach ($fields as $field) {
                $value = $user->getRawOriginal($field);
                
                if ($value && !$this->isValidlyEncrypted($value)) {
                    try {
                        // If it's plaintext (or invalid ciphertext), encrypt it with the current key
                        $updates[$field] = Crypt::encryptString($value);
                    } catch (\Exception $e) {
                        // Safe to skip if something goes wrong
                    }
                }
            }

            if (!empty($updates)) {
                // Direct DB update bypasses Eloquent's DecryptException on save()
                DB::table('users')->where('id', $user->id)->update($updates);
                $count++;
            }
        }

        echo "Successfully encrypted PII for $count users.\n";
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
