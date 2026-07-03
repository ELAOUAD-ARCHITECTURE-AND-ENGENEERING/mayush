# Backend Cache Plan - Phase 1

Date: 2026-06-05

## Objective

Reduce homepage backend work and TTFB risk without caching personalized pages or changing marketplace behavior.

The external baseline reported a backend duration / TTFB around 2.7s. This phase targets repeated session-independent storefront data queries first. Fresh GTmetrix/Lighthouse results must be collected after deployment; no synthetic score is claimed from these code changes alone.

## What Was Changed

### Storefront Data Cache Service

Added:

- `app/Services/StorefrontDataService.php`

This service caches public storefront data behind keys shaped by:

- storefront cache revision
- current locale
- selected currency code from session, when present
- data-specific key parts

The service uses normal Laravel cache stores, so it works with the current file cache and can benefit from Redis in production.

Cached data includes:

- active top banners
- custom alerts
- dynamic popups
- sale-alert products
- active flash deals
- featured flash deal
- flash deal products
- product collections referenced by banners
- slider upload records
- selected categories
- level-zero category menu data
- per-category homepage product rows
- promoted category products
- elite artisan shops
- recent best sellers

### Helper And View Wiring

Updated existing helpers and views to use cached storefront data instead of repeated direct queries:

- `app/Http/Helpers.php`
  - `get_cached_products`
  - `get_slider_images`
  - `get_featured_flash_deal`
  - `get_flash_deal_products`
  - `get_active_flash_deals`
  - `get_category`
  - `get_level_zero_categories`
  - `get_all_active_language`
  - `get_session_language`
  - `get_system_currency`
  - `get_all_active_currency`
  - `get_all_sale_alert_products`
  - `get_custom_labels`
- `resources/views/frontend/inc/nav.blade.php`
- `resources/views/frontend/layouts/app.blade.php`
- `resources/views/frontend/partials/promoted_category_section.blade.php`
- `resources/views/frontend/metro/partials/banner_section.blade.php`
- `resources/views/frontend/metro/partials/marketplace_banner.blade.php`

### Homepage Section Cache

Updated:

- `app/Services/HomeLayoutService.php`
- `app/Http/Controllers/HomeController.php`

Changes:

- Today's Deal products now use the existing `todays_deal_products` cache key.
- Preorder featured products use a short `home_preorder_featured_products` cache.
- Recent best sellers moved from controller-local query logic to cached service logic.
- Elite artisans now use cached storefront data.

### Cache Invalidation Coverage

Updated:

- `app/Providers/AppServiceProvider.php`
- `app/Services/StorefrontCacheService.php`

The existing storefront cache revision observer now also covers:

- flash deal product membership
- product collections
- top banners
- top banner translations
- dynamic popups
- custom alerts
- custom sale alerts
- languages
- currencies
- custom labels

`StorefrontCacheService::bump()` also forgets the new `home_preorder_featured_products` cache key.

Special case:

- `CustomSaleAlertController` uses `truncate()` and `insert()`, which do not fire model events. That controller already calls `cache:clear` after updates, so its bulk update path is still invalidated.

## Why This Is Safe

- No checkout, payment, cart mutation, login, admin, seller dashboard, order, or API logic was changed.
- No full-page HTML cache was added.
- Guest/session-sensitive output remains dynamic.
- Cached keys include locale and selected currency to avoid obvious cross-locale/currency leakage.
- Time-sensitive flash deal/best-seller caches use short TTLs.
- Product and settings changes bump the existing storefront revision through observers.

## Expected Performance Impact

Expected improvements after warm cache:

- Fewer repeated homepage layout queries for banners, popups, alerts, languages, currencies, categories, and flash deals.
- Less repeated work across deferred homepage section endpoints.
- Lower DB pressure for public anonymous homepage traffic.
- Lower TTFB variance when Redis is used for cache in production.

This does not yet solve:

- global render-blocking CSS
- heavy global JS
- hero image transfer cost
- newsletter/popup timing
- full-page HTML TTFB ceiling
- server/OPcache/CDN configuration

## Deployment Commands

Recommended after deployment:

```bash
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storefront:cache-warm
php artisan storefront:performance-readiness
```

If event caching is part of the production deployment process and event discovery/listeners are stable:

```bash
php artisan event:cache
```

For images:

```bash
php artisan images:audit --repair --priority-storefront --include-static --limit=500
php artisan images:status
```

## Production Configuration Notes

Confirm on production or staging:

- `APP_DEBUG=false`
- PHP OPcache enabled
- `CACHE_DRIVER=redis` if Redis is stable and monitored
- `SESSION_DRIVER=redis` can help at scale, but validate cart/language/currency behavior before changing it
- Horizon or queue workers process image jobs
- gzip/Brotli enabled for CSS/JS/HTML
- static assets and uploads cached at CDN/Cloudflare

Do not cache storefront HTML at the edge yet. Phase 0 found guest output can depend on session/cart/language/currency/popup state.

## Manual Verification Checklist

Homepage:

- guest homepage loads
- `/mayush` homepage loads
- hero slider still renders and links work
- featured categories appear
- Today's Deal deferred section loads
- featured products deferred section loads
- newest/best-selling collection sections load
- home categories deferred section loads when enabled
- flash deals render when active
- promoted category renders when enabled
- top banner renders when enabled
- custom alerts and dynamic popups still honor dismissal behavior

Header/footer:

- mobile menu opens/closes
- category menu works
- language switcher works
- currency switcher works
- search and visual-search controls still render
- cart dropdown count and total are correct for empty cart, guest cart, and logged-in cart
- wishlist/compare controls still work for authenticated customers

Admin invalidation:

- changing homepage settings updates public homepage after cache bump/clear
- changing banner/popup/custom alert updates public homepage
- changing product publish/status/price/stock updates relevant homepage sections after cache bump
- changing language/currency labels does not serve stale switchers

## Follow-Up Measurement

Run on staging after deploy:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storefront:cache-warm
```

Then run a warm homepage request and Lighthouse/GTmetrix retest.

If local Lighthouse fixtures exist:

```bash
set LIGHTHOUSE_BASE_URL=http://127.0.0.1:8000
set LIGHTHOUSE_CATEGORY_SLUG=<stable-category-slug>
set LIGHTHOUSE_PRODUCT_SLUG=<stable-product-slug>
npm run performance:lighthouse
```

Store reports under:

- `docs/performance/reports/` for shared analysis
- or `storage/app/lighthouse/` for raw local output
