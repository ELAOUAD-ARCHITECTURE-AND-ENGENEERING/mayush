# MAYUSH Mobile Agile Methodology Plan

**Status:** Analysis and planning only  
**Date:** 2026-07-15  
**Scope:** Android and iOS mobile application, first mobile product phase  
**Backend principle:** MAYUSH Laravel remains the authoritative marketplace platform

## 1. Executive summary

MAYUSH is not a greenfield commerce backend. The repository already contains a multi-vendor marketplace with customer, seller, admin/staff, delivery, affiliate, content, payment, shipping, loyalty, analytics, AI configuration, semantic search, and operational capabilities.

The mobile application should therefore be delivered as a new mobile client and experience layer over existing MAYUSH business rules. The mobile client must not independently calculate prices, discounts, tax, stock, shipping, payment totals, commissions, loyalty balances, refund eligibility, or vendor ownership.

The first mobile release should be a coherent customer discovery product: guest browsing, customer authentication, mobile home, categories, brands, product search, product detail, and curated inspiration. Cart, payment, seller mutations, delivery operations, administrative actions, and generative AI should enter only after their API contracts, authorization, test coverage, and release gates are satisfied.

The plan classifies work as:

- **Reuse directly:** existing capability is evidenced and suitable for client consumption.
- **Reuse after validation:** capability exists, but behavior or coverage needs verification.
- **Extend for mobile:** backend capability exists but needs a mobile contract, adapter, aggregation, authorization, or observability work.
- **Build as new functionality:** no complete equivalent was found.
- **Defer:** valid capability, but not safe or necessary for the first release.

## 2. Repository state and evidence method

The required `git status --short` check was run before inspection. The repository was already dirty:

```text
 M .agent/skills/ui-ux-pro-max/scripts/__pycache__/core.cpython-312.pyc
 M .agent/skills/ui-ux-pro-max/scripts/__pycache__/design_system.cpython-312.pyc
 M .phpunit.cache/test-results
 M app/Console/Kernel.php
 M resources/views/frontend/product_details/details.blade.php
?? app/Console/Commands/ExpireGuestSupportChats.php
?? app/Http/Controllers/Support/
?? app/Models/SupportConversation.php
?? app/Models/SupportMessage.php
?? database/migrations/2026_07_15_135614_create_support_conversations_table.php
?? database/migrations/2026_07_15_135622_create_support_messages_table.php
?? reports/mayush_completed_tasks_recap_2026-04-01_to_2026-07-11.md
```

`docs/mobile/` did not exist before this plan was created.

During final verification, additional unrelated worktree entries were visible for `resources/views/frontend/layouts/app.blade.php`, `routes/web.php`, and `resources/views/frontend/inc/live_chat_widget.blade.php`. They were not edited, staged, cleaned, or attributed to this planning task. The initial status above remains the required pre-task baseline.

Safe read-only discovery performed:

- `git status --short`.
- `php artisan route:list --path=api --except-vendor` — reported 356 API routes.
- `rg` searches across routes, controllers, middleware, models, policies, services, migrations, tests, and documentation.
- Read-only file inspection with line-numbered output.
- No migration, seeding, database write, dependency change, package installation, commit, branch, push, or application-source edit was performed.

All material claims below are tied to current repository evidence. Existing reports are used as context, not as proof.

## 3. Scope and non-scope

### In scope for this planning document

- Android and iOS requirements.
- Mobile architecture decision process.
- Customer, vendor, interior designer, admin, and other actor analysis.
- Authentication and account-management readiness.
- Home-screen readiness.
- Catalog and search readiness.
- Recommendation maturity.
- Curated inspiration and generative AI separation.
- API and security gaps.
- Mobile architecture decisions.
- Dependency-driven Agile initiatives, epics, capabilities, stories, QA, security, and documentation work.
- Initial release boundary and later actor waves.

### Out of scope for this phase

- Implementing the mobile application.
- Changing Laravel routes, controllers, models, services, policies, middleware, migrations, tests, configuration, or environment files.
- Fixing existing backend defects.
- Selecting a mobile framework without the decision process defined below.
- Replacing the existing web marketplace.
- Rebuilding checkout or payment business rules in the mobile client.

## 4. Verified MAYUSH platform baseline

### 4.1 Backend and API foundation

The application uses Laravel 10/PHP 8.2, Laravel Sanctum, Socialite, Apple sign-in packages, SMS integrations, and a large versioned API surface. Sanctum and API tokens are used by `User`, while the API is grouped under `/api/v2` and uses `auth:sanctum` for protected capabilities.

Evidence:

- `composer.json:14-26` — Apple sign-in, Socialite, Sanctum, SMS, and core Laravel packages.
- `app/Models/User.php:8-18` — `HasApiTokens` and `HasRoles`.
- `routes/api.php:16-35` — versioned authentication routes.
- `routes/api.php:119-246` — protected customer, order, address, wallet, notification, and last-viewed routes.
- `php artisan route:list --path=api --except-vendor` — 356 API routes currently registered.

### 4.2 Guest and public visitor

Verified or partially verified public capabilities include:

- Product, category, brand, seller, shop, banner, slider, collection, flash-deal, blog, policy, language, currency, and search discovery.
- Guest cart support and guest-account conversion.
- Public inspiration through blog/editorial content and product collections.

Evidence:

- `routes/api.php:78-116` — cart, coupon, shipping, carrier, and payment-type routes.
- `routes/api.php:256-345` — search suggestions, banners, brands, categories, product catalog, reviews, shops, and sliders.
- `routes/api.php:406-428` — blog, flash deals, settings, social-login configuration, and pickup-list routes.
- `app/Http/Controllers/Api/V2/AuthController.php:328-386` — guest-user account creation and temporary-cart conversion.

### 4.3 Customer/buyer

The customer actor is represented by `users.user_type = customer`, with customer relationships for wishlists, products, orders, wallets, loyalty/club points, packages, carts, reviews, addresses, bids, queries, uploads, and blogs.

Evidence:

- `app/Models/User.php:24-31` — user fields including `user_type`, verification fields, provider identity, avatar, and ban status.
- `app/Models/User.php:55-176` — customer, shop, order, wallet, points, package, cart, review, address, product-query, upload, and blog relationships.
- `app/Http/Controllers/Api/V2/AuthController.php:170-195` — customer, seller, and delivery-boy login selection.

Existing customer-facing API capability includes:

- Authentication, profile, addresses, wishlist, follow seller, reviews, notifications, chat, purchase history, reorder, invoice, refunds, wallet, club points, customer packages, classified products, coupons, shipping, cart, payment, order, digital products, auction, and search/catalog.

Evidence:

- `routes/api.php:127-175` — chat, purchase history, classified products, promotions, customer information, reviews, shops, and followed sellers.
- `routes/api.php:178-245` — wishlist, addresses, club points, refunds, payments, orders, profile, wallet, notifications, and last-viewed products.
- `routes/api.php:78-116` — cart, coupons, shipping, carriers, and payment types.
- `routes/api.php:299-320` — product catalog, product search, variant pricing, digital products, and product details.

### 4.4 Seller/vendor/shop owner

The seller actor is represented through the user model, seller middleware, shop model, approval status, seller API, and seller web routes.

Evidence:

- `app/Http/Middleware/IsSeller.php:17-27` — authenticated seller and non-banned checks.
- `app/Http/Middleware/SellerApproved.php:8-40` — approved-shop requirement for protected seller management.
- `routes/api_seller.php:7-165` — seller API surface.
- `routes/seller.php` — seller web workspace and seller onboarding routes.
- `app/Models/Shop.php:8-71` — shop, approval status, seller package, followers, documents, reviewer, and Elite subscription relationships.

Existing seller capabilities include:

- Seller onboarding and verification.
- Shop profile and branding.
- Product CRUD, attributes, categories, brands, taxes, colors, variants, stock, reviews, queries, digital products, wholesale, auction, coupons, conversations, seller packages, files, POS, orders, refunds, withdrawals, and dashboard analytics.

Evidence:

- `routes/api_seller.php:10-35` — seller orders, payment history, commissions, dashboard, shop update, and verification.
- `routes/api_seller.php:39-77` — refunds, withdrawals, product CRUD, product metadata, reviews, and queries.
- `routes/api_seller.php:81-140` — digital, wholesale, auction, coupons, conversations, packages, and file management.
- `routes/api_seller.php:148-159` — seller POS operations.
- `docs/seller-features-functionality-report.md:95-319` — documented seller feature surface, used here only after route/controller confirmation.

Seller mobile mutations require a separate security validation wave. Existing security tests cover many seller ownership boundaries, but the mobile plan must not assume every seller API controller is safe solely because the route is behind Sanctum.

Evidence:

- `tests/Feature/Security/AuthorizationPolicyTest.php:74-147` — seller product/order ownership and customer order isolation tests.
- `tests/Feature/ProductionReadiness/SellerIsolationTest.php:1-136` — seller isolation test class.
- `tests/Feature/Security/SellerDestructiveRouteSecurityTest.php:1-110` — seller destructive-route security tests.
- `docs/seller-features-functionality-report.md:397-407` — documented remaining seller authorization recommendations.

### 4.5 Admin, super admin, staff

Admin and staff are admitted by `IsAdmin`, which accepts `user_type = admin` or `user_type = staff`. The admin route surface includes dashboards, analytics, security, categories, brands, products, sellers, customers, roles, reviews, support, conversations, loyalty, affiliates, and other platform operations.

Evidence:

- `app/Http/Middleware/IsAdmin.php:17-26` — admin/staff gate.
- `routes/admin.php:95-143` — admin dashboard, health, technical analytics, security, and task dashboard.
- `routes/admin.php:146-213` — category, brand, product, import/export, product approval, and AI-product-generation routes.
- `routes/admin.php:256-347` — seller, customer, payment, withdrawals, profile, and business settings.
- `routes/admin.php:591-705` — reviews, support, conversations, attributes, loyalty, classified products, promotions, and affiliates.

There is no evidence that a complete native admin mobile workspace is required for the first customer release. Admin remains an existing web workspace with a later option for limited operational alerts/actions.

### 4.6 Delivery operator

Delivery operators are an existing actor and API surface, not a new role invented for mobile.

Evidence:

- `app/Http/Controllers/Api/V2/AuthController.php:170-180` — delivery-boy login selection.
- `routes/api.php:59-74` — delivery assignments, delivery states, earnings, collections, summaries, and order details.

### 4.7 Affiliate, content, staff, and system integration actors

Other platform actors include:

- Affiliate users and administrators.
- Blog authors and blog administrators.
- Staff users with admin/staff access rules.
- Payment gateway callbacks.
- Shipping webhooks and carrier integrations.
- SMS, email, Firebase notification, queue, AI, and embedding services.

Evidence:

- `routes/admin.php:701-705` — affiliate administration.
- `routes/api.php:406-409` — blog API.
- `routes/api.php:431-466` — tracking webhook and payment callback surfaces.
- `app/Notifications/OrderNotification.php:11-67` — queued notification and database notification channel.
- `app/Utility/SemanticUtility.php:13-50` — Gemini embedding integration and fallback behavior.

### 4.8 Interior Designer

The platform clearly serves interior-design users through its marketplace positioning, editorial content, collections, seller stories, and product discovery. However, no dedicated Interior Designer user type, middleware, policy, API namespace, professional profile, project, moodboard, or designer onboarding workflow was found in the inspected repository.

Status: **Not Found as a dedicated actor; Requires New Development if the role is approved.**

Evidence:

- `app/Models/User.php:24-31` — current user fields do not define an `interior_designer` type.
- `app/Http/Middleware/IsSeller.php:17-27` and `app/Http/Middleware/IsAdmin.php:17-26` — existing role gates cover seller/admin/staff, not designer.
- `routes/api.php` and `routes/api_seller.php` — no dedicated designer route group found during repository search.
- `tests/` search — no dedicated designer workflow test found.

This does not mean interior designers are absent as customers. It means a dedicated professional actor must not be claimed as already implemented.

## 5. Role and permission matrix

| Actor | Current representation | Authentication/middleware | Current workspace | Mobile decision | Status |
|---|---|---|---|---|---|
| Guest | No persisted identity; temporary cart/user support | Public routes; guest account conversion | Public storefront | Include in initial release for browsing | Verified |
| Customer | `users.user_type = customer`; customer relationships | Sanctum for protected API; `app_user_unbanned` on protected customer group | Customer web/API | Initial mobile actor | Partially Verified for mobile |
| Vendor | `users.user_type = seller`; `Shop`; seller package/approval | Sanctum, seller API, `IsSeller`, `SellerApproved` | Seller web/API | Seller mobile wave after customer MVP | Exists but Not Mobile-Ready |
| Interior Designer | No dedicated representation found | No dedicated middleware/API found | No dedicated workspace | Define role and professional product separately | Not Found |
| Admin | `users.user_type = admin` | `auth`, `admin`, `IsAdmin` | Admin web workspace | Limited alerts/actions later; no full initial mobile admin | Verified for web; mobile deferred |
| Staff | `users.user_type = staff` | `IsAdmin` accepts staff | Admin/staff routes | Define exact mobile permissions before exposure | Partially Verified |
| Delivery operator | `user_type = delivery_boy` | Sanctum API routes | Delivery API | Separate operational mobile wave | Partially Verified |
| Affiliate | Affiliate models/routes | Customer/admin flows vary by route | Affiliate web/admin | Defer from first release | Partially Verified |
| Content editor/author | Blog roles and admin routes | Admin/role permissions | Blog CMS | No first-release mobile workspace | Partially Verified |
| System integration | Gateway, shipping, AI, notification, queues | Provider credentials/webhooks/system controls | Non-human actor | Mobile consumes controlled results only | Verified as integration surface |

Defining a role is separate from building its mobile workspace. The first release requires the role matrix to be explicit, but only the guest/customer experience needs full initial implementation.

## 6. Mobile-readiness assessment

### 6.1 Native application

No current Flutter, React Native, Android, iOS, Capacitor, OpenAPI client, or mobile application source directory was found in repository discovery.

Status: **Requires New Development.**

Framework choice is therefore a decision, not a known repository constraint. Sprint 0 must compare Flutter, React Native, native Android/iOS, and any existing team standard before selection.

### 6.2 API documentation

Machine-readable API discovery exists, but the published OpenAPI is intentionally narrow:

- `app/Http/Controllers/AgentDiscoveryController.php:40-166` — OpenAPI document contains only `/promotions` and `/promotions/{id}`.
- `app/Http/Controllers/AgentDiscoveryController.php:164-166` — API docs page states that only the documented authenticated promotions surface is advertised.

Status: **Requires API Standardization.**

The mobile program must produce a complete mobile contract for the selected scope. It must not declare the full 356-route platform API documented merely because `/openapi.json` exists.

### 6.3 Authentication

| Capability | Evidence | Status | Required mobile work |
|---|---|---|---|
| Email/phone registration | `AuthController.php:26-96` | Partially Verified | Define mobile response/error contract and verification UX |
| Email/password login | `AuthController.php:142-222` | Partially Verified | Validate customer/seller/delivery role selection and mobile errors |
| OTP verification | `routes/otp.php:20-33`; `AuthController.php:99-138` | Partially Verified | Verify true OTP login flow, expiry, attempts, resend limits, and API contract |
| Password reset | `routes/api.php:29-35` | Exists but Not Mobile-Ready | Add contract tests and mobile recovery states |
| Google/Facebook/Apple | `AuthController.php:241-325`; `composer.json:14-26` | Requires Security Review | Validate native token/code handling, provider identity mapping, replay resistance, and provider-specific tests |
| Logout/revocation | `AuthController.php:229-238` | Partially Verified | Confirm all mobile tokens are revoked and define expiration policy |
| Account deletion | `AuthController.php:454-489`; `routes/api.php:24-28` | Partially Verified | Confirm mobile confirmation, retention, token revocation, and legal behavior |
| Profile/image | `routes/api.php:214-223` | Partially Verified | Normalize upload response and mobile image constraints |
| Address management | `routes/api.php:186-194` | Partially Verified | Add contract/ownership tests and safe form validation |
| Device token | `routes/api.php:216-219` | Partially Verified | Define device/platform lifecycle and token ownership |

Important social-auth finding: the API does call provider APIs through `userFromToken`/`userFromTokenAndSecret`, which is stronger than trusting a client-submitted profile, but the flow requires review before native release. In particular, `AuthController.php:243-281` uses both `provider` and `social_provider`, while `AuthController.php:288-306` looks up by `$request->provider` but stores `$social_user_details->id`. This must be covered by provider-specific contract and security tests.

### 6.4 Saved payment methods

The payment vault exists and is protected in the web flow:

- `app/Models/PaymentToken.php:14-35` — token and last-four fields are encrypted/hidden at model level.
- `app/Models/PaymentToken.php:50-104` — active, default, non-expired, and pruning behavior.
- `app/Http/Controllers/PaymentTokenController.php:14-60` — user-scoped list/default/deactivate operations.
- `routes/web.php:434-439` — current payment-method routes are web routes.
- `tests/Feature/Payment/PaymentVaultRegressionTest.php:19-104` — default-address/token eligibility, deduplication, own-token default/delete, and callback behavior.

Status: **Exists but Not Mobile-Ready.**

Required mobile extension:

- Mobile-safe list/default/deactivate endpoints or a formally approved equivalent.
- No full PAN, CVV, raw gateway credential, or reusable secret in mobile responses.
- Ownership tests.
- Expiry and inactive-token behavior.
- Idempotent default/deactivate operations.
- Payment-provider-specific mobile return/deep-link contracts.

### 6.5 Home screen

| Section | Current source/evidence | Ranking/behavior | Status |
|---|---|---|---|
| Dynamic banners/sliders | `routes/api.php:269-273`, `342-345`; `StorefrontDataService.php:27-32`, `166-179` | Admin-configured content; cached storefront data | Reuse after validation |
| Featured collections | `ProductCollection.php:8-62`; migration `2026_06_01_000001_create_product_collections_tables.php:11-32`; `ProductCollectionService.php:13-143` | Manual/dynamic collection rules, published windows | Reuse after validation |
| Best sellers | `ProductController.php:243-247`; `StorefrontDataService.php:102-121` | `num_of_sale` descending | Reuse directly after contract check |
| Flash deals | `routes/api.php:415-418`; `ProductController.php:217-222` | Active dates/status/featured rules | Reuse after validation |
| Today’s deals | `ProductController.php:211-215`; `StorefrontDataService.php:128-142` | `todays_deal` flag | Reuse directly after contract check |
| New arrivals | `ProductController.php:256-260`; `StorefrontDataService.php:144-152` | Newest ordering; seller-specific endpoint also exists | Extend naming/contract for global mobile use |
| Trending/popular | `ProductController.php:243-247`; product view/sale fields | Best-seller/popularity signals, not a separate personalized ranker | Requires definition and API standardization |
| Recommendations | `ProductController.php:249-254`; `FrequentlyBoughtProduct.php:9-26`; `RecommendationEngineTest.php:15-53` | Frequently bought together; tested co-purchase threshold | Rule-based, not personalized home recommendations |
| Recently viewed | `routes/api.php:245`; `LastViewedProduct.php:9-19`; `ProductCollectionService.php:81-96` | Authenticated user product history | Reuse after API contract check |
| Categories | `routes/api.php:277-292`; `StorefrontDataService.php:185-213` | Admin/configured and category hierarchy | Reuse directly after payload check |
| Brands | `routes/api.php:271-273`; `ProductController.php:204-209` | Top/all brand listings | Reuse directly after payload check |
| Sellers/shops | `routes/api.php:267`, `334-340` | Top sellers and shop/product listings | Reuse after public-data review |
| Curated inspiration | `HomepageRenderingTest.php:280-328`; blog routes `api.php:406-409` | Latest/admin-selected editorial content | Reuse after mobile content contract |
| AI inspiration | `AiPrompt.php:9-18`, `AiUsageLog.php:9-25`, `SemanticUtility.php:13-166` | AI/admin/semantic foundations only | New mobile product workflow |

No home section may be labelled “personalized” until user-specific signals, ranking behavior, privacy rules, and tests are demonstrated.

### 6.6 Catalog and search

The catalog API already supports categories, subcategories, brands, filters, search, sort keys, pagination, product variants, digital products, and product detail.

Evidence:

- `routes/api.php:296-320` — filters, category/brand/product endpoints, search, variant price, and product detail.
- `ProductController.php:181-201` — category and brand listing with `paginate(10)`.
- `ProductController.php:264-367` — category/brand filters, price range, multi-word matching, search storage, sort keys, and `paginate(10)`.
- `tests/Feature/Frontend/SearchFilterRegressionTest.php:16-153` — multi-word search and autocomplete regression coverage.
- `tests/Feature/Security/RateLimitingTest.php:122-135` — search rate limiting.

Current sort keys include price ascending, price descending, new arrival, popularity, and top rated (`ProductController.php:340-366`). The mobile plan must still validate cross-endpoint consistency, stable pagination, duplicate avoidance, locale/currency payloads, image derivatives, and error contracts.

Status: **Exists but Not Mobile-Ready; Requires API Standardization.**

## 7. Recommendation maturity assessment

### Level 1 — General merchandising: verified

MAYUSH has featured products, best sellers, today’s deals, flash deals, product collections, top sellers, and newest products.

Evidence:

- `routes/api.php:267-345`, `415-418`.
- `StorefrontDataService.php:67-152`.
- `ProductCollectionService.php:13-143`.

### Level 2 — Rule-based behavioral recommendations: partially verified

Available signals include:

- Product views: `ProductView.php:8-30` and migration `2026_03_31_130711_create_product_views_table.php:15-22`.
- Last viewed products: `LastViewedProduct.php:9-19` and `routes/api.php:245`.
- Frequently bought associations with source and affinity score: `FrequentlyBoughtProduct.php:9-26` and migration `2026_03_31_172315_add_source_to_frequently_bought_products_table.php:14-20`.
- Product collections that can show best-selling and recently-viewed sections: `ProductCollection.php:24-38`.
- Semantic product similarity: `SemanticUtility.php:129-166`.

The co-purchase engine is tested, but it is not proof of per-user personalization:

- `tests/Feature/RecommendationEngineTest.php:15-53` verifies products bought together at a threshold.

Status: **Partially Verified; Reuse after validation.**

### Level 3 — Advanced recommendation engine: not found

No evidence was found for collaborative filtering, similar-user modeling, continuous recommendation evaluation, user-level ranking service, recommendation impressions, recommendation click attribution, or online ranking updates.

Status: **Requires New Development.**

The mobile roadmap must therefore launch with Level 1 and selected Level 2 fallbacks. Level 3 is a separate product/analytics initiative.

## 8. AI Design Inspiration assessment

### 8.1 Curated inspiration discovery

Existing curated capability includes:

- Blog and editorial content.
- Product recommendation blocks in blog content.
- Product collections.
- Seller storytelling and shop content.
- Homepage inspiration section tests.

Evidence:

- `tests/Feature/Frontend/HomepageRenderingTest.php:280-328` — latest and admin-selected inspiration articles.
- `app/Services/Blog/BlogContentBlockService.php:130-171` — product recommendation content block.
- `app/Models/ProductCollection.php:8-62` — collection metadata and published windows.
- `app/Services/ProductCollectionService.php:13-143` — collection query, filters, sorting, best selling, and recently viewed behavior.

Status: **Reuse after validation; mobile content contract required.**

### 8.2 Generative AI workflow

Existing foundations include:

- Gemini configuration: `config/services.php:71-73`.
- Prompt model: `app/Models/AiPrompt.php:9-18`.
- Usage logging: `app/Models/AiUsageLog.php:9-25`.
- Admin AI prompt templates: `routes/admin.php:402-405`.
- Product semantic embeddings and search: `app/Utility/SemanticUtility.php:13-166`.
- AI-related tests: `tests/Feature/SemanticSearchTest.php:14-197` and `tests/Feature/V109/AiAndFacebookGuardTest.php:15-...`.

No complete customer-facing room-input, style-input, budget-input, generated-moodboard, save/share/history, product-availability validation, moderation, or designer-workflow API was found.

Status: **Requires New Development.**

Curated inspiration and generative AI must remain separate epics with separate cost, privacy, moderation, fallback, and release gates.

## 9. Feature-to-API traceability matrix

| ID | Actor | Mobile feature | Existing route/source | Current status | Classification | Required backend/mobile work | Dependencies | Test evidence |
|---|---|---|---|---|---|---|---|---|
| PLAN-01 | All | Android requirements | No mobile source found; `composer.json:1-26` only describes Laravel backend | Not Found | Requires New Development | Define supported OS, devices, permissions, signing, store requirements | Framework decision | Device matrix to be created |
| PLAN-02 | All | iOS requirements | No mobile source found | Not Found | Requires New Development | Define iOS versions, Apple login, APNs, signing, store requirements | Framework decision | Device matrix to be created |
| PLAN-03 | All | Mobile architecture | Existing Laravel API; no native client | Not Found | Decision Required | Compare native, Flutter, React Native using team standards and API fit | Stakeholder decision | Architecture decision record |
| PLAN-04 | All | Wireframes/design system | `docs/mayush_design_charte_graphique_guide.md:45-66`, `222-312` | Verified web foundation | Extend for mobile | Create mobile tokens, navigation, states, and component specs | PLAN-03 | UX review and screenshot tests |
| PLAN-05 | All | Localization/RTL | `routes/api.php:256-257`, `api.php:275-292` | Partially Verified | Reuse after validation | Define locale, direction, currency, fallback, and translated payload contract | API contract | Locale/RTL tests |
| PLAN-06 | All | Push/analytics/crash/flags | `routes/api.php:216-243`; `app/Notifications/OrderNotification.php:11-67` | Partially Verified | Extend for mobile | Device lifecycle, push provider, event taxonomy, crash reporting, flags | Mobile foundation | Event and delivery tests |
| AUTH-01 | Customer | Email registration | `routes/api.php:20-21`; `AuthController.php:26-96` | Partially Verified | Reuse after validation | Normalize response/errors and verification state | Mobile foundation | Auth contract tests |
| AUTH-02 | Customer | Email/password login | `routes/api.php:20`; `AuthController.php:142-222` | Partially Verified | Reuse after validation | Define customer-only default and role-aware login behavior | Role matrix | Rate-limit/auth tests |
| AUTH-03 | Customer | OTP login/verification | `routes/otp.php:20-33`; `AuthController.php:99-138` | Partially Verified | Extend for mobile | Verify true phone-login path, expiry, attempts, resend, provider behavior | SMS config | `RateLimitingTest.php:35-78` plus new API tests |
| AUTH-04 | Customer | Google/Facebook/Apple | `AuthController.php:241-325`; `composer.json:14-26` | Requires Security Review | Extend for mobile | Native token/code validation, provider mapping, replay tests | Provider credentials | New provider contract tests |
| AUTH-05 | Customer | Logout/token revocation | `AuthController.php:229-238`, `389-428` | Partially Verified | Extend for mobile | Confirm expiry/revocation; response currently exposes `expires_at: null` | Auth contract | Token revocation tests |
| AUTH-06 | Customer | Password reset | `routes/api.php:29-35` | Exists but Not Mobile-Ready | Reuse after validation | Response/error/attempt contract | Auth | Rate-limit tests |
| AUTH-07 | Customer | Profile/image | `routes/api.php:214-223` | Partially Verified | Reuse after validation | Image constraints, response resource, retry behavior | Upload policy | Upload/auth tests |
| AUTH-08 | Customer | Addresses/default | `routes/api.php:186-194` | Partially Verified | Reuse after validation | Ownership, validation, default rules, mobile forms | Cart/checkout | Route/security tests |
| AUTH-09 | Customer | Saved payment methods | `PaymentTokenController.php:14-60`; `routes/web.php:434-439` | Exists but Not Mobile-Ready | Extend for mobile | API list/default/deactivate; mask fields; ownership and expiry | Payment security | `PaymentVaultRegressionTest.php:19-104` plus API tests |
| HOME-01 | Guest/Customer | Banners/sliders | `routes/api.php:269`, `342-345`; `StorefrontDataService.php:27-32`, `166-179` | Partially Verified | Reuse after validation | Mobile payload, image variants, link contract | API resources | Homepage tests |
| HOME-02 | Guest/Customer | Collections | `ProductCollection.php:8-62`; `ProductCollectionService.php:13-143` | Partially Verified | Reuse after validation | Mobile collection endpoint/resource | API contract | `ProductCollectionLandingPageTest.php:15-104` |
| HOME-03 | Guest/Customer | Best sellers | `ProductController.php:243-247` | Verified ranking | Reuse after validation | Confirm item limits and mobile resource | Product resource | Catalog tests |
| HOME-04 | Guest/Customer | Flash/today’s deals | `routes/api.php:305`, `415-418`; `ProductController.php:211-222` | Verified source; contract pending | Reuse after validation | Dates, countdown, disabled/empty handling | Home payload | Flash-deal/home tests |
| HOME-05 | Guest/Customer | New arrivals | `StorefrontDataService.php:144-152`; `ProductController.php:256-260` | Partially Verified | Extend for mobile | Standardize global endpoint and ranking definition | Catalog contract | New-arrival tests |
| HOME-06 | Guest/Customer | Trending | Product sales/views: `ProductController.php:243-247`; `ProductView.php:8-30` | Not a named ranker | Requires API Standardization | Define ranking formula, time window, exclusions, cache | Analytics decision | Ranking tests |
| HOME-07 | Guest/Customer | Recommendations | `ProductController.php:249-254`; `RecommendationEngineTest.php:15-53` | Rule-based only | Extend for mobile | Label as frequently bought/general until user ranking exists | Recommendation epic | Recommendation contract tests |
| HOME-08 | Customer | Recently viewed | `routes/api.php:245`; `ProductController.php:389-392` | Partially Verified | Reuse after validation | Guest/auth merge and privacy behavior | Auth/product views | Last-viewed tests |
| HOME-09 | Guest/Customer | Categories/brands/sellers | `routes/api.php:267-340` | Existing API | Reuse after validation | Public-data/resource validation | Home/catalog | Catalog tests |
| HOME-10 | Guest/Customer | Curated inspiration | `HomepageRenderingTest.php:280-328`; `api.php:406-409` | Existing web/content foundation | Extend for mobile | Mobile editorial resource, links, saved state if required | Content API | Content tests |
| CAT-01 | Guest/Customer | Category/subcategory browsing | `routes/api.php:277-282`, `299-303` | Existing API | Reuse after validation | Validate hierarchy and payload consistency | Catalog | Category tests |
| CAT-02 | Guest/Customer | Brand browsing | `routes/api.php:271-273`, `304` | Existing API | Reuse after validation | Validate brand/product resource | Catalog | Brand tests |
| CAT-03 | Guest/Customer | Search suggestions | `routes/api.php:256`; `SearchSuggestionController` | Existing route | Reuse after validation | Response schema, debounce guidance, abuse limits | Search contract | Rate-limit/search tests |
| CAT-04 | Guest/Customer | Search | `routes/api.php:312`; `ProductController.php:264-367` | Existing API | Reuse after validation | Standardize query fields and fallback | Search | `SearchFilterRegressionTest.php:16-153` |
| CAT-05 | Guest/Customer | Sort/filter/pagination | `ProductController.php:269-366`, `181-201` | Existing but inconsistent risk | Requires API Standardization | Enumerate values, stable pagination, duplicate prevention | Catalog contract | Contract tests |
| CAT-06 | Guest/Customer | Semantic search | `SemanticUtility.php:129-166`; `SemanticSearchTest.php:14-197` | Partially Verified | Reuse after validation | Mobile opt-in/fallback and latency contract | Search | Semantic tests |
| REC-01 | Customer | Behavioral event capture | `ProductView.php:8-30`; `SearchUtility` usage `ProductController.php:311-320` | Partial | Extend for mobile | Event schema, consent, retention, anonymous identity | Analytics | Event contract tests |
| REC-02 | Customer | User-level recommendations | No user-level ranker found | Not Found | Requires New Development | Recommendation service and fallback strategy | REC-01 | Offline/ranking evaluation |
| AI-01 | Guest/Customer | Curated inspiration | Product collections/blog evidence above | Partial | Reuse after validation | Mobile content contract | Content API | Content tests |
| AI-02 | Customer/Designer | Generative inspiration | `AiPrompt.php:9-18`; `AiUsageLog.php:9-25`; `SemanticUtility.php:13-166` | Foundations only | Requires New Development | Prompt/input/result/history/moderation/product matching | AI product decision | AI safety and cost tests |
| ROLE-01 | Vendor | Seller workspace | `routes/api_seller.php:7-165` | Existing API; security review needed | Extend for mobile | Mobile resources, ownership tests, seller navigation | Seller security | Seller isolation tests |
| ROLE-02 | Delivery | Delivery workspace | `routes/api.php:59-74` | Existing API; operational review needed | Extend for mobile | Mobile status flows, push, offline behavior | Delivery ops | Delivery authorization tests |
| ROLE-03 | Admin/Staff | Limited mobile operations | `IsAdmin.php:17-26`; `routes/admin.php:95-143` | Web/admin exists | Defer/extend later | Define alert/action subset and mobile API | Product decision | Admin permission tests |
| ROLE-04 | Designer | Professional workflow | No dedicated role/route found | Not Found | Requires New Development | Role, profile, preferences, projects/moodboards, permissions | Product decision | New role/policy tests |

## 10. API documentation and standardization gaps

The API has strong breadth but is not yet a complete mobile contract.

Required documentation work:

1. Publish mobile-scoped OpenAPI paths instead of exposing only promotions.
2. Define a consistent success/error envelope.
3. Define authentication, role, and middleware requirements per endpoint.
4. Define localization and currency behavior.
5. Define pagination and sorting values.
6. Define product-card and product-detail resources.
7. Define image derivatives and fallback URLs.
8. Define price, tax, discount, stock, variant, and availability fields.
9. Define error codes rather than relying only on translated messages.
10. Define idempotency behavior for order/payment mutations.
11. Define deep-link destinations.
12. Define rate limits and retry guidance.
13. Add contract tests for each mobile-consumed endpoint.

Do not create a generic “mobile API” that duplicates existing domain logic. Prefer mobile resources, aggregation, and adapters over a second commerce implementation.

## 11. Security findings and release blockers

### S-01 — Native social identity contract

Status: **Requires Security Review; P0 before social-login release.**

Evidence: `AuthController.php:241-325` validates provider tokens through Socialite, but the request uses separate `provider` and `social_provider` fields, and the provider identity lookup/storage paths differ at lines 288 and 306. Native provider-specific tests are required.

Required controls:

- Validate signed provider tokens or authorization codes server-side.
- Verify issuer, audience, nonce/state where applicable, and provider identity.
- Do not trust client-submitted email/name as identity proof.
- Prevent provider-account takeover and account-linking mistakes.
- Add Google, Facebook, and Apple contract tests.

### S-02 — OTP abuse and verification state

Status: **Requires API Standardization and Security Review; P0 for OTP release.**

Evidence: API verification routes exist at `routes/api.php:16-35`; web OTP routes exist at `routes/otp.php:20-33`; rate limiting covers login/password/register in `tests/Feature/Security/RateLimitingTest.php:35-78`.

Required controls:

- Confirm actual mobile OTP-login route and flow.
- Expiry and attempt counters.
- Resend throttling.
- Phone-number normalization.
- No OTP in logs or analytics.
- Enumeration-resistant errors.

### S-03 — Seller and customer isolation

Status: **Requires Security Review; P0 before seller mobile mutations.**

Evidence: policies and tests exist at `app/Policies/OrderPolicy.php:15-49`, `app/Policies/ProductPolicy.php:15-...`, and `tests/Feature/Security/AuthorizationPolicyTest.php:74-147`. Existing seller API routes still require controller-by-controller ownership audit before mobile exposure.

### S-04 — Payment token API exposure

Status: **Exists securely in the web flow; mobile API is not ready; P0 before mobile checkout.**

Evidence: `PaymentToken.php:14-35`, `PaymentTokenController.php:14-60`, `routes/web.php:434-439`, and `PaymentVaultRegressionTest.php:19-104`.

Required controls:

- Masked card metadata only.
- No raw token in JSON responses.
- Own-user default/delete enforcement.
- Expired/inactive filtering.
- Idempotent operations.

### S-05 — Token expiration and revocation

Status: **Requires Security Review.**

Evidence: logout deletes the current Sanctum token at `AuthController.php:229-238`, while the login response returns `expires_at: null` at `AuthController.php:389-428`. The mobile token-lifetime policy must be explicitly decided and tested.

### S-06 — Deep links and external payment returns

Status: **Requires New Mobile/API Contract.**

All authentication, product, seller, collection, and payment return links must validate destination and state. External gateway callback behavior must remain server-controlled.

### S-07 — AI output safety and cost

Status: **Requires New Development and release gate.**

AI results must be validated against published/available MAYUSH products, moderated, rate-limited, logged without sensitive prompts, and safely degraded when Gemini is unavailable.

## 12. Mobile architecture decisions and open decisions

### Decision D-01 — Mobile framework

Status: **Decision Required.**

Repository evidence confirms Laravel APIs and historical Flutter-oriented comments in `routes/api.php:323-325`, but no current mobile source or team standard was found. This is insufficient to mandate Flutter.

Decision options:

| Option | Advantages | Risks | Decision criteria |
|---|---|---|---|
| Flutter | Single Android/iOS codebase; strong UI consistency; historical repository reference | Team skill and native SDK integration must be verified | Existing team capability, Apple/Google auth, payment/deep-link needs |
| React Native | JavaScript ecosystem; shared component talent may exist | Native module and performance complexity | Existing team standards and package support |
| Native Kotlin/Swift | Maximum platform control and native behavior | Two codebases and higher delivery surface | Need for platform-specific experiences or compliance |
| Other | May fit existing organization standard | Unknown integration cost | Must be supported by evidence |

Sprint 0 output: an Architecture Decision Record with selected framework, rejected options, proof-of-concept criteria, ownership, and rollback implications.

### Decision D-02 — Mobile API strategy

Recommendation: preserve `/api/v2` business logic and add mobile-scoped resources/adapters only where required. A mobile home aggregation endpoint may reduce network waterfalls, but it must compose existing services rather than replicate homepage rules.

### Decision D-03 — State and offline strategy

Required decisions:

- What content is cacheable for guests?
- How are carts synchronized after reauthentication?
- Are wishlist and recently-viewed changes queued offline?
- Are seller/delivery mutations ever allowed offline?
- What is the conflict policy for stock, price, and cart changes?

### Decision D-04 — Customer and professional boundaries

The first customer app should support professional discovery as content and collections. A dedicated Interior Designer role should be approved separately before adding database roles, policies, onboarding, or professional workspaces.

## 13. Agile initiative and epic structure

### Initiative M-INIT-01 — Mobile customer discovery

**Objective:** Deliver a fast, premium customer discovery app using existing MAYUSH catalog and merchandising capabilities.

**Epics:** M-01 foundation, M-02 account, M-03 home, M-04 catalog/search, M-05 product evaluation.

**Exit:** guest browsing, customer login, home, catalog, search, product details, and curated inspiration work on supported Android/iOS devices.

### Initiative M-INIT-02 — Mobile commerce and retention

**Objective:** Expose existing cart, checkout, payment, orders, loyalty, notifications, support, and post-purchase services safely.

**Epics:** M-06 commerce, M-07 post-purchase.

**Exit:** order placement and post-purchase workflows pass payment, authorization, idempotency, and device testing.

### Initiative M-INIT-03 — Role-based mobile operations

**Objective:** Expose selected existing seller, delivery, and limited admin capabilities.

**Epics:** M-08 seller core, M-09 seller optional modules, M-10 delivery/admin operations.

**Exit:** each actor has independently approved permissions, navigation, notifications, and rollback behavior.

### Initiative M-INIT-04 — Professional intelligence

**Objective:** Add dedicated designer workflows, advanced recommendations, and generative inspiration only after the platform contracts are ready.

**Epics:** M-11 recommendation maturity, M-12 curated inspiration, M-13 generative AI/designer workflow.

**Exit:** recommendations are measurable and privacy-aware; AI outputs are validated, moderated, cost-controlled, and linked to real MAYUSH products.

## 14. Detailed Agile backlog

Relative sizing uses XS/S/M/L/XL/Needs refinement only. No numerical points or calendar duration are assigned because team velocity and capacity have not been established.

### M-01 Mobile foundation

#### M1-001 — Mobile framework ADR

**Story:** As the delivery team, we need an evidence-based framework decision so Android and iOS delivery does not begin with an unsupported technical assumption.

**Classification:** Decision Required.  
**Size:** L.  
**Evidence:** No mobile source found; Flutter reference only at `routes/api.php:323-325`.

**Acceptance criteria:** options compared; team skill and native integration constraints captured; proof-of-concept criteria defined; framework selected or explicitly blocked.

**QA/security/docs:** document build, secure storage, social login, deep-link, push, payment-return, accessibility, and CI test criteria.

#### M1-002 — Mobile API inventory and contract baseline

**Story:** As a mobile developer, I need documented request/response/auth/error contracts for every selected endpoint.

**Classification:** Extend for mobile.  
**Size:** XL.  
**Evidence:** `AgentDiscoveryController.php:40-166` documents only promotions; selected APIs are in `routes/api.php:16-345`.

**Acceptance criteria:** selected endpoint matrix includes actor, middleware, request, response, pagination, errors, rate limits, and test evidence; OpenAPI scope is approved.

#### M1-003 — Mobile design system and reusable states

**Story:** As a customer, I need consistent mobile components aligned with Mayush branding and usable in loading, empty, error, and offline states.

**Classification:** Extend for mobile.  
**Size:** L.  
**Evidence:** `docs/mayush_design_charte_graphique_guide.md:45-66`, `222-312`, `609-638`.

**Acceptance criteria:** typography, colors, icons, spacing, touch targets, focus/contrast rules, product cards, forms, skeletons, empty states, error states, and RTL behavior are documented and reviewed.

### M-02 Account and authentication

#### M2-001 — Customer email/phone registration

**Classification:** Reuse after validation.  
**Size:** M.  
**Evidence:** `AuthController.php:26-96`.

**Acceptance criteria:** mobile handles email and phone registration, verification-required state, duplicate identity, validation error, retry, and successful Sanctum token response.

#### M2-002 — OTP verification and OTP login decision

**Classification:** Extend for mobile; P0.  
**Size:** L.  
**Evidence:** `routes/otp.php:20-33`; `AuthController.php:99-138`.

**Acceptance criteria:** the team documents whether OTP is registration verification, login, or both; expiry, attempts, resend, throttling, phone normalization, and provider failure are tested.

#### M2-003 — Social authentication

**Classification:** Requires Security Review; P0.  
**Size:** XL.  
**Evidence:** `AuthController.php:241-325`.

**Acceptance criteria:** provider-specific native tokens/codes are validated server-side; identity mapping is correct; account linking is safe; Apple private relay email is handled; replay and invalid-token tests pass.

#### M2-004 — Customer account and address management

**Classification:** Reuse after validation.  
**Size:** M.  
**Evidence:** `routes/api.php:186-223`.

**Acceptance criteria:** profile, avatar, phone/email checks, addresses, default address, deletion, and validation errors work only for the authenticated customer.

### M-03 Home and discovery

#### M3-001 — Home content composition

**Classification:** Extend for mobile.  
**Size:** L.  
**Evidence:** `routes/api.php:269-345`; `StorefrontDataService.php:27-213`.

**Acceptance criteria:** selected home sections have explicit data source, ranking, limit, image, locale, cache, empty, disabled, and deep-link behavior.

#### M3-002 — Merchandising sections

**Classification:** Reuse after validation.  
**Size:** L.  
**Evidence:** `ProductController.php:211-260`; `ProductCollectionService.php:73-96`.

**Acceptance criteria:** best sellers, today’s deals, flash deals, newest products, collections, sellers, brands, and recently viewed are labelled accurately and never described as personalized without user-level ranking.

#### M3-003 — Curated inspiration

**Classification:** Reuse after validation.  
**Size:** M.  
**Evidence:** `HomepageRenderingTest.php:280-328`; `BlogContentBlockService.php:130-171`.

**Acceptance criteria:** room/style/editorial content opens valid destinations, unavailable products are handled, and curated inspiration remains separate from generative AI.

### M-04 Catalog and search

#### M4-001 — Category and brand browsing

**Classification:** Reuse after validation.  
**Size:** M.  
**Evidence:** `routes/api.php:271-304`; `ProductController.php:181-201`.

**Acceptance criteria:** hierarchy, pagination, translated names, images, empty sections, and product availability are consistent.

#### M4-002 — Search suggestions and search history

**Classification:** Extend for mobile.  
**Size:** M.  
**Evidence:** `routes/api.php:256`; `ProductController.php:311-320`.

**Acceptance criteria:** suggestions are debounced, rate-limited, cancellable, privacy-aware, and safe when the API fails; history behavior is defined for guest and authenticated users.

#### M4-003 — Search, filters, sort, and pagination

**Classification:** Requires API Standardization.  
**Size:** L.  
**Evidence:** `ProductController.php:264-367`; `SearchFilterRegressionTest.php:16-153`.

**Acceptance criteria:** all allowed filters and sort keys are enumerated; pagination is stable; duplicate/skip behavior is tested; semantic fallback is defined; price/currency/availability are backend values.

### M-05 Product evaluation and engagement

#### M5-001 — Product details and variants

**Classification:** Reuse after validation.  
**Size:** L.  
**Evidence:** `routes/api.php:313-320`; `ProductController.php:57-160`, `369-386`.

**Acceptance criteria:** gallery, translations, attributes, dimensions, variant price, stock, seller, taxes, and unavailable-variant states are represented without client-side business calculation.

#### M5-002 — Wishlist, follow, reviews, views, and stock alerts

**Classification:** Reuse after validation.  
**Size:** L.  
**Evidence:** `routes/api.php:165-194`, `245`; `FollowSellerTest.php:13-81`; `StockAlertSubscriptionTest.php:12-95`.

**Acceptance criteria:** ownership, guest behavior, deduplication, notification behavior, and offline retry policies are explicit and tested.

### M-06 Commerce

#### M6-001 — Cart and coupon integration

**Classification:** Reuse after validation.  
**Size:** L.  
**Evidence:** `routes/api.php:78-103`.

**Acceptance criteria:** backend totals, quantity, coupon eligibility, stock changes, vendor splits, and failures are displayed accurately.

#### M6-002 — Shipping and addresses

**Classification:** Reuse after validation.  
**Size:** L.  
**Evidence:** `routes/api.php:100-116`, `186-194`, `353-360`.

**Acceptance criteria:** shipping cost, carrier, city/state/area, address selection, and unavailable-delivery states are server-controlled.

#### M6-003 — Saved payment methods and checkout

**Classification:** Extend for mobile; P0.  
**Size:** XL.  
**Evidence:** `PaymentTokenController.php:14-60`; `PaymentVaultRegressionTest.php:19-104`.

**Acceptance criteria:** only masked metadata is exposed; own-user operations pass; checkout is idempotent; payment returns are deep-link safe; existing high-risk payment tests remain green.

### M-07 Post-purchase and retention

#### M7-001 — Orders, invoice, reorder, cancellation, refunds

**Classification:** Reuse after validation.  
**Size:** XL.  
**Evidence:** `routes/api.php:135-142`, `200-210`.

**Acceptance criteria:** customer can see only own orders, status is authoritative, cancellation/refund rules remain backend-controlled, and invoice access is protected.

#### M7-002 — Notifications, chat, wallet, loyalty

**Classification:** Reuse after validation.  
**Size:** XL.  
**Evidence:** `routes/api.php:127-133`, `197-243`; `NotificationReliabilityTest.php:103-287`.

**Acceptance criteria:** notification read state, push registration, chat ownership, wallet/points balances, and after-commit notification behavior are validated.

### M-08 Vendor mobile workspace

#### M8-001 — Seller core workspace

**Classification:** Extend for mobile; Requires Security Review.  
**Size:** XL.

**Evidence:** `routes/api_seller.php:7-77`; `IsSeller.php:17-27`; `SellerApproved.php:14-40`.

**Acceptance criteria:** seller sees only owned shop/products/orders/financial data; approval state is honored; every mutation has an ownership test; mobile navigation reflects seller capabilities.

#### M8-002 — Seller optional modules

**Classification:** Defer behind feature flags.  
**Size:** XL / Needs refinement.

**Evidence:** `routes/api_seller.php:81-159`.

**Acceptance criteria:** each optional module is independently enabled, documented, authorized, tested, and releasable.

### M-09 Delivery and limited operations

#### M9-001 — Delivery workflow

**Classification:** Extend for mobile.  
**Size:** XL.

**Evidence:** `routes/api.php:59-74`.

**Acceptance criteria:** assigned delivery scope, state transitions, collection/earnings access, offline handling, and push notifications are approved by operations.

#### M9-002 — Admin/staff mobile alerts

**Classification:** Defer; Decision Required.  
**Size:** L.

**Evidence:** `IsAdmin.php:17-26`; `routes/admin.php:95-143`.

**Acceptance criteria:** only explicitly approved operational alerts/actions are exposed; no admin API is inferred from web routes.

### M-10 Recommendations and professional intelligence

#### M10-001 — Rule-based recommendation API

**Classification:** Extend for mobile.  
**Size:** L.

**Evidence:** `FrequentlyBoughtProduct.php:9-26`; `RecommendationEngineTest.php:15-53`; `ProductView.php:8-30`; `ProductCollectionService.php:81-96`.

**Acceptance criteria:** recommendation type is disclosed, fallback is deterministic, unavailable products are excluded, impressions/clicks are tracked, and guest privacy is preserved.

#### M10-002 — Advanced personalized ranking

**Classification:** Requires New Development; Defer.  
**Size:** XL / Needs refinement.

**Acceptance criteria:** product/business definition, data consent, feature signals, offline evaluation, online metrics, cold-start fallback, retention, and rollback are approved.

#### M10-003 — Designer role and workflow

**Classification:** Requires New Development; Decision Required.  
**Size:** XL / Needs refinement.

**Acceptance criteria:** role, permissions, onboarding, profile, preferences, saved projects/moodboards, notifications, audit rules, and relationship to customer/vendor roles are approved before schema work.

#### M10-004 — Generative AI inspiration

**Classification:** Requires New Development; Defer behind feature flag.  
**Size:** XL / Needs refinement.

**Evidence:** AI foundations at `AiPrompt.php:9-18`, `AiUsageLog.php:9-25`, `SemanticUtility.php:13-166`; no end-user inspiration workflow found.

**Acceptance criteria:** room/style/budget input, product matching, availability check, save/share/history, moderation, cost limits, prompt controls, usage analytics, deletion, and safe fallback are implemented and tested.

## 15. Dependency map and recommended delivery stages

```text
Stage 1: Existing-platform baseline
  -> actor matrix, evidence matrix, API scope, framework ADR

Stage 2: Mobile foundation
  -> app shell, API client, secure storage, design system, localization, analytics

Stage 3: Customer identity
  -> registration, login, OTP decision, social security review, profile, addresses

Stage 4: Customer discovery
  -> home sections, collections, sellers, inspiration, categories, brands

Stage 5: Catalog/search
  -> search suggestions, filters, sort, pagination, semantic fallback

Stage 6: Product evaluation
  -> detail, variants, reviews, wishlist, follow, viewed, stock alerts

Stage 7: Commerce
  -> cart, shipping, saved methods, checkout, payments, idempotency

Stage 8: Post-purchase
  -> orders, invoices, reorder, refunds, notifications, wallet, loyalty, support

Stage 9: Operational actors
  -> seller core, seller optional modules, delivery, limited admin

Stage 10: Intelligence and professional workflows
  -> rule-based recommendations, designer role, generative AI
```

Dependencies that block safe implementation:

- Framework selection blocks mobile foundation.
- API contract blocks reliable mobile UI integration.
- Social-auth security review blocks social-login release.
- Payment-method API and idempotency block mobile checkout.
- Seller ownership audit blocks seller mutations.
- Designer role decision blocks professional schema/workflow work.
- Recommendation definitions block the word “personalized.”
- Curated inspiration can proceed before generative AI.
- AI cost/moderation controls block generative release.

## 16. Priority model

Priority is based on architecture dependency, security risk, business value, user value, uncertainty, API readiness, reuse potential, testability, and operational risk.

| Priority | Meaning | Current examples |
|---|---|---|
| P0 | Release blocker or foundational dependency | Framework ADR, API contract, social identity validation, payment-token mobile contract, seller isolation before seller mutations |
| P1 | Core initial customer product | Guest browsing, customer auth, home, categories, brands, search, product details |
| P2 | Important enhancement after core journey | Wishlist/follow, recently viewed, curated inspiration, post-purchase, notifications |
| P3 | Optional/later capability | Full seller mobile, delivery mobile, advanced recommendations, designer workspace, generative AI |
| Blocked | Cannot start safely | Social login before token contract; checkout before payment/idempotency; seller mutations before ownership audit |
| Decision Required | Product/architecture choice missing | Framework, designer role, admin mobile scope, AI product boundary |

## 17. Recommended initial release boundary

### Included in initial release

- Guest browsing.
- Customer registration and login after contract validation.
- Profile basics.
- Address viewing/management if required by the selected journey.
- Mobile home.
- Banners and sliders.
- Existing product collections.
- Categories and brands.
- Product listing.
- Sorting and filtering after standardization.
- Search suggestions.
- Search fallback.
- Product detail.
- Recently viewed.
- Curated design inspiration.

### Included behind feature flag or validation gate

- OTP login.
- Google/Facebook/Apple login.
- Semantic search.
- Personalized-looking sections using clearly labelled general/rule-based ranking.
- Push notifications.
- Wishlist/follow if API contract validation completes.

### Defined but not implemented in the first release

- Cart and checkout.
- Saved payment methods.
- Post-purchase flows.
- Seller mobile workspace.
- Delivery operator workspace.
- Limited admin operations.
- Advanced personalized ranking.
- Designer role.
- Generative AI inspiration.

### Explicitly deferred

- Native full admin dashboard.
- Native full seller optional modules.
- POS mobile operations.
- Generative moodboards without product-availability validation.
- Advanced collaborative filtering without data/consent/evaluation design.

## 18. Mobile Definition of Ready

A story cannot enter implementation unless:

- Actor and permission boundary are defined.
- Repository evidence is linked.
- Classification is known.
- API exists or a new API task is approved.
- Request, response, auth, pagination, and error contracts are documented.
- Ownership rules are explicit.
- Wireframe and navigation destination exist.
- Loading, empty, error, retry, and offline behavior are defined.
- Analytics events are identified.
- Security and privacy impact are assessed.
- Dependencies and release flags are understood.
- Acceptance criteria are testable.
- Product decisions are resolved.

## 19. Mobile Definition of Done

A story is complete only when:

- Mobile code is reviewed.
- Any backend change is reviewed.
- API contract is updated.
- Business logic is not unnecessarily duplicated.
- Unit, integration, API contract, and authorization tests pass.
- Cross-customer and cross-vendor tests pass where relevant.
- Existing high-risk commerce tests remain green.
- Loading, empty, error, retry, and offline states are verified.
- Analytics events are verified.
- Accessibility checks pass.
- Supported Android/iOS device tests pass.
- Sensitive data is protected.
- Feature-flag and rollback behavior is tested.
- UX and Product Owner reviews pass.
- Documentation is updated.

## 20. Mandatory release gates

| Gate | Blocking condition | Owner type | Evidence required |
|---|---|---|---|
| G-01 | Cross-customer data exposure | Security/API | Negative authorization tests |
| G-02 | Cross-vendor data exposure | Security/Seller backend | Seller isolation and controller tests |
| G-03 | Unauthorized admin/staff access | Security/Admin | Permission and role tests |
| G-04 | Unverified social identity | Security/Auth | Provider token/code tests |
| G-05 | Insecure token storage/revocation | Mobile/API security | Device storage and revocation evidence |
| G-06 | Incorrect price, stock, tax, shipping, or cart total | Commerce backend/QA | API integration and checkout tests |
| G-07 | Duplicate order or payment | Payment/backend | Idempotency and callback tests |
| G-08 | Exposed payment secrets | Payment/security | Response/log inspection and vault tests |
| G-09 | Broken account deletion | Product/legal/API | Deletion, token, retention evidence |
| G-10 | Critical crash or broken deep link | Mobile/QA | Device matrix and deep-link tests |
| G-11 | AI output references unavailable products | AI/product | Product validation tests |
| G-12 | Uncontrolled AI cost or unsafe output | AI/security/finance | Quota, moderation, and usage-log evidence |
| G-13 | Critical accessibility failure | UX/QA | Accessibility audit |

## 21. Risk register

| Risk ID | Risk | Evidence | Severity | Mitigation | Blocking status |
|---|---|---|---|---|---|
| R-01 | Full API contract is undocumented | `AgentDiscoveryController.php:40-166` documents promotions only | High | Create mobile-scoped OpenAPI and contract tests | P0 |
| R-02 | Social provider request/identity mapping is ambiguous | `AuthController.php:243-306` | Critical | Provider-specific server validation and tests | P0 |
| R-03 | OTP mobile-login semantics are unclear | `routes/otp.php:20-33`; `api.php:16-35` | High | Define flow, expiry, attempt, resend, and API contract | P0 |
| R-04 | Seller API mutations may have inconsistent ownership hardening | `routes/api_seller.php:7-159`; existing seller security tests | Critical | Audit every mobile-exposed seller mutation | P0 for seller release |
| R-05 | Payment vault has web routes but no confirmed mobile contract | `routes/web.php:434-439` | Critical | Add mobile-safe methods and tests | P0 for checkout |
| R-06 | “Trending” and “recommended” may be mislabelled | `ProductController.php:243-260`; `RecommendationEngineTest.php:15-53` | Medium | Define ranking formulas and product labels | P1 |
| R-07 | Pagination/sort behavior varies across endpoints | `ProductController.php:181-201`, `264-367` | High | Standardize schemas and duplicate/skip tests | P0 for catalog |
| R-08 | Home payload may create request waterfall | Separate endpoints in `routes/api.php:267-345` | Medium | Measure first; add composing endpoint only if needed | P1 |
| R-09 | AI foundation is not a customer workflow | `AiPrompt.php:9-18`; `AiUsageLog.php:9-25` | High | Separate curated content from generative product | Deferred |
| R-10 | Designer role is not represented | `User.php:24-31`; role middleware evidence | High | Approve role/product model before schema | Decision Required |
| R-11 | Push token ownership/lifecycle is incomplete for mobile | `api.php:216-219` | Medium | Define device registry lifecycle and tests | P1 |
| R-12 | Existing worktree changes may affect current behavior | `git status --short` recorded above | High | Do not treat uncommitted changes as baseline; re-audit before implementation | P0 planning control |
| R-13 | Framework choice is unsupported by current source | No mobile source found; Flutter comment only | Medium | Run ADR/proof of concept | Decision Required |
| R-14 | Offline cart/price/stock conflict | Commerce routes and backend authority | High | Keep business mutations online; define cache/conflict policy | P0 for commerce |

## 22. Testing strategy

### Backend/API tests

- Authentication, OTP, password reset, social identity, and token revocation.
- Customer profile/address ownership.
- Catalog/search/filter/sort/pagination contracts.
- Recommendation ranking and fallback.
- Product variant price/stock behavior.
- Wishlist/follow/review/stock-alert ownership.
- Cart, coupon, shipping, payment, and order idempotency.
- Payment-token masking, ownership, default/deactivate, expiry.
- Seller ownership and seller-role restrictions.
- Delivery assignment/state ownership.
- Admin/staff permission boundaries.
- Push-device token ownership.
- AI product validation, moderation, quotas, and fallback.

Existing relevant tests include:

- `tests/Feature/Security/RateLimitingTest.php:35-135`.
- `tests/Feature/Security/AuthorizationPolicyTest.php:34-256`.
- `tests/Feature/ProductionReadiness/CheckoutFlowTest.php:1-193`.
- `tests/Feature/Payment/PaymentVaultRegressionTest.php:19-104`.
- `tests/Feature/ProductionReadiness/SellerIsolationTest.php:1-136`.
- `tests/Feature/SemanticSearchTest.php:14-197`.
- `tests/Feature/RecommendationEngineTest.php:15-53`.
- `tests/Feature/Frontend/HomepageRenderingTest.php:39-366`.
- `tests/Feature/Frontend/SearchFilterRegressionTest.php:16-153`.
- `tests/Feature/Reliability/NotificationReliabilityTest.php:103-287`.

### Mobile tests

- Unit tests for repositories, use cases, validation, and state.
- Widget/screen tests for auth, home, search, catalog, product, and account.
- API contract tests against staging fixtures.
- Golden/screenshot tests for core screens.
- Offline/retry tests.
- Deep-link tests.
- Push-notification tests.
- Accessibility tests.
- French and RTL tests.
- Android/iOS device tests.
- Slow-network and low-memory tests.

## 23. Documentation requirements

The mobile program must maintain:

- Complete mobile OpenAPI specification.
- API authentication guide.
- Actor/permission matrix.
- Mobile navigation map.
- Mobile design system.
- Error-code catalog.
- Pagination/sort/filter contract.
- Image and media contract.
- Deep-link catalog.
- Push-notification catalog.
- Analytics event dictionary.
- Privacy and consent rules.
- Payment-token security contract.
- Recommendation definition document.
- AI prompt, cost, moderation, and retention policy.
- Device-support matrix.
- Environment and release-signing guide.
- Incident, rollback, and feature-flag runbook.
- QA evidence per release gate.

## 24. Stakeholder decisions required

1. Which mobile framework is approved after the ADR/proof of concept?
2. Which Android and iOS versions are supported?
3. Is tablet support included in the first release?
4. Is Arabic/RTL included now or only future-ready?
5. Is the first release customer-only, or must seller/delivery also ship?
6. Is the Interior Designer a formal role, a customer segment, or a seller/professional profile extension?
7. Which payment methods are approved for mobile launch?
8. Must saved CMI payment methods be available in the first mobile commerce release?
9. Which home sections are mandatory versus configurable?
10. What exactly qualifies as “trending” and “recommended” for customer-facing labels?
11. Is semantic search enabled by default or feature-flagged?
12. Is curated inspiration sufficient for the first release?
13. When may generative AI be exposed to customers?
14. What AI cost and moderation limits are acceptable?
15. Which admin alerts/actions, if any, need mobile access?

## 25. Deferred capabilities

The following should remain deferred until their dependencies are satisfied:

- Full native admin workspace.
- Full seller optional modules.
- Delivery mobile workspace unless operations confirms the need.
- Advanced collaborative-filtering recommendations.
- Dedicated designer projects/moodboards until role definition is approved.
- Generative AI until product validation, moderation, cost, and privacy controls exist.
- Mobile checkout until payment-token APIs and idempotency are approved.
- Native social login until token validation and identity-linking tests pass.

## 26. Final recommendations

1. Treat the first mobile release as a customer discovery client of the existing MAYUSH platform.
2. Reuse the existing catalog, merchandising, seller, product-view, collection, notification, and semantic foundations.
3. Do not label general best sellers, collections, or frequently bought products as personalized recommendations.
4. Complete a mobile-scoped API contract; the existing OpenAPI endpoint is not sufficient.
5. Perform the social-auth and seller-API authorization reviews before exposing those mutations.
6. Reuse the existing payment vault, but add a dedicated mobile-safe contract before mobile checkout.
7. Separate curated inspiration from generative AI.
8. Define the Interior Designer role before creating migrations, policies, or mobile workflows for it.
9. Keep the first release coherent: guest browsing, customer identity, home, catalog, search, product detail, and curated inspiration.
10. Use feature flags for semantic search, social providers, recommendation experiments, notifications, and AI.
11. Require the Definition of Ready and Definition of Done for every mobile story.
12. Re-run the repository baseline and security audit before implementation because the current working tree already contains uncommitted application and migration changes.

## 27. Verification and safety statement

This document was created as a planning artifact only.

- No application source code was changed.
- No route, controller, model, service, policy, middleware, migration, test, configuration, or environment file was changed.
- No dependency was installed or removed.
- No migration was executed.
- No database data was modified.
- No seed operation was executed.
- No branch was created.
- No commit was created.
- Nothing was staged or pushed to GitHub.
- Existing repository changes were preserved and not cleaned, reset, or overwritten.
