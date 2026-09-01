# Mayush Mobile Buyer MVP - Acceptance Criteria

## Overview

This document specifies testable acceptance criteria (Given / When / Then format) for every critical user flow in the Mayush Mobile application MVP.

---

## 1. Application Entry & Language Selection
- **Given**: App launches for the first time.
- **When**: User selects "Français" or "العربية".
- **Then**: 
  - Choice is saved in persistent storage.
  - App language header `App-Language` is set to `fr` or `ar`.
  - UI layout adopts LTR for French and true RTL for Arabic.
  - App navigates to Home screen (`SCR-DIS-001`).
- **Backend Evidence**: `AppLanguage` middleware (`$request->header('App-Language')`).
- **Design Reference**: `01-entry/01-language-selection-french-arabic.png`.

---

## 2. Guest Product Discovery & Variant Selection
- **Given**: Unauthenticated guest user on Product Details screen (`SCR-PRD-001`).
- **When**: User selects a color or size variant option.
- **Then**: 
  - App sends `POST /api/v2/products/variant/price` with slug, color, variants, and quantity.
  - UI updates display price, stock quantity, and stock status badge from backend response.
  - "Add to Cart" button is disabled if `in_stock == 0`.
- **Backend Evidence**: `ProductController@getPrice`.
- **Design Reference**: `03-product/03-product-variant-selector-color-material-size.png`.

---

## 3. Guest Cart Persistence & Token Management
- **Given**: Guest user taps "Add to Cart".
- **When**: App sends `POST /api/v2/carts/add`.
- **Then**: 
  - If local `temp_user_id` is missing, backend generates and returns `'temp_user_id' => '...'`.
  - App saves returned `temp_user_id` in `AsyncStorage`.
  - Added-to-cart confirmation banner (`SCR-PRD-003`) appears.
  - Cart header count badge increments.
- **Backend Evidence**: `CartController@add`.
- **Design Reference**: `03-product/03-product-added-to-cart-confirmation.png`.

---

## 4. Checkout Authentication Gate & Cart Merge
- **Given**: Guest user has items in cart and taps "Proceed to Checkout" on Cart Screen (`SCR-CRT-001`).
- **When**: User is intercepted by Auth Gate (`SCR-ATH-001`) and completes Login (`SCR-ATH-002`) or Signup (`SCR-ATH-003`).
- **Then**: 
  - App sends stored `temp_user_id` in login/signup request body.
  - Server automatically reassigns all cart items matching `temp_user_id` to `$user->id`.
  - Client receives Sanctum `access_token`, verifies merged cart via `POST /api/v2/carts`, and clears `temp_user_id`.
  - User is NOT redirected to Home; app seamlessly resumes checkout at Address Selection (`SCR-CHK-001`).
- **Backend Evidence**: `AuthController@loginSuccess`.
- **Design Reference**: `04-auth/04-welcome-sign-in-create-account-guest-fr.png`.

---

## 5. Address & Delivery Selection
- **Given**: Authenticated customer on Checkout Address Screen (`SCR-CHK-001`).
- **When**: Customer selects an address and taps "Next".
- **Then**: 
  - App sends `POST /api/v2/update-address-in-cart` with `address_id`.
  - Backend links address to active cart items.
  - App proceeds to Delivery Selection (`SCR-CHK-003`).
- **Backend Evidence**: `AddressController@updateAddressInCart`.
- **Design Reference**: `06-checkout/06-choose-address-saved-list-fr.png`.

---

## 6. Order Creation & CMI Gateway Payment
- **Given**: Customer on Order Review Screen (`SCR-CHK-005`) with payment method set to `cmi`.
- **When**: Customer taps "Confirm Order".
- **Then**: 
  - App sends `POST /api/v2/order/store` with `payment_type: "cmi"`.
  - Server calculates authoritative totals, creates `CombinedOrder` (#{combined_order_id}), clears cart, and returns `{ combined_order_id: X, result: true }`.
  - App launches secure CMI Mobile Bridge modal (`SCR-CHK-006`).
  - Customer completes payment on CMI gateway page.
  - Server receives verified SHA-512 callback and updates order status to `paid`.
  - App receives deep link return, verifies order status via API `GET /api/v2/purchase-history-details/{order_id}`, and renders Order Confirmation (`SCR-CHK-009`).
- **Backend Evidence**: `OrderController@store`, `CmiController@callback`.
- **Design Reference**: `06-checkout/06-secure-payment-redirect-fr.png`.
