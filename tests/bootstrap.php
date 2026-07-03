<?php

require __DIR__.'/../vendor/autoload.php';

if (!getenv('GEMINI_API_KEY')) {
    $envPath = __DIR__.'/../.env';

    if (is_readable($envPath)) {
        foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (!str_starts_with($line, 'GEMINI_API_KEY=')) {
                continue;
            }

            $value = trim(substr($line, strlen('GEMINI_API_KEY=')));
            $value = trim($value, "\"'");

            if ($value !== '') {
                putenv("GEMINI_API_KEY={$value}");
                $_ENV['GEMINI_API_KEY'] = $value;
                $_SERVER['GEMINI_API_KEY'] = $value;
            }

            break;
        }
    }
}
