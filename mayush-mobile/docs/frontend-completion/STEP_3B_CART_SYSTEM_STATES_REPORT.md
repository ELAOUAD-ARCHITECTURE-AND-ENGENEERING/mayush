# STEP 3B — CART SYSTEM STATES, SAVED FOR LATER, AND GUEST-ACCOUNT MERGE REPORT

**Date**: August 6, 2026
**Status**: COMPLETED & VERIFIED
**Figma Prototype Page**: `Full App Prototype Flow` (`309:581`)
**Scope Nodes**: `309:666` – `309:669`, `309:676` – `309:677`

---

## 1. Node Implementation Matrix

| Figma Node | Figma Frame Name | Implementation Component | Navigator Key | Status | Arabic RTL Status |
|---|---|---|---|---|---|
| `309:666` | `05-cart-update-needed-price-stock-changes-fr` | `CartUpdateAlert.tsx` | `cart` | Created & Integrated | `IMPLEMENTED_WEB_CHECKED_NATIVE_VALIDATION_PENDING` |
| `309:667` | `05-cart-skeleton-loading-state` | `CartSkeleton.tsx` | `cart` | Created & Integrated | `IMPLEMENTED_WEB_CHECKED_NATIVE_VALIDATION_PENDING` |
| `309:668` | `05-cart-empty-state-fr` | `CartEmptyState.tsx` | `cart` | Created & Integrated | `IMPLEMENTED_WEB_CHECKED_NATIVE_VALIDATION_PENDING` |
| `309:669` | `05-cart-error-loading-state-fr` | `CartErrorState.tsx` | `cart` | Created & Integrated | `IMPLEMENTED_WEB_CHECKED_NATIVE_VALIDATION_PENDING` |
| `309:676` | `05-saved-for-later-items-list-fr` | `SavedForLaterList.tsx` | `cart` | Created & Integrated | `IMPLEMENTED_WEB_CHECKED_NATIVE_VALIDATION_PENDING` |
| `309:677` | `05-cart-merge-guest-account-fusion-fr` | `CartMergeSummary.tsx` | `cart` | Created & Integrated | `IMPLEMENTED_WEB_CHECKED_NATIVE_VALIDATION_PENDING` |

---

## 2. Six System States Implementation Detail

1. **Cart Update Needed (`309:666`)**:
   - `CartUpdateAlert.tsx` presents price diffs (e.g. `2 950 MAD` ➔ `2 700 MAD`), quantity adjustments, or out-of-stock items.
   - Actions: "Accepter les modifications" recalculates cart totals; "Retirer indisponibles" removes sold-out items.
2. **Cart Skeleton Loading (`309:667`)**:
   - `CartSkeleton.tsx` provides native placeholder loader blocks for cart lines, free shipping progress, and summary box.
   - Pure React Native elements without image screenshot backgrounds.
3. **Cart Empty State (`309:668`)**:
   - `CartEmptyState.tsx` renders empty cart icon, title, description, and primary discovery CTA ("Commencer mes achats").
   - Preserves active tab state on bottom navigation bar.
4. **Cart Error Loading State (`309:669`)**:
   - `CartErrorState.tsx` displays error icon, explanation, and "Réessayer" retry button.
   - Triggers clean state recovery on press.
5. **Saved for Later List (`309:676`)**:
   - `SavedForLaterList.tsx` renders dedicated saved product cards with product thumbnail, variant name, MAD price, "Déplacer vers le panier" action, and "Supprimer" action.
   - Tapping product image or title opens Product Details (`product-details`).
   - Renders clean empty state message when all saved items are removed.
6. **Guest-Account Cart Fusion Merge (`309:677`)**:
   - `CartMergeSummary.tsx` displays guest item count vs account item count.
   - `mergeCartsDeduplicated` helper combines guest and account items without duplicating product/variant lines.
   - Actions: "Fusionner les deux paniers", "Conserver le panier du compte", "Conserver le panier invité".
   - Continues to existing Checkout Summary (`checkout-summary`).

---

## 3. Verification Results

- **Test Suite**: `npm test` ➔ **92 / 92 PASSED (0 FAILED)**
- **TypeScript Compiler**: `npx tsc --noEmit` ➔ **0 Errors**
- **Web Export**: `npx expo export --platform web` ➔ **Exported: dist (Clean Build)**
- **Git Diff**: `git diff --check` ➔ **0 Warnings**

---

## 4. Documentation Updated

- `docs/phase-5c/CURRENT_SCREEN_STATUS.csv`
- `docs/mvp-state.json`
- `docs/mvp-progress.md`
- `docs/frontend-completion/STEP_3B_CART_SYSTEM_STATES_REPORT.md`

---

## 5. Next Task

**Exact Next Task**: `STEP 4 — AUTHENTICATION, REGISTRATION, PASSWORD RECOVERY AND ACCOUNT ENTRY`
