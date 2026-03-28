<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Crypt;

$users = User::whereNotNull('phone')
    ->orWhereNotNull('address')
    ->orWhereNotNull('postal_code')
    ->limit(10)
    ->get();

echo "Checking " . $users->count() . " users...\n";

foreach ($users as $user) {
    echo "User ID: " . $user->id . "\n";
    foreach (['phone', 'address', 'postal_code'] as $field) {
        $raw = $user->getRawOriginal($field);
        if (!$raw) {
            echo "  $field: NULL\n";
            continue;
        }
        
        $is_encrypted = false;
        try {
            Crypt::decryptString($raw);
            $is_encrypted = true;
        } catch (Exception $e) {
            $is_encrypted = false;
        }
        
        echo "  $field: " . ($is_encrypted ? "[ENCRYPTED]" : "[PLAINTEXT or INVALID]") . " (Raw: " . substr($raw, 0, 10) . "...)\n";
    }
    echo "-------------------\n";
}
