# STEP 8B.0 — Order Foundation & Pre-Implementation Repair

Date: 2026-08-11
Scope: Mayush Mobile buyer frontend only
Baseline: `PRE_STEP_8B_FULL_IMPLEMENTATION_AUDIT.md`

## 1. Credential cleanup result

The untracked `scripts/visual-qa/figma-client.js` contained the repository's only detected token-shaped Figma credential. The literal was removed without printing it. The client now reads `FIGMA_ACCESS_TOKEN` and optional `FIGMA_FILE_KEY` from the environment and fails at request time if the access token is absent. `.env.example` documents blank/local-only variables; the real `.env` remains ignored. A repository-wide token-pattern rescan found no additional copy.

No credential was committed by this task.

## 2. Secret rotation status

**MANUAL SECRET ROTATION REQUIRED**

Source removal does not revoke a credential that may already have been exposed. This environment did not provide authority to rotate it at Figma.

## 3. Git/worktree checkpoint status

No checkpoint was created. The existing branch is `mobile-app`, and the worktree already contains a large mixture of modified source, audit artifacts, generated results, and untracked visual-QA captures from earlier work. Selectively committing the Step 8B.0 files without an owner review would risk sweeping unrelated user work or producing a misleading partial checkpoint. Nothing was reset, deleted, staged, or force-added. Initial and final `git diff --check` are clean.

## 4. Canonical registry corrections

`build-canonical-registry.js` now:

- parses quoted CSV correctly;
- separates `screenStatus`, `connectionStatus`, and `implementationType`;
- treats file existence as validation, not semantic evidence;
- validates mapped source files and real RootNavigator `ScreenKey` values;
- permits non-route entries only when explicitly typed MODAL, SHEET, or INLINE_STATE;
- detects incompatible duplicate Figma identities and current frame-name conflicts;
- rejects an IMPLEMENTED connection whose destination screen is missing;
- validates connection row counts against route-map summaries;
- produces byte-stable JSON without generation timestamps.

Corrections include 309:716 missing, 309:737 missing/delivery-delay, and verified mappings for 309:789, 309:793, 309:796, and 309:797. All eight audited false-positive records (073, 094, 185, 186, 189, 193, 195, 198) are corrected through the explicit route-map correction ledger. Buyer lifecycle nodes use the `Buyer Orders & Fulfillment` taxonomy.

Two consecutive generator executions produced identical hashes:

- registry SHA-256: `385794FDA2053DA15B0B4FE3409D8988D4EFE0037753055A83B9CE7955D22EA1`
- gap audit SHA-256: `C000458D2DDB136F98E2C1A421167C4AE349E6BF63005A03A1750A134B4008B2`

## 5. Corrected screen metrics

Canonical screen/state completeness remains **151/207 (72.9%)**. This task did not implement a Figma screen. Nodes 309:716–723 remain MISSING with null component and route.

## 6. Corrected connection metrics

Exact prototype interaction completeness remains **45/206 (21.8%)**. In the canonical normalized audit there are 15 mismatched and 146 missing connections. The raw route-map ledger contains 43 implemented, 17 mismatched, and 146 missing rows; normalization counts two valid presentation-only timer transitions as implemented, producing the canonical 45/15/146 split. These figures are not screen completeness.

Strict core buyer-flow reliability remains **15/19** because canonical Step 8B order-detail behavior is intentionally not implemented.

## 7. Buyer order model

`src/commerce/orderState.ts` is the single buyer-order identity domain. It defines `BuyerOrder`, `OrderLine`, stable status keys, tracking event, package, and invoice metadata extension points. It does not create parallel tracking/package/invoice stores and does not populate fabricated Step 8B journeys.

The current generic order screens retain small deprecated presentation aliases (`id`, `createdAtLabel`, line `variant`) derived from canonical fields. They are compatibility-only and are not independent identity state. Step 8B should consume `orderId`, `createdAt`, and `variantLabel` directly.

## 8. Persistence architecture

`BuyerOrderRepository` persists only buyer orders, `selectedOrderId`, and the next local sequence under scoped key `mayush-mobile:buyer-orders:v1`. Hydration validates data, falls back deterministically when corrupt, and never calls `AsyncStorage.clear()`. Order snapshots deep-copy lines, address, variants, quantities, prices, delivery/payment methods, and creation time.

The controlled persistence test creates an order, constructs a new repository against the same storage, hydrates it, finds the order in list selectors, resolves it by ID, and selects it for legacy detail.

## 9. selectedOrderId architecture

RootNavigator no longer owns `PrototypeOrder[]` or `activeOrder`. Orders List selects an ID through `orderState.selectOrder(orderId)`; detail and checkout result screens read `getSelectedOrder()`. Invalid IDs are rejected and the selected ID is persisted.

## 10. Checkout → order integration

Order Review creates through `orderState.createOrder()`. Successful processing preserves the order, clears only the purchased cart key and checkout-session key, resets the local checkout attempt, and leaves wishlist, preferences, auth, account, notifications, and support untouched.

## 11. Variant/quantity fix

`VariantSelectorSheet` now passes its actual variant and quantity to RootNavigator. `createSelectedVariantCartLine()` writes both values into the cart line, and order creation snapshots them into `variantLabel` and quantity. A real state-function behavior assertion covers the propagation.

## 12. Auth truth fix

RootNavigator's local authentication boolean was removed. `authState.isAuthenticated()` is authoritative; RootNavigator subscribes to that state, and logout calls `authState.logout()`, which clears the user and notifies navigation consumers.

## 13. Wallet resume fix

Unauthenticated wallet checkout stores `createCheckoutAuthReturnDestination(checkoutAttemptId)`, routes through authentication, and resumes the payment-method route for that checkout attempt. Guest continuation changes wallet to CMI and clears the saved return destination instead of silently routing Home.

## 14. Checkout hydration fix

Checkout sessions now include a stable `checkoutAttemptId`. Save/load helpers round-trip route, selected address, delivery method, payment method, addresses, and attempt ID. RootNavigator restores every compatible field before exposing the resumable route.

## 15. Checkout attempt/idempotency behavior

Repository creation deduplicates repeated confirmation of the same active `checkoutAttemptId`. A new attempt ID with the same cart creates a new order. Completion clears the old session and generates a new attempt, preventing refresh from recreating the completed purchase.

## 16. Support order integration

`SelectOrderForSupportScreen` no longer contains its own order list. It subscribes to the buyer repository and displays repository order IDs, totals, line counts, dates, and statuses. Support drafts/tickets retain only optional `orderId` references. Stale fixture references with no matching buyer order were removed.

## 17. Notification order integration

Order notification fixtures now contain `notification.orderId` matching the seeded buyer order. Root notification CTAs validate/select that repository order and open legacy Order Detail, falling back to Orders List if it cannot resolve. No notification-owned order-object list was added.

Help Center/FAQ generic “track order” actions continue to reach Orders List; they do not invent a specific order ID.

## 18. Behavioral tests added

`tests/Step8B0OrderFoundationBehaviorTest.ts` and its lightweight Node runner execute real state functions for:

1. selected variant/quantity → cart;
2. checkout session round trip;
3. wallet auth return context;
4. same-attempt deduplication;
5. new-attempt same-cart creation;
6. order snapshot/payment semantics;
7. reload + list/detail selectors;
8. selectedOrderId;
9. support repository IDs;
10. notification order resolution;
11. logout notification/auth truth.

Final result: **11/11 focused behavior assertions passed**.

## 19. Test composition improvement

The existing **417/417** regression suite remains intact and continues to contain many source/presence assertions. Its obsolete pure `createPrototypeOrder` checks were replaced with repository-architecture checks. The new 11-test layer is reported separately because it executes state transitions, persistence, selectors, and cross-domain references rather than inflating the legacy count.

## 20. TypeScript/export/diff results

- Application TypeScript: PASS — `tsc --noEmit`
- Tools/tests TypeScript: PASS — `tsc --project tsconfig.tools.json --noEmit`
- Regression tests: PASS — 417/417
- Focused behavior tests: PASS — 11/11
- Expo web export: PASS
- Canonical generator: PASS twice, byte-stable hashes
- `git diff --check`: PASS

The workstation's roaming `npx` shim is broken because its global npm path is missing. Equivalent project-local binaries were used; this is an environment/tooling issue, not a TypeScript or Expo failure.

## 21. Remaining P0 blockers

**None.** The order-domain, identity, persistence, navigation ownership, checkout continuity, and canonical mapping blockers from the pre-audit are repaired.

## 22. Remaining P1 blockers/preconditions

1. **Rotate the exposed Figma credential externally.** Code cleanup is complete; revocation/rotation remains an owner action.
2. **Create an owner-reviewed checkpoint before Step 8B implementation.** The current mixed worktree is too broad for an automated safe commit. Review and isolate the intended source/audit artifacts without deleting user captures.

Neither item requires a codebase refactor. Both are operational safeguards before adding the eight Step 8B screens.

## 23. Per-node readiness

| Node | Canonical state | Foundation readiness | Reason |
|---|---|---|---|
| 309:716 | MISSING | READY_WITH_REUSE | Repository, selectedOrderId, preparing status, snapshots, and legacy-detail transition exist. |
| 309:717 | MISSING | READY_WITH_REUSE | Stable shipped status and order selector exist; UI/tracking content remains Step 8B. |
| 309:718 | MISSING | READY_WITH_REUSE | `trackingEvents` extension point exists without invented events. |
| 309:719 | MISSING | READY_WITH_REUSE | Delivered/return/refund status keys exist; actions remain unimplemented. |
| 309:720 | MISSING | READY_WITH_REUSE | Buyer multi-vendor lines support seller IDs; no seller mobile scope was introduced. |
| 309:721 | MISSING | READY_WITH_REUSE | `packages` and per-package line IDs are part of the single order model. |
| 309:722 | MISSING | READY_WITH_REUSE | `selectedPackageId` is repository UI-selection state, while package identity remains order data. |
| 309:723 | MISSING | READY_WITH_REUSE | Invoice metadata has explicit frontend/backend-pending state; download integration remains later. |

## 24. Final Step 8B readiness verdict

The structural P0 blockers are resolved, and the codebase is technically prepared for buyer order-detail implementation. Step 8B should start only after external credential rotation and an owner-reviewed worktree checkpoint. No node 309:716–723 UI, backend integration, seller/admin mobile feature, or pixel-parity work was performed here.

**STEP 8B READINESS: GO_WITH_PRECONDITIONS**
