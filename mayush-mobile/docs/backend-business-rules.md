# Mayush Mobile MVP - Backend Business Rules

> [!NOTE]  
> **DOCUMENT RECONCILIATION NOTICE**: This document has been reconciled by Phase 3. Where an earlier statement conflicts with [`PHASE_3_CONTRACT_LOCK_REPORT.md`](file:///c:/laragon/www/mayush/mayush-mobile/docs/PHASE_3_CONTRACT_LOCK_REPORT.md) or [`DOCUMENT_AUTHORITY.md`](file:///c:/laragon/www/mayush/mayush-mobile/docs/DOCUMENT_AUTHORITY.md), the Phase 3 locked contract is authoritative.

## Overview

This document specifies the authoritative business rules, validation constraints, and state transitions enforced by the Mayush Laravel backend. The mobile application **MUST strictly adhere to these rules** and must never bypass or re-implement backend logic locally.

---

## 1. Authoritative Financial & Pricing Rules

> [!IMPORTANT]  
> The Mayush Laravel backend is the sole source of truth for all financial figures (item prices, variant add-ons, taxes, GST, shipping fees, discounts, and grand totals). The mobile application MUST NOT calculate authoritative totals client-side.

1. **Price Display & Formatting**:
   - Item prices returned by `/api/v2/products` or `/api/v2/products/variant/price` are calculated considering wholesale tiers, active discounts, flash deal promotions, and product tax rules.
   - Display strings (e.g. `150.00 MAD`) formatted by `single_price()` or `format_price()` should be rendered directly in UI.

2. **Cart Totals & Calculations**:
   - Cart subtotal, shipping cost, tax, GST, coupon discount, and grand total MUST be retrieved from `POST /api/v2/cart-summary`.
   - Client-side cart screens may show temporary estimates for responsive UI feedback, but checkout confirmation MUST display the exact response from `cart-summary`.

3. **Minimum Order Amount**:
   - If `get_setting('minimum_order_amount_check') == 1`, the backend rejects `POST /api/v2/order/store` if active cart subtotal is less than `get_setting('minimum_order_amount')`.
   - Response status: `422 Unprocessable Entity`. Message: `"Your order amount is less than the minimum order amount"`.

---

## 2. Inventory, Stock & Variant Rules

1. **Minimum Purchase Quantity**:
   - Every product has a `min_qty` attribute (e.g., 1, 2, 5).
   - `CartController@add` and `@changeQuantity` validate `$request->quantity >= $product->min_qty`.

2. **Stock Availability & Variant Matching**:
   - Products with variants require matching `color` and `choice` combinations to resolve a `ProductStock` record.
   - If stock quantity `$product_stock->qty` is insufficient, backend responds with `result: false` and message `"Only X item(s) are available"`.
   - Digital products (`$product->digital == 1`) bypass physical stock checks.

---

## 3. Cart & Continuity Business Rules

1. **Guest Cart Identification & Lifecycle**:
   - Unauthenticated guest carts are identified by `temp_user_id`.
   - Laravel generates `temp_user_id` on first add when absent; mobile stores exact returned value in `AsyncStorage`.
   - `temp_user_id` MUST be passed in body parameters for all guest cart operations (`/carts`, `/carts/add`, `/cart-summary`, `/cart-count`).

2. **Automatic Cart Merge & Clearance**:
   - Upon customer sign-in (`POST /api/v2/auth/login`) or registration (`POST /api/v2/auth/signup`), the client passes `temp_user_id`.
   - The backend atomically updates all cart rows:
     ```sql
     UPDATE carts SET user_id = :userId, temp_user_id = NULL WHERE temp_user_id = :tempUserId;
     ```
   - Mobile application verifies merged cart via `POST /api/v2/carts` before clearing `temp_user_id` from local storage (`GuestCartIdentityState`).

---

## 4. Checkout & Order Lifecycle Rules

1. **Authentication Requirement**:
   - While guest browsing and guest cart creation are permitted, **authentication is strictly required before order creation**.
   - `POST /api/v2/order/store` is protected by `auth:sanctum` middleware.

2. **Address & Shipping Selection**:
   - A valid shipping address ID (`address_id`) must be associated with cart items via `POST /api/v2/update-address-in-cart`.
   - Shipping type (`home_delivery`, `pickup_point`, or `carrier`) must be saved via `POST /api/v2/update-shipping-type-in-cart` before order placement.

3. **Multi-Vendor Order Generation (`CombinedOrder`)**:
   - `POST /api/v2/order/store` creates a `CombinedOrder` master record and one or more `Order` records (one per seller).
   - Response returns `{ combined_order_id: X }`.
   - Order detail lookups (`GET /api/v2/purchase-history-details/{id}`) expect the individual `Order` primary key (`orders.id`).

4. **Order Status Transitions**:
   - **Cash On Delivery (COD)**: Payment status set to `unpaid`, delivery status set to `pending`, stock deducted immediately.
   - **Wallet**: If balance sufficient, payment status set to `paid`, stock deducted.
   - **CMI Credit Card**: Classified as `REQUIRES_SECURE_MOBILE_BRIDGE` (Release Dependency). Sanctum Bearer tokens MUST NOT be passed in URL query strings. Status verified via `GET /api/v2/purchase-history-details/{order_id}`.

---

## 5. Payment & Security Rules

1. **Allowed Payment Methods**:
   - Enforced allowed payment types: `"cash_on_delivery"`, `"cmi"`, `"wallet"`.
