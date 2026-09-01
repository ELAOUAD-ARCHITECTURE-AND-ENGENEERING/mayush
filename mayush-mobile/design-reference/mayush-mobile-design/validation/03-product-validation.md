# 03-product Validation Report

> **Fact-check scope:** Currency and address examples are accepted variations. Do not treat them as validation defects by themselves; [fact-check-correction.md](./fact-check-correction.md) supersedes earlier currency/address severity notes.

Folder: `03-product/`  
Screens reviewed: 8 of 8  
Reference sources: `00-foundation/`, `assetsl/`  
Scope: Mayush buyer mobile experience only

## Extracted validation rules

- Preserve the exact `MAYUSH DESIGN` artwork, approved cream/orange/navy/white palette, premium type hierarchy and rounded-outline icon family.
- Product commerce must use `MAD` and Morocco-specific delivery context.
- Full pages that show buyer navigation must use Accueil, Catégories, Favoris, Panier and Compte; modal/product subflows may omit it.
- Product media, variants, price, seller, delivery, stock, rating, specifications and policy claims must be dynamic data rather than baked into decorative images.
- Fixed add-to-cart actions must respect keyboard and bottom safe-area insets and must not overlap content/navigation.
- Quantity controls, swatches, thumbnails and icon actions require 44–48 dp hit areas, clear state feedback, accessibility labels and Dynamic Type tolerance.
- Returns, warranties, payment-at-delivery and delivery promises are business rules and require confirmation before implementation.

## 03-product-added-to-cart-confirmation.png

Folder: `03-product/`  
Screen purpose: Added-to-cart confirmation and next-step choice  
Probable native route: `ProductDetail` overlay  
Language: FR  
Screen type: Success state

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A modal bottom sheet confirming that a selected sofa was added to cart, allowing quantity adjustment and a choice between continuing shopping and opening the cart.

### Visible UI structure
- Header: Dimmed underlying product header
- Main content: Success icon/message, selected item, variant/quantity and cart subtotal
- Cards: Product summary and subtotal card
- Forms: Quantity stepper
- Primary action: Voir mon panier
- Secondary actions: Continuer mes achats and sheet dismissal
- Navigation: Underlying navigation is intentionally obscured
- Overlays: Large draggable bottom sheet over product detail

### Brand validation
- Logo: Official logo remains visible in the dimmed source screen.
- Colors: Orange, navy, cream, white and semantic green are appropriate.
- Typography: Clear success and action hierarchy.
- Icons: Consistent rounded outlines.
- Buttons: Strong primary/secondary distinction.
- Cards: Soft rounded summary cards align with the design system.
- Spacing: Generous and readable.
- Shadows: Modal elevation and dimming are appropriate.

### UX validation
- Clear user goal: Yes, acknowledge success and choose the next step.
- Primary action: Opening the cart is prominent.
- Navigation logic: Correct for a transient modal.
- Form usability: Quantity stepper is clear; disabling decrement at one must be explicit.
- Scroll behavior: Sheet must scroll on compact screens and preserve actions above the safe area.
- Empty/error behavior: Success is shown; add failure, stock-change and subtotal-refresh errors remain required.
- Accessibility concerns: Announce success, move focus into the sheet, trap modal focus and restore focus on dismiss.

### Native implementation usability
Directly feasible as a reusable RN bottom sheet with dynamic cart state. The subtotal should update asynchronously and controls should expose busy/error states.

### Reusable components identified
- SuccessBottomSheet
- ProductSummaryRow
- VariantSummary
- QuantityStepper
- CartSubtotalCard
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Added product id, name and image
- Selected variant
- Quantity
- Cart item count
- Cart subtotal in MAD
- Stock and add-to-cart result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Dismissal | A drag handle is present but no explicit close control is visible. | Support swipe/backdrop/system-back dismissal and add a labeled close action if product requirements demand one. |
| MINOR | Accessibility | Success/focus behavior and quantity-state announcements are not visually evidenced. | Announce the result, focus the heading, expose stepper value/actions and keep targets at least 44–48 dp. |
| MINOR | Safe area | The tall sheet may exceed compact screens. | Make the body scrollable and inset the action/footer content above the home indicator. |

### Canonical recommendation
Use directly as the canonical add-to-cart success reference after the minor dismissal, accessibility and compact-height corrections.

## 03-product-customer-reviews-ratings.png

Folder: `03-product/`  
Screen purpose: Product reviews summary and review list  
Probable native route: `ProductReviews/:productId`  
Language: FR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A dedicated product-review route with rating distribution, recommendation summary, filters, verified reviews, customer photos and a write-review action.

### Visible UI structure
- Header: Back, logo, search, wishlist and cart
- Main content: Product summary, score/distribution, recommendation summary and review list
- Cards: Review cards with customer media
- Forms: Sort and filter controls
- Primary action: Écrire un avis
- Secondary actions: Helpful, report, sort and filter
- Navigation: Correct five buyer tabs
- Overlays: Sticky write-review action above bottom navigation

### Brand validation
- Logo: Correct official artwork.
- Colors: Approved neutrals/orange plus valid semantic green verified badges.
- Typography: Clear hierarchy, though large score dominates.
- Icons: Coherent.
- Buttons: Sticky review action is visually prominent.
- Cards: Structured and readable.
- Spacing: Comfortable.
- Shadows: Minimal and consistent.

### UX validation
- Clear user goal: Yes.
- Primary action: Clear, but should be eligibility-gated.
- Navigation logic: Correct five-tab destinations.
- Form usability: Sort/filter controls are understandable.
- Scroll behavior: Use a virtualized review list; keep CTA and tab bar from covering the last review.
- Empty/error behavior: No zero-review or load-failure state is shown.
- Accessibility concerns: Star distribution needs textual equivalents; customer images need useful/hidden semantics; sticky controls need focus order.

### Native implementation usability
Feasible with a summary header and paginated `FlatList`. Rating bars, review photos and helpful/report actions should be reusable and state-driven.

### Reusable components identified
- ProductCompactHeader
- RatingSummary
- RatingDistributionRow
- ReviewFilterBar
- ReviewCard
- ReviewPhotoRail
- StickyActionButton
- BuyerBottomTabs

### Dynamic backend data required
- Product id, image, name, variant and MAD price
- Average rating and distribution
- Review count and recommendation rate
- Review author, verification, date, text, rating and photos
- Helpful/report state
- Review eligibility

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Currency | Product summary shows `499,00 €`. | Use MAD and the same product price source as Product Detail. |
| MAJOR | Product logic | `Écrire un avis` appears available without purchase/authentication state. | NEEDS PRODUCT DECISION: define buyer eligibility, one-review rules, moderation and sign-in handling. |
| MAJOR | Accessibility | Rating histogram relies on stars and visual bars. | Provide spoken labels such as “5 stars, 89 reviews” and avoid repeated decorative stars. |
| MINOR | Safe area | Sticky review CTA plus bottom tabs may reduce usable viewport or cover list content. | Add measured bottom inset and sufficient `contentContainerStyle` padding. |

### Canonical recommendation
Correct before use; it is the preferred reviews-route reference after MAD conversion, review-eligibility logic and accessibility work.

## 03-product-delivery-returns-info.png

Folder: `03-product/`  
Screen purpose: Product-specific delivery and returns information  
Probable native route: `ProductDeliveryReturns/:productId`  
Language: FR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A product logistics page showing selected city, delivery fee/window/method, stock, preparation and return terms.

### Visible UI structure
- Header: Back, official logo and bag count
- Main content: Product summary, selected city, delivery rows and return rows
- Cards: Product/city summary and grouped information rows
- Forms: Change-city action
- Primary action: Voir la politique de retour
- Secondary actions: Modify city and open delivery method/policy details
- Navigation: Correct five destinations, but Compte is incorrectly active
- Overlays: None

### Brand validation
- Logo: Correct official artwork.
- Colors: Approved palette with semantic green badges.
- Typography: Clear hierarchy.
- Icons: Consistent outline information icons.
- Buttons: Secondary outlined actions are clear.
- Cards: Rounded cards and separators are consistent.
- Spacing: Dense but readable.
- Shadows: Soft.

### UX validation
- Clear user goal: Yes.
- Primary action: Return policy access is clear.
- Navigation logic: Incorrect active tab suggests the route belongs to Account.
- Form usability: City change is obvious; selection workflow is not shown.
- Scroll behavior: Must scroll with bottom inset; rows should not assume fixed text height.
- Empty/error behavior: Delivery-unavailable, unknown fee and policy-fetch failure states are missing.
- Accessibility concerns: Badge color must not be the only status cue; rows and chevrons should be one labeled target.

### Native implementation usability
Feasible as a data-driven list of logistics/policy rows. Dates, fees, city zones, stock and policy claims must never be hard-coded.

### Reusable components identified
- AppHeader
- ProductSummaryRow
- LocationCard
- InformationSection
- InformationRow
- StatusBadge
- SecondaryButton
- BuyerBottomTabs

### Dynamic backend data required
- Product id, image, variant and MAD price
- Selected city and delivery zone
- Delivery fee, window, preparation and method
- Stock/availability
- Return eligibility, window and policy link
- Cart count

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Compte is active even though this is product delivery information. | Show no active tab for a pushed product subroute, or preserve the originating tab state consistently. |
| MAJOR | Business rules | Free returns, 14-day eligibility, 24-hour dispatch and labels such as `Rapide` appear unverified. | NEEDS PRODUCT DECISION: validate policies, SLA calculations, zones and eligibility before implementation. |
| MAJOR | State coverage | No undeliverable/unknown-fee/out-of-stock state is shown. | Define unavailable and retry states and make the main next action unambiguous. |
| MINOR | Dynamic content | Fixed dates/fee phrasing may become stale. | Compute from live destination, stock and shipping-method data with a timestamp/fallback. |

### Canonical recommendation
Correct before use; use as the delivery/returns route reference only after product approves all policy/SLA claims and the active-tab behavior is fixed.

## 03-product-detail-full-description-reviews-specs.png

Folder: `03-product/`  
Screen purpose: Comprehensive product detail with description, specifications, seller, reviews and recommendations  
Probable native route: `ProductDetail/:productId`  
Language: FR  
Screen type: Full page

### Status
REJECTED

### Confidence
High

### What the screen represents
An all-in-one product page combining gallery, purchase controls, policies, description, technical tables, seller profile, reviews and related products.

### Visible UI structure
- Header: Compact back/logo/search/wishlist/bag toolbar
- Main content: Two-column desktop-like hero followed by many dense sections
- Cards: Seller, specification, review and recommendation cards
- Forms: Variant, material and quantity controls
- Primary action: Sticky add-to-cart bar
- Secondary actions: Gallery, seller, reviews, specifications and recommendations
- Navigation: No standard bottom tabs; purchase bar spans the bottom
- Overlays: None

### Brand validation
- Logo: Official artwork is present but very small.
- Colors: Approved palette and MAD pricing are used.
- Typography: Text is far below practical mobile reading size.
- Icons: Consistent but too small.
- Buttons: Many controls are below reliable touch dimensions.
- Cards: Individual ideas align with the system, but the composition is desktop-like.
- Spacing: Excessive information is compressed into one static page.
- Shadows: Restrained.

### UX validation
- Clear user goal: Purchase is present, but information overload weakens hierarchy.
- Primary action: Visible at bottom but disconnected from a very long dense page.
- Navigation logic: Pushed detail can omit tabs, but the fixed toolbar/purchase layout lacks safe-area proof.
- Form usability: Variant/quantity controls are too small.
- Scroll behavior: The screenshot behaves like a scaled desktop poster; sections and text would become extremely long at native sizes.
- Empty/error behavior: No dynamic-loading or missing-content states.
- Accessibility concerns: Tiny type, controls and multi-column reading order are not accessible; Dynamic Type would destroy the layout.

### Native implementation usability
Not suitable as a complete native reference. Two-column hero/specification blocks and four-across content only work at poster scale. Split content into a standard single-column detail plus dedicated gallery, variants, specifications and reviews routes/sheets.

### Reusable components identified
- ProductMediaGallery concept
- SellerSummaryCard concept
- SpecificationRow concept
- ReviewSummary concept
- RelatedProductRail concept
- StickyAddToCart concept

### Dynamic backend data required
- Product media, name, SKU, price, discount, rating and stock
- Seller identity/rating
- Variants and quantity limits
- Delivery, payment, return and warranty rules
- Description, features and specifications
- Reviews and related products

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Mobile usability | A desktop-style two-column detail and tiny multi-column sections are forced into a phone canvas. | Replace with a single-column mobile structure and dedicated subroutes/sheets. |
| CRITICAL | Accessibility | Text and controls are too small for reading, touch and Dynamic Type. | Use foundation tokens at native size with 44–48 dp controls and scalable section heights. |
| MAJOR | Information architecture | Description, specs, seller, reviews and recommendations are overloaded on one screen. | Prioritize summary sections and link to dedicated detail/review/specification surfaces. |
| MAJOR | Business rules | Payment-on-delivery, 14-day returns and two-year warranty claims are unverified. | NEEDS PRODUCT DECISION: validate policy claims before showing them. |

### Canonical recommendation
Reject as a full implementation reference. Use only its content inventory, rebuilding from `03-product-detail-image-carousel-add-to-cart.png` and the dedicated gallery/variant/review surfaces.

## 03-product-detail-image-carousel-add-to-cart.png

Folder: `03-product/`  
Screen purpose: Main product detail and add-to-cart flow  
Probable native route: `ProductDetail/:productId`  
Language: FR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A focused product detail with image carousel, rating, seller, MAD pricing, variant summary, quantity, Casablanca delivery and a sticky add-to-cart CTA.

### Visible UI structure
- Header: Back, official logo, share, favorite and cart
- Main content: Media carousel, product summary, variant, quantity and delivery
- Cards: Variant and delivery cards
- Forms: Quantity stepper and variant entry
- Primary action: Ajouter au panier with price
- Secondary actions: Share, favorite, seller, variant, delivery and cart
- Navigation: Five tabs, but Explorer replaces Panier
- Overlays: None

### Brand validation
- Logo: Correct official artwork.
- Colors: Strong source-system alignment with readable MAD pricing.
- Typography: Clear and premium.
- Icons: Coherent rounded-outline family.
- Buttons: Add-to-cart CTA is prominent and realistic.
- Cards: Rounded white controls fit the foundation.
- Spacing: Spacious and usable.
- Shadows: Soft.

### UX validation
- Clear user goal: Yes.
- Primary action: Clear and persistent.
- Navigation logic: Wrong permanent tab destination.
- Form usability: Variant and quantity actions are understandable.
- Scroll behavior: Body should scroll beneath a measured sticky CTA and tabs without overlap.
- Empty/error behavior: Add failure, unavailable variant, price change and delivery-unavailable states are absent.
- Accessibility concerns: Carousel, stepper, seller link, variant swatch and icon actions need labels and state announcements.

### Native implementation usability
This is the strongest main Product Detail structure. Implement with a vertical list, paging media, state-driven variant/quantity controls and a safe-area-aware sticky purchase footer.

### Reusable components identified
- ProductHeader
- ProductMediaCarousel
- RatingInline
- SellerLink
- PriceBlock
- StockBadge
- VariantSummaryCard
- QuantityStepper
- DeliverySummaryCard
- StickyAddToCart
- BuyerBottomTabs

### Dynamic backend data required
- Product id, images, name, attributes, rating and review count
- Current/original MAD price
- Seller
- Stock and selected variant
- Quantity limits
- Casablanca/selected-city delivery estimate
- Wishlist/cart state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Navigation | Explorer replaces the required Panier tab even though a cart is central to this route. | Use Accueil, Catégories, Favoris, Panier and Compte with the standard active treatment. |
| MAJOR | Safe area | Sticky add-to-cart and bottom tabs consume substantial height and may cover content. | Measure both bars, apply bottom content inset and define which remains visible while scrolling. |
| MAJOR | State coverage | Unavailable variant, stock change, add failure and price refresh are not shown. | Define loading/disabled/error feedback and preserve selection on retry. |
| MINOR | Accessibility | Carousel dots and icon-only actions have no visible accessible state. | Announce slide position, favorite/cart status and give all controls 44–48 dp targets. |

### Canonical recommendation
Correct before use; after restoring the approved tabs and defining sticky-footer/state behavior, this should be the canonical main Product Detail reference.

## 03-product-gallery-zoom-thumbnails.png

Folder: `03-product/`  
Screen purpose: Full product media gallery and zoom  
Probable native route: `ProductGallery/:productId`  
Language: FR  
Screen type: Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A dedicated gallery showing one large product image, image position, pinch zoom guidance, thumbnails, favorite and share actions.

### Visible UI structure
- Header: Back, official logo and share
- Main content: Gallery title, product subtitle, large image and thumbnail rail
- Cards: Media frame and guidance card
- Forms: Gesture-based zoom and thumbnail selection
- Primary action: Inspect/select product imagery
- Secondary actions: Back, share and favorite
- Navigation: Correct five buyer tabs
- Overlays: Image position and zoom instruction chips

### Brand validation
- Logo: Correct official artwork.
- Colors: Approved palette.
- Typography: Clear and spacious.
- Icons: Coherent outline family.
- Buttons: Large icon buttons meet likely touch-target requirements.
- Cards: Rounded media/guidance surfaces fit the system.
- Spacing: Strong.
- Shadows: Soft and premium.

### UX validation
- Clear user goal: Yes.
- Primary action: Media inspection is obvious.
- Navigation logic: Correct buyer tabs, though a pushed gallery could also omit them consistently.
- Form usability: Thumbnail selection and zoom are understandable.
- Scroll behavior: Thumbnail rail should scroll horizontally; zoom/pan gestures must not conflict with system back/navigation.
- Empty/error behavior: Missing-image fallback and media-load retry are required.
- Accessibility concerns: Pinch-only interaction needs accessible zoom controls; thumbnails need position and selected-state labels.

### Native implementation usability
Directly feasible with a paging image viewer, pinch/pan gesture handler and virtualized thumbnail rail. Provide non-gesture zoom and reset actions.

### Reusable components identified
- ProductGalleryHeader
- ZoomableImage
- MediaPositionBadge
- ThumbnailRail
- ThumbnailButton
- GuidanceCard
- BuyerBottomTabs

### Dynamic backend data required
- Product id and localized title
- Ordered product images and alt descriptions
- Selected image index
- Wishlist state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Copy | `Pinch pour zoomer` is an English/French hybrid. | Use natural French such as `Pincez pour zoomer`. |
| MINOR | Accessibility | Pinch is the only indicated zoom method. | Add labeled zoom in/out/reset actions and meaningful image descriptions. |
| MINOR | State coverage | Broken/missing media behavior is not shown. | Add placeholder, progress and retry states without layout jumps. |

### Canonical recommendation
Use directly as the canonical Product Gallery reference after the copy, accessible zoom and media-failure corrections.

## 03-product-specifications-table.png

Folder: `03-product/`  
Screen purpose: Product technical specifications  
Probable native route: `ProductSpecifications/:productId`  
Language: FR  
Screen type: Full page

### Status
REJECTED

### Confidence
High

### What the screen represents
A dedicated technical-details page with product summary and rows for description, materials, dimensions, weight, color, assembly, care, origin, warranty and SKU.

### Visible UI structure
- Header: Back, official logo and cart
- Main content: Product summary and specification rows
- Cards: Product summary and specification container
- Forms: Expandable/disclosure rows
- Primary action: Open specification details
- Secondary actions: Cart and individual row disclosures
- Navigation: Commandes replaces Panier; Compte is active
- Overlays: None

### Brand validation
- Logo: Correct official artwork.
- Colors: Approved palette, but the product price is invalid.
- Typography: Generally readable.
- Icons: Coherent but every row chevron implies interaction without showing its purpose.
- Buttons: Header actions are clear.
- Cards: Rounded grouped list is reusable.
- Spacing: Comfortable though the page is long.
- Shadows: Soft.

### UX validation
- Clear user goal: Yes.
- Primary action: Reading specifications is clear; row interactivity is ambiguous.
- Navigation logic: Wrong permanent destination and active tab.
- Form usability: Not applicable.
- Scroll behavior: A simple virtualized/scrolling list is feasible.
- Empty/error behavior: Missing values, partial specs and retry states are not defined.
- Accessibility concerns: Each row must expose whether it expands/navigates; long values require wrapping and semantic units.

### Native implementation usability
The data-driven specification-row pattern is practical, but the full screenshot is unsafe due to currency/navigation and unexplained row behavior.

### Reusable components identified
- AppHeader
- ProductSummaryCard
- SpecificationList
- SpecificationRow
- StatusBadge
- BuyerBottomTabs

### Dynamic backend data required
- Product id, image, name, collection and MAD price
- Description
- Materials, dimensions, weight, color and assembly data
- Care instructions
- Origin
- Warranty terms
- SKU/reference

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Currency | Product price uses euros. | Use MAD from the canonical product price source. |
| CRITICAL | Navigation | Commandes replaces Panier and Compte is incorrectly active. | Restore the approved five tabs or omit tabs consistently on the pushed specifications route. |
| MAJOR | Interaction | Every specification row has a chevron, but its expansion/navigation behavior is unclear. | Make static rows non-interactive or define accessible expanded detail behavior. |
| MAJOR | Business rules | Origin, assembly tools and two-year warranty appear definitive but unverified. | NEEDS PRODUCT DECISION/data validation: show only seller/catalogue-backed values and approved warranty terms. |

### Canonical recommendation
Do not use as a complete reference. Rebuild the dedicated specifications route from its grouped-row concept with MAD, verified catalogue data and consistent navigation.

## 03-product-variant-selector-color-material-size.png

Folder: `03-product/`  
Screen purpose: Select product color, material, dimensions and quantity  
Probable native route: `ProductDetail` overlay  
Language: FR  
Screen type: Bottom sheet

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A large variant-selection sheet showing product summary, color/material/size options, availability, quantity, delivery estimate and add-to-cart action.

### Visible UI structure
- Header: Drag handle and explicit close action over a dimmed product route
- Main content: Product summary and grouped option selectors
- Cards: Material and dimension option cards
- Forms: Color swatches, material/size radio-like choices and quantity stepper
- Primary action: Ajouter au panier with MAD price
- Secondary actions: Close and change selections
- Navigation: None required for the modal flow
- Overlays: Large modal bottom sheet

### Brand validation
- Logo: Official logo remains visible in the dimmed source header.
- Colors: Approved palette with valid green stock status.
- Typography: Clear hierarchy and readable pricing.
- Icons: Consistent.
- Buttons: Strong sticky add-to-cart action.
- Cards: Option cards clearly communicate selected and disabled states.
- Spacing: Spacious; sheet is very tall.
- Shadows: Appropriate modal elevation.

### UX validation
- Clear user goal: Yes.
- Primary action: Clear and tied to selected price.
- Navigation logic: Correct modal behavior.
- Form usability: Selection state is strong; dependency/price changes and out-of-stock combinations need feedback.
- Scroll behavior: The sheet must scroll above a safe-area footer on compact screens and under Dynamic Type.
- Empty/error behavior: Variant-fetch, invalid-combination and add failure states are missing.
- Accessibility concerns: Unselected color swatches lack visible names; option groups require radio semantics and disabled explanations.

### Native implementation usability
Feasible as a data-driven RN bottom sheet. Options must be generated from a variant matrix, preserve selection dependencies and expose loading/disabled/error states.

### Reusable components identified
- VariantBottomSheet
- ProductSummaryRow
- ColorSwatchGroup
- OptionCardGroup
- QuantityStepper
- DeliveryEstimateBanner
- StickyAddToCart

### Dynamic backend data required
- Product id, image, name and base/current MAD price
- Variant attributes and combinations
- Per-variant stock, price and imagery
- Quantity limits
- Delivery estimate by selected variant/location
- Add-to-cart result
- Approved return/payment claims

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Business rules | `Retour sous 14 jours` and delivery date claims appear definitive without confirmed policy/data. | NEEDS PRODUCT DECISION: validate return policy and calculate dates from live destination/stock data. |
| MAJOR | Accessibility | Color options are primarily visual and only the selected color is named. | Give every swatch a visible/accessibility name, role, selected state and 44–48 dp target. |
| MAJOR | State logic | Dependencies between color, material and dimension availability are not explained. | Drive all options from a variant matrix, announce changes and explain disabled combinations. |
| MINOR | Safe area | Tall content plus sticky CTA may not fit compact devices or larger text. | Use a scrollable body, keyboard handling and bottom safe-area inset. |

### Canonical recommendation
Correct before use; it is the preferred variant-selection reference after policy approval, accessible option labeling and complete variant-state logic.

## Folder status totals

| Status | Count |
|---|---:|
| APPROVED | 0 |
| APPROVED_WITH_MINOR_FIXES | 2 |
| NEEDS_REWORK | 4 |
| REFERENCE_ONLY | 0 |
| REJECTED | 2 |
| DUPLICATE_ALTERNATIVE | 0 |
