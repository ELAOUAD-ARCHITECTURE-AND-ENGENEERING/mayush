<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Search backend and rollout controls
    |--------------------------------------------------------------------------
    |
    | MySQL remains the safe default. OpenSearch is deliberately opt-in until
    | the isolated proof-of-concept and production-readiness gates pass.
    |
    */
    'backend' => env('SEARCH_BACKEND', 'mysql'),

    'features' => [
        'improved_mysql' => (bool) env('MYSQL_IMPROVED_SEARCH', false),
        'frontend_v2' => (bool) env('SEARCH_UX_V2', false),
        'opensearch_poc' => (bool) env('OPENSEARCH_POC_ENABLED', false),
        'opensearch_shadow' => (bool) env('OPENSEARCH_SHADOW_ENABLED', false),
        'opensearch_primary' => (bool) env('OPENSEARCH_PRIMARY_ENABLED', false),
        'autocomplete_opensearch' => (bool) env('AUTOCOMPLETE_OPENSEARCH_ENABLED', false),
        'semantic' => (bool) env('AI_SEARCH_ENABLED', false),
        'related_results' => (bool) env('SEARCH_RELATED_RESULTS_ENABLED', false),
        'business_ranking' => (bool) env('BUSINESS_RANKING_V2', false),
    ],

    'locales' => [
        'supported' => ['en', 'fr', 'ar'],
        'default' => env('DEFAULT_LANGUAGE', 'fr'),
    ],

    'query' => [
        'min_length' => 2,
        'max_length' => 120,
        'max_terms' => 12,
        'minimum_should_match' => '70%',
    ],

    'autocomplete' => [
        'min_length' => 2,
        'max_length' => 80,
        'debounce_ms' => 300,
        'cache_seconds' => 300,
        'max_suggestions' => 8,
    ],

    'telemetry' => [
        'enabled' => (bool) env('SEARCH_TELEMETRY_ENABLED', true),
        'sample_rate' => (float) env('SEARCH_TELEMETRY_SAMPLE_RATE', 1.0),
        'store_raw_query' => false,
        'dataset_version' => env('SEARCH_DATASET_VERSION', 'v1'),
    ],

    'shadow' => [
        'enabled' => (bool) env('OPENSEARCH_SHADOW_ENABLED', false),
        'sample_rate' => (float) env('OPENSEARCH_SHADOW_SAMPLE_RATE', 0.05),
        // Selected from measured local/staging latency; do not hard-code a
        // production timeout before the OpenSearch proof of concept.
        'timeout_ms' => env('OPENSEARCH_SHADOW_TIMEOUT_MS'),
    ],

    'opensearch' => [
        'url' => env('OPENSEARCH_URL'),
        'username' => env('OPENSEARCH_USERNAME'),
        'password' => env('OPENSEARCH_PASSWORD'),
        'verify_tls' => (bool) env('OPENSEARCH_VERIFY_TLS', true),
        'connect_timeout_seconds' => (float) env('OPENSEARCH_CONNECT_TIMEOUT', 1.0),
        'timeout_seconds' => (float) env('OPENSEARCH_TIMEOUT', 2.0),
        'index_prefix' => env('OPENSEARCH_INDEX_PREFIX', 'mayush'),
    ],

    'freshness_sla_seconds' => [
        'visibility' => 60,
        'price_stock_promotion' => 120,
        'content_translation' => 300,
        'popularity' => 900,
    ],

    'ranking' => [
        'minimum_relevance_tier' => 'partial',
        'maximum_business_boost' => 0.20,
        'promotion_boost_ceiling' => 0.05,
        'sales_lookback_days' => 30,
        'conversion_minimum_samples' => 20,
    ],
];
