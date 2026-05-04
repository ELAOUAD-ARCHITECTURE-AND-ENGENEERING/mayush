# Blog Upgrade Conversion Roadmap - Refactored Report

Date: 2026-05-04
Project: Mayush Design Marketplace
Subject: Refactor of blog upgrade roadmap after reviewing the latest external conversion audit

## Executive Summary

The external audit is strategically correct about the largest business gap: Mayush blog readers currently have weak paths from inspiration content to marketplace shopping, vendor discovery, and email capture.

However, the audit also describes a few features as if they already exist when the current Blade views do not fully support them. The current blog has useful foundations: article listing, article detail page, author/date/category row, SEO meta sections, JSON-LD through `SeoService`, related posts, recent-post sidebar, category checkbox filters, multilingual content, and homepage blog cards. It does not yet have a true editorial-commerce conversion system.

Refactored direction: make the blog upgrade report conversion-first, but implementation-safe:

1. Build the product-to-article bridge first.
2. Add email capture second.
3. Improve the listing and article UX third.
4. Add vendor acquisition CTAs where relevant.
5. Keep all work as extensions of the existing `Blog` system.

## Current-State Correction

| External Audit Claim | Verified Current State | Correction |
| --- | --- | --- |
| Article pages have a table of contents | No TOC partial/service or heading-link system is present in current blog Blade | TOC is a target feature, not current functionality |
| Article pages have related posts | Confirmed in `details.blade.php` | Keep and improve |
| Article pages have author note | Current page has author/date row, but no full author box/note | Add author box if author data is available |
| Article pages have sidebar with recent articles | Confirmed | Sidebar exists but is thin and not conversion-oriented |
| Blog has no email capture | Confirmed | High-priority gap |
| Blog has no product CTAs | Confirmed | Highest-priority commerce gap |
| Listing category filtering does not work visually | Partially true: category checkbox filtering exists in sidebar, but not premium visual category tabs | Upgrade to visible category tabs while preserving server-side filtering/pagination |
| No hero/featured post slot | Confirmed on listing view | Add featured editorial hero |
| Zero monetization bridge | Mostly true | Product embeds, vendor CTA, and email capture are missing |
| WooCommerce/WordPress shortcodes may be an option | Not applicable | This project is Laravel; remove WordPress/WooCommerce advice entirely |

## Conversion Diagnosis

### Biggest Gap: No Product-To-Blog Bridge

Mayush publishes buying-intent topics such as bedroom lighting, small-space layouts, Moroccan materials, and interior style guidance. These readers are closer to purchase than generic blog traffic, but current article pages do not connect them to products.

Impact:

- Readers consume advice and leave.
- Product discovery depends on separate marketplace navigation.
- Blog SEO traffic does not compound into product clicks.
- Vendors do not benefit directly from editorial content.

Highest-ROI fix:

- Add contextual product cards after relevant article sections.
- Add sidebar "Shop this guide" products.
- Add post-read product grids.
- Use approved/published/in-stock products only.

### Second Biggest Gap: No Email Capture

Current blog pages do not capture email leads. For an interior design marketplace, blog email capture should offer a concrete design asset rather than a generic newsletter.

Recommended lead magnet:

- "Moroccan Interior Design Lookbook"
- "Small Space Styling Checklist"
- "Bedroom Lighting Planning Guide"
- "Monthly Mayush Design Edit"

Best placements:

- Mid-article after reader engagement.
- Post-read after value delivery.
- Sidebar on desktop.
- Inline listing card every configured N articles.

### Third Gap: Listing Page Is Functional, Not Editorial

The listing page has article cards, search, category filters, pagination, and recent posts. It does not yet feel like a premium editorial homepage.

Upgrade priorities:

- Featured hero article.
- Category tabs with server-side query filtering.
- Premium article cards.
- Product count/badge indicators.
- Inline lead capture.
- Cleaner mobile-first layout.

### Fourth Gap: Sidebar Is Underused

The current article sidebar mostly lists recent posts. It should become a conversion sidebar:

1. Email capture.
2. Products from the current article.
3. Table of contents.
4. Related/recent articles.
5. Vendor CTA.

Use sticky behavior only on desktop and avoid horizontal overflow.

### Fifth Gap: Vendor Acquisition Is Missing

Mayush is a marketplace. Interior design blog readers may include artisans, decorators, furniture makers, lighting shops, and design studios.

Add a vendor CTA in relevant places:

- Sidebar widget.
- Post-read module.
- Blog listing footer band.

CTA examples:

- "Sell your designs on Mayush"
- "Join Morocco's interior design marketplace"
- "Reach customers looking for furniture, decor, and design inspiration"

## Refactored Upgrade Roadmap

### Phase 0: Safety Decisions

Before coding, document:

| Decision | Recommended Answer |
| --- | --- |
| Article model | Use existing `App\Models\Blog` |
| Product pivot | `blog_product` with `blog_id`, `product_id`, `placement`, `sort_order` |
| Pivot uniqueness | Unique `blog_id + product_id + placement`; normalize empty placement to `manual` |
| Settings | Use `BusinessSetting` through `BlogSettingsService` |
| Sensitive settings | Encrypt with Laravel `Crypt`, mask in admin |
| Subscriber route | Prefer `POST /blog/subscribe` with CSRF, throttle, honeypot |
| Product API | Use only if lazy loading is enabled; follow current API convention |
| HTML rendering | Sanitize rich article HTML before rendering |
| SEO schema | Reuse or wrap existing `SeoService` |

### Phase 1: Foundation For Conversion

Goal: add data contracts without redesigning pages yet.

Deliverables:

- Migration to extend `blogs` with:
  - `hero_image`
  - `badge_type`
  - `custom_badge_text`
  - `read_time_minutes`
  - `is_featured`
  - `canonical_url`
  - `schema_enabled`
  - `shop_id`
  - `vendor_quote`
- Migration for `blog_product`.
- Migration for `blog_subscriber_logs`.
- `Blog` relationships:
  - `products()`
  - `shop()`
  - `scopeFeatured()`
  - `scopeByCategory()`
  - accessors for read time, product count, public URL, hero image.
- Optional inverse `Product::blogs()`.

Acceptance:

- Existing `/blog` works.
- Existing `/blog/{slug}` works.
- Existing admin blog CRUD works.
- Existing homepage blog section works.
- No duplicate `BlogArticle` or `blog_articles` exists.

### Phase 2: Product Bridge

Goal: connect articles to marketplace products.

Deliverables:

- `BlogProductMatcherService`.
- Product assignment relation.
- Product card partial.
- Product embed partial.
- Safe product query:
  - approved
  - published
  - visible
  - purchasable/in stock under Mayush rules
- Manual products first, fallback products second.

Recommended display placements:

- After relevant H2 sections.
- Sidebar "Shop this guide."
- Post-read "Complete the look."

Acceptance:

- Article with no products does not break.
- Article with manual products shows only safe products.
- Unapproved/unpublished/out-of-stock products are excluded.

### Phase 3: Email Capture

Goal: turn anonymous readers into owned leads.

Deliverables:

- `BlogEmailService`.
- `BlogSubscribeRequest`.
- `BlogSubscriberLog` model.
- `POST /blog/subscribe`.
- Email partials:
  - listing inline
  - mid-article
  - sidebar
  - post-read
- Local-only provider first.
- Mailchimp/Klaviyo/custom webhook behind settings.
- Honeypot, throttle, CSRF.

Recommended CTA:

- Lead magnet first, newsletter second.

Example copy:

```text
Get the Moroccan Interior Design Lookbook
Room ideas, material palettes, lighting tips, and shoppable Mayush finds in your inbox.
```

Acceptance:

- Valid email logs locally.
- Honeypot blocks spam.
- Provider failures do not expose raw errors.
- Button states and inline success/error work.

### Phase 4: Listing Page Editorial Upgrade

Goal: make `/blog` feel like a premium editorial entry point.

Deliverables:

- Featured hero article.
- Visual category tabs.
- Premium article cards.
- Product count badge.
- Badge label.
- Read time.
- Inline email capture card.
- Pagination preserved.

Important:

- Keep server-side filtering.
- Do not break current category checkbox behavior until replacement is tested.

Acceptance:

- `/blog` loads.
- Category filtering works with pagination.
- Mobile layout has no horizontal overflow.

### Phase 5: Article Page Editorial-Commerce Upgrade

Goal: convert article readers into product clickers, email subscribers, and vendor leads.

Deliverables:

- Breadcrumb.
- Article badge row.
- Read time and product count.
- Sanitized article body.
- TOC generated from H2/H3.
- Scroll progress.
- In-article product embeds.
- Mid-article email card.
- Vendor spotlight.
- Sticky desktop sidebar.
- Share bar.
- Author box.
- Post-read email and products.
- Related articles retained.

Acceptance:

- Malformed HTML does not break page.
- TOC fails gracefully if parsing unavailable.
- Sidebar disables cleanly on mobile.
- Related articles still render.

### Phase 6: Admin Controls

Goal: let admins control conversion features without code edits.

Deliverables:

- Existing blog create/edit enhancements:
  - hero image
  - featured toggle
  - badge type/custom text
  - canonical URL
  - schema toggle
  - shop selector
  - vendor quote
  - product assignment
- Blog settings page using `BusinessSetting`.
- Subscriber analytics page.
- CSV export with permission protection.

Acceptance:

- Existing blog CRUD still works.
- Product assignment does not allow unsafe products as selectable live embeds.
- Secret keys are masked and encrypted.

## Target Article Layout

Recommended order:

1. Breadcrumb.
2. Category and badge.
3. H1 title.
4. Excerpt.
5. Author/date/read time/product count row.
6. Hero image.
7. Scroll progress.
8. Main layout:
   - left/main: TOC on mobile, article content, product embeds, email capture.
   - right/sidebar: email card, "Shop this guide", TOC, recent/related posts, vendor CTA.
9. Author box.
10. Post-read product grid.
11. Post-read email capture.
12. Related articles.

## Target Listing Layout

Recommended order:

1. Editorial hero with featured post.
2. Category tabs.
3. Article grid.
4. Inline email card every configured N articles.
5. Pagination.
6. Optional vendor CTA/footer band.

## Strategic Priority Ranking

| Priority | Upgrade | Reason |
| --- | --- | --- |
| P0 | Sanitized article rendering | Security gate before richer article rendering |
| P0 | Product relation/data model | Required for commerce bridge |
| P1 | Product embeds | Highest direct revenue potential |
| P1 | Email capture | Builds owned audience |
| P1 | Product-aware article page | Converts buying-intent readers |
| P2 | Listing hero/category tabs | Improves browsing and premium feel |
| P2 | Sticky sidebar | Keeps conversion modules visible |
| P2 | Vendor CTA | Marketplace supply acquisition |
| P3 | Lazy product API | Performance enhancement after product embeds work |
| P3 | External email providers | After local logging and UX are stable |

## What To Remove From The External Audit

Remove this guidance:

```text
If your blog runs on a WooCommerce/WordPress plugin...
```

Reason:

Mayush is Laravel 10. WordPress/WooCommerce guidance is irrelevant and dangerous for this codebase.

Replace with:

```text
Because Mayush uses Laravel, product embeds must use Eloquent relationships, existing product helpers, Blade partials, and optional Laravel API endpoints.
```

## Updated Implementation Prompt Addendum

Add this addendum to the optimized prompt:

```text
Use the external conversion audit as strategic input only. Verify all current-state claims against the codebase before implementation. In this Mayush codebase, TOC, product embeds, email capture, sticky conversion sidebar, vendor CTA, and premium listing hero are target features, not confirmed existing features.

Prioritize the conversion bridge in this order:
1. Safe article rendering.
2. Blog-product relationship and product matcher.
3. In-article and sidebar product cards.
4. Local email capture and subscriber logs.
5. Listing hero/category tabs.
6. Vendor CTA and sticky sidebar.

Do not include WordPress/WooCommerce logic. Implement through Laravel models, services, controllers, routes, Blade partials, BusinessSetting, and existing Mayush helpers.
```

## Final Recommendation

Proceed with implementation only in small phases. The first production-safe implementation sprint should be:

1. Sanitized article rendering.
2. Blog conversion schema migrations.
3. `Blog` model relationships/accessors.
4. `BlogProductMatcherService`.
5. Product embed tests.
6. Local email capture schema and logging tests.

Do not redesign the entire frontend before these contracts are stable.

