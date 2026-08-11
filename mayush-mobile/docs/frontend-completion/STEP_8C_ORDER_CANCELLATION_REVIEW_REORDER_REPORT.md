# Step 8C — Buyer Order Cancellation, Review & Reorder

Status: `IMPLEMENTED_WEB_CHECKED_NATIVE_VALIDATION_PENDING`

Scope ends at Figma node `309:731`. Return/refund node `309:732` and all later return states remain unimplemented.

## 1. Live Figma verification 724–731

The connected file `wAdLNmlKanvI0AEPyEbrMs`, page `309:581 Full App Prototype Flow`, was inspected directly. All eight frames have the exact canonical name and are 393×852. Each is a flattened frame with no editable child hierarchy, so controls and copy were verified visually from the live render while reactions were read through the Plugin API.

| Node | Exact frame | Verified visual intent | Live canvas reaction |
|---|---|---|---|
| 309:724 | 07-cancel-order-confirmation-dialog-fr | Order warning, affected items, keep/continue actions | 309:725 |
| 309:725 | 07-cancel-order-reason-form-fr | Five reasons, optional message, refund/condition information, submit | 309:726 |
| 309:726 | 07-cancellation-request-registered-fr | Request status, review/refund caution, order summary | 309:727 |
| 309:727 | 07-order-cannot-be-cancelled-fr | Ineligible order, order summary, return/support recovery | 309:728 |
| 309:728 | 07-rate-order-review-products-fr | Per-product five-star ratings, publish/later actions | 309:729 |
| 309:729 | 07-reorder-articles-changed-unavailable-fr | Unavailable item, unavailable variant, price changes, selection controls | 309:730 |
| 309:730 | 07-reorder-items-added-to-cart-fr | Added/ignored counts, added subtotal, cart/shopping actions | 309:731 |
| 309:731 | 07-reorder-with-availability-changes-fr | Current product availability, variants, quantities, updated prices | 309:732 (deferred) |

No live/local frame-name collision was found.

## 2. Semantic-flow classification

The flattened canvas sequence is not treated as one production journey. Runtime implements three flows: cancellation (`724 → 725 → 726`, or eligibility failure → `727`), delivered-order review (`719 → 728`), and reorder (historical order → deterministic plan → `729` or `731` → existing cart → `730`). Invoice, cannot-cancel, and review screens do not advance into unrelated workflows merely because Figma frames are adjacent.

## 3. Cancellation eligibility rule

`canCancelOrder(order)` is the only eligibility rule. It permits `created`, `confirmed`, or `preparing` orders only while `deliveryStatus === preparing`. Shipped, in-transit, delivered, cancelled, returned, and refund states are ineligible. UI components do not duplicate this rule.

## 4. Confirmation behavior

Node `309:724` resolves the selected repository order and shows its reference and lines. Opening it creates only an `orderId`-based transient draft; the order is not mutated. Keeping the order returns safely. Continuing reaches the reason form only if the same selected order remains eligible; otherwise it reaches `309:727`.

## 5. Cancellation reason behavior

Node `309:725` uses stable keys: `ordered_by_mistake`, `modify_products`, `delivery_too_long`, `payment_problem`, and `other`. French and Arabic labels remain presentation values. A reason is required. The live form labels its 250-character message optional, including for “Autre”, so no unsupported mandatory comment rule is invented.

## 6. Request-registered semantics

Node `309:726` stores a local `frontend_requested` action record with `orderId`, reason, optional message, and timestamp. It explicitly states that no backend accepted the request. `BuyerOrder.orderStatus` remains unchanged and `refundStatus` remains `not_started`.

## 7. Cannot-cancel behavior

Node `309:727` derives from `canCancelOrder(order) === false`; it does not use a fake order. Return options remain deferred. The support action reuses the existing support draft and stores only the selected `orderId`.

## 8. Review architecture

Buyer product reviews use focused records containing `orderId`, `orderLineId`, `productId`, rating, timestamp, and `frontend_only` status. They do not copy `BuyerOrder` and do not reuse or mutate Step 7C support-ticket satisfaction ratings.

## 9. Delivered → review integration

The delivered-order “Évaluer les produits” action now initializes a draft only when `canReviewOrder` confirms delivered/completed state, then opens `309:728`. Preparing orders cannot start or submit this review. `FIGMA-PROT-101` is therefore exact and implemented.

## 10. Reorder-plan architecture

`buildReorderPlan` compares immutable historical order lines with deterministic current catalog records. Each plan line is classified `available`, `changed`, or `unavailable`, retains its historical fields, and separately carries current price, variant, quantity, and selection state. Plans reference stable order and line/product IDs.

## 11. Historical-vs-current data handling

Historical price, quantity, variant, seller, address, payment method, and order lines are never rewritten. Current availability and price changes exist only inside the transient reorder plan and the new cart transaction. Reload tests verify the historical snapshot remains byte-equivalent at the object level.

## 12. Unavailable/changed item handling

Unavailable products cannot be selected or added. Changed price/variant/quantity values are visible and require an explicit selection. No silent variant substitution or historical price rewrite occurs. The deterministic fixture models one unavailable product, one price change, and one current replacement variant.

## 13. Cart integration

Reorder uses `createSelectedVariantCartLine` and `addCartLine`, then persists the resulting normal `CartState` under the existing cart storage key. Only selected, currently purchasable lines enter the cart with current price, variant, and constrained quantity. No reorder-specific cart exists.

## 14. Reorder result variants

`changed_unavailable`, `availability_changes`, and `ready` are derived from plan contents. Node `309:729` is the grouped warning state; `309:731` is the detailed current-selection state; `309:730` is the success result after actual cart mutation. These are conditional/alternative states, not a forced `729 → 730 → 731` chain.

## 15. Backend/fixture boundaries

Cancellation acceptance, refund processing, public review publication, live inventory, and catalog/carrier calls are not claimed. Action records and current catalog differences are deterministic frontend data. No Laravel code, carrier integration, seller/admin identity, return system, or new dependency was introduced.

## 16. RTL

French LTR and Arabic RTL structure is implemented for headers, warning/information cards, reason radios, text input, product/review cards, star rows, comparison rows, badges, selection controls, totals, and buttons. Native RTL validation is pending; no device claim is made.

## 17. Prototype connection decisions

| Connection | Decision | Runtime reason |
|---|---|---|
| PROT-101, 719→728 | IMPLEMENTED | Delivered review CTA opens product review |
| PROT-105, 723→724 | MISMATCHED | Invoice does not semantically initiate cancellation |
| PROT-106, 724→725 | IMPLEMENTED | Eligible continue action opens reason form |
| PROT-107, 725→726 | IMPLEMENTED | Valid reason submission records request and opens registered state |
| PROT-108, 726→727 | MISMATCHED | Successful request state returns to orders/shopping, not failure |
| PROT-109, 727→728 | MISMATCHED | Cannot-cancel recovery is order/support, not product review |
| PROT-110, 728→729 | MISMATCHED | Review submission returns to delivered detail, not reorder |
| PROT-111, 729→730 | IMPLEMENTED | Continue with selected purchasable lines mutates cart and opens result |
| PROT-112, 730→731 | MISMATCHED | Success opens cart/shopping; selection precedes success |
| PROT-113, 731→732 | MISSING | Return workflow is explicitly deferred |

## 18. Regression tests

`node scripts/run-tests.js`: **417/417 PASS**.

## 19. Step 8B.0 tests

`node scripts/run-step8b0-behavior-tests.js`: **11/11 PASS**.

## 20. Step 8B tests

`node scripts/run-step8b-behavior-tests.js`: **17/17 PASS**.

## 21. New Step 8C behavior tests

`node scripts/run-step8c-behavior-tests.js`: **23/23 PASS**. Assertions cover eligibility, non-mutating confirmation, reason validation, truthful request/refund state, cannot-cancel derivation, delivered-only review, review identity and support-rating separation, immutable reorder snapshots, unavailable/changed lines, normal cart integration, deterministic alternatives, unrelated state, persistence, cross-domain references, absence of seller/admin identity, and the `309:732` boundary.

## 22. TypeScript checks

- Application `tsc --noEmit`: **PASS, 0 errors**.
- Tools/tests `tsc --project tsconfig.tools.json --noEmit`: **PASS, 0 errors**.

The workspace `npx` shim remains broken, so equivalent checked-in local `.cmd` binaries were invoked directly.

## 23. Expo export

`expo export --platform web`: **PASS**.

## 24. Diff result

`git diff --check`: **PASS**. Git emitted Windows line-ending notices only; no whitespace error was reported.

## 25. Deterministic canonical hashes

Two consecutive generations produced identical SHA-256 values:

- `canonical-figma-screen-registry.json`: `11CF4D404FD647F36221882224D07F6B0C3780468D0376C4C3119269F13150B7`
- `prototype-gap-audit.json`: `6E4C88135D44554E3843E0657FA3022EF8F4136823F912B3F91FF25E04053E76`

## 26. New screen metric

**167/207 IMPLEMENTED (80.7%)**, **40 MISSING**. All eight Step 8C targets are evidence-backed runtime routes.

## 27. New interaction metric

**55/206 IMPLEMENTED (26.7%)**, **19 MISMATCHED**, **132 MISSING**. Counts are generator-derived from the explicit semantic decisions above.

## 28. Exact remaining Buyer Orders & Fulfillment nodes

- 309:713 — 07-orders-in-progress-tab-statuses-fr
- 309:714 — 07-orders-completed-tab-reorder-review-fr
- 309:715 — 07-orders-cancelled-tab-refund-statuses-fr
- 309:732 — 07-request-return-item-selection-fr
- 309:733 — 07-return-detail-items-refund-status-fr
- 309:734 — 07-return-tracking-timeline-fr
- 309:735 — 07-request-refund-cancelled-order-fr
- 309:736 — 07-refund-completed-success-fr
- 309:737 — 07-delivery-delayed-notification-fr
- 309:738 — 07-delivery-failed-reschedule-fr
- 309:739 — 07-support-order-contact-form-fr
- 309:740 — 07-tracking-unavailable-in-preparation-fr
- 309:741 — 07-order-not-found-error-fr
- 309:742 — 07-orders-empty-state-fr
- 309:743 — 07-orders-error-loading-state-fr
- 309:744 — 07-orders-skeleton-loading-state
- 309:745 — 07-order-detail-skeleton-loading-state

## 29. Exact next task

**STEP 8D — BUYER RETURNS, REFUNDS & RETURN TRACKING**

Step 8D has not been started.
