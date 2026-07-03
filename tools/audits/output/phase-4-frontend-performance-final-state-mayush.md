# Phase 4 Frontend Performance Final State - Mayush

Generated: 2026-06-22

## Verdict

Status: GREEN for implemented Phase 4 code changes and focused regression tests.

Cloudflare/cache status remains GREEN on the live site. The second production GET probe returned HIT for homepage, category, CSS, and JS targets, with no Set-Cookie on public responses.

Browser-performance status should be re-measured after deployment because the Playwright route smoke below was run against the local Laravel preview, not production Cloudflare. Local dev TTFB was very high and is not treated as a production timing signal.

## Implemented Fixes

- Deferred website popup banner downloads until `AIZ.extra.showSessionPopup()` actually reveals an eligible popup.
- Added intrinsic dimensions, lazy decoding, and explicit lazy/eager priorities to listing, homepage, and product gallery images.
- Added listing-page layout reservations for the toolbar, result shell, product grid, product cards, and skeleton loader.
- Prevented the category/listing AJAX first render from immediately smooth-scrolling to the top.
- Added product LCP preload `fetchpriority="high"` and product gallery image dimensions.
- Added a no-gallery early exit for the global lightbox listener setup.
- Delayed product Swiper initialization by one animation frame so it does not run in the same task as DOMContentLoaded.

## Local Browser Smoke

Base URL: `http://127.0.0.1:8010`

| Viewport | Route | Status | CLS | LCP | Long Tasks | Max Long Task | Notes |
| --- | --- | ---: | ---: | ---: | ---: | ---: | --- |
| desktop | `/` | 200 | 0.0005 | 12864 ms | 8 | 438 ms | Popup real image src count 0; deferred count 1 |
| desktop | `/category/office-furniture` | 200 | 0.0256 | 14844 ms | 7 | 652 ms | Popup real image src count 0; deferred count 1 |
| desktop | product fixture | 200 | 0.0106 | 33232 ms | 8 | 1143 ms | 4 product main images have dimensions |
| mobile | `/` | 200 | 0.0014 | 12364 ms | 7 | 393 ms | Popup real image src count 0; deferred count 1 |
| mobile | `/category/office-furniture` | 200 | 0.0151 | 14448 ms | 7 | 674 ms | Popup real image src count 0; deferred count 1 |
| mobile | product fixture | 200 | 0.0223 | 32720 ms | 11 | 964 ms | 4 product main images have dimensions |

Local timing caveat: LCP and TTFB are inflated by the local PHP/dev environment and slow local route work. The useful signals from this smoke are route status, low observed CLS, deferred popup image state, and product image dimension state.

## Production Cache Probe

| URL | Run 1 | Run 2 | Cache-Control | Set-Cookie |
| --- | --- | --- | --- | --- |
| `https://mayushdesign.com/` | EXPIRED, 3048 ms | HIT, 79 ms | `no-cache, private` | false |
| `https://mayushdesign.com/category/office-furniture` | EXPIRED, 3583 ms | HIT, 56 ms | `no-cache, private` | false |
| `https://mayushdesign.com/assets/css/custom-style.css` | MISS, 105 ms | HIT, 41 ms | `public, max-age=2592000` | false |
| `https://mayushdesign.com/assets/js/aiz-core.js` | MISS, 162 ms | HIT, 45 ms | `public, max-age=2592000` | false |

## Commands Run

- `node --check public\assets\js\aiz-core.js`
- `npm run build:storefront`
- `php artisan test --filter=HomepageRenderingTest`
- `php artisan test --filter=MarketplaceHeaderLayoutTest`
- `php artisan test --filter=CartModalCtaTest`
- `php artisan test --filter=PromotedCategoryTest`
- Local Playwright smoke against `php artisan serve --host=127.0.0.1 --port=8010`
- Production GET cache probe for homepage, category, CSS, and JS

## Artifacts

- Raw JSON: `tools/audits/output/phase-4-frontend-performance-final-state-mayush.raw.json`
- Markdown report: `tools/audits/output/phase-4-frontend-performance-final-state-mayush.md`

