<?php
$logPath = 'storage/logs/laravel-2026-03-11.log';
$outPath = 'storage/logs/last_error.txt';
$log = file_get_contents($logPath);
$pos = strrpos($log, 'local.ERROR');
if ($pos !== false) {
    file_put_contents($outPath, substr($log, $pos, 3000));
} else {
    file_put_contents($outPath, substr($log, -3000));
}
echo "Wrote to $outPath\n";
