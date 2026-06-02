<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';

use Intervention\Image\ImageManager;

$manager = new ImageManager(['driver' => 'gd']);
$paths = [
    'assets/img/office_furniture_4k.png',
    'assets/img/cards/verified_by_visa.png',
    'assets/img/cards/secure_code.png',
    'assets/img/cards/amex.png',
    'assets/img/cards/cmi.png',
    'assets/img/cards/marocpay.png',
    'assets/img/cards/unionpay.png',
];

foreach ($paths as $path) {
    $source = dirname(__DIR__, 2).'/public/'.$path;
    $info = pathinfo($path);
    $output = dirname(__DIR__, 2).'/public/'.$info['dirname'].'/'.$info['filename'].'_card.webp';
    $image = $manager->make($source);
    $image->resize(480, 480, function ($constraint): void {
        $constraint->aspectRatio();
        $constraint->upsize();
    });
    $image->encode('webp', 80)->save($output);

    echo basename($output).PHP_EOL;
}
