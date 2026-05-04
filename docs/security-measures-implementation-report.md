# Security and Stability Measures Implementation Report

**Date:** 2026-04-30  
**Scope:** Prevent recurrence of code, schema, configuration, and deployment drift that can take the Mayush application offline.

## Implemented Measures

### 1. Runtime Preflight Guard

Added Artisan command:

```bash
php artisan app:preflight-restore
```

File:

- `app/Console/Commands/PreflightRestoreGuard.php`

The command validates:

- Database connectivity.
- Required `business_settings` records.
- Valid homepage, auth, and header views.
- Valid header menu JSON and optional public Blog navigation.
- Valid default currency.
- Pending migration state.
- Redis availability when Redis is required by queue/cache/session or when `--require-redis` is used.

Useful modes:

```bash
php artisan app:preflight-restore
php artisan app:preflight-restore --allow-pending-migrations
php artisan app:preflight-restore --require-blog-navigation
php artisan app:preflight-restore --require-redis
php artisan app:preflight-restore --skip-db
```

### 2. Safe Dynamic View Fallbacks

Added helper fallbacks:

- `safe_homepage_select()`
- `safe_auth_layout_select()`
- `safe_header_view()`

Updated critical paths to use safe fallbacks:

- `app/Http/Controllers/HomeController.php`
- `resources/views/frontend/inc/nav.blade.php`
- `resources/views/auth/login.blade.php`

This prevents crashes like:

- `View [header.] not found`
- `View [auth..admin_login] not found`
- invalid homepage paths from empty `homepage_select`

### 3. Safer Default Currency Handling

Updated `get_system_default_currency()` so missing currency configuration no longer immediately crashes storefront rendering. It now tries:

1. `system_default_currency`
2. first active currency
3. safe fallback object

The preflight command still treats a missing configured currency as a failure.

### 4. Database Backup Script

Added:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/maintenance/backup-database.ps1
```

File:

- `scripts/maintenance/backup-database.ps1`

The script reads `.env`, uses `mysqldump`, and writes a timestamped backup under:

```text
storage/app/backups/
```

Validation completed successfully with backup:

```text
storage/app/backups/amsadesign_db-before-change-20260430-162027.sql
```

### 5. Smoke Check Script

Added:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/maintenance/smoke-check.ps1
```

File:

- `scripts/maintenance/smoke-check.ps1`

Default smoke targets:

- `/`
- `/admin`
- `/api/v2/blog-list`
- `/api/v2/categories`

### 6. Deployment Guardrails

Updated:

- `.github/workflows/deploy.yml`

The production deploy now:

1. Clears runtime caches.
2. Creates a pre-migration database backup.
3. Runs `php artisan app:preflight-restore --require-redis --allow-pending-migrations`.
4. Runs migrations.
5. Runs `BlogNavigationSeeder` so public users can reach `/blog`.
6. Runs `php artisan app:preflight-restore --require-redis --require-blog-navigation` again after migrations.
7. Restarts queues/Horizon.

### 7. CI Guardrail Workflow

Added:

- `.github/workflows/restoration-guardrails.yml`

The workflow checks:

- Composer install.
- PHP syntax across tracked PHP files.
- Laravel route registration.
- Preflight command bootability.
- Unsafe dynamic view selector patterns.

### 8. Operational Runbook

Added:

- `docs/operational-safety-runbook.md`

The runbook documents:

- Required branch protection rules.
- Backup-before-migration procedure.
- Preflight procedure.
- Smoke checks.
- Redis/Horizon policy.
- Rollback procedure.

### 9. Composer Shortcut

Added Composer script:

```bash
composer preflight
```

This runs:

```bash
php artisan app:preflight-restore
```

## Validation Performed

Commands run successfully:

```bash
composer validate --no-check-publish
php -l app/Console/Commands/PreflightRestoreGuard.php
php -l app/Http/Helpers.php
php -l app/Http/Controllers/HomeController.php
php artisan route:list
php artisan view:cache
php artisan optimize:clear
php artisan app:preflight-restore
php artisan app:preflight-restore --skip-db --no-ansi
powershell -ExecutionPolicy Bypass -File scripts/maintenance/backup-database.ps1
powershell -ExecutionPolicy Bypass -File scripts/maintenance/smoke-check.ps1
vendor/bin/phpunit.bat tests/Feature/SeoRemediationTest.php --stop-on-failure
```

Results:

- Preflight passed.
- Smoke checks passed:
  - `/` returned `200 OK`
  - `/admin` returned `200 OK`
  - `/api/v2/blog-list` returned `200 OK`
  - `/api/v2/categories` returned `200 OK`
- SEO remediation tests passed: 9 tests, 52 assertions.
- Composer validation passed with existing dependency-version warnings.

## Remaining Manual Action

Configure GitHub branch protection for `main` in the GitHub UI:

- Require pull request before merge.
- Require at least one approval.
- Require status checks.
- Block force pushes.
- Block branch deletion.
- Restrict direct pushes to `main`.

Recommended required checks:

- `Mayush Quality Gates / App health`
- `Mayush Quality Gates / Composer audit`
- `Mayush Quality Gates / NPM production audit`
- `Mayush Restoration Guardrails / Static and Laravel guardrails`

## Operational Standard Going Forward

Before migrations:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/maintenance/backup-database.ps1
php artisan app:preflight-restore --allow-pending-migrations
php artisan migrate
php artisan db:seed --class=BlogNavigationSeeder --force
php artisan app:preflight-restore --require-blog-navigation
powershell -ExecutionPolicy Bypass -File scripts/maintenance/smoke-check.ps1
```

Before deployment:

```bash
php artisan app:preflight-restore --require-redis --allow-pending-migrations
```

After deployment:

```bash
php artisan app:preflight-restore --require-redis --require-blog-navigation
```
