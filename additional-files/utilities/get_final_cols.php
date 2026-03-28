<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tables = ['commission_histories', 'seller_withdraw_requests', 'orders'];
$out = "";
foreach ($tables as $t) {
    $out .= "TABLE: $t\n";
    if (Schema::hasTable($t)) {
        $cols = Schema::getColumnListing($t);
        foreach ($cols as $c) {
            $out .= "  $c\n";
        }
    } else {
        $out .= "  MISSING\n";
    }
}
file_put_contents('final_schema.txt', $out);
echo "Done\n";
