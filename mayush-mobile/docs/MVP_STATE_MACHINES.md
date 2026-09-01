# Mayush Mobile Buyer MVP - Application State Machines

## Overview

This document specifies deterministic state machines governing client-side state transitions within the Mayush Mobile application.

---

## 1. Application Initialization State Machine

```
[ Uninitialized ]
       │
       ▼ (App Launch)
[ Restoring Configuration ] ──(Error)──► [ Recoverable Failure State ]
       │                                         │
       ├─► Read Saved Language ('fr' | 'ar')     └─► (Retry) ──► [ Restoring Configuration ]
       ├─► Read Sanctum Token from SecureStore
       └─► Read existing `temp_user_id` from AsyncStorage (Do NOT generate if absent)
       │
       ▼ (Complete)
[ Validating Connectivity ] ──(No Internet)──► [ Offline Mode ]
       │                                           │
       ▼ (Online)                                  └─► (Network Restored) ──► [ Active Ready ]
[ Active Ready ]
```

---

## 2. Canonical `temp_user_id` & Cart Continuity State Machine

```
[ Guest Cart Identity: 'absent' ]
       │
       ▼ (First POST /api/v2/carts/add without temp_user_id)
[ Server Generates & Returns `temp_user_id` ]
       │
       ▼ (Persist returned `temp_user_id` in AsyncStorage)
[ Guest Cart Identity: 'active' (tempUserId: "mob_xxx") ]
       │
       ▼ (User Initiates Checkout -> Auth Gate -> Login / Signup)
[ Guest Cart Identity: 'merge_pending' (tempUserId: "mob_xxx") ]
       │
       ▼ (App sends `temp_user_id` in /login or /signup payload)
[ Server Reassigns Cart Items (`user_id = X, temp_user_id = NULL`) ]
       │
       ▼ (App Receives Auth Token, Queries `POST /api/v2/carts`)
       │
       ├─────────────────────────────────┐
       ▼ (Authoritative Cart Verified)   ▼ (Merge Verification Error)
[ 'merge_verified' ]            [ 'merge_failed' (tempUserId retained) ]
       │                                 │
       ▼                                 └─► Allow Retry / Reconciliation
[ Clear `temp_user_id` from Storage ]
       │
       ▼ (Bypasses Home)
[ Resume Checkout (Address Screen) ]
```

---

## 3. CMI Mobile Bridge Transaction State Machine

```
1. [ INITIATED ]: Order created on backend via `POST /api/v2/order/store`. `{combined_order_id}` returned.
2. [ BRIDGE_REQUESTED ]: App requests single-use bridge credential via secure HTTPS POST (NO Bearer tokens in query string!).
3. [ MODAL_LAUNCHED ]: App opens `WebBrowser.openAuthSessionAsync()` targeting CMI bridge route.
4. [ CMI_PROCESSING ]: User enters card details on CMI 3D-Secure Hosted Form.
5. [ SERVER_CALLBACK ]: CMI postback to `/cmi/callback` passes SHA-512 ver3 hash verification & idempotency check (`CmiCallbackLog`). Server marks order `paid`.
6. [ WEB_RETURN ]: CMI redirects to bridge return URL, returning deep link `mayush://order-confirmation?combined_id={combined_order_id}`.
7. [ VERIFY_STATUS ]: Browser return is treated purely as UI trigger signal. App queries `GET /api/v2/purchase-history-details/{order_id}`.
8. [ CONFIRMED ]: Render `SCR-CHK-009` (Order Confirmation).
```
