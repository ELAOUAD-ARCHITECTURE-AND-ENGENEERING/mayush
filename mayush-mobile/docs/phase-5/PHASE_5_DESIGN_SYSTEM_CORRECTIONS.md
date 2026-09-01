# Phase 5 Design System & UI Corrections Log

## Overview

This log records every refinement made to shared design-system components, tokens, and variants during the reconstruction of the 7 Phase 5 screens against original reference screenshots.

---

## 1. Corrections Log

| Component | Existing Problem | Source Screenshot | Observed Source Appearance | Previous Implementation | Correction Made | Files Changed | Screens Affected | Validation Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `MayushLogo` | Static local image only | `01-entry/01-splash-screen-logo.png` | Centered logo on cream canvas | Local asset only | Added dynamic URL rendering from API with local asset fallback | `MayushLogo.tsx`, `ThemeProvider.tsx` | Splash, Home, Categories | `VERIFIED` |
| `ProductCard` | Fixed height ratio gap | `02-discovery/02-home-hero-new-arrivals-best-sellers-fr.png` | 1:1 image thumbnail with badges and price | Rigid height layout | Refactored flex layout with 1:1 image ratio & discount badge overlay | `ProductCard.tsx` | Home, Product List | `VERIFIED` |
| `PrimaryButton` | Fixed button radius | `01-entry/01-language-selection-french-arabic.png` | Full-width orange CTA with 12px radius & 48px height | 16px radius | Standardized border radius to 12px & height to 48px | `PrimaryButton.tsx`, `radii.ts` | Language, Product Details | `VERIFIED` |
| `TextField` | Missing icon support | `00-foundation/00-controls-form-components-icons.png` | Search input with left search icon | Simple text input | Added `leftIcon` prop & focus outline styling | `TextField.tsx` | Home, Product List | `VERIFIED` |
| `BottomTabBar` | Fixed tab ordering in RTL | `11-arabic-rtl/11-home-ar.png` | Right-to-left reversed tab order | Static LTR order | Dynamically reverses tab order when `isRTL === true` | `BottomTabBar.tsx` | Home, Categories | `VERIFIED` |
