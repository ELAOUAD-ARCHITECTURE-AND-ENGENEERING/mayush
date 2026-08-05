# 02-discovery Validation Report

> **Fact-check scope:** Currency and address examples are accepted variations. Do not treat them as validation defects by themselves; [fact-check-correction.md](./fact-check-correction.md) supersedes earlier currency/address severity notes.

Folder: `02-discovery/`  
Screens reviewed: 16 of 16  
Reference sources: `00-foundation/`, `assetsl/`  
Scope: Mayush buyer mobile experience only

## Extracted validation rules

- Preserve the official `MAYUSH DESIGN` logo exactly; never add `BUYER` or alter its proportions.
- Use the cream, Mayush orange (approximately `#D97434`), deep navy, white-card and soft-beige system shown in the source boards.
- Use Playfair-led display hierarchy with a clean sans-serif UI face; use Tajawal-compatible Arabic typography and true RTL behavior.
- Use one rounded-outline icon family, consistent cards, restrained shadows and reusable spacing/radius tokens.
- Use `MAD`, Moroccan places and Morocco-specific delivery context.
- Use exactly five buyer tabs: Accueil, Catégories, Favoris, Panier, Compte. Locale equivalents must preserve those destinations and mirror order in RTL.
- Native references must support scrolling, safe-area insets, Dynamic Type, screen-reader labels and 44–48 dp minimum touch targets.
- Product grids/carousels must be data-driven and adapt to narrow devices and longer French/Arabic content.

## 02-categories-photo-grid-ar.png

Folder: `02-discovery/`  
Screen purpose: Arabic category directory  
Probable native route: `Categories`  
Language: AR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An RTL category-browsing page with search and a photographic category grid.

### Visible UI structure
- Header: Cart, wishlist and official logo
- Main content: Search field and category title
- Cards: Two-column photographic category cards
- Forms: Search input
- Primary action: Open a category
- Secondary actions: Wishlist and cart
- Navigation: Five-tab RTL bottom bar
- Overlays: None

### Brand validation
- Logo: Official artwork appears preserved.
- Colors: Cream, navy, orange and white align with the source boards.
- Typography: Arabic hierarchy is readable; use the approved Arabic font in implementation.
- Icons: Mostly coherent rounded outlines.
- Buttons: Category cards have clear card-level actions.
- Cards: Rounded photographic cards are consistent and reusable.
- Spacing: Spacious and balanced.
- Shadows: Soft and restrained.

### UX validation
- Clear user goal: Yes, browse furniture categories.
- Primary action: Category cards are understandable.
- Navigation logic: Fails the approved information architecture.
- Form usability: Search is large enough; it needs a localized accessible label.
- Scroll behavior: The grid should use a virtualized two-column list.
- Empty/error behavior: Not shown; category fetch failure and empty states remain required.
- Accessibility concerns: Confirm 44–48 dp icon targets, meaningful image labels and Dynamic Type wrapping.

### Native implementation usability
The grid is realistic with `FlatList`, an RTL-aware header and reusable category cards. The bottom navigation must be replaced, and layout direction must come from locale rather than hard-coded positioning.

### Reusable components identified
- AppHeader
- SearchField
- CategoryPhotoCard
- CategoryGrid
- BuyerBottomTabs

### Dynamic backend data required
- Category id
- Arabic category name
- Category image
- Cart count
- Wishlist count

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Navigation | The RTL bar includes `طلباتي` (My Orders) and omits Cart. | Use the mirrored Arabic equivalents of Accueil, Catégories, Favoris, Panier and Compte only. |
| MAJOR | Accessibility | Category labels and photographic cards are not proven to reflow at larger text sizes. | Allow two lines, preserve reading order and test 200% text scaling. |
| MINOR | Header | Wishlist and cart placement differs from the French category reference without a documented state reason. | Standardize the locale-aware buyer header while preserving RTL mirroring. |

### Canonical recommendation
Combine its RTL grid with `02-categories-photo-grid-fr.png`; use the French screen's approved navigation destinations and mirror them correctly.

## 02-categories-photo-grid-fr.png

Folder: `02-discovery/`  
Screen purpose: French category directory  
Probable native route: `Categories`  
Language: FR  
Screen type: Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
The primary French category index with search, filtering and photographic category shortcuts.

### Visible UI structure
- Header: Official logo and notification action
- Main content: Search/filter row and category grid
- Cards: Three-column category image cards
- Forms: Search field
- Primary action: Open a category
- Secondary actions: Filter and notifications
- Navigation: Correct five buyer tabs; Catégories active
- Overlays: None

### Brand validation
- Logo: Correct official `MAYUSH DESIGN` artwork.
- Colors: Approved cream, orange, navy and white palette.
- Typography: Clear hierarchy consistent with the foundation direction.
- Icons: Coherent outline style.
- Buttons: Search and filter controls read as actionable.
- Cards: Rounded category cards with soft shadows.
- Spacing: Clean, though three columns constrain labels.
- Shadows: Consistent and subtle.

### UX validation
- Clear user goal: Yes.
- Primary action: Category cards are clear.
- Navigation logic: Correct five-tab buyer navigation.
- Form usability: Search and filter controls are usable.
- Scroll behavior: Use a vertical virtualized grid with bottom-content inset.
- Empty/error behavior: Not pictured; implement separately.
- Accessibility concerns: Three columns can clip long labels and Dynamic Type; all icon controls need labels.

### Native implementation usability
Directly feasible using an adaptive `FlatList`. Prefer two columns on compact widths or increased text size and allow labels to wrap.

### Reusable components identified
- AppHeader
- SearchField
- FilterIconButton
- CategoryPhotoCard
- CategoryGrid
- BuyerBottomTabs

### Dynamic backend data required
- Category id
- Category name
- Category image
- Notification count

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Responsive layout | The three-column grid leaves limited width for longer French category names and Dynamic Type. | Switch adaptively to two columns or permit robust two-line labels with equal-height cards. |
| MINOR | Accessibility | Icon-only notification and filter actions have no visible evidence of accessible naming. | Add accessibility labels, roles and 44–48 dp hit areas. |

### Canonical recommendation
Use directly as the main French Categories reference after the minor responsive and accessibility corrections.

## 02-category-landing-living-room-ar.png

Folder: `02-discovery/`  
Screen purpose: Arabic living-room category landing  
Probable native route: `CategoryLanding/:categoryId`  
Language: AR  
Screen type: Full page

### Status
REJECTED

### Confidence
High

### What the screen represents
An RTL living-room landing page combining hero content, subcategories and product recommendations.

### Visible UI structure
- Header: Mirrored back action, title and header actions
- Main content: Category hero, subcategory shortcuts and product rows
- Cards: Category and product cards
- Forms: Search/filter affordances
- Primary action: Shop the category/products
- Secondary actions: Wishlist and product shortcuts
- Navigation: Five visible RTL tabs, but wrong destinations
- Overlays: None

### Brand validation
- Logo: No visibly altered logo.
- Colors: Generally consistent cream/navy/orange styling.
- Typography: Arabic hierarchy is legible and largely RTL-aware.
- Icons: Mostly coherent.
- Buttons: Actions are visually clear.
- Cards: Appropriate marketplace card treatment.
- Spacing: Generally spacious.
- Shadows: Soft and consistent.

### UX validation
- Clear user goal: Yes.
- Primary action: Product/category discovery is clear.
- Navigation logic: Incorrect buyer destinations.
- Form usability: Controls appear usable but require native labels.
- Scroll behavior: Requires nested horizontal lists within a vertical list and safe bottom padding.
- Empty/error behavior: Not shown.
- Accessibility concerns: Arabic text expansion, mixed-direction prices and image descriptions need testing.

### Native implementation usability
Its component model is feasible, but the presented commerce and navigation data are not safe to implement. Rebuild with shared category and product components.

### Reusable components identified
- CategoryHero
- SubcategoryCard
- ProductCard
- HorizontalProductRail
- BuyerBottomTabs

### Dynamic backend data required
- Arabic category content
- Subcategories
- Product images, names, prices, stock and ratings
- Cart and wishlist state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Currency | Product prices use Saudi riyal notation (`ر.س`) instead of MAD. | Render localized numeric values with a readable `MAD` suffix. |
| CRITICAL | Navigation | My Orders is a permanent tab and Cart is absent. | Restore the approved five buyer destinations and mirror them for RTL. |
| MAJOR | Consistency | The route structure does not match the stronger French category/list system. | Rebuild from shared `CategoryHero`, `ProductCard` and approved tab components. |

### Canonical recommendation
Do not use as a complete reference. Rebuild the Arabic category landing from `02-categories-photo-grid-fr.png` and the corrected MAD listing patterns, retaining only its useful RTL content structure.

## 02-category-landing-salon-collections-fr.png

Folder: `02-discovery/`  
Screen purpose: French salon category landing  
Probable native route: `CategoryLanding/:categoryId`  
Language: FR  
Screen type: Full page

### Status
REJECTED

### Confidence
High

### What the screen represents
A salon landing page with a hero, curated collections and purchasable product cards.

### Visible UI structure
- Header: Logo and buyer actions
- Main content: Salon hero, collection content and product rails
- Cards: Promotional, collection and product cards
- Forms: None prominent
- Primary action: Explore a salon collection/product
- Secondary actions: Favorites and product browsing
- Navigation: Five tabs with Messages replacing Cart
- Overlays: None

### Brand validation
- Logo: Appears to use official artwork.
- Colors: Warm premium palette is visually aligned.
- Typography: Strong hierarchy.
- Icons: Generally coherent.
- Buttons: Clear orange calls to action.
- Cards: Visually polished and rounded.
- Spacing: Spacious.
- Shadows: Soft.

### UX validation
- Clear user goal: Yes.
- Primary action: Clear.
- Navigation logic: Contradicts the approved buyer IA.
- Form usability: Not applicable.
- Scroll behavior: Feasible as vertical scroll plus horizontal rails.
- Empty/error behavior: Not shown.
- Accessibility concerns: Product-card text and rail controls need Dynamic Type and screen-reader order.

### Native implementation usability
The content sections can be native components, but wrong currency and navigation make the full image unsafe. Use shared product data and list components rather than fixed promotional artwork.

### Reusable components identified
- CategoryHero
- CollectionCard
- ProductCard
- HorizontalProductRail
- BuyerBottomTabs

### Dynamic backend data required
- Category copy and hero image
- Collections
- Product names, images, MAD prices and stock
- Wishlist/cart state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Currency | Product prices are shown in FCFA. | Replace all price data and formatting with MAD. |
| CRITICAL | Navigation | Messages replaces the required Panier tab. | Use Accueil, Catégories, Favoris, Panier and Compte only. |
| MAJOR | Implementation | Promotional/product content appears overly tailored to a fixed composition. | Build sections from data-driven reusable rails and cards with text wrapping. |

### Canonical recommendation
Do not use as a complete reference. Rebuild the route using the corrected home hero and MAD product-list components.

## 02-collection-salon-contemporain-shop-the-look.png

Folder: `02-discovery/`  
Screen purpose: Shop-the-look collection page  
Probable native route: `CollectionDetail/:collectionId`  
Language: FR  
Screen type: Full page

### Status
REJECTED

### Confidence
High

### What the screen represents
A curated contemporary-salon editorial page with a hero scene and product selections.

### Visible UI structure
- Header: Menu, logo, search and bag
- Main content: Editorial hero, collection narrative and product rail
- Cards: Five very narrow product cards in one row
- Forms: Search entry
- Primary action: Shop the look/products
- Secondary actions: Favorite and bag
- Navigation: Accueil, Collections, Favoris, Panier, Profil
- Overlays: None

### Brand validation
- Logo: Official artwork appears intact.
- Colors: Broadly on palette.
- Typography: Premium but overly compressed in product content.
- Icons: Mixed header/navigation treatment relative to foundation boards.
- Buttons: Some actions are clear; tiny product actions are not.
- Cards: Five-across cards are inconsistent with practical mobile cards.
- Spacing: Editorial areas are spacious but commerce rail is cramped.
- Shadows: Acceptable.

### UX validation
- Clear user goal: The editorial concept is understandable.
- Primary action: Product purchase path is visually weak because cards are tiny.
- Navigation logic: Collections replaces Catégories and Profil naming differs from Compte.
- Form usability: Search action is identifiable.
- Scroll behavior: Static five-across layout is not viable; use a horizontal list.
- Empty/error behavior: Not shown.
- Accessibility concerns: Product copy and controls are below practical reading/tap sizes and will fail Dynamic Type.

### Native implementation usability
The editorial hero can be implemented, but the five-across commerce area behaves like a poster. It must be rebuilt as a horizontally virtualized rail with standard ProductCards.

### Reusable components identified
- AppHeader
- CollectionHero
- EditorialSection
- ProductCard
- HorizontalProductRail
- BuyerBottomTabs

### Dynamic backend data required
- Collection title and story
- Hero/editorial imagery
- Tagged products and coordinates if the image is interactive
- Product names, images, MAD prices and availability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Currency | Prices use euros. | Use MAD exclusively. |
| CRITICAL | Navigation | Collections/Profil replace approved Catégories/Compte labels. | Use the exact buyer five-tab IA. |
| MAJOR | Mobile usability | Five product cards are forced across one phone width. | Use 1.5–2.2 visible cards in a horizontal rail with 44–48 dp actions. |
| MAJOR | Accessibility | Tiny product text cannot accommodate Dynamic Type. | Use shared type tokens, multiline truncation rules and scalable card heights. |

### Canonical recommendation
Do not use as a complete implementation reference; retain only the editorial hero idea and rebuild the commerce/navigation regions from approved components.

## 02-filter-panel-category-price-color-material.png

Folder: `02-discovery/`  
Screen purpose: Product filter controls  
Probable native route: `ProductFilters`  
Language: FR  
Screen type: Bottom sheet

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A comprehensive filtering surface for category, price, color, material, seller, stock, promotion, rating and delivery criteria.

### Visible UI structure
- Header: Filter title and close action
- Main content: Grouped filter controls
- Cards: Section containers
- Forms: Checkboxes, chips, swatches, slider and toggles
- Primary action: Apply filters
- Secondary actions: Reset and close
- Navigation: No bottom navigation required for this sheet
- Overlays: Modal bottom-sheet presentation

### Brand validation
- Logo: Not required.
- Colors: Brand neutrals and orange are mostly consistent.
- Typography: Clear section hierarchy.
- Icons: Coherent, but controls need state semantics.
- Buttons: Apply/reset hierarchy is understandable.
- Cards: Rounded grouped controls fit the design system.
- Spacing: Dense because too many groups appear in one surface.
- Shadows: Sheet elevation is appropriate.

### UX validation
- Clear user goal: Yes.
- Primary action: Apply is clear.
- Navigation logic: Correctly modal.
- Form usability: Euro-based slider is invalid; slider lacks precise accessible entry.
- Scroll behavior: Body must scroll independently while footer remains safely sticky.
- Empty/error behavior: Not applicable; unavailable options should expose disabled states.
- Accessibility concerns: Swatches need color names, checkboxes need large hit areas and the slider needs screen-reader values/actions.

### Native implementation usability
Feasible as a reusable RN bottom sheet with a `ScrollView`/virtualized sections and fixed action footer. Avoid a hard-coded tall poster; state must be driven by filter metadata.

### Reusable components identified
- BottomSheet
- FilterSection
- ChoiceChip
- CheckboxRow
- ColorSwatch
- PriceRangeInput
- StickyActionFooter

### Dynamic backend data required
- Filter facets and counts
- Minimum/maximum MAD price
- Materials and colors
- Seller options
- Availability, rating, promotion and delivery options
- Current filter state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Currency | Price range uses euros. | Use MAD values and Moroccan numeric formatting. |
| MAJOR | Mobile usability | The sheet is overloaded with many filter groups. | Use collapsible sections and preserve selected counts; keep Apply/Reset in a safe-area footer. |
| MAJOR | Accessibility | Color-only swatches and a visual slider do not expose accessible names or exact values. | Add text labels, selected-state announcements and numeric min/max inputs or accessible increment actions. |
| MINOR | Safe area | Footer clearance is not demonstrated. | Apply keyboard and bottom safe-area insets and test small screens. |

### Canonical recommendation
Correct before use; it is the preferred filter-surface concept after converting currency, simplifying sections and implementing accessible native controls.

## 02-flash-deals-countdown-timer.png

Folder: `02-discovery/`  
Screen purpose: Time-limited deals page  
Probable native route: `FlashDeals`  
Language: FR  
Screen type: Full page

### Status
REJECTED

### Confidence
High

### What the screen represents
A marketplace flash-sale page with countdown timers and discounted product cards.

### Visible UI structure
- Header: Altered Mayush branding and buyer actions
- Main content: Large countdown and deal categories
- Cards: Electronics, fashion and beauty deal cards
- Forms: None prominent
- Primary action: Purchase deal products
- Secondary actions: Favorite and browse deal tabs
- Navigation: Flash Deals is a permanent tab; Panier is absent
- Overlays: None

### Brand validation
- Logo: Invalid `BUYER` wording is added to the logo.
- Colors: Orange/navy broadly align, but the overall merchandising is off-brand.
- Typography: Readable but generic flash-commerce styling.
- Icons: Mixed retail icon treatment.
- Buttons: Clear, though based on an invalid route/product scope.
- Cards: Visually coherent but not Mayush furniture cards.
- Spacing: Dense.
- Shadows: Acceptable.

### UX validation
- Clear user goal: Yes, but it is the wrong marketplace scope.
- Primary action: Clear.
- Navigation logic: Contradicts approved buyer navigation.
- Form usability: Not applicable.
- Scroll behavior: Feasible, but repeated per-card timers would be noisy and expensive.
- Empty/error behavior: Expired-deal behavior is not defined.
- Accessibility concerns: Timers need non-continuous announcements and expiry-state handling.

### Native implementation usability
A timer list is technically possible, but this image should not define Mayush. It would require validated campaign rules, centralized timer state and furniture-specific data.

### Reusable components identified
- CountdownBanner
- DealCard
- CategoryChip
- BuyerBottomTabs

### Dynamic backend data required
- Campaign start/end timestamps
- Eligible furniture products
- MAD prices and discounts
- Stock and expiry state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Branding | The official logo is modified with `BUYER`. | Use only the official `MAYUSH DESIGN` asset. |
| CRITICAL | Product scope | Electronics, phones, watches, sneakers and beauty goods contradict the furniture/interior marketplace. | Use only validated Mayush buyer catalogue categories. |
| CRITICAL | Currency | Prices use FCFA. | Use MAD. |
| CRITICAL | Navigation | Flash Deals replaces a required permanent tab and Panier is missing. | Restore the exact buyer navigation. |
| MAJOR | Product logic | Countdown/expiry behavior is not defined. | NEEDS PRODUCT DECISION: define campaign eligibility, stock, expiry and price-transition rules before designing this route. |

### Canonical recommendation
Reject as an implementation reference. If flash campaigns are approved by product, create a new state from the canonical home/product components and validated campaign logic.

## 02-home-hero-new-arrivals-best-sellers-fr.png

Folder: `02-discovery/`  
Screen purpose: French buyer home  
Probable native route: `Home`  
Language: FR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
The main French home journey with hero promotion, category shortcuts, new arrivals, best sellers and inspiration content.

### Visible UI structure
- Header: Official logo, search and buyer actions
- Main content: Hero, categories, product rails, offer and inspiration sections
- Cards: Category, product and editorial cards
- Forms: Search entry
- Primary action: Shop hero or product
- Secondary actions: See-all, favorite and category actions
- Navigation: Correct five buyer tabs
- Overlays: None

### Brand validation
- Logo: Correct official artwork.
- Colors: Strong match to cream/orange/navy/white direction.
- Typography: Premium, clear hierarchy.
- Icons: Coherent outline family.
- Buttons: Orange CTAs are consistent.
- Cards: Good source-board alignment.
- Spacing: Clean overall; product rails are tight.
- Shadows: Soft and premium.

### UX validation
- Clear user goal: Yes.
- Primary action: Multiple but well organized.
- Navigation logic: Correct.
- Form usability: Search is prominent.
- Scroll behavior: Use vertical `FlatList` sections and horizontal virtualized rails.
- Empty/error behavior: Not shown; rail-level skeleton/error/empty states are required.
- Accessibility concerns: Four narrow product cards cannot safely support Dynamic Type; carousel controls and imagery need labels.

### Native implementation usability
This is the strongest French Home structure. It is feasible when each rail is data-driven and responsive; do not reproduce the dense row as a fixed four-column poster.

### Reusable components identified
- HomeHeader
- SearchField
- HeroCarousel
- CategoryShortcut
- ProductCard
- HorizontalProductRail
- EditorialCard
- BuyerBottomTabs

### Dynamic backend data required
- Hero campaigns
- Categories
- New-arrival and best-seller products
- Product images, names, MAD prices, ratings and stock
- Inspiration content
- Cart/wishlist state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Currency | Product prices use euros throughout. | Convert all price sources and formatting to MAD. |
| MAJOR | Responsive layout | Four product cards are shown across the phone width with limited copy space. | Use a horizontal rail showing roughly 1.5–2.2 standard cards and support multiline names. |
| MINOR | Accessibility | No evidence of carousel announcements or labeled icon controls. | Add labels, current-slide semantics and 44–48 dp hit areas. |

### Canonical recommendation
Correct before use; after MAD conversion and adaptive rail sizing, this should be the canonical French Home reference.

## 02-home-hero-shop-by-category-ar.png

Folder: `02-discovery/`  
Screen purpose: Arabic buyer home  
Probable native route: `Home`  
Language: AR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An RTL home experience with greeting, hero content, category discovery, products and promotions.

### Visible UI structure
- Header: RTL greeting, notification and official logo
- Main content: Search, hero, categories, product and promotional sections
- Cards: Category, product and editorial cards
- Forms: Search entry
- Primary action: Shop hero/products
- Secondary actions: Category, favorite and see-all actions
- Navigation: Correct five destinations mirrored for RTL
- Overlays: None

### Brand validation
- Logo: Official artwork appears preserved.
- Colors: Strong source-system alignment.
- Typography: Arabic hierarchy is readable; implementation should use the approved Arabic family.
- Icons: Coherent and correctly positioned for RTL overall.
- Buttons: Clear orange hierarchy.
- Cards: Consistent white/rounded treatment.
- Spacing: Spacious.
- Shadows: Soft.

### UX validation
- Clear user goal: Yes.
- Primary action: Clear.
- Navigation logic: Correct destinations and mirrored order.
- Form usability: RTL search entry is understandable.
- Scroll behavior: Native nested rails are feasible with RTL list direction.
- Empty/error behavior: Not shown.
- Accessibility concerns: Mixed Arabic/numeral/currency direction, long copy and icon mirroring require device testing.

### Native implementation usability
Feasible as the Arabic counterpart to the canonical French Home. Use locale-aware direction and shared data components; avoid rasterized Arabic text.

### Reusable components identified
- RtlHomeHeader
- SearchField
- HeroCarousel
- CategoryShortcut
- ProductCard
- HorizontalProductRail
- BuyerBottomTabs

### Dynamic backend data required
- Arabic localized hero and category copy
- Product images, names, MAD prices, stock and ratings
- Cart/wishlist state
- User greeting data

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Currency | Prices use Saudi riyal (`ر.س`) rather than MAD. | Render MAD while preserving readable bidi ordering. |
| MAJOR | Localization | Mixed-direction price strings are not validated for Arabic rendering. | Use locale-aware number formatting and explicit bidi-safe currency composition. |
| MINOR | Accessibility | Longer Arabic/Dynamic Type behavior is not demonstrated. | Test 200% text scaling, screen-reader order and mirrored focus sequence. |

### Canonical recommendation
Correct before use; it is the preferred Arabic Home reference once all prices use bidi-safe MAD formatting and locale testing passes.

## 02-home-logged-in-personalized-recommendations.png

Folder: `02-discovery/`  
Screen purpose: Personalized logged-in home  
Probable native route: `Home`  
Language: FR  
Screen type: Full page

### Status
REJECTED

### Confidence
High

### What the screen represents
A logged-in home dashboard with greeting, order tracking, recommendations, recently viewed content, wishlist inspiration and categories.

### Visible UI structure
- Header: Logo, user avatar/greeting, notifications and cart
- Main content: Hero, active-order card and multiple personalized rails
- Cards: Order, product, inspiration and category cards
- Forms: None prominent
- Primary action: Discover hero content or track order
- Secondary actions: See-all and favorite actions
- Navigation: Accueil, Explorer, Wishlist, Commandes, Compte
- Overlays: None

### Brand validation
- Logo: Official artwork appears intact.
- Colors: Generally on palette.
- Typography: Clear but very content-heavy.
- Icons: Labels switch between French and English (`Wishlist`).
- Buttons: Clear.
- Cards: Individually coherent.
- Spacing: Too many sections produce a dashboard-like poster.
- Shadows: Soft.

### UX validation
- Clear user goal: Competing goals dilute the home hierarchy.
- Primary action: Hero and order-tracking actions compete.
- Navigation logic: Explorer/Wishlist/Commandes violate approved destinations and language consistency.
- Form usability: Not applicable.
- Scroll behavior: Very long feed needs section virtualization and server-driven pagination.
- Empty/error behavior: No treatment for absent active order/history/personalization.
- Accessibility concerns: Four-across cards and dense content will fail large text and create excessive focus length.

### Native implementation usability
Individual modules are implementable, but the whole page is overloaded. Personalization sections should be optional modules on the canonical Home with clear empty and loading states.

### Reusable components identified
- PersonalizedHeader
- ActiveOrderCard
- ProductCard
- HorizontalProductRail
- InspirationCard
- CategoryShortcut

### Dynamic backend data required
- User name and avatar
- Active order and tracking summary
- Personalized recommendations
- Recently viewed products
- Wishlist-derived inspiration
- Categories
- Notification and cart counts

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Currency | Product prices use FCFA. | Use MAD only. |
| CRITICAL | Navigation | Explorer and Commandes replace approved Catégories/Panier; Wishlist is inconsistent English. | Use the exact French buyer tab labels. |
| MAJOR | Information architecture | Too many personalized modules compete on one screen. | Keep hero plus two prioritized modules; lazy-load or move the rest behind see-all routes. |
| MAJOR | State logic | Active order and recommendation modules have no empty/failure behavior. | Define conditional module visibility and skeleton/error/empty states. |

### Canonical recommendation
Do not use as a complete reference. Add only the active-order and recommendation modules, after validation, to the corrected canonical French Home.

## 02-promotions-campaigns-offers.png

Folder: `02-discovery/`  
Screen purpose: Promotions and campaigns listing  
Probable native route: `Promotions`  
Language: FR  
Screen type: Full page

### Status
REJECTED

### Confidence
High

### What the screen represents
A promotions hub with a campaign hero, campaign cards, offer filters, discounted products and service claims.

### Visible UI structure
- Header: Menu, altered logo and cart
- Main content: Promotion hero, campaigns, filter chips, product grid and trust strip
- Cards: Campaign and discounted product cards
- Forms: Offer category chips
- Primary action: Discover offers/add products
- Secondary actions: View all and favorite
- Navigation: Accueil, Rechercher, Promotions, Favoris, Compte
- Overlays: None

### Brand validation
- Logo: Invalid `BUYER` wording is added.
- Colors: Orange/navy/cream are broadly aligned.
- Typography: Clear but promotion-heavy.
- Icons: Generally coherent.
- Buttons: Strong hierarchy.
- Cards: Polished but based on invalid commerce data.
- Spacing: Dense yet readable.
- Shadows: Soft.

### UX validation
- Clear user goal: Yes.
- Primary action: Clear.
- Navigation logic: Promotions/Rechercher replace required Catégories/Panier.
- Form usability: Filter chips are understandable.
- Scroll behavior: Feasible as sectioned lists.
- Empty/error behavior: No campaign-expired or no-offer state.
- Accessibility concerns: Discount badges, old prices and chip selection need accessible semantics.

### Native implementation usability
Sections are implementable, but the screen contains invalid branding, currency, geography and navigation. Campaign availability and claims must be backend-driven and product-approved.

### Reusable components identified
- CampaignHero
- CampaignCard
- FilterChip
- ProductCard
- TrustClaimRow
- BuyerBottomTabs

### Dynamic backend data required
- Campaign eligibility and validity dates
- Offer categories
- Product images, MAD prices, original prices and discounts
- Stock
- Delivery/service claims

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Branding | `BUYER` modifies the official logo. | Use the exact official logo asset. |
| CRITICAL | Currency | Product prices use euros. | Use MAD. |
| CRITICAL | Geography | Trust copy says delivery everywhere in France. | Replace with validated Morocco service coverage. |
| CRITICAL | Navigation | Promotions/Rechercher displace Catégories/Panier. | Restore the five approved buyer tabs. |
| MAJOR | Product logic | Campaign terms and service claims appear invented. | NEEDS PRODUCT DECISION: validate discount, date, payment and delivery claims before publication. |

### Canonical recommendation
Reject as a full reference. If Promotions is an approved secondary route, rebuild it from the canonical Home/ProductCard system with validated Moroccan campaign data.

## 02-recently-viewed-products.png

Folder: `02-discovery/`  
Screen purpose: Recently viewed products and continuation recommendations  
Probable native route: `RecentlyViewed`  
Language: FR  
Screen type: Full page

### Status
REJECTED

### Confidence
High

### What the screen represents
A chronological history of viewed products followed by recommended products.

### Visible UI structure
- Header: Logo, search, wishlist and cart
- Main content: Recent-history list and recommendation rail
- Cards: Large history rows and smaller recommendation cards
- Forms: None
- Primary action: Reopen a recent product
- Secondary actions: History management, see-all and favorite
- Navigation: Récents is a permanent tab; Panier is absent
- Overlays: None

### Brand validation
- Logo: Official artwork appears preserved.
- Colors: Strong palette alignment.
- Typography: Clear hierarchy.
- Icons: Mostly coherent.
- Buttons: Row chevrons are clear.
- Cards: Good list-card style.
- Spacing: Comfortable.
- Shadows: Soft.

### UX validation
- Clear user goal: Yes.
- Primary action: Recent items are clearly actionable.
- Navigation logic: The permanent Recents tab violates the buyer IA.
- Form usability: Not applicable.
- Scroll behavior: Suitable for a virtualized list; recommendations can be a horizontal footer.
- Empty/error behavior: Missing empty-history state and privacy/history-clear confirmation.
- Accessibility concerns: Row must be one labeled target; recommendation cards need scalable text.

### Native implementation usability
The list pattern is practical, but it should be a secondary route reached from Account/Search/Home, not a permanent tab. History storage/clearing rules need definition.

### Reusable components identified
- AppHeader
- RecentlyViewedRow
- ProductCard
- HorizontalProductRail
- EmptyHistoryState

### Dynamic backend data required
- Viewed product ids and timestamps
- Product images, names, MAD prices and stock
- Recommendations
- Wishlist/cart state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Currency | All prices use FCFA. | Use MAD. |
| CRITICAL | Navigation | Récents replaces the required Panier tab. | Make this a secondary route and retain the exact buyer tabs. |
| MAJOR | Product logic | `Historique` lacks clear/delete and privacy behavior. | NEEDS PRODUCT DECISION: define retention, sync and clearing rules; add an empty state. |

### Canonical recommendation
Do not use as a complete reference. Reuse its large history-row component on a secondary route with MAD, canonical navigation and defined history behavior.

## 02-search-no-results-found.png

Folder: `02-discovery/`  
Screen purpose: Search no-results state  
Probable native route: `SearchResults?query=`  
Language: FR  
Screen type: Empty state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A no-results response retaining the query, recovery actions, category suggestions and support access.

### Visible UI structure
- Header: Menu, logo, wishlist and cart
- Main content: Search/filter row, empty illustration, guidance and category suggestions
- Cards: Category shortcuts and help card
- Forms: Editable search field
- Primary action: Discover new products
- Secondary actions: Modify search, browse category, contact support
- Navigation: Rechercher is a permanent tab; Panier is absent
- Overlays: None

### Brand validation
- Logo: Correct official artwork.
- Colors: Strong source-system alignment.
- Typography: Clear empty-state hierarchy.
- Icons: Coherent outline family.
- Buttons: Primary and secondary actions are differentiated.
- Cards: Rounded beige category/help cards are consistent.
- Spacing: Spacious and calm.
- Shadows: Minimal and appropriate.

### UX validation
- Clear user goal: Recovery from no results is very clear.
- Primary action: Both modifying search and exploring content are useful.
- Navigation logic: Incorrect permanent search tab replaces Cart.
- Form usability: Search query remains editable; keyboard-safe layout is required.
- Scroll behavior: Content must scroll on smaller phones/Dynamic Type with nav inset.
- Empty/error behavior: This is a strong empty state; distinguish it from network/server error.
- Accessibility concerns: Decorative illustration should be hidden; query and button labels must be announced without repetition.

### Native implementation usability
Straightforward native empty-state composition using reusable search and suggestion components. Ensure the keyboard does not cover actions.

### Reusable components identified
- AppHeader
- SearchField
- FilterIconButton
- EmptyStateIllustration
- PrimaryButton
- SecondaryButton
- CategoryShortcut
- SupportCard
- BuyerBottomTabs

### Dynamic backend data required
- Search query
- Suggested categories
- New-arrival destination
- Support availability/link
- Cart count

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Rechercher is a permanent tab and Panier is missing. | Use the exact buyer five-tab bar; search remains a route/action. |
| MAJOR | State semantics | No-results and connectivity failure could be confused without a separate error state. | Keep this copy only for a successful zero-result response and provide retry UI for network errors. |
| MINOR | Accessibility | The illustration and category carousel need explicit decorative/interactive semantics. | Hide decorative layers; label cards and announce horizontal-list position. |

### Canonical recommendation
Correct before use; this is the canonical French search empty-state reference once the bottom bar is normalized.

## 02-search-recent-popular-trending-categories.png

Folder: `02-discovery/`  
Screen purpose: Search landing with recent/popular queries and suggestions  
Probable native route: `Search`  
Language: FR  
Screen type: Full page

### Status
REJECTED

### Confidence
High

### What the screen represents
A search landing showing recent queries, popular searches, trending categories and suggested products.

### Visible UI structure
- Header: Logo, cart and notifications
- Main content: Search field, query chips, category rail and product rail
- Cards: Category and product cards
- Forms: Search input and removable recent-query chips
- Primary action: Submit search
- Secondary actions: Clear history, open suggestions/categories/products
- Navigation: Accueil, Découvrir, Rechercher, Projets, Compte
- Overlays: None

### Brand validation
- Logo: Official artwork appears intact.
- Colors: Warm premium palette is consistent.
- Typography: Clear hierarchy.
- Icons: Generally coherent, though navigation diverges.
- Buttons: Search CTA is prominent.
- Cards: Visually polished.
- Spacing: Dense but organized.
- Shadows: Soft.

### UX validation
- Clear user goal: Yes.
- Primary action: Clear.
- Navigation logic: Three destinations differ from approved buyer tabs and Cart/Favoris are missing.
- Form usability: Good visible query and clear action; clear-history needs confirmation if destructive.
- Scroll behavior: Feasible with sectioned vertical content and horizontal rails.
- Empty/error behavior: No recent-search empty state.
- Accessibility concerns: Chip wrapping, keyboard focus, Dynamic Type and clear-button labels need implementation rules.

### Native implementation usability
Search modules are reusable, but the full screen combines invalid navigation and commerce context. Build as a sectioned list with adaptive wrapping chips.

### Reusable components identified
- SearchField
- SearchQueryChip
- SectionHeader
- CategoryCard
- ProductCard
- BuyerBottomTabs

### Dynamic backend data required
- Local/synced recent queries
- Popular query terms
- Trending categories and counts
- Suggested product images, names, MAD prices and stock

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Currency | Suggested product prices use euros. | Use MAD. |
| CRITICAL | Navigation | Découvrir, Rechercher and Projets replace Catégories, Favoris and Panier. | Restore the approved five buyer tabs. |
| MAJOR | History behavior | `Tout effacer` behavior and history source are undefined. | NEEDS PRODUCT DECISION: define local/account sync and require an undo or confirmation. |
| MAJOR | Responsive layout | Query chips and four-across rails may break under Dynamic Type. | Use flex-wrapping chips and standard horizontally scrolling cards. |

### Canonical recommendation
Do not use as a complete reference. Rebuild the Search landing using its recent/popular-query hierarchy plus the corrected canonical navigation and MAD ProductCards.

## 02-search-results-grid-fauteuil.png

Folder: `02-discovery/`  
Screen purpose: Product search results  
Probable native route: `SearchResults?query=fauteuil`  
Language: FR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A filtered two-column result grid for the query “fauteuil,” with sorting, saved search and complementary products.

### Visible UI structure
- Header: Logo, wishlist and cart
- Main content: Search field, result count, filter/sort controls, chips and grid
- Cards: Two-column ProductCards and complementary mini cards
- Forms: Search, filter, sort and saved-search action
- Primary action: Open/add a result
- Secondary actions: Save search, filter, sort, favorite and clear filters
- Navigation: Rechercher replaces Panier; Catégories receives an unusual floating center treatment
- Overlays: None

### Brand validation
- Logo: Official artwork is preserved.
- Colors: Strong palette alignment.
- Typography: Clear hierarchy and readable MAD pricing.
- Icons: Mostly coherent; active-tab treatment conflicts with foundation navigation.
- Buttons: Filter/sort and product actions are clear.
- Cards: Strong reusable ProductCard pattern.
- Spacing: Appropriate for two columns.
- Shadows: Soft.

### UX validation
- Clear user goal: Yes.
- Primary action: Product discovery is direct.
- Navigation logic: Incorrect tab set and inconsistent active state.
- Form usability: Filter/sort controls are clear; saved search requires authenticated/guest behavior.
- Scroll behavior: Use a virtualized two-column list, not a nested full grid in `ScrollView`.
- Empty/error behavior: Pair with the no-results state and add retry/skeleton states.
- Accessibility concerns: Icon-only cart/favorite controls, filter-chip removal and crossed prices need complete spoken labels.

### Native implementation usability
Highly feasible as a `FlatList` with a list header and two columns. Product card heights must tolerate wrapped names and badges.

### Reusable components identified
- SearchResultsHeader
- SearchField
- FilterButton
- SortButton
- FilterChip
- ProductCard
- StockBadge
- BuyerBottomTabs

### Dynamic backend data required
- Query and result count
- Active filters and sort
- Product ids, images, names, materials, colors, MAD prices and stock
- Wishlist/cart state
- Save-search state
- Complementary products

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Rechercher replaces Panier and the active Catégories treatment is inconsistent. | Use the standard five buyer tabs and standard active-state styling; search may sit above the tab bar. |
| MAJOR | Product logic | Save-search behavior is unspecified for guests and authenticated users. | NEEDS PRODUCT DECISION: define authentication, persistence and notification behavior. |
| MINOR | Accessibility | Product action icons and removable chips rely heavily on icons. | Add full product/action labels, selected states and minimum 44–48 dp targets. |

### Canonical recommendation
Correct before use; this is the preferred search-results grid once bottom navigation and save-search behavior are normalized.

## 02-subcategory-canapes-filtered-list.png

Folder: `02-discovery/`  
Screen purpose: Filtered sofa subcategory product list  
Probable native route: `CategoryProducts/:categoryId`  
Language: FR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A two-column Canapés catalogue with query, camera search, filter/sort controls and applied-filter chips.

### Visible UI structure
- Header: Menu, official logo, cart and account
- Main content: Title/count, search, filters, sort, chips and product grid
- Cards: Two-column sofa ProductCards
- Forms: Search, camera action, filter and sort
- Primary action: Open a sofa
- Secondary actions: Favorite, clear filters and cart/account actions
- Navigation: Accueil, Catégories, Favoris, Commandes, Messages
- Overlays: None

### Brand validation
- Logo: Official artwork appears preserved.
- Colors: Mostly correct; red discount badges are a valid semantic sale color but should be tokenized.
- Typography: Clear hierarchy and MAD pricing.
- Icons: Coherent within content; tab family/destinations diverge.
- Buttons: Controls are understandable.
- Cards: Strong, realistic product cards.
- Spacing: Usable two-column density.
- Shadows: Minimal/soft.

### UX validation
- Clear user goal: Yes.
- Primary action: Product cards are obvious.
- Navigation logic: Commandes and Messages replace Panier and Compte.
- Form usability: Search/filter/sort are useful; camera icon interaction is undefined.
- Scroll behavior: Use `FlatList` with sticky/filter header only if tested for small screens.
- Empty/error behavior: Filtered-empty and load-more failure states are not shown.
- Accessibility concerns: Camera purpose, crossed prices, stock and favorite state need full spoken output; cards must grow for Dynamic Type.

### Native implementation usability
The grid is a practical native pattern. Use a single virtualized list with a header, shared cards and pagination; do not render fixed screenshot-specific items.

### Reusable components identified
- CategoryResultsHeader
- SearchField
- CameraSearchButton
- FilterButton
- SortButton
- FilterChip
- ProductCard
- SaleBadge
- StockBadge
- BuyerBottomTabs

### Dynamic backend data required
- Category and result count
- Query/filter/sort state
- Product ids, images, names, colors, MAD prices, original prices, discounts and stock
- Wishlist/cart state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Navigation | Commandes and Messages replace the required Panier and Compte tabs. | Use Accueil, Catégories, Favoris, Panier and Compte only. |
| MAJOR | Interaction | Camera search appears interactive but its behavior is not established. | NEEDS PRODUCT DECISION: define image-search permission, upload, failure and privacy behavior or remove the icon. |
| MAJOR | State coverage | No zero-result or pagination error behavior is represented. | Reuse the corrected empty state and add inline retry/loading components. |
| MINOR | Accessibility | Discounts and old/new prices need a coherent reading order. | Announce product name, current price, previous price, discount, stock and favorite state once per card. |

### Canonical recommendation
Correct before use; it is the preferred category-product grid after restoring canonical tabs and resolving or removing camera search.

## Folder status totals

| Status | Count |
|---|---:|
| APPROVED | 0 |
| APPROVED_WITH_MINOR_FIXES | 1 |
| NEEDS_REWORK | 7 |
| REFERENCE_ONLY | 0 |
| REJECTED | 8 |
| DUPLICATE_ALTERNATIVE | 0 |
