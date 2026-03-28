<?php
$files = array_merge(
    glob('resources/views/header/*.blade.php'),
    ['resources/views/frontend/inc/nav.blade.php', 'resources/views/frontend/inc/footer.blade.php']
);

foreach ($files as $f) {
    if (file_exists($f)) {
        $content = file_get_contents($f);
        $content = str_replace('customer.all-notifications', 'all-notifications', $content);
        file_put_contents($f, $content);
    }
}
echo "Replaced customer.all-notifications\n";
