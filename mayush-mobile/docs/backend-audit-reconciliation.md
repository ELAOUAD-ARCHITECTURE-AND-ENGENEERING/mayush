# Mayush Mobile MVP - Backend Audit Reconciliation Report

> [!NOTE]  
> **DOCUMENT RECONCILIATION NOTICE**: This document has been reconciled by Phase 3. Where an earlier statement conflicts with [`PHASE_3_CONTRACT_LOCK_REPORT.md`](file:///c:/laragon/www/mayush/mayush-mobile/docs/PHASE_3_CONTRACT_LOCK_REPORT.md) or [`DOCUMENT_AUTHORITY.md`](file:///c:/laragon/www/mayush/mayush-mobile/docs/DOCUMENT_AUTHORITY.md), the Phase 3 locked contract is authoritative.

## Executive Summary

During Phase 3 (MVP Screen & Data Contract definition), source-code verification was conducted across Laravel routes, controllers, middleware, requests, resources, and services.

This document records the reconciled conclusions, empirical code evidence, and authoritative rules for the Mayush Mobile MVP.

---

## 1. Discrepancy Matrix

| Topic | Phase 2 Audit Claim | Source Code Evidence | Reconciled Correct Conclusion | Impact on Mobile MVP |
| :--- | :--- | :--- | :--- | :--- |
| **CMI Mobile Flow Initialization** | Claimed `READY_WITH_ADAPTER` | `CmiController@pay` checks `Session::has('combined_order_id')` & `Auth::user()`. Fails if session is absent. | Classified as `REQUIRES_SECURE_MOBILE_BRIDGE` (Release Dependency). | Expo `WebBrowser` requires a secure mobile bridge. Bearer tokens MUST NOT be passed in URL query strings. |
| **Localization Request Header** | Claimed `System-Language` or `App-Language` | `AppLanguage` middleware explicitly checks `$request->hasHeader('App-Language')`. | Canonical header is **`App-Language`** (`fr` \| `ar`). | Client HTTP interceptor must send `App-Language: fr \| ar`. |
| **Payment Type Identifiers** | Audit listed inconsistent strings (`cod`, `cash_payment`) | `OrderController@store`: `$allowedPaymentTypes = ['cmi', 'cash_on_delivery', 'wallet']`. | Canonical request enum values for `payment_type`: `"cash_on_delivery"`, `"cmi"`, `"wallet"`. | Order placement request DTO must use `"cash_on_delivery"`, `"cmi"`, or `"wallet"`. |
| **`temp_user_id` Generation & Lifecycle** | Audit claimed client generates token | `CartController@add` generates token if omitted and returns `'temp_user_id' => $temp_user_id` in JSON response. | Laravel generates `temp_user_id` on first add when absent; mobile stores returned value; cleared ONLY after merge verification. | Canonical 9-step guest-cart identity lifecycle defined (`GuestCartIdentityState`). |
| **Order Identifier Mapping** | Assumed `combined_order_id` is passed directly to order details | `PurchaseHistoryController@details($id)` queries `Order::where('id', $id)`. | `POST /order/store` returns `combined_order_id`. `purchase-history-details/{id}` expects individual `order_id` (`orders.id`). | App queries `/purchase-history` to locate individual `order_id` matching `combined_order_id`. |
