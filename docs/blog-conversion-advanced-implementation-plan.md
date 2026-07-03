# Blog Conversion Advanced Implementation Plan

Date: 2026-05-04
Project: Mayush Design Marketplace
Feature: Existing Blog Conversion Upgrade
Status: Ready for phased implementation

## 1. Objective

Upgrade the existing Mayush Design blog into a premium editorial-commerce system that turns interior design content into:

- Marketplace product discovery.
- Vendor/shop visibility.
- Email subscribers.
- SEO growth.
- Higher customer conversion.

This plan extends the current Laravel blog system. It does not rebuild the blog, introduce WordPress/WooCommerce concepts, or create duplicate blog article architecture.

## 2. Non-Negotiable Architecture Rules

| Rule | Requirement |
| --- | --- |
| Article model | Use existing `App\Models\Blog` |
| Category model | Use existing `App\Models\BlogCategory` |
| Translation model | Use existing `App\Models\BlogTranslation` |
| Product model | Use existing `App\Models\Product` |
| Vendor/shop models | Use existing `App\Models\Seller`, `App\Models\Shop`, `App\Models\User` |
| Settings | Use `App\Models\BusinessSetting` through a typed service |
| Public routes | Preserve `/blog` and `/blog/{slug}` |
| Admin routes | Preserve `admin/blog` and `admin/blog-category` |
| Uploads | Use existing upload picker and `uploaded_asset()` |
| Pricing | Use existing helpers such as `single_price()` and product price helpers |
| SEO | Reuse or wrap existing `App\Services\SeoService` |
| Safety | No `BlogArticle` model and no `blog_articles` table |

## 3. Phase 0 Decisions

These decisions are locked for implementation.

| Decision | Final Choice |
| --- | --- |
| Sanitizer strategy | Add `App\Services\Blog\BlogContentSanitizerService`; use existing HTMLPurifier dependency if available; fail closed to stripped safe HTML |
| Secret storage | Store encrypted provider secrets in `business_settings` using Laravel `Crypt` via `BlogSettingsService` |
| Product availability | Product must be approved, published, physical/visible as applicable, and purchasable under current Mayush stock rules |
| Pivot table | Use `blog_product` |
| Pivot uniqueness | Unique `blog_id`, `product_id`, `placement`; normalize empty placement to `manual` |
| Subscribe route | Use web route `POST /blog/subscribe` with CSRF, throttle, honeypot |
| Product lazy API | Add only when lazy loading is enabled; preferred endpoint `GET /api/v2/blog/products` if consistent during route audit |
| Admin permissions | Reuse current blog permissions first; add new permissions only if required |
| Schema | Extend or wrap `SeoService`; avoid duplicate schema systems |

## 4. Technical Specification

### 4.1 Database Migrations

Create forward-only migrations.

#### Migration A: Extend `blogs`

Add columns if missing:

| Column | Type | Notes |
| --- | --- | --- |
| `hero_image` | string nullable | Existing upload ID pattern, separate from `banner` |
| `badge_type` | string nullable | `buying_guide`, `expert_pick`, `inspiration`, `comparison`, `custom` |
| `custom_badge_text` | string nullable | Used when `badge_type=custom` |
| `read_time_minutes` | unsigned small integer nullable | Optional override; otherwise computed |
| `is_featured` | boolean default false index | Listing hero/featured selection |
| `canonical_url` | string nullable | Optional canonical override |
| `schema_enabled` | boolean default true | Per-article schema toggle |
| `shop_id` | unsigned big integer nullable index | Vendor spotlight |
| `vendor_quote` | text nullable | Editorial quote |

Acceptance:

- Migration is idempotent with `Schema::hasColumn`.
- Existing blog rows remain valid.
- Existing blog listing/detail/admin CRUD still works.

#### Migration B: Create `blog_product`

Fields:

- `id`
- `blog_id`
- `product_id`
- `placement` default `manual`
- `sort_order` default `0`
- timestamps

Indexes:

- `blog_id`
- `product_id`
- `placement`
- unique `blog_id`, `product_id`, `placement`

Foreign keys:

- Use project convention. If existing migrations avoid foreign keys, use indexed unsigned IDs and enforce through Eloquent/tests.

Acceptance:

- Same product can be used in different placements.
- Same product cannot duplicate inside the same placement.

#### Migration C: Create `blog_subscriber_logs`

Fields:

- `id`
- `email`
- `placement`
- `blog_id` nullable
- `blog_title` nullable
- `provider`
- `provider_status` nullable
- `provider_response` longText nullable
- `ip_address` nullable
- `user_agent` nullable
- `subscribed_at`
- timestamps

Indexes:

- `email`
- `placement`
- `blog_id`
- `subscribed_at`

Acceptance:

- Every subscription attempt can be logged locally.
- Logs remain queryable for admin analytics.

### 4.2 Model Updates

#### `App\Models\Blog`

Add:

- `products()`
- `manualProducts()`
- `shop()`
- `scopeFeatured()`
- `scopeByCategory($query, $category)`
- `getReadTimeMinutesAttribute()`
- `getProductCountAttribute()`
- `getPublicUrlAttribute()`
- `getHeroImageUrlAttribute()`
- `getMetaImageUrlAttribute()`

Preserve:

- `category()`
- `author()`
- `tags()`
- `translations()`
- `published()`
- multilingual `getTranslation()`

#### `App\Models\Product`

Add only if needed:

- `blogs()`

Do not change existing product pricing, stock, or publish behavior.

#### New `App\Models\BlogSubscriberLog`

Relationships:

- `blog()`: nullable belongsTo `Blog`

Guarding:

- Use explicit `$fillable` or conservative `$guarded` consistent with local style.

### 4.3 Services

Create services under `app/Services/Blog`.

#### `BlogSettingsService`

Responsibilities:

- Read/write blog settings from `BusinessSetting`.
- Cache non-sensitive settings.
- Typed getters: `bool`, `int`, `string`, `array`.
- Encrypted getters/setters for provider keys.
- Mask secret values for admin forms.
- Preserve existing secret when submitted blank.

Acceptance:

- Missing settings return safe defaults.
- Decrypted keys never render to Blade.
- Cache invalidates on setting update.

#### `BlogContentSanitizerService`

Responsibilities:

- Sanitize rich article HTML before rendering.
- Allow editorial HTML: headings, paragraphs, lists, tables, images, links, blockquotes, strong/emphasis.
- Strip scripts, event handlers, `javascript:` URLs, unsafe iframes, unsafe styles.
- Fail gracefully if purifier dependency is unavailable.

Acceptance:

- Hostile HTML test strips scripts.
- Malformed HTML does not crash details page.

#### `BlogTocService`

Responsibilities:

- Parse sanitized HTML.
- Detect H2/H3.
- Generate stable IDs.
- Inject heading IDs.
- Return modified content and TOC array.
- Fail gracefully if DOM extension is unavailable.

Acceptance:

- No TOC when no headings exist.
- Malformed HTML does not break page.

#### `BlogProductMatcherService`

Product priority:

1. Manual products from `blog_product`.
2. Products from related marketplace category if mapping exists.
3. Featured products.
4. Best-selling products using `num_of_sale` if reliable.
5. Empty collection.

Safety rules:

- Use `Product::isApprovedPublished()`.
- Exclude products that are not purchasable under current stock rules.
- Eager load thumbnail, user, user.shop, stocks, taxes.
- Cache embed results when enabled.

Acceptance:

- Unsafe products never appear in embeds or API.
- Empty product results render no block.

#### `BlogEmailService`

Providers:

- `local`
- `mailchimp`
- `klaviyo`
- `webhook`

Responsibilities:

- Process validated subscription input.
- Always log attempt locally.
- Hide raw provider errors from frontend.
- Store provider response safely for admin diagnostics.
- Emit consistent JSON response.

Acceptance:

- Local provider works without external credentials.
- Provider failure still logs attempt.
- Frontend receives safe message.

#### `BlogSchemaService`

Responsibilities:

- Inspect and reuse `SeoService` where possible.
- Generate Article schema only when enabled.
- Generate Product schema for embedded products when enabled.
- Avoid duplicate JSON-LD output.

Acceptance:

- JSON-LD is valid JSON.
- Existing blog meta sections do not duplicate.

## 5. Routes

### Web Routes

Preserve:

- `GET /blog`
- `GET /blog/{slug}`

Add:

- `POST /blog/subscribe`

Middleware:

- `web`
- CSRF
- throttle, for example `throttle:blog-subscribe`

### Admin Routes

Preserve:

- `admin/blog`
- `admin/blog-category`

Add under existing admin middleware:

- `GET admin/blog/settings`
- `POST admin/blog/settings`
- `GET admin/blog/subscribers`
- `GET admin/blog/subscribers/export`
- `GET admin/blog/products/search`

Permission policy:

- Article/category CRUD: existing blog permissions.
- Settings: reuse `edit_blog` initially.
- Subscribers/export: reuse `view_blogs` initially.
- If more granular permissions are added later, seed and test them.

### API Routes

Add only if lazy product loading is implemented:

- `GET /api/v2/blog/products`

If current route conventions require `/api/blog/products`, document the deviation before implementation.

## 6. Controllers And Requests

### Existing Controller Updates

`app/Http/Controllers/BlogController.php`

Frontend:

- Load settings.
- Load featured hero article.
- Support category query filtering.
- Eager load conversion relations.
- Render sanitized/TOC content on details.
- Pass product/embed/sidebar data to views.

Admin:

- Extend create/update handling for conversion fields.
- Save product assignments.
- Preserve current validation and permissions.

### New Controllers

Only add if keeping `BlogController` focused becomes necessary:

- `App\Http\Controllers\BlogSettingsController`
- `App\Http\Controllers\BlogSubscriberController`
- `App\Http\Controllers\Api\V2\BlogProductController`

Use root-level controller convention unless project direction changes.

### Form Requests

Create:

- `BlogSubscribeRequest`
- `BlogSettingsRequest`

Optional:

- `BlogConversionArticleRequest` if admin update logic becomes too large.

## 7. Frontend Views And Assets

### Existing Views To Update

- `resources/views/frontend/blog/listing.blade.php`
- `resources/views/frontend/blog/details.blade.php`
- `resources/views/frontend/partials/home_blog_section.blade.php` only if needed for safe product counts/read time

### Partials To Add

Under `resources/views/frontend/blog/partials`:

- `hero.blade.php`
- `article-card.blade.php`
- `email-inline.blade.php`
- `email-mid.blade.php`
- `email-sidebar.blade.php`
- `email-post-read.blade.php`
- `product-card.blade.php`
- `product-embed.blade.php`
- `sidebar.blade.php`
- `toc.blade.php`
- `share-bar.blade.php`
- `vendor-spotlight.blade.php`
- `related-articles.blade.php`
- `post-products.blade.php`
- `schema.blade.php`

Partial rules:

- Missing data renders nothing.
- Disabled feature renders nothing.
- No products renders no product block.
- Use `{{ }}` for normal output.
- Use sanitized HTML only for article body.

### CSS

Preferred asset:

- `public/assets/css/blog-conversion.css`

Scope:

- `.mb-blog`

Requirements:

- Mobile first.
- No horizontal overflow.
- 3-column article cards desktop.
- 2-column tablet.
- 1-column mobile.
- Sticky sidebar desktop only.
- Mayush orange accent.
- Avoid clashing with AIZ/global styles.

### JavaScript

Preferred asset:

- `public/assets/js/blog-conversion.js`

Rules:

- Vanilla JavaScript only.
- Load only on blog pages.
- Defer script.
- Do not fail when elements are missing.

Behaviors:

- Email AJAX forms.
- Button loading states.
- Inline success/error messages.
- TOC smooth scroll and active highlight.
- Scroll progress.
- Copy link share.
- Product lazy loading if enabled.

## 8. Admin Screens

### Blog Create/Edit Enhancements

Add fields:

- Hero image.
- Featured toggle.
- Badge type.
- Custom badge text.
- Canonical URL.
- Schema enabled.
- Shop/vendor spotlight selector.
- Vendor quote.
- Product assignment.

Product assignment:

- Search by name/SKU/category.
- Show thumbnail, price, approval/published status.
- Unsafe products visible as disabled or excluded.
- Support sort order and placement.

### Blog Settings Page

Tabs:

- General.
- Email Capture.
- Product Embeds.
- Sidebar and Layout.
- SEO and Performance.

Use `BusinessSetting`.

Secret fields:

- Mask existing values.
- Blank means preserve.
- New value means encrypt and replace.

### Subscriber Logs

Columns:

- Email.
- Article.
- Placement.
- Provider.
- Provider status.
- Date.
- IP address.

Features:

- Search by email.
- Filter by placement.
- Filter by provider.
- CSV export.

## 9. Frontend UX Target

### `/blog` Listing

Order:

1. Featured editorial hero.
2. Category tabs.
3. Article grid.
4. Inline email card every N articles.
5. Pagination.
6. Optional vendor CTA band.

Card content:

- Image.
- Category.
- Badge.
- Title.
- Excerpt.
- Read time.
- Product count.
- CTA: `Read guide`.

### `/blog/{slug}` Article

Order:

1. Breadcrumb.
2. Category and custom badge.
3. H1.
4. Excerpt.
5. Author/date/read time/product count.
6. Hero image.
7. Scroll progress.
8. Main content and sidebar layout.
9. TOC.
10. Sanitized article body.
11. In-article product embeds.
12. Mid-article email capture.
13. Vendor spotlight.
14. Share bar.
15. Author box.
16. Post-read product grid.
17. Post-read email capture.
18. Related articles.

## 10. Testing Strategy

### Unit Tests

- `BlogSettingsServiceTest`
- `BlogContentSanitizerServiceTest`
- `BlogTocServiceTest`
- `BlogProductMatcherServiceTest`
- `BlogEmailServiceTest`
- `BlogSchemaServiceTest`

### Feature/Integration Tests

- `/blog` loads.
- `/blog/{slug}` loads.
- Existing seeded articles render.
- Existing admin blog CRUD works.
- Article with no products does not break.
- Article with manual products renders safe products.
- Unsafe products are excluded.
- Subscribe route logs locally.
- Honeypot blocks spam.
- Throttle blocks repeated submissions.
- Settings disabled do not break layout.
- TOC handles malformed HTML.
- Schema JSON is valid.
- No duplicate `blog_articles` table exists.
- No `BlogArticle` model exists.
- No WordPress/WooCommerce patterns exist.

### Visual/Manual Checks

- Mobile 320px.
- Tablet 768px.
- Desktop 1024px.
- Wide 1440px.
- No horizontal overflow.
- Sidebar not sticky on mobile.
- Product cards link to valid product URLs.
- Email success/error states are clear.

### Required Commands Before Delivery

```bash
composer guardrails
php artisan app:preflight-restore --require-blog-navigation
php artisan test tests/Integration/Controllers/Frontend/BlogPlatformTest.php
```

Add focused new tests to the command list as they are created.

## 11. Deployment Plan

### Pre-Deployment

1. Confirm clean working tree.
2. Run focused blog tests.
3. Run `composer guardrails`.
4. Run preflight.
5. Confirm migrations are forward-only.
6. Confirm no duplicate blog architecture.

### Deployment

Existing deploy guardrails already:

- Create database backup.
- Run preflight before migrations.
- Run migrations.
- Run `BlogNavigationSeeder`.
- Run post-migration preflight.

New deployment additions if needed:

- Seed default blog settings.
- Clear `business_settings` cache.
- Clear blog settings cache.
- Warm critical blog routes only if safe.

### Rollback

Code rollback:

- Revert deployment commit.
- Clear caches.
- Run smoke checks.

Database rollback:

- Prefer forward fix migration.
- Restore pre-deploy backup only if schema/data failure is severe.

Do not partially roll back code without considering new columns/settings used by views.

## 12. Risk Register

| Risk | Severity | Mitigation |
| --- | --- | --- |
| Unsafe article HTML | Critical | Sanitizer service before frontend redesign |
| Duplicate blog architecture | Critical | Guard tests: no `BlogArticle`, no `blog_articles` |
| Product embeds show unsafe products | High | Product matcher tests and approved/published/stock rules |
| Secret keys exposed | High | Encrypted setting service and masked admin fields |
| Existing blog CRUD breaks | High | Regression tests before each phase |
| SEO duplicates meta/schema | Medium | Reuse/wrap `SeoService`; inspect rendered tags |
| Mobile layout overflow | Medium | Responsive visual checks |
| Email provider failures | Medium | Local logging fallback and safe frontend errors |
| Lazy API slows pages | Medium | Only load when lazy blocks exist; cache responses |
| Admin permissions too broad | Medium | Start with current permissions; add granular permissions later |

## 13. Milestones

### Milestone 1: Safe Data Contracts

Includes:

- Migrations.
- Model relationships.
- Sanitizer.
- Basic tests.

Exit gate:

- Existing blog tests pass.
- No frontend redesign yet.

### Milestone 2: Product Commerce Bridge

Includes:

- Product matcher.
- Product partials.
- Manual products.
- Safe product tests.

Exit gate:

- Manual product article renders.
- Unsafe products excluded.

### Milestone 3: Email Capture

Includes:

- Subscriber logs.
- Subscribe route.
- Local provider.
- Email partials and JS.

Exit gate:

- Local email logging works.
- Honeypot and throttle tests pass.

### Milestone 4: Premium Frontend

Includes:

- Listing hero/tabs/cards.
- Article layout/TOC/sidebar/share.
- CSS/JS.

Exit gate:

- Responsive checks pass.
- `/blog` and `/blog/{slug}` pass.

### Milestone 5: Admin And Analytics

Includes:

- Blog settings.
- Product assignment.
- Subscriber logs/export.

Exit gate:

- Admin CRUD still works.
- Settings toggles disable features cleanly.

### Milestone 6: SEO, Cache, Hardening

Includes:

- Schema.
- Product API if enabled.
- Caching.
- Final tests.

Exit gate:

- Guardrails pass.
- Preflight passes.
- Focused blog suite passes.

## 14. Initial File Plan

### New Files

- `app/Models/BlogSubscriberLog.php`
- `app/Services/Blog/BlogSettingsService.php`
- `app/Services/Blog/BlogContentSanitizerService.php`
- `app/Services/Blog/BlogTocService.php`
- `app/Services/Blog/BlogProductMatcherService.php`
- `app/Services/Blog/BlogEmailService.php`
- `app/Services/Blog/BlogSchemaService.php`
- `app/Http/Requests/BlogSubscribeRequest.php`
- `app/Http/Requests/BlogSettingsRequest.php`
- `database/migrations/*_extend_blogs_for_conversion.php`
- `database/migrations/*_create_blog_product_table.php`
- `database/migrations/*_create_blog_subscriber_logs_table.php`
- `resources/views/frontend/blog/partials/*.blade.php`
- `public/assets/css/blog-conversion.css`
- `public/assets/js/blog-conversion.js`
- Focused tests under `tests/Feature` and `tests/Integration`

### Modified Files

- `app/Models/Blog.php`
- `app/Models/Product.php` only if inverse relation is needed
- `app/Http/Controllers/BlogController.php`
- `routes/web.php`
- `routes/admin.php`
- `routes/api.php` only if lazy API is enabled
- `resources/views/frontend/blog/listing.blade.php`
- `resources/views/frontend/blog/details.blade.php`
- `resources/views/backend/blog_system/blog/create.blade.php`
- `resources/views/backend/blog_system/blog/edit.blade.php`
- `resources/views/backend/inc/admin_nav.blade.php` only if adding settings/subscribers menu entries

## 15. Acceptance Criteria

The feature is complete only when:

1. `/blog` works.
2. `/blog/{slug}` works.
3. Existing admin blog CRUD works.
4. Existing translations remain compatible.
5. Existing homepage blog cards still work.
6. No `BlogArticle` model exists.
7. No `blog_articles` table exists.
8. Product embeds show only safe products.
9. Email submissions log locally.
10. Settings can disable all conversion modules without breaking pages.
11. Article HTML is sanitized.
12. Blog pages are responsive with no horizontal overflow.
13. SEO/meta/schema are not duplicated.
14. Focused tests pass.
15. `composer guardrails` passes.
16. `php artisan app:preflight-restore --require-blog-navigation` passes.

## 16. Recommended Next Action

Start with Milestone 1 only.

Do not redesign frontend first. The safest next implementation task is:

1. Add migrations.
2. Add `BlogSubscriberLog`.
3. Add sanitizer service.
4. Add `Blog` relationships/accessors.
5. Add product matcher skeleton.
6. Add tests proving current blog routes still work and duplicate architecture does not exist.

