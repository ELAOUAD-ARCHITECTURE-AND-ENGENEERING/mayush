# STEP 8A.1 — Canonical mapping reconciliation (superseded metrics repaired)

This generated artifact is retained for historical continuity. The current implementation audit and Step 8B.0 preparation supersede earlier inferred node maps and invented filenames.

## Deterministic canonical metrics

- Figma screen/state completeness: **151/207 (72.9%)**
- Exact prototype connection completeness: **45/206 (21.8%)**
- Mismatched connections: **15**
- Missing connections: **146**

## Order-node correction

- `309:712`: Buyer Orders list — implemented by `OrdersListScreen.tsx`.
- `309:716`: canonical buyer order detail in preparation — **MISSING** until Step 8B.
- `309:737`: delivery-delay notification — **MISSING** and not the legacy Order Detail.
- Nodes whose frame names begin `07-` are classified **Buyer Orders & Fulfillment**, not seller/admin mobile functionality.

## Source-of-truth rules

The generator requires explicit semantic evidence, validates mapped files and real ScreenKeys, permits only explicit MODAL/SHEET/INLINE_STATE non-routes, rejects frame identity collisions, rejects implemented connections with missing destinations, validates route-map summary counts, and emits byte-stable JSON without generation timestamps.
