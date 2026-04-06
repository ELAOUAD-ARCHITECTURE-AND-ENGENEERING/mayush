<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$out = "";
$theme = get_setting('homepage_select');
$out .= "Active theme: {$theme}\n\n";

$partials = [
    'partials.todays_deal',
    'partials.newest_products_section',
    'partials.featured_products_section',
    'partials.best_selling_section',
    'partials.home_categories_section',
    'partials.best_sellers_section',
    'partials.preorder_products_section',
];

$viewBase = resource_path("views/frontend/{$theme}");
$out .= "View base: {$viewBase}\n\n";

foreach ($partials as $partial) {
    $file = $viewBase . '/' . str_replace('.', '/', $partial) . '.blade.php';
    $exists = file_exists($file) ? 'EXISTS' : 'MISSING';
    $out .= "  {$partial}: {$exists}\n";
}

$auctionFile = resource_path("views/auction/frontend/{$theme}/auction_products_section.blade.php");
$out .= "\n  auction_products_section: " . (file_exists($auctionFile) ? 'EXISTS' : 'MISSING') . "\n";

$eliteFile = resource_path("views/frontend/partials/elite_artisans_section.blade.php");
$out .= "  elite_artisans_section: " . (file_exists($eliteFile) ? 'EXISTS' : 'MISSING') . "\n";

$indexFile = $viewBase . '/index.blade.php';
$out .= "  index.blade.php: " . (file_exists($indexFile) ? 'EXISTS' : 'MISSING') . "\n";

$out .= "\nAvailable partials in {$theme}:\n";
$partialDir = $viewBase . '/partials';
if (is_dir($partialDir)) {
    foreach (glob($partialDir . '/*.blade.php') as $f) {
        $out .= "  - " . basename($f) . "\n";
    }
} else {
    $out .= "  Partials directory DOES NOT EXIST\n";
}

file_put_contents(__DIR__ . '/theme_check_output.txt', $out);
echo $out;
