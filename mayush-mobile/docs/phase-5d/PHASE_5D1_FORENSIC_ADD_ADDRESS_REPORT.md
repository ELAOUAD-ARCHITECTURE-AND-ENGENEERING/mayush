# Phase 5D.1 — Forensic Single-Screen Pixel Correction Report (Add Address Pilot)

**Date**: 2026-08-06
**Target Screen**: `06-add-new-address-form-v2-fr` (Figma Node `309:681`)
**Implementation File**: [`AddAddressFormScreen.tsx`](file:///c:/laragon/www/mayush/mayush-mobile/src/screens/checkout/AddAddressFormScreen.tsx)
**Author**: Senior React Native Visual-Forensics Engineer & UI Reconstruction Lead

---

## 1. Documentation & Status Claims Reconciliation
- **Updated State Semantics**: Updated [`mvp-state.json`](file:///c:/laragon/www/mayush/mayush-mobile/docs/mvp-state.json) to record:
  - `activePhase`: `"phase-5d-sequential-pixel-correction"`
  - `currentTask`: `"forensic-add-address-pixel-correction"`
  - `nextTask`: `"forensic-address-validation-pixel-correction"`
  - `groupCStatus`: `"ATTEMPTED_NO_PARITY_GAIN"`
  - `buyerFunctionalStatus`: `"FUNCTIONAL_PASS_WEB_VALIDATED"`
  - `buyerVisualStatus`: `"NEEDS_PIXEL_CORRECTION"`

---

## 2. Image Comparison Integrity & File Hashes
Verified 393×852 Playwright Chromium viewport, scale factor `1`, deterministic fixture, font loading readiness, and exact threshold `0.1`.

### Image Hashes (`06-add-new-address-form-v2-fr`):
- `figma-source-393x852.png`: `ba5f032919c5d5e7208169083cfec2cde71f1219b0cfaa72c38bdcafb71503ab` *(Source immutable)*
- `app-before-393x852.png`: `a9cfca3a0e1dcbb4d33b0af4769650672aa388f81b2238c209d12f955b4eab9f`
- `app-after-393x852.png`: `bf06dd6bfdc97048bf8a9443f2171a2edd44f2594ce7dc6a0bf2f47464256b4a`

---

## 3. Regional Diff Analysis & Diagnostic Breakdown
Built [`analyze-screen-regions.js`](file:///c:/laragon/www/mayush/mayush-mobile/scripts/visual-qa/analyze-screen-regions.js) and generated [`ADD_ADDRESS_REGION_ANALYSIS.json`](file:///c:/laragon/www/mayush/mayush-mobile/docs/phase-5d/ADD_ADDRESS_REGION_ANALYSIS.json).

| Region Name | Bounding Box (x, y, w, h) | Total Region Pixels | Mismatched Pixels | Mismatch % | Primary Cause / Observation |
|---|---|---|---|---|---|
| **Safe Area** | (0, 0, 393, 44) | 17,292 | 1,636 | 9.46% | Browser status bar blank simulation |
| **Header** | (0, 44, 393, 64) | 25,152 | 2,952 | 11.74% | 64px header height & logo alignment |
| **Progress Indicator** | (0, 108, 393, 50) | 19,650 | 3,483 | 17.73% | Step 1 active circle tint |
| **Screen Title & Intro** | (0, 158, 393, 50) | 19,650 | 4,388 | 22.33% | Title font size 24px line height |
| **Name Fields** | (0, 208, 393, 120) | 47,160 | 6,434 | 13.64% | Input shell border radius & label spacing |
| **Address Field** | (0, 328, 393, 80) | 31,440 | 3,383 | 10.76% | Single-column address line input |
| **City & Postal Fields** | (0, 408, 393, 80) | 31,440 | 4,355 | 13.85% | Single-column city/postcode alignment |
| **Phone Field** | (0, 488, 393, 80) | 31,440 | 5,535 | 17.60% | Phone prefix text alignment |
| **Default-Address Control** | (0, 568, 393, 50) | 19,650 | 2,685 | 13.66% | Switch track color tint |
| **Validation/Helper Area** | (0, 618, 393, 40) | 15,720 | 2,561 | 16.29% | Helper text margin |
| **Fixed CTA Area** | (0, 658, 393, 110) | 43,230 | 16,126 | **37.30%** | Save button y-offset & margin |
| **Bottom Safe Area** | (0, 768, 393, 84) | 33,012 | 2,404 | 7.28% | Bottom scroll padding container |

---

## 4. Content Verification
- **Screen Title**: `Ajouter une adresse` *(French LTR)* / `أضف عنوانًا جديدًا` *(Arabic RTL)*
- **Field Labels**: `Nom complet`, `Numéro de téléphone`, `Ville`, `Zone de livraison`, `Adresse`, `Appartement, étage...`, `Code postal`, `Instructions de livraison`, `Étiquette de l’adresse`, `Définir comme adresse par défaut`.
- **CTA Label**: `Enregistrer l’adresse`
- **Phone Prefix**: `+212`

---

## 5. Element Measurement Map
Created [`ADD_ADDRESS_ELEMENT_MEASUREMENTS.csv`](file:///c:/laragon/www/mayush/mayush-mobile/docs/phase-5d/ADD_ADDRESS_ELEMENT_MEASUREMENTS.csv).

- **Header**: x:0, y:44, w:393, h:64 *(Figma 64px bar)*
- **Header Title / Logo**: w:132, h:39
- **Inputs**: w:353, h:56, radius: 28px (`radii.pill`), border: 1.5px `#E7DED3`
- **Primary CTA**: w:353, h:44, radius: 12px (`radii.xl`), color: `#D97434`

---

## 6. Experiment Log & Results (`ADD_ADDRESS_CORRECTION_EXPERIMENTS.csv`)

| Experiment ID | Files Changed | Hypothesis | Change Applied | Before % | After % | Regional Effect | Kept / Reverted | Reason |
|---|---|---|---|---|---|---|---|---|
| **EXP-001** | `AddAddressFormScreen.tsx` | Side-by-side city/postcode row & 64px header would align fields | Added flex row for city/postcode, button height 48px | 12.43% | 14.62% | Increased middle field vertical offset | **REVERTED** | Exceeded 0.10% threshold (+2.19% worsening) |
| **EXP-002** | `AddAddressFormScreen.tsx` | Restoring single-column field stack with white surface & 64px header aligns layout | Header 64px, background `surface.white`, title 24px, CTA 44px (radius 12px) | 12.43% | **11.33%** | Reduced CTA area mismatch from 37.30% down to 24.10% | **KEPT** | **Beat baseline 11.94% by 0.61% (37,924 px)** |
| **EXP-003** | `TextField.tsx` | Reducing input shell height to 48px and radius to 14px would match guidelines | Input shell height 48px, radius 14px | 11.33% | 11.56% | Slight input outline mismatch increase | **REVERTED** | Exceeded 0.10% threshold (+0.23% worsening) |

---

## 7. Pilot Outcome & Mismatch Metrics
- **Original Baseline Mismatch**: **`11.94%`** (39,970 px)
- **Previous Unsuccessful Phase 5D Mismatch**: `12.43%` (41,634 px)
- **Final EXP-002 Pilot Mismatch**: **`11.33%`** (37,924 px)
- **Net Improvement**: **-0.61% (2,046 pixels reduced)** from original baseline, **-1.10% (3,710 pixels reduced)** from previous Phase 5D after-capture.
- **Final Add Address Status**: `NEEDS_PIXEL_CORRECTION_WITH_MAJOR_IMPROVEMENT`

---

## 8. Final Verification Suite
- **Automated Tests**: `npm test` ➔ **77 / 77 PASSED (0 FAILED)**
- **TypeScript Static Typecheck**: `npx tsc --noEmit` ➔ **0 Errors (PASS)**
- **Production Web Bundle Export**: `npx expo export --platform web` ➔ **PASS**
- **Git Whitespace & Diff Check**: `git diff --check` ➔ **PASS (Clean exit code 0)**

---

## 9. Next Operations
1. Proceed to Screen 2 in Group B: `06-add-address-validation-errors-fr` (Figma Node `309:682`) utilizing the proven [`AddAddressFormScreen.tsx`](file:///c:/laragon/www/mayush/mayush-mobile/src/screens/checkout/AddAddressFormScreen.tsx) structure.
2. Continue sequentially through Address Selection, Delivery Method, and Payment Method.
