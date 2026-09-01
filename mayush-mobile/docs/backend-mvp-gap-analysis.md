# Mayush Mobile MVP - Backend Gap Analysis

> [!NOTE]  
> **DOCUMENT RECONCILIATION NOTICE**: This document has been reconciled by Phase 3. Where an earlier statement conflicts with [`PHASE_3_CONTRACT_LOCK_REPORT.md`](file:///c:/laragon/www/mayush/mayush-mobile/docs/PHASE_3_CONTRACT_LOCK_REPORT.md) or [`DOCUMENT_AUTHORITY.md`](file:///c:/laragon/www/mayush/mayush-mobile/docs/DOCUMENT_AUTHORITY.md), the Phase 3 locked contract is authoritative.

## Overview

This gap analysis compares the requirements of the buyer-only mobile application against the existing Mayush Laravel backend capabilities. It identifies required client-side adapters, release dependencies, security posture, and product decisions.

---

## 1. Summary of Gaps & Solutions

| Capability / Requirement | Backend Status | Mobile Client / Bridge Solution | Criticality |
| :--- | :--- | :--- | :--- |
| **Guest Cart Token (`temp_user_id`)** | Supported via request body parameter | Laravel generates `temp_user_id` on first add when absent. Mobile stores exact returned value in `AsyncStorage`. | **RESOLVED** (Standard API) |
| **Guest-to-User Cart Merge** | Supported in `AuthController` | Mobile app passes `temp_user_id` in `/login` and `/signup` requests. Clears token ONLY after merge verification (`POST /api/v2/carts`). | **RESOLVED** (Built-in) |
| **CMI Credit Card Gateway** | Session/Browser based (`/cmi/pay`) | Secure CMI Mobile Bridge required. Mobile app opens Expo `WebBrowser` modal targeting bridge URL without query-string bearer tokens. | **REQUIRES_SECURE_MOBILE_BRIDGE** (Release Dependency) |
| **Multi-Language Header** | Handled by `AppLanguage` middleware | Mobile API client attaches `App-Language: fr \| ar` header to HTTP requests. | **RESOLVED** (Standard Header) |
| **Social Login (Google/Apple)** | Supported on backend (`/social-login`) | Excluded from first MVP app scope. Reclassified as `FUTURE_PHASE_BACKEND_CAPABILITY`. | **EXCLUDED FROM MVP** |

---

## 2. Reconciled Technical Specifications

### 2.1 Guest Cart Token State Lifecycle
- **Backend Behavior**: `CartController` checks `$request->temp_user_id` if `$request->user_id` is null. When `carts/add` is invoked without a `temp_user_id`, the backend generates a random string and returns `'temp_user_id' => $temp_user_id`.
- **Client Lifecycle Rule**:
  1. App launch reads existing `temp_user_id` from `AsyncStorage`.
  2. App does NOT generate `temp_user_id` client-side when absent.
  3. On first guest `POST /api/v2/carts/add`, `temp_user_id` is omitted if absent; Laravel generates and returns `temp_user_id`.
  4. Mobile stores exact returned `temp_user_id`.
  5. On login/signup, mobile sends stored `temp_user_id` in request body.
  6. Server reassigns cart items (`user_id = X, temp_user_id = NULL`).
  7. Mobile receives auth token, queries `POST /api/v2/carts` to verify merged cart, and clears `temp_user_id` ONLY after merge verification.

---

### 2.2 Secure CMI Mobile Bridge (Release Dependency)
- **Classification**: `REQUIRES_SECURE_MOBILE_BRIDGE`
- **Security Rule**: Sanctum Bearer tokens (`?token=...`) MUST NEVER be passed in URL query strings.
- **Verification Rule**: Browser return is treated purely as a UI trigger signal. The mobile application MUST verify payment status via API call to `GET /api/v2/purchase-history-details/{order_id}`.

---

### 2.3 Language & Header Contract
- **Canonical Header**: `App-Language: fr | ar`
- **Selectable MVP Languages**: `fr` (French LTR) and `ar` (Arabic RTL).

---

### 2.4 Verified Payment Types
- `"cash_on_delivery"`
- `"cmi"`
- `"wallet"`
