# Search Enhancement Phase 1 — MySQL Fallback Report

## Scope

Phase 1 corrected the current MySQL-backed search path without adding or
activating OpenSearch:

- bounded query normalization with original-query preservation;
- translated product matching in full-text paths;
- grouped multi-word matching for storefront and API search;
- bound parameters for name/product-name ranking expressions;
- explicit sort precedence over relevance ordering;
- opt-in semantic mode protection through public-product revalidation;
- semantic provider fallback to normal MySQL search;
- safe attribute filtering reuse;
- backend minimum and maximum autocomplete query bounds.

The `MYSQL_IMPROVED_SEARCH` flag controls optional relevance ordering. Security,
visibility, input-boundary and multi-word correctness guards remain active even
when that optional ranking flag is off, so rollback cannot restore the known
unsafe query behavior.

## Local validation

Command:

```text
php artisan test tests/Unit/Services/SearchQueryNormalizerTest.php tests/Unit/Services/SearchTelemetryTest.php tests/Integration/Controllers/Frontend/SearchControllerTest.php tests/Feature/Frontend/SearchFilterRegressionTest.php tests/Feature/ProductionReadiness/SearchFallbackTest.php tests/Feature/Security/RateLimitingTest.php tests/Integration/API/ApiV2ProductTest.php tests/Integration/API/SearchSuggestionTest.php
```

Result:

- 40 tests passed.
- 336 assertions passed.
- No failures.
- No risky tests.

Additional regression coverage confirms:

- API multi-word queries do not return products matching only the last term.
- API product suggestions require all meaningful terms.
- Opt-in semantic results exclude unpublished products.
- When semantic search is disabled, an AI-mode request returns lexical MySQL results without contacting the AI provider (`SearchDisabledFeatureContractTest`).

Current checkpoint regression also passed the production-readiness, search, API,
SQL-injection and XSS suites: 108 tests and 253 assertions.

## Production status

- No production deployment performed.
- OpenSearch remains uninstalled and disabled.
- No production `.env` or service configuration changed.
- Existing unrelated worktree changes remain preserved.

## Gate

Phase 1 local gate passed. The MySQL fallback is independently shippable after
the required production backup, staged deployment, smoke-test, and monitoring
workflow is completed. Phase 2 frontend work may begin locally.
