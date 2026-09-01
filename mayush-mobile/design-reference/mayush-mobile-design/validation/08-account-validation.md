# 08-account Validation Report

> **Fact-check scope:** Currency and address examples are accepted variations. Do not treat them as validation defects by themselves; [fact-check-correction.md](./fact-check-correction.md) supersedes earlier currency/address severity notes.

Folder: `08-account/`  
Total screenshots visually reviewed: 51  
Validation date: 2026-08-02

## Extracted validation rules applied

- Official mark: preserve the exact `MAYUSH DESIGN` logo and proportions; never append `BUYER` or `BUYER APP` in buyer screens.
- Palette: warm cream page background, white cards, soft beige secondary surfaces, orange primary actions (approximately `#D97434`; several boards also show a brighter legacy orange), deep navy text/icons, and semantic green/red/amber/blue only for state meaning.
- Typography: elegant serif display headings plus a clean sans-serif UI/body hierarchy; Arabic needs a proper Arabic UI face, correct RTL order, and mirrored directional controls.
- Components: rounded cards and fields, subtle low-elevation shadows, consistent 2px rounded outline icons, clear labels, inline help/error text, and one dominant CTA.
- Navigation: `Accueil`, `Catégories`, `Favoris`, `Panier`, `Compte` only, in that order for French/LTR; RTL mirrors the layout without replacing destinations.
- Morocco: MAD, `+212`, Moroccan cities/zones/addresses, locale-aware dates, and `Africa/Casablanca` behavior rather than a permanently fixed UTC offset.
- Mobile/native checks from `ui-ux-pro-max`: at least 44pt iOS/48dp Android touch areas, 8dp separation, visible labels, predictable back/close paths, safe-area clearance, fixed-bar content insets, pressed/disabled/loading feedback, Dynamic Type wrapping, semantic accessibility labels/roles, and reusable/virtualized list structures.

The `ui-ux-pro-max` skill materially made the audit stricter on touch-target sizing, destructive-action hierarchy, back-stack integrity, Dynamic Type resilience, fixed bottom-bar clearance, and the distinction between visual controls and their native accessibility semantics.

---

## 08-delete-address-confirmation-fr.png

Folder: `08-account/`  
Screen purpose: Confirm address deletion  
Probable native route: `Account/Addresses/DeleteConfirmation`  
Language: FR  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Full-screen destructive confirmation explaining that past orders will remain unchanged.

### Visible UI structure
- Header: back, logo, notifications
- Main content: title, delete illustration, explanatory copy
- Primary action: Supprimer
- Secondary action: Conserver l’adresse
- Navigation: five-item bottom bar
- Overlays: none

### Brand validation
- Brand palette and illustration are coherent; the destructive CTA is red-orange, but global navigation is inconsistent.

### UX validation
- Consequence copy and cancel route are clear.
- Despite the filename, this is not a dialog; it unnecessarily becomes a long page and preserves unrelated bottom tabs.
- Bottom destinations include `Acheter` and `Commandes`, replacing required destinations.

### Native implementation usability
The content is implementable, but it should be a compact accessible dialog/bottom sheet launched from the address card, with focus management and a loading/destructive state.

### Reusable components identified
- ConfirmationDialog
- DestructiveButton
- SecondaryButton
- WarningIllustration

### Dynamic backend data required
- Address identifier/label
- Default-address flag
- Delete loading/error state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Interaction model | Presented as a full route rather than the named confirmation dialog. | Use a modal/dialog with scrim, explicit close/cancel, and focus restoration. |
| MAJOR | Navigation | Bottom bar uses Acheter and Commandes, omitting Catégories and Panier. | Remove bottom navigation from the destructive dialog. |
| MAJOR | Business rule | Deleting a default/only address is not addressed. | Define replacement/default rules and error recovery. NEEDS PRODUCT DECISION. |

### Canonical recommendation
Correct before use. Retain the consequence copy but convert it to the shared destructive confirmation component.

---

## 08-disconnect-device-confirmation-fr.png

Folder: `08-account/`  
Screen purpose: Confirm remote device/session disconnection  
Probable native route: `Account/Security/Sessions/DisconnectConfirmation`  
Language: FR  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Device-session details and confirmation to revoke access for an iPhone session.

### Visible UI structure
- Header: logo and notifications
- Main content: security illustration, warning copy, session details
- Primary action: Déconnecter cet appareil
- Secondary action: Annuler
- Navigation: none
- Overlays: none

### Brand validation
- Official logo, cream/navy/orange palette, cards, illustration, typography, radii, and shadows align well.

### UX validation
- Device, browser, location, and last activity support an informed decision.
- Copy says the user is logging out “of this device,” which is ambiguous for a remote session.
- The destructive action is brand orange instead of semantic danger red, and there is no header back/close.

### Native implementation usability
Feasible as a dialog or focused confirmation route, with session data, danger CTA, disabled/loading feedback, error recovery, and navigation back to the session list.

### Reusable components identified
- AppHeader
- SecurityWarningHero
- SessionDetailsCard
- DestructiveButton
- SecondaryButton

### Dynamic backend data required
- Session identifier
- Device/browser/location/last activity
- Current-session flag
- Revocation loading/error state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Destructive action | Disconnect uses the ordinary primary-orange style. | Use danger red and require explicit confirmation. |
| MAJOR | Copy | `vous déconnecter de cet appareil` is ambiguous for remote revocation. | Say the selected session/device will be revoked and other sessions remain active. |
| MINOR | Navigation | No top close/back control. | Add a close/back affordance and preserve the originating list state. |

### Canonical recommendation
Correct before use. It is preferred over v2 because it preserves the official logo without prohibited wording; borrow v2’s clearer remote-session explanation.

---

## 08-disconnect-device-confirmation-v2-fr.png

Folder: `08-account/`  
Screen purpose: Confirm remote disconnection, v2 alternative  
Probable native route: `Account/Security/Sessions/DisconnectConfirmation`  
Language: FR  
Screen type:
- Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Alternative confirmation with approximate location, last activity, and an advisory that only one device is affected.

### Visible UI structure
- Header: back, logo, notifications, `BUYER APP`
- Main content: illustration, details card, advisory
- Primary action: Déconnecter
- Secondary action: Annuler
- Navigation: header back
- Overlays: none

### Brand validation
- Core palette/components align, but `BUYER APP` is explicitly prohibited and modifies official logo usage.

### UX validation
- Remote-session consequence is clearer than v1.
- Destructive action remains orange; cancel is visually placed before the dangerous action, which is good.

### Native implementation usability
Usable as content inspiration, but not a complete reference because of branding and danger-hierarchy failures.

### Reusable components identified
- AppHeader
- SessionDetailsCard
- WarningAlert
- DestructiveButton

### Dynamic backend data required
- Session identifier/device/location/last activity
- Revocation state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Branding | Adds `BUYER APP` under the logo. | Remove all added buyer wording and use the approved header logo asset. |
| MAJOR | Destructive action | Disconnect is styled as ordinary primary action. | Apply the semantic danger token. |

### Canonical recommendation
Keep only as an alternative. Transfer its clearer explanation and advisory to `08-disconnect-device-confirmation-fr.png`.

---

## 08-edit-address-form-fr.png

Folder: `08-account/`  
Screen purpose: Edit a saved delivery address  
Probable native route: `Account/Addresses/Edit`  
Language: FR  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Populated address form with city/zone selectors, default toggle, save, and delete.

### Visible UI structure
- Header: back, logo, notifications
- Main content: six populated labeled fields and default toggle
- Primary action: Enregistrer les modifications
- Secondary action: Supprimer cette adresse
- Navigation: four-item bottom bar
- Overlays: none

### Brand validation
- Palette, labels, cards/fields, type hierarchy, toggle, and buttons align well.

### UX validation
- Persistent labels and Morocco phone/location are strong.
- Address value already contains `Appt 5`, then the optional apartment field repeats `Apt 5`.
- Four-tab bar adds Commandes and omits Catégories/Favoris; fixed tabs also compete with form keyboard/actions.

### Native implementation usability
Good form basis using keyboard-aware scroll, select sheets, validation, default mutation, and delete confirmation. Omit global tabs in the focused edit subflow.

### Reusable components identified
- AppHeader
- FormInput
- SelectField
- ToggleRow
- PrimaryButton
- DestructiveTextButton

### Dynamic backend data required
- Saved address fields
- Supported city/zone options
- Default-address state
- Save/delete mutation state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Four-tab bar contains Commandes and lacks required destinations. | Remove it from the form or restore the approved global tabs. |
| MAJOR | Data integrity | Apartment value is duplicated in street address and apartment field. | Normalize address lines and avoid double rendering/storage. |
| MINOR | Forms | Postal code is absent from this edit form although address-list cards show it. | Add the product-approved postal-code field consistently. |

### Canonical recommendation
Correct before use; keep the labeled-field approach and remove incompatible bottom navigation.

---

## 08-edit-profile-form-ar.png

Folder: `08-account/`  
Screen purpose: Edit customer profile in Arabic  
Probable native route: `Account/Profile/Edit`  
Language: AR  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Arabic RTL profile edit with avatar, name, email, phone, preferred language, save, and return link.

### Visible UI structure
- Header: RTL back arrow and logo
- Main content: avatar editor and four labeled fields
- Primary action: save changes
- Secondary action: return to account
- Navigation: header and bottom text navigation
- Overlays: none

### Brand validation
- Official logo, palette, cards, icon family, rounded controls, and Arabic typography largely align.

### UX validation
- Main fields and top back arrow use RTL order correctly.
- Phone prefix `+971` is UAE, contradicting Morocco and `+212`.
- Bottom return arrow points left, not mirrored for RTL.
- Directly editing email/phone conflicts with the separate verification flows.

### Native implementation usability
Feasible with RTL-aware form components and bidi handling for email/phone, but identity-field security/routing needs normalization.

### Reusable components identified
- AppHeader
- AvatarEditor
- FormInput
- PhoneInput
- SelectField
- PrimaryButton

### Dynamic backend data required
- Avatar/name/email/phone/language
- Verification state
- Save/loading/error state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Localization | Uses UAE prefix `+971`. | Use a valid Moroccan `+212` example and locale-aware masking. |
| CRITICAL | RTL | Bottom back arrow is not mirrored. | Point back/return arrow right in Arabic and verify navigation gestures. |
| MAJOR | Security flow | Email and phone appear directly editable despite separate verified-change routes. | Make them read-only links to verified change flows, or remove duplicate routes. NEEDS PRODUCT DECISION. |

### Canonical recommendation
Correct before use. Retain as the Arabic profile reference only after Morocco and RTL/security corrections.

---

## 08-edit-profile-form-fr.png

Folder: `08-account/`  
Screen purpose: Edit customer profile in French  
Probable native route: `Account/Profile/Edit`  
Language: FR  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
French profile form with unsaved-changes alert, full name, email, phone, language, save, and cancel.

### Visible UI structure
- Header: back, logo, notifications
- Main content: info alert, field card, security alert
- Primary action: Enregistrer les modifications
- Secondary action: Annuler les modifications
- Navigation: header back
- Overlays: none

### Brand validation
- Visual system is highly consistent; field states and alerts use coherent tokens.

### UX validation
- Strong labels, helper copy, success indicators, and clear save/cancel actions.
- `+33` and France flag violate Morocco.
- Email/phone direct editing conflicts with separate verification flows.
- `Modifications non enregistrées` is shown without visible changed-field differentiation.

### Native implementation usability
Good reusable form layout with dirty-state protection and keyboard handling, once sensitive-field routing and localized phone data are corrected.

### Reusable components identified
- AppHeader
- InlineAlert
- FormInput
- PhoneInput
- SelectField
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Profile values
- Dirty/validation state
- Email/phone verification state
- Save/loading/error state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Localization | France flag and `+33` contradict the Morocco account context. | Use Morocco `+212`. |
| MAJOR | Security flow | Sensitive email/phone fields bypass dedicated change/OTP screens. | Render read-only summaries linked to secure flows, or consolidate. NEEDS PRODUCT DECISION. |
| MINOR | State clarity | Dirty alert appears without indicating which fields changed. | Show it only after edits and confirm before back/cancel when dirty. |

### Canonical recommendation
Correct before use; it remains the strongest French profile-form layout after sensitive-field and locale fixes.

---

## 08-faq-accordion-questions-fr.png

Folder: `08-account/`  
Screen purpose: Searchable FAQ list with inline answer  
Probable native route: `Support/FAQ`  
Language: FR  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
FAQ search, category chips, accordion questions, one expanded answer, and escalation to support.

### Visible UI structure
- Header: logo and notifications, no back
- Main content: FAQ hero, search, filter chips, accordion card
- Primary action: support escalation card
- Secondary actions: category chips and accordion rows
- Navigation: none
- Overlays: none

### Brand validation
- Strong palette, illustration, typography, chips, cards, icons, and spacing consistency.

### UX validation
- Search and inline accordion interaction are clear and natively feasible.
- Four category chips may need horizontal scrolling on small screens.
- No visible back action.
- Expanded copy claims orders can be modified/cancelled until shipment; that policy is not established.

### Native implementation usability
Strong basis using debounced search and accordion list; FAQ copy/categories must be CMS/backend-driven and product-approved, not hard-coded.

### Reusable components identified
- AppHeader
- SearchField
- FilterChipRow
- FAQAccordion
- SupportEscalationCard

### Dynamic backend data required
- FAQ categories/questions/answers
- Search results
- Support destination/availability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Business behavior | Cancellation/modification-until-shipped policy may be invented. | Validate policy and source FAQ copy from approved content. NEEDS PRODUCT DECISION. |
| MAJOR | Navigation | Deep support screen has no visible back control. | Add shared back header. |
| MINOR | Responsive layout | Chip row may overflow with long translations. | Use horizontally scrollable chips with clear selected state. |

### Canonical recommendation
Correct before use. This is the preferred FAQ-list interaction after policy and navigation validation.

---

## 08-faq-detail-expanded-answer-fr.png

Folder: `08-account/`  
Screen purpose: Long-form FAQ detail article  
Probable native route: `Support/FAQ/:faqId`  
Language: FR  
Screen type:
- Full page

### Status
REFERENCE_ONLY

### Confidence
High

### What the screen represents
Expanded order-tracking FAQ article with steps, related links, orders CTA, and support CTA.

### Visible UI structure
- Header: back, logo, notifications, `BUYER APP`
- Main content: breadcrumb-like title, answer steps, information alert, related links
- Primary action: Voir mes commandes
- Secondary action: Contacter le support
- Navigation: header and in-content links
- Overlays: none

### Brand validation
- Main style is coherent, but `BUYER APP` violates logo/wording rules.

### UX validation
- Step structure is useful, but copy embeds unverified order statuses and notification behavior.
- `Vos informations sont sécurisées...` footer is unrelated to the FAQ task.

### Native implementation usability
Useful article-detail composition using CMS rich text and related-link components. Do not copy the static content or branding verbatim.

### Reusable components identified
- AppHeader
- HelpArticle
- StepList
- InlineInfoAlert
- RelatedLinks
- PrimaryButton

### Dynamic backend data required
- FAQ title/body/steps
- Related articles
- Deep links to orders/support

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Branding | `BUYER APP` is added beneath the logo. | Remove it. |
| MAJOR | Content | Status sequence and per-change notification claim are unverified. | Use approved order-state vocabulary/content. NEEDS PRODUCT DECISION. |
| MINOR | Relevance | Security footer is unrelated. | Remove it from FAQ articles. |

### Canonical recommendation
Keep only as a component/article-layout reference, not as a complete implementation screen.

---

## 08-faq-tab-categories-fr.png

Folder: `08-account/`  
Screen purpose: FAQ list alternative with top tabs  
Probable native route: `Support/FAQ`  
Language: FR  
Screen type:
- Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
FAQ category tabs above collapsed category cards and support CTA.

### Visible UI structure
- Header: back, logo, notifications, `BUYER APP`
- Main content: six top tabs, category accordion cards, support banner
- Primary action: Contacter le support
- Secondary actions: tabs and accordions
- Navigation: five-item bottom bar
- Overlays: none

### Brand validation
- General components align, but added buyer wording violates branding.

### UX validation
- Six labels are cramped in one row and will fail long text/Dynamic Type.
- Bottom navigation orders Panier before Favoris.

### Native implementation usability
Category tabs/chips are reusable, but this complete variant is less resilient than the searchable accordion reference.

### Reusable components identified
- AppHeader
- ScrollableTabs
- FAQAccordion
- SupportBanner
- BottomTabBar

### Dynamic backend data required
- FAQ categories/questions
- Selected category
- Support destination

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Branding | Shows prohibited `BUYER APP` wording. | Remove it. |
| MAJOR | Responsive layout | Six tabs are too dense for mobile and text scaling. | Use scrollable chips or a category selector. |
| MAJOR | Navigation | Panier/Favoris order is wrong. | Normalize required bottom navigation. |

### Canonical recommendation
Keep only as an alternative; prefer `08-faq-accordion-questions-fr.png` and reuse categories as scrollable chips.

---

## 08-help-center-categories-fr.png

Folder: `08-account/`  
Screen purpose: Help center category index  
Probable native route: `Support/Home`  
Language: FR  
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
Help center with search, five support categories, and contact-support escalation.

### Visible UI structure
- Header: logo and notifications
- Main content: support hero, search, category list, support card
- Primary action: Contacter le support
- Secondary actions: search and categories
- Navigation: none
- Overlays: none

### Brand validation
- Excellent alignment with the official palette, illustration language, typography, cards, outline icons, and spacing.

### UX validation
- Search, categories, and escalation create a logical hierarchy.
- No visible back control on a deep support screen.
- Contact CTA must resolve to a defined channel and show loading/error behavior.

### Native implementation usability
Strong reusable reference using search, a data-driven category list, and escalation card. Content should come from approved help data.

### Reusable components identified
- AppHeader
- SupportHero
- SearchField
- HelpCategoryRow
- SupportContactCard

### Dynamic backend data required
- Help categories/descriptions
- Search results
- Support channel/availability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Navigation | Missing top back control. | Add the shared back header. |
| MINOR | Interaction | Contact channel is unspecified. | Route to the product-approved support flow and expose async/error states. NEEDS PRODUCT DECISION. |

### Canonical recommendation
Use directly as the canonical help-center home after the minor navigation/contact definition.

---

## 08-help-center-with-recent-requests-fr.png

Folder: `08-account/`  
Screen purpose: Help-center home alternative with recent tickets  
Probable native route: `Support/Home`  
Language: FR  
Screen type:
- Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Help home combining quick category cards, recent request summaries, support hours, and contact CTA.

### Visible UI structure
- Header: back, logo, notifications
- Main content: hero, search, quick-help grid, recent-request card
- Primary action: Contacter le support
- Secondary actions: categories, tickets, view all
- Navigation: five-item bottom bar
- Overlays: none

### Brand validation
- Visual styling is coherent and ticket badges use semantic colors.

### UX validation
- Recent requests are useful for signed-in users, but the screen is dense.
- Bottom navigation swaps Panier/Favoris.
- Support hours (`lundi-vendredi 9h00-18h00`) are unverified business information.

### Native implementation usability
Reusable ticket-preview section can be added conditionally to the canonical help home. Use a virtualized list for larger ticket histories.

### Reusable components identified
- AppHeader
- SearchField
- QuickHelpCard
- RecentTicketRow
- StatusBadge
- BottomTabBar

### Dynamic backend data required
- Recent support requests and statuses
- Support hours/channel
- Help categories

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Panier/Favoris are reversed. | Normalize bottom tabs or omit them in support subflow. |
| MAJOR | Business content | Support schedule is presented as fact without source. | Validate schedule or remove. NEEDS PRODUCT DECISION. |
| MINOR | Density | Grid plus tickets plus CTA is crowded. | Use progressive disclosure and show only 1–2 recent requests. |

### Canonical recommendation
Keep as an alternative. Add its recent-request module to `08-help-center-categories-fr.png` only when real ticket data exists.

---

## 08-help-support-faq-categories-fr.png

Folder: `08-account/`  
Screen purpose: Combined help/FAQ home alternative  
Probable native route: `Support/Home`  
Language: FR  
Screen type:
- Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Help/FAQ home with search, five compact FAQ-category tiles, quick actions, and direct contact details.

### Visible UI structure
- Header: back, logo, notifications
- Main content: support hero, search, category tile row, quick-action list, contact card
- Primary action: contact methods
- Secondary actions: categories and quick actions
- Navigation: header back
- Overlays: none

### Brand validation
- Palette and component styling align, but the five-tile row is unusually dense.

### UX validation
- Search and quick actions are clear.
- Five narrow tiles will clip long French/Arabic labels and create small targets.
- Email/phone and response-time claims are unverified and may be invented.

### Native implementation usability
Useful modules, but the static five-column layout is not responsive. Use horizontal cards or a two-column grid and backend-configured contact details.

### Reusable components identified
- AppHeader
- SearchField
- HelpCategoryCard
- QuickActionRow
- ContactInfoCard

### Dynamic backend data required
- FAQ category counts
- Support email/phone/hours/SLA
- Quick-action destinations

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Responsive layout | Five fixed tiles are too narrow for mobile and translation. | Use two columns or a horizontal scroll with 44/48pt targets. |
| MAJOR | Invented information | `support@mayush.design`, phone number, hours, and response time are unverified. | Bind to approved support configuration. NEEDS PRODUCT DECISION. |

### Canonical recommendation
Keep only as an alternative; use `08-help-center-categories-fr.png` as the help-home reference.

---

## 08-language-region-preferences-fr.png

Folder: `08-account/`  
Screen purpose: Combined language, country, currency, and date preferences  
Probable native route: `Account/Preferences/Locale`  
Language: FR  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Locale settings with language radios, MAD/Morocco/date-time rows, and save CTA.

### Visible UI structure
- Header: back, logo, notifications
- Main content: language radio group and region-preference card
- Primary action: Enregistrer les préférences
- Secondary actions: currency/country/date rows
- Navigation: header back
- Overlays: none

### Brand validation
- Visual system, radios, cards, icon circles, spacing, and CTA align well.

### UX validation
- MAD and Morocco are correct.
- English is offered although the source foundation establishes French and Arabic only.
- Currency and country rows look editable, potentially allowing unsupported non-Moroccan contexts.
- Date example is static and should reflect locale/device time.

### Native implementation usability
Feasible with radio/select components and locale rerendering. Supported languages/regions must be driven by configuration, and fixed values should not appear interactive.

### Reusable components identified
- AppHeader
- RadioGroup
- PreferenceRow
- PrimaryButton

### Dynamic backend data required
- Supported languages/locales
- Supported countries/currencies
- User locale/date format

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Product scope | English appears unsupported by the FR/AR foundation. | Remove or confirm English localization completeness. NEEDS PRODUCT DECISION. |
| MAJOR | Affordance | Morocco/MAD look changeable despite fixed marketplace context. | Make them read-only or expose only supported options. |
| MINOR | Dynamic data | Date/time example is a fixed value. | Render a live localized example. |

### Canonical recommendation
Correct before use. Retain as the broader locale-preferences route after supported-locale decisions.

---

## 08-language-selection-3-languages-fr.png

Folder: `08-account/`  
Screen purpose: App language selection  
Probable native route: `Account/Preferences/Language`  
Language: FR  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Three-language radio selection with immediate-change notice and bottom navigation.

### Visible UI structure
- Header: back, logo, notifications
- Main content: language hero, radio-list card, immediate-change alert
- Primary action: selecting a language
- Secondary actions: back
- Navigation: five-item bottom bar
- Overlays: none

### Brand validation
- Strong alignment across palette, logo, illustration, type, radios, cards, and alert.

### UX validation
- Current language is clear.
- No save CTA is consistent with the stated immediate behavior, but selection needs a confirmation/loading/re-render state.
- English is unconfirmed; bottom navigation reverses Panier/Favoris.

### Native implementation usability
Most feasible language-selection variant: use configuration-driven locales and immediately re-render with preserved navigation state.

### Reusable components identified
- AppHeader
- LanguageHero
- RadioList
- InfoAlert
- BottomTabBar

### Dynamic backend data required
- Supported languages
- Current locale
- Locale-change loading/error state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Product scope | English support is not established. | Remove or confirm complete English localization. NEEDS PRODUCT DECISION. |
| MAJOR | Navigation | Panier/Favoris order is incorrect. | Normalize the five tabs or omit them in this subflow. |
| MINOR | State feedback | Immediate language switch has no loading/error feedback. | Show progress, persist choice, and preserve the back stack. |

### Canonical recommendation
Correct before use. This is the preferred language-selection interaction if reduced to approved locales and corrected navigation.

---

## 08-language-selection-interface-preview-fr.png

Folder: `08-account/`  
Screen purpose: Language-selection alternative with large interface preview  
Probable native route: `Account/Preferences/Language`  
Language: FR  
Screen type:
- Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
French/Arabic selector with miniature home/account interface previews and explicit save.

### Visible UI structure
- Header: logo and notifications, no back
- Main content: language hero, two options, miniature interface preview
- Primary action: Enregistrer la langue
- Secondary actions: language radios
- Navigation: none
- Overlays: none

### Brand validation
- Colors/components align, but the preview contains tiny screenshot-like UI and product imagery.

### UX validation
- Preview attempts to explain language impact, but text is too small to read/access and does not demonstrate actual RTL.
- No back control.

### Native implementation usability
Avoid implementing a static miniature screenshot. If preview is retained, use a small live sample card that switches alignment/text and remains accessible.

### Reusable components identified
- AppHeader
- LanguageOption
- LocalePreviewCard
- PrimaryButton

### Dynamic backend data required
- Supported locales
- Current/pending locale
- Localized sample strings

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Native feasibility | Dense miniature UI behaves like a static poster and cannot scale with Dynamic Type. | Replace with a simple live translated/RTL sample. |
| MAJOR | Navigation | No visible back path. | Add shared back header. |

### Canonical recommendation
Keep only as an alternative; use the simpler three-language layout and optionally reuse a reduced live preview.

---

## 08-language-selection-with-preview-fr.png

Folder: `08-account/`  
Screen purpose: Language-selection alternative with bilingual row preview  
Probable native route: `Account/Preferences/Language`  
Language: FR  
Screen type:
- Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
French/Arabic selector, simple interface preview, immediate-change notice, save, and bottom tabs.

### Visible UI structure
- Header: back, logo, notifications, `BUYER APP`
- Main content: options card, preview rows, alert
- Primary action: Enregistrer
- Secondary actions: language choices
- Navigation: five-item bottom bar
- Overlays: none

### Brand validation
- Core style aligns, but prohibited buyer wording is present.

### UX validation
- Preview is understandable, but Arabic row still uses a right-pointing chevron rather than mirroring.
- Bottom navigation replaces Catégories/Panier with Commandes/Messages.

### Native implementation usability
Simple preview is feasible, but full screenshot is unsafe as a reference due to branding, RTL, and information-architecture defects.

### Reusable components identified
- AppHeader
- LanguageOption
- LocalePreviewRow
- InfoAlert
- BottomTabBar

### Dynamic backend data required
- Supported languages
- Current locale
- Preview translations

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Branding | Shows `BUYER APP`. | Remove it. |
| CRITICAL | RTL | Arabic preview chevron is not mirrored. | Mirror direction and row order for Arabic. |
| MAJOR | Navigation | Commandes/Messages replace approved tabs. | Use approved bottom navigation or omit it. |

### Canonical recommendation
Keep only as an alternative; do not use as the full implementation reference.

---

## 08-logout-confirmation-dialog-fr.png

Folder: `08-account/`  
Screen purpose: Confirm account logout  
Probable native route: `Account/LogoutConfirmation`  
Language: FR  
Screen type:
- Dialog concept rendered as a full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Logout confirmation with a large illustration, cancel, and logout actions.

### Visible UI structure
- Header: back, logo, notifications, `BUYER APP`
- Main content: illustration and confirmation copy
- Primary action: Se déconnecter
- Secondary action: Annuler
- Navigation: header back
- Overlays: none

### Brand validation
- Overall visual direction aligns, but added buyer wording violates the branding rule.

### UX validation
- Choice is clear, yet the named dialog is a tall page with no scrim/context.
- Logout is destructive to session continuity but uses ordinary primary orange.
- Large illustration delays the decision and is fragile with larger text.

### Native implementation usability
Implement as a compact native alert/dialog or bottom sheet with accessible focus, cancel default, async state, and error recovery.

### Reusable components identified
- ConfirmationDialog
- DestructiveButton
- SecondaryButton

### Dynamic backend data required
- Current authentication state
- Logout loading/error state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Branding | `BUYER APP` is added to the logo area. | Remove it. |
| MAJOR | Interaction model | Full-screen poster does not match a confirmation dialog. | Use a compact modal/bottom sheet with scrim and focus restoration. |
| MAJOR | Action hierarchy | Logout uses primary-orange rather than danger styling. | Use semantic destructive styling and keep cancel visually safer/default. |

### Canonical recommendation
Correct before use; retain only the core title/copy/actions in the shared confirmation component.

---

## 08-marketing-preferences-cart-reminders-fr.png

Folder: `08-account/`  
Screen purpose: Marketing communication preferences  
Probable native route: `Account/Preferences/Marketing`  
Language: FR  
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
Four marketing-category toggles, privacy note, and explicit save.

### Visible UI structure
- Header: back, logo, notifications
- Main content: marketing hero, toggle card, privacy alert
- Primary action: Enregistrer mes préférences
- Secondary actions: four switches
- Navigation: header back
- Overlays: none

### Brand validation
- Strong alignment in palette, illustration, type, card, toggle, spacing, and CTA.

### UX validation
- Categories are understandable and cart reminders are clearly separated.
- `NEW` text embedded in a decorative badge icon is inconsistent and not localization-friendly.
- Saving should provide loading/success/error feedback and switches need state semantics.

### Native implementation usability
Strong React Native reference using a settings list, native switches, privacy copy, and mutation feedback.

### Reusable components identified
- AppHeader
- PreferencesHero
- ToggleSettingRow
- InfoAlert
- PrimaryButton

### Dynamic backend data required
- Preference values
- Consent/version timestamp if required
- Save/loading/error state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Icons/localization | `NEW` is embedded in an icon. | Use the shared tag/star icon and keep translated text in the row label. |
| MINOR | Feedback | Save result is not represented. | Add disabled/loading and accessible success/error feedback. |

### Canonical recommendation
Use directly as the canonical marketing-preferences screen after minor icon and feedback corrections.

---

## 08-marketing-preferences-detailed-fr.png

Folder: `08-account/`  
Screen purpose: Marketing preferences alternative, detailed  
Probable native route: `Account/Preferences/Marketing`  
Language: FR  
Screen type:
- Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Five marketing/push categories with switches, promotional copy, and save.

### Visible UI structure
- Header: logo and notifications, no back
- Main content: hero, toggle list, note
- Primary action: Enregistrer mes préférences
- Secondary actions: switches
- Navigation: none
- Overlays: none

### Brand validation
- Visually consistent; star/tag/product icons broaden the visual vocabulary but remain close to the outline set.

### UX validation
- Missing back control.
- `Notifications push` is a delivery channel mixed into marketing content categories, overlapping notification settings.
- `Aucun spam, promis !` is a marketing/legal promise requiring approval.

### Native implementation usability
List is feasible, but the channel/category model must be normalized with the canonical notification and marketing settings.

### Reusable components identified
- PreferencesHero
- ToggleSettingRow
- InfoAlert
- PrimaryButton

### Dynamic backend data required
- Marketing consent/category values
- Available channels
- Save state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Information architecture | Push channel is mixed with marketing topics and duplicates notification preferences. | Separate communication channel from topic consent. NEEDS PRODUCT DECISION. |
| MAJOR | Navigation | No back/close control. | Add shared back header. |
| MINOR | Legal copy | `Aucun spam, promis !` is an unverified promise. | Replace with approved consent/privacy wording. |

### Canonical recommendation
Keep only as an alternative; prefer `08-marketing-preferences-cart-reminders-fr.png`.

---

## 08-marketing-preferences-toggles-fr.png

Folder: `08-account/`  
Screen purpose: Marketing preferences alternative grouped by promotions/partners  
Probable native route: `Account/Preferences/Marketing`  
Language: FR  
Screen type:
- Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Grouped promotion and partner marketing toggles with save and persistent bottom navigation.

### Visible UI structure
- Header: back, logo, notifications
- Main content: two preference cards and info alert
- Primary action: Enregistrer les préférences
- Secondary actions: switches
- Navigation: five-item bottom bar
- Overlays: none

### Brand validation
- Core visual language is consistent.

### UX validation
- Partner offers are a distinct consent, but partner marketing is not confirmed.
- Bottom tabs replace Catégories/Panier with Commandes/Messages.

### Native implementation usability
Grouped cards are reusable; the complete page should not guide implementation because of unsupported navigation/product scope.

### Reusable components identified
- AppHeader
- PreferenceSection
- ToggleSettingRow
- PrimaryButton
- BottomTabBar

### Dynamic backend data required
- Promotional/channel/partner consent values
- Save state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Commandes/Messages replace required tabs. | Normalize or remove bottom navigation. |
| MAJOR | Product scope | Partner-offer consent assumes a partner program. | Confirm before exposing. NEEDS PRODUCT DECISION. |

### Canonical recommendation
Keep only as an alternative; reuse grouped consent sections only if approved.

---

## 08-my-addresses-list-ar.png

Folder: `08-account/`  
Screen purpose: Saved-address list in Arabic  
Probable native route: `Account/Addresses`  
Language: AR  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Arabic RTL address list with default badge, edit/delete/default actions, add address, and bottom tabs.

### Visible UI structure
- Header: back, logo, notifications
- Main content: add-address outline button and two address cards
- Primary action: add new address
- Secondary actions: edit, delete, set default
- Navigation: five-item bottom bar
- Overlays: none

### Brand validation
- Palette, typography, cards, badges, and icons align; Arabic address/phone content remains readable.

### UX validation
- Main card contents flow RTL.
- Top back arrow incorrectly points left.
- Bottom bar includes Store, Orders, and Cart variants while omitting Accueil/Catégories.
- Destructive buttons are clear but need confirmation.

### Native implementation usability
Feasible with RTL card rows and virtualized address list after navigation mirroring and destination corrections.

### Reusable components identified
- AppHeader
- AddressCard
- StatusBadge
- PrimaryButton
- BottomTabBar

### Dynamic backend data required
- Saved addresses/labels/phones
- Default-address flag
- CRUD mutation states

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | RTL | Header back arrow is not mirrored. | Point it right and support RTL swipe-back semantics. |
| MAJOR | Navigation | Permanent destinations do not match the approved buyer tabs. | Use mirrored Accueil, Catégories, Favoris, Panier, Compte. |
| MINOR | Interaction | Delete actions need an accessible confirmation/loading state. | Open the shared delete dialog. |

### Canonical recommendation
Correct before use. Keep as the Arabic address-list reference after RTL/navigation normalization.

---

## 08-my-addresses-list-labels-fr.png

Folder: `08-account/`  
Screen purpose: Saved-address list, three-card alternative  
Probable native route: `Account/Addresses`  
Language: FR  
Screen type:
- Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Three address cards with type labels, default badges, inline edit/default/delete actions, and add button.

### Visible UI structure
- Header: back, logo, notifications
- Main content: three detailed address cards
- Primary action: Ajouter une adresse
- Secondary actions: edit, set default, delete, overflow
- Navigation: header back
- Overlays: none

### Brand validation
- Strong visual consistency with the source system.

### UX validation
- Cards are information-rich but three inline actions plus overflow are redundant and crowded.
- Default card still shows `Définir par défaut`, contradicting its badge.
- Phone numbers omit the required `+212` prefix.

### Native implementation usability
Useful action/label ideas, but v2 is a cleaner data hierarchy. Keep actions in an overflow or two-action footer with adequate hit areas.

### Reusable components identified
- AppHeader
- AddressCard
- AddressTypeBadge
- OverflowMenu
- PrimaryButton

### Dynamic backend data required
- Saved addresses and labels
- Default flag
- Address CRUD states

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | State contradiction | Default address also offers `Définir par défaut`. | Hide/disable the action when already default. |
| MAJOR | Touch density | Edit/default/delete plus overflow overload each card. | Consolidate actions and keep 44/48pt targets. |
| MINOR | Localization | Phones display local format without `+212`. | Use canonical international display consistently. |

### Canonical recommendation
Keep only as an alternative; prefer v2 and carry over address-type labels.

---

## 08-my-addresses-list-v2-fr.png

Folder: `08-account/`  
Screen purpose: Saved-address list, preferred French variant  
Probable native route: `Account/Addresses`  
Language: FR  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Two spacious address cards, default badge, explicit edit/delete/default actions, add address, and bottom tabs.

### Visible UI structure
- Header: back, logo, notifications
- Main content: add-address button and two cards
- Primary action: Ajouter une adresse
- Secondary actions: edit, set default, delete, overflow
- Navigation: five-item bottom bar
- Overlays: none

### Brand validation
- Excellent palette, typography, card spacing, icon circles, badges, radii, and shadows.

### UX validation
- Clearer than the three-card alternative and uses `+212`.
- Bottom navigation replaces Panier with `Mes commandes`.
- First card lacks set-default action appropriately; second lacks visible edit action despite overflow, making action models inconsistent.

### Native implementation usability
Strong list/card basis. Use a `FlatList`, consistent overflow actions, default badge, and shared delete confirmation; normalize bottom tabs.

### Reusable components identified
- AppHeader
- AddressCard
- StatusBadge
- OverflowMenu
- PrimaryButton
- BottomTabBar

### Dynamic backend data required
- Saved addresses/phones/labels
- Default flag
- Address CRUD states

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | `Mes commandes` replaces Panier. | Restore required five destinations/order. |
| MINOR | Action consistency | Visible card actions differ without explanation. | Use the same overflow/action pattern per card, conditional only for default state. |

### Canonical recommendation
Correct before use. This is the canonical French address-list reference after navigation/action normalization.

---

## 08-my-information-personal-details-fr.png

Folder: `08-account/`  
Screen purpose: Read-only personal account information  
Probable native route: `Account/Profile`  
Language: FR  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Profile details and verification states, with edit profile and security destinations.

### Visible UI structure
- Header: back, logo, notifications
- Main content: profile/identity card and two action cards
- Primary action: Modifier le profil
- Secondary action: Sécurité et confidentialité
- Navigation: four-item bottom bar
- Overlays: none

### Brand validation
- Palette, card system, status badges, icons, spacing, and hierarchy align well.

### UX validation
- Read-only presentation is clear but repeats email in header and detail row.
- `Vérification du compte` is vague and duplicates phone verification.
- Bottom navigation uses Accueil, Mes commandes, Notifications, Profil—an incompatible four-tab model.

### Native implementation usability
Feasible as a reusable read-only details list. Verification types must be explicit and all dates/statuses dynamic.

### Reusable components identified
- AppHeader
- ProfileSummaryCard
- InfoDetailRow
- StatusBadge
- ActionCard
- BottomTabBar

### Dynamic backend data required
- Name/email/phone/language
- Account creation date
- Verification states

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Four incompatible permanent destinations. | Restore approved buyer bottom tabs. |
| MAJOR | State clarity | Generic account verification is undefined and duplicates phone status. | Label exact verification method/status. NEEDS PRODUCT DECISION. |
| MINOR | Redundancy | Email appears twice in the same card. | Remove one occurrence or use header space for avatar/status only. |

### Canonical recommendation
Correct before use; retain as the canonical read-only profile detail screen after information and navigation cleanup.

---

## 08-account-dashboard-profile-menu-ar.png

Folder: `08-account/`  
Screen purpose: Signed-in account dashboard in Arabic  
Probable native route: `Account/Home`  
Language: AR  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Arabic account home with profile summary, account destinations, logout, and persistent buyer navigation.

### Visible UI structure
- Header: notification icon and centered logo
- Main content: profile card and account menu rows
- Cards: separate white cards per destination
- Primary action: view profile
- Secondary actions: orders, favorites, addresses, notifications, personal data, security, language, help, logout
- Navigation: five-item RTL bottom bar
- Overlays: none

### Brand validation
- Logo, cream background, white cards, orange accents, navy type, rounded icon style, spacing, and soft shadows are recognizable.
- Arabic typography is readable and the main menu flows RTL.

### UX validation
- The user goal and row affordances are clear.
- Directional row chevrons are correctly pointed toward the RTL destination side.
- The bottom navigation is incompatible: `طلباتي` (Orders) replaces `Catégories`.
- The page is tall and must scroll with bottom inset; all rows need 44/48pt hit areas and accessible labels.

### Native implementation usability
Feasible with a `ScrollView`/`FlatList`, reusable account rows, profile card, and shared bottom tabs. Content and chevrons must be driven by RTL-aware layout tokens.

### Reusable components identified
- AppHeader
- ProfileSummaryCard
- AccountMenuRow
- LogoutRow
- BottomTabBar

### Dynamic backend data required
- Customer name
- Customer email
- Current language
- Notification badge/count if used

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Orders replaces the required Categories destination in the permanent bottom bar. | Use the mirrored five destinations: Accueil, Catégories, Favoris, Panier, Compte. |
| MINOR | Layout | The header omits visible OS status-bar treatment while most related screens include it. | Use the real platform status bar/safe-area rather than baking chrome into the reference. |
| MINOR | Accessibility | Compact icon/chevron controls do not prove 44/48pt targets or screen-reader labels. | Implement padded `Pressable` targets with Arabic accessibility labels and selected-state semantics. |

### Canonical recommendation
Correct before use. Keep as the Arabic account-home reference after normalizing bottom navigation and safe areas.

---

## 08-account-dashboard-profile-menu-fr.png

Folder: `08-account/`  
Screen purpose: Signed-in account dashboard in French  
Probable native route: `Account/Home`  
Language: FR  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
French account home with identity/contact information, completion progress, menu groups, logout, and account bottom navigation.

### Visible UI structure
- Header: centered logo and notification icon
- Main content: profile/completion card and grouped account rows
- Cards: profile card, two menu groups, logout card
- Primary action: add profile photo
- Secondary actions: account destinations and logout
- Navigation: four-item bottom bar
- Overlays: none

### Brand validation
- The palette, card language, serif/sans hierarchy, outline icons, radii, and shadows align with the foundation.
- The logo is preserved but dominates the header vertically.

### UX validation
- Grouping and row affordances are clear.
- The four-tab bar omits `Favoris`, violating the final information architecture.
- The visible `+33` number contradicts the Moroccan `+212` context.
- Long content needs scrolling and bottom inset; the logout row must be separated semantically as destructive.

### Native implementation usability
Feasible with reusable profile-progress and menu-row components. Completion percentage and missing-action copy must be calculated from backend profile requirements, not embedded.

### Reusable components identified
- AppHeader
- ProfileSummaryCard
- ProfileCompletionRing
- AccountMenuSection
- AccountMenuRow
- BottomTabBar

### Dynamic backend data required
- Customer initials/name/email/phone
- Email verification state
- Profile completion percentage and missing fields
- Current language
- Notification and cart counts

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Localization | Phone uses France prefix `+33`. | Replace example with a realistic Moroccan `+212` number and format from locale data. |
| MAJOR | Navigation | Bottom navigation has four tabs and omits `Favoris`. | Restore exactly Accueil, Catégories, Favoris, Panier, Compte. |
| MINOR | Header | Oversized logo consumes substantial first-screen space. | Use the shared compact header-logo token and preserve clear space. |

### Canonical recommendation
Correct before use. This is the preferred French account-home composition once phone and navigation defects are fixed.

---

## 08-account-guest-welcome-login-fr.png

Folder: `08-account/`  
Screen purpose: Guest account landing and authentication entry  
Probable native route: `Account/Guest`  
Language: FR  
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
Guest-facing account page offering sign-in, registration, continued browsing, language, and help.

### Visible UI structure
- Header: logo and notification icon
- Main content: furniture illustration, welcome copy
- Cards: language/help group
- Primary action: Se connecter
- Secondary actions: Créer un compte, Continuer à explorer, language, help
- Navigation: none
- Overlays: none

### Brand validation
- Strong visual alignment with the cream/orange/navy premium furniture direction and official logo.
- Button and card styles are consistent with the foundation.

### UX validation
- Authentication choices are clear and ordered well.
- `Continuer à explorer` provides an escape route, so absence of bottom tabs is defensible.
- A notification icon for a guest is unexplained and likely nonfunctional.
- The large hero creates a long first interaction on smaller devices.

### Native implementation usability
Straightforward with shared auth buttons, an illustration asset, and account utility rows. Use a scroll container, Dynamic Type wrapping, and safe-area padding.

### Reusable components identified
- AppHeader
- EmptyGuestHero
- PrimaryButton
- SecondaryButton
- UtilityMenuRow

### Dynamic backend data required
- Current app language
- Authentication state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | UX | Notification icon has no clear value for a signed-out guest. | Hide it when unauthenticated or define a guest notification destination. |
| MINOR | Layout | Large hero may push primary actions below the fold on short phones or large text settings. | Reduce hero height responsively and keep actions scrollable without clipping. |

### Canonical recommendation
Use directly as the guest-account reference after the minor guest-header and responsive-height corrections.

---

## 08-account-security-overview-fr.png

Folder: `08-account/`  
Screen purpose: Account security landing alternative  
Probable native route: `Account/Security`  
Language: FR  
Screen type:
- Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Illustrated security overview with verification states, connected-device/session links, and an additional-authentication prompt.

### Visible UI structure
- Header: back, logo, notifications
- Main content: security illustration and six security rows
- Cards: one card per security topic
- Primary action: none
- Secondary actions: password, phone/email verification, devices, sessions, additional authentication
- Navigation: five-item bottom bar
- Overlays: none

### Brand validation
- Brand colors, illustration style, card surfaces, badges, icons, and type hierarchy are coherent.

### UX validation
- Security topics are understandable, but `Appareils connectés` and `Sessions actives` are not differentiated enough.
- `Authentification supplémentaire` is vague compared with the standard `Vérification en deux étapes` terminology.
- Bottom-tab order swaps `Panier` and `Favoris`.

### Native implementation usability
Feasible with security menu rows and status badges, but it duplicates the broader security/privacy screens and would fragment routing.

### Reusable components identified
- AppHeader
- SecurityHero
- SettingsRow
- StatusBadge
- BottomTabBar

### Dynamic backend data required
- Email verification state
- Phone verification state
- Active device/session counts
- Two-step verification state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Bottom tabs place Panier before Favoris. | Use the required global order. |
| MAJOR | Information architecture | Devices and active sessions appear as overlapping destinations. | Merge them or define the distinction in labels/helper copy. |
| MINOR | Copy | `Authentification supplémentaire` is imprecise. | Use `Vérification en deux étapes` or the product-approved security method. |

### Canonical recommendation
Keep only as an alternative. Replace with `08-security-privacy-full-menu-fr.png`, incorporating the verification badges/2FA state if needed.

---

## 08-account-settings-menu-photo-fr.png

Folder: `08-account/`  
Screen purpose: Account settings menu with customer photo  
Probable native route: `Account/Settings`  
Language: FR  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Account-settings index with customer contact summary, edit profile, settings destinations, logout, and bottom tabs.

### Visible UI structure
- Header: logo and notification icon
- Main content: title, profile card, settings list, logout
- Cards: profile, settings group, logout
- Primary action: edit profile icon
- Secondary actions: settings rows and logout
- Navigation: five-item bottom bar
- Overlays: none

### Brand validation
- Palette, typography, cards, icon circles, spacing, and soft shadows match the source system.
- The circular image depicts furniture rather than a plausible customer avatar.

### UX validation
- Hierarchy is clear and rows are generously sized.
- Bottom tabs put Panier before Favoris.
- No explicit back control is acceptable only if this is the Compte root; route ownership must be consistent with the account dashboard.

### Native implementation usability
Feasible as a data-driven settings list. The profile photo must be a user avatar URI or initials fallback, never a fixed furniture crop.

### Reusable components identified
- AppHeader
- ProfileSummaryCard
- SettingsSection
- SettingsRow
- LogoutRow
- BottomTabBar

### Dynamic backend data required
- Customer avatar/name/email/phone
- Notification badge/count
- Available settings destinations

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Required Favoris/Panier order is reversed. | Restore Accueil, Catégories, Favoris, Panier, Compte. |
| MINOR | Content | Furniture photo reads as product imagery, not a profile photo. | Use the customer avatar or initials fallback. |
| MINOR | Information architecture | This competes with the account-dashboard screenshot as the Compte root. | Decide one Compte-root hierarchy and deep-link the other as Settings if retained. |

### Canonical recommendation
Correct before use and combine its settings grouping with the preferred account dashboard; do not create two competing Compte roots.

---

## 08-active-sessions-devices-list-fr.png

Folder: `08-account/`  
Screen purpose: Active-session list, first variant  
Probable native route: `Account/Security/Sessions`  
Language: FR  
Screen type:
- Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Four-device session list with current-device highlighting, per-row overflow menus, a security warning, and bulk session disconnection.

### Visible UI structure
- Header: logo and notifications, no top back button
- Main content: security hero, session card, warning
- Cards: combined session list and advisory card
- Primary action: disconnect all other sessions
- Secondary actions: overflow menus and bottom return link
- Navigation: bottom text return only
- Overlays: none

### Brand validation
- Colors, hero illustration, cards, icon treatment, and badges fit the system.

### UX validation
- Device metadata is informative and the current session is distinguishable.
- Current state is redundantly labeled `Actuelle` and `Session en cours`.
- Per-device actions are hidden in small overflow targets; the bulk destructive CTA is orange rather than danger red.
- Top-level back behavior is delayed until the bottom of a long page.

### Native implementation usability
Feasible using a `FlatList`, session cards, and confirmation dialogs. It should not be copied wholesale because destructive and navigation behavior need normalization.

### Reusable components identified
- AppHeader
- SecurityHero
- DeviceSessionRow
- StatusBadge
- AlertCard
- DestructiveButton

### Dynamic backend data required
- Session/device identifier
- Device model, OS, browser
- Approximate location
- Last activity timestamp
- Current-session flag

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Destructive action | Bulk session revocation uses the primary orange style. | Use semantic danger styling and a confirmation dialog. |
| MAJOR | Navigation | No immediately reachable back control at the top. | Add the shared back header and preserve scroll state on return. |
| MINOR | State clarity | Current device has two redundant badges. | Keep one `Appareil actuel` badge from the status system. |
| MINOR | Accessibility | Vertical overflow icons appear too small as standalone targets. | Expand hit areas and provide explicit accessible action labels. |

### Canonical recommendation
Keep only as an alternative. Prefer the v2 list structure, but carry over OS/browser detail and the bulk-security option as secondary functionality.

---

## 08-active-sessions-devices-v2-fr.png

Folder: `08-account/`  
Screen purpose: Active-session list, second variant  
Probable native route: `Account/Security/Sessions`  
Language: FR  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Current and remote connected-device cards with visible per-device disconnect actions.

### Visible UI structure
- Header: back, logo, notifications
- Main content: security hero, advisory alert, device cards, password reminder
- Cards: one card per device
- Primary action: none
- Secondary actions: disconnect each remote device
- Navigation: header back
- Overlays: none

### Brand validation
- Layout, palette, illustration, type hierarchy, cards, and icon circles align well.
- A full-color Chrome logo departs from the shared outline icon family, though browser-brand marks can be semantically justified.

### UX validation
- Visible disconnect actions improve discoverability over overflow menus.
- The current iPhone row omits device/OS detail while the alternative includes it.
- Destructive links use brand orange and need confirmation; dynamic list length requires scrolling.

### Native implementation usability
Good reusable basis using a virtualized device list and a remote-session confirmation route/dialog. Device icon/brand rendering should be tokenized and data-driven.

### Reusable components identified
- AppHeader
- SecurityHero
- AlertCard
- DeviceSessionCard
- TextAction
- ConfirmationDialog

### Dynamic backend data required
- Session identifier
- Device and browser labels
- Approximate city/country
- Last activity timestamp
- Current-session flag

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Destructive action | Remote disconnect links are orange and have no visible confirmation step. | Use danger styling and open the canonical disconnect confirmation. |
| MINOR | Data hierarchy | Current-device row lacks OS/browser metadata available in the alternative. | Show consistent device, OS/browser, location, and last activity fields. |
| MINOR | Icons | Browser brand icon is stylistically different from all other device icons. | Allow branded glyphs only in a consistent container, with accessible browser text. |

### Canonical recommendation
Correct before use. This is the preferred active-sessions structure; combine it with metadata/bulk-revocation ideas from `08-active-sessions-devices-list-fr.png`.

---

## 08-add-address-form-v2-fr.png

Folder: `08-account/`  
Screen purpose: Add delivery address, detailed variant  
Probable native route: `Account/Addresses/Add`  
Language: FR  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Address creation form with Moroccan phone prefix, city and delivery-zone selectors, street address, and optional apartment/floor.

### Visible UI structure
- Header: back, logo, notifications
- Main content: stacked form cards
- Cards: one surface per input
- Primary action: Enregistrer l’adresse
- Secondary actions: selectors and back
- Navigation: five-item bottom bar
- Overlays: none

### Brand validation
- Strong palette, typography, card, icon, radius, and CTA consistency.

### UX validation
- `+212` and separate city/zone selectors fit the Moroccan delivery model.
- Inputs rely primarily on placeholder text; labels are not persistent after entry.
- Postal code and default-address controls from the simpler variant are absent.
- Bottom nav replaces Favoris with Commandes and may collide with the keyboard/CTA.

### Native implementation usability
Feasible using keyboard-aware scrolling, labeled inputs, native pickers/bottom sheets, validation, and an address mutation. Fixed bottom navigation should be omitted in this focused subflow.

### Reusable components identified
- AppHeader
- FormInput
- PhoneInput
- SelectField
- PrimaryButton
- BottomTabBar

### Dynamic backend data required
- Customer name/phone defaults
- Supported Moroccan cities
- Delivery zones by city
- Address validation rules

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Permanent bottom bar includes Commandes and omits Favoris. | Remove bottom tabs from this form or restore the approved five tabs. |
| MAJOR | Forms | Placeholder-only presentation will lose field identity after input and under autofill. | Add persistent labels, required indicators, helper/error text, and semantic keyboards. |
| MAJOR | Data capture | No postal code or default-address control is shown. | Confirm required address schema; add postal code and default toggle if supported. |

### Canonical recommendation
Correct before use. Use as the preferred add-address base, combined with postal/default controls from `08-add-address-simple-form-fr.png`.

---

## 08-add-address-simple-form-fr.png

Folder: `08-account/`  
Screen purpose: Add delivery address, simplified alternative  
Probable native route: `Account/Addresses/Add`  
Language: FR  
Screen type:
- Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Simplified six-field delivery-address form with postal code and a default-address checkbox.

### Visible UI structure
- Header: back, logo, notifications
- Main content: stacked input outlines and checkbox
- Cards: none beyond field surfaces
- Primary action: Enregistrer l’adresse
- Secondary actions: default checkbox and back
- Navigation: none
- Overlays: none

### Brand validation
- Palette, logo, typography, icons, and CTA are aligned; inputs are flatter than most account cards.

### UX validation
- Focused subflow appropriately omits bottom tabs.
- Phone has no visible `+212`; city is unstructured free text; delivery zone is missing.
- All fields are placeholder-only and keyboard/error behavior is unspecified.

### Native implementation usability
Implementable but incomplete for dynamic Moroccan delivery eligibility. Its postal/default controls are useful components for the preferred v2 form.

### Reusable components identified
- AppHeader
- FormInput
- CheckboxRow
- PrimaryButton

### Dynamic backend data required
- Customer defaults
- Moroccan address fields and validation
- Default-address state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Localization | Phone field does not establish the required `+212` context. | Use the Morocco prefix/keyboard mask. |
| MAJOR | Delivery UX | City is free text and no delivery-zone selector is present. | Use validated city and delivery-zone selection. |
| MAJOR | Forms | Fields use placeholders instead of persistent labels. | Add visible labels, required markers, and inline validation. |

### Canonical recommendation
Keep only as an alternative. Reuse its postal-code and default-address controls in the corrected v2 form.

---

## 08-change-email-form-fr.png

Folder: `08-account/`  
Screen purpose: Change account email  
Probable native route: `Account/Security/ChangeEmail`  
Language: FR  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Security-sensitive email-change form with masked current email, new email, an optional password confirmation, and notice of code verification.

### Visible UI structure
- Header: back, logo, notifications
- Main content: security hero and labeled form
- Cards: current-email summary and security information alerts
- Primary action: Continuer
- Secondary actions: password visibility and back
- Navigation: none
- Overlays: none

### Brand validation
- Excellent consistency in palette, hierarchy, icon family, cards, spacing, radii, and CTA.

### UX validation
- Labels and explanation are clear.
- Marking password confirmation as optional weakens the security model without explanation.
- The flow mentions a code sent to the new address but does not clarify recovery when the current email is compromised.
- Keyboard-aware scrolling and inline validation are required.

### Native implementation usability
Feasible with reusable secure fields and a two-step verification flow, but security requirements need product/backend confirmation before implementation.

### Reusable components identified
- AppHeader
- SecurityHero
- ReadOnlyField
- FormInput
- PasswordInput
- InfoAlert
- PrimaryButton

### Dynamic backend data required
- Masked current email
- Authentication method/account type
- New-email availability/validation result
- Verification challenge state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Security logic | Current-password confirmation is labeled optional without an alternative re-authentication rule. | Require recent authentication or explicitly show the approved SSO/OTP re-auth path. NEEDS PRODUCT DECISION. |
| MAJOR | Recovery flow | Verification/recovery behavior is underspecified. | Define which address receives confirmation, expiry, retry, and compromised-email recovery. NEEDS PRODUCT DECISION. |

### Canonical recommendation
Correct before use. Visual layout is strong, but the re-authentication and verification rules must be product-approved first.

---

## 08-change-password-form-fr.png

Folder: `08-account/`  
Screen purpose: Change account password  
Probable native route: `Account/Security/ChangePassword`  
Language: FR  
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
Three-field password update with visibility controls, a strength/requirements card, and a single update action.

### Visible UI structure
- Header: back, logo, notifications
- Main content: password hero, three labeled secure inputs, strength card
- Cards: strength guidance
- Primary action: Mettre à jour mon mot de passe
- Secondary actions: three visibility toggles
- Navigation: none
- Overlays: none

### Brand validation
- Strong brand alignment across illustration, color, type hierarchy, fields, cards, icons, and CTA.

### UX validation
- Clear goal, visible labels, password toggles, and requirements.
- Empty-looking fields are paired with `Force ... Faible`, which can appear as premature validation.
- The page must remain usable with keyboard open and Dynamic Type.

### Native implementation usability
Well suited to shared `PasswordInput`, requirements/strength meter, keyboard-aware scroll, async button loading, and inline mismatch errors.

### Reusable components identified
- AppHeader
- SecurityHero
- PasswordInput
- PasswordStrengthMeter
- PrimaryButton

### Dynamic backend data required
- Password policy
- Recent-authentication requirement
- Submit/loading/error/success state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | State clarity | Strength reads `Faible` before any visible value has been entered. | Start neutral/hidden and reveal strength after input. |
| MINOR | Accessibility | Icon-only visibility controls need enlarged targets and announced visible/hidden state. | Use padded Pressables with accessibility labels and state. |

### Canonical recommendation
Use directly as the main change-password reference after neutralizing the initial meter state and confirming keyboard behavior.

---

## 08-change-password-with-requirements-fr.png

Folder: `08-account/`  
Screen purpose: Password-change alternative with checklist  
Probable native route: `Account/Security/ChangePassword`  
Language: FR  
Screen type:
- Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Alternative password form that displays a five-item validation checklist and a forgot-password link.

### Visible UI structure
- Header: back, logo, notifications
- Main content: three field cards and green requirements checklist
- Primary action: Mettre à jour le mot de passe
- Secondary actions: show-password icons and forgot-password link
- Navigation: four-item bottom bar
- Overlays: none

### Brand validation
- Main palette and component styling are coherent.

### UX validation
- Checklist is easier to scan than prose, but every rule is green while fields show no entered values, creating an impossible success state.
- Bottom navigation contains four tabs and permanently adds Commandes while omitting Catégories and Favoris.

### Native implementation usability
Checklist component is reusable, but the overall screenshot should not guide implementation due to false state and navigation defects.

### Reusable components identified
- AppHeader
- PasswordInput
- PasswordRequirementsList
- PrimaryButton

### Dynamic backend data required
- Password policy
- Per-rule validation state
- Submit state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Form state | All requirements are satisfied while no password value is visible. | Bind each requirement state to actual input; show neutral states initially. |
| MAJOR | Navigation | Four-tab bar uses Commandes and omits required destinations. | Remove bottom tabs from the secure subflow or restore the approved five. |

### Canonical recommendation
Keep only as an alternative. Reuse the checklist and forgot-password link in `08-change-password-form-fr.png` if desired.

---

## 08-change-phone-number-fr.png

Folder: `08-account/`  
Screen purpose: Start phone-number change  
Probable native route: `Account/Security/ChangePhone`  
Language: FR  
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
Current masked number plus a new Morocco-prefixed phone input that leads to verification.

### Visible UI structure
- Header: back, logo, notifications
- Main content: explanatory copy, security illustration, current/new phone cards, privacy alert
- Primary action: Envoyer le code
- Secondary actions: prefix selector and back
- Navigation: none
- Overlays: none

### Brand validation
- Logo, palette, illustration, typography, cards, inputs, radii, and CTA align well.

### UX validation
- `+212`, Morocco flag, formatting hint, and verification expectation are clear.
- The paper-plane button icon is outside the supplied action/security set.
- Submit must disable until a complete valid number and expose loading/error feedback.

### Native implementation usability
Strong React Native reference using a phone input, semantic phone keyboard, mask, inline validation, and transition to OTP.

### Reusable components identified
- AppHeader
- SecurityHero
- ReadOnlyPhoneCard
- PhoneInput
- InfoAlert
- PrimaryButton

### Dynamic backend data required
- Masked current phone
- Country/prefix policy
- New-number validation
- OTP challenge/loading/error state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Icons | Paper-plane CTA icon is not part of the supplied icon family. | Use the standard phone/check/forward treatment or a matched vector icon. |
| MINOR | Interaction state | Enabled/disabled behavior is not evidenced. | Disable until a valid number, then show loading and error feedback. |

### Canonical recommendation
Use directly after the minor icon/state corrections; this is the canonical phone-change start screen.

---

## 08-complete-profile-progress-60-fr.png

Folder: `08-account/`  
Screen purpose: Profile-completion progress and missing-field prompt  
Probable native route: `Account/Profile/Completion`  
Language: FR  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Progress screen showing profile completion, completed/missing fields, benefits, continue, and defer actions.

### Visible UI structure
- Header: back, logo, notifications
- Main content: 60% progress card, four field rows, benefit illustrations
- Primary action: Continuer
- Secondary action: Plus tard
- Navigation: header back
- Overlays: none

### Brand validation
- Strong consistency in palette, typography, icons, cards, badges, radii, and actions.

### UX validation
- The progress arithmetic contradicts the visible checklist: four visible items show three complete and one missing (75%), while the summary says three complete, two missing (60%).
- `Continuer` does not say which missing step opens.
- Benefit statements may imply notifications depend on profile completeness; that behavior is unproven.

### Native implementation usability
Feasible only if percentage, counts, and rows derive from one canonical requirement model. Benefits and routing cannot be static artwork/copy.

### Reusable components identified
- AppHeader
- ProgressRing
- CompletionSummaryCard
- ProfileRequirementRow
- BenefitItem
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Required profile fields
- Completion state per field
- Calculated completion percentage/counts
- Next incomplete route

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Data consistency | `60%` and `3 completed / 2 missing` contradict the four visible rows showing 3/1. | Calculate all values from the same requirements array and render every counted item. |
| MAJOR | Action clarity | `Continuer` has no explicit next step. | Label contextually (for example `Ajouter une adresse`) or show the next incomplete item. |
| MAJOR | Product behavior | Benefits imply account capabilities depend on completion without confirmed rules. | Validate the completion incentives and gating. NEEDS PRODUCT DECISION. |

### Canonical recommendation
Correct before use; do not implement until completion rules and counts are internally consistent.

---

## 08-notification-detail-order-preparation-fr.png

Folder: `08-account/`  
Screen purpose: Order-preparation notification detail  
Probable native route: `Account/Notifications/:notificationId`  
Language: FR  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Notification detail for an order being prepared, with order metadata, estimated delivery, mark-read action, and order deep link.

### Visible UI structure
- Header: back, logo, notifications
- Main content: notification hero, detail card, order summary, info alert
- Primary action: Voir la commande
- Secondary action: Marquer comme lu
- Navigation: header back
- Overlays: none

### Brand validation
- Strong palette, illustration, type, card, icon, spacing, CTA, and status treatment consistency.

### UX validation
- Clear connection between notification and order.
- Badge says `En cours` while title says `en cours de préparation`; source status system provides `En préparation`.
- `Marquer comme lu` should disappear/disable once read, with accessible state feedback.

### Native implementation usability
Feasible using a reusable notification-detail template and order-summary card. State, dates, estimates, and deep links must be dynamic.

### Reusable components identified
- AppHeader
- NotificationHero
- StatusBadge
- OrderSummaryCard
- PrimaryButton
- SecondaryButton
- InfoAlert

### Dynamic backend data required
- Notification id/read state/timestamp
- Order id/status
- Estimated delivery window
- Delivery city

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Status consistency | Uses `En cours` for the preparation state. | Use the canonical `En préparation` badge and order-state enum. |
| MINOR | Interaction state | Read action has no shown read/loading/result state. | Disable during mutation and replace with an accessible read indicator after success. |

### Canonical recommendation
Correct before use. Retain as the preparation-state notification reference after badge normalization.

---

## 08-notification-detail-order-shipped-fr.png

Folder: `08-account/`  
Screen purpose: Shipped-order notification detail  
Probable native route: `Account/Notifications/:notificationId`  
Language: FR  
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
Order-shipped notification containing timestamp, delivery estimate, order number, total in MAD, location, payment method, help, and deep links.

### Visible UI structure
- Header: labeled Retour, logo, notifications
- Main content: shipment hero, status/detail card, help card
- Primary action: Voir ma commande
- Secondary action: Voir toutes les notifications / support
- Navigation: header back
- Overlays: none

### Brand validation
- Excellent palette, type, illustrations, cards, icons, shadows, and CTA consistency; currency is correctly MAD.

### UX validation
- Information hierarchy and next actions are clear.
- Badge wording `Commande expédiée` varies from the source label `Expédiée` and the preparation variant uses a different layout.
- Large content must scroll and support text scaling without footer clipping.

### Native implementation usability
Strong reusable notification-detail basis using structured order data and conditional state sections.

### Reusable components identified
- AppHeader
- NotificationHero
- StatusBadge
- OrderInfoGrid
- HelpCard
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Notification timestamp/read state
- Order id/status/total/currency
- Delivery estimate/address
- Payment method

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Status copy | Badge differs from canonical `Expédiée`. | Bind to shared status-label tokens. |
| MINOR | Component consistency | Header/detail structure differs from preparation state. | Use one notification template with state-driven content. |

### Canonical recommendation
Use directly as the canonical notification-detail template after minor status/component normalization.

---

## 08-notification-management-channels-fr.png

Folder: `08-account/`  
Screen purpose: Notification channel and category management alternative  
Probable native route: `Account/Preferences/Notifications`  
Language: FR  
Screen type:
- Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Global notification channel switches plus category drill-down rows and bottom tabs.

### Visible UI structure
- Header: back, logo, notifications
- Main content: channel card and category card
- Primary action: none (appears autosaved)
- Secondary actions: switches and category rows
- Navigation: five-item bottom bar
- Overlays: none

### Brand validation
- Visual style aligns with the foundation.

### UX validation
- `Notifications dans l’application` and `Notifications push` are not clearly distinguished.
- All categories display `Activé` without showing their actual channel mix.
- Bottom navigation replaces Panier with Commandes.

### Native implementation usability
Channel/category separation is useful, but settings semantics and autosave feedback need definition; prefer the clearer canonical settings variant.

### Reusable components identified
- AppHeader
- ToggleSettingRow
- CategoryDisclosureRow
- BottomTabBar

### Dynamic backend data required
- Channel preferences
- Category preferences/channel summaries
- Save/error state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Information architecture | In-app versus push meaning is unclear. | Define channel taxonomy and helper copy. NEEDS PRODUCT DECISION. |
| MAJOR | Navigation | Commandes replaces Panier. | Restore approved bottom tabs or omit them. |
| MINOR | Feedback | Autosave is implied but not communicated. | Show saving/saved/error feedback. |

### Canonical recommendation
Keep only as an alternative; prefer `08-notification-settings-toggles-fr.png`.

---

## 08-notification-preferences-by-category-fr.png

Folder: `08-account/`  
Screen purpose: Notification topic toggles alternative  
Probable native route: `Account/Preferences/Notifications`  
Language: FR  
Screen type:
- Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Six topic toggles, essential-notification note, and persistent account tabs.

### Visible UI structure
- Header: back, logo, notifications
- Main content: six setting cards and info note
- Primary action: none (appears autosaved)
- Secondary actions: switches
- Navigation: five-item bottom bar
- Overlays: none

### Brand validation
- Strong brand consistency in cards, icons, switches, spacing, and typography.

### UX validation
- Topic descriptions are understandable.
- Note says some essential notifications cannot be disabled, yet no locked/disabled row identifies them.
- Bottom tabs reverse Panier/Favoris.

### Native implementation usability
Topic rows are reusable, but state/essential rules and save behavior must be explicit.

### Reusable components identified
- AppHeader
- ToggleSettingCard
- InfoAlert
- BottomTabBar

### Dynamic backend data required
- Notification topics
- Preference and mandatory/locked state
- Save/error state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | State contradiction | Essential notifications are mentioned but all switches look editable. | Mark mandatory rows locked/on with explanation. NEEDS PRODUCT DECISION. |
| MAJOR | Navigation | Panier/Favoris order is wrong. | Normalize global tabs. |
| MINOR | Feedback | No save/autosave status. | Show saving/saved/error state. |

### Canonical recommendation
Keep only as an alternative; reuse topic descriptions within the canonical settings model.

---

## 08-notification-preferences-detailed-fr.png

Folder: `08-account/`  
Screen purpose: Dense notification settings alternative  
Probable native route: `Account/Preferences/Notifications`  
Language: FR  
Screen type:
- Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Four delivery channels plus five categories and an essential-notification note.

### Visible UI structure
- Header: back, logo, notifications, `BUYER APP`
- Main content: hero, channels card, categories card, note
- Primary action: none
- Secondary actions: nine switches
- Navigation: header back
- Overlays: none

### Brand validation
- Core visuals align, but prohibited buyer wording is present.

### UX validation
- Very dense and duplicates settings across two conceptual axes without showing their interaction.
- Essential state remains undefined, and there is no save/autosave feedback.

### Native implementation usability
Could be implemented with grouped lists, but the data model must define channel-by-category preference matrices; screenshot does not.

### Reusable components identified
- AppHeader
- PreferencesHero
- PreferenceSection
- ToggleSettingRow
- InfoAlert

### Dynamic backend data required
- Channel preferences
- Topic preferences
- Mandatory rules
- Save state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Branding | Adds `BUYER APP`. | Remove it. |
| MAJOR | Settings logic | Independent channel/topic switches have undefined precedence. | Define channel × topic behavior. NEEDS PRODUCT DECISION. |
| MAJOR | Density | Nine toggles plus hero exceed comfortable mobile density. | Use progressive disclosure/category detail screens. |

### Canonical recommendation
Keep only as an alternative; do not use as a full native reference.

---

## 08-notification-settings-toggles-fr.png

Folder: `08-account/`  
Screen purpose: Notification types and reception-channel settings  
Probable native route: `Account/Preferences/Notifications`  
Language: FR  
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
Notification-topic switches, delivery-channel switches, explanatory note, and explicit save.

### Visible UI structure
- Header: back, logo, notifications
- Main content: type card, reception card, info note
- Primary action: Enregistrer les préférences
- Secondary actions: switches
- Navigation: header back
- Overlays: none

### Brand validation
- Highly consistent palette, typography, cards, icons, toggles, spacing, and primary action.

### UX validation
- Separating topics from channels is clear and explicit save avoids ambiguous autosave.
- Copy says only important account/order notifications will arrive but does not identify which settings are mandatory.
- Needs switch accessibility state and save feedback.

### Native implementation usability
Strong reusable settings reference using native switches, grouped sections, semantic state, and a preference mutation.

### Reusable components identified
- AppHeader
- PreferenceSection
- ToggleSettingRow
- InfoAlert
- PrimaryButton

### Dynamic backend data required
- Topic/channel preferences
- Mandatory notification rules
- Save/loading/error state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Product clarity | Mandatory/important notifications are not identified. | Mark locked essential topics/channels after product approval. NEEDS PRODUCT DECISION. |
| MINOR | Feedback/accessibility | No explicit save state and switch semantics cannot be verified. | Announce on/off labels and show loading/success/error. |

### Canonical recommendation
Use directly as the canonical notification-settings screen after minor rule/feedback clarification.

---

## 08-password-changed-success-fr.png

Folder: `08-account/`  
Screen purpose: Password-change success confirmation  
Probable native route: `Account/Security/PasswordChanged`  
Language: FR  
Screen type:
- Success state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Full-page success feedback with routes to account or active sessions.

### Visible UI structure
- Header: logo and notifications
- Main content: success illustration and confirmation copy
- Primary action: Retour à mon compte
- Secondary action: Vérifier mes sessions
- Navigation: five-item bottom bar
- Overlays: none

### Brand validation
- Success green, illustration, palette, typography, buttons, and cardless spacious layout align.

### UX validation
- Outcome and next steps are clear.
- Bottom navigation uses Mes achats, Mes commandes, and Support instead of required destinations.
- Success route should prevent resubmission on back and announce success to screen readers.

### Native implementation usability
Central success component is reusable; remove incorrect navigation and implement reset/replace navigation behavior.

### Reusable components identified
- SuccessState
- PrimaryButton
- SecondaryButton
- BottomTabBar

### Dynamic backend data required
- Password-change result
- Active-session availability/count if shown

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Bottom tabs are entirely incompatible. | Remove tabs from the terminal success state or use the approved five. |
| MAJOR | Back-stack | Screenshot does not define prevention of returning to submitted password form. | Replace/reset route after success. |

### Canonical recommendation
Correct before use. Keep the central success content; remove incompatible tabs and define terminal navigation.

---

## 08-payment-methods-card-cod-wallet-fr.png

Folder: `08-account/`  
Screen purpose: Saved payment methods and account credit  
Probable native route: `Account/Payments`  
Language: FR  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Saved bank card, cash on delivery, wallet/credit balance in MAD, and add-payment-method action.

### Visible UI structure
- Header: back, logo, notifications
- Main content: payment hero and three method cards
- Primary action: Ajouter un moyen de paiement
- Secondary actions: method rows
- Navigation: header back
- Overlays: none

### Brand validation
- Strong palette, typography, illustration, card, icon, badge, CTA, and MAD consistency.

### UX validation
- Methods are scannable and default state is visible.
- `CMI / Carte bancaire` conflates a payment gateway with a card brand.
- Saved-card storage, wallet/credit, COD availability, and 1,250 MAD balance are unverified product behavior/data.

### Native implementation usability
Feasible only after payment capabilities are confirmed. Sensitive card data must use tokenized provider data and never be stored/rendered from ordinary backend fields.

### Reusable components identified
- AppHeader
- PaymentHero
- PaymentMethodCard
- StatusBadge
- PrimaryButton

### Dynamic backend data required
- Tokenized saved payment methods
- Default method
- COD eligibility
- Wallet/credit availability and MAD balance
- Add/remove/default mutation states

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Payment logic | Saved card, COD, and wallet are presented as implemented without confirmation. | Confirm provider/tokenization, COD rules, and wallet ledger. NEEDS PRODUCT DECISION. |
| MAJOR | Terminology | `CMI / Carte bancaire` is misleading. | Display masked card brand/type; mention gateway only where legally/operationally relevant. |
| MAJOR | Invented data | Wallet balance appears as factual user data. | Render only from a confirmed wallet/credit service or remove the section. |

### Canonical recommendation
Correct before use; do not implement this route until the payment-method and wallet model is approved.

---

## 08-security-privacy-full-menu-fr.png

Folder: `08-account/`  
Screen purpose: Comprehensive security/privacy settings index  
Probable native route: `Account/Security`  
Language: FR  
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
Security/privacy menu for password, phone, email, sessions, communication preferences, data export, and account deletion.

### Visible UI structure
- Header: logo and notifications, no back
- Main content: security hero and eight menu rows
- Primary action: none
- Secondary actions: security/privacy destinations
- Navigation: none
- Overlays: none

### Brand validation
- Excellent alignment with palette, illustration, typography, cards, icon family, spacing, radii, and destructive color.

### UX validation
- Comprehensive, readable hierarchy and destructive delete is separated at the end.
- Missing visible back navigation.
- Notification/marketing preferences are broader account settings and may not belong under security; 2FA from the alternative is missing.

### Native implementation usability
Strong data-driven settings-list reference. Add approved 2FA/privacy destinations and render account-deletion/export availability from product configuration.

### Reusable components identified
- AppHeader
- SecurityHero
- SettingsRow
- DestructiveSettingsRow

### Dynamic backend data required
- Available security/privacy features
- Verification/session/2FA state summaries
- Data-export availability/status

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Navigation | No visible back control. | Add shared back header. |
| MINOR | Information architecture | Communication preferences sit inside security while 2FA is absent. | Move preferences to account settings and add approved two-step verification. NEEDS PRODUCT DECISION. |

### Canonical recommendation
Use as the canonical security/privacy index after minor navigation and menu-scope corrections; borrow 2FA from the alternative.

---

## 08-security-privacy-with-2fa-fr.png

Folder: `08-account/`  
Screen purpose: Security/privacy menu alternative with 2FA  
Probable native route: `Account/Security`  
Language: FR  
Screen type:
- Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Seven-row security menu emphasizing password, verified contacts, sessions, two-step verification, privacy, and deletion.

### Visible UI structure
- Header: back, logo, notifications, `BUYER APP`
- Main content: security hero and menu rows
- Primary action: none
- Secondary actions: security destinations
- Navigation: header back
- Overlays: none

### Brand validation
- Main design aligns, but `BUYER APP` violates branding.

### UX validation
- 2FA and data privacy are correctly surfaced; destructive deletion is last.
- It omits data export and uses orange chevrons everywhere, making hierarchy flatter.

### Native implementation usability
Useful menu-content alternative only; integrate selected rows into the canonical full menu.

### Reusable components identified
- AppHeader
- SecurityHero
- SettingsRow
- DestructiveSettingsRow

### Dynamic backend data required
- 2FA/security feature availability
- Verification state summaries

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Branding | Adds `BUYER APP`. | Remove it. |
| MINOR | Feature coverage | Data export is missing. | Include if required/available. NEEDS PRODUCT DECISION. |

### Canonical recommendation
Keep only as an alternative; transfer `Vérification en deux étapes` and `Confidentialité des données` to the corrected full menu.

---

## 08-silent-hours-day-selection-fr.png

Folder: `08-account/`  
Screen purpose: Recurring notification quiet hours with day selection  
Probable native route: `Account/Preferences/Notifications/QuietHours`  
Language: FR  
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
Quiet-hours toggle, start/end times, weekday selection, essential-order exception, save, and return.

### Visible UI structure
- Header: back, logo, notifications
- Main content: enable card, time rows, day controls, info alert
- Primary action: Enregistrer
- Secondary actions: time/day controls and return
- Navigation: header/back text
- Overlays: none

### Brand validation
- Strong palette, type, cards, icon circles, switches, controls, spacing, and CTA consistency.

### UX validation
- Recurrence and time window are understandable.
- Weekend unselected controls have extremely low contrast and appear disabled rather than selectable.
- Exception for important order notifications requires product definition.

### Native implementation usability
Feasible with native time pickers, multi-select weekday chips, timezone-aware scheduling, and accessible switch/selected states.

### Reusable components identified
- AppHeader
- ToggleSettingCard
- TimePickerRow
- DaySelector
- InfoAlert
- PrimaryButton

### Dynamic backend data required
- Quiet-hours enabled/start/end/days
- Account/device timezone
- Mandatory notification exception rules

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Accessibility | Unselected weekend states have near-invisible checks and look disabled. | Use an outlined unselected state with adequate contrast; reserve faded styling for disabled. |
| MINOR | Product behavior | Essential-order exception is not defined. | Confirm which notifications bypass quiet hours. NEEDS PRODUCT DECISION. |

### Canonical recommendation
Use as the canonical quiet-hours screen after unselected-state and exception-rule clarification.

---

## 08-silent-hours-do-not-disturb-fr.png

Folder: `08-account/`  
Screen purpose: Quiet-hours alternative with timezone  
Probable native route: `Account/Preferences/Notifications/QuietHours`  
Language: FR  
Screen type:
- Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
Quiet-hours toggle, start/end dropdowns, fixed Casablanca timezone, explanatory alert, and save.

### Visible UI structure
- Header: logo and notifications, no back
- Main content: hero and settings card
- Primary action: Enregistrer mes préférences
- Secondary actions: time/timezone controls and return link
- Navigation: bottom return link
- Overlays: none

### Brand validation
- Coherent brand illustration, palette, fields, cards, typography, and CTA.

### UX validation
- Timezone awareness is useful, but `(GMT+01:00) Casablanca` is a fragile fixed offset because Morocco observes timezone changes.
- Copy says sounds/vibrations stop and notifications reappear later, contradicting the other variant’s push-pause with essential exceptions.
- Weekday recurrence is missing and no top back exists.

### Native implementation usability
Timezone concept is reusable if stored as `Africa/Casablanca`; behavior must be unified with the canonical day-selection screen.

### Reusable components identified
- PreferencesHero
- ToggleSettingRow
- TimePickerField
- TimezoneField
- InfoAlert
- PrimaryButton

### Dynamic backend data required
- Enabled/start/end/timezone
- Quiet-hours delivery behavior

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Behavior contradiction | Defines a different quiet-hours behavior than the day-selection variant. | Decide suppress vs silence vs defer and exception rules. NEEDS PRODUCT DECISION. |
| MAJOR | Timezone | Hard-codes GMT+01 rather than a daylight-rule timezone. | Store/display `Africa/Casablanca` with current offset dynamically. |
| MINOR | Navigation | No top back control. | Add shared back header. |

### Canonical recommendation
Keep only as an alternative. Add dynamic timezone handling to `08-silent-hours-day-selection-fr.png` after behavior is approved.

---

## 08-verify-phone-otp-code-fr.png

Folder: `08-account/`  
Screen purpose: Verify changed phone using six-digit OTP  
Probable native route: `Account/Security/VerifyPhone`  
Language: FR  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Masked phone summary, six OTP cells, expiry timer, resend/edit controls, and verify CTA.

### Visible UI structure
- Header: back and logo
- Main content: instructions, phone/OTP illustration card, code cells, timer
- Primary action: Vérifier
- Secondary actions: Renvoyer le code and Modifier le numéro
- Navigation: header back
- Overlays: none

### Brand validation
- Palette, logo, typography, card, input cells, icons, and CTA align well.

### UX validation
- OTP purpose and edit route are clear.
- Phone is `+33`, violating Morocco.
- Resend is active while timer still has 01:45; verify CTA appears enabled with empty cells.
- Must support SMS autofill, paste, numeric keyboard, auto-advance/backspace, errors, throttling, and accessibility.

### Native implementation usability
Feasible with a controlled six-digit OTP component, but current state logic is misleading and must be corrected before reference use.

### Reusable components identified
- AppHeader
- OTPInput
- CountdownTimer
- SecondaryButton
- PrimaryButton

### Dynamic backend data required
- Masked target phone
- OTP challenge id/expiry
- Resend cooldown/attempts
- Verification loading/error/success state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Localization | Target number uses `+33`. | Use masked Moroccan `+212`. |
| CRITICAL | State logic | Resend is enabled before expiry/cooldown and Verify is enabled with empty cells. | Disable actions until their valid conditions; show loading/error/lockout states. |
| MAJOR | Accessibility/input | Six visual cells can produce poor focus and screen-reader behavior. | Use one semantic numeric input with visual cells, SMS autofill, paste, and announced errors. |

### Canonical recommendation
Correct before use; central layout is usable only after Morocco and OTP state/accessibility fixes.

---

## Duplicate/version conclusions for 08-account

### Security index group

Files:
- `08-account-security-overview-fr.png`
- `08-security-privacy-full-menu-fr.png`
- `08-security-privacy-with-2fa-fr.png`

Recommended canonical file: `08-security-privacy-full-menu-fr.png`  
Reason: most complete, no prohibited buyer wording, clearest destructive separation, and strongest reusable settings-list structure.

Useful elements from alternatives:
- Verification badges/device summaries from `08-account-security-overview-fr.png`
- Two-step verification and privacy rows from `08-security-privacy-with-2fa-fr.png`

### Active sessions group

Files:
- `08-active-sessions-devices-list-fr.png`
- `08-active-sessions-devices-v2-fr.png`

Recommended canonical file: `08-active-sessions-devices-v2-fr.png`  
Reason: visible per-device actions and top back navigation are clearer; it still needs danger styling and confirmation.

Useful elements from alternatives:
- OS/browser detail and bulk revoke-other-sessions action from the first variant

### Add-address group

Files:
- `08-add-address-simple-form-fr.png`
- `08-add-address-form-v2-fr.png`

Recommended canonical file: `08-add-address-form-v2-fr.png`  
Reason: correct `+212` treatment and city/delivery-zone selectors make it more realistic for Morocco.

Useful elements from alternatives:
- Postal code and default-address controls from the simple form

### Change-password group

Files:
- `08-change-password-form-fr.png`
- `08-change-password-with-requirements-fr.png`

Recommended canonical file: `08-change-password-form-fr.png`  
Reason: correct focused navigation and coherent strength guidance; the alternative shows impossible all-green requirements and invalid bottom tabs.

Useful elements from alternatives:
- Scannable requirement checklist and forgot-password link

### Disconnect-device confirmation group

Files:
- `08-disconnect-device-confirmation-fr.png`
- `08-disconnect-device-confirmation-v2-fr.png`

Recommended canonical file: `08-disconnect-device-confirmation-fr.png`  
Reason: preserves official logo usage; v2 is not automatically superior because it adds prohibited `BUYER APP` text.

Useful elements from alternatives:
- Clearer “only this device” advisory and approximate-location labeling from v2

### FAQ list group

Files:
- `08-faq-accordion-questions-fr.png`
- `08-faq-tab-categories-fr.png`

Recommended canonical file: `08-faq-accordion-questions-fr.png`  
Reason: search plus scrollable chips/inline answers is more resilient than six cramped tabs and avoids prohibited buyer wording.

Useful elements from alternatives:
- Broader category set from the tab variant

### Help-center home group

Files:
- `08-help-center-categories-fr.png`
- `08-help-center-with-recent-requests-fr.png`
- `08-help-support-faq-categories-fr.png`

Recommended canonical file: `08-help-center-categories-fr.png`  
Reason: cleanest hierarchy, best touch density, and no invented contact details.

Useful elements from alternatives:
- Conditional recent-ticket preview from `08-help-center-with-recent-requests-fr.png`
- FAQ question counts only if backed by real content from `08-help-support-faq-categories-fr.png`

### Language-selection group

Files:
- `08-language-selection-3-languages-fr.png`
- `08-language-selection-interface-preview-fr.png`
- `08-language-selection-with-preview-fr.png`

Recommended canonical file: `08-language-selection-3-languages-fr.png`  
Reason: simplest native interaction and clearest current selection; remove English unless approved and correct bottom-nav order.

Useful elements from alternatives:
- Reduced live FR/RTL sample idea from the preview variants; do not embed miniature screenshots

### Marketing-preferences group

Files:
- `08-marketing-preferences-cart-reminders-fr.png`
- `08-marketing-preferences-detailed-fr.png`
- `08-marketing-preferences-toggles-fr.png`

Recommended canonical file: `08-marketing-preferences-cart-reminders-fr.png`  
Reason: best category clarity, focused scope, correct navigation behavior, and no unsupported partner program.

Useful elements from alternatives:
- Grouped consent sections if product-approved

### Address-list group

Files:
- `08-my-addresses-list-labels-fr.png`
- `08-my-addresses-list-v2-fr.png`
- `08-my-addresses-list-ar.png` (Arabic counterpart)

Recommended canonical file: `08-my-addresses-list-v2-fr.png`  
Arabic reference: `08-my-addresses-list-ar.png` after RTL/navigation fixes  
Reason: v2 has clearer card hierarchy and correct `+212`; the labeled alternative contradicts default state and overloads actions.

Useful elements from alternatives:
- Address type labels from the labeled variant

### Notification-settings group

Files:
- `08-notification-management-channels-fr.png`
- `08-notification-preferences-by-category-fr.png`
- `08-notification-preferences-detailed-fr.png`
- `08-notification-settings-toggles-fr.png`

Recommended canonical file: `08-notification-settings-toggles-fr.png`  
Reason: clearest type/channel separation, explicit save, no prohibited wording, and least ambiguous native behavior.

Useful elements from alternatives:
- Category descriptions and optional drill-down summaries after channel/topic rules are defined

### Notification-detail state family

Files:
- `08-notification-detail-order-preparation-fr.png`
- `08-notification-detail-order-shipped-fr.png`

Recommended canonical template file: `08-notification-detail-order-shipped-fr.png`  
Preparation-state reference: `08-notification-detail-order-preparation-fr.png` after changing badge to `En préparation`  
Reason: shipped version has the stronger reusable information hierarchy and correct MAD example; both are distinct dynamic states rather than redundant routes.

### Quiet-hours group

Files:
- `08-silent-hours-day-selection-fr.png`
- `08-silent-hours-do-not-disturb-fr.png`

Recommended canonical file: `08-silent-hours-day-selection-fr.png`  
Reason: includes recurring days and clearer settings structure.

Useful elements from alternatives:
- Timezone field, implemented with `Africa/Casablanca` rather than a fixed GMT offset

## Account-folder canonical route shortlist

| Route/state | Canonical screenshot | Status | Required correction before implementation |
|---|---|---|---|
| Account home FR | `08-account-dashboard-profile-menu-fr.png` | NEEDS_REWORK | Use +212 and restore five tabs |
| Account home AR | `08-account-dashboard-profile-menu-ar.png` | NEEDS_REWORK | Replace Orders tab with Categories and preserve RTL |
| Guest account | `08-account-guest-welcome-login-fr.png` | APPROVED_WITH_MINOR_FIXES | Remove/define guest notification icon |
| Profile details | `08-my-information-personal-details-fr.png` | NEEDS_REWORK | Normalize tabs and verification labels |
| Edit profile FR | `08-edit-profile-form-fr.png` | NEEDS_REWORK | Use +212 and route sensitive changes securely |
| Edit profile AR | `08-edit-profile-form-ar.png` | NEEDS_REWORK | Use +212 and mirror return arrow |
| Profile completion | `08-complete-profile-progress-60-fr.png` | NEEDS_REWORK | Fix contradictory counts/percentage |
| Address list FR | `08-my-addresses-list-v2-fr.png` | NEEDS_REWORK | Restore Panier and consistent actions |
| Address list AR | `08-my-addresses-list-ar.png` | NEEDS_REWORK | Fix RTL back arrow and tabs |
| Add address | `08-add-address-form-v2-fr.png` | NEEDS_REWORK | Add labels/postal/default and remove bad tabs |
| Edit address | `08-edit-address-form-fr.png` | NEEDS_REWORK | Remove bad tabs and duplicate apartment data |
| Delete address confirmation | `08-delete-address-confirmation-fr.png` | NEEDS_REWORK | Convert to dialog and remove tabs |
| Security index | `08-security-privacy-full-menu-fr.png` | APPROVED_WITH_MINOR_FIXES | Add back and approved 2FA row |
| Change password | `08-change-password-form-fr.png` | APPROVED_WITH_MINOR_FIXES | Neutral initial strength state |
| Password success | `08-password-changed-success-fr.png` | NEEDS_REWORK | Remove incompatible tabs/reset stack |
| Change email | `08-change-email-form-fr.png` | NEEDS_REWORK | Define mandatory re-authentication |
| Change phone | `08-change-phone-number-fr.png` | APPROVED_WITH_MINOR_FIXES | Minor icon/state polish |
| Verify phone OTP | `08-verify-phone-otp-code-fr.png` | NEEDS_REWORK | +212 and correct resend/verify state |
| Active sessions | `08-active-sessions-devices-v2-fr.png` | NEEDS_REWORK | Danger styling/confirmation and consistent metadata |
| Disconnect device confirmation | `08-disconnect-device-confirmation-fr.png` | NEEDS_REWORK | Danger styling and remote-session copy |
| Logout confirmation | `08-logout-confirmation-dialog-fr.png` | NEEDS_REWORK | Remove BUYER APP; compact modal/danger action |
| Payment methods | `08-payment-methods-card-cod-wallet-fr.png` | NEEDS_REWORK | Product decision on saved cards/COD/wallet |
| Language & region | `08-language-region-preferences-fr.png` | NEEDS_REWORK | Confirm locales and fixed Morocco/MAD behavior |
| Language selection | `08-language-selection-3-languages-fr.png` | NEEDS_REWORK | Confirm English and correct nav |
| Marketing preferences | `08-marketing-preferences-cart-reminders-fr.png` | APPROVED_WITH_MINOR_FIXES | Icon/save feedback |
| Notification settings | `08-notification-settings-toggles-fr.png` | APPROVED_WITH_MINOR_FIXES | Define mandatory notices/save feedback |
| Quiet hours | `08-silent-hours-day-selection-fr.png` | APPROVED_WITH_MINOR_FIXES | Accessible unselected state; define exceptions |
| Notification detail | `08-notification-detail-order-shipped-fr.png` | APPROVED_WITH_MINOR_FIXES | Shared template/status token |
| Notification detail—preparing | `08-notification-detail-order-preparation-fr.png` | NEEDS_REWORK | Use En préparation |
| Help center | `08-help-center-categories-fr.png` | APPROVED_WITH_MINOR_FIXES | Add back/define support route |
| FAQ | `08-faq-accordion-questions-fr.png` | NEEDS_REWORK | Validate policy copy/add back |
| FAQ article | `08-faq-detail-expanded-answer-fr.png` | REFERENCE_ONLY | Remove BUYER APP/use CMS-approved content |
