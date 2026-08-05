# Mayush Mobile Buyer MVP - Phase 3 Final Handoff Freeze

## Executive Declaration

Phase 3 (MVP Screen and Data Contract) has reached complete specification lock and technical documentation normalization. All historical audit discrepancies have been reconciled, CSV matrix formatting is RFC-verified, TypeScript contracts compile cleanly with zero errors, and state machines are deterministic.

**Handoff Status**: **`READY_FOR_PHASE_4`**

---

## 1. Document Authority & Precedence Order

When specifications conflict, the following precedence hierarchy MUST be strictly enforced:

1. **Actual Laravel Source Code** (`app/`, `routes/`, `config/`)
2. **`PHASE_3_HANDOFF_FREEZE.md` & `PHASE_3_CONTRACT_LOCK_REPORT.md`**
3. **`MVP_SCREEN_CONTRACT.md`** (32 main rows: 15 Primary, 1 Screen State, 2 Bottom Sheet, 1 Modal, 1 Dialog, 1 Toast, 3 Loading, 1 Empty, 5 Error, 2 Success + 8 Arabic RTL reference items)
4. **`MVP_SCREEN_API_MATRIX.csv`** (23 header columns, 22 data rows, RFC-parsed)
5. **`MVP_STATE_MACHINES.md`**
6. **`MVP_SERVER_AUTHORITY.md`**
7. **`MVP_DATA_REQUIREMENTS.md`**
8. **`MVP_ACCEPTANCE_CRITERIA.md`**
9. **`CMI_MOBILE_BRIDGE_REQUIREMENTS.md`**
10. **Earlier Phase 2 Audit Documents** (Historical audit logs only)

---

## 2. Frozen Canonical Standards

- **Language Header**: `App-Language: fr | ar` (Supported backend languages: `fr | ar | en`).
- **Selectable MVP Languages**: `fr` (French LTR) and `ar` (Arabic RTL).
- **HTTP Body Rules**: `Content-Type: application/json` sent ONLY when JSON request body is present.
- **Verified Payment Identifiers**: `"cash_on_delivery"`, `"cmi"`, `"wallet"`.
- **Guest Cart Lifecycle**:
  ```typescript
  type GuestCartIdentityState =
    | { status: 'absent' }
    | { status: 'active'; tempUserId: string }
    | { status: 'merge_pending'; tempUserId: string }
    | { status: 'merge_verified' }
    | { status: 'merge_failed'; tempUserId: string; reason: string };
  ```
  - App launch reads existing `temp_user_id` from `AsyncStorage`.
  - App does NOT generate `temp_user_id` client-side when absent.
  - On first guest `POST /api/v2/carts/add`, `temp_user_id` is omitted if absent; Laravel generates and returns `temp_user_id`.
  - Mobile stores exact returned `temp_user_id`.
  - On login/signup, mobile sends stored `temp_user_id` in request body.
  - Server reassigns cart items (`user_id = X, temp_user_id = NULL`).
  - Mobile receives auth token, queries `POST /api/v2/carts` to verify merged cart, and clears `temp_user_id` ONLY after merge verification.

- **Authentication Scope**: Customer Login (email/phone & password) and Customer Signup. Social login (Google/Apple) is excluded from the MVP app scope and reclassified as `FUTURE_PHASE_BACKEND_CAPABILITY`.
- **CMI Security Controls**:
  - Classification: `REQUIRES_SECURE_MOBILE_BRIDGE` (Release Dependency).
  - Sanctum Bearer tokens (`?token=...`) MUST NEVER be passed in URL query strings.
  - Browser return signals are NOT proof of payment. Payment status must be verified via `GET /api/v2/purchase-history-details/{order_id}`.
- **Order Identifier Mapping**:
  - `POST /api/v2/order/store` returns `{combined_order_id}` (`CombinedOrder` master record).
  - `GET /api/v2/purchase-history-details/{id}` expects individual `{order_id}` (`orders.id`).
  - The client queries `GET /api/v2/purchase-history` to locate the individual `{order_id}` matching `{combined_order_id}` before requesting order details.

---

## 3. Validation Results

- **TypeScript Compilation**: `npx tsc --noEmit` -> **PASSED with 0 errors**.
- **CSV Matrix Parse**: `MVP_SCREEN_API_MATRIX.csv` -> **PASSED with 0 errors**.

---

## 4. Release Dependencies (Non-Blocking for Phase 4)

1. **`cmi-secure-mobile-bridge`**: Secure HTTPS bridge endpoint for initializing CMI web sessions without exposing Bearer tokens in URLs.
2. **`backend-order-mapping-enhancement`**: Backend helper or API resource enhancement for querying order details by `combined_order_id`.

---

## 5. Phase 4 Authorization

The project specification is fully locked and normalized. **Phase 4 (`mobile-foundation-and-design-system`) is officially authorized to begin.**
