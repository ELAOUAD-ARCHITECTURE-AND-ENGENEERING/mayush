# Phase 5A — Screen Maturity Lock

## Validation basis

- Source references remain the implementation authority.
- Live French first-run validation was completed in the local Expo preview: Splash → Language → Preparing → Onboarding 1 → 2 → 3 → Home.
- Home carousel, category navigation, product-list navigation, product-detail fallback state, and all five bottom tabs were exercised as real routes.
- Arabic is wired through the shared RTL shell: reversed tabs and rows, mirrored directional icons, Tajawal font faces, Arabic copy, and `MAD` currency are used by the same components.
- The supplied Pixel Parity Protocol remains required for final device-sized side-by-side and overlay captures. This maturity lock does not claim that an image screenshot is a functional page.

## Locked statuses

| Screen | Status | Phase 5A disposition |
| --- | --- | --- |
| Splash | `NATIVE_VISUAL_PASS` | Reference decorative background, transparent shared logo, and a live animated loader at the measured source positions. |
| Language choice | `NATIVE_VISUAL_PASS` | Native French/Arabic selection cards, Morocco-appropriate Arabic marker, CTA, and French/Arabic copy. |
| Preparation | `NATIVE_VISUAL_PASS` | Reference background with a real timed, animated preparation indicator and localized status copy. |
| Onboarding 1 | `NATIVE_VISUAL_PASS` | Native header, progress, copy, skip/continue controls, and isolated reference scene. |
| Onboarding 2 | `NATIVE_VISUAL_PASS` | Native header, progress, copy, skip/continue controls, and isolated reference scene. |
| Onboarding 3 | `NATIVE_VISUAL_PASS` | Native header, progress, copy, skip/start controls, and isolated reference scene. |
| Home | `NATIVE_VISUAL_PASS` | Native hero slider, source-based art, typography, card hierarchy, bottom navigation, and `MAD` display. |
| Categories | `READY_FOR_BACKEND_INTEGRATION` | Native grid, controls, RTL shell, and routing are stable; Phase 5B supplies Laravel category content. |
| Product listing | `READY_FOR_BACKEND_INTEGRATION` | Native filters, cards, prices, stock state, RTL shell, and routing are stable; Phase 5B supplies Laravel results. |
| Product details | `READY_FOR_BACKEND_INTEGRATION` | Native gallery, price/stock/variant shell, sticky CTA, and route are stable; Phase 5B supplies live product and variant pricing. |
| Wishlist | `PRESENTATION_ONLY` | Native empty-state presentation and route only; no persisted/server wishlist behaviour. |
| Cart | `PRESENTATION_ONLY` | Native empty-state presentation and route only; no cart mutation, checkout, or payment behaviour. |
| Account | `PRESENTATION_ONLY` | Native guest presentation and route only; no authentication or account backend behaviour. |

## Typography and currency correction

- French UI: Inter
- French display headings: Playfair Display
- Arabic UI: Tajawal
- Store currency: `MAD` through the existing centralized currency configuration

## Phase 5B boundary

No Laravel controller, route, migration, checkout, cart, wishlist, authentication, payment, or order behaviour was added in Phase 5A.

When Phase 5B begins, discovery requests use `EXPO_PUBLIC_API_BASE_URL`, which must equal the active Laravel `APP_URL` for local or production. See `.env.example`. The work is limited to Home, Categories, Product list, Product details, and server-authoritative variant pricing; it must not expand into cart, wishlist, authentication, checkout, or payment.
