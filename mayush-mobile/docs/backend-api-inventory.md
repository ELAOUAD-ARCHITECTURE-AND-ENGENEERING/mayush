# Mayush Mobile MVP - Backend API Inventory

> [!NOTE]  
> **DOCUMENT RECONCILIATION NOTICE**: This document has been reconciled by Phase 3. Where an earlier statement conflicts with [`PHASE_3_CONTRACT_LOCK_REPORT.md`](file:///c:/laragon/www/mayush/mayush-mobile/docs/PHASE_3_CONTRACT_LOCK_REPORT.md) or [`DOCUMENT_AUTHORITY.md`](file:///c:/laragon/www/mayush/mayush-mobile/docs/DOCUMENT_AUTHORITY.md), the Phase 3 locked contract is authoritative.

## Overview

This document catalogs every API route exposed by the Mayush Laravel backend (`/api/v2/...`) relevant to the buyer-only mobile application MVP flow.

---

## 1. Authentication & User Profile Routes

| Method | Endpoint | Auth | Middleware | Purpose / Description |
| :--- | :--- | :--- | :--- | :--- |
| `POST` | `/api/v2/auth/login` | Public | `AppLanguage`, `throttle:login` | Authenticate customer with email/phone & password. Supports `temp_user_id` for cart merge. |
| `POST` | `/api/v2/auth/signup` | Public | `AppLanguage` | Register new customer. Supports `temp_user_id` for guest cart merge. |
| `POST` | `/api/v2/auth/social-login` | Public | `AppLanguage` | `FUTURE_PHASE_BACKEND_CAPABILITY`: Excluded from first mobile MVP scope. |
| `POST` | `/api/v2/guest-user-account-create` | Public | `AppLanguage` | Instant account creation for guest checkout flow. |
| `GET` | `/api/v2/auth/user` | Required | `auth:sanctum` | Get current authenticated user details. |
| `GET` | `/api/v2/auth/logout` | Required | `auth:sanctum` | Revoke current Sanctum access token. |
| `GET` | `/api/v2/auth/resend_code` | Required | `auth:sanctum` | Resend verification code (email or SMS). |
| `POST` | `/api/v2/auth/confirm_code` | Required | `auth:sanctum` | Submit OTP / email verification code. |
| `POST` | `/api/v2/password/forget_request` | Public | `AppLanguage` | Request password reset code. |
| `POST` | `/api/v2/password/confirm_reset` | Public | `AppLanguage` | Submit new password with reset code. |
| `GET` | `/api/v2/customer/info` | Required | `auth:sanctum` | Retrieve detailed customer profile metadata. |
| `POST` | `/api/v2/profile/update` | Required | `auth:sanctum` | Update customer profile info (name, phone, avatar). |

---

## 2. Catalog & Discovery Routes

| Method | Endpoint | Auth | Middleware | Purpose / Description |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/v2/categories` | Public | `AppLanguage` | Fetch parent or root categories (`parent_id=0`). |
| `GET` | `/api/v2/sub-categories/{id}` | Public | `AppLanguage` | Fetch subcategories by parent category ID. |
| `GET` | `/api/v2/category/info/{slug}` | Public | `AppLanguage` | Get single category details by slug. |
| `GET` | `/api/v2/categories/featured` | Public | `AppLanguage` | List featured categories for home screen. |
| `GET` | `/api/v2/categories/home` | Public | `AppLanguage` | List home grid categories. |
| `GET` | `/api/v2/products` | Public | `AppLanguage` | Paginated product listing. |
| `GET` | `/api/v2/products/{id}` | Public | `AppLanguage` | Fetch detailed product information by ID. |
| `GET` | `/api/v2/products/{slug}/{user_id?}`| Public | `AppLanguage` | Detailed product info by slug. |
| `GET` | `/api/v2/products/search` | Public | `AppLanguage` | Filter products by keyword, category, brand, price range, sorting. |
| `POST` | `/api/v2/products/variant/price` | Public | `AppLanguage` | Calculate dynamic variant price, tax, and stock availability. |
| `GET` | `/api/v2/products/category/{slug}` | Public | `AppLanguage` | List products under specific category. |
| `GET` | `/api/v2/products/brand/{slug}` | Public | `AppLanguage` | List products under specific brand. |
| `GET` | `/api/v2/products/todays-deal` | Public | `AppLanguage` | Fetch Today's Deal products. |
| `GET` | `/api/v2/products/featured` | Public | `AppLanguage` | Fetch featured products list. |
| `GET` | `/api/v2/products/best-seller` | Public | `AppLanguage` | Fetch best-selling products list. |
| `GET` | `/api/v2/flash-deals` | Public | `AppLanguage` | List active flash deal campaigns. |
| `GET` | `/api/v2/flash-deal-products/{id}` | Public | `AppLanguage` | Products participating in flash deal campaign. |
| `GET` | `/api/v2/sliders` | Public | `AppLanguage` | Home screen slider banners. |
| `GET` | `/api/v2/banners-one` | Public | `AppLanguage` | Home screen promotional banners section 1. |
| `GET` | `/api/v2/banners-two` | Public | `AppLanguage` | Home screen promotional banners section 2. |
| `GET` | `/api/v2/banners-three` | Public | `AppLanguage` | Home screen promotional banners section 3. |
| `GET` | `/api/v2/languages` | Public | `AppLanguage` | List enabled application languages. |
| `GET` | `/api/v2/currencies` | Public | `AppLanguage` | List enabled currencies and exchange rates. |
| `GET` | `/api/v2/brands` | Public | `AppLanguage` | List all product brands. |

---

## 3. Cart Routes

| Method | Endpoint | Auth | Middleware | Purpose / Description |
| :--- | :--- | :--- | :--- | :--- |
| `POST` | `/api/v2/carts` | Public / Guest | `AppLanguage` | Get active cart items grouped by seller (`user_id` or `temp_user_id`). |
| `POST` | `/api/v2/carts/add` | Public / Guest | `AppLanguage` | Add product/variant to cart. Generates `temp_user_id` if guest and absent. |
| `POST` | `/api/v2/carts/change-quantity`| Public / Guest | `AppLanguage` | Update cart item quantity. |
| `DELETE`| `/api/v2/carts/{id}` | Public / Guest | `AppLanguage` | Delete item from cart by cart item ID. |
| `POST` | `/api/v2/cart-summary` | Public / Guest | `AppLanguage` | Get subtotal, tax, shipping, discount, and grand total. |
| `POST` | `/api/v2/cart-count` | Public / Guest | `AppLanguage` | Get total count of cart items. |

---

## 4. Checkout & Address Routes

| Method | Endpoint | Auth | Middleware | Purpose / Description |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/v2/user/shipping/address` | Required | `auth:sanctum` | List saved shipping addresses for customer. |
| `POST` | `/api/v2/user/shipping/create` | Required | `auth:sanctum` | Add new shipping address. |
| `POST` | `/api/v2/user/shipping/update` | Required | `auth:sanctum` | Edit existing shipping address. |
| `GET` | `/api/v2/user/shipping/delete/{id}`| Required | `auth:sanctum` | Delete shipping address. |
| `POST` | `/api/v2/user/shipping/make_default` | Required | `auth:sanctum` | Set default shipping address. |
| `GET` | `/api/v2/countries` | Public | `AppLanguage` | List active shipping countries. |
| `GET` | `/api/v2/states-by-country/{id}` | Public | `AppLanguage` | List states/regions for a country. |
| `GET` | `/api/v2/cities-by-state/{id}` | Public | `AppLanguage` | List cities for a state. |
| `GET` | `/api/v2/areas-by-city/{id}` | Public | `AppLanguage` | List areas/neighborhoods for a city. |
| `POST` | `/api/v2/update-address-in-cart` | Public / Guest | `AppLanguage` | Link shipping address ID to cart. |
| `POST` | `/api/v2/update-shipping-type-in-cart` | Public / Guest | `AppLanguage` | Update delivery method (`home_delivery`, `pickup_point`, `carrier`). |
| `POST` | `/api/v2/coupon-apply` | Required / Guest | `AppLanguage` | Validate and apply promo coupon code. |
| `POST` | `/api/v2/coupon-remove` | Required / Guest | `AppLanguage` | Remove promo coupon code. |
| `POST` | `/api/v2/order/store` | Required | `auth:sanctum` | Finalize checkout and store `CombinedOrder`. |

---

## 5. Payment Routes

| Method | Endpoint | Auth | Middleware | Purpose / Description |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/v2/payment-types` | Public | `AppLanguage` | List available payment methods (CMI, COD, Wallet). |
| `POST` | `/api/v2/payments/pay/cod` | Required | `auth:sanctum` | Confirm Cash On Delivery payment. |
| `POST` | `/api/v2/payments/pay/wallet` | Required | `auth:sanctum` | Pay using customer account wallet balance. |
| `GET` | Secure CMI Mobile Bridge | Session / Web | `throttle:checkout-submit` | `REQUIRES_SECURE_MOBILE_BRIDGE`: CMI Hosted Payment Page entrypoint. |
| `POST` | `/cmi/callback` | Server Webhook | `throttle:cmi-webhook`, `cmi.ip-whitelist` | CMI server-to-server transaction notification webhook. |
| `GET/POST` | `/cmi/success` | Browser Redirect | Public | CMI success redirect return URL. |
| `GET/POST` | `/cmi/fail` | Browser Redirect | Public | CMI failure redirect return URL. |

---

## 6. Orders & Purchase History Routes

| Method | Endpoint | Auth | Middleware | Purpose / Description |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/v2/purchase-history` | Required | `auth:sanctum` | Paginated list of customer orders (`Order` items). |
| `GET` | `/api/v2/purchase-history-details/{id}`| Required | `auth:sanctum` | Detailed summary of specific order by individual `order_id`. |
| `GET` | `/api/v2/purchase-history-items/{id}` | Required | `auth:sanctum` | List items contained within specific order by individual `order_id`. |
| `GET` | `/api/v2/order/cancel/{id}` | Required | `auth:sanctum` | Cancel pending unpaid order. |
| `GET` | `/api/v2/invoice/download/{id}` | Required | `auth:sanctum` | Download order PDF invoice. |
