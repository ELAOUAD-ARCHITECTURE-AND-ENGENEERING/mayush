# Mayush Mobile Design System - Design Source Manifest

## Overview

This manifest records every design foundation board and representative MVP source screenshot used to extract design tokens, layout primitives, and component specifications.

---

## 1. Foundation Boards Audit

| Source File Path | Visual Role | Key Components Observed | Colors & Tokens Observed | Radius & Spacing Observed | LTR / RTL Relevance |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `00-foundation/00-brand-essence-color-typography-summary.png` | Brand Core & Palette | Brand identity, typography scale | `#D97434` (Mayush Orange), `#1F2A3A` (Deep Navy), `#F2E8DA` (Warm Cream) | Primary radius: 12px; Card radius: 16px | Both |
| `00-foundation/00-brand-moodboard-ui-kit-overview.png` | UI Kit Overview | Product cards, badges, buttons | Primary Orange, Soft Gold accents, Warm Neutral grays | Button height: 48px; Input height: 48px | Both |
| `00-foundation/00-controls-form-components-icons.png` | Form Controls & Icons | Text fields, switches, steppers, radios | Focused border `#D97434`, Error red `#D92D20`, Disabled `#D9DEE4` | Input radius: 10px; Stepper radius: 8px | Directional inputs |
| `00-foundation/00-navigation-layout-component-board.png` | Navigation Primitives | Bottom tab bar, headers, back buttons | Active icon `#D97434`, Inactive `#A7AFBA`, Bar background `#FFFFFF` | Tab bar height: 64px + safe area | Reversed in RTL |
| `00-foundation/00-product-commerce-component-board.png` | Commerce Cards | Product cards, variant chips, badges | Discount badge `#D92D20`, Star rating `#F5B041` | Product image ratio 1:1 / 4:3; Card radius: 16px | Both |
| `00-foundation/00-ui-states-feedback-patterns.png` | Feedback Patterns | Bottom sheets, dialogs, toasts, skeletons | Overlay backdrop `rgba(17, 17, 17, 0.5)`, Skeleton `#E7DED3` | Sheet top radius: 24px; Dialog radius: 16px | Both |

---

## 2. Representative MVP Screenshot Audit

| MVP Feature | Reference Screenshot | Observed Aspect Ratio / Geometry | Key UI Components |
| :--- | :--- | :--- | :--- |
| **Splash / Entry** | `01-entry/01-splash-screen-logo.png` | Fullscreen (393 × 852) | Mayush Logo centered, cream background |
| **Language Selection**| `01-entry/01-language-selection-french-arabic.png` | Card options (393 × 852) | Language selection cards, primary orange CTA |
| **Home Screen** | `02-discovery/02-home-hero-new-arrivals-best-sellers-fr.png` | Scroll layout | Hero slider, category icons grid, product horizontal list |
| **Product Detail** | `03-product/03-product-detail-image-carousel-add-to-cart.png` | Carousel + Sticky Bottom | Image carousel pagination, price badge, sticky add-to-cart bar |
| **Cart Screen** | `05-cart-wishlist/05-cart-items-promo-code-summary-fr.png` | Grouped list + Summary | Seller header, quantity stepper, coupon input, checkout CTA |
| **Address Selection** | `06-checkout/06-choose-address-saved-list-fr.png` | Radio list | Saved address card, radio selector, add address button |
| **Payment Selection** | `06-checkout/06-choose-payment-cmi-cod-wallet-fr.png` | Selectable cards | CMI card option, COD option, Wallet option |
| **Arabic RTL** | `11-arabic-rtl/11-home-ar.png` | Mirrored (RTL) | Right-aligned headers, reversed slider navigation, right-to-left layout |
