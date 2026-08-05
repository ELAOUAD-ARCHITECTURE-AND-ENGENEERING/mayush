# Phase 5A Runtime Asset Audit

## Rule

Interactive controls, labels, prices, loading state, navigation, and form state must be native React Native elements. A supplied reference can be used as decorative background or isolated scene artwork only when no route behaviour depends on pixels in that image.

## Result

| Asset group | Use | Audit result |
| --- | --- | --- |
| `assets/brand/logo-transparent.png` | All page and launch logos | Transparent official mark; used through `MayushLogo`. |
| `01-entry/01-splash-screen-logo.png` | Splash decorative backdrop | The photographic edge decoration is a background layer only. The visible shared logo and the animated orange loader are native. |
| `01-entry/01-loading-screen-preparing-experience.png` | Preparation decorative backdrop | The supplied background is masked through the centre; logo, three-dot indicator, progress line, and localized status text are native and animated. |
| `assets/reference-art/onboarding-step-*-scene*.png` | Onboarding interior vignettes | Cropped scene artwork, not a full route screenshot. Header, progress, copy, CTA, and RTL behaviour are native. Step 2 contains a non-interactive miniature product-preview illustration; its sealed price is `MAD` and is not used as application data. |
| `assets/reference-art/home-*.png` | Home hero, category, product, and inspiration artwork | Isolated visual regions used inside native carousel/cards. The carousel, labels, prices, actions, and navigation are native. |
| `assets/illustrations/wishlist-empty-scene.png` | Wishlist empty-state art | Isolated illustration without route chrome or controls. |
| `assets/illustrations/cart-empty-scene.png` | Cart empty-state art | Isolated illustration without route chrome or controls. |
| `assets/illustrations/account-guest-scene.png` | Guest-account art | Isolated illustration without route chrome or controls. |

## Removed or avoided as interactive UI

- `SplashScreen` no longer relies on a static screenshot for the loader; the orbital head, trail, and rotation are live `Animated` views.
- No button, tab, text field, price, progress indicator, or navigation target is supplied by a screenshot.
- `assets/splash-icon.png` remains an unused legacy derivative; the Expo native launch configuration now uses `assets/brand/logo-transparent.png` on `#FCF2E9`.
