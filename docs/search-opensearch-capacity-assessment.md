# OpenSearch Capacity Assessment and POC Gate

Status: **not approved for installation or customer traffic**

This document records the read-only assessment available from the local Mayush environment. Production capacity and cost remain unknown until an authorized infrastructure owner supplies the production measurements. No OpenSearch service, index, client package, synchronization job, snapshot repository or production configuration was created.

## Local evidence collected

The local database evidence is currently inconsistent and must not be used as a capacity forecast. An earlier read-only capture recorded a populated 202-table database, while the authoritative read-only check performed on **2026-07-28** against `amsadesign_db` returned 77 tables, 1.30 MiB and zero marketplace rows in the key product, translation, seller and variant tables. The earlier capture is retained below as historical evidence only; the populated dataset must be re-established or supplied as an anonymized fixture before the POC relevance and sizing gates can be evaluated.

| Area | Observation | Evidence/command | Interpretation |
| --- | --- | --- | --- |
| Current database check | 77 tables, approximately 1.30 MiB total; key marketplace tables are empty | `php artisan db:show --counts` on 2026-07-28 | Current local database is not a representative search POC dataset |
| Historical database capture | 202 tables, approximately 201.89 MiB total | Earlier `php artisan db:show --counts` capture | Historical only; counts are not currently reproducible and must be independently revalidated |
| Historical product snapshot | 891 products; 1,352 translations; 2,267 stock/variant rows | Earlier `php artisan db:show --counts` capture | Useful as a fixture-shape reference, not evidence of current local data or production scale |
| Historical seller/category/attribute snapshot | 77 users; 35 shops; 423 categories; 33 attributes; 1,312 attribute values | Earlier `php artisan db:show --counts` capture | Visibility and facet fixtures still need a controlled representative dataset |
| Historical search/semantic rows | 113 searches; 426 `semantic_embeddings` rows | Earlier `php artisan db:show --counts` capture | Not enough for popularity conclusions and not evidence that OpenSearch is installed or suitable |
| Search dependencies | No `vendor/elasticsearch/elasticsearch` or `vendor/laravel/scout` directory; neither is a direct `composer.json` dependency | `Test-Path vendor\...`; `Get-Content composer.json` | Composer-lock references are transitive/dev metadata, not an available OpenSearch integration |
| Container tooling | Docker CLI 29.4.3 is installed, but the Docker daemon is not reachable | `docker --version`; `docker info` | A local POC needs Docker Desktop/daemon access or another isolated engine |
| Current services | Apache/httpd, MySQL and multiple existing Node processes were observed | `Get-Process httpd,mysqld,redis-server,php,node` | No process was changed; production resource impact is not inferred from these local processes |
| Local build tooling | `npm run build:storefront` could not start because `npm-cli.js` is missing from the local npm installation | exact build command | Frontend build gate is still open; this is unrelated to OpenSearch suitability |
| Host RAM/CPU/disk | Not measurable from this sandbox: CIM, volume and disk-free queries returned access denied | `Get-CimInstance ...`; `fsutil volume diskfree C:` | Do not estimate same-server OpenSearch capacity from incomplete host data |

## Required production/staging measurements

An infrastructure owner must capture these values from the actual target host or managed-service quote and attach the timestamp, host size, region and measurement window:

- Total and available RAM, CPU cores, load average/CPU saturation and swap activity.
- Disk size, free disk, IOPS/latency and projected monthly index growth.
- MySQL memory/CPU/slow-query pressure and backup window.
- Redis memory/eviction state, Horizon worker count/CPU and Reverb resource use.
- Apache/PHP-FPM request rate and search endpoint p50/p95/p99 latency.
- Search request volume by hour, peak concurrency, autocomplete rate and expected growth.
- Product, translation, seller, category, attribute, variant, promotion, stock and visibility counts.
- Backup storage capacity, retention, restore time objective and snapshot destination.
- Same-server, separate-VPS and managed-service monthly cost, including storage, backups, support and monitoring.

## Deployment options requiring approval

| Option | Current decision | Minimum evidence required |
| --- | --- | --- |
| Same production server | No-go for now | Measured headroom after reserving MySQL/Redis/Horizon/Reverb capacity, memory limits, disk/IOPS test, restart/rollback plan and owner approval |
| Separate VPS | Candidate | Capacity-sized staging test, private network/TLS plan, backup/monitoring ownership and monthly cost |
| Managed OpenSearch | Candidate | Provider quote, data-location/security review, latency test, backup/restore test and exit plan |
| Remain on MySQL | Safe fallback and valid outcome | Baseline improvements and relevance/latency results showing that added infrastructure is not justified |
| Typesense proof of concept | Alternative candidate | Same relevance dataset, Arabic/Darija tests, filter/facet/latency test and operational-cost comparison |

## Isolated POC prerequisites

The POC may proceed only after the infrastructure decision is recorded and an isolated environment is available. It must not use production traffic or production data without approved anonymization.

1. Provide a local or isolated staging OpenSearch endpoint with authentication and TLS.
2. Load an anonymized snapshot containing English, French, Arabic, Darija/Arabizi, incomplete translations, variants, dimensions, prices, stock, promotions, sellers and visibility states.
3. Compare exact identifier, lexical, multilingual, filter, facet, sort, pagination, no-result and malformed-query behavior with the MySQL baseline.
4. Test index creation, mappings, analyzers, updates, deletes, aliases, full reindex, incremental updates, bulk updates, partial failures, retry/dead-letter behavior and index lag.
5. Test snapshot creation, verification and restoration in the isolated environment.
6. Stop or make the endpoint unavailable and prove that the Laravel application returns to MySQL within the configured failure budget.
7. Record relevance metrics, confidence intervals, latency distributions, resource consumption, failure rates, operating cost and owner sign-off.

## POC go/no-go gate

OpenSearch remains rejected unless all of the following are evidenced:

- Measurable relevance improvement on the versioned multilingual dataset, without an unjustified arbitrary regression threshold.
- Correct visibility, seller approval/ban, product-type, variant, price, stock and promotion filtering.
- Acceptable Arabic, French, English, Darija and Arabizi behavior, including protection of digits used in prices, dimensions, SKUs and model numbers.
- Complete failure fallback to MySQL with no customer-visible dependency on OpenSearch availability.
- Indexing, bulk-update, version-ordering, repair, snapshot and restoration behavior passes.
- Capacity headroom and total cost are approved for the selected deployment model.
- A named operational owner, alert runbook, rollback commands and backup/restore owner are approved.

Until this gate passes, the required effective state remains:

```text
SEARCH_BACKEND=mysql
OPENSEARCH_POC_ENABLED=false
OPENSEARCH_SHADOW_ENABLED=false
OPENSEARCH_PRIMARY_ENABLED=false
AUTOCOMPLETE_OPENSEARCH_ENABLED=false
AI_SEARCH_ENABLED=false
```
