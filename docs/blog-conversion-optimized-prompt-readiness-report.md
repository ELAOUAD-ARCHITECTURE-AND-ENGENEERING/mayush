# Optimized Blog Conversion Prompt Readiness Report

Date: 2026-05-04
Project: Mayush Design Marketplace
Subject: Review of "Mayush Design - Existing Blog Conversion Upgrade / Antigravity Master Prompt"

## Executive Summary

The optimized prompt is materially safer and more project-aware than the earlier version. It correctly avoids a duplicate `BlogArticle` architecture, anchors the work to existing Mayush models/routes/views, preserves `/blog` and `/blog/{slug}`, uses `BusinessSetting`, and phases implementation instead of requesting one large risky rebuild.

Recommendation: approve this prompt as the implementation baseline after applying the refinements in this report.

Current readiness: **8.5 / 10**

Primary reason it is not 10 / 10: a few operational details still need sharper instructions before coding, especially HTML sanitization, encrypted settings storage, product stock visibility, pivot-table uniqueness, API route placement, and admin permission boundaries.

## Current-State Alignment Check

| Prompt Area | Current Mayush State | Assessment |
| --- | --- | --- |
| Existing article model | `App\Models\Blog` exists | Correctly reused |
| Existing category model | `App\Models\BlogCategory` exists | Correctly reused |
| Blog translations | `App\Models\BlogTranslation` exists | Correctly preserved |
| Tags | `App\Models\Tag` plus `blog_tag` exists | Correctly reused |
| Product model | `App\Models\Product` exists | Correctly reused |
| Product visibility | `Product::scopeIsApprovedPublished()` exists | Prompt correctly references it |
| Product stock | `ProductStock`, `stocks`, `current_stock`, `min_qty`, `digital`, variant logic exist | Prompt needs more exact stock rule |
| Vendor/shop | `Seller`, `Shop`, `User` exist | Correctly reused |
| Settings | `BusinessSetting` exists | Correctly reused, but encryption pattern needs detail |
| Uploads | `Upload`, `uploaded_asset()` pattern exists | Correctly reused |
| Frontend routes | `/blog`, `/blog/{slug}` exist | Correctly preserved |
| Admin routes | `admin/blog`, `admin/blog-category` exist | Correctly preserved |
| Frontend views | `frontend/blog/listing.blade.php`, `details.blade.php` exist | Correctly targeted |
| Backend views | `backend/blog_system/...` exists | Correctly targeted |
| Existing SEO | Blog views use meta sections and `SeoService` JSON-LD helpers | Prompt correctly warns against duplicate meta, but should explicitly reuse `SeoService` where possible |
| HTML sanitization | Current detail view renders `{!! $blogDescription !!}` | Prompt should make sanitization a first implementation gate |
| HTML purifier availability | `ezyang/htmlpurifier` is present in `composer.lock` | Sanitization can likely be implemented without adding a new package |

## What Improved Compared With The Earlier Prompt

- Removed the most dangerous issue: no new `BlogArticle` model or `blog_articles` table.
- Explicitly requires extension of current `Blog`, `BlogCategory`, `BlogTranslation`, `Tag`, `Product`, `Shop`, `Seller`, `User`, `Upload`, and `BusinessSetting`.
- Preserves existing public routes and admin routes.
- Moves the system toward forward-only migrations.
- Uses existing Mayush/AIZ helpers: `translate()`, `uploaded_asset()`, `single_price()`, `get_setting()`, `route()`.
- Defines safe implementation phases.
- Adds tests to prevent duplicate blog architecture.
- Adds final verification with `composer guardrails` and preflight.

## Remaining Gaps To Fix Before Implementation

### 1. HTML Sanitization Must Move To Phase 1

Current blog detail rendering uses trusted HTML output. The optimized prompt says to sanitize rich HTML, but places the practical work under frontend/security generally.

Required refinement:

- Create a `BlogContentSanitizerService` or include sanitization in `BlogRenderService`.
- Sanitize article `description` before rendering.
- Allow safe editorial tags such as headings, paragraphs, lists, links, tables, images, blockquotes, and basic formatting.
- Strip scripts, event handlers, inline JavaScript URLs, iframes unless explicitly allowlisted, and unsafe styles.
- Add a malformed/hostile HTML test.

Recommended prompt addition:

```text
Before upgrading the frontend layout, implement sanitized article rendering. The current blog details view renders rich HTML, so the first frontend/security task is to route article content through a sanitizer that allows editorial HTML but strips scripts, event handlers, javascript: URLs, and unsafe embeds.
```

### 2. Encrypted BusinessSetting Values Need A Concrete Pattern

`BusinessSetting` stores key/value settings, but it does not automatically encrypt selected keys.

Required refinement:

- `BlogSettingsService` must encrypt/decrypt sensitive keys with Laravel `Crypt`.
- Sensitive keys should use explicit getters/setters.
- Never expose decrypted keys to Blade.
- Admin edit fields should display masked values, not raw secrets.
- Saving an empty secret field should preserve the existing encrypted value.

Recommended prompt addition:

```text
For sensitive settings stored in `business_settings`, use Laravel `Crypt` inside `BlogSettingsService`. Never render decrypted provider keys. Admin forms must show masked secrets and preserve existing secrets when submitted blank.
```

### 3. Product Stock Rules Need Mayush-Specific Definition

The prompt says "out-of-stock products" should not show, but Mayush products can use:

- `digital`
- `current_stock`
- `stocks`
- variant stock quantities
- `min_qty`
- `stock_visibility_state`

Required refinement:

- Product embeds should include `Product::isApprovedPublished()`.
- Exclude auction/preorder/wholesale/digital only if that matches business intent.
- For physical products, require available stock:
  - If variant product: at least one stock row with `qty >= min_qty`.
  - Otherwise: `current_stock >= min_qty` or equivalent local stock rule.
- If existing storefront allows backorders or non-stock-managed products, document that exception.

Recommended prompt addition:

```text
Define product availability using existing Mayush product stock behavior. A physical product is embeddable only when approved, published, non-hidden, and purchasable under the same rules used by the product detail/cart flow. For variant products, require at least one stock row with quantity meeting `min_qty`; for simple products, require stock meeting `min_qty` unless Mayush has an explicit non-stock-managed exception.
```

### 4. Product Pivot Uniqueness Needs A Decision

The prompt says prevent duplicates "if appropriate." That is too loose for a migration.

Two valid choices:

- If one product can appear only once per article: unique `blog_id`, `product_id`.
- If one product can appear in multiple placements: unique `blog_id`, `product_id`, `placement`.

Recommendation:

Use unique `blog_id`, `product_id`, `placement` so the same product can be used in sidebar and post-read placements without duplicate rows in the same placement.

Recommended prompt addition:

```text
Use a unique index on `blog_id`, `product_id`, and `placement`. If `placement` is nullable, normalize default placement to `manual` before saving to keep uniqueness reliable.
```

### 5. API Route Namespace Should Follow Current API Conventions

The prompt says `GET /api/blog/products`. Current project has `routes/api.php` and existing API V2 conventions in parts of the app.

Required refinement:

- Decide whether the endpoint should be public web-style `/api/blog/products` or V2 `/api/v2/blog/products`.
- If frontend-only and CSRF-free read endpoint, public API is acceptable.
- Subscriber endpoint should preferably be web route `/blog/subscribe` with CSRF and throttle.

Recommendation:

- Product lazy loading: `GET /api/v2/blog/products` if consistent with existing public API style, or keep `/api/blog/products` only if route file already maps that way.
- Subscribe: `POST /blog/subscribe` under web middleware with CSRF and throttle.

### 6. Admin Permissions Need Exact Names

Existing controller uses permissions like:

- `view_blogs`
- `add_blog`
- `edit_blog`
- `delete_blog`
- `publish_blog`

Prompt should specify whether new screens reuse these or require new permissions.

Recommendation:

- Blog settings: `edit_blog` or new `manage_blog_settings`.
- Subscriber logs: `view_blogs` or new `view_blog_subscribers`.
- CSV export: `view_blog_subscribers` if adding permissions is acceptable.

If adding permissions, seed them and test Spatie permission behavior.

### 7. Existing SEO Service Should Be Reused Explicitly

Current blog views already call `App\Services\SeoService` for JSON-LD.

Required refinement:

- `BlogSchemaService` should wrap or delegate to `SeoService` where possible.
- Avoid two independent schema systems.

Recommended prompt addition:

```text
Before creating `BlogSchemaService`, inspect `App\Services\SeoService`. Reuse or extend it when possible, and only create a blog-specific wrapper when it prevents duplicated schema logic.
```

### 8. "Antigravity" Label Is Not Useful In Repo Documentation

The phrase "Antigravity Master Prompt" does not harm implementation, but it is not project terminology.

Recommendation:

Rename internally to:

`Mayush Existing Blog Conversion Upgrade Prompt`

This keeps documentation professional and tool-neutral.

## Implementation Readiness Score

| Category | Score | Notes |
| --- | ---: | --- |
| Architecture safety | 9/10 | Correctly avoids duplicate blog system |
| Project alignment | 9/10 | Uses real models, helpers, routes, views |
| Migration safety | 8/10 | Forward-only, but pivot uniqueness needs precision |
| Security | 7/10 | Good requirements, but sanitization/encrypted setting behavior must be concrete |
| Performance | 8/10 | Caching and eager loading covered; homepage impact noted |
| SEO | 8/10 | Strong, but should explicitly reuse existing `SeoService` |
| Admin compatibility | 8/10 | Correct path, but permission names need exact policy |
| Testability | 9/10 | Good acceptance tests and anti-duplication tests |

Overall: **8.5/10**

## Recommended Final Amendments To The Prompt

Add this section before "Implementation Phases":

```text
## Additional Current-State Safety Requirements

1. Before changing the blog details view, implement sanitized article rendering. Use the existing purifier/sanitization capabilities if available. Allow editorial HTML but strip scripts, event handlers, javascript: URLs, unsafe iframes, and unsafe styles.

2. Before creating `BlogSchemaService`, inspect `App\Services\SeoService`. Reuse or extend it to avoid duplicate schema/meta systems.

3. Store sensitive blog provider settings in `business_settings` using Laravel `Crypt` through `BlogSettingsService`. Never render decrypted keys in Blade. Blank secret fields preserve the existing encrypted value.

4. Use a unique index on `blog_id`, `product_id`, and `placement` for the blog/product pivot. Normalize empty placement to `manual`.

5. Define product embeddability with current Mayush storefront rules: approved, published, visible, and purchasable. Variant products require at least one stock row meeting `min_qty`; simple physical products require stock meeting `min_qty` unless a documented Mayush non-stock-managed exception exists.

6. Reuse existing permissions where possible:
   - Article/category CRUD: existing blog permissions.
   - Blog settings: `edit_blog` unless a new `manage_blog_settings` permission is seeded.
   - Subscriber logs/export: `view_blogs` unless a new `view_blog_subscribers` permission is seeded.

7. Prefer `POST /blog/subscribe` under web middleware for email capture so CSRF protection is automatic. Add throttle middleware. Use API route only if there is a clear frontend need.

8. Product lazy loading endpoint must follow the current API route convention selected during audit. Document whether it is `/api/blog/products` or `/api/v2/blog/products`.
```

## Suggested Phase 0 Gate

Before implementation begins, require a short Phase 0 output:

| Item | Required Answer |
| --- | --- |
| Sanitizer strategy | Which purifier/helper will sanitize `Blog.description`? |
| Secret storage strategy | How will Mailchimp/Klaviyo keys be encrypted and edited? |
| Product availability rule | Exact query/filter for approved, published, in-stock products |
| Pivot uniqueness | Confirm unique index choice |
| API route convention | Confirm `/api/blog/products` or `/api/v2/blog/products` |
| Admin permissions | Confirm reused/new permissions |
| SEO service strategy | Reuse `SeoService` or create wrapper |

Do not start Phase 1 until these decisions are documented.

## Final Recommendation

Use the optimized prompt, but include the amendments above. It is now aligned with Mayush's real Laravel structure and is safe enough for phased implementation if:

- Phase 0 decisions are documented first.
- Implementation is split into small commits.
- Existing blog tests remain passing after each phase.
- `composer guardrails` and preflight are run before final delivery.

The best next engineering move is Phase 0 plus Phase 1 only:

1. Document the seven decisions in the Phase 0 gate.
2. Add forward migrations.
3. Add model relationships/scopes/accessors.
4. Add subscriber log model.
5. Add product matcher skeleton and tests.
6. Do not touch the full frontend design until the data contracts and safety tests pass.

