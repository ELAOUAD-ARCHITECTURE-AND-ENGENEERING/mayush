# EXPO GO MANUAL SMOKE-TEST PROTOCOL

> **STATUS**: `MANUAL_EXECUTION_REQUIRED`  
> **NATIVE VALIDATION STATUS**: Deferred (Step 9C Android Validation Environment Setup delayed per mission spec).

---

## 1. Onboarding & Language Selection (FR / AR)

- [ ] **Step 1.1**: Launch app on Expo Go (`npx expo start`).
- [ ] **Step 1.2**: Observe language selector screen (`language`). Select French (`fr`).
- [ ] **Step 1.3**: Confirm onboarding slides 1-3 display in LTR French layout with valid CTAs.
- [ ] **Step 1.4**: Restart/reset app, select Arabic (`ar`). Confirm layout flips to RTL with Arabic typography.

---

## 2. Home & Personalization CTA

- [ ] **Step 2.1**: Arrive at `home` as a guest user. Confirm promotions banner and hero products render correctly.
- [ ] **Step 2.2**: Press "Offres du moment" banner CTA. Confirm navigation to `promotions-campaigns`.
- [ ] **Step 2.3**: Press "Récemment consultés" CTA. Confirm navigation to `recently-viewed`.
- [ ] **Step 2.4**: Log in as registered user (`youssef@mayush.ma`). Confirm header greets user by name (`Youssef El Amrani`).

---

## 3. Discovery, Search & Filtering

- [ ] **Step 3.1**: Tap search bar on Home. Confirm navigation to `search-landing`.
- [ ] **Step 3.2**: Type "Fauteuil" and submit. Confirm navigation to `search-results` displaying product grid.
- [ ] **Step 3.3**: Tap filter icon. Confirm `filter-panel-modal` slides up cleanly.
- [ ] **Step 3.4**: Apply filter criteria. Confirm modal closes and grid updates.

---

## 4. Product Details, Variant Selection & Add to Cart

- [ ] **Step 4.1**: Tap product card "Fauteuil Nori Accent · Vert Sauge" (1 500 MAD). Confirm `product-details` renders image carousel, price, and specs.
- [ ] **Step 4.2**: Tap "Sélectionner les options". Confirm `variant-selector` modal sheet appears.
- [ ] **Step 4.3**: Select variant options and quantity (1).
- [ ] **Step 4.4**: Tap "Ajouter au panier". Confirm cart feedback toast/notification appears and item count increments.

---

## 5. Cart Management & Promo Code

- [ ] **Step 5.1**: Navigate to `cart`. Confirm line item "Fauteuil Nori Accent" appears with quantity 1.
- [ ] **Step 5.2**: Update quantity to 2. Confirm total recomputes correctly based on unit price.
- [ ] **Step 5.3**: Enter active promo code (e.g. `WELCOME10`). Confirm valid discount recomputes total.
- [ ] **Step 5.4**: Tap "Passer la commande". Confirm navigation to `checkout-summary`.

---

## 6. Checkout Flow (Address, Delivery & Payment)

- [ ] **Step 6.1**: On `checkout-summary`, tap "Modifier l'adresse". Select default address from authoritative account address store.
- [ ] **Step 6.2**: Select delivery method ("Livraison Standard" - 350 MAD).
- [ ] **Step 6.3**: Select payment method ("Carte bancaire marocaine (CMI)").
- [ ] **Step 6.4**: Tap "Confirmer et payer". Confirm navigation to order review / payment simulation.

---

## 7. Order Placement & Confirmation

- [ ] **Step 7.1**: Confirm payment simulation proceeds through `secure-payment-redirect` to `payment-verification`.
- [ ] **Step 7.2**: Confirm frontend simulated success state lands on `order-thank-you` displaying generated Order ID (`MAY-2026-XXXXXX`).
- [ ] **Step 7.3**: Confirm cart resets to empty.

---

## 8. Order Tracking & Post-Order Actions

- [ ] **Step 8.1**: Tap "Suivre ma commande" on thank-you screen or navigate to `order-list-v2`.
- [ ] **Step 8.2**: Tap order `MAY-2026-001842`. Confirm `order-detail-v2` displays status timeline and item details.
- [ ] **Step 8.3**: Tap "Voir les colis" or tracking link. Confirm `order-tracking-v2` lists tracking information.
- [ ] **Step 8.4**: Tap "Commander à nouveau". Confirm items re-populate cart cleanly.

---

## 9. Help Center & Support Form

- [ ] **Step 9.1**: Navigate to Settings > Help Center (`help-faq-v2`).
- [ ] **Step 9.2**: Search "Livraison". Confirm FAQ articles display matching content.
- [ ] **Step 9.3**: Tap "Contacter le support". Confirm support form loads with order selector.
- [ ] **Step 9.4**: Submit ticket. Confirm `support-request-sent-success` confirms ticket dispatch.

---

## 10. Settings & Privacy / Data Management

- [ ] **Step 10.1**: Navigate to `settings`. Confirm rows for Language, Notifications, Privacy, Storage, and About.
- [ ] **Step 10.2**: Tap "Gestion du stockage". Confirm clear cache modal works cleanly.
- [ ] **Step 10.3**: Tap "À propos de Mayush Design". Confirm `about-mayush` displays company information.
- [ ] **Step 10.4**: Tap "Politique de confidentialité". Confirm `privacy-policy` displays terms and legal information.
