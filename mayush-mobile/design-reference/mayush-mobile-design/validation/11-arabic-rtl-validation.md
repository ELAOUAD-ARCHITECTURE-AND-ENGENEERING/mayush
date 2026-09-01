# 11 — Arabic RTL validation

> **Fact-check scope:** Currency and address examples are accepted variations. Do not treat them as validation defects by themselves; [fact-check-correction.md](./fact-check-correction.md) supersedes earlier currency/address severity notes.

Scope: all 46 PNG files in `11-arabic-rtl/`, visually validated against `00-foundation/`, `assetsl/` (the project has no `12-assetsl/` directory), and the mission's buyer-only mobile rules. No screenshot was edited, renamed, deleted or regenerated.

## Extracted validation rules

- Preserve the official multicolor `MAYUSH DESIGN` logo without distortion or added `BUYER APP` / `تطبيق المشتري` wording.
- Use the mission-authorized warm cream, Mayush orange near `#D97434`, deep navy, white and soft beige; semantic green/red/amber may supplement but not replace the brand palette.
- Use Tajawal or Cairo for Arabic UI, support Dynamic Type, keep Arabic UI labels free of accidental French/English, and isolate LTR email, phone, order ID, version, time, number and `MAD` runs.
- Mirror the shell, not just the text: RTL back is on the right and points right; forward row/CTA chevrons are on the left and point left; logical screen-reader order follows the visual RTL order.
- The five buyer tabs remain `Accueil`, `Catégories`, `Favoris`, `Panier`, `Compte`; in RTL the visual order from right is Home, Categories, Favourites, Cart, Account. Labels, active treatment and icon family must be identical across routes.
- Use only `MAD`; Morocco is the default country and visible phone numbers use `+212`.
- Native controls need at least 44×44 pt / 48×48 dp, an 8 dp spacing rhythm, visible press/disabled/loading states, accessible names and focus order.
- Forms require persistent labels, semantic keyboards, keyboard avoidance, inline errors and scrollable content. Fixed tab/composer/CTA bars reserve safe-area and keyboard insets.
- Use reusable React Native cards, lists, fields, badges, dialogs and state views; decorative art must not carry required text or replace a real progress/control state.
- Unproven payment, wallet, session, consent, update, notification and support behavior is marked **NEEDS PRODUCT DECISION**.

The `ui-ux-pro-max` mobile checklist materially informed the target-size, Dynamic Type, native-control, safe-area, keyboard, destructive-action and accessibility findings. Its generic palette suggestion was not used because the supplied Mayush references are authoritative.

## 11-access-denied-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Explain that a protected buyer destination cannot be accessed.  
Probable native route: `/error/access-denied`  
Language: Arabic  
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A blocking authorization error with support and home recovery actions.

### Visible UI structure
- Header: notification, centered logo
- Main content: oversized denial illustration, title and explanation
- Cards: none
- Forms: none
- Primary action: contact support
- Secondary actions: return home
- Navigation: no bottom bar
- Overlays: none

### Brand validation
The palette, logo artwork and rounded orange CTA fit Mayush, but `تطبيق المشتري` creates the prohibited buyer sub-brand.

### UX validation
The error is understandable, but the reason is ambiguous and the home arrow is not mirrored. **NEEDS PRODUCT DECISION:** whether the denial is permission, account, seller-only or policy based.

### Native implementation usability
Feasible as a reusable error-state ScrollView, with the support destination and denied-resource copy provided at runtime.

### Reusable components identified
- AppHeader
- SystemStateIllustration
- PrimaryButton
- TextLink

### Dynamic backend data required
- Denial reason
- Support availability/destination
- Safe return route

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | `تطبيق المشتري` adds prohibited buyer wording to the official logo. | Use the unmodified official `MAYUSH DESIGN` lockup only. |
| MAJOR | RTL/navigation | The return-home arrow points left. | Put a right-pointing RTL back/return arrow on the logical leading side. |
| MINOR | UX | The illustration dominates the viewport and the denial reason is generic. | Reduce the art and render a product-approved, contextual reason. |

### Canonical recommendation
Correct before use; retain the recovery hierarchy, not the logo lockup or arrow treatment.

## 11-account-dashboard-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Buyer account overview and settings shortcuts.  
Probable native route: `/(tabs)/account`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
The Account tab with profile identity and routes to orders, addresses, security, notifications, language and help.

### Visible UI structure
- Header: centered logo, notification
- Main content: account heading and profile card
- Cards: profile and shortcut list
- Forms: none
- Primary action: shortcut rows
- Secondary actions: edit profile
- Navigation: five-tab bottom bar, Account active
- Overlays: none

### Brand validation
Official logo, cream/navy/orange palette, white cards and coherent outline icons are consistent.

### UX validation
Row icons and left-pointing forward chevrons are correctly mirrored, but the shared tab sequence is wrong and the shell omits a clear status/safe-area treatment. The email requires bidi isolation.

### Native implementation usability
Straightforward with a virtualized settings list and reusable profile header; the tab navigator must own order and selected state.

### Reusable components identified
- AppHeader
- ProfileSummaryCard
- SettingsNavigationRow
- BuyerTabBar

### Dynamic backend data required
- Buyer name
- Email
- Avatar
- Notification unread state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Cart and Favourites are swapped in the RTL five-tab order. | Render logical RTL order Home, Categories, Favourites, Cart, Account from the right. |
| MAJOR | Safe area | The header has no visible status-bar/safe-area contract. | Implement a shared SafeAreaView header and test iOS/Android insets. |
| MINOR | Bidi | The email is not visibly isolated as an LTR run. | Apply Unicode bidi isolation and `writingDirection: 'ltr'` to the address value. |
| MINOR | Accessibility | The lone pencil action has no visible label or described purpose. | Give it a 48 dp target and `accessibilityLabel="Edit profile"`. |

### Canonical recommendation
Correct before use; it is the only Arabic Account overview and should become canonical after shell normalization.

## 11-active-sessions-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Review and revoke signed-in devices.  
Probable native route: `/account/security/sessions`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A session-management list identifying the current device and other recent devices.

### Visible UI structure
- Header: RTL back, centered logo, notification
- Main content: title, explanatory text, device cards, security note
- Cards: three device sessions
- Forms: none
- Primary action: remove device
- Secondary actions: back
- Navigation: five-tab bar, Account active
- Overlays: none

### Brand validation
Logo, card treatment and palette are consistent; destructive actions are incorrectly orange instead of semantic red.

### UX validation
Locations and relative activity are legible, but the current device can apparently be “removed” without explaining whether that signs out this device. **NEEDS PRODUCT DECISION:** revoke semantics and confirmation/authentication.

### Native implementation usability
Feasible with a FlatList and session cards; device icons, current badge, pending revoke and error states must be data-driven.

### Reusable components identified
- AppHeader
- DeviceSessionCard
- StatusBadge
- DestructiveButton
- BuyerTabBar

### Dynamic backend data required
- Session ID/device name/type
- Current-session flag
- Last activity
- Approximate location

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Cart and Favourites are swapped in the shared RTL bar. | Normalize the tab navigator once for every Arabic route. |
| MAJOR | Destructive UX | Device removal uses brand orange and includes the current device without clear logout semantics. | Use semantic red, confirm the target, and distinguish “sign out this device” from “revoke another device.” |
| MINOR | Accessibility | Repeated remove buttons need device-specific accessible names. | Announce “Sign out [device name]” and expose current-device state. |
| MINOR | Bidi | Latin device names are embedded without an explicit isolation strategy. | Isolate device-name runs while keeping Arabic metadata RTL. |

### Canonical recommendation
Correct before use; retain the card hierarchy but not the destructive action or tab implementation.

## 11-add-address-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Create a Moroccan delivery address.  
Probable native route: `/account/addresses/new`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A five-field form for recipient name, phone, street address, city and postal code.

### Visible UI structure
- Header: LTR-positioned back and notification around logo
- Main content: large address illustration and heading
- Cards: none
- Forms: five outlined inputs
- Primary action: save address
- Secondary actions: back
- Navigation: no bottom bar
- Overlays: none

### Brand validation
Colors, official logo, rounded inputs and line icons fit the system; the decorative hero is disproportionately tall.

### UX validation
The header is not mirrored, fields have placeholder-only labels, and the full-height composition will place lower fields/CTA behind the keyboard. The phone field does not visibly establish `+212`.

### Native implementation usability
Feasible only as a keyboard-aware ScrollView with persistent labels, validation, Moroccan phone keyboard/prefix and address autocomplete or product-approved free text.

### Reusable components identified
- AppHeader
- FormInput
- PhoneInput
- CitySelector
- PrimaryButton

### Dynamic backend data required
- Address validation rules
- Supported Moroccan cities/zones
- Postal-code requirement

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | RTL/header | Back is on the left pointing left and notification is on the right. | Mirror the header: right-pointing back on the right, notification on the left. |
| MAJOR | Form usability | Placeholder-only fields plus the large hero do not survive keyboard or error states. | Add persistent labels/errors and keyboard-aware scrolling; reduce/collapse the hero. |
| MINOR | Morocco context | The phone field does not show the required `+212` prefix. | Use a locked Morocco prefix and phone keypad. |
| MINOR | Icon semantics | An envelope represents postal code. | Use a postal/location icon or no decorative input icon. |

### Canonical recommendation
Correct before use; combine its field set with the stronger data formatting shown in `11-edit-address-ar.png`.

## 11-addresses-list-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: List and manage saved delivery addresses.  
Probable native route: `/account/addresses`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Saved Home and Work address cards with default, edit, delete and add actions.

### Visible UI structure
- Header: unmirrored back/notification
- Main content: title and address cards
- Cards: two saved addresses
- Forms: none
- Primary action: add address
- Secondary actions: edit and delete
- Navigation: five-tab bar, Account active
- Overlays: none

### Brand validation
Official logo, palette, badge, cards and icons are consistent.

### UX validation
Address content, `+212` phones and left-side management actions read well in RTL, but the header and tab bar remain LTR-positioned.

### Native implementation usability
Feasible as a FlatList of address cards with a dialog for deletion and optimistic/default update feedback.

### Reusable components identified
- AppHeader
- AddressCard
- StatusBadge
- IconButton
- PrimaryButton
- BuyerTabBar

### Dynamic backend data required
- Address IDs/labels/recipient
- Address lines/city/postal code/country
- Phone
- Default flag

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | RTL/navigation | Header and bottom tabs are not mirrored; Cart/Favourites also conflict with the approved order. | Use the shared RTL header and canonical five-tab sequence. |
| MINOR | Accessibility | Pencil/trash icons rely on icon meaning alone. | Add address-specific labels, 48 dp targets and destructive confirmation. |

### Canonical recommendation
Correct before use; this is the preferred Arabic saved-address list after shell normalization.

## 11-cancellation-submitted-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Confirm that an order cancellation request was submitted.  
Probable native route: `/orders/:orderId/cancel/submitted`  
Language: Arabic  
Screen type:
- Success state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A cancellation-request receipt with review status, refund caveat, next steps, item summary and onward actions.

### Visible UI structure
- Header: centered logo and notification
- Main content: success art, explanatory status and order metadata
- Cards: cancellation status, refund note, next steps, item summary
- Forms: none
- Primary action: view orders
- Secondary actions: continue shopping
- Navigation: five-tab bar, Account active
- Overlays: none

### Brand validation
Palette/cards are consistent, but a green success check combined with an orange cancellation cross sends mixed status semantics.

### UX validation
The page is unusually dense for a submitted state, CTA chevrons are unmirrored, and the permanent Account tab has the wrong sequence. Review/refund timing is not guaranteed and needs product-approved copy.

### Native implementation usability
Feasible as a ScrollView backed by cancellation state and order data; refund copy and status transitions must come from the order domain.

### Reusable components identified
- AppHeader
- SystemStateIllustration
- OrderStatusBadge
- InfoCallout
- OrderItemSummary
- BuyerTabBar

### Dynamic backend data required
- Order ID/date/items/prices
- Cancellation request status
- Refund state
- Review expectation/contact channel

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Status semantics | Green completion and cancellation/error symbols conflict while the request is only under review. | Use one pending-review semantic treatment and reserve success for accepted cancellation. |
| MAJOR | RTL/navigation | Both CTA chevrons point right and the tab order is wrong. | Move forward chevrons left pointing left and normalize the RTL tabs. |
| MAJOR | Product logic | Review/refund wording implies behavior not evidenced by the project. | **NEEDS PRODUCT DECISION:** bind approved statuses, SLA and refund language to domain rules. |
| MINOR | Density | The success state resembles a static receipt poster. | Prioritize outcome/actions and collapse the item summary behind “View order.” |

### Canonical recommendation
Correct before use; retain only the request-status and refund-caveat components.

## 11-cart-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Review cart items and proceed to checkout.  
Probable native route: `/(tabs)/cart`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A three-item cart with stock status, quantity controls, totals and checkout CTA.

### Visible UI structure
- Header: correctly mirrored RTL back/notification
- Main content: title, item list and totals
- Cards: cart-items card and totals card
- Forms: quantity steppers
- Primary action: continue to payment
- Secondary actions: remove item
- Navigation: five-tab bar, Cart active
- Overlays: none

### Brand validation
Official logo, images, palette, cards and `MAD` pricing are consistent.

### UX validation
The goal and totals are clear; however the checkout chevron and tab sequence are wrong. Mixed-language product names need bidi isolation, and quantity/removal changes need pending/error feedback.

### Native implementation usability
Feasible with a FlatList, QuantityStepper, price summary and sticky checkout area with bottom inset.

### Reusable components identified
- AppHeader
- CartItemRow
- QuantityStepper
- PriceSummary
- PrimaryButton
- BuyerTabBar

### Dynamic backend data required
- Cart item/product/variant/image
- Unit price/quantity/stock
- Subtotal/delivery/discount/total

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | RTL/navigation | Checkout chevron points right and Cart/Favourites are swapped in the tab bar. | Point forward CTA left and use canonical RTL tab ordering. |
| MAJOR | State handling | Quantity/remove controls show no disabled, updating, max-stock or failure state. | Add optimistic state with rollback, loading and accessible announcements. |
| MINOR | Bidi/copy | Latin product fragments are not isolated within Arabic labels. | Isolate dynamic product-name runs and test long localized names. |

### Canonical recommendation
Correct before use; use as the Arabic cart composition after navigation/state fixes.

## 11-categories-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Browse furniture categories.  
Probable native route: `/(tabs)/categories`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A searchable two-column category grid for furniture, lighting, tables, decor, bedrooms, offices and outdoors.

### Visible UI structure
- Header: logo and notification
- Main content: title, search and category grid
- Cards: eight category cards
- Forms: search field
- Primary action: category cards
- Secondary actions: search
- Navigation: five-tab bar, Categories active
- Overlays: none

### Brand validation
Furniture imagery, official logo, palette, cards and outline icons align with Mayush.

### UX validation
Card chevrons correctly point left, but the notification/search adornment and bottom tabs are not correctly mirrored. The placeholder begins with the spelling `إبحث` rather than `ابحث`.

### Native implementation usability
Feasible with a two-column FlatList and responsive card content; long category names need flexible height.

### Reusable components identified
- AppHeader
- SearchField
- CategoryCard
- BuyerTabBar

### Dynamic backend data required
- Category ID/name/image/icon
- Category result counts/search results

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | RTL/navigation | Search icon is on the left, notification is on the right, and the tabs are unmirrored with Cart/Favourites swapped. | Apply one RTL shell and place the search leading icon on the right. |
| MAJOR | Layout | Card copy and imagery are locked to a poster-like split that may fail with long names. | Use flexible grid cards with measured text and Dynamic Type tests. |
| MINOR | Arabic copy | `إبحث عن تصنيف` is orthographically inconsistent. | Use `ابحث عن تصنيف` after Arabic copy review. |

### Canonical recommendation
Correct before use; it remains the Arabic category-grid candidate.

## 11-change-email-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Change the buyer account email.  
Probable native route: `/account/security/email`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A security form showing masked current email and collecting a new email plus optional password confirmation.

### Visible UI structure
- Header: unmirrored back/notification
- Main content: security illustration, title, current email and notice
- Cards: current-email and information callouts
- Forms: new email and password
- Primary action: continue
- Secondary actions: back
- Navigation: no bottom bar
- Overlays: none

### Brand validation
Palette, official logo and control shapes are consistent; the decorative security hero is oversized.

### UX validation
Field adornments are reversed for RTL, CTA chevron is wrong, and keyboard space is not protected. **NEEDS PRODUCT DECISION:** whether current password, re-authentication and email verification are mandatory.

### Native implementation usability
Feasible with a keyboard-aware security form, LTR email entry, validation and a separate verification state.

### Reusable components identified
- AppHeader
- SecurityHero
- FormInput
- PasswordInput
- InfoCallout
- PrimaryButton

### Dynamic backend data required
- Masked current email
- Re-authentication requirement
- Verification status/destination

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | RTL/header | Back and notification remain in LTR positions. | Put a right-pointing back action on the right and notification on the left. |
| MAJOR | RTL/forms | Email/lock icons and password visibility affordance are reversed. | Place leading icons right, trailing visibility control left, preserving LTR email value entry. |
| MAJOR | Security flow | Password is marked optional without an evidenced re-authentication policy. | **NEEDS PRODUCT DECISION:** define re-auth and verification before implementation. |
| MINOR | Keyboard/layout | The large hero risks hiding the CTA and focused fields. | Reduce the hero and use keyboard-aware scroll-to-field behavior. |

### Canonical recommendation
Correct before use; do not implement the security sequence until the product decision is resolved.

## 11-change-password-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Change the current account password.  
Probable native route: `/account/security/password`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A three-password security form for current password, new password and confirmation.

### Visible UI structure
- Header: unmirrored back/notification and logo with buyer sub-label
- Main content: large security illustration and guidance
- Cards: none
- Forms: three password inputs
- Primary action: continue
- Secondary actions: back
- Navigation: no bottom bar
- Overlays: none

### Brand validation
Controls, colors and official logo artwork match the boards, but `تطبيق المشتري` violates the no-extra-branding rule.

### UX validation
Input lock/visibility placement is correctly RTL, but header and CTA chevron are not. Password rules, strength/error state and keyboard avoidance are absent.

### Native implementation usability
Feasible with reusable secure inputs and server validation; require scroll-to-error, strength guidance and submission progress.

### Reusable components identified
- AppHeader
- SecurityHero
- PasswordInput
- PasswordRequirements
- PrimaryButton

### Dynamic backend data required
- Password policy
- Re-authentication result
- Change submission/result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | `تطبيق المشتري` adds prohibited wording below the logo. | Use only the official logo. |
| MAJOR | RTL/navigation | Header and continue chevron remain LTR. | Mirror header placement and use a left-pointing forward chevron. |
| MAJOR | Form usability | No rules, inline validation, mismatch, strength or submission states are shown. | Add accessible password requirements and live/error/loading states. |
| MINOR | Keyboard/layout | Oversized art leaves insufficient keyboard-safe space. | Collapse decorative content while editing and use a KeyboardAvoidingView/ScrollView. |

### Canonical recommendation
Correct before use; the three-field sequence is reusable after branding and form-state fixes.

## 11-change-phone-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Change the account phone number.  
Probable native route: `/account/security/phone`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A phone-change form showing the current `+212` number, collecting a new number and an optional verification code.

### Visible UI structure
- Header: unmirrored back/notification
- Main content: security hero, title, current phone and verification notice
- Cards: current phone and notice
- Forms: new phone and code
- Primary action: continue
- Secondary actions: back
- Navigation: no bottom bar
- Overlays: none

### Brand validation
Logo, palette, cards and icons are consistent; hero scale is excessive.

### UX validation
The current number correctly uses `+212`, but field icons are reversed and the screen asks for a code before showing a send-code step. **NEEDS PRODUCT DECISION:** OTP sequence, retry and old-number re-authentication.

### Native implementation usability
Feasible as two explicit states (enter phone, verify OTP), not as this ambiguous single form.

### Reusable components identified
- AppHeader
- PhoneInput
- OtpInput
- InfoCallout
- PrimaryButton

### Dynamic backend data required
- Current masked phone
- OTP delivery/expiry/retry state
- Verification result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | RTL/header | Back/notification positions are LTR. | Mirror the shared header. |
| MAJOR | RTL/forms | Phone adornments sit on the left instead of the RTL leading edge. | Put the phone icon/prefix at the right and isolate digits LTR. |
| MAJOR | Product flow | “Optional” verification code appears before any send-code action. | **NEEDS PRODUCT DECISION:** split phone entry and OTP verification with resend/expiry/error states. |
| MINOR | Keyboard/layout | Lower controls and CTA are vulnerable to keyboard overlap. | Use a keyboard-aware scroll layout and compact hero. |

### Canonical recommendation
Correct before use; use only as a visual basis for a two-step verified phone flow.

## 11-checkout-address-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Select a saved delivery address during checkout.  
Probable native route: `/checkout/address`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A checkout step listing one selected and three alternative Moroccan addresses before payment.

### Visible UI structure
- Header: correctly mirrored RTL back/notification
- Main content: title and address selection
- Cards: four saved address cards
- Forms: radio selection
- Primary action: continue to payment
- Secondary actions: edit address
- Navigation: five-tab bar, Account active
- Overlays: none

### Brand validation
Palette, logo, address cards and selection treatment fit Mayush.

### UX validation
The address order and `+212` values are readable, but checkout should not expose an Account-active permanent tab bar. CTA chevron is unmirrored and no add-address path is visible.

### Native implementation usability
Feasible with selectable address cards in a FlatList and a sticky checkout CTA, provided tab navigation is removed and selection state is transactional.

### Reusable components identified
- AppHeader
- SelectableAddressCard
- RadioControl
- PrimaryButton

### Dynamic backend data required
- Saved addresses
- Selected address ID
- Address serviceability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Checkout navigation | Permanent tabs remain visible and Account is active during a checkout step. | Use a focused checkout stack without bottom tabs. |
| MAJOR | RTL/action | The forward payment chevron points right. | Put it on the left and point it left. |
| MAJOR | Flow completeness | No add-address action or serviceability error is available. | Add “new address” and unavailable-zone states before advancing. |
| MINOR | Safe area | Sticky CTA and tab bar compete for bottom space. | Use one sticky CTA with keyboard/safe-area inset. |

### Canonical recommendation
Correct before use; retain the address cards and remove the tab shell.

## 11-connection-restored-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Confirm connectivity recovery.  
Probable native route: `/system/connection-restored`  
Language: Arabic  
Screen type:
- Success state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A full-page success state shown after network connectivity returns.

### Visible UI structure
- Header: large logo with buyer sub-label
- Main content: success illustration, title and connection message
- Cards: none
- Forms: none
- Primary action: continue
- Secondary actions: return home
- Navigation: no bottom bar
- Overlays: none

### Brand validation
The palette and logo artwork fit Mayush; `تطبيق المشتري` is prohibited extra branding.

### UX validation
A transient recovered connection normally needs a banner/automatic continuation rather than a blocking page. The copy also claims the buyer is connected to an account, which a network check does not establish, and the return arrow is wrong.

### Native implementation usability
Implement as a reusable connectivity banner or automatically dismissed state, not a hard-coded full-page poster.

### Reusable components identified
- ConnectivityBanner
- SystemStateIllustration
- PrimaryButton

### Dynamic backend data required
- Connectivity state
- Interrupted destination/retry result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | `تطبيق المشتري` modifies the official lockup. | Remove the sub-label. |
| MAJOR | UX pattern | Connectivity recovery blocks the journey with an unnecessary full page. | Auto-resume and announce recovery through a banner/snackbar; use a page only after a failed critical transaction. |
| MAJOR | Logic | “Connected to your account” is not proven by network restoration. | State only that connectivity returned and separately verify/authenticate account state. |
| MINOR | RTL | The home-return arrow points left. | Use the correct right-pointing return/back affordance. |

### Canonical recommendation
Correct and convert to a transient component before use; do not use the full page directly.

## 11-delete-address-confirm-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Confirm deletion of a saved address.  
Probable native route: `/account/addresses/:addressId/delete`  
Language: Arabic  
Screen type:
- Dialog

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A destructive confirmation showing the address being removed and Delete/Cancel actions.

### Visible UI structure
- Header: correctly mirrored RTL back/notification
- Main content: warning art and confirmation copy
- Cards: target address
- Forms: none
- Primary action: delete
- Secondary actions: cancel
- Navigation: no bottom bar
- Overlays: visually presented as a full page, despite dialog intent

### Brand validation
The logo and palette are consistent, but the destructive CTA uses brand orange instead of semantic red.

### UX validation
The target is clear and Cancel is available; however a full-page hero is excessive for confirmation and increases accidental navigation/context loss.

### Native implementation usability
Use a native accessible Alert or compact modal dialog with focus trapping and pending/error state.

### Reusable components identified
- ConfirmationDialog
- AddressSummary
- DestructiveButton
- SecondaryButton

### Dynamic backend data required
- Address ID/label/summary
- Delete result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Dialog pattern | A confirmation is rendered as a full decorative page. | Use a compact accessible dialog over the address list, preserving context. |
| MAJOR | Destructive semantics | Delete is orange rather than danger red. | Apply semantic red with icon/text and disabled/loading state. |
| MINOR | Accessibility | Focus order/announcement cannot be inferred from the poster. | Focus the dialog title, announce the address, and return focus after cancellation. |

### Canonical recommendation
Correct before use; keep the copy/target summary and replace the composition with a native dialog.

## 11-edit-address-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Edit an existing Moroccan delivery address.  
Probable native route: `/account/addresses/:addressId/edit`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A prefilled address-edit form with recipient, phone, street, city and postal code.

### Visible UI structure
- Header: correctly mirrored RTL back/notification
- Main content: oversized address illustration, title and guidance
- Cards: none
- Forms: five prefilled fields
- Primary action: update
- Secondary actions: return to account
- Navigation: no bottom bar
- Overlays: none

### Brand validation
Official logo, palette, shapes and icon family are consistent.

### UX validation
Arabic labels/values align right, but leading icons sit left, the phone omits required `+212`, the postal icon is wrong, and the bottom return arrow is unmirrored. The keyboard will cover lower content.

### Native implementation usability
Feasible as a keyboard-aware form using the same schema as Add Address; values and validation must remain dynamic.

### Reusable components identified
- AppHeader
- FormInput
- PhoneInput
- CitySelector
- PrimaryButton

### Dynamic backend data required
- Existing address fields/ID
- City/service-zone options
- Validation/update result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | RTL/forms | Leading input icons are placed on the left. | Put adornments on the right and preserve logical focus/reader order. |
| MAJOR | Morocco context | `0612-345678` omits the required `+212` prefix. | Format as a Moroccan `+212` LTR value and use phone input semantics. |
| MAJOR | Keyboard/layout | Hero and fixed-length form do not provide keyboard-safe scrolling. | Collapse art and use keyboard-aware scrolling with error focus. |
| MINOR | Icon semantics | Envelope icon represents postal code. | Use an appropriate postal/location symbol. |
| MINOR | RTL/navigation | Bottom return arrow points left. | Mirror it to point right. |

### Canonical recommendation
Correct before use; combine its populated-label pattern with the Add Address route.

## 11-edit-profile-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Edit buyer profile data.  
Probable native route: `/account/profile/edit`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A profile avatar editor and fields for full name, email, phone and city.

### Visible UI structure
- Header: unmirrored back/notification
- Main content: avatar hero, title and four fields
- Cards: form fields
- Forms: name/email/phone/city
- Primary action: save
- Secondary actions: avatar edit/back
- Navigation: no bottom bar
- Overlays: none

### Brand validation
Logo, palette, cards and outline icons align with the source boards.

### UX validation
Field icons are correctly on the RTL leading edge and `+212` is present, but the header is not mirrored. Email/phone values need LTR isolation, and the large avatar hero creates keyboard risk.

### Native implementation usability
Feasible with a keyboard-aware form and native image picker/permissions; read-only versus editable contact fields must be explicit.

### Reusable components identified
- AppHeader
- AvatarPicker
- LabeledFormInput
- PrimaryButton

### Dynamic backend data required
- Name/email/phone/city/avatar
- Field editability/verification state
- Save/upload result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | RTL/header | Back and notification remain in LTR positions. | Use the mirrored shared header. |
| MAJOR | Form behavior | Fields visually resemble cards and do not communicate editable/read-only states or validation. | Use native text inputs with focus, error, disabled and verified treatments. |
| MINOR | Bidi | Email/phone runs are not explicitly isolated. | Render LTR values inside the RTL field structure. |
| MINOR | Keyboard/layout | Large avatar art consumes keyboard-safe space. | Compact the hero while editing and scroll focused fields above the keyboard. |

### Canonical recommendation
Correct before use; retain the profile field grouping and avatar action.

## 11-faq-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Browse frequently asked buyer questions.  
Probable native route: `/support/faq`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An Arabic FAQ accordion covering orders, delivery, payment, returns and account management.

### Visible UI structure
- Header: correctly mirrored back/notification
- Main content: FAQ illustration, title and accordion
- Cards: five accordion rows
- Forms: none
- Primary action: expand question
- Secondary actions: back
- Navigation: five-tab bar, Account active
- Overlays: none

### Brand validation
Official logo, warm palette, cards and topic icons are consistent.

### UX validation
Question text and downward indicators are clear; the tab sequence remains wrong and the large hero reduces visible answer space. Expanded, loading and no-results states are not shown.

### Native implementation usability
Feasible with accessible accordion rows; expansion state and long answers need dynamic height and screen-reader announcements.

### Reusable components identified
- AppHeader
- FaqAccordionItem
- BrandIllustration
- BuyerTabBar

### Dynamic backend data required
- FAQ categories/questions/answers
- Expansion/search state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Cart/Favourites are swapped in the RTL bar. | Normalize the canonical five-tab order. |
| MINOR | Layout | Decorative art consumes the first-view answer area. | Reduce it and prioritize searchable FAQ content. |
| MINOR | State/accessibility | No expanded answer or accordion announcement is demonstrated. | Define expanded styling, `accessibilityState.expanded`, loading and empty states. |

### Canonical recommendation
Correct before use; it is the preferred Arabic FAQ list after tab normalization.

## 11-help-center-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Entry hub for buyer support topics.  
Probable native route: `/support`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A searchable support-topic hub for orders, payment, shipping, account and returns.

### Visible UI structure
- Header: logo and notification, no back
- Main content: title, search and topic list
- Cards: five topic rows
- Forms: search
- Primary action: topic row
- Secondary actions: search
- Navigation: five-tab bar, Account active
- Overlays: none

### Brand validation
Palette, official logo, card treatment and icons are consistent.

### UX validation
Row chevrons correctly point left, but search icon and missing deep-route back action conflict with RTL/navigation expectations. The bottom tabs remain wrong.

### Native implementation usability
Feasible as a searchable FlatList of support categories with local/remote content and empty/error states.

### Reusable components identified
- AppHeader
- SearchField
- SupportTopicRow
- BuyerTabBar

### Dynamic backend data required
- Topic ID/title/description/icon
- Search results

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Cart/Favourites are swapped in the tab bar. | Use canonical RTL ordering. |
| MAJOR | RTL/search | Search leading icon appears left. | Put the icon on the right and clear control on the left. |
| MAJOR | Route hierarchy | A support subroute has no back action. | Add the mirrored back action unless Product defines Support as a top-level Account landing page. |
| MINOR | Safe area | Dense list ends directly at the fixed tab bar. | Reserve bottom inset and test Dynamic Type. |

### Canonical recommendation
Correct before use; retain the topic rows and left-pointing chevrons.

## 11-home-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Arabic buyer storefront home.  
Probable native route: `/(tabs)/home`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
The Home tab with greeting, search, promotional hero, category shortcuts, featured products and benefit strip.

### Visible UI structure
- Header: logo and correctly left-positioned notification
- Main content: greeting, search, hero carousel, categories and featured products
- Cards: hero, category shortcuts, three product cards, benefit strip
- Forms: search
- Primary action: shop now/add to cart
- Secondary actions: view all/favourite
- Navigation: five-tab bar, Home active
- Overlays: none

### Brand validation
Strong Mayush furniture direction, official logo, warm cream/wood imagery, orange CTAs, navy copy and `MAD` pricing.

### UX validation
Hero CTA is correctly mirrored, but search icon and tab sequence are wrong. “Search by name or order number” mixes product discovery with order lookup. Three product columns make text/actions dense for a ~390 dp mobile viewport.

### Native implementation usability
Feasible with a SectionList and reusable carousel/category/product components, but product cards should use a responsive two-column grid and dynamic content.

### Reusable components identified
- AppHeader
- SearchField
- HeroCarousel
- CategoryShortcut
- ProductCard
- BenefitStrip
- BuyerTabBar

### Dynamic backend data required
- Greeting/user state
- Hero slides/deep links
- Categories
- Featured products/prices/stock/favourite/cart state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Cart/Favourites are swapped in the RTL tab sequence. | Render Home, Categories, Favourites, Cart, Account from the right. |
| MAJOR | Search logic | Home product search mentions order number. | Search catalog attributes only; keep order lookup in Orders/Support. |
| MAJOR | Mobile layout | Three product cards per row create tiny text and action targets. | Use two responsive columns or a horizontal featured carousel with 48 dp actions. |
| MINOR | RTL/search | Search icon appears on the left. | Move the leading magnifier to the right. |
| MINOR | Accessibility | Carousel position and auto-advance controls are not represented. | Add page announcements, pause/manual controls and accessible labels. |

### Canonical recommendation
Correct before use; this is the canonical Arabic Home candidate after shell, search and product-grid changes.

## 11-language-selection-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Select application language.  
Probable native route: `/account/language`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A radio-card selector for Arabic, French and English with a save action.

### Visible UI structure
- Header: notification left, back right but pointing left
- Main content: title and language choices
- Cards: three language options
- Forms: radio selection
- Primary action: save changes
- Secondary actions: back
- Navigation: no bottom bar
- Overlays: none

### Brand validation
Official logo, palette, selection border/check and card treatment fit the system.

### UX validation
Rows correctly place icon/text right and selection left, but the back arrow direction is wrong. **NEEDS PRODUCT DECISION:** whether English is a supported production locale.

### Native implementation usability
Feasible with accessible radio rows and an app-level locale restart/re-render strategy.

### Reusable components identified
- AppHeader
- LanguageRadioCard
- PrimaryButton

### Dynamic backend data required
- Supported locales
- Current/saved locale
- Locale-change result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | RTL/header | Back is on the right but points left. | Use a right-pointing RTL back arrow. |
| MAJOR | Product scope | English support is not established by the supplied French/Arabic system. | **NEEDS PRODUCT DECISION:** remove or fully localize/test English before exposing it. |
| MINOR | Copy | `اللغة الافتراضية` can imply device default rather than selected language. | Use product-approved “current/selected language” wording. |

### Canonical recommendation
Correct before use; retain the radio-card selection pattern.

## 11-logout-device-confirm-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Confirm logout/revocation of a device session.  
Probable native route: `/account/security/sessions/:sessionId/logout`  
Language: Arabic  
Screen type:
- Dialog

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A confirmation that only the selected device session will be logged out.

### Visible UI structure
- Header: unmirrored back/notification
- Main content: large warning illustration and dialog-like card
- Cards: confirmation panel
- Forms: none
- Primary action: confirm
- Secondary actions: cancel
- Navigation: no bottom bar
- Overlays: rendered as full page

### Brand validation
Official logo and palette fit Mayush; the destructive action lacks semantic red.

### UX validation
Scope is explained, but the target device is not named and a full-page confirmation loses context. **NEEDS PRODUCT DECISION:** whether this is the current or remote session and whether re-authentication is required.

### Native implementation usability
Use a compact native dialog opened from the session card, with target device name and revocation progress.

### Reusable components identified
- ConfirmationDialog
- DeviceSummary
- DestructiveButton
- SecondaryButton

### Dynamic backend data required
- Target session/device/current flag
- Revoke result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | RTL/header | Back/notification positions remain LTR. | Use the mirrored header or omit it for a real modal. |
| MAJOR | Context | The confirmation does not name the device/session. | Include device name, location and last activity. |
| MAJOR | Destructive pattern | Full-page orange confirm is not a semantic destructive dialog. | Use a red, target-specific accessible modal with loading/error state. |
| MINOR | Product logic | Current versus remote logout behavior is not explicit. | Resolve through **NEEDS PRODUCT DECISION** before wiring the action. |

### Canonical recommendation
Correct and convert to a modal; combine with `11-active-sessions-ar.png`.

## 11-marketing-preferences-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Configure promotional communications.  
Probable native route: `/account/preferences/marketing`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Four switches for promotional email, in-app offers, SMS and personalized recommendations.

### Visible UI structure
- Header: correctly mirrored RTL back/notification
- Main content: heading, illustration and preference rows
- Cards: four switch rows
- Forms: switches
- Primary action: switch changes
- Secondary actions: back
- Navigation: five-tab bar, Account active
- Overlays: none

### Brand validation
Logo, palette, rounded rows and consistent orange icons are on-system.

### UX validation
Text/icons on the right and switches on the left are correctly RTL. The tab order is wrong, the hero delays controls, and the all-on state must not be interpreted as a consent default. **NEEDS PRODUCT DECISION:** save model and consent defaults.

### Native implementation usability
Feasible with native Switch controls and optimistic persistence with rollback/feedback.

### Reusable components identified
- AppHeader
- PreferenceSwitchRow
- BrandIllustration
- BuyerTabBar

### Dynamic backend data required
- Four preference values
- Consent provenance/update result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Cart/Favourites are swapped in the RTL tab bar. | Normalize the shared tab navigator. |
| MAJOR | Consent/state | All toggles are shown on with no save/auto-save feedback or consent context. | **NEEDS PRODUCT DECISION:** define lawful defaults, persistence and confirmation/error feedback. |
| MINOR | Layout | Decorative illustration consumes space before preferences. | Reduce it and prioritize controls under Dynamic Type. |

### Canonical recommendation
Correct before use; retain the switch-row structure only after consent behavior is defined.

## 11-no-internet-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Explain offline state and allow retry.  
Probable native route: `/system/offline`  
Language: Arabic  
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A blocking no-network state with retry and retained bottom navigation.

### Visible UI structure
- Header: logo with `BUYER APP`, notification on right
- Main content: offline illustration and explanation
- Cards: none
- Forms: none
- Primary action: retry
- Secondary actions: bottom tabs
- Navigation: five-tab bar, Account active
- Overlays: none

### Brand validation
The palette and art fit Mayush, but `BUYER APP` directly violates the logo rule and `الأقسام` conflicts with other category labels.

### UX validation
Retry is clear, but offline support/cached content is absent and the retained Account-active, unmirrored tab shell is contradictory for a global network state.

### Native implementation usability
Feasible as a global offline fallback with reachability-based retry; cache availability and interrupted-action recovery must be defined.

### Reusable components identified
- OfflineState
- PrimaryButton
- ConnectivityBanner

### Dynamic backend data required
- Network/retry state
- Cached-content availability
- Interrupted route/action

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | `BUYER APP` is prohibited extra wording. | Use the official logo alone. |
| MAJOR | RTL/navigation | Notification/tab shell is unmirrored; Cart/Favourites are swapped and Account is arbitrarily active. | Present a global state without route-active tabs, or preserve the actual underlying route using canonical order. |
| MAJOR | Offline UX | No cached-content or safe interrupted-action path is offered. | Show retry progress and available offline/cached navigation where supported. |
| MINOR | Copy consistency | Categories is labeled `الأقسام` instead of the established Arabic term. | Standardize one approved Arabic tab label. |

### Canonical recommendation
Correct before use; keep the retry concept but replace branding and global-shell behavior.

## 11-notification-settings-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Configure notification categories and quiet hours.  
Probable native route: `/account/notifications`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Switches for order, offer, message and recommendation alerts plus routes to silent hours and notification details.

### Visible UI structure
- Header: correctly mirrored RTL back/notification
- Main content: title, switch group and navigation rows
- Cards: notification group, silent-hours row, details row
- Forms: switches
- Primary action: preference changes
- Secondary actions: silent hours/details
- Navigation: five-tab bar, Account active
- Overlays: none

### Brand validation
Logo, palette, cards, status orange and icon family are consistent.

### UX validation
Rows and left-pointing forward chevrons are correctly RTL; bottom tabs are not. `22:00–07:00` needs bidi isolation, and save/permission-denied behavior is not shown.

### Native implementation usability
Feasible with native switches and settings rows, plus OS permission checks/deep-link behavior.

### Reusable components identified
- AppHeader
- PreferenceSwitchRow
- SettingsNavigationRow
- BuyerTabBar

### Dynamic backend data required
- Category preferences
- OS permission status
- Silent-hours range
- Save result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Cart/Favourites are swapped in the bottom bar. | Use canonical RTL order. |
| MAJOR | Permission/state | No OS-disabled, saving, failure or auto-save feedback is defined. | Add OS permission callout/deep link and explicit persistence feedback. |
| MINOR | Bidi | `22:00 - 07:00` is not visibly isolated. | Render the time range as a protected LTR run within the RTL row. |

### Canonical recommendation
Correct before use; it is the preferred Arabic notification overview after shell/state fixes.

## 11-order-detail-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Show a completed order's full details.  
Probable native route: `/orders/:orderId`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A delivered-order detail with order ID/date, delivery address, payment state/method, items, total and reorder actions.

### Visible UI structure
- Header: correctly mirrored RTL back/notification
- Main content: order metadata, address/payment and item summary
- Cards: order/status and product summary cards
- Forms: none
- Primary action: view orders
- Secondary actions: reorder
- Navigation: no bottom bar
- Overlays: none

### Brand validation
Logo, cream/navy cards, semantic green and product imagery fit Mayush; currency violates the mandated format.

### UX validation
Information hierarchy is implementable, but every price uses `درهم` rather than `MAD`. The order/date/name mixtures need bidi isolation and “View my orders” is an odd primary action from an order detail.

### Native implementation usability
Feasible with a ScrollView and reusable order cards once currency and domain actions are corrected.

### Reusable components identified
- AppHeader
- OrderHeaderCard
- AddressSummary
- PaymentSummary
- OrderItemRow
- PrimaryButton

### Dynamic backend data required
- Order ID/date/status
- Delivery address/phone
- Payment state/method
- Items/images/prices/total
- Reorder eligibility

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Currency | All monetary values use `درهم`, contradicting the mandatory `MAD` source of truth. | Render every amount through one `MAD` currency formatter and protect the LTR amount run. |
| MAJOR | Action hierarchy | “View my orders” is primary even though the user is already inside an order; reorder eligibility is unexplained. | Make context-relevant tracking/support/reorder actions conditional on order state. |
| MINOR | Bidi | Order ID, date/time, phone and product-name runs need explicit isolation. | Apply reusable bidi-safe formatters. |

### Canonical recommendation
Correct before use; never use its currency labels as implementation reference.

## 11-order-not-found-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Recover from an unsuccessful order lookup.  
Probable native route: `/orders/not-found`  
Language: Arabic  
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An order-search error with retry-by-number, home and support recovery options.

### Visible UI structure
- Header: unmirrored back/notification
- Main content: search-error illustration, explanation and info card
- Cards: troubleshooting callout
- Forms: none
- Primary action: enter another order number
- Secondary actions: home/support
- Navigation: no bottom bar
- Overlays: none

### Brand validation
Logo, palette, cards and illustration are consistent.

### UX validation
Recovery choices are useful, but header/primary chevron remain LTR and the page does not preserve/show the failed query. The hero pushes the actual recovery controls down.

### Native implementation usability
Feasible as a reusable lookup-empty state driven by the prior query and authenticated order scope.

### Reusable components identified
- AppHeader
- EmptyErrorState
- InfoCallout
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Failed order ID/query
- Lookup result/error reason
- Support destination

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | RTL/navigation | Header and primary forward chevron are unmirrored. | Put back on the right pointing right and forward chevron left pointing left. |
| MAJOR | Recovery context | Failed order number/reason is not shown or retained. | Display a bidi-safe query and return the user to a prefilled lookup form. |
| MINOR | Layout | Illustration overwhelms recovery actions. | Reduce it and keep primary recovery above the fold under text scaling. |

### Canonical recommendation
Correct before use; retain the three recovery destinations.

## 11-order-notification-detail-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Show details behind an order-status notification.  
Probable native route: `/notifications/orders/:notificationId`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A notification detail summarizing an in-progress paid order and linking to the order.

### Visible UI structure
- Header: unmirrored back/notification
- Main content: title and single order-notification card
- Cards: order metadata, thumbnails, amount, status and action
- Forms: none
- Primary action: view order
- Secondary actions: back
- Navigation: five-tab bar, Account active
- Overlays: none

### Brand validation
Official logo, palette, product images, badges and `MAD` are consistent.

### UX validation
Content is understandable, but header/CTA/tab shell remain LTR or incorrectly ordered, and Account active state does not represent a notification route. Date/ID/amount need bidi isolation.

### Native implementation usability
Feasible as a compact notification-detail component that deep-links to the canonical Order Detail route.

### Reusable components identified
- AppHeader
- OrderNotificationCard
- StatusBadge
- ProductThumbnailStack
- BuyerTabBar

### Dynamic backend data required
- Notification ID/read state/body
- Order ID/date/status/amount/items
- Deep-link target

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | RTL/header | Back/notification positions are unmirrored. | Apply the shared RTL header. |
| MAJOR | RTL/action | “View order” chevron points right. | Put a left-pointing forward chevron on the left. |
| MAJOR | Navigation | Tab order is wrong and Account is arbitrarily active. | Prefer no bottom bar for notification detail, or preserve the true source tab using canonical order. |
| MINOR | Bidi | ID/date/time/`MAD` runs are not explicitly isolated. | Use shared bidi-safe formatters. |

### Canonical recommendation
Correct before use; combine its notification summary with the canonical order-detail route.

## 11-orders-list-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Browse and filter buyer order history.  
Probable native route: `/orders`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Searchable/tabbed cards for processing, completed and cancelled orders.

### Visible UI structure
- Header: logo and notification, no back
- Main content: title, search, RTL status tabs and three order cards
- Cards: order summaries
- Forms: order-ID search
- Primary action: view details
- Secondary actions: status filtering
- Navigation: five-tab bar, Account active
- Overlays: none

### Brand validation
Logo, palette, cards, product imagery, badges and `MAD` fit Mayush.

### UX validation
Status tabs and left-pointing detail chevrons are correctly RTL; search icon and bottom tab sequence are not. `حسابي` conflicts with `الحساب` elsewhere.

### Native implementation usability
Feasible with a virtualized paginated FlatList, status filter and reusable order cards.

### Reusable components identified
- AppHeader
- SearchField
- SegmentedTabs
- OrderSummaryCard
- BuyerTabBar

### Dynamic backend data required
- Orders/status/date/amount/items/thumbnails
- Pagination/search/filter state
- Payment/delivery states

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Cart/Favourites are swapped and Account label changes to `حسابي`. | Centralize canonical labels, icons, order and active state. |
| MAJOR | RTL/search | Magnifier is on the left in an RTL field. | Put it on the right and isolate typed order IDs LTR. |
| MINOR | Density | Cards pack many status fields into limited height. | Allow dynamic height and test long Arabic/status text at large font sizes. |

### Canonical recommendation
Correct before use; this is the Arabic Orders list candidate after shell/search normalization.

## 11-order-tracking-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Track order fulfilment progress.  
Probable native route: `/orders/:orderId/tracking`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An order header and vertical five-step timeline with confirmation, preparation, shipping, in-transit and delivered stages.

### Visible UI structure
- Header: unmirrored back/notification
- Main content: order metadata and timeline
- Cards: header and tracking timeline
- Forms: none
- Primary action: help
- Secondary actions: back
- Navigation: five-tab bar, Account active
- Overlays: none

### Brand validation
Palette, logo, semantic colors and icons are consistent.

### UX validation
The timeline is placed on the RTL side, but pending steps are titled in completed past tense (`تم الشحن`, `تم التسليم`) while saying preparation is still in progress. This misrepresents fulfilment state; shell/CTA are also unmirrored.

### Native implementation usability
Feasible with a data-driven vertical Stepper, but never hard-code step labels/descriptions independently.

### Reusable components identified
- AppHeader
- OrderMetaCard
- VerticalStatusStepper
- SupportLinkCard
- BuyerTabBar

### Dynamic backend data required
- Order ID/date
- Current fulfilment stage
- Stage timestamps/descriptions
- Tracking/support target

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Order state | Pending stages use completed labels while their descriptions say preparation is ongoing. | Derive title, tense, icon and color from one authoritative fulfilment state model. |
| MAJOR | RTL/navigation | Header, help chevron and bottom tabs are incorrect. | Mirror header/forward action and normalize tab order. |
| MAJOR | Copy/data | Dates lack spacing and completed/pending timestamps are inconsistent. | Use localized date formatting and show timestamps only for reached stages. |
| MINOR | Accessibility | Timeline state relies heavily on color/position. | Announce current/completed/upcoming state in each accessible step label. |

### Canonical recommendation
Correct before use; do not implement the shown status text literally.

## 11-password-changed-success-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Confirm successful password change.  
Probable native route: `/account/security/password/success`  
Language: Arabic  
Screen type:
- Success state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A success page that offers sign-in and return-home actions after a password change.

### Visible UI structure
- Header: logo with buyer sub-label
- Main content: success/security illustration and confirmation copy
- Cards: none
- Forms: none
- Primary action: sign in
- Secondary actions: return home
- Navigation: no bottom bar
- Overlays: none

### Brand validation
Palette/art are consistent; `تطبيق المشتري` is prohibited extra branding.

### UX validation
The screen assumes password change logs the user out, but that policy is not established. A home bypass conflicts with a mandatory re-login, and the return arrow is unmirrored. **NEEDS PRODUCT DECISION:** post-change session policy.

### Native implementation usability
Feasible as a reusable success state once the authentication outcome and safe destination are defined.

### Reusable components identified
- SystemStateIllustration
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Password-change result
- Session invalidation policy
- Safe destination

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | `تطبيق المشتري` modifies the logo. | Remove the sub-label. |
| MAJOR | Auth logic | Sign-in is required while Home remains available. | **NEEDS PRODUCT DECISION:** either retain the session and return to Security, or invalidate it and allow only sign-in. |
| MAJOR | RTL/navigation | Home-return arrow points left. | Mirror to a right-pointing return/back arrow. |
| MINOR | UX | Full-page art is excessive for a simple completion state. | Use a compact success confirmation unless re-authentication truly changes the flow. |

### Canonical recommendation
Correct before use; authentication policy must be resolved first.

## 11-payment-method-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Select checkout payment method and confirm amount.  
Probable native route: `/checkout/payment`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Radio-card choices for bank card, cash on delivery and wallet, followed by an order total and confirm action.

### Visible UI structure
- Header: correctly mirrored RTL back/notification
- Main content: payment choices and order summary
- Cards: three payment options and totals
- Forms: radio selection
- Primary action: confirm payment
- Secondary actions: back
- Navigation: no bottom bar
- Overlays: none

### Brand validation
Logo, palette, cards, selection state and `MAD` formatting are consistent.

### UX validation
Method rows are correctly mirrored and totals are clear, but “wallet” and direct confirm behavior are unproven. Card entry/gateway handoff, cash-on-delivery eligibility and errors are not shown. **NEEDS PRODUCT DECISION:** supported methods and payment integration.

### Native implementation usability
Feasible as a method selector only after integrating a compliant external/native payment flow and eligibility data.

### Reusable components identified
- AppHeader
- PaymentMethodCard
- RadioControl
- PriceSummary
- PrimaryButton

### Dynamic backend data required
- Eligible payment methods
- Totals/fees/discount
- Wallet balance if supported
- Payment intent/result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Payment logic | Wallet and “confirm payment” are shown without evidenced availability or gateway/card-details flow. | **NEEDS PRODUCT DECISION:** define methods, eligibility and provider handoff before implementation. |
| MAJOR | State handling | No processing, declined, retry, duplicate-submit or COD eligibility state is represented. | Disable during submission and provide provider-safe recovery/error states. |
| MINOR | RTL/button | Lock icon appears on the left as though it were a trailing action. | Treat it as a decorative leading icon on the right or remove it. |

### Canonical recommendation
Correct before use; use only as the visual basis for method selection, not payment logic.

## 11-payment-success-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Confirm successful payment/order placement.  
Probable native route: `/checkout/success`  
Language: Arabic  
Screen type:
- Success state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A success page with order ID/date, processing message and View Order action.

### Visible UI structure
- Header: correctly mirrored back/notification, logo with buyer sub-label
- Main content: success art, copy, order card and confirmation callout
- Cards: order metadata and payment confirmation
- Forms: none
- Primary action: view order
- Secondary actions: back/tabs
- Navigation: five-tab bar, Account active
- Overlays: none

### Brand validation
Strong palette/status treatment and official logo artwork; `تطبيق المشتري` violates branding.

### UX validation
Outcome is clear, but paid amount/method/receipt reference are absent. Account is wrongly active and the tab sequence is incorrect for a focused checkout completion.

### Native implementation usability
Feasible as a dedicated success route with server-confirmed order/payment data; prevent navigating back into payment submission.

### Reusable components identified
- AppHeader
- SystemStateIllustration
- OrderMetaCard
- SuccessCallout
- PrimaryButton

### Dynamic backend data required
- Order ID/date/status
- Paid amount/method/reference
- Receipt/order route

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | `تطبيق المشتري` adds prohibited wording. | Use only the official logo. |
| MAJOR | Payment evidence | Amount, method and payment reference are omitted. | Show server-confirmed receipt data and make it accessible/shareable where required. |
| MAJOR | Navigation | Account-active incorrect tabs remain after checkout. | Use a focused success route with View Order/Home actions and reset the checkout stack. |
| MINOR | Bidi | Order ID and date/time need isolation. | Apply shared bidi-safe components. |

### Canonical recommendation
Correct before use; retain the success hierarchy but add receipt data and remove the tab shell.

## 11-privacy-security-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Hub for account privacy and security controls.  
Probable native route: `/account/security`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Navigation rows for active sessions, password, data privacy, permissions and security alerts.

### Visible UI structure
- Header: correctly mirrored RTL back/notification
- Main content: title and security section
- Cards: five settings rows
- Forms: none
- Primary action: navigation rows
- Secondary actions: back
- Navigation: five-tab bar, Account active
- Overlays: none

### Brand validation
Official logo, palette, icons, cards and spacing are coherent.

### UX validation
Rows and left-pointing chevrons are correctly RTL, but the bottom bar uses the wrong order/label. Some destinations imply product-managed OS permissions/data controls that may only deep-link to device settings. **NEEDS PRODUCT DECISION.**

### Native implementation usability
Feasible as a reusable settings list with route/OS-deep-link destinations controlled by capability.

### Reusable components identified
- AppHeader
- SettingsNavigationRow
- BuyerTabBar

### Dynamic backend data required
- Available security/privacy capabilities
- Permission status
- Alert preference summary

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Bottom bar order is wrong and Categories becomes `الأقسام`. | Centralize canonical tab order and Arabic labels. |
| MAJOR | Product scope | Data privacy/permissions destinations are not defined. | **NEEDS PRODUCT DECISION:** distinguish in-app consent controls from OS settings/legal pages. |
| MINOR | Safe area | Fixed tab bar requires explicit bottom content inset. | Implement shared safe-area spacing and Dynamic Type tests. |

### Canonical recommendation
Correct before use; it is the preferred Arabic security hub once destinations are defined.

## 11-product-detail-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Present an Arabic furniture product and add it to cart.  
Probable native route: `/products/:productId`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A sofa product detail with hero image, favourite control, title, price, specifications, quantity and add-to-cart CTA.

### Visible UI structure
- Header: correctly mirrored RTL back/notification
- Main content: product image/details/specs/quantity
- Cards: spec strip and quantity row
- Forms: quantity stepper
- Primary action: add to cart
- Secondary actions: favourite/back
- Navigation: five-tab bar, Account incorrectly active
- Overlays: none

### Brand validation
Furniture photography, logo, palette, rounded sections and `MAD` pricing strongly fit Mayush.

### UX validation
Core product hierarchy and quantity order are usable, but Account is active and Cart/Favourites are swapped. No gallery pagination, variant/stock/delivery state or add feedback is represented.

### Native implementation usability
Feasible as a ScrollView with image carousel and sticky CTA, backed by variant/stock/cart state.

### Reusable components identified
- AppHeader
- ProductGallery
- FavouriteButton
- ProductSpecRow
- QuantityStepper
- StickyAddToCart
- BuyerTabBar

### Dynamic backend data required
- Product ID/name/images/description
- Price/tax/specifications/warranty
- Variant/stock/quantity/cart/favourite state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Account is active on Product Detail and Cart/Favourites are swapped. | Preserve source context or omit active tabs; always use canonical order. |
| MAJOR | Product state | Gallery, variant, stock, delivery and add-to-cart feedback are missing. | Add data-driven carousel/availability/variant and pending/success/error states. |
| MINOR | Bidi/responsiveness | Dimension and mixed product values need isolated, wrapping-safe formatting. | Use bidi-safe value components and Dynamic Type testing. |

### Canonical recommendation
Correct before use; this is the Arabic Product Detail candidate after state/navigation fixes.

## 11-scheduled-maintenance-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Explain scheduled service maintenance.  
Probable native route: `/system/maintenance`  
Language: Arabic  
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A blocking maintenance page with expected return time and “try later” action.

### Visible UI structure
- Header: logo and notification
- Main content: maintenance illustration, title and copy
- Cards: expected-return time
- Forms: none
- Primary action: try later
- Secondary actions: none
- Navigation: no bottom bar
- Overlays: none

### Brand validation
Logo, palette and illustration treatment are consistent.

### UX validation
The state is understandable, but notification access is likely unusable during maintenance, the CTA destination is unclear, and one time lacks start/end/timezone certainty. **NEEDS PRODUCT DECISION:** maintenance scope and recovery behavior.

### Native implementation usability
Feasible as a server/config-driven fallback; status, interval and retry availability cannot be embedded in art.

### Reusable components identified
- MaintenanceState
- DateTimeCallout
- PrimaryButton

### Dynamic backend data required
- Maintenance active/scope
- Start/end/estimated return/timezone
- Retry/status-page URL

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Recovery | “Try later” has no clear close, retry or status behavior. | Provide explicit retry/status-page/exit behavior based on maintenance scope. |
| MAJOR | Time data | Only a hard-coded expected time is shown without interval/timezone. | Drive a localized start/end/estimate from remote configuration. |
| MINOR | Header | Notification action is unrelated or unavailable in a blocking outage. | Remove it unless notifications remain a proven working route. |

### Canonical recommendation
Correct before use; retain the maintenance concept with dynamic time/recovery data.

## 11-search-results-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Display Arabic catalog search results.  
Probable native route: `/search`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A query field, filters/sort, result count and two-column furniture product grid.

### Visible UI structure
- Header: logo/notification, no back
- Main content: title, query, filters and product grid
- Cards: product cards
- Forms: search/filter controls
- Primary action: product cards
- Secondary actions: clear/filter/sort/favourite
- Navigation: five-tab bar, Account incorrectly active
- Overlays: none

### Brand validation
Official logo, furniture imagery, palette, product cards and `MAD` pricing fit Mayush.

### UX validation
Search adornments and filter order are correctly RTL, but a deep results route lacks back, the bottom nav is wrong/incorrectly active, and content reaches behind the fixed bar.

### Native implementation usability
Feasible with a paginated two-column FlatList, filter sheet and sort menu; reserve footer inset and loading/empty/error states.

### Reusable components identified
- AppHeader
- SearchField
- FilterChip
- SortControl
- ProductCard
- BuyerTabBar

### Dynamic backend data required
- Query/result count/products
- Filter facets/selections
- Sort/pagination/favourite state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Route navigation | No RTL back action is available from results. | Add a right-side, right-pointing back action and preserve query state. |
| MAJOR | Bottom navigation | Account is active and Cart/Favourites are swapped. | Preserve source tab context or omit bottom navigation; normalize order. |
| MAJOR | Safe area | Grid content visually continues under the fixed tab bar. | Add FlatList bottom inset equal to tab bar plus safe area. |
| MINOR | State coverage | Loading, no results and filter-error states are absent. | Define reusable skeleton/empty/error variants. |

### Canonical recommendation
Correct before use; this is the Arabic Search Results candidate after navigation/inset fixes.

## 11-session-expired-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Recover from an expired authenticated session.  
Probable native route: `/auth/session-expired`  
Language: Arabic  
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A forced session-end state with sign-in and home actions.

### Visible UI structure
- Header: logo with buyer sub-label
- Main content: security-error illustration and explanation
- Cards: none
- Forms: none
- Primary action: sign in again
- Secondary actions: return home
- Navigation: no bottom bar
- Overlays: none

### Brand validation
Palette/art match Mayush; `تطبيق المشتري` is prohibited extra branding.

### UX validation
Re-authentication is clear, but return Home may expose an authenticated destination or discard interrupted work, and its arrow is unmirrored. The reason is vaguely “security.”

### Native implementation usability
Feasible as an auth-stack guard that preserves a safe pending deep link and clears sensitive state.

### Reusable components identified
- SessionExpiredState
- PrimaryButton
- TextLink

### Dynamic backend data required
- Expiry reason
- Safe return/deep link
- Authentication result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | `تطبيق المشتري` modifies the official logo. | Remove it. |
| MAJOR | Auth recovery | Home bypass/interrupted-work behavior is undefined. | Route only to public Home or sign-in, preserve a safe return target, and explain data preservation. |
| MINOR | RTL | Home-return arrow points left. | Mirror it to point right. |

### Canonical recommendation
Correct before use; retain the sign-in recovery priority.

## 11-silent-hours-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Configure notification quiet hours and repeat days.  
Probable native route: `/account/notifications/silent-hours`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An enable switch, overnight start/end time controls, weekday recurrence and explanatory callout.

### Visible UI structure
- Header: correctly mirrored RTL back/notification
- Main content: illustration, title and settings card
- Cards: configuration and information cards
- Forms: switch, two time selectors, day chips
- Primary action: no explicit save
- Secondary actions: back
- Navigation: no bottom bar
- Overlays: none

### Brand validation
Logo, palette, cards, icons and selected-day treatment align with Mayush.

### UX validation
RTL order of From/To and Saturday-to-Sunday chips is good, but `AM`/`PM` are accidental English UI, no save/auto-save feedback is shown, and copy implies even transactional order alerts are suppressed. **NEEDS PRODUCT DECISION:** suppression scope/persistence.

### Native implementation usability
Feasible using native time pickers and multi-select chips, with an overnight range model and timezone handling.

### Reusable components identified
- AppHeader
- PreferenceSwitchRow
- TimePickerField
- WeekdayChipGroup
- InfoCallout

### Dynamic backend data required
- Enabled flag
- Start/end time/timezone
- Repeat days
- Notification exemption rules/save result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Language/localization | `AM` and `PM` appear in an Arabic UI. | Use locale-aware Arabic time or a consistent 24-hour format. |
| MAJOR | Primary action/state | No Save action or auto-save feedback is visible. | Add an explicit save CTA or immediate-save confirmation/error states. |
| MAJOR | Product logic | Copy suggests all alerts, including critical order updates, are muted. | **NEEDS PRODUCT DECISION:** define quiet-hour exemptions and disclose them. |
| MINOR | Bidi | Time values/separator need protected LTR runs. | Use localized time components with bidi isolation. |

### Canonical recommendation
Correct before use; keep the RTL day order and replace the time/persistence behavior.

## 11-slow-connection-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Indicate degraded connectivity and reconnection.  
Probable native route: `/system/slow-connection`  
Language: Arabic  
Screen type:
- Loading state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A blocking full-page spinner telling the buyer not to close the app while reconnection occurs.

### Visible UI structure
- Header: logo, notification on right
- Main content: title, explanation, large loading illustration and info card
- Cards: reconnection callout
- Forms: none
- Primary action: missing
- Secondary actions: none
- Navigation: no bottom bar
- Overlays: none

### Brand validation
Palette, logo and art are consistent, but notification placement is unmirrored.

### UX validation
The buyer can be trapped indefinitely with no retry, cancel, offline path or timeout. “Do not close” is coercive and does not protect an interrupted transaction.

### Native implementation usability
Implement degraded connectivity as a banner plus per-request loading/error state; if blocking, include timeout and recovery controls.

### Reusable components identified
- ConnectivityBanner
- LoadingIndicator
- RetryPanel

### Dynamic backend data required
- Connectivity quality/retry state
- Interrupted request
- Timeout/error result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Recovery | Blocking state has no primary recovery action or timeout. | Provide Retry, Cancel/Back and offline-safe path after a bounded timeout. |
| MAJOR | UX pattern | A whole-app page replaces contextual slow-request feedback. | Use a nonblocking banner and show request-level progress/error where relevant. |
| MAJOR | Transaction safety | “Do not close” does not explain whether a purchase/action submitted. | Persist/idempotently check the action and state its status before retrying. |
| MINOR | RTL/header | Notification sits on the right. | Mirror or remove the unrelated header action. |

### Canonical recommendation
Correct and convert to reusable connectivity/request states; do not use the blocking page directly.

## 11-splash-loading-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Brand loading shown during application startup.  
Probable native route: `/splash/loading`  
Language: Arabic  
Screen type:
- Loading state

### Status
REFERENCE_ONLY

### Confidence
High

### What the screen represents
A full-screen logo, buyer sub-label and decorative spinner at startup.

### Visible UI structure
- Header: none
- Main content: centered logo/sub-label and loading illustration
- Cards: decorative spinner card
- Forms: none
- Primary action: none
- Secondary actions: none
- Navigation: none
- Overlays: none

### Brand validation
The primary logo is recognizable, but `تطبيق المشتري` is prohibited and the composition is far more decorative than the foundation launch treatment.

### UX validation
A startup wait has no progress/recovery; prolonged use would look frozen. A true iOS/Android native launch screen is static, so the illustrated spinner cannot be relied upon as animation there.

### Native implementation usability
Use only as inspiration: make the native launch screen static and minimal, then render any animated/auth-bootstrap loader as a React Native screen with timeout/error routing.

### Reusable components identified
- StaticLaunchBrand
- AppBootstrapLoader

### Dynamic backend data required
- Bootstrap/auth/config loading state
- Timeout/error destination

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | `تطبيق المشتري` adds prohibited buyer wording. | Use the official logo alone. |
| MAJOR | Native feasibility | Animated spinner cannot be implemented as part of the static native launch screen. | Split into static launch and post-launch React Native loader. |
| MINOR | Recovery | No timeout or failure state is implied. | Route prolonged bootstrap to a reusable error/retry state. |

### Canonical recommendation
Keep only as post-launch loading inspiration; it must not be used directly as the native splash reference.

## 11-support-chat-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Conduct an Arabic support conversation with attachments.  
Probable native route: `/support/chat/:conversationId`  
Language: Arabic  
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A live support chat about a cancelled order/payment failure, including message timestamps, delivery marks and PNG attachments.

### Visible UI structure
- Header: correctly mirrored RTL back/notification and online support identity
- Main content: day separator and alternating user/agent messages
- Cards: message bubbles and attachment group
- Forms: composer with attachment action
- Primary action: send message
- Secondary actions: attach/view all attachments/back
- Navigation: no bottom bar, appropriate for chat
- Overlays: none

### Brand validation
Official logo, palette, bubble differentiation, orange accents and icon family are consistent.

### UX validation
User messages are right and support messages left, matching RTL chat expectations. Composer and attachments are clear; the send glyph direction, bidi timestamps and keyboard/safe-area behavior need minor specification.

### Native implementation usability
Realistic with an inverted virtualized message list, keyboard-aware composer, upload queue and accessible attachment cards.

### Reusable components identified
- ChatHeader
- MessageBubble
- ChatTimestamp
- AttachmentCard
- MessageComposer

### Dynamic backend data required
- Conversation/agent presence
- Messages/sender/timestamps/delivery state
- Attachments/upload state
- Send/retry state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | RTL/icon | Send arrow points right. | Mirror the directional send glyph for RTL or use a non-directional paper-plane icon approved by the icon set. |
| MINOR | Bidi | Times, counts, extensions and file sizes need explicit isolation. | Use bidi-safe timestamp/file metadata components. |
| MINOR | Keyboard/safe area | Static screenshot does not prove the composer remains visible above keyboard/home indicator. | Anchor with keyboard and safe-area insets; preserve list position. |

### Canonical recommendation
Use directly as the main Arabic chat reference after the listed minor implementation fixes.

## 11-support-tickets-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Browse and filter buyer support tickets.  
Probable native route: `/support/tickets`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A searchable, tabbed ticket list with open, under-review and closed states plus new-ticket CTA.

### Visible UI structure
- Header: correctly mirrored RTL back/notification
- Main content: title, search, RTL status tabs and three ticket cards
- Cards: ticket summaries
- Forms: ticket search
- Primary action: create new ticket
- Secondary actions: view details/filter
- Navigation: five-tab bar, Account active
- Overlays: none

### Brand validation
Logo, palette, badges, cards and icons align with Mayush.

### UX validation
Search adornment, tab order and left-pointing detail chevrons are correctly RTL. The bottom tab sequence is wrong; result pagination/loading/empty states are absent.

### Native implementation usability
Feasible with a paginated FlatList, segmented status filter and reusable ticket cards.

### Reusable components identified
- AppHeader
- SearchField
- SegmentedTabs
- SupportTicketCard
- PrimaryButton
- BuyerTabBar

### Dynamic backend data required
- Ticket ID/date/status/subject/preview
- Linked order ID
- Search/filter/pagination state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Cart/Favourites are swapped in the bottom bar. | Normalize canonical RTL order. |
| MINOR | State coverage | Loading, empty, error and pagination states are not represented. | Add reusable list states and retry. |
| MINOR | Bidi | Ticket/order IDs and timestamps require isolation. | Use shared LTR metadata components inside RTL cards. |

### Canonical recommendation
Correct before use; it is the Arabic ticket-list candidate after tab/state fixes.

## 11-ticket-closed-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Confirm closure of a support ticket.  
Probable native route: `/support/tickets/:ticketId/closed`  
Language: Arabic  
Screen type:
- Success state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A successful ticket-resolution page with return-to-support and Home actions.

### Visible UI structure
- Header: logo and notification
- Main content: success illustration and closure copy
- Cards: none
- Forms: none
- Primary action: return to support
- Secondary actions: Home
- Navigation: no bottom bar
- Overlays: none

### Brand validation
Official logo, cream/navy palette and semantic success green are consistent.

### UX validation
Outcome and destinations are clear, but both forward/return chevrons are on the right pointing right, contrary to the RTL action direction. Ticket identity and reopen/escalation policy are absent.

### Native implementation usability
Feasible as a compact success state backed by ticket status and safe navigation.

### Reusable components identified
- SystemStateIllustration
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Ticket ID/status/resolution
- Reopen eligibility
- Support/home destinations

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | RTL/actions | Both directional CTA chevrons are unmirrored. | Put forward chevrons left pointing left, or use a right-pointing back arrow for a true return action. |
| MAJOR | Support logic | Ticket/resolution and reopen/escalation availability are omitted. | Show a bidi-safe ticket ID and **NEEDS PRODUCT DECISION** for reopen/escalation. |
| MINOR | Layout | Large art delays the actual completion actions. | Use a compact success summary. |

### Canonical recommendation
Correct before use; retain the success semantics, not the action iconography.

## 11-update-available-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Offer an optional app update.  
Probable native route: `/system/update-available`  
Language: Arabic  
Screen type:
- Dialog

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A full-page optional update notice with version, size, release notes, update and “not now” actions.

### Visible UI structure
- Header: correctly mirrored RTL back/notification
- Main content: update illustration and release card
- Cards: version/release notes
- Forms: none
- Primary action: update now
- Secondary actions: not now
- Navigation: five-tab bar, Account active
- Overlays: rendered as full page

### Brand validation
Logo, app icon, palette, cards and typography align with Mayush.

### UX validation
Version/size/notes are useful but appear hard-coded, “not now” arrow is unmirrored, and Account-active tabs are unrelated. **NEEDS PRODUCT DECISION:** remote/store source and deferral policy.

### Native implementation usability
Feasible as a remotely configured modal that deep-links to App Store/Play Store; mobile apps do not self-update through this CTA.

### Reusable components identified
- UpdatePrompt
- ReleaseNotesCard
- PrimaryButton
- TextLink

### Dynamic backend data required
- Installed/latest version
- Store URL/size/release notes
- Optional/required flag
- Deferral policy

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Update behavior | CTA suggests in-app update while store handoff/progress/failure are undefined. | Deep-link to the correct store and handle return/version recheck. |
| MAJOR | Navigation | Account-active wrong-order tabs appear in a global prompt. | Present as a modal over the current route without replacing tab state. |
| MAJOR | Dynamic data | `2.4.0`, `23.5 MB` and notes must not be static implementation content. | Source them from signed remote/store configuration. |
| MINOR | RTL | “Not now” arrow points left as an LTR back glyph. | Use a mirrored right-pointing dismiss/back arrow or omit the icon. |

### Canonical recommendation
Correct and convert to a modal; retain the version/release-note hierarchy.

## 11-update-required-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Block use until a mandatory app update.  
Probable native route: `/system/update-required`  
Language: Arabic  
Screen type:
- Error state

### Status
REJECTED

### Confidence
High

### What the screen represents
A mandatory-update blocker with Update Now and Return Home actions.

### Visible UI structure
- Header: logo with buyer sub-label
- Main content: update-required illustration and explanation
- Cards: none
- Forms: none
- Primary action: update now
- Secondary actions: return home
- Navigation: no bottom bar
- Overlays: none

### Brand validation
Palette/art match Mayush, but `تطبيق المشتري` is prohibited extra branding.

### UX validation
The screen says an update is required to continue yet provides a Home bypass. That contradiction defeats the forced-update gate; the return arrow is also unmirrored.

### Native implementation usability
A real mandatory-update gate is feasible with remote minimum-version config and store deep link, but this screenshot must not guide its behavior.

### Reusable components identified
- MandatoryUpdateGate
- PrimaryButton

### Dynamic backend data required
- Minimum supported/latest version
- Store URL
- Remote gate state/outage fallback

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Update logic | “Required to continue” is contradicted by a Return Home bypass. | Remove the bypass; allow only store update, retry/version recheck and platform-safe app exit/background behavior. |
| MAJOR | Branding | `تطبيق المشتري` modifies the official logo. | Use only the official logo. |
| MAJOR | Resilience | No store-unavailable, offline or version-recheck recovery exists. | Add bounded retry/support fallback without bypassing the minimum-version gate. |
| MINOR | RTL | Return arrow points left. | If any permitted return exists, use correct RTL direction. |

### Canonical recommendation
Reject as an implementation reference; replace with a product-approved mandatory-update gate derived from `11-update-available-ar.png` components but without a bypass.

## 11-wishlist-ar.png

Folder: `11-arabic-rtl/`  
Screen purpose: Browse saved favourite products.  
Probable native route: `/(tabs)/wishlist`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A searchable/sortable two-column grid of twelve saved products with favourite and bag actions.

### Visible UI structure
- Header: logo and notification
- Main content: title, search, result count/sort and product grid
- Cards: six visible product cards
- Forms: search/sort
- Primary action: product cards
- Secondary actions: remove favourite/add to cart
- Navigation: five tabs with Favourites and Profile both present; Profile active
- Overlays: none

### Brand validation
Furniture imagery, official logo, palette, cards and `MAD` pricing fit Mayush.

### UX validation
The screen's largest contradiction is navigation: Profile replaces the canonical Account position while both Profile and Favourites appear, and Profile is active on the Favourites route. Search icon is unmirrored and bag-only actions are ambiguous.

### Native implementation usability
Feasible with a paginated two-column FlatList after restoring the canonical navigator and labeled cart/favourite actions.

### Reusable components identified
- AppHeader
- SearchField
- SortControl
- ProductCard
- FavouriteButton
- AddToCartButton
- BuyerTabBar

### Dynamic backend data required
- Favourite products/count
- Product ID/name/image/price/stock
- Sort/search/pagination/cart state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation structure | Profile appears as a permanent tab instead of canonical Account placement. | Use only Home, Categories, Favourites, Cart, Account. |
| MAJOR | Active state | Profile is active while the screen title is Favourites. | Activate Favourites and ensure route/tab state is controlled by the navigator. |
| MAJOR | Navigation order | The five tabs are not in canonical RTL order. | Render Home, Categories, Favourites, Cart, Account from the right. |
| MAJOR | Action clarity | Small bag icons look interactive but have no label/state. | Use a 48 dp labeled Add to Cart action with stock/loading/error feedback. |
| MINOR | RTL/search | Search magnifier is on the left. | Move it to the right and isolate mixed product names. |

### Canonical recommendation
Correct before use; use the two-column grid as the visual base but replace the complete navigation/action layer.

## Duplicate and version review

No exact duplicate route/state exists within these 46 Arabic files. Related pairs are complementary rather than duplicates:

- `11-add-address-ar.png` and `11-edit-address-ar.png`: share one address-form schema; neither replaces the other. Use the populated labels/data formatting from Edit, the `+212` requirement from the mission, and one mirrored keyboard-aware layout.
- `11-active-sessions-ar.png` and `11-logout-device-confirm-ar.png`: list and confirmation states belong to one revoke-session flow. The confirmation should become a modal over Active Sessions.
- `11-notification-settings-ar.png` and `11-silent-hours-ar.png`: overview and detail routes; use the overview row to enter the corrected time/day editor.
- `11-update-available-ar.png` and `11-update-required-ar.png`: optional and mandatory states are distinct. Optional Update is the component source; Mandatory Update is rejected because it contains a bypass.
- `11-orders-list-ar.png`, `11-order-detail-ar.png`, `11-order-tracking-ar.png` and `11-order-notification-detail-ar.png`: separate hierarchy levels. Share a single order-status/currency/date model rather than copying visible examples.

## Folder totals and verification

- Screenshots enumerated in `11-arabic-rtl/`: **46**
- Screenshots visually opened: **46**
- Per-screen validation entries: **46**
- APPROVED: **0**
- APPROVED_WITH_MINOR_FIXES: **1**
- NEEDS_REWORK: **43**
- REFERENCE_ONLY: **1**
- REJECTED: **1**
- DUPLICATE_ALTERNATIVE: **0**

Count check: `0 + 1 + 43 + 1 + 1 + 0 = 46`.

Highest-priority corrections in this folder:

1. `11-update-required-ar.png` — mandatory-update bypass is a critical contradiction.
2. `11-order-tracking-ar.png` — pending fulfilment steps are falsely labeled completed.
3. `11-order-detail-ar.png` — currency is not `MAD`.
4. `11-slow-connection-ar.png` — blocking state has no bounded recovery action.
5. `11-wishlist-ar.png` — wrong tab structure and active route.
6. `11-payment-method-ar.png` — payment/wallet behavior needs a product decision.

The most common systemic fixes are to centralize one bidi-safe Arabic shell, render the canonical tab sequence from the right as Home → Categories → Favourites → Cart → Account, remove all buyer sub-brand labels, isolate LTR dynamic runs, and provide keyboard/safe-area/state handling through reusable React Native components.
