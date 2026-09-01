# 04-auth Validation Report

> **Fact-check scope:** Currency and address examples are accepted variations. Do not treat them as validation defects by themselves; [fact-check-correction.md](./fact-check-correction.md) supersedes earlier currency/address severity notes.

Folder: `04-auth/`
Total screenshots: 20
Validation date: 2026-08-02

## Extracted validation rules used

- Preserve the official `MAYUSH DESIGN` mark without distortion or added qualifiers such as `BUYER` or `BUYER APP`.
- Use warm cream, Mayush orange near `#D97434`, deep navy, white and beige surfaces, rounded controls, restrained shadows, and one coherent outline icon family.
- Auth routes may omit buyer bottom navigation. Navigation back behavior must remain predictable and Arabic arrows and content order must mirror for RTL.
- Morocco is the default context: `+212` for phone entry and `MAD` for any commerce context. France, Gulf, or other locale artifacts are not acceptable.
- Inputs need persistent labels, 44 pt / 48 dp minimum targets, semantic keyboards, `textContentType`/autofill, password visibility controls, inline errors, disabled/loading states, and keyboard-safe scrolling.
- OTP must support one-time-code autofill, paste, sequential focus, screen-reader labels, resend throttling, expiry behavior, and recovery without six unrelated native text fields.
- Layouts must survive small phones, safe areas, Dynamic Type, longer French/Arabic strings, and the software keyboard. Decorative illustrations cannot contain required live text.

## 04-account-created-success-fr.png

Folder: `04-auth/`
Screen purpose: Confirm successful account creation and offer the next buyer action
Probable native route: `Auth/AccountCreated`
Language: French
Screen type:
- Success state

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
Terminal registration success before the buyer enters discovery or optional profile completion.

### Visible UI structure
- Header: large official Mayush logo
- Main content: furniture-themed success illustration, title and explanatory copy
- Cards: illustrated success panel
- Forms: none
- Primary action: `Découvrir Mayush`
- Secondary actions: `Compléter mon profil`
- Navigation: no bottom navigation, appropriate for this auth transition
- Overlays: none

### Brand validation
- Logo: official artwork and proportions are preserved, but it occupies excessive vertical space.
- Colors: cream, orange, navy and beige match the foundation.
- Typography: hierarchy is clear and French copy is consistent.
- Icons: check, profile and arrows use a compatible outline/solid vocabulary.
- Buttons: primary and outlined secondary treatments match the system.
- Cards: soft illustrated surface is consistent with success-state references.
- Spacing: spacious, though the logo and illustration make the route poster-like on short devices.
- Shadows: restrained and consistent.

### UX validation
- The outcome and primary next step are unambiguous.
- Profile completion is correctly secondary and non-blocking.
- The content must scroll on small phones and at large Dynamic Type; neither CTA may sit in the bottom unsafe area.
- Announce the success heading to screen readers and move accessibility focus to it.

### Native implementation usability
Implementable with a `ScrollView`, live text, a decorative image, and shared button components. The illustration must remain decorative while all messaging and actions stay native and dynamic.

### Reusable components identified
- `AuthScreenContainer`
- `MayushLogo`
- `SuccessIllustration`
- `PrimaryButton`
- `OutlinedButton`

### Dynamic backend data required
- Account-creation success result
- Profile-completion state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Responsive layout | Oversized brand and illustration may push actions below the fold on compact phones or with Dynamic Type. | Use a scrollable layout, responsive image height, and bottom safe-area padding. |
| MINOR | Accessibility | Focus/announcement behavior for the success result is not represented. | Focus the success heading and announce the state without trapping focus. |

### Canonical recommendation
Use directly as the main French account-created reference after the minor responsive/accessibility corrections.

---

## 04-consent-terms-privacy-fr.png

Folder: `04-auth/`
Screen purpose: Collect required legal acceptance and optional marketing consent
Probable native route: `Auth/Consent`
Language: French
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A pre-account consent step separating required terms/privacy review from optional promotional e-mail consent.

### Visible UI structure
- Header: official Mayush logo
- Main content: title, explanatory copy, required and optional consent sections, privacy reassurance
- Cards: two required consent rows and one optional switch row
- Forms: acceptance indicators and marketing toggle
- Primary action: `Accepter et continuer`
- Secondary actions: open terms, open privacy policy, `Retour`
- Navigation: no bottom navigation, appropriate for auth
- Overlays: none

### Brand validation
- Logo: official mark is preserved.
- Colors: cream, orange, navy, white and beige are consistent.
- Typography: clear hierarchy; long policy names have adequate room in this static example.
- Icons: coherent outline family, but check circles look like completed status rather than controls.
- Buttons: primary treatment is consistent.
- Cards: rounded white consent rows align with foundation surfaces.
- Spacing: generally orderly; the bottom text link is visually weak.
- Shadows: soft and restrained.

### UX validation
- Required and optional consent are correctly separated.
- Both required rows already appear accepted, although no explicit checkbox state or evidence of review is shown; the user cannot understand what action changed them.
- Privacy-policy acknowledgement should not be represented as consent to data processing unless product/legal requirements explicitly require it. NEEDS PRODUCT DECISION.
- The absolute claim that information is “never” shared must be verified against the real policy. NEEDS PRODUCT DECISION.
- Required controls, switch, links and back action need accessible names, states, 44/48 targets and Dynamic Type wrapping.

### Native implementation usability
Feasible with reusable consent rows, a native `Switch`, link routes and a scroll container. Required acceptance state must be modeled explicitly rather than baked into artwork.

### Reusable components identified
- `LegalConsentRow`
- `SettingsSwitchRow`
- `SecurityNotice`
- `PrimaryButton`
- `TextLink`

### Dynamic backend data required
- Current terms version
- Current privacy-policy version
- Required acceptance state
- Marketing-consent state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Consent logic | Required rows appear pre-accepted and do not expose a clear checkbox/action model. | Start unaccepted or show an explicit reviewed-and-accepted control; enable continue only after valid acceptance. |
| MAJOR | Product/legal | “Nous ne partageons jamais…” is an unverified absolute privacy claim. | Replace with policy-approved copy. NEEDS PRODUCT DECISION. |
| MINOR | Accessibility | The underlined `Retour` action is comparatively small and the cards must expose checked states. | Guarantee 44/48 targets and announce link, switch and checked semantics. |

### Canonical recommendation
Correct the consent model and legal copy before use; retain the required-versus-optional grouping.

---

## 04-create-new-password-requirements-ar.png

Folder: `04-auth/`
Screen purpose: Create and confirm a replacement password with visible requirements
Probable native route: `Auth/ResetPassword/NewPassword`
Language: Arabic
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Arabic password-reset form after a valid recovery token or OTP.

### Visible UI structure
- Header: large Mayush logo, no top back control
- Main content: RTL heading, help copy and password-requirement panel
- Cards: beige requirement card
- Forms: new-password and confirmation inputs with eye icons
- Primary action: `حفظ كلمة المرور`
- Secondary actions: return to login
- Navigation: auth flow without bottom tabs
- Overlays: none

### Brand validation
- Logo: official mark is preserved.
- Colors: cream, orange and navy align with the foundation.
- Typography: Arabic is readable, but the hierarchy and field typography differ from the French counterpart.
- Icons: eye and requirement checks are visually coherent.
- Buttons: orange primary treatment matches the system.
- Cards: soft beige requirements card is consistent.
- Spacing: generous but inefficient on smaller phones.
- Shadows: soft and consistent.

### UX validation
- The goal and requirements are clear.
- Inputs rely on placeholder-like text rather than persistent labels, so the field purpose disappears after entry.
- The return-to-login chevron points left; a back affordance in RTL must mirror to the right and remain at the logical start edge.
- Password manager/autofill, secure text entry, show/hide state, mismatch errors, loading/disabled state and keyboard-safe scrolling are not represented.
- Requirement status must not rely on muted check icons alone and must be announced as each rule becomes satisfied.

### Native implementation usability
Feasible with shared password inputs and a live requirements list, but the RTL navigation and semantic label model must be corrected before the image guides implementation.

### Reusable components identified
- `RTLAuthHeader`
- `PasswordInput`
- `PasswordRequirementsCard`
- `PrimaryButton`
- `TextLink`

### Dynamic backend data required
- Reset-token validity
- Server password policy
- Password-update result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | RTL navigation | The back-to-login chevron uses LTR direction and no mirrored top back control is provided. | Mirror the arrow/right-edge placement and follow platform RTL back behavior. |
| MAJOR | Form accessibility | Password fields have no persistent visible labels or represented validation errors. | Add labels, accessible hints, mismatch feedback, autofill metadata and focus-first-error behavior. |
| MINOR | Responsive layout | Large fixed vertical spacing risks keyboard and Dynamic Type overflow. | Use keyboard-aware scrolling and responsive spacing. |

### Canonical recommendation
Use only after RTL navigation and password-field semantics are reworked; pair visually with the stronger French requirements structure.

---

## 04-create-new-password-requirements-fr.png

Folder: `04-auth/`
Screen purpose: Create and confirm a replacement password with requirements
Probable native route: `Auth/ResetPassword/NewPassword`
Language: French
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
French reset-password entry after the recovery identity has been verified.

### Visible UI structure
- Header: back arrow and official Mayush logo
- Main content: heading, instructions and requirements card
- Cards: password-policy panel
- Forms: two labeled secure inputs with visibility toggles
- Primary action: `Enregistrer le mot de passe`
- Secondary actions: back navigation
- Navigation: no bottom navigation, appropriate for auth
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: foundation cream, orange, navy and white.
- Typography: clear labels and hierarchy.
- Icons: lock, eye, shield and checks use compatible outlines.
- Buttons: orange rounded CTA is consistent.
- Cards: white elevated requirements card matches the system.
- Spacing: balanced, though CTA proximity to the bottom needs a safe inset.
- Shadows: soft and consistent.

### UX validation
- Persistent labels, two distinct password fields and requirements provide a clear flow.
- Requirements visually appear satisfied even though the password state is only implied; implementation must bind each row to live validation and avoid checkmark-only meaning.
- Add password-manager metadata, mismatch/error copy, disabled/loading behavior and keyboard-safe scroll.
- Back and eye buttons require expanded 44/48 targets and accessibility labels.

### Native implementation usability
Strong reusable React Native reference using secure `TextInput`s, shared validation rows and a keyboard-aware `ScrollView`.

### Reusable components identified
- `AuthHeader`
- `PasswordInput`
- `PasswordRequirementsCard`
- `RequirementRow`
- `PrimaryButton`

### Dynamic backend data required
- Reset-token validity
- Server password policy
- Password-update result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Validation state | All requirements appear complete without a represented incomplete/error state. | Drive icon, text and announced state from live policy validation. |
| MINOR | Mobile behavior | Keyboard, loading/disabled state and bottom safe-area behavior are not visible. | Use keyboard-aware scroll, prevent duplicate submit and add safe-area CTA spacing. |

### Canonical recommendation
Use as the main password-creation reference after the minor state and mobile-behavior fixes.

## 04-email-verification-link-sent-fr.png

Folder: `04-auth/`
Screen purpose: Confirm that an account-verification e-mail was sent
Probable native route: `Auth/VerifyEmail/Sent`
Language: French
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A pending e-mail-verification state with actions to edit the address, open a mail app, resend, or confirm that verification has already occurred.

### Visible UI structure
- Header: official Mayush logo
- Main content: mail illustration, title, masked address and guidance
- Cards: none
- Forms: none
- Primary action: `Ouvrir ma messagerie`
- Secondary actions: edit e-mail, resend e-mail, `J’ai déjà confirmé`
- Navigation: no bottom navigation
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: cream/orange/navy palette is consistent.
- Typography: clear hierarchy; the masked address is emphasized.
- Icons: mail, edit and refresh icons are coherent.
- Buttons: primary and outlined styles match foundation controls.
- Cards: illustration is brand-compatible.
- Spacing: clean but vertically long.
- Shadows: subtle and appropriate.

### UX validation
- The state is understandable and exposes recovery choices.
- Four competing actions dilute the hierarchy; `J’ai déjà confirmé` requires an explicit server recheck, not a client-side bypass.
- `Ouvrir ma messagerie` cannot assume one installed mail client and needs chooser/fallback behavior.
- Resend requires throttling, visible countdown, disabled/loading feedback and accessible success/error confirmation.

### Native implementation usability
Native implementation is feasible, but deep-link fallback and resend state are functional requirements that the static reference does not resolve.

### Reusable components identified
- `AuthStatusScreen`
- `MaskedIdentifier`
- `PrimaryButton`
- `OutlinedButton`
- `TextLink`

### Dynamic backend data required
- Masked e-mail address
- Verification status
- Resend cooldown/attempt state
- Resend and verification-check results

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Auth logic | `J’ai déjà confirmé` could imply bypassing verification. | Make it a server status refresh with loading, failure and still-pending feedback. |
| MAJOR | Recovery behavior | Resend has no cooldown/disabled/error state and can be abused or double-submitted. | Add server-backed throttling, countdown and confirmation. |
| MINOR | Platform behavior | Opening “my messaging” assumes an available mail handler. | Use the system chooser and show instructions if no handler is available. |

### Canonical recommendation
Correct action hierarchy and verification/resend behavior before use; retain the illustration and masked-address treatment.

---

## 04-forgot-password-enter-email-ar.png

Folder: `04-auth/`
Screen purpose: Start account recovery using e-mail or phone
Probable native route: `Auth/ForgotPassword`
Language: Arabic
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Arabic recovery entry for an identifier associated with a Mayush account.

### Visible UI structure
- Header: large official logo; no top back control
- Main content: RTL title and explanatory copy
- Cards: none
- Forms: one combined e-mail/phone field with mail icon and `+212` selector
- Primary action: `متابعة`
- Secondary actions: return to login
- Navigation: no bottom tabs
- Overlays: none

### Brand validation
- Logo: official mark is preserved.
- Colors: approved cream, orange and navy.
- Typography: Arabic is legible and consistently aligned.
- Icons: mail and chevron styles align with the family.
- Buttons: primary orange CTA is consistent.
- Cards: input surface is rounded and restrained.
- Spacing: very large fixed gaps create weak compact-phone adaptation.
- Shadows: subtle and consistent.

### UX validation
- The recovery goal is clear and Morocco’s `+212` appears.
- A permanent phone prefix inside a field that also accepts e-mail creates ambiguous entry, keyboard and autofill behavior.
- The return chevron points left rather than mirroring for RTL.
- A persistent label, inline error, privacy-preserving confirmation, submit loading/disabled state and keyboard-aware scroll are missing from the visual model.

### Native implementation usability
Feasible after splitting the identifier mode or making the prefix conditional. The current single control should not be copied literally.

### Reusable components identified
- `RTLAuthHeader`
- `IdentifierModeInput`
- `CountryCodePicker`
- `PrimaryButton`
- `TextLink`

### Dynamic backend data required
- Recovery-request result
- Identifier validation result
- Supported recovery channels

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Form logic | `+212` is always shown while the field also accepts e-mail. | Use explicit e-mail/phone mode or conditionally reveal the country selector with matching keyboard/autofill. |
| MAJOR | RTL navigation | Return chevron direction is not mirrored. | Place/use the logical RTL back arrow on the right. |
| MINOR | Accessibility | The identifier depends on placeholder text. | Add a persistent label, error/hint text and semantic input metadata. |

### Canonical recommendation
Correct the identifier control and RTL navigation before use; use as the Arabic locale companion only after those changes.

---

## 04-forgot-password-enter-email-fr.png

Folder: `04-auth/`
Screen purpose: Start password recovery with e-mail or phone
Probable native route: `Auth/ForgotPassword`
Language: French
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
Medium

### What the screen represents
A compact French recovery request form.

### Visible UI structure
- Header: circular back button and official Mayush logo
- Main content: title, instructions and decorative furniture silhouette
- Cards: none
- Forms: combined e-mail/telephone input
- Primary action: `Continuer`
- Secondary actions: `Retour à la connexion`
- Navigation: no bottom navigation
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: approved cream, orange and navy.
- Typography: clear French hierarchy.
- Icons: back, mail and send icons are visually compatible, though the send icon is not semantically necessary.
- Buttons: strong orange primary treatment.
- Cards: rounded input matches foundation controls.
- Spacing: balanced but relies on fixed poster-like decoration.
- Shadows: soft and restrained.

### UX validation
- The task and recovery path are clear.
- The combined field needs runtime mode detection or an explicit mode choice so keyboard, `textContentType`, validation and `+212` behavior are correct.
- Placeholder-only labeling must become a persistent label.
- Submission should return privacy-preserving copy whether or not the account exists and expose loading/error recovery.

### Native implementation usability
Straightforward with a keyboard-aware auth container and conditional identifier input. The furniture silhouette should remain decorative and non-blocking.

### Reusable components identified
- `AuthHeader`
- `IdentifierModeInput`
- `PrimaryButton`
- `TextLink`
- `DecorativeAuthBackground`

### Dynamic backend data required
- Supported recovery channels
- Recovery-request result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Form semantics | One ambiguous identifier field cannot expose both e-mail and telephone keyboard/autofill semantics simultaneously. | Detect or let users choose the mode and switch metadata/prefix accordingly. |
| MINOR | Accessibility | Field labeling and loading/error states are not represented. | Add persistent label, accessible hint, disabled/loading state and inline recovery feedback. |

### Canonical recommendation
Use as the main forgot-password reference after implementing conditional identifier semantics and standard feedback states.

---

## 04-login-email-phone-password-ar.png

Folder: `04-auth/`
Screen purpose: Sign in using e-mail or Moroccan phone plus password
Probable native route: `Auth/Login`
Language: Arabic
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Arabic buyer login with Moroccan locale styling and a decorative Moroccan-contemporary background.

### Visible UI structure
- Header: back arrow at upper left, official logo
- Main content: RTL welcome copy and decorative mashrabiya/furniture imagery
- Cards: none
- Forms: combined e-mail/phone control with Morocco flag and `+212`, password input with visibility toggle
- Primary action: `تسجيل الدخول`
- Secondary actions: forgot password and create account
- Navigation: no bottom navigation
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: cream, orange and navy are consistent.
- Typography: Arabic is clear; mixed `+212` remains readable.
- Icons: compatible outline controls.
- Buttons: primary orange CTA matches the system.
- Cards: input surfaces are consistent.
- Spacing: attractive but fixed decoration reduces adaptive flexibility.
- Shadows: subtle and consistent.

### UX validation
- The primary task and recovery/registration paths are visible.
- The upper-left LTR back arrow is incorrect for an RTL route.
- The `+212` selector is always visible although e-mail is also accepted, making focus, keyboard and autofill behavior ambiguous.
- Persistent labels, submit disabled/loading state, inline errors, password-manager metadata and keyboard avoidance are not represented.

### Native implementation usability
The layout is feasible using shared auth components and absolute decorative layers, but the RTL header and conditional identifier control require redesign before implementation.

### Reusable components identified
- `RTLAuthHeader`
- `IdentifierModeInput`
- `CountryCodePicker`
- `PasswordInput`
- `PrimaryButton`

### Dynamic backend data required
- Authentication result
- Supported identifier modes
- Remembered identifier, if permitted

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | RTL navigation | Back arrow remains on the left and points left. | Mirror placement and direction using logical RTL navigation. |
| MAJOR | Form logic | E-mail and phone share a control with a permanently visible `+212` selector. | Add mode selection or conditionally reveal the phone prefix with matching keyboard/autofill. |
| MINOR | Mobile accessibility | Placeholder labels and decorative fixed layout may fail keyboard/Dynamic Type use. | Add labels and a keyboard-aware, scrollable layout with 44/48 targets. |

### Canonical recommendation
Use as the Arabic visual companion only after mirroring navigation and correcting identifier semantics; align component behavior with the French login reference.

## 04-login-email-phone-password-fr.png

Folder: `04-auth/`
Screen purpose: Buyer sign-in using e-mail or Moroccan phone and password
Probable native route: `Auth/Login`
Language: French
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
The principal French login route and the strongest available base login reference.

### Visible UI structure
- Header: back arrow and official Mayush logo
- Main content: welcome heading and supporting copy
- Cards: none
- Forms: labeled identifier and password inputs, Morocco flag/`+212`, password visibility control
- Primary action: `Se connecter`
- Secondary actions: forgot password and create account
- Navigation: no bottom navigation, appropriate for auth
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: approved cream/orange/navy treatment.
- Typography: strong hierarchy and readable labels.
- Icons: mail, phone, lock, eye and arrow share an outline style.
- Buttons: correct primary orange rounded control.
- Cards: input surfaces follow foundation radii and elevation.
- Spacing: comfortable; requires adaptation when keyboard appears.
- Shadows: soft and consistent.

### UX validation
- The goal, password recovery and account creation are clear.
- Persistent field labels are present.
- The shared identifier row displays both e-mail and phone affordances plus `+212`; implementation must conditionally adapt keyboard, autofill and prefix rather than treating it as a fixed visual composite.
- Add submit loading/disabled state, inline errors, password-manager support, return-key flow and first-error focus.

### Native implementation usability
Suitable as the core React Native login composition with a keyboard-aware `ScrollView`, conditional identifier mode and shared semantic inputs.

### Reusable components identified
- `AuthHeader`
- `IdentifierModeInput`
- `CountryCodePicker`
- `PasswordInput`
- `PrimaryButton`
- `TextLink`

### Dynamic backend data required
- Authentication result
- Supported identifier modes
- Remembered identifier, if permitted

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Form semantics | The static composite suggests `+212` even for an e-mail login. | Make the prefix and phone icon conditional or provide an explicit identifier mode. |
| MINOR | State coverage | Disabled, loading and inline error behavior are absent from this base state. | Reuse validated state components and prevent duplicate submit. |
| MINOR | Mobile behavior | Keyboard and large text can compress the lower links/CTA. | Use keyboard-aware scrolling and preserve bottom safe-area insets. |

### Canonical recommendation
Use as the main French login reference with conditional identifier behavior and standard form states.

---

## 04-login-error-incorrect-credentials-fr.png

Folder: `04-auth/`
Screen purpose: Show failed login caused by invalid credentials
Probable native route: `Auth/Login`
Language: French
Screen type:
- Error state

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
An authentication failure state retaining the entered identifier and exposing retry and recovery.

### Visible UI structure
- Header: official Mayush logo
- Main content: welcome copy and login form
- Cards: none
- Forms: populated identifier, obscured password, inline error row
- Primary action: `Réessayer`
- Secondary actions: forgot password
- Navigation: no bottom navigation
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: cream/orange/navy with semantic red error.
- Typography: error is readable and clearly separated.
- Icons: user, lock, eye and error icons are coherent.
- Buttons: primary orange treatment is consistent.
- Cards: input surfaces match the foundation.
- Spacing: clear and uncluttered.
- Shadows: restrained.

### UX validation
- Generic credential failure avoids confirming which account field is valid.
- Recovery is visible and the failed form remains editable.
- `Réessayer` should submit edited values, not repeat a stale request, and the first relevant field/error should receive accessible focus.
- Password content must never be restored from storage after a process restart; rate-limit/lockout/captcha policy is a product decision.

### Native implementation usability
Directly reusable as the error variant of the canonical login component with live form state and screen-reader announcements.

### Reusable components identified
- `LoginForm`
- `FormErrorMessage`
- `PasswordInput`
- `PrimaryButton`
- `TextLink`

### Dynamic backend data required
- Authentication error category
- Rate-limit or lockout state
- Entered identifier

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Accessibility | The visual does not specify error announcement or focus recovery. | Announce the error and focus the error summary or first invalid input. |
| MINOR | Interaction state | Retry loading/disabled and rate-limit behavior are not represented. | Disable during request and surface server throttling with a recovery path. |

### Canonical recommendation
Use as the canonical incorrect-credentials state combined with the base login header/back behavior.

---

## 04-login-loading-state-fr.png

Folder: `04-auth/`
Screen purpose: Indicate authentication submission in progress
Probable native route: `Auth/Login`
Language: French
Screen type:
- Loading state

### Status
REJECTED

### Confidence
High

### What the screen represents
A disabled login form while the authentication request is processing.

### Visible UI structure
- Header: official logo, no back control
- Main content: login heading, disabled-looking form, centered progress messaging
- Cards: none
- Forms: e-mail/phone, password and remember-me controls
- Primary action: disabled `Connexion en cours…`
- Secondary actions: forgot password and create account remain visible
- Navigation: no bottom tabs
- Overlays: inline loading spinner rather than modal overlay

### Brand validation
- Logo: official mark is present.
- Colors: cream/orange/navy direction is recognizable, but low-opacity disabled text has weak contrast.
- Typography: `compte acheteur` adds prohibited buyer-specific wording.
- Icons: generally coherent.
- Buttons: loading treatment is understandable but overly faded.
- Cards: inputs match the rounded system.
- Spacing: workable.
- Shadows: restrained.

### UX validation
- The route exposes request progress and prevents editing in the depicted state.
- The placeholder contains French phone prefix `+33`, contradicting the mandatory Moroccan `+212` context.
- `compte acheteur` conflicts with the required unqualified Mayush brand language.
- Forgot-password/create-account remain apparently actionable during a pending submission, risking navigation races.
- Loading is duplicated in both page center and CTA.

### Native implementation usability
The pattern is implementable, but this image must not guide the app because its locale and branding are explicitly wrong. Rebuild loading as a state of the canonical login screen.

### Reusable components identified
- `LoginForm`
- `LoadingButton`
- `InlineProgressStatus`
- `CheckboxRow`

### Dynamic backend data required
- Authentication request status
- Remember-me preference

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Locale | The identifier example uses `+33` instead of Morocco’s `+212`. | Replace with conditional Moroccan phone entry and never use this image as locale truth. |
| MAJOR | Branding | Copy says `compte acheteur`, adding buyer-specific wording. | Use neutral `compte Mayush` copy. |
| MAJOR | State logic | Two loading indicators compete while other auth links appear enabled. | Keep one progress treatment, disable conflicting actions and preserve a cancellation/back policy. |
| MINOR | Accessibility | Disabled text/icon contrast is too low. | Meet contrast requirements and announce busy/disabled state. |

### Canonical recommendation
Reject as an implementation reference; derive loading behavior from `04-login-email-phone-password-fr.png` instead.

---

## 04-login-prompt-overlay-favorites-fr.png

Folder: `04-auth/`
Screen purpose: Prompt a guest to authenticate before synchronizing favorites or continuing purchase
Probable native route: `Favorites` with `AuthPromptSheet`
Language: French
Screen type:
- Bottom sheet

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
A guest-gating sheet presented over a populated favorites route.

### Visible UI structure
- Header: underlying `Mes favoris`, search and cart controls
- Main content: dimmed favorites grid and large auth prompt sheet
- Cards: underlying product cards
- Forms: none
- Primary action: `Se connecter`
- Secondary actions: create account and continue exploring
- Navigation: underlying top-level route; bottom tabs are not visible
- Overlays: tall rounded sheet with scrim and drag handle

### Brand validation
- Logo: official logo appears inside the sheet, but is unnecessarily dominant.
- Colors: sheet uses approved cream/orange/navy.
- Typography: French sheet copy is consistent.
- Icons: user and account icons are coherent.
- Buttons: primary and outlined styles match the foundation.
- Cards: background product cards are visually consistent.
- Spacing: sheet is overly tall for three actions.
- Shadows: scrim separates layers adequately.

### UX validation
- Login, registration and dismissal/continue choices are clear.
- Underlying favorites display EUR, a critical contradiction to mandatory `MAD`.
- Sheet copy mentions both favorites and continuing an order, creating context ambiguity on a favorites trigger.
- Drag handle plus `Continuer à explorer` offers dismissal, but explicit close and accessibility-modal focus behavior should be standardized.
- Guest favorites should be preserved through auth and merge behavior requires a product decision.

### Native implementation usability
The sheet pattern is reusable with a native modal/bottom-sheet library, but the screenshot cannot be copied as a whole until the underlying currency, route context and height are corrected.

### Reusable components identified
- `AuthPromptSheet`
- `ModalScrim`
- `MayushLogo`
- `PrimaryButton`
- `OutlinedButton`
- `TextButton`

### Dynamic backend data required
- Guest authentication state
- Trigger source
- Guest favorites count
- Cart/favorites preservation result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Currency | Background product prices use EUR. | Replace every price with backend-provided `MAD` formatting. |
| MAJOR | Context/copy | Favorites prompt also says “poursuivre votre commande,” mixing two trigger contexts. | Parameterize copy for favorites versus checkout/cart. |
| MINOR | Sheet usability | Sheet is taller than needed and lacks an explicit close icon. | Reduce height to content, keep swipe dismissal and add a 44/48 close control. |

### Canonical recommendation
Correct before use; retain only the three-path auth-prompt concept and combine it with a valid MAD favorites screen.

## 04-login-prompt-overlay-wishlist-ar.png

Folder: `04-auth/`
Screen purpose: Prompt an Arabic guest to sign in before preserving wishlist/cart activity
Probable native route: `Favorites` with `AuthPromptSheet`
Language: Arabic
Screen type:
- Bottom sheet

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An RTL login gate over a list labeled as favorites.

### Visible UI structure
- Header: dimmed RTL route title, right-side back arrow and cart badge
- Main content: dimmed item list and auth prompt
- Cards: background item rows with quantity/delete controls
- Forms: none
- Primary action: sign in
- Secondary actions: create account and continue browsing
- Navigation: underlying route only; no bottom tabs visible
- Overlays: rounded RTL bottom sheet with scrim and drag handle

### Brand validation
- Logo: sheet uses a lock illustration rather than logo; branding remains recognizable through palette.
- Colors: cream/orange/navy are consistent.
- Typography: Arabic sheet copy is readable and right-to-left.
- Icons: coherent outlines.
- Buttons: primary/outlined hierarchy is consistent.
- Cards: background rows resemble cart items more than favorites.
- Spacing: sheet is tall but legible.
- Shadows: scrim and separation are adequate.

### UX validation
- The three auth choices are clear and sheet RTL text is generally correct.
- Background prices use `ر.س` (Saudi riyal), contradicting the Morocco/MAD requirement.
- A page titled wishlist includes quantity steppers and trash controls typical of a cart, confusing the route model.
- Preserve guest state through authentication; merge/conflict behavior is a product decision.
- Modal focus, hardware/predictive back, swipe dismissal and explicit close behavior must be defined.

### Native implementation usability
The RTL sheet is feasible, but the complete image is unreliable because the underlying commerce route and currency are wrong.

### Reusable components identified
- `RTLAuthPromptSheet`
- `ModalScrim`
- `PrimaryButton`
- `OutlinedButton`
- `TextButton`

### Dynamic backend data required
- Guest auth state
- Trigger source
- Guest favorites/cart state
- Merge result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Currency | Background prices use Saudi riyal instead of `MAD`. | Format all commerce values in `MAD`. |
| MAJOR | Route logic | Wishlist rows expose quantity steppers and deletion as if they were cart rows. | Use actual favorite-card actions or relabel the route as cart. |
| MINOR | Modal accessibility | No explicit close affordance or focus model is shown. | Add a mirrored close control and modal focus/back semantics. |

### Canonical recommendation
Correct currency and background route before use; retain the RTL sheet alignment as a partial pattern only.

---

## 04-otp-phone-verification-ar.png

Folder: `04-auth/`
Screen purpose: Verify a Moroccan phone with a six-digit OTP
Probable native route: `Auth/VerifyPhone`
Language: Arabic
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
Arabic OTP entry with a masked `+212` destination, resend timing and number-edit recovery.

### Visible UI structure
- Header: official Mayush logo
- Main content: RTL heading, masked number, six code boxes and timer text
- Cards: none
- Forms: six-digit OTP entry
- Primary action: verify
- Secondary actions: resend and edit number
- Navigation: no bottom navigation
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: approved cream/orange/navy.
- Typography: Arabic and Latin digits remain readable.
- Icons: refresh and edit icons are coherent.
- Buttons: primary orange CTA matches the system.
- Cards: outlined OTP cells match form controls.
- Spacing: clear but should become scrollable on compact devices.
- Shadows: minimal and appropriate.

### UX validation
- `+212`, masking, code length and recovery paths are clear.
- Numeric OTP direction may remain LTR inside the RTL page, but focus order and spoken position labels must be explicit.
- Implement as one semantic OTP value with visual cells, supporting SMS one-time-code autofill, paste, deletion and automatic advance.
- Resend must be disabled until its cooldown permits it; timer meaning must distinguish code expiry from resend delay.

### Native implementation usability
Strong locale reference using one controlled hidden/native input rendered into six visual cells, not six independent fields.

### Reusable components identified
- `RTLAuthHeader`
- `OtpInput`
- `OtpTimer`
- `PrimaryButton`
- `TextActionRow`

### Dynamic backend data required
- Masked phone number
- OTP length
- Code-expiry time
- Resend cooldown/attempt state
- Verification result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | OTP semantics | The visual does not define autofill/paste or accessible focus order. | Use one semantic OTP input with one-time-code metadata and announce digit positions. |
| MINOR | Timer state | `00:45` appears beside resend wording without a clearly disabled action state. | Label countdown meaning and enable resend only when allowed. |

### Canonical recommendation
Use as the Arabic OTP reference after semantic input and resend-state corrections.

---

## 04-otp-phone-verification-fr.png

Folder: `04-auth/`
Screen purpose: Verify a Moroccan phone with a six-digit OTP
Probable native route: `Auth/VerifyPhone`
Language: French
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
French OTP entry with masked `+212` number, validity timer, resend and number-edit actions.

### Visible UI structure
- Header: official logo and security illustration
- Main content: title, masked number, six OTP cells and timer
- Cards: decorative security illustration
- Forms: OTP input
- Primary action: `Vérifier`
- Secondary actions: resend code and edit number
- Navigation: no bottom navigation
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: cream/orange/navy align with foundation.
- Typography: clear hierarchy and readable masked phone.
- Icons: timer, refresh and edit outlines are consistent.
- Buttons: correct orange primary style.
- Cards: illustration is compatible with system-state art.
- Spacing: good, though no explicit top back is shown.
- Shadows: restrained.

### UX validation
- The action and destination are clear and Morocco context is correct.
- OTP cells must be driven by one semantic input with number pad, SMS autofill, paste and accessible announcements.
- `Code valide pendant` is not the same as resend cooldown; implementation needs separate server-backed state and throttling.
- Verify must remain disabled until code length is complete and show loading/error without clearing valid digits unnecessarily.

### Native implementation usability
Suitable as the primary French OTP composition after adding semantic input behavior and state definitions.

### Reusable components identified
- `AuthStatusHeader`
- `OtpInput`
- `OtpTimer`
- `PrimaryButton`
- `TextActionRow`

### Dynamic backend data required
- Masked phone number
- OTP length
- Code expiry
- Resend cooldown
- Verification result

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | OTP accessibility | Six cells do not specify one-time-code autofill, paste or screen-reader semantics. | Implement one semantic value with visual cells and clear digit-position announcements. |
| MINOR | State logic | Validity timer and resend availability are not distinguished. | Model code expiry and resend cooldown separately; show disabled/loading states. |
| MINOR | Navigation | No clear back action is visible. | Provide predictable platform back behavior or an explicit back control. |

### Canonical recommendation
Use as the main French OTP reference after the minor state and accessibility fixes.

---

## 04-otp-verification-error-incorrect-code-fr.png

Folder: `04-auth/`
Screen purpose: Show an incorrect OTP and allow recovery
Probable native route: `Auth/VerifyPhone`
Language: French
Screen type:
- Error state

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
OTP verification failure for a Moroccan number.

### Visible UI structure
- Header: official logo and OTP illustration
- Main content: title, masked `+212` number, six cells and red error banner
- Cards: resend and edit-number action rows
- Forms: OTP input with first cell focused
- Primary action: `Vérifier`
- Secondary actions: resend and edit number
- Navigation: no bottom tabs
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: brand palette plus semantic red error.
- Typography: clear; error contrast is strong.
- Icons: warning, refresh, edit and chevrons are coherent.
- Buttons: orange CTA matches foundation.
- Cards: action rows use consistent rounded surfaces.
- Spacing: balanced.
- Shadows: restrained.

### UX validation
- Cause and recovery actions are visible.
- Error persists while cells look empty and `Vérifier` remains visually enabled, creating a contradictory state.
- On failure, focus should return to the OTP input, error should be announced, and the user should know whether digits were cleared.
- Resend rate limit and remaining attempts/lockout behavior require product/security definition.

### Native implementation usability
Feasible as the error variant of the canonical OTP component, but the state machine must be corrected before this image guides implementation.

### Reusable components identified
- `OtpInput`
- `InlineErrorBanner`
- `ActionListRow`
- `PrimaryButton`

### Dynamic backend data required
- Masked phone number
- Verification error
- Remaining attempts/lockout state
- Resend cooldown

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | State contradiction | OTP appears empty while incorrect-code error remains and Verify looks enabled. | Define whether digits remain or clear; disable Verify until six digits are present and clear stale errors on edit. |
| MAJOR | Security/product | Attempt limits and resend throttling are not represented. | Add server-backed rate-limit/lockout feedback. NEEDS PRODUCT DECISION. |
| MINOR | Accessibility | Error announcement and focus return are unspecified. | Use live announcement and return focus to the semantic OTP input. |

### Canonical recommendation
Correct the OTP error state machine before use; combine with `04-otp-phone-verification-fr.png` rather than treating this as a standalone layout.

## 04-password-changed-success-fr.png

Folder: `04-auth/`
Screen purpose: Confirm successful password change
Probable native route: `Auth/ResetPassword/Success`
Language: French
Screen type:
- Success state

### Status
REJECTED

### Confidence
High

### What the screen represents
A terminal password-reset success state offering sign-in or return home.

### Visible UI structure
- Header: Mayush logo with added `BUYER APP` wording
- Main content: security success illustration, title and confirmation copy
- Cards: illustrated success panel
- Forms: none
- Primary action: `Se connecter`
- Secondary actions: `Retour à l’accueil`
- Navigation: no bottom navigation
- Overlays: none

### Brand validation
- Logo: prohibited `BUYER APP` qualifier modifies the official brand lockup.
- Colors: cream/orange/navy direction is otherwise consistent.
- Typography: French hierarchy is clear.
- Icons: check, shield and lock are compatible.
- Buttons: orange primary treatment is consistent.
- Cards: illustration matches success-state language.
- Spacing: vertically balanced.
- Shadows: soft and restrained.

### UX validation
- Success and next actions are clear.
- Returning home while unauthenticated is valid only if guest browsing is supported; destination behavior needs product confirmation.
- Focus should move to the success title and the state must be announced.

### Native implementation usability
The generic success composition is implementable, but the screenshot must not guide branding. Rebuild it with the official logo and shared success components.

### Reusable components identified
- `AuthStatusScreen`
- `SuccessIllustration`
- `PrimaryButton`
- `TextButton`

### Dynamic backend data required
- Password-change success result
- Post-reset authentication state

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Branding | The logo adds `BUYER APP`, explicitly prohibited by the mobile validation rules. | Replace with the exact official Mayush logo and no qualifier. |
| MINOR | Product flow | `Retour à l’accueil` destination/auth state is not defined. | Confirm guest-home behavior and preserve a predictable back stack. NEEDS PRODUCT DECISION. |

### Canonical recommendation
Reject as a full implementation reference; recreate the state using the official logo and the composition of `04-account-created-success-fr.png`.

---

## 04-registration-form-ar.png

Folder: `04-auth/`
Screen purpose: Create a Mayush account in Arabic
Probable native route: `Auth/Register`
Language: Arabic
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
An RTL registration form collecting identity, contact details, password confirmation and legal acceptance.

### Visible UI structure
- Header: official logo; no back control
- Main content: RTL title and account-benefit copy
- Cards: none
- Forms: name, e-mail, `+212` phone, password, confirmation and terms checkbox
- Primary action: create account
- Secondary actions: sign in and legal links
- Navigation: no bottom navigation
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: approved cream/orange/navy.
- Typography: Arabic is legible and right-aligned.
- Icons: user, mail, phone and lock icons are coherent.
- Buttons: primary orange style is consistent.
- Cards: inputs follow rounded foundation shapes.
- Spacing: dense vertical stack is vulnerable to keyboard and larger text.
- Shadows: subtle.

### UX validation
- Required information and legal acknowledgement are visible.
- All fields use placeholder-like labels that disappear on input; password fields lack visibility toggles and requirements.
- No mirrored back control is present.
- Phone entry correctly shows `+212`, but name/e-mail/phone/password need semantic keyboards, autofill, inline error messages and next/submit focus flow.
- Consent checkbox/link targets must remain at least 44/48 and wrap correctly in RTL.

### Native implementation usability
Feasible with a keyboard-aware form and shared inputs, but the current poster-density and missing labels/password behavior require rework.

### Reusable components identified
- `RTLAuthHeader`
- `FormInput`
- `PhoneInput`
- `PasswordInput`
- `LegalCheckboxRow`
- `PrimaryButton`

### Dynamic backend data required
- Registration validation result
- Account-creation result
- Current terms/privacy versions
- Phone/e-mail verification requirements

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Form accessibility | Inputs depend on placeholders; passwords have no visibility control or requirements. | Add persistent labels, secure-entry toggles, policy hints and inline errors. |
| MAJOR | Mobile usability | Five fields plus consent are composed as a fixed page that will collide with keyboard/Dynamic Type. | Use keyboard-aware scrolling, sticky CTA only with proper insets, and focus-to-error behavior. |
| MINOR | RTL navigation | No mirrored back control is visible. | Add predictable right-edge RTL back navigation. |

### Canonical recommendation
Correct form semantics, keyboard layout and RTL navigation before use; retain `+212` and overall RTL alignment.

---

## 04-registration-form-fr.png

Folder: `04-auth/`
Screen purpose: Create a Mayush account in French
Probable native route: `Auth/Register`
Language: French
Screen type:
- Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
French account registration with Moroccan phone context and mandatory legal acceptance.

### Visible UI structure
- Header: back control and official logo
- Main content: title, benefits copy and dense registration form
- Cards: none
- Forms: full name, e-mail, `+212` phone, password, confirmation and terms checkbox
- Primary action: `Créer mon compte`
- Secondary actions: sign in and legal links
- Navigation: no bottom navigation
- Overlays: none

### Brand validation
- Logo: official and undistorted.
- Colors: approved cream, orange, navy and white.
- Typography: hierarchy is clear; legal line is dense.
- Icons: form icons and back arrow are consistent outlines.
- Buttons: primary orange CTA follows the foundation.
- Cards: form surfaces use the right radius and shadow direction.
- Spacing: visually compressed for a long form.
- Shadows: soft and consistent.

### UX validation
- Morocco `+212`, legal acknowledgement and login escape are present.
- Fields rely on placeholders instead of persistent labels and no validation/error/helper states are shown.
- Five inputs, legal copy, CTA and footer cannot reliably fit once the keyboard opens or Dynamic Type increases.
- Password requirements, autofill, password manager, show/hide state and mismatch recovery must be explicit.
- Registration CTA should remain disabled until required valid values and consent exist.

### Native implementation usability
Implementable with reusable inputs and keyboard-aware scrolling, but should not be followed as a fixed single-viewport poster.

### Reusable components identified
- `AuthHeader`
- `FormInput`
- `PhoneInput`
- `PasswordInput`
- `LegalCheckboxRow`
- `PrimaryButton`

### Dynamic backend data required
- Registration validation result
- Account-creation result
- Current terms/privacy versions
- Verification requirements

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Form accessibility | Placeholder-only inputs lose their labels after entry. | Add persistent labels, hints and inline error placement. |
| MAJOR | Mobile usability | Dense fixed layout will break with keyboard, small devices and large Dynamic Type. | Use a keyboard-aware scroll layout and keep focused fields/CTA visible. |
| MAJOR | Password UX | Requirements, mismatch and autofill/password-manager behavior are absent. | Reuse the validated password component and policy card. |

### Canonical recommendation
Correct before use; keep the field sequence and `+212` but combine with password behavior from `04-create-new-password-requirements-fr.png`.

---

## 04-welcome-sign-in-create-account-guest-fr.png

Folder: `04-auth/`
Screen purpose: Offer sign-in, account creation or guest browsing at entry
Probable native route: `Auth/Welcome`
Language: French
Screen type:
- Full page

### Status
APPROVED_WITH_MINOR_FIXES

### Confidence
High

### What the screen represents
The buyer auth landing page after onboarding or when authentication is requested without a hard gate.

### Visible UI structure
- Header: official logo over a Moroccan-contemporary interior hero
- Main content: large rounded welcome panel and benefit copy
- Cards: white auth-choice panel
- Forms: none
- Primary action: `Se connecter`
- Secondary actions: create account, continue as guest, terms and privacy links
- Navigation: no bottom navigation, appropriate for entry/auth
- Overlays: none

### Brand validation
- Logo: official and prominent without added wording.
- Colors: warm cream, orange, navy, white and beige are strongly aligned.
- Typography: clear hierarchy and all UI copy is French.
- Icons: chair, lock, account and guest icons are coherent outlines.
- Buttons: primary, outlined and text hierarchy is correct.
- Cards: rounded elevated white panel matches the system.
- Spacing: spacious and premium; bottom legal links are close to the safe area.
- Shadows: soft and consistent.

### UX validation
- Three paths are explicit and guest browsing is not hidden.
- Legal links are available without requiring account creation.
- The hero and panel must adapt to landscape, compact height and Dynamic Type; legal links cannot be clipped by the home indicator.
- Preserve intended destination when the user returns from login/registration.

### Native implementation usability
Strong native reference using `ImageBackground`, safe-area container, scrollable card and shared buttons. All copy/actions must remain live native content.

### Reusable components identified
- `AuthWelcomeHero`
- `MayushLogo`
- `AuthChoiceCard`
- `PrimaryButton`
- `OutlinedButton`
- `TextLink`

### Dynamic backend data required
- Authentication state
- Intended post-auth destination
- Guest-mode availability
- Current legal document links

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MINOR | Safe area | Legal links are very close to the bottom edge. | Apply bottom safe-area padding and scroll fallback. |
| MINOR | Responsive layout | Tall fixed hero/card composition may not survive compact height or large type. | Use responsive hero sizing and a scrollable content panel. |

### Canonical recommendation
Use as the canonical French auth-welcome reference after safe-area and responsive adjustments.

---

## Folder assessment

- Reviewed: 20/20 screenshots.
- Status totals: 0 APPROVED, 8 APPROVED_WITH_MINOR_FIXES, 10 NEEDS_REWORK, 0 REFERENCE_ONLY, 2 REJECTED, 0 DUPLICATE_ALTERNATIVE.
- Highest-priority corrections: reject `04-login-loading-state-fr.png` for `+33`/buyer wording; reject `04-password-changed-success-fr.png` for `BUYER APP`; correct EUR/SAR contamination in both auth-prompt backgrounds; mirror Arabic navigation; standardize conditional e-mail/phone input, keyboard/autofill, password and OTP behavior.
- Canonical bases: `04-welcome-sign-in-create-account-guest-fr.png`, `04-login-email-phone-password-fr.png`, `04-login-error-incorrect-credentials-fr.png`, `04-forgot-password-enter-email-fr.png`, `04-create-new-password-requirements-fr.png`, `04-otp-phone-verification-fr.png`, `04-otp-phone-verification-ar.png`, and `04-account-created-success-fr.png`.
