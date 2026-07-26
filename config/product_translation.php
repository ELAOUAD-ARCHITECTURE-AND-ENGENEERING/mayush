<?php

return [
    'source_language' => env('PRODUCT_TRANSLATION_SOURCE_LANGUAGE', 'fr'),
    'target_language' => env('PRODUCT_TRANSLATION_TARGET_LANGUAGE', 'ma'),
    'provider' => 'openrouter',
    'translation_target_language' => 'ar',
    'queue' => env('PRODUCT_TRANSLATION_QUEUE', 'translations'),
    // Translation runs are long-lived and must use their own supervised Redis
    // connection. This keeps QUEUE_CONNECTION and unrelated application
    // queues unchanged while giving translation jobs a safe visibility window.
    'queue_connection' => env('PRODUCT_TRANSLATION_QUEUE_CONNECTION', 'redis_translations'),
    'worker_timeout' => (int) env('PRODUCT_TRANSLATION_WORKER_TIMEOUT', 480),
    'fields' => ['name', 'unit', 'description', 'meta_title', 'meta_description', 'meta_keywords'],
    'required_fields' => ['name'],
    'chunk_size' => 100,
    'quota_retry_delay' => (int) env('PRODUCT_TRANSLATION_QUOTA_RETRY_DELAY', 60),
    'temporary_retry_delay' => (int) env('PRODUCT_TRANSLATION_TEMPORARY_RETRY_DELAY', 15),
    'max_item_attempts' => (int) env('PRODUCT_TRANSLATION_MAX_ITEM_ATTEMPTS', 3),
    'cache_ttl' => (int) env('PRODUCT_TRANSLATION_CACHE_TTL', 2592000),
];
