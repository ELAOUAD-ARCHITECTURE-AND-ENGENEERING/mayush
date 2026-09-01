# 01-entry Validation Report

> **Fact-check scope:** Currency and address examples are accepted variations. Do not treat them as validation defects by themselves; [fact-check-correction.md](./fact-check-correction.md) supersedes earlier currency/address severity notes.

**Folder:** `01-entry/`
**Total screenshots:** 9
**Date validated:** 2026-08-02

---

## Extracted Foundation Rules (Source of Truth)

Before validating screens, the following rules were extracted from `00-foundation/` and `assetsl/`:

- **Logo:** The canonical mark is the unmodified “MAYUSH DESIGN” primary/header logo. Do not use the transliterated Arabic mark or the `BUYER APP` lockup shown as an alternative on `assetsl/12-brand-assets.png`.
- **Typography:** Playfair Display for display headings; Inter for French UI/body; Tajawal or Cairo for Arabic UI. Poppins appears on alternate boards and is not canonical until a product decision resolves the conflict.
- **Primary orange:** `#D97434`; darker interaction token `#C46524`. Alternate `#FF8A00` and `#FFBA00` swatches on some boards are conflicting references, not approved replacements.
- **Navy/ink:** Foundation boards range from `#1A1A1A` to deep navy; use the deep navy appearance consistently for UI text/icons and reserve near-black for highest-emphasis text.
- **Background:** Warm cream `#F5F1E8`; beige `#F0E8DD`; cards/surfaces white.
- **Cards:** White, subtle border or soft elevation, generally 12–16 px radius.
- **Buttons:** Orange primary CTA with white text and a reusable 48–56 dp native height; outlined/secondary actions remain subordinate.
- **Bottom nav (when present):** Accueil, Catégories, Favoris, Panier, Compte — 5 tabs
- **Currency:** MAD only
- **Phone prefix:** +212
- **Country:** Morocco
- **Decorative elements:** Plants, arches, pendant lamps — Moroccan-contemporary style
- **Native usability:** Minimum 44 pt (iOS) / 48 dp (Android) touch targets, safe-area clearance, Dynamic Type-safe wrapping, and no embedded transactional text or currency inside decorative assets.
- **Reference-board defects to exclude:** USD/INR examples, Indian `+91` phone/address data, Saudi/other currency, duplicated asset names, and the unmirrored RTL back-arrow sample.

---

## 01-splash-screen-logo.png

Folder: `01-entry/`
Screen purpose: App launch splash screen
Probable native route: `SplashScreen`
Language: Neutral (no text beyond logo)
Screen type: Loading state

### Status
APPROVED

### Confidence
High

### What the screen represents
The initial splash screen shown when the app launches. Displays the Mayush Design logo centered on a warm cream background with decorative interior elements (arch, pendant lamp, side table, plant). An orange dot loading spinner is visible below the logo.

### Visible UI structure
- Logo centered vertically
- Decorative background with interior design elements
- Loading indicator (orange dots)
- No navigation, no header, no actions

### Brand validation
- Logo: Correct "MAYUSH DESIGN" rendering with textured letterforms ✅
- Colors: Cream background, orange/navy logo ✅
- Typography: N/A (no body text)
- Icons: N/A
- Buttons: N/A
- Spacing: Generous, premium feel ✅
- Shadows: Soft, appropriate ✅

### UX validation
- Clear user goal: Wait for app to load ✅
- Primary action: None required (auto-transition) ✅
- Navigation logic: N/A ✅
- Accessibility: Loading spinner may need screen reader announcement

### Native implementation usability
Fully implementable. Simple centered layout with animated loading indicator. Background can use a static image or composed View elements.

### Reusable components identified
- MayushLogo
- LoadingSpinner
- SplashBackground

### Dynamic backend data required
None

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| — | — | No screenshot-level issue found | Announce the loading state to screen readers and respect reduced-motion preferences during implementation |

### Canonical recommendation
Use directly as the main reference for the splash screen.

---

## 01-language-selection-french-arabic.png

Folder: `01-entry/`
Screen purpose: First-launch language selection
Probable native route: `LanguageSelection`
Language: Bilingual (FR + AR)
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
First-time language selection screen. User chooses between French (with France flag) and Arabic (with Morocco flag). French is pre-selected. "Continuer" button to proceed.

### Visible UI structure
- Header: Mayush logo
- Title: "Choisissez votre langue" + Arabic translation
- Subtitle: Bilingual explanation
- Two language option cards with flags and checkmarks
- Primary action: "Continuer →" orange button
- Footer tagline: "Votre expérience, votre langue."

### Brand validation
- Logo: Correct ✅
- Colors: Orange primary, cream background, white cards ✅
- Typography: Poppins for French, Arabic font for Arabic text ✅
- Buttons: Rounded orange, consistent with foundation ✅
- Cards: White with subtle border, rounded ✅
- Spacing: Clean and spacious ✅

### UX validation
- Clear user goal: Select language ✅
- Primary action: "Continuer" prominently placed ✅
- Navigation logic: Pre-selection of French is reasonable ✅
- Form usability: Large tap targets for language options ✅
- Accessibility: Option cards are large enough for touch, but flags conflate language with nationality and are not reliable language identifiers.

### Native implementation usability
Straightforward to implement. Two selectable radio cards, one CTA button. State management for selected language.

### Reusable components identified
- MayushLogo
- SelectableCard
- PrimaryButton
- ScreenContainer (cream background)

### Dynamic backend data required
- Available languages (could be static)

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| MAJOR | Localization / Morocco context | The Arabic option uses the Saudi Arabian flag, contradicting the required Moroccan country context | Replace it with a neutral language glyph or a Morocco-appropriate marker; do not represent Arabic as Saudi nationality |
| MINOR | UX | The French and Saudi flags treat languages as countries, which is ambiguous for Moroccan users | Prefer `FR` / `AR`, localized language names, or neutral language icons |
| MINOR | Localization | The footer tagline is French-only on an otherwise bilingual first-run screen | Add the Arabic equivalent or remove the nonessential tagline |

### Canonical recommendation
Correct the Arabic language marker and bilingual footer before use. The card layout and CTA hierarchy remain the best reference for this route.

---

## 01-loading-screen-preparing-experience.png

Folder: `01-entry/`
Screen purpose: Post-language-selection loading screen
Probable native route: `LoadingScreen`
Language: FR
Screen type: Loading state

### Status
APPROVED

### Confidence
High

### What the screen represents
Transitional loading screen after language selection. Shows Mayush logo, orange dot loading indicator, and French text "Préparation de votre expérience..."

### Visible UI structure
- Logo centered
- Loading dots animation
- Status text below
- Cream background with decorative elements (lamp, geometric pattern)

### Brand validation
- Logo: Correct ✅
- Colors: Consistent cream/orange ✅
- Typography: Poppins, appropriate size ✅
- Spacing: Generous ✅

### UX validation
- Clear purpose: System is preparing ✅
- Auto-transition expected ✅

### Native implementation usability
Simple implementation. May need an Arabic variant with translated text.

### Reusable components identified
- MayushLogo
- LoadingSpinner
- StatusText

### Dynamic backend data required
None

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| — | — | No screenshot-level issue found | Keep the status text dynamic so the same native screen can localize after language selection |

### Canonical recommendation
Use directly as the main reference for FR. Create AR variant for Arabic users.

---

## 01-onboarding-step1-discover-interior-fr.png

Folder: `01-entry/`
Screen purpose: Onboarding step 1 of 3 (French)
Probable native route: `Onboarding` (step 1)
Language: FR
Screen type: Full page

### Status
APPROVED

### Confidence
High

### What the screen represents
First onboarding slide. Hero interior photo showing a modern Moroccan-style living room. Title "Découvrez un intérieur qui vous ressemble." Subtitle about style and quality. "Continuer" button and "Passer" (skip) link.

### Visible UI structure
- Header: Logo + "Passer" skip link (top right)
- Step indicator: "1/3"
- Hero image: Living room interior
- Title text (display heading)
- Subtitle text
- Primary action: "Continuer" orange button

### Brand validation
- Logo: Correct ✅
- Colors: Orange button, cream background, navy text ✅
- Typography: Display heading (Playfair-style), Poppins body ✅
- Image style: Moroccan-contemporary interior, consistent with brand ✅
- Button: Rounded orange, correct ✅

### UX validation
- Clear user goal: Learn about the app ✅
- Primary action: "Continuer" clear ✅
- Skip option: "Passer" available ✅
- Step indicator: Clear pagination ✅
- Scroll: Not needed, content fits viewport ✅

### Native implementation usability
Standard onboarding carousel/swiper. Fully implementable with react-native-swiper or FlatList.

### Reusable components identified
- OnboardingSlide
- MayushLogo
- PrimaryButton
- SkipLink
- StepIndicator

### Dynamic backend data required
None (static content)

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| — | — | No issues found | — |

### Canonical recommendation
Use directly as the main reference for onboarding step 1 FR.

---

## 01-onboarding-step1-discover-interior-ar.png

Folder: `01-entry/`
Screen purpose: Onboarding step 1 of 3 (Arabic)
Probable native route: `Onboarding` (step 1)
Language: AR
Screen type: Full page

### Status
APPROVED

### Confidence
High

### What the screen represents
Arabic RTL version of onboarding step 1. Same living room photo. Title "اكتشف تصميماً يعكس ذوقك". "تخطى" (skip) correctly placed top LEFT for RTL. "متابعة" (continue) button.

### Visible UI structure
- Header: Logo + "تخطى" skip (top left — correct RTL) ✅
- Step indicator: "1/3"
- Hero image (same as FR)
- Arabic title and subtitle (right-aligned)
- Primary action: "متابعة" orange button

### Brand validation
- Logo: Standard "MAYUSH DESIGN" English logo preserved ✅
- Colors: Consistent ✅
- Typography: Arabic font, appropriate sizing ✅
- Button: Orange rounded, white text ✅

### UX validation
- RTL layout: Skip button correctly mirrored to left ✅
- Text alignment: Right-aligned Arabic text ✅
- Step indicator position: Appropriate ✅

### Native implementation usability
Same OnboardingSlide component with RTL support via I18nManager.

### Reusable components identified
Same as FR version with RTL props

### Dynamic backend data required
None

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| — | — | No issues found | — |

### Canonical recommendation
Use directly as the Arabic reference for onboarding step 1.

---

## 01-onboarding-step2-choose-with-confidence-fr.png

Folder: `01-entry/`
Screen purpose: Onboarding step 2 of 3 (French)
Probable native route: `Onboarding` (step 2)
Language: FR
Screen type: Full page

### Status
APPROVED

### Confidence
High

### What the screen represents
Second onboarding slide showing a floating product card overlay ("Canapé Riviera") on the interior background. Title: "Choisissez en toute confiance." Demonstrates product browsing capability.

### Visible UI structure
- Header: Logo + "Passer" skip
- Step indicator: "2/3"
- Hero image with product card overlay
- Product card shows: name, color variants, features, favorite icon
- Title + subtitle text
- "Continuer" button

### Brand validation
- Logo: Correct ✅
- Product card: White, rounded, shadow — consistent with foundation ✅
- Colors: Orange accents, navy text ✅
- Button: Consistent ✅

### UX validation
- Demonstrates app functionality effectively ✅
- Product card preview is illustrative, not interactive ✅
- Clear progression ✅

### Native implementation usability
Static illustration slide. The floating product card is decorative for onboarding, not a functional component here.

### Reusable components identified
- OnboardingSlide
- ProductCardPreview
- StepIndicator
- PrimaryButton

### Dynamic backend data required
- None; localized onboarding copy and imagery are bundled content

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| — | — | No issues found | — |

### Canonical recommendation
Use directly as the main reference for onboarding step 2 FR.

---

## 01-onboarding-step2-choose-with-confidence-ar.png

Folder: `01-entry/`
Screen purpose: Onboarding step 2 of 3 (Arabic)
Probable native route: `Onboarding` (step 2)
Language: AR
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Arabic version of onboarding step 2. Shows product cards with MAD prices. Title: "اختر بكل ثقة". Product cards show furniture items with Arabic names and prices.

### Visible UI structure
- Header: Arabic-style logo "مايوش" (NOT the standard MAYUSH DESIGN logo)
- Step indicator: "2/3" (displayed as "٢/٣" or similar)
- Product cards with prices in Saudi riyals (`ر.س`), not MAD ❌
- "المفضلة" favorites label
- "متابعة" button with arrow

### Brand validation
- Logo: **REPLACED with Arabic transliteration "مايوش"** ❌ MAJOR
- Colors: Consistent orange/cream ✅
- Product cards: White, rounded ✅
- Currency: Saudi riyal is shown on all three product cards ❌ CRITICAL
- Button: Orange with arrow ✅

### UX validation
- RTL layout: Generally correct ✅
- Skip button: "تخطى" top left ✅
- Product showcase: Effective ✅

### Native implementation usability
The composition is possible, but it is not safe to reproduce directly. The logo, currency, and embedded mini-interface content must be rebuilt from approved native/localized elements rather than baked into a language-specific poster.

### Reusable components identified
- OnboardingSlide
- ProductCardPreview
- StepIndicator
- RTLPrimaryButton

### Dynamic backend data required
- None; any visible sample product content must come from approved localized fixture/content data rather than an embedded poster

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Currency / Morocco context | Product prices use Saudi riyals (`ر.س`) instead of MAD | Replace every price with realistic MAD values and keep `MAD` readable in the RTL line |
| MAJOR | Branding | The official mark is replaced with an Arabic transliteration | Use the exact primary/header `MAYUSH DESIGN` asset from `assetsl/12-brand-assets.png` |
| MAJOR | Native feasibility | Localized product names, prices, dimensions and actions are embedded in a dense decorative mini-interface that looks interactive | Rebuild the visible cards as native/non-interactive illustration layers or simplify the hero so copy and currency remain localizable |
| MINOR | Language | The comparison card contains the English abbreviation `VS` | Replace it with Arabic copy or a language-neutral comparison symbol |

### Canonical recommendation
Correct the logo, currency, localization, and illustrative mini-UI before use. Use the French step-2 screen as the canonical structural reference in the meantime.

---

## 01-onboarding-step3-order-simply-fr.png

Folder: `01-entry/`
Screen purpose: Onboarding step 3 of 3 (French)
Probable native route: `Onboarding` (step 3)
Language: FR
Screen type: Full page

### Status
APPROVED

### Confidence
High

### What the screen represents
Final onboarding slide. Shows a Mayush-branded delivery box in a styled interior. Title: "Commandez simplement." Subtitle about fluid experience from product choice to delivery. "Commencer" (Start) button — correctly different from "Continuer" on previous steps.

### Visible UI structure
- Header: Logo + "Passer" skip
- Step indicator: "3/3"
- Hero image: Delivery box with Mayush branding
- Title + subtitle
- Primary action: "Commencer" orange button

### Brand validation
- Logo: Correct ✅
- Delivery box branding: Mayush logo on box ✅
- Colors: Consistent ✅
- Button: Orange, "Commencer" appropriately different for final step ✅

### UX validation
- Clear conclusion to onboarding ✅
- "Commencer" signals transition to app ✅
- No bottom navigation (correct for onboarding) ✅

### Native implementation usability
Simple final slide with different CTA text. Same OnboardingSlide component.

### Reusable components identified
- OnboardingSlide
- StepIndicator
- PrimaryButton

### Dynamic backend data required
- None; localized onboarding copy and imagery are bundled content

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| — | — | No issues found | — |

### Canonical recommendation
Use directly as the main reference for onboarding step 3 FR.

---

## 01-onboarding-step3-order-simply-ar.png

Folder: `01-entry/`
Screen purpose: Onboarding step 3 of 3 (Arabic)
Probable native route: `Onboarding` (step 3)
Language: AR
Screen type: Full page

### Status
NEEDS_REWORK

### Confidence
High

### What the screen represents
Arabic version of final onboarding slide. Shows a phone mockup displaying the Mayush app with a product page. Title: "تسوق بكل سهولة". "ابدأ الآن" (Start now) button.

### Visible UI structure
- Header: Logo (appears to be MAYUSH DESIGN standard) + "تخطى" skip
- Step indicator: "3/3"
- Phone mockup showing app interface
- Arabic title + subtitle
- "ابدأ الآن" button

### Brand validation
- Logo: Standard MAYUSH DESIGN appears at top ✅
- Colors: Consistent ✅
- Phone mockup: Contains a Saudi-riyal price and a four-item buyer navigation that omits Favoris ❌
- Button: Orange, correct ✅

### UX validation
- RTL outer layout: Correct; the embedded phone navigation is not a valid final buyer navigation.
- Final step CTA: "ابدأ الآن" (Start now) appropriate ✅
- Visual approach differs from FR (phone mockup vs delivery box) — acceptable variation

### Native implementation usability
Implementable. Same component with different illustration asset.

### Reusable components identified
- OnboardingSlide
- StepIndicator
- RTLPrimaryButton
- AppPreviewIllustration

### Dynamic backend data required
- None; the corrected sample price/navigation must remain approved localized illustration content

### Issues

| Severity | Category | Issue | Recommended correction |
|---|---|---|---|
| CRITICAL | Currency / Morocco context | The product price inside the phone mockup uses Saudi riyals (`ر.س`) | Replace it with a realistic `MAD` price and preserve readable bidi ordering |
| MAJOR | Navigation | The phone mockup shows only four bottom tabs and omits Favoris | Show exactly Accueil, Catégories, Favoris, Panier and Compte, mirrored correctly for RTL |
| MINOR | Cross-language consistency | The French version uses a delivery-box illustration while Arabic uses an implementation-like phone mockup | Prefer the same language-neutral delivery concept or explicitly approve this divergence as a product decision |

### Canonical recommendation
Use the French step-3 screen as the canonical structural reference. Correct the Arabic mockup currency and five-tab navigation before using this image.

---

## 01-entry Folder Summary

| File | Status | Confidence | Critical | Major | Minor |
|---|---|---|---|---|---|
| 01-splash-screen-logo.png | APPROVED | High | 0 | 0 | 0 |
| 01-language-selection-french-arabic.png | NEEDS_REWORK | High | 0 | 1 | 2 |
| 01-loading-screen-preparing-experience.png | APPROVED | High | 0 | 0 | 0 |
| 01-onboarding-step1-discover-interior-fr.png | APPROVED | High | 0 | 0 | 0 |
| 01-onboarding-step1-discover-interior-ar.png | APPROVED | High | 0 | 0 | 0 |
| 01-onboarding-step2-choose-with-confidence-fr.png | APPROVED | High | 0 | 0 | 0 |
| 01-onboarding-step2-choose-with-confidence-ar.png | NEEDS_REWORK | High | 1 | 2 | 1 |
| 01-onboarding-step3-order-simply-fr.png | APPROVED | High | 0 | 0 | 0 |
| 01-onboarding-step3-order-simply-ar.png | NEEDS_REWORK | High | 1 | 1 | 1 |

**Totals:**
- APPROVED: 6
- APPROVED_WITH_MINOR_FIXES: 0
- NEEDS_REWORK: 3
- REFERENCE_ONLY: 0
- REJECTED: 0
- DUPLICATE_ALTERNATIVE: 0

**Key findings:** `01-language-selection-french-arabic.png` uses a Saudi flag for Arabic; `01-onboarding-step2-choose-with-confidence-ar.png` uses Saudi riyals and replaces the official logo; and `01-onboarding-step3-order-simply-ar.png` repeats Saudi-riyal pricing inside a four-tab mockup. None of these three images should guide implementation without correction.

### Duplicate groups
No duplicates in this folder. FR and AR screens are intentional language variants sharing the same route.
