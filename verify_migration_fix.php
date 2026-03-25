<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

// Helper from migration
function test_is_pii_encrypted($value) {
    if (empty($value)) return true;
    try {
        Crypt::decryptString($value);
        return true;
    } catch (\Exception $e) {
        return false;
    }
}

echo "Starting verification...\n";

// 1. Create a test user with plaintext data
$testUser = User::factory()->create([
    'phone' => '+123456789',
    'address' => 'Plaintext Address',
    'postal_code' => '12345'
]);

echo "Created test user {$testUser->id} with plaintext data.\n";

// 2. Run the migration logic (simulated)
$fields = ['phone', 'address', 'postal_code'];
$updates = [];

foreach ($fields as $field) {
    $value = $testUser->getRawOriginal($field);
    if ($value && !test_is_pii_encrypted($value)) {
        echo "  Field $field is plaintext, encrypting...\n";
        $updates[$field] = Crypt::encryptString($value);
    }
}

if (!empty($updates)) {
    DB::table('users')->where('id', $testUser->id)->update($updates);
    echo "  Successfully updated test user via DB::table.\n";
}

// 3. Verify it's now encrypted and decryptable
$refreshedUser = DB::table('users')->where('id', $testUser->id)->first();
foreach ($fields as $field) {
    $encryptedValue = $refreshedUser->$field;
    try {
        $decrypted = Crypt::decryptString($encryptedValue);
        echo "  Field $field decrypted: $decrypted [OK]\n";
    } catch (Exception $e) {
        echo "  Field $field FAILED to decrypt: " . $e->getMessage() . "\n";
    }
}

// 4. Test "Invalid Payload" scenario (Old Key simulation)
echo "\nTesting invalid payload scenario...\n";
$badPayload = 'eyJpdiI6InpYclZ...invalid...'; // Random junk
DB::table('users')->where('id', $testUser->id)->update(['phone' => $badPayload]);

// Simulate migration run on bad payload
$updates = [];
$val = DB::table('users')->where('id', $testUser->id)->value('phone');

if ($val && !test_is_pii_encrypted($val)) {
    echo "  Detected invalid phone payload, re-encrypting safely...\n";
    try {
        $updates['phone'] = Crypt::encryptString($val);
        DB::table('users')->where('id', $testUser->id)->update($updates);
        echo "  Successfully re-encrypted bad payload without crashing.\n";
    } catch (Exception $e) {
        echo "  Failed to re-encrypt: " . $e->getMessage() . "\n";
    }
}

echo "\nVerification complete. Cleaning up test user...\n";
$testUser->delete();
