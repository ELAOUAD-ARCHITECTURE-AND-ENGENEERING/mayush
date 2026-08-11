# STEP 8F — Cart Interactions & Promotions Report

## Outcome

Step 8F is complete through canonical node `309:665` with status `FRONTEND_COMPLETE_WEB_CHECKED_NATIVE_VALIDATION_PENDING`. The existing RootNavigator-owned cart remains authoritative. No Laravel/backend code, seller/admin mobile functionality, replacement cart architecture, forensic pixel-parity work, Checkout gap node, or Discovery node `309:591` was implemented.

Canonical results are **191 / 207 screens/states implemented (92.3%)** and **61 / 206 exact prototype interactions implemented (29.6%)**. The canonical cart range `309:658–669` is **12 / 12 screen/state nodes implemented**.

## 1. Live Figma verification and implementation classification

The live canonical Figma file `wAdLNmlKanvI0AEPyEbrMs`, page `309:581`, was inspected directly for all seven targets. Each target is a flattened `393 × 852` frame with one whole-frame `ON_CLICK → NAVIGATE`, `DISSOLVE`, `0.3s` historical showcase reaction. Runtime classification follows component semantics rather than frame adjacency.

| Node | Live Figma evidence | Runtime classification | Implementation |
| --- | --- | --- | --- |
| `309:659` | “Mon panier”, quantity controls, central “Mise à jour du panier” feedback | `TOAST` | `CartToast` inside `CartScreen`; transient 1600 ms feedback after the existing quantity mutation path |
| `309:660` | “Modifier la variante”; Beige/Gris clair/Terracotta/Vert sauge/Bleu nuit; Bouclé/Velours; 70/75/80 cm choices; quantity, stock, delivery, price difference, update CTA | `BOTTOM_SHEET` | `VariantEditSheet` edits an existing stable cart line through cart-owned variant options |
| `309:661` | Multi-colis notice; Atelier Maison and Décor Élégance seller sections and subtotals; global total | `INLINE_STATE` / `REUSE_WITH_VARIANT` | `groupCartLinesBySeller` projection rendered through `SellerCartGroup` |
| `309:662` | `PROMO20`; “Appliquer”; exact incompatibility error; “Essayer un autre code”; “Voir les offres disponibles” | `INLINE_STATE` | Recoverable validation state in `CartScreen`; invalid attempts do not mutate durable cart or totals |
| `309:663` | “Code promotionnel appliqué”; `MAYUSH10`; 450 MAD saving; subtotal 8,230; discount −450; total 7,780 MAD TTC | `INLINE_STATE` | Cart-derived applied-promotion badge and one shared pricing calculation |
| `309:664` | “Code promotionnel”; code input; `WELCOME15`, `DECO100`, `MAISON10`, `EXTRA200` offer cards and expiry labels | `BOTTOM_SHEET` | Cart offers sheet derives only from the deterministic shared promotion catalog |
| `309:665` | “Retirer cet article du panier ?”; item card; Annuler/Retirer; wishlist alternative | `BOTTOM_SHEET` | `RemoveItemDialog`; no mutation on open/cancel and exact `cartLineId` removal on confirm |

No independent RootNavigator route was added for any of these presentation states.

## 2. Cart architecture reuse

`CartScreen`, the RootNavigator `cart` owner, `CART_STORAGE_KEY`, `addCartLine`, `updateCartLineQuantity`, checkout consumption, saved-for-later behavior, reorder integration, and existing `CartUpdateAlert`, `CartSkeleton`, `CartEmptyState`, and `CartErrorState` remain in place. Step 8F extends `cartState.ts`; it does not create a second cart identity or any `cartV2State`, promotion-cart store, multi-vendor cart store, or reorder cart.

Durable cart facts are cart lines and optional `appliedPromotionId`. Toast visibility, open variant/offers/removal sheets, input errors, and grouping presentation state remain component-local and are absent from persistence.

## 3. Quantity feedback and rules

Quantity updates target the exact `cartLineId`, preserve product/variant/unit price/seller identity, apply existing minimum/removal behavior, cap against `maxQuantity` when present, recompute totals, revalidate a promotion, and persist through the existing cart key. The Figma feedback copy is shown transiently and is never persisted. Quantity changes do not open the variant editor.

## 4. Cart-line variant modification

The bottom sheet consumes product-owned `variantOptions`. `updateCartLineVariant` rejects selections not present in those options, preserves `id` and `productId` when no equivalent line exists, applies the selected integer MAD price and constrained quantity, and uses existing equivalent product+variant merge semantics when another matching line exists. It never mutates wishlist source data or historical orders.

## 5. Multi-vendor projection and totals

`groupCartLinesBySeller` groups each line exactly once by stable `sellerId`, supplies display names and seller subtotals, and never mutates or duplicates the underlying cart. Seller subtotals sum to the global subtotal. Checkout still receives one coherent `CartState`; no seller dashboard, fulfillment, login, or seller-owned cart store exists.

## 6. Promotion architecture, validation, persistence, and math

One deterministic `CART_PROMOTION_CATALOG` supplies cart input validation and available-offer cards. Promotion identity uses `promoId`; display text is not an identity. Central validation returns stable keys: `VALID`, `INVALID_CODE`, `MINIMUM_NOT_REACHED`, `NOT_ELIGIBLE`, or `EXPIRED`.

`PROMO20` deterministically exercises the Figma incompatibility state. Invalid validation preserves an already-valid promotion and leaves totals unchanged. Valid selection stores only `appliedPromotionId`. Hydration revalidates that identity against the catalog and current cart; missing or ineligible identities are removed safely.

Discount math uses integer MAD values. Percentage discounts use `Math.round`, fixed discounts are rounded, all discounts are clamped to `[0, subtotal]`, and total is `max(0, subtotal − discount)`. No tax or coupon backend behavior was invented.

## 7. Applied promotion, offers, checkout, orders, and reorder

A successful promotion updates `subtotalMad`, `discountMad`, and `totalMad` through `getCartTotals`. Removing or replacing the promotion uses the same calculation. The offers sheet filters and validates the shared catalog; choosing an eligible offer applies it, closes the sheet, and returns to the cart’s applied state.

`CheckoutSummaryScreen`, `PaymentMethodScreen`, and `OrderReviewScreen` consume `getCartTotals(cart).discountMad/totalMad`. `BuyerOrderRepository.createOrder` snapshots `promotionId`, `promotionCode`, `discountMad`, and final `totalMad`, so an order does not depend on future catalog state. Reorder-added lines continue through normal `addCartLine` and promotion revalidation; historical `BuyerOrder` records remain immutable.

## 8. Removal and promotion revalidation

Opening or cancelling `RemoveItemDialog` leaves the cart unchanged. Confirm removes only the selected `cartLineId`. Sibling variants survive. Every removal revalidates the applied promotion; a subtotal that falls below its minimum drops the promotion and recomputes truthful totals. Final-line removal renders existing node `309:668`; non-final removal returns to the populated cart.

## 9. Existing states and RTL

Nodes `309:666–669` remain the existing `CartUpdateAlert`, `CartSkeleton`, `CartEmptyState`, and `CartErrorState` integrations. Step 8F behavior coverage verifies they remain reachable in `CartScreen`.

French LTR and Arabic RTL structure is included for quantity feedback, variant sheet rows/controls, seller headers/subtotals, promo input/error/actions, offer cards, and removal confirmation. Direction-sensitive rows reverse while numeric MAD formatting remains readable. Native validation was not performed and remains pending.

## 10. Semantic prototype decisions (`FIGMA-PROT-043–049`)

| Connection | Decision | Runtime reason |
| --- | --- | --- |
| `043` `659 → 660` | `MISMATCHED` | A quantity toast never auto-opens variant editing. |
| `044` `660 → 661` | `MISMATCHED` | Saving a variant returns to the populated cart; it does not force seller-group mode. |
| `045` `661 → 662` | `MISMATCHED` | Rendering seller groups does not create an invalid promotion. |
| `046` `662 → 663` | `MISMATCHED` | Invalid input cannot become valid without a new valid action. |
| `047` `663 → 664` | `IMPLEMENTED` | The explicit “Voir les offres” control opens the offers sheet. |
| `048` `664 → 663` | `IMPLEMENTED` | Selecting a valid eligible offer applies it and closes the sheet. |
| `049` `665 → 668` | `IMPLEMENTED` conditionally | Confirming removal reaches empty state only when the selected line was the final line. |

The full cart-range outgoing interaction audit for sources `309:658–669` is **4 implemented, 4 mismatched, 4 missing (4 / 12 exact)**. The Step 8F target edge set `043–049` is **3 implemented, 4 mismatched, 0 missing**. Missing showcase edges from existing nodes `666–669` were not falsely marked implemented.

## 11. Verification

- Regression: **417 / 417 PASS**
- Step 8B.0: **11 / 11 PASS**
- Step 8B: **17 / 17 PASS**
- Step 8C: **23 / 23 PASS**
- Step 8D: **24 / 24 PASS**
- Step 8E: **28 / 28 PASS**
- Step 8F: **32 / 32 PASS**
- Application TypeScript: **0 errors**
- Tools/tests TypeScript: **0 errors**
- Expo web export: **PASS** (`dist`)
- `git diff --check`: **PASS**
- Native validation: **PENDING**

The Step 8F suite behaviorally covers all 32 required cases: exact-line quantity and variant changes, merge/rejection semantics, seller projection invariants, promotion validation/math/hydration/source consistency, checkout/order/reorder consistency, removal/cancel/final-line behavior, promotion revalidation, persistence/transience boundaries, existing cart system states, cross-domain immutability, seller/admin exclusion, and native-pending status.

## 12. Deterministic canonical outputs and metrics

The canonical generator was run twice with byte-identical outputs:

- `canonical-figma-screen-registry.json` SHA-256: `0528e41b7faac87136d629200c284bd7d1aef7bbc60d4f6ef8eada0909053f40`
- `prototype-gap-audit.json` SHA-256: `7e841ed966833d10e984c1c37acfb5c21b532d68ffea63d55677027f0f859492`

Metrics:

- Screens/states: **191 implemented, 16 missing, 207 total (92.3%)**
- Exact interactions: **61 implemented, 34 mismatched, 111 missing, 206 total (29.6%)**
- `CART_SCREEN_STATE_COMPLETENESS` (`309:658–669`): **12 / 12 implemented, 0 missing (100%)**
- `CART_INTERACTION_COMPLETENESS` (outgoing sources `309:658–669`): **4 / 12 exact implemented (33.3%); 4 mismatched; 4 missing**

## 13. Diff and remaining inventory

The working tree is cumulative because verified Steps 8B–8E remain uncommitted. Within that cumulative tree, Step 8F updates the existing cart, checkout/order consumption, four cart components, RootNavigator wiring, canonical generator/ledgers, and adds the Step 8F test runner, 32-case suite, and this report. No unrelated dirty Laravel, cache, or prior visual-validation files were modified for Step 8F.

Exact remaining canonical missing nodes:

- `309:591` — `02-home-logged-in-personalized-recommendations`
- `309:683` — `06-city-selector-list-fr`
- `309:684` — `06-delivery-zone-selector-fr`
- `309:685` — `06-edit-address-form-fr`
- `309:686` — `06-no-address-saved-empty-state-v2-fr`
- `309:688` — `06-delivery-by-vendor-multi-seller-fr`
- `309:689` — `06-delivery-unavailable-address-error-fr`
- `309:691` — `06-pay-with-wallet-balance-fr`
- `309:692` — `06-saved-payment-cards-visa-mastercard-fr`
- `309:701` — `06-payment-confirmation-taking-longer-fr`
- `309:702` — `06-payment-pending-confirmation-fr`
- `309:704` — `06-terms-conditions-confirmation-fr`
- `309:707` — `06-order-already-in-progress-duplicate-check-fr`
- `309:708` — `06-order-needs-update-price-stock-changes-fr`
- `309:709` — `06-checkout-skeleton-loading-state`
- `309:710` — `06-checkout-error-loading-state-fr`

## 14. Exact next task

The optimal next canonical cluster is verified as **STEP 8G — CHECKOUT ADDRESS, DELIVERY & PAYMENT OPTION STATES**, starting with missing nodes `309:683–686`, `309:688–689`, and `309:691–692` around the already-implemented address/delivery/payment base screens. Step 8G was not executed.
