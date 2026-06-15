# Mayush Observability Architecture & Implementation Plan

## 1. Executive Summary & Constraints

This RFC details the implementation of a production-safe, centralized observability layer for the Mayush Marketplace. The current system relies on disjointed CLI commands (`mayush:payments:audit`, `mayush:payments:expire-stale`) and has critical blind spots regarding queue health, real-time anomalies, and backend monitoring authorization.

**Strict Constraints Respected:**
- **Zero Business Logic Changes:** No checkout, payment, or shipping flows will be altered.
- **No Data Leakage:** PII and raw CMI payloads will not be exposed in the new log channels.
- **Protected Access:** All new dashboards (Pulse, Horizon, System Health) will be strictly guarded behind the existing AIZ `admin/staff` authorization layer.

---

## 2. Technical Findings & Security Flaws

1. **Horizon Production Lockout (Critical):** `app/Providers/HorizonServiceProvider.php` currently defines the `viewHorizon` gate using an empty array: `in_array(optional($user)->email, [])`. This completely blocks production access to the queue monitoring dashboard.
2. **Missing APM:** Laravel Pulse is missing, leaving us blind to slow N+1 queries or degrading storefront performance.
3. **Log Monolith:** All errors fall into a single `laravel.log` stack, making it impossible to isolate payment anomalies from standard application noise.

---

## 3. Implementation Phases

### Phase 2: Laravel Pulse (Application Performance Monitoring)
Pulse will be installed to monitor slow queries, slow requests, and exceptions. 

*   **Installation:** `composer require laravel/pulse`
*   **Tuning (`config/pulse.php`):** We will configure Pulse to ignore expected high-traffic static routes and keep the retention period short (7 days) to prevent MySQL bloat.
*   **Authorization Hook:** We will publish the provider and override the Gate:
    ```php
    // app/Providers/PulseServiceProvider.php
    Gate::define('viewPulse', function ($user = null) {
        return $user && in_array($user->user_type, ['admin', 'staff']);
    });
    ```

### Phase 3: Horizon Hardening (Queue Visibility)
We will fix the Horizon lockout by updating the `HorizonServiceProvider.php` gate to mirror Pulse:
```php
// app/Providers/HorizonServiceProvider.php
Gate::define('viewHorizon', function ($user = null) {
    return $user && in_array($user->user_type, ['admin', 'staff']);
});
```

### Phase 4: Custom System Health Dashboard (Admin UI)
We will build a custom, real-time health dashboard integrated natively into the AIZ Admin panel.

*   **Route:** `Route::get('/system-health', [SystemHealthController::class, 'index'])->name('admin.system.health');` inside the `['prefix' => 'admin', 'middleware' => ['auth', 'admin']]` group.
*   **Controller (`app/Http/Controllers/Admin/SystemHealthController.php`):**
    Will compute specific operational anomalies using raw Eloquent queries to avoid heavy hydration:
    1.  **Queue Health:** `Laravel\Horizon\Contracts\MasterSupervisorRepository` and `DB::table('failed_jobs')->count()`.
    2.  **Stuck Payments:** `PaymentAttempt::whereIn('status', ['initiated', 'pending'])->where('created_at', '<', now()->subHours(24))->count()`.
    3.  **Failed CMI Callbacks:** `CmiCallbackLog::where('processing_status', '!=', 'success')->where('created_at', '>=', now()->subDay())->count()`.
    4.  **Shipped Unpaid Orders:** `Order::where('delivery_status', 'shipped')->where('payment_status', 'unpaid')->count()`.
*   **View (`resources/views/backend/system/health.blade.php`):** A Bootstrap grid displaying these KPI cards, acting as the primary NOC (Network Operations Center) screen for admins.

### Phase 5: Domain-Driven Logging Channels
Update `config/logging.php` to define dedicated daily rotated channels:
```php
'payments' => ['driver' => 'daily', 'path' => storage_path('logs/payments.log'), 'days' => 14],
'shipping' => ['driver' => 'daily', 'path' => storage_path('logs/shipping.log'), 'days' => 14],
'queues'   => ['driver' => 'daily', 'path' => storage_path('logs/queues.log'), 'days' => 7],
'search'   => ['driver' => 'daily', 'path' => storage_path('logs/search.log'), 'days' => 7],
```
*   **Instrumentation:** Refactor `app/Console/Commands/ExpireStalePayments.php` and the CMI error handlers to use `Log::channel('payments')->error()`. 

### Phase 6: Operational Audit CLI
Build `app/Console/Commands/OperationsAuditCommand.php` (`php artisan mayush:operations:audit`). This acts as the CLI companion to the UI dashboard, outputting the exact same anomaly metrics via `$this->table()`. This is crucial for future cron-based alerting.

### Phase 7 & 8: Nightwatch & Testing
*   Add placeholders to `.env.example` for Nightwatch (will remain disabled).
*   Add automated tests (`Tests\Feature\Monitoring\AuthorizationTest`) asserting that standard users receive `403 Forbidden` on `/pulse`, `/horizon`, and `/admin/system-health`.

---

## 4. Verification Plan

1.  **Automated Checks:** `php artisan test --filter=Monitoring`
2.  **Manual Checks:** Verify Horizon and Pulse routes locally acting as an Admin and a Guest.
3.  **Command Verification:** Run `php artisan mayush:operations:audit` and ensure it executes without crashing.
