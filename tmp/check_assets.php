<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$out = "";

// 1. Check products table
$out .= "=== DATABASE CHECK ===\n";
try {
    $count = \App\Models\Product::count();
    $out .= "Products table OK: {$count} rows\n";
} catch(\Exception $e) {
    $out .= "Products table ERROR: " . $e->getMessage() . "\n";
}

try {
    $uploadCount = \App\Models\Upload::count();
    $out .= "Uploads table OK: {$uploadCount} rows\n";
} catch(\Exception $e) {
    $out .= "Uploads table ERROR: " . $e->getMessage() . "\n";
}

// 2. Full scan
$allUploads = \App\Models\Upload::whereNull('external_link')->get(['id','file_name']);
$totalFound = 0;
$totalMissing = 0;
$missingExamples = [];
$foundViaFallback = 0;

foreach($allUploads as $u) {
    $path = public_path($u->file_name);
    if (file_exists($path)) {
        $totalFound++;
    } else {
        $basename = basename($u->file_name);
        $fallback = public_path('uploads/all/' . $basename);
        if (file_exists($fallback)) {
            $totalFound++;
            $foundViaFallback++;
        } else {
            $totalMissing++;
            if (count($missingExamples) < 15) {
                $missingExamples[] = "[{$u->id}] {$u->file_name}";
            }
        }
    }
}

$out .= "\n=== FULL SCAN RESULTS ===\n";
$out .= "Total local uploads: " . count($allUploads) . "\n";
$out .= "Found on disk (direct path): " . ($totalFound - $foundViaFallback) . "\n";
$out .= "Found on disk (via fallback): {$foundViaFallback}\n";
$out .= "Total found: {$totalFound}\n";
$out .= "Missing from disk: {$totalMissing}\n";
$out .= "Missing rate: " . round($totalMissing / max(count($allUploads), 1) * 100, 1) . "%\n";

if (!empty($missingExamples)) {
    $out .= "\nMissing examples:\n";
    foreach($missingExamples as $m) {
        $out .= "  - {$m}\n";
    }
}

// 3. Placeholder check
$out .= "\n=== PLACEHOLDER CHECK ===\n";
$placeholder = public_path('assets/img/placeholder.jpg');
$out .= file_exists($placeholder) ? "placeholder.jpg EXISTS\n" : "placeholder.jpg MISSING\n";

// 4. Path patterns
$out .= "\n=== FILE_NAME PATTERNS ===\n";
$patterns = \App\Models\Upload::selectRaw("
    CASE 
        WHEN file_name LIKE 'uploads/all/%' THEN 'uploads/all/...'
        WHEN file_name LIKE 'uploads/%' THEN 'uploads/other/...'
        WHEN file_name LIKE '%/%' THEN 'other_path/...'
        ELSE 'no_directory'
    END as pattern,
    COUNT(*) as cnt
")->whereNull('external_link')->groupByRaw("
    CASE 
        WHEN file_name LIKE 'uploads/all/%' THEN 'uploads/all/...'
        WHEN file_name LIKE 'uploads/%' THEN 'uploads/other/...'
        WHEN file_name LIKE '%/%' THEN 'other_path/...'
        ELSE 'no_directory'
    END
")->get();

foreach($patterns as $p) {
    $out .= "  {$p->pattern}: {$p->cnt}\n";
}

// 5. Check what files are on disk but not in DB
$out .= "\n=== FILES ON DISK ===\n";
$diskFiles = glob(public_path('uploads/all/*'));
$out .= "Files in uploads/all/: " . count($diskFiles) . "\n";

file_put_contents(__DIR__ . '/asset_report.txt', $out);
echo $out;
