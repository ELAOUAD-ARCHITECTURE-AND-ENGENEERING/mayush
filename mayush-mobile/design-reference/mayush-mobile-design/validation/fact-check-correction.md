# Fact-check and scope correction

This document supersedes conflicting statements in the earlier validation reports, summary, canonical list, and CSV notes where stated below. It was produced after a self-audit of the delivered claims, direct reinspection of the cited screenshots, and review of the independent sampled counter-assessment at [claude-counter-assessment.md](./claude-counter-assessment.md).

## Verified facts

- The source inventory is **365** PNG files across 01-entry/ through 11-arabic-rtl/.
- [screen-validation.csv](./screen-validation.csv) has exactly 365 unique source keys, no missing source files, no orphan rows, and valid status/count fields.
- [filename-route-integrity.csv](./filename-route-integrity.csv) has exactly 365 unique source keys: 352 exact filename matches, two partial matches, and 11 System State filename/state mismatches.
- 10-access-denied-403-fr.png visibly says *Activité inhabituelle détectée*, while 10-server-unavailable-fr.png visibly presents local-data refresh. The filename mismatch findings are true.
- 05-cart-items-promo-code-summary-fr.png is a usable, coherent cart composition with correct visible arithmetic and the approved five buyer tabs. Its material issue is the MAYUSH BUYER lock-up plus density/state refinements—not a complete-screen failure.
- 06-payment-failed-retry-fr.png has a good recovery layout. The “not charged” statement remains an unverified payment-settlement claim, but it is a **MAJOR** accuracy issue, not CRITICAL.
- 03-product-detail-image-carousel-add-to-cart.png has five bottom-nav slots: Accueil, Explorer, Catégories, Favoris, Compte. Panier is missing from the bottom bar; the cart icon in the header does not satisfy the required buyer tab destination. The corrective instruction to replace the Explorer slot with Panier remains valid. The claim that this screenshot has six bottom tabs is not supported.

## Corrected validation distribution

| Status | Screens |
|---|---:|
| APPROVED | 9 |
| APPROVED_WITH_MINOR_FIXES | 59 |
| NEEDS_REWORK | 174 |
| REFERENCE_ONLY | 41 |
| REJECTED | 32 |
| DUPLICATE_ALTERNATIVE | 50 |
| **Total** | **365** |

## Currency and address scope — authoritative

Currency, currency formatting, city names, postal codes, country/address examples, and delivery-address conventions are **accepted variations** for this audit. They must not by themselves make a screen fail, reduce its status, or increase issue severity.

References to MAD, Morocco, +212, EUR, USD, SAR, FCFA, cities, address fields, or postal data in the original reports are therefore **context observations only** unless they create a separate visible contradiction within the same transaction (for example, impossible arithmetic or two conflicting amounts). Phone-account verification mechanics, official branding, navigation, RTL direction, data math, consent, and payment claims remain in scope.

## System States reclassification

Direct inspection confirms visible 37 / 46, 39 / 46, and related pagination, non-official Buyer App logos, and inconsistent deck-like visual systems in 10-system-states/. These are presentation/deck exports, not a coherent native-app mockup set.

The authoritative System States classification is:

| Status | Count | Interpretation |
|---|---:|---|
| REFERENCE_ONLY | 35 | May inform an isolated state/component idea; not a direct native screen reference |
| DUPLICATE_ALTERNATIVE | 17 | Exact v2 presentation-slide alternatives; no direct native use |
| REJECTED | 11 | Presentation slides whose filenames name a different state from the visible content |

The duplicated unusual-activity asset is also a filename/state mismatch, so it is correctly counted once as REJECTED rather than as a duplicate alternative. This replaces the prior System States totals in the report and canonical list. The shared CSV has been updated accordingly.

## Other corrected assessments

| File | Earlier assessment | Corrected assessment |
|---|---|---|
| 05-cart-items-promo-code-summary-fr.png | REJECTED | NEEDS_REWORK; preserve its layout/components after logo and interaction-density fixes |
| 05-cart-merge-guest-account-fusion-fr.png | REJECTED partly for currency | NEEDS_REWORK; currency accepted, but merge/undo product behavior remains unresolved |
| 05-cart-remove-item-confirmation-dialog-fr.png | REJECTED partly for currency | NEEDS_REWORK; currency accepted, but destructive-action semantics need correction |
| 05-wishlist-remove-confirmation-dialog-fr.png | REJECTED partly for currency | NEEDS_REWORK; currency accepted, but danger treatment and undo need correction |
| 06-payment-failed-retry-fr.png | One CRITICAL + one MAJOR issue | Two MAJOR issues; preserve recovery layout after gateway-state handling is defined |

## Confidence calibration

“High” in the historical per-screen reports means the visible composition could be read clearly. It does **not** prove the unseen backend behavior, payment settlement, policy, delivery rules, or native interaction details. Any screen requiring such inference must be treated as Medium confidence for product behavior, even when its pixels are clear.

## Canonical implementation rule

Use a screen directly only when its current status in [screen-validation.csv](./screen-validation.csv) is APPROVED or APPROVED_WITH_MINOR_FIXES. Apply this correction before using the summary or a legacy per-screen severity count. For System States, build reusable native state components from the foundation rules and real product decisions rather than copying the deck exports.
