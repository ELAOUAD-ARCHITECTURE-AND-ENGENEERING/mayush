# Mayush Authorization Security Audit & Implementation Report

## Executive Summary
A comprehensive security audit of Mayush Marketplace's authorization layer was conducted, focusing on policies, gates, middleware, and IDOR (Insecure Direct Object Reference) prevention. The audit successfully identified and patched several critical IDOR vectors where standard `customer` users could bypass `seller` scoping checks and maliciously modify or delete cross-tenant data. 

The system has been refactored from a vulnerable "manual check" paradigm to a robust **Zero-Trust / Policy-First** architecture.

---

## 1. Role Model & Authorization Architecture
The application uses a hybrid role model based on the `users.user_type` column (`admin`, `staff`, `seller`, `customer`, `delivery_boy`) combined with Spatie `HasRoles` for granular staff permissions.

Policies now serve as the central source of truth for object-level authorization, eliminating scattered, hardcoded `user_type` checks in controllers.

---

## 2. Identified Vulnerabilities (Now Patched)

### **Critical Vulnerability: Customer Bypass in AizUploadController**
**Description:**
The original implementation used conditional logic that inadvertently allowed `customer` users to bypass the `seller` scoping block:
```php
if (auth()->user()->user_type == 'seller' && $upload->user_id != auth()->user()->id) {
    abort(403);
}
```
If a user logged in as a `customer` intercepted the request to `DELETE /aiz-uploader/destroy/{id}`, the statement evaluated to `false`, granting them unrestricted access to delete any file on the entire system.

**Resolution:**
The logic was replaced with a strict `$this->authorize('delete', $upload)` call governed by the new `UploadPolicy`, completely closing this attack vector.

### **High Vulnerability: Implicit Query Bypasses**
**Description:**
Several bulk methods (e.g., `trash`, `restore`, `bulk_force_delete`) attempted to scope queries using:
```php
if (auth()->user()->user_type == 'seller') {
    $uploads->where('user_id', auth()->user()->id);
}
```
This allowed `customer` users to bypass the `where` clause entirely.

**Resolution:**
The logic was inverted to a "default deny" scope:
```php
if (auth()->user()->user_type != 'admin') {
    $uploads->where('user_id', auth()->user()->id);
}
```

### **Horizontal Privilege Escalation in Seller Products & Orders**
Controllers such as `ProductController`, `OrderController`, and `DigitalProductController` relied on manual `abort(403)` checks. Replaced with `$this->authorize()`.

---

## 3. Zero-Trust Policy Layer Implemented

The following Laravel Policies were developed and registered in the `AuthServiceProvider`:

- **`UploadPolicy`**: Mandates strict ownership `$user->id === $upload->user_id` for viewing, deleting, or restoring uploads. Admins have global override.
- **`ProductPolicy`**: Governs updates and deletion of products to the exact seller who created them.
- **`OrderPolicy`**: Explicitly isolates order visibility between the purchasing customer, the fulfillment seller, and the delivery boy. Admins have global access.
- **`RefundPolicy`**: Restricts viewing refund requests to the requesting customer and the targeted seller.
- **`ReviewPolicy`**: Only the authoring customer can update a review.
- **`ShopPolicy`**: Manages financial data visibility for shop owners.
- **`SystemLogPolicy`**: Restricts log viewing to `staff` with explicit Spatie roles (`manage_payments`, `view_system_logs`).

---

## 4. Policy-to-Model Mappings

| Model | Policy | Controllers Protected |
|---|---|---|
| `Upload` | `UploadPolicy` | `AizUploadController` |
| `Product` | `ProductPolicy` | `Seller\ProductController`, `Seller\DigitalProductController` |
| `Order` | `OrderPolicy` | `Seller\OrderController` |
| `Review` | `ReviewPolicy` | `ReviewController` |
| `RefundRequest` | `RefundPolicy` | `RefundRequestController` |
| `Shop` | `ShopPolicy` | `ShopController` (Implicit) |
| `system-log` | `SystemLogPolicy` | Global Gates / Middleware |

---

## 5. Automated Verification (32 Test Scenarios)

A dedicated security test suite `tests/Feature/Security/AuthorizationPolicyTest.php` was expanded to 32 scenarios actively attempting exploits against the new policy architecture:

**Uploads**
1. customer cannot delete any upload
2. seller cannot delete another seller's upload
3. seller can delete own upload
4. admin can delete any upload
5. restore/trash/force-delete bulk actions respect ownership

**Products**
6. seller cannot edit another seller's product
7. seller can edit own product
8. seller cannot delete another seller's product
9. seller can delete own product
10. admin can manage any product

**Digital Products**
11. seller cannot edit another seller's digital product
12. seller can edit own digital product

**Orders**
13. seller cannot view another seller's order
14. seller can view own order
15. customer cannot view another customer's order
16. customer can view own order
17. admin/staff can view orders according to existing role logic

**Refunds**
18. customer can request refund only for own order
19. seller cannot approve/refuse refunds outside own scope
20. admin/staff refund actions remain authorized

**Reviews**
21. customer cannot moderate reviews
22. seller cannot moderate unrelated reviews
23. admin/staff can moderate reviews
24. review ownership rules are respected

**Shops**
25. seller can manage own shop
26. seller cannot manage another seller's shop

**Analytics & Logs**
27. seller analytics are scoped to authenticated seller
28. seller cannot view another seller's analytics
29. customer cannot access system logs
30. seller cannot access system logs
31. admin/staff can access system logs
32. payment attempts and CMI callback logs remain admin/staff only

---

## 6. Remaining Risks & Known Limitations
1. **Legacy Routes**: While the primary `Seller\` controllers are hardened, older backend routes that rely heavily on `user_type` instead of policies still exist. These were not rewritten to avoid breaking changes.
2. **Bulk Actions**: The query scoping fix in `AizUploadController` is secure, but a true policy-driven bulk action architecture (using `Gate::allows` over collections) would be cleaner in future refactoring.
3. **No External Route Tests for All Items**: Some features like Refunds and Reviews use direct policy checks in the test suite rather than invoking live routes, because creating complex multi-step checkout state for every test is outside the scope of this security sprint.
