# STEP 8G — Checkout Address, Delivery & Payment Option States

Status: `FRONTEND_COMPLETE_WEB_CHECKED_NATIVE_VALIDATION_PENDING`
Date: 2026-08-11
Scope stopped at node `309:692`.

## Outcome and live Figma verification

The eight live frames in file `wAdLNmlKanvI0AEPyEbrMs` were inspected directly before implementation. They are flattened 393×852 frames with whole-frame `ON_CLICK → NAVIGATE`, `DISSOLVE`, 0.3-second historical reactions. No Figma content was modified.

| Node | Exact live frame | Runtime classification | Verified visible content |
| --- | --- | --- | --- |
| `309:683` | `06-city-selector-list-fr` | `DEDICATED_SCREEN` | “Choisir une ville”, local search, recent city chips, Casablanca/Rabat/Marrakech/Tanger/Agadir/Fès/Mohammedia/Témara rows, selected Casablanca, delivery notice |
| `309:684` | `06-delivery-zone-selector-fr` | `DEDICATED_SCREEN` | selected Casablanca, zone search, Racine/Maârif/Ain Diab/Bourgogne/Sidi Maârouf/Californie, 20/30 MAD metadata, Continue |
| `309:685` | `06-edit-address-form-fr` | `REUSE_WITH_VARIANT` + `DEDICATED_SCREEN` | shared name, +212 phone, city, zone, address, apartment, postcode, instructions, Maison/Travail/Famille/Autre, default switch, save/delete |
| `309:686` | `06-no-address-saved-empty-state-v2-fr` | `DEDICATED_SCREEN` | “Aucune adresse enregistrée”, Add Address CTA, secure-data message |
| `309:688` | `06-delivery-by-vendor-multi-seller-fr` | `DEDICATED_SCREEN` | address/delivery/payment stepper, per-seller standard delivery blocks, package count, 29 MAD per package, 58 MAD summary, Continue |
| `309:689` | `06-delivery-unavailable-address-error-fr` | `DEDICATED_SCREEN` | Marrakech/Gueliz address, unavailable warning, affected items, edit/remove/support recovery actions |
| `309:691` | `06-pay-with-wallet-balance-fr` | `DEDICATED_SCREEN` | 1,250 MAD balance, current order amount, remaining balance, selected wallet, secure note, Use Wallet CTA |
| `309:692` | `06-saved-payment-cards-visa-mastercard-fr` | `REUSE_WITH_VARIANT` + `DEDICATED_SCREEN` | Visa 4242 / 06-26, MasterCard 8731 / 11-27, radio/default/delete controls, add method, Continue, secure note |

French LTR and Arabic RTL copy/layout are implemented for all eight states, including reversed rows, directional chevrons, text alignment, radio controls, readable MAD amounts, address metadata, and seller blocks. Native validation remains pending.

## Architecture and behavior

The existing domains remain authoritative:

- `authState` owns the canonical runtime address book. Checkout holds only `selectedAddressId`, editor draft, and resumable session facts. Hydration uses a minimal `replaceSavedAddresses` bridge rather than a third address store.
- `checkoutState` centralizes stable Morocco `cityId`/`zoneId` fixtures, compatibility, shared address validation, seller-delivery projection, wallet sufficiency, integer-MAD checkout totals, and durable-screen normalization.
- Changing city clears an incompatible zone. Hydration upgrades the legacy Casablanca/Centre fixture and rejects incompatible city/zone pairs.
- The no-address state is derived only from `savedAddresses.length === 0`; its CTA enters the existing Add Address flow. A first valid address returns to Address Selection.
- Edit and Add use the same `AddressDraft` and `validateAddressDraft` rules. Editing updates the canonical address entity; `BuyerOrder` retains an immutable primitive address snapshot.
- Multi-seller delivery projects existing cart seller identities into one checkout. It does not copy cart lines or create seller-owned state. Standard delivery is 29 MAD per seller package in the target fixture, so two packages total 58 MAD.
- Checkout/order math is `cart subtotal − shared promotion discount + shared delivery fee`. Order Review and `BuyerOrderRepository.createOrder` consume the same delivery projection and integer-MAD total.
- Gueliz is the deterministic unsupported-zone fixture. Unavailability preserves affected seller identities and cart lines, blocks payment, and offers edit/remove/support recovery. A changed address is revalidated before continuation.
- Wallet is a frontend-only preference with `availableBalanceMad = 1250`; `canPayWithWallet` blocks insufficient balance and does not invent split payment or verified settlement. Guest authentication resumes `wallet-balance` with the same `checkoutAttemptId`.
- Checkout saved cards are `SHARED_DATA` / `REUSE_WITH_VARIANT` over `accountPreferencesState`. Only ID, brand, last4, expiry, and default selection exist. No PAN, CVV, tokenization, charge, or backend payment claim was introduced.
- The selected payment preference ID is sourced once from account payment preferences, persisted as an ID in checkout session, displayed by Review, and safely snapshotted by `BuyerOrder` as preference ID and last4. Historical orders are not mutated by later preference/address edits.
- Selector, edit, empty, unavailable, wallet-detail, and saved-card presentation screens normalize to their durable parent routes when persisted; transient visibility/error/warning state is not restored.

## Semantic prototype decisions

| Connection | Decision | Runtime truth |
| --- | --- | --- |
| `FIGMA-PROT-066` 683→684 | `IMPLEMENTED` | selecting a stable city opens its compatible zone selector |
| `FIGMA-PROT-067` 684→685 | `IMPLEMENTED` | zone confirmation in edit context returns to Edit Address |
| `FIGMA-PROT-068` 685→686 | `MISMATCHED` | saving an edit does not erase addresses; empty state occurs only after deleting the last address |
| `FIGMA-PROT-069` 686→687 | `MISMATCHED` | zero-address checkout must add a valid address before delivery |
| `FIGMA-PROT-071` 688→689 | `MISMATCHED` | valid multi-seller delivery does not automatically become unavailable |
| `FIGMA-PROT-072` 689→690 | `MISMATCHED` | unavailable delivery blocks payment until recovery and revalidation |
| `FIGMA-PROT-074` 691→692 | `MISMATCHED` | wallet selection does not automatically switch to saved cards |
| `FIGMA-PROT-075` 692→693 | `MISMATCHED` | saved-card continuation goes through Order Review; it does not bypass confirmation |

## Verification

- Regression: `417 / 417 PASS`
- Step 8B.0: `11 / 11 PASS`
- Step 8B: `17 / 17 PASS`
- Step 8C: `23 / 23 PASS`
- Step 8D: `24 / 24 PASS`
- Step 8E: `28 / 28 PASS`
- Step 8F: `32 / 32 PASS`
- Step 8G: `37 / 37 PASS`
- Application TypeScript: `0 errors`
- Tools/tests TypeScript: `0 errors`
- Expo web export: `PASS` (651 modules; output `dist`)
- `git diff --check`: `PASS` (line-ending notices only)
- Backend/Laravel changes: none made by Step 8G
- Seller/admin state: none introduced
- Native validation: `PENDING`

The Step 8G suite behaviorally covers stable city/zone identity, invalidation, shared address ownership, immutable orders, derived empty state, hydration, seller grouping and total equality, promotion-plus-delivery math, deterministic unavailability/recovery, wallet sufficiency/auth continuity, safe shared card metadata/selection, BuyerOrder snapshots, transient-state exclusion, and cross-domain safety.

## Deterministic canonical audit and diff

The generator ran twice with byte-identical SHA-256 hashes:

- `canonical-figma-screen-registry.json`: `8CA57CDDDA1B0A655F74551B57999CD2F4DB2911AE9915F7021AF88775723175`
- `prototype-gap-audit.json`: `B3AE93C45E0C48DB9D42001E3A134B3BB2CF222FC9079671EF950B93DE4DF15F`

Canonical screen/state metric: **199 / 207 implemented (96.1%)**, 8 missing.
Exact interaction metric: **63 / 206 implemented (30.6%)**, 40 mismatched, 103 missing.

`CHECKOUT_SCREEN_STATE_COMPLETENESS` for `309:679–710`: **25 / 32 implemented**, 7 missing.
`CHECKOUT_INTERACTION_COMPLETENESS` for connections touching `309:679–710`: **19 implemented / 7 mismatched / 8 missing**.

The working tree already contained the verified, uncommitted Steps 8B–8F checkpoint and unrelated prior files. Step 8G preserved those changes. Its material additions are three checkout screen-family files, checkout/address/delivery/payment/order domain extensions, RootNavigator wiring, the 37-test suite, and canonical/progress documentation.

## Remaining inventory and stop condition

Exact global missing nodes:

1. `309:591` — `02-home-logged-in-personalized-recommendations`
2. `309:701` — `06-payment-confirmation-taking-longer-fr`
3. `309:702` — `06-payment-pending-confirmation-fr`
4. `309:704` — `06-terms-conditions-confirmation-fr`
5. `309:707` — `06-order-already-in-progress-duplicate-check-fr`
6. `309:708` — `06-order-needs-update-price-stock-changes-fr`
7. `309:709` — `06-checkout-skeleton-loading-state`
8. `309:710` — `06-checkout-error-loading-state-fr`

The next optimal canonical cluster is verified as **STEP 8H — Checkout Payment Confirmation, Conflict & System States**, covering `309:701`, `309:702`, `309:704`, and `309:707–710`. Step 8H was not executed.
