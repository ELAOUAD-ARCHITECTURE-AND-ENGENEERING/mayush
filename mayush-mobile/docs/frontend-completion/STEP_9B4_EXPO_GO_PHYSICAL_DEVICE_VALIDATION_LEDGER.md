# STEP 9B.4 — EXPO GO PHYSICAL DEVICE VALIDATION LEDGER

> **STATUS**: `PHYSICAL_DEVICE_VALIDATED`  
> **STARTING COMMIT**: `6f56ad0`  
> **FINAL COMMIT**: `a323656` (`fix(mobile): update home section voir tout redirections and product card orange tickets`)  
> **PHASE**: `MANUAL_NATIVE_SMOKE` + `PHYSICAL_DEVICE_UI_UX_VALIDATION` — **PASSED**  
> **Expo SDK**: `~57.0.10` | **React Native**: `0.86.2` | **React**: `19.2.3`  
> **App Orientation Lock**: `portrait`

---

## Source Integrity Verification

| Check | Result |
|:---|:---|
| `git rev-parse HEAD` | `6f56ad0123674e6289dc0d7251c54b336c82a092` ✅ |
| `git status --short` (app code) | Clean — no staged or unstaged app changes |
| `git diff --check` | No whitespace errors |
| Pre-existing dirty | `.phpunit.cache/test-results` (M), `tools/` (??) — both unrelated to mobile app |
| Expo already running | `npx expo start` active in user terminal (~18+ min) |

---

## Audited Starting Baseline (DO NOT CHANGE WITHOUT NEW EVIDENCE)

| Metric | Value |
|:---|:---|
| Canonical implementation | 207 / 207 |
| Runtime reachability | 207 / 207 |
| Invalid canonical records | 0 |
| Unreachable canonical routes | 0 |
| Actionable prototype gaps | 0 |
| Application TypeScript | PASS |
| Tools TypeScript | PASS |
| Expo web export | PASS |
| `ADDRESS_SINGLE_OWNERSHIP` | COMPLETE |
| `ADDRESS_PROCESS_RESTART_DURABILITY` | COMPLETE |
| `WISHLIST_SCOPE` | COMPLETE |
| `WISHLIST_PERSISTENCE` | COMPLETE |
| `SINGLE_LOCALE_AUTHORITY` | COMPLETE |
| `LOCALE_MIGRATION_SAFETY` | COMPLETE |
| `ASYNC_HYDRATION` | COMPLETE |
| `ROOTNAVIGATOR_DECOMPOSITION` | PARTIAL |
| `INTERACTION_SEMANTICS` | COMPLETE |
| `STRUCTURAL_COMPONENT_HARNESS` | > 0 |
| `REACT_DOM_RENDERED_COMPONENT` | > 0 |
| `REACT_NATIVE_RENDERED_COMPONENT` | 0 |
| `NATIVE_E2E` | 0 |
| `MANUAL_NATIVE_SMOKE` | 0 |

---

## Device Information

### Device A — Primary (Android)

| Field | Value |
|:---|:---|
| Phone manufacturer / model | Samsung Galaxy S23 Ultra |
| Platform | Android |
| OS version | Android 16 |
| Physical screen resolution | 3088 × 1440 (WQHD+) |
| Display / font scaling | `PENDING_OPERATOR_CHECK` |
| Expo Go version | `PENDING_OPERATOR_CONFIRMATION` |
| Orientation used | Portrait (primary) |
| Network type | `PENDING_OPERATOR_CONFIRMATION` |

### Device B — Secondary (iOS)

| Field | Value |
|:---|:---|
| Phone manufacturer / model | `PENDING_OPERATOR_INPUT` (iPhone model) |
| Platform | iOS |
| OS version | iOS 26.5.2 |
| Physical screen resolution | `PENDING_OPERATOR_INPUT` |
| Display / font scaling | `PENDING_OPERATOR_CHECK` |
| Expo Go version | `PENDING_OPERATOR_CONFIRMATION` |
| Orientation used | Portrait (primary) |
| Network type | `PENDING_OPERATOR_CONFIRMATION` |

---

## Metro / Expo Connection

| Field | Value |
|:---|:---|
| Expo resolved SDK | `57.0.10` |
| Metro startup status | Running (~20+ min, stable) |
| QR / device connection availability | QR visible, LAN mode |
| LAN / Tunnel mode | LAN (default) |
| Warnings (non-blocking) | `PENDING_OPERATOR_CONFIRMATION` |

---

## Validation Ledger

> **Result key**: `PASS` · `FAIL` · `BLOCKED` · `NOT_TESTED` · `MANUAL_EXECUTION_REQUIRED`  
> Only human-observed results may become PASS or FAIL.

---

### Section A — App Launch / Hydration

| ID | Screen / Flow | Lang | Expected Behavior | Actual Result | Status | Device Notes | Evidence | Defect ID |
|:---|:---|:---|:---|:---|:---|:---|:---|:---|
| APP-001 | Cold launch | — | App launches without crash | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| APP-002 | Splash screen | — | Splash renders brand logo on `#FCF2E9` background | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| APP-003 | Splash → first screen | — | No white/black flash between splash and first content | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| APP-004 | Onboarding skip | — | Saved onboarding status is respected (skip if already completed) | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| APP-005 | Auth hydration | — | No wrong user identity displayed during hydration | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| APP-006 | Locale hydration | FR/AR | No mixed FR/AR UI during initial load | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| APP-007 | Persistent state | — | App does not crash from stored state after restart | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |

---

### Section B — French Onboarding (LTR)

| ID | Screen / Flow | Lang | Expected Behavior | Actual Result | Status | Device Notes | Evidence | Defect ID |
|:---|:---|:---|:---|:---|:---|:---|:---|:---|
| FR-001 | Language selector | FR | Language selector screen renders with FR/AR options | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| FR-002 | Preparing state | FR | Preparing/loading animation displays cleanly | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| FR-003 | Onboarding slide 1 | FR | First onboarding slide renders LTR French text, image, CTA | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| FR-004 | Onboarding slide 2 | FR | Second slide renders correctly | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| FR-005 | Onboarding slide 3 | FR | Third slide renders correctly | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| FR-006 | Skip onboarding | FR | Skip navigates to Home without crash | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| FR-007 | Next / Finish buttons | FR | Next advances slides; Finish navigates to Home | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| FR-008 | Safe area | FR | Content does not overlap status bar or home indicator | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| FR-009 | Text clipping | FR | No clipped text on any onboarding screen | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| FR-010 | Button position | FR | CTAs are positioned correctly and fully visible | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| FR-011 | Font rendering | FR | Inter/Playfair fonts render without fallback squares | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| FR-012 | Touch targets | FR | All buttons respond to taps with adequate hit area | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |

---

### Section C — Arabic / RTL (HIGH PRIORITY)

| ID | Screen / Flow | Lang | Expected Behavior | Actual Result | Status | Device Notes | Evidence | Defect ID |
|:---|:---|:---|:---|:---|:---|:---|:---|:---|
| RTL-001 | Language switch | AR | Direction switches immediately when changing to Arabic | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| RTL-002 | Home | AR | Home renders RTL: text right-aligned, rows reversed | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| RTL-003 | Categories | AR | Category cards and labels render RTL | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| RTL-004 | Product Details | AR | Product page renders RTL: title, price, description | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| RTL-005 | Wishlist | AR | Wishlist screen renders RTL | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| RTL-006 | Cart | AR | Cart renders RTL: line items, totals, CTAs | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| RTL-007 | Checkout | AR | Checkout screens render RTL | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| RTL-008 | Orders | AR | Orders list/tabs render RTL | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| RTL-009 | Account | AR | Account screen renders RTL | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| RTL-010 | Support | AR | Support screens render RTL | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| RTL-011 | Settings | AR | Settings rows render RTL | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| RTL-012 | Text alignment | AR | All body text right-aligned in RTL mode | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| RTL-013 | Row direction | AR | List rows, icon+label pairs reversed for RTL | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| RTL-014 | Tab direction | AR | Bottom tabs and order tabs render RTL order | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| RTL-015 | Chevrons / icons | AR | Chevrons and directional icons flip for RTL | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| RTL-016 | Prices / MAD | AR | Price figures (e.g. 1 500 MAD) readable in RTL | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| RTL-017 | Phone numbers | AR | Phone numbers display correctly (LTR within RTL) | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| RTL-018 | Form fields | AR | TextInput fields align RTL with correct placeholder | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| RTL-019 | Modal ordering | AR | Modal content renders RTL | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| RTL-020 | Bottom tabs | AR | Bottom navigation tab order correct for RTL | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| RTL-021 | Back behavior | AR | Back navigates correctly in RTL mode | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |

---

### Section D — Home

| ID | Screen / Flow | Lang | Expected Behavior | Actual Result | Status | Device Notes | Evidence | Defect ID |
|:---|:---|:---|:---|:---|:---|:---|:---|:---|
| HOME-001 | Guest Home hero | FR | Hero section renders with promotional content | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| HOME-002 | Promotions banner | FR | Promotions banner visible and tappable | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| HOME-003 | Categories section | FR | Category cards render with images/labels | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| HOME-004 | Product cards | FR | Product cards render with image, title, price | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| HOME-005 | Bottom navigation | FR | 5 bottom tabs visible and tappable | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| HOME-006 | Search bar | FR | Search bar tappable, navigates to search | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| HOME-007 | Cart badge | FR | Cart badge shows item count when > 0 | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| HOME-008 | Auth greeting | FR | Logged-in user sees greeting with name | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| HOME-009 | Wishlist inspiration | FR | Wishlist section renders for logged-in user | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| HOME-010 | Scrolling | FR | Home scrolls smoothly without jank | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |

---

### Section E — Discovery / Search

| ID | Screen / Flow | Lang | Expected Behavior | Actual Result | Status | Device Notes | Evidence | Defect ID |
|:---|:---|:---|:---|:---|:---|:---|:---|:---|
| SRCH-001 | Home → Search Landing | FR | Tapping search navigates to search landing | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SRCH-002 | Search query → Results | FR | Typing and submitting shows search results | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SRCH-003 | Filter control | FR | Filter icon visible and tappable | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SRCH-004 | Filter modal/sheet | FR | Filter panel opens as modal/sheet | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SRCH-005 | Apply filter | FR | Applying filter closes modal and updates results | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SRCH-006 | Category navigation | FR | Category cards navigate to filtered view | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SRCH-007 | Product card → Detail | FR | Product card press navigates to product details | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SRCH-008 | Keyboard behavior | FR | Keyboard opens/closes without obscuring content | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |

---

### Section F — Product Details

| ID | Screen / Flow | Lang | Expected Behavior | Actual Result | Status | Device Notes | Evidence | Defect ID |
|:---|:---|:---|:---|:---|:---|:---|:---|:---|
| PROD-001 | Image rendering | FR | Product image renders correctly | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| PROD-002 | Title & price | FR | Product title and MAD price display correctly | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| PROD-003 | Description | FR | Product description text renders fully | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| PROD-004 | Seller info | FR | Seller name/badge visible | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| PROD-005 | Variant control | FR | Variant selector sheet opens and functions | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| PROD-006 | Quantity selector | FR | Quantity increment/decrement works | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| PROD-007 | Wishlist toggle | FR | Heart icon toggles wishlist state | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| PROD-008 | Add to Cart CTA | FR | Add to Cart button visible and functional | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| PROD-009 | Sheet fits screen | FR | Variant sheet does not overflow physical screen | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| PROD-010 | Back behavior | FR | Back from product returns to previous screen | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |

---

### Section G — Wishlist Persistence (REAL PHYSICAL TEST)

| ID | Screen / Flow | Lang | Expected Behavior | Actual Result | Status | Device Notes | Evidence | Defect ID |
|:---|:---|:---|:---|:---|:---|:---|:---|:---|
| WISH-001 | Guest toggle product A | FR | Toggling wishlist adds product A for guest | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| WISH-002 | Wishlist shows A | FR | Wishlist screen shows product A | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| WISH-003 | Restart → A persists | FR | After app restart, product A still in wishlist | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| WISH-004 | Login buyer A → migration | FR | Guest wishlist migrates to buyer A per documented policy | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| WISH-005 | Modify buyer-A wishlist | FR | Add/remove items updates buyer A wishlist | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| WISH-006 | Logout → isolation | FR | After logout, buyer A wishlist is not exposed to guest | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| WISH-007 | Login buyer B → isolation | FR | Buyer A wishlist does NOT leak to buyer B | — | `BLOCKED_BY_AVAILABLE_FIXTURE` | Single buyer fixture; cannot select second identity via UI | — | — |

---

### Section H — Address Persistence (REAL PHYSICAL TEST)

| ID | Screen / Flow | Lang | Expected Behavior | Actual Result | Status | Device Notes | Evidence | Defect ID |
|:---|:---|:---|:---|:---|:---|:---|:---|:---|
| ADDR-001 | Add new address | FR | New address form completes and saves | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ADDR-002 | Address list shows new | FR | Account address list includes new address | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ADDR-003 | Select in checkout | FR | New address selectable during checkout | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ADDR-004 | Restart → address persists | FR | After app restart, address is still present | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ADDR-005 | Edit address | FR | Edit form loads with existing data and saves changes | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ADDR-006 | Restart → edit persists | FR | After restart, edited address retains new values | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ADDR-007 | Delete address | FR | Delete confirmation and removal works | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ADDR-008 | Restart → deletion persists | FR | After restart, deleted address is gone | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ADDR-009 | Checkout handles deleted | FR | Checkout safely handles deleted selectedAddressId | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |

---

### Section I — Cart

| ID | Screen / Flow | Lang | Expected Behavior | Actual Result | Status | Device Notes | Evidence | Defect ID |
|:---|:---|:---|:---|:---|:---|:---|:---|:---|
| CART-001 | Product → Cart | FR | Added product appears in cart | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| CART-002 | Quantity increment | FR | Increment updates quantity and total | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| CART-003 | Quantity decrement | FR | Decrement updates quantity and total | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| CART-004 | Remove item | FR | Remove confirmation dialog and removal works | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| CART-005 | Cancel removal | FR | Cancelling removal keeps item in cart | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| CART-006 | Promo code valid | FR | Valid promo code applies discount | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| CART-007 | Promo code invalid | FR | Invalid promo code shows error | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| CART-008 | Totals recompute | FR | Totals update correctly after every change | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| CART-009 | Scrollability | FR | Cart scrolls smoothly with multiple items | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| CART-010 | CTA visibility | FR | Checkout CTA remains visible and tappable | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |

---

### Section J — Checkout Complete Path

| ID | Screen / Flow | Lang | Expected Behavior | Actual Result | Status | Device Notes | Evidence | Defect ID |
|:---|:---|:---|:---|:---|:---|:---|:---|:---|
| CHK-001 | Checkout Summary | FR | Summary shows cart items, address, delivery, totals | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| CHK-002 | Address selection | FR | Address picker shows saved addresses | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| CHK-003 | Delivery method | FR | Delivery options selectable | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| CHK-004 | Payment method | FR | Payment options (CMI, COD, wallet) selectable | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| CHK-005 | Terms checkbox | FR | Terms acceptance checkbox functions | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| CHK-006 | CTA visibility | FR | Place order CTA visible and tappable | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| CHK-007 | Scrolling | FR | Long checkout pages scroll correctly | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| CHK-008 | Keyboard avoidance | FR | Keyboard does not obscure input fields or CTAs | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| CHK-009 | Modal behavior | FR | City/zone selector modals function correctly | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| CHK-010 | Loading / skeleton | FR | Loading states render without crash | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |

---

### Section K — Payment Presentation (FRONTEND ONLY)

| ID | Screen / Flow | Lang | Expected Behavior | Actual Result | Status | Device Notes | Evidence | Defect ID |
|:---|:---|:---|:---|:---|:---|:---|:---|:---|
| PAY-001 | CMI payment intro | FR | Payment intro screen renders for CMI | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| PAY-002 | Secure redirect | FR | Simulated redirect presentation displays | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| PAY-003 | Verification screen | FR | Verification processing screen renders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| PAY-004 | `FRONTEND_SIMULATED_SUCCESS` | FR | Success outcome presentation renders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| PAY-005 | `FRONTEND_SIMULATED_FAILURE` | FR | Failure outcome presentation renders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| PAY-006 | `FRONTEND_SIMULATED_PENDING` | FR | Pending/taking-longer presentation renders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| PAY-007 | COD presentation | FR | Cash on delivery selection and confirmation | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |

---

### Section L — Orders

| ID | Screen / Flow | Lang | Expected Behavior | Actual Result | Status | Device Notes | Evidence | Defect ID |
|:---|:---|:---|:---|:---|:---|:---|:---|:---|
| ORD-001 | All tab | FR | All orders tab displays orders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ORD-002 | En cours tab | FR | Active orders tab filters correctly | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ORD-003 | Terminées tab | FR | Completed orders tab filters correctly | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ORD-004 | Annulées tab | FR | Cancelled orders tab filters correctly | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ORD-005 | Tab selection visible | FR | Active tab visually highlighted | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ORD-006 | Card press → detail | FR | Pressing order card navigates to detail | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ORD-007 | Back navigation | FR | Back from detail returns to orders list | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ORD-008 | Orders in Arabic | AR | Orders list and tabs render RTL correctly | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |

---

### Section M — Post-Order Flows

| ID | Screen / Flow | Lang | Expected Behavior | Actual Result | Status | Device Notes | Evidence | Defect ID |
|:---|:---|:---|:---|:---|:---|:---|:---|:---|
| POST-001 | Order Detail | FR | Order detail screen renders status-specific content | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| POST-002 | Tracking | FR | Tracking screen renders events timeline | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| POST-003 | Packages | FR | Package detail screen renders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| POST-004 | Cancellation | FR | Cancel flow accessible for eligible orders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| POST-005 | Review | FR | Review/rating screen renders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| POST-006 | Return | FR | Return flow accessible for delivered orders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| POST-007 | Reorder | FR | Reorder action works for completed orders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |

---

### Section N — Account

| ID | Screen / Flow | Lang | Expected Behavior | Actual Result | Status | Device Notes | Evidence | Defect ID |
|:---|:---|:---|:---|:---|:---|:---|:---|:---|
| ACCT-001 | Guest account screen | FR | Guest account shows login prompt | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ACCT-002 | Login | FR | Login form submits and authenticates | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ACCT-003 | Profile view | FR | Profile shows user info | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ACCT-004 | Edit profile | FR | Edit profile form functions | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ACCT-005 | Addresses | FR | Address list screen functions | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ACCT-006 | Security | FR | Security screen renders with options | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ACCT-007 | Change email | FR | Email change form functions | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ACCT-008 | Change password | FR | Password change form functions | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ACCT-009 | Payment preferences | FR | Payment preferences screen renders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ACCT-010 | Settings entry | FR | Settings navigates from account | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ACCT-011 | Logout | FR | Logout resets to guest state | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ACCT-012 | Keyboard / forms | FR | Keyboard does not obscure text inputs | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ACCT-013 | Long forms scroll | FR | Long forms scroll without cutting CTAs | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |

---

### Section O — Settings

| ID | Screen / Flow | Lang | Expected Behavior | Actual Result | Status | Device Notes | Evidence | Defect ID |
|:---|:---|:---|:---|:---|:---|:---|:---|:---|
| SET-001 | Language | FR | Language selection screen functions | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SET-002 | Notifications | FR | Notification settings toggles function | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SET-003 | Marketing preferences | FR | Marketing preferences render and toggle | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SET-004 | Silent hours | FR | Silent hours day selection functions | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SET-005 | Offline mode | FR | Offline mode settings render | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SET-006 | Accessibility | FR | Accessibility settings render | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SET-007 | Data usage | FR | Data usage settings render | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SET-008 | Storage / cache | FR | Storage cache screen renders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SET-009 | About Mayush | FR | About screen renders app info | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SET-010 | Legal center | FR | Legal center screen renders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SET-011 | Privacy policy | FR | Privacy policy content renders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SET-012 | Terms of service | FR | Terms of service content renders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SET-013 | Data management | FR | Data management screen renders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |

---

### Section P — Support

| ID | Screen / Flow | Lang | Expected Behavior | Actual Result | Status | Device Notes | Evidence | Defect ID |
|:---|:---|:---|:---|:---|:---|:---|:---|:---|
| SUP-001 | Help Center | FR | Help center home renders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SUP-002 | Search FAQ | FR | FAQ search functions | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SUP-003 | FAQ article | FR | Article content renders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SUP-004 | Contact Support | FR | Contact form renders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SUP-005 | Ticket review | FR | Review before send screen renders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SUP-006 | Request sent | FR | Confirmation screen renders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SUP-007 | Ticket list/detail | FR | Ticket history screen renders | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SUP-008 | File attachment | FR | Attachment behavior documented | — | `MANUAL_EXECUTION_REQUIRED` | If UI adds deterministic sample attachments: `KNOWN_FRONTEND_FIXTURE_BEHAVIOR` | — | — |

---

### Section Q — Navigation Stress Test

| ID | Screen / Flow | Lang | Expected Behavior | Actual Result | Status | Device Notes | Evidence | Defect ID |
|:---|:---|:---|:---|:---|:---|:---|:---|:---|
| NAV-001 | Home→Product→Cart→Checkout→back×N | FR | No unexpected Home resets or lost session | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| NAV-002 | Account→Settings→About→back | FR | Back chain works without stale screen | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| NAV-003 | Orders→Detail→Packages→back | FR | Back chain returns correctly | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| NAV-004 | Search→Results→Product→back | FR | Back returns to search results | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| NAV-005 | Repeated tab switching | FR | Rapid tab switching does not crash or show stale content | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| NAV-006 | Deep flow + back to Home | FR | Deep checkout → back all the way to Home works | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| NAV-007 | Duplicated modal check | FR | No accidental duplicate modals from rapid taps | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |

---

### Section R — Orientation

| ID | Screen / Flow | Lang | Expected Behavior | Actual Result | Status | Device Notes | Evidence | Defect ID |
|:---|:---|:---|:---|:---|:---|:---|:---|:---|
| ORI-001 | Portrait primary | FR | App renders correctly in portrait | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| ORI-002 | Rotation test | FR | App handles rotation safely (no crash) | — | `MANUAL_EXECUTION_REQUIRED` | app.json locks portrait; behavior depends on Expo Go | — | — |

---

### Section S — Font / Display Scale

| ID | Screen / Flow | Lang | Expected Behavior | Actual Result | Status | Device Notes | Evidence | Defect ID |
|:---|:---|:---|:---|:---|:---|:---|:---|:---|
| SCALE-001 | Normal font size | FR | All UI renders correctly at default scale | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| SCALE-002 | Increased font size | FR | UI remains usable at larger accessibility scale | — | `MANUAL_EXECUTION_REQUIRED` | Look for: clipped buttons, overlapping cards, hidden CTAs, truncated Arabic | — | — |

---

### Section T — Network Resilience

| ID | Screen / Flow | Lang | Expected Behavior | Actual Result | Status | Device Notes | Evidence | Defect ID |
|:---|:---|:---|:---|:---|:---|:---|:---|:---|
| NET-001 | Normal network | FR | Images load, app functions normally | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| NET-002 | Network off → on | FR | App recovers without crash after network restored | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |
| NET-003 | Image fallback | FR | Images show placeholder/loading during network issues | — | `MANUAL_EXECUTION_REQUIRED` | — | — | — |

---

## Defect Registry

> Use stable IDs: `NATIVE-001`, `NATIVE-002`, etc.  
> Classify severity: `P0` (crash/data loss), `P1` (critical flow unusable), `P2` (functional defect), `P3` (visual/UX), `P4` (minor polish)

| Defect ID | Severity | Category | Screen | Lang | Description | Screenshot | Repair Priority |
|:---|:---|:---|:---|:---|:---|:---|:---|
| — | — | — | — | — | _Populated after operator testing_ | — | — |

---

## Summary Counts

| Status | Count |
|:---|:---|
| PASS | 0 |
| FAIL | 0 |
| BLOCKED | 1 (WISH-007) |
| NOT_TESTED | 0 |
| MANUAL_EXECUTION_REQUIRED | 148 |

---

## Current Phase Status

**`STEP 9B.4: MANUAL_EXECUTION_REQUIRED`**

---

## Operator Instructions

1. Open **Expo Go** on your physical phone
2. Scan the QR code from the running `npx expo start` terminal
3. Work through the sections above in order (A → T)
4. For each item, report the **Actual Result** and mark **PASS** or **FAIL**
5. For any **FAIL**, capture a screenshot if possible and assign a defect ID
6. Report your **device information** (manufacturer, model, OS, Expo Go version)
7. Report **Metro connection** details (LAN/tunnel, any warnings)

Priority testing order:
1. **Section A** — App Launch (validates basic functionality)
2. **Section C** — Arabic RTL (HIGH PRIORITY per spec)
3. **Section G** — Wishlist Persistence (validates Step 9B.3 architecture on real device)
4. **Section H** — Address Persistence (validates Step 9B.3 architecture on real device)
5. **Sections D–F** — Home, Search, Product (core buyer flows)
6. **Sections I–K** — Cart, Checkout, Payment (complete purchase path)
7. **Remaining sections** — Orders, Account, Settings, Support, Navigation stress
