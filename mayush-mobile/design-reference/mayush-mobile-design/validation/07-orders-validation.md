# 07-orders Validation Report

> **Fact-check scope:** Currency and address examples are accepted variations. Do not treat them as validation defects by themselves; [fact-check-correction.md](./fact-check-correction.md) supersedes earlier currency/address severity notes.

Folder: `07-orders/`  
Total screenshots reviewed: 37  
Date validated: 2026-08-02

## Extracted foundation rules applied

- Use the undistorted official `MAYUSH DESIGN` lockup, warm cream page background, white cards, deep navy content, restrained beige secondary surfaces, and the approved Mayush orange.
- Use Playfair Display for elegant display headings and a clean sans-serif for UI/body text; allow Dynamic Type without clipping.
- Preserve the five buyer tabs in this exact order: `Accueil`, `Catégories`, `Favoris`, `Panier`, `Compte`. `Commandes`, `Explorer`, and seller-oriented destinations are not permanent buyer tabs.
- Use one rounded 2 px line-icon family. Minimum native hit area is 44 pt on iOS / 48 dp on Android.
- Use the foundation status meanings: in-progress/preparing orange, shipped blue, delivered/completed/refunded green, cancelled/reviewing red, and pending/neutral gray. Color must be reinforced by an icon or text.
- Use `MAD`, Moroccan addresses, `+212`, locale-aware French number formatting, predictable back behavior, safe-area padding, and bottom list insets.
- Implement long order lists with reusable virtualized cards, dynamic content, explicit loading/error/empty recovery, and accessible labels/selected states.

The `ui-ux-pro-max` audit skill materially influenced the review by adding explicit checks for 44/48 pt touch targets, one-primary-action hierarchy, safe areas, Dynamic Type, screen-reader reading order, disabled/loading feedback, fixed-navigation insets, semantic status color, and React Native list virtualization. Its design-system search helper was unavailable at the installed pointer path, so the Mayush foundation and asset boards remained the controlling visual source of truth.

---

## 07-cancellation-request-registered-fr.png

Folder: `07-orders/`  
Screen purpose: Confirm that an order-cancellation request was submitted  
Probable native route: `Orders/Cancel/RequestStatus`  
Language: FR  
Screen type: Full page / Success state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A post-submission state for order `MAY-2026-001842`, with a pending cancellation, refund-review notice, item summary, and onward actions.

### Visible UI structure
- Centered logo and notification action
- Large confirmation illustration and heading
- Order/status card and explanatory alerts
- Three-line item summary and total
- Primary `Voir mes commandes` and secondary shopping action

### Brand validation
- Logo: Official and proportional, but oversized for an in-flow confirmation
- Colors: Mayush cream, navy, orange, white, and semantic green are present
- Typography: Hierarchy matches the display/body pairing
- Icons: Mostly coherent rounded outlines
- Buttons: Clear primary/secondary hierarchy
- Cards: Consistent white rounded surfaces
- Spacing: Spacious but vertically poster-like
- Shadows: Soft and consistent

### UX validation
- Clear user goal: Understand submission outcome
- Primary action: Clear
- Navigation logic: Onward choices exist, but no back/close route is shown
- Form usability: N/A
- Scroll behavior: Requires vertical scrolling on smaller devices
- Empty/error behavior: N/A
- Accessibility concerns: The green success treatment may announce approval even though the request is only pending; preserve focus on the result heading and support Dynamic Type

### Native implementation usability
Feasible with a scroll view and reusable status/summary cards, but the state model must distinguish `request submitted`, `request approved`, and `refund started`.

### Reusable components identified
- AppHeader
- ResultIllustration
- OrderReferenceCard
- StatusBadge
- InlineNotice
- OrderItemSummary
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Order number and date
- Cancellation-request status
- Refund-review status and decision message
- Product thumbnail, name, and price
- Item count and order total

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | State logic | A green success check dominates a request that is only `Annulation demandée`; approval has not occurred. | Use a neutral/pending submission illustration and reserve success for accepted cancellation. |
| MAJOR | Product decision | `Remboursement en cours d’examen` appears before seller approval and says no refund is guaranteed. | NEEDS PRODUCT DECISION: define cancellation approval and automatic-refund state transitions, then show only the current state. |
| MINOR | Navigation | No back or close affordance is visible. | Add a predictable dismissal/back action or make the primary action the explicit completion control. |

### Canonical recommendation
Corrected before use; keep as the canonical cancellation-request-submitted state only after pending versus approved semantics are separated.

---

## 07-cancel-order-confirmation-dialog-fr.png

Folder: `07-orders/`  
Screen purpose: Confirm a destructive order-cancellation action  
Probable native route: `Orders/Cancel/Confirm`  
Language: FR  
Screen type: Dialog

### Status
REFERENCE_ONLY

### Confidence
High

### What the screen represents
A final confirmation step listing the affected order and products before sending a cancellation request to the seller.

### Visible UI structure
- Logo and notification action
- Confirmation heading and decorative warning illustration
- Order reference and irreversible-action warning
- Three item rows
- Keep-order and confirm-cancellation actions
- Seller-validation helper text

### Brand validation
- Logo: Official and proportional
- Colors: Palette-consistent, but destructive orange is not semantically distinct
- Typography: Brand-consistent display heading
- Icons: Coherent line icons
- Buttons: Competing full-width actions with destructive action in brand orange
- Cards: Consistent
- Spacing: Designed as a full-page poster, not a dialog
- Shadows: Soft and consistent

### UX validation
- Clear user goal: Confirm or abandon cancellation
- Primary action: Visible, but destructive semantics are weak
- Navigation logic: No close/back escape despite being named a dialog
- Form usability: N/A
- Scroll behavior: Full-page content would overflow smaller devices
- Empty/error behavior: N/A
- Accessibility concerns: A true dialog needs focus trapping, an accessible title/description, escape behavior, and a non-color-only danger label

### Native implementation usability
Individual pieces are reusable, but this composition should not be implemented literally as a dialog. Use a concise native modal/bottom sheet or treat it as a full route with a standard header.

### Reusable components identified
- ConfirmationDialog
- OrderReferenceCard
- OrderItemRow
- InlineWarning
- DestructiveButton
- SecondaryButton

### Dynamic backend data required
- Order reference
- Cancellation eligibility
- Affected products, quantities, and prices
- Approval policy message

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Screen type | The file is labeled a dialog but renders a complete long page without dialog bounds, scrim, close control, or modal behavior. | Convert to a concise accessible dialog/bottom sheet or rename the intended route in product specifications; do not copy this composition directly. |
| MAJOR | Destructive action | `Confirmer l’annulation` uses the normal orange primary style. | Use the foundation destructive red treatment while keeping `Conserver ma commande` visually safest/default. |
| MAJOR | Product decision | Copy alternates between irreversible cancellation and a request sent for seller validation. | NEEDS PRODUCT DECISION: confirm whether this action cancels immediately or submits a pending request. |

### Canonical recommendation
Kept only as a component/content reference; it must be replaced by a real modal or standard full-page confirmation pattern.

---

## 07-cancel-order-reason-form-fr.png

Folder: `07-orders/`  
Screen purpose: Collect a cancellation reason and optional message  
Probable native route: `Orders/Cancel/Reason`  
Language: FR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A cancellation request form for a paid order, including reason selection, a message, refund method, policy copy, and submission CTA.

### Visible UI structure
- Back header with logo
- Order summary/status card
- Radio-group reason selector
- Optional 250-character message field
- Refund method card
- Cancellation conditions notice
- `Envoyer la demande` CTA

### Brand validation
- Logo: Official and correctly proportioned
- Colors: Consistent palette
- Typography: Clear hierarchy
- Icons: Coherent line family
- Buttons: Correct single primary CTA
- Cards: Consistent rounded white surfaces
- Spacing: Good 8 pt rhythm, though the page is long
- Shadows: Restrained

### UX validation
- Clear user goal: Submit a cancellation request
- Primary action: Clear
- Navigation logic: Predictable back action
- Form usability: Visible radio labels and character count; keyboard must not obscure CTA
- Scroll behavior: Must be a keyboard-aware scroll view
- Empty/error behavior: Required-reason and submit-failure states are not shown
- Accessibility concerns: Radio-group selected state and refund method must be announced; CTA should disable/show progress during submission

### Native implementation usability
Straightforward with controlled radio inputs, a multiline TextInput, reusable cards, and a keyboard-aware scroll container.

### Reusable components identified
- AppHeader
- OrderSummaryCard
- RadioOptionGroup
- FormTextArea
- PaymentMethodCard
- InlineNotice
- PrimaryButton

### Dynamic backend data required
- Order reference, status, item thumbnails, count, and total
- Allowed cancellation reasons
- Cancellation eligibility and seller-approval policy
- Refund method and masked payment account
- Estimated refund delay

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Product decision | Seller approval and cancellation eligibility are presented as fixed policy without confirming the actual buyer/order state machine. | NEEDS PRODUCT DECISION: define eligibility windows, seller authority, and automatic versus manual approval before implementation. |
| MINOR | Form feedback | No inline state is specified for missing reason, failed submission, or pending submission. | Add validation below the radio group and loading/error feedback on the CTA. |

### Canonical recommendation
Corrected before use; this is the preferred cancellation-reason structure once policy and feedback states are confirmed.

---

## 07-delivery-delayed-notification-fr.png

Folder: `07-orders/`  
Screen purpose: Explain a delivery delay and revised estimate  
Probable native route: `Orders/DeliveryDelay`  
Language: FR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A delay notice with original/revised delivery estimates, carrier update, reason, reassurance, tracking, and support actions.

### Visible UI structure
- Back header, logo, notification action
- Delay heading
- Order/status card
- Delivery-change timeline and delay explanation
- Apology notice
- Tracking and support CTAs

### Brand validation
- Logo: Correct
- Colors: Consistent, with orange warning emphasis
- Typography: Clear, brand-aligned hierarchy
- Icons: Coherent
- Buttons: Clear primary/secondary actions
- Cards: Consistent
- Spacing: Readable but dense
- Shadows: Consistent

### UX validation
- Clear user goal: Understand delay and next action
- Primary action: Continue tracking
- Navigation logic: Back and support are available
- Form usability: N/A
- Scroll behavior: Must scroll with bottom inset
- Empty/error behavior: Carrier-update unavailable state is not represented
- Accessibility concerns: Timeline reading order must match chronology; warning cannot rely on orange alone

### Native implementation usability
Feasible as reusable event rows and notices, provided all dates/reasons come from carrier/order data rather than static copy.

### Reusable components identified
- AppHeader
- OrderStatusCard
- DeliveryChangeTimeline
- InlineWarning
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Order reference and package status
- Original and revised delivery windows
- Carrier update time and delay reason
- Tracking availability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Data consistency | Order reference `#MAYUSH-2025-05178` conflicts with all visible 2026 delivery events and the established order-reference format. | Use one canonical order ID format and the actual order year. |
| MAJOR | Date accuracy | `Mercredi 28 mai 2026` and `Vendredi 30 mai 2026` are false; those dates are Thursday and Saturday. | Derive localized weekday labels from ISO timestamps; never store weekday copy separately. |
| MINOR | Content hierarchy | `Vos paiements sont sécurisés` is unrelated to delivery delay recovery. | Replace with carrier/support reassurance or remove. |

### Canonical recommendation
Corrected before use; suitable as the delay-state route after date and identifier logic are repaired.

---

## 07-delivery-failed-reschedule-fr.png

Folder: `07-orders/`  
Screen purpose: Explain a failed delivery and prompt rescheduling  
Probable native route: `Orders/RescheduleDelivery`  
Language: FR  
Screen type: Full page / Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A failed-delivery state caused by recipient absence, with order, address, previous window, and reschedule/support actions.

### Visible UI structure
- Back header, logo, notification action
- Error heading and delivery-failure summary
- Failure reason
- Order preview/status card
- Delivery address and last-attempt window
- Action-required notice
- Reschedule and support CTAs

### Brand validation
- Logo: Correct
- Colors: Error red plus Mayush orange/navy/cream are coherent
- Typography: Strong hierarchy
- Icons: Consistent
- Buttons: Clear single recovery CTA
- Cards: Consistent
- Spacing: Comfortable
- Shadows: Soft and consistent

### UX validation
- Clear user goal: Reschedule failed delivery
- Primary action: Clear
- Navigation logic: Back and support available
- Form usability: N/A; next screen must expose available slots
- Scroll behavior: Feasible in one scroll view
- Empty/error behavior: Missing no-slot and reschedule-failure states
- Accessibility concerns: Error and action-required status include text/icons; native button states still required

### Native implementation usability
Feasible with an error-state layout and order summary. Rescheduling must open a real slot picker sourced from carrier availability.

### Reusable components identified
- AppHeader
- ErrorSummaryCard
- OrderPreviewCard
- AddressCard
- InlineWarning
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Failed-attempt timestamp and reason
- Order reference, products, item count, total, and status
- Delivery address
- Reschedule eligibility and carrier slot availability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Date accuracy | `Mercredi 28 mai 2026` is incorrect; 28 May 2026 is Thursday. | Generate weekday text from the timestamp. |
| MAJOR | Product decision | The screen assumes the buyer can self-reschedule without showing carrier eligibility, fees, or slot availability. | NEEDS PRODUCT DECISION: define reschedule rules and failure outcomes before treating the CTA as canonical. |
| MINOR | Content hierarchy | Payment-security reassurance is unrelated to the failed-delivery task. | Replace with a rescheduling/support note or remove. |

### Canonical recommendation
Corrected before use; keep as the canonical failed-delivery entry after carrier rules and date localization are defined.

---

## 07-invoice-detail-download-share-fr.png

Folder: `07-orders/`  
Screen purpose: View, download, and share an order invoice  
Probable native route: `Orders/Invoice`  
Language: FR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An invoice detail with billing identity, product lines, subtotal, delivery, VAT, total, and native download/share actions.

### Visible UI structure
- Back header, logo, notification action
- Invoice title and order/invoice identifiers
- Billing contact/address card
- Product table
- Financial totals
- Download, share, and order-detail actions

### Brand validation
- Logo: Correct
- Colors: Consistent
- Typography: Clear, though the invoice title/icon are oversized
- Icons: Coherent and suitable for native actions
- Buttons: Clear hierarchy
- Cards: Consistent
- Spacing: Readable but vertically long
- Shadows: Restrained

### UX validation
- Clear user goal: Inspect or export invoice
- Primary action: Download is clear; share is secondary
- Navigation logic: Back and order-detail return path exist
- Form usability: N/A
- Scroll behavior: Must remain scrollable and support text scaling
- Empty/error behavior: Download/share failure states are absent
- Accessibility concerns: Product totals need a logical table-like reading order; export actions require progress and error announcements

### Native implementation usability
Feasible with dynamic invoice rows and the native share sheet. The generated PDF/document should be the fiscal source, not a screenshot of this UI.

### Reusable components identified
- AppHeader
- InvoiceIdentityCard
- BillingInfoCard
- InvoiceLineItem
- MoneySummary
- PrimaryButton
- ShareButton

### Dynamic backend data required
- Order and invoice identifiers/dates
- Buyer billing identity, email, phone, and address
- Product references, quantities, and amounts
- Subtotal, delivery, tax rate/amount, and payable total
- Invoice document URL or generated file

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Payment logic | The visible item prices sum to `14 270 MAD`, then `TVA (20%)` is added to reach `17 124 MAD`; buyer-facing catalog/order prices elsewhere appear tax-inclusive. | NEEDS PRODUCT DECISION: establish TTC versus HT pricing and render invoice totals from authoritative fiscal data. |
| MAJOR | Error recovery | Download and share have no loading, permission, offline, or failure treatment. | Disable during export, show progress, and provide retry/open-file recovery. |
| MINOR | Layout | The oversized title block consumes substantial mobile space. | Use the standard compact detail header while retaining invoice identity. |

### Canonical recommendation
Corrected before use; do not implement until tax-inclusive/exclusive rules are confirmed.

---

## 07-multiple-packages-split-shipment-fr.png

Folder: `07-orders/`  
Screen purpose: Show one order split into separately tracked packages  
Probable native route: `Orders/Packages`  
Language: FR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A three-package overview with products, seller, carrier, status, estimated delivery, and per-package tracking actions.

### Visible UI structure
- Back header, logo, notification action
- Multi-package explanation and order-status card
- Three expanded package cards
- Per-package seller/carrier/date data and tracking buttons
- Informational footer notice

### Brand validation
- Logo: Correct
- Colors: Base palette is consistent; status semantics are not
- Typography: Clear hierarchy
- Icons: Consistent
- Buttons: Repeated outline tracking actions are clear
- Cards: Consistent
- Spacing: Dense but scannable
- Shadows: Soft and consistent

### UX validation
- Clear user goal: Track each package independently
- Primary action: Repeated per-package tracking is appropriate
- Navigation logic: Predictable back action
- Form usability: N/A
- Scroll behavior: Long list must preserve expanded/collapsed state
- Empty/error behavior: No tracking-unavailable package state is shown
- Accessibility concerns: Expansion chevrons need expanded/collapsed semantics; seller/carrier/status reading order must be grouped per package

### Native implementation usability
Feasible as a FlatList of expandable PackageCard components with nested, non-scrolling item rows.

### Reusable components identified
- AppHeader
- OrderStatusCard
- PackageCard
- StatusBadge
- ProductThumbnailStrip
- OutlineButton
- InlineNotice

### Dynamic backend data required
- Order reference and aggregate status
- Package count and IDs
- Package products
- Seller and carrier names
- Package status, estimate, and tracking availability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Status system | `En transit` is green while the foundation reserves blue for shipped/in-transit and green for delivered/completed. | Apply the canonical status-badge token mapping. |
| MAJOR | Product decision | Three seller/carrier brands are presented as fixed facts. | NEEDS PRODUCT DECISION: confirm multi-vendor fulfillment and source seller/carrier names dynamically; treat shown names as mock data only. |
| MINOR | Interaction | All three cards show expanded chevrons simultaneously, creating a very long page. | Default to the most relevant package expanded or preserve user expansion state. |

### Canonical recommendation
Corrected before use; preferred package-overview structure after status tokens and fulfillment rules are confirmed.

---

## 07-order-cannot-be-cancelled-fr.png

Folder: `07-orders/`  
Screen purpose: Explain why cancellation is no longer available  
Probable native route: `Orders/Cancel/Unavailable`  
Language: FR  
Screen type: Full page / Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An order that is already in delivery and can no longer be cancelled, with a suggested future return path and support action.

### Visible UI structure
- Logo and notification action
- Unavailable-cancellation illustration and explanation
- Order/status summary and product strip
- Return-policy notice
- Return-options and support actions

### Brand validation
- Logo: Correct
- Colors: Consistent
- Typography: Strong hierarchy
- Icons: Consistent
- Buttons: Clear visually, but the primary action is not currently actionable
- Cards: Consistent
- Spacing: Poster-like with large header area
- Shadows: Soft

### UX validation
- Clear user goal: Understand blocked cancellation
- Primary action: Misleading because a return cannot be started before receipt
- Navigation logic: No back/close action
- Form usability: N/A
- Scroll behavior: Fits only on a tall viewport; smaller devices must scroll
- Empty/error behavior: N/A
- Accessibility concerns: The blocked reason should receive focus; the unavailable return action must not appear enabled

### Native implementation usability
Feasible, but the action state must be driven by delivery/return eligibility and must not expose a dead-end enabled button.

### Reusable components identified
- ResultIllustration
- OrderSummaryCard
- InlineNotice
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Order reference, status, products, count, and paid total
- Cancellation eligibility reason
- Return eligibility start date and policy URL
- Support route

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Action logic | `Voir les options de retour` is the dominant CTA while copy says a return is possible only after the order is received. | Show `Voir la politique de retour`, disable with an eligibility explanation, or defer the action until delivery. |
| MAJOR | Navigation | The user cannot return to the order detail except via OS back. | Add the standard back header or an explicit `Retour aux détails` action. |
| MAJOR | Product decision | The exact point at which cancellation becomes unavailable and return becomes available is not defined. | NEEDS PRODUCT DECISION: map both actions to authoritative order states. |

### Canonical recommendation
Corrected before use; retain the explanatory concept but replace the premature primary CTA.

---

## 07-order-detail-ar.png

Folder: `07-orders/`  
Screen purpose: Arabic order detail for a paid/in-delivery order  
Probable native route: `Orders/Detail`  
Language: AR  
Screen type: Full page

### Status
REJECTED

### Confidence
High

### What the screen represents
An RTL order detail with payment/delivery status, products, delivery address, shipping/payment methods, and totals.

### Visible UI structure
- Logo and left-pointing back arrow
- RTL order metadata/status card
- Three product rows
- Delivery address with edit action
- Shipping and cash-on-delivery cards
- Financial summary

### Brand validation
- Logo: Correct
- Colors: Consistent
- Typography: Arabic typography is readable and `MAD` remains legible
- Icons: Coherent
- Buttons: Only an address edit text action is visible
- Cards: Consistent
- Spacing: Generally clean
- Shadows: Soft

### UX validation
- Clear user goal: Review order details
- Primary action: None, which is acceptable for read-only detail
- Navigation logic: Back arrow direction is wrong for RTL
- Form usability: N/A
- Scroll behavior: Long content requires vertical scrolling
- Empty/error behavior: N/A
- Accessibility concerns: RTL reading order must match the visual order; mixed Arabic, numbers, and `MAD` require explicit bidi handling

### Native implementation usability
The card structure is feasible, but the screenshot cannot guide implementation because payment and total data are internally contradictory and RTL navigation is incorrect.

### Reusable components identified
- RTLAppHeader
- OrderMetaCard
- OrderItemRow
- AddressCard
- ShippingMethodCard
- PaymentMethodCard
- MoneySummary

### Dynamic backend data required
- Order number/date
- Payment and fulfillment statuses
- Product name, variant, quantity, image, and price
- Delivery address
- Shipping/payment method
- Subtotal, shipping, and total

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Payment logic | Status says `مدفوع` (paid) while the payment method says cash on delivery/pay when receiving the order. | Use `غير مدفوع`/payment pending for COD until collection, or show the actual prepaid method. |
| CRITICAL | Arithmetic | Product prices total `10 630 MAD`, but subtotal is `13 630 MAD` and total is `13 660 MAD`. | Calculate all totals from authoritative line items; block rendering when totals do not reconcile. |
| CRITICAL | RTL | The back arrow points left; an RTL back action must point right. | Use the RTL back asset and verify screen-reader order. |
| MAJOR | Date accuracy | Arabic copy calls 28 May 2026 Wednesday; it is Thursday. | Generate localized day names from timestamps. |
| MAJOR | Action logic | Delivery address remains editable while the order is already in delivery. | NEEDS PRODUCT DECISION: hide/disable editing after the carrier cutoff and explain why. |

### Canonical recommendation
Replaced by a corrected Arabic variant; do not use this image as an implementation reference.

---

## 07-order-detail-delivered-actions-fr.png

Folder: `07-orders/`  
Screen purpose: Delivered order detail with post-purchase actions  
Probable native route: `Orders/Detail`  
Language: FR  
Screen type: Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A delivered-order detail with products, totals, address, reorder/review/return/invoice actions, and support.

### Visible UI structure
- Back header and logo
- Delivered status and date cards
- Three product rows with totals
- Delivery address/map action
- Four post-purchase action tiles
- Support notice

### Brand validation
- Logo: Correct
- Colors: Correct green delivered state plus core palette
- Typography: Clear hierarchy
- Icons: Consistent rounded outlines
- Buttons: Action tiles are visually subordinate to detail content
- Cards: Consistent
- Spacing: Balanced
- Shadows: Soft and consistent

### UX validation
- Clear user goal: Review a delivered order and act on it
- Primary action: No forced primary; contextual actions are appropriate
- Navigation logic: Predictable back action
- Form usability: N/A
- Scroll behavior: Use a scroll view with sufficient bottom inset
- Empty/error behavior: Action-specific unavailable states are not shown
- Accessibility concerns: Four tiles need 44/48 pt hit areas, concise labels, and eligible/unavailable announcements

### Native implementation usability
Highly feasible with a state-driven OrderDetail template and reusable action tiles.

### Reusable components identified
- AppHeader
- OrderStatusCard
- OrderItemRow
- MoneySummary
- AddressCard
- OrderActionTile
- SupportBanner

### Dynamic backend data required
- Order reference/status/delivery time
- Product details and totals
- Delivery address/map coordinates
- Reorder, review, return, and invoice eligibility
- Return deadline

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Date accuracy | `Vendredi 30 mai 2026` is incorrect; 30 May 2026 is Saturday. | Generate the weekday from the delivery timestamp. |
| MINOR | Responsive layout | Four equal action tiles may become cramped with larger French text or Dynamic Type. | Allow two-column wrapping or a vertical action list at large text sizes. |

### Canonical recommendation
Used directly as the main delivered-order reference after the weekday and responsive action-grid corrections.

---

## 07-order-detail-in-preparation-timeline-fr.png

Folder: `07-orders/`  
Screen purpose: Paid order detail while items are in preparation  
Probable native route: `Orders/Detail`  
Language: FR  
Screen type: Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A paid order detail with a four-step fulfillment timeline, products, delivery/payment/coupon data, total, cancellation, and support.

### Visible UI structure
- Back header and logo
- Order/payment/current-status card
- Four-step order timeline
- Two product rows
- Delivery and payment/coupon cards
- Total, cancel, support, and trust notice

### Brand validation
- Logo: Correct
- Colors: Status/token usage is coherent
- Typography: Clear
- Icons: Consistent
- Buttons: Destructive cancellation is correctly outlined/red
- Cards: Consistent
- Spacing: Dense but logical
- Shadows: Soft

### UX validation
- Clear user goal: Understand order progress and details
- Primary action: No unnecessary dominant CTA; cancellation is clearly destructive
- Navigation logic: Predictable back action
- Form usability: N/A
- Scroll behavior: Straightforward vertical scroll
- Empty/error behavior: Tracking/cancellation unavailable states exist elsewhere
- Accessibility concerns: Timeline stages need labels such as current/completed/upcoming beyond color

### Native implementation usability
Strong reusable React Native template. Render the timeline from order events and keep cancellation eligibility state-driven.

### Reusable components identified
- AppHeader
- OrderMetaGrid
- OrderProgressStepper
- OrderItemRow
- AddressDeliveryCard
- PaymentCouponCard
- MoneySummary
- DestructiveButton

### Dynamic backend data required
- Order reference, creation time, payment/current status
- Fulfillment events
- Products and variants
- Address, delivery estimate, and carrier
- Payment method, coupon, and totals
- Cancellation eligibility

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Date accuracy | `Mercredi 28 mai 2026`, `ven. 30 mai`, and `lun. 2 juin 2026` use incorrect weekdays; the dates are Thursday, Saturday, and Tuesday. | Generate all localized weekday labels from timestamps. |
| MINOR | Accessibility | Step completion/current state is heavily color-dependent. | Add semantic state labels and accessibilityState values for each step. |

### Canonical recommendation
Used directly as the primary in-preparation order-detail reference after date localization and stepper accessibility fixes.

---

## 07-order-detail-multi-vendor-packages-fr.png

Folder: `07-orders/`  
Screen purpose: Order detail grouped by sellers and packages  
Probable native route: `Orders/Detail`  
Language: FR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A multi-vendor order split across four packages, grouped into two seller cards with item/package statuses and delivery dates.

### Visible UI structure
- Back header, logo, notification action
- Four-column order summary
- Multi-package notice
- Two seller cards with two package lines each
- Payment and address summaries
- Track-all and support actions

### Brand validation
- Logo: Correct
- Colors: Core palette is consistent; some aggregate statuses are misleading
- Typography: Clear but information-dense
- Icons: Consistent
- Buttons: Clear
- Cards: Consistent
- Spacing: Dense table-like content
- Shadows: Soft

### UX validation
- Clear user goal: Understand a complex split order
- Primary action: Track all packages
- Navigation logic: Back and support available
- Form usability: N/A
- Scroll behavior: Use one vertical list; avoid nested scrolling
- Empty/error behavior: Per-package unavailable/error state is absent
- Accessibility concerns: Column headings and each item/status/date relationship need explicit grouped reading order

### Native implementation usability
Feasible as seller sections with package rows, but the screenshot's dates/statuses are not safe data references.

### Reusable components identified
- AppHeader
- OrderMetaGrid
- InlineNotice
- SellerPackageSection
- PackageItemRow
- PaymentSummaryCard
- AddressCard
- PrimaryButton

### Dynamic backend data required
- Aggregate order/payment status and package count
- Seller identities
- Product lines, quantities, package IDs, and package states
- Delivery estimates and actual delivery timestamps
- Total, payment method, and address

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Chronology | Order date is 28 May 2026, but Studio Noya items claim delivery on 26 and 27 May, before the order existed. | Source dates from package events and validate that lifecycle timestamps are monotonic. |
| MAJOR | State contradiction | One row says `En transit` while its date column says `Livré le 27 mai`; the seller-level badge says `Expédié` while another row is already delivered. | Define clear row and aggregate status rules; show `Livraison estimée` for in-transit items and `Livré le` only after delivery. |
| MAJOR | Product decision | Multi-vendor seller/package grouping is assumed. | NEEDS PRODUCT DECISION: confirm marketplace fulfillment and seller visibility before making this the default detail model. |

### Canonical recommendation
Corrected before use; combine its seller grouping with the cleaner per-package state treatment from `07-multiple-packages-split-shipment-fr.png`.

---

## 07-order-detail-shipped-tracking-fr.png

Folder: `07-orders/`  
Screen purpose: Shipped order detail with tracking entry point  
Probable native route: `Orders/Detail`  
Language: FR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A shipped-order detail showing tracking number/carrier, products, address, payment summary, and tracking/support actions.

### Visible UI structure
- Back header and logo
- Shipped status and tracking metadata
- Two product rows
- Address/map action
- Payment summary
- Tracking and support CTAs
- Trust notice

### Brand validation
- Logo: Correct
- Colors: Shipped status incorrectly uses green rather than the foundation blue
- Typography: Clear
- Icons: Consistent
- Buttons: Clear
- Cards: Consistent
- Spacing: Balanced
- Shadows: Soft

### UX validation
- Clear user goal: Review shipment and open tracking
- Primary action: `Suivre la livraison`
- Navigation logic: Predictable back action
- Form usability: N/A
- Scroll behavior: Straightforward
- Empty/error behavior: Tracking unavailable state exists separately
- Accessibility concerns: Copy-tracking-number action requires a label and confirmation announcement

### Native implementation usability
Feasible using the shared OrderDetail template and native clipboard feedback.

### Reusable components identified
- AppHeader
- ShipmentStatusCard
- CopyButton
- OrderItemRow
- AddressCard
- MoneySummary
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Order/tracking references
- Carrier and shipment/estimate dates
- Products and totals
- Delivery address
- Payment status/method
- Tracking availability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Status system | `Expédiée` uses the green delivered/completed treatment rather than the foundation blue shipped badge. | Apply the canonical shipped-blue token and icon. |
| MAJOR | Date accuracy | 28 May 2026 is called Wednesday; 30 May is called Friday; 2 June is called Monday. | Generate localized weekdays from timestamps. |
| MINOR | Localization | French prices use `7 890.00 MAD` rather than locale-appropriate separators. | Use a single formatter such as `7 890,00 MAD` or the product-approved no-decimal format. |

### Canonical recommendation
Corrected before use; use the in-preparation detail as the shared structural base and apply this shipped-state content.

---

## 07-order-detail-skeleton-loading-state.png

Folder: `07-orders/`  
Screen purpose: Loading placeholder for order detail  
Probable native route: `Orders/Detail`  
Language: FR  
Screen type: Loading state

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A complete order-detail skeleton preserving header, summary, timeline, items, address, payment, totals, and actions while data loads.

### Visible UI structure
- Back header, logo, notification action
- Summary and timeline skeletons
- Product list skeleton
- Address/payment/total skeleton sections
- Two action placeholders

### Brand validation
- Logo: Correct and kept visible
- Colors: Neutral beige placeholders align with the system
- Typography: Real section headings aid orientation
- Icons: Static navigation icons are coherent
- Buttons: Primary placeholder is incorrectly orange
- Cards: Match loaded-state geometry
- Spacing: Consistent
- Shadows: Soft

### UX validation
- Clear user goal: Wait while preserving expected layout
- Primary action: Should not appear actionable during loading
- Navigation logic: Back remains available
- Form usability: N/A
- Scroll behavior: Skeleton reserves layout space and prevents jumps
- Empty/error behavior: Must transition to loaded or error state
- Accessibility concerns: Hide decorative skeleton blocks from the accessibility tree and announce loading once without repeated chatter

### Native implementation usability
Highly feasible with reusable skeleton primitives matching the real OrderDetail template.

### Reusable components identified
- AppHeader
- SkeletonBlock
- SkeletonCard
- OrderDetailSkeleton
- LoadingAnnouncement

### Dynamic backend data required
- Loading state only

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Loading affordance | The full-width orange CTA placeholder looks enabled and tappable. | Render all action placeholders in neutral skeleton colors and disable pointer/accessibility actions. |
| MINOR | Accessibility | No visible or specified loading label accompanies the skeleton. | Add one polite screen-reader announcement and avoid exposing each placeholder. |

### Canonical recommendation
Used directly as the main order-detail loading reference after neutralizing action placeholders.

---

## 07-order-not-found-error-fr.png

Folder: `07-orders/`  
Screen purpose: Recover from an invalid or unavailable order reference  
Probable native route: `Orders/NotFound`  
Language: FR  
Screen type: Full page / Error state

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
An order-not-found state with an illustration, explanation, orders-list recovery action, and support access.

### Visible UI structure
- Logo and notification action
- Order-not-found illustration
- Error heading and guidance
- Primary orders-list action
- Secondary support action

### Brand validation
- Logo: Correct
- Colors: Consistent
- Typography: Clear
- Icons: Matches the state-illustration and action-icon language
- Buttons: Strong recovery hierarchy
- Cards: N/A
- Spacing: Spacious, slightly poster-like
- Shadows: Soft in illustration

### UX validation
- Clear user goal: Recover from missing order
- Primary action: Return to orders
- Navigation logic: No back action
- Form usability: The instruction says verify the number, but there is no number input on this screen
- Scroll behavior: Single-screen state
- Empty/error behavior: Appropriate recovery path
- Accessibility concerns: Focus should move to the error heading; illustration should be decorative or labeled once

### Native implementation usability
Simple and feasible with the official state illustration and shared error-state template.

### Reusable components identified
- AppHeader
- StateIllustration
- ErrorState
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Failed order reference, if safe to display
- Support availability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Navigation | No back action is visible. | Add the standard back header, especially when opened from a deep link. |
| MINOR | Copy | `Vérifier le numéro saisi` offers no local edit/retry control. | Either show the attempted reference with `Réessayer`, or simplify copy to returning to orders. |

### Canonical recommendation
Used directly as the main not-found reference after adding back/retry clarity.

---

## 07-orders-cancelled-tab-refund-statuses-fr.png

Folder: `07-orders/`  
Screen purpose: Show cancelled orders and refund statuses  
Probable native route: `Orders/List?tab=cancelled`  
Language: FR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
The cancelled-orders tab with cancelled, refund-pending, refunded, and partially refunded cards.

### Visible UI structure
- Back header and logo
- Orders-history title
- Four top tabs with `Annulées` active
- Four order cards with thumbnails, amount, state, and details action
- Informational footer

### Brand validation
- Logo: Correct
- Colors: Status colors are mostly semantic
- Typography: Clear
- Icons: Consistent
- Buttons: Text/chevron details actions are clear
- Cards: Consistent
- Spacing: Dense but readable
- Shadows: Soft

### UX validation
- Clear user goal: Review cancelled/refund states
- Primary action: Per-card details
- Navigation logic: Bottom buyer navigation is missing, unlike related orders-list variants
- Form usability: N/A
- Scroll behavior: Requires a virtualized list and bottom inset
- Empty/error behavior: Separate states exist
- Accessibility concerns: Top tabs need selected semantics; partial refund needs a clear amount basis

### Native implementation usability
Feasible as the cancelled filter of the shared OrdersList route; should not be a separately composed navigation model.

### Reusable components identified
- AppHeader
- OrdersTabBar
- OrderCard
- StatusBadge
- ProductThumbnailStrip
- InlineNotice

### Dynamic backend data required
- Order ID/date, item count, thumbnails, and amount
- Cancellation origin
- Refund status, date, refunded items, and refunded amount

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | The shared order-list destination loses the approved bottom navigation while completed/all variants retain it. | Use one OrdersList shell with the canonical five-tab buyer nav and preserve the selected account context. |
| MAJOR | Refund clarity | `Partiellement remboursée` shows `1 article remboursé` but the amount displayed is not labeled as order total versus refund amount. | Label both original total and refunded amount, or explicitly state what the shown amount represents. |
| MINOR | Localization | Prices use `.00` in French. | Use the approved locale-aware MAD formatter consistently. |

### Canonical recommendation
Combined with `07-orders-list-all-tabs-fr.png`; reuse its cards/status ideas inside one corrected canonical list shell.

---

## 07-orders-completed-tab-reorder-review-fr.png

Folder: `07-orders/`  
Screen purpose: Show completed orders with reorder, review, and detail actions  
Probable native route: `Orders/List?tab=completed`  
Language: FR  
Screen type: Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
The completed-orders tab with product thumbnails, totals, destinations, and three contextual actions per order.

### Visible UI structure
- Back header, logo, cart shortcut/badge
- Orders title and four tabs
- Three completed order cards
- Reorder, review, and detail actions
- Support banner
- Correct five-tab buyer bottom navigation

### Brand validation
- Logo: Correct
- Colors: Consistent
- Typography: Clear
- Icons: Coherent with the foundation
- Buttons: Secondary contextual actions are well grouped
- Cards: Consistent
- Spacing: Good
- Shadows: Soft

### UX validation
- Clear user goal: Review completed purchases and take post-purchase actions
- Primary action: Contextual per-card actions
- Navigation logic: Approved bottom navigation is present and `Compte` is active
- Form usability: N/A
- Scroll behavior: Use FlatList with bottom-nav inset
- Empty/error behavior: Separate states exist
- Accessibility concerns: Card and nested actions must not create overlapping tap targets; product quantity badges need labels

### Native implementation usability
Strong reusable OrdersList tab state using virtualized OrderCard components.

### Reusable components identified
- AppHeader
- OrdersTabBar
- OrderCard
- ProductThumbnailStrip
- OrderCardActions
- SupportBanner
- BuyerBottomTabs

### Dynamic backend data required
- Completed order ID/date/amount
- Products and quantities
- Delivery city/method
- Reorder, review, and invoice/detail eligibility
- Cart badge count

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Navigation redundancy | A cart shortcut appears in the header while `Panier` is already in bottom navigation. | Remove unless a tested need justifies it; otherwise keep icon/badge behavior identical to the bottom tab. |
| MINOR | Responsive actions | Three inline actions may wrap or truncate at large text sizes. | Stack actions or use an overflow sheet when Dynamic Type requires it. |

### Canonical recommendation
Used directly as the completed-tab reference after responsive action and redundant-cart review.

---

## 07-orders-empty-state-fr.png

Folder: `07-orders/`  
Screen purpose: Show that the buyer has no orders yet  
Probable native route: `Orders/List`  
Language: FR  
Screen type: Full page / Empty state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A no-orders state with a decorative package illustration, collection-discovery CTA, favorites action, and bottom navigation.

### Visible UI structure
- Large logo and `Mes commandes` title
- Empty-state illustration and copy
- Discover-collections primary CTA
- Favorites secondary CTA
- Five-item bottom navigation

### Brand validation
- Logo: Correct but excessively large
- Colors: Consistent
- Typography: Brand-aligned
- Icons: Mostly consistent
- Buttons: Clear
- Cards: Bottom navigation uses a rounded floating surface
- Spacing: Overly decorative and vertically inefficient
- Shadows: Soft

### UX validation
- Clear user goal: Start shopping or view favorites
- Primary action: Clear
- Navigation logic: Bottom navigation violates the required destinations
- Form usability: N/A
- Scroll behavior: Should fit without unnecessary scrolling on normal devices
- Empty/error behavior: Helpful actions are present
- Accessibility concerns: Decorative illustration should not dominate focus; maintain access to account/orders context

### Native implementation usability
Feasible as a shared EmptyState component, but the page shell/navigation cannot be copied directly.

### Reusable components identified
- EmptyState
- StateIllustration
- PrimaryButton
- SecondaryButton
- BuyerBottomTabs

### Dynamic backend data required
- Whether the current orders query is empty
- Favorites availability/count, if used

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | `Explorer` replaces the mandated `Catégories` buyer tab. | Use `Accueil`, `Catégories`, `Favoris`, `Panier`, `Compte` in that order. |
| MAJOR | Route context | The screen omits the standard orders tab/search shell, making an empty filtered tab indistinguishable from an account with no orders. | Preserve `Mes commandes` navigation context and tailor copy to all-orders versus filtered-empty state. |
| MINOR | Layout | Logo and illustration consume most of the viewport. | Use the compact app header and foundation empty-state scale. |

### Canonical recommendation
Corrected before use; retain the illustration/copy idea inside the canonical OrdersList shell.

---

## 07-orders-error-loading-state-fr.png

Folder: `07-orders/`  
Screen purpose: Recover when the orders list cannot load  
Probable native route: `Orders/List`  
Language: FR  
Screen type: Full page / Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A connection-error state with retry, return-to-account, and help guidance.

### Visible UI structure
- Logo and notification action
- Large connection-error illustration
- Error heading and explanatory copy
- Retry and account actions inside a card
- Help note

### Brand validation
- Logo: Correct but oversized
- Colors: Consistent
- Typography: Brand-aligned
- Icons: Coherent
- Buttons: Strong recovery hierarchy
- Cards: Consistent
- Spacing: Large unused vertical areas
- Shadows: Soft

### UX validation
- Clear user goal: Retry loading orders
- Primary action: `Réessayer`
- Navigation logic: Orders context, tabs, and buyer bottom navigation disappear
- Form usability: N/A
- Scroll behavior: Full-screen replacement
- Empty/error behavior: Recovery copy/action are clear
- Accessibility concerns: Focus should move to the error heading; retry needs loading/disabled feedback and an announced result

### Native implementation usability
The error component is feasible, but it should render inside the shared OrdersList shell so navigation and route context remain stable.

### Reusable components identified
- InlineFullState
- StateIllustration
- RetryButton
- SecondaryButton
- SupportHint

### Dynamic backend data required
- Error category/connectivity state
- Retry availability
- Support route

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | The full-screen error removes the orders title/tabs and approved bottom navigation. | Keep the stable OrdersList shell and replace only its content region with the error state. |
| MAJOR | Loading feedback | `Réessayer` has no specified progress, disabled, timeout, or repeated-failure behavior. | Show an inline spinner, disable while pending, and surface the next failure with retry/help. |
| MINOR | Layout | Oversized illustration and logo create excessive empty space. | Use the foundation compact error-state scale. |

### Canonical recommendation
Corrected before use; use the recovery content inside `07-orders-list-all-tabs-fr.png` after its navigation is fixed.

---

## 07-orders-in-progress-tab-statuses-fr.png

Folder: `07-orders/`  
Screen purpose: Show active orders across payment and fulfillment states  
Probable native route: `Orders/List?tab=in-progress`  
Language: FR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
The in-progress tab with cards for payment pending, confirmed, preparing, shipped, and out-for-delivery orders.

### Visible UI structure
- Logo and notification action
- Orders title and four tabs
- Five status-rich order cards
- Pay, details, and tracking actions
- Five-item bottom navigation

### Brand validation
- Logo: Correct
- Colors: Status colors are mostly coherent
- Typography: Clear
- Icons: Consistent locally
- Buttons: Contextual actions are clear
- Cards: Consistent
- Spacing: Dense but scannable
- Shadows: Soft

### UX validation
- Clear user goal: Monitor active orders and resolve payment
- Primary action: Per-card contextual action
- Navigation logic: Permanent bottom destinations are invalid
- Form usability: N/A
- Scroll behavior: Must use FlatList with fixed-nav inset
- Empty/error behavior: Separate states exist
- Accessibility concerns: Cards and nested CTAs need separate labels; selected tab and current bottom destination must be announced

### Native implementation usability
The card component is feasible and useful, but the surrounding navigation cannot guide the buyer app.

### Reusable components identified
- OrdersTabBar
- ActiveOrderCard
- StatusBadge
- ProductThumbnailStrip
- ContextualButton
- BuyerBottomTabs

### Dynamic backend data required
- Order ID/date/amount and products
- Payment state/action URL
- Fulfillment status
- Tracking availability
- Cart badge count

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Navigation | Bottom nav is `Accueil`, `Catégories`, `Panier`, `Commandes`, `Compte`; it removes `Favoris` and makes `Commandes` permanent. | Use exactly `Accueil`, `Catégories`, `Favoris`, `Panier`, `Compte`; orders remain inside account. |
| MAJOR | Navigation state | `Compte` appears active while a separate permanent `Commandes` destination is also visible. | Remove the competing hierarchy and keep orders as a child of `Compte`. |
| MINOR | Content density | Five large cards nearly meet the fixed bar with little breathing room. | Add list footer/safe-area inset and verify on 375 px devices. |

### Canonical recommendation
Combined with `07-orders-list-all-tabs-fr.png`; reuse these status/action variants only after replacing the bottom navigation.

---

## 07-orders-list-all-tabs-ar.png

Folder: `07-orders/`  
Screen purpose: Arabic all-orders list with search and filters  
Probable native route: `Orders/List`  
Language: AR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An RTL orders list with search, four status filters, three cards, and a five-item bottom navigation.

### Visible UI structure
- Mirrored notification action and centered logo
- Right-aligned Arabic title/subtitle
- Search field and RTL filter chips
- Three RTL order cards with payment/delivery status and details action
- RTL bottom navigation

### Brand validation
- Logo: Correct
- Colors: Consistent
- Typography: Arabic text is readable and visually hierarchical
- Icons: Consistent within the screen
- Buttons: RTL chevrons correctly point toward the next screen
- Cards: Consistent
- Spacing: Good
- Shadows: Soft

### UX validation
- Clear user goal: Search/filter prior orders
- Primary action: Per-card details
- Navigation logic: Buyer destinations are incorrect and partly seller-oriented
- Form usability: Search is usable; native bidi behavior must be set explicitly
- Scroll behavior: Use RTL-aware FlatList with bottom inset
- Empty/error behavior: Separate states exist
- Accessibility concerns: Screen-reader order must start at the right; Arabic labels, Latin IDs, numbers, and `MAD` need controlled bidi isolation

### Native implementation usability
The RTL list/card structure is feasible, but the bottom navigation must be replaced before use.

### Reusable components identified
- RTLAppHeader
- SearchInput
- RTLFilterTabs
- RTLOrderCard
- StatusBadge
- RTLBuyerBottomTabs

### Dynamic backend data required
- Order IDs/dates/totals
- Product thumbnails and counts
- Payment and delivery states
- Search/filter results

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Navigation | Bottom nav includes `منتجاتي` (My products) and `طلباتي` (My orders), omits the required `Catégories` and `Panier`, and resembles seller navigation. | Use mirrored buyer destinations: Accueil, Catégories, Favoris, Panier, Compte. |
| MAJOR | Product scope | `منتجاتي` implies products owned/managed by the user, which contradicts the buyer-only experience. | Replace with the approved buyer category/cart labels. |
| MINOR | Input layout | Search icon is placed on the left in an otherwise RTL field. | Confirm the platform RTL convention and keep icon/text/padding consistent across all Arabic search fields. |

### Canonical recommendation
Corrected before use; retain as the Arabic list/layout reference only after matching the canonical buyer navigation.

---

## 07-orders-list-all-tabs-fr.png

Folder: `07-orders/`  
Screen purpose: Main all-orders list with search and status tabs  
Probable native route: `Orders/List`  
Language: FR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
The principal French orders list with search, all/in-progress/completed/cancelled tabs, payment and delivery status, and detail actions.

### Visible UI structure
- Logo and notification action
- `Mes commandes` heading
- Order-number search
- Four top tabs
- Three order cards for active, completed, and cancelled states
- Five-item buyer bottom navigation

### Brand validation
- Logo: Correct but large
- Colors: Consistent and status meanings are clear
- Typography: Strong hierarchy
- Icons: Coherent
- Buttons: Outline detail actions are clear
- Cards: Consistent
- Spacing: Good
- Shadows: Soft

### UX validation
- Clear user goal: Find and inspect any order
- Primary action: Per-card detail
- Navigation logic: Correct destinations are present but in the wrong order
- Form usability: Search field is clear
- Scroll behavior: Must virtualize and reserve space above the fixed bar
- Empty/error behavior: Separate variants exist
- Accessibility concerns: Search label, tab selected state, status text, and nested card action must be announced; cards must tolerate Dynamic Type

### Native implementation usability
This is the strongest reusable French OrdersList shell once bottom-tab order is corrected.

### Reusable components identified
- AppHeader
- SearchInput
- OrdersTabBar
- OrderCard
- PaymentDeliveryStatusRow
- BuyerBottomTabs

### Dynamic backend data required
- Search query/results
- Order ID/date/status/amount
- Product thumbnails/count
- Payment and delivery statuses

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Bottom tabs place `Panier` before `Favoris`, contrary to the required order. | Use `Accueil`, `Catégories`, `Favoris`, `Panier`, `Compte` exactly. |
| MINOR | Header scale | Large logo/title reduce above-the-fold list content. | Use the compact standard header while keeping clear route identity. |

### Canonical recommendation
Corrected before use; this should be the canonical French OrdersList reference after bottom-navigation normalization.

---

## 07-orders-skeleton-loading-state.png

Folder: `07-orders/`  
Screen purpose: Loading placeholder for the orders list  
Probable native route: `Orders/List`  
Language: FR  
Screen type: Loading state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
The orders list while data loads, preserving heading, search, tabs, three cards, and bottom navigation.

### Visible UI structure
- Logo and notification action
- Real title/subtitle and disabled-looking search
- Skeleton tabs
- Three skeleton order cards
- Fixed buyer bottom navigation

### Brand validation
- Logo: Correct
- Colors: Neutral skeleton tones are appropriate
- Typography: Real heading anchors orientation
- Icons: Consistent
- Buttons: No false active CTA
- Cards: Match loaded-state geometry
- Spacing: Consistent
- Shadows: Soft

### UX validation
- Clear user goal: Wait for orders to load
- Primary action: N/A
- Navigation logic: Bottom destinations are ordered incorrectly
- Form usability: Search should be disabled while the initial list is unresolved
- Scroll behavior: Skeleton reserves space and avoids layout shift
- Empty/error behavior: Must transition to loaded/error state
- Accessibility concerns: Skeleton elements should be hidden; announce loading once; keep bottom tabs operable only if navigation is safe

### Native implementation usability
Strong shared skeleton structure, but it must use the same corrected navigation/order-card measurements as the canonical loaded screen.

### Reusable components identified
- AppHeader
- OrdersListSkeleton
- SkeletonTab
- SkeletonOrderCard
- BuyerBottomTabs
- LoadingAnnouncement

### Dynamic backend data required
- Loading state only

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Bottom tabs repeat the swapped `Panier`/`Favoris` order. | Match the approved canonical buyer-tab order. |
| MINOR | Accessibility | Loading is visual only. | Hide placeholders from assistive technology and announce `Chargement de vos commandes`. |

### Canonical recommendation
Corrected before use; pair it exactly with the corrected `07-orders-list-all-tabs-fr.png` shell.

---

## 07-order-tracking-timeline-ar.png

Folder: `07-orders/`  
Screen purpose: Arabic real-time order tracking timeline  
Probable native route: `Orders/Tracking`  
Language: AR  
Screen type: Full page

### Status
REJECTED

### Confidence
High

### What the screen represents
An RTL tracking detail with order/carrier/estimate data, address, six-stage timeline, carrier call, and customer support.

### Visible UI structure
- Mirrored header with notification left and right-pointing back arrow
- Arabic tracking title
- Order/status card
- Carrier, tracking number, estimate, and latest-update card
- Delivery address
- RTL vertical timeline
- Carrier/support CTAs

### Brand validation
- Logo: Correct
- Colors: Consistent
- Typography: Arabic hierarchy is clear
- Icons: Coherent and correctly mirrored where directional
- Buttons: Clear
- Cards: Consistent
- Spacing: Dense but readable
- Shadows: Soft

### UX validation
- Clear user goal: Follow the shipment and contact help
- Primary action: Contact carrier
- Navigation logic: RTL back direction is correct
- Form usability: N/A
- Scroll behavior: Single vertical scroll
- Empty/error behavior: Tracking-unavailable variant exists
- Accessibility concerns: Timeline order must be chronological in both visual and screen-reader traversal; mixed IDs/times require bidi isolation

### Native implementation usability
The RTL component model is feasible, but impossible timestamp ordering makes the image unsafe as a tracking reference.

### Reusable components identified
- RTLAppHeader
- TrackingMetaCard
- AddressCard
- RTLTrackingTimeline
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Order/status/timestamp
- Carrier and tracking ID
- Estimate and latest carrier scan
- Delivery address
- Ordered tracking events
- Carrier/support contact availability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Chronology | Current/latest event is 08:15 on 28 May, before order confirmation at 10:24, preparation at 10:30, and carrier handoff at 14:45 on the same date. | Validate monotonic event timestamps and sort from backend event time; reject impossible data before rendering. |
| MAJOR | Date accuracy | 28 May 2026 is labeled Wednesday; it is Thursday. | Generate Arabic weekday text from the timestamp. |
| MAJOR | State wording | Top status says out for delivery while the highlighted event says only en route/at distribution center. | Map Arabic labels to one canonical fulfillment state model. |

### Canonical recommendation
Replaced by a corrected Arabic tracking variant based on the French timeline structure; do not implement from this image.

---

## 07-order-tracking-timeline-realtime-fr.png

Folder: `07-orders/`  
Screen purpose: French real-time order tracking timeline  
Probable native route: `Orders/Tracking`  
Language: FR  
Screen type: Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A carrier-aware tracking screen with order metadata, sequential fulfillment stages, address, latest update, support, and order-detail link.

### Visible UI structure
- Back header, logo, notification action
- Order/tracking summary card
- Six-step timeline
- Address and delivery-estimate card
- Latest-update card
- Support and order-detail actions

### Brand validation
- Logo: Correct
- Colors: Status hierarchy is coherent
- Typography: Clear
- Icons: Consistent
- Buttons: Clear primary/secondary hierarchy
- Cards: Consistent
- Spacing: Good
- Shadows: Soft

### UX validation
- Clear user goal: Understand current shipment state
- Primary action: Support is over-emphasized relative to passive tracking, but remains understandable
- Navigation logic: Back and detail paths exist
- Form usability: N/A
- Scroll behavior: Straightforward vertical scroll
- Empty/error behavior: Tracking-unavailable screen exists
- Accessibility concerns: Timeline needs completed/current/upcoming semantics beyond color and an ordered screen-reader sequence

### Native implementation usability
Strong React Native reference using a data-driven timeline and reusable tracking cards.

### Reusable components identified
- AppHeader
- TrackingSummaryCard
- TrackingTimeline
- AddressEstimateCard
- LatestUpdateCard
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Order ID/status/date
- Carrier and tracking ID
- Estimated window
- Ordered tracking events and latest update
- Address

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Date accuracy | `Mercredi 28 mai 2026` is incorrect; it is Thursday. | Generate weekday copy from the date. |
| MINOR | Action hierarchy | `Contacter le support` is the orange primary CTA even when no issue is shown. | Make order details or passive tracking the primary emphasis; keep support secondary unless an exception exists. |

### Canonical recommendation
Used directly as the canonical French tracking reference after weekday and action-hierarchy fixes.

---

## 07-package-detail-items-shipping-info-fr.png

Folder: `07-orders/`  
Screen purpose: Show products and shipping events for one package  
Probable native route: `Orders/Packages/Detail`  
Language: FR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A package detail with seller/status, three included items, carrier/tracking information, timeline, address/instructions, and tracking/support actions.

### Visible UI structure
- Back header, logo, notification action
- Package/seller/status card
- Product rows
- Shipping metadata
- Three-event delivery timeline
- Address/instruction card
- Track and support CTAs

### Brand validation
- Logo: Correct
- Colors: Core palette is consistent; active delivery is incorrectly green
- Typography: Clear
- Icons: Coherent
- Buttons: Clear
- Cards: Consistent
- Spacing: Dense but usable
- Shadows: Soft

### UX validation
- Clear user goal: Inspect and track a single package
- Primary action: Track this package
- Navigation logic: Predictable back action
- Form usability: N/A
- Scroll behavior: Straightforward
- Empty/error behavior: Missing carrier/tracking failure state within this detail
- Accessibility concerns: Copy-tracking action needs label/confirmation; event order must be read chronologically

### Native implementation usability
Feasible with the same PackageCard and TrackingTimeline primitives used by package overview/tracking screens.

### Reusable components identified
- AppHeader
- PackageStatusCard
- OrderItemRow
- ShippingMetaRow
- TrackingTimeline
- AddressInstructionsCard
- PrimaryButton

### Dynamic backend data required
- Package ID/status/seller
- Product lines and quantities
- Shipment time, carrier, tracking ID, and estimate
- Tracking events
- Delivery address/instructions

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Status system | `En cours de livraison` uses green, conflicting with the foundation where green indicates delivered/completed. | Use the approved in-progress/out-for-delivery token and reserve green for completed delivery. |
| MAJOR | Date accuracy | 28 May 2026 is labeled Wednesday rather than Thursday. | Generate the localized weekday. |
| MINOR | Copy action | The copy icon beside tracking ID has no visible feedback. | Add a 44/48 pt hit area and accessible `Numéro copié` toast/announcement. |

### Canonical recommendation
Corrected before use; keep as the preferred single-package detail after status/date normalization.

---

## 07-rate-order-review-products-fr.png

Folder: `07-orders/`  
Screen purpose: Rate products from a delivered order  
Probable native route: `Orders/Review`  
Language: FR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A post-delivery review screen with independent five-star controls for three products and publish/later actions.

### Visible UI structure
- Logo and notification action
- Review illustration, heading, and delivery date
- Three product cards/rows with five stars each
- Review-value notice
- Publish and later buttons

### Brand validation
- Logo: Correct
- Colors: Consistent
- Typography: Clear
- Icons: Star controls are coherent
- Buttons: Strong primary/secondary hierarchy
- Cards: Consistent
- Spacing: Good
- Shadows: Soft

### UX validation
- Clear user goal: Rate delivered products
- Primary action: Publish reviews
- Navigation logic: `Plus tard` supplies an escape route, though a standard back action is absent
- Form usability: Star targets appear large enough, but zero-rating validation and written comments are missing
- Scroll behavior: Requires scroll and state preservation
- Empty/error behavior: Submit loading/error/success states are absent
- Accessibility concerns: Each star needs product-specific label/value, adjustable/selectable semantics, and focus order; CTA must not submit accidental zero ratings

### Native implementation usability
Feasible with reusable RatingInput rows, but validation and optional comment design must be resolved first.

### Reusable components identified
- ReviewHeader
- ProductReviewRow
- RatingInput
- InlineNotice
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Order delivery date
- Reviewable products and prior rating/review state
- Rating requirements and submission result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Form validation | `Publier mes avis` appears enabled while all three ratings are zero. | Disable until the required minimum is met or explicitly allow skipping unrated products and explain the rule. |
| MAJOR | Content contradiction | Copy says `Vos commentaires` matter, but there is no comment input. | Add optional comment fields via progressive disclosure or change copy to ratings only. |
| MAJOR | Feedback | No submit loading, field error, partial failure, or success state is defined. | Add per-product validation and transactional submit feedback. |

### Canonical recommendation
Corrected before use; retain the per-product rating pattern but define validation, comments, and submission states.

---

## 07-refund-completed-success-fr.png

Folder: `07-orders/`  
Screen purpose: Confirm completion of a return refund  
Probable native route: `Orders/Returns/RefundSuccess`  
Language: FR  
Screen type: Full page / Success state

### Status
REJECTED

### Confidence
High

### What the screen represents
A refund-completed result with return/order IDs, refunded amount/method, returned items, and navigation onward.

### Visible UI structure
- Logo and notification action
- Success illustration and heading
- Return/order/payment summary
- Two returned product rows
- Total-refunded row
- Orders and shopping actions

### Brand validation
- Logo: Correct
- Colors: Brand-consistent, though completed refund would benefit from semantic green
- Typography: Clear
- Icons: Consistent
- Buttons: Clear
- Cards: Consistent
- Spacing: Good
- Shadows: Soft

### UX validation
- Clear user goal: Confirm refund completion
- Primary action: Return to orders
- Navigation logic: Onward actions exist
- Form usability: N/A
- Scroll behavior: Simple vertical layout
- Empty/error behavior: N/A
- Accessibility concerns: Success should be announced and monetary relationships must be unambiguous

### Native implementation usability
The result template is feasible, but the displayed money is irreconcilable and must not guide implementation.

### Reusable components identified
- ResultIllustration
- ReturnSummaryCard
- ReturnedItemRow
- MoneySummary
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Return and order IDs
- Refund status, amount, currency, and payment destination
- Returned products, quantities, and refunded line amounts

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Arithmetic | Returned items are shown at `7 890 MAD` and `5 490 MAD` (total `13 380 MAD`), while completed refund is `4 140 MAD` with no deduction or partial-line explanation. | Render actual refunded amount per line and disclose fees/deductions; totals must reconcile exactly. |
| MAJOR | State semantics | Orange dominates a completed refund despite the foundation `Remboursé` green status. | Use the semantic refunded-success token while retaining orange only for brand accents. |

### Canonical recommendation
Replaced by a reconciled refund-success screen; do not use this image for implementation.

---

## 07-reorder-articles-changed-unavailable-fr.png

Folder: `07-orders/`  
Screen purpose: Resolve unavailable variants and price changes during reorder  
Probable native route: `Orders/Reorder/AvailabilityChanges`  
Language: FR  
Screen type: Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
An alternative reorder-changes design requiring choices for an unavailable product, replacement variant, and increased-price items.

### Visible UI structure
- Back header, logo, notification action
- Three expanded issue sections
- Product/variant replacement choices
- Old/new price rows with unlabeled checkboxes
- Update-selection and continue actions

### Brand validation
- Logo: Correct
- Colors: Consistent
- Typography: Clear
- Icons: Consistent
- Buttons: Two strong full-width choices create ambiguity
- Cards: Consistent
- Spacing: Dense
- Shadows: Soft

### UX validation
- Clear user goal: Resolve reorder changes
- Primary action: `Mettre à jour la sélection`, but selection rules are unclear
- Navigation logic: Predictable back action
- Form usability: Unlabeled checkboxes and ambiguous `Continuer` state
- Scroll behavior: Long but feasible
- Empty/error behavior: No unresolved-selection validation shown
- Accessibility concerns: Every checkbox requires a complete label and state; old/new values must be read together

### Native implementation usability
Individual resolution rows are feasible, but this variant is less usable than `07-reorder-with-availability-changes-fr.png`.

### Reusable components identified
- AppHeader
- ChangeSection
- ProductChangeRow
- Checkbox
- PriceComparison
- PrimaryButton

### Dynamic backend data required
- Original order/items/variants/prices
- Current availability, replacements, and prices
- Buyer selection and recalculated total

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Form clarity | Price-change checkboxes have no text describing whether checking accepts, removes, or selects the item. | Use labeled controls such as `Accepter le nouveau prix` and show the resulting total. |
| MAJOR | Action state | `Continuer` appears actionable while required choices are unresolved. | Disable and label the action until all required decisions are valid. |
| MINOR | Copy | `Supprimer cet article de ma commande` can imply modifying the historical order. | Say `Ne pas ajouter cet article au nouveau panier`. |

### Canonical recommendation
Kept only as an alternative; replace with `07-reorder-with-availability-changes-fr.png` and carry over explicit old/new comparison details.

---

## 07-reorder-items-added-to-cart-fr.png

Folder: `07-orders/`  
Screen purpose: Confirm which reorder items were added to cart  
Probable native route: `Orders/Reorder/Success`  
Language: FR  
Screen type: Full page / Success state

### Status
APPROVED

### Confidence
High

### What the screen represents
A reorder result showing two available items added, one unavailable item ignored, subtotal added, and cart/shopping actions.

### Visible UI structure
- Logo and notification action
- Success illustration and heading
- Source order reference
- Added/ignored counts and subtotal
- Primary cart and secondary shopping actions

### Brand validation
- Logo: Correct
- Colors: Consistent with success reinforced by check icons/text
- Typography: Clear
- Icons: Consistent
- Buttons: Correct hierarchy
- Cards: Consistent
- Spacing: Balanced
- Shadows: Soft

### UX validation
- Clear user goal: Understand the result and proceed to cart
- Primary action: `Voir mon panier`
- Navigation logic: Explicit onward actions
- Form usability: N/A
- Scroll behavior: Fits a standard phone with safe-area padding
- Empty/error behavior: N/A
- Accessibility concerns: Result heading and counts should be announced; button hit areas are ample

### Native implementation usability
Directly feasible as a data-driven result screen using reusable success and summary components.

### Reusable components identified
- ResultIllustration
- OrderReferenceCard
- ReorderResultSummary
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Source order reference
- Count of added and unavailable items
- Added-cart subtotal

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| — | — | No blocking visual, UX, or native-feasibility issue found. | Preserve responsive text wrapping and announce the success state. |

### Canonical recommendation
Used directly as the canonical reorder-success reference.

---

## 07-reorder-with-availability-changes-fr.png

Folder: `07-orders/`  
Screen purpose: Select currently available items for reorder  
Probable native route: `Orders/Reorder/AvailabilityChanges`  
Language: FR  
Screen type: Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A reorder review showing updated prices, selectable available products, an unavailable item, recalculated subtotal, and add-to-cart CTA.

### Visible UI structure
- Back header, logo, notification action
- Source order summary
- Three product rows with selection, variants, quantity, and old/new prices
- Availability-change notice
- Recalculated subtotal
- Add-available-items and cart actions

### Brand validation
- Logo: Correct
- Colors: Consistent with red unavailable and orange selection/price updates
- Typography: Clear
- Icons: Consistent
- Buttons: Clear single primary action
- Cards: Consistent
- Spacing: Dense but organized
- Shadows: Soft

### UX validation
- Clear user goal: Confirm available reorder items
- Primary action: Add selected available items
- Navigation logic: Back and cart path exist
- Form usability: Selected checkmarks and quantity steppers are clear
- Scroll behavior: Keyboard-free vertical scroll
- Empty/error behavior: Add-to-cart failure/stock-race state is not shown
- Accessibility concerns: Selection, quantity, old/new price, and unavailable state need full product-specific labels

### Native implementation usability
Strong FlatList/form reference with controlled selection, variant picker, stepper, and calculated footer.

### Reusable components identified
- AppHeader
- OrderReferenceCard
- ReorderItemRow
- VariantSelect
- QuantityStepper
- PriceComparison
- InlineWarning
- PrimaryButton

### Dynamic backend data required
- Source order metadata
- Current products, variants, stock, max quantity, and prices
- Buyer selection and recalculated subtotal
- Cart mutation result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Disabled state | The unavailable lamp still exposes a variant dropdown, which appears interactive. | Disable/remove all product controls for unavailable items and explain alternatives if supported. |
| MINOR | Localization | Prices mix no-decimal and old/new formatting. | Use one locale-aware MAD formatter. |
| MINOR | Race condition | No state covers stock/price changing again during add-to-cart. | Revalidate on submit and show an inline reconciliation state. |

### Canonical recommendation
Used directly as the canonical availability-changes reference after disabled-state and formatting fixes.

---

## 07-request-refund-cancelled-order-fr.png

Folder: `07-orders/`  
Screen purpose: Request refund for a cancelled paid order  
Probable native route: `Orders/Refund/Request`  
Language: FR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A full-refund request for one cancelled product, showing original payment, refundable amount, refund method, and estimate.

### Visible UI structure
- Back header, logo, notification action
- Refund heading
- Cancelled order card
- Affected item and price
- Payment/refundable/refund-method cards
- Estimated delay and policy notice
- Confirm-request CTA

### Brand validation
- Logo: Correct
- Colors: Consistent
- Typography: Clear
- Icons: Consistent
- Buttons: Clear
- Cards: Consistent
- Spacing: Spacious
- Shadows: Soft

### UX validation
- Clear user goal: Request the displayed refund
- Primary action: Clear
- Navigation logic: Predictable back action
- Form usability: No user input is required
- Scroll behavior: Straightforward
- Empty/error behavior: Request failure/duplicate request state is absent
- Accessibility concerns: Monetary amount/method must be announced together; CTA needs progress and idempotency

### Native implementation usability
Feasible, but only after deciding whether a separate buyer request is valid or refunds begin automatically upon cancellation.

### Reusable components identified
- AppHeader
- OrderStatusCard
- OrderItemCard
- RefundSummaryRow
- InlineNotice
- PrimaryButton

### Dynamic backend data required
- Cancelled order/item
- Original payment method
- Refundable amount and refund destination
- Refund eligibility and estimated delay
- Existing refund-request state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Product decision | The buyer must manually request a refund after a paid order is cancelled, while other screens imply cancellation automatically starts refund review. | NEEDS PRODUCT DECISION: define one cancellation-to-refund workflow and remove duplicate/manual steps if automatic. |
| MAJOR | Idempotency | No state prevents a duplicate refund request. | Hide/disable CTA when a request exists and handle repeat submissions safely. |

### Canonical recommendation
Corrected before use; retain only if product policy explicitly requires a separate refund request.

---

## 07-request-return-item-selection-fr.png

Folder: `07-orders/`  
Screen purpose: Select delivered items and reason for a return request  
Probable native route: `Orders/Returns/Request`  
Language: FR  
Screen type: Full page

### Status
REJECTED

### Confidence
High

### What the screen represents
A return form with product selection/quantity, reason choices, optional note, policy notice, and submission.

### Visible UI structure
- Back header, logo, notification action
- Delivered order summary
- Three product selection/quantity rows
- Return reason radio controls
- Optional note field
- Seven-day policy notice
- Submit CTA

### Brand validation
- Logo: Correct
- Colors: Consistent
- Typography: Clear
- Icons: Mostly coherent
- Buttons: Clear primary action
- Cards: Consistent
- Spacing: Dense but usable
- Shadows: Soft

### UX validation
- Clear user goal: Select items and submit a return
- Primary action: Clear
- Navigation logic: Predictable back action
- Form usability: Checkbox plus quantity creates redundant state; damaged-item evidence is absent
- Scroll behavior: Must be keyboard-aware and preserve selections
- Empty/error behavior: Validation/submit failure states are absent
- Accessibility concerns: Each checkbox/stepper must be labeled by product; radio group and text field need error focus behavior

### Native implementation usability
The form components are feasible, but wrong product imagery and undefined damaged-return evidence make the screenshot unsafe.

### Reusable components identified
- AppHeader
- OrderStatusCard
- ReturnItemRow
- QuantityStepper
- RadioOptionGroup
- FormTextArea
- PolicyNotice
- PrimaryButton

### Dynamic backend data required
- Delivered order/items/images/quantities
- Returnable quantity and eligibility deadline
- Return reasons and evidence requirements
- Note and submission result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Content accuracy | `Suspension Lune Noire` displays an armchair image. | Bind each row to the authoritative SKU/media record and add visual/data QA. |
| MAJOR | Form model | Rows use both a checkbox and quantity `0/1`, creating two sources of selection truth. | Let quantity >0 determine selection or use one checkbox that reveals/enables quantity. |
| MAJOR | Product decision | `Produit endommagé` can be submitted without photos/evidence. | NEEDS PRODUCT DECISION: define evidence requirements and add attachment capture if required. |
| MINOR | Validation | Required selections/reason and submit errors are not shown. | Add inline validation and disable submit until valid. |

### Canonical recommendation
Replaced by a corrected return-request screen with accurate SKU imagery and one coherent selection model.

---

## 07-return-detail-items-refund-status-fr.png

Folder: `07-orders/`  
Screen purpose: View return items, reason, method, and estimated refund  
Probable native route: `Orders/Returns/Detail`  
Language: FR  
Screen type: Full page

### Status
APPROVED

### Confidence
High

### What the screen represents
A return detail under review with two correctly matched items, return reason, original payment method, reconciled `4 140 MAD` estimate, tracking, and support.

### Visible UI structure
- Back header, logo, notification action
- Return/order/status summary
- Two returned item rows
- Return-reason card
- Refund-method card
- Estimated refund amount
- Track and support CTAs

### Brand validation
- Logo: Correct
- Colors: Consistent
- Typography: Clear
- Icons: Consistent
- Buttons: Clear hierarchy
- Cards: Consistent
- Spacing: Balanced
- Shadows: Soft

### UX validation
- Clear user goal: Review return and refund details
- Primary action: Track the request
- Navigation logic: Back and support available
- Form usability: N/A
- Scroll behavior: Straightforward
- Empty/error behavior: N/A; tracking state exists separately
- Accessibility concerns: Status and amount include text; item and price reading order is clear

### Native implementation usability
Directly feasible using the shared return-detail and item-row components. Visible item amounts reconcile exactly with the estimated refund.

### Reusable components identified
- AppHeader
- ReturnMetaCard
- ReturnedItemRow
- ReturnReasonCard
- RefundMethodCard
- MoneySummary
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Return and order IDs/status
- Returned products, SKUs, quantities, images, and refunded line amounts
- Return reason/detail
- Refund destination and estimated amount

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| — | — | No blocking visual, UX, data, or native-feasibility issue found. | Preserve dynamic text wrapping and authoritative monetary reconciliation. |

### Canonical recommendation
Used directly as the canonical return-detail reference.

---

## 07-return-tracking-timeline-fr.png

Folder: `07-orders/`  
Screen purpose: Track return processing and refund progress  
Probable native route: `Orders/Returns/Tracking`  
Language: FR  
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A return/refund timeline from request submission through approval, receipt, quality control, refund initiation, and completion.

### Visible UI structure
- Logo and notification action
- Return tracking heading
- Return/order/current-status summary
- Six-stage vertical timeline
- Estimated refund date notice
- Support and return-detail actions

### Brand validation
- Logo: Correct
- Colors: Base palette is consistent; current refund status semantics conflict
- Typography: Clear
- Icons: Consistent
- Buttons: Clear
- Cards: Consistent
- Spacing: Good
- Shadows: Soft

### UX validation
- Clear user goal: Understand return/refund progress
- Primary action: Return details
- Navigation logic: No back action
- Form usability: N/A
- Scroll behavior: Straightforward
- Empty/error behavior: No delayed/failed-quality/refund state
- Accessibility concerns: Timeline needs completed/current/upcoming semantics and chronological reading order

### Native implementation usability
Feasible with the shared TrackingTimeline, but state naming and dates must be corrected.

### Reusable components identified
- AppHeader
- ReturnMetaCard
- TrackingTimeline
- EstimateNotice
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Return/order IDs
- Aggregate return/refund status
- Ordered processing events
- Quality-control estimate
- Estimated/actual refund date

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | State contradiction | Header says `Remboursement en cours` while the timeline says `Remboursement initié — En attente`; only quality control is active. | Label aggregate state `Retour en cours` or `Contrôle qualité en cours` until refund initiation. |
| MAJOR | Navigation | No back arrow appears on a nested tracking route. | Add the standard back header. |
| MAJOR | Date accuracy | `Mercredi 28 mai 2026` is incorrect; it is Thursday. | Derive weekday text from the estimate date. |

### Canonical recommendation
Corrected before use; use the French order-tracking timeline component with return-specific stages and precise aggregate status.

---

## 07-support-order-contact-form-fr.png

Folder: `07-orders/`  
Screen purpose: Contact support about a selected order  
Probable native route: `Orders/Support`  
Language: FR  
Screen type: Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
An order-linked support form with subject, required message, optional attachments, submission, alternate contacts, and FAQ.

### Visible UI structure
- Back header, logo, notification action
- Support illustration/title
- Selected order/status card
- Subject dropdown and 1000-character message field
- File attachment area
- Submit CTA
- Email/phone alternatives and FAQ link

### Brand validation
- Logo: Correct
- Colors: Consistent
- Typography: Clear
- Icons: Consistent
- Buttons: Clear single primary action
- Cards: Consistent
- Spacing: Good
- Shadows: Soft

### UX validation
- Clear user goal: Send an order-specific support request
- Primary action: `Envoyer la demande`
- Navigation logic: Predictable back action and alternate help routes
- Form usability: Visible required labels, character count, accepted files, and size limit
- Scroll behavior: Must be keyboard-aware and preserve draft/attachments
- Empty/error behavior: Validation/upload/submit states are not shown
- Accessibility concerns: Dropdown, attachment picker, errors, progress, and file removal need accessible labels and focus management

### Native implementation usability
Strong React Native form reference using controlled inputs, document/image picker, multipart upload, and keyboard-aware scrolling.

### Reusable components identified
- AppHeader
- SupportIllustration
- OrderStatusCard
- SelectInput
- FormTextArea
- AttachmentPicker
- InlineNotice
- PrimaryButton
- ContactMethodsCard

### Dynamic backend data required
- Selected order ID/date/status
- Support subject options
- Message and attachments
- File type/size rules
- Support email, phone, hours, and FAQ URL
- Upload/submission status

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Form feedback | No inline validation, upload progress, file removal, submit loading, or failure state is shown. | Define all form/upload states and focus the first invalid field. |
| MINOR | Product data | Email, phone number, office hours, and file limits appear fixed in the image. | Source support contacts and upload rules from approved configuration; treat shown values as mock content. |
| MINOR | Draft safety | A long message/attachments can be lost on accidental back. | Preserve a draft or confirm dismissal when unsaved content exists. |

### Canonical recommendation
Used directly as the canonical order-support form after adding form/upload feedback and configurable contacts.

---

## 07-tracking-unavailable-in-preparation-fr.png

Folder: `07-orders/`  
Screen purpose: Explain that tracking is unavailable before shipment  
Probable native route: `Orders/TrackingUnavailable`  
Language: FR  
Screen type: Full page / Empty state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A pre-shipment tracking-unavailable state with order summary, email expectation, refresh, and order-detail action.

### Visible UI structure
- Logo and notification action
- Tracking-unavailable heading and illustration
- Order/product/total summary card
- Explanatory notice
- Refresh and detail actions

### Brand validation
- Logo: Correct but large
- Colors: Consistent
- Typography: Clear
- Icons: Coherent
- Buttons: Clear
- Cards: Consistent
- Spacing: Poster-like
- Shadows: Soft

### UX validation
- Clear user goal: Understand why tracking is absent
- Primary action: Refresh
- Navigation logic: No back action on a nested route
- Form usability: N/A
- Scroll behavior: Single-screen state
- Empty/error behavior: Explanation and alternative are present
- Accessibility concerns: Refresh requires progress/disabled feedback; the status update should be announced

### Native implementation usability
Feasible as a tracking-state component, but it should remain within the standard nested-route header and use live shipment status.

### Reusable components identified
- AppHeader
- StateIllustration
- OrderSummaryCard
- InlineNotice
- RetryButton
- SecondaryButton

### Dynamic backend data required
- Order ID/status/item count/thumbnail/total
- Tracking availability
- Email notification preference/status

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | No back action is visible. | Add the standard nested-route back header. |
| MAJOR | Loading feedback | `Actualiser` provides no progress, disabled, timeout, or unchanged-state feedback. | Show loading and an accessible `Toujours en préparation` result when state is unchanged. |
| MINOR | Summary clarity | One product thumbnail is paired with `3 articles` and the entire total, which can imply the sofa alone costs `14 270 MAD`. | Use a thumbnail strip or label the block clearly as an order summary. |

### Canonical recommendation
Corrected before use; retain as the canonical tracking-unavailable state after navigation and refresh feedback fixes.

---

## Duplicate group: Reorder availability changes

Files:
- `07-reorder-articles-changed-unavailable-fr.png`
- `07-reorder-with-availability-changes-fr.png`

Recommended canonical file:  
`07-reorder-with-availability-changes-fr.png`

Reason:  
It presents current availability, selected items, quantity, old/new pricing, and the recalculated subtotal in one coherent form. The alternative uses unlabeled checkboxes and an ambiguous `Continuer` action, so it is not safe as a full implementation reference.

Useful elements from rejected alternatives:
- The explicit `Ancienne → Nouvelle` replacement-variant comparison from `07-reorder-articles-changed-unavailable-fr.png`
- The grouped issue headings for unavailable product, unavailable variant, and price update

## Direct variant comparisons

- Orders list FR/AR: `07-orders-list-all-tabs-fr.png` has the strongest route shell, but its Panier/Favoris order must be fixed. `07-orders-list-all-tabs-ar.png` is a useful RTL layout counterpart, but its bottom navigation is seller-like and must be replaced entirely.
- Tracking FR/AR: `07-order-tracking-timeline-realtime-fr.png` is the structural canonical. `07-order-tracking-timeline-ar.png` mirrors direction correctly but is rejected because its timestamps are impossible.
- Order detail states: `07-order-detail-in-preparation-timeline-fr.png` is the strongest shared structural base; delivered and shipped states should reuse it. `07-order-detail-ar.png` is rejected because totals, payment method/status, date, and RTL back direction conflict.
- List states: loading, empty, and error should remain states inside the corrected `07-orders-list-all-tabs-fr.png` shell, not replace the navigation context.
- Package states: `07-multiple-packages-split-shipment-fr.png` is the package-overview canonical after correction; `07-package-detail-items-shipping-info-fr.png` is the nested single-package detail; `07-order-detail-multi-vendor-packages-fr.png` supplies seller grouping but requires data/state repair.

## Folder-level conclusion

The orders set supplies a broad and mostly feasible React Native component inventory, but only two screenshots are safe without correction. The best foundations are the in-preparation detail, delivered detail, French tracking timeline, completed list, reorder selection/success, return detail, and support form. Before development, normalize the buyer bottom navigation, generate all day names from timestamps, enforce monotonic event data and reconciled money, define cancellation/refund/return state machines, and centralize status-badge tokens.
