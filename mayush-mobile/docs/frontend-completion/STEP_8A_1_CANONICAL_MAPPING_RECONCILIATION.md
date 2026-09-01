# STEP 8A.1 — Canonical mapping reconciliation (superseded metrics repaired)

This generated artifact is retained for historical continuity. The current implementation audit through Step 8C supersedes earlier inferred node maps and invented filenames.

## Deterministic canonical metrics

- Figma screen/state completeness: **207/207 (100.0%)**
- Exact prototype connection completeness: **66/206 (32.0%)**
- Mismatched connections: **45**
- Missing connections: **95**

## Order-node correction

- `309:712`: Buyer Orders list — implemented by `OrdersListScreen.tsx`.
- `309:716–723`: canonical buyer order detail, tracking, packages, and invoice — **IMPLEMENTED** in Step 8B.
- `309:724–731`: buyer cancellation, review, and reorder states — **IMPLEMENTED** in Step 8C as three semantically separate flows.
- `309:732–309:736`: return and refund workflow — **IMPLEMENTED** in Step 8D.
- `309:737+`: delivery issues, order support, and order system states — **MISSING** and deferred to Step 8E.
- `309:737`: delivery-delay notification — **MISSING** and not the legacy Order Detail.
- Nodes whose frame names begin `07-` are classified **Buyer Orders & Fulfillment**, not seller/admin mobile functionality.

## Source-of-truth rules

The generator requires explicit semantic evidence, validates mapped files and real ScreenKeys, permits only explicit MODAL/SHEET/INLINE_STATE non-routes, rejects frame identity collisions, rejects implemented connections with missing destinations, validates route-map summary counts, and emits byte-stable JSON without generation timestamps.
