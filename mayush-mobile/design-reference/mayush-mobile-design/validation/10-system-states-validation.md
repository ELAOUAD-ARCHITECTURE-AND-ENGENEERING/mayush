# 10-system-states Validation Report

**Folder:** `10-system-states/`  
**Total screenshots visually reviewed:** 63  
**Reference basis:** all boards in `00-foundation/` and `assetsl/`, with the project brief taking precedence where the boards conflict  
**Audit date:** 2026-08-02

This folder contains extensive filename/content mismatches, byte-identical `v2` copies, forbidden `BUYER APP` lockups, invented logo variants, and generator pagination such as `31 / 46`. Statuses below describe the visible image, not merely its filename.

> **Fact-check correction — authoritative:** Direct visual verification of `10-access-denied-403-fr.png` and `10-server-unavailable-fr.png`, together with the repeated `n / 46` pagination and incompatible logo/palette system, confirms that this folder is an exported presentation/deck series rather than a coherent final mobile-app screen set. Do not use any non-mismatched original as a direct React Native page reference: its authoritative classification is **REFERENCE_ONLY**. The 17 exact `v2` copies remain **DUPLICATE_ALTERNATIVE** and the 11 filename/state swaps remain **REJECTED**; the duplicate unusual-activity asset is counted as rejected because its filename is unsafe. The historical per-screen sections below are retained for component/state ideas; [screen-validation.csv](./screen-validation.csv) and `fact-check-correction.md` carry the superseding status.

---

## 10-data-restoration-progress-fr.png

Folder: `10-system-states/`  
Screen purpose: Show account-data restoration progress  
Probable native route: `/system/data-restoration`  
Language: French  
Screen type:
- Loading state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A multi-stage restoration process with progress, step labels, and a permanent bottom bar.

### Visible UI structure
- Blue-toned header/progress treatment
- Restoration illustration
- Percentage and step list
- Five-tab bottom navigation

### Brand validation
- Colors: Blue replaces the approved Mayush orange/navy emphasis
- Navigation: Uses `Accueil`, `Rechercher`, `Favoris`, `Messages`, `Compte` instead of the approved tabs
- Components: Clean but inconsistent with the source-of-truth system

### UX validation
Progress is understandable, though a destructive interruption policy and failure/retry behavior are not described.

### Native implementation usability
The progress model is reusable, but the navigation and color tokens must be replaced.

### Reusable components identified
- StepProgress
- ProgressBar
- RestorationStatusRow
- BottomNavigation

### Dynamic backend data required
- Restoration stages
- Current progress
- Per-stage status and errors

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Navigation | Bottom tabs use `Rechercher` and `Messages`, omitting `Catégories` and `Panier` | Use Accueil, Catégories, Favoris, Panier, Compte |
| MAJOR | Branding | Blue emphasis conflicts with the Mayush palette | Apply approved orange, navy, cream, and semantic status colors |
| MINOR | UX | Interruption and failure behavior are unspecified | Define whether restore continues in background and add retry/error states |

### Canonical recommendation
Correct navigation, palette, and recovery behavior before use.

---

## 10-generic-error-fr.png

Folder: `10-system-states/`  
Screen purpose: Generic recoverable application error  
Probable native route: Contextual error boundary  
Language: French  
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A neutral error page with retry and home recovery actions.

### Visible UI structure
- Modified logo
- Error illustration
- Short explanation
- Retry and return-home actions

### Brand validation
- Logo: Contains forbidden `BUYER APP`
- Palette/buttons/spacing: Consistent

### UX validation
The actions are clear. In production, retry should preserve context and the fallback must avoid an endless failure loop.

### Native implementation usability
Suitable for a reusable React error boundary or request error state after branding correction.

### Reusable components identified
- ErrorBoundaryFallback
- PrimaryButton
- SecondaryButton
- StatusIllustration

### Dynamic backend data required
- Error class or support code
- Retry callback
- Safe fallback route

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Forbidden `BUYER APP` logo treatment | Use the official logo only |

### Canonical recommendation
Correct branding before using as the generic French error template.

---

## 10-installation-progress-fr.png

Folder: `10-system-states/`  
Screen purpose: Depict an application installation in progress  
Probable native route: None without a platform-specific distribution decision  
Language: French  
Screen type:
- Loading state

### Status
REJECTED

### Confidence
High

### What the screen represents
A blocking in-app application installer telling the buyer not to close the app or device.

### Visible UI structure
- Modified logo
- Installation illustration
- Progress bar and percentage
- Blocking caution copy

### Brand validation
- Logo: Forbidden `BUYER APP`
- Visual components: Internally coherent

### UX validation
The screen implies that the installed application controls its own binary installation. That is not a standard App Store/Play Store buyer flow and the wording about not closing the device is misleading.

### Native implementation usability
Not a reliable cross-platform React Native route. Store-managed updates, managed enterprise distribution, and downloaded content each require different UX.

### Reusable components identified
- ProgressBar
- StatusIllustration

### Dynamic backend data required
- NEEDS PRODUCT DECISION: distribution/update mechanism

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Native behavior | In-app binary installation is presented as a normal cross-platform capability | Replace with platform store update UX unless an approved distribution mechanism exists |
| MAJOR | Branding | Modified logo uses forbidden wording | Use the official logo |

### Canonical recommendation
Do not use; product and engineering must first define the update mechanism.

---

## 10-location-access-denied-fr.png

Folder: `10-system-states/`  
Screen purpose: Recover from denied location permission  
Probable native route: `/system/permissions/location`  
Language: French  
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A location-permission explanation with system-settings and continue-without-location actions.

### Visible UI structure
- Invented logo treatment
- Location illustration
- Permission explanation
- Settings and continue actions
- Gallery counter/progress

### Brand validation
- Logo: Unofficial mark with `BUYER APP`
- Colors/buttons: Generally consistent

### UX validation
The recovery paths are clear; route context should explain the benefit and manual city/address entry must remain possible.

### Native implementation usability
Feasible with platform permission checks, settings linking, and a manual-location fallback.

### Reusable components identified
- PermissionDeniedState
- PrimaryButton
- SecondaryButton
- StatusIllustration

### Dynamic backend data required
- None; permission state is local

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Unofficial logo and forbidden `BUYER APP` label | Use the official wordmark |
| MAJOR | Artifact | `31/46` progress/gallery chrome is embedded | Remove non-product chrome |

### Canonical recommendation
Correct before use; keep the manual fallback in the native design.

---

## 10-location-access-denied-fr-v2.png

Folder: `10-system-states/`  
Screen purpose: Duplicate denied-location state  
Probable native route: `/system/permissions/location`  
Language: French  
Screen type:
- Error state

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
An exact byte-identical copy of `10-location-access-denied-fr.png`.

### Visible UI structure
- Same illustration, copy, and actions

### Brand validation
- Inherits the base file's logo and artifact problems

### UX validation
- Adds no distinct permission behavior

### Native implementation usability
No separate implementation is needed.

### Reusable components identified
- PermissionDeniedState

### Dynamic backend data required
- None

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Duplication | Exact duplicate | Use the base file for this group |

### Canonical recommendation
Keep only as an alternative; duplicate of `10-location-access-denied-fr.png`.

---

## 10-maintenance-completed-ar.png

Folder: `10-system-states/`  
Screen purpose: Arabic confirmation that maintenance has ended  
Probable native route: `/system/maintenance-completed`  
Language: Arabic  
Screen type:
- Success state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An Arabic post-maintenance success page offering a return to the application and a secondary discovery action.

### Visible UI structure
- Transliteration-style Arabic Mayush mark
- Left-side, left-pointing back arrow
- Success illustration and Arabic copy
- Two actions
- `46/46` gallery counter

### Brand validation
- Logo: Rewritten/transliterated rather than preserved
- Colors: Consistent
- Typography: Readable Arabic

### UX validation
The back control is not mirrored for RTL and its left-edge placement follows LTR structure. The secondary phrase `عرض الجديد` is awkward and unrelated to maintenance recovery.

### Native implementation usability
Feasible once `I18nManager`/direction-aware primitives, mirrored navigation, and localized copy are applied.

### Reusable components identified
- RTLSystemStateLayout
- DirectionalBackButton
- PrimaryButton
- StatusIllustration

### Dynamic backend data required
- Maintenance status
- Safe return route

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | RTL | Back arrow placement and direction are not mirrored | Place the control on the right and mirror its directional glyph |
| MAJOR | Branding | Official logo is replaced by an Arabic transliteration | Preserve the exact official Mayush logo |
| MAJOR | Artifact | `46/46` gallery counter is visible | Remove generation/gallery chrome |
| MINOR | Copy | `عرض الجديد` is awkward and not recovery-focused | Use a professionally localized return/resume label |

### Canonical recommendation
Correct RTL navigation, logo, artifacts, and copy before use.

---

## 10-maintenance-completed-fr.png

Folder: `10-system-states/`  
Screen purpose: Confirm maintenance completion  
Probable native route: `/system/maintenance-completed`  
Language: French  
Screen type:
- Success state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A post-maintenance success state with app-return and new-content actions.

### Visible UI structure
- Modified logo
- Success illustration
- Completion copy
- Return and discovery actions

### Brand validation
- Logo: Uses forbidden `BUYER APP`
- Palette and components: Consistent

### UX validation
Returning to the interrupted task should be primary. `Voir les nouveautés` is not directly connected to maintenance recovery.

### Native implementation usability
Simple reusable success state after branding and action correction.

### Reusable components identified
- SystemStateLayout
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Maintenance status
- Interrupted/return route

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Forbidden `BUYER APP` lockup | Use the official logo |
| MINOR | UX | Secondary discovery action is unrelated | Resume the interrupted route or remove the secondary action |

### Canonical recommendation
Correct before use; retain as the preferred French maintenance-completed composition.

---

## 10-maintenance-completed-fr-v2.png

Folder: `10-system-states/`  
Screen purpose: Duplicate French maintenance-completed state  
Probable native route: `/system/maintenance-completed`  
Language: French  
Screen type:
- Success state

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
An exact byte-identical copy of `10-maintenance-completed-fr.png`.

### Visible UI structure
- Same success content and actions

### Brand validation
- Inherits the modified logo problem

### UX validation
- Adds no unique behavior

### Native implementation usability
No separate implementation is required.

### Reusable components identified
- SystemStateLayout

### Dynamic backend data required
- Maintenance status

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Duplication | Exact duplicate | Use the base French file for this group |

### Canonical recommendation
Keep only as an alternative; duplicate of `10-maintenance-completed-fr.png`.

---

## 10-notifications-disabled-fr.png

Folder: `10-system-states/`  
Screen purpose: Named as notifications-disabled but visibly depicts offline mode  
Probable native route: `/system/offline` based on visible content  
Language: French  
Screen type:
- Error state

### Status
REJECTED

### Confidence
High

### What the screen represents
The artwork and copy indicate no internet connection, despite the notification-permission filename.

### Visible UI structure
- Modified logo
- Offline illustration and copy
- Retry/offline actions
- `9/46` gallery counter

### Brand validation
- Logo: Forbidden `BUYER APP`
- Palette: Broadly consistent

### UX validation
The offline concept is usable, but the file-to-route contradiction makes it unsafe as implementation guidance.

### Native implementation usability
The visible offline state is feasible; it is not a notification-permission reference.

### Reusable components identified
- OfflineState
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Connectivity status
- Cached-content availability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Route integrity | Filename says notifications disabled while visible UI is offline | Reject the mapping and later create separate route-accurate references |
| MAJOR | Branding | Forbidden `BUYER APP` logo | Use the official logo |
| MAJOR | Artifact | `9/46` gallery counter is visible | Remove gallery chrome |

### Canonical recommendation
Do not use for notification permissions; the visible offline concept may only be salvaged after relabeling in a future design correction mission.

---

## 10-offline-ar.png

Folder: `10-system-states/`  
Screen purpose: Arabic offline recovery state  
Probable native route: `/system/offline`  
Language: Arabic  
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An Arabic no-connection page with retry and cached/offline continuation options.

### Visible UI structure
- Invented `M` logo plus `BUYER APP`
- Offline illustration and Arabic copy
- Primary and secondary actions
- `42 من 46` gallery progress and pagination dots

### Brand validation
- Logo: Unofficial and contains forbidden wording
- Palette: Consistent
- Arabic typography: Readable

### UX validation
The recovery actions are logical. Offline continuation must only be offered when useful cached content exists.

### Native implementation usability
Feasible using connectivity monitoring, cache capability checks, and direction-aware layout.

### Reusable components identified
- OfflineState
- RTLButtonGroup
- ConnectivityBanner

### Dynamic backend data required
- Connectivity status
- Cached-content availability and freshness

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Unofficial `M`/`BUYER APP` lockup | Use the official Mayush logo |
| MAJOR | Artifact | Gallery count and pagination dots are embedded | Remove all gallery chrome |

### Canonical recommendation
Correct branding/artifacts and gate offline mode by cache capability; then use as the Arabic offline reference.

---

## 10-offline-ar-v2.png

Folder: `10-system-states/`  
Screen purpose: Duplicate Arabic offline state  
Probable native route: `/system/offline`  
Language: Arabic  
Screen type:
- Error state

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
An exact byte-identical copy of `10-offline-ar.png`.

### Visible UI structure
- Same offline content and gallery chrome

### Brand validation
- Inherits the base file's logo defects

### UX validation
- Adds no distinct state

### Native implementation usability
No separate implementation is needed.

### Reusable components identified
- OfflineState

### Dynamic backend data required
- Connectivity and cache availability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Duplication | Byte-identical duplicate | Use the base Arabic file for this group |

### Canonical recommendation
Keep only as an alternative; duplicate of `10-offline-ar.png`.

---

## 10-offline-cached-content-fr.png

Folder: `10-system-states/`  
Screen purpose: Named as cached offline content but visibly depicts disabled notifications  
Probable native route: `/system/permissions/notifications` based on visible content  
Language: French  
Screen type:
- Error state

### Status
REJECTED

### Confidence
High

### What the screen represents
The image explains that notifications are disabled and offers settings access, contradicting its offline-cached-content filename.

### Visible UI structure
- Modified logo
- Notification illustration and copy
- Settings and continue actions

### Brand validation
- Logo: Uses forbidden `BUYER APP`
- Components: Otherwise consistent

### UX validation
The visible notification-permission fallback is reasonable, but it cannot guide cached offline content.

### Native implementation usability
The visible permission flow is feasible using platform settings; the named route is invalid.

### Reusable components identified
- PermissionDeniedState
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- None; permission status is local

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Route integrity | Filename and visible notification content describe different states | Do not use until separate route-accurate references exist |
| MAJOR | Branding | Forbidden `BUYER APP` logo treatment | Use the official logo |

### Canonical recommendation
Do not use as cached-offline guidance; the visible notification concept can only be salvaged after a future asset correction.

---

## 10-biometric-unavailable-fr.png

Folder: `10-system-states/`  
Screen purpose: Explain that biometric authentication is unavailable  
Probable native route: `/system/biometric-unavailable`  
Language: French  
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A recoverable authentication state that sends the buyer to device settings or password login.

### Visible UI structure
- Modified logo/header
- Biometric warning illustration
- Explanatory copy
- Settings and password actions
- Gallery counter

### Brand validation
- Logo: Invented lowercase mark plus forbidden `BUYER APP`
- Colors/components: Otherwise close to the cream, orange, and navy system
- Typography/icons: Generally coherent

### UX validation
The recovery choices are understandable, but the gallery counter is not application UI and device-settings deep-link failure needs a fallback.

### Native implementation usability
Straightforward with a reusable system-state template and native settings-link handling.

### Reusable components identified
- SystemStateLayout
- PrimaryButton
- SecondaryButton
- StatusIllustration

### Dynamic backend data required
- None; biometric capability is device state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Unofficial logo and forbidden `BUYER APP` label | Use the official Mayush logo only |
| MAJOR | Artifact | `33/46` gallery counter is embedded in the screen | Remove all generation/gallery chrome |

### Canonical recommendation
Correct before use; it is a workable biometric fallback reference after cleanup.

---

## 10-biometric-unavailable-fr-v2.png

Folder: `10-system-states/`  
Screen purpose: Duplicate biometric-unavailable state  
Probable native route: `/system/biometric-unavailable`  
Language: French  
Screen type:
- Error state

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
An exact byte-identical alternative to `10-biometric-unavailable-fr.png`.

### Visible UI structure
- Same header, illustration, copy, and actions as the base file

### Brand validation
- Inherits the unofficial logo and `BUYER APP` problem

### UX validation
- Adds no distinct state or useful behavior

### Native implementation usability
No separate implementation is required.

### Reusable components identified
- SystemStateLayout
- PrimaryButton

### Dynamic backend data required
- None

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Duplication | Exact duplicate creates conflicting reference inventory | Keep the base file as the group reference |

### Canonical recommendation
Keep only as a historical alternative; duplicate of `10-biometric-unavailable-fr.png`.

---

## 10-cache-clearing-progress-fr.png

Folder: `10-system-states/`  
Screen purpose: Named as cache-clearing progress but depicts an expired session  
Probable native route: `/auth/session-expired` based on visible content  
Language: French  
Screen type:
- Error state

### Status
REJECTED

### Confidence
High

### What the screen represents
The filename and visible screen are different routes: the image tells the buyer the session has expired and offers reconnection.

### Visible UI structure
- Modified logo
- Session-expired illustration and copy
- `Se reconnecter` action

### Brand validation
- Logo: Forbidden `BUYER APP` lockup
- Palette/layout: Broadly consistent

### UX validation
The visible session recovery is logical, but it cannot validate cache-clearing behavior and would be dangerously miswired if implemented from the filename.

### Native implementation usability
The visible session state is implementable, but the asset-to-route contract is invalid.

### Reusable components identified
- SystemStateLayout
- PrimaryButton
- StatusIllustration

### Dynamic backend data required
- Session validity
- Post-login return route

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Route integrity | Filename says cache clearing while the visible UI says session expired | Do not use; create route-accurate references during a later design correction mission |
| MAJOR | Branding | Forbidden `BUYER APP` logo treatment | Use the official logo only |

### Canonical recommendation
Do not use as a cache-clearing or session-expiry canonical reference.

---

## 10-camera-access-denied-fr.png

Folder: `10-system-states/`  
Screen purpose: Recover from denied camera permission  
Probable native route: `/system/permissions/camera`  
Language: French  
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A camera-permission explanation with a path to system settings and a cancel action.

### Visible UI structure
- Modified logo
- Camera illustration
- Permission explanation
- Open-settings primary action
- Cancel action

### Brand validation
- Logo: Uses forbidden `BUYER APP` wording
- Colors, buttons, and spacing: Consistent with the preferred system

### UX validation
The goal and actions are clear. The implementation must handle unavailable deep links and explain which buyer feature requested camera access.

### Native implementation usability
Feasible with platform permission APIs, `Linking.openSettings`, and a reusable permission-state component.

### Reusable components identified
- PermissionDeniedState
- PrimaryButton
- TextButton
- StatusIllustration

### Dynamic backend data required
- None; permission status is device state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Modified logo contains forbidden wording | Replace with the official logo |

### Canonical recommendation
Correct branding before use; retain as the French camera-permission reference.

---

## 10-connection-restored-fr.png

Folder: `10-system-states/`  
Screen purpose: Confirm restored network connection  
Probable native route: Transient global connectivity state  
Language: French  
Screen type:
- Success state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A full-page success state after the device reconnects.

### Visible UI structure
- Modified logo
- Success illustration
- Restored-connection message
- Continue and `Voir les nouveautés` actions

### Brand validation
- Logo: Uses forbidden `BUYER APP`
- Palette and components: Consistent

### UX validation
Restored connectivity normally warrants a banner/toast or automatic resume. `Voir les nouveautés` is unrelated to connectivity recovery.

### Native implementation usability
Feasible, but the state should normally be represented by a global connection banner and retry orchestration.

### Reusable components identified
- ConnectivityBanner
- SystemStateLayout
- PrimaryButton

### Dynamic backend data required
- Connectivity status
- Failed request or route to resume

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Forbidden `BUYER APP` lockup | Use the official logo |
| MINOR | UX | `Voir les nouveautés` is unrelated to restored connectivity | Resume the interrupted task or dismiss automatically |

### Canonical recommendation
Correct before use; prefer its message as a transient connectivity component, not a mandatory page.

---

## 10-connection-restored-fr-v2.png

Folder: `10-system-states/`  
Screen purpose: Duplicate restored-connection state  
Probable native route: Transient global connectivity state  
Language: French  
Screen type:
- Success state

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
An exact byte-identical copy of `10-connection-restored-fr.png`.

### Visible UI structure
- Same success message and actions as the base file

### Brand validation
- Inherits the base file's modified logo

### UX validation
- Adds no unique state

### Native implementation usability
No separate implementation is required.

### Reusable components identified
- ConnectivityBanner
- SystemStateLayout

### Dynamic backend data required
- Connectivity status

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Duplication | Exact duplicate adds no implementation guidance | Use the base file for the group |

### Canonical recommendation
Keep only as an alternative; duplicate of `10-connection-restored-fr.png`.

---

## 10-connection-timeout-fr.png

Folder: `10-system-states/`  
Screen purpose: Explain a request timeout and offer recovery  
Probable native route: Contextual request error or `/system/timeout`  
Language: French  
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A timeout state with retry and offline-mode alternatives.

### Visible UI structure
- Modified logo
- Timeout illustration
- Explanatory message
- Retry primary action
- Offline-mode secondary action

### Brand validation
- Logo: Forbidden `BUYER APP` treatment
- Palette, spacing, and actions: Consistent

### UX validation
The recovery hierarchy is logical. Offline mode must only appear where cached data and safe offline behavior actually exist.

### Native implementation usability
Feasible with a reusable request-error component and contextual retry callback.

### Reusable components identified
- RequestErrorState
- PrimaryButton
- SecondaryButton
- StatusIllustration

### Dynamic backend data required
- Failed request context
- Cache/offline availability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Modified logo includes forbidden wording | Replace with the official logo |

### Canonical recommendation
Correct branding before use; product must gate the offline option by actual capability.

---

## 10-connection-timeout-fr-v2.png

Folder: `10-system-states/`  
Screen purpose: Duplicate request-timeout state  
Probable native route: Contextual request error or `/system/timeout`  
Language: French  
Screen type:
- Error state

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
An exact byte-identical copy of `10-connection-timeout-fr.png`.

### Visible UI structure
- Same illustration, copy, and actions as the base file

### Brand validation
- Inherits the modified logo problem

### UX validation
- No distinct UX contribution

### Native implementation usability
No separate component or route is needed.

### Reusable components identified
- RequestErrorState
- PrimaryButton

### Dynamic backend data required
- Failed request context

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Duplication | Byte-identical duplicate | Retain the base file for the duplicate group |

### Canonical recommendation
Keep only as an alternative; duplicate of `10-connection-timeout-fr.png`.

---

## 10-content-loading-ar.png

Folder: `10-system-states/`  
Screen purpose: Arabic content-loading state  
Probable native route: Contextual loading state  
Language: Arabic  
Screen type:
- Loading state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A generic Arabic loading page with an animated spinner and reassurance copy.

### Visible UI structure
- Shopping-bag substitute logo with `BUYER APP`
- Loading illustration/spinner
- Arabic status copy
- Gallery counter

### Brand validation
- Logo: Not the official Mayush wordmark and contains forbidden English wording
- Colors: Close to the approved palette
- Typography: Arabic is readable, but the Latin submark is inconsistent

### UX validation
The generic message is understandable, but a full-page loader should preserve stable navigation/chrome when loading route content.

### Native implementation usability
Feasible as a reusable loading state if the logo/artifact chrome is removed and the container adapts to route context.

### Reusable components identified
- LoadingState
- ActivityIndicator
- ArabicText

### Dynamic backend data required
- Loading status
- Optional progress or operation label

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Shopping-bag mark and `BUYER APP` replace the official logo | Use the official logo or no logo for contextual loaders |
| MAJOR | Artifact | `41/46` gallery counter is visible | Remove gallery-generation chrome |

### Canonical recommendation
Correct before use; treat as an Arabic loading component, not a universal page poster.

---

## 10-content-loading-ar-v2.png

Folder: `10-system-states/`  
Screen purpose: Duplicate Arabic loading state  
Probable native route: Contextual loading state  
Language: Arabic  
Screen type:
- Loading state

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
An exact byte-identical copy of `10-content-loading-ar.png`.

### Visible UI structure
- Same logo substitute, loader, copy, and counter

### Brand validation
- Inherits the base file's branding defects

### UX validation
- Provides no unique state

### Native implementation usability
No separate implementation is required.

### Reusable components identified
- LoadingState
- ArabicText

### Dynamic backend data required
- Loading status

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Duplication | Exact duplicate | Use the base Arabic loading file for this group |

### Canonical recommendation
Keep only as an alternative; duplicate of `10-content-loading-ar.png`.

---

## 10-content-loading-skeleton-fr.png

Folder: `10-system-states/`  
Screen purpose: Generic skeleton-loading composition  
Probable native route: Reusable within buyer content routes  
Language: French/neutral  
Screen type:
- Loading state

### Status
REFERENCE_ONLY

### Confidence
High

### What the screen represents
A generic page skeleton demonstrating placeholder rows, text, cards, and a large media block.

### Visible UI structure
- Official logo header
- Search/header action
- Multiple skeleton placeholder patterns
- No bottom navigation

### Brand validation
- Logo and palette: Consistent
- Skeleton radii and surfaces: Reusable
- Layout: Generic rather than tied to a real route

### UX validation
Skeletons should mirror each destination layout and preserve stable navigation. One universal full-page composition could increase perceived layout shift.

### Native implementation usability
Individual placeholder primitives are realistic in React Native; the complete poster should not be copied as one route.

### Reusable components identified
- SkeletonBlock
- SkeletonText
- SkeletonAvatar
- SkeletonCard

### Dynamic backend data required
- Loading state only

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Native UX | Generic skeleton does not match a specific destination layout or retain stable bottom navigation | Recompose the primitives per real route and preserve app chrome |

### Canonical recommendation
Use only as a skeleton-component board; do not implement it as a standalone buyer route.

---

## 10-access-denied-403-fr.png

Folder: `10-system-states/`  
Screen purpose: Visible unusual-activity verification state; does not match the access-denied filename  
Probable native route: `Security/UnusualActivity` (not `System/AccessDenied`)  
Language: FR  
Screen type:
- Error state

### Status
REJECTED

### Confidence
High

### What the screen represents
A security challenge asking the buyer to verify an account after unusual activity. It cannot represent a 403 access-denied route.

### Visible UI structure
- Header with back arrow, altered Mayush wordmark and `37 / 46`
- Security illustration and warning copy
- Verification information card
- Primary `Vérifier mon compte` action and secondary `Fermer`

### Brand validation
- Logo: Incorrect text-only `Mayush Buyer App` treatment
- Colors/typography: Broadly compatible, but not the canonical logo/type hierarchy
- Icons/cards/buttons: Reusable styles; illustration is much denser than the official state set
- Spacing/shadows: Native-feasible, with excessive top chrome

### UX validation
- The visible goal is clear for unusual activity
- Filename/route mismatch is security-critical and would map the wrong recovery behavior
- The generator counter is not valid app UI
- Buttons are large enough; safe-area spacing is adequate

### Native implementation usability
The underlying verification layout is implementable, but the image must not be mapped to 403 handling. Security challenge state and access denial require different routing and analytics.

### Reusable components identified
- SystemStateScreen
- SecurityStateIllustration
- InfoCard
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Security challenge reason
- Verification method availability
- Account protection status

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Route / security logic | Filename says access denied 403 while visible content is unusual-activity verification | Do not use for 403; create separate correctly mapped access-denied and unusual-activity states |
| MAJOR | Branding | Official logo is replaced and `Buyer App` is added | Use the exact official header logo without extra wording |
| MAJOR | Production readiness | `37 / 46` generator progress is embedded in the header | Remove all generation/pagination artifacts |

### Canonical recommendation
Do not use for the named route. Keep only as a historical unusual-activity concept after separating the route and correcting branding.

---

## 10-account-blocked-fr.png

Folder: `10-system-states/`  
Screen purpose: Visible 403 access-refused state; does not show an account-blocked recovery flow  
Probable native route: `System/AccessDenied`  
Language: FR  
Screen type:
- Error state

### Status
REJECTED

### Confidence
High

### What the screen represents
A generic 403 page with `Retour` and `Compte`, not a blocked-account state.

### Visible UI structure
- Status bar and full logo lockup
- Shield/403 illustration
- Access-refused heading and explanatory card
- `Retour` and `Compte` actions

### Brand validation
- Logo: Official mark modified with forbidden `BUYER APP`
- Colors/typography: Cream/orange/navy are consistent
- Icons/buttons/cards: Coherent but generic
- Spacing/shadows: Usable on a phone

### UX validation
- Goal is clear for page-level access denial
- No explanation, support route, identity verification or appeal path for a blocked account
- `Compte` is vague as a recovery action

### Native implementation usability
Implementable as a 403 state with native components, but unsafe for account-blocked behavior.

### Reusable components identified
- SystemStateScreen
- ErrorIllustration
- InfoCard
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Authorization reason
- Allowed fallback route

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Route / account logic | Filename promises account-blocked behavior while visible screen is a generic 403 | Do not map this image to account-blocked; define the real block reason and recovery flow |
| MAJOR | Branding | `BUYER APP` is added to the official logo | Use the official logo unchanged |
| MAJOR | Recovery UX | A blocked buyer has no support, verification or appeal action | Add product-approved recovery/support behavior with reason-safe copy |

### Canonical recommendation
Reject for account blocking. The 403 illustration may inform a separately named access-denied component after brand cleanup.

---

## 10-account-other-device-fr.png

Folder: `10-system-states/`  
Screen purpose: Account detected on another device  
Probable native route: `Security/OtherDeviceChallenge`  
Language: FR  
Screen type:
- Dialog-like full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A security checkpoint asking the buyer to verify a sign-in or disconnect after another-device activity.

### Visible UI structure
- Invented `M` wordmark with `Mayush Buyer App` and `36 / 46`
- Heading and explanatory copy
- Phone/laptop warning illustration
- `Vérifier maintenant` and `Se déconnecter` actions

### Brand validation
- Logo: Incorrect invented symbol and forbidden wording
- Colors/typography: Broadly compatible
- Buttons: Clear hierarchy and accessible size
- Spacing: Spacious; safe-area compliant

### UX validation
- Verification goal is understandable
- `Se déconnecter` does not state whether it signs out this device, the other device, or all sessions
- No device metadata is shown to help the buyer make a security decision

### Native implementation usability
The state can be built with a shared security-state template. Action semantics and device data must be product-defined before implementation.

### Reusable components identified
- SecurityStateScreen
- DeviceIllustration
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Device name and platform
- Approximate location
- Activity timestamp
- Session identifier and permitted revoke action

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Invented logo and `Buyer App` wording | Replace with exact official logo |
| MAJOR | Security action | `Se déconnecter` has ambiguous scope | Label the exact session action, such as `Déconnecter l’autre appareil` |
| MAJOR | Production readiness | `36 / 46` is a generator artifact | Remove it |

### Canonical recommendation
Correct before use; retain the illustration and two-action hierarchy only.

---

## 10-account-sync-fr.png

Folder: `10-system-states/`  
Screen purpose: Account data synchronization  
Probable native route: `System/AccountSync`  
Language: FR  
Screen type:
- Loading state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A full-page sync showing completed orders/favorites and preferences still in progress.

### Visible UI structure
- Logo with forbidden `BUYER APP`
- Sync illustration
- Heading and explanation
- Three progress rows with success/spinner states

### Brand validation
- Logo: Official mark modified with extra wording
- Colors/typography: Consistent cream/orange/navy and Playfair-style heading
- Icons/cards/shadows: Coherent and reusable
- Spacing: Comfortable

### UX validation
- Progress is understandable
- No failure, retry or cancel path is represented
- Full-screen blocking is acceptable only for mandatory first-run merge/restore, not routine sync

### Native implementation usability
Implement with a state-driven list and accessible progress announcements. Product must decide whether this sync is blocking.

### Reusable components identified
- SyncStateScreen
- SyncProgressRow
- StatusIcon
- LoadingIndicator

### Dynamic backend data required
- Sync categories
- Per-category status
- Error/retry state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | `BUYER APP` modifies the official logo | Use exact official logo |
| MINOR | Recovery UX | Failure/retry behavior is absent | Define per-row failure and global retry states |

### Canonical recommendation
Correct before use; this is the canonical account-sync composition within this folder.

---

## 10-account-sync-fr-v2.png

Folder: `10-system-states/`  
Screen purpose: Byte-identical alternative of account synchronization  
Probable native route: `System/AccountSync`  
Language: FR  
Screen type:
- Loading state

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
An exact byte-for-byte copy of `10-account-sync-fr.png`.

### Visible UI structure
- Same logo, illustration and sync rows as the canonical candidate

### Brand validation
- Same forbidden `BUYER APP` issue as the base file

### UX validation
- No UX difference from the base file

### Native implementation usability
Adds no implementation information.

### Reusable components identified
- Same as `10-account-sync-fr.png`

### Dynamic backend data required
- Same as `10-account-sync-fr.png`

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Inherits the forbidden logo lockup | Correct only in the chosen canonical design |
| MINOR | Duplication | Exact duplicate adds no state or layout information | Use the base file as the single candidate |

### Canonical recommendation
Keep only as a duplicate alternative of `10-account-sync-fr.png`.

---

## 10-account-sync-progress-fr.png

Folder: `10-system-states/`  
Screen purpose: Visible offline cached-content state; does not match account-sync-progress filename  
Probable native route: `System/OfflineCachedContent`  
Language: FR  
Screen type:
- Error state

### Status
REJECTED

### Confidence
High

### What the screen represents
An offline-mode page listing locally available orders, favorites and addresses.

### Visible UI structure
- Logo with `BUYER APP` and notification icon
- Offline illustration and last-update timestamp
- Cached-content list
- Retry button
- Generator previous/next arrows with `10 / 46`

### Brand validation
- Logo: Modified with forbidden wording
- Colors/components: Compatible
- Navigation: Generator controls imitate production navigation

### UX validation
- Visible offline goal is logical
- Route/filename mismatch makes implementation mapping unreliable
- Cached content availability is plausible but must be confirmed by product/security

### Native implementation usability
The cached-content concept is implementable, but it requires an offline data contract and must not be treated as account-sync progress.

### Reusable components identified
- OfflineStateScreen
- CachedContentRow
- TimestampLabel
- RetryButton

### Dynamic backend data required
- Last successful sync time
- Locally cached sections and availability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Route / state mapping | Filename says account sync progress while content is offline cached mode | Do not use for the named route; define separate files/routes in implementation documentation |
| MAJOR | Branding | `BUYER APP` is added | Use official logo |
| MAJOR | Production readiness | `10 / 46` and previous/next arrows are generator UI | Remove entirely |

### Canonical recommendation
Reject for account sync. Keep only as a noncanonical offline-cache idea pending a product decision on offline data.

---

## 10-app-initialization-fr.png

Folder: `10-system-states/`  
Screen purpose: Application initialization  
Probable native route: `System/AppInitialization`  
Language: FR  
Screen type:
- Loading state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A blocking first-run/app-start initialization progress view.

### Visible UI structure
- Logo with `BUYER APP`
- Initialization illustration
- Heading/status copy
- Progress bar
- `2 / 46` and three pagination dots

### Brand validation
- Logo: Modified with forbidden wording
- Colors/illustration: Consistent with cream/orange/navy system
- Typography: Coherent
- Spacing: Generous

### UX validation
- Goal is understandable
- `2 / 46` reads like generated-deck pagination, not meaningful initialization progress
- No timeout or recovery state is shown

### Native implementation usability
Implementable as a startup state, but progress must reflect real work or use an indeterminate indicator; fake step counts are not allowed.

### Reusable components identified
- BrandedLoadingScreen
- ProgressBar
- StatusText

### Dynamic backend data required
- Initialization status
- Optional real progress percentage
- Failure/retry state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | `BUYER APP` appears below the official logo | Remove the extra wording |
| MAJOR | Loading UX | `2 / 46` and pagination dots are invented generator progress | Use real progress or an indeterminate loader with timeout recovery |

### Canonical recommendation
Correct before use; the entry loading screen remains the cleaner general loading reference.

---

## 10-app-initialization-fr-v2.png

Folder: `10-system-states/`  
Screen purpose: Byte-identical alternative of initialization  
Probable native route: `System/AppInitialization`  
Language: FR  
Screen type:
- Loading state

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
An exact copy of `10-app-initialization-fr.png`.

### Visible UI structure
- Same logo, illustration, progress and generator pagination

### Brand validation
- Same branding defects as the base file

### UX validation
- No distinct UX information

### Native implementation usability
No additional value over the base file.

### Reusable components identified
- Same as the base file

### Dynamic backend data required
- Same as the base file

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding / loading | Inherits the base file's logo and fake-progress defects | Fix only the selected canonical candidate |
| MINOR | Duplication | Byte-identical copy | Use `10-app-initialization-fr.png` |

### Canonical recommendation
Duplicate alternative of `10-app-initialization-fr.png`.

---

## 10-app-up-to-date-fr.png

Folder: `10-system-states/`  
Screen purpose: Visible slow-connection state; does not match app-up-to-date filename  
Probable native route: `System/SlowConnection`  
Language: FR  
Screen type:
- Error state

### Status
REJECTED

### Confidence
High

### What the screen represents
A network degradation message with retry and wait actions.

### Visible UI structure
- Logo with `BUYER APP`
- Slow Wi-Fi illustration
- `Connexion lente` heading and network-instability card
- `Réessayer` and `Continuer à attendre`

### Brand validation
- Logo: Modified with forbidden wording
- Colors/components: Otherwise consistent

### UX validation
- Slow-network actions are understandable
- Filename/route contradiction makes it unsafe as an app-version reference

### Native implementation usability
Implementable for slow network with a retryable request state, but not usable for version checking.

### Reusable components identified
- NetworkStateScreen
- InfoCard
- RetryButton
- SecondaryButton

### Dynamic backend data required
- Failed/slow request context
- Retry state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Route / state mapping | App-up-to-date filename contains slow-connection UI | Reject for the named route and document a real version-current state separately |
| MAJOR | Branding | Forbidden `BUYER APP` lockup | Use official logo |

### Canonical recommendation
Reject for app-up-to-date. Useful only as a slow-connection concept after brand cleanup.

---

## 10-background-sync-fr.png

Folder: `10-system-states/`  
Screen purpose: Background synchronization  
Probable native route: `System/BackgroundSyncStatus`  
Language: FR  
Screen type:
- Loading state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A detailed sync monitor claiming the buyer may keep using the app while order, cancellation and refund data synchronize.

### Visible UI structure
- Logo with `BUYER APP`
- Sync illustration and heading
- Overall progress panel
- Three per-domain progress rows

### Brand validation
- Logo: Modified with forbidden wording
- Colors/cards/icons: Consistent and reusable
- Typography: Coherent but dense

### UX validation
- Information is understandable
- A blocking full page with no navigation contradicts `Vous pouvez continuer à utiliser l’application`
- Routine background work should use a nonblocking banner/toast or settings detail

### Native implementation usability
Per-row sync UI is feasible, but this should be a status detail screen or unobtrusive banner rather than a forced foreground route.

### Reusable components identified
- SyncStatusCard
- SyncProgressRow
- StatusBadge
- InlineProgressBar

### Dynamic backend data required
- Sync queue categories
- Per-category progress and failures
- Last sync timestamp

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Forbidden `BUYER APP` lockup | Use official logo |
| MAJOR | Interaction model | Screen says the app remains usable but removes all navigation/actions | Present as nonblocking feedback or add a clear continue/dismiss path |

### Canonical recommendation
Correct the interaction model before use; retain the row/card patterns as component references.

---

## 10-offline-fr.png

Folder: `10-system-states/`  
Screen purpose: Named as offline but visibly confirms the app is up to date  
Probable native route: `/system/app-up-to-date` based on visible content  
Language: French  
Screen type:
- Success state

### Status
REJECTED

### Confidence
High

### What the screen represents
The visible page confirms the installed application is current, directly contradicting its offline filename.

### Visible UI structure
- Modified logo
- Update success illustration
- Version/current-state copy
- Continue action

### Brand validation
- Logo: Forbidden `BUYER APP`
- Palette/components: Generally consistent

### UX validation
The visible success state is clear, but it cannot guide offline behavior and is unsafe to map by filename.

### Native implementation usability
The visible update-confirmation component is feasible; the route contract is not.

### Reusable components identified
- SystemStateLayout
- PrimaryButton
- StatusIllustration

### Dynamic backend data required
- Installed version
- Required/latest version

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Route integrity | Offline filename contains an app-up-to-date success state | Do not implement from this asset mapping |
| MAJOR | Branding | Forbidden `BUYER APP` lockup | Use the official logo |

### Canonical recommendation
Reject for both routes; later create correctly named and branded offline and up-to-date references.

---

## 10-page-not-found-fr.png

Folder: `10-system-states/`  
Screen purpose: Handle a missing or invalid buyer route  
Probable native route: Navigation fallback / `*`  
Language: French  
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A 404-style navigation fallback with a clear return-home action.

### Visible UI structure
- Modified logo
- Missing-page illustration and copy
- Return-home action

### Brand validation
- Logo: Uses forbidden `BUYER APP`
- Colors/layout/button: Consistent

### UX validation
The recovery path is clear. Native copy should avoid web-centric assumptions and route back safely without losing authenticated state.

### Native implementation usability
Suitable for a navigation fallback component after branding correction.

### Reusable components identified
- NotFoundState
- PrimaryButton
- StatusIllustration

### Dynamic backend data required
- Safe fallback route

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Modified logo contains forbidden wording | Use the official Mayush logo |

### Canonical recommendation
Correct branding before use; retain as the French missing-route reference.

---

## 10-password-changed-fr.png

Folder: `10-system-states/`  
Screen purpose: Confirm a successful password change  
Probable native route: `/account/security/password/success`  
Language: French  
Screen type:
- Success state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A security success page confirming password replacement and offering continued app use.

### Visible UI structure
- Random green `M`/`BUYER APP` mark
- Green success illustration
- Confirmation copy and action
- `35/46` gallery counter

### Brand validation
- Logo: Not official and includes forbidden wording
- Color: Green dominates beyond a small semantic success accent
- Typography/layout: Otherwise clear

### UX validation
The outcome and next action are understandable. Product must decide whether other sessions are revoked and communicate that behavior.

### Native implementation usability
Feasible as a reusable account-security success state.

### Reusable components identified
- SecuritySuccessState
- PrimaryButton
- StatusIllustration

### Dynamic backend data required
- Password-change result
- Session-revocation result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Unofficial green logo and forbidden `BUYER APP` text | Restore the official logo and brand palette; reserve green for the success indicator |
| MAJOR | Artifact | `35/46` gallery counter is visible | Remove generation/gallery chrome |

### Canonical recommendation
Correct logo, color balance, and artifacts; NEEDS PRODUCT DECISION on other-session behavior.

---

## 10-photos-access-denied-fr.png

Folder: `10-system-states/`  
Screen purpose: Recover from denied photo-library permission  
Probable native route: `/system/permissions/photos`  
Language: French  
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A photo-library permission explanation with settings and cancellation paths.

### Visible UI structure
- Invented logo
- Photo permission illustration/copy
- Settings and cancel actions
- `32/46` progress indicator

### Brand validation
- Logo: Unofficial with forbidden `BUYER APP`
- Components/colors: Broadly consistent

### UX validation
The action hierarchy is clear. Copy should state the feature requiring access and support modern limited-photo access where available.

### Native implementation usability
Feasible with platform-specific media permission handling and settings fallback.

### Reusable components identified
- PermissionDeniedState
- PrimaryButton
- TextButton
- StatusIllustration

### Dynamic backend data required
- None; permission state is local

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Unofficial logo and forbidden wording | Use the official wordmark |
| MAJOR | Artifact | Gallery progress is embedded in application UI | Remove `32/46` and its progress treatment |

### Canonical recommendation
Correct before use and account for iOS limited-library permission behavior.

---

## 10-photos-access-denied-fr-v2.png

Folder: `10-system-states/`  
Screen purpose: Duplicate denied-photo-access state  
Probable native route: `/system/permissions/photos`  
Language: French  
Screen type:
- Error state

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
An exact byte-identical copy of `10-photos-access-denied-fr.png`.

### Visible UI structure
- Same permission copy, actions, and artifact chrome

### Brand validation
- Inherits the base file's logo defects

### UX validation
- No distinct behavior

### Native implementation usability
No separate implementation is required.

### Reusable components identified
- PermissionDeniedState

### Dynamic backend data required
- None

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Duplication | Exact duplicate | Use the base photo-permission file |

### Canonical recommendation
Keep only as an alternative; duplicate of `10-photos-access-denied-fr.png`.

---

## 10-reconnection-progress-fr.png

Folder: `10-system-states/`  
Screen purpose: Show a network reconnection attempt  
Probable native route: Transient global connectivity state  
Language: French  
Screen type:
- Loading state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A blocking reconnection page with animated progress and cancellation.

### Visible UI structure
- Modified logo
- Reconnection illustration/spinner
- Status copy
- Cancel action

### Brand validation
- Logo: Uses forbidden `BUYER APP`
- Palette and spacing: Consistent

### UX validation
The state is understandable, but routine reconnect attempts should normally be nonblocking and resume the failed action automatically.

### Native implementation usability
Feasible as a connectivity overlay/banner; cancellation semantics need to be explicit.

### Reusable components identified
- ConnectivityOverlay
- ActivityIndicator
- TextButton

### Dynamic backend data required
- Connectivity status
- Retry attempt state
- Interrupted request context

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Forbidden `BUYER APP` lockup | Use the official logo |

### Canonical recommendation
Correct branding and use the concept as a transient overlay/banner unless the entire route genuinely cannot function offline.

---

## 10-reconnection-progress-fr-v2.png

Folder: `10-system-states/`  
Screen purpose: Duplicate network-reconnection progress  
Probable native route: Transient global connectivity state  
Language: French  
Screen type:
- Loading state

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
An exact byte-identical copy of `10-reconnection-progress-fr.png`.

### Visible UI structure
- Same reconnect state

### Brand validation
- Inherits the base branding issue

### UX validation
- No unique behavior

### Native implementation usability
No separate implementation is needed.

### Reusable components identified
- ConnectivityOverlay

### Dynamic backend data required
- Connectivity status

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Duplication | Byte-identical duplicate | Use the base file for this state |

### Canonical recommendation
Keep only as an alternative; duplicate of `10-reconnection-progress-fr.png`.

---

## 10-refresh-progress-fr.png

Folder: `10-system-states/`  
Screen purpose: Show manual content refresh progress  
Probable native route: Contextual refresh overlay  
Language: French  
Screen type:
- Loading state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A full-page content refresh state with progress feedback.

### Visible UI structure
- Modified logo
- Refresh illustration/spinner
- Loading copy
- Cancel action

### Brand validation
- Logo: Forbidden `BUYER APP`
- Colors/components: Consistent

### UX validation
Pull-to-refresh or an inline indicator is more native for most buyer lists. A blocking page should be limited to destructive or mandatory refresh operations.

### Native implementation usability
Feasible, but should use `RefreshControl` or route-level skeletons depending on context.

### Reusable components identified
- RefreshControl
- LoadingOverlay
- TextButton

### Dynamic backend data required
- Refresh status
- Failed request context

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Forbidden `BUYER APP` logo | Use the official logo or retain route chrome without a logo |

### Canonical recommendation
Correct branding and adapt into contextual native refresh feedback rather than a universal full page.

---

## 10-scheduled-maintenance-fr.png

Folder: `10-system-states/`  
Screen purpose: Inform the buyer of scheduled maintenance  
Probable native route: `/system/maintenance`  
Language: French  
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A maintenance notice with a specified date/time window and an exit/acknowledgment action.

### Visible UI structure
- Modified logo
- Maintenance illustration
- Date/time card
- Explanatory copy and action

### Brand validation
- Logo: Uses forbidden `BUYER APP`
- Palette/cards: Consistent

### UX validation
The window is clear, but fixed sample time is not implementation truth. Timezone, locale, extensions, and retry-at-end behavior must be defined.

### Native implementation usability
Feasible with remote configuration or maintenance API data and locale-aware formatting.

### Reusable components identified
- MaintenanceState
- DateTimeCard
- PrimaryButton

### Dynamic backend data required
- Maintenance start/end
- Timezone
- Status and extension message
- Safe exit/return behavior

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Forbidden `BUYER APP` lockup | Use the official logo |
| MINOR | Data realism | Fixed sample date/time could be mistaken for product behavior | Render backend-controlled, localized times and define timezone |

### Canonical recommendation
Correct branding and connect the schedule to dynamic maintenance data before use.

---

## 10-scheduled-maintenance-fr-v2.png

Folder: `10-system-states/`  
Screen purpose: Duplicate scheduled-maintenance notice  
Probable native route: `/system/maintenance`  
Language: French  
Screen type:
- Error state

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
An exact byte-identical copy of `10-scheduled-maintenance-fr.png`.

### Visible UI structure
- Same maintenance window and action

### Brand validation
- Inherits the base file's logo issue

### UX validation
- No unique behavior

### Native implementation usability
No separate implementation is required.

### Reusable components identified
- MaintenanceState

### Dynamic backend data required
- Maintenance window/status

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Duplication | Exact duplicate | Use the base maintenance file |

### Canonical recommendation
Keep only as an alternative; duplicate of `10-scheduled-maintenance-fr.png`.

---

## 10-server-error-500-fr.png

Folder: `10-system-states/`  
Screen purpose: Recover from a server-side failure  
Probable native route: Contextual HTTP 5xx error boundary  
Language: French  
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A server error page with retry and return-home actions.

### Visible UI structure
- Modified logo
- 500/server illustration and copy
- Retry and home actions

### Brand validation
- Logo: Forbidden `BUYER APP`
- Palette and action hierarchy: Consistent

### UX validation
The recovery choices are clear. Production copy should avoid exposing unnecessary internals and include a support/reference code only when useful.

### Native implementation usability
Feasible as a contextual API error state with retry callback.

### Reusable components identified
- RequestErrorState
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Error class/reference code
- Retry callback
- Safe fallback route

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Modified logo includes forbidden wording | Use the official logo |

### Canonical recommendation
Correct branding before use as the French server-error reference.

---

## 10-server-unavailable-ar.png

Folder: `10-system-states/`  
Screen purpose: Arabic server-unavailable recovery  
Probable native route: `/system/server-unavailable`  
Language: Arabic  
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An Arabic service-unavailable page with retry and later-return options.

### Visible UI structure
- Orange text substitute logo plus `BUYER APP`
- Server illustration and Arabic copy
- Retry/return actions
- `43/46` gallery counter

### Brand validation
- Logo: Rewritten and includes forbidden English wording
- Colors/components: Generally consistent
- Arabic text: Readable

### UX validation
The recovery hierarchy is reasonable. RTL content is mostly ordered correctly, but all directional primitives must be runtime-mirrored.

### Native implementation usability
Feasible as a reusable localized API error state.

### Reusable components identified
- RTLSystemStateLayout
- RequestErrorState
- PrimaryButton

### Dynamic backend data required
- Service availability
- Retry callback
- Optional estimated recovery time

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Unofficial orange wordmark plus `BUYER APP` | Preserve the exact official logo |
| MAJOR | Artifact | `43/46` gallery counter is visible | Remove generation/gallery chrome |

### Canonical recommendation
Correct branding/artifacts before use as the Arabic server-unavailable reference.

---

## 10-server-unavailable-ar-v2.png

Folder: `10-system-states/`  
Screen purpose: Duplicate Arabic server-unavailable state  
Probable native route: `/system/server-unavailable`  
Language: Arabic  
Screen type:
- Error state

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
An exact byte-identical copy of `10-server-unavailable-ar.png`.

### Visible UI structure
- Same server error content and counter

### Brand validation
- Inherits the base file's branding defects

### UX validation
- Adds no unique state

### Native implementation usability
No separate implementation is required.

### Reusable components identified
- RTLSystemStateLayout
- RequestErrorState

### Dynamic backend data required
- Service availability

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Duplication | Exact duplicate | Use the base Arabic server file |

### Canonical recommendation
Keep only as an alternative; duplicate of `10-server-unavailable-ar.png`.

---

## 10-server-unavailable-detailed-fr.png

Folder: `10-system-states/`  
Screen purpose: Detailed service-unavailable recovery  
Probable native route: `/system/server-unavailable`  
Language: French  
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A detailed server-unavailable page with likely causes, retry guidance, and recovery actions.

### Visible UI structure
- Modified logo
- Server illustration
- Explanatory details/checklist
- Retry and return actions

### Brand validation
- Logo: Uses forbidden `BUYER APP`
- Palette/cards/icons: Consistent

### UX validation
This is the clearest French server-unavailable composition, although technical detail should stay concise and retry must preserve context.

### Native implementation usability
Feasible with a shared request-error template and optional expandable diagnostic details.

### Reusable components identified
- RequestErrorState
- TroubleshootingList
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Service availability
- Retry callback
- Optional incident/reference code

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Modified logo contains forbidden wording | Use the official logo |

### Canonical recommendation
Correct branding, then use as the preferred French server-unavailable structure.

---

## 10-server-unavailable-fr.png

Folder: `10-system-states/`  
Screen purpose: Named as server unavailable but visibly depicts local data/cache updating  
Probable native route: `/system/local-data-update` based on visible content  
Language: French  
Screen type:
- Loading state

### Status
REJECTED

### Confidence
High

### What the screen represents
The visible UI describes local data being updated rather than an unavailable server.

### Visible UI structure
- Gold `M`/`BYYER APP`-style mark
- Data-update illustration and copy
- Progress/action treatment
- `39/46` gallery counter

### Brand validation
- Logo: Invented, misspelled/forbidden buyer wording
- Palette: Gold emphasis is inconsistent

### UX validation
The file-to-route mismatch makes the state unsafe. Local cache updates also need clear background/interrupt behavior.

### Native implementation usability
The visible progress concept may be feasible, but it cannot validate a server error route.

### Reusable components identified
- ProgressBar
- SyncStatusCard

### Dynamic backend data required
- Local migration/sync progress
- Failure and retry state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Route integrity | Filename says server unavailable while visible copy describes local data updating | Do not map this asset to either route |
| MAJOR | Branding | Invented logo and forbidden/malformed buyer wording | Use the official Mayush logo |
| MAJOR | Artifact | `39/46` gallery counter is embedded | Remove non-product chrome |

### Canonical recommendation
Reject; use `10-server-unavailable-detailed-fr.png` after correction for the server route.

---

## 10-session-expired-ar.png

Folder: `10-system-states/`  
Screen purpose: Arabic expired-session recovery  
Probable native route: `/auth/session-expired`  
Language: Arabic  
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An Arabic authentication-expiry state asking the buyer to sign in again.

### Visible UI structure
- Invented `M` logo plus `BUYER APP`
- Session illustration and Arabic copy
- Reauthentication action
- `45/46` gallery counter

### Brand validation
- Logo: Unofficial and contains forbidden wording
- Palette and Arabic typography: Generally consistent/readable

### UX validation
The recovery goal is clear. The app should preserve the intended destination and never imply that unsaved checkout/payment operations are safely replayed without verification.

### Native implementation usability
Feasible with a centralized auth guard and post-login redirect state.

### Reusable components identified
- SessionExpiredState
- PrimaryButton
- RTLSystemStateLayout

### Dynamic backend data required
- Session validity
- Intended return route
- Pending-operation safety state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Unofficial `M`/`BUYER APP` lockup | Use the official logo |
| MAJOR | Artifact | `45/46` gallery counter is visible | Remove generation/gallery chrome |

### Canonical recommendation
Correct branding/artifacts; then use as the Arabic session-expiry reference.

---

## 10-session-expired-fr.png

Folder: `10-system-states/`  
Screen purpose: Named as session expired but visibly depicts a temporarily blocked account  
Probable native route: `/auth/temporarily-blocked` based on visible content  
Language: French  
Screen type:
- Error state

### Status
REJECTED

### Confidence
High

### What the screen represents
The visible page reports a temporary account block with a `15:00` countdown rather than an expired session.

### Visible UI structure
- Modified orange logo plus `Buyer App`
- Blocked-account illustration/copy
- Countdown timer
- Recovery/help action
- `38/46` gallery counter

### Brand validation
- Logo: Rewritten and includes forbidden wording
- Components: Otherwise coherent

### UX validation
Temporary lockout is security-sensitive and requires a defined attempt policy, accessible recovery, and server-synchronized timing. It cannot stand in for normal session expiry.

### Native implementation usability
The visible lockout state is feasible, but only with authoritative server timing and account policy.

### Reusable components identified
- AccountLockoutState
- CountdownTimer
- SupportLink

### Dynamic backend data required
- Lockout reason
- Server expiry timestamp
- Recovery eligibility

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Security/route | Filename says expired session while visible state is a timed account lockout | Reject the mapping and define separate auth states |
| MAJOR | Branding | Rewritten logo and forbidden `Buyer App` | Use the official logo |
| MAJOR | Artifact | `38/46` counter is visible | Remove generation/gallery chrome |

### Canonical recommendation
Do not use for session expiry or lockout until product policy and correct asset naming are established.

---

## 10-session-restoration-fr.png

Folder: `10-system-states/`  
Screen purpose: Restore and verify an existing buyer session  
Probable native route: Startup auth restoration overlay  
Language: French  
Screen type:
- Loading state

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
A secure session-restoration progress page shown during startup/auth hydration.

### Visible UI structure
- Official Mayush logo
- Security illustration
- Progress bar/percentage
- Verification steps
- Notification icon

### Brand validation
- Logo, palette, typography, cards, spacing: Consistent with the preferred foundation
- Icons: Mostly coherent

### UX validation
The progress model is clear and appropriately reassuring. `Sécurité vérifiée` conflicts slightly with verification still being in progress, and the notification bell is unrelated during a blocking startup state.

### Native implementation usability
Realistic as a short auth-hydration state with timeout/error fallback; progress should reflect real stages rather than fake percentages.

### Reusable components identified
- SessionRestorationState
- StepProgress
- ProgressBar
- StatusRow

### Dynamic backend data required
- Token/session validation status
- Profile/cart/wishlist hydration status
- Timeout/error condition

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Copy | `Sécurité vérifiée` appears while verification is still in progress | Use a non-final step label until verification completes |
| MINOR | Hierarchy | Notification bell is unrelated to a blocking session-restore flow | Remove the bell and keep the state focused |

### Canonical recommendation
Use as the main session-restoration reference after the two minor corrections.

---

## 10-session-restoration-fr-v2.png

Folder: `10-system-states/`  
Screen purpose: Duplicate session-restoration state  
Probable native route: Startup auth restoration overlay  
Language: French  
Screen type:
- Loading state

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
An exact byte-identical copy of `10-session-restoration-fr.png`.

### Visible UI structure
- Same official logo, progress, and steps

### Brand validation
- Inherits the base file's otherwise strong brand compliance

### UX validation
- Adds no unique state

### Native implementation usability
No separate implementation is required.

### Reusable components identified
- SessionRestorationState
- StepProgress

### Dynamic backend data required
- Session restoration status

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Duplication | Exact duplicate | Use the base session-restoration file |

### Canonical recommendation
Keep only as an alternative; duplicate of `10-session-restoration-fr.png`.

---

## 10-session-restored-fr.png

Folder: `10-system-states/`  
Screen purpose: Confirm successful session restoration  
Probable native route: Startup auth restoration success  
Language: French  
Screen type:
- Success state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A completion state confirming that the buyer session and data have been restored.

### Visible UI structure
- Invented `M` logo plus `BUYER APP`
- Success illustration/copy
- Continue action
- Language picker
- Sample timestamp and `34/46` counter

### Brand validation
- Logo: Unofficial and forbidden
- Palette/components: Mostly consistent

### UX validation
Continuation is clear, but a language picker is unrelated and the success page should usually auto-resume. Static date/time creates false implementation data.

### Native implementation usability
Feasible as a brief confirmation or toast, preferably followed by automatic route restoration.

### Reusable components identified
- SessionRestoredToast
- SuccessState
- PrimaryButton

### Dynamic backend data required
- Restoration result
- Return route
- Actual restoration timestamp if shown

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding/artifact | Unofficial logo, `BUYER APP`, and `34/46` gallery chrome | Use official branding and remove artifact UI |
| MAJOR | UX hierarchy | Language picker is unrelated to session restoration | Remove it and auto-resume or show one focused continue action |
| MINOR | Data realism | Static sample timestamp appears authoritative | Bind to real data or omit it |

### Canonical recommendation
Correct before use; prefer a brief success transition paired with the canonical restoration screen.

---

## 10-splash-screen.png

Folder: `10-system-states/`  
Screen purpose: Duplicate application splash concept  
Probable native route: Native launch screen  
Language: Neutral/English sublabel  
Screen type:
- Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
A splash screen variant duplicating the Entry splash route and adding forbidden `BUYER APP` wording.

### Visible UI structure
- Centered altered logo
- Cream background

### Brand validation
- Palette/layout: Consistent
- Logo: Modified with `BUYER APP`

### UX validation
The static composition is viable, but the Entry splash has the correct official logo and is the stronger source.

### Native implementation usability
Feasible as a native launch asset, but no separate System States route is needed.

### Reusable components identified
- NativeSplashAsset

### Dynamic backend data required
- None

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Adds forbidden `BUYER APP` to the logo | Use `01-entry/01-splash-screen-logo.png` |
| MINOR | Duplication | Duplicates the Entry splash and its own v2 | Maintain one launch-screen reference |

### Canonical recommendation
Replace with `01-entry/01-splash-screen-logo.png`; keep only as a historical alternative.

---

## 10-splash-screen-v2.png

Folder: `10-system-states/`  
Screen purpose: Exact duplicate of the System States splash alternative  
Probable native route: Native launch screen  
Language: Neutral/English sublabel  
Screen type:
- Full page

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
An exact byte-identical copy of `10-splash-screen.png`, also superseded by the Entry splash.

### Visible UI structure
- Same altered logo and cream background

### Brand validation
- Inherits forbidden `BUYER APP` wording

### UX validation
- Adds no distinct launch behavior

### Native implementation usability
No separate implementation is required.

### Reusable components identified
- NativeSplashAsset

### Dynamic backend data required
- None

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Duplication | Exact duplicate and cross-folder route alternative | Use the Entry splash as canonical |

### Canonical recommendation
Keep only as an alternative; duplicate of `10-splash-screen.png`, superseded by `01-entry/01-splash-screen-logo.png`.

---

## 10-too-many-attempts-fr.png

Folder: `10-system-states/`  
Screen purpose: Explain a temporary retry lockout  
Probable native route: `/auth/too-many-attempts`  
Language: French  
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A timed authentication/rate-limit state with a countdown, disabled waiting action, and close control.

### Visible UI structure
- Modified logo
- Warning illustration/copy
- Countdown timer
- Disabled `Patienter` button
- Close action

### Brand validation
- Logo: Uses forbidden `BUYER APP`
- Warning colors/components: Coherent

### UX validation
The state is understandable, but a disabled button labelled `Patienter` adds no action. The design does not show whether retry becomes enabled automatically or how account recovery works.

### Native implementation usability
Feasible with a server-authoritative lockout timestamp and accessible timer announcements.

### Reusable components identified
- RateLimitState
- CountdownTimer
- SupportLink

### Dynamic backend data required
- Server lockout expiry
- Retry eligibility
- Recovery/support option

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Forbidden `BUYER APP` lockup | Use the official logo |
| MINOR | UX | Disabled `Patienter` looks actionable without doing anything | Show passive countdown and enable a real retry action at expiry |

### Canonical recommendation
Correct branding and define the post-countdown/recovery flow before use.

---

## 10-unusual-activity-detected-fr.png

Folder: `10-system-states/`  
Screen purpose: Filename claims unusual-activity detection but file is identical to server-unavailable detail  
Probable native route: `/system/server-unavailable` based on visible content  
Language: French  
Screen type:
- Error state

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
An exact byte-identical copy of `10-server-unavailable-detailed-fr.png`; it contains no unusual-activity warning or identity-verification UX.

### Visible UI structure
- Same server illustration, troubleshooting detail, and retry actions

### Brand validation
- Inherits the server file's modified `BUYER APP` logo

### UX validation
It cannot serve the security-sensitive route named by the filename.

### Native implementation usability
No separate implementation is required; unusual-activity recovery needs its own approved flow.

### Reusable components identified
- RequestErrorState

### Dynamic backend data required
- Service availability for the visible state
- NEEDS PRODUCT DECISION for any unusual-activity recovery

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Security/route | Filename promises an unusual-activity state but image is byte-identical to a server error | Never map this file to a security route |
| MAJOR | Branding | Inherits forbidden `BUYER APP` wording | Use official branding on the server-state canonical |

### Canonical recommendation
Keep only as a duplicate alternative of `10-server-unavailable-detailed-fr.png`; no usable unusual-activity reference exists here.

---

## 10-update-available-fr.png

Folder: `10-system-states/`  
Screen purpose: Offer an optional application update  
Probable native route: `/system/update-available`  
Language: French  
Screen type:
- Dialog

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An optional-update prompt with version information, release timing, update, and later actions.

### Visible UI structure
- Modified logo
- Update illustration
- Version/date details
- Update primary action
- Later secondary action

### Brand validation
- Logo: Uses forbidden `BUYER APP`
- Palette/actions/cards: Consistent

### UX validation
The optional action hierarchy is clear. Store handoff, unsupported version behavior, and re-prompt cadence need definition.

### Native implementation usability
Feasible as an in-app modal that deep-links to the relevant app store.

### Reusable components identified
- UpdatePrompt
- VersionInfo
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Installed/latest version
- Release notes/date
- Store URL
- Deferral policy

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Forbidden `BUYER APP` logo | Use the official logo |
| MINOR | Data realism | Static version/date may be treated as fixed product data | Bind all release details to platform/configuration data |

### Canonical recommendation
Correct branding and dynamic data behavior before use as the optional-update reference.

---

## 10-update-available-fr-v2.png

Folder: `10-system-states/`  
Screen purpose: Duplicate optional-update prompt  
Probable native route: `/system/update-available`  
Language: French  
Screen type:
- Dialog

### Status
DUPLICATE_ALTERNATIVE

### Confidence
High

### What the screen represents
An exact byte-identical copy of `10-update-available-fr.png`.

### Visible UI structure
- Same update details and actions

### Brand validation
- Inherits the base logo issue

### UX validation
- Adds no distinct behavior

### Native implementation usability
No separate implementation is required.

### Reusable components identified
- UpdatePrompt

### Dynamic backend data required
- Version/configuration data

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Duplication | Exact duplicate | Use the base optional-update file |

### Canonical recommendation
Keep only as an alternative; duplicate of `10-update-available-fr.png`.

---

## 10-update-download-progress-fr.png

Folder: `10-system-states/`  
Screen purpose: Show application-update download progress  
Probable native route: Undefined pending update-mechanism decision  
Language: French  
Screen type:
- Loading state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A foreground update-download page with progress, downloaded size, and cancellation.

### Visible UI structure
- Modified logo
- Download illustration
- Progress bar/percentage and size
- Cancel action

### Brand validation
- Logo: Uses forbidden `BUYER APP`
- Progress components: Consistent

### UX validation
The feedback is clear, but it assumes the app directly downloads its own binary. This is not the normal store-managed React Native update model.

### Native implementation usability
Only realistic for an explicitly approved OTA/content package mechanism; otherwise hand off to the app store. NEEDS PRODUCT DECISION.

### Reusable components identified
- DownloadProgress
- ProgressBar
- TextButton

### Dynamic backend data required
- NEEDS PRODUCT DECISION: update mechanism
- Download size/progress only if applicable

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Native behavior | Implies an in-app binary download without a defined platform mechanism | Define store/OTA/content update architecture before designing this state |
| MAJOR | Branding | Forbidden `BUYER APP` logo | Use the official logo |

### Canonical recommendation
Do not use until product/engineering approves the update mechanism; then adapt the progress component only if applicable.

---

## 10-update-failed-fr.png

Folder: `10-system-states/`  
Screen purpose: Recover from a failed application update  
Probable native route: Undefined pending update-mechanism decision  
Language: French  
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An update-failure page with retry and later actions.

### Visible UI structure
- Modified logo
- Failure illustration/copy
- Retry and defer actions

### Brand validation
- Logo: Uses forbidden `BUYER APP`
- Error/action styling: Consistent

### UX validation
Recovery is understandable, but optional versus mandatory update policy and the source of failure are not established.

### Native implementation usability
Feasible only after choosing store handoff, OTA, or content update behavior. NEEDS PRODUCT DECISION.

### Reusable components identified
- UpdateErrorState
- PrimaryButton
- SecondaryButton

### Dynamic backend data required
- Update policy/mechanism
- Failure class
- Retry eligibility

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Native behavior | Screen assumes an in-app update lifecycle that is not defined | Specify platform update flow and adapt recovery accordingly |
| MAJOR | Branding | Forbidden `BUYER APP` lockup | Use the official logo |

### Canonical recommendation
Correct only after the update architecture and mandatory/optional policy are approved.

---

## 10-update-required-ar.png

Folder: `10-system-states/`  
Screen purpose: Arabic mandatory-update prompt  
Probable native route: `/system/update-required`  
Language: Arabic  
Screen type:
- Dialog

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An Arabic forced-update state blocking continued use until the buyer opens the store update path.

### Visible UI structure
- Transliteration-style orange logo plus `Mayush Buyer App`
- Update illustration and Arabic copy
- Required-update action
- `44/46` gallery counter

### Brand validation
- Logo: Rewritten and contains forbidden English wording
- Palette and Arabic typography: Generally consistent/readable

### UX validation
The mandatory nature is clear. Product must define behavior when the store cannot open, the device is offline, or the update is unavailable in the buyer's store region.

### Native implementation usability
Feasible as a remote-config/version-gate dialog with a platform store deep link and fallback handling.

### Reusable components identified
- ForcedUpdateGate
- RTLSystemStateLayout
- PrimaryButton

### Dynamic backend data required
- Minimum supported version
- Installed version
- Platform store URL
- Outage/fallback message

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Official logo is replaced and `Mayush Buyer App` is added | Preserve the exact official logo |
| MAJOR | Artifact | `44/46` gallery counter is visible | Remove generation/gallery chrome |

### Canonical recommendation
Correct branding/artifacts and define store-link fallbacks before using as the Arabic forced-update reference.

---

## 10-update-required-fr.png

Folder: `10-system-states/`  
Screen purpose: French mandatory-update prompt  
Probable native route: `/system/update-required`  
Language: French  
Screen type:
- Dialog

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A forced-update page that blocks unsupported versions and links the buyer to update.

### Visible UI structure
- Modified logo
- Update illustration/copy
- Update primary action
- `Fermer` secondary action

### Brand validation
- Logo: Uses forbidden `BUYER APP`
- Palette/actions: Consistent

### UX validation
The requirement is clear, but `Fermer` is ambiguous: it could look like a bypass even if it exits the app. Store-unavailable/offline behavior needs a defined fallback.

### Native implementation usability
Feasible as a version gate backed by remote configuration and platform store links.

### Reusable components identified
- ForcedUpdateGate
- PrimaryButton
- TextButton

### Dynamic backend data required
- Minimum supported/installed version
- Platform store URL
- Availability/fallback state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | Forbidden `BUYER APP` logo treatment | Use the official logo |
| MINOR | UX | `Fermer` does not say whether it exits or bypasses the gate | Label the exact behavior and prevent unsupported-version continuation |

### Canonical recommendation
Correct branding and clarify close/fallback behavior before using as the French forced-update reference.

---

## Folder conclusion

Status totals for `10-system-states/`:

- APPROVED: 0
- APPROVED_WITH_MINOR_FIXES: 0
- NEEDS_REWORK: 0
- REFERENCE_ONLY: 35
- REJECTED: 11
- DUPLICATE_ALTERNATIVE: 17

No System States screenshot is a direct full-screen implementation reference. `10-content-loading-skeleton-fr.png` and other non-duplicate originals are retained only for isolated component/state ideas. The most dangerous assets are the filename/content contradictions (`10-cache-clearing-progress-fr.png`, `10-notifications-disabled-fr.png`, `10-offline-cached-content-fr.png`, `10-offline-fr.png`, `10-server-unavailable-fr.png`, and `10-session-expired-fr.png`) and security-route mismatches (`10-access-denied-403-fr.png`, `10-account-blocked-fr.png`, `10-account-sync-progress-fr.png`, and `10-unusual-activity-detected-fr.png`).
