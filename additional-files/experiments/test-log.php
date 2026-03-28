<?php
$file = __DIR__ . '/storage/logs/test-perm.log';
$result = @file_put_contents($file, date('c') . PHP_EOL, FILE_APPEND);
var_dump($file, $result, error_get_last());
