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
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
    $errors = libxml_get_errors();
    foreach($errors as $err) echo "XML Error: " . $err->message . "\n";
    if (!$dom->documentElement) {
        echo "DOCUMENT ELEMENT IS NULL\n";
    } else {
        echo "ROOT NODE: " . $dom->documentElement->nodeName . "\n";
    }
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
