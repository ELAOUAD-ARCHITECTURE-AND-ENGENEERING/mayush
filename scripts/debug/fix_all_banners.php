<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\BusinessSetting;

// Show ALL entries for home_banner1_images (including lang-specific ones)
$all = BusinessSetting::where('type', 'home_banner1_images')->get();
echo "=== ALL home_banner1_images entries ===\n";
foreach ($all as $entry) {
    echo "ID: {$entry->id}, type: {$entry->type}, lang: {$entry->lang}, value: {$entry->value}\n";
}

// Now update ALL of them to use the new image IDs [5074, 5075]
$newImages = json_encode([5074, 5075]);
$updated = BusinessSetting::where('type', 'home_banner1_images')->update(['value' => $newImages]);
echo "\nUpdated {$updated} rows to use new images: {$newImages}\n";

// Also update titles, descriptions, cta_texts for ALL lang entries
$titles = json_encode(['Découvrez la collection Automne', 'Des pièces uniques pour votre salon']);
$descriptions = json_encode(['Transformez votre intérieur avec nos meubles au design contemporain et matériaux nobles.', "Créez une atmosphère chaleureuse avec notre sélection exclusive d'articles de décoration."]);
$ctas = json_encode(['Acheter maintenant', 'Explorer la collection']);
$links = json_encode(['/search', '/search']);

// Create or update for all lang variations
foreach (['home_banner1_titles', 'home_banner1_descriptions', 'home_banner1_cta_texts', 'home_banner1_links'] as $key) {
    $existing = BusinessSetting::where('type', $key)->get();
    echo "\n=== {$key}: " . count($existing) . " entries ===\n";
    foreach ($existing as $e) {
        echo "  ID: {$e->id}, lang: {$e->lang}, value: " . substr($e->value, 0, 80) . "\n";
    }
}

// Upsert for each lang that has home_banner1_images
$langs = BusinessSetting::where('type', 'home_banner1_images')->pluck('lang')->unique();
foreach ($langs as $lang) {
    $langFilter = $lang ? ['type' => 'home_banner1_titles', 'lang' => $lang] : ['type' => 'home_banner1_titles'];
    BusinessSetting::updateOrCreate($langFilter, ['value' => $titles]);
    
    $langFilter = $lang ? ['type' => 'home_banner1_descriptions', 'lang' => $lang] : ['type' => 'home_banner1_descriptions'];
    BusinessSetting::updateOrCreate($langFilter, ['value' => $descriptions]);
    
    $langFilter = $lang ? ['type' => 'home_banner1_cta_texts', 'lang' => $lang] : ['type' => 'home_banner1_cta_texts'];
    BusinessSetting::updateOrCreate($langFilter, ['value' => $ctas]);
    
    $langFilter = $lang ? ['type' => 'home_banner1_links', 'lang' => $lang] : ['type' => 'home_banner1_links'];
    BusinessSetting::updateOrCreate($langFilter, ['value' => $links]);
}

echo "\nDone! All lang entries updated.\n";

// Clear the cache
Illuminate\Support\Facades\Cache::forget('business_settings');
Illuminate\Support\Facades\Artisan::call('cache:clear');
echo "Cache cleared.\n";

// Verify
$allAfter = BusinessSetting::where('type', 'home_banner1_images')->get();
echo "\n=== VERIFY home_banner1_images after update ===\n";
foreach ($allAfter as $entry) {
    echo "ID: {$entry->id}, lang: {$entry->lang}, value: {$entry->value}\n";
}
