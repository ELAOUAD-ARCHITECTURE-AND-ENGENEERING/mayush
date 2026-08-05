# Mayush Buyer Mobile Screenshot Validation — Summary

## Scope and method

365 buyer-mobile screenshots were visually reviewed: every PNG in `01-entry/` through `11-arabic-rtl/`. The foundation rules were extracted first from all 9 boards in `00-foundation/` and all 21 asset boards in the available `assetsl/` directory. The requested `12-assetsl/` directory is not present in this workspace; `assetsl/` is the available source used for asset validation.

The work is documentation only. No screenshot was renamed, edited, regenerated, deleted, or used to implement React Native screens.

> **Fact-check correction:** Currency and address conventions are accepted variations and are not validation defects on their own. The authoritative recalibration, System States reclassification, and corrected individual assessments are in [fact-check-correction.md](./fact-check-correction.md).

## Status total

| Status | Screens |
|---|---:|
| APPROVED | 9 |
| APPROVED_WITH_MINOR_FIXES | 59 |
| NEEDS_REWORK | 174 |
| REFERENCE_ONLY | 41 |
| REJECTED | 32 |
| DUPLICATE_ALTERNATIVE | 50 |
| **Total reviewed** | **365** |

The complete one-row index is [screen-validation.csv](./screen-validation.csv). All 365 PNG paths are represented once; there are no missing, orphaned, or duplicate CSV keys.

## Extracted implementation baseline

- Use the untouched official MAYUSH DESIGN logo. Never substitute `BUYER`, `BUYER APP`, an Arabic transliteration, or a rewritten lock-up.
- Use warm cream, white cards, beige secondary surfaces, deep navy, and Mayush orange around `#D97434`; preserve rounded components, soft shadows, and a spacious premium furniture-marketplace composition.
- Use one coherent navy-outline icon family, 12–16 dp rounded controls, 4/8 dp spacing rhythm, accessible 44–48 dp tap targets, safe areas, Dynamic Type support, and scrollable keyboard-safe forms.
- Buyer tab bar is exactly: Accueil, Catégories, Favoris, Panier, Compte. It has five stable destinations and one active treatment.
- Currency and address examples are flexible. Arabic still needs actual RTL order/mirroring while keeping IDs, phones, amounts, and dates readable.

See [component-observations.md](./component-observations.md) for the reusable component and foundation contradictions.

## Most common brand inconsistencies

- Rewritten brand marks or forbidden buyer wording appear in `04-password-changed-success-fr.png`, `05-cart-items-promo-code-summary-fr.png`, `05-cart-update-needed-price-stock-changes-fr.png`, `08-logout-confirmation-dialog-fr.png`, and several System State/RTL screens.
- The approved five-tab buyer navigation is replaced by Explorer, Orders, Messages, Recents, or an incomplete set in many Discovery, Account, Cart, and Arabic screens.
- Foundation boards themselves contain conflicting icon/arrow variants and alternate Buyer App branding; official buyer rules override those conflicting examples.

## Most common UX problems

- Checkout/post-payment states are presented as static posters instead of recoverable flows: `06-payment-verification-processing-fr.png`, `06-payment-pending-confirmation-fr.png`, `06-payment-failed-retry-fr.png`, and `06-payment-confirmation-taking-longer-fr.png` need idempotency, polling, timeout, retry, and app-return decisions.
- Enabled primary actions contradict empty or unchecked required inputs in `06-order-review-confirm-multi-vendor-fr.png` and `06-terms-conditions-confirmation-fr.png`.
- Hard-coded dates, amounts, product availability, and legal/policy claims are treated as live truth. `06-order-processing-loading-state-fr.png` retains `#FR-XXXXXX`; `06-order-thank-you-confirmation-summary-fr.png` has a static/incorrect weekday; `07-order-detail-ar.png` has contradictory payment/totals.
- Several full-page destructive confirmations and dense policy/document layouts should be native dialogs, bottom sheets, or scrollable CMS views rather than fixed posters.

## Most common RTL problems

- Back arrows/chevrons and row order are not consistently mirrored in `06-choose-delivery-standard-express-relay-ar.png`, `07-order-detail-ar.png`, `07-order-tracking-timeline-ar.png`, and broad portions of `11-arabic-rtl/`.
- Arabic screens have inconsistent directionality and cross-locale text treatment; currency/address examples remain accepted variations.
- RTL bottom navigation is often the wrong five destinations or has LTR ordering. Arabic must be repaired as a shared shell, not as isolated text replacement.

## Most common native implementation problems

- Static text and product/data layouts do not accommodate Dynamic Type, long French/Arabic labels, network variability, stock changes, or server-calculated totals.
- Payment, carrier, wallet, COD, refund, saved-card, tax, and delivery claims lack an approved product contract. Treat them as **NEEDS PRODUCT DECISION**, not backend assumptions.
- Invented implementation details are visible: `06-order-needs-update-price-stock-changes-fr.png` leaks Laravel, and `06-secure-payment-redirect-loading-fr.png` displays a fake production-looking payment URL.
- The 11 filename/visible-state mismatches in System States are unsafe implementation inputs. See [filename-route-integrity-review.md](./filename-route-integrity-review.md).

## Highest-priority corrections before development

1. Replace or remap the 11 mismatched System State assets, especially `10-access-denied-403-fr.png`, `10-account-blocked-fr.png`, `10-cache-clearing-progress-fr.png`, `10-offline-fr.png`, `10-server-unavailable-fr.png`, and `10-unusual-activity-detected-fr.png`. Their filenames would route developers to the wrong security/connectivity behavior.
2. Rework Checkout purchase truth: fix the 8,440 MAD minus 800 MAD total and unchecked-consent CTA in `06-order-review-confirm-multi-vendor-fr.png`; fix enabled consent in `06-terms-conditions-confirmation-fr.png`; define payment gateway return/pending/failure behavior.
3. Replace invalid payment reference material: the expired selected Visa in `06-saved-payment-cards-visa-mastercard-fr.png`, invented URL in `06-secure-payment-redirect-loading-fr.png`, and unresolved order placeholder in `06-order-processing-loading-state-fr.png`.
4. Establish one buyer navigation shell and remove incompatible tabs across Home, Cart, Account, Orders, and Arabic routes.
5. Correct RTL layout, copy direction, and buyer navigation consistently. Currency and address examples are accepted variations unless they create an internal transaction contradiction.
6. Reject/rebuild the unsafe cart sources `05-cart-items-list-ar.png`, `05-cart-quantity-update-toast-fr.png`, and `05-cart-update-needed-price-stock-changes-fr.png`; correct the merge and destructive-confirmation flows before use.
7. Rebuild critical Arabic outcomes: `11-update-required-ar.png` exposes an invalid Home bypass, `11-order-tracking-ar.png` has contradictory fulfillment labels, and `11-slow-connection-ar.png` blocks recovery.

## Canonical-use guidance

Use [canonical-screen-list.md](./canonical-screen-list.md) together with each folder report—not filenames alone—to select the reference for a native route/state. Exact visual duplicates are listed in [duplicate-screen-review.md](./duplicate-screen-review.md). The dedicated [filename-route-integrity-review.md](./filename-route-integrity-review.md) confirms 352 exact filename matches, two imprecise names, and 11 route-critical mismatches.
