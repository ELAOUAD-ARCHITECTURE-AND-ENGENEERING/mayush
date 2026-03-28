<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

$stats = [
    'total' => 0,
    'null' => 0,
    'valid_encrypted' => 0,
    'invalid_or_plaintext' => 0,
];

$invalid_samples = [];

foreach (DB::table('users')->cursor() as $user) {
    $stats['total']++;
    $val = $user->phone;
    
    if (empty($val)) {
        $stats['null']++;
        continue;
    }
    
    try {
        Crypt::decryptString($val);
        $stats['valid_encrypted']++;
    } catch (Exception $e) {
        $stats['invalid_or_plaintext']++;
        if (count($invalid_samples) < 5) {
            $invalid_samples[] = [
                'id' => $user->id,
                'raw' => substr($val, 0, 20) . '...'
            ];
        }
    }
}

echo "PII Encryption Diagnostic:\n";
print_r($stats);
echo "\nSamples of invalid data:\n";
print_r($invalid_samples);
