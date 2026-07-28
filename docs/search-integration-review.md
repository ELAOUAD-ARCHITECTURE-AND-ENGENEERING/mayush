# Search Integration Review at Checkpoint

Review basis: branch `codex/search-enhancement`, current local implementation and specialist reviews completed on 2026-07-28. The review was limited to the repository and local environment; no production system was changed.

## Verified facts

- The branch is isolated from the starting worktree.
- Search changes are committed in bounded checkpoints; unrelated `.phpunit.cache/test-results`, `tests/performance_log.json` and `reports/mixed-french-english-products.csv` remain untouched and uncommitted.
- MySQL remains the configured default backend.
- All OpenSearch/semantic/autocomplete/business-ranking rollout flags default to false.
- No OpenSearch client package, service, index, mapping, outbox, index worker, shadow executor, reindex command or production service change exists in this checkpoint.
- Existing customer-facing search endpoints and response shapes remain in place; focused frontend, backend and API regression tests pass.
- Improved frontend behavior is now gated by `SEARCH_UX_V2`; the flag-off branch keeps a minimal safe autocomplete binding.
- Overlong queries are bounded and return no products instead of silently becoming an unfiltered listing.
- Arabic/Darija/Arabizi vocabulary expansion is documented but inactive; digits remain preserved.
- MySQL relevance ordering, including autocomplete ordering, is independently guarded by `MYSQL_IMPROVED_SEARCH`.
- Listing URL synchronization now occurs after first-render category, brand and preorder parameters are added.
- The local database measurement was rechecked read-only: `amsadesign_db` currently has 77 tables and 1.30 MiB, with empty marketplace tables. An earlier populated count is historical and not currently reproducible.

## Confirmed validation gaps

- The repository’s `npm run` wrapper is broken because its npm CLI file is missing. The direct storefront build and manifest validation pass with existing `node_modules`.
- Apache local HTTP smoke reaches the application but fails against the populated development MySQL schema because `shops.approval_status` is missing while the current visibility scope expects it. The development database has many pending migrations; no migration was run as an incidental search change.
- Browser-level keyboard, mobile and RTL acceptance has not been completed in a current-migration local/staging fixture.
- Production capacity, traffic, backup storage, cost and service ownership are not verified.
- No isolated OpenSearch endpoint is available; therefore no mapping/analyzer/facet/filter/alias/snapshot/fallback POC evidence exists.

## Backward compatibility review

- Search routes and API endpoints were not renamed.
- Existing AJAX response values (`'0'` for no autocomplete result and the existing product-listing JSON shape) remain supported.
- Existing MySQL is still the only active backend.
- No checkout, cart, wishlist, login, seller, payment, order or API flow outside search was intentionally modified.

## Required next decision

Do not begin OpenSearch integration or production deployment. First prepare an isolated local or staging database at the current migration level, complete browser/HTTP acceptance, assign the relevance dataset owner, and obtain the infrastructure capacity/cost decision and isolated OpenSearch endpoint. Only then can the POC gate be evaluated.
