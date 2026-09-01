# Mayush Mobile Buyer MVP - Visible Design Feature Classification

## Overview

This document audits visual features shown in source design screenshots (`mayush-mobile-design/`) and classifies their backend data availability.

---

## 1. Feature Classification Table

| Design Feature | Source Screenshot | Classification | Backend Support / Implementation Decision |
| :--- | :--- | :--- | :--- |
| **French & Arabic Language Selector** | `01-entry/01-language-selection-french-arabic.png` | `SUPPORTED` | Native `App-Language` header passing (`fr` \| `ar`). French LTR and Arabic RTL layouts. |
| **Home Hero Banners** | `02-discovery/02-home-hero-new-arrivals-best-sellers-fr.png` | `SUPPORTED` | `GET /api/v2/sliders` and `GET /api/v2/banners-one`. |
| **Category Photo Grid** | `02-discovery/02-categories-photo-grid-fr.png` | `SUPPORTED` | `GET /api/v2/categories?parent_id=0`. |
| **Product Variants (Color/Size)** | `03-product/03-product-variant-selector-color-material-size.png` | `SUPPORTED` | `POST /api/v2/products/variant/price`. |
| **Guest Cart Persistence** | `05-cart-wishlist/05-cart-items-promo-code-summary-fr.png` | `SUPPORTED` | `temp_user_id` stored in `AsyncStorage`. |
| **Cart Merge on Login** | `04-auth/04-welcome-sign-in-create-account-guest-fr.png` | `SUPPORTED` | Native server merge in `AuthController@loginSuccess`. |
| **CMI Credit Card Payment** | `06-checkout/06-choose-payment-cmi-cod-wallet-fr.png` | `REQUIRES_SECURE_MOBILE_BRIDGE` | Documented release dependency. WebBrowser modal targeting CMI mobile bridge route. |
| **Cash On Delivery (COD)** | `06-checkout/06-choose-payment-cmi-cod-wallet-fr.png` | `SUPPORTED` | `payment_type: "cash_on_delivery"`. |
| **Customer Wallet Payment** | `06-checkout/06-choose-payment-cmi-cod-wallet-fr.png` | `SUPPORTED_CONDITIONALLY` | Exposed when user is logged in, wallet setting active, and user has positive wallet balance (`/api/v2/wallet/balance`). |
| **Social Login (Google/Apple)** | `04-auth/04-welcome-sign-in-create-account-guest-fr.png` | `FUTURE_PHASE_BACKEND_CAPABILITY` | Backend supports `/social-login`, but excluded from first MVP app scope. |
| **Express & Relay Delivery Options** | `06-checkout/06-choose-delivery-standard-express-relay-fr.png` | `SUPPORTED_CONDITIONALLY` | Supported via `carrier` or `pickup_point` shipping types in `AddressController`. Default is `home_delivery`. |
| **Saved Payment Cards (Vault)** | Screenshot shows "Cards" option | `HIDDEN_IN_MVP` | Hidden in initial MVP; customers enter card details directly on secure CMI Hosted Payment Page. |
| **Wishlist Sync** | `05-cart-wishlist/05-cart-items-promo-code-summary-fr.png` | `EXCLUDED_FROM_MVP` | Wishlist management excluded from Phase 1 buyer MVP scope. |
