<?php

return [
    'source_language' => env('PRODUCT_TRANSLATION_SOURCE_LANGUAGE', 'fr'),
    'target_language' => env('PRODUCT_TRANSLATION_TARGET_LANGUAGE', 'ma'),
    'azure_target_language' => 'ar',
    'queue' => env('PRODUCT_TRANSLATION_QUEUE', 'translations'),
    'fields' => ['name', 'unit', 'description'],
    'required_fields' => ['name'],
    'chunk_size' => 100,
];
