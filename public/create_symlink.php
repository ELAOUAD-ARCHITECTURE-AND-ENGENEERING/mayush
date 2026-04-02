<?php
header('Content-Type: text/plain');

$target = __DIR__ . '/build';
$link = dirname(__DIR__) . '/build';

if (file_exists($link)) {
    if (is_link($link)) {
        echo "Link exists and is already a symlink.\n";
        echo "Target: " . readlink($link) . "\n";
    } else {
        echo "A directory or file named 'build' already exists in the root and is NOT a symlink.\n";
        echo "You might need to delete it manually if you want to replace it with a symlink.\n";
    }
} else {
    if (symlink($target, $link)) {
        echo "Symlink 'build' created successfully: $link -> $target\n";
    } else {
        $error = error_get_last();
        echo "Failed to create symlink.\n";
        echo "Error: " . ($error['message'] ?? 'Unknown error') . "\n";
    }
}

echo "\nVerification:\n";
echo "Root Dir: " . dirname(__DIR__) . "\n";
echo "Public Dir: " . __DIR__ . "\n";
echo "Build Assets check: " . (file_exists($target . '/assets/analytics-tracker-BglyRnQE.js') ? 'YES' : 'NO') . "\n";
