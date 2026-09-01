# 09 — Support and settings validation

> **Fact-check scope:** Currency and address examples are accepted variations. Do not treat them as validation defects by themselves; [fact-check-correction.md](./fact-check-correction.md) supersedes earlier currency/address severity notes.

Scope: all 41 PNG files in `09-support-settings/`, validated visually against `00-foundation/` and `assetsl/` (the project contains `assetsl/`, not a `12-assetsl/` directory). Original images were not modified.

## Extracted validation rules

- Preserve the official multicolor `MAYUSH DESIGN` logo proportions; the asset board's `BUYER APP` lockup is not permitted by the mission.
- Treat the mission's orange near `#D97434`, deep navy, warm cream, white and soft beige as authoritative where the reference boards disagree (`#D97434` versus `#FF8A00`).
- Use Playfair Display for elegant display headings, Inter for French UI/body text, and Tajawal/Cairo for Arabic; layouts must survive Dynamic Type without clipped labels.
- Use rounded white cards, restrained beige separators, a consistent radius/elevation scale, and coherent 2 px rounded outline icons from the support/system asset family.
- Top-level buyer navigation is exactly `Accueil`, `Catégories`, `Favoris`, `Panier`, `Compte`; deep support/settings routes may omit the bar.
- Destructive actions use semantic red and require clear confirmation; success, warning and error states pair color with text/icon.
- Native controls need at least 44×44 pt (48×48 dp on Android), 8 dp separation, pressed/disabled/loading feedback, accessible labels and logical screen-reader order.
- Fixed bars reserve safe-area/content insets; forms use visible labels, inline errors, semantic keyboards, keyboard avoidance and recoverable submission feedback.
- Long lists should use reusable/virtualized React Native list rows; decorative illustrations must not embed functional copy.
- `MAD` is the only buyer currency. Functional claims that are not evidenced by the project are marked **NEEDS PRODUCT DECISION**.

The `ui-ux-pro-max` audit checklist materially influenced the touch-target, Dynamic Type, safe-area, screen-reader, destructive-action, form-feedback and native-list findings below. Its generic generated palette recommendation was not used because the supplied Mayush boards and mission rules are the visual source of truth.

## 09-about-app-version-info-fr.png

Folder: `09-support-settings/`  
Screen purpose: Show installed app version and update controls.  
Probable native route: `/settings/about/app`  
Language: French  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An app-information page exposing installed version, build, release date, update status and release notes.

### Visible UI structure
- Header: back, oversized official logo, notification icon
- Main content: decorative app illustration and display heading
- Cards: version/build/date/status rows
- Primary action: `Vérifier les mises à jour`
- Secondary action: release notes
- Navigation: no bottom bar, appropriate for a deep settings route
- Overlays: none

### Brand validation
- Logo is official and undistorted but larger than the foundation header treatment.
- Cream/navy/white palette, Playfair heading, rounded cards and line icons are consistent.
- Status green is semantic and includes icon/text.

### UX validation
- Goal and actions are clear, but unrelated notification access and decorative height delay the information.
- Version data contradicts other update/about screens, so the journey cannot be implemented from these references consistently.
- Back and action targets appear large enough; content should scroll under text scaling.

### Native implementation usability
Feasible with reusable React Native rows and store-version checks. Version/build/release data must be runtime/config driven, and store handoff/loading/error states must be defined.

### Reusable components identified
- AppHeader
- SettingsInfoRow
- StatusBadge
- PrimaryButton
- SecondaryButton
- BrandIllustration

### Dynamic backend data required
- Installed version
- Build number
- Release date
- Update status
- Release notes URL/content

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Consistency | Version `2.3.1` conflicts with `1.2.0`, `1.2.3`, `1.3.0` and `v2.4.0` elsewhere. | Bind all version screens to one runtime/store source; never hard-code cross-screen examples as canonical data. |
| MAJOR | Layout | Oversized logo and illustration consume most of the first viewport. | Use the compact foundation header and move version information above decorative content. |
| MINOR | Navigation | Notification icon is unrelated to an app-information route. | Remove it unless notifications are a global, documented header action. |
| MINOR | Copy/privacy | `Vos informations ... jamais partagées` is unrelated and overbroad. | Remove it or replace it with route-relevant update/privacy copy approved by legal. |

### Canonical recommendation
Correct before use; retain the information-row structure but normalize the header and dynamic version source.

## 09-about-mayush-design-company-fr.png

Folder: `09-support-settings/`  
Screen purpose: Present company information and legal/support links.  
Probable native route: `/settings/about`  
Language: French  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A brand/about page with company positioning, website, support, legal and privacy destinations.

### Visible UI structure
- Header: back and notification icons around a large brand hero
- Main content: logo, sofa illustration, company description
- Cards: website, support, legal notices, privacy policy
- Primary action: orange support card
- Secondary actions: website/legal/privacy rows
- Navigation: no bottom bar
- Overlays: none

### Brand validation
- Official logo, warm palette, furniture imagery and typography fit Mayush.
- Card and icon treatment is consistent, although the header/hero is much taller than the foundation app bar.

### UX validation
- Destination labels are clear and touchable.
- The app version at the footer conflicts with the dedicated app/update screens.
- External website behavior and notification icon purpose are not shown.

### Native implementation usability
Straightforward with a ScrollView and reusable navigation rows. External links need explicit secure-browser handling and accessibility hints.

### Reusable components identified
- AppHeader
- BrandHero
- SettingsNavigationRow
- PrimaryActionCard
- ExternalLinkRow

### Dynamic backend data required
- Company description
- Website URL
- Legal/privacy URLs
- App version
- Copyright year

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Consistency | Footer version `1.2.0` contradicts every other version reference. | Use runtime app metadata and one canonical release source. |
| MINOR | Navigation | Notification icon is not relevant to the route. | Remove or document it as a global header action. |
| MINOR | Native UX | `Site web` does not disclose that it opens an external browser. | Add an external-link icon/accessibility hint and confirmed URL. |

### Canonical recommendation
Correct before use; it is the best company-about concept, but version and header behavior must be normalized.

## 09-accessibility-settings-text-contrast-fr.png

Folder: `09-support-settings/`  
Screen purpose: Configure visual, motion and assistive preferences.  
Probable native route: `/settings/accessibility`  
Language: French  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
Medium

### What the screen represents
An in-app accessibility preferences page for text size, contrast, reduced motion and assistive features.

### Visible UI structure
- Header: back, official logo, notification icon
- Main content: accessibility illustration and sections `Affichage` / `Assistance`
- Cards: settings rows with values, chevrons and toggles
- Primary action: reset settings
- Secondary actions: learn more and value selectors
- Navigation: no bottom bar
- Overlays: none

### Brand validation
- Palette, official logo, Playfair/Inter hierarchy and rounded surfaces are coherent.
- Orange reset CTA incorrectly gives a destructive/reset action primary promotional styling.

### UX validation
- Grouping and labels are understandable.
- `Lecteur d’écran — Activé` suggests the app can control an OS feature; `Navigation au clavier` is unexplained for a buyer mobile app.
- The fixed poster-like composition has not demonstrated behavior at the large text setting it advertises.

### Native implementation usability
Reusable rows are feasible, but OS accessibility status must be read/deep-linked rather than falsely controlled. Use system font scaling and `reduceMotion` signals; do not implement independent accessibility modes that conflict with the OS.

### Reusable components identified
- AppHeader
- SettingsSection
- SettingsValueRow
- SettingsToggleRow
- DestructiveButton
- InfoNote

### Dynamic backend data required
- Device text-size preference
- Contrast preference
- Reduce-motion preference
- Screen-reader/device capability state
- Keyboard-navigation preference, if supported

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Platform behavior | `Lecteur d’écran — Activé` implies an in-app switchable state. | **NEEDS PRODUCT DECISION:** show detected VoiceOver/TalkBack status or deep-link to OS settings; do not claim app control. |
| MAJOR | Accessibility | Layout does not demonstrate Dynamic Type despite a selected `Grande` text size. | Validate all rows, values and buttons at maximum supported text scaling with wrapping and vertical growth. |
| MINOR | Semantics | Reset is styled as the single orange primary action rather than a cautionary action. | Use a secondary/destructive confirmation pattern and state exactly what resets. |

### Canonical recommendation
Correct before use; retain section structure, but align controls with native OS capabilities.

## 09-app-permissions-camera-photos-location-fr.png

Folder: `09-support-settings/`  
Screen purpose: Show and manage device permission status.  
Probable native route: `/settings/permissions`  
Language: French  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A permissions dashboard for notifications, camera, photos/files and location.

### Visible UI structure
- Header: back, official logo, notification icon
- Main content: privacy illustration and explanatory copy
- Cards: four permission rows with status and `Ouvrir les paramètres`
- Primary action: `Tout autoriser`
- Secondary actions: OS settings links
- Navigation: no bottom bar
- Overlays: none

### Brand validation
- Official logo, palette, icon strokes and card styling are consistent.
- Repetition of outlined controls inside every card makes the page denser than the foundation patterns.

### UX validation
- Each permission's purpose is explained.
- Every row already says `Autorisée`, yet `Tout autoriser` remains active.
- Native mobile platforms do not support one reliable bulk permission grant, and previously denied permissions require OS settings.

### Native implementation usability
Rows are feasible with permission hooks, but requests must be sequential and contextual. Status must refresh on app focus after returning from OS settings.

### Reusable components identified
- AppHeader
- PermissionStatusRow
- StatusBadge
- OutlineButton
- PrimaryButton
- InfoNote

### Dynamic backend data required
- Device permission states only; no backend data visibly required

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Platform logic | Active `Tout autoriser` contradicts four already-authorized statuses and cannot reliably grant all permissions in one native action. | Hide/disable it when all are granted; otherwise request permissions contextually and sequentially using OS dialogs. |
| MAJOR | Interaction | Every authorized row still presents `Ouvrir les paramètres`, with no distinction between granted, denied and blocked states. | Model `granted`, `denied`, `blocked`, `limited` and `unavailable` states with state-specific actions. |
| MINOR | Header | Notification icon duplicates one of the permissions being managed. | Remove it from this deep settings header. |

### Canonical recommendation
Correct before use; keep the explanatory rows, replace the bulk authorization logic.

## 09-app-update-available-fr.png

Folder: `09-support-settings/`  
Screen purpose: Offer a non-mandatory app update.  
Probable native route: `/system/update-available`  
Language: French  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A dismissible update prompt comparing installed and available versions.

### Visible UI structure
- Header: official logo and notification icon, no back action
- Main content: update illustration and version card
- Primary action: update now
- Secondary action: `Plus tard`
- Navigation: no bottom bar, appropriate for a system gate
- Overlays: none

### Brand validation
- Brand surfaces, colors and typography are coherent.
- Very large branding and illustration make the state feel like a poster rather than a compact update decision.

### UX validation
- Optional update choice is clear.
- The screen misleadingly ties installing an update to acceptance of terms/privacy.
- Current/target versions conflict with the other update screens.

### Native implementation usability
Feasible as a server-configured gate that deep-links to the App Store/Play Store. Must handle store unavailable, user return and update-check loading states.

### Reusable components identified
- SystemStateLayout
- VersionComparisonCard
- PrimaryButton
- SecondaryButton
- LegalLinkText

### Dynamic backend data required
- Minimum/recommended app version policy
- Installed version
- Store version
- Release notes
- Store URL

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Legal/UX | Copy says updating constitutes acceptance of terms and privacy policy. | Remove this consent claim; legal acceptance must be an explicit, separately reviewed flow if required. |
| MAJOR | Consistency | Versions `1.2.3` and `1.3.0` contradict `1.2.0`, `2.3.1` and `v2.4.0`. | Drive all update UI from the same release/config source. |
| MAJOR | Native behavior | CTA does not state it opens the platform store. | Label/hint the external store handoff and define failure/return behavior. |
| MINOR | Header | Notification access is unrelated to the update gate. | Remove the notification icon. |

### Canonical recommendation
Correct before use; use this only as the optional-update state paired with the forced-update screen after both share one version source.

## 09-attach-files-documents-fr.png

Folder: `09-support-settings/`  
Screen purpose: Add and review attachments for a support request.  
Probable native route: `/support/request/attachments`  
Language: French  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An attachment step supporting image picker, document picker, camera capture, removal and continuation.

### Visible UI structure
- Header: back, official logo, notification icon
- Main content: three source cards and upload constraints
- Cards: attached-file list with thumbnails and remove actions
- Primary action: continue
- Secondary actions: add another file and delete individual files
- Navigation: no bottom bar
- Overlays: none

### Brand validation
- Palette, official logo, rounded cards and coherent line icons fit the system.
- Red remove actions correctly communicate deletion.

### UX validation
- File sources, count `3/5`, formats and sizes are clear.
- Limit `10 Mo` contradicts the `5 Mo` limit on contact/reply screens.
- Camera/photo/document permissions and failure/progress states are absent.

### Native implementation usability
Feasible with native image/document pickers, camera permissions, upload queue and removable local items. File MIME/size validation must be programmatic rather than inferred from extensions.

### Reusable components identified
- AppHeader
- AttachmentSourceCard
- AttachmentListItem
- InlineInfo
- PrimaryButton
- DashedAddButton

### Dynamic backend data required
- Allowed MIME types
- Maximum file size
- Maximum attachment count
- Selected file metadata
- Upload progress/error state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Consistency | `10 Mo` conflicts with `5 Mo` elsewhere in the same support flow. | Define one backend-enforced limit and display it consistently on every attachment entry point. |
| MAJOR | Product behavior | DOC/DOCX acceptance and five-file limit are not evidenced. | **NEEDS PRODUCT DECISION:** confirm MIME allowlist, count and antivirus/scanning behavior. |
| MINOR | Feedback | No upload progress, failed-upload or permission-denied state is represented. | Add per-file progress/error/retry and permission recovery patterns. |

### Canonical recommendation
Correct before use; best attachment-management reference once constraints are unified.

## 09-choose-language-french-arabic-fr.png

Folder: `09-support-settings/`  
Screen purpose: Switch between French and Arabic.  
Probable native route: `/settings/language`  
Language: French with Arabic option label  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A two-language selector with selected state, restart notice and apply action.

### Visible UI structure
- Header/main brand: centered official logo, no standard back header
- Main content: decorative language illustration and two large selectable cards
- Primary action: apply language
- Secondary action: return home
- Navigation: no bottom bar
- Overlays: none

### Brand validation
- Logo, cream/navy/orange palette, cards and display typography align.
- The hero uses a Chinese character even though only French and Arabic are supported.

### UX validation
- Current language and alternative are visible, but French is already selected while `Appliquer` remains fully active.
- Full-app restart behavior is asserted without evidence.
- Arabic option should be announced with its language name and selection state, not decorative color alone.

### Native implementation usability
Feasible with radio rows and persisted locale; RTL direction change may require a controlled app reload depending on the chosen i18n/navigation architecture.

### Reusable components identified
- LanguageOptionRow
- RadioIndicator
- PrimaryButton
- SecondaryTextAction
- InlineInfo

### Dynamic backend data required
- Supported locale list
- Current locale
- Whether restart/reload is required

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Content/branding | Decorative Chinese glyph introduces an unsupported language. | Replace with neutral translation/FR-AR imagery from the approved asset system. |
| MAJOR | Product behavior | Mandatory restart is stated as fact. | **NEEDS PRODUCT DECISION:** confirm i18n reload strategy; explain only the behavior actually implemented. |
| MINOR | State clarity | Apply CTA is enabled when the currently selected language has not changed. | Disable until selection changes or make selection apply immediately. |

### Canonical recommendation
Correct before use; retain the two radio-card concept and remove unsupported language cues.

## 09-clear-cache-confirmation-dialog-fr.png

Folder: `09-support-settings/`  
Screen purpose: Confirm deletion of temporary cache data.  
Probable native route: `/settings/storage/clear-confirmation`  
Language: French  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A full-screen confirmation for clearing app cache, despite being named as a dialog.

### Visible UI structure
- Header: back, oversized logo, notification icon
- Main content: trash/broom illustration, confirmation copy and data summary
- Primary action: `Vider le cache`
- Secondary action: cancel
- Navigation: no bottom bar
- Overlays: none; no dialog scrim or modal surface

### Brand validation
- Logo, palette, display heading and cards are consistent.
- Destructive action uses brand orange instead of semantic red.

### UX validation
- Consequence and estimated space are stated.
- Filename/intent says dialog, but the visual is a full route, losing spatial context.
- `245 Mo` conflicts with the `124 Mo` and `128 Mo` cache screens.

### Native implementation usability
Cache clearing is feasible for app-owned caches. Prefer a native confirmation dialog/bottom sheet from the storage page, with loading, success, partial-failure and refreshed-size states.

### Reusable components identified
- ConfirmationDialog
- DestructiveButton
- SecondaryButton
- CacheSummary
- StatusIllustration

### Dynamic backend data required
- App-owned cache size
- Clear operation progress/result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Screen pattern | Named/required as a dialog but rendered as a standalone full page with no scrim. | Use the foundation confirmation dialog or bottom sheet over the storage route. |
| MAJOR | Destructive semantics | Delete action is orange rather than semantic red. | Use the destructive button token and announce irreversible cache deletion clearly. |
| MAJOR | Data consistency | `245 Mo` conflicts with `124 Mo` and `128 Mo` in related screens. | Read the live cache total once and pass the same value into the confirmation. |

### Canonical recommendation
Combine with `09-storage-cache-management-fr.png` as a corrected modal state; do not use this full-page layout directly.

## 09-close-request-confirmation-fr.png

Folder: `09-support-settings/`  
Screen purpose: Confirm closing an unresolved support request.  
Probable native route: `/support/tickets/:ticketId/close`  
Language: French  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A destructive close-ticket confirmation with an option to keep the request open.

### Visible UI structure
- Header: back, logo and notification icon
- Main content: close illustration, consequence copy and warning card
- Primary action: close request
- Secondary action: keep open
- Navigation: no bottom bar
- Overlays: none; not a dialog/sheet

### Brand validation
- Brand palette and component shapes are coherent.
- The irreversible close CTA uses promotional orange instead of danger red.

### UX validation
- Consequence and recovery choice are clear.
- A modal/bottom sheet would preserve the ticket context and reduce accidental route churn.
- Security/privacy footer is unrelated to closing a ticket.

### Native implementation usability
Feasible as a confirmation dialog or bottom sheet launched from ticket detail, with disabled/loading state and post-close feedback.

### Reusable components identified
- ConfirmationDialog
- WarningCallout
- DestructiveButton
- SecondaryButton

### Dynamic backend data required
- Ticket identifier
- Current ticket status
- Close eligibility
- Close operation result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation pattern | Confirmation is a full page instead of a contextual dialog/sheet. | Present it over ticket detail with a clear dismiss route. |
| MAJOR | Destructive semantics | `Clôturer la demande` uses brand orange. | Use semantic danger styling and require explicit confirmation. |
| MINOR | Content hierarchy | Large illustration and privacy footer add noise to a two-choice decision. | Use a compact modal with ticket context and consequence only. |

### Canonical recommendation
Correct before use; combine the copy with the foundation confirmation pattern.

## 09-contact-support-form-fr.png

Folder: `09-support-settings/`  
Screen purpose: Create a support request.  
Probable native route: `/support/contact`  
Language: French  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A labeled support form with category/subject selection, message, email, reply channel and optional attachment.

### Visible UI structure
- Header: back, logo, notification icon and support illustration
- Forms: subject, category, message, email, channel selector, attachment row
- Primary action: send request
- Secondary actions: choose attachment and WhatsApp reply channel
- Navigation: no bottom bar
- Overlays: dropdown states not shown

### Brand validation
- Official logo, cream/white/navy palette, orange focus state, rounded fields and coherent icons align.
- Page is vertically dense because of the large decorative header.

### UX validation
- Visible labels and required markers are good.
- `Sujet` and `Catégorie` both appear as dropdowns, leaving no free-form subject and unclear distinction.
- WhatsApp support and response behavior are not established; keyboard avoidance/error states are absent.

### Native implementation usability
Feasible with reusable labeled inputs, picker sheets, radio controls and attachment navigation. Use KeyboardAvoidingView/scroll-to-error, email keyboard/autofill and disabled/loading submission.

### Reusable components identified
- AppHeader
- FormSelect
- MultilineInput
- EmailInput
- SegmentedChoice
- AttachmentRow
- PrimaryButton

### Dynamic backend data required
- Support categories/subjects
- Authenticated email/profile
- Supported response channels
- Attachment constraints
- Form submission/loading/errors

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Consistency | Attachment limit is `5 Mo`, conflicting with the dedicated `10 Mo` screen. | Use one backend-defined attachment policy everywhere. |
| MAJOR | Form architecture | Subject and category are both selectors with overlapping meaning. | Keep category as a picker and make subject a concise labeled text input, or document a distinct taxonomy. |
| MAJOR | Product behavior | WhatsApp is offered as a reply channel without evidence. | **NEEDS PRODUCT DECISION:** confirm opt-in, verified number, data-sharing and fallback behavior. |
| MINOR | Mobile usability | No keyboard-visible state or inline validation is shown. | Add keyboard avoidance, per-field errors, first-invalid-field focus and loading/disabled submit state. |

### Canonical recommendation
Correct before use; retain its form-control styling after resolving taxonomy, channel and attachment policy.

## 09-data-usage-image-quality-wifi-cache-fr.png

Folder: `09-support-settings/`  
Screen purpose: Configure image/data usage and access cache management.  
Probable native route: `/settings/data-usage`  
Language: French  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A data-usage settings page with image quality, Wi-Fi-only downloads, reduced data, automatic refresh and cache usage.

### Visible UI structure
- Header: back, large official logo, notification icon
- Main content: settings cards and cache meter
- Primary action: clear cache
- Secondary actions: quality dropdown and toggles
- Navigation: five-tab buyer bottom bar with `Compte` active
- Overlays: none

### Brand validation
- Palette, logo, cards, type and orange controls align.
- Bottom navigation uses coherent outline icons but has the wrong order.

### UX validation
- Settings are understandable and grouped.
- `Panier` appears before `Favoris`, contradicting the required nav.
- Cache total conflicts with related screens; destructive cache action is styled as a primary CTA.

### Native implementation usability
Feasible with persisted settings rows and a live cache query. Network/image policies require a documented image pipeline and background-refresh behavior.

### Reusable components identified
- AppHeader
- SettingsToggleRow
- SettingsValueRow
- CacheMeterCard
- BottomNavigation
- DestructiveButton

### Dynamic backend data required
- Image quality preference
- Wi-Fi-only preference
- Reduced-data preference
- Automatic refresh preference
- Cache size

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Tabs are `Accueil`, `Catégories`, `Panier`, `Favoris`, `Compte`. | Swap `Favoris` and `Panier` to the approved order and reuse one global tab component. |
| MAJOR | Consistency | Cache reads `128 Mo`, versus `124 Mo` and `245 Mo` in the same journey. | Query a single live cache value and pass it through navigation. |
| MINOR | Destructive semantics | `Vider le cache` is orange primary. | Use a secondary/destructive entry that opens a red confirmation dialog. |

### Canonical recommendation
Correct before use; good settings-row concept, but `09-storage-cache-management-fr.png` should be the canonical cache detail route.

## 09-faq-article-track-order-steps-fr.png

Folder: `09-support-settings/`  
Screen purpose: Explain how to track an order.  
Probable native route: `/support/faq/articles/track-order`  
Language: French  
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A help article with four steps, related articles, usefulness feedback and support escalation.

### Visible UI structure
- Header: back, logo, notification icon and article illustration
- Main content: numbered instruction card
- Cards: related articles and helpfulness prompt
- Primary action: contact support
- Secondary actions: related links and yes/no feedback
- Navigation: no bottom bar, acceptable for a deep article
- Overlays: none

### Brand validation
- Official logo, palette, Playfair/Inter hierarchy, line icons and rounded cards are consistent.
- Content is dense but retains adequate spacing and hierarchy.

### UX validation
- Goal, steps and escalation are clear.
- Desktop verb `Cliquez` is inappropriate for native mobile.
- Real-time carrier handoff is useful only if the order system supports it.

### Native implementation usability
Feasible with a ScrollView, numbered step component and reusable related-link rows. Text must wrap at large font sizes.

### Reusable components identified
- AppHeader
- FAQArticleHero
- NumberedStepList
- RelatedArticleRow
- HelpfulnessControl
- SupportCTA

### Dynamic backend data required
- Article title/body/steps
- Related articles
- Helpfulness vote state
- Support route

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Mobile copy | `Cliquez sur` uses desktop terminology. | Replace with `Touchez` or `Sélectionnez`. |
| MINOR | Product behavior | Copy promises real-time status and carrier redirect. | **NEEDS PRODUCT DECISION:** retain only if tracking URLs/status freshness are supported. |

### Canonical recommendation
Use as the main FAQ article reference after copy/product verification.

## 09-faq-tab-categories-fr.png

Folder: `09-support-settings/`  
Screen purpose: Browse FAQ questions by category.  
Probable native route: `/support/faq`  
Language: French  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A searchable FAQ list with category tabs, accordion questions, feedback and support escalation.

### Visible UI structure
- Header: back, logo, notification icon
- Main content: search field and five category tabs
- Cards: six accordion rows, feedback panel and orange support banner
- Primary action: contact support
- Secondary actions: question expansion, yes/no feedback
- Navigation: no bottom bar
- Overlays: none

### Brand validation
- Palette, official logo, typography and card/icon language align.
- Five text tabs are tightly packed and may not survive French/Arabic expansion.

### UX validation
- Search and categorized accordion are logical.
- `Cet article vous a-t-il été utile ?` is placed on the FAQ list, not an article.
- Tab and chevron targets need 44–48 dp hit areas and horizontal-scroll behavior for Dynamic Type.

### Native implementation usability
Feasible with a virtualized accordion list and horizontally scrollable filter chips. Preserve expanded state and search query on back navigation.

### Reusable components identified
- AppHeader
- SearchField
- ScrollableCategoryTabs
- FAQAccordionRow
- HelpfulnessControl
- SupportBanner

### Dynamic backend data required
- FAQ categories
- Question list and answers
- Search results
- Helpfulness vote state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Information architecture | Article-specific usefulness prompt appears on the category/list page. | Move feedback into the expanded answer/article, or ask whether the help center page was useful. |
| MAJOR | Accessibility | Five fixed-width tabs are too tight for Dynamic Type and Arabic. | Use scrollable chips/tabs with minimum touch area and accessible selected state. |
| MINOR | Header | Notification icon is unrelated to FAQ browsing. | Remove from the deep help header. |

### Canonical recommendation
Correct before use; it can become the canonical FAQ listing after the feedback and tab behavior are fixed.

## 09-forced-update-required-fr.png

Folder: `09-support-settings/`  
Screen purpose: Block use until a required app update is installed.  
Probable native route: `/system/update-required`  
Language: French  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A non-dismissible forced-update gate with update and support actions.

### Visible UI structure
- Header/main brand: logo only; no back or bottom navigation
- Main content: update illustration, reason and target-version card
- Primary action: update now
- Secondary action: contact support
- Navigation: intentionally blocked
- Overlays: none

### Brand validation
- Official logo, cream/orange/navy palette and display typography are consistent.
- Large illustration is acceptable for a blocking system state.

### UX validation
- Required action and support fallback are clear.
- Version conflicts with all other version screens.
- Store handoff, offline behavior and return-to-app recheck are unspecified.

### Native implementation usability
Feasible using remotely configured minimum supported versions and a non-dismissible navigation gate. Deep-link to official stores and recheck on foreground.

### Reusable components identified
- SystemGateLayout
- UpdateVersionCard
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Minimum supported version
- Installed version
- Store URL/version
- Support availability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Consistency | Target `v2.4.0` conflicts with other update/about values. | Use one release policy source for optional and forced update states. |
| MAJOR | Native behavior | Update CTA does not disclose App Store/Play Store handoff or recheck behavior. | Add platform-specific label/accessibility hint and foreground revalidation. |

### Canonical recommendation
Correct before use; pair with the optional update screen and shared version policy.

## 09-help-category-orders-delivery-fr.png

Folder: `09-support-settings/`  
Screen purpose: Browse help for orders and delivery.  
Probable native route: `/support/categories/orders-delivery`  
Language: French  
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A category landing page containing popular questions, related order actions and support escalation.

### Visible UI structure
- Header: back, logo, notification icon and delivery illustration
- Cards: popular question rows and related action rows
- Primary action: contact support
- Secondary action: `Retour à l’aide`
- Navigation: no bottom bar
- Overlays: none

### Brand validation
- Brand imagery, official logo, warm palette, type hierarchy and cards are consistent.
- Outline icons share the support-system style.

### UX validation
- Content hierarchy and routes are clear.
- Top back action plus full-width bottom back action are redundant.
- Return/refund and cancellation availability must be driven by actual order state.

### Native implementation usability
Feasible with reusable navigation rows and server/CMS-driven FAQ content. State-dependent actions must be filtered, not shown universally.

### Reusable components identified
- AppHeader
- HelpCategoryHero
- NavigationRow
- SupportCTA
- SecondaryButton

### Dynamic backend data required
- Category questions
- Related actions
- Current order/action eligibility, when personalized

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Navigation | Duplicates back navigation at top and bottom. | Keep the standard header back; remove the full-width return action. |
| MINOR | Product behavior | Return/refund/cancellation actions appear universally. | **NEEDS PRODUCT DECISION:** show only routes supported for the user's eligible orders. |

### Canonical recommendation
Use as the main order/delivery help-category reference after minor navigation and eligibility corrections.

## 09-help-center-home-ar.png

Folder: `09-support-settings/`  
Screen purpose: Arabic RTL help-center home.  
Probable native route: `/support`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An RTL help hub with search, categories, recent requests, FAQ and contact-support actions.

### Visible UI structure
- Header: mirrored status bar, notification icon at left, centered official logo
- Main content: RTL title/search, six category cells, recent request rows, FAQ row
- Primary action: contact support
- Secondary actions: categories, recent requests, FAQ
- Navigation: mirrored five-tab buyer bar with `Compte` active
- Overlays: none

### Brand validation
- Official logo, cream/navy/orange palette, Arabic type and rounded cards align.
- Icon family and semantic status colors are coherent.

### UX validation
- Search and most rows are correctly aligned RTL; IDs/numbers remain readable.
- The bottom bar preserves the same erroneous Favoris/Panier swap as French screens.
- `عرض كل الطلبات` is visually pulled to the left instead of following the row's RTL alignment.

### Native implementation usability
Feasible with `I18nManager`/logical start-end properties and localized list components. Explicitly test screen-reader order and mixed LTR ticket IDs/dates.

### Reusable components identified
- RTLAppHeader
- SearchField
- HelpCategoryGrid
- SupportRequestRow
- SupportCTA
- BottomNavigation

### Dynamic backend data required
- Arabic categories/content
- Recent support requests
- Status/date values
- Search/FAQ destinations

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Mirrored bar orders `Compte`, `Favoris`, `Panier`, `Catégories`, `Accueil`; Panier/Favoris are swapped relative to the approved route model. | Generate the RTL visual order from the canonical logical tabs: `Compte`, `Panier`, `Favoris`, `Catégories`, `Accueil`. |
| MAJOR | RTL | `عرض كل الطلبات` and its arrow are left-clustered rather than using logical start/end consistently. | Right-align the action label in RTL and place the directional icon using logical layout rules. |
| MINOR | Localization | Arabic grammar and mixed date/ID ordering require native-language QA. | Perform Arabic linguistic and TalkBack/VoiceOver review with live content. |

### Canonical recommendation
Correct before use; it is the Arabic help-home reference only after nav and RTL action alignment are fixed.

## 09-help-center-home-categories-requests-fr.png

Folder: `09-support-settings/`  
Screen purpose: French help-center home.  
Probable native route: `/support`  
Language: French  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A help hub with search, six categories, recent requests, FAQ and support availability.

### Visible UI structure
- Header: logo and notification icon, no back action
- Main content: title/search, category grid, two request rows, FAQ row
- Primary action: contact support
- Secondary actions: view all requests and FAQ
- Navigation: five-tab buyer bar with `Compte` active
- Overlays: none

### Brand validation
- Official logo, warm palette, Playfair/Inter hierarchy, cards and icons align.
- Bottom icons are coherent but the order is wrong.

### UX validation
- Hub hierarchy and support escalation are clear.
- Support hours and recent-request status wording are hard-coded functional claims.
- Bottom navigation swaps `Panier` and `Favoris`.

### Native implementation usability
Feasible using reusable grid/list components and a virtualized content list. Support hours/status must be data-driven and localized.

### Reusable components identified
- HelpCenterHeader
- SearchField
- HelpCategoryGrid
- SupportRequestRow
- SupportCTA
- BottomNavigation

### Dynamic backend data required
- Categories
- Recent support requests/statuses
- Support availability/hours
- FAQ/search routes

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Bottom tabs place `Panier` before `Favoris`. | Reuse the canonical five-tab component in the required order. |
| MINOR | Product behavior | `Du lundi au vendredi de 9h à 18h` is unverified. | **NEEDS PRODUCT DECISION:** source hours/time zone from support configuration or omit. |
| MINOR | Copy | `Livraison livrée` is awkward/duplicative. | Use `Commande livrée` or the canonical support status label. |

### Canonical recommendation
Correct before use; this should become the French help-home canonical after navigation and dynamic copy fixes.

## 09-help-center-search-results-fr.png

Folder: `09-support-settings/`  
Screen purpose: Show help-center search results.  
Probable native route: `/support/search`  
Language: French  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A populated support search state with article matches, category matches and contact escalation.

### Visible UI structure
- Header: logo and notification icon, no explicit back
- Main content: populated search field, result count, article/category result cards
- Primary action: contact support
- Secondary actions: clear search and open results
- Navigation: five-tab buyer bar with `Compte` active
- Overlays: none

### Brand validation
- Palette, typography, cards, icons and search treatment are consistent.
- Bottom navigation order is inconsistent with the approved system.

### UX validation
- Query, count and result grouping are clear.
- Clear button needs an accessibility label and sufficiently large hitSlop.
- No standard back action is visible, and tab order is wrong.

### Native implementation usability
Feasible with debounced search and a SectionList. Provide loading, empty, error and preserved-query states.

### Reusable components identified
- SearchField
- SearchResultRow
- SectionHeader
- SupportCTA
- BottomNavigation

### Dynamic backend data required
- Query
- Result count
- Article matches
- Category matches
- Search loading/error state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | `Panier` precedes `Favoris` in the bottom bar. | Apply the canonical tab order. |
| MINOR | Back behavior | No route-level back action is visible. | Add predictable back navigation while preserving the query/results. |
| MINOR | Accessibility | Clear-search icon is unlabeled visually. | Provide `accessibilityLabel="Effacer la recherche"` and 44–48 dp target. |

### Canonical recommendation
Correct before use; best populated help-search reference once navigation/back behavior is normalized.

## 09-legal-center-terms-policies-fr.png

Folder: `09-support-settings/`  
Screen purpose: Navigate legal documents and policies.  
Probable native route: `/settings/legal`  
Language: French  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A legal index linking terms, privacy, delivery, return, refund and legal notices.

### Visible UI structure
- Header: back, logo, notification icon and legal illustration
- Main content: six document navigation rows
- Primary action: none; rows are equal navigation actions
- Secondary actions: none
- Navigation: five-tab buyer bar with `Compte` active
- Overlays: none

### Brand validation
- Official branding, palette, typography, row cards and icon language align.
- Tab order is incorrect.

### UX validation
- Legal destinations are clearly named and scannable.
- The screenshot cannot establish that any linked legal text is approved or current.
- Bottom bar swaps required destinations.

### Native implementation usability
Feasible as a reusable legal-link list. Documents should be remotely versioned/cached and opened in a readable single-column view or secure browser.

### Reusable components identified
- AppHeader
- LegalHero
- SettingsNavigationRow
- BottomNavigation

### Dynamic backend data required
- Approved legal document titles
- Version/effective date
- Document URL/content
- Locale

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Bottom bar orders `Panier` before `Favoris`. | Use the one canonical buyer bottom-navigation component. |
| MAJOR | Legal content | Presence of links does not validate policies or their applicability. | **NEEDS PRODUCT DECISION / LEGAL REVIEW:** connect only approved, versioned Morocco-facing documents. |

### Canonical recommendation
Correct before use; retain as the legal-index structure, not as evidence that legal content exists.

## 09-maintenance-mode-services-impacted-fr.png

Folder: `09-support-settings/`  
Screen purpose: Explain a service maintenance outage.  
Probable native route: `/system/maintenance`  
Language: French  
Screen type:
- Error state

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A maintenance state listing impacted services, retry and support options.

### Visible UI structure
- Header: logo and notification icon, no back action
- Main content: maintenance illustration, explanation and impacted-services card
- Primary action: retry
- Secondary action: contact support
- Navigation: no bottom bar, appropriate for a system state
- Overlays: none

### Brand validation
- Official logo, warm palette, state illustration, rounded card and type hierarchy align.
- Status labels use warning orange with text, so meaning is not color-only.

### UX validation
- Impact and recovery actions are clear.
- `dans quelques instants` may make an unsupported duration promise.
- Last-check timestamp is useful and should update after retry.

### Native implementation usability
Feasible as a remotely configured maintenance screen with service flags, retry loading and refreshed timestamp.

### Reusable components identified
- SystemStateLayout
- ImpactedServiceRow
- StatusBadge
- PrimaryButton
- SecondaryButton
- LastCheckedLabel

### Dynamic backend data required
- Maintenance message
- Impacted services/statuses
- Last checked time
- Support route
- Retry result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Copy | `dans quelques instants` promises an unknown recovery window. | Use neutral copy or a backend-provided ETA. |
| MINOR | Header | Notification icon may be unavailable/irrelevant during maintenance. | Remove unless global notifications remain functional. |
| MINOR | State semantics | `Indisponible` uses warning orange rather than error red. | Confirm semantic token; use error if service is fully unavailable. |

### Canonical recommendation
Use as the main maintenance-state reference after minor dynamic-copy/token corrections.

## 09-marketing-preferences-detailed-fr.png

Folder: `09-support-settings/`  
Screen purpose: Configure marketing communication preferences.  
Probable native route: `/settings/marketing`  
Language: French  
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A detailed opt-in preferences page for email, push, collections, recommendations, cart reminders and campaigns.

### Visible UI structure
- Header: back, logo, notification icon and marketing illustration
- Main content: six labeled toggle rows
- Primary action: save preferences
- Secondary action: return
- Navigation: no bottom bar
- Overlays: none

### Brand validation
- Logo, palette, display type, orange toggles, cards and coherent icons align.
- Visual selected/off states are distinguishable.

### UX validation
- Preference labels and descriptions are clear and granular.
- Explicit save is acceptable, but dirty/saved/loading feedback is not represented.
- Consent categories and defaults require product/legal confirmation.

### Native implementation usability
Feasible using controlled Switch rows with an API-backed preference model, optimistic or explicit-save behavior, error rollback and accessible state announcements.

### Reusable components identified
- AppHeader
- SettingsToggleRow
- PrimaryButton
- SecondaryTextAction
- SettingsHero

### Dynamic backend data required
- Marketing preference definitions
- Current opt-in values
- Save status/error
- Consent timestamps/source, if required

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Form feedback | No dirty, saving, saved or failed state is visible. | Disable save until changed; show loading and accessible success/error feedback. |
| MINOR | Product/legal | Preference taxonomy/defaults are not evidenced. | **NEEDS PRODUCT DECISION / LEGAL REVIEW:** map toggles to actual consent purposes/channels. |
| MINOR | Header | Notification icon is unrelated to marketing consent. | Remove from deep settings headers. |

### Canonical recommendation
Use as the detailed marketing-preferences reference after state and consent mapping are confirmed.

## 09-my-support-tickets-list-fr.png

Folder: `09-support-settings/`  
Screen purpose: Browse and filter support tickets.  
Probable native route: `/support/tickets`  
Language: French  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A ticket history with search, status tabs, unread counts and ticket-detail actions.

### Visible UI structure
- Header: back, logo, notification icon and support illustration
- Main content: search, four status tabs and ticket cards
- Primary action: per-ticket `Voir les détails`
- Secondary actions: search/filter tabs
- Navigation: five-tab buyer bar with `Compte` active
- Overlays: none

### Brand validation
- Official logo, palette, support illustration, badges and cards align.
- Bottom navigation order conflicts with the design-system requirement.

### UX validation
- Ticket metadata and statuses are understandable.
- Fourth card is visibly clipped under the fixed bottom bar, indicating insufficient list inset.
- Dense cards need flexible vertical growth under Dynamic Type.

### Native implementation usability
Feasible with a SectionList/FlatList, status filter and memoized ticket cards. Add bottom safe-area inset, pagination/loading/empty/error states and preserved search/filter state.

### Reusable components identified
- AppHeader
- SearchField
- ScrollableStatusTabs
- SupportTicketCard
- StatusBadge
- BottomNavigation

### Dynamic backend data required
- Ticket list
- Ticket/order IDs
- Subjects/statuses/unread counts
- Updated timestamps
- Search/filter state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Bottom bar swaps `Favoris` and `Panier`. | Use the canonical bottom-navigation component/order. |
| MAJOR | Safe area | Last card is hidden by the fixed bottom bar. | Add list `contentContainerStyle` bottom inset equal to tab bar plus safe area. |
| MINOR | Responsive text | Card metadata/actions are tightly packed. | Allow card height to grow and verify maximum Dynamic Type. |

### Canonical recommendation
Correct before use; this is the populated ticket-list canonical after nav and inset fixes.

## 09-no-support-requests-empty-state-fr.png

Folder: `09-support-settings/`  
Screen purpose: Empty state for a buyer with no support requests.  
Probable native route: `/support/tickets`  
Language: French  
Screen type:
- Empty state

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
An empty support history with education, FAQ and contact-support routes.

### Visible UI structure
- Header: back, logo and notification icon
- Main content: large support/furniture illustration, empty message and benefits card
- Primary action: consult FAQ
- Secondary action: contact support
- Navigation: no bottom bar
- Overlays: none

### Brand validation
- Strong Mayush furniture/support imagery, palette, official logo and state-card style align.
- Illustration is more elaborate than the foundation empty-state style but remains on brand.

### UX validation
- Empty condition and next steps are clear.
- `Parlez à un conseiller` implies a live person/channel not identified by either CTA.
- Decorative content dominates the first viewport on smaller devices.

### Native implementation usability
Feasible as a reusable EmptyState component within the ticket route. Use scrollable layout and dynamic content insets.

### Reusable components identified
- AppHeader
- EmptyState
- BenefitRow
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Support/FAQ route availability only

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Product copy | `Parlez à un conseiller` does not identify chat, phone or ticket form. | **NEEDS PRODUCT DECISION:** name only the available support channel. |
| MINOR | Layout | Large illustration delays actions on short/small screens. | Reduce hero height or prioritize the empty message and CTA. |

### Canonical recommendation
Use as the canonical empty-ticket state after minor copy/layout correction.

## 09-notification-settings-matrix-grid-fr.png

Folder: `09-support-settings/`  
Screen purpose: Configure notification categories by delivery channel.  
Probable native route: `/settings/notifications`  
Language: French  
Screen type:
- Full page

### Status
REFERENCE_ONLY

### Confidence
High

### What the screen represents
A six-by-four notification preference matrix for in-app, push, email and SMS channels.

### Visible UI structure
- Header: back, logo and notification icon
- Main content: matrix card with column headers and 24 switches, informational card
- Primary action: save preferences
- Secondary actions: individual switches
- Navigation: no bottom bar
- Overlays: none

### Brand validation
- Logo, palette and cards broadly align.
- Active switches are navy rather than the established orange control state.

### UX validation
- Matrix communicates channel/category mapping at a glance, but it is too dense for a narrow phone.
- Off and unsupported/disabled gray states are indistinguishable.
- Dynamic Type and screen-reader traversal across a visual grid will be difficult.

### Native implementation usability
Individual settings are implementable, but the full matrix should become grouped category rows or drill-down screens. SMS/email/push availability must be configured per category.

### Reusable components identified
- AppHeader
- NotificationCategoryCard
- SettingsToggleRow
- PrimaryButton
- InfoNote

### Dynamic backend data required
- Notification categories
- Supported channels per category
- Current opt-ins
- System push-permission state
- Save result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Mobile layout | 24 switches form a desktop-like matrix that will not adapt to Dynamic Type/Arabic. | Replace with a vertical list of categories; open channel choices per category. |
| MAJOR | State clarity | Gray switches could mean off, disabled or unsupported. | Use explicit labels/helper text and native disabled semantics. |
| MAJOR | Brand consistency | Enabled switches use navy, unlike orange foundation toggles. | Use the canonical selected-control token. |
| MAJOR | Product behavior | SMS/email/push support by category is invented. | **NEEDS PRODUCT DECISION:** define actual channels, consent rules and unavailable states. |

### Canonical recommendation
Keep only as a preference-taxonomy/component idea; do not implement the full screen layout directly.

## 09-offline-mode-limited-functionality-fr.png

Folder: `09-support-settings/`  
Screen purpose: Explain offline browsing and unavailable actions.  
Probable native route: `/system/offline`  
Language: French  
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An offline state allowing limited browsing while checkout, tracking and notifications are unavailable.

### Visible UI structure
- Header: logo and notification icon
- Main content: offline illustration, cached-content explanation and unavailable-actions card
- Primary action: retry connection
- Secondary action: continue browsing
- Navigation: five-tab buyer bar with `Compte` active
- Overlays: none

### Brand validation
- Palette, logo, state illustration and card treatment align.
- Bottom navigation order is inconsistent.

### UX validation
- Offline condition and recovery are clear.
- Cached product browsing and later synchronization are asserted without evidence.
- `Recevoir des notifications` being unavailable is an OS/network condition, not necessarily an in-app feature gate.

### Native implementation usability
Feasible only if an offline cache/storage strategy exists. Connectivity retry and stale-content indicators are needed; do not promise sync without implementation.

### Reusable components identified
- SystemStateLayout
- UnavailableFeatureRow
- PrimaryButton
- SecondaryButton
- BottomNavigation

### Dynamic backend data required
- Connectivity state
- Cached content availability/age
- Retry result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Bottom tabs swap `Favoris` and `Panier`. | Apply canonical navigation order. |
| MAJOR | Product behavior | Cached browsing and deferred synchronization are presented as implemented. | **NEEDS PRODUCT DECISION:** define offline scope, stale labels and sync conflict behavior before using this copy. |

### Canonical recommendation
Correct before use; adopt only after offline behavior is technically defined.

## 09-privacy-data-policies-delete-account-fr.png

Folder: `09-support-settings/`  
Screen purpose: Manage privacy, data requests, permissions and account deletion.  
Probable native route: `/settings/privacy`  
Language: French  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A privacy hub combining policy links, marketing/recommendation toggles, permissions, data export and account deletion.

### Visible UI structure
- Header: back, logo and notification icon
- Main content: privacy rows/toggles and destructive delete-account row
- Primary action: none
- Secondary actions: policy, permissions and data request rows
- Navigation: five-tab buyer bar with `Compte` active
- Overlays: none

### Brand validation
- Official logo, palette, card system and icons align.
- Delete row uses orange rather than the semantic danger red.

### UX validation
- Privacy choices are grouped logically, but the delete-account row is partly obscured by the fixed bottom bar.
- Bottom navigation order is wrong.
- Marketing here overlaps the more detailed marketing-preferences screen.

### Native implementation usability
Feasible with a scrollable settings list, but requires bottom inset and dedicated authenticated deletion/data-export flows with status, re-authentication and recovery information.

### Reusable components identified
- AppHeader
- SettingsNavigationRow
- SettingsToggleRow
- DestructiveNavigationRow
- BottomNavigation

### Dynamic backend data required
- Privacy/marketing preferences
- Data export request status
- Account deletion eligibility/state
- Approved policy URL/version

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Safe area | Delete-account row is obscured by the bottom bar. | Add bottom content inset and keep destructive action fully visible/separated. |
| MAJOR | Navigation | `Panier` appears before `Favoris`. | Use the required canonical order. |
| MINOR | Information architecture | Marketing controls duplicate a dedicated detailed screen. | Use a summary link or one shared preference model, avoiding contradictory toggles. |

### Canonical recommendation
Correct before use; retain as the privacy hub after safe-area, navigation and consent-model fixes.

## 09-privacy-policy-full-document-fr.png

Folder: `09-support-settings/`  
Screen purpose: Read/search a privacy policy.  
Probable native route: `/settings/legal/privacy`  
Language: French  
Screen type:
- Full page

### Status
REJECTED

### Confidence
High

### What the screen represents
A privacy document viewer with search, a nine-section table of contents and browser handoff.

### Visible UI structure
- Header: back, logo and notification icon
- Main content: title/date, document search, fixed left contents column and right article column
- Primary action: open in browser
- Secondary actions: section selection/search
- Navigation: no bottom bar
- Overlays: none

### Brand validation
- Colors, logo, typography and surfaces fit Mayush.
- The two-column composition is a desktop pattern forced into a phone.

### UX validation
- Body and table-of-contents text are too narrow and will fail Dynamic Type.
- Two adjacent vertical reading/scroll regions create unclear gesture behavior.
- Footer says information is `jamais partagées` while the document contains a `Partage des données` section.

### Native implementation usability
Do not reproduce this layout. Use one vertical document view with collapsible/sticky table of contents or open an approved responsive web document.

### Reusable components identified
- AppHeader
- LegalDocumentHeader
- DocumentSearchField
- CollapsibleTableOfContents
- ExternalLinkButton

### Dynamic backend data required
- Legally approved document content
- Effective/update date
- Section anchors
- Locale/version

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Mobile usability | Desktop two-column document/sidebar is unreadable and gesture-ambiguous on mobile. | Replace with a single-column responsive document and collapsible contents. |
| CRITICAL | Legal contradiction | `jamais partagées` contradicts the visible `Partage des données` section and is an overbroad promise. | Remove the claim; use legal-approved wording only. |
| MAJOR | Accessibility | Fixed narrow columns cannot support Dynamic Type or readable line length. | Use fluid width, native text scaling and one reading order. |

### Canonical recommendation
Replace with a responsive single-column legal viewer; keep only the search/section-anchor concept.

## 09-reply-to-support-message-fr.png

Folder: `09-support-settings/`  
Screen purpose: Compose a reply to an open support ticket.  
Probable native route: `/support/tickets/:ticketId/reply`  
Language: French  
Screen type:
- Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
A standalone reply form showing ticket summary, latest support message, response input and attachment action.

### Visible UI structure
- Header: back, logo, notification icon and conversation illustration
- Main content: ticket/latest-message card and reply form
- Primary action: send reply
- Secondary actions: attach file and cancel
- Navigation: no bottom bar
- Overlays: none

### Brand validation
- Logo, palette, cards, text hierarchy and support icons align.
- Standalone hero treatment is inconsistent with the denser canonical ticket-detail thread.

### UX validation
- Context and reply goal are clear.
- Duplicates the reply action/state already belonging to the ticket-detail conversation route.
- Attachment limit conflicts with the dedicated attachment screen; keyboard state is not shown.

### Native implementation usability
Composer is feasible, but it should be a screen/sheet reached from the canonical ticket detail, reusing its header/ticket context and upload policy.

### Reusable components identified
- TicketSummaryCard
- MultilineInput
- AttachmentRow
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Ticket ID/status/subject
- Latest support message
- Reply text
- Attachment constraints/progress
- Send state/error

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Consistency | `Max 5 Mo` conflicts with `10 Mo` in attachment management. | Use the shared backend attachment policy. |
| MINOR | Mobile usability | Keyboard may cover actions; no loading/error state is shown. | Use keyboard avoidance, scroll-to-input and disabled/loading/retry states. |

### Canonical recommendation
Combine its composer fields with `09-ticket-detail-conversation-thread-fr.png`; keep this image only as an alternative expanded-compose state.

## 09-review-send-support-request-fr.png

Folder: `09-support-settings/`  
Screen purpose: Review a support request before submission.  
Probable native route: `/support/request/review`  
Language: French  
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A final review step for category, related order, subject, message, attachment and masked contact information.

### Visible UI structure
- Header: back, logo and notification icon
- Main content: illustration and six summary cards with edit actions
- Primary action: send request
- Secondary actions: per-section modify links
- Navigation: no bottom bar
- Overlays: none

### Brand validation
- Official logo, palette, type, cards, icons and orange CTA are coherent.
- Moroccan `+212` phone context is correct.

### UX validation
- Review/edit hierarchy and masking are clear.
- Message contains a punctuation/line-break artifact.
- Security claim is broader than warranted; modify links need 44–48 dp hit areas.

### Native implementation usability
Feasible with reusable summary rows driven by one form state object. Submission needs loading/idempotency/error handling.

### Reusable components identified
- AppHeader
- ReviewSummaryCard
- EditAction
- MaskedContactBlock
- PrimaryButton

### Dynamic backend data required
- Draft support request
- Related order/items
- Attachment metadata
- Masked contact details
- Submit result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Copy | Message displays a stray punctuation/line-break artifact before `ma demande`. | Correct wrapping and source copy. |
| MINOR | Privacy copy | `jamais partagées` is an overbroad claim for a support submission. | Replace with accurate processing/security language approved by legal. |
| MINOR | Touch target | Small `Modifier` text/icon controls may be precision targets. | Make the entire action area at least 44–48 dp and provide accessibility labels. |

### Canonical recommendation
Use as the review-step reference after minor copy, privacy and target-size fixes.

## 09-select-order-for-support-fr.png

Folder: `09-support-settings/`  
Screen purpose: Associate a support request with an order.  
Probable native route: `/support/request/select-order`  
Language: French  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A searchable list of recent orders with status, thumbnails, totals and an option to continue without an order.

### Visible UI structure
- Header: back, logo, notification icon and support illustration
- Main content: order search and three order cards
- Primary action: select an order
- Secondary action: continue without order
- Navigation: no bottom bar
- Overlays: none

### Brand validation
- Official logo, warm palette, cards, product thumbnails, semantic statuses and `MAD` currency align.

### UX validation
- Order context is readable and realistic.
- No order is visibly selected, yet primary CTA is active.
- Chevrons imply immediate navigation while a separate selection CTA implies multi-step selection.

### Native implementation usability
Feasible with a FlatList and radio/selectable order cards. Use one interaction model, explicit selected state and disabled CTA until selection.

### Reusable components identified
- AppHeader
- SearchField
- SelectableOrderCard
- StatusBadge
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Eligible recent orders
- Order IDs/dates/statuses/items/totals
- Selected order ID
- Search state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Primary action | `Sélectionner une commande` is enabled with no selected card, and chevrons conflict with selection behavior. | Add radio/selected state, disable CTA until chosen, and remove chevrons unless they open order preview. |
| MINOR | Accessibility | Card selection is not conveyed beyond potential color/state. | Announce `selected` state and make the full card a 44–48 dp semantic control. |

### Canonical recommendation
Correct before use; retain the order-card data layout and implement explicit selection.

## 09-settings-error-loading-state-fr.png

Folder: `09-support-settings/`  
Screen purpose: Recover when settings fail to load.  
Probable native route: `/settings`  
Language: French  
Screen type:
- Error state

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A settings load-error state with retry and escape back to the account area.

### Visible UI structure
- Header: back, logo and notification icon
- Main content: connection/error illustration and explanatory copy
- Primary action: retry
- Secondary action: return to account
- Navigation: no bottom bar
- Overlays: none

### Brand validation
- Official logo, state illustration, palette, typography and buttons align with foundation feedback patterns.

### UX validation
- Cause, retry and escape route are clear.
- Filename calls this a loading state, but it is visibly an error state.
- Copy assumes network failure even though settings can fail for other causes.

### Native implementation usability
Feasible as the settings route's error branch. Retry must show loading/disabled feedback and preserve accessible focus on failure/success.

### Reusable components identified
- AppHeader
- ErrorState
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Settings load error/retry state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Classification | Filename says loading but visual is error. | Keep file unchanged historically; document/implement it as the error state. |
| MINOR | Error clarity | Assumes an Internet connection error for every failure. | Map copy to detected connectivity versus generic service failure. |
| MINOR | Header | Notification icon is unrelated to failed settings load. | Remove from deep settings error header. |

### Canonical recommendation
Use as the canonical settings error state after minor copy/header correction.

## 09-settings-menu-ar.png

Folder: `09-support-settings/`  
Screen purpose: Arabic RTL settings menu.  
Probable native route: `/settings`  
Language: Arabic  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An RTL settings list covering account/security, notifications, language, currency, theme, help/contact and logout.

### Visible UI structure
- Header: logo, right-side back arrow and settings hero
- Main content: three grouped RTL settings cards
- Primary action: logout
- Secondary actions: settings rows
- Navigation: no bottom bar
- Overlays: none

### Brand validation
- Logo, warm palette, Arabic typography, rounded rows and outline icons align.
- Logout is orange rather than semantic danger red.

### UX validation
- Rows, icons and chevrons are mostly mirrored correctly with right-aligned text.
- Currency-changing option conflicts with mandatory `MAD` and introduces unsupported behavior.
- Route contents do not translate the French settings menu: it adds currency/theme/account/logout while omitting accessibility, permissions, data, storage, privacy and app info.

### Native implementation usability
RTL row implementation is feasible using logical start/end layout. Canonical route taxonomy must be shared between locales, with only localized labels/order direction.

### Reusable components identified
- RTLAppHeader
- SettingsSection
- SettingsNavigationRow
- DestructiveButton

### Dynamic backend data required
- Account/security route availability
- Notification/language settings
- Theme availability, if supported
- Authentication/logout state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Currency | `العملة` offers a preferred-currency change, contradicting mandatory `MAD`. | Remove currency selection; display `MAD` consistently as a fixed market context. |
| MAJOR | Localization parity | Arabic and French settings represent different information architectures. | Use one canonical route list and translate/mirror it; add only locale-valid deviations approved by product. |
| MAJOR | Destructive semantics | Logout CTA uses orange brand treatment. | Use semantic destructive styling and a clear confirmation/session-state flow. |
| MINOR | RTL QA | Status-bar direction differs from the Arabic help screenshot, and mixed `MAD`/theme text needs native-language review. | Standardize RTL shell behavior and perform device linguistic/screen-reader QA. |

### Canonical recommendation
Correct before use; mirror and translate `09-settings-menu-full-list-fr.png` rather than treating this divergent list as canonical.

## 09-settings-menu-full-list-fr.png

Folder: `09-support-settings/`  
Screen purpose: Navigate the app's settings areas.  
Probable native route: `/settings`  
Language: French  
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A full settings index for notifications, silent hours, language, accessibility, permissions, data, storage, privacy and app information.

### Visible UI structure
- Header: back and centered logo
- Main content: title/subtitle and nine navigation rows in one card
- Primary action: none
- Secondary actions: row navigation
- Navigation: no bottom bar, acceptable for a deep settings index
- Overlays: none

### Brand validation
- Official logo, cream/white/navy palette, Playfair/Inter hierarchy, rounded card and coherent line icons align.

### UX validation
- Labels and descriptions are clear, and route taxonomy matches the folder's feature set.
- Nine rows in one undifferentiated card reduce grouping/scannability.
- Long list should scroll and retain back position at Dynamic Type.

### Native implementation usability
Straightforward with a SectionList and reusable navigation rows. Routes can be typed and deep-linked; rows need full-width press targets and accessibility hints.

### Reusable components identified
- AppHeader
- SettingsSection
- SettingsNavigationRow

### Dynamic backend data required
- Settings-route availability only; most rows are local navigation

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Information hierarchy | Nine heterogeneous items sit in one card. | Group into notifications/preferences, device/data, and legal/about sections. |
| MINOR | Responsive layout | Static screenshot does not demonstrate scrolling/Dynamic Type. | Implement as a flexible SectionList with safe-area inset and wrapping descriptions. |

### Canonical recommendation
Use as the French canonical settings index after minor grouping/responsive refinement.

## 09-settings-skeleton-loading-state.png

Folder: `09-support-settings/`  
Screen purpose: Placeholder while settings content loads.  
Probable native route: `/settings`  
Language: Not visible (French route shell)  
Screen type:
- Loading state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A settings skeleton with placeholder cards, but visible chevrons/toggles and bottom navigation.

### Visible UI structure
- Header: logo and notification icon; no back action
- Main content: title/subtitle and three skeleton cards
- Primary action: none
- Secondary actions: none intended, although real chevrons/toggles appear
- Navigation: five-tab bar with `Compte` active
- Overlays: none

### Brand validation
- Skeleton beige, white cards, cream background and official logo fit the foundation state style.
- Bottom navigation order conflicts with the required system.

### UX validation
- Layout reserves content space, which reduces shift.
- Real-looking toggles and chevrons imply interactive controls during loading.
- Shell does not match canonical settings header (back action) or its no-bottom-bar pattern.

### Native implementation usability
Feasible with non-interactive skeleton blocks and accessibility-hidden placeholders. Keep the stable route shell identical to loaded settings and announce loading once.

### Reusable components identified
- AppHeader
- SettingsSkeleton
- SkeletonRow
- BottomNavigation

### Dynamic backend data required
- Settings loading state only

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Bottom tabs swap `Favoris` and `Panier`, and the bar conflicts with the loaded settings reference. | Match the canonical settings shell and global tab rules. |
| MAJOR | State clarity | Visible toggles/chevrons look actionable while content is unavailable. | Skeletonize or hide controls; mark placeholders inaccessible/non-interactive. |
| MINOR | Consistency | Subtitle/header differ from `09-settings-menu-full-list-fr.png`. | Keep stable title, back action and surrounding layout to minimize content shift. |

### Canonical recommendation
Correct before use; retain skeleton proportions but match the loaded settings shell and remove interactive-looking controls.

## 09-silent-hours-day-selection-fr.png

Folder: `09-support-settings/`  
Screen purpose: Configure notification quiet hours.  
Probable native route: `/settings/notifications/silent-hours`  
Language: French  
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
Quiet-hours settings for enabled state, start/end time, days, Casablanca time zone and critical-alert override.

### Visible UI structure
- Header: back, logo, notification icon and clock illustration
- Main content: one settings card plus critical-notification toggle
- Primary action: save
- Secondary action: return to settings
- Navigation: no bottom bar
- Overlays: time/day pickers not shown

### Brand validation
- Palette, logo, typography, orange switches, cards and line icons align.

### UX validation
- Schedule and current values are clear.
- Critical-notification override needs precise definition and consent.
- Casablanca offset must account for actual time-zone changes rather than a fixed GMT string.

### Native implementation usability
Feasible with native time pickers, multi-select days and IANA zone data (`Africa/Casablanca`). Define schedules that cross midnight and device-zone changes.

### Reusable components identified
- AppHeader
- SettingsToggleRow
- SettingsValueRow
- TimePickerSheet
- DayPickerSheet
- PrimaryButton

### Dynamic backend data required
- Quiet-hours enabled state
- Start/end time
- Selected days
- Time zone
- Critical-notification policy

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Product behavior | `Notifications critiques` always bypass silence without a defined category policy. | **NEEDS PRODUCT DECISION:** specify eligible transactional alerts and user control. |
| MINOR | Localization/time | `(GMT+01:00) Casablanca` can become stale. | Store/use `Africa/Casablanca` and render current localized offset. |

### Canonical recommendation
Use as the quiet-hours reference after policy and time-zone handling are confirmed.

## 09-storage-cache-management-fr.png

Folder: `09-support-settings/`  
Screen purpose: Inspect and clear app cache.  
Probable native route: `/settings/storage`  
Language: French  
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A cache-management page showing total, image and temporary-file storage plus clear action.

### Visible UI structure
- Header: back, logo, notification icon and storage illustration
- Main content: cache breakdown card and information callout
- Primary action: clear cache
- Secondary actions: image/temp-file detail rows
- Navigation: no bottom bar
- Overlays: confirmation not shown

### Brand validation
- Official logo, warm palette, cards, typography and icons align.
- Clear-cache action uses primary orange rather than danger semantics.

### UX validation
- Cache breakdown and consequence are clear.
- `124 Mo` conflicts with `128 Mo` and `245 Mo` in related images.
- Detail chevrons imply subroutes whose function is not explained.

### Native implementation usability
Feasible for app-owned caches, with asynchronous size calculation, confirmation, progress and refreshed result. Platform-specific limitations must be handled.

### Reusable components identified
- AppHeader
- StorageSummaryCard
- StorageBreakdownRow
- InfoCallout
- DestructiveButton
- ConfirmationDialog

### Dynamic backend data required
- Total/image/temp cache sizes
- Clear operation progress/result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Data consistency | `124 Mo` conflicts with other cache values in the same flow. | Calculate once, refresh on focus, and pass the current value into confirmation. |
| MAJOR | Destructive semantics | Clear action is promotional orange. | Use semantic danger style and open a compact confirmation dialog. |
| MINOR | Interaction clarity | Breakdown rows have chevrons without defined destination/action. | Remove chevrons or define inspect/clear-per-category behavior. |

### Canonical recommendation
Correct before use; this is the preferred cache-management page, combined with a corrected modal from `09-clear-cache-confirmation-dialog-fr.png`.

## 09-support-connection-error-fr.png

Folder: `09-support-settings/`  
Screen purpose: Recover when support content fails to load.  
Probable native route: `/support`  
Language: French  
Screen type:
- Error state

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A support-specific connection error with retry, app continuation and email fallback.

### Visible UI structure
- Header: back, logo and notification icon
- Main content: connection-error illustration and recovery copy
- Primary action: retry
- Secondary action: continue in app
- Navigation: no bottom bar
- Overlays: none

### Brand validation
- Official logo, palette, illustration, typography and buttons match foundation error patterns.

### UX validation
- Failure, retry and escape path are clear.
- Support email is useful only if verified and tappable.
- Retry needs loading/disabled feedback.

### Native implementation usability
Feasible as the support route error branch with connectivity-aware messaging and `mailto:` fallback.

### Reusable components identified
- AppHeader
- ErrorState
- PrimaryButton
- SecondaryButton
- ContactFallback

### Dynamic backend data required
- Support load/connectivity error
- Verified support email
- Retry state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Product data | `support@mayushdesign.ma` is not evidenced. | **NEEDS PRODUCT DECISION:** verify address and render a tappable accessible mail action. |
| MINOR | Header | Notification icon is unrelated to a support connection failure. | Remove unless available globally. |

### Canonical recommendation
Use as the canonical support connection-error state after email/header verification.

## 09-support-request-sent-success-fr.png

Folder: `09-support-settings/`  
Screen purpose: Confirm successful support-request submission.  
Probable native route: `/support/request/success`  
Language: French  
Screen type:
- Success state

### Status
APPROVED

### Confidence
High

### What the screen represents
A submission success state with ticket ID, category, timestamp, status and clear next actions.

### Visible UI structure
- Header: logo and notification icon
- Main content: success illustration and ticket summary card
- Primary action: view ticket
- Secondary action: return to support
- Navigation: no bottom bar
- Overlays: none

### Brand validation
- Official logo, semantic green with icon/text, cream/orange/navy palette, typography and cards align.

### UX validation
- Success, reference number and next steps are unambiguous.
- Actions are large, distinct and logically ordered; no required action is missing.
- Layout should remain scrollable under Dynamic Type.

### Native implementation usability
Directly feasible from submission response data. Prevent duplicate submissions and allow ticket ID copying/accessibility announcement.

### Reusable components identified
- SuccessStateLayout
- TicketSummaryCard
- StatusBadge
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Created ticket ID
- Category
- Submission timestamp
- Initial status

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| — | — | No material issue identified. | Use the image directly; the notification icon may remain only if it is the documented global header action. |

### Canonical recommendation
Use directly as the canonical support-request success reference; the notification icon may be omitted during implementation.

## 09-support-temporarily-unavailable-fr.png

Folder: `09-support-settings/`  
Screen purpose: Explain a temporary support-service outage.  
Probable native route: `/support/unavailable`  
Language: French  
Screen type:
- Error state

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A support-outage state with retry and FAQ fallback.

### Visible UI structure
- Header: back, logo and notification icon
- Main content: unavailable-support illustration and explanatory copy
- Primary action: retry
- Secondary action: consult FAQ
- Navigation: no bottom bar
- Overlays: none

### Brand validation
- Logo, palette, illustration, type and buttons align with feedback patterns.

### UX validation
- Failure scope and recovery/fallback are clear.
- No last-check time or loading feedback is shown.
- Urgent-help note repeats the FAQ action without adding another recovery channel.

### Native implementation usability
Feasible with a service-health flag, retry/loading and cached FAQ access where available.

### Reusable components identified
- AppHeader
- ErrorState
- PrimaryButton
- SecondaryButton
- InfoNote

### Dynamic backend data required
- Support service availability
- Retry state
- FAQ cache/route availability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Feedback | No retry-in-progress or last-check state is represented. | Add spinner/disabled CTA and optional refreshed timestamp. |
| MINOR | Copy | `Besoin d’aide urgente ?` only repeats the FAQ option. | Remove the note or provide a verified alternative channel. |

### Canonical recommendation
Use as the canonical support-unavailable state after minor feedback/copy refinement.

## 09-ticket-detail-conversation-thread-fr.png

Folder: `09-support-settings/`  
Screen purpose: View an open support ticket conversation and reply.  
Probable native route: `/support/tickets/:ticketId`  
Language: French  
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
Ticket metadata plus buyer/support messages, attachments and a reply CTA.

### Visible UI structure
- Header: back, logo and notification icon
- Main content: ticket summary card and two message cards
- Primary action: reply to request
- Secondary actions: order link and attachments
- Navigation: no bottom bar
- Overlays: none

### Brand validation
- Official logo, palette, cards, ticket/support icons and semantic response status align.
- Metadata is dense but remains visually grouped.

### UX validation
- Ticket context, chronology and reply path are clear.
- Dotted spinner-like glyph next to `Priorité normale` is semantically ambiguous.
- Downloaded support attachments need secure viewer/error behavior.

### Native implementation usability
Feasible with a virtualized/inverted message list plus sticky reply CTA. Message cards and attachments must grow for long content; preserve scroll and support safe file handling.

### Reusable components identified
- AppHeader
- TicketSummaryCard
- ConversationMessageCard
- AttachmentChip
- StatusBadge
- StickyReplyCTA

### Dynamic backend data required
- Ticket/order metadata
- Priority/status
- Conversation messages/authors/timestamps
- Attachment metadata/URLs
- Reply eligibility

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Icon semantics | Spinner-like dots beside `Normale` look like loading, not priority. | Use text only or a documented priority icon/token. |
| MINOR | Security/native UX | Attachment open/download behavior is unspecified. | Use authenticated URLs, MIME validation, safe preview and download error state. |
| MINOR | Responsive layout | Dense metadata/message cards may expand substantially. | Use flexible heights and validate long messages/Dynamic Type. |

### Canonical recommendation
Use as the canonical open-ticket detail; combine it with the composer from `09-reply-to-support-message-fr.png`.

## 09-ticket-resolved-rating-fr.png

Folder: `09-support-settings/`  
Screen purpose: Show resolved-ticket outcome and collect support rating.  
Probable native route: `/support/tickets/:ticketId/resolved`  
Language: French  
Screen type:
- Success state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A resolved refund ticket with support response, resolution timestamp, five-star rating and related questions.

### Visible UI structure
- Header: back, logo and notification icon
- Main content: resolution illustration, ticket/status card, support response, rating card, related questions
- Primary action: star rating interaction
- Secondary actions: related FAQ links
- Navigation: no bottom bar
- Overlays: none

### Brand validation
- Official logo, semantic green success, palette, cards and typography align.
- Rating style matches foundation stars.

### UX validation
- Outcome and rating prompt are clear.
- Ticket prefix switches from `SUP-` everywhere else to `TKT-`.
- Screen alternates `ticket` and `demande`; refund amount/timing are definitive business claims.

### Native implementation usability
Feasible with a ticket outcome card and accessible radio-style rating. Refund copy must come from actual transaction/status data.

### Reusable components identified
- AppHeader
- ResolvedTicketSummary
- SupportResponseCard
- RatingControl
- RelatedArticleRow

### Dynamic backend data required
- Ticket ID/status/created/resolved times
- Support resolution response
- Refund amount/currency/method/status/ETA
- Existing/new rating
- Related questions

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Identifier consistency | Uses `TKT-2026-004892` while list/detail/reply use `SUP-...`. | Choose one support-ticket identifier schema and use it globally. |
| MAJOR | Terminology | Mixes `Ticket résolu` with `Votre demande` while other screens use both terms inconsistently. | Establish one buyer-facing noun (`demande d’assistance` or `ticket`) and localize consistently. |
| MAJOR | Business logic | States a `6 250 MAD` refund and `24h à 5 jours ouvrés` timing as fixed truth. | **NEEDS PRODUCT DECISION:** bind amount, processor status and approved ETA copy to actual payment/refund data. |
| MINOR | Accessibility | Empty stars appear as icons without visible labels/selected semantics. | Implement as an accessible 1–5 radio group with 44–48 dp targets and announced value. |

### Canonical recommendation
Correct before use; retain the resolved/rating composition after identifier, terminology and refund logic are normalized.

## Folder status summary

| Status | Count |
|---|---:|
| APPROVED | 1 |
| APPROVED_WITH_MINOR_FIXES | 12 |
| NEEDS_REWORK | 25 |
| REFERENCE_ONLY | 1 |
| REJECTED | 1 |
| DUPLICATE_ALTERNATIVE | 1 |
| **Total** | **41** |

Highest-priority corrections in this folder:

1. `09-app-permissions-camera-photos-location-fr.png`: remove impossible/contradictory bulk permission behavior.
2. `09-app-update-available-fr.png`: remove misleading terms acceptance and unify version/store logic.
3. `09-privacy-policy-full-document-fr.png`: replace the desktop two-column document and contradictory privacy promise.
4. `09-select-order-for-support-fr.png`: add explicit selection and disable the CTA until selected.
5. `09-settings-menu-ar.png`: remove currency switching and restore route parity with French.
6. All affected bottom-nav screens: restore `Accueil`, `Catégories`, `Favoris`, `Panier`, `Compte` and safe-area insets.
