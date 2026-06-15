# Mayush Payment & Checkout Hardening Documentation

## 1. Current CMI Flow
CMI redirects users to the external gateway portal using a signed payload. On success/failure, the gateway posts an asynchronous Server-to-Server callback and redirects the user back to the application.

## 2. Current Express Buy Flow
Express Buy permits single-click checkouts by deducting inventory immediately, verifying session fingerprints, and using the vaulted `recurringTrxnRef` to silently authorize CMI payments without user interaction.

## 3. Payment Attempt Lifecycle
Payment Attempts (`payment_attempts` table) track the user's intent to pay:
- `initiated`: User clicked Pay and redirected to CMI.
- `paid`: Callback successfully matched and cleared.
- `failed`: Callback returned an error or ProcReturnCode != 00.

## 4. Callback Log Lifecycle
All CMI callbacks are logged to `cmi_callback_logs` before any processing:
- `received`: Initial state upon hitting the endpoint.
- `rejected`: Hash mismatch, missing OID, or invalid amount.
- `duplicate`: Already processed successfully.
- `processed`: Successfully transitioned the order to paid.

## 5. Idempotency Strategy
Idempotency is guaranteed at the database layer using the `CmiCallbackLog` checking `gateway_reference` combined with `processing_status = 'processed'`.

## 6. Duplicate Callback Handling
If a duplicate is detected, the callback is logged as `is_duplicate = true` and `processing_status = 'duplicate'`. The system returns `ACTION=POSTAUTH` to acknowledge the gateway without re-running any side effects (affiliate, shipping, emails).

## 7. Payment State Transition Rules
Managed by `PaymentStateService`:
- Orders can transition from `initiated` -> `pending` -> `paid` -> `failed`.
- Once `paid`, an order cannot transition back unless refunded (outside of this scope).
- Transitions use `DB::transaction()` and row-level `lockForUpdate()` to prevent race conditions.

## 8. Express Buy Protections
Express Buy utilizes a 15-second cache lock (`express_buy_submit_{user_id}`) to prevent double-clicks from spawning parallel checkouts, and uses `lockForUpdate` on inventory rows to prevent overselling.

## 9. Admin/Audit Process
Administrators can run `php artisan mayush:payments:audit` to instantly identify stale payments, duplicate callbacks, or shipped but unpaid orders.

## 10. Known Remaining Risks
- The gateway might fail to redirect the user back to the site, requiring them to rely entirely on the async webhook.
- Hard failures (DB crash) during webhook processing could leave a payment attempt in `initiated` while CMI considers it paid. The audit script helps detect this.

## 11. How to Test Payment Safety
Run the Production Readiness test suites:
`php artisan test --filter=CmiPaymentSafety`
`php artisan test --filter=ExpressBuySafety`

## 12. How to Reconcile Payment Issues Manually
Review the `cmi_callback_logs` table for the exact `raw_payload`. If `ProcReturnCode == '00'` but the order is unpaid, cross-reference the CMI merchant portal. Use `PaymentStateService::markOrderPaidSafely` to forcefully transition a stuck order.
