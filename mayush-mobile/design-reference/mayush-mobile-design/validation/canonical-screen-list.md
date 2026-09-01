# Canonical Screen List — Mayush Buyer App

For every buyer route or UI state, this document identifies the best screenshot to use as the implementation reference.

> **Fact-check scope:** Treat currency, city, postal-code, and address examples as acceptable data variations. They are not reasons to reject a reference on their own. See [fact-check-correction.md](./fact-check-correction.md) for the authoritative System States reclassification and corrected individual assessments.

---

## Splash Screen

Canonical reference: `01-entry/01-splash-screen-logo.png`
Arabic reference: Same (language-neutral)
Status: APPROVED
Required fixes: None

---

## Language Selection

Canonical reference: `01-entry/01-language-selection-french-arabic.png`
Arabic reference: Same (bilingual screen)
Status: NEEDS_REWORK
Required fixes:
- Replace the Saudi flag used for Arabic with a neutral or Morocco-appropriate language marker
- Make the footer tagline bilingual or remove it

---

## Loading / Preparing Experience

Canonical reference: `01-entry/01-loading-screen-preparing-experience.png`
Arabic reference: Same native layout with localized dynamic text; no separate raster reference is required
Status: APPROVED
Required fixes: None to the French screenshot; implementation must localize the status copy

---

## Onboarding Step 1

Canonical reference: `01-entry/01-onboarding-step1-discover-interior-fr.png`
Arabic reference: `01-entry/01-onboarding-step1-discover-interior-ar.png`
Status: APPROVED
Required fixes: None

---

## Onboarding Step 2

Canonical reference: `01-entry/01-onboarding-step2-choose-with-confidence-fr.png`
Arabic reference: `01-entry/01-onboarding-step2-choose-with-confidence-ar.png`
Status: FR APPROVED / AR NEEDS_REWORK
Required fixes:
- AR: Replace Arabic logo transliteration with official MAYUSH DESIGN logo
- AR: Remove the English `VS` label and rebuild/simplify embedded mini-UI content so it remains localizable

---

## Onboarding Step 3

Canonical reference: `01-entry/01-onboarding-step3-order-simply-fr.png`
Arabic reference: `01-entry/01-onboarding-step3-order-simply-ar.png`
Status: FR APPROVED / AR NEEDS_REWORK
Required fixes:
- AR: Normalize the mockup to the five approved buyer tabs, including Favoris
- Consider using the language-neutral delivery-box concept from the French version

---

# Discovery

## Home — French

Canonical reference: `02-discovery/02-home-hero-new-arrivals-best-sellers-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Make horizontal product rails reflow at Dynamic Type and compact widths
- Normalize the shared five-tab buyer navigation

## Home — Arabic

Canonical reference: `02-discovery/02-home-hero-shop-by-category-ar.png`  
Status: NEEDS_REWORK  
Required fixes:
- Preserve the otherwise strong mirrored buyer destination set

Rejected alternative: `02-discovery/02-home-logged-in-personalized-recommendations.png` because it has invalid mixed-language tabs and an overloaded feed.

## Categories — French

Canonical reference: `02-discovery/02-categories-photo-grid-fr.png`  
Status: APPROVED_WITH_MINOR_FIXES  
Required fixes:
- Make tiles reflow and announce selection/category names accessibly

## Categories — Arabic

Canonical reference: `02-discovery/02-categories-photo-grid-ar.png`  
Status: NEEDS_REWORK  
Required fixes:
- Keep its RTL grid content but use the French screen's exact five buyer destinations
- Centralize mirrored tab labels/icons and active state

## Category Landing / Product Grid

Canonical reference: `02-discovery/02-subcategory-canapes-filtered-list.png`  
Status: NEEDS_REWORK  
Required fixes:
- Restore Panier and Compte in the five-tab navigation
- Define or remove camera search
- Use a virtualized responsive grid and accessible filter/favorite state

No safe category-landing alternative exists: `02-category-landing-living-room-ar.png` uses Saudi riyal and Orders-for-Cart; `02-category-landing-salon-collections-fr.png` uses FCFA and Messages-for-Cart.

## Search — Pre-results

Canonical reference: None safe  
Rejected source: `02-discovery/02-search-recent-popular-trending-categories.png`  
Status: REJECTED  
Required remake:
- Use MAD and the standard five-tab buyer IA
- NEEDS PRODUCT DECISION for search-history storage/clearing and trending data

## Search Results

Canonical reference: `02-discovery/02-search-results-grid-fauteuil.png`  
Status: NEEDS_REWORK  
Required fixes:
- Normalize bottom navigation and active state
- Define saved-search behavior
- Virtualize the grid and expose filter/sort state accessibly

## Search No Results

Canonical reference: `02-discovery/02-search-no-results-found.png`  
Status: NEEDS_REWORK  
Required fixes:
- Normalize the buyer navigation
- Keep the query and route context during suggested recovery

## Filter Panel

Canonical reference: `02-discovery/02-filter-panel-category-price-color-material.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use collapsible/scrollable sections and accessible swatches/range values
- Preserve focus and bottom safe area as a modal/bottom sheet

## Collection / Shop the Look

Canonical reference: None safe  
Rejected source: `02-discovery/02-collection-salon-contemporain-shop-the-look.png`  
Status: REJECTED  
Required remake:
- Replace invalid navigation
- Replace unusable five-across cards with a responsive product rail/grid

## Flash Deals

Canonical reference: None safe  
Rejected source: `02-discovery/02-flash-deals-countdown-timer.png`  
Status: REJECTED  
Required remake:
- Restore official logo and remove non-furniture inventory
- Restore buyer navigation
- NEEDS PRODUCT DECISION for campaign timing and expiry behavior

## Promotions

Canonical reference: None safe  
Rejected source: `02-discovery/02-promotions-campaigns-offers.png`  
Status: REJECTED  
Required remake:
- Restore official logo and buyer tabs
- Bind campaign eligibility/dates dynamically

## Recently Viewed

Canonical reference: None safe  
Rejected source: `02-discovery/02-recently-viewed-products.png`  
Status: REJECTED  
Required remake:
- Remove the permanent Recents tab
- NEEDS PRODUCT DECISION for retention and clearing behavior

---

# Product

## Product Detail

Canonical reference: `03-product/03-product-detail-image-carousel-add-to-cart.png`  
Status: NEEDS_REWORK  
Required fixes:
- Replace Explorer with Panier
- Make the sticky add-to-cart footer keyboard/safe-area aware
- Bind availability, price, variants, favorite, delivery, and seller data dynamically

## Full Description / Reviews / Specifications Concept

Canonical reference: None as a complete page  
Rejected source: `03-product/03-product-detail-full-description-reviews-specs.png`  
Status: REJECTED  
Required remake:
- Split the desktop-style two-column poster into mobile sections/tabs or progressive disclosure
- Support Dynamic Type and reachable controls

## Product Gallery

Canonical reference: `03-product/03-product-gallery-zoom-thumbnails.png`  
Status: APPROVED_WITH_MINOR_FIXES  
Required fixes:
- Change `Pinch pour zoomer` to `Pincez pour zoomer`
- Provide accessible zoom/image-navigation controls and safe modal dismissal

## Variant Selector

Canonical reference: `03-product/03-product-variant-selector-color-material-size.png`  
Status: NEEDS_REWORK  
Required fixes:
- Drive valid combinations and stock from a variant matrix
- Give color/material/size controls full accessible names and state
- Validate all delivery/return policy claims

## Product Specifications

Canonical reference: None safe as a complete page  
Rejected source: `03-product/03-product-specifications-table.png`  
Status: REJECTED  
Required remake:
- Reuse only the grouped-row idea
- Replace Commandes-for-Panier navigation
- Bind validated catalog specifications and warranty data

## Product Reviews

Canonical reference: `03-product/03-product-customer-reviews-ratings.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use MAD in any purchase context
- NEEDS PRODUCT DECISION for review eligibility, moderation, sorting, media, and verified-purchase labels

## Delivery and Returns

Canonical reference: `03-product/03-product-delivery-returns-info.png`  
Status: NEEDS_REWORK  
Required fixes:
- Correct the active buyer navigation destination
- Validate every SLA, fee, return-window, and refund claim against product policy

## Add-to-Cart Confirmation

Canonical reference: `03-product/03-product-added-to-cart-confirmation.png`  
Status: APPROVED_WITH_MINOR_FIXES  
Required fixes:
- Define backdrop/system-back dismissal
- Move and restore accessibility focus
- Support compact-height scrolling and bottom safe-area padding

---

## Auth Welcome

Canonical reference: `04-auth/04-welcome-sign-in-create-account-guest-fr.png`  
Status: APPROVED_WITH_MINOR_FIXES  
Required fixes: Preserve the official logo, clarify guest continuation, and retain native safe-area spacing.

---

## Login

French reference: `04-auth/04-login-email-phone-password-fr.png` — APPROVED_WITH_MINOR_FIXES.  
Arabic reference: `04-auth/04-login-email-phone-password-ar.png` — NEEDS_REWORK.  
Required fixes: correct Arabic return affordance/field order, keyboard/autofill behaviour, password recovery, and a real disabled/loading/error state. Do not use `04-login-loading-state-fr.png` (REJECTED).

---

## Guest Login Prompt

Sources: `04-auth/04-login-prompt-overlay-favorites-fr.png` and `04-auth/04-login-prompt-overlay-wishlist-ar.png`.  
Status: NEEDS_REWORK  
Required fixes: retain only the component concept; define auth gating, dismiss behaviour, and correct wallet/currency/RTL content.

---

## Registration and Consent

French registration: `04-auth/04-registration-form-fr.png` — NEEDS_REWORK.  
Arabic registration: `04-auth/04-registration-form-ar.png` — NEEDS_REWORK.  
Consent: `04-auth/04-consent-terms-privacy-fr.png` — NEEDS_REWORK.  
Required fixes: define legal-required versus optional consent, country/phone rules, validation, password requirements, and submit states.

---

## Verification and Password Recovery

OTP French/Arabic: `04-auth/04-otp-phone-verification-fr.png` and `04-auth/04-otp-phone-verification-ar.png` — APPROVED_WITH_MINOR_FIXES.  
Email verification: `04-auth/04-email-verification-link-sent-fr.png` — NEEDS_REWORK.  
Forgot password French: `04-auth/04-forgot-password-enter-email-fr.png` — APPROVED_WITH_MINOR_FIXES; Arabic — NEEDS_REWORK.  
New password French: `04-auth/04-create-new-password-requirements-fr.png` — APPROVED_WITH_MINOR_FIXES; Arabic — NEEDS_REWORK.  
Required fixes: controlled OTP cooldown/error states, correct RTL, no stale terms claims, and no enabled submit before inputs validate.

---

## Auth Completion

Account created: `04-auth/04-account-created-success-fr.png` — APPROVED_WITH_MINOR_FIXES.  
Password changed: `04-auth/04-password-changed-success-fr.png` — REJECTED.  
Required fixes: use official brand and a route-safe success return; replace the invalid BUYER APP password-success source.

---

## Cart

French reference: `05-cart-wishlist/05-cart-promo-applied-order-summary-fr.png`  
Status: NEEDS_REWORK  
Arabic cart source: `05-cart-wishlist/05-cart-items-list-ar.png` — REJECTED.  
Required fixes: correct buyer navigation, official branding, real cart totals, MAD formatting, seller grouping, and checkout handoff. Do not use the currency/price-contradictory Arabic list.

---

## Cart Promo and Variant Controls

Promo sheet: `05-cart-wishlist/05-cart-promo-code-modal-available-offers-fr.png` — APPROVED_WITH_MINOR_FIXES.  
Variant sheet: `05-cart-wishlist/05-cart-modify-variant-bottom-sheet-fr.png` — APPROVED_WITH_MINOR_FIXES.  
Required fixes: preserve bottom-sheet keyboard behaviour, dynamic eligibility, stock, price recalculation, and result feedback.

---

## Cart Exceptions and Multi-vendor

Multi-vendor cart: `05-cart-wishlist/05-cart-multi-vendor-grouped-by-seller-fr.png` — NEEDS_REWORK.  
Empty/error/promo-invalid/skeleton states — NEEDS_REWORK.  
Guest merge and destructive remove confirmations — NEEDS_REWORK; stale-price update and the mislabeled quantity “toast” — REJECTED.  
Required fixes: define server reconciliation, destructive confirmation, errors, idempotency, and spinner/inline update semantics.

---

## Saved for Later and Wishlist

Saved for later: `05-cart-wishlist/05-saved-for-later-items-list-fr.png` — APPROVED_WITH_MINOR_FIXES.  
Wishlist empty/price change: `05-cart-wishlist/05-wishlist-empty-state-fr.png`, `05-cart-wishlist/05-wishlist-price-change-notifications-fr.png` — APPROVED_WITH_MINOR_FIXES.  
Grid/list/move-to-cart/out-of-stock sources — NEEDS_REWORK; remove-confirmation source — REJECTED.  
Required fixes: normalize five-tab navigation, dynamic variant/stock/price states, and an accessible destructive confirmation.

## Checkout: Add Address

Canonical reference: `06-checkout/06-add-new-address-form-fr.png`  
Arabic reference: `06-checkout/06-add-new-address-form-ar.png`  
Status: APPROVED_WITH_MINOR_FIXES  
Required fixes: keep controlled validation, keyboard-safe scrolling, locale-flexible address data, and disabled submit until valid.

---

## Checkout: Choose Address

Canonical reference: `06-checkout/06-choose-address-saved-list-fr.png`  
Arabic reference: `06-checkout/06-choose-address-saved-list-ar.png`  
Status: APPROVED_WITH_MINOR_FIXES  
Required fixes: Preserve safe-area spacing and make selection, editing, and empty-state recovery explicit.

---

## Checkout: City and Delivery Zone

City selector: `06-checkout/06-city-selector-list-fr.png` — APPROVED_WITH_MINOR_FIXES.  
Delivery-zone selector: `06-checkout/06-delivery-zone-selector-fr.png` — NEEDS_REWORK.  
Required fixes: bind cities/zones and fees to authoritative serviceability data; define unavailable-zone feedback.

---

## Checkout: Delivery Method

French reference: `06-checkout/06-choose-delivery-standard-express-relay-fr.png` — APPROVED_WITH_MINOR_FIXES.  
Arabic reference: `06-checkout/06-choose-delivery-standard-express-relay-ar.png` — NEEDS_REWORK.  
Required fixes: correct RTL back placement, reconcile locale prices, and base carrier/relay availability on the selected address and cart.

---

## Checkout: Summary and Loading

Summary reference: `06-checkout/06-checkout-summary-4step-overview-fr.png`  
Arabic reference: `06-checkout/06-checkout-summary-4step-ar.png`  
Status: NEEDS_REWORK  
Supporting states: `06-checkout/06-checkout-error-loading-state-fr.png` (APPROVED_WITH_MINOR_FIXES) and `06-checkout/06-checkout-skeleton-loading-state.png` (REFERENCE_ONLY).  
Required fixes: normalize one checkout progress model and derive stock, prices, discount, totals, and retry behaviour dynamically.

---

## Checkout: Payment Method

French reference: `06-checkout/06-choose-payment-cmi-cod-wallet-fr.png` — APPROVED_WITH_MINOR_FIXES.  
Arabic reference: `06-checkout/06-choose-payment-cmi-cod-wallet-ar.png` — NEEDS_REWORK.  
Required fixes: NEEDS PRODUCT DECISION for CMI, COD, wallet, saved-card tokenization, and refunds; remove expired default-card data.

---

## Checkout: Payment Redirect and Verification

Reference concept: `06-checkout/06-secure-payment-redirect-fr.png`  
Status: NEEDS_REWORK  
Required fixes: replace invented `secure.mayushdesign.ma/payment` display, define native/app-return and timeout/cancel recovery, and model verification/pending outcomes idempotently.

---

## Checkout: Order Review

Reference concept: `06-checkout/06-order-review-confirm-multi-vendor-fr.png`  
Arabic reference: `06-checkout/06-order-review-confirm-ar.png`  
Status: NEEDS_REWORK  
Required fixes: correct the 8,440 MAD minus 800 MAD arithmetic, disable payment until required terms are accepted, and calculate packages/seller totals from live data.

---

## Checkout: Confirmation and Outcomes

Success reference: `06-checkout/06-payment-confirmed-success-fr.png` — APPROVED_WITH_MINOR_FIXES.  
Cancelled reference: `06-checkout/06-payment-cancelled-resume-fr.png` — APPROVED_WITH_MINOR_FIXES.  
All other confirmation, pending, failed, delayed, wallet, COD, review-update, and processing visuals are NEEDS_REWORK or REFERENCE_ONLY.

Required fixes: remove static/incorrect dates and `#FR-XXXXXX`, avoid unsupported “not charged” claims after a failed gateway response, and implement idempotency, polling/backoff, timeout, and app-resume recovery.

---

## Checkout version alternatives

The 13 byte-identical v2 assets are DUPLICATE_ALTERNATIVE. Use the non-v2 canonical references above; see `duplicate-screen-review.md` for exact file groups.

# System states

> **Fact-check correction — do not select a direct System States canonical screen from the entries below.** The `10-system-states/` assets are an exported presentation/deck sequence (for example, visible `37 / 46` and `39 / 46` pagination) with alternate brand marks and palettes. The 35 non-duplicate/non-mismatched originals are **REFERENCE_ONLY**; the 17 exact v2 copies are **DUPLICATE_ALTERNATIVE**; and the 11 filename/state swaps are **REJECTED**. Recreate the needed native system states from the foundation components and valid product behavior instead.

## Session Restoration

Canonical reference: `10-system-states/10-session-restoration-fr.png`  
Status: APPROVED_WITH_MINOR_FIXES  
Required fixes:
- Change the final-sounding `Sécurité vérifiée` label while verification is still running
- Remove the unrelated notification bell

## Generic Loading Skeleton Components

Canonical reference: `10-system-states/10-content-loading-skeleton-fr.png`  
Status: REFERENCE_ONLY  
Required fixes:
- Recompose skeleton primitives to match each destination route
- Preserve stable route chrome and bottom navigation

## App Initialization

Canonical reference: `10-system-states/10-app-initialization-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Replace the modified logo with the official asset
- Remove gallery count and pagination artifacts
- Back progress with real startup stages or use an indeterminate loader

## Account Synchronization

Canonical reference: `10-system-states/10-account-sync-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use the official logo
- Add explicit timeout/failure/retry behavior

## Background Synchronization

Canonical reference: `10-system-states/10-background-sync-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use the official logo
- Convert to nonblocking feedback or provide a clear dismissal/resume path

## Data Restoration

Canonical reference: `10-system-states/10-data-restoration-progress-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Replace blue styling with Mayush tokens
- Restore Accueil, Catégories, Favoris, Panier, Compte navigation
- Define interruption, failure, and retry states

## Session Expired

Canonical reference: `10-system-states/10-session-expired-ar.png` (Arabic only)  
Status: NEEDS_REWORK  
Required fixes:
- Use the official logo and remove `BUYER APP`
- Remove `45/46` gallery chrome
- Preserve a safe post-login return route
- No reliable correctly named French reference exists in this folder

## Session Restored

Canonical reference: `10-system-states/10-session-restored-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use official branding and remove gallery artifacts
- Remove the unrelated language picker
- Auto-resume the intended route or retain one focused continue action
- Use a real timestamp or omit it

## Too Many Attempts

Canonical reference: `10-system-states/10-too-many-attempts-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use the official logo
- Replace disabled `Patienter` with passive countdown feedback
- Enable an actual retry/recovery action when the server lockout expires

## Account on Another Device

Canonical reference: `10-system-states/10-account-other-device-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Replace modified branding and remove gallery artifacts
- Clarify whether logout affects this device or the other session
- Show safe device/session context

## Password Changed

Canonical reference: `10-system-states/10-password-changed-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Restore official logo and palette; reserve green for semantic success
- Remove gallery chrome
- NEEDS PRODUCT DECISION: whether other sessions are revoked

## Biometric Unavailable

Canonical reference: `10-system-states/10-biometric-unavailable-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use official branding and remove `33/46`
- Handle system-settings deep-link failure

## Camera Permission Denied

Canonical reference: `10-system-states/10-camera-access-denied-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use the official logo
- Explain which buyer feature requested the camera
- Provide a settings-link fallback

## Photo Permission Denied

Canonical reference: `10-system-states/10-photos-access-denied-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use official branding and remove `32/46`
- Support limited photo-library permission where available

## Location Permission Denied

Canonical reference: `10-system-states/10-location-access-denied-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use official branding and remove `31/46`
- Retain manual city/address entry

## Notification Permission Denied

Canonical reference: None reliable  
Status: REJECTED  
Reason: `10-offline-cached-content-fr.png` visibly shows this concept but is named as a different route and cannot be trusted as the implementation contract.

## Offline — Arabic

Canonical reference: `10-system-states/10-offline-ar.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use the official logo and remove gallery chrome
- Only offer offline continuation when usable cached content exists

## Offline — French

Canonical reference: None reliable  
Status: REJECTED  
Reason: The visible French offline composition is stored as `10-notifications-disabled-fr.png`, while `10-offline-fr.png` shows an unrelated update success.

## Connection Timeout

Canonical reference: `10-system-states/10-connection-timeout-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use the official logo
- Gate offline continuation by actual cache capability

## Reconnection Progress

Canonical reference: `10-system-states/10-reconnection-progress-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use the official logo
- Prefer a transient nonblocking overlay/banner and automatically resume the failed action

## Connection Restored

Canonical reference: `10-system-states/10-connection-restored-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use official branding
- Remove unrelated `Voir les nouveautés`
- Prefer transient success feedback and resume the interrupted route

## Generic Error

Canonical reference: `10-system-states/10-generic-error-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use the official logo
- Preserve retry context and prevent endless failure loops

## Server Error 500

Canonical reference: `10-system-states/10-server-error-500-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use the official logo
- Bind retry to the failed request and expose a support code only when useful

## Server Unavailable — French

Canonical reference: `10-system-states/10-server-unavailable-detailed-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use the official logo
- Keep diagnostic copy concise and preserve retry context

## Server Unavailable — Arabic

Canonical reference: `10-system-states/10-server-unavailable-ar.png`  
Status: NEEDS_REWORK  
Required fixes:
- Preserve the exact official logo
- Remove `43/46` gallery chrome

## Page Not Found

Canonical reference: `10-system-states/10-page-not-found-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use official branding
- Use native navigation-fallback copy and a safe authenticated return route

## Scheduled Maintenance

Canonical reference: `10-system-states/10-scheduled-maintenance-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use the official logo
- Render backend-controlled localized date/time with timezone
- Define extension and retry-at-end behavior

## Maintenance Completed — French

Canonical reference: `10-system-states/10-maintenance-completed-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use official branding
- Resume the interrupted route instead of sending the buyer to new content

## Maintenance Completed — Arabic

Canonical reference: `10-system-states/10-maintenance-completed-ar.png`  
Status: NEEDS_REWORK  
Required fixes:
- Mirror the back arrow and place it on the RTL-leading edge
- Preserve the official logo and remove `46/46`
- Professionally localize the secondary action

## Optional Update

Canonical reference: `10-system-states/10-update-available-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use official branding
- Bind version, date, store URL, and re-prompt policy dynamically

## Mandatory Update — French

Canonical reference: `10-system-states/10-update-required-fr.png`  
Status: NEEDS_REWORK  
Required fixes:
- Use official branding
- Clarify that `Fermer` exits rather than bypasses the version gate
- Define offline/store-unavailable fallback behavior

## Mandatory Update — Arabic

Canonical reference: `10-system-states/10-update-required-ar.png`  
Status: NEEDS_REWORK  
Required fixes:
- Preserve the official logo and remove `44/46`
- Define offline/store-unavailable fallback behavior

## Update Download / Installation / Failure

Canonical reference: None pending product decision  
Status: NEEDS_REWORK  
Reason: `10-update-download-progress-fr.png` and `10-update-failed-fr.png` assume an in-app update lifecycle; `10-installation-progress-fr.png` is rejected because normal native distribution is store-managed. Define App Store/Play Store versus approved OTA/content-update behavior first.

## Native Splash

Canonical reference: `01-entry/01-splash-screen-logo.png`  
Status: APPROVED  
Required fixes: None. Both System States splash files are duplicate alternatives with forbidden `BUYER APP` wording.

## Access Denied / Account Blocked / Unusual Activity

Canonical reference: None reliable  
Status: REJECTED  
Reason: `10-access-denied-403-fr.png`, `10-account-blocked-fr.png`, and `10-unusual-activity-detected-fr.png` contradict their filenames or duplicate an unrelated server error. Security-sensitive routes require separate product-approved recovery logic.


---

# Support and settings

## Canonical route/state recommendations

| Route or state | Recommended canonical file | Status | Required correction before implementation |
|---|---|---|---|
| Settings index (FR) | `09-settings-menu-full-list-fr.png` | APPROVED_WITH_MINOR_FIXES | Group rows and validate Dynamic Type/scroll. |
| Settings index (AR) | `09-settings-menu-ar.png` | NEEDS_REWORK | Translate/mirror the French taxonomy; remove currency switch; danger-style logout. |
| Settings loading | `09-settings-skeleton-loading-state.png` | NEEDS_REWORK | Match loaded shell, remove interactive-looking controls, normalize navigation. |
| Settings error | `09-settings-error-loading-state-fr.png` | APPROVED_WITH_MINOR_FIXES | Use connectivity-aware copy and retry loading feedback. |
| Language | `09-choose-language-french-arabic-fr.png` | NEEDS_REWORK | Remove Chinese glyph, define locale-reload behavior and unchanged selection state. |
| Accessibility | `09-accessibility-settings-text-contrast-fr.png` | NEEDS_REWORK | Reflect native OS capabilities and prove maximum Dynamic Type. |
| Permissions | `09-app-permissions-camera-photos-location-fr.png` | NEEDS_REWORK | Replace impossible bulk grant with contextual state-specific native requests. |
| Data usage | `09-data-usage-image-quality-wifi-cache-fr.png` | NEEDS_REWORK | Fix tab order; unify live cache value and destructive semantics. |
| Storage/cache | `09-storage-cache-management-fr.png` | NEEDS_REWORK | Use live values and semantic-danger confirmation. |
| Clear-cache confirmation | Combine `09-storage-cache-management-fr.png` with `09-clear-cache-confirmation-dialog-fr.png` | NEEDS_REWORK | Implement the latter's copy as a dialog/sheet, not a full route. |
| Privacy hub | `09-privacy-data-policies-delete-account-fr.png` | NEEDS_REWORK | Fix bottom inset/tab order and separate account deletion. |
| Marketing preferences | `09-marketing-preferences-detailed-fr.png` | APPROVED_WITH_MINOR_FIXES | Add save/error feedback and legal consent mapping. |
| Notification preferences | `09-notification-settings-matrix-grid-fr.png` | REFERENCE_ONLY | Rebuild as vertical category cards; do not use matrix layout directly. |
| Silent hours | `09-silent-hours-day-selection-fr.png` | APPROVED_WITH_MINOR_FIXES | Use `Africa/Casablanca`; define critical-alert override. |
| Legal index | `09-legal-center-terms-policies-fr.png` | NEEDS_REWORK | Fix tabs and connect approved/versioned legal documents. |
| Privacy document | None; replace `09-privacy-policy-full-document-fr.png` | REJECTED | Create single-column responsive reader or use approved responsive browser document. |
| Company about | `09-about-mayush-design-company-fr.png` | NEEDS_REWORK | Runtime version and external-link behavior. |
| App/version about | `09-about-app-version-info-fr.png` | NEEDS_REWORK | Shared version source and compact header. |
| Optional update | `09-app-update-available-fr.png` | NEEDS_REWORK | Remove consent claim; explicit platform-store handoff. |
| Forced update | `09-forced-update-required-fr.png` | NEEDS_REWORK | Share version policy and foreground store recheck. |
| Maintenance | `09-maintenance-mode-services-impacted-fr.png` | APPROVED_WITH_MINOR_FIXES | Dynamic impacted services/ETA/last check. |
| Offline | `09-offline-mode-limited-functionality-fr.png` | NEEDS_REWORK | Define actual offline cache/sync scope and fix tabs. |
| Help center home (FR) | `09-help-center-home-categories-requests-fr.png` | NEEDS_REWORK | Fix tabs and data-drive hours/statuses. |
| Help center home (AR) | `09-help-center-home-ar.png` | NEEDS_REWORK | Fix mirrored logical tabs and RTL all-requests action. |
| FAQ list | `09-faq-tab-categories-fr.png` | NEEDS_REWORK | Move usefulness question and use scrollable accessible tabs. |
| FAQ article | `09-faq-article-track-order-steps-fr.png` | APPROVED_WITH_MINOR_FIXES | Replace `Cliquez`; verify tracking promises. |
| Help search populated | `09-help-center-search-results-fr.png` | NEEDS_REWORK | Fix tabs; add back and accessible clear-search behavior. |
| Orders/delivery help | `09-help-category-orders-delivery-fr.png` | APPROVED_WITH_MINOR_FIXES | Remove duplicate back action; gate order actions by eligibility. |
| Contact support form | `09-contact-support-form-fr.png` | NEEDS_REWORK | Clarify taxonomy; unify attachment policy; verify WhatsApp. |
| Select order for support | `09-select-order-for-support-fr.png` | NEEDS_REWORK | Add explicit selection and disabled CTA until selected. |
| Attachments | `09-attach-files-documents-fr.png` | NEEDS_REWORK | Confirm MIME/count/size and show upload/permission states. |
| Review request | `09-review-send-support-request-fr.png` | APPROVED_WITH_MINOR_FIXES | Fix copy/privacy wording and edit targets. |
| Request sent | `09-support-request-sent-success-fr.png` | APPROVED | Can guide implementation directly. |
| Ticket list populated | `09-my-support-tickets-list-fr.png` | NEEDS_REWORK | Fix tabs and bottom content inset. |
| Ticket list empty | `09-no-support-requests-empty-state-fr.png` | APPROVED_WITH_MINOR_FIXES | Clarify support channel and reduce hero on short screens. |
| Open ticket detail | `09-ticket-detail-conversation-thread-fr.png` | APPROVED_WITH_MINOR_FIXES | Clarify priority icon and secure attachment preview. |
| Reply composer | Combine `09-reply-to-support-message-fr.png` with canonical ticket detail | DUPLICATE_ALTERNATIVE | Reuse composer only; unify attachment limit/keyboard states. |
| Close ticket confirmation | `09-close-request-confirmation-fr.png` content in a dialog/sheet | NEEDS_REWORK | Use semantic danger and preserve ticket context. |
| Resolved ticket/rating | `09-ticket-resolved-rating-fr.png` | NEEDS_REWORK | Unify `SUP-` identifier/terminology and bind refund data. |
| Support connection error | `09-support-connection-error-fr.png` | APPROVED_WITH_MINOR_FIXES | Verify email and retry state. |
| Support unavailable | `09-support-temporarily-unavailable-fr.png` | APPROVED_WITH_MINOR_FIXES | Add last-check/loading and verified fallback. |


---

# Orders

## Main orders list

Canonical reference: `07-orders-list-all-tabs-fr.png`  
Arabic reference: `07-orders-list-all-tabs-ar.png`  
Status: NEEDS_REWORK  
Required fixes: French bottom tabs must be `Accueil`, `Catégories`, `Favoris`, `Panier`, `Compte`; Arabic bottom destinations must be rebuilt as the mirrored buyer set and must remove `منتجاتي`/`طلباتي` seller-like tabs.

## Orders list — in progress

Canonical structure: `07-orders-list-all-tabs-fr.png`  
Status/action content source: `07-orders-in-progress-tab-statuses-fr.png`  
Status: NEEDS_REWORK  
Required fixes: Reuse the active-order card variants but discard the permanent `Commandes` bottom tab.

## Orders list — completed

Canonical reference: `07-orders-completed-tab-reorder-review-fr.png`  
Status: APPROVED_WITH_MINOR_FIXES  
Required fixes: Make three card actions responsive at large text sizes; remove or justify the redundant header cart shortcut.

## Orders list — cancelled/refund states

Canonical structure: `07-orders-list-all-tabs-fr.png`  
Status content source: `07-orders-cancelled-tab-refund-statuses-fr.png`  
Status: NEEDS_REWORK  
Required fixes: Keep the standard bottom navigation and explicitly label partial refund amounts versus original order totals.

## Orders list — loading

Canonical reference: `07-orders-skeleton-loading-state.png`  
Status: NEEDS_REWORK  
Required fixes: Correct the bottom-tab order, hide skeleton blocks from assistive technology, and announce loading once.

## Orders list — empty

Canonical reference: `07-orders-empty-state-fr.png`  
Status: NEEDS_REWORK  
Required fixes: Replace `Explorer` with `Catégories`, use the compact header, and keep empty content inside the OrdersList/tab context.

## Orders list — error

Canonical reference: `07-orders-error-loading-state-fr.png`  
Status: NEEDS_REWORK  
Required fixes: Preserve the OrdersList shell/bottom tabs; add retry loading, timeout, and repeated-failure feedback.

## Order detail — shared/in preparation

Canonical reference: `07-order-detail-in-preparation-timeline-fr.png`  
Status: APPROVED_WITH_MINOR_FIXES  
Required fixes: Generate all weekday names from timestamps and expose stepper state semantically.

## Order detail — delivered

Canonical reference: `07-order-detail-delivered-actions-fr.png`  
Status: APPROVED_WITH_MINOR_FIXES  
Required fixes: Correct the weekday and make the post-purchase action grid adapt to Dynamic Type.

## Order detail — shipped

Canonical reference: `07-order-detail-shipped-tracking-fr.png`  
Status: NEEDS_REWORK  
Required fixes: Use the foundation shipped-blue badge, repair weekday/date ranges, and normalize French MAD formatting.

## Order detail — Arabic

Canonical reference: None safe  
Rejected source: `07-order-detail-ar.png`  
Status: REJECTED  
Required remake: Fix COD-versus-paid contradiction, reconcile product/subtotal/total money, use the right-pointing RTL back arrow, and disable late address editing according to product policy.

## Order detail — loading

Canonical reference: `07-order-detail-skeleton-loading-state.png`  
Status: APPROVED_WITH_MINOR_FIXES  
Required fixes: Neutralize orange action placeholders and add accessible loading semantics.

## Order not found

Canonical reference: `07-order-not-found-error-fr.png`  
Status: APPROVED_WITH_MINOR_FIXES  
Required fixes: Add back/deep-link recovery and align copy with available retry actions.

## Multi-package overview

Canonical reference: `07-multiple-packages-split-shipment-fr.png`  
Status: NEEDS_REWORK  
Required fixes: Normalize status tokens and confirm multi-vendor/carrier visibility. Use expandable package cards with preserved state.

## Multi-vendor order detail

Canonical reference: `07-order-detail-multi-vendor-packages-fr.png`  
Status: NEEDS_REWORK  
Required fixes: Remove pre-order delivery dates and resolve aggregate versus package status contradictions.

## Single-package detail

Canonical reference: `07-package-detail-items-shipping-info-fr.png`  
Status: NEEDS_REWORK  
Required fixes: Correct the active-delivery badge color, weekday, and tracking-copy feedback.

## Order tracking — French

Canonical reference: `07-order-tracking-timeline-realtime-fr.png`  
Status: APPROVED_WITH_MINOR_FIXES  
Required fixes: Correct weekday generation and keep support secondary unless an exception exists.

## Order tracking — Arabic

Canonical reference: None safe  
Rejected source: `07-order-tracking-timeline-ar.png`  
Status: REJECTED  
Required remake: Sort/validate monotonic timestamps, correct the localized weekday, and map one canonical Arabic fulfillment vocabulary.

## Tracking unavailable

Canonical reference: `07-tracking-unavailable-in-preparation-fr.png`  
Status: NEEDS_REWORK  
Required fixes: Add back navigation and refresh progress/unchanged-state feedback; clarify the multi-item summary.

## Delivery delayed

Canonical reference: `07-delivery-delayed-notification-fr.png`  
Status: NEEDS_REWORK  
Required fixes: Repair the order identifier/year, timestamp-derived weekday labels, and irrelevant payment-security copy.

## Delivery failed/reschedule

Canonical reference: `07-delivery-failed-reschedule-fr.png`  
Status: NEEDS_REWORK  
Required fixes: Use carrier-backed reschedule eligibility/slots, repair weekday labels, and define no-slot/failure recovery.

## Cancellation reason

Canonical reference: `07-cancel-order-reason-form-fr.png`  
Status: NEEDS_REWORK  
Required fixes: NEEDS PRODUCT DECISION for cancellation windows/seller approval; add required-reason and submit feedback.

## Cancellation confirmation

Canonical reference: None safe as a dialog  
Component/content source: `07-cancel-order-confirmation-dialog-fr.png`  
Status: REFERENCE_ONLY  
Required remake: Use a concise accessible modal/bottom sheet or a standard full route, semantic destructive styling, and one unambiguous action outcome.

## Cancellation request submitted

Canonical reference: `07-cancellation-request-registered-fr.png`  
Status: NEEDS_REWORK  
Required fixes: Use pending rather than success semantics until approval and separate cancellation approval from refund review.

## Cancellation unavailable

Canonical reference: `07-order-cannot-be-cancelled-fr.png`  
Status: NEEDS_REWORK  
Required fixes: Do not expose an enabled return action before delivery; add back navigation and define state eligibility.

## Invoice

Canonical reference: `07-invoice-detail-download-share-fr.png`  
Status: NEEDS_REWORK  
Required fixes: NEEDS PRODUCT DECISION for HT/TTC tax logic; render authoritative fiscal totals and implement native export/share recovery.

## Product ratings

Canonical reference: `07-rate-order-review-products-fr.png`  
Status: NEEDS_REWORK  
Required fixes: Define required ratings, optional comments, disabled CTA, accessible star labels, and submit outcomes.

## Reorder availability review

Canonical reference: `07-reorder-with-availability-changes-fr.png`  
Status: APPROVED_WITH_MINOR_FIXES  
Required fixes: Disable unavailable-item controls, normalize price formatting, and revalidate stock/price on submit.

## Reorder success

Canonical reference: `07-reorder-items-added-to-cart-fr.png`  
Status: APPROVED  
Required fixes: None beyond standard responsive/accessibility implementation.

## Refund request for cancelled order

Canonical reference: Pending product decision; source `07-request-refund-cancelled-order-fr.png`  
Status: NEEDS_REWORK  
Required fixes: Decide whether cancellation refunds are automatic or manually requested; enforce idempotency.

## Refund completed

Canonical reference: None safe  
Rejected source: `07-refund-completed-success-fr.png`  
Status: REJECTED  
Required remake: Reconcile returned line amounts with the completed refund and use semantic refunded-success styling.

## Return request

Canonical reference: None safe  
Rejected source: `07-request-return-item-selection-fr.png`  
Status: REJECTED  
Required remake: Correct SKU imagery, use one selection model, define evidence requirements for damage, and add validation.

## Return detail

Canonical reference: `07-return-detail-items-refund-status-fr.png`  
Status: APPROVED  
Required fixes: None beyond authoritative dynamic binding and responsive accessibility.

## Return tracking

Canonical reference: `07-return-tracking-timeline-fr.png`  
Status: NEEDS_REWORK  
Required fixes: Show `Contrôle qualité en cours` rather than premature refund progress, add back navigation, and correct weekday copy.

## Order support form

Canonical reference: `07-support-order-contact-form-fr.png`  
Status: APPROVED_WITH_MINOR_FIXES  
Required fixes: Add validation/upload/submit states, unsaved-draft protection, and configured support contacts.


---

# Account

## Canonical route/state recommendations

| Route/state | Canonical file | Status | Key correction |
|---|---|---|---|
| Account home FR | `08-account-dashboard-profile-menu-fr.png` | NEEDS_REWORK | Exact five tabs and trusted account-state content |
| Account home AR | `08-account-dashboard-profile-menu-ar.png` | NEEDS_REWORK | Replace Orders tab with Categories; keep RTL |
| Guest account | `08-account-guest-welcome-login-fr.png` | APPROVED_WITH_MINOR_FIXES | Define/remove guest notification icon |
| Account settings | `08-account-settings-menu-photo-fr.png` | NEEDS_REWORK | Correct tab order and use actual avatar |
| Profile details | `08-my-information-personal-details-fr.png` | NEEDS_REWORK | Normalize tabs and verification meaning |
| Edit profile FR | `08-edit-profile-form-fr.png` | NEEDS_REWORK | Verified sensitive-field flows |
| Edit profile AR | `08-edit-profile-form-ar.png` | NEEDS_REWORK | Mirrored return arrow and sensitive-field flow |
| Profile completion | `08-complete-profile-progress-60-fr.png` | NEEDS_REWORK | Resolve 60% versus visible 75% contradiction |
| Address list FR | `08-my-addresses-list-v2-fr.png` | NEEDS_REWORK | Restore Panier and action consistency |
| Address list AR | `08-my-addresses-list-ar.png` | NEEDS_REWORK | Fix RTL back arrow and bottom tabs |
| Add address | `08-add-address-form-v2-fr.png` | NEEDS_REWORK | Persistent labels; postal/default controls; remove wrong tabs |
| Edit address | `08-edit-address-form-fr.png` | NEEDS_REWORK | Remove wrong tabs and duplicated apartment value |
| Delete address confirmation | `08-delete-address-confirmation-fr.png` | NEEDS_REWORK | Convert full page to destructive dialog |
| Security/privacy index | `08-security-privacy-full-menu-fr.png` | APPROVED_WITH_MINOR_FIXES | Add back and approved 2FA row |
| Change password | `08-change-password-form-fr.png` | APPROVED_WITH_MINOR_FIXES | Neutral initial strength state |
| Password success | `08-password-changed-success-fr.png` | NEEDS_REWORK | Remove invalid tabs and reset submitted stack |
| Change email | `08-change-email-form-fr.png` | NEEDS_REWORK | Mandatory recent re-authentication/product decision |
| Change phone | `08-change-phone-number-fr.png` | APPROVED_WITH_MINOR_FIXES | Minor icon and enabled-state polish |
| Verify phone OTP | `08-verify-phone-otp-code-fr.png` | NEEDS_REWORK | Cooldown/empty-code state logic |
| Active sessions | `08-active-sessions-devices-v2-fr.png` | NEEDS_REWORK | Danger styling/confirmation and full metadata |
| Disconnect device | `08-disconnect-device-confirmation-fr.png` | NEEDS_REWORK | Danger CTA and remote-session wording |
| Logout confirmation | `08-logout-confirmation-dialog-fr.png` | NEEDS_REWORK | Remove BUYER APP; compact accessible modal |
| Payment methods | `08-payment-methods-card-cod-wallet-fr.png` | NEEDS_REWORK | Decide tokenized cards/COD/wallet first |
| Language & region | `08-language-region-preferences-fr.png` | NEEDS_REWORK | Confirm supported languages and market settings |
| Language selection | `08-language-selection-3-languages-fr.png` | NEEDS_REWORK | Confirm/remove English; fix nav |
| Marketing preferences | `08-marketing-preferences-cart-reminders-fr.png` | APPROVED_WITH_MINOR_FIXES | Icon/save feedback |
| Notification settings | `08-notification-settings-toggles-fr.png` | APPROVED_WITH_MINOR_FIXES | Define mandatory notices and save feedback |
| Quiet hours | `08-silent-hours-day-selection-fr.png` | APPROVED_WITH_MINOR_FIXES | Improve unselected contrast; define bypass rules |
| Notification detail template | `08-notification-detail-order-shipped-fr.png` | APPROVED_WITH_MINOR_FIXES | Shared status/template tokens |
| Preparation notification state | `08-notification-detail-order-preparation-fr.png` | NEEDS_REWORK | Use `En préparation` |
| Help center | `08-help-center-categories-fr.png` | APPROVED_WITH_MINOR_FIXES | Add back and approved contact route |
| FAQ list | `08-faq-accordion-questions-fr.png` | NEEDS_REWORK | Validate cancellation policy and add back |
| FAQ article layout | `08-faq-detail-expanded-answer-fr.png` | REFERENCE_ONLY | Remove BUYER APP; use approved CMS content |


---

# Arabic and RTL

## Canonical Arabic candidates

These are not globally approved unless noted; their status/fixes must accompany the canonical merge.

| Route/state | Arabic candidate | Status |
|---|---|---|
| Home | `11-home-ar.png` | NEEDS_REWORK |
| Categories | `11-categories-ar.png` | NEEDS_REWORK |
| Search results | `11-search-results-ar.png` | NEEDS_REWORK |
| Product detail | `11-product-detail-ar.png` | NEEDS_REWORK |
| Cart | `11-cart-ar.png` | NEEDS_REWORK |
| Wishlist | `11-wishlist-ar.png` | NEEDS_REWORK |
| Checkout address | `11-checkout-address-ar.png` | NEEDS_REWORK |
| Payment method | `11-payment-method-ar.png` | NEEDS_REWORK / PRODUCT DECISION |
| Payment success | `11-payment-success-ar.png` | NEEDS_REWORK |
| Orders list | `11-orders-list-ar.png` | NEEDS_REWORK |
| Order detail | `11-order-detail-ar.png` | NEEDS_REWORK; payment/totals/RTL corrections required |
| Order tracking | `11-order-tracking-ar.png` | NEEDS_REWORK; status CRITICAL |
| Account | `11-account-dashboard-ar.png` | NEEDS_REWORK |
| Address list | `11-addresses-list-ar.png` | NEEDS_REWORK |
| Notification settings | `11-notification-settings-ar.png` | NEEDS_REWORK |
| Privacy/security | `11-privacy-security-ar.png` | NEEDS_REWORK |
| Help center | `11-help-center-ar.png` | NEEDS_REWORK |
| FAQ | `11-faq-ar.png` | NEEDS_REWORK |
| Support tickets | `11-support-tickets-ar.png` | NEEDS_REWORK |
| Support chat | `11-support-chat-ar.png` | APPROVED_WITH_MINOR_FIXES |
| Startup loading | `11-splash-loading-ar.png` | REFERENCE_ONLY (post-launch only) |
| Optional update | `11-update-available-ar.png` | NEEDS_REWORK; convert to modal |
| Mandatory update | none from this folder | `11-update-required-ar.png` REJECTED |

## Required cross-route Arabic corrections

- Use one bidi-safe buyer shell with official five-tab destinations and consistent Arabic labels.
- Place back on the right pointing right; forward/row chevrons belong on the left pointing left.
- Isolate email, phone numbers, IDs, dates/times, versions, dimensions, and amounts as readable LTR runs.
- Remove BUYER APP and تطبيق المشتري everywhere and preserve the official logo.
- Replace 11-update-required-ar.png; mandatory update cannot expose a Home bypass.
