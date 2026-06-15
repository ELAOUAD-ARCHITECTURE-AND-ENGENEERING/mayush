# Mayush Database Performance Hardening

This document records the database indexing optimizations applied to the Mayush Marketplace in June 2026.

## Context

The Mayush platform has grown, and high-volume critical tables (products, orders, carts, settings) were lacking critical B-Tree indexes, resulting in full table scans during common operations (e.g. cart validation, order loading, checking addon activations).

## Additions

The `2026_06_10_140619_add_performance_indexes_to_mayush_core_tables` migration safely introduces the following critical indexes:

### 1. `business_settings`
- Added `type` index. 
- Over 128 occurrences of `BusinessSetting::where('type', ...)` existed. Although many use `get_setting()` caching, raw calls were doing full table scans.

### 2. `product_stocks`
- Added `product_id` index.
- Critical for adding to cart, loading variations on the Product Detail Page, and restoring stock on stale payment expiration.

### 3. `orders`
- Added `(user_id, created_at)` for customer dashboards.
- Added `(seller_id, created_at)` for seller dashboards.
- Added `(seller_id, payment_status)` for filtering seller orders.
- Added `(payment_status, delivery_status, created_at)` for broad administrative audits.
- Added `(combined_order_id, payment_status)` for multi-vendor checkout validation.

### 4. `order_details`
- Added `order_id` index (critical for eager loading `orders.orderDetails`).
- Added `product_id` index (critical for product ratings and stock audits).

### 5. `products`
- Added `slug` index for O(1) storefront product lookups.
- Added `(user_id, published, approved)` for seller storefront pages.
- Added `(brand_id, published, approved)` for brand catalog pages.

### 6. Payment Tables
- Added composite/single indexes on `payment_attempts` (`gateway_reference`, `merchant_reference`, `(status, created_at)`).
- Added composite/single indexes on `cmi_callback_logs` (`merchant_reference`, `payload_hash`, `(processing_status, created_at)`).

## Known Technical Debt (Duplicate Indexes)

The `products` table still contains legacy duplicate indexes (e.g., `created_at_2`, `created_at_3`, `unit_price_2`, `unit_price_3`). These were deliberately **not dropped** in this phase. 

**Future Cleanup Action:** 
A dedicated cleanup sprint should run `SHOW INDEX FROM products` to confirm these are exact B-Tree redundancies (not distinct prefixes or FULLTEXT) before safely dropping them, minimizing locking risks on the largest table in the platform.
