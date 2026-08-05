# Mayush Mobile Buyer MVP - Document Precedence & Authority Policy

## Executive Principle

To ensure complete clarity and eliminate ambiguity across all current and future development sessions (Codex, Claude, human engineers), this document establishes the **absolute hierarchy of authority** for all project specifications.

---

## 1. Hierarchy of Precedence Order

When specifications, requirements, or values in documentation disagree, the following order of precedence MUST be strictly applied:

1. **Actual Laravel Source Code** (`app/`, `routes/`, `config/`) — The ultimate source of truth for runtime behavior.
2. **`PHASE_3_HANDOFF_FREEZE.md` & `PHASE_3_CONTRACT_LOCK_REPORT.md`** — The frozen, locked Phase 3 specification.
3. **`MVP_SCREEN_CONTRACT.md`** — Canonical UI screen and state definitions.
4. **`MVP_SCREEN_API_MATRIX.csv`** — RFC-compliant UI-to-API trigger mapping.
5. **`MVP_STATE_MACHINES.md`** — Deterministic client-side state machine specifications.
6. **`MVP_SERVER_AUTHORITY.md`** — Authoritative financial and inventory rules.
7. **`MVP_DATA_REQUIREMENTS.md`** — Headers, storage targets, and data persistence contracts.
8. **`MVP_ACCEPTANCE_CRITERIA.md`** — Testable acceptance criteria (Given/When/Then).
9. **`CMI_MOBILE_BRIDGE_REQUIREMENTS.md`** — Release dependency requirements for CMI gateway.
10. **Earlier Phase 2 Audit Documents** (`backend-capability-audit.md`, `backend-mvp-gap-analysis.md`, etc.) — Historical audit logs.

---

## 2. Policy for Historical Phase 2 Documents

- All Phase 2 audit documents are **historical records**.
- Where an earlier Phase 2 statement conflicts with a Phase 3 locked contract, **the Phase 3 locked contract is strictly authoritative**.
- Future sessions and developers **MUST NEVER recover deprecated or obsolete values from Phase 2 documents**.

---

## 3. Canonical Project Values Summary

- **App Language Header**: `App-Language: fr | ar`
- **Supported Backend Languages**: `fr | ar | en`
- **MVP Selectable Languages**: `fr | ar`
- **Verified Payment Types**: `cash_on_delivery`, `cmi`, `wallet`
- **CMI Mobile Classification**: `REQUIRES_SECURE_MOBILE_BRIDGE` (Release Dependency)
- **Social Login Status**: `FUTURE_PHASE_BACKEND_CAPABILITY` (Excluded from MVP app scope)
- **Guest-Cart Token Origin**: Backend generates `temp_user_id` on first add when absent; mobile stores returned value.
- **Cart Token Clearance**: Cleared ONLY after authenticated-cart merge is verified.
- **Bearer Token Security**: `Authorization: Bearer <token>` MUST NEVER be sent in URL query strings.
