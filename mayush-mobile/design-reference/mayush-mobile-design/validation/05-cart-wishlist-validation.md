# 05-cart-wishlist Validation Report

> **Fact-check scope:** Currency and address examples are accepted variations. Do not treat them as validation defects by themselves; [fact-check-correction.md](./fact-check-correction.md) supersedes earlier currency/address severity notes.

Folder: `05-cart-wishlist/`
Total screenshots: 22
Validation date: 2026-08-02

## Extracted validation rules used

- Preserve the official `MAYUSH DESIGN` mark; added `BUYER` wording is prohibited.
- Use the cream/orange/navy/white/beige system, rounded cards, low-elevation shadows, consistent outline icons, and semantic status colors paired with text/icons.
- All commerce amounts must use `MAD`; visible line totals, quantities, discounts, shipping, and grand totals must reconcile exactly.
- The buyer bottom navigation is exactly `Accueil`, `Catégories`, `Favoris`, `Panier`, `Compte`, with five consistent icons and one active treatment. Cart/wishlist full pages are top-level destinations and require it.
- Destructive actions require clear danger treatment and confirmation or undo. Disabled, loading, out-of-stock, price-change, and stock-change states must state the consequence and recovery path.
- Lists/grids must be dynamic, virtualized, safe-area aware, and resilient to Dynamic Type and long product/seller names. Every icon-only control needs a 44 pt / 48 dp target and accessibility label.
- Bottom sheets need a strong scrim, drag/close affordance, scrollable content, keyboard handling, and bottom insets; sticky CTAs must not obscure content or the home indicator.

## 05-cart-empty-state-fr.png

Folder: `05-cart-wishlist/`
Screen purpose: Explain an empty cart and route buyers back to discovery or favorites
Probable native route: `Cart`
Language: French
Screen type:
- Empty state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Top-level cart with no items, category suggestions and two recovery actions.

### Visible UI structure
- Header: hamburger menu, official logo and notifications
- Main content: empty-cart illustration, message and suggested categories
- Cards: horizontal category cards
- Forms: none
- Primary action: `Commencer mes achats`
- Secondary actions: view favorites and view all suggestions
- Navigation: five tabs, with `Rechercher` and `Profil` replacing required labels
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: cream/orange/navy direction is consistent.
- Typography: clear hierarchy; category counts are readable.
- Icons: outline family is mostly coherent.
- Buttons: primary and outlined actions match the system.
- Cards: suggestions use rounded image cards.
- Spacing: useful whitespace but high content volume for an empty state.
- Shadows: restrained.

### UX validation
- The empty state explains the problem and offers useful next steps.
- The permanent nav is incompatible: `Rechercher` replaces `Catégories`, `Profil` replaces `Compte`, and a hamburger introduces competing top-level navigation.
- Suggestions require horizontal-scroll affordance and virtualized dynamic data; buttons and chevrons need 44/48 targets.
- Bottom content must include tab-bar/safe-area insets.

### Native implementation usability
Feasible using shared empty state, buttons and a horizontal `FlatList`, but the navigation shell must be replaced.

### Reusable components identified
- `AppHeader`
- `EmptyState`
- `PrimaryButton`
- `OutlinedButton`
- `CategoryCard`
- `BuyerTabBar`

### Dynamic backend data required
- Cart count
- Suggested categories and images
- Category product counts
- Authentication/favorites availability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Tabs use `Rechercher` and `Profil` instead of `Catégories` and `Compte`. | Use the exact approved five-tab labels/icons and remove the competing hamburger. |
| MINOR | Empty-state density | Four suggestions plus two CTAs make the empty route long. | Keep suggestions horizontally scrollable and prioritize the primary recovery action. |

### Canonical recommendation
Correct the app shell before use; retain the empty illustration, copy and two recovery actions.

---

## 05-cart-error-loading-state-fr.png

Folder: `05-cart-wishlist/`
Screen purpose: Recover when cart data cannot be loaded
Probable native route: `Cart`
Language: French
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A top-level cart fetch failure with retry and shopping escape actions.

### Visible UI structure
- Header: official logo, cart badge and route title
- Main content: cart error illustration, failure message and reassurance
- Cards: none
- Forms: none
- Primary action: `Réessayer`
- Secondary actions: `Continuer mes achats`
- Navigation: five tabs with `Recherche` replacing `Catégories`
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: orange/navy/white with neutral error illustration is compatible.
- Typography: hierarchy and recovery copy are clear.
- Icons: refresh, bag and tab icons are coherent.
- Buttons: primary and outlined styles are consistent.
- Cards: illustration aligns with foundation error states.
- Spacing: centered and readable.
- Shadows: minimal.

### UX validation
- Cause category and retry path are clear; claim that nothing was lost is only valid if local/server state guarantees it.
- Required tab label `Catégories` is replaced by `Recherche`.
- Retry needs loading, disabled, timeout and repeated-failure behavior; screen-reader focus should move to the error heading.

### Native implementation usability
Reusable as an error state component after navigation correction and removal/verification of the absolute data-loss claim.

### Reusable components identified
- `AppHeader`
- `ErrorState`
- `PrimaryButton`
- `OutlinedButton`
- `BuyerTabBar`

### Dynamic backend data required
- Cart-fetch error state
- Retry status
- Cart count, if cached

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | `Recherche` replaces required `Catégories`. | Normalize the exact approved five-tab navigation. |
| MAJOR | Product claim | `Aucun changement n’a été perdu` may be false after a failed synchronization. | Show only if guaranteed; otherwise use neutral retry copy. NEEDS PRODUCT DECISION. |
| MINOR | Error recovery | Retry loading and repeated failure are not represented. | Disable while retrying and provide persistent failure/support recovery. |

### Canonical recommendation
Correct navigation and reassurance copy before use; retain the retry-first error hierarchy.

---

## 05-cart-invalid-promo-code-error-fr.png

Folder: `05-cart-wishlist/`
Screen purpose: Explain why a promotion cannot be applied to the current cart
Probable native route: `Cart` with `PromoErrorSheet`
Language: French
Screen type:
- Bottom sheet
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A populated cart showing an invalid/inapplicable promo code and recovery actions.

### Visible UI structure
- Header: official logo, notification and cart badge
- Main content: promo input, three item cards and order summary
- Cards: item cards and total card
- Forms: promo-code field with apply action
- Primary action: `Voir les offres disponibles`
- Secondary actions: try another code and dismiss
- Navigation: correct five buyer tabs
- Overlays: large promo-error sheet plus a second inline error banner

### Brand validation
- Logo: official and undistorted.
- Colors: approved palette with semantic red error.
- Typography: clear and all UI copy is French.
- Icons: consistent outline family.
- Buttons: orange primary and outlined secondary follow the system.
- Cards: compact cart cards and summary are coherent.
- Spacing: compressed by simultaneous duplicate error surfaces.
- Shadows: sheet/scrim separation is adequate.

### UX validation
- The error explains that the code is not applicable and exposes two recovery paths.
- The same error appears inline and in the sheet simultaneously, creating duplicate focus/announcement and obscuring the cart total/CTA.
- Applying promo should expose loading/disabled state and preserve the typed code; sheet must be keyboard-aware and scrollable.
- Dismiss icons require 44/48 targets and a single accessible error announcement.

### Native implementation usability
All components are feasible, but use either inline feedback or the sheet—not both at once.

### Reusable components identified
- `PromoCodeInput`
- `InlineErrorBanner`
- `PromoErrorSheet`
- `CartItemCard`
- `OrderSummaryCard`
- `BuyerTabBar`

### Dynamic backend data required
- Cart items and quantities
- Product prices/availability
- Promo validation result and reason
- Cart subtotal, shipping, tax and total
- Available promotions

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Feedback hierarchy | Identical error is shown inline and in a bottom sheet simultaneously. | Choose one contextual error surface and announce it once. |
| MAJOR | Mobile usability | Sheet obscures the order summary and likely checkout action. | Size to content, support scrolling/keyboard and preserve a clear dismissal path. |
| MINOR | Accessibility | Multiple small close icons may have insufficient targets and duplicate announcements. | Use one 44/48 close control with a descriptive label. |

### Canonical recommendation
Correct the duplicate feedback pattern before use; keep the message and recovery-choice content.

---

## 05-cart-items-list-ar.png

Folder: `05-cart-wishlist/`
Screen purpose: Review and update a populated cart in Arabic
Probable native route: `Cart`
Language: Arabic
Screen type:
- Full page

### Status
REJECTED

### Confidence
High

### What the screen represents
RTL cart with three items, seller metadata, quantity controls, provisional totals and checkout CTA.

### Visible UI structure
- Header: notification, official logo and right-side back arrow
- Main content: shipping notice, three item cards and summary
- Cards: product rows and totals card
- Forms: quantity steppers
- Primary action: complete order
- Secondary actions: remove items
- Navigation: correct five destinations in mirrored RTL order
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: cream/orange/navy with semantic green are consistent.
- Typography: Arabic is readable; `MAD` remains legible.
- Icons: coherent outline family.
- Buttons: primary orange checkout CTA matches the system.
- Cards: rounded product and summary cards are consistent.
- Spacing: dense but scannable.
- Shadows: soft and restrained.

### UX validation
- RTL hierarchy, `MAD`, seller detail and stock-related shipping copy are understandable.
- Visible arithmetic is wrong: `450 × 1 + 850 × 2 + 650 × 1 = 2,800 MAD`, but subtotal and total show `1,950 MAD`.
- Checkout from an incorrect total is a critical misleading commerce state.
- Quantity updates need per-row loading, stock caps, rollback/error and live total announcement.

### Native implementation usability
The composition is implementable with an inverted/RTL virtualized list and shared cards, but the screenshot must not guide checkout totals.

### Reusable components identified
- `RTLAppHeader`
- `CartItemCard`
- `QuantityStepper`
- `OrderSummaryCard`
- `StickyCheckoutBar`
- `RTLBuyerTabBar`

### Dynamic backend data required
- Cart items, variants and quantities
- Seller names
- Unit prices and stock limits
- Shipping estimate
- Discount, subtotal and total

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Cart arithmetic | Line quantities total `2,800 MAD`, not the displayed `1,950 MAD`. | Compute summary from authoritative backend line totals and block checkout on mismatch. |
| MAJOR | State handling | Quantity steppers show no update/loading/error or stock-limit state. | Apply optimistic updates only with rollback, disable during mutation and announce totals. |
| MINOR | Safe area | Sticky CTA and tab bar need explicit scroll/bottom insets. | Add safe-area-aware sticky regions and content padding. |

### Canonical recommendation
Reject as a transaction/totals reference; use only the RTL layout ideas after pairing them with verified cart data.

## 05-cart-items-promo-code-summary-fr.png

Folder: `05-cart-wishlist/`
Screen purpose: Review a populated cart, save items, add a promo and proceed to checkout
Probable native route: `Cart`
Language: French
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A detailed three-item cart with variant edits, saved-for-later actions, a promo entry point and reconciled provisional summary.

### Visible UI structure
- Header: Mayush logo with added `BUYER`, search and notifications
- Main content: three large item cards, promo row and order summary
- Cards: product cards and summary card
- Forms: quantity steppers; promo entry opens another surface
- Primary action: `Passer à la commande`
- Secondary actions: modify variant, save for later and delete
- Navigation: correct five buyer tabs
- Overlays: none

### Brand validation
- Logo: prohibited `BUYER` qualifier changes the official lockup.
- Colors: orange/navy/white direction is otherwise consistent.
- Typography: readable but very dense.
- Icons: generally coherent outline family.
- Buttons: CTA treatment matches foundation.
- Cards: product cards use consistent radii/elevation.
- Spacing: crowded actions and content create a poster-density screen.
- Shadows: restrained.

### UX validation
- Amounts reconcile: `550 + 1,750 + 180 = 2,480 MAD`; after `240 MAD` reduction, total is `2,240 MAD`.
- Per-item action rows are crowded and likely produce sub-44/48 targets with Dynamic Type.
- Saved-for-later, variant editing and deletion need mutation states, confirmation/undo and list virtualization.
- Shipping correctly remains pending, but checkout must revalidate price/stock/promo.

### Native implementation usability
Feasible with reusable cards and a virtualized list, but the whole screenshot must not guide branding because the logo is explicitly invalid.

### Reusable components identified
- `CartItemCard`
- `QuantityStepper`
- `ItemActionBar`
- `PromoEntryRow`
- `OrderSummaryCard`
- `StickyCheckoutBar`
- `BuyerTabBar`

### Dynamic backend data required
- Cart items, variants, quantities and sellers
- Unit/line prices and availability
- Promotion and discount
- Shipping estimate
- Subtotal and total

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Logo includes prohibited `BUYER` wording. | Replace with the exact official Mayush logo. |
| MAJOR | Mobile usability | Three per-item text actions are cramped and will not scale with large type. | Move secondary actions to an overflow/action sheet or stack them with 44/48 targets. |
| MINOR | State handling | Item mutations and checkout revalidation are not represented. | Add pending/error/undo states and server revalidation. |

### Canonical recommendation
Correct before use. Keep the cart layout, product cards, promo entry, reconciled summary, and five-tab shell; replace only the prohibited `BUYER` lock-up and resolve the documented density/state-handling issues.

---

## 05-cart-merge-guest-account-fusion-fr.png

Folder: `05-cart-wishlist/`
Screen purpose: Resolve guest-cart and account-cart conflict after sign-in
Probable native route: `Cart/MergeConflict`
Language: French
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A post-login cart conflict offering merge or replacement choices.

### Visible UI structure
- Header: official logo
- Main content: merge illustration, two cart summaries and three choices
- Cards: guest/account comparison card
- Forms: none
- Primary action: merge carts
- Secondary actions: keep account cart or keep guest cart
- Navigation: no bottom tabs
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: brand orange/navy/cream are consistent.
- Typography: clear hierarchy; action labels are readable.
- Icons: merge, account and cart illustrations are stylistically coherent.
- Buttons: primary and outlined choices are consistent, but destructive consequences are not differentiated.
- Cards: comparison card follows rounded/elevated system.
- Spacing: clear and centered.
- Shadows: soft.

### UX validation
- All cart values use EUR, directly violating mandatory `MAD`.
- “Keep” choices actually replace one cart and can discard the other, yet no destructive warning, item-level preview, duplicate/stock resolution, confirmation or undo is provided.
- `Aucune donnée n’a été perdue` and “change your mind at any time” contradict replacement behavior unless server history truly preserves both carts. NEEDS PRODUCT DECISION.

### Native implementation usability
The decision layout is feasible, but the business behavior is unsafe and unresolved. It cannot serve as a buyer transaction reference.

### Reusable components identified
- `CartMergeIllustration`
- `CartComparisonCard`
- `PrimaryButton`
- `OutlinedButton`
- `ConfirmationDialog`

### Dynamic backend data required
- Guest/account cart item counts and totals
- Duplicate-item/variant conflicts
- Stock/price revalidation result
- Merge/replace result and recoverability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Business behavior | Replacement choices can discard cart contents without sufficient confirmation or conflict detail. | Define merge/dedup/stock rules, preview consequences and add confirmation plus recoverable undo. NEEDS PRODUCT DECISION. |
| MAJOR | Contradictory copy | “No data lost”/change anytime conflicts with replacement descriptions. | Use claims only if both carts are versioned and restorable. |

### Canonical recommendation
Correct before use. Retain the decision-card concept only after merge/deduplication, conflict disclosure, confirmation, and undo behavior are product-approved.

---

## 05-cart-modify-variant-bottom-sheet-fr.png

Folder: `05-cart-wishlist/`
Screen purpose: Change a cart item’s color, material, size and quantity
Probable native route: `Cart` with `VariantEditorSheet`
Language: French
Screen type:
- Bottom sheet

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A near-full-height cart item editor over the populated cart.

### Visible UI structure
- Header: dimmed cart header behind sheet
- Main content: product summary and grouped variant/quantity options
- Cards: color swatches, material buttons, dimension/price options and stock row
- Forms: variant selectors and quantity stepper
- Primary action: update item with current price
- Secondary actions: close/dismiss
- Navigation: underlying cart only
- Overlays: rounded bottom sheet with scrim, drag handle and close icon

### Brand validation
- Logo: official logo remains visible in background.
- Colors: cream/orange/navy/white and semantic green are consistent.
- Typography: clear grouping and pricing hierarchy.
- Icons: check, close, quantity and lock icons are coherent.
- Buttons: sticky orange CTA matches foundation.
- Cards: option cards use consistent radius/borders.
- Spacing: dense but logically grouped.
- Shadows: strong enough to separate the modal.

### UX validation
- Selected options, price deltas, stock and quantity are understandable.
- Copy says the variant causes a price difference while the visible delta is `+0 MAD`.
- Swatches need text/selected semantics and cannot rely on color alone; unavailable combinations and stock caps need disabled states.
- Sheet needs scroll, unsaved-change dismissal confirmation, bottom safe-area inset and mutation loading/error rollback.

### Native implementation usability
Strong reusable RN sheet using a scrollable content area and sticky safe-area CTA. Variant availability should come from backend combinations rather than static grids.

### Reusable components identified
- `VariantEditorSheet`
- `ProductSummaryRow`
- `ColorSwatchOption`
- `SegmentedOption`
- `QuantityStepper`
- `StickyActionBar`

### Dynamic backend data required
- Product/variant identifiers
- Available option combinations
- Selected variant and quantity
- Variant price/delta
- Stock and delivery estimate
- Update result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Copy/state | “entraîne une différence de prix” conflicts with `+0 MAD`. | Hide the section at zero or state `Aucune différence de prix`. |
| MINOR | Accessibility | Swatches and selected/disabled option semantics are not specified. | Pair color with text and announce selected/unavailable states. |
| MINOR | Sheet behavior | Unsaved-change dismissal, scroll and safe-area behavior are not shown. | Use a scrollable sheet, confirm dirty dismissal and inset the sticky CTA. |

### Canonical recommendation
Use as the canonical cart variant-editor reference after the minor delta, accessibility and sheet-behavior fixes.

---

## 05-cart-multi-vendor-grouped-by-seller-fr.png

Folder: `05-cart-wishlist/`
Screen purpose: Review a multi-vendor cart grouped by seller
Probable native route: `Cart`
Language: French
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A four-item cart grouped into two seller sections with seller subtotals and a provisional total.

### Visible UI structure
- Header: official logo, notifications and cart title
- Main content: multi-package notice, two seller groups and total
- Cards: seller group cards with item rows
- Forms: quantity steppers
- Primary action: `Continuer ma commande`
- Secondary actions: collapse groups and remove items
- Navigation: bottom buyer navigation is absent on this top-level cart
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: cream/orange/navy/white are consistent.
- Typography: seller and price hierarchy is strong.
- Icons: compatible outline family.
- Buttons: orange CTA matches foundation.
- Cards: rounded seller grouping is well suited to the system.
- Spacing: readable though long content requires virtualization.
- Shadows: soft and restrained.

### UX validation
- Grouping clearly explains split parcels and subtotals reconcile: `425 + 820 = 1,245`, `290 + 400 = 690`, grand total `1,935 MAD`.
- Required five-tab navigation is missing from a top-level cart route.
- Seller collapse state, quantity mutation feedback, stock changes and shipping per vendor need explicit behavior.
- Checkout must revalidate each seller and explain whether separate shipping/fulfillment terms apply. NEEDS PRODUCT DECISION.

### Native implementation usability
Good native grouping concept using a virtualized `SectionList`, but navigation shell and multi-vendor fulfillment rules must be added.

### Reusable components identified
- `MultiVendorNotice`
- `SellerCartSection`
- `CartItemRow`
- `QuantityStepper`
- `StickyCheckoutBar`
- `BuyerTabBar`

### Dynamic backend data required
- Sellers and verification labels
- Items, variants, quantities and unit prices
- Seller subtotals
- Stock and shipping estimates per seller
- Provisional total

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Top-level cart omits the approved buyer tab bar. | Add the exact five-tab bar with Panier active and safe-area inset. |
| MAJOR | Product logic | Multi-vendor shipping/checkout behavior is not explained beyond multiple parcels. | Define per-seller shipping, stock failure and order-splitting rules. NEEDS PRODUCT DECISION. |
| MINOR | Native performance | Long nested seller/item content requires explicit virtualization. | Use a single `SectionList`, not nested scroll views. |

### Canonical recommendation
Correct before use; retain as the preferred multi-vendor grouping concept combined with the canonical buyer tab bar.

## 05-cart-promo-applied-order-summary-fr.png

Folder: `05-cart-wishlist/`
Screen purpose: Confirm an applied promo and present the updated order total
Probable native route: `Cart`
Language: French
Screen type:
- Success state

### Status
NEEDS_REWORK

### Confidence
Medium

### What the screen represents
A populated cart after promo application, with removable promotion, order summary and checkout CTA.

### Visible UI structure
- Header: back arrow, official logo and cart badge
- Main content: green promo-success card, three cart rows and order summary
- Cards: promotion, items and totals
- Forms: quantity steppers
- Primary action: checkout with total
- Secondary actions: remove promo, modify items and delete items
- Navigation: correct five buyer tabs
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: approved palette with semantic green success.
- Typography: clear hierarchy and all copy is French.
- Icons: compatible outline family.
- Buttons: orange sticky checkout action matches foundation.
- Cards: rounded surfaces are consistent.
- Spacing: dense but scannable.
- Shadows: restrained.

### UX validation
- Line arithmetic reconciles: `450 + 6,490 + 1,290 = 8,230 MAD`; minus `450 MAD` gives `7,780 MAD`.
- Code `MAYUSH10` visually suggests a 10% offer, yet the discount is fixed at `450 MAD`; because no offer rule is shown, the result is ambiguous. NEEDS PRODUCT DECISION.
- Promo removal and quantity changes must reprice atomically, show pending/error state and prevent checkout on stale totals.
- Success must be announced without relying on green alone.

### Native implementation usability
Feasible and structurally strong, but promotion rule/detail must be explicit enough to audit before this becomes canonical.

### Reusable components identified
- `PromoAppliedCard`
- `CartItemRow`
- `QuantityStepper`
- `OrderSummaryCard`
- `StickyCheckoutBar`
- `BuyerTabBar`

### Dynamic backend data required
- Cart items and quantities
- Promotion code, rule and discount
- Unit/line prices
- Shipping, tax, subtotal and total
- Repricing/update result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Promotion clarity | `MAYUSH10` and a `450 MAD` discount are not reconcilable from the visible rule. | Show the applied offer rule or use a non-percentage-like code; backend remains authoritative. NEEDS PRODUCT DECISION. |
| MAJOR | Transaction state | Quantity/promo mutations can make displayed totals stale. | Lock checkout during repricing and atomically update all summary rows. |
| MINOR | Accessibility | Success relies heavily on green. | Keep icon/text, announce success and verify contrast. |

### Canonical recommendation
Correct promotion-rule clarity and repricing behavior before use; retain the reconciled total-card layout.

---

## 05-cart-promo-code-modal-available-offers-fr.png

Folder: `05-cart-wishlist/`
Screen purpose: Enter a promo code or choose an eligible offer
Probable native route: `Cart` with `PromoOffersSheet`
Language: French
Screen type:
- Bottom sheet

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A tall promo-code sheet listing available offers, thresholds, expiry dates and apply/use states.

### Visible UI structure
- Header: dimmed cart screen behind sheet
- Main content: promo input, apply button and four offer rows
- Cards: promotion offer cards
- Forms: code input
- Primary action: `Appliquer`
- Secondary actions: per-offer `Utiliser` and close
- Navigation: underlying cart only
- Overlays: rounded scrollable bottom sheet with scrim and drag handle

### Brand validation
- Logo: official logo remains visible in background.
- Colors: cream/orange/navy treatment is consistent.
- Typography: clear code, discount, threshold and expiry hierarchy.
- Icons: ticket, close and gift icons are coherent.
- Buttons: orange primary/outlined offer actions match foundation.
- Cards: offer cards use appropriate border/elevation.
- Spacing: dense but grouped.
- Shadows: scrim and sheet separation are adequate.

### UX validation
- Manual code entry and discoverable offers are useful.
- All displayed 2025 expiries are stale for a 2026 implementation reference; these must be backend data, not copied constants.
- Input needs keyboard-safe resizing, autocapitalization policy, paste, loading/disabled/error behavior and eligibility messaging.
- The list needs virtualization; applied/eligible/expired states must be announced and not rely only on color.

### Native implementation usability
Good reusable sheet reference using a keyboard-aware `BottomSheetFlatList` and backend-driven offer cards.

### Reusable components identified
- `PromoOffersSheet`
- `PromoCodeInput`
- `OfferCard`
- `LoadingButton`
- `ModalCloseButton`

### Dynamic backend data required
- Current cart eligibility/subtotal
- Available promotion codes and rules
- Thresholds and expiry dates
- Applied promotion
- Apply result/error

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Stale example data | All visible expiry dates are in 2025. | Render live backend dates and localized expiry/expired states; never hard-code screenshot values. |
| MINOR | Keyboard/list behavior | Tall sheet plus input needs explicit keyboard and virtualization behavior. | Use keyboard-aware sheet sizing and `BottomSheetFlatList`. |
| MINOR | State accessibility | Applied/usable states lean on color. | Add selected/disabled semantics and accessible status text. |

### Canonical recommendation
Use as the canonical promo-offers sheet after replacing example dates with live data and implementing keyboard/state behavior.

---

## 05-cart-quantity-update-toast-fr.png

Folder: `05-cart-wishlist/`
Screen purpose: Show a cart quantity update in progress
Probable native route: `Cart`
Language: French
Screen type:
- Loading state

### Status
REJECTED

### Confidence
High

### What the screen represents
A populated cart with one item row dimmed and a centered progress panel while quantity is updated.

### Visible UI structure
- Header: hamburger, official logo and cart badge
- Main content: three item cards, security row and order summary
- Cards: products and total card
- Forms: quantity steppers
- Primary action: checkout
- Secondary actions: empty cart and remove items
- Navigation: correct five buyer tabs
- Overlays: small loading panel over the updated item

### Brand validation
- Logo: official and undistorted.
- Colors: approved orange/navy/white palette.
- Typography: readable, though the overlay message is very small.
- Icons: coherent outline family.
- Buttons: primary CTA is consistent.
- Cards: cart cards are coherent.
- Spacing: generally usable.
- Shadows: loading panel is visually separated.

### UX validation
- The affected row is visually linked to the update, but the progress panel blocks content and uses tiny text.
- Visible arithmetic is wrong: `2,450 × 1 + 5,890 × 2 + 790 × 1 = 15,020 MAD`, not `15,580 MAD`.
- Checkout must be disabled while totals are pending; optimistic update needs rollback/error recovery and live total announcement.
- `Vider le panier` requires confirmation or undo.

### Native implementation usability
Per-row mutation state is implementable, but the screenshot is unsafe as a transaction reference because its total is incorrect.

### Reusable components identified
- `CartItemCard`
- `QuantityStepper`
- `RowLoadingOverlay`
- `OrderSummaryCard`
- `BuyerTabBar`

### Dynamic backend data required
- Cart items and quantities
- Unit/line prices
- Quantity-update request/result
- Shipping, subtotal and total

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Cart arithmetic | Visible quantities total `15,020 MAD`, but summary shows `15,580 MAD`. | Derive totals from authoritative line totals and block checkout during mismatch/update. |
| MAJOR | Transaction state | Checkout remains visually available while pricing is pending. | Disable checkout and conflicting row actions until repricing succeeds or rolls back. |
| MINOR | Loading accessibility | Overlay copy is tiny and progress announcement is unspecified. | Use inline row progress with accessible busy state and adequate text size. |

### Canonical recommendation
Reject as a totals/loading reference; rebuild per-row loading on a verified canonical cart.

---

## 05-cart-remove-item-confirmation-dialog-fr.png

Folder: `05-cart-wishlist/`
Screen purpose: Confirm removal of an item from cart
Probable native route: `Cart` with `RemoveCartItemSheet`
Language: French
Screen type:
- Bottom sheet
- Dialog

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A removal confirmation sheet including the selected product, cancel, remove and move-to-favorites actions.

### Visible UI structure
- Header: dimmed cart header and official logo
- Main content: dimmed cart plus product-specific confirmation sheet
- Cards: selected item summary
- Forms: none
- Primary action: `Retirer`
- Secondary actions: cancel and move to favorites
- Navigation: underlying cart
- Overlays: large rounded bottom sheet with scrim and drag handle

### Brand validation
- Logo: official and undistorted.
- Colors: approved palette, but destructive action is orange rather than semantic red.
- Typography: clear French hierarchy.
- Icons: trash, heart and cart icons are coherent.
- Buttons: destructive hierarchy is incorrect.
- Cards: item preview follows rounded system.
- Spacing: sheet is taller than needed.
- Shadows: scrim is adequate.

### UX validation
- Product-specific confirmation and cancel are clear.
- Every visible commerce amount uses EUR, including free-shipping threshold, cart progress and item prices.
- Destructive `Retirer` is styled as the positive orange primary action; moving to favorites is not clearly defined as remove-and-save.
- Success should offer undo, and modal focus/back/dismiss behavior must be accessible.

### Native implementation usability
The confirmation concept is reusable, but this complete screenshot must not guide implementation due to wrong currency and destructive hierarchy.

### Reusable components identified
- `RemoveCartItemSheet`
- `ProductSummaryCard`
- `DangerButton`
- `OutlinedButton`
- `UndoToast`

### Dynamic backend data required
- Selected cart item and variant
- Current price
- Remove/move result
- Favorites availability/auth state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Destructive action | Removal is orange primary instead of semantic danger. | Use a red danger action, keep Cancel prominent and offer undo. |
| MAJOR | Action semantics | `Déplacer vers les favoris` does not state that cart removal also occurs. | Use explicit `Retirer et ajouter aux favoris` copy and handle failures atomically. |

### Canonical recommendation
Correct before use: keep the decision concept but use semantic danger treatment and explicit remove-versus-move behavior.

## 05-cart-skeleton-loading-state.png

Folder: `05-cart-wishlist/`
Screen purpose: Preserve cart layout while cart data loads
Probable native route: `Cart`
Language: Language-neutral
Screen type:
- Loading state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A cart skeleton with item, promo, summary, CTA and navigation placeholders.

### Visible UI structure
- Header: back arrow, official logo and bag badge placeholder
- Main content: two item skeletons, promo row, summary rows and CTA skeleton
- Cards: skeleton item and summary cards
- Forms: quantity-stepper placeholders
- Primary action: disabled/loading CTA placeholder
- Secondary actions: none
- Navigation: five icon positions, but cart occupies the third slot and favorite the fourth
- Overlays: none

### Brand validation
- Logo: official and visible while content loads.
- Colors: cream and neutral skeleton grays fit the system.
- Typography: intentionally absent in loading placeholders.
- Icons: visible icons match the outline family.
- Buttons: CTA skeleton communicates pending state.
- Cards: skeleton geometry closely resembles cart content.
- Spacing: stable and low-shift.
- Shadows: subtle.

### UX validation
- Skeleton reduces perceived load and preserves approximate layout.
- The tab order is incompatible: cart is centered in the Favoris slot and heart is in the Panier slot.
- Skeleton should appear only after a short threshold, respect reduced motion and use accessibility busy labels without exposing every placeholder.
- A top-level cart usually should not need a back arrow if tabs remain available.

### Native implementation usability
Feasible with tokenized skeleton primitives and a non-animated fallback, but tab order must be corrected before use.

### Reusable components identified
- `CartSkeleton`
- `SkeletonBlock`
- `SkeletonItemCard`
- `BuyerTabBarSkeleton`

### Dynamic backend data required
- Cart loading status
- Optional cached cart badge count

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Panier and Favoris icon positions are swapped relative to the approved order. | Keep exact `Accueil`, `Catégories`, `Favoris`, `Panier`, `Compte` geometry even in skeleton state. |
| MINOR | Accessibility/motion | Skeleton shimmer and screen-reader behavior are unspecified. | Respect reduced motion, mark the region busy and hide decorative placeholders from accessibility. |

### Canonical recommendation
Correct the tab positions before use; retain the low-layout-shift skeleton geometry.

---

## 05-cart-update-needed-price-stock-changes-fr.png

Folder: `05-cart-wishlist/`
Screen purpose: Require acknowledgment of price, quantity and stock changes before checkout
Probable native route: `Cart/Revalidation`
Language: French
Screen type:
- Error state

### Status
REJECTED

### Confidence
High

### What the screen represents
A cart revalidation screen listing a price increase, reduced available quantity and unavailable item.

### Visible UI structure
- Header: Mayush logo with added `BUYER`, back arrow and cart badge
- Main content: warning title, three changed-item cards and change summary
- Cards: price-change, stock-limit and unavailable-product cards
- Forms: none
- Primary action: update cart
- Secondary actions: remove unavailable items and expand details
- Navigation: five tabs, with `Demandes` replacing `Favoris`
- Overlays: none

### Brand validation
- Logo: prohibited `BUYER` qualifier modifies the official lockup.
- Colors: orange warning/red unavailable treatments are semantic and readable.
- Typography: strong hierarchy.
- Icons: generally coherent.
- Buttons: primary and outlined treatments are consistent.
- Cards: clear change cards match foundation radius/shadow.
- Spacing: dense but structured.
- Shadows: restrained.

### UX validation
- Before/after price and quantity consequences are explicit.
- Copy says `2 changements détectés` although three distinct product changes are shown.
- Bottom navigation replaces required `Favoris` with `Demandes`.
- `Mettre à jour mon panier` must explain acceptance of price increase/removal and revalidate again server-side; alternative/substitution paths may be needed. NEEDS PRODUCT DECISION.

### Native implementation usability
The change-card pattern is useful, but the image must not guide the app due to invalid branding, navigation and contradictory count.

### Reusable components identified
- `CartRevalidationHeader`
- `PriceChangeCard`
- `QuantityChangeCard`
- `UnavailableItemCard`
- `PrimaryButton`
- `BuyerTabBar`

### Dynamic backend data required
- Changed items and reasons
- Old/new prices
- Requested/available quantities
- Current availability
- Revalidation/update result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Logo adds prohibited `BUYER`. | Replace with the exact official logo. |
| MAJOR | Navigation | `Demandes` replaces required `Favoris`. | Restore the approved five-tab set and active treatment. |
| MAJOR | State contradiction | Three visible change cases are summarized as two. | Derive the count from authoritative changed line items. |

### Canonical recommendation
Reject as a complete reference; salvage individual change-card patterns into a correctly branded/navigated revalidation route.

---

## 05-saved-for-later-items-list-fr.png

Folder: `05-cart-wishlist/`
Screen purpose: Manage cart items saved for later
Probable native route: `Cart/SavedForLater`
Language: French
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A saved-for-later list with stock status and actions to return items to cart, favorite or delete them.

### Visible UI structure
- Header: official logo, search, notifications and decorative hero
- Main content: count plus four saved product rows
- Cards: product rows and service reassurance strip
- Forms: none
- Primary action: per-item return to cart
- Secondary actions: add to favorites, delete all, delete item
- Navigation: correct five buyer tabs with Panier active
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: approved orange/navy/cream/white with green/orange stock status.
- Typography: clear product hierarchy, though action labels are compressed.
- Icons: coherent outlines.
- Buttons: orange cart action is clear.
- Cards: consistent rounded/elevated product rows.
- Spacing: content-dense but orderly.
- Shadows: soft.

### UX validation
- Stock status and three item actions are understandable.
- Repeated right-side action stacks are narrow and may fail Dynamic Type/44–48 targets.
- `Tout supprimer` and per-item delete need confirmation or undo; moving to cart needs variant/stock revalidation.
- `Support 7j/7` is a business claim requiring confirmation. NEEDS PRODUCT DECISION.

### Native implementation usability
Feasible as a virtualized list. Secondary item actions should collapse to an action sheet/overflow at large text sizes.

### Reusable components identified
- `SavedForLaterList`
- `SavedItemCard`
- `StockBadge`
- `ItemActionMenu`
- `ServiceReassuranceStrip`
- `BuyerTabBar`

### Dynamic backend data required
- Saved items and variants
- Current prices and stock
- Cart/favorites state
- Move/delete mutation results
- Verified service claims

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Responsive actions | Three repeated actions are too narrow for long text/Dynamic Type. | Use an overflow/action sheet or adaptive stacked actions with 44/48 targets. |
| MINOR | Destructive behavior | Delete-all/delete-item recovery is not shown. | Confirm bulk deletion and offer undo for removals. |
| MINOR | Product claim | `Support 7j/7` is not established by the screenshot source. | Verify or replace with approved service copy. NEEDS PRODUCT DECISION. |

### Canonical recommendation
Use as the saved-for-later reference after adaptive actions, delete recovery and claim verification.

---

## 05-wishlist-empty-state-fr.png

Folder: `05-cart-wishlist/`
Screen purpose: Explain an empty favorites list and return buyers to discovery
Probable native route: `Favorites`
Language: French
Screen type:
- Empty state

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
Top-level empty favorites with a discovery CTA, selected collections and benefit strip.

### Visible UI structure
- Header: official logo, search and notifications
- Main content: empty illustration, message, CTA and collection cards
- Cards: horizontal collection cards and benefit strip
- Forms: none
- Primary action: `Découvrir les collections`
- Secondary actions: view all selections
- Navigation: correct five buyer tabs with Favoris active
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: cream/orange/navy/white align strongly.
- Typography: clear; `liste d’envies` conflicts slightly with route term `favoris`.
- Icons: coherent outline/active-heart treatment.
- Buttons: orange CTA is consistent.
- Cards: collection cards match product/discovery foundation.
- Spacing: clean but long for an empty state.
- Shadows: soft and restrained.

### UX validation
- State explanation and recovery are clear.
- Terminology should stay `favoris` rather than alternate `liste d’envies` for consistency.
- Horizontal cards need explicit scroll behavior and all small chevrons require expanded targets.
- Bottom insets and list virtualization must prevent tab overlap.

### Native implementation usability
Strong reusable empty route using `ScrollView` plus horizontal `FlatList`; benefit strip can wrap/adapt on small screens.

### Reusable components identified
- `AppHeader`
- `EmptyState`
- `PrimaryButton`
- `CollectionCard`
- `BenefitStrip`
- `BuyerTabBar`

### Dynamic backend data required
- Favorites count
- Recommended collections and product counts
- Notification state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Terminology | `Votre liste d’envies` differs from the established `Mes favoris`. | Use `favoris` consistently across title, empty copy and actions. |
| MINOR | Responsive density | Selections and benefit strip may crowd compact heights/large text. | Keep content scrollable and allow the benefit strip to wrap or paginate. |

### Canonical recommendation
Use as the canonical French empty-favorites reference after terminology and responsive adjustments.

## 05-wishlist-items-grid-with-prices-fr.png

Folder: `05-cart-wishlist/`
Screen purpose: Browse and act on a populated favorites grid
Probable native route: `Favorites`
Language: French
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A two-column favorites grid with sale prices, seller names, stock and add/remove actions.

### Visible UI structure
- Header: official logo, search and cart badge
- Main content: title/count and four visible product cards
- Cards: two-column product cards
- Forms: none
- Primary action: per-card add to cart
- Secondary actions: remove favorite and open product
- Navigation: five tabs, but `Commandes` replaces required `Panier`
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: cream/orange/navy/white and green stock status are consistent.
- Typography: clear prices/names, but dense secondary text.
- Icons: coherent outlines and filled favorite state.
- Buttons: orange cart actions are consistent but narrow.
- Cards: imagery, radius and elevation align with product-card foundation.
- Spacing: grid is dense and vulnerable to larger type.
- Shadows: soft.

### UX validation
- Product identity, seller, old/new price and stock are scannable.
- Required Panier destination is replaced by `Commandes`, an incompatible permanent tab.
- Two action labels inside narrow cards risk clipping and sub-44/48 targets under Dynamic Type.
- Removing a favorite needs undo; adding to cart may require a variant sheet and stock revalidation.

### Native implementation usability
Feasible with a two-column `FlatList` and adaptive product cards, but tab shell and card actions require correction.

### Reusable components identified
- `FavoritesGrid`
- `FavoriteProductCard`
- `PriceBlock`
- `StockBadge`
- `BuyerTabBar`
- `UndoToast`

### Dynamic backend data required
- Favorite items and count
- Product/variant images and names
- Seller names
- Current/compare-at prices
- Stock/variant requirements
- Cart/favorite mutation results

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | `Commandes` replaces mandatory `Panier`. | Restore the exact approved five-tab set. |
| MAJOR | Responsive cards | Two labeled actions are cramped in each half-width card. | Use adaptive stacking or an overflow action with 44/48 targets and Dynamic Type support. |
| MINOR | Mutation recovery | Favorite removal has no undo. | Use optimistic removal with an accessible undo toast. |

### Canonical recommendation
Correct navigation and adaptive card actions before use; retain the grid, pricing and stock hierarchy.

---

## 05-wishlist-list-view-ar.png

Folder: `05-cart-wishlist/`
Screen purpose: Browse an Arabic favorites list and add items to cart
Probable native route: `Favorites`
Language: Arabic
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A populated RTL favorites route with filtering, old/new prices, stock and add/remove actions.

### Visible UI structure
- Header: notification, official logo and mirrored right-side back arrow
- Main content: RTL title/filter and four large product rows
- Cards: list-view product cards
- Forms: filter control
- Primary action: per-card add to cart
- Secondary actions: remove favorite and open product
- Navigation: mirrored five tabs, but `طلباتي` (my orders) replaces cart
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: cream/orange/navy with green stock state are consistent.
- Typography: mostly strong Arabic, but `Zellige` is accidental Latin/English-like text in a product name.
- Icons: coherent outline family.
- Buttons: orange add-to-cart actions match foundation.
- Cards: roomy rows are suitable for RTL.
- Spacing: clear though long list requires virtualization.
- Shadows: soft.

### UX validation
- RTL alignment and mixed `MAD` numerals remain readable.
- `طلباتي` introduces orders as a permanent tab, so the required Panier destination is missing.
- Product copy mixes Arabic with `Zellige`; localize or approve a proper brand/product transliteration.
- Filter, heart, delete and add actions need semantic labels, pressed states and undo/variant behavior.

### Native implementation usability
Strong RTL list concept using a virtualized `FlatList`, but the tab model and language consistency require correction.

### Reusable components identified
- `RTLAppHeader`
- `FavoritesList`
- `RTLFavoritesCard`
- `FilterButton`
- `StockBadge`
- `RTLBuyerTabBar`

### Dynamic backend data required
- Favorite items/count
- Localized product names/descriptions
- Current/compare-at prices
- Stock and variants
- Filter options
- Cart/favorite mutation results

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | `طلباتي` (orders) replaces the required cart tab. | Use the Arabic equivalent of Panier/cart in the correct mirrored position. |
| MAJOR | Language | `Zellige` appears as untranslated Latin copy inside an Arabic name. | Use approved Arabic localization/transliteration or a verified product brand. |
| MINOR | Accessibility | Repeated icon actions need explicit Arabic labels and 44/48 targets. | Add semantic labels/states and expanded touch areas. |

### Canonical recommendation
Correct navigation and mixed-language copy before use; retain the roomy RTL card layout.

---

## 05-wishlist-move-to-cart-variant-sheet-fr.png

Folder: `05-cart-wishlist/`
Screen purpose: Select a favorite product’s variant and quantity before adding it to cart
Probable native route: `Favorites` with `AddFavoriteToCartSheet`
Language: French with accidental English
Screen type:
- Bottom sheet

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A large product option sheet over the favorites route.

### Visible UI structure
- Header: dimmed hamburger/logo/favorite/cart header and `Ma wishlist`
- Main content: product summary, color/material/size/quantity, stock and delivery estimate
- Cards: swatches, size buttons, stock and delivery rows
- Forms: selectors and quantity stepper
- Primary action: add to cart
- Secondary actions: cancel and close
- Navigation: underlying route; bottom tabs not visible
- Overlays: near-full-height rounded sheet with scrim, drag handle and close icon

### Brand validation
- Logo: official and undistorted.
- Colors: approved orange/navy/cream/white with semantic green.
- Typography: sheet copy is French, but underlying title `Ma wishlist` is English-mixed.
- Icons: coherent outline family.
- Buttons: primary orange CTA is consistent.
- Cards: option/stock surfaces align with foundation.
- Spacing: clear but very tall.
- Shadows: scrim and modal elevation are adequate.

### UX validation
- Required variant, stock and quantity choices are visible before cart mutation.
- Underlying route uses English `wishlist` instead of French `favoris` and a competing hamburger header.
- Delivery dates lack year and are screenshot-fixed; values must be live/localized.
- Unavailable option combinations, quantity cap, price changes, dirty-dismiss confirmation and add mutation states are not shown.

### Native implementation usability
The sheet content is native-feasible and reusable, but the visible surrounding route and state behavior prevent direct use.

### Reusable components identified
- `AddFavoriteToCartSheet`
- `ProductSummaryRow`
- `ColorSwatchOption`
- `SelectField`
- `SizeOption`
- `QuantityStepper`
- `DeliveryEstimateRow`

### Dynamic backend data required
- Product/variant identifiers
- Available color/material/size combinations
- Price and stock per combination
- Quantity cap
- Delivery estimate
- Add-to-cart result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Language/navigation | Underlying French route says `Ma wishlist` and uses a hamburger shell. | Use `Mes favoris` and the approved five-tab app shell. |
| MAJOR | Variant state | Unavailable combinations and price changes are not represented. | Drive all options from backend availability and update price/stock atomically. |
| MINOR | Date/state | Fixed `28 mai–31 mai` and dirty dismissal behavior are unspecified. | Render live localized estimates and confirm dismissal if selections changed. |

### Canonical recommendation
Correct before use; combine the sheet’s option layout with `05-wishlist-empty-state-fr.png`/a corrected favorites shell and the behavior of the cart variant editor.

---

## 05-wishlist-out-of-stock-alternatives-fr.png

Folder: `05-cart-wishlist/`
Screen purpose: Explain an out-of-stock favorite and offer alternatives
Probable native route: `Favorites`
Language: French
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A favorites route with one unavailable product, alternative/delete actions and a second in-stock product.

### Visible UI structure
- Header: official logo, search and notifications
- Main content: favorites heading, out-of-stock card, second in-stock card and help strip
- Cards: large product cards and support card
- Forms: none
- Primary action: add in-stock product to cart
- Secondary actions: view alternatives, remove unavailable product and contact support
- Navigation: correct five buyer tabs with Favoris active
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: approved palette with red unavailable and green stock states.
- Typography: clear product/status hierarchy.
- Icons: coherent outlines/favorite hearts.
- Buttons: add-to-cart and destructive actions are distinguishable.
- Cards: rounded large cards fit the system.
- Spacing: clean but content relationship is ambiguous.
- Shadows: soft.

### UX validation
- Unavailable item is clearly disabled and offers alternatives/removal.
- The second product looks like another favorite but has an outline heart, contradicting the route; if it is an alternative, it lacks an `Alternatives` section label.
- `Voir les alternatives` plus a visible unlabeled alternative duplicates discovery paths.
- Removal needs undo and add-to-cart may require variant selection.

### Native implementation usability
Feasible, but the data hierarchy must distinguish favorite items from recommendations before the screenshot can guide a list implementation.

### Reusable components identified
- `OutOfStockFavoriteCard`
- `AlternativeProductCard`
- `StockBadge`
- `HelpCard`
- `BuyerTabBar`
- `UndoToast`

### Dynamic backend data required
- Favorite product status/price
- Alternative recommendations and rationale
- Variant/stock state
- Cart/favorite mutation results
- Support availability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Information hierarchy | Second card’s outline heart contradicts its placement in `Mes favoris`. | Label a separate `Alternatives` section or show only actual favorited items. |
| MAJOR | Action duplication | `Voir les alternatives` and an unlabeled alternative are shown together. | Choose an inline alternatives section or a dedicated navigation action. |
| MINOR | Mutation recovery | Favorite removal has no undo. | Add an accessible undo toast. |

### Canonical recommendation
Rework the favorites-versus-alternatives hierarchy before use; retain the out-of-stock card pattern.

## 05-wishlist-price-change-notifications-fr.png

Folder: `05-cart-wishlist/`
Screen purpose: Show price increases on favorites and offer notification opt-in
Probable native route: `Favorites/PriceChanges`
Language: French
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A favorites list filtered or decorated for product price updates, with current/old prices and add-to-cart actions.

### Visible UI structure
- Header: official logo, search and notifications
- Main content: two price-change product cards and notification opt-in strip
- Cards: large product cards and notification card
- Forms: none
- Primary action: per-product add to cart
- Secondary actions: remove favorite and activate notifications
- Navigation: correct five buyer tabs with Favoris active
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: approved cream/orange/navy; notice state uses orange rather than misleading success green.
- Typography: clear old/new price hierarchy.
- Icons: coherent heart, bell, cart and trash outlines.
- Buttons: orange add-to-cart controls match foundation.
- Cards: rounded product cards are consistent.
- Spacing: dense but clear on the depicted width.
- Shadows: soft and restrained.

### UX validation
- Old/new prices are explicit and both changes are increases.
- Notification permission must use a pre-permission explanation, then platform prompt only after user action; denial/settings recovery is needed.
- Add-to-cart may need variant selection and must use current backend price/stock.
- Large images and side-by-side content need an adaptive stacked layout for smaller widths and Dynamic Type.

### Native implementation usability
Strong reusable price-change state with a virtualized list and responsive card layout; do not hard-code old/current prices.

### Reusable components identified
- `PriceChangedFavoriteCard`
- `PriceBlock`
- `NotificationOptInCard`
- `BuyerTabBar`
- `UndoToast`

### Dynamic backend data required
- Favorite items
- Previous/current prices and change timestamp
- Current stock/variants
- Notification permission/subscription state
- Cart/favorite mutation results

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Permissions | Notification activation flow and denial recovery are not represented. | Use a pre-permission explainer, request system permission on tap and link to settings after denial. |
| MINOR | Responsive layout | Wide split cards can clip on compact phones or large Dynamic Type. | Stack media/content/actions below the breakpoint and preserve 44/48 targets. |
| MINOR | Price freshness | Cart CTA repeats a price that can change again. | Revalidate current price/stock when adding to cart. |

### Canonical recommendation
Use as the canonical favorite price-change reference after permission, responsive and price-revalidation fixes.

---

## 05-wishlist-remove-confirmation-dialog-fr.png

Folder: `05-cart-wishlist/`
Screen purpose: Confirm removing a product from favorites
Probable native route: `Favorites` with `RemoveFavoriteSheet`
Language: French
Screen type:
- Bottom sheet
- Dialog

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A destructive favorites-removal confirmation over a filtered/sorted favorites list.

### Visible UI structure
- Header: dimmed official logo and notifications
- Main content: dimmed favorite cards and large confirmation sheet
- Cards: underlying product cards
- Forms: filter and sort controls in background
- Primary action: `Retirer`
- Secondary actions: `Annuler`
- Navigation: underlying route; bottom tabs not visible
- Overlays: rounded bottom sheet with scrim and drag handle

### Brand validation
- Logo: official and undistorted, but repeated inside the sheet without need.
- Colors: brand palette is present, but destructive action is orange rather than semantic red.
- Typography: clear French confirmation hierarchy.
- Icons: trash icon is coherent.
- Buttons: destructive hierarchy is incorrect.
- Cards: sheet shape aligns with system.
- Spacing: confirmation sheet is excessively tall for a short decision.
- Shadows: scrim is adequate.

### UX validation
- Cancel and remove choices are clear.
- Background favorite prices are all EUR, violating mandatory `MAD`.
- Destructive removal should use semantic danger styling and offer undo; repeated logo consumes modal space.
- Modal focus, back/swipe dismissal and accessibility announcement must be defined.

### Native implementation usability
A smaller native confirmation sheet is straightforward, but this screenshot should not guide implementation due to currency and destructive-action errors.

### Reusable components identified
- `RemoveFavoriteSheet`
- `DangerButton`
- `OutlinedButton`
- `UndoToast`
- `ModalScrim`

### Dynamic backend data required
- Selected favorite product identifier/name
- Remove result
- Undo availability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Destructive action | `Retirer` uses positive orange rather than semantic danger and no undo is offered. | Use red danger styling, retain Cancel and provide undo. |
| MINOR | Modal sizing | Logo and excessive whitespace make the sheet much taller than needed. | Remove the duplicate logo and size the dialog to content with safe-area inset. |

### Canonical recommendation
Correct before use: recreate a compact danger confirmation with an undo path; currency examples are accepted variations.

---

## Folder assessment

- Reviewed: 22/22 screenshots.
- Status totals: 0 APPROVED, 5 APPROVED_WITH_MINOR_FIXES, 14 NEEDS_REWORK, 0 REFERENCE_ONLY, 3 REJECTED, 0 DUPLICATE_ALTERNATIVE.
- Highest-priority corrections: reject wrong totals in `05-cart-items-list-ar.png` and `05-cart-quantity-update-toast-fr.png`; define safe guest-cart merge and destructive confirmation behavior; replace invalid `BUYER` branding in `05-cart-items-promo-code-summary-fr.png` and `05-cart-update-needed-price-stock-changes-fr.png`; normalize all permanent tab bars.
- Canonical bases after noted fixes: `05-cart-modify-variant-bottom-sheet-fr.png`, `05-cart-promo-code-modal-available-offers-fr.png`, `05-saved-for-later-items-list-fr.png`, `05-wishlist-empty-state-fr.png`, and `05-wishlist-price-change-notifications-fr.png`. No full populated cart screenshot is directly implementation-safe; use `05-cart-multi-vendor-grouped-by-seller-fr.png` only after adding canonical navigation and product decisions.
