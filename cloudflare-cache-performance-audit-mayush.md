# Cloudflare Cache Performance Audit - Mayush Marketplace

## A. Executive Summary

- Cloudflare cache working for guest HTML: **Yes**.
- Private pages safe from caching: **No**.
- Current performance situation: homepage cold TTFB was **0.147s** and final warm TTFB was **0.157s**. Best observed public HTML TTFB was **0.108s**.
- Main risks found: see section I for private cache, cookie, query string, Set-Cookie, and cache warming findings.
- Final recommendation: **RED**. Prioritize fixing any HIT responses on private/cookie/query probes before relying on guest HTML edge cache.

## B. Configuration Observed

- Domain tested: https://mayushdesign.com and https://www.mayushdesign.com
- Date/time of test: 2026-06-22 13:05:00 UTC
- Tooling used: curl.exe HEAD/GET with timing output; local Lighthouse when available
- User agent: Mozilla/5.0 MayushCacheAudit
- Cloudflare headers detected: Yes

| Requested URL | Status | Final URL | Server | CF-Ray | CF-Cache-Status | Cache-Control | Age | Set-Cookie | Redirect Chain |
|---|---:|---|---|---|---|---|---:|---|---|
| https://mayushdesign.com | 200 | https://mayushdesign.com/ | cloudflare | a0fb764d3a05d4da-MRS | DYNAMIC | no-cache, private |  | True | 200 |
| https://www.mayushdesign.com | 200 | https://www.mayushdesign.com/ | cloudflare | a0fb766068fafc1f-MRS | DYNAMIC | no-cache, private |  | True | 200 |

## C. Guest HTML Cache Results

| URL | Run 1 cache | Run 2 cache | Run 3 cache | Age header | Cold TTFB | Warm TTFB | Result |
|---|---|---|---|---:|---:|---:|---|
| https://mayushdesign.com | HIT | HIT | HIT | 384 | 0.147s | 0.157s | PASS |
| https://mayushdesign.com/category/office-furniture | HIT | HIT | HIT | 284 | 0.150s | 0.135s | PASS |
| https://mayushdesign.com/category/office-desks | HIT | HIT | HIT | 656 | 0.338s | 0.246s | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | HIT | HIT | HIT | 651 | 0.215s | 0.158s | PASS |
| https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3 | HIT | HIT | HIT | 645 | 0.160s | 0.136s | PASS |
| https://mayushdesign.com/blog/perfect-home-office-design | HIT | HIT | HIT | 642 | 0.133s | 0.108s | PASS |
| https://mayushdesign.com/contact-us | HIT | HIT | HIT | 275 | 0.169s | 0.150s | PASS |

## D. Private/Dynamic Route Safety Results

| URL | Status code | Cache status progression | Set-Cookie present | TTFB | Result |
|---|---:|---|---|---:|---|
| https://mayushdesign.com/cart | 200 | DYNAMIC -> DYNAMIC | True | 2.238s | PASS |
| https://mayushdesign.com/checkout | 302 | DYNAMIC -> DYNAMIC | True | 0.241s | PASS |
| https://mayushdesign.com/login | 200 | DYNAMIC -> DYNAMIC | True | 0.977s | PASS |
| https://mayushdesign.com/register | 200 | DYNAMIC -> DYNAMIC | True | 0.197s | PASS |
| https://mayushdesign.com/admin | 302 | DYNAMIC -> DYNAMIC | True | 0.261s | PASS |
| https://mayushdesign.com/seller | 404 | DYNAMIC -> DYNAMIC | True | 1.808s | PASS |
| https://mayushdesign.com/dashboard | 302 | DYNAMIC -> DYNAMIC | True | 0.193s | PASS |
| https://mayushdesign.com/customer | 404 | DYNAMIC -> DYNAMIC | True | 1.988s | PASS |
| https://mayushdesign.com/user | 404 | DYNAMIC -> DYNAMIC | True | 1.922s | PASS |
| https://mayushdesign.com/orders | 404 | DYNAMIC -> DYNAMIC | True | 2.335s | PASS |
| https://mayushdesign.com/wishlist | 404 | DYNAMIC -> DYNAMIC | True | 1.782s | PASS |
| https://mayushdesign.com/compare | 200 | DYNAMIC -> DYNAMIC | True | 2.618s | PASS |

## E. Cookie Safety Results

| URL | Cookie used | Cache status | Set-Cookie present | Result |
|---|---|---|---|---|
| https://mayushdesign.com | logged_in=1 | DYNAMIC | True | PASS |
| https://mayushdesign.com | laravel_session=test; XSRF-TOKEN=test | HIT | False | FAIL |
| https://mayushdesign.com | cart=test | DYNAMIC | True | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | logged_in=1 | DYNAMIC | True | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | laravel_session=test; XSRF-TOKEN=test | HIT | False | FAIL |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | cart=test | DYNAMIC | True | PASS |

## F. Query String Results

| URL | Cache status progression | Result |
|---|---|---|
| https://mayushdesign.com/?test=1 | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/category/office-furniture?test=1 | DYNAMIC -> DYNAMIC | PASS |

## G. Static Asset Results

| URL | Asset type | Cache status | Cache-Control | Age | Size | Result |
|---|---|---|---|---:|---:|---|
| https://mayushdesign.com/public/assets/css/vendors.css | CSS | DYNAMIC | public, max-age=2592000 |  |  | WARNING |
| https://mayushdesign.com/public/js/storefront-bootstrap.js?v=1780397529 | JS | DYNAMIC | public, max-age=2592000 |  |  | WARNING |
| https://mayushdesign.com/public/assets/img/flags/fr.png | Image | DYNAMIC | public, max-age=31536000, immutable |  |  | WARNING |
| https://mayushdesign.com/public/uploads/all/NQErD03t1rIispRs3lhXOlXiI9y7PRHkyDdUWa2g.webp | WebP | DYNAMIC | public, max-age=31536000, immutable |  |  | WARNING |

## H. Performance Situation

- Homepage cold TTFB: **0.147s**.
- Homepage warm TTFB: **0.157s**.
- Best observed TTFB: **0.108s**.
- Average public HTML warm TTFB after first run: **0.160s**.
- Cache status progressions: https://mayushdesign.com: HIT -> HIT -> HIT | https://mayushdesign.com/category/office-furniture: HIT -> HIT -> HIT | https://mayushdesign.com/category/office-desks: HIT -> HIT -> HIT | https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7: HIT -> HIT -> HIT | https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3: HIT -> HIT -> HIT | https://mayushdesign.com/blog/perfect-home-office-design: HIT -> HIT -> HIT | https://mayushdesign.com/contact-us: HIT -> HIT -> HIT
- Expected TTFB movement from roughly 2.97s toward sub-100ms/sub-50ms edge response: Not observed consistently in this run.

### Optional Lighthouse Results

| URL | Performance | FCP | LCP | TBT | CLS | Speed Index | Server response |
|---|---:|---:|---:|---:|---:|---:|---|
| https://mayushdesign.com | 31 | 4.4 s | 17.5 s | 2,420 ms | 0.002 | 7.5 s | Root document took 60 ms |
| https://mayushdesign.com/category/office-furniture | 26 | 6.4 s | 15.4 s | 400 ms | 0.853 | 6.4 s | Root document took 60 ms |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | 29 | 7.8 s | 10.7 s | 2,010 ms | 0.003 | 7.9 s | Root document took 60 ms |

## I. Problems Found

- Critical/risk: at least one cookie-bearing request received cached HTML.

## J. Recommended Fixes

- Keep the guest HTML cache rule constrained to browser HTML requests, public paths, empty query strings, and no cookies: `http.cookie eq ""` and `http.request.uri.query eq ""`.
- Ensure bypass rules cover `/cart`, `/checkout`, `/login`, `/register`, `/admin`, `/seller`, `/dashboard`, `/customer`, `/user`, `/orders`, `/wishlist`, `/compare`, `/api`, `/ajax`, `/payment`, and `/cmi`.
- If any private route returns HIT, move or reprioritize the bypass rules so they win before guest HTML caching, according to the active Cloudflare ruleset order.
- If cookie probes return HIT, add or fix the cookie-empty condition and purge affected HTML cache immediately.
- If guest HTML sends unnecessary `Set-Cookie`, remove session initialization from public storefront rendering paths or exclude those responses from HTML cache.
- If public pages do not warm to HIT, check Cloudflare cache eligibility, origin `Cache-Control`, rule expression, Cache Rules order, and whether workers/page rules are overriding cache behavior.
- Add purge automation for product, category, banner, content page, and menu changes.
- Keep HTML Edge Cache TTL short at first, such as 10 minutes, and keep Browser TTL respecting origin.

## K. Final Verdict

**RED**

Next Action Plan:

1. Fix any FAIL rows in private route, cookie, or query-string safety before expanding cache coverage.
2. Tune the guest HTML cache rule until selected public URLs warm to HIT with age greater than 0 and stable low TTFB.
3. Re-run this audit after any Cloudflare rule changes and after adding purge automation.

Raw structured results: `tools/audits/output/cloudflare-cache-performance-audit-mayush.raw.json`
