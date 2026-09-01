# Filename-to-Screen Route Integrity Review

This audit verifies that every screenshot filename accurately names the buyer route or UI state visibly depicted. It is independent of visual quality: a screen can be attractive and still be unsafe if its filename would cause the implementation team to wire the wrong route.

## Classification

- `EXACT_MATCH` — Filename accurately describes the primary visible buyer state.
- `PARTIAL_MATCH` — Filename is broadly related but omits a material state qualifier or uses an imprecise route name.
- `MISMATCH` — Filename and visible UI represent different routes or business states. Do not implement from the filename.
- `AMBIGUOUS` — Visual evidence does not establish one unambiguous route/state.

`MISMATCH` is a route-integrity defect. It is marked CRITICAL when it can alter payment, authentication, account security, or user recovery behavior; otherwise it is MAJOR.

## Method

- Every source PNG is opened visually; filenames are never used as the only evidence.
- Locale pairs are allowed to differ visually while remaining exact matches if both depict the named state.
- Exact visual duplicates are separately recorded in `duplicate-screen-review.md`; a duplicate can still have a mismatched filename.
- No source screenshot is renamed or altered by this review.

## Coverage tracker

| Folder | Screens | Audited | Exact | Partial | Mismatch | Ambiguous |
|---|---:|---:|---:|---:|---:|---:|
| 01-entry | 9 | 9 | 9 | 0 | 0 | 0 |
| 02-discovery | 16 | 16 | 16 | 0 | 0 | 0 |
| 03-product | 8 | 8 | 8 | 0 | 0 | 0 |
| 04-auth | 20 | 20 | 20 | 0 | 0 | 0 |
| 05-cart-wishlist | 22 | 22 | 21 | 1 | 0 | 0 |
| 06-checkout | 52 | 52 | 52 | 0 | 0 | 0 |
| 07-orders | 37 | 37 | 36 | 1 | 0 | 0 |
| 08-account | 51 | 51 | 51 | 0 | 0 | 0 |
| 09-support-settings | 41 | 41 | 41 | 0 | 0 | 0 |
| 10-system-states | 63 | 63 | 52 | 0 | 11 | 0 |
| 11-arabic-rtl | 46 | 46 | 46 | 0 | 0 | 0 |
| **Total** | **365** | **365** | **352** | **2** | **11** | **0** |

## Results

The per-folder findings and the final mismatch index are appended after independent visual verification.

### 01-entry

Result: 9/9 audited — 9 EXACT_MATCH, 0 PARTIAL_MATCH, 0 MISMATCH, 0 AMBIGUOUS.

Every Entry filename accurately names the visible launch, language, loading, or onboarding state. The French/Arabic onboarding visuals differ in content but retain their correct step and intent. Preserve all filenames; record wrong flags, currencies, logo treatment, and embedded navigation only in the primary design validation.

### 02-discovery

Result: 16/16 audited — 16 EXACT_MATCH, 0 PARTIAL_MATCH, 0 MISMATCH, 0 AMBIGUOUS.

All Discovery filenames accurately identify their home, category, collection, search, promotion, and recently-viewed screen concepts. This does not make the assets implementation-safe: several remain rejected for foreign currencies, invalid buyer tabs, altered branding, or impractical mobile layout.

### 03-product

Result: 8/8 audited — 8 EXACT_MATCH, 0 PARTIAL_MATCH, 0 MISMATCH, 0 AMBIGUOUS.

All Product filenames accurately describe their visible detail, gallery, variant, specification, review, delivery/return, or add-to-cart state. The rejected full-detail and specifications assets are feasibility/content failures, not filename failures.

### 04-auth

Result: 20/20 audited — 20 EXACT_MATCH, 0 PARTIAL_MATCH, 0 MISMATCH, 0 AMBIGUOUS.

Every authentication filename accurately identifies the visible account, consent, password, login, OTP, or password-recovery state. Foreign prefixes/currencies and buyer-brand variants are design defects, not semantic naming defects.

### 05-cart-wishlist

Result: 22/22 audited — 21 EXACT_MATCH, 1 PARTIAL_MATCH, 0 MISMATCH, 0 AMBIGUOUS.

`05-cart-quantity-update-toast-fr.png` is not a toast: it is an inline/blocking “Mise à jour du panier…” loading overlay. Rename it before it becomes an implementation requirement, or document it as a cart-update loading state. The remaining filenames accurately represent their cart/wishlist concept.

### 06-checkout

Result: 52/52 audited — 52 EXACT_MATCH, 0 PARTIAL_MATCH, 0 MISMATCH, 0 AMBIGUOUS.

All Checkout filenames accurately describe their address, delivery, payment, order-review, redirect, or outcome state. Version pairs and payment-logic contradictions remain covered by the primary validation and duplicate review, not this semantic check.

### 07-orders

Result: 37/37 audited — 36 EXACT_MATCH, 1 PARTIAL_MATCH, 0 MISMATCH, 0 AMBIGUOUS.

`07-order-tracking-timeline-realtime-fr.png` visibly provides a status timeline and ETA but no live map or courier telemetry. Treat it as a timeline unless real-time behavior is confirmed; the “realtime” claim should not drive a product contract.

### 08-account

Result: 51/51 audited — 51 EXACT_MATCH, 0 PARTIAL_MATCH, 0 MISMATCH, 0 AMBIGUOUS.

All Account filenames correctly identify their account, address, payment, preferences, language, security, or dashboard state.

### 09-support-settings

Result: 41/41 audited — 41 EXACT_MATCH, 0 PARTIAL_MATCH, 0 MISMATCH, 0 AMBIGUOUS.

All Support and Settings filenames accurately identify the visible help, tickets, chat, FAQ, policy, notification, or permission state. The primary report still identifies many visual/UX concerns; no route-name mismatch was found.

### 10-system-states

Result: 63/63 audited — 52 EXACT_MATCH, 0 PARTIAL_MATCH, 11 MISMATCH, 0 AMBIGUOUS.

These filenames must not be trusted until corrected or mapped in an explicit manifest:

| Filename | Visible state | Severity |
|---|---|---|
| `10-access-denied-403-fr.png` | Unusual-activity verification hold | CRITICAL |
| `10-account-blocked-fr.png` | Generic access-denied / 403 | CRITICAL |
| `10-account-sync-progress-fr.png` | Offline cached-content state | MAJOR |
| `10-app-up-to-date-fr.png` | Slow-connection waiting state | MAJOR |
| `10-cache-clearing-progress-fr.png` | Session expired / reauthentication | CRITICAL |
| `10-notifications-disabled-fr.png` | Offline connection failure | CRITICAL |
| `10-offline-cached-content-fr.png` | Notification permission-disabled state | CRITICAL |
| `10-offline-fr.png` | App-up-to-date confirmation | CRITICAL |
| `10-server-unavailable-fr.png` | Local data refresh/update progress | CRITICAL |
| `10-session-expired-fr.png` | Rate-limit / time-limited account block | CRITICAL |
| `10-unusual-activity-detected-fr.png` | Server-unavailable error; exact duplicate of the detailed server error visual | CRITICAL |

All 11 are implementation-route risks: a developer following their current filename could wire the wrong recovery, security, or connectivity behavior.

### 11-arabic-rtl

Result: 46/46 audited — 46 EXACT_MATCH, 0 PARTIAL_MATCH, 0 MISMATCH, 0 AMBIGUOUS.

Every Arabic RTL filename accurately identifies its visible route/state. The broad RTL, branding, currency, and navigation corrections required in the primary validation remain independent of filename accuracy.

## Final route-integrity result

All 365 buyer screenshots were independently checked against their visible content: 352 EXACT_MATCH, 2 PARTIAL_MATCH, 11 MISMATCH, and 0 AMBIGUOUS. The complete row-level evidence is in [filename-route-integrity.csv](./filename-route-integrity.csv). Do not use the 11 mismatched System State filenames as implementation contracts; use the visible state mapping above until source assets are renamed or a route manifest is introduced.
