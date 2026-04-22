<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $component = Livewire\Livewire::mount('analytics.technical-dashboard');
    $html = is_string($component) ? $component : (method_exists($component, 'html') ? $component->html() : (string) $component);
    echo "--- HTML LENGTH ---\n";
    echo strlen($html) . "\n";
    echo "--- FIRST 100 CHARS ---\n";
    echo substr($html, 0, 100) . "\n";
    echo "--- DOM TEST ---\n";
    preg_match('/<script>(.*?)<\/script>/s', $html, $matches);
    if (isset($matches[1])) {
        file_put_contents('script_dump.js', $matches[1]);
        echo "Saved script_dump.js\n";
    } else {
        echo "No script found!\n";
    }
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
