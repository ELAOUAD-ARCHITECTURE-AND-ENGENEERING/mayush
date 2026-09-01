# Mayush Mobile Buyer MVP - Screen & UX State Contract

## Overview

This document specifies the definitive screen and UX state contract for the Mayush Mobile buyer-only application MVP. Every screen, state, modal, sheet, and state variation is mapped directly to source screenshots from `mayush-mobile/design-reference/mayush-mobile-design/`.

---

## 1. Classification Totals

### Main MVP Screen & State Contract (32 Rows)
- **`PRIMARY_SCREEN`**: 15 rows
- **`SCREEN_STATE`**: 1 row
- **`BOTTOM_SHEET`**: 2 rows
- **`MODAL`**: 1 row
- **`DIALOG`**: 1 row
- **`TOAST`**: 1 row
- **`LOADING_STATE`**: 3 rows
- **`EMPTY_STATE`**: 1 row
- **`ERROR_STATE`**: 5 rows
- **`SUCCESS_STATE`**: 2 rows

### Arabic RTL Reference Mappings (8 Separate Items)
- `11-arabic-rtl/11-language-selection-ar.png`
- `11-arabic-rtl/11-home-ar.png`
- `11-arabic-rtl/11-categories-ar.png`
- `11-arabic-rtl/11-product-detail-ar.png`
- `11-arabic-rtl/11-cart-ar.png`
- `11-arabic-rtl/11-checkout-address-ar.png`
- `11-arabic-rtl/11-payment-method-ar.png`
- `11-arabic-rtl/11-payment-success-ar.png`

---

## 2. Screen & State Records

| Contract ID | Source Screenshot Filename | Feature | Screen / State Name | Classification | Guest Access | Auth Required |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `SCR-ENT-001` | `01-entry/01-splash-screen-logo.png` | Entry | Splash Screen | `PRIMARY_SCREEN` | Yes | No |
| `SCR-ENT-002` | `01-entry/01-language-selection-french-arabic.png` | Entry | Language Selection | `PRIMARY_SCREEN` | Yes | No |
| `SCR-DIS-001` | `02-discovery/02-home-hero-new-arrivals-best-sellers-fr.png` | Discovery | Home Screen | `PRIMARY_SCREEN` | Yes | No |
| `SCR-DIS-002` | `02-discovery/02-categories-photo-grid-fr.png` | Discovery | Categories Screen | `PRIMARY_SCREEN` | Yes | No |
| `SCR-DIS-003` | `02-discovery/02-subcategory-canapes-filtered-list.png` | Discovery | Category Product List | `PRIMARY_SCREEN` | Yes | No |
| `SCR-PRD-001` | `03-product/03-product-detail-image-carousel-add-to-cart.png` | Product | Product Details | `PRIMARY_SCREEN` | Yes | No |
| `SCR-PRD-002` | `03-product/03-product-variant-selector-color-material-size.png` | Product | Variant Selector | `BOTTOM_SHEET` | Yes | No |
| `SCR-PRD-003` | `03-product/03-product-added-to-cart-confirmation.png` | Product | Added To Cart Banner | `BOTTOM_SHEET` | Yes | No |
| `SCR-CRT-001` | `05-cart-wishlist/05-cart-items-promo-code-summary-fr.png` | Cart | Cart Screen | `PRIMARY_SCREEN` | Yes | No |
| `SCR-CRT-002` | `05-cart-wishlist/05-cart-empty-state-fr.png` | Cart | Cart Empty State | `EMPTY_STATE` | Yes | No |
| `SCR-CRT-003` | `05-cart-wishlist/05-cart-quantity-update-toast-fr.png` | Cart | Quantity Update | `TOAST` | Yes | No |
| `SCR-CRT-004` | `05-cart-wishlist/05-cart-remove-item-confirmation-dialog-fr.png` | Cart | Remove Item Dialog | `DIALOG` | Yes | No |
| `SCR-CRT-005` | `05-cart-wishlist/05-cart-skeleton-loading-state.png` | Cart | Cart Loading Skeleton | `LOADING_STATE` | Yes | No |
| `SCR-CRT-006` | `05-cart-wishlist/05-cart-error-loading-state-fr.png` | Cart | Cart Error State | `ERROR_STATE` | Yes | No |
| `SCR-ATH-001` | `04-auth/04-welcome-sign-in-create-account-guest-fr.png` | Auth Gate | Welcome Auth Gateway | `PRIMARY_SCREEN` | Yes | No |
| `SCR-ATH-002` | `04-auth/04-login-email-phone-password-fr.png` | Auth Gate | Customer Login | `PRIMARY_SCREEN` | Yes | **At Checkout** |
| `SCR-ATH-003` | `04-auth/04-registration-form-fr.png` | Auth Gate | Customer Registration | `PRIMARY_SCREEN` | Yes | **At Checkout** |
| `SCR-ATH-004` | `04-auth/04-otp-phone-verification-fr.png` | Auth Gate | OTP Phone Verification | `SCREEN_STATE` | Conditional | Conditional |
| `SCR-ATH-005` | `04-auth/04-account-created-success-fr.png` | Auth Gate | Registration Success | `SUCCESS_STATE` | No | Yes |
| `SCR-CHK-001` | `06-checkout/06-choose-address-saved-list-fr.png` | Checkout | Select Address | `PRIMARY_SCREEN` | No | **Yes** |
| `SCR-CHK-002` | `06-checkout/06-add-new-address-form-fr.png` | Checkout | Add New Address | `PRIMARY_SCREEN` | No | **Yes** |
| `SCR-CHK-003` | `06-checkout/06-choose-delivery-standard-express-relay-fr.png` | Checkout | Select Delivery Method | `PRIMARY_SCREEN` | No | **Yes** |
| `SCR-CHK-004` | `06-checkout/06-choose-payment-cmi-cod-wallet-fr.png` | Checkout | Select Payment Method | `PRIMARY_SCREEN` | No | **Yes** |
| `SCR-CHK-005` | `06-checkout/06-order-review-confirm-multi-vendor-fr.png` | Checkout | Order Review | `PRIMARY_SCREEN` | No | **Yes** |
| `SCR-CHK-006` | `06-checkout/06-secure-payment-redirect-fr.png` | Checkout | CMI Gateway Modal | `MODAL` | No | **Yes** |
| `SCR-CHK-007` | `06-checkout/06-payment-verification-processing-fr.png` | Checkout | Payment Processing | `LOADING_STATE` | No | **Yes** |
| `SCR-CHK-008` | `06-checkout/06-payment-failed-retry-fr.png` | Checkout | Payment Failed | `ERROR_STATE` | No | **Yes** |
| `SCR-CHK-009` | `06-checkout/06-order-thank-you-confirmation-summary-fr.png` | Checkout | Order Confirmation | `SUCCESS_STATE` | No | **Yes** |
| `SCR-SYS-001` | `10-system-states/10-content-loading-skeleton-fr.png` | System | Content Loading | `LOADING_STATE` | Yes | No |
| `SCR-SYS-002` | `10-system-states/10-generic-error-fr.png` | System | Generic Error | `ERROR_STATE` | Yes | No |
| `SCR-SYS-003` | `10-system-states/10-offline-fr.png` | System | Offline Screen | `ERROR_STATE` | Yes | No |
| `SCR-SYS-004` | `10-system-states/10-session-expired-fr.png` | System | Session Expired | `ERROR_STATE` | No | Yes |

---

## 3. Order Identifier Mapping Note

- `POST /api/v2/order/store` returns `{combined_order_id}`.
- `GET /api/v2/purchase-history-details/{id}` expects `{order_id}` (individual seller order ID).
- For order confirmation rendering (`SCR-CHK-009`), the app queries `GET /api/v2/purchase-history` to locate the individual `{order_id}` associated with `{combined_order_id}`.
