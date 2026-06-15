<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$attr = \App\Models\Attribute::where('name', 'Dimension')->first();
if (!$attr) {
    $attr = new \App\Models\Attribute;
    $attr->name = 'Dimension';
    $attr->save();

    $trans = new \App\Models\AttributeTranslation;
    $trans->attribute_id = $attr->id;
    $trans->lang = 'en';
    $trans->name = 'Dimension';
    $trans->save();
}
echo $attr->id;
