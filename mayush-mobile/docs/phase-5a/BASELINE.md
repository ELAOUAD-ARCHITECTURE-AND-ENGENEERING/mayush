# Phase 5A Frontend Stabilization Baseline

Recorded: 2026-08-05 (UTC)

## Scope

This baseline preserves the existing Expo mobile frontend before native splash
correction and visual-alignment work. It intentionally excludes checkout and
does not add new product, cart, wishlist, account, or authentication features.

## Git hygiene

`mayush-mobile/.gitignore` excludes:

- dependencies and Expo metadata (`node_modules/`, `.expo/`, `.expo-shared/`)
- generated outputs (`dist/`, `web-build/`, `coverage/`, `ios/`, `android/`)
- local credentials and environment overrides (`.env`, `.env.*`, `*.pem`, key files)
- debug and crash output (`*.log`, `*.stackdump`)

Package lockfiles and source/reference assets remain tracked.

## Verification record

Executed from `mayush-mobile/`:

```text
npx tsc --noEmit
Result: PASS (exit 0)

npm test
Result: PASS — 45 passed, 0 failed
```

## Currency baseline

The Laravel `system_default_currency` resolves to `MAD`. The mobile fallback
display configuration mirrors that setting; server-formatted API prices remain
authoritative for Phase 5B integration.

