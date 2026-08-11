# Mayush Mobile — Font & Typography Rendering Audit (Phase 5D)

**Date**: 2026-08-06
**Scope**: Font Loading, Family Mapping, Weights, and Playwright Web Rasterization

---

## 1. Runtime Font Assets & Families
- **Primary Body Font (Latin)**: `Inter`
  - `Inter_400Regular` (Regular, 400)
  - `Inter_500Medium` (Medium, 500)
  - `Inter_600SemiBold` (SemiBold, 600)
  - `Inter_700Bold` (Bold, 700)
- **Display Header Font (Serif Accent)**: `Playfair Display`
  - `PlayfairDisplay_400Regular`
  - `PlayfairDisplay_600SemiBold`
  - `PlayfairDisplay_700Bold`
- **Arabic Font (RTL Context)**: `Tajawal`
  - `Tajawal_400Regular`
  - `Tajawal_500Medium`
  - `Tajawal_700Bold`

---

## 2. Font Loading & Visual QA Readiness
- `App.tsx` guards initialization via `useFonts`.
- `VisualQaApp.tsx` mounts under `ThemeProvider`, allowing Google Web Fonts to load before emitting `data-visual-qa-ready="true"`.
- Playwright Chromium waits for network idle and the DOM readiness marker before taking 393×852 screenshots.

---

## 3. Font Family Usage Across Scoped Screens
- Checkout, Address, Delivery, Payment, Auth, and Order processing screens rely on `Inter` for crisp readability on high-density mobile displays.
- Section titles use 700 bold weight (`fontSizes.xl` / `fontSizes.xxl`).
- Standard buttons use 600 semi-bold weight.
