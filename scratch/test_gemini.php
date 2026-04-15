<?php

use App\Utility\SemanticUtility;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Gemini API...\n";
$vector = SemanticUtility::generateEmbedding("testing one two three");

if (empty($vector)) {
    echo "FAILED: Vector is empty.\n";
} else {
    echo "SUCCESS: Vector generated with " . count($vector) . " dimensions.\n";
    echo "First 5 values: " . implode(', ', array_slice($vector, 0, 5)) . "...\n";
}
