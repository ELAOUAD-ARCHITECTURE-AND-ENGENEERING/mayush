# SEO Truth Investigation Report - Mayush

**Date:** 2026-04-24  
**Scope:** Full codebase SEO/GEO verification, existing `SEO/` reports, local Laravel code, generated artifacts, and live responses from `https://mayushdesign.com`.

## Executive Truth

The existing `SEO/SEO_Compliance_Matrix.md` claim of **98% overall SEO/GEO compliance is not accurate**.

There are real SEO improvements in the local codebase, including canonical tags, meta sections, Open Graph/Twitter tags on several templates, structured data, a sitemap generator command, and a local robots file. However, the project is not 98% complete because several critical items are either:

- implemented only locally but not live,
- implemented incorrectly,
- not scheduled/deployed,
- dependent on database settings that are currently poor,
- or verified by reports without enough evidence.

**Truthful status estimate:**

| Area | Local Code Status | Live Site Status | Truth |
|:---|:---:|:---:|:---|
| Technical SEO | Partial | Failing key items | Not 100% |
| Sitemap | Command exists | Empty live sitemap | Failing |
| Robots / AI bot access | Local file allows bots | Live robots blocks GPTBot, ClaudeBot, Google-Extended | Failing live |
| Canonicals | Present locally | Present live on sampled pages | Good |
| Meta titles/descriptions | Present framework-wide | Live descriptions are poor/overlong on key pages | Partial |
| Open Graph / Twitter | Partial | Missing on product and blog samples | Partial |
| Structured data | Partial | Some JSON-LD invalid live | Partial |
| H1 hierarchy | Improved locally | Live still has newsletter H1 duplication | Partial/failing live |
| Image alt text | Partial | Many templates still missing alt | Not complete |
| GEO content strategy | Some homepage FAQ work | Not enough category/city coverage | Partial |

## Methodology

1. Reviewed all files in `SEO/`.
2. Checked local SEO-related files:
   - `resources/views/frontend/layouts/app.blade.php`
   - `resources/views/frontend/index.blade.php`
   - `resources/views/frontend/product_details.blade.php`
   - `resources/views/frontend/product_listing.blade.php`
   - `resources/views/frontend/seller_shop.blade.php`
   - `resources/views/frontend/blog/details.blade.php`
   - `resources/views/frontend/inc/footer.blade.php`
   - `resources/views/frontend/product_details/image_gallery.blade.php`
   - `app/Console/Commands/GenerateSitemap.php`
   - `app/Console/Commands/UpdateSeoStrategy.php`
   - `app/Console/Kernel.php`
   - `routes/web.php`
   - `public/robots.txt`
   - `public/sitemap.xml`
   - root `sitemap.xml`
   - `.env` SEO-relevant values only.
3. Scanned `resources/views` Blade templates for headings, meta tags, JSON-LD, image alt attributes, and encoding issues.
4. Ran Laravel console checks where possible:
   - `php artisan list`
   - `php artisan schedule:list`
   - `php artisan route:list --path=sitemap` was attempted but failed because `App\Http\Controllers\Api\V2\AffiliateController` is missing. This is not an SEO finding, but it limited route-list verification.
5. Fetched live pages:
   - `/`
   - `/robots.txt`
   - `/sitemap.xml`
   - `/category/eclairage`
   - `/product/test-2`
   - `/blog`
6. Parsed live pages for title, description, H1 count, canonical, Open Graph, Twitter tags, JSON-LD count, and JSON-LD validity.

## Verified Local Code Findings

### 1. Main layout SEO exists locally

`resources/views/frontend/layouts/app.blade.php` includes:

- dynamic title,
- canonical tag,
- meta description,
- meta keywords,
- robots meta,
- fallback Open Graph tags,
- fallback Twitter tags,
- Organization JSON-LD,
- WebSite/SearchAction JSON-LD.

Evidence:

- Title: `resources/views/frontend/layouts/app.blade.php:26`
- Canonical: `resources/views/frontend/layouts/app.blade.php:31`
- Meta description: `resources/views/frontend/layouts/app.blade.php:32`
- OG tags: `resources/views/frontend/layouts/app.blade.php:55`
- Organization JSON-LD: `resources/views/frontend/layouts/app.blade.php:64`
- WebSite JSON-LD: `resources/views/frontend/layouts/app.blade.php:74`

Truth: **Implemented locally, but not fully safe.**

Problem: the JSON-LD is built with raw Blade interpolation. If a value contains quotes, line breaks, or control characters, it can break JSON. This is not theoretical: the live Organization JSON-LD is currently invalid.

### 2. Product schema exists locally

`resources/views/frontend/product_details.blade.php` contains Product JSON-LD with:

- name,
- image,
- description,
- sku,
- mpn,
- brand,
- aggregateRating when reviews exist,
- Offer,
- seller Organization.

Evidence: `resources/views/frontend/product_details.blade.php:26`

Truth: **Implemented locally, but not robust.**

Problems:

- Raw Blade interpolation can break JSON.
- `price` uses `unit_price`, not necessarily the final active price after discount/variant logic.
- Only single `Offer` exists. `AggregateOffer` is not implemented.
- Product pages sampled live had no OG/Twitter tags.

### 3. Homepage FAQ schema exists locally

`resources/views/frontend/index.blade.php` contains a hidden H1 and FAQPage schema.

Evidence:

- Hidden H1: `resources/views/frontend/index.blade.php:4`
- FAQPage JSON-LD: `resources/views/frontend/index.blade.php:506`

Truth: **Implemented locally, but not proven live.**

Problems:

- The live homepage H1 is still `Abonnez-vous a notre newsletter`, so the local H1 improvement is not reflected live.
- The local file shows mojibake text in French SEO copy, which indicates encoding damage.
- The FAQ claims metrics such as 50 sellers, 5000 products, and 92% delivery success without verified source evidence in the codebase.

### 4. Product listing SEO exists locally

`resources/views/frontend/product_listing.blade.php` includes dynamic page metadata, OG/Twitter partial metadata, BreadcrumbList schema, and a contextual H1.

Evidence:

- Meta sections: `resources/views/frontend/product_listing.blade.php:29`
- OG tags: `resources/views/frontend/product_listing.blade.php:43`
- BreadcrumbList schema: `resources/views/frontend/product_listing.blade.php:47`
- H1: `resources/views/frontend/product_listing.blade.php:517`

Truth: **Partially implemented.**

Problems:

- BreadcrumbList JSON-LD uses raw Blade interpolation.
- Only `og:title` and `og:description` are added locally in this template; full OG image/type/url coverage depends on fallback layout behavior.
- Live category page still has two H1s because the newsletter H1 is present.

### 5. Seller shop metadata exists locally

`resources/views/frontend/seller_shop.blade.php` includes meta title/description, Twitter tags, and OG tags.

Evidence:

- Meta sections: `resources/views/frontend/seller_shop.blade.php:3`
- Twitter tags: `resources/views/frontend/seller_shop.blade.php:14`
- OG tags: `resources/views/frontend/seller_shop.blade.php:22`
- Previously duplicate H1s are now H2 locally: `resources/views/frontend/seller_shop.blade.php:631`, `resources/views/frontend/seller_shop.blade.php:778`

Truth: **Partially implemented locally.**

Problem: images in this template still lack alt text in multiple places.

### 6. Footer H1 fix exists locally, but live does not match

Local `resources/views/frontend/inc/footer.blade.php` changed the footer description title to H2.

Evidence: `resources/views/frontend/inc/footer.blade.php:107`

Truth: **Fixed locally, not live.**

Live sampled pages still show `Abonnez-vous a notre newsletter` as an H1, causing duplicate H1s on category, product, and blog pages.

### 7. Sitemap generator exists, but sitemap is not production-ready

`app/Console/Commands/GenerateSitemap.php` exists and is registered by Laravel command discovery.

Evidence:

- Command file: `app/Console/Commands/GenerateSitemap.php`
- Artisan command exists: `app:generate-sitemap`
- Uses Spatie sitemap package from `composer.json:44`

Truth: **Command exists, but output and serving are broken for production.**

Problems:

- `.env` has `APP_URL=http://localhost/mayush`.
- `public/sitemap.xml` contains 1089 URLs, but all sampled/parsed URLs start with `http://localhost/mayush`.
- root `sitemap.xml` is 0 bytes.
- live `https://mayushdesign.com/sitemap.xml` returns 200 with 0 bytes.
- `app:generate-sitemap` is not scheduled in `app/Console/Kernel.php`.
- `routes/web.php` has a broken sitemap route:

```php
Route::get('/sitemap.xml', function() {
    return base_path('sitemap.xml');
});
```

Evidence: `routes/web.php:214`

That route returns the path string, not the sitemap file contents. Depending on server document root/static file handling, the site may serve the empty root file, the public file, or this incorrect route.

### 8. Robots file exists locally, but live robots is different

Local `public/robots.txt` allows:

- GPTBot,
- ChatGPT-User,
- Google-Extended,
- PerplexityBot,
- ClaudeBot,
- Bingbot,
- Googlebot,
- CCBot,
- facebookexternalhit.

Truth: **Local file is good, live file is not.**

Live `https://mayushdesign.com/robots.txt` is still Cloudflare-managed and explicitly blocks:

- GPTBot,
- ClaudeBot,
- Google-Extended.

This directly contradicts the compliance matrix item that AI bot access is complete.

### 9. Sitemap schedule is missing

`php artisan schedule:list` did not list `app:generate-sitemap`.

Truth: **Not automated in Laravel scheduler.**

The previous reports recommend a cron job, but there is no evidence in the codebase that sitemap generation runs daily.

### 10. SEO pivot command exists but should not be treated as proof

`app/Console/Commands/UpdateSeoStrategy.php` can update BusinessSetting values for SEO title, description, keywords, and translations.

Truth: **Command exists, but it is not proof that production settings are updated.**

Problems:

- It is not scheduled.
- It mutates database settings only when run.
- It contains mojibake text in French and Arabic strings.
- The live homepage meta description is still an overlong project summary, not the clean marketplace description from this command.

## Template Scan Results

Blade scan scope:

- 857 total Blade files.
- 333 frontend/preorder frontend Blade files scanned.
- 1 frontend read error: `resources/views/frontend/pay.blade.php` permission denied.

Important scan results:

| Metric | Result |
|:---|---:|
| Frontend files with JSON-LD | 5 |
| Dynamic JSON-LD blocks using unsafe raw interpolation | 5 |
| Frontend image tags scanned | 436 |
| Frontend image tags without `alt` | 320 |
| Frontend files with mojibake markers | 164 |
| Frontend files with multiple static H1s | 1 local layout file, caused by popup H1s |

Truth: **The site is not at 100% template SEO.**

The compliance matrix says image alt text and H1 hierarchy are complete. The scan does not support that globally.

Important caveat: some scanned templates are account, checkout, modal, backend-in-frontend, or partial views. Not all are SEO landing pages. Still, the claim of full template SEO completion is not true across the codebase.

## Live Site Verification

Live checks were run against `https://mayushdesign.com` on 2026-04-24.

### Homepage `/`

| Check | Result |
|:---|:---|
| HTTP status | 200 |
| Title | `MAYUSH DESIGN | L'Elegance au Coeur de Votre Espace` |
| Meta description | Present, but extremely long and unsuitable |
| H1 count | 1 |
| H1 text | `Abonnez-vous a notre newsletter` |
| OG tags | 6 |
| Twitter tags | 6 |
| Canonical | Present |
| JSON-LD blocks | 2 |
| JSON-LD validity | 1 invalid Organization block, 1 valid WebSite block |

Truth: **Homepage is not SEO-optimized enough.**  
The visible/parsed H1 is about newsletter subscription, not the marketplace. The meta description is a long project summary and includes odd assistant-like text.

### `/robots.txt`

| Check | Result |
|:---|:---|
| HTTP status | 200 |
| Source | Cloudflare managed content |
| GPTBot | Disallowed |
| ClaudeBot | Disallowed |
| Google-Extended | Disallowed |
| PerplexityBot | No disallow detected |
| ChatGPT-User | No disallow detected |

Truth: **AI bot access is not complete live.**

### `/sitemap.xml`

| Check | Result |
|:---|:---|
| HTTP status | 200 |
| Content length | 0 bytes |
| URL count | 0 |

Truth: **Live sitemap is empty. This is a critical SEO failure.**

### `/category/eclairage`

| Check | Result |
|:---|:---|
| HTTP status | 200 |
| Title | `Eclairage` |
| Meta description | Present |
| H1 count | 2 |
| H1 texts | `Lighting : Meubles et Decoration`, `Abonnez-vous a notre newsletter` |
| OG tags | 8 |
| Twitter tags | 8 |
| Canonical | Present |
| JSON-LD blocks | 3 |
| JSON-LD validity | BreadcrumbList valid, WebSite valid, Organization invalid |

Truth: **Category SEO is partially live, but H1 hierarchy and Organization schema are broken.**

### `/product/test-2`

| Check | Result |
|:---|:---|
| HTTP status | 200 |
| Title | `tets` |
| Meta description | `tets` |
| H1 count | 2 |
| H1 texts | `test`, `Abonnez-vous a notre newsletter` |
| OG tags | 0 |
| Twitter tags | 0 |
| Canonical | Present |
| JSON-LD blocks | 1 |
| JSON-LD validity | Product schema valid |

Truth: **Product schema can work, but product page social metadata is missing on this sample and content quality is poor.**

### `/blog`

| Check | Result |
|:---|:---|
| HTTP status | 200 |
| Title | Generic homepage title |
| Meta description | Generic overlong homepage/project description |
| H1 count | 2 |
| H1 texts | `Blogs`, `Abonnez-vous a notre newsletter` |
| OG tags | 0 |
| Twitter tags | 0 |
| Canonical | Present |
| JSON-LD blocks | 0 |

Truth: **Blog listing SEO is weak.**

## Existing SEO Folder Quality

The `SEO/` folder contains useful planning material but should not be treated as reliable compliance proof.

Files reviewed:

- `SEO/audit.py`
- `SEO/seo_audit_report.md`
- `SEO/technical_seo_report.md`
- `SEO/content_optimization_report.md`
- `SEO/keyword_research_report.md`
- `SEO/competitor_analysis_report.md`
- `SEO/final_optimization_report.md`
- `SEO/SEO_Compliance_Matrix.md`

Problems:

1. `SEO_Compliance_Matrix.md` claims 98% without checking live deployment.
2. Some reports contain corrupted characters/control characters.
3. `final_optimization_report.md` uses `?` markers instead of real pass/fail symbols.
4. `audit.py` is too shallow:
   - hard-coded URL,
   - regex HTML parsing,
   - no timeout,
   - no JSON-LD validation,
   - no sitemap URL host validation,
   - no canonical check,
   - no live/local comparison,
   - no schema type validation.

## Corrected Compliance Matrix

| Pillar | Old Claim | Verified Truth | Status |
|:---|:---:|:---|:---:|
| Technical Hardening | 100% | Canonicals exist, but sitemap, live robots, APP_URL, and automation fail | 45% |
| Meta & Social Tags | 100% | Layout and some templates have tags; product/blog samples missing OG/Twitter | 60% |
| Structured Data | 95% | Some schema exists; live Organization JSON-LD invalid; AggregateOffer missing | 55% |
| GEO Optimization | 90% | FAQ work exists locally, but category/city/content authority work incomplete | 45% |
| Template SEO | 100% | H1 not fixed live; many images lack alt; mojibake exists | 50% |

**Truthful overall estimate:** approximately **50-60%**, depending on whether we score local code only or live production impact.

**Live SEO readiness is lower than local code readiness** because the live sitemap is empty and live robots blocks important AI bots.

## Highest Priority Fixes

### Priority 1: Fix sitemap production delivery

Actions:

1. Set production `APP_URL=https://mayushdesign.com`.
2. Regenerate sitemap in production.
3. Ensure the served file is non-empty at `https://mayushdesign.com/sitemap.xml`.
4. Remove or fix the broken `/sitemap.xml` route in `routes/web.php`.
5. Delete or replace the empty root `sitemap.xml` if the server document root is project root.
6. Add `app:generate-sitemap` to `app/Console/Kernel.php` or configure a verified server cron.

### Priority 2: Fix live robots / Cloudflare bot rules

Actions:

1. Update Cloudflare managed robots/content signals so production does not block desired AI/search bots.
2. Confirm live `/robots.txt` allows or does not disallow:
   - Googlebot,
   - Bingbot,
   - GPTBot,
   - ChatGPT-User,
   - ClaudeBot,
   - PerplexityBot.

### Priority 3: Fix JSON-LD rendering

Actions:

1. Replace raw Blade interpolation inside JSON-LD with safe JSON encoding.
2. Validate rendered JSON-LD on homepage, category, product, blog, and seller pages.
3. Fix the live invalid Organization schema caused by multiline/control-character meta description.

### Priority 4: Fix H1 hierarchy live

Actions:

1. Ensure footer/newsletter title is not an H1 in production.
2. Ensure every indexable page has one meaningful H1.
3. Retest homepage, category, product, blog, seller, policy, and brand pages.

### Priority 5: Clean meta settings and content quality

Actions:

1. Replace the live homepage meta description with a concise 150-160 character marketplace description.
2. Avoid assistant-like text in meta descriptions.
3. Add specific meta titles/descriptions for blog listing, products, categories, sellers, and policy pages.
4. Fix product content quality issues like sample product `test-2`.

### Priority 6: Complete social metadata

Actions:

1. Ensure product pages output OG/Twitter tags.
2. Ensure blog listing and blog detail pages output OG/Twitter tags.
3. Include `og:image`, `og:type`, `og:url`, and Twitter image where appropriate.

### Priority 7: Image alt text audit

Actions:

1. Fix high-impact templates first:
   - product cards,
   - listing cards,
   - product gallery,
   - seller shop,
   - blog images,
   - homepage sections.
2. Use product/category/seller names as contextual alt text.
3. Do not leave decorative images without either useful alt or empty `alt=""`.

### Priority 8: Fix encoding/mojibake

Actions:

1. Repair mojibake in French and Arabic copy.
2. Confirm files are saved as UTF-8.
3. Re-check rendered HTML.

## Final Verdict

The project has a meaningful SEO foundation, but the **98% compliance claim is false**.

The most serious blockers are:

1. **Live sitemap is empty.**
2. **Live robots blocks important AI bots.**
3. **Sitemap generated locally uses localhost URLs.**
4. **Sitemap generation is not scheduled.**
5. **Live H1 hierarchy still has newsletter H1 duplication.**
6. **Live Organization JSON-LD is invalid.**
7. **Many image tags still lack alt text.**
8. **Some SEO files and frontend text contain encoding corruption.**

The project should be reported as **partially SEO-updated, not production-complete**.
