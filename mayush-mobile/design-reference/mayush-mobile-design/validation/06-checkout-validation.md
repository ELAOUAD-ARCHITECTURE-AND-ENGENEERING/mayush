# 06 — Checkout validation

> **Fact-check scope:** Currency and address examples are accepted variations. Do not treat them as validation defects by themselves; [fact-check-correction.md](./fact-check-correction.md) supersedes earlier currency/address severity notes.

Scope: 52 buyer checkout, payment and confirmation screenshots were visually reviewed against 00-foundation and assetsl. Baseline: official MAYUSH DESIGN logo, cream/white surfaces, navy text/icons, Mayush-orange action color, rounded cards, soft shadows, MAD, Moroccan address context and +212. Bottom navigation is correctly omitted in checkout/payment flows. Wallet, COD, carrier promises, refunds and security claims require a confirmed product decision.

## 06-add-address-validation-errors-fr.png

Folder: 06-checkout  
Screen purpose: Address-form validation failure before checkout.  
Probable native route: CheckoutAddressForm  
Language: French  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A submitted delivery-address form showing invalid phone, missing city and an unavailable delivery zone.

### Visible UI structure
- Back header and official logo
- Address inputs, inline errors and unavailable-zone panel
- Help panel and fixed Corriger l’adresse et continuer action

### Brand validation
Palette, rounded controls, logo and navy/orange icon language are aligned; red is semantic for errors. The French examples use a France-style 75019 postal code and unprefixed phone format.

### UX validation
Field-level feedback is clear, but the form must scroll above the keyboard and the primary action must remain disabled until errors are fixed. France-specific data contradicts the Morocco baseline.

### Native implementation usability
Implementable with a scroll view, controlled inputs, keyboard avoidance, input-error state and sticky CTA; zone validation must be server-backed or explicitly cached.

### Reusable components identified
- CheckoutHeader
- AddressForm
- FormInput
- InlineFieldError
- DeliveryZoneAlert
- StickyPrimaryButton

### Dynamic backend data required
- Existing address values
- Phone/city validation result
- Serviceability result for address and cart

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Localisation | 75019 and 06 12 34 56 visually read as French rather than Moroccan checkout data. | Use Moroccan examples and the +212 selector consistently. |
| MAJOR | Form state | The orange continue CTA remains visually enabled while required errors remain. | Disable it until errors clear and expose the disabled reason. |
| MINOR | Accessibility | Error text is long and dense near the bottom action. | Verify Dynamic Type, scroll-to-first-error and keyboard-safe spacing. |

### Canonical recommendation
Correct before use; retain its clear field-error and serviceability patterns.

## 06-add-new-address-form-ar.png

Folder: 06-checkout  
Screen purpose: Create a Moroccan delivery address in Arabic.  
Probable native route: CheckoutAddressForm  
Language: Arabic  
Screen type: Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A complete RTL address-entry form with +212, city and delivery-zone selection, address label and default toggle.

### Visible UI structure
- RTL logo header with back action
- Eight structured address fields
- Address-label chips, default toggle and save CTA

### Brand validation
The official logo, cream background, white controls, navy outline icons and orange selection state follow the expected system.

### UX validation
The hierarchy is logical and the Moroccan prefix is explicit. The long form needs keyboard-safe scrolling and accessible required/optional states.

### Native implementation usability
Suitable for a reusable controlled form with RTL layout, localized labels and bottom-safe submission.

### Reusable components identified
- CheckoutHeader
- AddressForm
- CountryPhoneInput
- SelectInput
- SegmentedChoice
- SwitchRow
- PrimaryButton

### Dynamic backend data required
- Country calling codes
- Cities and delivery zones
- Address fields and default-address preference

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Native usability | Eight fields plus labels and a toggle require scroll/keyboard behavior not visible in a static composition. | Define focus order, keyboard avoidance, validation and sticky-save behavior. |

### Canonical recommendation
Use directly after minor native-form behavior is specified; this is the Arabic add-address reference.

## 06-add-new-address-form-fr.png

Folder: 06-checkout  
Screen purpose: Create a Moroccan delivery address in French.  
Probable native route: CheckoutAddressForm  
Language: French  
Screen type: Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
The French equivalent of the checkout address form, including +212, city, zone, address label and default control.

### Visible UI structure
- Back/logo header
- Vertical labeled form controls
- Address-type chip row, default switch and save CTA

### Brand validation
Brand colors, full official logo, form-control treatment and outline icons are consistent with the foundation direction.

### UX validation
The goal and required inputs are clear. Long address labels and keyboard behavior require testing; postal-code requirements must be city-aware rather than hardcoded.

### Native implementation usability
Suitable as a reusable React Native form screen with a scroll container and localized validation schema.

### Reusable components identified
- CheckoutHeader
- AddressForm
- CountryPhoneInput
- SelectInput
- SegmentedChoice
- SwitchRow
- PrimaryButton

### Dynamic backend data required
- Calling code list
- Cities/zones
- Address values, validation and default preference

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Native usability | The static layout does not show focus, invalid, loading or keyboard states. | Define these states and keep save clear of the keyboard. |

### Canonical recommendation
Use directly after minor behavior specification; canonical French add-address form.

## 06-add-new-address-form-v2-fr.png

Folder: 06-checkout  
Screen purpose: Duplicate French add-address form.  
Probable native route: CheckoutAddressForm  
Language: French  
Screen type: Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Byte-identical duplicate of 06-add-new-address-form-fr.png.

### Visible UI structure
- Same header, fields, label choices, switch and save CTA

### Brand validation
Identical to the canonical asset.

### UX validation
Identical to the canonical asset.

### Native implementation usability
No additional implementation value.

### Reusable components identified
- AddressForm
- CountryPhoneInput
- PrimaryButton

### Dynamic backend data required
- Same as canonical French add-address form

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Version control | Exact duplicate of 06-add-new-address-form-fr.png. | Keep the non-v2 file as canonical; preserve this historical duplicate only. |

### Canonical recommendation
Keep only as an alternative; use 06-add-new-address-form-fr.png.

## 06-cash-on-delivery-confirmation-fr.png

Folder: 06-checkout  
Screen purpose: Confirm cash-on-delivery terms and amount.  
Probable native route: CheckoutCODConfirmation  
Language: French  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
Medium

### What the screen represents
A COD confirmation view calculating order amount plus COD fee and asking for acceptance.

### Visible UI structure
- Back/logo header and COD explanation
- Amount breakdown, condition card, acceptance row
- Orange confirm CTA and trust footer

### Brand validation
Strong palette, card treatment and icon consistency; all amounts use MAD.

### UX validation
The amount is legible, but COD eligibility, COD fee and inspection/return wording are commercial policy decisions. The acceptance visual must map to an actual control and final order confirmation.

### Native implementation usability
Implementable as a confirmation screen once COD eligibility and fee calculation are defined per order/address.

### Reusable components identified
- PaymentHeader
- AmountBreakdown
- NoticeCard
- ConsentCheckbox
- PrimaryButton

### Dynamic backend data required
- COD eligibility
- Order amount, COD fee and total
- COD terms version and consent record

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Product decision | COD availability, 20,00 MAD fee and inspection/return promises are presented as universal facts. | Confirm policy by seller, city and order value; otherwise state eligibility dynamically. |
| MAJOR | Consent | The acceptance state and whether confirmation is blocked before consent are not unambiguous. | Use a native checkbox with a disabled CTA until recorded consent. |

### Canonical recommendation
Correct before use; retain the amount-breakdown composition only.

## 06-checkout-error-loading-state-fr.png

Folder: 06-checkout  
Screen purpose: Recover from a checkout-load failure.  
Probable native route: CheckoutError  
Language: French  
Screen type: Error state

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
An unavailable checkout state with retry and cart-return actions.

### Visible UI structure
- Checkout progress header
- Connection-error illustration and explanatory copy
- Retry CTA, return-to-cart secondary action and security note

### Brand validation
Brand palette and icon/illustration treatment are coherent; error contrast remains readable.

### UX validation
It clearly preserves the cart and offers a recovery path. Retry needs a loading/disabled state and the back arrow should not accidentally create duplicate sessions.

### Native implementation usability
Directly implementable as a status screen with network retry and retained local checkout draft.

### Reusable components identified
- CheckoutProgress
- ErrorState
- PrimaryButton
- SecondaryButton
- NetworkStatusIllustration

### Dynamic backend data required
- Checkout-session recovery state
- Cart snapshot availability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Native state | Retry busy/disabled feedback and offline detection are absent. | Add request state, timeout and accessibility announcement. |

### Canonical recommendation
Use directly after minor interaction-state additions.

## 06-checkout-skeleton-loading-state.png

Folder: 06-checkout  
Screen purpose: Skeleton while checkout content loads.  
Probable native route: CheckoutLoading  
Language: Neutral  
Screen type: Loading state

### Status
REFERENCE_ONLY

### Confidence
High

### What the screen represents
A visual loading placeholder for address, delivery, payment and cart modules.

### Visible UI structure
- Header/logo/cart count
- Four-step placeholder, module skeletons and loading CTA

### Brand validation
Neutral beige skeletons and brand chrome are consistent, though a branded full-page skeleton is more decorative than necessary.

### UX validation
Skeleton shapes communicate loading but must not look tappable. It needs a timed fallback/error state and accessible loading announcement.

### Native implementation usability
Useful as component guidance, not as a fixed route or poster-length skeleton.

### Reusable components identified
- CheckoutSkeleton
- SkeletonCard
- SkeletonLine
- LoadingAnnouncer

### Dynamic backend data required
- Checkout loading status

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Loading UX | No timeout, retry handoff or screen-reader loading semantics are shown. | Specify transition to error and announce loading. |

### Canonical recommendation
Keep as component reference only.

## 06-checkout-summary-4step-ar.png

Folder: 06-checkout  
Screen purpose: Arabic review of address, delivery, payment and basket before payment.  
Probable native route: CheckoutReview  
Language: Arabic  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A four-step RTL checkout review at delivery step with item, seller and total details.

### Visible UI structure
- RTL back/cart/logo header and four-step tracker
- Editable address, delivery and payment cards
- Seller-tagged product list, totals and continue CTA

### Brand validation
Core palette and logo are aligned; MAD is used and Arabic layout generally reads RTL.

### UX validation
The details are dense and a static delivery window is presented as fact. Edit actions need a defined RTL touch order, and data must remain legible with longer seller/product names.

### Native implementation usability
Implementable with collapsible summary cards and a virtualized item list, but not as fixed-height content.

### Reusable components identified
- CheckoutProgress
- EditableSummaryCard
- CheckoutItemRow
- SellerBadge
- PriceSummary
- PrimaryButton

### Dynamic backend data required
- Address, delivery option/window, payment choice
- Seller-grouped items, quantities, discounts and totals

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Dynamic content | Fixed delivery date/window and seller presentation will be stale or overflow. | Drive from fulfillment data and support wrapping/expansion. |
| MAJOR | Flow consistency | Four steps conflict with the three-step checkout review presentation. | Adopt one checkout step model across all review flows. |

### Canonical recommendation
Correct before use; combine its RTL summary pattern with the corrected French review flow.

## 06-checkout-summary-4step-overview-fr.png

Folder: 06-checkout  
Screen purpose: French checkout review at delivery step.  
Probable native route: CheckoutReview  
Language: French  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A French four-step review of selected delivery address, delivery, COD payment, seller items and total.

### Visible UI structure
- Back/cart/logo header and step tracker
- Three editable checkout summaries
- Seller-grouped cart rows, price summary and CTA

### Brand validation
Consistent cream, navy, orange, white cards and MAD amounts; official logo is preserved.

### UX validation
The dense content is coherent but the hard-coded Mer. 28 Mai / Ven. 30 Mai delivery promise must be dynamic. This variation also conflicts with three-step review screens.

### Native implementation usability
Suitable after resolving the step model and making the body a scrollable dynamic seller-group list.

### Reusable components identified
- CheckoutProgress
- EditableSummaryCard
- SellerGroup
- CheckoutItemRow
- PriceSummary
- PrimaryButton

### Dynamic backend data required
- Address, delivery option/window, payment selection
- Items grouped by seller, quantity, pricing, discount and shipping

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Dynamic data | A calendar date/window is hard-coded. | Generate it from chosen service level and locale. |
| MAJOR | Flow consistency | Four steps conflict with the three-step checkout review presentation. | Select and document one canonical checkout state machine. |

### Canonical recommendation
Correct before use; retain its compact seller grouping.

## 06-checkout-summary-4step-overview-v2-fr.png

Folder: 06-checkout  
Screen purpose: Duplicate French four-step checkout overview.  
Probable native route: CheckoutReview  
Language: French  
Screen type: Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Byte-identical duplicate of 06-checkout-summary-4step-overview-fr.png.

### Visible UI structure
- Same summary cards, seller items, totals and CTA

### Brand validation
Identical to canonical source.

### UX validation
Identical to canonical source.

### Native implementation usability
No additional implementation value.

### Reusable components identified
- CheckoutProgress
- SellerGroup
- PriceSummary

### Dynamic backend data required
- Same as canonical French checkout overview

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Version control | Exact duplicate of non-v2 file. | Keep non-v2 file as the review reference. |

### Canonical recommendation
Keep only as an alternative; use 06-checkout-summary-4step-overview-fr.png after correction.

## 06-checkout-summary-4step-v2-ar.png

Folder: 06-checkout  
Screen purpose: Duplicate Arabic four-step checkout overview.  
Probable native route: CheckoutReview  
Language: Arabic  
Screen type: Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Byte-identical duplicate of 06-checkout-summary-4step-ar.png.

### Visible UI structure
- Same RTL summary cards, items, totals and CTA

### Brand validation
Identical to canonical Arabic source.

### UX validation
Identical to canonical Arabic source.

### Native implementation usability
No additional implementation value.

### Reusable components identified
- CheckoutProgress
- EditableSummaryCard
- CheckoutItemRow

### Dynamic backend data required
- Same as canonical Arabic checkout overview

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Version control | Exact duplicate of 06-checkout-summary-4step-ar.png. | Keep non-v2 file as source. |

### Canonical recommendation
Keep only as an alternative; use 06-checkout-summary-4step-ar.png after correction.

## 06-choose-address-saved-list-ar.png

Folder: 06-checkout  
Screen purpose: Select a saved delivery address in Arabic.  
Probable native route: CheckoutAddressSelection  
Language: Arabic  
Screen type: Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
RTL saved-address selection with default status, select/edit actions and add-address CTA.

### Visible UI structure
- RTL cart/logo/back header
- Two address cards with radios, default badge, edit/select buttons
- Add-address CTA and privacy note

### Brand validation
The Arabic screen retains official branding, correct +212, Moroccan city data and consistent orange selection treatment.

### UX validation
Radio/card selection is understandable. Selection controls must expose state accessibly and cards must reflow for long addresses.

### Native implementation usability
Directly implementable as a radio list with navigation to edit/add flows.

### Reusable components identified
- CheckoutHeader
- AddressSelectionCard
- RadioControl
- SecondaryButton
- PrimaryButton

### Dynamic backend data required
- Saved addresses, default flag and delivery-zone eligibility

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Accessibility | Selection is expressed by both radio and CTA, but semantic selected state is not specified. | Use one accessible radio-group model and announce selected address. |

### Canonical recommendation
Use directly after minor accessibility behavior is specified; Arabic saved-address reference.

## 06-choose-address-saved-list-fr.png

Folder: 06-checkout  
Screen purpose: Select a saved delivery address in French.  
Probable native route: CheckoutAddressSelection  
Language: French  
Screen type: Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
French saved-address selection with default badge, edit/select controls and add-address CTA.

### Visible UI structure
- Back/cart/logo header
- Two radio address cards with actions
- Add-address CTA and data-security note

### Brand validation
Uses correct logo, Mayush palette, +212 and believable Casablanca address data.

### UX validation
Clear choice and recovery paths. Long addresses and an already-selected default must not create ambiguous Sélectionner buttons.

### Native implementation usability
Strong reusable list-screen reference with responsive card height.

### Reusable components identified
- CheckoutHeader
- AddressSelectionCard
- RadioControl
- PrimaryButton

### Dynamic backend data required
- Address list, default state and serviceability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Interaction clarity | Selected/default state and Sélectionner CTA can overlap conceptually. | Change the selected card CTA to Sélectionnée or disable it. |

### Canonical recommendation
Use directly after selected-state clarification; canonical French saved-address list.

## 06-choose-address-saved-list-v2-ar.png

Folder: 06-checkout  
Screen purpose: Duplicate Arabic saved-address selection.  
Probable native route: CheckoutAddressSelection  
Language: Arabic  
Screen type: Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Byte-identical duplicate of 06-choose-address-saved-list-ar.png.

### Visible UI structure
- Same address radio cards and add-address CTA

### Brand validation
Identical to canonical source.

### UX validation
Identical to canonical source.

### Native implementation usability
No additional implementation value.

### Reusable components identified
- AddressSelectionCard
- RadioControl
- PrimaryButton

### Dynamic backend data required
- Same as canonical Arabic address selection

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Version control | Exact duplicate of non-v2 Arabic file. | Keep non-v2 version as canonical. |

### Canonical recommendation
Keep only as an alternative; use 06-choose-address-saved-list-ar.png.

## 06-choose-address-saved-list-v2-fr.png

Folder: 06-checkout  
Screen purpose: Duplicate French saved-address selection.  
Probable native route: CheckoutAddressSelection  
Language: French  
Screen type: Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Byte-identical duplicate of 06-choose-address-saved-list-fr.png.

### Visible UI structure
- Same address cards, actions and add-address CTA

### Brand validation
Identical to canonical source.

### UX validation
Identical to canonical source.

### Native implementation usability
No additional implementation value.

### Reusable components identified
- AddressSelectionCard
- RadioControl
- PrimaryButton

### Dynamic backend data required
- Same as canonical French address selection

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Version control | Exact duplicate of non-v2 French file. | Keep non-v2 version as canonical. |

### Canonical recommendation
Keep only as an alternative; use 06-choose-address-saved-list-fr.png.

## 06-choose-delivery-standard-express-relay-ar.png

Folder: 06-checkout  
Screen purpose: Choose a delivery method in Arabic.  
Probable native route: CheckoutDeliverySelection  
Language: Arabic  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An RTL delivery selection screen with standard, express and relay options.

### Visible UI structure
- Logo/back header and selected-address card
- Three delivery radio cards, preparation notice and payment CTA

### Brand validation
The visual system is largely coherent and prices use MAD. The top-left back arrow is not mirrored for RTL, and address phone data omits +212.

### UX validation
Options are understandable but their 29/49/19 MAD pricing conflicts with the French equivalent’s 20/40/15 MAD without a stated cause.

### Native implementation usability
Implementable as a radio list, but delivery pricing/availability and RTL navigation need data and design alignment.

### Reusable components identified
- CheckoutHeader
- AddressSummaryCard
- DeliveryOptionCard
- RadioControl
- NoticeCard
- PrimaryButton

### Dynamic backend data required
- Selected address
- Available services, price, ETA and relay locations

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | RTL | Back arrow remains on visual left rather than RTL leading edge. | Mirror header navigation and test all icons/actions in Arabic. |
| MAJOR | Pricing consistency | Equivalent French and Arabic screens show different service prices. | Drive prices from one quote and explain any address difference. |
| MINOR | Localisation | Phone number is shown without +212. | Display country context as in address forms. |

### Canonical recommendation
Correct before use; use French structure only after shared data rules are applied.

## 06-choose-delivery-standard-express-relay-fr.png

Folder: 06-checkout  
Screen purpose: Choose a delivery method in French.  
Probable native route: CheckoutDeliverySelection  
Language: French  
Screen type: Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A French delivery-option selection after address choice.

### Visible UI structure
- Back/cart/logo header and selected address
- Standard, express and relay radio cards
- Preparation notice, continue CTA and trust footer

### Brand validation
Strong use of the approved colors, card radius, icon family and Moroccan address context; all values use MAD.

### UX validation
The selected state and options are clear. Relay pickup requires a follow-up location selector, and pricing must be derived from a quote rather than encoded here.

### Native implementation usability
Good reusable radio-card list once dynamic delivery quotes are connected.

### Reusable components identified
- CheckoutHeader
- AddressSummaryCard
- DeliveryOptionCard
- RadioControl
- PrimaryButton

### Dynamic backend data required
- Address
- Available delivery methods, prices, ETAs and relay locations

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Flow detail | Point relais has no visible pickup-point selection handoff. | Add a required follow-up selector before continuing. |

### Canonical recommendation
Use directly after minor relay-flow clarification; canonical French delivery selection.

## 06-choose-delivery-standard-express-relay-v2-fr.png

Folder: 06-checkout  
Screen purpose: Duplicate French delivery-method selection.  
Probable native route: CheckoutDeliverySelection  
Language: French  
Screen type: Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Byte-identical duplicate of 06-choose-delivery-standard-express-relay-fr.png.

### Visible UI structure
- Same address card, option cards and CTA

### Brand validation
Identical to canonical source.

### UX validation
Identical to canonical source.

### Native implementation usability
No additional implementation value.

### Reusable components identified
- DeliveryOptionCard
- RadioControl
- PrimaryButton

### Dynamic backend data required
- Same as canonical delivery selection

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Version control | Exact duplicate of non-v2 French file. | Keep non-v2 version as canonical. |

### Canonical recommendation
Keep only as an alternative; use 06-choose-delivery-standard-express-relay-fr.png.

## 06-choose-payment-cmi-cod-wallet-ar.png

Folder: 06-checkout  
Screen purpose: Choose payment method in Arabic.  
Probable native route: CheckoutPaymentSelection  
Language: Arabic  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
Medium

### What the screen represents
An RTL selection between CMI online payment, cash on delivery and a Mayush wallet.

### Visible UI structure
- Logo heading and order total
- Three payment radio cards, support panel and continue CTA

### Brand validation
Palette and logo are consistent; MAD remains readable. CMI is a named external payment provider and the Mayush wallet is an unsupported product assertion until confirmed.

### UX validation
Choice is clear but there is no conventional back/cart navigation. Payment availability and displayed total must be derived from the active checkout, not a static scenario.

### Native implementation usability
Reusable payment-radio screen, subject to confirmed payment methods and gateway flow.

### Reusable components identified
- PaymentMethodCard
- RadioControl
- AmountHeader
- SupportCard
- PrimaryButton

### Dynamic backend data required
- Checkout total
- Eligible payment methods
- Wallet balance and COD eligibility

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Product decision | Wallet and COD are shown as universal payment methods. | Confirm availability and render only eligible methods. |
| MAJOR | Navigation | No visible return action or cart context. | Add an RTL-safe back/navigation affordance. |

### Canonical recommendation
Correct before use; keep its RTL payment-card structure.

## 06-choose-payment-cmi-cod-wallet-fr.png

Folder: 06-checkout  
Screen purpose: Choose payment method in French.  
Probable native route: CheckoutPaymentSelection  
Language: French  
Screen type: Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
Medium

### What the screen represents
A French selection between CMI payment, cash on delivery and Mayush wallet.

### Visible UI structure
- Back/logo/security header
- Order total, three payment radio cards
- Help card and continue CTA

### Brand validation
Logo, MAD amount, orange radio accent and illustrated payment icons align with the system.

### UX validation
Good hierarchy, but CMI, COD and wallet eligibility are dynamic product decisions. Cards must show unavailable reasons and preserve selection across refreshes.

### Native implementation usability
Implementable as a standard radio-group screen with external-gateway handoff.

### Reusable components identified
- PaymentMethodCard
- RadioControl
- AmountHeader
- HelpCard
- PrimaryButton

### Dynamic backend data required
- Checkout total
- Eligible payment methods
- Wallet balance and COD eligibility

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Product clarity | Method availability is portrayed as fixed. | Render eligibility/fees dynamically and explain unavailable methods. |

### Canonical recommendation
Use directly after payment-method policy is confirmed; canonical French payment selector.

## 06-choose-payment-cmi-cod-wallet-v2-fr.png

Folder: 06-checkout  
Screen purpose: Duplicate French payment-method selector.  
Probable native route: CheckoutPaymentSelection  
Language: French  
Screen type: Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Byte-identical duplicate of 06-choose-payment-cmi-cod-wallet-fr.png.

### Visible UI structure
- Same payment cards, help card and continue CTA

### Brand validation
Identical to canonical source.

### UX validation
Identical to canonical source.

### Native implementation usability
No additional implementation value.

### Reusable components identified
- PaymentMethodCard
- RadioControl
- PrimaryButton

### Dynamic backend data required
- Same as canonical payment selector

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Version control | Exact duplicate of non-v2 French file. | Keep non-v2 version as canonical. |

### Canonical recommendation
Keep only as an alternative; use 06-choose-payment-cmi-cod-wallet-fr.png.

## 06-city-selector-list-fr.png

Folder: 06-checkout  
Screen purpose: Select a city before calculating delivery.  
Probable native route: CitySelector  
Language: French  
Screen type: Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A searchable list of Moroccan cities, with recent chips and availability status.

### Visible UI structure
- Back/logo header
- Search field and recent-city chips
- Radio list for Casablanca, Rabat, Marrakech, Tanger, Agadir, Fès, Mohammedia and Témara

### Brand validation
The logo, cream surface, orange selection state and navy city icons are aligned; locations are appropriate for Morocco.

### UX validation
Search and radio selection are clear. “Livraison disponible aujourd’hui” is a volatile promise and should not be static across every city.

### Native implementation usability
Directly implementable as a searchable, virtualized list or bottom sheet.

### Reusable components identified
- AppHeader
- SearchInput
- RecentSearchChip
- SelectableListRow
- RadioControl

### Dynamic backend data required
- Supported cities
- City availability/SLA
- User’s recent cities

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Dynamic data | Same-day availability is shown as a fixed fact for all cities. | Return city serviceability and cutoff-driven ETA dynamically. |

### Canonical recommendation
Use directly after dynamic delivery-status rules are specified.

## 06-delivery-by-vendor-multi-seller-fr.png

Folder: 06-checkout  
Screen purpose: Explain split delivery by seller.  
Probable native route: CheckoutSellerDelivery  
Language: French  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A seller-grouped delivery screen explaining that an order may arrive in separate packages.

### Visible UI structure
- Back/logo header and abbreviated three-step tracker
- Information notice
- Two seller package cards with items, ETA, fee and total delivery summary
- Continue CTA

### Brand validation
Visual language and MAD use are cohesive. Seller and item information uses clean reusable cards.

### UX validation
The split-package concept is useful, but the tracker changes to three steps while related routes use four. Delivery selection per seller, free-shipping rules and total must be explicitly derived from current cart data.

### Native implementation usability
Implementable with seller groups and expandable package cards; list content must remain dynamic and scrollable.

### Reusable components identified
- CheckoutProgress
- SellerDeliveryGroup
- CheckoutItemRow
- DeliveryFeeRow
- DeliverySummary
- PrimaryButton

### Dynamic backend data required
- Seller groups, packages and items
- Service per seller, ETA, fee and total shipping

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Flow consistency | Three-step tracker conflicts with the four-step checkout model elsewhere. | Choose one documented checkout state model. |
| MAJOR | Dynamic pricing | Seller fees and delivery estimates are displayed as fixed content. | Quote by seller/address/cart and handle unavailable seller service. |

### Canonical recommendation
Correct before use; retain the seller-grouped package concept.

## 06-delivery-unavailable-address-error-fr.png

Folder: 06-checkout  
Screen purpose: Explain that cart items cannot be delivered to chosen address.  
Probable native route: CheckoutDeliveryUnavailable  
Language: French  
Screen type: Error state

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A delivery serviceability failure with the selected address, affected products, remove actions and support handoff.

### Visible UI structure
- Back/logo header
- Address summary
- Serviceability alert
- Affected-item list, remove CTA and support card

### Brand validation
Semantic orange alert, white cards, icon family and MAD product prices are consistent.

### UX validation
Cause, affected items and next actions are clear. Removing multiple items is destructive and needs individual review/undo rather than a broad irreversible action.

### Native implementation usability
Good error-state pattern using normal item rows, confirmation dialog and optimistic/update handling.

### Reusable components identified
- AddressSummaryCard
- ErrorNotice
- AffectedItemRow
- DestructiveActionButton
- SupportCard

### Dynamic backend data required
- Address serviceability
- Affected cart item IDs and reasons
- Remove/update result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Destructive UX | Single CTA removes all affected items without visible confirmation or undo. | Confirm affected selection and provide undo/cart summary. |

### Canonical recommendation
Use directly after safe multi-item removal behavior is defined.

## 06-delivery-zone-selector-fr.png

Folder: 06-checkout  
Screen purpose: Choose a Casablanca delivery zone.  
Probable native route: DeliveryZoneSelector  
Language: French  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
Medium

### What the screen represents
A city-aware list of delivery zones and indicative starting prices.

### Visible UI structure
- Back/logo header
- Selected-city selector and zone search
- Radio rows for Racine, Maârif, Ain Diab, Bourgogne, Sidi Maârouf and Californie
- Continue CTA

### Brand validation
Visual components, Moroccan city context and MAD pricing align with the foundation direction.

### UX validation
Selecting a zone with only “Dès 20 MAD” does not give a checkout-usable quote. The large city selector and search-list navigation could be a native bottom sheet rather than another full screen.

### Native implementation usability
Reusable selector is feasible, but price/ETA should update only after the actual cart and address are known.

### Reusable components identified
- AppHeader
- CitySelectRow
- SearchInput
- DeliveryZoneRow
- RadioControl
- PrimaryButton

### Dynamic backend data required
- City
- Eligible zones
- Estimated starting price / final delivery quote

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Quote accuracy | “Dès” prices can be mistaken for final checkout fees. | Display final quoted price/ETA after cart and address validation; label estimates clearly. |
| MINOR | Navigation | Full-screen selector is heavy for a dependent field. | Evaluate a searchable bottom sheet with retained form context. |

### Canonical recommendation
Correct before use; keep as a component/interaction reference.

## 06-edit-address-form-fr.png

Folder: 06-checkout  
Screen purpose: Edit an existing Moroccan delivery address.  
Probable native route: CheckoutAddressEdit  
Language: French  
Screen type: Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A prefilled address form with default-address toggle and delete-address action.

### Visible UI structure
- Back/logo header
- Prefilled contact/address fields
- Address-label chips, default switch, save CTA and delete action

### Brand validation
Official logo, cream/white/nave/orange system, +212 and Casablanca information are consistent.

### UX validation
The edit/save flow is clear. Delete is visually present in the same scroll context and needs confirmation, blocking when it is the only usable delivery address and safe return behavior.

### Native implementation usability
Reusable address form with a destructive secondary action and server validation.

### Reusable components identified
- AddressForm
- CountryPhoneInput
- SegmentedChoice
- SwitchRow
- PrimaryButton
- DestructiveTextButton

### Dynamic backend data required
- Saved address and default status
- Cities/zones
- Update/delete eligibility and result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Destructive UX | Delete-address confirmation and fallback when it is default are absent. | Require confirmation and explain selection of a replacement/default address. |

### Canonical recommendation
Use directly after delete behavior is specified.

## 06-no-address-saved-empty-state-fr.png

Folder: 06-checkout  
Screen purpose: Empty address state that leads to address creation.  
Probable native route: CheckoutAddressEmpty  
Language: French  
Screen type: Empty state

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
An empty-state illustration explaining that an address is needed to calculate delivery options.

### Visible UI structure
- Back/cart/logo header
- Location/map illustration
- Explanation, add-address CTA and privacy note

### Brand validation
The warm illustration, logo, orange CTA, icon and ample whitespace fit the Mayush foundation direction.

### UX validation
Clear primary action with useful explanation. It needs a progress/context label so users understand this belongs to checkout, not account settings.

### Native implementation usability
Directly implementable as a standard empty state using shared header and button components.

### Reusable components identified
- CheckoutHeader
- EmptyStateIllustration
- EmptyState
- PrimaryButton

### Dynamic backend data required
- Whether saved/eligible address exists

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Context | No explicit checkout progress/status is visible. | Add a concise checkout step or return label. |

### Canonical recommendation
Use directly after minor context addition; canonical no-address state.

## 06-no-address-saved-empty-state-v2-fr.png

Folder: 06-checkout  
Screen purpose: Duplicate no-address empty state.  
Probable native route: CheckoutAddressEmpty  
Language: French  
Screen type: Empty state

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Byte-identical duplicate of 06-no-address-saved-empty-state-fr.png.

### Visible UI structure
- Same checkout header, illustration and add-address CTA

### Brand validation
Identical to canonical source.

### UX validation
Identical to canonical source.

### Native implementation usability
No additional implementation value.

### Reusable components identified
- EmptyState
- PrimaryButton

### Dynamic backend data required
- Same as canonical no-address state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Version control | Exact duplicate of non-v2 French file. | Keep non-v2 version as canonical. |

### Canonical recommendation
Keep only as an alternative; use 06-no-address-saved-empty-state-fr.png.

## 06-order-already-in-progress-duplicate-check-fr.png

Folder: 06-checkout  
Screen purpose: Prevent duplicate order creation while an order is processing.  
Probable native route: CheckoutDuplicateOrderGuard  
Language: French  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A duplicate-order guard showing an existing order, its payment mode and actions to inspect or recheck it.

### Visible UI structure
- Four-step checkout header
- Processing illustration
- Existing-order card
- View/recheck/support actions and instruction not to leave page

### Brand validation
Brand treatment is coherent and MAD is used correctly.

### UX validation
The intent is valuable, but “Ne quittez pas cette page” conflicts with three active exit/navigation actions. Order date text says Mercredi 28 mai 2026, which is not the weekday for that date and must never be fixed.

### Native implementation usability
Feasible as an idempotency guard, but needs a defined polling/session-recovery model rather than a static warning.

### Reusable components identified
- CheckoutProgress
- ProcessingIllustration
- ExistingOrderCard
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Existing pending order, payment state, total, created timestamp
- Idempotency/session status

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | UX contradiction | Screen says not to leave while offering view, refresh, support and back actions. | State the safe action precisely and allow recoverable navigation. |
| MAJOR | Dynamic date | Fixed weekday/date is incorrect and stale. | Format real timestamp from locale-aware data. |

### Canonical recommendation
Correct before use; preserve the duplicate-payment protection concept.

## 06-order-needs-update-price-stock-changes-fr.png

Folder: 06-checkout  
Screen purpose: Warn that price, stock, coupon and delivery values changed.  
Probable native route: CheckoutRevalidationRequired  
Language: French  
Screen type: Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A checkout revalidation state listing changed price, stock, promotion and shipping information.

### Visible UI structure
- Back/logo/status header
- Cause explanation and four changed-value rows
- Refresh-order CTA and return-to-cart action

### Brand validation
Strong visual hierarchy, clear price-change formatting and compatible icon style.

### UX validation
This correctly surfaces changes, but the copy says “Laravel a révalidé votre commande,” exposing an implementation technology to buyers. Refresh behavior must show what changed and require explicit acceptance if total increases.

### Native implementation usability
Implementable with a server revalidation result and dynamic diff rows.

### Reusable components identified
- SystemNotice
- PriceDiffRow
- StockDiffRow
- PromotionDiffRow
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Revalidation timestamp/result
- Previous/current item price, stock, promotion, shipping and recalculated total

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Buyer copy | Laravel is an internal implementation detail, not buyer-facing evidence. | Replace with neutral “Nous avons détecté des modifications.” |
| MAJOR | Consent | Updated total is not shown before the refresh action. | Display old/new order total and require confirmation if price rises. |

### Canonical recommendation
Correct before use; this is the preferred price/stock-change concept.

## 06-order-processing-loading-state-fr.png

Folder: 06-checkout  
Screen purpose: Order creation in progress.  
Probable native route: CheckoutOrderProcessing  
Language: French  
Screen type: Loading state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A full-screen processing state intended to stop duplicate payment/order submission.

### Visible UI structure
- Large official logo
- Progress illustration, no-close warning and security notice
- Order-reference panel

### Brand validation
Premium illustration and palette are on brand, though the oversized logo reduces functional space.

### UX validation
No-close guidance is weak on mobile; app interruption is unavoidable. Placeholder reference #FR-XXXXXX must never reach an implementation reference.

### Native implementation usability
Needs a resilient native processing state with idempotency, background recovery, timeout and a safe route to order status.

### Reusable components identified
- ProcessingState
- ProgressIndicator
- OrderReferenceCard
- RecoveryLink

### Dynamic backend data required
- Attempt/order reference
- Processing/payment status and recovery token

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Placeholder data | #FR-XXXXXX is an unresolved placeholder. | Use no reference until issued, or show real dynamic ID. |
| MAJOR | Native resilience | “Ne fermez pas l’application” is not a recovery strategy. | Support background/return recovery and a visible timeout/help path. |

### Canonical recommendation
Correct before use; retain only its processing-state visual language.

## 06-order-review-confirm-ar.png

Folder: 06-checkout  
Screen purpose: Review and confirm an Arabic COD order.  
Probable native route: CheckoutReview  
Language: Arabic  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An RTL order review showing two items, address, shipping, COD method, coupon row, total and consent.

### Visible UI structure
- RTL back/logo header
- Item rows with quantity controls
- Address, shipping, payment and coupon cards
- Summary, consent row and confirm-order CTA

### Brand validation
Official logo, cream/white cards, Arabic labels and MAD values are broadly aligned.

### UX validation
The top back arrow remains visually left rather than RTL leading right. Product prices are duplicated in two colors without a clear semantic role, and the CTA proceeds to confirm while agreement should be a discrete legal state.

### Native implementation usability
Feasible with RTL cards and dynamic totals, but needs explicit item-price semantics, consent gating and locale-safe number layout.

### Reusable components identified
- CheckoutHeader
- CheckoutItemRow
- EditableSummaryCard
- CouponRow
- PriceSummary
- ConsentCheckbox
- PrimaryButton

### Dynamic backend data required
- Items, quantities, options and prices
- Address, shipping quote, payment method, coupon/discount and total
- Terms version/consent

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | RTL | Header back arrow is not mirrored to RTL leading edge. | Place back navigation on the right and mirror icon direction. |
| MAJOR | Price clarity | Navy and orange values repeat each item price without explanation. | Display one price or label old/new/discount values explicitly. |
| MAJOR | Consent | Confirm must be unavailable until required consent is recorded. | Bind CTA state to a native accessible checkbox. |

### Canonical recommendation
Correct before use; use its Arabic content structure only.

## 06-order-review-confirm-multi-vendor-fr.png

Folder: 06-checkout  
Screen purpose: Review and pay a multi-vendor French order.  
Probable native route: CheckoutReview  
Language: French  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A seller-grouped confirmation screen with address, delivery, Visa, coupon, total and legal-consent rows.

### Visible UI structure
- Back/logo abbreviated checkout tracker
- Collapsible seller/product card
- Address, delivery, payment and coupon rows
- Price summary, unchecked consent and pay CTA

### Brand validation
Cards, seller badges, navy/orange hierarchy and MAD are visually coherent.

### UX validation
The displayed subtotal is 8 440 MAD and coupon is -800 MAD, but total remains 8 440 MAD instead of 7 640 MAD. The terms checkbox is visibly unchecked while Pay 8 440 MAD is enabled. Trust claims “Satisfait ou remboursé 14 jours” and support 7/7 need approved policy.

### Native implementation usability
The pattern is implementable, but price calculation and legal gating must come from an atomic server checkout quote.

### Reusable components identified
- CheckoutProgress
- SellerGroup
- EditableSummaryRow
- CouponRow
- PriceSummary
- ConsentCheckbox
- PrimaryButton

### Dynamic backend data required
- Seller items, address, shipping, payment method
- Coupon validation, subtotal, discount, shipping, tax and final total
- Terms version and acceptance

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Payment calculation | 8 440 - 800 is shown but the total remains 8 440 MAD. | Render final total from one authoritative quote and test arithmetic. |
| CRITICAL | Legal consent | Unchecked terms checkbox coexists with enabled pay CTA. | Disable payment until required consent is explicitly accepted. |
| MAJOR | Policy claim | 14-day refund and 7/7 support appear as universal promises. | Confirm policy or replace with approved, conditional copy. |
| MAJOR | Flow consistency | Three-step tracker conflicts with four-step summary screens. | Standardize checkout state labels and sequence. |

### Canonical recommendation
Correct before use; preferred French multi-vendor review only after total/consent fixes.

## 06-order-review-confirm-multi-vendor-v2-fr.png

Folder: 06-checkout  
Screen purpose: Duplicate French multi-vendor review.  
Probable native route: CheckoutReview  
Language: French  
Screen type: Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Byte-identical duplicate of 06-order-review-confirm-multi-vendor-fr.png.

### Visible UI structure
- Same seller group, summaries, total and pay CTA

### Brand validation
Identical to canonical source.

### UX validation
Identical to canonical source, including the calculation and consent defects.

### Native implementation usability
No additional implementation value.

### Reusable components identified
- SellerGroup
- PriceSummary
- ConsentCheckbox

### Dynamic backend data required
- Same as canonical multi-vendor review

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Version control | Exact duplicate retains the canonical file’s wrong total and enabled payment without consent. | Do not use; correct the non-v2 source. |

### Canonical recommendation
Keep only as an alternative; use corrected 06-order-review-confirm-multi-vendor-fr.png.

## 06-order-thank-you-confirmation-ar.png

Folder: 06-checkout  
Screen purpose: Arabic post-order confirmation.  
Probable native route: OrderConfirmation  
Language: Arabic  
Screen type: Success state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A paid Arabic order-success summary with address, estimated delivery, package count, product total and actions.

### Visible UI structure
- Large logo and thank-you heading
- Order number/payment-status card
- Address/delivery/package details
- Product summary, total, order and continue-shopping CTAs

### Brand validation
Colors, logo and MAD presentation fit the intended system, though logo scale is oversized for a task-focused success screen.

### UX validation
The fixed Thursday 29 May 2025 delivery window is stale. Paid status, delivery promise and multiple parcels require dynamic fulfillment data and should not be assumed from a generic success state.

### Native implementation usability
Implementable as a dynamic confirmation page, with order status loaded from the confirmed order record.

### Reusable components identified
- OrderSuccessHeader
- OrderStatusCard
- DeliverySummaryCard
- OrderItemRow
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Order ID, payment status, address, delivery estimate, packages, items and total

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Dynamic date | 29 May 2025 is static/stale delivery content. | Derive from selected service and current order fulfillment. |
| MAJOR | Information hierarchy | Oversized logo reduces summary space and pushes actionable order information down. | Use compact success header and prioritize order status/action. |

### Canonical recommendation
Correct before use; retain its RTL order-summary pattern.

## 06-order-thank-you-confirmation-summary-fr.png

Folder: 06-checkout  
Screen purpose: French post-payment order confirmation.  
Probable native route: OrderConfirmation  
Language: French  
Screen type: Success state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A French paid-order success screen with item summary, address, estimated delivery, carrier and parcels.

### Visible UI structure
- Decorative success header
- Order number/payment-status card
- Three item rows and total
- Delivery/address/card, parcel/carrier card and two CTAs

### Brand validation
Uses proper logo, premium furniture visual direction, colors, cards and MAD amounts.

### UX validation
Items total 14 270 MAD correctly, but “Mercredi 28 mai 2026” is not the weekday for that date and must not be fixed. Carrier “Mayush Delivery,” 2 parcels, delivery window and paid state all need real fulfillment/payment evidence.

### Native implementation usability
Strong structure for a dynamic success screen; multiple order attributes must be backend-provided and support line wrapping.

### Reusable components identified
- OrderSuccessHeader
- PaymentStatusBadge
- OrderItemRow
- DeliverySummaryCard
- CarrierSummaryCard
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Order ID, payment state, items, total, delivery address/ETA, packages and carrier

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Dynamic date | Displayed weekday/date is wrong and static. | Format actual ETA from locale-aware order data. |
| MAJOR | Product decision | Mayush Delivery/carrier and two-package state are asserted without contextual eligibility. | Render real carrier/package data or use neutral pending language. |

### Canonical recommendation
Correct before use; preferred French confirmation layout after dynamic-data fixes.

## 06-order-thank-you-confirmation-summary-v2-fr.png

Folder: 06-checkout  
Screen purpose: Duplicate French order confirmation.  
Probable native route: OrderConfirmation  
Language: French  
Screen type: Success state

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Byte-identical duplicate of 06-order-thank-you-confirmation-summary-fr.png.

### Visible UI structure
- Same success header, order card, summaries and CTAs

### Brand validation
Identical to canonical source.

### UX validation
Identical to canonical source, including dynamic-date defect.

### Native implementation usability
No additional implementation value.

### Reusable components identified
- OrderSuccessHeader
- OrderItemRow
- DeliverySummaryCard

### Dynamic backend data required
- Same as canonical French confirmation

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Version control | Exact duplicate preserves the static incorrect weekday/date. | Correct the non-v2 source and keep only that source. |

### Canonical recommendation
Keep only as an alternative; use corrected 06-order-thank-you-confirmation-summary-fr.png.

## 06-pay-with-wallet-balance-fr.png

Folder: 06-checkout  
Screen purpose: Pay the checkout total from a Mayush wallet balance.  
Probable native route: WalletPaymentConfirmation  
Language: French  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
Medium

### What the screen represents
A wallet payment confirmation showing balance, order amount and remaining balance.

### Visible UI structure
- Compact brand/security header
- Wallet amount card
- Selected wallet card, security notice and confirm CTA

### Brand validation
Typography, orange accents, navy icons and MAD balance arithmetic are consistent.

### UX validation
1 250 - 650 = 600 MAD is correct. But a Mayush wallet, stored balance, payment authorization and “100% secure” claim are all product/security decisions, and no insufficient-balance, reauthentication or cancellation behavior is shown.

### Native implementation usability
Feasible only if a wallet exists; use a secure confirmation/reauthentication pattern rather than a decorative native screen alone.

### Reusable components identified
- WalletBalanceCard
- PaymentMethodCard
- NoticeCard
- PrimaryButton

### Dynamic backend data required
- Wallet availability and balance
- Checkout total and post-payment balance
- Payment authorization/reauth result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Product decision | Mayush wallet is presented as an available platform feature. | Confirm product scope; otherwise remove from buyer references. |
| MAJOR | Payment security | No insufficient-funds, reauthentication or idempotent confirmation state. | Specify and design these states before native implementation. |

### Canonical recommendation
Correct before use; keep only as a conditional wallet concept.

## 06-payment-cancelled-resume-fr.png

Folder: 06-checkout  
Screen purpose: Resume or change payment after user cancellation.  
Probable native route: PaymentCancelled  
Language: French  
Screen type: Error state

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A cancellation state offering retry, payment-method change or return to cart.

### Visible UI structure
- Brand header and cancellation icon
- Order reference/amount card
- Resume, change-method and return-to-cart actions

### Brand validation
Good Mayush palette, hierarchy, MAD and consistent outlined payment icons.

### UX validation
Clear recovery choices. The backend must confirm cancellation before stating it and reject retry if a simultaneous payment is pending.

### Native implementation usability
Directly implementable with payment-attempt status and idempotency handling.

### Reusable components identified
- PaymentStateHeader
- OrderAmountCard
- PrimaryButton
- SecondaryActionList

### Dynamic backend data required
- Payment-attempt status, order reference and payable total

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Payment state | Copy assumes payment was not made without showing gateway-confirmed status. | Bind wording/actions to the verified attempt state. |

### Canonical recommendation
Use directly after state-confirmation rules are specified.

## 06-payment-confirmation-taking-longer-fr.png

Folder: 06-checkout  
Screen purpose: Handle delayed payment confirmation.  
Probable native route: PaymentConfirmationPending  
Language: French  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A delayed-confirmation screen with a recheck action, order-status action and support.

### Visible UI structure
- Brand header, clock/payment illustration and warning copy
- Order reference/amount card
- Verification notice and three recovery actions

### Brand validation
Brand presentation and MAD are consistent; the warning is visually clear.

### UX validation
It says not to retry immediately but offers “Vérifier à nouveau” without cooldown/polling context. Copy says both confirmation is pending and that order is recorded, which must be precise to gateway state.

### Native implementation usability
Needs polling/backoff, idempotency and recoverable app-resume behavior.

### Reusable components identified
- PaymentPendingState
- OrderAmountCard
- PollingStatus
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Gateway attempt status
- Order state, amount and next permitted retry/recheck time

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Payment logic | Immediate recheck CTA contradicts wait instruction and lacks cooldown. | Show automated polling/countdown and disable duplicate requests. |
| MAJOR | State accuracy | “Commande bien enregistrée” may not be true before gateway confirmation. | Use payment-state-specific, verified wording. |

### Canonical recommendation
Correct before use; retain the delayed-confirmation information architecture.

## 06-payment-confirmed-success-fr.png

Folder: 06-checkout  
Screen purpose: Confirm successful payment and order creation.  
Probable native route: PaymentSuccess  
Language: French  
Screen type: Success state

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A payment success state with amount, order ID, payment reference and navigation actions.

### Visible UI structure
- Brand/success illustration
- Payment amount/order/payment-reference card
- View-order and continue-shopping actions

### Brand validation
The orange/navy visual language and MAD formatting are consistent; decorative composition remains premium.

### UX validation
Clear destination actions. The success state must be displayed only after verified gateway/webhook confirmation and payment reference must be safely copyable/accessibly labeled.

### Native implementation usability
Directly implementable from confirmed payment/order data, with a guard against returning to pending/duplicate flows.

### Reusable components identified
- PaymentSuccessState
- PaymentReferenceCard
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Verified paid status, amount, order ID and payment reference

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Payment state | Static success artwork does not establish gateway verification source. | Gate this route on authoritative confirmed status and include refresh recovery. |

### Canonical recommendation
Use directly after confirmed-payment routing is defined.

## 06-payment-confirmed-success-v2-fr.png

Folder: 06-checkout  
Screen purpose: Duplicate payment-success state.  
Probable native route: PaymentSuccess  
Language: French  
Screen type: Success state

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Byte-identical duplicate of 06-payment-confirmed-success-fr.png.

### Visible UI structure
- Same success illustration, payment-reference card and destination CTAs

### Brand validation
Identical to canonical source.

### UX validation
Identical to canonical source.

### Native implementation usability
No additional implementation value.

### Reusable components identified
- PaymentSuccessState
- PaymentReferenceCard
- PrimaryButton

### Dynamic backend data required
- Same as canonical payment success

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Version control | Exact duplicate of non-v2 French file. | Keep non-v2 version as canonical. |

### Canonical recommendation
Keep only as an alternative; use 06-payment-confirmed-success-fr.png.

## 06-payment-failed-retry-fr.png

Folder: 06-checkout  
Screen purpose: Recover from a failed payment.  
Probable native route: PaymentFailed  
Language: French  
Screen type: Error state

### Status
NEEDS_REWORK

### Confidence
Medium

### What the screen represents
A payment-failure state with retry, method-change and support actions.

### Visible UI structure
- Large brand header and failed icon
- Order reference/amount card
- Retry, payment-method and support actions

### Brand validation
Error red, navy text, orange action and MAD figure work coherently.

### UX validation
“Vous n’avez pas été débité” is a high-stakes financial assertion that can be false while gateway status is uncertain. It needs a separate pending/unknown settlement state instead of a universal failure claim.

### Native implementation usability
Feasible only with verified gateway outcome, retry limits and idempotency handling.

### Reusable components identified
- PaymentFailureState
- OrderAmountCard
- PrimaryButton
- SecondaryActionList

### Dynamic backend data required
- Gateway attempt status, final/unknown settlement status, order reference and amount

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Financial accuracy | The screen promises no debit without evidence of final gateway settlement. | Show this only for confirmed non-capture; route uncertain outcomes to pending verification. |
| MAJOR | Retry safety | Retry lacks a visible attempt/cooldown/idempotency guard. | Disable duplicate attempts until gateway state is final. |

### Canonical recommendation
Correct before use; retain the recovery-action structure.

## 06-payment-pending-confirmation-fr.png

Folder: 06-checkout  
Screen purpose: Payment accepted but awaiting confirmation.  
Probable native route: PaymentConfirmationPending  
Language: French  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A pending payment confirmation notice asking the buyer not to pay again and offering a status refresh.

### Visible UI structure
- Large logo/clock/status label
- Pending explanation and reference/amount card
- Duplicate-payment notice, refresh/order/support actions

### Brand validation
Brand colors, card system and MAD are coherent.

### UX validation
The no-repeat-payment guidance is good, but manual Actualiser le statut needs throttling and explicit automatic refresh. Copy claims the payment was received before confirmation, which must be accurate to provider status.

### Native implementation usability
Requires resilient polling/webhook-based state, attempt tracking and return-from-gateway recovery.

### Reusable components identified
- PaymentPendingState
- OrderAmountCard
- PollingStatus
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Gateway/attempt status, order state, amount and next refresh policy

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Payment logic | Manual refresh is unrestricted despite warning about duplicate payment/processing. | Use auto-polling/backoff and controlled manual retry. |
| MAJOR | State accuracy | Paiement bien reçu may be stronger than actual pending provider result. | Use provider-confirmed state wording only. |

### Canonical recommendation
Correct before use; combine with a proper pending/polling design.

## 06-payment-step-intro-step3-fr.png

Folder: 06-checkout  
Screen purpose: Informational introduction to payment step 3.  
Probable native route: CheckoutPaymentIntro  
Language: French  
Screen type: Full page

### Status
REFERENCE_ONLY

### Confidence
High

### What the screen represents
A decorative instructional interstitial before choosing a payment method.

### Visible UI structure
- Four-step progress header
- Large payment card illustration
- Three explanatory rows, PCI DSS mark and continue CTA

### Brand validation
The color, icon and component system is broadly aligned. The PCI DSS mark and “100% sécurisé” language are legal/compliance claims, not visual decoration.

### UX validation
This repeats information already present in method selection and adds an unnecessary checkout step. It can be skipped for a faster buyer journey.

### Native implementation usability
Keep as copy/component inspiration only, not a mandatory full-screen route.

### Reusable components identified
- CheckoutProgress
- InformationCard
- IconBulletRow
- PrimaryButton

### Dynamic backend data required
- Confirmed payment-security/compliance claims, if used

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Flow efficiency | Redundant interstitial increases checkout length without a user decision. | Remove it or integrate concise guidance into payment selection. |
| MAJOR | Compliance | PCI DSS/100% secure claim requires verified legal approval. | Use only approved compliance wording and marks. |

### Canonical recommendation
Keep as reference only; do not implement as a required step.

## 06-payment-step-intro-step3-v2-fr.png

Folder: 06-checkout  
Screen purpose: Duplicate payment-step introduction.  
Probable native route: CheckoutPaymentIntro  
Language: French  
Screen type: Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Byte-identical duplicate of 06-payment-step-intro-step3-fr.png.

### Visible UI structure
- Same progress header, explanation and continue CTA

### Brand validation
Identical to canonical source.

### UX validation
Identical to canonical source.

### Native implementation usability
No additional implementation value.

### Reusable components identified
- CheckoutProgress
- InformationCard
- PrimaryButton

### Dynamic backend data required
- Same as canonical payment intro

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Version control | Exact duplicate preserves a non-essential payment interstitial. | Keep non-v2 only as reference. |

### Canonical recommendation
Keep only as an alternative; use 06-payment-step-intro-step3-fr.png as reference only.

## 06-payment-verification-processing-fr.png

Folder: 06-checkout  
Screen purpose: Explain that payment verification is processing.  
Probable native route: PaymentVerificationProcessing  
Language: French  
Screen type: Loading state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A payment-verification wait screen with order reference and amount.

### Visible UI structure
- Large brand header and circular progress illustration
- Verification copy and reference/amount card
- Warning not to retry plus security copy

### Brand validation
Premium visual treatment and MAD values are consistent, although logo scale is excessive for a functional wait state.

### UX validation
It gives no visible timeout, status refresh, support or app-resume recovery. No-retry guidance needs a verified, accessible state machine.

### Native implementation usability
Not suitable as-is; implement as a recoverable status route tied to gateway callbacks/polling.

### Reusable components identified
- PaymentPendingState
- ProgressIndicator
- OrderAmountCard
- TimeoutRecoveryPanel

### Dynamic backend data required
- Attempt state, payment reference, amount, polling/timeout configuration

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Recovery UX | No timeout, support path or return-from-background behavior is shown. | Add automatic polling, timeout escalation and safe order-status route. |
| MINOR | Layout | Oversized logo consumes high-value mobile space. | Use compact payment header. |

### Canonical recommendation
Correct before use; retain the wait-state visual language only.

## 06-saved-payment-cards-visa-mastercard-fr.png

Folder: 06-checkout  
Screen purpose: Select/manage saved Visa and Mastercard payment methods.  
Probable native route: SavedPaymentMethods  
Language: French  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A saved-card list with selection, default state, delete controls and add-payment option.

### Visible UI structure
- Back/logo header
- Visa and Mastercard cards with expiration, radio and delete action
- Add method row, security copy and continue CTA

### Brand validation
Card layout and overall Mayush palette are coherent. Branded network marks must be used under their legal guidelines.

### UX validation
The selected default Visa expires 06/26, which is already expired in the current August 2026 context. Card storage and direct management are product/security decisions; add/delete must involve provider tokenization and confirmation.

### Native implementation usability
Possible only with a PCI-compliant payment provider/tokenized cards and an expired-card state.

### Reusable components identified
- SavedPaymentMethodCard
- RadioControl
- DestructiveIconButton
- AddPaymentMethodRow
- PrimaryButton

### Dynamic backend data required
- Tokenized card metadata, expiry, default/eligibility, add/delete result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Payment validity | Selected default card expires 06/26 and is invalid as of August 2026. | Use valid/dynamic test expiry and block expired-card selection. |
| MAJOR | Security/product | Saved-card management is not guaranteed by the visual system. | Confirm provider tokenization/compliance and required reauthentication. |

### Canonical recommendation
Correct before use; retain list composition only if saved cards are in product scope.

## 06-secure-payment-redirect-fr.png

Folder: 06-checkout  
Screen purpose: Explain external CMI secure-payment redirection.  
Probable native route: PaymentGatewayRedirectIntro  
Language: French  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
Medium

### What the screen represents
A pre-redirect screen naming CMI, reference/amount and a continue/cancel choice.

### Visible UI structure
- Decorative secure-payment header
- Order reference and amount card
- Security illustration and continue/cancel actions

### Brand validation
Brand presentation is polished and MAD amounts are correct. CMI naming and secure wording must be approved by actual integration/legal evidence.

### UX validation
An extra interstitial is acceptable only if external redirect needs consent; otherwise it slows checkout. It needs a precise return/cancellation path and does not prove actual provider destination.

### Native implementation usability
Use a native external-browser/WebView handoff only with verified deep-link callback and provider integration; the visual alone is insufficient.

### Reusable components identified
- PaymentGatewayIntro
- OrderAmountCard
- PrimaryButton
- TextAction

### Dynamic backend data required
- Payment provider eligibility, gateway session/redirect URL, order reference and amount

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Product/integration | CMI payment redirection is asserted without verified provider configuration. | Treat availability and copy as NEEDS PRODUCT DECISION until confirmed. |
| MINOR | Flow efficiency | Intro may duplicate payment-selection explanation. | Keep only if required for a clear external handoff. |

### Canonical recommendation
Correct before use; use only after gateway redirect design is confirmed.

## 06-secure-payment-redirect-loading-fr.png

Folder: 06-checkout  
Screen purpose: Loading state while opening secure payment environment.  
Probable native route: PaymentGatewayRedirectLoading  
Language: French  
Screen type: Loading state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A browser-like secure-payment loading view showing order reference, amount and a secure.mayushdesign.ma/payment URL.

### Visible UI structure
- Browser-style return/overflow header
- Large logo, reference and amount
- Secure URL panel and loading message

### Brand validation
Visual treatment is coherent but presenting a specific production-looking payment URL is unsafe when it is not an approved actual gateway URL.

### UX validation
The browser chrome is ambiguous in a native app, overflow menu has no defined purpose, and there is no timeout/cancel/recovery behavior. A custom host followed by CMI contradicts the prior external-provider story.

### Native implementation usability
Not a direct native reference: gateway handoff requires actual browser/WebView behavior, deep links and provider-approved URL presentation.

### Reusable components identified
- GatewayLoadingState
- BrowserHeader
- OrderAmountCard
- TimeoutRecoveryPanel

### Dynamic backend data required
- Provider redirect session, approved host/URL, order reference, amount and callback state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Security/integration | secure.mayushdesign.ma/payment is invented-looking and conflicts with a CMI redirection. | Never mock a production payment host; display only provider-approved destination. |
| MAJOR | Native UX | Faux browser menu/return behavior and no recovery create unclear interaction. | Use defined external browser/WebView contract with cancel/timeout/callback handling. |

### Canonical recommendation
Correct before use; do not use as a direct payment implementation reference.

## 06-secure-payment-redirect-v2-fr.png

Folder: 06-checkout  
Screen purpose: Duplicate CMI redirect introduction.  
Probable native route: PaymentGatewayRedirectIntro  
Language: French  
Screen type: Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Byte-identical duplicate of 06-secure-payment-redirect-fr.png.

### Visible UI structure
- Same provider explanation, reference/amount card and actions

### Brand validation
Identical to canonical source.

### UX validation
Identical to canonical source.

### Native implementation usability
No additional implementation value.

### Reusable components identified
- PaymentGatewayIntro
- OrderAmountCard
- PrimaryButton

### Dynamic backend data required
- Same as canonical secure redirect introduction

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Version control | Exact duplicate retains unconfirmed external-payment integration assumption. | Correct non-v2 source and keep only it. |

### Canonical recommendation
Keep only as an alternative; use corrected 06-secure-payment-redirect-fr.png.

## 06-terms-conditions-confirmation-fr.png

Folder: 06-checkout  
Screen purpose: Final legal/consent confirmation before payment.  
Probable native route: CheckoutTermsConfirmation  
Language: French  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A final confirmation screen covering delivery address, product personalization, shipping, returns and payment authorization.

### Visible UI structure
- Logo/back header and introductory copy
- Five checked-looking acknowledgement rows
- Unchecked global consent checkbox, privacy statement and continue CTA

### Brand validation
Consistent logo, colors, rounded white panel and icons. The visual presentation cannot establish legal validity.

### UX validation
All individual acknowledgement rows appear checked while the required aggregate checkbox is empty and J’accepte et je continue is enabled. “Nous ne partageons jamais vos informations avec des tiers” is an absolute claim likely contradicted by payment/delivery processors and needs legal approval.

### Native implementation usability
Implementable only with versioned legal documents, deep links, required consent gating, audit timestamps and accessible checkbox controls.

### Reusable components identified
- LegalConsentList
- ConsentRow
- ConsentCheckbox
- LegalDocumentLink
- PrimaryButton

### Dynamic backend data required
- Applicable terms/privacy/return policy versions and URLs
- Required consent flags and acceptance timestamp

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Consent | Aggregate required checkbox is empty while primary continue CTA remains enabled. | Disable CTA until explicit consent; record version/time. |
| MAJOR | Legal accuracy | Never share with third parties is an unqualified promise despite payment/delivery processing. | Replace with legal-approved privacy copy and link policy. |
| MAJOR | Interaction clarity | Individual checkmarks look preaccepted rather than reviewable acknowledgements. | Use information rows or separate optional controls clearly. |

### Canonical recommendation
Correct before use; retain as legal-consent architecture only after product/legal review.

## Folder conclusion

Reviewed: 52 screenshots.  
Approved: 0.  
Approved with minor fixes: 13.  
Needs rework: 24.  
Reference only: 2.  
Rejected: 0.  
Duplicate alternatives: 13.

Highest priority: correct payment arithmetic and consent in 06-order-review-confirm-multi-vendor-fr.png; remove buyer-facing Laravel copy; replace placeholder order references; define idempotent pending/failed gateway states; correct Arabic delivery RTL/pricing; and remove invented payment URL/security claims.
