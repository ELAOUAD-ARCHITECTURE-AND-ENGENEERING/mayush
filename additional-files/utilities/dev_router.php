<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Check if file exists in root
if ($uri !== '/' && file_exists(__DIR__.$uri)) {
    return false; // serve the file
}

require_once __DIR__.'/index.php';
