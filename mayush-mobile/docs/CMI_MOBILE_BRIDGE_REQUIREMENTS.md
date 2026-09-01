# Mayush Mobile MVP - CMI Mobile Bridge Requirements

## Executive Principle

> [!CAUTION]  
> The existing `CmiController@pay` endpoint in Laravel relies on web browser session state (`Session::get('combined_order_id')`) and web authentication (`Auth::user()`). Direct URL access from an Expo `WebBrowser` without session state will fail. A secure mobile web bridge MUST be implemented in a dedicated later phase prior to production release.

---

## 1. Security & Functional Requirements

1. **Authentication & Ownership Validation**:
   - The bridge must authenticate the mobile customer via Sanctum.
   - Must verify that the requested `CombinedOrder` exists and belongs to the authenticated `user_id`.
   - Must verify that `payment_type` is `"cmi"` and `payment_status` is `"unpaid"`.

2. **Strict Query Parameter Restrictions**:
   - > [!FORBIDDEN]  
     > Passing raw Sanctum Bearer tokens in URL query strings (e.g. `/cmi/mobile-pay?token=<sanctum-bearer-token>`) is **STRICTLY FORBIDDEN** due to URL logging and security vulnerability risks.
   - The bridge must issue a short-lived (60-second expiry), single-use bridge token or use web session cookie exchange.

3. **Replay & Tamper Protection**:
   - Single-use bridge token invalidated immediately upon first consumption.
   - Changing the `combined_order_id` or cross-customer order access is rejected.
   - Merchant secret key (`config('cmi.secret_key')`) remains strictly server-side.

4. **Deep Linking & Return Flow**:
   - CMI Hosted Payment Page redirects back to an approved HTTPS return URL.
   - Upon completion, the return URL redirects to app deep link: `mayush://order-confirmation?order_id={order_id}`.
   - **Browser return is treated purely as a UI trigger signal**. The mobile application MUST verify payment status via API call to `GET /api/v2/purchase-history-details/{id}`.

5. **Edge Case & Callback Handling**:
   - Handle server-to-server CMI webhook callback (`/cmi/callback`) idempotently using `CmiCallbackLog`.
   - Handle browser window closure, user cancellation, card rejection, and hash validation failures safely.
