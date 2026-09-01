# Step 8H — Checkout Payment Confirmation, Conflict & System States

**Status:** `FRONTEND_COMPLETE_WEB_CHECKED_NATIVE_VALIDATION_PENDING`
**Date:** 2026-08-11
**Scope stopped at:** Figma node `309:710`

## 1. Live Figma verification

The seven flattened 393×852 frames were re-read from live Figma file `wAdLNmlKanvI0AEPyEbrMs`. Screenshots, metadata, and read-only prototype reactions were inspected; Figma was not modified.

| Node | Exact frame | Verified content | Runtime classification |
|---|---|---|---|
| `309:701` | `06-payment-confirmation-taking-longer-fr` | Delayed-verification icon, unresolved-payment warning, order reference/amount, Verify again, order status, support | `DEDICATED_SCREEN` / `ROUTE` |
| `309:702` | `06-payment-pending-confirmation-fr` | `EN COURS`, pending explanation, order reference/amount, refresh, order, support | `DEDICATED_SCREEN` / `ROUTE` |
| `309:704` | `06-terms-conditions-confirmation-fr` | Five confirmation rows, required checkbox, security note, legal/privacy links, `J’accepte et je continue` | `DEDICATED_SCREEN` / `ROUTE` |
| `309:707` | `06-order-already-in-progress-duplicate-check-fr` | Existing-order identity/status/amount/payment/date and three recovery actions | `DEDICATED_SCREEN` / `ROUTE` |
| `309:708` | `06-order-needs-update-price-stock-changes-fr` | Price, stock, expired promotion and delivery-fee old/current differences; explicit update/cart actions | `DEDICATED_SCREEN` / `ROUTE` |
| `309:709` | `06-checkout-skeleton-loading-state` | Editable header, four-step progress, address/delivery/payment/summary/totals/CTA skeleton regions | `DEDICATED_SCREEN` / transient `ROUTE` |
| `309:710` | `06-checkout-error-loading-state-fr` | Generic connection recovery copy, retry, return-to-cart and security reassurance | `DEDICATED_SCREEN` / transient `ROUTE` |

Live reactions confirm the historical frame-level sequence `701→702`, `702→712`, `704→705`, and `707→708→709→710→712`. No additional incoming reactions were found. Runtime behavior treats the four semantic families independently.

## 2. Existing architecture reused

- `RootNavigator` remains the only navigator and checkout owner.
- `checkoutAttemptId` remains the checkout identity and idempotency key.
- `BuyerOrderRepository.createOrder` remains the sole order-creation path; a new lookup by checkout-attempt identity exposes the existing order without copying it.
- `CartState`, its AsyncStorage key, promotion catalog/revalidation, integer-MAD totals, and Step 8G seller delivery projection remain authoritative.
- Account addresses, saved safe-card metadata, wallet auth return, support references, order history, returns and refunds remain in their existing domains.
- No `checkoutV3State`, pending-order store, duplicate store, error repository, backend call, polling, websocket, or processor callback was introduced.

## 3. Payment verification architecture

The durable order continues to use the existing mutually exclusive payment status. `taking_longer` is presentation over the same CMI order whose durable status remains pending; it creates no order and claims no settlement. `309:701` moves to `309:702` only after the explicit **Vérifier à nouveau** action. Pending reload resolves the same persisted `orderId` through `checkoutAttemptId`. Opening Orders does not confirm payment. COD is explicitly excluded from processor-pending presentation; wallet and saved cards keep their existing frontend-only boundaries.

## 4. Terms lifecycle

Terms acceptance stores only `{ checkoutAttemptId, materialSignature, acceptedAt }` in the existing checkout session. The signature covers cart lines/variants/quantities/prices/sellers, promotion identity, selected address, delivery method/fee, and payment method. A new attempt or material checkout change invalidates acceptance. The screen reuses existing legal/privacy destinations. Acceptance proceeds to idempotent order processing; it never means payment success and never jumps directly to Thank You.

## 5. Duplicate and conflict behavior

- Repeated submission of the same `checkoutAttemptId` resolves its existing order and shows `309:707`; repository sequence and order count do not increase.
- The same cart under a new attempt remains a legitimate new purchase.
- Deterministic pre-order price/stock conflicts route to `309:708`; they are not inferred from cart equality or from the duplicate screen.
- Explicit acceptance uses `applyCartConflictChanges` in the existing cart domain, revalidates the applied promotion, rebuilds delivery from the resulting seller lines, recomputes integer-MAD totals, invalidates stale terms, persists the cart, and passes through the transient checkout skeleton before review.
- Existing `BuyerOrder` snapshots remain immutable.

## 6. System states and persistence boundary

`309:709` is a native editable skeleton and reveals no real values below an overlay. `309:710` retries through the pure checkout recovery projection. Retry preserves cart, promotion, address, delivery/payment choices, and attempt identity; it creates no order and never calls `AsyncStorage.clear()`. Empty cart, missing address, unavailable delivery, and ready checkout each resolve to their semantic correction destination.

Durable facts are checkout selection/attempt/terms facts and existing order/payment status. Skeleton, error, delayed-timer presentation, spinner, and modal visibility are transient. Persistence normalizes skeleton/error to Checkout Summary, delayed/duplicate to the durable Pending state, and update/terms screens to Order Review.

## 7. Prototype decisions

| Edge | Decision | Reason |
|---|---|---|
| `FIGMA-PROT-084` `701→702` | `IMPLEMENTED` | Explicit check-again action moves the same unresolved order to stable pending presentation. |
| `FIGMA-PROT-085` `702→712` | `IMPLEMENTED` | Explicit **Voir ma commande** opens Orders without changing payment status. |
| `FIGMA-PROT-087` `704→705` | `MISMATCHED` | Terms acceptance must pass through order/payment processing, not Thank You. |
| `FIGMA-PROT-090` `707→708` | `MISMATCHED` | Duplicate-attempt recovery and catalog conflicts are independent. |
| `FIGMA-PROT-091` `708→709` | `IMPLEMENTED` | Explicit update acceptance revalidates the checkout through the transient skeleton. |
| `FIGMA-PROT-092` `709→710` | `MISMATCHED` | Error is conditional; loading normally resolves ready. |
| `FIGMA-PROT-093` `710→712` | `MISMATCHED` | Retry restores the appropriate checkout state and does not imply an order. |

## 8. RTL and boundary checks

French LTR and Arabic RTL structures are present for headers, stepper, icons, rows, checkboxes, old/current differences, prices, buttons and chevrons. MAD formatting remains readable. Native device validation was not performed, so all seven rows are recorded as `IMPLEMENTED_WEB_CHECKED_NATIVE_VALIDATION_PENDING`.

No Laravel/backend, Figma, seller/admin, Command Center, pixel-parity, or node `309:591` changes were made. The pre-existing mobile `tsconfig.json` exclusion and pre-Step-8H isolation reports remain separate infrastructure changes. Pre-existing PHPUnit cache and phase-5B `result.json` changes remain excluded historical dirt.

## 9. Verification

| Check | Result |
|---|---|
| Application TypeScript | PASS — 0 errors |
| Tools/tests TypeScript | PASS — 0 errors |
| Regression | PASS — 417/417 |
| Step 8B.0 | PASS — 11/11 |
| Step 8B | PASS — 17/17 |
| Step 8C | PASS — 23/23 |
| Step 8D | PASS — 24/24 |
| Step 8E | PASS — 28/28 |
| Step 8F | PASS — 32/32 |
| Step 8G | PASS — 37/37 |
| Step 8H | PASS — 44/44 |
| Expo web export | PASS — 652 modules |
| `git diff --check` | PASS — no whitespace errors; line-ending warnings only |
| Command Center diff | Empty; untouched |

Canonical generation was run twice with byte-identical SHA-256 hashes:

- Registry: `01228951A402D145CC49CDC22D98F852C546C9837469F31BBB517574C629FC09`
- Prototype audit: `28543C69F3BECA710F7A231FA2A1D02C54BFDCC278759B4BE26E0B09F78804A3`

## 10. Completion metrics and next task

- Canonical screen/state completeness: **206/207 (99.5%)**
- Exact prototype interaction completeness: **66/206 (32.0%)**
- Global interaction classification: **66 implemented / 44 mismatched / 96 missing**
- `CHECKOUT_SCREEN_STATE_COMPLETENESS`: **32/32 implemented; 0 missing** for `309:679–710`
- `CHECKOUT_INTERACTION_COMPLETENESS`: **21 implemented / 11 mismatched / 0 missing** across the 32 Checkout-source prototype connections
- `GLOBAL_FRONTEND_CANONICAL_REMAINING`: **1**
- Exact remaining node: `309:591 — 02-home-logged-in-personalized-recommendations`
- Next task: **STEP 8I — FINAL DISCOVERY GAP & CANONICAL FRONTEND COMPLETION**

Step 8I was not executed.
