# Mayush Search Execution Inventory

Checkpoint: current working tree on branch `codex/search-enhancement` after the local validation pass on 2026-07-28

This inventory records the state after the local checkpoint and before any OpenSearch implementation. It is intentionally evidence-based; items marked as blocked require an external environment or human approval.

| Workstream | Current status | Existing files | Remaining work | Dependencies | Risk | Assigned owner |
| --- | --- | --- | --- | --- | --- | --- |
| Phase 0 baseline, flags and telemetry | Complete and validated | `config/search.php`, `app/Services/SearchQueryNormalizer.php`, `app/Services/SearchTelemetry.php`, dataset/baseline docs and unit tests | Expand the labeled relevance set when representative product data is approved | Dataset owner and business reviewer | Metrics may be too small for reliable relevance decisions | Lead + relevance reviewer |
| Phase 1 MySQL fallback | Complete and locally validated | `app/Http/Controllers/SearchController.php`, API product/suggestion controllers and regression tests, `SearchDisabledFeatureContractTest` | Address specialist-review follow-ups, then perform independent production-safe smoke validation | Separate production approval and current-migration fixture | Wildcard escaping, history privacy, facet visibility and malformed-input follow-ups remain open | MySQL/security track |
| Phase 2 frontend UX | Implemented; PHP/Blade tests, full PHPUnit, direct build and manifest validation pass | `resources/views/frontend/layouts/app.blade.php`, `resources/views/frontend/product_listing.blade.php`, UX contract test | Run browser/accessibility checks in an isolated current-migration fixture; repair npm wrapper if required | Working local/staging browser fixture | Dynamic JS behavior is not proven by source-contract tests alone | Frontend UX track |
| Relevance dataset | Initial specification only | `docs/search-relevance-dataset.md` | Assign owner/reviewer, collect anonymized queries/products, label and version the set, add confidence methodology | Business/data approval and representative data | Optimizing against a biased sample can worsen relevance | Relevance track |
| OpenSearch capacity assessment | Partially documented; decision blocked | `docs/search-opensearch-capacity-assessment.md`; no OpenSearch service or integration exists | Reconcile the local dataset, capture target capacity, expected index size, traffic, cost and deployment options | Read-only production metrics and infrastructure approval | Same-server installation could starve MySQL/Redis/workers | DevOps/infrastructure track |
| Local OpenSearch proof of concept | Blocked before environment is provided | No OpenSearch client, index or service | Validate connectivity, mappings, analyzers, filters, facets, aliases, recovery and fallback in isolation | Capacity decision and isolated OpenSearch environment | No evidence currently supports OpenSearch adoption | OpenSearch architecture track |
| Transactional outbox/indexing | Not started | No search outbox, index worker or versioning path exists | Design and implement only after the engine gate passes; define freshness SLAs and bulk-update behavior | Successful POC and selected deployment architecture | Duplicate or out-of-order indexing can expose stale products/prices | Indexing reliability track |
| Shadow validation/rollout | Not started | Flags exist but no shadow executor exists | Add asynchronous sampled comparison after disabled production integration | Production-disabled integration, telemetry and approved timeout | Shadow traffic must never affect customer latency | Release/observability track |
| Semantic/related results | Disabled and not part of OpenSearch work | Existing semantic path is opt-in and guarded; no new AI work in this checkpoint | Reassess only after lexical search and relevance data are stable | Lexical acceptance and explicit approval | Semantic results can introduce unrelated or unauthorized products | Search relevance/product owner |
| Final global regression | Pending | Existing relevant test suites and docs | Run full relevant local suite, build/browser checks, then separate production smoke and business monitoring | All preceding customer-facing gates | Shared layout/search changes can affect cart, checkout, seller and account flows | Lead + release reviewer |

## File ownership allocation

Only one workstream may edit a file at a time.

| Owner | Files in scope | Excluded files |
| --- | --- | --- |
| MySQL/security | Search controllers, API search controllers and related backend tests | OpenSearch mappings, frontend layout and production infrastructure |
| Frontend UX | Shared frontend layout, product listing template, frontend search tests and search-specific styles/scripts | Backend search controllers and OpenSearch code |
| Relevance/data | Relevance dataset specification, fixtures and evaluation tooling | Runtime search ranking and production data |
| OpenSearch architecture | New POC-only integration/mapping files after the POC environment exists | MySQL fallback and frontend UX files |
| Indexing reliability | Outbox migration/model/jobs/commands and indexing tests after engine selection | Existing completed MySQL/frontend files unless lead approves a sequential patch |
| DevOps | Capacity and deployment assessment documents/scripts only | Production configuration and services without explicit approval |
| Lead/integration review | Cross-workstream integration, acceptance docs and final tests | Unrelated existing worktree changes |

## Evidence and constraints at checkpoint

- Branch creation succeeded and the work is isolated on `codex/search-enhancement`.
- The search checkpoint commit contains only search-related changes. Existing `.phpunit.cache/test-results`, `tests/performance_log.json`, and `reports/mixed-french-english-products.csv` remain outside that commit.
- Local focused and full validation passed: the full PHPUnit suite reports 1,022 tests and 3,264 assertions; `php artisan view:cache`, direct storefront build, manifest validation and `git diff --check` passed.
- `npm run build:storefront` did not start because the local npm installation is missing `npm-cli.js`. This is a tooling validation gap, not evidence that the frontend build passed.
- The current read-only database check reports 77 tables and 1.30 MiB with no representative marketplace data. Earlier populated counts are retained as historical evidence only.
- No OpenSearch installation, index, client integration, production configuration, reindex, shadow request or primary activation has occurred.
- The incremental disabled-feature action passed 108 relevant tests and 253 assertions; it added only the behavioral semantic-disabled-to-MySQL fallback contract test.
- Apache/httpd and MySQL were observed running. No service was changed by this task.
