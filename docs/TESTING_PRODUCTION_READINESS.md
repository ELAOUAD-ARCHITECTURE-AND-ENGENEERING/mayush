# Mayush Platform: Production Readiness Testing & Launching Checklist

This document details the automated Production Readiness test suite designed for the Mayush multi-vendor e-commerce platform. It provides a comprehensive guide to running the suite, understanding the tested domains, and troubleshooting failures.

---

## 1. Overview of the Test Suite

The Production Readiness test suite validates all high-risk business flows and security barriers of the platform prior to production deployments. By utilizing an in-memory SQLite configuration (`phpunit.sqlite.xml`), the test suite executes in under five minutes without modifying local database states or making external web service calls.

---

## 2. Tested Business Groups

The suite covers **10 critical areas** of the application:

### ① Application Boot & Basic Storefront UI Rendering
* **File:** [ApplicationBootTest.php](file:///c:/xampp/htdocs/mayush/tests/Feature/ProductionReadiness/ApplicationBootTest.php)
* **Description:** Ensures the core homepages, categories, and product detail pages resolve with successful `200` status codes and do not fail on basic syntax, route changes, or view asset load errors.

### ② Blog Engine & Storytelling Content Pages
* **File:** [BlogContentTest.php](file:///c:/xampp/htdocs/mayush/tests/Feature/ProductionReadiness/BlogContentTest.php)
* **Description:** Verifies that articles, blog lists, and storyteller biographies are viewable by guests, and that active/inactive post statuses are handled correctly.

### ③ Queue Operations & Job Safety
* **File:** [QueueJobSafetyTest.php](file:///c:/xampp/htdocs/mayush/tests/Feature/ProductionReadiness/QueueJobSafetyTest.php)
* **Description:** Validates queue connection setups, checks Horizon configuration, and confirms that image optimization derivative jobs (WebP generation, queue dispatches) operate securely.

### ④ Checkout Flow Integrity
* **File:** [CheckoutFlowTest.php](file:///c:/xampp/htdocs/mayush/tests/Feature/ProductionReadiness/CheckoutFlowTest.php)
* **Description:** Tests the step-by-step cart and checkout flows (applying shipping options, flat rate rules, multi-vendor order splits) to guarantee users can reach the payment phase without database integrity errors.

### ⑤ Payment Integrity & Anti-Tampering (CMI Portal)
* **File:** [CmiPaymentSafetyTest.php](file:///c:/xampp/htdocs/mayush/tests/Feature/ProductionReadiness/CmiPaymentSafetyTest.php)
* **Description:** Checks payment callbacks, validation signatures, and hash verification algorithms. Simulates malicious request tampering (hash manipulation, altered prices) to ensure the callback controller rejects unauthorized payments.

### ⑥ Express Buy / One-Click Purchase
* **File:** [ExpressBuySafetyTest.php](file:///c:/xampp/htdocs/mayush/tests/Feature/ProductionReadiness/ExpressBuySafetyTest.php)
* **Description:** Validates the bypass rules of the "Buy Now" checkout pipeline, ensuring quick purchases bypass intermediate steps while enforcing authorization, correct stock reduction, and proper pricing calculations.

### ⑦ Shipping Integration Safety (ONESSTA 3PL)
* **File:** [OnesstaShippingSafetyTest.php](file:///c:/xampp/htdocs/mayush/tests/Feature/ProductionReadiness/OnesstaShippingSafetyTest.php)
* **Description:** Mocks the ONESSTA shipping API endpoints to test label generation, rate fetching, and fulfillment dispatch triggers. Confirms that failures in ONESSTA services do not crash order processing.

### ⑧ Multi-Vendor Isolation / Security Bounds
* **File:** [SellerIsolationTest.php](file:///c:/xampp/htdocs/mayush/tests/Feature/ProductionReadiness/SellerIsolationTest.php)
* **Description:** Enforces vendor multi-tenancy. Asserts that sellers cannot view other sellers' orders, dashboards, or edit another seller's products via route parameter injection or ID enumeration.

### ⑨ Core Security Smoke Tests
* **File:** [SecuritySmokeTest.php](file:///c:/xampp/htdocs/mayush/tests/Feature/ProductionReadiness/SecuritySmokeTest.php)
* **Description:** Performs penetration-testing smoke checks, including verifying that dangerous file extensions (e.g. `.exe`, `.sh`, `.php`) are blocked on media upload routes, and that admin dashboards reject non-admin users.

### ⑩ Semantic Search & Fallback Integrity
* **File:** [SearchFallbackTest.php](file:///c:/xampp/htdocs/mayush/tests/Feature/ProductionReadiness/SearchFallbackTest.php)
* **Description:** Validates search flows using Gemini text-embeddings. Tests fallback behaviors, ensuring that if external LLM APIs fail or return rate-limit errors, the store automatically reverts to standard text/SQL search patterns.

---

## 3. Running the Test Suite

### Local Environment
To run the production-readiness tests in your local development environment:

```bash
# Clean application cache
php artisan optimize:clear

# Execute the test suite with SQLite in-memory configuration
vendor/bin/phpunit --configuration=phpunit.sqlite.xml --filter=ProductionReadiness
```

### Continuous Integration (CI/CD)
Integrate this command into your GitHub Actions / GitLab CI pipeline before deploying to staging/production:

```yaml
- name: Run Production Readiness Tests
  run: vendor/bin/phpunit --configuration=phpunit.sqlite.xml --filter=ProductionReadiness
  env:
    APP_ENV: testing
    DB_CONNECTION: sqlite
    DB_DATABASE: ":memory:"
```

---

## 4. Remediation Checklist for Common Failures

| Symptom / Failure | Probable Cause | Remediation |
| :--- | :--- | :--- |
| **500 Error in Order Details View** | Order view blades accessing null attributes (e.g., GST or order detail metrics). | Ensure mock orders create corresponding `OrderDetail` records with necessary fields in tests. |
| **302 Redirect instead of 403 Forbidden on Seller Routes** | The seller's shop is not set to `approved` in the test database, triggering the `SellerApproved` onboarding redirect. | Make sure to create the Seller's `Shop` model with `'approval_status' => 'approved'` in the test setup. |
| **403 Forbidden on Admin Routes** | Spatie permission Gates rejecting requests, or `get_admin()` helper returning null. | Use the `SeedsAppConfigs` trait to seed the admin user, and verify `tests/TestCase.php` bypasses Gate permissions for admin. |
| **302 Redirect on CmiPaymentCallback** | Hash calculation verification failing on callback payload. | Use the helper method in `CmiPaymentSafetyTest.php` to calculate and attach a valid hash using the mocked CMI store key. |
| **SQL State Constraint Errors during Checkout** | Missing required table properties (e.g., `shipping_cost`, `shipping_type`) on the temporary cart records. | Add flat rate shipping rules and valid shipping prices when creating mock carts. |
