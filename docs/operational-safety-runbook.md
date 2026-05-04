# Mayush Operational Safety Runbook

This runbook prevents the code/schema/config drift that caused the 2026-04-30 outage.

## Non-Negotiable Rules

1. Do not commit directly to `main`.
2. Do not run migrations without a fresh database backup.
3. Do not deploy code and database changes separately unless a rollback plan is written first.
4. Do not merge changes that leave `php artisan app:preflight-restore` failing.
5. Do not commit unrelated mass deletions, especially `sqlupdates/`, without explicit review.

## Before Risky Local Work

```powershell
git switch main
git pull origin main
git switch -c feature/descriptive-name
powershell -ExecutionPolicy Bypass -File scripts/maintenance/backup-database.ps1
php artisan app:preflight-restore --allow-pending-migrations
```

## Before Running Migrations

```powershell
powershell -ExecutionPolicy Bypass -File scripts/maintenance/backup-database.ps1
php artisan app:preflight-restore --allow-pending-migrations
php artisan migrate
php artisan db:seed --class=BlogNavigationSeeder --force
php artisan app:preflight-restore --require-blog-navigation
php artisan optimize:clear
powershell -ExecutionPolicy Bypass -File scripts/maintenance/smoke-check.ps1
```

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

The app now has safe fallbacks for the critical dynamic views, but missing settings should still be treated as a deployment failure.

Run `php artisan db:seed --class=BlogNavigationSeeder --force` after blog releases to ensure public users can reach `/blog` without hardcoded localhost URLs. Then run `php artisan app:preflight-restore --require-blog-navigation` to verify the header menu contract.

## Redis and Horizon Policy

If any of these are set to `redis`, Redis must be reachable before deployment:

- `QUEUE_CONNECTION`
- `CACHE_DRIVER`
- `SESSION_DRIVER`

Use:

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

## GitHub Repository Protection

Configure this from GitHub: `Settings` -> `Rules` -> `Rulesets` -> `New branch ruleset`.

Use these values:

| Field | Value |
| --- | --- |
| Ruleset name | `MainProtector` |
| Enforcement status | `Active` |
| Bypass list | Empty unless an emergency-only admin bypass is approved |
| Target branches | Include default branch, or add pattern `main` |
| Restrict deletions | Enabled |
| Require a pull request before merging | Enabled |
| Required approvals | `1` minimum |
| Dismiss stale pull request approvals | Recommended |
| Require approval of the most recent reviewable push | Recommended |
| Require status checks to pass | Enabled |
| Require branches to be up to date before merging | Enabled |
| Block force pushes | Enabled |

Add these required status checks after they have run at least once on a pull request:

- `Mayush Quality Gates / App health`
- `Mayush Quality Gates / Composer audit`
- `Mayush Quality Gates / NPM production audit`
- `Mayush Restoration Guardrails / Static and Laravel guardrails`

The screenshot layout is correct, but the ruleset is not protective until these are fixed:

- `Enforcement status` must be `Active`, not `Disabled`.
- Target branches must include `main`.
- Required status checks must be added; leaving "No checks have been added" does not enforce CI.
- If GitHub shows that rulesets are not enforced for this private repository without GitHub Team, configure the same requirements under classic `Settings` -> `Branches` -> `Branch protection rules` or upgrade the organization plan.
