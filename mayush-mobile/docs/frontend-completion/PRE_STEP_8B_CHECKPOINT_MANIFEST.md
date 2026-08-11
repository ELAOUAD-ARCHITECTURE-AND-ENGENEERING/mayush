# Pre-Step-8B Checkpoint Staging Manifest

Date: 2026-08-11
Branch: `mobile-app`
Checkpoint status: **FIGMA GATE VALIDATED — READY FOR MANIFESTED STAGING**

## Gate status

- Secret scan: **NO HARDCODED FIGMA SECRET FOUND**
- Rotated/connected credential: **FIGMA NEW CREDENTIAL: VALID**
- Unknown worktree paths: **0**
- Staging performed: **Yes — manifest scopes only**
- Commit performed: **Authorized as the coherent frontend checkpoint**

Codex's connected Figma integration performed a read-only request against file `wAdLNmlKanvI0AEPyEbrMs`. Page `309:581` and nodes `309:716–723` all resolved on `Full App Prototype Flow` with the expected live names. No Figma node was modified.

## Worktree classification

The complete `git status --porcelain=v1 -uall` inventory was classified before this manifest was added:

| Category | Visible paths | Checkpoint disposition |
|---|---:|---|
| A. Application source | 149 | Include |
| B. Tests | 6 | Include |
| C. Audit/documentation | 77 | Include |
| D. Design reference | 18 | Include |
| E. Generated canonical audit output | 3 | Include |
| F. Visual-QA generated result files | 17 | Exclude |
| G. Machine-local cache | 1 | Exclude |
| H. Unknown | 0 | Block if nonzero |

There are additionally 160 ignored temporary PNG captures/previews. This manifest and the final checkpoint report are intended additions to category C, making the eventual proposed legitimate checkpoint set 255 paths at the current baseline.

## Files proposed for staging

The proposed checkpoint is a single coherent Mayush Mobile frontend baseline. The following scopes are unambiguous and include every legitimate dependency currently required to reproduce the app:

### A. Application source and configuration

- `.gitignore`
- `.env.example` (variable names only; no value)
- `App.tsx`
- `package.json`
- `package-lock.json`
- `tsconfig.json`
- `tsconfig.tools.json`
- every changed or untracked file under `src/**`, including commerce state, navigation, screens, components, content, contracts, services, design-system code, and the visual-QA development registry

### B. Tests

- `scripts/run-tests.js`
- `scripts/run-step8b0-behavior-tests.js`
- every file under `tests/**`
- changed test files under `src/**/__tests__/**`

### C. Audit tooling and documentation

- `docs/mvp-progress.md`
- `docs/mvp-state.json`
- every Markdown/CSV/JSON audit artifact under `docs/frontend-completion/**`, except ignored preview images
- every file under `docs/phase-5c/**`
- every file under `docs/phase-5d/**`
- every source/config file under `scripts/frontend-audit/**`
- every source/config file under `scripts/visual-qa/**`

### D. Design reference

- `design-reference/mayush-mobile-design/figma-handoff/figma-pixel-parity-state.json`
- `design-reference/mayush-mobile-design/figma-handoff/figma-prototype-route-map.json`
- `design-reference/mayush-mobile-design/figma-handoff/figma-prototype-route-map.md`
- every Figma reference image under `design-reference/step-7a/**`
- every Figma reference image under `design-reference/step-7b/**`

### E. Generated canonical outputs

- `docs/frontend-completion/canonical-figma-screen-registry.json`
- `docs/frontend-completion/prototype-gap-audit.json`
- `docs/frontend-completion/STEP_8A_1_CANONICAL_MAPPING_RECONCILIATION.md`

These generated files are included because they are deterministic, current canonical evidence and are reproduced byte-for-byte by `build-canonical-registry.js`.

## Files intentionally excluded

### Local secret/environment

- `.env`, `.env.*` except example templates
- credentials, tokens, private keys, and local environment overrides

Reason: machine-local secret material must never be staged. Current secret scan found no hardcoded Figma secret.

### Build output and tool caches

- `dist/**`
- `.expo/**`
- `node_modules/**`
- `coverage/**`
- `expo-env.d.ts`
- repository-root `.phpunit.cache/test-results`

Reason: reproducible build output or machine-local cache. The PHP cache is already tracked historically, so it remains visibly modified but must be omitted from this mobile checkpoint.

### Temporary visual-QA captures

- all `app-*-393x852.png`
- all `figma-source-live.png`
- all `overlay-*.png`
- all `pixel-diff-*.png`
- all `side-by-side-*.png`
- `docs/frontend-completion/previews/**`

Reason: 160 reproducible comparison captures/previews are not frontend source or canonical evidence. Scoped `.gitignore` rules now protect these paths.

### Generated visual comparison results

- the 17 modified `design-reference/mayush-mobile-design/validation/phase-5b-pixel-parity/*/result.json` files

Reason: these are pixel-comparison outputs from prior visual-QA activity, not required to reproduce the functional frontend and explicitly outside this operational checkpoint. They are tracked historically, so they will remain uncommitted unless separately reviewed.

## Expected commit structure

**Strategy A: one coherent frontend checkpoint, followed by its operational report**

Suggested commit:

`chore(mobile): checkpoint frontend foundation before order tracking expansion`

This is safer than splitting the accumulated frontend into artificial commits because RootNavigator, the untracked account/auth/support screens, state modules, tests, audit tools, route map, and canonical outputs depend on each other. A Step 8B.0-only commit would not reproduce the verified application from a clean checkout.

## Unresolved unknown files

None. Category H contains zero paths.

## Checkpoint authorization

The Figma and verification gates now pass. Stage only the included scopes above. Create the coherent frontend checkpoint first; then record its immutable hash in `PRE_STEP_8B_CHECKPOINT_REPORT.md` as a documentation-only follow-up commit.
