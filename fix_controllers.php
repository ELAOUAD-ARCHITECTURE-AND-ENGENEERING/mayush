<?php

$routesDir = __DIR__ . '/routes';
$controllersDir = __DIR__ . '/app/Http/Controllers';

$routeFiles = glob($routesDir . '/*.php');

foreach ($routeFiles as $routeFile) {
    $content = file_get_contents($routeFile);
    
    // Find all "use App\Http\Controllers\X;"
    preg_match_all('/use App\\\\Http\\\\Controllers\\\\([a-zA-Z0-9_\\\\]+);/', $content, $matches);
    
    if (!empty($matches[1])) {
        foreach ($matches[1] as $controllerName) {
            $controllerPath = $controllersDir . '/' . str_replace('\\', '/', $controllerName) . '.php';
            
            if (!file_exists($controllerPath)) {
                echo "Missing: $controllerName\n";
                
                $parts = explode('\\', $controllerName);
                $className = array_pop($parts);
                $namespace = 'App\\Http\\Controllers';
                if (!empty($parts)) {
                    $namespace .= '\\' . implode('\\', $parts);
                    $dir = $controllersDir . '/' . implode('/', $parts);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0777, true);
                    }
                }
                
                $stub = "<?php\n\nnamespace $namespace;\n\nuse App\Http\Controllers\Controller;\nuse Illuminate\Http\Request;\n\nclass $className extends Controller\n{\n    public function index()\n    {\n        return 'Stub';\n    }\n    public function store(Request \$request)\n    {\n        return 'Stub';\n    }\n    public function update(Request \$request)\n    {\n        return 'Stub';\n    }\n    public function destroy(\$id)\n    {\n        return 'Stub';\n    }\n}\n";
                
                file_put_contents($controllerPath, $stub);
                echo "Created stub for $controllerName\n";
            }
        }
    }
}
