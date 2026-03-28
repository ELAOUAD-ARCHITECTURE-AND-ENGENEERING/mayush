<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$res = [];
$deals = App\Models\FlashDeal::where('status', 1)->get();
foreach ($deals as $deal) {
    foreach ($deal->flash_deal_products as $fd) {
        $p = $fd->product;
        if (!$p) continue;
        $res[] = [
            'id' => $p->id,
            'name' => $p->getTranslation('name'),
            'unit_price' => $p->unit_price,
            'p_discount' => $p->discount,
            'p_discount_type' => $p->discount_type,
            'fd_discount' => $fd->discount,
            'fd_discount_type' => $fd->discount_type,
            'out_base' => home_base_price($p),
            'out_disc' => home_discounted_base_price($p)
        ];
    }
}
file_put_contents('debug.json', json_encode($res, JSON_PRETTY_PRINT));
