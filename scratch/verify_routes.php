<?php

define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routeCollection = Route::getRoutes();

$errors = [];

foreach ($routeCollection as $route) {
    $action = $route->getAction();
    
    if (isset($action['controller'])) {
        $controllerAction = $action['controller'];
        if (str_contains($controllerAction, '@')) {
            list($controller, $method) = explode('@', $controllerAction);
            
            if (!class_exists($controller)) {
                $errors[] = "Missing Controller: {$controller} (Route: {$route->getName()})";
                continue;
            }
            
            if (!method_exists($controller, $method)) {
                $errors[] = "Missing Method: {$controller}@{$method} (Route: {$route->getName()})";
            }
        }
    }
}

if (empty($errors)) {
    echo "All routes verified successfully!\n";
} else {
    echo "Errors found:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}
