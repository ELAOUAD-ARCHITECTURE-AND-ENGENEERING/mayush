# Mayush Mobile Buyer MVP - Final Scope Definition

## 1. Included in Buyer MVP

- **Language & Localization**: French (LTR) and Arabic (RTL) selection and complete UI layout mirroring.
- **Guest Product Discovery**: Home hero sliders, featured categories, today's deals, best sellers, category photo grid, and subcategory filtered product lists.
- **Product Details & Variants**: Image carousel, description, price, stock, color & size variant selector, dynamic price/stock calculation via `variant/price`.
- **Guest Cart**: Add to cart, view cart, adjust item quantities, remove item, apply promo coupons, and persist `temp_user_id` in `AsyncStorage`.
- **Authentication Gate at Checkout**: Customer Login (email/phone & password), Registration, conditional OTP verification when required by backend, and automatic guest-to-user cart merge with client verification.
- **Authenticated Multi-Step Checkout**:
  - Saved shipping address selection & creation.
  - Delivery method & shipping cost computation.
  - Payment method selection (CMI Credit Card, Cash On Delivery, Wallet).
  - Authoritative order review.
  - Server order creation (`CombinedOrder`).
  - CMI hosted browser modal checkout via secure mobile bridge (Release dependency).
  - Order confirmation / thank you summary screen (`SCR-CHK-009`).
- **Essential System States**: Loading skeletons, generic error screens, offline indicator, and session expiration handling.

---

## 2. Excluded from Buyer MVP (Phase 1)

- Social Login (Google/Facebook/Apple) - Reclassified as `FUTURE_PHASE_BACKEND_CAPABILITY`.
- Customer Wishlist management screens.
- Order refund and return requests.
- Product review submission form.
- Customer support ticket creation.
- Re-order functionality.
- Seller dashboard/vendor management (Buyer-only MVP).
- Saved payment cards (Vault) management screen inside app.
