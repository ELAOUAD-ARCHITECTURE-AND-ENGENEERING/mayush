# Mayush Mobile — Address Form Component Variant Decision

**Date**: 2026-08-06
**Scope**: Address Form Geometry & TextField Global Isolation

---

## 1. Context & Problem Statement
In Phase 5D.1 EXP-003, attempting to force global `TextField.tsx` to 48px height and 14px radius worsened the Add Address mismatch from 11.33% to 11.56% (+0.23% increase). Global changes to core form primitives risk side-effect regressions across authentication, cart, and profile screens.

---

## 2. Decision
1. **Preserve Global Primitives**: Keep standard [`TextField.tsx`](file:///c:/laragon/www/mayush/mayush-mobile/src/design-system/components/forms/TextField.tsx) container height at 56px and radius at 28px (`radii.pill`) for general app forms.
2. **Screen-Specific Layout Scope**: Keep form layout customization strictly scoped inside [`AddAddressFormScreen.tsx`](file:///c:/laragon/www/mayush/mayush-mobile/src/screens/checkout/AddAddressFormScreen.tsx) via `containerStyle` props, avoiding unverified global token mutations.
3. **Dual-State Shared Component**: Both default Add Address (`06-add-new-address-form-v2-fr`) and Validation Errors (`06-add-address-validation-errors-fr`) share the exact same `AddAddressFormScreen` component rendering tree.
