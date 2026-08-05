# Component Observations

## Validation baseline

Implementation should follow the explicit buyer-app rules and the safest elements from `00-foundation/` and `assetsl/`:

- Official unmodified `MAYUSH DESIGN` logo; never append `BUYER`, `BUYER APP`, or an Arabic transliteration
- Primary orange around `#D97434`, deep navy text/icons, warm cream background, white cards, soft beige secondary surfaces
- Playfair Display-style editorial headings, Inter-style French UI text, and Tajawal/Cairo-style Arabic UI text
- 4/8-point spacing increments, generally 12–16 dp corner radius, soft low-elevation shadows
- One navy outline icon family; semantic red/green/blue only for error/success/information state
- Buyer bottom navigation: Accueil, Catégories, Favoris, Panier, Compte
- Currency, city, postal, and address examples are accepted variations; keep them data-driven and legible in their locale

The boards contain conflicting examples—orange values such as `#FF8A00`, Poppins samples, alternate Buyer App branding, and a non-mirrored RTL arrow. Treat currency/address examples as flexible data; the branding, interaction, navigation, and RTL rules remain the source of truth.

## Recommended React Native component inventory

| Component | Required behavior | Representative references | Main cautions |
|---|---|---|---|
| `AppHeader` | Compact title/logo variants; safe-area aware; optional search/action slots | `02-home-hero-new-arrivals-best-sellers-fr.png`, `08-account-dashboard-profile-menu-fr.png` | Do not enlarge every route into a promotional hero; no modified logo |
| `DirectionalBackButton` | 44–48 dp target; physical placement and glyph mirror in RTL | `11-arabic-rtl/*`, `10-maintenance-completed-ar.png` | Never hard-code a left arrow/left position |
| `BuyerBottomNavigation` | Exactly five destinations; badge support; safe-area inset | Many Discovery, Orders, Account, Support images | Numerous screenshots replace Catégories/Panier with Explorer, Rechercher, Messages, or Commandes |
| `PrimaryButton` | Full/inline widths; loading/disabled/destructive variants; large-text wrapping | All flows | Avoid fake enabled states; destructive actions require explicit danger styling |
| `SecondaryButton` / `TextButton` | Clear subordinate hierarchy and minimum target | System states and dialogs | Do not use unrelated promotional actions as recovery actions |
| `FormInput` | Persistent label, helper/error state, keyboard type, clear affordance, RTL/bidi support | Auth, address, account forms | Use an explicit country-code control where relevant; placeholders cannot carry the only label |
| `PhoneInput` | Locale-aware, accessible country code; bidi-safe digits | `04-auth/*`, `06-add-new-address-form-ar.png`, Account edits | Test Arabic layout without reversing the number; validate the active number policy server-side |
| `OtpInput` | One-time-code autofill; paste; countdown; error; accessibility grouping | `04-otp-*`, `08-verify-phone-otp-code-fr.png` | Do not enable verification with empty cells or resend before cooldown |
| `PasswordField` / `PasswordRequirements` | Show/hide; live requirement state; strength; secure autofill | `04-create-new-password-*`, `08-change-password-*` | Empty-looking passwords must not show all-green completion |
| `ProductCard` | Dynamic image/title/seller/price/old price/badge/favorite; two-column and horizontal variants | Discovery, wishlist, recently viewed | Must wrap long titles and format prices by active locale; never embed card text in artwork |
| `ProductGrid` | Responsive columns, pagination/infinite loading, skeleton/empty/error | Search/category/wishlist | Avoid poster-fixed grid height and bottom-nav overlap |
| `Price` | Locale-aware amount; old/current/discount variants | Product, cart, checkout, orders | Centralize rounding and totals; Arabic must remain bidi-readable |
| `StatusBadge` | Central token map for pending, preparing, shipped, delivered, cancelled, refunded | Orders and notifications | Do not infer color independently per screen; foundation blue/orange/green/red semantics must be consistent |
| `CartLineItem` | Variant, seller, quantity, price change/stock state, move/remove actions | `05-cart-*` | Quantity and totals must reconcile after backend changes |
| `AddressCard` | Default label, delivery eligibility, select/edit/delete actions | Checkout and Account | Default address cannot offer “set default”; destructive delete should be confirmed |
| `CheckoutStepper` | Four-step semantic progress with completed/current/upcoming states | `06-checkout-summary-4step-*` | Several screens show earlier steps as active rather than completed |
| `PaymentMethodCard` | CMI/store handoff, COD/wallet capability, selected/disabled state | `06-choose-payment-cmi-cod-wallet-fr.png`, `08-payment-methods-card-cod-wallet-fr.png` | NEEDS PRODUCT DECISION for saved-card tokenization and wallet/COD eligibility |
| `OrderCard` | Status, date, identifier, seller/package grouping, eligible actions | `07-orders-*` | Actions must wrap at Dynamic Type and be driven by the order state machine |
| `OrderTimeline` | Monotonic events, generated localized weekdays/times, current/completed semantics | `07-order-tracking-timeline-realtime-fr.png` | Never hard-code weekday names that contradict timestamps |
| `PackageCard` | Multi-package and multi-seller status grouping | `07-multiple-packages-split-shipment-fr.png` | Aggregate and package statuses/dates must not contradict |
| `SettingsRow` | Icon, label, value, switch/chevron; optional danger state | Account and Support/Settings | Prefer vertical lists; rows and switches require full accessible labels |
| `PermissionDeniedState` | Context, current permission state, open-settings and safe fallback | `10-camera-access-denied-fr.png`, photo/location variants | Requests must be contextual; bulk grant is not a native capability |
| `SystemStateLayout` | Illustration, heading, message, primary/secondary action; compact/full variants | `10-system-states/*` | Keep route mapping accurate; do not add a logo to every contextual error |
| `ConnectivityBanner` | Offline/reconnecting/restored nonblocking states | `10-reconnection-progress-fr.png`, `10-connection-restored-fr.png` | Prefer auto-resume/banner behavior to full-page interruption when possible |
| `SkeletonBlock` | Hidden/combined accessibility semantics; route-specific geometry | `10-content-loading-skeleton-fr.png` | Skeleton must match destination layout and preserve stable navigation |
| `ConfirmationDialog` / `BottomSheet` | Focus containment; dismissal rules; keyboard/safe-area behavior; destructive action | Cart removal, address deletion, logout, cache clear, ticket close | Several screenshots are full posters that should be compact dialogs/sheets |
| `TicketThread` / `ReplyComposer` | Virtualized chronology; secure attachment preview; keyboard-safe sticky composer | `09-ticket-detail-conversation-thread-fr.png` | Reuse composer rather than opening a duplicate reply page |
| `ForcedUpdateGate` | Minimum-version check; store deep link; offline/store-unavailable fallback | `10-update-required-fr.png`, Arabic equivalent | Normal app binaries are store-managed; in-app installation requires an explicit architecture decision |

## Navigation architecture

Bottom navigation should be one shared component configured once, not redrawn per feature. The observed violations are systemic:

- `07-orders-list-all-tabs-fr.png` and several Orders states add `Commandes` as a permanent tab.
- `10-data-restoration-progress-fr.png` uses `Rechercher` and `Messages` and omits `Catégories` and `Panier`.
- Multiple Account and Support images change tab count, order, labels, icons, and active-state treatment.
- Arabic screens require the same logical five destinations rendered with true RTL direction; seller-like destinations such as `منتجاتي` or `طلباتي` are not part of the buyer app.

Checkout, authentication, payment redirects, forced updates, and true modal flows may hide bottom navigation. When shown, scroll containers need a content inset equal to navigation height plus the device safe area.

## Typography and adaptive layout

- Use tokenized text roles rather than fixed artwork: display/section heading, title, body, caption, label, button, price, and status.
- French labels must wrap without truncating controls; Arabic uses different word lengths and line metrics and needs independent line-height verification.
- Validate at maximum supported Dynamic Type/font scaling. Three-action order cards, dense settings matrices, OTP rows, and compact modal layouts are the highest-risk areas.
- Use flex/grid constraints based on available width; do not implement screenshot coordinates or fixed card heights.
- Keep touch targets at least 44 pt on iOS / approximately 48 dp on Android, with about 8 dp separation where adjacent actions could be confused.

## RTL and bidirectional text

- Set direction at the screen/container level and use logical `start`/`end` spacing; do not manually reverse isolated rows.
- Mirror directional back arrows, chevrons, steppers, carousels, and progress timelines. Brand marks, non-directional icons, product photos, and numeric content should not be mirrored.
- Keep `MAD`, phone numbers, order identifiers, dates, times, and OTP digits readable by isolating mixed-direction runs.
- Bottom navigation must preserve destination meaning while its physical ordering follows the approved RTL convention.
- `10-maintenance-completed-ar.png` is a confirmed failure: its back arrow remains on the left and points left.

## Data and state contracts visible in the screens

The screenshots require data models, not invented endpoints:

- Product: id, localized name, imagery, seller, price/old price, variants, stock, rating/review count, delivery/return facts
- Cart/checkout: line items, seller groups, quantity, promotion result, address eligibility, delivery methods, fees, taxes/discounts, payment eligibility, order/payment state
- Orders: identifier, placed timestamp, localized status, items/packages/sellers, timeline events, totals/refunds, action eligibility, tracking/carrier data
- Account: profile fields, verification state, addresses, sessions/devices, notification/marketing preferences, locale, security settings
- Support: category, request/ticket id, order link, messages, attachments, status, priority if buyer-visible, timestamps, resolution/rating eligibility
- System: connectivity, session validity, permissions, maintenance window, minimum supported version, store link, retry/timeout state

All derived values—totals, percentages, progress, weekday names, countdowns, version dates, refund amounts, and delivery windows—must be computed or provided authoritatively. Fixed screenshot examples must not become application constants.

## Product decisions required before component finalization

- Saved-card/tokenization provider and whether COD/wallet are real buyer payment options
- Cancellation, return, refund, reorder, and multi-package order state machines
- Email/phone change reauthentication and session-revocation policy
- Supported languages and market/localization configuration
- Notification categories that are mandatory and quiet-hours override rules
- Offline cache scope and whether queued buyer mutations are supported
- Support channels, SLA/status visibility, attachment MIME/count/size policy, WhatsApp/email details
- App update architecture: platform store only versus approved OTA/content packages
- Permission request contexts and manual fallbacks

## Implementation acceptance checklist

Before a screenshot becomes a development ticket:

1. Route name and visible content must describe the same state.
2. Official logo, palette, typography roles, and icon family must pass.
3. Navigation must match the buyer five-tab contract or be intentionally absent.
4. Locale-specific copy and legible, data-driven currency/address presentation must pass.
5. Primary action, disabled/loading/error/success behavior, and back/dismiss behavior must be defined.
6. Layout must survive scrolling, keyboard, safe areas, long French/Arabic copy, and Dynamic Type.
7. Data must be dynamic and internally reconciled.
8. Directional UI must be genuinely mirrored in Arabic.
9. Component primitives must be reusable rather than one-off poster coordinates.
10. Any unresolved business behavior must be labeled `NEEDS PRODUCT DECISION` before implementation.
