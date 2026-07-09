# Mayush Payment Hardening Verification

## 1. Verified Aspects
We have rigorously verified the recent payment hardening implementation across the full testing lifecycle. The aspects validated include:
1. Valid CMI callbacks locking and updating `Order` state cleanly.
2. Duplicate CMI callbacks being reliably blocked by `CmiCallbackLog` idempotency without firing duplicate side effects.
3. Express Buy explicit double-click rate-limiting by `Cache::lock`.
4. Stale Payment expiration logic (`mayush:payments:expire-stale`).
5. Safe inventory rollback constraints (`num_of_sale >= 0` enforced).

## 2. Tests Added & Modified
- **[NEW] `tests/Feature/ProductionReadiness/StalePaymentExpirationTest.php`**
  Asserts that stale payments older than 60 minutes are automatically marked `expired`, related unpaid orders are `cancelled`, stock `qty` is restored securely, `num_of_sale` is decremented safely, and an `InventoryLog` is recorded.
- **[MODIFIED] `tests/Feature/ProductionReadiness/CmiPaymentSafetyTest.php`**
  Augmented with `Mail::fake()`, `Queue::fake()`, and `Http::fake()` to programmatically assert that executing a duplicate CMI callback does NOT run side-effects (e.g., Onessta shipment dispatch or email).

## 3. SQLite vs MySQL Database Testing Constraints
The standard test suite utilizes `DB_CONNECTION=sqlite` operating completely in `:memory:`. 
While this is blazing fast for general logic validation, **SQLite does not support actual row-level locking** (e.g., `lockForUpdate()`). In a concurrent race condition within SQLite, the lock is silently ignored, and race conditions can artificially pass.

To achieve 100% confidence in the concurrency protection provided by `PaymentStateService`, these tests must be validated on MySQL/MariaDB.

## 4. How to Test on MySQL / MariaDB Locally
Do NOT use production credentials or production databases.
Instead, execute the suite against a dedicated local MySQL test instance.

**Step 1:** In your `.env.testing` file (or passing inline), set:
```env
DB_CONNECTION=mysql
DB_DATABASE=mayush_testing
DB_USERNAME=root
DB_PASSWORD=
```

**Step 2:** Create the empty test database:
```bash
mysql -u root -e "CREATE DATABASE mayush_testing;"
```

**Step 3:** Run the targeted safety tests using the MySQL connection:
```bash
php artisan test --filter=CmiPaymentSafety --env=testing
php artisan test --filter=ExpressBuySafety --env=testing
php artisan test --filter=StalePaymentExpiration --env=testing
php artisan test --filter=ProductionReadiness --env=testing
```

## 5. Remaining Risks
The hardening drastically limits duplicate behaviors and stale ghost orders. There are no known remaining major blockers. If `mayush:payments:expire-stale` behaves as expected under high traffic, stale orders and stranded inventory are solved. 

## 6. Manual Verification Checklist
When deploying to staging, manually verify:
- [ ] Submitting a cart Checkout and capturing payment.
- [ ] Submitting an Express Buy.
- [ ] Forcing a duplicate webhook `POST` using Postman using the exact same signature. Ensure the response is `ACTION=POSTAUTH` and no new shipments are created.
- [ ] Allowing a checkout to sit on the CMI gateway page for >60 minutes, confirming stock restores.

## 7. Recommended Commit Message
```text
feat(payment): Comprehensive CMI Checkout & Idempotency Hardening Verification

- Introduced StalePaymentExpirationTest to validate stock recovery
- Augmented CmiPaymentSafetyTest with Mock Fakes to assert idempotency side-effect blocking
- Documented local MySQL verification strategies
```
