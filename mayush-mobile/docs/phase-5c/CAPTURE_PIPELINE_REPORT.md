# Mayush Mobile — 393×852 Capture Pipeline Report

**Date**: 2026-08-06
**Pipeline**: `scripts/visual-qa/capture-phase5b.js`
**Tooling**: Playwright Chromium Headless + PNGjs + Pixelmatch

---

## 1. Pipeline Overview
The capture pipeline serves the production web export (`npx expo export --platform web`) over an isolated local HTTP server and uses Playwright Chromium to load each of the 17 scoped screen fixtures at an exact **393×852 viewport** (`deviceScaleFactor: 1`).

---

## 2. Capture Pipeline Results
All 17 scoped purchase-flow screens were successfully captured and compared against their exact Figma source frames:

| Screen Name | Viewport | Source | App Capture | Side-by-Side | Overlay | Pixel Diff | Mismatch % | Status |
|---|---|---|---|---|---|---|---|---|
| `06-checkout-summary-4step-overview-v2-fr` | 393×852 | PASS | PASS | PASS | PASS | PASS | 18.58% | NEEDS_PIXEL_CORRECTION |
| `06-choose-address-saved-list-v2-fr` | 393×852 | PASS | PASS | PASS | PASS | PASS | 19.48% | NEEDS_PIXEL_CORRECTION |
| `06-add-new-address-form-v2-fr` | 393×852 | PASS | PASS | PASS | PASS | PASS | 11.94% | NEEDS_PIXEL_CORRECTION |
| `06-add-address-validation-errors-fr` | 393×852 | PASS | PASS | PASS | PASS | PASS | 15.50% | NEEDS_PIXEL_CORRECTION |
| `06-choose-delivery-standard-express-relay-v2-fr` | 393×852 | PASS | PASS | PASS | PASS | PASS | 19.18% | NEEDS_PIXEL_CORRECTION |
| `06-choose-payment-cmi-cod-wallet-v2-fr` | 393×852 | PASS | PASS | PASS | PASS | PASS | 20.32% | NEEDS_PIXEL_CORRECTION |
| `04-welcome-sign-in-create-account-guest-fr` | 393×852 | PASS | PASS | PASS | PASS | PASS | 38.45% | NEEDS_PIXEL_CORRECTION |
| `06-payment-step-intro-step3-v2-fr` | 393×852 | PASS | PASS | PASS | PASS | PASS | 16.41% | NEEDS_PIXEL_CORRECTION |
| `06-secure-payment-redirect-v2-fr` | 393×852 | PASS | PASS | PASS | PASS | PASS | 21.45% | NEEDS_PIXEL_CORRECTION |
| `06-secure-payment-redirect-loading-fr` | 393×852 | PASS | PASS | PASS | PASS | PASS | 13.01% | NEEDS_PIXEL_CORRECTION |
| `06-payment-verification-processing-fr` | 393×852 | PASS | PASS | PASS | PASS | PASS | 14.61% | NEEDS_PIXEL_CORRECTION |
| `06-cash-on-delivery-confirmation-fr` | 393×852 | PASS | PASS | PASS | PASS | PASS | 20.95% | NEEDS_PIXEL_CORRECTION |
| `06-payment-confirmed-success-v2-fr` | 393×852 | PASS | PASS | PASS | PASS | PASS | 15.18% | NEEDS_PIXEL_CORRECTION |
| `06-payment-failed-retry-fr` | 393×852 | PASS | PASS | PASS | PASS | PASS | 18.26% | NEEDS_PIXEL_CORRECTION |
| `06-payment-cancelled-resume-fr` | 393×852 | PASS | PASS | PASS | PASS | PASS | 18.13% | NEEDS_PIXEL_CORRECTION |
| `06-order-review-confirm-multi-vendor-v2-fr` | 393×852 | PASS | PASS | PASS | PASS | PASS | 18.24% | NEEDS_PIXEL_CORRECTION |
| `06-order-processing-loading-state-fr` | 393×852 | PASS | PASS | PASS | PASS | PASS | 13.22% | NEEDS_PIXEL_CORRECTION |

---

## 3. Evidence Artifacts Generated
For each of the 17 screens, the following files are saved in `mayush-mobile/design-reference/mayush-mobile-design/validation/phase-5b-pixel-parity/<screen-name>/`:
- `figma-source-393x852.png`
- `app-before-393x852.png`
- `side-by-side-before.png`
- `overlay-before-50.png`
- `pixel-diff-before.png`
- `result.json`
