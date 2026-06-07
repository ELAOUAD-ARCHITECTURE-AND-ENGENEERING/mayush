# Homepage Performance Audit - Phase 0

Date: 2026-06-05

This audit is based on repository inspection only. It does not claim new GTmetrix or Lighthouse measurements. The pasted production baseline remains the external baseline until staging or production is retested.

## External Baseline Provided

- GTmetrix grade: D
- GTmetrix performance: 48%
- GTmetrix structure: 91%
- GTmetrix LCP/FCP: 3.6s / 3.6s
- GTmetrix TTFB/backend duration: 2.7s
- GTmetrix TTI/TBT: 8.6s / 326ms
- GTmetrix fully loaded: 11.7s
- Lighthouse LCP/FCP: 10.4s / 2.7s
- Lighthouse speed index: 14.4s
- Lighthouse unused JavaScript: about 448 KiB
- Lighthouse notes heavy `public/assets/js/vendors.js`, render-blocking CSS, unused CSS, oversized homepage images.

## Stack And Deployment Shape

- Laravel: 10.50.2 (`php artisan --version`)
- PHP: 8.2.12 locally (`php -v`)
- Composer package: `laravel/laravel`
- AIZ / Active eCommerce indicators:
  - `aiz-packages/color-code-converter`
  - `aiz-packages/combination-generate`
  - `mehedi-iitdu/core-component-repository`
  - global `AIZ` uploader/core assets and Blade conventions
- Package manager: npm with `package-lock.json`
- Frontend build:
  - No Vite or Mix config found.
  - Storefront esbuild script: `scripts/storefront/build.mjs`
  - Build command: `npm run build:storefront`
  - Build output: `public/build/storefront/manifest.json`
- Legacy assets are still loaded globally from `public/assets`.
- Cache defaults from `.env.example`:
  - `CACHE_DRIVER=file`
  - `SESSION_DRIVER=file`
  - `QUEUE_CONNECTION=redis`
  - storefront performance flags are present.
- Production optimization commands are documented in this audit and root `AGENTS.md`; production `.env` was not changed.

## Existing Performance Work Already Present

The repository already contains useful performance foundations:

- Fragment/data cache service: `app/Services/StorefrontCacheService.php`
- Homepage data service: `app/Services/HomeLayoutService.php`
- Cache invalidation observers:
  - `app/Observers/StorefrontCacheObserver.php`
  - `app/Observers/ProductObserver.php`
  - `app/Observers/UploadObserver.php`
- Image derivative/WebP system:
  - `app/Services/ImageOptimizationService.php`
  - `config/image-optimization.php`
  - `app/Console/Commands/AuditImages.php`
  - `app/Console/Commands/ImagesStatus.php`
  - `app/Console/Commands/OptimizeImages.php`
- Storefront readiness command:
  - `app/Console/Commands/StorefrontPerformanceReadiness.php`
- Storefront cache warm command:
  - `app/Console/Commands/WarmStorefrontCache.php`
- Storefront indexes migration:
  - `database/migrations/2026_06_02_000001_add_storefront_performance_indexes.php`
- Lighthouse matrix script:
  - `scripts/performance/lighthouse-matrix.mjs`
- Deployment/performance runbook:
  - `docs/storefront-lighthouse-99-runbook.md`

Important caveat: the runbook recommends not edge-caching storefront HTML because session/cart state remains request-specific. That conflicts with a broad full-page cache plan and should be reconciled before Phase 2.

## Homepage Route And Controller

Primary route:

- `GET /` -> `HomeController@index`, route name `home`
- `GET /mayush` -> `HomeController@index`, route name `home.local_subdirectory`

AJAX/deferred section routes:

- `GET|POST /home/section/featured`
- `GET|POST /home/section/best_selling`
- `GET|POST /home/section/home_categories`
- `GET|POST /home/section/best_sellers`
- `GET|POST /home/section/auction_products`
- `GET /home/section/todays_deal`
- `GET /home/section/newest_products`
- `GET /home/section/promoted_category`
- `GET /home/section/preorder_products`
- `GET|POST /home/section/load-elite-artisans-section`

Files:

- `routes/web.php`
- `app/Http/Controllers/HomeController.php`
- `app/Services/HomeLayoutService.php`

`HomeController@index` performs:

- referral processing through `App\Services\AuthService::processRegistrationReferral`
- homepage data assembly through `HomeLayoutService::getHomepageData`
- optional portfolio landing branch via `get_setting('portfolio_landing')`
- renders `resources/views/frontend/{safe_homepage_select()}/index.blade.php`

Active expected homepage theme from README/code is Metro:

- `resources/views/frontend/metro/index.blade.php`

## Layout, Header, Footer, And Homepage Views

Main layout:

- `resources/views/frontend/layouts/app.blade.php`

Header wrapper:

- `resources/views/frontend/inc/nav.blade.php`

Header variants:

- `resources/views/header/header1.blade.php`
- `resources/views/header/header2.blade.php`
- `resources/views/header/header3.blade.php`
- `resources/views/header/header4.blade.php`
- `resources/views/header/header5.blade.php`
- `resources/views/header/header6.blade.php`
- `resources/views/header/header7.blade.php`

Footer:

- `resources/views/frontend/inc/footer.blade.php`

Homepage and important Metro partials:

- `resources/views/frontend/metro/index.blade.php`
- `resources/views/frontend/metro/partials/featured_categories_section.blade.php`
- `resources/views/frontend/partials/promoted_category_section.blade.php`
- `resources/views/frontend/metro/partials/flash_deals_section.blade.php`
- `resources/views/frontend/metro/partials/category_icon_navigation.blade.php`
- `resources/views/frontend/metro/partials/marketplace_banner.blade.php`
- `resources/views/frontend/metro/partials/banner_section.blade.php`
- `resources/views/frontend/metro/partials/home_categories_section.blade.php`
- `resources/views/frontend/metro/partials/inspiration_articles_section.blade.php`
- `resources/views/frontend/metro/partials/classifieds_section.blade.php`
- `resources/views/frontend/metro/partials/product_box_1.blade.php`
- `resources/views/frontend/metro/partials/product_box_2.blade.php`

## Assets Loaded On Homepage

Render-blocking/global CSS in `resources/views/frontend/layouts/app.blade.php`:

- external Google Fonts CSS preload/noscript
- external Line Awesome CSS preload/noscript
- `public/assets/css/vendors.css` - 479,369 bytes
- `public/assets/css/bootstrap-rtl.min.css` for RTL only - 153,356 bytes
- `public/assets/css/aiz-core.css` - 334,756 bytes
- `public/assets/css/semantic_search.css` - 3,730 bytes
- `public/assets/css/custom-style.css` - 71,056 bytes
- `public/assets/css/mayush-design-tokens.css` - 4,647 bytes
- `public/assets/css/mayush-components.css` - 28,197 bytes
- conditional `public/assets/css/thecore.css`
- conditional external AOS CSS for shop/contact pages, not homepage by route rule
- large inline CSS block in the layout
- large inline Metro CSS block in `resources/views/frontend/metro/index.blade.php`
- inline section CSS in several homepage partials

Global JS in `resources/views/frontend/layouts/app.blade.php`:

- `public/js/storefront-bootstrap.js` defer - 4,913 bytes
- page-profile modules from `public/build/storefront`, including `core.js` and `home.js`
- `public/assets/js/vendors.js` - 1,619,281 bytes locally
- `public/assets/js/aiz-core.js` - 146,446 bytes
- inline global AIZ/localization scripts
- inline cart, wishlist, compare, add-to-cart, popup, notification, accordion, sale-alert, and language/currency scripts
- conditional AOS JS for shop/contact pages

The production Lighthouse report listed `vendors.js` at about 478 KB, likely compressed transfer size or a different deployed asset. The local file is 1.62 MB uncompressed and is still the most important JS target.

## Above-The-Fold Images

Likely above the fold on Metro homepage:

- Header/logo:
  - `resources/views/header/header1.blade.php`
  - `uploaded_asset(get_setting('header_logo'))`
- Top banner if enabled:
  - `resources/views/frontend/inc/nav.blade.php`
  - `TopBanner::where('status', 1)` plus configured uploaded top banner images
- Hero/slider:
  - `resources/views/frontend/metro/index.blade.php`
  - settings: `home_slider_images`, `home_slider_links`, `home_slider_titles`, `home_slider_descriptions`, `home_slider_cta_texts`, `home_slider_cta_links`
  - image helper: `get_slider_images()`
  - first slide currently has `loading="eager"` and `fetchpriority="high"`
  - `uploaded_asset_srcset($slider, ['medium', 'large'])` is used when derivatives exist
  - preload uses the original `uploaded_asset($metroSliderImages[0])`, not necessarily the `large` derivative
- Featured categories immediately after hero:
  - `resources/views/frontend/metro/partials/featured_categories_section.blade.php`
  - category cover images use `uploaded_asset($category->cover_image, 'thumb')`
- Promoted category is directly included near the top:
  - `resources/views/frontend/partials/promoted_category_section.blade.php`
  - static image `assets/img/office_furniture_4k.png` via `optimized_static_asset(..., 'medium')`
  - first four product cards load lazy product images
- Flash deals can appear early:
  - `resources/views/frontend/metro/partials/flash_deals_section.blade.php`
  - banner is a CSS background image using `uploaded_asset($flash_deal->banner)`, which prevents normal `width`, `height`, `srcset`, lazy loading, and fetch priority control.

## Database Query Sources On Homepage

Not measured as a full production trace in this phase. The local database is reachable, but a safe inline query trace was not completed due Windows shell quoting issues. The following are code-path query sources found by inspection.

Controller/service path:

- `HomeController@index`
  - `AuthService::processRegistrationReferral`
  - `HomeLayoutService::getHomepageData`
  - `get_setting('portfolio_landing')`
  - optional `getPortfolioGoingOns`
- `HomeLayoutService::getHomepageData`
  - `get_system_language()`
  - cached `featured_categories` query with `Category::with('bannerImage')->where('featured', 1)->get()`
  - cached `hot_categories` query with `Category::with('bannerImage')->where('hot_category', '1')->get()`
  - cached latest blogs query with category/translations eager loading
  - inspiration blogs query, cached only when admin-selected IDs are not configured

Layout/header/footer path:

- `resources/views/frontend/layouts/app.blade.php`
  - many `get_setting()` calls backed by cached `business_settings`
  - `get_session_language()` in layout reads `languages`
  - homepage SEO stats service methods for FAQ/item-list schema can run DB queries
  - `CustomAlert::where('status', 1)->orderBy(...)->get()` on every page
  - `DynamicPopup::where('status', 1)->orderBy(...)->get()` on every page
  - popup id 100 checks user orders only for authenticated users
  - optional `get_all_sale_alert_products()` loads `CustomSaleAlert::with('product')->get()`
- `resources/views/frontend/inc/nav.blade.php`
  - `TopBanner::where('status', 1)->orderBy('id','desc')->get()`
  - safe header selection via `get_element_type_by_id(get_setting('header_element'))`
- `resources/views/header/header1.blade.php`
  - active languages/currencies for switchers
  - category menu includes `get_level_zero_categories()->take(12)` and nested children
  - cart dropdown uses `get_user_cart()` for guests when `temp_user_id` exists; for non-empty carts it calls `get_single_product()` per cart item
  - authenticated user notifications/wishlist/compare branches are not guest homepage work but affect logged-in performance
- `resources/views/frontend/inc/footer.blade.php`
  - many settings and optional last-viewed product/carousel logic for authenticated customers
  - mobile/cart footer references `get_user_cart()`

Homepage partial path:

- `resources/views/frontend/metro/index.blade.php`
  - repeated `get_setting()` calls for hero, sections, banners, collection images, flash deal navigation
  - `get_slider_images($decoded_slider_images)` queries uploads
  - `get_active_flash_deals()` queries active flash deals
- `resources/views/frontend/partials/promoted_category_section.blade.php`
  - `Category::find($promoted_category_id)`
  - category children access through `$promoted_category->categories`
  - product query for discounted products and possible fallback query
  - no explicit eager loading for product card dependencies
- `resources/views/frontend/metro/partials/flash_deals_section.blade.php`
  - `get_featured_flash_deal()`
  - `get_flash_deal_products($flash_deal->id)` with `with('product')`
  - product cards may lazy-load product relations such as taxes, thumbnail, translations, labels, bids
- `resources/views/frontend/metro/partials/home_categories_section.blade.php`
  - `get_category($home_categories)`
  - per-category `get_cached_products($category->id)`
  - category banner/icon upload lookups
- Product cards:
  - `get_image()` / `get_first_product_image()` can query `uploads` per image id unless the relation/object is already present
  - `home_base_price()` / `home_discounted_base_price()` iterate `$product->taxes`, so missing eager loading can create N+1 queries
  - `get_custom_labels($product->custom_label_id)` queries one label at a time
  - auction branches can load carts, bids, and GST data

## Middleware Affecting Homepage

Web middleware group from `app/Http/Kernel.php`:

- `EncryptCookies`
- `AddQueuedCookiesToResponse`
- `StartSession`
- `AuthenticateSession`
- `ShareErrorsFromSession`
- `VerifyCsrfToken`
- `SubstituteBindings`
- `Language`
- `CheckForMaintenanceMode`
- `MarkdownForAgents`
- `AgentDiscoveryHeaders`
- `StorefrontServerTiming`

Performance implications:

- Public homepage starts a session for guests.
- Language middleware writes locale to session.
- File session driver in `.env.example` can add file I/O and complicates full-page caching.
- `StorefrontServerTiming` can help staging profiling when `STOREFRONT_SERVER_TIMING=true`.

## Cache Configuration And Behavior

Configured:

- `config/cache.php` defaults to `env('CACHE_DRIVER', 'file')`
- Redis store is available through `predis/predis`
- `config/session.php` defaults to file sessions
- `.env.example` includes Redis queue and storefront performance flags

Existing cached keys and invalidation:

- `business_settings` cached 24h in `get_setting()`
- `translations-{locale}` cached forever in translation helper
- `addons` cached 24h
- `featured_categories`, `hot_categories` cached forever
- `home_latest_blogs`, `home_inspiration_blogs` cached 15 minutes
- `featured_products`, `newest_products`, `products-category-{id}`, `best_selers` cached
- `StorefrontCacheService::bump()` forgets several homepage keys and increments a revision
- Observers bump cache for uploads, products, business settings, categories, blogs, and flash deals

Gaps:

- Cache invalidation does not obviously include every direct helper key used by homepage, for example `products-category-{id}` is not listed in `StorefrontCacheService::HOME_CACHE_KEYS`.
- Some cache keys are not language/currency aware.
- Some homepage partials directly query in Blade and are not wrapped in a dedicated service/cache layer.
- Cache store defaults to file; production should prefer Redis for high-traffic storefront fragments.

## Session, Cart, Wishlist, And Guest Behavior

Guest homepage:

- Web middleware starts session.
- `get_user_cart()` queries carts only when `temp_user_id` exists.
- Header cart dropdown still computes totals from cart products if a guest has a temp cart.
- Wishlist/notifications are guarded by auth checks in header branches.
- Compare count uses session data.
- Language and currency switchers depend on session state.

Implication:

- Anonymous homepage HTML is not purely static for every visitor. Safe page caching must vary by locale/currency and bypass visitors with cart/session-specific cookies, temp cart, dismissed popups, marketing consent, or compare/session state.

## Suspected Bottlenecks

| Area | Files | Risk | Evidence |
| --- | --- | --- | --- |
| TTFB from uncached view/layout queries | `layouts/app.blade.php`, `inc/nav.blade.php`, header/footer, Metro partials | Medium | Direct model queries for alerts, popups, top banners, flash deals, promoted products, sale alerts, category menu. |
| Product-card N+1 queries | `product_box_1.blade.php`, `product_box_2.blade.php`, helpers in `app/Http/Helpers.php` | High | Product cards call image, price/tax, custom label, translation, and optional auction helpers. |
| Legacy JS bundle | `public/assets/js/vendors.js`, `layouts/app.blade.php` | Medium | 1.62 MB local uncompressed and globally loaded. Removing/splitting blindly can break AIZ widgets. |
| Legacy CSS bundle | `vendors.css`, `aiz-core.css`, `custom-style.css`, layout head | Medium | About 885 KB uncompressed legacy CSS loaded before paint. |
| Hero image preload mismatch | `metro/index.blade.php` | Low | First hero image preload uses `uploaded_asset()` default/original, while rendered image requests `uploaded_asset(..., 'large')`. |
| CSS background images | `flash_deals_section.blade.php`, collection panels | Low/Medium | Background images cannot use image attributes, lazy loading, srcset, or decoding. |
| Popup/custom alert work on every public page | `layouts/app.blade.php` | Medium | `DynamicPopup` and `CustomAlert` are queried and rendered globally. Early popup can hurt perceived performance. |
| File cache/session in production | `.env.example`, `config/cache.php`, `config/session.php` | Medium | File I/O and session locking are poor fits for high-traffic storefront. Actual production `.env` not inspected. |
| Full-page cache safety | middleware, header/cart/currency/language/popup behavior | High | Personalized/session-sensitive output exists even for guests with temp sessions. |

## Risk Levels For Optimization Candidates

Low risk:

- Documentation and deployment command updates.
- Align hero preload URL with the actual derivative used by the first hero image.
- Add missing `width`, `height`, `decoding`, and derivative helper usage to images that already have safe fallbacks.
- Run image readiness commands and queue derivative repair.
- Cache top banners, custom alerts, dynamic popups, active flash deals, and sale alert product data with observer invalidation.
- Add eager loading/select columns to services when output is unchanged.

Medium risk:

- Move more Blade direct queries into `HomeLayoutService`.
- Cache rendered homepage partial fragments by revision/language/currency.
- Defer newsletter/popup rendering and scripts.
- Defer non-critical CSS or split homepage CSS from global CSS.
- Load some JS only on pages that need it.
- Replace CSS background images with responsive `<picture>`/`img` markup.

High risk:

- Guest full-page HTML cache.
- Removing libraries from `vendors.js`.
- Changing cart/wishlist/search/mobile-menu JS boot order.
- Schema/index changes beyond the existing storefront index migration.
- Changing session driver behavior without staging validation.

## Exact Implementation Order Proposed

### Phase 1A - Measurement And TTFB Instrumentation

1. Enable `STOREFRONT_SERVER_TIMING=true` on staging only.
2. Add a safe local/staging query profiler command or test helper for `GET /` and homepage section endpoints.
3. Capture warm/cold query count, DB time, app time, HTML size, and top query sources.
4. Run `php artisan storefront:cache-warm`.

### Phase 1B - Backend Data Caching And Eager Loading

1. Move direct homepage Blade queries for top banner, popups, alerts, active flash deals, promoted category, and sale alerts into services.
2. Cache session-independent collections by revision + locale + currency where output changes by those values.
3. Eager-load product card dependencies:
   - translations
   - taxes
   - thumbnail/upload or image references where practical
   - category/shop only where rendered
   - flash deal product relation
4. Replace `get_custom_labels()` per-card queries with batched label loading or product relation.
5. Add missing cache invalidation keys for category product caches and popup/alert/top-banner settings.

### Phase 1C - Deployment Runtime Optimization

1. Confirm production `APP_DEBUG=false`.
2. Use Redis for cache/session if production traffic justifies it.
3. Run:
   - `composer install --no-dev --optimize-autoloader`
   - `php artisan optimize:clear`
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan view:cache`
   - `php artisan event:cache` if event discovery/listeners are stable.

### Phase 2 - Guest Page Cache Decision

Do not start with broad HTML caching. First decide between:

- server/Cloudflare static asset caching only, plus fragment caches, or
- narrow code-level guest homepage cache that varies by locale/currency and bypasses any request with cart/session-sensitive cookies.

This phase requires approval because it can break cart, language, currency, popup dismissal, marketing consent, and CSRF-sensitive flows.

### Phase 3 - Image System Completion

1. Run image audit/status commands and ensure hero derivatives exist.
2. Align hero preload to the exact `large` or mobile derivative rendered.
3. Add mobile hero variant strategy if missing.
4. Convert CSS background banners to responsive images where safe.
5. Ensure product/category/banner templates consistently use derivative helpers and dimensions.

### Phase 4 - CSS Render Blocking

1. Extract critical CSS for header/hero/first categories.
2. Defer or split non-critical legacy CSS.
3. Move large inline page CSS into a cacheable file where safe.
4. Verify mobile header, RTL, carousel, and marketplace cards.

### Phase 5 - JavaScript

1. Inventory `vendors.js` dependencies actually used on homepage.
2. Defer non-critical inline scripts.
3. Delay popup/newsletter/sale-alert/marketing scripts.
4. Split AIZ vendor features by route only after usage is proven.
5. Keep cart, wishlist, compare, search, mobile menu, and sliders working.

### Phase 6 - Popup UX

1. Cache popup configuration.
2. Delay popup display by interaction, scroll, or 5-8 seconds.
3. Keep popup hidden from LCP and first paint.
4. Ensure dismissal/session behavior still works.

### Phase 7 - Fonts And Third Parties

1. Review Google Fonts families; current request loads Inter, Playfair Display, Public Sans, and Outfit.
2. Self-host or reduce families/weights if design allows.
3. Keep `font-display=swap`.
4. Delay marketing/analytics when consent/defer flag allows.

### Phase 8 - Accessibility And Best Practices

1. Add/fix accessible labels for logo/menu/search controls where missing.
2. Fix low contrast and heading hierarchy regressions.
3. Decide source-map publication strategy for build artifacts.
4. Document production security headers.

### Phase 9 - Validation

1. Run focused PHPUnit tests.
2. Run `npm run build:storefront`.
3. Run storefront readiness/image status.
4. Run Lighthouse locally/staging when fixture slugs are available.
5. Ask for GTmetrix retest after deployment.

## Phase 0 Checks Performed

- Read README, `.env.example`, `composer.json`, `package.json`, build scripts, cache/session config, routes, controller, services, layout, header, footer, and Metro homepage partials.
- Confirmed Laravel/PHP versions with local commands.
- Confirmed homepage routes with `php artisan route:list --name=home`.
- Confirmed local migrations status; one unrelated pending blog workflow migration exists:
  - `2026_06_03_120000_enhance_blog_author_workflow`
- Inspected asset sizes for main CSS/JS files.
- Created root `AGENTS.md` because no project-root agent guide existed.

## Open Measurement Gaps

- No fresh Lighthouse or GTmetrix run was performed in Phase 0.
- No production database query trace was performed.
- No local homepage browser smoke was performed in Phase 0.
- Production `.env`, Redis, OPcache, web server compression, HTTP/2/3, CDN, and Cloudflare cache settings were not changed or verified.
