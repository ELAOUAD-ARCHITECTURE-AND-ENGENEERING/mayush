<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "--- CHECKING BUSINESS SETTINGS ---\n";
$settings = \App\Models\BusinessSetting::where('value', 'LIKE', 'http://%')->get();
if ($settings->isEmpty()) {
    echo "No insecure URLs found in BusinessSetting.\n";
} else {
    foreach ($settings as $s) {
        echo "{$s->type}: {$s->value}\n";
    }
}
echo "--- END CHECKING ---\n";
