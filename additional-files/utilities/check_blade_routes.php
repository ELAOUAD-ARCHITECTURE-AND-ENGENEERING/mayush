<?php
$routes_json = shell_exec('php artisan route:list --json');
$routes_data = json_decode($routes_json, true);

$available_routes = [];
if ($routes_data) {
    foreach ($routes_data as $route) {
        if (!empty($route['name'])) {
            $available_routes[$route['name']] = true;
        }
    }
}

$directories = [
    'resources/views/frontend',
    'resources/views/backend',
    'resources/views/seller',
    'resources/views/header'
];

$missing_routes = [];

foreach ($directories as $dir) {
    if (!is_dir($dir)) continue;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && strpos($file->getFilename(), '.blade.php') !== false) {
            $content = file_get_contents($file->getPathname());
            
            // Match route('route.name')
            preg_match_all('/route\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $routeName) {
                    if (!isset($available_routes[$routeName])) {
                        $missing_routes[$routeName][] = $file->getPathname();
                    }
                }
            }
        }
    }
}

echo "Missing Routes in Blade Templates:\n";
foreach ($missing_routes as $routeName => $files) {
    echo "Route: $routeName\n";
    foreach(array_unique($files) as $f) {
        echo "  - $f\n";
    }
    echo "---------------------------------\n";
}
?>