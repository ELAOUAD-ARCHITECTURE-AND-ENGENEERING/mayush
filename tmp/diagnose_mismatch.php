<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$out = "=== DIAGNOSING MISSING FILES vs LOCAL FILES ===\n\n";

// 1. Get 10 missing DB records
$allUploads = \App\Models\Upload::whereNull('external_link')->get(['id','file_name']);
$missingDb = [];
foreach($allUploads as $u) {
    if (!file_exists(public_path($u->file_name)) && !file_exists(public_path('uploads/all/' . basename($u->file_name)))) {
        $missingDb[] = $u;
        if(count($missingDb) == 10) break;
    }
}

$out .= "Missing from disk according to DB exact match:\n";
foreach($missingDb as $m) {
    $out .= "  [{$m->id}] {$m->file_name}\n";
    // Check if there are similar files (without extension)
    $pathinfo = pathinfo($m->file_name);
    $filenameWithoutExt = $pathinfo['filename'];
    
    $matches = glob(public_path('uploads/all/' . $filenameWithoutExt . '.*'));
    if (!empty($matches)) {
        $out .= "    --> Found similar files: \n";
        foreach($matches as $match) {
            $out .= "        " . basename($match) . "\n";
        }
    }
}

// 2. Sample 10 files from disk that are NOT in DB
$diskFiles = glob(public_path('uploads/all/*.*'));
$out .= "\nDisk files: " . count($diskFiles) . "\n";

$dbFilesMap = [];
foreach($allUploads as $u) {
    $dbFilesMap[basename($u->file_name)] = true;
}

$notInDb = [];
foreach($diskFiles as $f) {
    $b = basename($f);
    if (!isset($dbFilesMap[$b])) {
        $notInDb[] = $b;
        if (count($notInDb) == 10) break;
    }
}

$out .= "\nFiles on disk but not matched to exact DB basename:\n";
foreach($notInDb as $nid) {
    $out .= "  - {$nid}\n";
}

file_put_contents(__DIR__ . '/diagnose_mismatch.txt', $out);
echo $out;
