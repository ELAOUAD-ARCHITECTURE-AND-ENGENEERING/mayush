# STEP 8D — Buyer Returns, Refunds & Return Tracking

Status: `FRONTEND_COMPLETE_WEB_CHECKED_NATIVE_VALIDATION_PENDING`
Date: 2026-08-11
Scope boundary: nodes `309:732–309:736` only.

## 1. Live Figma verification

The live Figma file `wAdLNmlKanvI0AEPyEbrMs`, page `309:581` (`Full App Prototype Flow`), was inspected directly. All five targets are flattened `393×852` frames with exact names:

- `309:732` — `07-request-return-item-selection-fr`
- `309:733` — `07-return-detail-items-refund-status-fr`
- `309:734` — `07-return-tracking-timeline-fr`
- `309:735` — `07-request-refund-cancelled-order-fr`
- `309:736` — `07-refund-completed-success-fr`

Live reactions are `732→733`, `733→734`, `734→736`, `735→736`, and `736→737`, all `ON_CLICK` / `NAVIGATE` / `DISSOLVE` at about 0.3 seconds. Visible content verified item checkboxes, quantity steppers, four return reasons, optional note, local return reference, refund method/amount, six-event timeline, cancelled order context, explicit confirm CTA, and completed state.

## 2. Semantic return/refund flow classification

Two independent workflows are implemented:

1. Delivered order → eligible line selection → local return record/detail → return tracking.
2. Paid cancelled order → local refund request → explicit local completion → success.

Reorder does not enter returns, and tracking reads do not complete refunds.

## 3. Eligibility rules

`canReturnOrder(order)` requires both order and delivery lifecycle states to be `delivered`. `canReturnLine(order, line)` adds positive quantity and line membership. Preparing, shipped, in-transit, and cancelled orders are not returnable through this flow. No legal or statutory window was invented.

## 4. Item-selection architecture

`ReturnRequestDraft` stores only `orderId`, selected `orderLineId`/optional `packageId`/quantity tuples, a stable reason key, and optional message. The UI filters with the centralized rules, rejects foreign IDs, clamps quantity to the purchased snapshot, and supports the four live reasons: damaged on delivery, not as described, size/dimensions, and changed mind.

## 5. Return request architecture

Submission revalidates every line against the selected `BuyerOrder`, generates a clearly frontend-local `returnRequestId`, snapshots only the requested quantity and historical unit price needed for the transaction, and persists a separate `ReturnRequestRecord`. It never copies or rewrites the full order.

## 6. Return detail/status behavior

Node `309:733` is modeled as a combined active return detail and refund-status view. It resolves its order lines through `orderId` and `orderLineId`, displays the local return reference, selected quantities, reason, original payment route, estimated amount, and the explicit frontend fixture status `inspection` / `processing`.

## 7. Return tracking

Tracking events belong to `returnRequestId` and reproduce the live six-stage structure: request created, approved, parcel received, inspection, refund processing, refunded. Events are deterministic frontend fixtures. Opening the screen only reads events and never changes the request or refund status.

## 8. Cancelled-order refund architecture

Node `309:735` uses the genuinely cancelled, paid BuyerOrder fixture `MAY-2026-001257`. Refund records reference `orderId` and may reference an existing Step 8C `cancellationRequestId`; they do not contain return shipment or return-line state.

## 9. Refund amount derivation

Return amounts are the sum of selected historical line unit prices multiplied by requested quantities. Cancelled-order refund amount is the immutable `BuyerOrder.totalMad`. The live cancelled fixture therefore displays `450 MAD`; no arbitrary amount is stored.

## 10. Refund-completed semantics

Node `309:736` renders only when a durable refund record is explicitly `completed`. The UI preserves the Figma completion presentation while truthfully labeling it as a local frontend lifecycle state, not proof of bank, card, CMI, or wallet settlement.

## 11. Immutable historical-order behavior

Return/refund actions do not modify historical lines, price, address, payment method, seller, quantity, delivery tracking, or order totals. Delivered fixture line IDs were corrected to unique order-scoped IDs so foreign-order validation is structurally reliable.

## 12. Multi-package compatibility

`getOrderLinePackageId` and `validateReturnLineOwnership` verify optional package ownership. A line from package A cannot be submitted with package B. The UI does not require package selection because the live Figma does not expose it.

## 13. Support/reference integration

The existing support draft now accepts optional `returnRequestId` and `refundId` alongside `selectedOrderId`. Return screens reuse the existing support route. No copied BuyerOrder, return object, or refund object is introduced.

## 14. Persistence decision

Durable cancellation, review, return, and refund records share the already scoped key `mayush-mobile:buyer-order-actions:v1`. This keeps one focused buyer-order action repository and remains compatible with Step 8C data. Return drafts, selected request IDs, selected refund IDs, and other UI selections are transient. `AsyncStorage.clear()` is not used.

## 15. Backend/fixture boundaries

All return tracking, approval/receipt/inspection events, refund processing, and completion are deterministic frontend fixtures marked `recordSource: frontend_fixture`. There are no HTTP calls, carrier APIs, bank claims, polling, sockets, seller/admin state, or backend acceptance claims.

## 16. RTL

French LTR and Arabic RTL copy/layout are implemented for item rows, checkboxes, quantities, reasons, status and amount cards, timeline rows, notices, and actions. Status remains `IMPLEMENTED_WEB_CHECKED_NATIVE_VALIDATION_PENDING`; no Android or iOS evidence is claimed.

## 17. Prototype connection decisions

| ID | Decision | Reason |
|---|---|---|
| `FIGMA-PROT-113` (`731→732`) | `MISMATCHED` | Reorder availability does not semantically initiate a return. |
| `FIGMA-PROT-114` (`732→733`) | `IMPLEMENTED` | Valid submit creates the durable local return record and opens detail. |
| `FIGMA-PROT-115` (`733→734`) | `IMPLEMENTED` | Track CTA opens the same request’s timeline. |
| `FIGMA-PROT-116` (`734→736`) | `MISMATCHED` | Opening/read-only tracking cannot implicitly complete a refund. |
| `FIGMA-PROT-117` (`735→736`) | `IMPLEMENTED` | Confirm CTA performs explicit processing and completion fixture transitions. |
| `FIGMA-PROT-118` (`736→737`) | `MISSING` | `309:737` is outside Step 8D; success returns to orders or shopping. |

## 18. Regression tests

`417/417 PASS`.

## 19. Step 8B.0 tests

`11/11 PASS`.

## 20. Step 8B tests

`17/17 PASS`.

## 21. Step 8C tests

`23/23 PASS`.

## 22. New Step 8D behavior tests

`24/24 PASS`. The suite executes actual repository methods and covers all requested eligibility, ownership, immutability, persistence, tracking, refund, support, boundary, and seller/admin isolation assertions.

## 23. Both TypeScript checks

- Application: `npx tsc --noEmit` — PASS, 0 errors.
- Tools/tests: `npx tsc --project tsconfig.tools.json --noEmit` — PASS, 0 errors.

## 24. Expo export

`npx expo export --platform web` — PASS. Metro bundled 645 modules and exported `dist`.

## 25. Diff result

`git diff --check` — PASS after Step 8D implementation and documentation updates. Existing unrelated dirty Laravel cache and historical validation-result files were preserved.

## 26. Deterministic hashes

Canonical generation ran twice with byte-identical SHA-256 hashes:

- Registry: `d6380a0b9be1e510a2bf90df55d46472528ca7715dea6377ac7574701bacff8a`
- Prototype audit: `3c2297cd67ac5b20c807f556b39f6c09fc8839540c20abba9bd6a57d274aad3b`

## 27. New screen metric

`172/207 IMPLEMENTED`, `35 MISSING`, `83.1%`.

## 28. New interaction metric

`58/206 IMPLEMENTED`, `21 MISMATCHED`, `127 MISSING`, `28.2%`.

## 29. Exact remaining Buyer Orders & Fulfillment nodes

- `309:737` — `07-delivery-delayed-notification-fr`
- `309:738` — `07-delivery-failed-reschedule-fr`
- `309:739` — `07-support-order-contact-form-fr`
- `309:740` — `07-tracking-unavailable-in-preparation-fr`
- `309:741` — `07-order-not-found-error-fr`
- `309:742` — `07-orders-empty-state-fr`
- `309:743` — `07-orders-error-loading-state-fr`
- `309:744` — `07-orders-skeleton-loading-state`
- `309:745` — `07-order-detail-skeleton-loading-state`

All remain `MISSING`.

## 30. Exact next task

`STEP 8E — DELIVERY ISSUES, ORDER SUPPORT & ORDER SYSTEM STATES`.

Step 8E was not started.
