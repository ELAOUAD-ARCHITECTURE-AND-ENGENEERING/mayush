# April 30 Completion Implementation Notes

## Scope

This implementation completes the missing April 30 work that was absent from the current `main` branch:

- Non-payment integration test stabilization.
- Review, audit log, category, brand, and product schema repair.
- Blog platform expansion with tags, translations, authorship, seeded content, and homepage exposure.
- Production smoke support for CSRF behavior outside Laravel's testing kernel.

Payment gateway test failures remain intentionally isolated in the `PaymentGateways` PHPUnit suite.

## Architecture

### Test Infrastructure

- `phpunit.xml` now separates `Integration` from `PaymentGateways`.
- `.env.testing` pins tests to SQLite in-memory with array cache/session drivers.
- `tests/bootstrap.php` performs minimal Composer autoload bootstrapping.
- `Tests\Traits\SeedsAppConfigs` seeds only the settings required by tests that render application views.
- `RouteContractTest` protects critical named routes used by middleware and controllers.

### Schema Hygiene

All production-facing schema repairs are forward migrations:

- `2026_04_30_000001_fix_reviews_table_columns.php`
- `2026_04_30_000002_make_audit_logs_target_user_id_nullable.php`
- `2026_04_30_130000_upgrade_blog_system.php`
- `2026_04_30_135000_fix_missing_category_columns.php`

The migrations are guarded with `Schema::hasTable` and `Schema::hasColumn` checks to support existing databases and fresh test databases.

### Blog Platform

`Blog` remains backward-compatible with existing base columns while adding:

- `author()`
- `tags()`
- `translations()` / `blog_translations()`
- `getTranslation()`
- `published()` scope

Translated content lives in `blog_translations`. Base blog columns remain fallback content.

## Deployment

1. Backup the production database.
2. Deploy code.
3. Run `php artisan migrate --force`.
4. Run idempotent content/navigation seeders if this release includes the blog rollout:
   - `php artisan db:seed --class=BlogSeeder --force`
   - `php artisan db:seed --class=SeoInteriorDesignArticleSeeder --force`
   - `php artisan db:seed --class=BlogNavigationSeeder --force`
5. Run `php artisan optimize:clear`.
6. Clear targeted homepage category/blog caches if needed:
   - `php artisan cache:forget hot_categories`
   - `php artisan cache:forget home_latest_blogs`
7. Run smoke checks:
   - `/`
   - `/blog`
   - one seeded or existing `/blog/{slug}`
   - main header navigation includes `Blog` and points to `/blog`
   - review submission workflow
   - admin review moderation route
8. Optionally run `php scripts/security/csrf-smoke-test.php https://your-domain.test/login` against a non-testing environment.

## Rollback

Code rollback is safe because migrations are additive. Do not drop newly added columns during an emergency rollback unless a separate maintenance window and backup restore plan are approved.

If rollback is required:

1. Revert to the previous release artifact.
2. Run `php artisan optimize:clear`.
3. Keep additive schema in place.
4. Restore the database only if data corruption is observed.

## Verification

Verified locally:

- `php artisan test --testsuite=Integration`
- `php artisan test --testsuite=Unit`
- `php artisan test --testsuite=Security`
- `php artisan test --testsuite=Feature`
- `php artisan test tests\Feature\PromotedCategoryTest.php`
- `php artisan test tests\Feature\SemanticSearchTest.php`
- `php artisan test tests\Feature\EliteSystemTest.php`
- `php artisan test tests\Integration\Controllers\Frontend\BlogPlatformTest.php`

Known remaining outside-scope checks:

- Payment gateway integration failures remain isolated in the `PaymentGateways` PHPUnit suite and are intentionally outside this non-payment rollout.
