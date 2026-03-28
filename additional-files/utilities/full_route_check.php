<?php

// First, get all valid route names from artisan
exec('php artisan route:list --json', $output, $returnVar);

if ($returnVar !== 0) {
    die("Error running php artisan route:list --json\n");
}

$routesJson = implode('', $output);
$routes = json_decode($routesJson, true);

if (!$routes) {
    die("Error decoding route list JSON\n");
}

$validRouteNames = array_filter(array_column($routes, 'name'));
$validRouteNames = array_combine($validRouteNames, $validRouteNames);

$viewsPath = __DIR__.'/resources/views';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
$results = [];

echo "Starting comprehensive route verification using artisan route list...\n";

foreach ($files as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') continue;
    
    $pathname = $file->getPathname();
    $content = file_get_contents($pathname);
    
    // Updated pattern to look for route() calls that ARE NOT already guarded by Route::has()
    // We look for the start of the call and ensure it's not preceded by 'Route::has(...)? '
    preg_match_all("/(?<!Route::has\(['\"][^'\"]+['\"]\)\s\?\s)route\(\s*['\"]([^'\"]+)['\"]/", $content, $matches);
    
    if (!empty($matches[1])) {
        foreach ($matches[1] as $routeName) {
            // Skip dynamic routes or variables
            if (str_contains($routeName, '$')) continue;
            
            if (!isset($validRouteNames[$routeName])) {
                // Double check if it's guarded by searching for the Route::has check for this specific route
                $isGuarded = str_contains($content, "Route::has('$routeName')") || str_contains($content, "Route::has(\"$routeName\")");
                
                if (!$isGuarded) {
                    $relativeFile = str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $pathname);
                    $results[$relativeFile][] = $routeName;
                }
            }
        }
    }
}

if (empty($results)) {
    echo "SUCCESS: No broken routes found in Blade files.\n";
} else {
    echo "FAILURE: Found broken routes in Blade files:\n\n";
    foreach ($results as $file => $routes) {
        echo "File: $file\n";
        foreach (array_unique($routes) as $route) {
            echo "  - Broken Route: $route\n";
        }
        echo "\n";
    }
}
