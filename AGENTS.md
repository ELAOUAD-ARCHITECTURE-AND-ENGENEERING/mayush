# MayushDesign Agent Notes

## Project

- Laravel marketplace/e-commerce application with AIZ / Active eCommerce fingerprints.
- Current framework: Laravel 10.x, PHP 8.2+.
- Frontend is mostly Blade, Bootstrap/AIZ legacy assets, jQuery, Slick, and small esbuild storefront modules.
- Do not change production `.env` values or commit secrets.
- Treat checkout, payment callbacks, cart, wishlist, login, admin, seller/vendor dashboard, order, and API flows as high-risk areas.

## Common Commands

```bash
composer install
npm install
npm run build:storefront
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan test
php artisan storefront:cache-warm
php artisan storefront:performance-readiness
php artisan images:audit --repair --priority-storefront --include-static --limit=500
php artisan images:status
```

Use `npm run performance:lighthouse` only when `LIGHTHOUSE_BASE_URL`, `LIGHTHOUSE_CATEGORY_SLUG`, and `LIGHTHOUSE_PRODUCT_SLUG` point to stable local or staging fixtures.

## Performance Workflow

- Work in phases and keep changes reviewable.
- Measure before claiming results. If Lighthouse, GTmetrix, or query timing cannot be run, say so.
- Prefer caching public, session-independent data fragments before full-page HTML caching.
- Guest full-page cache is risky because homepage output can depend on language, currency, cart/temp user, cookies, popups, custom alerts, marketing consent, and Cloudflare behavior.
- Do not remove marketplace functionality to improve a synthetic score without approval.
- Preserve original uploads when optimizing images; use existing derivative/WebP pipeline and fallback helpers.
- Check responsive/mobile behavior after any CSS, hero, header, or popup change.

## Testing Notes

- PHPUnit uses SQLite in memory via `phpunit.xml`.
- Relevant focused tests for homepage work include:
  - `php artisan test --filter=HomepageRenderingTest`
  - `php artisan test --filter=ImageOptimizationPipelineTest`
  - `php artisan test --filter=PromotedCategoryTest`
  - `php artisan test --filter=MarketplaceHeaderLayoutTest`
  - `php artisan test --filter=CartModalCtaTest`
- Run broader tests when touching shared helpers, middleware, cart/wishlist behavior, product cards, image helpers, or cache invalidation.
