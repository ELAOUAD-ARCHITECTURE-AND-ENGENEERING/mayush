# Mayush Mobile MVP - Backend Capability Audit

> [!NOTE]  
> **DOCUMENT RECONCILIATION NOTICE**: This document has been reconciled by Phase 3. Where an earlier statement conflicts with [`PHASE_3_CONTRACT_LOCK_REPORT.md`](file:///c:/laragon/www/mayush/mayush-mobile/docs/PHASE_3_CONTRACT_LOCK_REPORT.md) or [`DOCUMENT_AUTHORITY.md`](file:///c:/laragon/www/mayush/mayush-mobile/docs/DOCUMENT_AUTHORITY.md), the Phase 3 locked contract is authoritative.

## Executive Summary

An exhaustive technical audit of the Mayush Laravel backend (`v2` API, controllers, services, models, middleware, and payment gateways) was conducted to evaluate readiness for the buyer-only React Native / Expo mobile application. 

The audit reveals that **the backend possesses a robust RESTful API surface (`/api/v2/...`)** built specifically for mobile client consumption, using **Laravel Sanctum** token-based authentication, a **guest cart system with server-side cart merge upon login**, **authoritative calculation of all prices/shipping/taxes**, and **CMI credit card integration**.

---

## 1. Capability Classification Matrix

Each system capability required for the buyer MVP flow is classified according to readiness:

| Category | Capability | Status Classification | Primary Controller / Service |
| :--- | :--- | :--- | :--- |
| **Catalog** | Category & Subcategory Browsing | `READY` | `Api\V2\CategoryController`, `SubCategoryController` |
| **Catalog** | Product Search & Discovery | `READY` | `Api\V2\ProductController` |
| **Catalog** | Product Details & Gallery | `READY` | `Api\V2\ProductController` |
| **Catalog** | Dynamic Variant Pricing & Stock | `READY` | `Api\V2\ProductController@getPrice` |
| **Catalog** | Flash Deals & Banners | `READY` | `Api\V2\FlashDealController`, `SliderController` |
| **Catalog** | Multi-Language Content | `READY` | `AppLanguage` Middleware (`App-Language: fr \| ar`) |
| **Guest Cart** | Guest Cart Session & Token | `READY` | `Api\V2\CartController` (`temp_user_id`) |
| **Guest Cart** | Add / Edit / Remove Items | `READY` | `Api\V2\CartController` |
| **Guest Cart** | Cart Totals & Discounts | `READY` | `Api\V2\CartController@summary` |
| **Auth** | Customer Registration & Login | `READY` | `Api\V2\AuthController` |
| **Auth** | Phone / Email Verification (OTP) | `READY` | `Api\V2\AuthController`, `OTPVerificationController` |
| **Auth** | Customer Profile & Addresses | `READY` | `Api\V2\AddressController`, `ProfileController` |
| **Cart Merge**| Guest-to-Authenticated Cart Merge | `READY` | `Api\V2\AuthController@loginSuccess` (`temp_user_id`) |
| **Checkout** | Delivery Addresses & Regions | `READY` | `Api\V2\AddressController` |
| **Checkout** | Shipping Method & Cost Calculation | `READY` | `Api\V2\ShippingController`, `AddressController` |
| **Checkout** | Coupon Validation & Application | `READY` | `Api\V2\CheckoutController`, `CouponController` |
| **Checkout** | Order Creation (`CombinedOrder`) | `READY` | `Api\V2\OrderController@store` |
| **Payment** | Cash On Delivery (COD) | `READY` | `Api\V2\OrderController@store` (`payment_type: "cash_on_delivery"`) |
| **Payment** | Customer Wallet | `READY_CONDITIONALLY` | `Api\V2\WalletController`, `OrderController` |
| **Payment** | CMI Credit Card Gateway | `REQUIRES_SECURE_MOBILE_BRIDGE` | `Payment\CmiController`, Secure Mobile Bridge (Release Dependency) |
| **Auth (Social)**| Social Login (Google/Apple) | `FUTURE_PHASE_BACKEND_CAPABILITY` | `Api\V2\AuthController@socialLogin` (Excluded from MVP Scope) |
| **Orders** | Order Confirmation & Retrieval | `READY` | `Api\V2\PurchaseHistoryController` |

---

## 2. Reconciled Capability Records

### 2.1 Multi-Language Header Contract
- **Status**: `READY`
- **Canonical Header**: `App-Language: fr | ar` (Supported backend languages: `fr | ar | en`).
- **Selectable MVP Languages**: `fr | ar`.

### 2.2 Guest Cart & Token Lifecycle
- **Status**: `READY`
- **Canonical Lifecycle**:
  - App launch reads existing `temp_user_id` from `AsyncStorage`.
  - App does NOT generate `temp_user_id` client-side when absent.
  - On first guest `POST /api/v2/carts/add`, `temp_user_id` is omitted if absent; Laravel generates and returns `temp_user_id`.
  - Mobile stores exact returned `temp_user_id`.
  - On login/signup, mobile sends stored `temp_user_id` in request body.
  - Server reassigns cart items (`user_id = X, temp_user_id = NULL`).
  - Mobile receives auth token, queries `POST /api/v2/carts` to verify merged cart, and clears `temp_user_id` ONLY after merge verification.

### 2.3 Verified Payment Types
- **Status**: `READY`
- **Canonical Identifiers**:
  - `"cash_on_delivery"`
  - `"cmi"`
  - `"wallet"`

### 2.4 CMI Payment Integration & Web Adapter
- **Status**: `REQUIRES_SECURE_MOBILE_BRIDGE`
- **Release Dependency**: A secure mobile web bridge must be implemented prior to production release (documented in `CMI_MOBILE_BRIDGE_REQUIREMENTS.md`).
- **Security Rule**: Sanctum Bearer tokens (`?token=...`) MUST NEVER be passed in URL query strings.
