<?php
// Simple web script
echo "PHP Version: " . phpversion() . "\n";
echo "Loaded php.ini: " . php_ini_loaded_file() . "\n";
echo "variables_order: " . ini_get('variables_order') . "\n";
$envPath = __DIR__ .'/.env';
if (file_exists($envPath)) {
    echo "env exists. length: " . filesize($envPath) . "\n";
} else {
    echo "env missing!\n";
}
