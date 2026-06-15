# Blog Conversion Module Prompt Analysis And Refactor

Date: 2026-05-04
Project: Mayush Design Marketplace
Scope: Analyze and refactor the supplied "Blog Conversion Module" prompt before implementation.

## Executive Summary

The supplied prompt has a strong business vision: turn the blog into an editorial-commerce engine that connects design inspiration, marketplace products, vendor discovery, email capture, SEO, and conversion.

However, the prompt should not be implemented literally in the current Mayush codebase. The project already has a Laravel-native blog system, including `Blog`, `BlogCategory`, `BlogTranslation`, `Tag`, existing frontend `/blog` routes, admin blog CRUD, homepage blog integration, seeded articles, and multilingual support. Creating parallel `BlogArticle`, `BlogArticleController`, and `blog_articles` tables would duplicate existing architecture and increase migration, SEO, route, and admin maintenance risk.

Recommended direction: refactor the prompt to extend the existing Mayush blog system instead of replacing it.

## Current Project Audit Summary

| Area | Current State | Implementation Implication |
| --- | --- | --- |
| Framework | Laravel 10.50.2 | Use Laravel controllers, routes, Blade, Eloquent, migrations, cache, requests, services |
| Blog model | `App\Models\Blog` | Extend this model instead of creating `BlogArticle` |
| Blog categories | `App\Models\BlogCategory` | Extend existing table/model instead of creating a duplicate category system |
| Blog translations | `App\Models\BlogTranslation` | Keep multilingual article support |
| Blog tags | `App\Models\Tag` plus `blog_tag` pivot | Reuse for related articles and content taxonomy |
| Product model | `App\Models\Product` | Product embeds must reuse existing product table/model |
| Vendor/shop models | `App\Models\Seller`, `App\Models\Shop`, `App\Models\User` | Vendor spotlight should reuse existing shop/seller relationships |
| Settings | `App\Models\BusinessSetting` | Blog settings should use `business_settings`, not a new settings table |
| Upload/media | `App\Models\Upload`, upload IDs in model fields | Reuse existing upload pattern for blog images and OG image |
| Frontend routes | `GET /blog`, `GET /blog/{slug}` already exist | Update current routes/controllers safely |
| Admin routes | `admin/blog`, `admin/blog-category` already exist | Extend existing admin CRUD, do not create a separate admin module |
| Frontend views | `resources/views/frontend/blog/listing.blade.php`, `details.blade.php` | Upgrade existing views and add partials |
| Backend views | `resources/views/backend/blog_system/...` | Extend existing backend blog screens |
| SEO fields | Existing blog fields include `meta_title`, `meta_img`, `meta_description`, `meta_keywords` | Add missing SEO fields only where needed |
| Product status | `Product::scopeIsApprovedPublished()` exists | Product embeds must use approved and published products |

## Strengths In The Supplied Prompt

- Clear editorial-commerce business goal.
- Explicitly rejects WordPress and WooCommerce patterns.
- Strong audit-first requirement.
- Strong security requirements for email capture and admin forms.
- Good frontend conversion features: hero articles, product embeds, TOC, share bar, email capture, related posts.
- Good performance requirements: eager loading, caching, pagination, lazy loading.
- Good SEO requirements: article schema, product schema, canonical URL, OG/Twitter metadata.
- Good deliverable checklist.

## Main Problems In The Supplied Prompt

### 1. It Creates Duplicate Blog Architecture

The prompt proposes:

- `BlogArticle`
- `BlogArticleController`
- `blog_articles`
- `blog_article_products`

Current Mayush already uses:

- `Blog`
- `BlogController`
- `blogs`
- `BlogCategory`
- `blog_categories`

Creating `BlogArticle` as a separate model would split content between two systems and create confusion around `/blog`, admin CRUD, homepage blog cards, seeded articles, translations, SEO, and existing tests.

Recommended refactor: use the existing `Blog` model as the article model. Add conversion-specific fields to `blogs` through forward migrations.

### 2. It Underestimates Current Blog Work Already Completed

The project already has:

- Published scope.
- Multilingual article translations.
- Blog details/listing views.
- Homepage "Latest from Blog" partial.
- Blog seeders and SEO-focused interior design articles.
- Header navigation seeder for `/blog`.
- Blog platform tests.

The prompt should say "upgrade existing blog" as the primary path and only create new classes where they add new conversion behavior.

### 3. Admin Namespace Does Not Match Existing Conventions

The prompt suggests:

`app/Http/Controllers/Admin/Blog/...`

Current Mayush mostly uses root-level admin controllers such as:

- `BlogController`
- `BlogCategoryController`
- `ProductController`
- `BusinessSettingsController`

Recommended refactor: extend existing controllers first. Only introduce namespaced admin controllers if a broader admin refactor is approved.

### 4. It Does Not Account For Existing AIZ/Mayush Helpers

The prompt should require use of existing helpers:

- `translate()`
- `uploaded_asset()`
- `single_price()`
- `route()`
- `get_setting()`
- `BusinessSetting`
- existing admin layouts and permission middleware

This matters for consistency and to avoid broken UI.

### 5. It Needs A Safer Implementation Sequence

The supplied implementation order is broad and high-risk. This should be split into phases:

1. Data model extensions and tests.
2. Product relation and render services.
3. Frontend editorial-commerce views.
4. Email capture and subscriber logs.
5. Admin controls and settings.
6. SEO/schema and analytics.
7. Performance, caching, and hardening.

This keeps completed blog progress safe.

## Recommended Data Model Refactor

Do not create `blog_articles`.

Extend existing `blogs` table with forward migrations:

| Required Concept | Current Field | Add Field If Missing |
| --- | --- | --- |
| Category | `category_id` | No duplicate needed |
| Author | `user_id` | Already exists |
| Title | `title` plus translations | Already exists |
| Slug | `slug` | Already exists |
| Excerpt | `short_description` plus translations | Already exists |
| Content | `description` plus translations | Already exists |
| Featured image | `banner` | Reuse or add `hero_image` only if distinct |
| Published | `status`, `published_at` | Already exists |
| SEO title | `meta_title` | Already exists |
| SEO description | `meta_description` | Already exists |
| OG image | `meta_img` | Reuse or add `og_image` only if distinct |
| Canonical URL | Missing | Add `canonical_url` nullable |
| Schema toggle | Missing | Add `schema_enabled` boolean default true |
| Badge | Missing | Add `badge_type`, `custom_badge_text` |
| Read time | Computed | Store optional `read_time_minutes` or compute via service |
| Featured article | Missing or ambiguous | Add `is_featured` boolean |
| Vendor spotlight | Missing | Add nullable `shop_id`, `vendor_quote` if approved |

Create new tables only for new behavior:

- `blog_product` or `blog_article_products`: relation between existing `blogs.id` and `products.id`.
- `blog_subscriber_logs`: email capture audit log.

Use `business_settings` for blog settings.

## Refactored Implementation Prompt

Use this prompt instead of the original when assigning implementation work:

```text
You are a senior Laravel 10 full-stack engineer, marketplace architect, SEO-focused developer, and conversion optimization specialist.

You are working on Mayush Design, a Laravel 10 multi-vendor interior design marketplace for Morocco.

Goal:
Upgrade the existing Mayush blog into a premium editorial-commerce system that connects interior design articles, marketplace products, vendor discovery, email lead capture, SEO growth, and customer conversion.

Hard constraints:
- This is Laravel, not WordPress or WooCommerce.
- Do not create WordPress hooks, plugins, shortcodes, wp_ajax, WP_Query, WooCommerce queries, or WordPress admin logic.
- Do not duplicate existing Mayush models, tables, controllers, routes, views, settings, upload handling, or admin layouts.
- Reuse the existing `Blog`, `BlogCategory`, `BlogTranslation`, `Tag`, `Product`, `Shop`, `Seller`, `User`, `Upload`, and `BusinessSetting` patterns wherever possible.
- Use forward-only migrations. Do not edit historical migrations unless explicitly required for test-only fresh schema compatibility.
- Preserve existing blog URLs: `/blog` and `/blog/{slug}`.
- Preserve existing admin routes unless a migration plan is approved.

Audit first:
Before coding, inspect the current project and report:
1. Existing blog tables, models, controllers, routes, views, seeders, and tests.
2. Existing product, category, seller/shop, upload, settings, auth, translation, and SEO conventions.
3. Exact files to modify and exact files to create.
4. Migration plan with no duplicate blog article table.

Architecture:
Implement the feature as an extension of the existing Blog module.

Use:
- `App\Models\Blog` as the article model.
- `App\Models\BlogCategory` as the category model.
- `App\Models\Product` for product embeds.
- `App\Models\Shop` and/or `App\Models\Seller` for vendor spotlight.
- `App\Models\BusinessSetting` for blog settings.
- Existing backend blog views under `resources/views/backend/blog_system`.
- Existing frontend blog views under `resources/views/frontend/blog`.

Add only the missing structures:
- Forward migration to add conversion fields to `blogs` if missing:
  - `hero_image`
  - `badge_type`
  - `custom_badge_text`
  - `read_time_minutes`
  - `is_featured`
  - `canonical_url`
  - `schema_enabled`
  - optional `shop_id`
  - optional `vendor_quote`
- Forward migration for product relation table using existing `blogs` and `products`:
  - `blog_id`
  - `product_id`
  - `sort_order`
  - `placement`
- Forward migration for `blog_subscriber_logs`.

Services:
Create services only where they reduce controller/view complexity:
- `App\Services\Blog\BlogSettingsService`
- `App\Services\Blog\BlogTocService`
- `App\Services\Blog\BlogProductMatcherService`
- `App\Services\Blog\BlogEmailService`
- `App\Services\Blog\BlogSchemaService`

Frontend:
Upgrade `/blog` listing and `/blog/{slug}` details with:
- Editorial hero article.
- Category filtering using query parameters.
- Responsive article grid.
- Product count badge.
- Inline email capture.
- Article TOC.
- Product embeds.
- Sidebar widgets.
- Share bar.
- Related articles.
- Post-read products and email capture.
- Scoped CSS under `.mb-blog`.
- Vanilla JS only.

Admin:
Extend existing admin blog/category screens:
- Article conversion fields.
- Product assignment search.
- Featured article toggle.
- Badge controls.
- Schema toggle.
- Blog settings page backed by `BusinessSetting`.
- Subscriber logs and CSV export.

Security:
- Validate admin forms with Form Requests or existing Laravel validation patterns.
- Sanitize rich article HTML before trusted rendering.
- Escape all normal Blade output.
- Protect subscriber endpoint with CSRF for web, throttling, and honeypot.
- Encrypt external provider API keys in settings.
- Never expose provider errors or stack traces to frontend users.

Product embeds:
- Use only approved, published, visible products.
- Prefer manually assigned products.
- Fall back to category-matched, featured, or best-selling products.
- Reuse existing pricing, image, product URL, and shop/vendor helpers.

SEO:
- Respect existing Mayush meta implementation.
- Avoid duplicate meta tags.
- Add Article schema and optional Product schema when enabled.
- Add canonical URL support.

Testing:
Add or update tests for:
- Blog listing.
- Blog details.
- Article with no products.
- Manually assigned products.
- Product API filtering.
- Local email subscription logging.
- Honeypot and throttling.
- TOC malformed HTML fallback.
- Settings toggles disabled.
- Schema validity.
- No WordPress/WooCommerce patterns.

Before final response:
- Run focused tests.
- Run `composer guardrails`.
- Run `php artisan app:preflight-restore --require-blog-navigation`.
- Provide created files, modified files, migrations, routes, assumptions, and pass/fail checklist.
```

## Proposed Implementation Phases

### Phase 1: Foundation And Data Contracts

Deliverables:

- Migration for missing `blogs` conversion fields.
- Migration for `blog_product` relation.
- Migration for `blog_subscriber_logs`.
- Model relationships on `Blog` and `Product` if needed.
- Tests for relationships and published product filtering.

Risk:

- Medium. Schema changes touch existing blog.

Mitigation:

- Forward-only migrations.
- No duplicate article table.
- Keep existing `/blog` behavior passing.

### Phase 2: Services

Deliverables:

- `BlogSettingsService`
- `BlogTocService`
- `BlogProductMatcherService`
- `BlogSchemaService`
- `BlogEmailService`

Risk:

- Medium. Services touch frontend rendering and email provider behavior.

Mitigation:

- Local-only email provider first.
- External provider clients behind service methods.
- Graceful fallback when DOM extension is unavailable.

### Phase 3: Frontend Editorial-Commerce Experience

Deliverables:

- Upgraded listing page.
- Upgraded details page.
- Blog partials for hero, article card, TOC, sidebar, product embeds, email cards, related articles, share bar.
- Scoped CSS under `.mb-blog`.
- Vanilla JS for TOC, share, copy link, email forms, progress bar.

Risk:

- Medium to high. User-facing design and conversion surface.

Mitigation:

- Keep current routes.
- Keep current SEO fields.
- Test mobile and desktop layout.

### Phase 4: Admin Controls

Deliverables:

- Extended blog create/edit forms.
- Product assignment UI.
- Blog settings page.
- Subscriber log and CSV export.

Risk:

- Medium. Admin permissions and old backend layout must remain stable.

Mitigation:

- Reuse existing backend blog views and permissions.
- Avoid new admin shell.

### Phase 5: APIs, Caching, SEO, And Hardening

Deliverables:

- Product embed API.
- Subscribe endpoint.
- Cache settings and product embed responses.
- Article schema and product schema.
- Full testing checklist.

Risk:

- Medium. Public endpoints need rate limits and secure responses.

Mitigation:

- Use throttling, CSRF where applicable, honeypot, and sanitized responses.

## Acceptance Criteria

Implementation is acceptable only if:

- Existing blog URLs still work.
- Existing seeded articles still render.
- Existing admin blog CRUD still works.
- No duplicate `blog_articles` table is created.
- No WordPress or WooCommerce code is introduced.
- Product embeds never show unpublished or unapproved products.
- Email submissions are logged locally even if provider integration fails.
- All conversion features can be disabled from settings without breaking layout.
- `composer guardrails` passes.
- Focused blog tests pass.
- `php artisan app:preflight-restore --require-blog-navigation` passes.

## Recommended Next Step

Do not start with the full feature at once. Begin with Phase 1 and Phase 2:

1. Add minimal schema extensions.
2. Add relationships.
3. Add settings service.
4. Add product matcher service.
5. Add TOC service.
6. Add tests.

After those pass, upgrade frontend and admin screens in separate commits.

