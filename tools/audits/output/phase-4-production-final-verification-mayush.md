# Phase 4 Production Final Verification - Mayush

## A. Executive Summary

- Overall verdict: **RED**
- Cache safety status: **GREEN**
- Static asset cache status: **GREEN**
- Production browser performance status: **YELLOW**
- CLS status: **YELLOW**
- LCP status: **RED**
- JavaScript/long-task status: **GREEN**
- Product page consistency status: **GREEN**

Final assessment: production static asset GET cache remains healthy, public guest HTML warms to `HIT`, and cookie/query/private cache safety is preserved after private routes were rechecked without following redirects. The Phase 4 popup/LCP regression target is not fixed: the deferred popup image still becomes mobile LCP around 10.9s on category, blog, and contact routes. Category CLS improved dramatically on mobile and partially on desktop, but desktop category CLS remains above the 0.10 target. Product mobile JavaScript long tasks are materially improved versus baseline.

## B. Deployment Verification Scope

- Date/time: 2026-06-23T13:11:32.924Z
- Production domain tested: https://mayushdesign.com
- Tools used: curl.exe GET, Playwright Chromium, Performance API, Resource Timing, LCP observer, Layout Shift observer, Long Task observer
- User agents: desktop browser-like UA and mobile Safari-like UA
- HTML cache runs: 5 GETs per public URL
- Static asset cache runs: 5 GETs per asset
- Browser runs: 7 navigations per URL/profile
- Desktop profile: 1366x768, no artificial throttling
- Mobile profile: 390x844, deviceScaleFactor 3, mobile UA
- Production-only: yes
- Private/dynamic route probes: GET without following redirects, so redirects are judged on the route response itself rather than the final redirected public page.

## C. Cache Regression Results

| URL/test type | condition | cache status progression | age | TTFB | set-cookie present | result |
| --- | --- | --- | --- | --- | --- | --- |
| https://mayushdesign.com/ | public HTML | EXPIRED -> EXPIRED -> HIT -> HIT -> HIT | 1 | 0.158s median | no | PASS |
| https://mayushdesign.com/category/office-furniture | public HTML | EXPIRED -> HIT -> EXPIRED -> HIT -> HIT | 0 | 0.176s median | no | PASS |
| https://mayushdesign.com/category/office-desks | public HTML | EXPIRED -> HIT -> HIT -> HIT -> HIT | 1 | 0.156s median | no | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | public HTML | EXPIRED -> HIT -> HIT -> EXPIRED -> HIT | 9 | 0.158s median | no | PASS |
| https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3 | public HTML | EXPIRED -> HIT -> HIT -> HIT -> EXPIRED |  | 0.150s median | no | PASS |
| https://mayushdesign.com/blog/perfect-home-office-design | public HTML | EXPIRED -> HIT -> HIT -> HIT -> HIT | 1 | 0.149s median | no | PASS |
| https://mayushdesign.com/contact-us | public HTML | EXPIRED -> HIT -> EXPIRED -> HIT -> HIT | 4 | 0.144s median | no | PASS |
| https://mayushdesign.com/ | cookie laravel_session=test; XSRF-TOKEN=test | DYNAMIC -> DYNAMIC |  | 2.897s median | yes | PASS |
| https://mayushdesign.com/ | cookie cart=test | DYNAMIC -> DYNAMIC |  | 2.763s median | yes | PASS |
| https://mayushdesign.com/ | cookie remember_web=test | DYNAMIC -> DYNAMIC |  | 2.542s median | yes | PASS |
| https://mayushdesign.com/ | cookie wishlist=test | DYNAMIC -> DYNAMIC |  | 2.736s median | yes | PASS |
| https://mayushdesign.com/?test=1 | query ?test=1 | DYNAMIC -> DYNAMIC |  | 2.547s median | yes | PASS |
| https://mayushdesign.com/category/office-furniture | cookie laravel_session=test; XSRF-TOKEN=test | DYNAMIC -> DYNAMIC |  | 2.978s median | yes | PASS |
| https://mayushdesign.com/category/office-furniture | cookie cart=test | DYNAMIC -> DYNAMIC |  | 3.026s median | yes | PASS |
| https://mayushdesign.com/category/office-furniture | cookie remember_web=test | DYNAMIC -> DYNAMIC |  | 2.983s median | yes | PASS |
| https://mayushdesign.com/category/office-furniture | cookie wishlist=test | DYNAMIC -> DYNAMIC |  | 2.902s median | yes | PASS |
| https://mayushdesign.com/category/office-furniture?test=1 | query ?test=1 | DYNAMIC -> DYNAMIC |  | 3.181s median | yes | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | cookie laravel_session=test; XSRF-TOKEN=test | DYNAMIC -> DYNAMIC |  | 7.088s median | yes | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | cookie cart=test | DYNAMIC -> DYNAMIC |  | 6.842s median | yes | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | cookie remember_web=test | DYNAMIC -> DYNAMIC |  | 6.499s median | yes | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | cookie wishlist=test | DYNAMIC -> DYNAMIC |  | 6.586s median | yes | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7?test=1 | query ?test=1 | DYNAMIC -> DYNAMIC |  | 6.645s median | yes | PASS |
| https://mayushdesign.com/cart | private/dynamic | DYNAMIC -> DYNAMIC |  | 1.987s median | yes | PASS |
| https://mayushdesign.com/checkout | private/dynamic | DYNAMIC -> DYNAMIC |  | 0.224s median | yes | PASS |
| https://mayushdesign.com/login | private/dynamic | DYNAMIC -> DYNAMIC |  | 0.286s median | yes | PASS |
| https://mayushdesign.com/register | private/dynamic | DYNAMIC -> DYNAMIC |  | 0.220s median | yes | PASS |
| https://mayushdesign.com/admin | private/dynamic | DYNAMIC -> DYNAMIC |  | 0.338s median | yes | PASS |
| https://mayushdesign.com/seller | private/dynamic | DYNAMIC -> DYNAMIC |  | 1.942s median | yes | PASS |
| https://mayushdesign.com/dashboard | private/dynamic | DYNAMIC -> DYNAMIC |  | 0.305s median | yes | PASS |
| https://mayushdesign.com/customer | private/dynamic | DYNAMIC -> DYNAMIC |  | 1.865s median | yes | PASS |
| https://mayushdesign.com/user | private/dynamic | DYNAMIC -> DYNAMIC |  | 1.799s median | yes | PASS |
| https://mayushdesign.com/orders | private/dynamic | DYNAMIC -> DYNAMIC |  | 1.686s median | yes | PASS |
| https://mayushdesign.com/wishlist | private/dynamic | DYNAMIC -> DYNAMIC |  | 1.656s median | yes | PASS |
| https://mayushdesign.com/compare | private/dynamic | DYNAMIC -> DYNAMIC |  | 1.722s median | yes | PASS |
| https://mayushdesign.com/api | private/dynamic | DYNAMIC -> DYNAMIC |  | 2.049s median | yes | PASS |
| https://mayushdesign.com/ajax | private/dynamic | DYNAMIC -> DYNAMIC |  | 1.742s median | yes | PASS |
| https://mayushdesign.com/payment | private/dynamic | DYNAMIC -> DYNAMIC |  | 1.821s median | yes | PASS |
| https://mayushdesign.com/cmi | private/dynamic | DYNAMIC -> DYNAMIC |  | 1.921s median | yes | PASS |

## D. Static Asset Cache Results

| asset URL | type | GET cache progression | age | cache-control | TTFB | result |
| --- | --- | --- | --- | --- | --- | --- |
| https://mayushdesign.com/assets/css/custom-style.css | stylesheet | MISS -> HIT -> HIT -> HIT -> HIT | 77189 | public, max-age=2592000 | 0.135s median | PASS |
| https://mayushdesign.com/assets/js/aiz-core.js | script | HIT -> HIT -> MISS -> HIT -> HIT | 1 | public, max-age=2592000 | 0.141s median | PASS |
| https://mayushdesign.com/public/assets/css/vendors.css | stylesheet | HIT -> HIT -> HIT -> HIT -> HIT | 85308 | public, max-age=2592000 | 0.136s median | PASS |
| https://mayushdesign.com/public/assets/js/vendors.js | script | MISS -> MISS -> HIT -> HIT -> HIT | 3 | public, max-age=2592000 | 0.149s median | PASS |
| https://mayushdesign.com/public/uploads/all/NQErD03t1rIispRs3lhXOlXiI9y7PRHkyDdUWa2g.webp | webp | HIT -> HIT -> HIT -> HIT -> HIT | 85405 | public, max-age=31536000, immutable | 0.135s median | PASS |
| https://mayushdesign.com/public/uploads/all/TGRZ28TzCTIZDiAelEAAhxTKq5cLgZkkd8rMb7bB.webp | webp | MISS -> HIT -> HIT -> HIT -> HIT | 1 | public, max-age=31536000, immutable | 0.174s median | PASS |
| https://mayushdesign.com/public/uploads/all/XRCeu6Dd7oTarmD5rx9W03FfaYPQvRJ1RpspNe04.webp | webp | MISS -> HIT -> HIT -> HIT -> HIT | 1 | public, max-age=31536000, immutable | 0.146s median | PASS |
| https://mayushdesign.com/public/assets/img/flags/fr.png | image | HIT -> HIT -> HIT -> HIT -> HIT | 85417 | public, max-age=31536000, immutable | 0.135s median | PASS |
| https://mayushdesign.com/public/assets/img/placeholder.jpg | image | HIT -> HIT -> HIT -> HIT -> HIT | 85323 | public, max-age=31536000, immutable | 0.142s median | PASS |
| https://mayushdesign.com/public/assets/img/flags/ma.png | image | MISS -> MISS -> HIT -> HIT -> HIT | 1 | public, max-age=31536000, immutable | 0.154s median | PASS |
| https://mayushdesign.com/public/uploads/all/aFGC28EwvkcME7ykmgHiSLI2ef4JvbhaMDgl1Uva.svg | svg/ico | HIT -> HIT -> HIT -> HIT -> HIT | 85424 | public, max-age=31536000, immutable | 0.150s median | PASS |

## E. Production Browser Navigation Results

| URL | profile | median TTFB | p75 TTFB | median FCP | median LCP | median CLS | median load time | total transfer size | result |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| https://mayushdesign.com/ | Desktop realistic | 59 ms | 78 ms | 400 ms | 640 ms | 0.0211 | 995 ms | 2816 KB | PASS |
| https://mayushdesign.com/category/office-furniture | Desktop realistic | 47 ms | 57 ms | 284 ms | 284 ms | 0.2991 | 759 ms | 3994 KB | WARNING |
| https://mayushdesign.com/category/office-desks | Desktop realistic | 61 ms | 70 ms | 368 ms | 368 ms | 0.1928 | 841 ms | 1458 KB | WARNING |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | Desktop realistic | 57 ms | 60 ms | 408 ms | 456 ms | 0.0196 | 1315 ms | 1377 KB | PASS |
| https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3 | Desktop realistic | 53 ms | 62 ms | 384 ms | 436 ms | 0.0198 | 1289 ms | 1533 KB | PASS |
| https://mayushdesign.com/blog/perfect-home-office-design | Desktop realistic | 59 ms | 62 ms | 160 ms | 500 ms | 0.0244 | 848 ms | 1322 KB | PASS |
| https://mayushdesign.com/contact-us | Desktop realistic | 58 ms | 1971 ms | 380 ms | 380 ms | 0.0258 | 942 ms | 1475 KB | PASS |
| https://mayushdesign.com/ | Mobile controlled | 58 ms | 2527 ms | 380 ms | 640 ms | 0.0232 | 981 ms | 2816 KB | PASS |
| https://mayushdesign.com/category/office-furniture | Mobile controlled | 57 ms | 2723 ms | 384 ms | 10972 ms | 0.0371 | 882 ms | 2566 KB | FAIL |
| https://mayushdesign.com/category/office-desks | Mobile controlled | 58 ms | 2891 ms | 360 ms | 10956 ms | 0.0371 | 850 ms | 1454 KB | FAIL |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | Mobile controlled | 63 ms | 6456 ms | 408 ms | 460 ms | 0.0231 | 1335 ms | 1377 KB | PASS |
| https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3 | Mobile controlled | 62 ms | 6733 ms | 424 ms | 456 ms | 0.0231 | 1322 ms | 1533 KB | PASS |
| https://mayushdesign.com/blog/perfect-home-office-design | Mobile controlled | 59 ms | 65 ms | 156 ms | 10916 ms | 0.0236 | 828 ms | 1097 KB | FAIL |
| https://mayushdesign.com/contact-us | Mobile controlled | 60 ms | 1706 ms | 396 ms | 10980 ms | 0.0230 | 888 ms | 1475 KB | FAIL |

## F. Before vs After Performance Summary

| metric | previous value | production final value | status |
| --- | --- | --- | --- |
| homepage desktop LCP | 1136 ms | 640 ms | improved |
| homepage mobile LCP | 2028 ms | 640 ms | improved |
| category office-furniture desktop CLS | 0.491 | 0.2991 | improved |
| category office-furniture mobile CLS | 0.732 | 0.0371 | improved |
| category office-desks desktop CLS | 0.527 | 0.1928 | improved |
| category office-desks mobile CLS | 1.465 | 0.0371 | improved |
| product 1 mobile long tasks | 224 / 30480 ms | 5 / 689 ms | improved |
| product 2 mobile long tasks | 203 / 23140 ms | 5 / 664 ms | improved |
| product 1 mobile LCP | 2352 ms | 460 ms | improved |
| product 2 mobile LCP | 1960 ms | 456 ms | improved |
| contact mobile CLS | 0.248 | 0.0230 | improved |
| popup LCP status | popup became LCP on several mobile routes | still observed as LCP | not fixed |
| static asset GET cache status | GREEN | GREEN | preserved |
| cookie/query/private safety | GREEN | GREEN | preserved |

## G. LCP Final Diagnosis

| URL | profile | final LCP element | image URL | popup image became LCP | dimension/priority state | status | remaining fix |
| --- | --- | --- | --- | --- | --- | --- | --- |
| https://mayushdesign.com/ | Desktop realistic | div.slick-slide.slick-current.slick-active > div > div.carousel-box.h-auto > div.metro-hero-slide.has-content.d-block > img.img-fit.h-100.m-auto | https://mayushdesign.com/public/uploads/all/DMRKkB5dKsEoy6R3KLMxvkNUds9uBtn70mzQupp1_large.webp | no | dimensions present | PASS |  |
| https://mayushdesign.com/category/office-furniture | Desktop realistic | div.aiz-main-wrapper.d-flex.flex-column:nth-of-type(2) > section.bg-light.border-top.border-bottom:nth-of-type(2) > div.container.py-32px > div.footer-desc-container > p.footer-text-control.fs-13.text-gray-dark |  | no | n/a or missing | PASS |  |
| https://mayushdesign.com/category/office-desks | Desktop realistic | div.aiz-main-wrapper.d-flex.flex-column:nth-of-type(2) > section.bg-light.border-top.border-bottom:nth-of-type(2) > div.container.py-32px > div.footer-desc-container > p.footer-text-control.fs-13.text-gray-dark |  | no | n/a or missing | PASS |  |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | Desktop realistic | div.col-md-10.col-lg-9.col-xl-10:nth-of-type(2) > div.swiper.main-slider.position-relative > div.swiper-wrapper:nth-of-type(1) > div.swiper-slide.rounded-corner-8px.border:nth-of-type(1) > img.img-fluid.w-100.h-100 | https://mayushdesign.com/public/uploads/all/Sklh6U6vfUREXMSHm2dOhC8kVXFNN1Ie6TFBSJ3I.webp | no | dimensions present | PASS |  |
| https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3 | Desktop realistic | div.col-md-10.col-lg-9.col-xl-10:nth-of-type(2) > div.swiper.main-slider.position-relative > div.swiper-wrapper:nth-of-type(1) > div.swiper-slide.rounded-corner-8px.border:nth-of-type(1) > img.img-fluid.w-100.h-100 | https://mayushdesign.com/public/uploads/all/jLMBOAdY2XtkLKOiHQ2fQSezIHm10PeupyNYFcaE.webp | no | dimensions present | PASS |  |
| https://mayushdesign.com/blog/perfect-home-office-design | Desktop realistic | div.container:nth-of-type(2) > div.row.gutters-16.justify-content-center > div.col-xxl-7.col-lg-8:nth-of-type(1) > div.mb-4 > img.mb-blog-article-image.w-100.mt-3 | https://mayushdesign.com/public/assets/img/placeholder-rect.jpg | no | n/a or missing | PASS |  |
| https://mayushdesign.com/contact-us | Desktop realistic | div.aiz-main-wrapper.d-flex.flex-column:nth-of-type(2) > section.eai-hero.eai-grid-bg:nth-of-type(1) > div.container:nth-of-type(3) > div.eai-hero-content > h1 |  | no | n/a or missing | PASS |  |
| https://mayushdesign.com/ | Mobile controlled | div.home-slider.slider-full > div.aiz-carousel.dots-inside-bottom.mobile-img-auto-height > div.carousel-box.h-auto > div.metro-hero-slide.has-content.d-block > img.img-fit.h-100.m-auto | https://mayushdesign.com/public/uploads/all/DMRKkB5dKsEoy6R3KLMxvkNUds9uBtn70mzQupp1_large.webp | no | n/a or missing | PASS |  |
| https://mayushdesign.com/category/office-furniture | Mobile controlled | div.modal-dialog.modal-dialog-centered.modal-dialog-zoom:nth-of-type(2) > div.modal-content.position-relative.border-0 > div.aiz-editor-data:nth-of-type(1) > div.d-block > img.w-100.mayush-deferred-popup-image | https://mayushdesign.com/public/uploads/all/2H9sh0ezrlFNDB56hN7jiUyp7qIikGaR4fMCDB1k.webp | yes | dimensions present | FAIL | Keep popup image deferred until after user-visible delay. |
| https://mayushdesign.com/category/office-desks | Mobile controlled | div.modal-dialog.modal-dialog-centered.modal-dialog-zoom:nth-of-type(2) > div.modal-content.position-relative.border-0 > div.aiz-editor-data:nth-of-type(1) > div.d-block > img.w-100.mayush-deferred-popup-image | https://mayushdesign.com/public/uploads/all/2H9sh0ezrlFNDB56hN7jiUyp7qIikGaR4fMCDB1k.webp | yes | dimensions present | FAIL | Keep popup image deferred until after user-visible delay. |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | Mobile controlled | div.col-md-10.col-lg-9.col-xl-10:nth-of-type(2) > div.swiper.main-slider.position-relative > div.swiper-wrapper:nth-of-type(1) > div.swiper-slide.rounded-corner-8px.border:nth-of-type(1) > img.img-fluid.w-100.h-100 | https://mayushdesign.com/public/uploads/all/Sklh6U6vfUREXMSHm2dOhC8kVXFNN1Ie6TFBSJ3I.webp | no | dimensions present | PASS |  |
| https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3 | Mobile controlled | div.col-md-10.col-lg-9.col-xl-10:nth-of-type(2) > div.swiper.main-slider.position-relative > div.swiper-wrapper:nth-of-type(1) > div.swiper-slide.rounded-corner-8px.border:nth-of-type(1) > img.img-fluid.w-100.h-100 | https://mayushdesign.com/public/uploads/all/jLMBOAdY2XtkLKOiHQ2fQSezIHm10PeupyNYFcaE.webp | no | dimensions present | PASS |  |
| https://mayushdesign.com/blog/perfect-home-office-design | Mobile controlled | div.modal-dialog.modal-dialog-centered.modal-dialog-zoom:nth-of-type(2) > div.modal-content.position-relative.border-0 > div.aiz-editor-data:nth-of-type(1) > div.d-block > img.w-100.mayush-deferred-popup-image | https://mayushdesign.com/public/uploads/all/2H9sh0ezrlFNDB56hN7jiUyp7qIikGaR4fMCDB1k.webp | yes | dimensions present | FAIL | Keep popup image deferred until after user-visible delay. |
| https://mayushdesign.com/contact-us | Mobile controlled | div.modal-dialog.modal-dialog-centered.modal-dialog-zoom:nth-of-type(2) > div.modal-content.position-relative.border-0 > div.aiz-editor-data:nth-of-type(1) > div.d-block > img.w-100.mayush-deferred-popup-image | https://mayushdesign.com/public/uploads/all/2H9sh0ezrlFNDB56hN7jiUyp7qIikGaR4fMCDB1k.webp | yes | dimensions present | FAIL | Keep popup image deferred until after user-visible delay. |

## H. CLS Final Diagnosis

| URL | profile | previous CLS | final production CLS | improvement | remaining shifting elements | status |
| --- | --- | --- | --- | --- | --- | --- |
| https://mayushdesign.com/ | Desktop realistic | 0.0010 | 0.0211 | -2014.1% | 0.000 html.storefront-profile-core.storefront-profile-home > body > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(1); 0.000 html.storefront-profile-core.storefront-profile-home > body > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(2); 0.000 html.storefront-profile-core.storefront-profile-home > body > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(3) | PASS |
| https://mayushdesign.com/category/office-furniture | Desktop realistic | 0.4910 | 0.2991 | 39.1% | 0.000 html.storefront-profile-core.storefront-profile-listing > body > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(1); 0.000 html.storefront-profile-core.storefront-profile-listing > body > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(2); 0.000 html.storefront-profile-core.storefront-profile-listing > body > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(3) | WARNING |
| https://mayushdesign.com/category/office-desks | Desktop realistic | 0.5270 | 0.1928 | 63.4% | 0.000 html.storefront-profile-core.storefront-profile-listing > body > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(3); 0.000 html.storefront-profile-core.storefront-profile-listing > body > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(1); 0.000 html.storefront-profile-core.storefront-profile-listing > body > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(2) | WARNING |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | Desktop realistic |  | 0.0196 |  | 0.000 html.storefront-profile-core.storefront-profile-product > body > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(1); 0.000 html.storefront-profile-core.storefront-profile-product > body > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(2); 0.000 html.storefront-profile-core.storefront-profile-product > body > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(3) | PASS |
| https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3 | Desktop realistic |  | 0.0198 |  | 0.000 html.storefront-profile-core.storefront-profile-product > body > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(1); 0.000 html.storefront-profile-core.storefront-profile-product > body > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(2); 0.000 html.storefront-profile-core.storefront-profile-product > body > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(3) | PASS |
| https://mayushdesign.com/blog/perfect-home-office-design | Desktop realistic |  | 0.0244 |  | 0.000 html.storefront-profile-core > body > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(1); 0.000 html.storefront-profile-core > body > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(2); 0.000 html.storefront-profile-core > body > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(3) | PASS |
| https://mayushdesign.com/contact-us | Desktop realistic |  | 0.0258 |  | 0.000 html.storefront-profile-core > body > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(1); 0.000 html.storefront-profile-core > body > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(2); 0.000 html.storefront-profile-core > body > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(3) | PASS |
| https://mayushdesign.com/ | Mobile controlled | 0.0040 | 0.0232 | -479.3% | 0.000 html.storefront-profile-core.storefront-profile-home > body.side-menu-closed > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(1); 0.000 html.storefront-profile-core.storefront-profile-home > body.side-menu-closed > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(2); 0.000 html.storefront-profile-core.storefront-profile-home > body.side-menu-closed > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(3) | PASS |
| https://mayushdesign.com/category/office-furniture | Mobile controlled | 0.7320 | 0.0371 | 94.9% | 0.000 html.storefront-profile-core.storefront-profile-listing > body.side-menu-closed > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(1); 0.000 html.storefront-profile-core.storefront-profile-listing > body.side-menu-closed > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(2); 0.000 html.storefront-profile-core.storefront-profile-listing > body.side-menu-closed > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(3) | PASS |
| https://mayushdesign.com/category/office-desks | Mobile controlled | 1.4650 | 0.0371 | 97.5% | 0.000 html.storefront-profile-core.storefront-profile-listing > body.side-menu-closed > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(3); 0.000 html.storefront-profile-core.storefront-profile-listing > body.side-menu-closed > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(1); 0.000 html.storefront-profile-core.storefront-profile-listing > body.side-menu-closed > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(2) | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | Mobile controlled |  | 0.0231 |  | 0.000 html.storefront-profile-core.storefront-profile-product > body.side-menu-closed > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(1); 0.000 html.storefront-profile-core.storefront-profile-product > body.side-menu-closed > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(2); 0.000 html.storefront-profile-core.storefront-profile-product > body.side-menu-closed > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(3) | PASS |
| https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3 | Mobile controlled |  | 0.0231 |  | 0.000 html.storefront-profile-core.storefront-profile-product > body.side-menu-closed > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(1); 0.000 html.storefront-profile-core.storefront-profile-product > body.side-menu-closed > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(2); 0.000 html.storefront-profile-core.storefront-profile-product > body.side-menu-closed > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(3) | PASS |
| https://mayushdesign.com/blog/perfect-home-office-design | Mobile controlled |  | 0.0236 |  | 0.000 html.storefront-profile-core > body.side-menu-closed > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(3); 0.000 html.storefront-profile-core > body.side-menu-closed > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(1); 0.000 html.storefront-profile-core > body.side-menu-closed > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(2) | PASS |
| https://mayushdesign.com/contact-us | Mobile controlled |  | 0.0230 |  | 0.000 html.storefront-profile-core > body.side-menu-closed > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(1); 0.000 html.storefront-profile-core > body.side-menu-closed > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(2); 0.000 html.storefront-profile-core > body.side-menu-closed > div.aiz-refresh:nth-of-type(4) > div.aiz-refresh-content > div:nth-of-type(3) | PASS |

## I. JavaScript / Long Task Final Diagnosis

| URL | profile | long task count | total long task duration | max long task | previous baseline | remaining heavy scripts | status |
| --- | --- | --- | --- | --- | --- | --- | --- |
| https://mayushdesign.com/ | Desktop realistic | 3 | 294 ms | 193 ms |  | img:QTYwAHoQWi2yBPijy8EIWxT1MU98hH5N3SqP6EQx.jpeg 504 KB 120 ms; script:vendors.js 466 KB 207 ms; img:i9LUP5EdnezL0LDBcpSj4OHZcKBeGmKfPIm8Mqk4.jpg 272 KB 130 ms | PASS |
| https://mayushdesign.com/category/office-furniture | Desktop realistic | 2 | 178 ms | 106 ms |  | script:vendors.js 467 KB 138 ms; img:5RmECAhfybCeQ9E7gZTgCSvaeA0W9xVz6Cu6GVW5.webp 467 KB 96 ms; img:Mr6F683xg5xht88HQZdmT6g4LTurOt5H5WhpsxLg.webp 272 KB 59 ms | PASS |
| https://mayushdesign.com/category/office-desks | Desktop realistic | 2 | 178 ms | 112 ms |  | script:vendors.js 467 KB 261 ms; img:y5eKTbqhoNf2o9XYOg4QyUWotX1yuxXmOem9UTJH.webp 133 KB 61 ms; img:WKdfAt3nJDadsWs6sEQjABWTuqe6k9igWgnMyi8b.webp 128 KB 60 ms | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | Desktop realistic | 5 | 645 ms | 345 ms |  | script:vendors.js 467 KB 163 ms; script: 119 KB 242 ms; link:CBvVD6nRCBJKb8BScvK0Hh3G4p0uXzvHADNtKukP.webp 117 KB 116 ms | PASS |
| https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3 | Desktop realistic | 5 | 622 ms | 317 ms |  | script:vendors.js 466 KB 149 ms; img:uIZm1HaStcsgZzKLyoiBxomtTQ4cJftkhs3zJ8oL.webp 151 KB 56 ms; img:V7nrFZ0lupBm8iCO0pXUiqvf97xXjsvxBYB2G613.webp 124 KB 60 ms | PASS |
| https://mayushdesign.com/blog/perfect-home-office-design | Desktop realistic | 2 | 176 ms | 117 ms |  | script:vendors.js 466 KB 58 ms; img:4U5DzjkMXokoxXNaMwdFFRInioKTUSe7lgzokBWY.webp 196 KB 37 ms; script: 119 KB 113 ms | PASS |
| https://mayushdesign.com/contact-us | Desktop realistic | 2 | 174 ms | 123 ms |  | script:vendors.js 466 KB 229 ms; img:hero-contact.svg 235 KB 49 ms; img:hero-contact.webp 234 KB 234 ms | PASS |
| https://mayushdesign.com/ | Mobile controlled | 3 | 285 ms | 195 ms |  | img:QTYwAHoQWi2yBPijy8EIWxT1MU98hH5N3SqP6EQx.jpeg 504 KB 213 ms; script:vendors.js 466 KB 487 ms; img:i9LUP5EdnezL0LDBcpSj4OHZcKBeGmKfPIm8Mqk4.jpg 272 KB 204 ms | PASS |
| https://mayushdesign.com/category/office-furniture | Mobile controlled | 2 | 185 ms | 131 ms |  | script:vendors.js 467 KB 211 ms; img:HJC0htaGECiF1aqdXBCls4wpqs5MYJFOn8TmHZl7.webp 208 KB 56 ms; img:FqebI7n17KzIslypQVGOYVv2yLahZ8fSzMa6mNRN.webp 173 KB 61 ms | PASS |
| https://mayushdesign.com/category/office-desks | Mobile controlled | 2 | 181 ms | 159 ms |  | script:vendors.js 467 KB 121 ms; img:y5eKTbqhoNf2o9XYOg4QyUWotX1yuxXmOem9UTJH.webp 133 KB 51 ms; img:WKdfAt3nJDadsWs6sEQjABWTuqe6k9igWgnMyi8b.webp 128 KB 50 ms | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | Mobile controlled | 5 | 689 ms | 677 ms | 224 / 30480 ms | script:vendors.js 466 KB 99 ms; script: 119 KB 165 ms; link:CBvVD6nRCBJKb8BScvK0Hh3G4p0uXzvHADNtKukP.webp 117 KB 55 ms | PASS |
| https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3 | Mobile controlled | 5 | 664 ms | 610 ms | 203 / 23140 ms | script:vendors.js 467 KB 189 ms; img:uIZm1HaStcsgZzKLyoiBxomtTQ4cJftkhs3zJ8oL.webp 151 KB 67 ms; img:V7nrFZ0lupBm8iCO0pXUiqvf97xXjsvxBYB2G613.webp 124 KB 68 ms | PASS |
| https://mayushdesign.com/blog/perfect-home-office-design | Mobile controlled | 2 | 179 ms | 114 ms |  | script:vendors.js 467 KB 91 ms; script: 119 KB 113 ms; css:la-solid-900.woff2 95 KB 121 ms | PASS |
| https://mayushdesign.com/contact-us | Mobile controlled | 2 | 172 ms | 169 ms |  | script:vendors.js 467 KB 277 ms; img:hero-contact.svg 235 KB 110 ms; img:hero-contact.webp 128 KB 238 ms | PASS |

## J. Production Functional Smoke Results

| check | result |
| --- | --- |
| homepage | PASS |
| categoryListing | PASS |
| productGallery | PASS |
| thumbnails | PASS |
| quantityUi | PASS |
| cartButtonVisible | PASS |
| wishlistCompareUi | PASS |
| searchUi | PASS |
| mobileHeaderMenu | PASS |
| delayedPopupBehavior | WARNING |
| noSeriousConsoleErrors | WARNING |
| productGalleryInteraction | WARNING |

## K. Remaining Issues

- The popup image still becomes mobile LCP on category, blog, and contact routes around 10.9s after navigation.
- Desktop category CLS remains above target: `office-furniture` final CLS 0.2991 and `office-desks` final CLS 0.1928.
- Product gallery and thumbnails are present, but the automated safe arrow-click smoke timed out on both product fixtures; verify manually and harden the click target if reproducible.
- Console noise remains in production, including 401/419/404 resource errors and a contact CSS MIME error; these were observed during smoke but were not clearly introduced by Phase 4.

## L. Recommended Next Actions

Immediate:
- Prevent popup/modal images from becoming LCP during measurement windows, either by extending/debouncing display past initial Web Vitals collection, using a non-LCP-safe reveal pattern, or gating popup display behind user intent/consent.
- Reduce remaining desktop category CLS by reserving the exact listing/footer/filter/header spaces identified in the layout-shift sources.
- Verify and harden product gallery arrow/thumb click targets because automated safe gallery interaction timed out even though gallery and thumbnails were present.

Medium-term:
- Add production RUM with URL type, device, country, connection, LCP, CLS, INP, FCP, TTFB, and long-task attribution.
- Continue reducing image payload on listing pages with right-sized derivatives and stricter lazy loading.
- Separate route-specific storefront JS from legacy global vendor initialization where safe.

Monitoring plan:
- Schedule this production GET + Playwright audit after deployments that touch Cloudflare rules, storefront layout, popups, images, or route scripts.
- Track Web Vitals with production RUM and alert on category CLS >= 0.10, homepage/product CLS >= 0.05, popup LCP recurrence, and product mobile long-task regressions.
- Keep GET as the static asset cache truth source; do not fail the cache verdict on HEAD-only DYNAMIC responses.

## M. Final Verdict

**RED**

Terminal summary:

```text
final verdict: RED
production homepage mobile LCP: 640 ms
worst production category mobile CLS: 0.0371
worst product mobile long-task total: 689 ms
popup LCP status: not fixed
cache safety status: preserved
static asset cache status: preserved
top 3 remaining actions:
1. Prevent delayed popup images from becoming mobile LCP on category/blog/contact routes.
2. Reduce desktop category CLS below 0.10.
3. Verify and harden product gallery arrow/thumb click targets.
```
