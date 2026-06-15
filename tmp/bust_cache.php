<?php
$files = [
    'resources/views/seller/product/products/edit.blade.php',
    'resources/views/seller/product/products/create.blade.php',
    'resources/views/seller/product/digitalproducts/edit.blade.php',
    'resources/views/seller/product/digitalproducts/create.blade.php',
    'resources/views/preorder/seller/products/edit.blade.php',
    'resources/views/preorder/seller/products/create.blade.php',
    'resources/views/preorder/backend/products/edit.blade.php',
    'resources/views/preorder/backend\products\create.blade.php',
    'resources/views/backend/product/products/edit.blade.php',
    'resources/views/backend/product/products/create.blade.php',
    'resources/views/backend/product/digital_products/edit.blade.php',
    'resources/views/backend/product/digital_products/create.blade.php'
];

foreach($files as $file) {
    $path = __DIR__ . '/../' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        $content = str_replace(
            '<script src="{{ static_asset(\'assets/js/hummingbird-treeview.js\') }}"></script>', 
            '<script src="{{ static_asset(\'assets/js/hummingbird-treeview.js\') }}?v={{ filemtime(public_path(\'assets/js/hummingbird-treeview.js\')) }}"></script>',
            $content
        );
        file_put_contents($path, $content);
        echo "Updated $file\n";
    }
}
