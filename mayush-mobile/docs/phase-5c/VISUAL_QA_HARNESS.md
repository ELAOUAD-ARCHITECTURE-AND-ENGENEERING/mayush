# Mayush Mobile — Development-Only Visual QA Harness

**Date**: 2026-08-06
**Module**: `mayush-mobile/src/dev/visual-qa/`
**Purpose**: Deterministic rendering of any targeted prototype screen at 393×852 without altering production RootNavigator or buyer flows.

---

## 1. Architecture
- **Location**: `src/dev/visual-qa/`
  - `visualQaTypes.ts`: Defines `VisualQaScreenKey` and `VisualQaFixtureData`.
  - `visualQaFixtures.ts`: Contains fixed, deterministic fixtures matching Figma source frames (cart lines, addresses, order totals, MAD formatting).
  - `visualQaRegistry.ts`: Maps screen keys to component renderers.
  - `VisualQaApp.tsx`: Main QA wrapper; enforces 393×852 container, sets `data-visual-qa-ready="true"` on DOM, and injects readiness marker.
  - `index.ts`: Re-exports visual QA assets.

---

## 2. Invocation Modes
1. **URL Query Parameter (Web)**:
   `http://localhost:8085/?qaScreen=06-checkout-summary-4step-overview-v2-fr`
2. **Environment Variable**:
   `EXPO_PUBLIC_VISUAL_QA=true`
   `EXPO_PUBLIC_QA_SCREEN=06-choose-address-saved-list-v2-fr`

---

## 3. Production Protection Rules
- `App.tsx` conditionally evaluates `isVisualQaMode`.
- In normal production runtime or when `qaScreen` is absent, standard `RootNavigator` renders.
- Visual QA code and controls are strictly bypassed during standard app usage.
