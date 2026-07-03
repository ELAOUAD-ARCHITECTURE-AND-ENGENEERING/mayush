<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$files = [
    'routes/admin.php',
    'routes/seller.php'
];

$resourceMethods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];

foreach ($files as $file) {
    echo "=== Processing $file ===\n";
    $content = file_get_contents(base_path($file));
    
    // Match Route::resource('...', Controller::class)
    preg_match_all('/Route::resource\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*([A-Za-z0-9_\\\\]+)::class[^;]*\);/is', $content, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $fullMatch = $match[0];
        $routeName = $match[1];
        $controllerName = $match[2];
        
        $fqcn = '\\App\\Http\\Controllers\\' . $controllerName;
        if (!class_exists($fqcn)) {
            // Might be nested or already have namespace
            if (class_exists($controllerName)) {
                $fqcn = $controllerName;
            } else {
                echo "Controller not found: $controllerName\n";
                continue;
            }
        }
        
        $implemented = [];
        foreach ($resourceMethods as $method) {
            if (method_exists($fqcn, $method)) {
                $implemented[] = $method;
            }
        }
        
        $onlyStr = "['" . implode("', '", $implemented) . "']";
        echo "Resource: $routeName | Controller: $controllerName\n";
        if (count($implemented) < 7) {
            echo "->only($onlyStr)\n";
        } else {
            echo "All 7 methods implemented.\n";
        }
        echo "--------------------------\n";
    }
}
