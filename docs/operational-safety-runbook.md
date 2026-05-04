# Mayush Automated Operational Safety Runbook

This runbook documents the automated controls that prevent the code/schema/config drift that caused the 2026-04-30 outage.

Manual GitHub UI setup is intentionally separated in `docs/github-branch-ruleset-manual.md`.

## Enforced Rules

| Rule | Automatic control |
| --- | --- |
| Do not deploy without a fresh database backup | `.github/workflows/deploy.yml` creates a timestamped `mysqldump` before `php artisan migrate --force` |
| Do not deploy with broken runtime settings | `php artisan app:preflight-restore` runs before and after migrations |
| Keep public Blog access available | Deploy runs `BlogNavigationSeeder`, then validates `--require-blog-navigation` |
| Keep dynamic view selectors safe | CI blocks known unsafe selector patterns and the app uses safe helpers |
| Keep code clean before merge | Quality gates validate Composer, routes, audits, and focused tests |
| Protect against destructive deletions | `scripts/ci/check-operational-guardrails.php` fails CI for protected file/path deletions and mass deletion bursts |
| Keep guardrail automation present | CI verifies the guardrail workflow, deploy backup/preflight tokens, scripts, and docs exist |

## Automatic CI Guardrails

GitHub Actions runs:

- `.github/workflows/quality-gates.yml`
- `.github/workflows/restoration-guardrails.yml`

The restoration workflow runs:

```bash
php artisan app:preflight-restore --skip-db --no-ansi
php scripts/ci/check-operational-guardrails.php
```

Run the same guard locally with:

```powershell
composer guardrails
```

To compare a branch against a base commit locally:

```powershell
$env:GUARDRAIL_BASE = "origin/main"
$env:GUARDRAIL_HEAD = "HEAD"
composer guardrails
```

Do not set `ALLOW_DESTRUCTIVE_CHANGES=true` unless an emergency deletion has been explicitly approved and reviewed.

## Automatic Deployment Guardrails

Production deploy automatically performs:

1. Clears runtime/view caches.
2. Creates a pre-migration MySQL backup with `mysqldump --single-transaction --quick --routines --triggers`.
3. Runs `php artisan app:preflight-restore --require-redis --allow-pending-migrations`.
4. Runs `php artisan migrate --force`.
5. Runs `php artisan db:seed --class=BlogNavigationSeeder --force`.
6. Runs `php artisan app:preflight-restore --require-redis --require-blog-navigation`.
7. Restarts queues and Horizon.

## Required Runtime Settings

The application must have these `business_settings` records:

- `homepage_select`
- `header_element`
- `authentication_layout_select`
- `system_default_currency`
- `no_of_decimals`
- `decimal_separator`
- `header_menu_labels`
- `header_menu_links`

The app has safe fallbacks for the critical dynamic views, but missing settings are still treated as deployment failures by preflight.

## Redis and Horizon Policy

If any of these are set to `redis`, Redis must be reachable before deployment:

- `QUEUE_CONNECTION`
- `CACHE_DRIVER`
- `SESSION_DRIVER`

Preflight automatically checks Redis when queue/cache/session are Redis-backed or when deploy passes `--require-redis`.

Manual diagnostic command:

```powershell
php artisan app:preflight-restore --require-redis
php artisan horizon:status
```

If local development does not use Redis, keep:

```env
QUEUE_CONNECTION=sync
CACHE_DRIVER=file
SESSION_DRIVER=file
```

and do not run Horizon locally.

## Rollback Procedure

Code rollback:

```powershell
git fetch origin
git switch main
git reset --hard origin/main
php artisan optimize:clear
powershell -ExecutionPolicy Bypass -File scripts/maintenance/smoke-check.ps1
```

Database rollback:

1. Restore the latest known-good SQL backup.
2. Clear caches.
3. Run the smoke checks.

Do not roll back only code or only database when migrations changed schema contracts.

## Manual Controls

The only remaining manual control is GitHub repository branch/ruleset protection because it lives in GitHub account settings, outside the repository code. Configure it once using `docs/github-branch-ruleset-manual.md`.
