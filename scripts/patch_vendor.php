<?php

/**
 * This script ensures that critical vendor patches are applied after every composer update/install.
 * Specifically, it prevents the CoreComponentRepository from triggering a redirect deadlock
 * in the admin panel which happens when license activation fails.
 */

$targetFile = __DIR__ . '/../vendor/mehedi-iitdu/core-component-repository/src/CoreComponentRepository.php';

if (!file_exists($targetFile)) {
    echo "Vendor file not found at $targetFile. Skipping patch.\n";
    exit(0);
}

$content = file_get_contents($targetFile);

// Check if patches are already applied
if (strpos($content, 'PATCHED by app:patch-vendor') !== false) {
    echo "CoreComponentRepository patch already present.\n";
} else {
    // Apply patches
    $patterns = [
        "/return redirect\('https:\/\/activeitzone.com\/activation\/'\)->send\(\);/" => "// // return redirect('https://activeitzone.com/activation/')->send(); // PATCHED by app:patch-vendor",
        "/return redirect\(\)->route\('addons.index'\)->send\(\);/" => "// // return redirect()->route('addons.index')->send(); // PATCHED by app:patch-vendor"
    ];

    $newContent = preg_replace(array_keys($patterns), array_values($patterns), $content);

    if ($newContent !== $content) {
        file_put_contents($targetFile, $newContent);
        echo "Successfully patched CoreComponentRepository.\n";
    } else {
        echo "Could not find targets to patch in CoreComponentRepository. It might already be modified or updated.\n";
    }
}

exit(0);
