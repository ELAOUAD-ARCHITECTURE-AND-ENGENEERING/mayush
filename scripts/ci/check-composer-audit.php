<?php

$path = $argv[1] ?? 'composer-audit.json';

if (!is_file($path)) {
    fwrite(STDERR, "Composer audit file not found: {$path}\n");
    exit(1);
}

$contents = file_get_contents($path);
if (str_contains($contents, "\0")) {
    $contents = mb_convert_encoding($contents, 'UTF-8', 'UTF-16LE');
}
$contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents);

$audit = json_decode($contents, true);

if (!is_array($audit)) {
    fwrite(STDERR, "Composer audit file is not valid JSON: {$path}\n");
    exit(1);
}

$advisoryCount = 0;
foreach (($audit['advisories'] ?? []) as $packageAdvisories) {
    $advisoryCount += is_array($packageAdvisories) ? count($packageAdvisories) : 1;
}

if ($advisoryCount > 0) {
    fwrite(STDERR, "Composer audit found {$advisoryCount} security vulnerability advisories.\n");
    exit(1);
}

echo "Composer audit found no security vulnerability advisories.\n";

$abandoned = $audit['abandoned'] ?? [];
if (!empty($abandoned)) {
    echo "Composer audit reported abandoned packages for migration planning:\n";
    foreach ($abandoned as $package => $replacement) {
        $suffix = $replacement ? " replacement: {$replacement}" : ' no suggested replacement';
        echo "- {$package}:{$suffix}\n";
    }
}
