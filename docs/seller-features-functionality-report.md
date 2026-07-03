# Seller Features And Functionality Report

Date: 2026-06-03

## Executive Summary

Mayush has a broad multi-vendor seller system with web dashboard routes, seller mobile/API routes, admin seller-management workflows, storefront shop pages, preorder seller flows, and optional modules for POS, GST, wholesale, auction, refunds, subscriptions, coupons, digital products, and Elite seller profiles.

The strongest areas are feature breadth, seller onboarding status modeling, seller dashboard metrics, product listing ownership checks in several read/delete paths, private seller notes, storefront shop filtering, and test coverage around notes, destructive routes, order tracking, seller approval, and product workflows.

The highest-risk areas are missing or inconsistent seller ownership checks in some write/detail actions, especially seller order status updates, product update/toggle endpoints, and seller coupon edit/update/delete. These should be hardened before expanding seller self-service.

## Seller Entry And Access Control

The seller web area is mounted under `/seller` in `routes/seller.php`. Most routes require `seller`, `verified`, `user`, and `prevent-back-history` middleware. Product-management routes add the `seller.approved` middleware, which blocks pending, rejected, or under-review sellers from managing products.

Relevant files:

- `routes/seller.php`
- `app/Http/Middleware/IsSeller.php`
- `app/Http/Middleware/SellerApproved.php`
- `app/Models/Shop.php`

Current access behavior:

- Only authenticated users with `user_type == seller` and not banned pass the seller middleware.
- Product creation, editing, delete, bulk upload, digital products, and custom labels require the shop `approval_status` to be `approved`.
- Pending or rejected sellers are redirected to seller onboarding.
- Seller-sensitive shop fields such as `bank_name`, `bank_info`, `business_info`, and `verification_info` use the `SafeEncrypted` cast in `Shop`.

## Seller Onboarding And Approval

The seller onboarding flow supports document upload and admin review:

- Seller onboarding page.
- Contract download.
- Mandatory upload of signed contract, government ID, and business registration.
- Optional certification upload.
- Documents are stored under `private/seller-documents`.
- Shop status changes to `under_review` after upload.
- Admin can approve or reject seller applications.
- Audit logs are written for document submission and admin approval/rejection.
- Seller and admin email/in-app notifications exist for application events.

Relevant files:

- `app/Http/Controllers/Seller/OnboardingController.php`
- `app/Http/Controllers/SellerController.php`
- `app/Models/SellerDocument.php`
- `database/migrations/2026_05_06_100001_add_seller_onboarding_fields_to_shops_table.php`
- `database/migrations/2026_05_06_100002_create_seller_documents_table.php`

Observation:

- There is a bug in resubmission counting. `OnboardingController::upload()` sets `approval_status` to `under_review` before checking whether it was previously `rejected`, so `resubmission_count` will not increment as intended.

## Seller Dashboard And Analytics

Seller dashboard functionality includes:

- Current-month order counts by status: pending, cancelled, on the way, delivered.
- Current-month and previous-month paid sales totals.
- Top products by sales.
- Total views, total sale count, and average conversion rate.
- Low-stock product insights.
- High-view, low-conversion product insights.
- Inventory velocity over the last 7 days.
- Sales and views trend data for the last 7 days.
- Predictive stockout estimate for products likely to run out within 7 days.

The dedicated analytics dashboard also exposes JSON endpoints for:

- Visitor stats.
- Funnel stats.
- Top products.
- Revenue trend.
- Financial summary.
- Geographic order stats.
- Projected earnings.

Relevant files:

- `app/Http/Controllers/Seller/DashboardController.php`
- `app/Http/Controllers/Seller/AnalyticsDashboardController.php`
- `app/Services/SellerFinancialService.php`
- `resources/views/seller/dashboard.blade.php`
- `resources/views/seller/analytics/dashboard.blade.php`

Observation:

- Seller top-product analytics correctly filters products by seller.
- General visitor/funnel stats call `AnalyticsService` without an obvious seller filter in the controller, so verify that service scopes by seller before treating those metrics as seller-private.
- Projected net earnings assumes a 10% commission instead of using the configured seller/category commission rules.

## Product Management

Seller product management is extensive:

- Product listing with search.
- Product create/edit/update/delete.
- Product duplication.
- Publish/unpublish toggle.
- Seller featured toggle.
- Variant/SKU combination generation.
- Category assignment.
- Stock creation and updates.
- Product tax/GST handling.
- Frequently bought product associations.
- Product translations.
- Category-wise product discount.
- Product search for internal selection widgets.
- Bulk product upload with category/brand template downloads.
- Optional digital product management.
- Optional custom labels.

Relevant files:

- `app/Http/Controllers/Seller/ProductController.php`
- `app/Http/Controllers/Seller/ProductBulkUploadController.php`
- `app/Http/Controllers/Seller/DigitalProductController.php`
- `app/Http/Controllers/Seller/CustomLabelController.php`
- `app/Services/ProductService.php`
- `app/Services/ProductStockService.php`
- `resources/views/seller/product/products/*`
- `resources/views/seller/product/product_bulk_upload/index.blade.php`

Strengths:

- Product list, edit, duplicate, and delete are seller-scoped in key paths.
- Product creation respects seller subscription and GST verification when those addons are active.
- Product create notifies admins when admin product approval is enabled.

Risks:

- `ProductController::update()` accepts a route-bound product but does not check ownership before updating.
- `updatePublished()` and `updateFeatured()` load by product ID and do not check seller ownership.
- `get_selected_products()` loads arbitrary product IDs without seller scoping.

## Order Management

Seller order functionality includes:

- Order list filtered to the current seller.
- Filters by payment status, delivery status, and order code search.
- Order details page.
- Delivery status updates.
- Payment status updates.
- Order export to Excel.
- Invoice download through seller invoice route.
- Notifications by email, web notification, SMS, and Firebase where enabled.
- Restocking when cancelling seller order details.
- Commission calculation when payment becomes paid.

Relevant files:

- `app/Http/Controllers/Seller/OrderController.php`
- `app/Http/Controllers/Seller/InvoiceController.php`
- `resources/views/seller/orders/index.blade.php`
- `resources/views/seller/orders/show.blade.php`

High-priority risk:

- `OrderController::show()` decrypts and loads an order without verifying `order.seller_id == Auth::id()`.
- `update_delivery_status()` and `update_payment_status()` load an arbitrary order ID and mutate order-level status before or while only filtering order details by current seller.
- These endpoints need explicit seller ownership checks before changing or showing any order.

## Shop, Profile, Branding, And Storefront

Seller shop settings include:

- Shop name, address, phone, slug, logo, meta title, meta description.
- Shipping cost.
- Pickup latitude/longitude.
- Social links.
- Banner and slider assets.
- Business certificate information.
- Seller photo, ID card, and live selfie upload.
- GST document upload when GST addon is active.
- Storytelling fields: artisan story, brand philosophy, workshop video URL, story title/content, hero media, artisan quote, gallery.
- Category-wise commission display.

Storefront behavior includes:

- Public shop page by slug.
- Unverified shop fallback view.
- Shop product filtering by brand, category, price, rating, and sort order.
- Preorder shop-product filtering when preorder products are enabled.
- All-seller listing limited to verified sellers.

Relevant files:

- `app/Http/Controllers/Seller/ShopController.php`
- `app/Http/Controllers/FrontendShopController.php`
- `resources/views/seller/shop.blade.php`
- `resources/views/frontend/seller_shop.blade.php`
- `resources/views/frontend/seller_shop_without_verification.blade.php`
- `resources/views/frontend/shop_listing.blade.php`

## Financials, Commission, Payments, And Withdrawals

Seller financial features include:

- Payment history list scoped to the seller.
- Commission history list scoped to the seller with date filtering.
- Money withdraw request submission.
- Admin payout notifications and seller/admin payout emails.
- Seller financial analytics: gross sales, net earnings, commissions, refunds, order count, payout-ready amount.
- Admin seller-specific commission configuration.
- Admin payment modal and seller profile payment history.

Relevant files:

- `app/Http/Controllers/Seller/PaymentController.php`
- `app/Http/Controllers/Seller/CommissionHistoryController.php`
- `app/Http/Controllers/Seller/SellerWithdrawRequestController.php`
- `app/Services/SellerFinancialService.php`
- `app/Http/Controllers/SellerController.php`

Risks:

- Withdraw requests do not validate amount or compare it to seller payable balance in the controller.
- Projected earnings use a fixed 90% net assumption.

## Coupons And Promotions

Seller coupon features include:

- Coupon list scoped to current seller.
- Coupon create with validated request data.
- Product-based coupon form loading using current seller products.
- Cart-based coupon form loading.
- Coupon edit/update/delete routes.

Relevant files:

- `app/Http/Controllers/Seller/CouponController.php`
- `resources/views/seller/coupons/*`
- `resources/views/partials/coupons/seller/*`

Risks:

- Coupon edit loads by decrypted ID without verifying `user_id`.
- Coupon update uses route model binding without verifying ownership.
- Coupon delete destroys by ID without verifying ownership.
- Edit form helper endpoints load coupons by arbitrary ID.

## Communication, Notes, Support, And Notifications

Seller communication features include:

- Seller/customer conversations when conversation system is enabled.
- Product query listing, detail, and seller reply.
- Support ticket listing, creation, detail view, and replies.
- Notification list, bulk delete, and read-and-redirect behavior.
- Private seller notes.

Relevant files:

- `app/Http/Controllers/Seller/ConversationController.php`
- `app/Http/Controllers/Seller/ProductQueryController.php`
- `app/Http/Controllers/Seller/SupportTicketController.php`
- `app/Http/Controllers/Seller/NotificationController.php`
- `app/Http/Controllers/Seller/NoteController.php`

Strengths:

- Seller notes are scoped so sellers can see their own notes and admin notes marked with seller access.
- Note edit/update/delete are limited to the current seller.
- Tests cover seller note creation, update, delete, and cross-seller access denial.

Observation:

- `NoteController::create()` checks `seller_can_add_note`, but `store()` does not repeat that check, so direct POST should be blocked too if the setting is off.

## Customer-Facing Seller Features

Customer-facing seller functionality includes:

- Public seller shop pages.
- Follow seller.
- Unfollow seller.
- Followed sellers page for customers.
- Product detail seller information.
- "From this seller" product blocks.

Relevant files:

- `app/Http/Controllers/FollowSellerController.php`
- `app/Models/FollowSeller.php`
- `resources/views/frontend/product_details/seller_info.blade.php`
- `resources/views/frontend/product_details/from_this_seller_products.blade.php`

Strengths:

- Follow creation validates the target shop and uses `firstOrCreate` to avoid duplicate follow rows.
- Follow removal is scoped to the current customer and target shop.

## Optional Seller Modules

The seller menu and routes show optional addon-driven modules:

- Preorder seller dashboard, preorder product management, preorder orders, settings, commission history, conversations, product queries, and reviews.
- Wholesale products.
- Auction products and auction orders.
- Seller POS manager and POS configuration.
- Refund request handling.
- GST/HSN assignment.
- Seller subscription packages.
- Classified/promoted products.
- Elite seller profile subscription and profile upgrade.

Relevant files:

- `routes/preorder.php`
- `routes/api_seller.php`
- `resources/views/seller/inc/seller_sidenav.blade.php`
- `app/Http/Controllers/Seller/SellerEliteController.php`

## Seller API

The mobile/API seller surface exists under `/api/v2/seller` with Sanctum auth and app-language middleware.

Capabilities include:

- Order list, details, order items, delivery/payment status updates.
- Shop profile, payment history, commission list, dashboard counters, shop update, verification form.
- Refunds.
- Withdraw requests.
- Product CRUD and lookup metadata.
- Product reviews and product queries.
- Digital products.
- Wholesale products.
- Auction products and auction order list.
- Coupons.
- Conversations.
- Seller packages.
- Seller file upload.
- POS products, customer lookup, cart, shipping address, order placement, and POS configuration.

Relevant file:

- `routes/api_seller.php`

Recommendation:

- Audit API controllers for the same ownership risks found in web seller controllers, especially order status updates, product update/toggle/delete, coupon update/delete, and file deletion.

## Admin Seller Management

Admin seller functionality includes:

- Seller list with search, verification filters, and approval filters.
- Add seller manually.
- Edit seller profile.
- Delete seller and related product data.
- Login as seller.
- Ban/unban seller.
- View pending sellers.
- Approve/reject shop verification.
- Review onboarding documents.
- Approve/reject full seller application with reason.
- Set seller-based commission.
- Edit seller custom follower count.
- Mark seller as suspected.
- View seller profile tabs: overview, products, orders, payments, documents.

Relevant file:

- `app/Http/Controllers/SellerController.php`

## Test Coverage Found

Existing seller-related tests include:

- Seller registration.
- Seller approval workflow.
- Seller note ownership and CRUD.
- Seller analytics dashboard.
- Product variant stock.
- Product image update.
- Classified product path.
- Seller destructive route security.
- Route method security.
- Order tracking seller authorization.
- Browser QA for seller dashboard and seller notes.
- Combined order split and seller commission behavior.
- Frontend shop/seller filtering regression.

Relevant directories:

- `tests/Feature/Seller`
- `tests/Feature/Security`
- `tests/Integration/Controllers`
- `tests/Feature/Frontend`
- `cypress/e2e/promotion_flow.cy.js`

## Priority Recommendations

1. Harden seller ownership checks on all seller order detail and status endpoints.
2. Harden product update, published toggle, featured toggle, and selected-product helper endpoints.
3. Harden seller coupon edit/update/delete/helper endpoints.
4. Add withdraw amount validation against actual available payout balance.
5. Fix seller onboarding resubmission counting.
6. Add tests for cross-seller denial on order show/status update, product update/toggles, and coupon update/delete.
7. Verify analytics service scoping for seller visitor/funnel stats.
8. Replace fixed projected net earnings with configured commission logic.
9. Repeat the authorization audit for `/api/v2/seller` controllers.

## Overall Assessment

The seller system is feature-rich and already supports most marketplace seller operations expected from a multi-vendor ecommerce platform. The main product risk is not missing features; it is inconsistent authorization hardening across some seller write/detail endpoints. Once those ownership checks and related tests are added, the seller module will be in a much better position for expansion into mobile seller workflows, richer analytics, and stronger seller self-service.
