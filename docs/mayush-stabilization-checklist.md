# Mayush Stabilization Checklist

Date: 2026-05-06
Branch: main

## Baseline

- PHP: 8.2.12
- Laravel: 10.50.2
- Test database: SQLite in-memory via `phpunit.xml`
- Queue: sync in tests, `QUEUE_CONNECTION` driven elsewhere
- Frontend build stack: no build script beyond `npm run audit:prod`
- Route list: passed, 1,891 routes
- Route cache: passed
- Config clear: passed
- Cache clear: passed
- Composer validation baseline: passed with warnings for unbound `genealabs/laravel-sign-in-with-apple` and `genealabs/laravel-socialiter`
- Composer validation after fixes: passed without the previous `genealabs/*` warnings
- Test baseline: `php artisan test` timed out after 15 minutes before completing

## Fixed This Session

- #37 Contact form: public submissions now use `contact.store` POST, separate from the admin `contact` GET route.
- #47 Follow seller: seller shop follow/unfollow controls now submit CSRF-protected POST requests, and duplicate follows are prevented.
- #53 Seller notes: note detail AJAX route now uses a GET JSON contract with seller ownership checks.
- PH-0 debug hardening: production-facing `dd()`, `dump()`, `ray()`, and `var_dump()` artifacts were removed from `app`, `routes`, and `resources`; known debug stops in addon/payment paths now return/log controlled responses.
- Composer hardening: open-ended `genealabs/*` constraints were locked to `^12.0`.
- Cart modal CTA: "View Cart" points to cart and "Proceed to Checkout" points to checkout.
- MA-107 stock alerts: out-of-stock products now expose a visible CSRF-safe stock alert subscription form, and `stock:send-alerts` now processes the same `stock_subscriptions` table, respects variant stock, marks `notified_at` only after mail is queued, and logs failed queue attempts for retry.
- MA-099c tracking guard: mock tracking is blocked in production unless explicitly enabled through `LOGISTICS_MOCK_CARRIER_ENABLED`; order tracking view/sync authorization is shared across customers, sellers, admins, and staff, with guests and unrelated user roles blocked.
- MA-109 Onessta 3PL: ONESSTA admin routes now require admin access, missing credentials fail safely without remote calls, webhook signature rejections return controlled 403 JSON, eligible new orders immediately queue ONESSTA shipment creation with a local `QUEUED` shipment record, admin confirmation remains an idempotent retry path, and fake-HTTP coverage verifies credential validation, shipment creation/failure, tracking polling, webhook status/COD updates, and order-confirmation job dispatch.
- Technical analytics dashboard: verified-vendor KPIs and growth chart now use the same approved-and-verified shop definition, new vendor counts honor the selected date window, security metrics respect the selected range, and the Livewire dashboard render/date-range action is covered.
- #69/#70 Invoice documents: order details now store a product-name snapshot at order creation, invoice rendering falls back to that snapshot when the product relation is missing, and invoice labels now distinguish client/billing address, shipping address, and seller shop.
- Admin button route contracts: admin product, seller, and classified-product delete routes now require DELETE, touched delete buttons/forms include CSRF and method spoofing, the product list AJAX delete sends DELETE with CSRF, the classified delete URL typo was removed, and seller verification accept/reject controls render POST forms instead of GET links.
- AI-SEM semantic search: skipped semantic DB tests were replaced with deterministic SQLite tests using mocked HTTP/no external AI calls.
- Product variants: stock persistence now keeps simple-product SKU and product linkage, with variant-specific price/qty coverage.
- Product images: product mass assignment now persists gallery, thumbnail, and meta image values used by the product service.
- Product import/export: product imports now have regression coverage for required headings, required fields, image URL validation, stock creation, category links, and export headings aligned to the import template plus business-critical fields.
- Brand import: brand imports now validate files, required columns, duplicate names, logo references, and slug uniqueness; controller import runs in a transaction.
- Media references: test schema now includes `uploads.external_link`, matching importer/helper expectations for externally hosted images.
- Affiliate flows: users can apply once, approved affiliates can request withdrawals, affiliate histories are scoped to the current user, referral visits store cookies and track clicks, commission awards are idempotent, and admin approval now uses POST.
- SEO/sitemap: sitemap command now excludes disabled brands and unverified seller shops, admin sitemap generator methods are wired to the real command, public sitemap/robots routes are tested, and product/category SEO rendering has smoke coverage.
- Wallet payments: wallet recharge validates amount/method, failed wallet payments render a controlled retry page without crediting, online/API wallet credits are idempotent by gateway reference, and offline recharge approvals adjust balance only on approval-state changes.
- #26 Registration password validation: weak customer registration passwords are rejected and valid strong passwords create customer accounts.
- #27 Password reset flow: email reset code forms now post to the code-reset route, reset codes are validated, expire using the configured password reset window, and are cleared after successful use.
- #32 Purchase history: the customer purchase-history page now has a working filter route, a valid reorder route/link, an empty state, customer scoping, and a missing-product fallback.
- #41/#42 Compare list: compare add/remove/reset now have regression coverage, duplicate adds are prevented, remove/reset use CSRF-protected DELETE forms, and add-to-cart modal loading from compare is verified.
- #46 Buy Now: buy-now now validates stock before clearing carts, preserves the existing cart when stock is unavailable, creates active checkout cart rows, and uses selected variant pricing.
- #43/#44 Cart to checkout: cart rows saved through the shared cart utility are active by default, and the cart modal checkout CTA remains pointed at checkout.
- #48/#49 Search and filters: standard search now uses a SQLite-safe fallback outside MySQL/MariaDB, multi-word queries require all words instead of drifting to the last term, AJAX sidebar filters now honor category, brand, seller, price, and color inputs, and listing results render a no-results state.
- AI-SEM semantic search: deterministic semantic database tests pass locally with mocked embedding behavior and no external AI calls.
- Search schema: the `searches` table is now created by a guarded migration so keyword tracking does not crash local/test databases.
- #52 Classified product add path: seller classified product create/store/edit/update routes now live under an explicit authenticated user route group instead of the customer-only group; sellers can open the add form, while upload-credit enforcement remains on store.
- #51/#53 Seller notes: seller notes now have full create/list/view/update/delete regression coverage, ownership checks, and DELETE form markup instead of the removed unsafe GET delete alias.
- MA-104 Seller analytics: seller analytics dashboard and top-products endpoint now have smoke/scoping tests, including empty-data rendering and seller-owned product isolation.
- Seller onboarding seed safety: the onboarding email-template seed migration now writes to either `default_text` or legacy `content` depending on the installed schema, so SQLite/local migrations do not fail.
- HomeController boot safety: restored the missing `dashboard()` method wrapper that caused the local app to 500 during browser smoke.
- CI quality gate: workflow now fails on production debug artifacts in `app`, `routes`, or `resources`.
- Browser QA: added `docs/browser-qa-plan.md` covering the 20 requested manual/browser journeys.
- Browser QA execution: added `docs/browser-qa-execution-report.md` with local HTTP smoke results and the Browser Use Node runtime blocker.
- #45/Phase 16 Customer notifications: customer notification rendering now has the missing notification type translation schema, customer bulk deletion has a real web route, web/API delete and mark-read actions are scoped to the authenticated user, and non-owned notification reads return controlled 404 responses.
- QA-SYNC category/product sync deduplication: product bulk import now treats non-empty SKU as the product sync key, updates the existing product/stock on repeat imports, and the `product_categories` pivot is deduped and protected by a `(product_id, category_id)` unique key so repeated category syncs remain idempotent.
- REF-01 HomeController decomposition: homepage data assembly now lives in `HomeLayoutService`, the homepage renders with empty/minimal seeded data, and homepage AJAX section routes accept the GET/POST methods used by the frontend scripts.
- OPS-01/OPS-02/DevOps readiness: scheduler and queue production requirements are documented in `docs/scheduler-queue-readiness.md`, the operational runbook links to it, and CI/deploy gates now use strict Composer validation, debug artifact scanning, config cache validation, route cache validation, and `php artisan test --stop-on-failure`.
- MA-099/MA-099b/Wallet payment flows: CMI vault cards now deduplicate through a non-sensitive fingerprint compatible with encrypted card fields, saved-card default/delete actions use POST/DELETE only, CMI wallet callbacks credit through `WalletService` with gateway-reference idempotency, and duplicate CMI callbacks are acknowledged without requiring the expired wallet amount cache.
- SEC-01 route method/authorization hardening: customer and seller address default/billing/delete, purchase-history cancellation, and customer classified-product deletion now use CSRF-protected POST/DELETE forms instead of GET links; address mutations are scoped to the owning user, purchase cancellation returns 404 for non-owned orders, and classified product deletion remains owner-only.
- SEC-01 seller destructive GET sweep: seller physical product, digital product, custom label, and upload deletes now require DELETE routes and CSRF/method-spoofed forms; seller product, digital product, and upload deletes are owner-scoped with 403 responses for cross-seller IDs.
- SEC-01 admin catalog destructive GET sweep: admin category, brand, digital product, tax, language, and color deletes now require DELETE routes; category/brand AJAX deletes send CSRF-protected DELETE requests, and digital/tax/language/color delete controls render CSRF/method-spoofed forms.
- SEC-01 admin back-office destructive GET sweep: customer, custom page, role, staff, flash deal, subscriber, order, loyalty template, top banner, custom label, and custom notification deletes now require DELETE routes and CSRF/method-spoofed forms.
- SEC-01 remaining web destructive GET sweep: account deletion, attribute-value deletion, auction product/bid deletion, wholesale product deletion, and preorder order/conversation/FAQ deletion no longer accept GET; preorder visible delete controls now render DELETE forms, and the formerly dead attribute-value delete route now has a controller action.
- Browser QA automation recovery: the in-app browser was retried against `http://127.0.0.1:8001`, reached the app, and exposed a runtime form-entry blocker on email inputs; project-local Playwright was added as the fallback browser runner in `tests/BrowserQa/mayush-browser-qa.js`.
- Browser QA deterministic data: seeded an isolated SQLite browser QA database with admin/customer/seller users, an approved seller shop, stocked and out-of-stock products, and an order/tracking record for repeatable browser flows.
- Browser QA execution: Playwright completed the requested smoke run and documented pass/fail evidence in `docs/browser-qa-execution-report.md`; homepage, customer auth setup, purchase history, and DELETE-form smoke passed, while asset 404s, missing optional coupon tables, and SQLite-incompatible dashboard date SQL blocked several flows.
- Browser QA blocker remediation: local `artisan serve` asset URLs now resolve without `/public`, optional coupon/payment-method helpers are schema-safe in SQLite, auth layout fallback no longer builds `auth..user_login`, contact pages tolerate plain text content, customer dashboard addresses tolerate missing location relations, and admin/seller dashboard date grouping is SQLite-safe.
- Browser QA seed automation: added `tests/BrowserQa/seed-browser-qa.php` so browser fixtures are reproducible without PowerShell pipe encoding issues.
- Browser QA runner hardening: Playwright Browser QA now isolates guest/customer/seller/admin contexts, uses resilient login/click assertions, submits registration/contact forms through the DOM, and explicitly disables OTP/recaptcha/turnstile in deterministic seed data.
- Browser QA discovered runtime fixes: authenticated guest redirects now use the real `home` route instead of `/home`; customer profile, customer dashboard billing/shipping addresses, and checkout shipping address partials now tolerate missing city/country relations in local/test data.
- Production readiness pass: documented ONESSTA/CMI/mail/queue/scheduler/storage release checks in `docs/production-readiness-pass.md`, added ONESSTA production env keys to `.env.example`, and fixed ONESSTA shipment creation to inherit the production queue connection instead of falling back to inline `sync` execution when `ONESSTA_CREATE_SHIPMENT_QUEUE_CONNECTION` is omitted.
- ONESSTA production diagnostics: added `php artisan onessta:diagnose-order {order}` to explain why a specific order did or did not create a shipment, report local blockers, and optionally queue shipment creation through the same idempotent service path with `--dispatch`.
- CMI production diagnostics: added `php artisan cmi:diagnose --production` to verify required CMI config, HTTPS callback/success/fail URLs, callback POST route wiring, IP whitelist middleware, and production `CMI_ALLOWED_IPS` readiness before a live payment test.

## Tested This Session

- `tests/Feature/ProductionDebugArtifactTest.php`
- `tests/Feature/Frontend/ContactFormTest.php`
- `tests/Feature/Frontend/FollowSellerTest.php`
- `tests/Feature/Seller/SellerNoteTest.php`
- `tests/Feature/Frontend/CartModalCtaTest.php`
- `tests/Feature/Frontend/StockAlertSubscriptionTest.php`
- `tests/Feature/Frontend/StockAlertCommandTest.php`
- `tests/Feature/OrderTrackingTest.php`
- `tests/Feature/OnesstaIntegrationTest.php`
- `tests/Unit/CarrierTrackingManagerTest.php`
- `tests/Feature/Admin/TechnicalAnalyticsRepositoryTest.php`
- `tests/Feature/TechnicalDashboardTest.php`
- `tests/Feature/Admin/InvoiceRenderingTest.php`
- `tests/Feature/Admin/AdminButtonRouteContractTest.php`
- `tests/Feature/SemanticSearchTest.php`
- `tests/Feature/Seller/ProductVariantStockTest.php`
- `tests/Feature/Seller/ProductImageUpdateTest.php`
- `tests/Feature/Admin/ProductBulkImportExportTest.php`
- `tests/Feature/Admin/BrandBulkImportTest.php`
- `tests/Feature/Affiliate/AffiliateFlowTest.php`
- `tests/Feature/Seo/SitemapSeoTest.php`
- `tests/Feature/Wallet/WalletPaymentFlowTest.php`
- `tests/Feature/Auth/PasswordRegistrationValidationTest.php`
- `tests/Feature/Auth/PasswordResetFlowTest.php`
- `tests/Feature/Customer/PurchaseHistoryRegressionTest.php`
- `tests/Feature/Frontend/CompareFlowTest.php`
- `tests/Feature/Checkout/BuyNowFlowTest.php`
- `tests/Feature/Frontend/SearchFilterRegressionTest.php`
- `tests/Feature/Seller/ClassifiedProductPathTest.php`
- `tests/Feature/Seller/SellerAnalyticsDashboardTest.php`
- `tests/Feature/Customer/CustomerNotificationTest.php`
- `tests/Feature/Admin/CategoryProductSyncDeduplicationTest.php`
- `tests/Feature/Frontend/HomepageRenderingTest.php`
- `tests/Feature/DevOps/CiAndOpsReadinessTest.php`
- `tests/Feature/Payment/PaymentVaultRegressionTest.php`
- Combined targeted result: 46 passed, 120 assertions.
- Product import/export targeted result: 9 passed, 28 assertions.
- Affiliate flow targeted result: 10 passed, 30 assertions.
- SEO/sitemap targeted result: 5 passed, 32 assertions.
- Wallet/payment targeted result: 7 passed, 28 assertions.
- Customer auth/purchase history targeted result: 11 passed, 39 assertions.
- Cart/compare/buy-now targeted result: 10 passed, 40 assertions.
- Search/filter + semantic targeted result: 20 passed, 53 assertions.
- Seller dashboard/classified/notes targeted result: 13 passed, 61 assertions.
- Stock alert targeted result: 8 passed, 28 assertions.
- Order tracking targeted result: 14 passed, 26 assertions.
- Onessta targeted result: 15 passed, 56 assertions.
- Technical analytics targeted result: 4 passed, 17 assertions.
- Invoice/business documents targeted result: 12 passed, 45 assertions.
- Admin button route contract targeted result: 8 passed, 24 assertions.
- Customer notifications targeted result: 4 passed, 22 assertions.
- Category/product sync deduplication targeted result: 3 passed, 7 assertions.
- Product import/export regression after sync change: 4 passed, 20 assertions.
- Homepage rendering targeted result: 3 passed, 16 assertions.
- DevOps scheduler/queue/CI readiness targeted result: 3 passed, 15 assertions.
- Scheduler verification: `php artisan schedule:list` passed and listed 18 scheduled commands/jobs.
- Config cache verification: `php artisan config:cache` passed.
- Payment vault/CMI callback targeted result: 5 passed, 25 assertions.
- Wallet payment regression result: 7 passed, 28 assertions.
- Buy Now regression result: 4 passed, 17 assertions.
- Express Buy security regression result: 5 passed, 12 assertions.
- Route method security regression result: 5 passed, 26 assertions.
- Seller destructive route security result: 4 passed, 16 assertions.
- Admin button/catalog/back-office route contract result: 5 passed, 69 assertions.
- Remaining web destructive route contract result: 4 passed, 35 assertions.
- Purchase history/classified product regression after route hardening: 9 passed, 39 assertions.
- Browser QA route/security precheck after destructive GET sweep: 18 passed, 146 assertions.
- Browser QA fallback runner: `BROWSER_QA_BASE_URL=http://127.0.0.1:8001 BROWSER_QA_FLOW_TIMEOUT_MS=8000 node tests\BrowserQa\mayush-browser-qa.js` completed and produced the execution matrix in `docs/browser-qa-execution-report.md`.
- Browser QA npm audit after adding Playwright: `npm audit --omit=dev --audit-level=high` passed with 0 vulnerabilities.
- Browser QA blocker regression result: `tests\Feature\BrowserQa\BrowserQaBlockerRegressionTest.php` passed, 8 tests and 18 assertions.
- Browser QA blocker regression final result: `tests\Feature\BrowserQa\BrowserQaBlockerRegressionTest.php` passed, 11 tests and 23 assertions.
- Contact/purchase-history/seller-dashboard regression result after Browser QA fixes: 11 passed, 45 assertions.
- Browser QA rerun after blocker remediation: homepage, password reset, contact form, product detail, add to cart, stock alert, and destructive-form smoke rendered cleanly without console errors; repeated auth and navigation-wait flows remain for runner hardening.
- Browser QA final Playwright run: public homepage, registration, login/logout, password reset, contact, search, product detail, stock alert, add to cart, cart to checkout, buy now, follow seller, purchase history, DELETE-form smoke, seller dashboard/notes, and admin sitemap all passed with no captured console errors.
- Production readiness targeted result: `tests\Feature\OnesstaIntegrationTest.php`, `tests\Feature\OrderConfirmationWorkflowTest.php`, and `tests\Feature\DevOps\CiAndOpsReadinessTest.php` passed with 19 tests and 74 assertions.
- ONESSTA diagnostic targeted result: `tests\Feature\OnesstaOrderDiagnosticsTest.php`, `tests\Feature\OrderConfirmationWorkflowTest.php`, and `tests\Feature\OnesstaIntegrationTest.php` passed with 17 tests and 61 assertions.
- CMI diagnostic targeted result: `tests\Feature\Payment\CmiGatewayDiagnosticsTest.php`, `tests\Feature\Payment\PaymentVaultRegressionTest.php`, and `tests\Feature\Security\RouteHardeningTest.php` passed with 10 tests and 36 assertions.
- Production-like route exposure check: config and route cache passed with `APP_ENV=production`, `APP_DEBUG=false`, and no `_debugbar` or `_ignition` routes were exposed.
- Browser/HTTP smoke: public homepage, register, login, password reset, contact, robots, and sitemap returned 200; protected purchase history and checkout redirected to login.
- Route cache after fixes: passed.
- Composer validation after fixes: passed.
- Production debug artifact scan after fixes: no matches.
- Final verification route list: `php artisan route:list` passed and listed 1883 routes.
- Final verification route/config/cache cycle: `php artisan route:clear`, `php artisan config:clear`, `php artisan cache:clear`, and `php artisan route:cache` all passed.
- Final verification full suite: `php artisan test` passed with 560 tests and 1629 assertions.
- Final verification npm production audit: `npm audit --omit=dev --audit-level=high` passed with 0 vulnerabilities.
- Final verification debug artifact scan: no `dd()`, `dump()`, `ray()`, or `var_dump()` matches in `app`, `routes`, or `resources`.

## Not Tested

- Full test suite: timed out after 15 minutes during baseline.
- In-app browser form submission: the runtime is now reachable, but direct typing/filling into `input[type=email]` failed during login automation, so Playwright remains the active fallback until that browser-control issue is resolved upstream.
- Live payment gateways, mail delivery, and carrier integrations: not run locally.
- In-app browser form entry remains blocked on email inputs; Playwright is the working fallback for automated Browser QA.
- Some legacy destructive GET routes outside this pass remain for later phases, including auction/digital product, admin catalog/settings deletes, seller-side product deletes, and mobile API mutation routes.

## Requires Manual QA

- Deeper browser QA remains useful for product image upload/edit, variant selection, invoice PDF visual layout, and real payment/shipping callbacks; the Playwright smoke matrix now covers checkout, contact, follow seller, seller notes, and stock alert subscription.
- Production Onessta/CMI credentials, real mail delivery, cron, queue worker, supervisor, Redis/Horizon availability, failed-job cleanup, and storage symlink readiness.
