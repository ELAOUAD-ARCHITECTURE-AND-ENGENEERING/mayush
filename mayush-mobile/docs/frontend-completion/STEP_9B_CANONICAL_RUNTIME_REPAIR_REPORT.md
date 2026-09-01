# Step 9B — Canonical Runtime Repair & Reachability Closure

## 1. Step 9A audited baseline

- Canonical inventory: **207**.
- Genuine source/state implementations: **204/207**.
- Runtime-reachable canonical states: **199/207**.
- False inline states: **309:713, 309:714, 309:715**.
- Runtime-orphan routes: **309:598, 309:599, 309:693, 309:699, 309:791**.
- Exact prototype connections: **66/206**; independently actionable gaps: **21**.
- Tests: **666/666**, classified as 380 source-text, 216 pure-state, 34 repository, 13 navigation, 23 persistence, 0 rendered-component, and 0 native E2E assertions.

## 2. Live Figma evidence for the eight targets

Live connected file `wAdLNmlKanvI0AEPyEbrMs`, page `Full App Prototype Flow`, was inspected read-only before implementation.

| Node | Exact frame | Visual and source intent | Outgoing action | Runtime form |
|---|---|---|---|---|
| 309:598 | `02-promotions-campaigns-offers` | Promotions hero, campaign cards, offer filters, and discounted products. The legitimate source is the visible Home `Offres du moment` / `En profiter` control. | Whole-frame showcase navigation to 309:599; not copied literally. | Route |
| 309:599 | `02-recently-viewed-products` | Recently consulted product list plus continuation rail. The legitimate source is authenticated Home's visible `Récemment consultés` / `Voir tout` action. | Whole-frame showcase navigation to 309:600; not copied literally. | Route |
| 309:693 | `06-payment-step-intro-step3-v2-fr` | Checkout step 3 introduction explaining secure payment before redirect. Incoming Figma edge is 309:692 saved cards; continue leads to 309:694 secure redirect. | Continue to secure redirect. | Conditional route for CMI |
| 309:699 | `06-payment-failed-retry-fr` | Explicit failure, no-debit copy, order reference/amount, retry and change-payment recovery. | Whole-frame showcase navigation to payment selection 309:690. | Outcome route |
| 309:713 | `07-orders-in-progress-tab-statuses-fr` | `En cours` selected; pending, confirmed, preparing, shipped, and in-transit rows. | Order card to canonical status detail (Figma illustrates 309:716). | Inline tab state |
| 309:714 | `07-orders-completed-tab-reorder-review-fr` | `Terminées` selected; delivered rows with reorder/review/detail actions. | Figma's whole-frame link to 309:715 is presentation-only. | Inline tab state |
| 309:715 | `07-orders-cancelled-tab-refund-statuses-fr` | `Annulées` selected; cancelled, refund-pending, refunded, and partial-refund presentation. | Order detail action; whole-frame showcase points at 309:716. | Inline tab state |
| 309:791 | `09-about-mayush-design-company-fr` | Company presentation, website, support, legal, privacy, and version information. Figma incoming edge is the 309:790 About App showcase; the runtime source is a visible Settings information row. | Whole-frame showcase navigation to 309:792; not copied literally. | Route |

## 3. Orders tab architecture

`OrdersListScreen` remains one screen with one `selectedOrderTab` state: `all`, `in_progress`, `completed`, or `cancelled`. Pressable tabs update this state through `reduceOrderTabSelection`; no duplicate repositories or screens were created. `BuyerOrderRepository` remains authoritative.

## 4. Orders filtering semantics

`filterOrdersByTab` uses stable `BuyerOrder.orderStatus` values:

- In progress: `created`, `confirmed`, `preparing`, `shipped`, `in_transit`, `return_requested`, `refund_pending`.
- Completed: `delivered`.
- Cancelled/refund history: `cancelled`, `returned`, `refunded`.

Translated labels are never used for filtering. A zero-match tab renders a local tab-empty message; only a zero-order repository is treated as the global empty condition. Every card passes its exact `orderId` into the existing selection path and `getCanonicalOrderDetailRoute`.

## 5. Orders RTL repair

The Orders header, search row, tab order, cards, status groups, card actions, chevrons, and empty state now use the buyer theme's `isRTL` authority. Identifiers and MAD values retain LTR writing direction. This is source-level structural readiness only; no native RTL validation is claimed.

## 6. 309:598 reachability

The existing visible generic-Home `Offres du moment` CTA now invokes `onOpenPromotions`, which resolves to `promotions-campaigns`. No hidden, debug-only, or automatic route was added.

## 7. 309:599 reachability

Authenticated Home's existing `Récemment consultés` `Voir tout` action now invokes `onOpenRecentlyViewed`, resolving to `recently-viewed`.

## 8. Recently-viewed data boundary

Home and the full screen both call the same deterministic catalog projection over `HOME_RECENT_FALLBACK_IDS`. The previous unrelated 601–603 product fixtures and invented `Vu aujourd'hui` timestamps were removed. The screen explicitly identifies its local fallback boundary. No analytics, tracking SDK, recommendation engine, backend, timestamps, or history database was introduced.

## 9. 309:693 payment-intro reachability

Order creation remains idempotent by `checkoutAttemptId`. After the one order is created, Order Processing resolves CMI to `payment-step-intro`, then secure redirect/loading/verification. Entering the intro performs no order mutation or creation.

## 10. Payment-method applicability

- CMI: enters 309:693, then the secure-payment presentation flow.
- Cash on delivery: bypasses 309:693 and enters COD confirmation.
- Wallet: preserves frontend-only wallet semantics and never claims external CMI settlement.
- Saved cards: carry safe fixture metadata plus an explicit frontend verification scenario; no PAN/CVV or real card-processing claim exists.

## 11. 309:699 payment-failure producer

The saved Mastercard fixture has a stable `failed_fixture` scenario. `resolveFrontendPaymentVerificationOutcome` deterministically returns `failed`; repository transition records the single exclusive payment status and navigation resolves to `payment-failed`. There is no randomness, backend call, or direct test-only `ScreenKey` mutation.

## 12. Payment status exclusivity

`BuyerOrderRepository.transitionPaymentStatus` enforces legal transitions. A confirmed attempt cannot subsequently become failed or cancelled. Failed/cancelled recovery returns the same attempt to `prototype_pending_confirmation`; retry returns to the intro and change-payment returns to payment selection. Neither path clears cart/checkout state nor creates a second order. Cart/session clearing occurs only on successful completion.

## 13. 309:791 About reachability

Settings now contains a visible `À propos de Mayush Design` row using the existing section/list architecture. It resolves to `about-mayush`; Back resolves safely to `settings`.

## 14. Metadata corrections 653/771

309:653 and 309:771 are now emitted as `MODAL`, with null routes and explicit interactive-overlay evidence. No application behavior was changed for these mappings.

## 15. Runtime triggers

The registry now records `evidenceKind`, `runtimeEvidence`, and `reachability` for every node. The five repaired routes have named application producers; 713–715 require `selectedOrderTab`, `setSelectedOrderTab`, and `filterOrdersByTab` evidence.

## 16. Step 9B behavioral tests

Dedicated suite: **35/35 passed**. It executes tab selection/filtering, empty-substate distinction, exact order-detail mapping, RTL direction helpers, Home and Settings navigation resolvers, shared recent-product identities, payment applicability, repository idempotency, deterministic failure, recovery, exclusivity, historical snapshot stability, totals stability, and forbidden-scope checks.

Full suite: **701/701 passed** (previous 666 plus 35 Step 9B behaviors).

## 17. Rendered-test status

`RENDERED_TEST_INFRASTRUCTURE_PENDING`. The repository has React/React Native runtime dependencies but no existing rendered-component test harness. No testing-stack migration was introduced. Current independent classification is 383 source-text, 227 pure-state, 42 repository, 26 navigation, 23 persistence, 0 rendered-component, and 0 native E2E assertions.

## 18. Full regression

- Base suite: 417/417.
- Step 8B.0: 11/11.
- Step 8B: 17/17.
- Step 8C: 23/23.
- Step 8D: 24/24.
- Step 8E: 28/28.
- Step 8F: 32/32.
- Step 8G: 37/37.
- Step 8H: 44/44.
- Step 8I: 33/33.
- Step 9B: 35/35.

## 19. TypeScript

- `npx tsc --noEmit`: PASS (using the installed `C:\Program Files\nodejs\npx.cmd` because the roaming `npx` shim is broken).
- `npx tsc --project tsconfig.tools.json --noEmit`: PASS.

## 20. Expo

`npx expo export --platform web`: PASS; Metro bundled 656 modules and emitted the web export. Android SDK installation and native validation were not started.

## 21. Secret/worktree safety

Laravel/backend, `tools/command-center/**`, Phase 5B `result.json` artifacts, `.phpunit.cache`, and ignored visual captures were excluded from Step 9B edits and checkpoint staging. Secret scan and `git diff --check` are clean. Existing unrelated dirty material remains preserved.

## 22. Canonical generator hardening

Generator schema 3 requires runtime reachability for routes and explicit non-route evidence metadata. `INLINE_STATE` records must use `INTERACTIVE_STATE`; a static label cannot satisfy generation. Route registration plus render presence alone is insufficient. The independent auditor consumes and revalidates the emitted evidence tokens. The auditor was also corrected to recognize the now-real 309:713 inline-tab card-to-detail behavior.

## 23. Deterministic generator hashes

- Registry run 1/2 SHA-256: `AD6B81F600F888E223D229A8624024350418F9F46602A58F8BDA45EF59D5F1C8` / identical.
- Prototype gap audit run 1/2 SHA-256: `C5CBD32AB9B4D00BE3751865DC997FB6517344A27B9567C85416E5DAD88AEFA8` / identical.

## 24. Independent runtime audit results

Two post-repair audit runs are byte-identical. Final audit SHA-256: `F3B0FB8BB62D9CFB1C32CB60EE46BE830F03B8215F3C98F6B2F1967B3563C95B`. Both report 207/207 source/state implementations, 207/207 runtime reachability, zero invalid records, zero metadata issues, and zero unreachable routes.

## 25. Source/state implementation count

**207/207**.

## 26. Runtime reachability count

**207/207**.

## 27. Remaining false-positive states

**0**.

## 28. Remaining orphan routes

**0**.

## 29. Actionable interaction gaps remaining

The independent audit now removes FIGMA-PROT-095 from the actionable class because the In-progress tab's order card genuinely selects an exact order and resolves canonical detail. **20 actionable exact-prototype gaps remain**. Whole-frame showcase edges such as 598→599, 599→600, and 790→791 were intentionally not copied as application navigation.

## 30. Architecture debt carried forward

- Dual language persistence authorities.
- Address ownership duplication.
- Wishlist account scoping/persistence.
- Async singleton hydration concerns.
- Rendered-component and native E2E test infrastructure.

## 31. Checkpoint status

Eligible Step 8H, Step 8I, Step 9A audit, TypeScript-isolation, and Step 9B files are included in a local checkpoint named `chore(mobile): checkpoint runtime-complete buyer frontend`. The checkpoint is not pushed; unrelated dirty material is excluded.

## 32. Exact next phase

`STEP 9C — ANDROID VALIDATION ENVIRONMENT SETUP`

## Final verdict

STEP 9B: RUNTIME_CANONICAL_COMPLETE
