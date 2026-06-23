# Cloudflare Cache Reverification - Mayush Marketplace

## A. Executive Summary

- Guest HTML cache status: **YELLOW**.
- Cookie safety status: **GREEN**.
- Private route safety status: **GREEN**.
- Static asset cache status: **YELLOW**.
- Overall verdict: **YELLOW**.

The cache-safety rules pass, but static asset caching or frontend performance still needs follow-up.

## B. What Was Retested

- Domain: https://mayushdesign.com and https://www.mayushdesign.com
- Date/time: 2026-06-22 13:26:55 UTC
- Tools: curl.exe with header/timing capture; local Lighthouse when available
- User agent: Mozilla/5.0 MayushCacheReAudit
- HTML request method: GET. Static assets: HEAD. Private route checks: GET without following redirects.
- Cloudflare headers detected: Yes

| URL | Status | Final URL | Redirect Chain | Server | CF-Ray | CF-Cache-Status | Cache-Control | Age | Set-Cookie | TTFB | Total |
|---|---:|---|---|---|---|---|---|---:|---|---:|---:|
| https://mayushdesign.com | 200 | https://mayushdesign.com/ | 200 | cloudflare | a0fb966409aeb870-MRS | MISS | no-cache, private |  | False | 2.601s | 2.640s |
| https://www.mayushdesign.com | 200 | https://www.mayushdesign.com/ | 200 | cloudflare | a0fb96775ffb93a1-MRS | MISS | no-cache, private |  | False | 2.779s | 2.810s |

## C. Public Guest HTML Results

| URL | Run 1 cache | Run 2 cache | Run 3 cache | Age | Cold TTFB | Warm TTFB | Result |
|---|---|---|---|---:|---:|---:|---|
| https://mayushdesign.com/ | MISS | HIT | HIT | 8 | 3.066s | 0.162s | PASS |
| https://mayushdesign.com/category/office-furniture | MISS | HIT | HIT | 1 | 3.122s | 0.141s | PASS |
| https://mayushdesign.com/category/office-desks | MISS | HIT | HIT | 1 | 2.977s | 0.142s | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | MISS | HIT | HIT | 1 | 6.869s | 0.153s | PASS |
| https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3 | MISS | HIT | HIT | 1 | 6.822s | 0.203s | PASS |
| https://mayushdesign.com/blog/perfect-home-office-design | MISS | HIT | HIT | 1 | 2.458s | 0.179s | PASS |
| https://mayushdesign.com/contact-us | MISS | HIT | MISS | 0 | 1.814s | 1.964s | WARNING |

## D. Critical Cookie Safety Results

| URL | Cookie used | Run 1 cache | Run 2 cache | Set-Cookie present | Result |
|---|---|---|---|---|---|
| https://mayushdesign.com/ | logged_in=1 | DYNAMIC | DYNAMIC | True | PASS |
| https://mayushdesign.com/ | laravel_session=test; XSRF-TOKEN=test | DYNAMIC | DYNAMIC | True | PASS |
| https://mayushdesign.com/ | cart=test | DYNAMIC | DYNAMIC | True | PASS |
| https://mayushdesign.com/ | remember_web=test | DYNAMIC | DYNAMIC | True | PASS |
| https://mayushdesign.com/ | wishlist=test | DYNAMIC | DYNAMIC | True | PASS |
| https://mayushdesign.com/ | mayush_test_cookie=1 | DYNAMIC | DYNAMIC | True | PASS |
| https://mayushdesign.com/category/office-furniture | logged_in=1 | DYNAMIC | DYNAMIC | True | PASS |
| https://mayushdesign.com/category/office-furniture | laravel_session=test; XSRF-TOKEN=test | DYNAMIC | DYNAMIC | True | PASS |
| https://mayushdesign.com/category/office-furniture | cart=test | DYNAMIC | DYNAMIC | True | PASS |
| https://mayushdesign.com/category/office-furniture | remember_web=test | DYNAMIC | DYNAMIC | True | PASS |
| https://mayushdesign.com/category/office-furniture | wishlist=test | DYNAMIC | DYNAMIC | True | PASS |
| https://mayushdesign.com/category/office-furniture | mayush_test_cookie=1 | DYNAMIC | DYNAMIC | True | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | logged_in=1 | DYNAMIC | DYNAMIC | True | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | laravel_session=test; XSRF-TOKEN=test | DYNAMIC | DYNAMIC | True | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | cart=test | DYNAMIC | DYNAMIC | True | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | remember_web=test | DYNAMIC | DYNAMIC | True | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | wishlist=test | DYNAMIC | DYNAMIC | True | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | mayush_test_cookie=1 | DYNAMIC | DYNAMIC | True | PASS |

## E. Private/Dynamic Route Results

| URL | Status | Cache status progression | Redirect location | Set-Cookie present | Result |
|---|---:|---|---|---|---|
| https://mayushdesign.com/cart | 200 | DYNAMIC -> DYNAMIC |  | True | PASS |
| https://mayushdesign.com/checkout | 302 | DYNAMIC -> DYNAMIC | https://mayushdesign.com | True | PASS |
| https://mayushdesign.com/login | 200 | DYNAMIC -> DYNAMIC |  | True | PASS |
| https://mayushdesign.com/register | 200 | DYNAMIC -> DYNAMIC |  | True | PASS |
| https://mayushdesign.com/admin | 302 | DYNAMIC -> DYNAMIC | https://mayushdesign.com/login | True | PASS |
| https://mayushdesign.com/seller | 404 | DYNAMIC -> DYNAMIC |  | True | PASS |
| https://mayushdesign.com/dashboard | 302 | DYNAMIC -> DYNAMIC | https://mayushdesign.com/users/login | True | PASS |
| https://mayushdesign.com/customer | 404 | DYNAMIC -> DYNAMIC |  | True | PASS |
| https://mayushdesign.com/user | 404 | DYNAMIC -> DYNAMIC |  | True | PASS |
| https://mayushdesign.com/orders | 404 | DYNAMIC -> DYNAMIC |  | True | PASS |
| https://mayushdesign.com/wishlist | 404 | DYNAMIC -> DYNAMIC |  | True | PASS |
| https://mayushdesign.com/compare | 200 | DYNAMIC -> DYNAMIC |  | True | PASS |
| https://mayushdesign.com/api | 404 | DYNAMIC -> DYNAMIC |  | True | PASS |
| https://mayushdesign.com/ajax | 404 | DYNAMIC -> DYNAMIC |  | True | PASS |
| https://mayushdesign.com/payment | 404 | DYNAMIC -> DYNAMIC |  | True | PASS |
| https://mayushdesign.com/cmi | 404 | DYNAMIC -> DYNAMIC |  | True | PASS |

## F. Query String Results

| URL | Cache status progression | Result |
|---|---|---|
| https://mayushdesign.com/?test=1 | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/category/office-furniture?test=1 | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7?test=1 | DYNAMIC -> DYNAMIC | PASS |

## G. Static Asset Results

| URL | Type | Cache status progression | Cache-Control | Age | Content-Type | Content-Length | TTFB | Result |
|---|---|---|---|---:|---|---:|---:|---|
| https://mayushdesign.com/public/assets/css/vendors.css | CSS | DYNAMIC -> DYNAMIC | public, max-age=2592000 |  | text/css |  | 0.258s | WARNING |
| https://mayushdesign.com/public/js/storefront-bootstrap.js?v=1780397529 | JS | DYNAMIC -> DYNAMIC | public, max-age=2592000 |  | text/javascript |  | 0.299s | WARNING |
| https://mayushdesign.com/public/assets/img/flags/fr.png | Image | DYNAMIC -> DYNAMIC | public, max-age=31536000, immutable |  | image/png | 545 | 0.213s | WARNING |
| https://mayushdesign.com/public/uploads/all/NQErD03t1rIispRs3lhXOlXiI9y7PRHkyDdUWa2g.webp | WebP | DYNAMIC -> DYNAMIC | public, max-age=31536000, immutable |  |  | 714 | 0.189s | WARNING |

No same-origin font file was discovered in the captured homepage HTML, so no font asset row is included.

## H. Performance Situation After Fix

- Homepage cold TTFB: **3.066s**.
- Homepage warm TTFB: **0.162s**.
- Best observed public HTML TTFB: **0.141s**.
- Average warm TTFB for public HTML: **0.292s**.
- Root document response situation: public HTML root documents are served from Cloudflare HIT in the sampled GET tests, but canonical no-cache/private responses can still appear when cookies are present.
- TTFB status: solved for cached HIT public HTML, but not fully consistent because `contact-us` returned MISS again on the third run and the product Lighthouse root document was slow.
- Lighthouse interpretation: if Lighthouse remains low while root document response is fast, the remaining bottleneck is mainly frontend work such as LCP, TBT, CLS, and render-blocking resources.

### Optional Lighthouse Results

| URL | Performance | FCP | LCP | TBT | CLS | Speed Index | Server response |
|---|---:|---:|---:|---:|---:|---:|---|
| https://mayushdesign.com/ | 37 | 3.1 s | 4.9 s | 8,290 ms | 0.002 | 16.7 s | Root document took 50 ms |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | 32 | 2.9 s | 6.9 s | 29,480 ms | 0.001 | 43.3 s | Root document took 6,730 ms |

Lighthouse note: the category Lighthouse run failed during Chrome temporary-file cleanup on Windows, so only homepage and product Lighthouse rows are included.

## I. Comparison With Previous Audit

| Test area | Previous result | New result | Status |
|---|---|---|---|
| Guest HTML cache | HIT already working | YELLOW; sampled public pages returned PASS, WARNING | Stable |
| Cookie safety | laravel_session/XSRF cookie returned HIT on homepage and product | GREEN; cookie FAIL count 0 | Fixed |
| Private routes | No route HIT in corrected private-route checks | GREEN; private FAIL count 0 | Stable |
| Query strings | DYNAMIC/non-HIT | Query FAIL count 0 | Stable |
| Static assets | DYNAMIC | YELLOW; static WARNING count 4 | Unchanged or partially improved |
| Homepage warm TTFB | Around 0.157s | 0.162s | Unchanged/stable |
| Lighthouse/root document response | Root document fast but Lighthouse low | Lighthouse count 2; root document remains fast for HIT HTML | Mostly frontend-limited |

## J. Remaining Problems

- Static assets did not consistently return `HIT`; they remain a cache-optimization warning.
- Lighthouse performance remains low despite fast cached root document responses, pointing to frontend rendering and asset work.

## K. Recommended Next Actions

- Check the Cloudflare static asset rule, Page Rules, Workers, Development Mode, origin `Set-Cookie`, and response headers.
- Create or adjust a dedicated static asset cache rule for `/public/assets/*`, `/public/js/*`, and `/public/uploads/*` with appropriate immutable/browser TTL behavior.
- Treat remaining Lighthouse work as frontend performance: optimize LCP images, reduce JavaScript/TBT, fix category CLS, add critical CSS, improve lazy loading, and reduce render-blocking assets.

## L. Final Verdict

**YELLOW**

Terminal summary:

```text
Guest HTML cache: YELLOW
Cookie safety: GREEN
Private/query safety: GREEN
Static asset cache: YELLOW
Overall verdict: YELLOW
Homepage TTFB cold/warm: 3.066s / 0.162s
Best public HTML TTFB: 0.141s
Average warm public HTML TTFB: 0.292s
Top 3 next actions:
1. Add or fix dedicated static asset cache rules.
2. Continue Lighthouse work on LCP, TBT, and CLS.
3. Keep monitoring cookie/private/query cache safety after cache rule edits.
```

Raw structured results: `tools/audits/output/cloudflare-cache-reverification-mayush.raw.json`
