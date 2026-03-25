<?php

$controllers = [
    'App\Http\Controllers\Preorder\DashboardController',
    'App\Http\Controllers\Preorder\seller\DashboardController',
    'App\Http\Controllers\Preorder\seller\OrderController',
    'App\Http\Controllers\Preorder\seller\PreorderCommissionHistoryController',
    'App\Http\Controllers\Preorder\seller\PreorderController',
    'App\Http\Controllers\Preorder\seller\PreorderConversationController',
    'App\Http\Controllers\Preorder\seller\PreorderProductController',
    'App\Http\Controllers\Preorder\seller\PreorderProductQueryController',
    'App\Http\Controllers\Preorder\seller\PreorderProductReviewController',
];

foreach ($controllers as $class) {
    $path = __DIR__ . '/app/Http/Controllers/' . str_replace(['App\\Http\\Controllers\\', '\\'], ['', '/'], $class) . '.php';
    
    if (!file_exists($path)) {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        
        $parts = explode('\\', str_replace('App\\Http\\Controllers\\', '', $class));
        $className = array_pop($parts);
        $namespace = 'App\\Http\\Controllers\\' . implode('\\', $parts);
        
        $content = "<?php\n\nnamespace $namespace;\n\nuse App\Http\Controllers\Controller;\nuse Illuminate\Http\Request;\n\nclass $className extends Controller\n{\n    public function index()\n    {\n        return 'Stub';\n    }\n}\n";
        
        file_put_contents($path, $content);
        echo "Created $path\n";
    }
}
