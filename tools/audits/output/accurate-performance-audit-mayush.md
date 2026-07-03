# Accurate Performance Audit - Mayush Marketplace

## A. Executive Summary

- Cache truth status: **GREEN**.
- Static asset cache status: **GREEN**.
- Browser performance status: **YELLOW**.
- LCP status: **YELLOW**.
- JavaScript/TBT status: **YELLOW**.
- CLS status: **YELLOW**.
- Product page consistency status: **YELLOW**.
- Overall verdict: **YELLOW**.

Cache safety is intact, public HTML warms to Cloudflare HIT, and static asset GET requests now return HIT. Remaining work is browser-side rendering plus the fact that HEAD remains unreliable for asset cache checks.

## B. Methodology

Tested on 2026-06-22T13:58:07.946Z with repeated curl GET/HEAD probes and Playwright-controlled Chromium navigation. This is more reliable than a Lighthouse score alone because it separates Cloudflare cache truth from browser rendering, uses repeated runs with median/p75 values, reads real browser Performance APIs, exports Resource Timing, observes Long Tasks and layout shifts, and keeps cold/warm navigation behavior visible.

## C. Cloudflare HTML Cache Results

| URL | Run statuses | Age | Median TTFB | p75 TTFB | Result |
|---|---|---:|---:|---:|---|
| https://mayushdesign.com/ | EXPIRED -> HIT -> HIT -> HIT -> HIT | 6 | 0.180s | 0.195s | PASS |
| https://mayushdesign.com/category/office-furniture | EXPIRED -> MISS -> HIT -> HIT -> HIT | 5 | 0.173s | 3.309s | PASS |
| https://mayushdesign.com/category/office-desks | EXPIRED -> HIT -> HIT -> HIT -> HIT | 2 | 0.164s | 0.174s | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | EXPIRED -> HIT -> HIT -> HIT -> HIT | 2 | 0.153s | 0.161s | PASS |
| https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3 | EXPIRED -> HIT -> MISS -> HIT -> HIT | 9 | 0.167s | 6.871s | PASS |
| https://mayushdesign.com/blog/perfect-home-office-design | EXPIRED -> MISS -> HIT -> HIT -> HIT | 5 | 0.149s | 2.463s | PASS |
| https://mayushdesign.com/contact-us | EXPIRED -> HIT -> HIT -> HIT -> HIT | 2 | 0.355s | 0.367s | PASS |

## D. Cookie and Query Safety Results

| URL | Cookie/query condition | Cache status | Result |
|---|---|---|---|
| https://mayushdesign.com/ | cookie laravel_session + XSRF | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/ | cookie cart | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/ | query ?test=1 | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/category/office-furniture | cookie laravel_session + XSRF | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/category/office-furniture | cookie cart | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/category/office-furniture | query ?test=1 | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/category/office-desks | cookie laravel_session + XSRF | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/category/office-desks | cookie cart | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/category/office-desks | query ?test=1 | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | cookie laravel_session + XSRF | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | cookie cart | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | query ?test=1 | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3 | cookie laravel_session + XSRF | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3 | cookie cart | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3 | query ?test=1 | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/blog/perfect-home-office-design | cookie laravel_session + XSRF | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/blog/perfect-home-office-design | cookie cart | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/blog/perfect-home-office-design | query ?test=1 | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/contact-us | cookie laravel_session + XSRF | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/contact-us | cookie cart | DYNAMIC -> DYNAMIC | PASS |
| https://mayushdesign.com/contact-us | query ?test=1 | DYNAMIC -> DYNAMIC | PASS |

## E. Static Asset Cache Results

| URL | Type | GET cache progression | HEAD cache progression | Cache-Control | Age | TTFB | Result |
|---|---|---|---|---|---:|---:|---|
| https://mayushdesign.com/public/assets/css/vendors.css | stylesheet | HIT -> HIT -> HIT -> HIT -> HIT | DYNAMIC -> DYNAMIC | public, max-age=2592000 | 1683 | 0.149s | PASS |
| https://mayushdesign.com/public/js/storefront-bootstrap.js?v=1780397529 | script | HIT -> HIT -> HIT -> HIT -> HIT | DYNAMIC -> DYNAMIC | public, max-age=2592000 | 1685 | 0.132s | PASS |
| https://mayushdesign.com/public/assets/img/flags/fr.png | image | HIT -> HIT -> HIT -> HIT -> HIT | DYNAMIC -> DYNAMIC | public, max-age=31536000, immutable | 1690 | 0.144s | PASS |
| https://mayushdesign.com/public/uploads/all/NQErD03t1rIispRs3lhXOlXiI9y7PRHkyDdUWa2g.webp | webp | HIT -> HIT -> HIT -> HIT -> HIT | DYNAMIC -> DYNAMIC | public, max-age=31536000, immutable | 1784 | 0.133s | PASS |
| https://mayushdesign.com/public/uploads/all/aFGC28EwvkcME7ykmgHiSLI2ef4JvbhaMDgl1Uva.svg | svg/ico | HIT -> HIT -> HIT -> HIT -> HIT | DYNAMIC -> DYNAMIC | public, max-age=31536000, immutable | 1695 | 0.135s | PASS |

## F. Browser Navigation Results

| URL | Profile | Median TTFB | p75 TTFB | Median FCP | Median LCP | Median CLS | Median load time | Total transfer size | Result |
|---|---|---:|---:|---:|---:|---:|---:|---:|---|
| https://mayushdesign.com/ | Desktop realistic | 130 ms | 140 ms | 456 ms | 1136 ms | 0.001 | 1930 ms | 2885231 | PASS |
| https://mayushdesign.com/ | Mobile controlled | 140 ms | 148 ms | 1124 ms | 2028 ms | 0.004 | 11909 ms | 2882844 | PASS |
| https://mayushdesign.com/category/office-furniture | Desktop realistic | 142 ms | 149 ms | 452 ms | 452 ms | 0.491 | 1363 ms | 9812281 | WARNING |
| https://mayushdesign.com/category/office-furniture | Mobile controlled | 126 ms | 131 ms | 952 ms | 15900 ms | 0.732 | 5669 ms | 9809336 | WARNING |
| https://mayushdesign.com/category/office-desks | Desktop realistic | 132 ms | 3246 ms | 420 ms | 420 ms | 0.527 | 1146 ms | 1495030 | WARNING |
| https://mayushdesign.com/category/office-desks | Mobile controlled | 143 ms | 145 ms | 948 ms | 16860 ms | 1.465 | 6797 ms | 1488222 | WARNING |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | Desktop realistic | 142 ms | 196 ms | 504 ms | 560 ms | 0.005 | 2065 ms | 1412101 | PASS |
| https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7 | Mobile controlled | 147 ms | 185 ms | 1240 ms | 2352 ms | 0.005 | 20564 ms | 1409751 | PASS |
| https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3 | Desktop realistic | 134 ms | 6888 ms | 584 ms | 616 ms | 0.005 | 2330 ms | 1571602 | PASS |
| https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3 | Mobile controlled | 137 ms | 159 ms | 1280 ms | 1960 ms | 0.005 | 20243 ms | 1569732 | PASS |
| https://mayushdesign.com/blog/perfect-home-office-design | Desktop realistic | 151 ms | 2467 ms | 180 ms | 820 ms | 0.010 | 1031 ms | 1355462 | PASS |
| https://mayushdesign.com/blog/perfect-home-office-design | Mobile controlled | 131 ms | 140 ms | 224 ms | 13348 ms | 0.013 | 3277 ms | 1123054 | WARNING |
| https://mayushdesign.com/contact-us | Desktop realistic | 130 ms | 150 ms | 424 ms | 424 ms | 0.014 | 1071 ms | 1512400 | PASS |
| https://mayushdesign.com/contact-us | Mobile controlled | 131 ms | 172 ms | 896 ms | 13336 ms | 0.248 | 3284 ms | 1293270 | WARNING |

## G. Resource Timing Diagnosis

### Top 10 Largest Resources

| URL | Type | Host | Transfer | Encoded | Duration |
|---|---|---|---:|---:|---:|
| https://mayushdesign.com/public/uploads/all/QTYwAHoQWi2yBPijy8EIWxT1MU98hH5N3SqP6EQx.jpeg | img | mayushdesign.com | 515653 | 515353 | 82 ms |
| https://mayushdesign.com/public/uploads/all/QTYwAHoQWi2yBPijy8EIWxT1MU98hH5N3SqP6EQx.jpeg | img | mayushdesign.com | 515653 | 515353 | 2650 ms |
| https://mayushdesign.com/public/assets/js/vendors.js?v=1781882436 | script | mayushdesign.com | 478233 | 477933 | 1895 ms |
| https://mayushdesign.com/public/assets/js/vendors.js?v=1781882436 | script | mayushdesign.com | 478060 | 477760 | 186 ms |
| https://mayushdesign.com/public/assets/js/vendors.js?v=1781882436 | script | mayushdesign.com | 478055 | 477755 | 159 ms |
| https://mayushdesign.com/public/assets/js/vendors.js?v=1781882436 | script | mayushdesign.com | 478055 | 477755 | 2052 ms |
| https://mayushdesign.com/public/assets/js/vendors.js?v=1781882436 | script | mayushdesign.com | 478055 | 477755 | 283 ms |
| https://mayushdesign.com/public/assets/js/vendors.js?v=1781882436 | script | mayushdesign.com | 478055 | 477755 | 159 ms |
| https://mayushdesign.com/public/assets/js/vendors.js?v=1781882436 | script | mayushdesign.com | 478051 | 477751 | 224 ms |
| https://mayushdesign.com/public/assets/js/vendors.js?v=1781882436 | script | mayushdesign.com | 477982 | 477682 | 214 ms |

### Top 10 Slowest Resources

| URL | Type | Host | Transfer | Encoded | Duration |
|---|---|---|---:|---:|---:|
| https://mayushdesign.com/public/uploads/all/5RmECAhfybCeQ9E7gZTgCSvaeA0W9xVz6Cu6GVW5.webp | img | mayushdesign.com | 477750 | 477450 | 13451 ms |
| https://mayushdesign.com/public/uploads/all/4SdqjgDQl82ZjWbA700Q09vdJfLYZlrKX9nsy4kt.webp | img | mayushdesign.com | 371572 | 371272 | 13280 ms |
| https://mayushdesign.com/public/uploads/all/jEoyqTCTGiJ4dCSOAyZ4djLLexhBp1vnfoPMHJv4.webp | img | mayushdesign.com | 295426 | 295126 | 13037 ms |
| https://mayushdesign.com/public/uploads/all/HwlkIVB8oNnAHKlEqGQTFXwAATRONrKlfkGykH2s.webp | img | mayushdesign.com | 283340 | 283040 | 12982 ms |
| https://mayushdesign.com/public/uploads/all/ciE82uiuXQ4IN4q24xpfv6F40Xc62q8UrEGOe7wB.webp | img | mayushdesign.com | 281498 | 281198 | 12963 ms |
| https://mayushdesign.com/public/uploads/all/Mr6F683xg5xht88HQZdmT6g4LTurOt5H5WhpsxLg.webp | img | mayushdesign.com | 278666 | 278366 | 12915 ms |
| https://mayushdesign.com/public/uploads/all/ywohyCVx9mo3chfCOhuEZTy4tDkGq9IU9EwGDhcN.webp | img | mayushdesign.com | 268816 | 268516 | 12872 ms |
| https://mayushdesign.com/public/uploads/all/xOWY9cEVvpfMN1NW1pBspVdVancMQgyJtdnkwFs0.webp | img | mayushdesign.com | 267822 | 267522 | 12847 ms |
| https://mayushdesign.com/public/uploads/all/hLVkq2KASycVE372j8uXC7MEN3uoKgs4Hende70U.webp | img | mayushdesign.com | 261764 | 261464 | 12783 ms |
| https://mayushdesign.com/public/uploads/all/MtcwbHmKpsbIYUYa3unRNQZmaXrkkStVKHd4josd.webp | img | mayushdesign.com | 256282 | 255982 | 12689 ms |

### Top JS Files

| URL | Type | Host | Transfer | Encoded | Duration |
|---|---|---|---:|---:|---:|
| https://mayushdesign.com/public/assets/js/vendors.js?v=1781882436 | script | mayushdesign.com | 478233 | 477933 | 1895 ms |
| https://mayushdesign.com/public/assets/js/vendors.js?v=1781882436 | script | mayushdesign.com | 478060 | 477760 | 186 ms |
| https://mayushdesign.com/public/assets/js/vendors.js?v=1781882436 | script | mayushdesign.com | 478055 | 477755 | 159 ms |
| https://mayushdesign.com/public/assets/js/vendors.js?v=1781882436 | script | mayushdesign.com | 478055 | 477755 | 2052 ms |
| https://mayushdesign.com/public/assets/js/vendors.js?v=1781882436 | script | mayushdesign.com | 478055 | 477755 | 283 ms |
| https://mayushdesign.com/public/assets/js/vendors.js?v=1781882436 | script | mayushdesign.com | 478055 | 477755 | 159 ms |
| https://mayushdesign.com/public/assets/js/vendors.js?v=1781882436 | script | mayushdesign.com | 478051 | 477751 | 224 ms |
| https://mayushdesign.com/public/assets/js/vendors.js?v=1781882436 | script | mayushdesign.com | 477982 | 477682 | 214 ms |
| https://mayushdesign.com/public/assets/js/vendors.js?v=1781882436 | script | mayushdesign.com | 477870 | 477570 | 1840 ms |
| https://mayushdesign.com/public/assets/js/vendors.js?v=1781882436 | script | mayushdesign.com | 477833 | 477533 | 2621 ms |

### Top CSS Files

| URL | Type | Host | Transfer | Encoded | Duration |
|---|---|---|---:|---:|---:|
| https://mayushdesign.com/public/assets/css/vendors.css | link | mayushdesign.com | 78404 | 78104 | 65 ms |
| https://mayushdesign.com/public/assets/css/vendors.css | link | mayushdesign.com | 78404 | 78104 | 664 ms |
| https://mayushdesign.com/public/assets/css/vendors.css | link | mayushdesign.com | 78404 | 78104 | 78 ms |
| https://mayushdesign.com/public/assets/css/vendors.css | link | mayushdesign.com | 78404 | 78104 | 460 ms |
| https://mayushdesign.com/public/assets/css/vendors.css | link | mayushdesign.com | 78404 | 78104 | 80 ms |
| https://mayushdesign.com/public/assets/css/vendors.css | link | mayushdesign.com | 78404 | 78104 | 481 ms |

### Top Images

| URL | Type | Host | Transfer | Encoded | Duration |
|---|---|---|---:|---:|---:|
| https://mayushdesign.com/public/uploads/all/QTYwAHoQWi2yBPijy8EIWxT1MU98hH5N3SqP6EQx.jpeg | img | mayushdesign.com | 515653 | 515353 | 82 ms |
| https://mayushdesign.com/public/uploads/all/QTYwAHoQWi2yBPijy8EIWxT1MU98hH5N3SqP6EQx.jpeg | img | mayushdesign.com | 515653 | 515353 | 2650 ms |
| https://mayushdesign.com/public/uploads/all/5RmECAhfybCeQ9E7gZTgCSvaeA0W9xVz6Cu6GVW5.webp | img | mayushdesign.com | 477750 | 477450 | 146 ms |
| https://mayushdesign.com/public/uploads/all/5RmECAhfybCeQ9E7gZTgCSvaeA0W9xVz6Cu6GVW5.webp | img | mayushdesign.com | 477750 | 477450 | 13451 ms |
| https://mayushdesign.com/public/uploads/all/4SdqjgDQl82ZjWbA700Q09vdJfLYZlrKX9nsy4kt.webp | img | mayushdesign.com | 371572 | 371272 | 275 ms |
| https://mayushdesign.com/public/uploads/all/4SdqjgDQl82ZjWbA700Q09vdJfLYZlrKX9nsy4kt.webp | img | mayushdesign.com | 371572 | 371272 | 13280 ms |
| https://mayushdesign.com/public/uploads/all/jEoyqTCTGiJ4dCSOAyZ4djLLexhBp1vnfoPMHJv4.webp | img | mayushdesign.com | 295426 | 295126 | 569 ms |
| https://mayushdesign.com/public/uploads/all/jEoyqTCTGiJ4dCSOAyZ4djLLexhBp1vnfoPMHJv4.webp | img | mayushdesign.com | 295426 | 295126 | 13037 ms |
| https://mayushdesign.com/public/uploads/all/HwlkIVB8oNnAHKlEqGQTFXwAATRONrKlfkGykH2s.webp | img | mayushdesign.com | 283340 | 283040 | 283 ms |
| https://mayushdesign.com/public/uploads/all/HwlkIVB8oNnAHKlEqGQTFXwAATRONrKlfkGykH2s.webp | img | mayushdesign.com | 283340 | 283040 | 12982 ms |

### Third-Party Resources

| URL | Type | Host | Transfer | Encoded | Duration |
|---|---|---|---:|---:|---:|
| https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:... | link | fonts.googleapis.com | 3741 | 3441 | 141 ms |
| https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesom... | link | maxst.icons8.com | 0 | 0 | 185 ms |
| https://static.cloudflareinsights.com/beacon.min.js/v833ccba57c9e4d2798f2e76cebdd09a11778172... | script | static.cloudflareinsights.com | 0 | 0 | 202 ms |
| https://challenges.cloudflare.com/turnstile/v0/api.js | script | challenges.cloudflare.com | 0 | 0 | 186 ms |
| https://www.googletagmanager.com/gtm.js?id=GTM-KSHLDCWK&gtg_health=1 | script | www.googletagmanager.com | 0 | 0 | 171 ms |
| https://challenges.cloudflare.com/cdn-cgi/challenge-platform/h/g/turnstile/f/ov2/av0/rch/uib... | iframe | challenges.cloudflare.com | 0 | 0 | 157 ms |
| https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:... | link | fonts.googleapis.com | 1462 | 1162 | 164 ms |
| https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesom... | link | maxst.icons8.com | 0 | 0 | 329 ms |
| https://static.cloudflareinsights.com/beacon.min.js/v833ccba57c9e4d2798f2e76cebdd09a11778172... | script | static.cloudflareinsights.com | 0 | 0 | 796 ms |
| https://challenges.cloudflare.com/turnstile/v0/api.js | script | challenges.cloudflare.com | 0 | 0 | 575 ms |

## H. LCP Diagnosis

- **Desktop realistic https://mayushdesign.com/**: IMG `div.slick-slide.slick-current > div > div.carousel-box.h-auto > div.metro-hero-slide.has-content > img.img-fit.h-100`; source: https://mayushdesign.com/public/uploads/all/DMRKkB5dKsEoy6R3KLMxvkNUds9uBtn70mzQupp1_large.webp; issue: No obvious LCP element attribute issue captured.; fix: Preload the LCP image, remove lazy loading, add fetchpriority="high", set width/height, and serve a right-sized WebP/AVIF derivative.
- **Mobile controlled https://mayushdesign.com/**: IMG `div.home-slider.slider-full > div.aiz-carousel.dots-inside-bottom > div.carousel-box.h-auto > div.metro-hero-slide.has-content > img.img-fit.h-100`; source: https://mayushdesign.com/public/uploads/all/DMRKkB5dKsEoy6R3KLMxvkNUds9uBtn70mzQupp1_large.webp; issue: No obvious LCP element attribute issue captured.; fix: Preload the LCP image, remove lazy loading, add fetchpriority="high", set width/height, and serve a right-sized WebP/AVIF derivative.
- **Desktop realistic https://mayushdesign.com/category/office-furniture**: P `div.aiz-main-wrapper.d-flex:nth-of-type(2) > section.bg-light.border-top:nth-of-type(2) > div.container.py-32px > div.footer-desc-container > p.footer-text-control.fs-13`; source: ; issue: missing fetchpriority; fix: Reduce render-blocking CSS/JS before the LCP text block and reserve stable layout space.
- **Mobile controlled https://mayushdesign.com/category/office-furniture**: IMG `div.modal-dialog.modal-dialog-centered:nth-of-type(2) > div.modal-content.position-relative > div.aiz-editor-data:nth-of-type(1) > div.d-block > img.w-100`; source: https://mayushdesign.com/public/uploads/all/2H9sh0ezrlFNDB56hN7jiUyp7qIikGaR4fMCDB1k.webp; issue: missing fetchpriority; missing explicit dimensions; fix: Preload the LCP image, remove lazy loading, add fetchpriority="high", set width/height, and serve a right-sized WebP/AVIF derivative.
- **Desktop realistic https://mayushdesign.com/category/office-desks**: P `div.aiz-main-wrapper.d-flex:nth-of-type(2) > section.bg-light.border-top:nth-of-type(2) > div.container.py-32px > div.footer-desc-container > p.footer-text-control.fs-13`; source: ; issue: missing fetchpriority; fix: Reduce render-blocking CSS/JS before the LCP text block and reserve stable layout space.
- **Mobile controlled https://mayushdesign.com/category/office-desks**: IMG `div.modal-dialog.modal-dialog-centered:nth-of-type(2) > div.modal-content.position-relative > div.aiz-editor-data:nth-of-type(1) > div.d-block > img.w-100`; source: https://mayushdesign.com/public/uploads/all/2H9sh0ezrlFNDB56hN7jiUyp7qIikGaR4fMCDB1k.webp; issue: missing fetchpriority; missing explicit dimensions; fix: Preload the LCP image, remove lazy loading, add fetchpriority="high", set width/height, and serve a right-sized WebP/AVIF derivative.
- **Desktop realistic https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7**: IMG `div.col-md-10.col-lg-9:nth-of-type(2) > div.swiper.main-slider > div.swiper-wrapper:nth-of-type(1) > div.swiper-slide.rounded-corner-8px:nth-of-type(1) > img.img-fluid.w-100`; source: https://mayushdesign.com/public/uploads/all/Sklh6U6vfUREXMSHm2dOhC8kVXFNN1Ie6TFBSJ3I.webp; issue: missing explicit dimensions; fix: Preload the LCP image, remove lazy loading, add fetchpriority="high", set width/height, and serve a right-sized WebP/AVIF derivative.
- **Mobile controlled https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7**: IMG `div.col-md-10.col-lg-9:nth-of-type(2) > div.swiper.main-slider > div.swiper-wrapper:nth-of-type(1) > div.swiper-slide.rounded-corner-8px:nth-of-type(1) > img.img-fluid.w-100`; source: https://mayushdesign.com/public/uploads/all/Sklh6U6vfUREXMSHm2dOhC8kVXFNN1Ie6TFBSJ3I.webp; issue: missing explicit dimensions; fix: Preload the LCP image, remove lazy loading, add fetchpriority="high", set width/height, and serve a right-sized WebP/AVIF derivative.
- **Desktop realistic https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3**: IMG `div.col-md-10.col-lg-9:nth-of-type(2) > div.swiper.main-slider > div.swiper-wrapper:nth-of-type(1) > div.swiper-slide.rounded-corner-8px:nth-of-type(1) > img.img-fluid.w-100`; source: https://mayushdesign.com/public/uploads/all/jLMBOAdY2XtkLKOiHQ2fQSezIHm10PeupyNYFcaE.webp; issue: missing explicit dimensions; fix: Preload the LCP image, remove lazy loading, add fetchpriority="high", set width/height, and serve a right-sized WebP/AVIF derivative.
- **Mobile controlled https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3**: IMG `div.col-md-10.col-lg-9:nth-of-type(2) > div.swiper.main-slider > div.swiper-wrapper:nth-of-type(1) > div.swiper-slide.rounded-corner-8px:nth-of-type(1) > img.img-fluid.w-100`; source: https://mayushdesign.com/public/uploads/all/jLMBOAdY2XtkLKOiHQ2fQSezIHm10PeupyNYFcaE.webp; issue: missing explicit dimensions; fix: Preload the LCP image, remove lazy loading, add fetchpriority="high", set width/height, and serve a right-sized WebP/AVIF derivative.
- **Desktop realistic https://mayushdesign.com/blog/perfect-home-office-design**: IMG `div.container:nth-of-type(2) > div.row.gutters-16 > div.col-xxl-7.col-lg-8:nth-of-type(1) > div.mb-4 > img.mb-blog-article-image.w-100`; source: https://mayushdesign.com/public/blog-assets/perfect-home-office.webp; issue: lazy-loaded LCP; missing fetchpriority; missing explicit dimensions; fix: Preload the LCP image, remove lazy loading, add fetchpriority="high", set width/height, and serve a right-sized WebP/AVIF derivative.
- **Mobile controlled https://mayushdesign.com/blog/perfect-home-office-design**: IMG `div.modal-dialog.modal-dialog-centered:nth-of-type(2) > div.modal-content.position-relative > div.aiz-editor-data:nth-of-type(1) > div.d-block > img.w-100`; source: https://mayushdesign.com/public/uploads/all/2H9sh0ezrlFNDB56hN7jiUyp7qIikGaR4fMCDB1k.webp; issue: missing fetchpriority; missing explicit dimensions; fix: Preload the LCP image, remove lazy loading, add fetchpriority="high", set width/height, and serve a right-sized WebP/AVIF derivative.
- **Desktop realistic https://mayushdesign.com/contact-us**: H1 `div.aiz-main-wrapper.d-flex:nth-of-type(2) > section.eai-hero.eai-grid-bg:nth-of-type(1) > div.container:nth-of-type(3) > div.eai-hero-content > h1`; source: ; issue: missing fetchpriority; fix: Reduce render-blocking CSS/JS before the LCP text block and reserve stable layout space.
- **Mobile controlled https://mayushdesign.com/contact-us**: IMG `div.modal-dialog.modal-dialog-centered:nth-of-type(2) > div.modal-content.position-relative > div.aiz-editor-data:nth-of-type(1) > div.d-block > img.w-100`; source: https://mayushdesign.com/public/uploads/all/2H9sh0ezrlFNDB56hN7jiUyp7qIikGaR4fMCDB1k.webp; issue: missing fetchpriority; missing explicit dimensions; fix: Preload the LCP image, remove lazy loading, add fetchpriority="high", set width/height, and serve a right-sized WebP/AVIF derivative.

## I. JavaScript / Long Task Diagnosis

- **Desktop realistic https://mayushdesign.com/**: 4 long tasks, 485 ms total, max 163 ms. Likely causes: vendor/storefront bundles and head scripts https://mayushdesign.com/public/build/storefront/core-CZO..., https://mayushdesign.com/public/build/storefront/home-UDS.... Fix: defer non-critical scripts, delay third-party scripts until interaction, split product page JS, lazy-load gallery/carousel code, and avoid initializing unused sliders/components.
- **Mobile controlled https://mayushdesign.com/**: 27 long tasks, 7601 ms total, max 1736 ms. Likely causes: vendor/storefront bundles and head scripts https://mayushdesign.com/public/build/storefront/core-CZO..., https://mayushdesign.com/public/build/storefront/home-UDS.... Fix: defer non-critical scripts, delay third-party scripts until interaction, split product page JS, lazy-load gallery/carousel code, and avoid initializing unused sliders/components.
- **Desktop realistic https://mayushdesign.com/category/office-furniture**: 3 long tasks, 274 ms total, max 119 ms. Likely causes: vendor/storefront bundles and head scripts https://mayushdesign.com/public/build/storefront/core-CZO..., https://mayushdesign.com/public/build/storefront/listing-.... Fix: defer non-critical scripts, delay third-party scripts until interaction, split product page JS, lazy-load gallery/carousel code, and avoid initializing unused sliders/components.
- **Mobile controlled https://mayushdesign.com/category/office-furniture**: 24 long tasks, 4454 ms total, max 590 ms. Likely causes: vendor/storefront bundles and head scripts https://mayushdesign.com/public/build/storefront/core-CZO..., https://mayushdesign.com/public/build/storefront/listing-.... Fix: defer non-critical scripts, delay third-party scripts until interaction, split product page JS, lazy-load gallery/carousel code, and avoid initializing unused sliders/components.
- **Desktop realistic https://mayushdesign.com/category/office-desks**: 2 long tasks, 195 ms total, max 106 ms. Likely causes: vendor/storefront bundles and head scripts https://mayushdesign.com/public/build/storefront/core-CZO..., https://mayushdesign.com/public/build/storefront/listing-.... Fix: defer non-critical scripts, delay third-party scripts until interaction, split product page JS, lazy-load gallery/carousel code, and avoid initializing unused sliders/components.
- **Mobile controlled https://mayushdesign.com/category/office-desks**: 24 long tasks, 4054 ms total, max 466 ms. Likely causes: vendor/storefront bundles and head scripts https://mayushdesign.com/public/build/storefront/core-CZO..., https://mayushdesign.com/public/build/storefront/listing-.... Fix: defer non-critical scripts, delay third-party scripts until interaction, split product page JS, lazy-load gallery/carousel code, and avoid initializing unused sliders/components.
- **Desktop realistic https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7**: 5 long tasks, 841 ms total, max 400 ms. Likely causes: vendor/storefront bundles and head scripts https://mayushdesign.com/public/build/storefront/core-CZO..., https://mayushdesign.com/public/build/storefront/product-.... Fix: defer non-critical scripts, delay third-party scripts until interaction, split product page JS, lazy-load gallery/carousel code, and avoid initializing unused sliders/components.
- **Mobile controlled https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7**: 224 long tasks, 30480 ms total, max 5668 ms. Likely causes: vendor/storefront bundles and head scripts https://mayushdesign.com/public/build/storefront/core-CZO..., https://mayushdesign.com/public/build/storefront/product-.... Fix: defer non-critical scripts, delay third-party scripts until interaction, split product page JS, lazy-load gallery/carousel code, and avoid initializing unused sliders/components.
- **Desktop realistic https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3**: 6 long tasks, 902 ms total, max 404 ms. Likely causes: vendor/storefront bundles and head scripts https://mayushdesign.com/public/build/storefront/core-CZO..., https://mayushdesign.com/public/build/storefront/product-.... Fix: defer non-critical scripts, delay third-party scripts until interaction, split product page JS, lazy-load gallery/carousel code, and avoid initializing unused sliders/components.
- **Mobile controlled https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3**: 203 long tasks, 23140 ms total, max 4280 ms. Likely causes: vendor/storefront bundles and head scripts https://mayushdesign.com/public/build/storefront/core-CZO..., https://mayushdesign.com/public/build/storefront/product-.... Fix: defer non-critical scripts, delay third-party scripts until interaction, split product page JS, lazy-load gallery/carousel code, and avoid initializing unused sliders/components.
- **Desktop realistic https://mayushdesign.com/blog/perfect-home-office-design**: 2 long tasks, 191 ms total, max 100 ms. Likely causes: vendor/storefront bundles and head scripts not isolated. Fix: defer non-critical scripts, delay third-party scripts until interaction, split product page JS, lazy-load gallery/carousel code, and avoid initializing unused sliders/components.
- **Mobile controlled https://mayushdesign.com/blog/perfect-home-office-design**: 7 long tasks, 1197 ms total, max 331 ms. Likely causes: vendor/storefront bundles and head scripts not isolated. Fix: defer non-critical scripts, delay third-party scripts until interaction, split product page JS, lazy-load gallery/carousel code, and avoid initializing unused sliders/components.
- **Desktop realistic https://mayushdesign.com/contact-us**: 2 long tasks, 169 ms total, max 99 ms. Likely causes: vendor/storefront bundles and head scripts https://mayushdesign.com/public/build/storefront/core-CZO.... Fix: defer non-critical scripts, delay third-party scripts until interaction, split product page JS, lazy-load gallery/carousel code, and avoid initializing unused sliders/components.
- **Mobile controlled https://mayushdesign.com/contact-us**: 9 long tasks, 1355 ms total, max 346 ms. Likely causes: vendor/storefront bundles and head scripts https://mayushdesign.com/public/build/storefront/core-CZO.... Fix: defer non-critical scripts, delay third-party scripts until interaction, split product page JS, lazy-load gallery/carousel code, and avoid initializing unused sliders/components.

## J. CLS Diagnosis

- **Desktop realistic https://mayushdesign.com/**: CLS 0.007. Shifting elements: 0.000 div.market-sub-row.d-none:nth-of-type(2) > div.container.h-100:nth-of-type(1) > div.d-flex.align-items-center > div.d-flex.align-items-center > div.d-flex.align-items-center, div.position-relative > form.stop-propagation > div.d-flex.position-relative > div.search-input-box.d-flex > div.d-flex.align-items-center:nth-of-type(2), div.market-primary-row:nth-of-type(1) > div.container > div.market-main-row.d-flex > a.market-location.d-none:nth-of-type(2) > span; 0.000 html > body.preloader-active > div.aiz-refresh:nth-of-type(5) > div.aiz-refresh-content > div:nth-of-type(3), html > body.preloader-active > div.aiz-refresh:nth-of-type(5) > div.aiz-refresh-content > div:nth-of-type(2), html > body.preloader-active > div.aiz-refresh:nth-of-type(5) > div.aiz-refresh-content > div:nth-of-type(1); 0.000 . Fix: set image/banner dimensions, reserve slider/product-card space, stabilize font loading, and avoid late header/cart/wishlist counter layout changes.
- **Mobile controlled https://mayushdesign.com/**: CLS 0.004. Shifting elements: 0.001 #todays_deal_section; 0.000 html > body.preloader-active.side-menu-closed > div.aiz-refresh:nth-of-type(5) > div.aiz-refresh-content > div:nth-of-type(1); 0.000 html.storefront-profile-core.storefront-profile-home > body.preloader-active.side-menu-closed > div.aiz-refresh:nth-of-type(5) > div.aiz-refresh-content > div:nth-of-type(1). Fix: set image/banner dimensions, reserve slider/product-card space, stabilize font loading, and avoid late header/cart/wishlist counter layout changes.
- **Desktop realistic https://mayushdesign.com/category/office-furniture**: CLS 0.493. Shifting elements: 0.000 div.market-sub-row.d-none:nth-of-type(2) > div.container.h-100:nth-of-type(1) > div.d-flex.align-items-center > div.d-flex.align-items-center > div.d-flex.align-items-center, div.position-relative > form.stop-propagation > div.d-flex.position-relative > div.search-input-box.d-flex > div.d-flex.align-items-center:nth-of-type(2), form > div.row > div.col-xl-9:nth-of-type(2) > ul.breadcrumb.mb-0 > li.text-dark.fw-600:nth-of-type(4), div.market-primary-row:nth-of-type(1) > div.container > div.market-main-row.d-flex > a.market-location.d-none:nth-of-type(2) > span, div.collapse-sidebar.scroll-bar-show:nth-of-type(2) > div.bg-white.border-bottom-listing-sidebar:nth-of-type(3) > div.fs-16.fw-700:nth-of-type(1) > a.dropdown-toggle.collapsed > ::after; 0.001 form > div.row > div.col-xl-9:nth-of-type(2) > div.text-left.mb-4:nth-of-type(1) > p.fs-13.text-secondary, div.row > div.col-xl-9:nth-of-type(2) > div.text-left.mb-4:nth-of-type(1) > div.d-flex.flex-column > div.mb-3.mb-xl-0:nth-of-type(1), div.row > div.col-xl-9:nth-of-type(2) > div.text-left.mb-4:nth-of-type(1) > div.d-flex.flex-column > div.d-flex.flex-wrap:nth-of-type(2), div.container.sm-px-0 > form > div.row > div.col-xl-9:nth-of-type(2) > div.px-3:nth-of-type(2), html > body.preloader-active > div.aiz-refresh:nth-of-type(5) > div.aiz-refresh-content > div:nth-of-type(3); 0.406 html.storefront-profile-core.storefront-profile-listing > body.preloader-active > div.aiz-main-wrapper.d-flex:nth-of-type(2) > section.bg-light.border-top:nth-of-type(2), html.storefront-profile-core.storefront-profile-listing > body.preloader-active > div.aiz-main-wrapper.d-flex:nth-of-type(2) > div.global-trust-strip:nth-of-type(3), div.row > div.col-xl-3:nth-of-type(1) > div.aiz-filter-sidebar.collapse-sidebar-wrap > div.collapse-sidebar.scroll-bar-show:nth-of-type(2) > div.bg-white.border-bottom-listing-sidebar:nth-of-type(3), div.row > div.col-xl-3:nth-of-type(1) > div.aiz-filter-sidebar.collapse-sidebar-wrap > div.collapse-sidebar.scroll-bar-show:nth-of-type(2) > div.bg-white.preorder-time-hide:nth-of-type(4), div.row > div.col-xl-3:nth-of-type(1) > div.aiz-filter-sidebar.collapse-sidebar-wrap > div.collapse-sidebar.scroll-bar-show:nth-of-type(2) > div.bg-white.preorder-time-hide:nth-of-type(5). Fix: set image/banner dimensions, reserve slider/product-card space, stabilize font loading, and avoid late header/cart/wishlist counter layout changes.
- **Mobile controlled https://mayushdesign.com/category/office-furniture**: CLS 0.731. Shifting elements: 0.006 form > div.row > div.col-xl-9:nth-of-type(2) > div.text-left.mb-4:nth-of-type(1) > p.fs-13.text-secondary, div.row > div.col-xl-9:nth-of-type(2) > div.text-left.mb-4:nth-of-type(1) > div.d-flex.flex-column > div.d-flex.flex-wrap:nth-of-type(2), div.col-xl-9:nth-of-type(2) > div.text-left.mb-4:nth-of-type(1) > div.d-flex.flex-column > div.mb-3.mb-xl-0:nth-of-type(1) > div.d-flex.align-items-center:nth-of-type(1), header.mayush-market-header.sticky-top > div.market-primary-row:nth-of-type(1) > div.container > div.market-main-row.d-flex > a.market-logo-link.d-flex:nth-of-type(1); 0.000 html > body.preloader-active.side-menu-closed > div.aiz-refresh:nth-of-type(5) > div.aiz-refresh-content > div:nth-of-type(3), html > body.preloader-active.side-menu-closed > div.aiz-refresh:nth-of-type(5) > div.aiz-refresh-content > div:nth-of-type(2), div.d-flex.position-relative > div.search-input-box.d-flex > div.d-flex.align-items-center:nth-of-type(2) > button.btn.btn-sm:nth-of-type(1) > i.las.la-camera; 0.015 html > body.preloader-active.side-menu-closed > div.aiz-main-wrapper.d-flex:nth-of-type(2) > div.global-trust-strip:nth-of-type(3), html > body.preloader-active.side-menu-closed > div.aiz-main-wrapper.d-flex:nth-of-type(2) > section.bg-light.border-top:nth-of-type(2), form > div.row > div.col-xl-9:nth-of-type(2) > div.text-left.mb-4:nth-of-type(1) > p.fs-13.text-secondary, div.col-xl-9:nth-of-type(2) > div.text-left.mb-4:nth-of-type(1) > div.d-flex.flex-column > div.d-flex.flex-wrap:nth-of-type(2) > div.d-xl-none.mr-2:nth-of-type(1), div.col-xl-9:nth-of-type(2) > div.text-left.mb-4:nth-of-type(1) > div.d-flex.flex-column > div.d-flex.flex-wrap:nth-of-type(2) > div.view-toggles-container.bg-white:nth-of-type(3). Fix: set image/banner dimensions, reserve slider/product-card space, stabilize font loading, and avoid late header/cart/wishlist counter layout changes.
- **Desktop realistic https://mayushdesign.com/category/office-desks**: CLS 0.523. Shifting elements: 0.000 div.d-flex.position-relative > div.search-input-box.d-flex > div.d-flex.align-items-center:nth-of-type(2) > button.btn.btn-sm:nth-of-type(1) > i.las.la-camera; 0.000 html > body.preloader-active > div.aiz-refresh:nth-of-type(5) > div.aiz-refresh-content > div:nth-of-type(3); 0.000 div.market-sub-row.d-none:nth-of-type(2) > div.container.h-100:nth-of-type(1) > div.d-flex.align-items-center > div.d-flex.align-items-center > div.d-flex.align-items-center, div.position-relative > form.stop-propagation > div.d-flex.position-relative > div.search-input-box.d-flex > div.d-flex.align-items-center:nth-of-type(2), div.market-primary-row:nth-of-type(1) > div.container > div.market-main-row.d-flex > a.market-location.d-none:nth-of-type(2) > span, form > div.row > div.col-xl-9:nth-of-type(2) > ul.breadcrumb.mb-0 > li.text-dark.fw-600:nth-of-type(4), div.collapse-sidebar.scroll-bar-show:nth-of-type(2) > div.bg-white.border-bottom-listing-sidebar:nth-of-type(3) > div.fs-16.fw-700:nth-of-type(1) > a.dropdown-toggle.collapsed > ::after. Fix: set image/banner dimensions, reserve slider/product-card space, stabilize font loading, and avoid late header/cart/wishlist counter layout changes.
- **Mobile controlled https://mayushdesign.com/category/office-desks**: CLS 1.471. Shifting elements: 0.015 html > body.preloader-active > div.aiz-main-wrapper.d-flex:nth-of-type(2) > div.global-trust-strip:nth-of-type(3), html > body.preloader-active > div.aiz-main-wrapper.d-flex:nth-of-type(2) > section.bg-light.border-top:nth-of-type(2), header.mayush-market-header.sticky-top > div.market-primary-row:nth-of-type(1) > div.container > div.market-main-row.d-flex > a.market-logo-link.d-flex:nth-of-type(1), #lang-change, form > div.row > div.col-xl-9:nth-of-type(2) > ul.breadcrumb.mb-0 > li.opacity-50.hov-opacity-100:nth-of-type(3); 0.000 html > body.preloader-active > div.aiz-refresh:nth-of-type(5) > div.aiz-refresh-content > div:nth-of-type(3), html > body.preloader-active > div.aiz-refresh:nth-of-type(5) > div.aiz-refresh-content > div:nth-of-type(2), html > body.preloader-active > div.aiz-refresh:nth-of-type(5) > div.aiz-refresh-content > div:nth-of-type(1), div.d-flex.position-relative > div.search-input-box.d-flex > div.d-flex.align-items-center:nth-of-type(2) > button.btn.btn-sm:nth-of-type(1) > i.las.la-camera; 0.000 html > body.preloader-active.side-menu-closed > div.aiz-refresh:nth-of-type(5) > div.aiz-refresh-content > div:nth-of-type(3). Fix: set image/banner dimensions, reserve slider/product-card space, stabilize font loading, and avoid late header/cart/wishlist counter layout changes.
- **Desktop realistic https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7**: CLS 0.005. Shifting elements: 0.000 div.market-sub-row.d-none:nth-of-type(2) > div.container.h-100:nth-of-type(1) > div.d-flex.align-items-center > div.d-flex.align-items-center > div.d-flex.align-items-center, div.row > div.col-sm-12.col-lg-6:nth-of-type(1) > div.product-slider-wrapper.mb-2rem > ul.breadcrumb.bg-transparent > li.fs-12.fw-400:nth-of-type(3), div.row > div.col-sm-12.col-lg-6:nth-of-type(2) > div.d-flex.flex-wrap:nth-of-type(3) > div.d-flex.align-items-center:nth-of-type(1) > div.d-flex.align-items-center:nth-of-type(2), div.position-relative > form.stop-propagation > div.d-flex.position-relative > div.search-input-box.d-flex > div.d-flex.align-items-center:nth-of-type(2), div.market-primary-row:nth-of-type(1) > div.container > div.market-main-row.d-flex > a.market-location.d-none:nth-of-type(2) > span; 0.002 div.col-sm-12.col-lg-6:nth-of-type(2) > form.product-details-page > div.border-dashed.border-1:nth-of-type(4) > div.d-flex.pb-10px:nth-of-type(1) > div:nth-of-type(2), header.mayush-market-header.sticky-top > div.market-primary-row:nth-of-type(1) > div.container > div.market-main-row.d-flex > div.dropdown.d-none:nth-of-type(4), header.mayush-market-header.sticky-top > div.market-primary-row:nth-of-type(1) > div.container > div.market-main-row.d-flex > div.d-none.d-lg-block:nth-of-type(3), div.row > div.col-sm-12.col-lg-6:nth-of-type(1) > div.product-slider-wrapper.mb-2rem > ul.breadcrumb.bg-transparent > li.fs-12.fw-400:nth-of-type(3); 0.002 div.col-md-2.col-lg-3:nth-of-type(1) > div.thumb-container.position-relative > div.swiper.thumb-slider > div.swiper-wrapper > div.swiper-slide.rounded-corner-8px:nth-of-type(2), div.col-md-2.col-lg-3:nth-of-type(1) > div.thumb-container.position-relative > div.swiper.thumb-slider > div.swiper-wrapper > div.swiper-slide.rounded-corner-8px:nth-of-type(3), div.col-md-2.col-lg-3:nth-of-type(1) > div.thumb-container.position-relative > div.swiper.thumb-slider > div.swiper-wrapper > div.swiper-slide.rounded-corner-8px:nth-of-type(4). Fix: set image/banner dimensions, reserve slider/product-card space, stabilize font loading, and avoid late header/cart/wishlist counter layout changes.
- **Mobile controlled https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7**: CLS 0.008. Shifting elements: 0.003 div.pt-30px.pb-6 > div.row > div.col-sm-12.col-lg-6:nth-of-type(2) > div.d-flex.align-items-center:nth-of-type(1) > div:nth-of-type(1), div.row > div.col-md-10.col-lg-9:nth-of-type(2) > div.swiper.main-slider > div.swiper-button-prev:nth-of-type(2) > ::after, div.row > div.col-md-10.col-lg-9:nth-of-type(2) > div.swiper.main-slider > div.swiper-button-next:nth-of-type(3) > ::after; 0.001 header.mayush-market-header.sticky-top > div.market-primary-row:nth-of-type(1) > div.container > div.market-main-row.d-flex > a.market-logo-link.d-flex:nth-of-type(1), div.pt-30px.pb-6 > div.row > div.col-sm-12.col-lg-6:nth-of-type(2) > div.d-flex.align-items-center:nth-of-type(1) > div.mx-3:nth-of-type(2), #lang-change, div.row > div.col-sm-12.col-lg-6:nth-of-type(1) > div.product-slider-wrapper.mb-2rem > ul.breadcrumb.bg-transparent > li.fs-12.fw-400:nth-of-type(2), div.pt-30px.pb-6 > div.row > div.col-sm-12.col-lg-6:nth-of-type(2) > div.d-flex.align-items-center:nth-of-type(1) > div:nth-of-type(1); 0.000 div.d-flex.position-relative > div.search-input-box.d-flex > div.d-flex.align-items-center:nth-of-type(2) > button.btn.btn-sm:nth-of-type(1) > i.las.la-camera. Fix: set image/banner dimensions, reserve slider/product-card space, stabilize font loading, and avoid late header/cart/wishlist counter layout changes.
- **Desktop realistic https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3**: CLS 0.005. Shifting elements: 0.000 form.product-details-page > div.warranty-section.pb-20px:nth-of-type(4) > ul.m-0.p-0 > li.d-flex.align-items-center > span.d-block.pl-10px:nth-of-type(2), div.d-flex.position-relative > div.search-input-box.d-flex > div.d-flex.align-items-center:nth-of-type(2) > button.btn.btn-sm:nth-of-type(1) > i.las.la-camera; 0.000 div.market-sub-row.d-none:nth-of-type(2) > div.container.h-100:nth-of-type(1) > div.d-flex.align-items-center > div.d-flex.align-items-center > div.d-flex.align-items-center, div.row > div.col-sm-12.col-lg-6:nth-of-type(1) > div.product-slider-wrapper.mb-2rem > ul.breadcrumb.bg-transparent > li.fs-12.fw-400:nth-of-type(3), div.row > div.col-sm-12.col-lg-6:nth-of-type(2) > div.d-flex.flex-wrap:nth-of-type(3) > div.d-flex.align-items-center > div.d-flex.align-items-center:nth-of-type(2), div.position-relative > form.stop-propagation > div.d-flex.position-relative > div.search-input-box.d-flex > div.d-flex.align-items-center:nth-of-type(2), div.market-primary-row:nth-of-type(1) > div.container > div.market-main-row.d-flex > a.market-location.d-none:nth-of-type(2) > span; 0.002 div.col-sm-12.col-lg-6:nth-of-type(2) > form.product-details-page > div.border-dashed.border-1:nth-of-type(2) > div.d-flex.pb-10px:nth-of-type(1) > div:nth-of-type(2), header.mayush-market-header.sticky-top > div.market-primary-row:nth-of-type(1) > div.container > div.market-main-row.d-flex > div.dropdown.d-none:nth-of-type(4), header.mayush-market-header.sticky-top > div.market-primary-row:nth-of-type(1) > div.container > div.market-main-row.d-flex > div.d-none.d-lg-block:nth-of-type(3), div.row > div.col-sm-12.col-lg-6:nth-of-type(1) > div.product-slider-wrapper.mb-2rem > ul.breadcrumb.bg-transparent > li.fs-12.fw-400:nth-of-type(3). Fix: set image/banner dimensions, reserve slider/product-card space, stabilize font loading, and avoid late header/cart/wishlist counter layout changes.
- **Mobile controlled https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3**: CLS 0.005. Shifting elements: 0.001 div.pt-30px.pb-6 > div.row > div.col-sm-12.col-lg-6:nth-of-type(2) > div.d-flex.align-items-center:nth-of-type(1) > div.mx-3:nth-of-type(2), div.row > div.col-sm-12.col-lg-6:nth-of-type(1) > div.product-slider-wrapper.mb-2rem > ul.breadcrumb.bg-transparent > li.fs-12.fw-400:nth-of-type(2), div.pt-30px.pb-6 > div.row > div.col-sm-12.col-lg-6:nth-of-type(2) > div.d-flex.align-items-center:nth-of-type(1) > div:nth-of-type(1), div.pt-30px.pb-6 > div.row > div.col-sm-12.col-lg-6:nth-of-type(2) > div.d-flex.align-items-center:nth-of-type(1) > div:nth-of-type(3); 0.001 header.mayush-market-header.sticky-top > div.market-primary-row:nth-of-type(1) > div.container > div.market-main-row.d-flex > a.market-logo-link.d-flex:nth-of-type(1), html > body.preloader-active > div.aiz-refresh:nth-of-type(5) > div.aiz-refresh-content > div:nth-of-type(3), html > body.preloader-active > div.aiz-refresh:nth-of-type(5) > div.aiz-refresh-content > div:nth-of-type(2), html > body.preloader-active > div.aiz-refresh:nth-of-type(5) > div.aiz-refresh-content > div:nth-of-type(1); 0.000 html > body.preloader-active.side-menu-closed > div.aiz-refresh:nth-of-type(5) > div.aiz-refresh-content > div:nth-of-type(1). Fix: set image/banner dimensions, reserve slider/product-card space, stabilize font loading, and avoid late header/cart/wishlist counter layout changes.
- **Desktop realistic https://mayushdesign.com/blog/perfect-home-office-design**: CLS 0.010. Shifting elements: 0.006 html > body.preloader-active; 0.000 div.market-sub-row.d-none:nth-of-type(2) > div.container.h-100:nth-of-type(1) > div.d-flex.align-items-center > div.d-flex.align-items-center > div.d-flex.align-items-center, div.position-relative > form.stop-propagation > div.d-flex.position-relative > div.search-input-box.d-flex > div.d-flex.align-items-center:nth-of-type(2), div.market-primary-row:nth-of-type(1) > div.container > div.market-main-row.d-flex > a.market-location.d-none:nth-of-type(2) > span; 0.000 html > body.preloader-active > div.aiz-refresh:nth-of-type(5) > div.aiz-refresh-content > div:nth-of-type(3), html > body.preloader-active > div.aiz-refresh:nth-of-type(5) > div.aiz-refresh-content > div:nth-of-type(2). Fix: set image/banner dimensions, reserve slider/product-card space, stabilize font loading, and avoid late header/cart/wishlist counter layout changes.
- **Mobile controlled https://mayushdesign.com/blog/perfect-home-office-design**: CLS 0.013. Shifting elements: 0.009 html > body; 0.001 #lang-change; 0.000 html.storefront-profile-core > body.preloader-active.side-menu-closed > div.aiz-refresh:nth-of-type(5) > div.aiz-refresh-content > div:nth-of-type(1). Fix: set image/banner dimensions, reserve slider/product-card space, stabilize font loading, and avoid late header/cart/wishlist counter layout changes.
- **Desktop realistic https://mayushdesign.com/contact-us**: CLS 0.014. Shifting elements: 0.000 div.market-sub-row.d-none:nth-of-type(2) > div.container.h-100:nth-of-type(1) > div.d-flex.align-items-center > div.d-flex.align-items-center > div.d-flex.align-items-center, div.position-relative > form.stop-propagation > div.d-flex.position-relative > div.search-input-box.d-flex > div.d-flex.align-items-center:nth-of-type(2), div.market-primary-row:nth-of-type(1) > div.container > div.market-main-row.d-flex > a.market-location.d-none:nth-of-type(2) > span, html > body.preloader-active > div.aiz-refresh:nth-of-type(5) > div.aiz-refresh-content > div:nth-of-type(3); 0.000 header.mayush-market-header.sticky-top > div.market-primary-row:nth-of-type(1) > div.container > div.market-main-row.d-flex > div.dropdown.d-none:nth-of-type(4), header.mayush-market-header.sticky-top > div.market-primary-row:nth-of-type(1) > div.container > div.market-main-row.d-flex > div.d-none.d-lg-block:nth-of-type(3), div.position-relative > form.stop-propagation > div.d-flex.position-relative > div.search-input-box.d-flex > div.d-flex.align-items-center:nth-of-type(2), #marketing-consent-reject; 0.013 header.mayush-market-header.sticky-top > div.market-primary-row:nth-of-type(1) > div.container > div.market-main-row.d-flex > div.d-none.d-lg-block:nth-of-type(3). Fix: set image/banner dimensions, reserve slider/product-card space, stabilize font loading, and avoid late header/cart/wishlist counter layout changes.
- **Mobile controlled https://mayushdesign.com/contact-us**: CLS 0.285. Shifting elements: 0.182 div.aiz-main-wrapper.d-flex:nth-of-type(2) > section.eai-hero.eai-grid-bg:nth-of-type(1) > div.container:nth-of-type(3) > div.eai-hero-content > p.eai-hero-body:nth-of-type(1), div.aiz-main-wrapper.d-flex:nth-of-type(2) > section.eai-hero.eai-grid-bg:nth-of-type(1) > div.container:nth-of-type(3) > div.eai-hero-content > ul.eai-hero-micro-list, div.aiz-main-wrapper.d-flex:nth-of-type(2) > section.eai-hero.eai-grid-bg:nth-of-type(1) > div.container:nth-of-type(3) > div.eai-hero-content > p.eai-hero-secondary:nth-of-type(2); 0.100 html > body.preloader-active > div.aiz-main-wrapper.d-flex:nth-of-type(2) > section.eai-hero.eai-grid-bg:nth-of-type(1) > div.container:nth-of-type(3), div.aiz-main-wrapper.d-flex:nth-of-type(2) > section.eai-hero.eai-grid-bg:nth-of-type(1) > div.container:nth-of-type(3) > div.eai-hero-content > div.eai-hero-ctas:nth-of-type(2), div.container:nth-of-type(3) > div.eai-hero-content > ul.eai-hero-micro-list > li:nth-of-type(5) > ::marker, div.container:nth-of-type(3) > div.eai-hero-content > ul.eai-hero-micro-list > li:nth-of-type(6) > ::marker; 0.000 header.mayush-market-header.sticky-top > div.market-primary-row:nth-of-type(1) > div.container > div.market-main-row.d-flex > a.market-logo-link.d-flex:nth-of-type(1). Fix: set image/banner dimensions, reserve slider/product-card space, stabilize font loading, and avoid late header/cart/wishlist counter layout changes.

## K. Product Page Special Investigation

Browser/document variation is most consistent with cache eligibility differences such as query strings or cookies, not origin-only latency.

| Test | Cache progression | Median TTFB |
|---|---|---:|
| curl normal UA | EXPIRED -> EXPIRED -> HIT | 6.594s |
| curl mobile UA | HIT -> HIT -> HIT | 0.133s |
| curl Lighthouse-like UA | HIT -> HIT -> HIT | 0.188s |
| curl query string | DYNAMIC -> DYNAMIC -> DYNAMIC | 6.698s |
| Playwright desktop no cookies | browser nav | 130 ms |
| Playwright mobile no cookies | browser nav | 134 ms |

## L. Prioritized Fix Plan

Immediate Cloudflare/static asset fixes:
1. Preserve the now-working static asset GET cache rule for `/public/assets/*`, `/public/js/*`, and `/public/uploads/*`.
2. Treat HEAD as unreliable for asset cache verification here; use GET for truth checks and keep monitoring that GET remains HIT.
3. Keep `http.cookie eq ""` on guest HTML and keep query-string bypass behavior intact.

Immediate frontend fixes:
1. Preload the LCP image on each route and remove lazy loading from the LCP candidate.
2. Defer non-critical vendor/storefront scripts and delay third-party scripts until interaction.
3. Reserve fixed dimensions for banners, sliders, product cards, and above-the-fold images.

Laravel Blade/template fixes:
1. Add `fetchpriority="high"` plus width/height on route-specific hero/product-gallery LCP images.
2. Avoid loading product gallery/carousel scripts globally on pages that do not need them.
3. Emit critical CSS for above-the-fold header, hero, and product card layout.

Medium-term improvements:
1. Split JS bundles by route and remove duplicate legacy libraries where possible.
2. Add production RUM with web-vitals grouped by URL type, device, country, connection, navigation type, bfcache, timestamp, LCP, CLS, INP, FCP, and TTFB.
3. Add a scheduled cache-safety and browser-performance regression audit after Cloudflare/template changes.

## M. Final Verdict

**YELLOW**

```text
final verdict: YELLOW
median homepage TTFB: 130 ms
median homepage LCP: 1136 ms
median product LCP: 560 ms
total JS transfer size: 4780249 bytes
total image transfer size: 3887482 bytes
top 3 fixes: LCP image preload/fetchpriority/dimensions; defer/split non-critical JS; reserve layout space to reduce CLS
```
