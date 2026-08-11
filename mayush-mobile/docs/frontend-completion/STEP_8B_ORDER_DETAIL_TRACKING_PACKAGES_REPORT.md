# Step 8B — Buyer Order Detail, Tracking & Multi-Package Fulfillment

Status: `IMPLEMENTED_WEB_CHECKED_NATIVE_VALIDATION_PENDING`

Scope ended at Figma node `309:723`. Cancellation (`309:724+`) and review/reorder (`309:728+`) were not implemented.

## 1. Live Figma verification

The connected live file `wAdLNmlKanvI0AEPyEbrMs`, page `309:581 Full App Prototype Flow`, was inspected before implementation. All eight targets exist with the exact requested names and 393×852 dimensions. Each target is a single flattened raster frame, so hierarchy and text were verified visually rather than inferred from child layers. No live/local naming collision was found.

| Node | Exact live frame | Live outgoing reaction |
|---|---|---|
| 309:716 | 07-order-detail-in-preparation-timeline-fr | 309:718 |
| 309:717 | 07-order-detail-shipped-tracking-fr | 309:718 |
| 309:718 | 07-order-tracking-timeline-realtime-fr | 309:719 |
| 309:719 | 07-order-detail-delivered-actions-fr | 309:728 (deferred) |
| 309:720 | 07-order-detail-multi-vendor-packages-fr | 309:721 |
| 309:721 | 07-multiple-packages-split-shipment-fr | 309:722 |
| 309:722 | 07-package-detail-items-shipping-info-fr | 309:723 |
| 309:723 | 07-invoice-detail-download-share-fr | 309:724 (deferred) |

Incoming reactions `309:712/713/715 → 309:716` were also observed.

## 2. Reuse audit

| Node | Classification | Result |
|---|---|---|
| 309:716 | REUSE_WITH_VARIANT | Shared canonical order detail, preparing variant |
| 309:717 | REUSE_WITH_VARIANT | Shared canonical order detail, shipped variant |
| 309:718 | NEW_SCREEN_REQUIRED | Deterministic order tracking timeline |
| 309:719 | REUSE_WITH_VARIANT | Shared canonical order detail, delivered variant |
| 309:720 | REUSE_WITH_VARIANT | Shared canonical order detail, multi-vendor variant |
| 309:721 | NEW_SCREEN_REQUIRED | Split package list |
| 309:722 | NEW_SCREEN_REQUIRED | Selected package detail |
| 309:723 | NEW_SCREEN_REQUIRED | Order-snapshot invoice preview |

The prior generic `OrderDetailsScreen` was migrated into a status-driven canonical screen; no second order model or parallel legacy detail flow remains.

## 3. New and reused components

Shared primitives in `OrderScreenComponents.tsx` provide the header, cards, status presentation, product rows, buttons, and event/date labels. `OrderDetailsScreen.tsx` supplies preparing, shipped, delivered, and multi-vendor variants. New focused screens are `OrderTrackingScreen`, `OrderPackagesScreen`, `OrderPackageDetailsScreen`, and `OrderInvoiceScreen`. Existing Mayush typography, icons, navigation, tokens, spacing, and product art are reused; no screenshot is used as a runtime background.

## 4. Route architecture

`RootNavigator` remains the navigator. Stable keys now cover `order-detail-preparing`, `order-detail-shipped`, `order-tracking`, `order-detail-delivered`, `order-detail-multi-vendor`, `order-packages`, `order-package-detail`, and `order-invoice`. Orders and order notifications first select an order in the repository, then `getCanonicalOrderDetailRoute` resolves its canonical detail route. Back navigation preserves `selectedOrderId`; an invalid package selection safely returns to the package/order flow.

## 5. Order repository extensions

`src/commerce/orderState.ts` remains the single buyer-order domain. It now carries deterministic carrier, tracking, delivery dates, tracking-event state, package seller/shipping metadata, delivery/discount totals, and invoice-preview metadata. Four coherent repository fixtures represent preparing, shipped, delivered, and multi-package orders. Package relationships use stable `lineIds`; validation rejects foreign, missing, or incompatible duplicate lines. `selectedPackageId` remains transient and is never stored inside a `BuyerOrder` or persisted snapshot.

## 6. Preparation detail

Node `309:716` renders the selected preparing order, its items, summary, address/payment context, and a coherent timeline whose future events are not completed. Domain identity uses the stable `preparing` key; French/Arabic labels are presentation only.

## 7. Shipped detail

Node `309:717` uses the same selected order and adds fixture-backed carrier, tracking number, shipment date, ETA, shipped state, and tracking CTA. The UI explicitly remains deterministic frontend data and does not claim a live carrier connection.

## 8. Tracking behavior

Node `309:718` derives events from the selected order. Completed/current/upcoming states remain coherent and opening tracking does not mutate delivery status. The Figma `309:718 → 309:719` reaction is not counted as exact because the runtime truthfully returns to the selected order’s actual status-specific detail rather than silently converting a shipped order into a delivered order.

## 9. Delivered detail

Node `309:719` exposes delivered-order actions and invoice access. Review/reorder/return/cancellation destinations remain truthful deferred notices; node `309:728` and connection `FIGMA-PROT-101` are not marked implemented.

## 10. Multi-vendor order

Node `309:720` groups buyer-order lines by package/seller under one `BuyerOrder`. It contains no seller login, fulfillment controls, inventory, dashboard, or administrative identity state.

## 11. Split shipments

Node `309:721` renders multiple packages belonging to one selected order, allowing package-specific seller, status, carrier, tracking, ETA, and item count. Package validation confirms complete, non-overlapping line membership.

## 12. Package detail

Node `309:722` resolves `selectedOrderId + selectedPackageId`, then derives its items from the parent order’s lines. Foreign or stale package IDs do not render another order’s data. Package selection remains transient across reloads.

## 13. Invoice detail

Node `309:723` renders an order-snapshot invoice preview with reference, buyer snapshot, items, quantities, prices, delivery, discount, total, payment method, and date where fixture-supported. No ICE, IF, RC, CNSS, tax-registration number, or other invented Moroccan legal identifier is present.

## 14. Backend and fixture boundaries

Tracking, package progress, and invoice preview are deterministic frontend fixtures. There is no polling, socket, carrier API, PDF issuance, filesystem download, native share dependency, or legal-invoice claim. Download/share controls report that integration is unavailable rather than claiming a file was created.

## 15. RTL implementation

All Step 8B screens use the theme language and structural RTL direction for headers, timeline/event rows, chevrons, status and package cards, product rows, totals, and actions. French LTR and Arabic RTL source paths compile and web-export. Native validation remains pending; no device claim is made.

## 16. Cross-domain order consistency

Orders List, notification navigation, support order selection, and checkout-created/persisted orders continue to use `BuyerOrderRepository`. Notification fixture references now resolve valid preparing/shipped orders. Support continues storing only `orderId`, and canonical route selection also applies to persisted checkout-created orders.

## 17. Regression tests

`node scripts/run-tests.js`: **417/417 PASS**.

## 18. Step 8B.0 behavior tests

`node scripts/run-step8b0-behavior-tests.js`: **11/11 PASS**.

## 19. New Step 8B behavior tests

`node scripts/run-step8b-behavior-tests.js`: **17/17 PASS**. The suite exercises real repository/state behavior for route resolution, selection continuity, tracking ownership, delivered actions, package integrity and validation, transient package state, invoice resolution, support/notification references, persisted checkout orders, reload behavior, and absence of seller/admin identity state.

## 20. TypeScript checks

- Application: `tsc --noEmit` — **PASS, 0 errors**.
- Tools/tests: `tsc --project tsconfig.tools.json --noEmit` — **PASS, 0 errors**.

The workspace `npx` shim is broken, so the equivalent checked-in local `.cmd` binaries were invoked directly.

## 21. Expo export

`expo export --platform web`: **PASS** (639 modules, web bundle emitted to `dist`).

## 22. Diff result

`git diff --check`: **PASS**.

## 23. Deterministic canonical hashes

Two consecutive generations produced identical SHA-256 values:

- `canonical-figma-screen-registry.json`: `7161E83AB55DB03C163983163C9F9BFF903D0E252A1856B2E011D1ACD864693C`
- `prototype-gap-audit.json`: `2CB2E92F1B5B0BA3C466A50DFF3360C25EC3FDE5DE620A4CAE46DD7BE508D024`

## 24. New screen metric

**159/207 IMPLEMENTED (76.8%)**, **48 MISSING**. This batch adds all eight scoped screens to the generated registry.

## 25. New interaction metric

**51/206 IMPLEMENTED (24.8%)**, **14 MISMATCHED**, **141 MISSING**. Exact connections added are `FIGMA-PROT-098`, `099`, `102`, `103`, and `104`, plus the verified incoming preparation connection classification. `100`, `101`, and `105` remain unimplemented/mismatched as required by runtime truth and scope.

## 26. Exact remaining missing nodes

- 309:591 — 02-home-logged-in-personalized-recommendations
- 309:659 — 05-cart-quantity-update-toast-fr
- 309:660 — 05-cart-modify-variant-bottom-sheet-fr
- 309:661 — 05-cart-multi-vendor-grouped-by-seller-fr
- 309:662 — 05-cart-invalid-promo-code-error-fr
- 309:663 — 05-cart-promo-applied-order-summary-fr
- 309:664 — 05-cart-promo-code-modal-available-offers-fr
- 309:665 — 05-cart-remove-item-confirmation-dialog-fr
- 309:683 — 06-city-selector-list-fr
- 309:684 — 06-delivery-zone-selector-fr
- 309:685 — 06-edit-address-form-fr
- 309:686 — 06-no-address-saved-empty-state-v2-fr
- 309:688 — 06-delivery-by-vendor-multi-seller-fr
- 309:689 — 06-delivery-unavailable-address-error-fr
- 309:691 — 06-pay-with-wallet-balance-fr
- 309:692 — 06-saved-payment-cards-visa-mastercard-fr
- 309:701 — 06-payment-confirmation-taking-longer-fr
- 309:702 — 06-payment-pending-confirmation-fr
- 309:704 — 06-terms-conditions-confirmation-fr
- 309:707 — 06-order-already-in-progress-duplicate-check-fr
- 309:708 — 06-order-needs-update-price-stock-changes-fr
- 309:709 — 06-checkout-skeleton-loading-state
- 309:710 — 06-checkout-error-loading-state-fr
- 309:713 — 07-orders-in-progress-tab-statuses-fr
- 309:714 — 07-orders-completed-tab-reorder-review-fr
- 309:715 — 07-orders-cancelled-tab-refund-statuses-fr
- 309:724 — 07-cancel-order-confirmation-dialog-fr
- 309:725 — 07-cancel-order-reason-form-fr
- 309:726 — 07-cancellation-request-registered-fr
- 309:727 — 07-order-cannot-be-cancelled-fr
- 309:728 — 07-rate-order-review-products-fr
- 309:729 — 07-reorder-articles-changed-unavailable-fr
- 309:730 — 07-reorder-items-added-to-cart-fr
- 309:731 — 07-reorder-with-availability-changes-fr
- 309:732 — 07-request-return-item-selection-fr
- 309:733 — 07-return-detail-items-refund-status-fr
- 309:734 — 07-return-tracking-timeline-fr
- 309:735 — 07-request-refund-cancelled-order-fr
- 309:736 — 07-refund-completed-success-fr
- 309:737 — 07-delivery-delayed-notification-fr
- 309:738 — 07-delivery-failed-reschedule-fr
- 309:739 — 07-support-order-contact-form-fr
- 309:740 — 07-tracking-unavailable-in-preparation-fr
- 309:741 — 07-order-not-found-error-fr
- 309:742 — 07-orders-empty-state-fr
- 309:743 — 07-orders-error-loading-state-fr
- 309:744 — 07-orders-skeleton-loading-state
- 309:745 — 07-order-detail-skeleton-loading-state

## 27. Exact remaining Buyer Orders & Fulfillment nodes

- 309:713 — 07-orders-in-progress-tab-statuses-fr
- 309:714 — 07-orders-completed-tab-reorder-review-fr
- 309:715 — 07-orders-cancelled-tab-refund-statuses-fr
- 309:724 — 07-cancel-order-confirmation-dialog-fr
- 309:725 — 07-cancel-order-reason-form-fr
- 309:726 — 07-cancellation-request-registered-fr
- 309:727 — 07-order-cannot-be-cancelled-fr
- 309:728 — 07-rate-order-review-products-fr
- 309:729 — 07-reorder-articles-changed-unavailable-fr
- 309:730 — 07-reorder-items-added-to-cart-fr
- 309:731 — 07-reorder-with-availability-changes-fr
- 309:732 — 07-request-return-item-selection-fr
- 309:733 — 07-return-detail-items-refund-status-fr
- 309:734 — 07-return-tracking-timeline-fr
- 309:735 — 07-request-refund-cancelled-order-fr
- 309:736 — 07-refund-completed-success-fr
- 309:737 — 07-delivery-delayed-notification-fr
- 309:738 — 07-delivery-failed-reschedule-fr
- 309:739 — 07-support-order-contact-form-fr
- 309:740 — 07-tracking-unavailable-in-preparation-fr
- 309:741 — 07-order-not-found-error-fr
- 309:742 — 07-orders-empty-state-fr
- 309:743 — 07-orders-error-loading-state-fr
- 309:744 — 07-orders-skeleton-loading-state
- 309:745 — 07-order-detail-skeleton-loading-state

## 28. Exact next task

**STEP 8C — BUYER ORDER CANCELLATION, REVIEW & REORDER**

Step 8C has not been started.
