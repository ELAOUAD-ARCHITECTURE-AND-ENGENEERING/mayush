# Production Readiness Pass

Date: 2026-05-13
Branch: main
PHP: 8.2.12
Laravel: 10.50.2

## Summary

This pass reviewed the production readiness surfaces that cannot be proven by local feature tests alone: ONESSTA, CMI/payment callbacks, mail, queue workers, scheduler, storage links, production route exposure, and deployment configuration.

Local code verification is healthy, but production release still depends on server-level configuration and live credential checks. The main code fix in this pass prevents ONESSTA shipment creation from silently falling back to inline `sync` execution when production queue variables are partially configured.

## Current Local Environment Snapshot

The local `.env` is useful for development, but it is not production-ready:

- `APP_ENV=local`
- `APP_DEBUG=true`
- `QUEUE_CONNECTION=sync`
- `ONESSTA_ENABLED=true`
- ONESSTA credentials are present.
- CMI credentials are present.
- `CMI_ALLOWED_IPS` is not effectively configured.
- Redis is not reachable locally at `127.0.0.1:6379`.

Production must override these values with the deployment settings listed below.

## Verified Locally

- ONESSTA Artisan commands are registered:
  - `onessta:sync-cities`
  - `onessta:sync-pickup-cities`
  - `onessta:poll-tracking`
  - `onessta:cache:flush-cities`
  - `onessta:cache:flush-pickup-cities`
- Scheduler is registered and lists 18 scheduled commands/jobs, including ONESSTA city sync, pickup-city sync, and tracking poll.
- ONESSTA migrations have run locally:
  - `onessta_shipments`
  - `onessta_tracking_events`
  - `onessta_webhook_logs`
  - `onessta_city_maps`
  - `onessta_pickup_city_maps`
  - COD fields on `onessta_shipments`
- Public storage link exists locally at `public/storage`.
- Production-like route/config cache completed with `APP_ENV=production`, `APP_DEBUG=false`, and no `_debugbar` or `_ignition` routes exposed.
- CMI callback route uses throttling and `CmiIpWhitelist`.
- `cmi:diagnose` is registered for production gateway preflight checks.
- ONESSTA webhook route exists at `/webhooks/onessta` and signature failure is configured to fail closed by default.

## Fix Applied

ONESSTA shipment creation no longer defaults to inline `sync` when `ONESSTA_CREATE_SHIPMENT_QUEUE_CONNECTION` is omitted. It now inherits:

1. `ONESSTA_CREATE_SHIPMENT_QUEUE_CONNECTION`
2. `ONESSTA_QUEUE_CONNECTION`
3. `QUEUE_CONNECTION`
4. `sync` only as the final fallback

This prevents production order creation from silently performing ONESSTA shipment creation inline when the production queue connection is Redis.

Updated files:

- `packages/mayush/onessta-shipping/config/onessta.php`
- `.env.example`
- `docs/scheduler-queue-readiness.md`
- `tests/Feature/DevOps/CiAndOpsReadinessTest.php`

## ONESSTA Order Creation Contract

When a new eligible order is created, ONESSTA shipment creation is triggered by `Mayush\Shipping\Onessta\Observers\OrderObserver`.

Shipment creation requires all of the following:

- `ONESSTA_ENABLED=true`
- the `onessta` addon is active
- order is not cancelled
- order shipping method/type is `onessta` or `home_delivery`
- required ONESSTA credentials are configured
- city mapping data exists for the order destination
- a queue worker is running for the configured ONESSTA queue

The expected production behavior is:

1. Order is created in Mayush.
2. `OrderObserver::created()` calls `OrderShipmentDispatchService::ensureForOrder()`.
3. A local `onessta_shipments` record is created in `QUEUED` state.
4. `CreateShipmentJob` is dispatched using `onessta.queue.create_shipment_connection` and `onessta.queue.name`.
5. The worker creates the remote shipment in ONESSTA.
6. Tracking updates arrive through webhook processing or polling.

If order creation appears to do nothing, verify the addon activation row, queue worker status, shipping method/type, city mappings, and failed jobs before debugging the remote API.

## Production Blockers To Verify On Server

### ONESSTA

Required production `.env` keys:

```env
ONESSTA_ENABLED=true
ONESSTA_MODE=live
ONESSTA_BASE_URL=https://api.onessta.com/api/v1
ONESSTA_TOKEN=...
ONESSTA_API_KEY=...
ONESSTA_CLIENT_ID=...
ONESSTA_QUEUE_CONNECTION=redis
ONESSTA_CREATE_SHIPMENT_QUEUE_CONNECTION=redis
ONESSTA_QUEUE_NAME=onessta
ONESSTA_WEBHOOK_ENABLED=true
ONESSTA_WEBHOOK_API_KEY=...
ONESSTA_WEBHOOK_SECRET=...
ONESSTA_FAIL_ON_SIGNATURE_MISMATCH=true
```

Server checks:

```bash
composer install --no-dev --optimize-autoloader
composer dump-autoload
php artisan optimize:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan onessta:sync-cities --force
php artisan onessta:sync-pickup-cities --force
php artisan queue:work redis --queue=onessta --once
php artisan queue:failed
php artisan onessta:diagnose-order {order_id}
php artisan onessta:diagnose-order {order_id} --dispatch
```

Create one controlled test order and confirm:

- `onessta_shipments` row is created immediately with `QUEUED` or remote-created status.
- `CreateShipmentJob` is queued on the `onessta` queue, not run inline.
- Shipment appears in the ONESSTA platform.
- Webhook from ONESSTA updates local shipment/tracking status.

If an order does not create a shipment, run:

```bash
php artisan onessta:diagnose-order {order_id}
```

The command reports local blockers such as disabled integration, inactive addon, unsupported shipping method, missing credentials, missing city mapping, existing local shipment state, queue connection/name, and possible failed shipment jobs. After fixing blockers, queue the shipment through the same idempotent path used by order creation:

```bash
php artisan onessta:diagnose-order {order_id} --dispatch
```

### CMI / Payments

Required production `.env` keys:

```env
CMI_MERCHANT_ID=...
CMI_SECRET_KEY=...
CMI_GATEWAY_URL=https://attijari.cmi.co.ma/fim/est3Dgate
CMI_OK_URL=https://your-domain.example/cmi/success
CMI_FAIL_URL=https://your-domain.example/cmi/fail
CMI_CALLBACK_URL=https://your-domain.example/cmi/callback
CMI_ALLOWED_IPS=...
```

Current local readiness note: credentials are present locally, but `CMI_ALLOWED_IPS` is not configured. Production should configure the CMI callback IP allowlist.

Server checks:

- Run `php artisan cmi:diagnose --production`.
- Run one sandbox or low-value live CMI payment.
- Confirm successful callback marks order/payment paid exactly once.
- Replay the same callback and confirm idempotency.
- Send a failed callback and confirm it does not mark order paid.
- Confirm callback requests from non-CMI IPs are blocked when `CMI_ALLOWED_IPS` is set.

### Queue / Horizon / Redis

Current local readiness note: local Redis is not reachable, and `horizon:status` fails with `tcp://127.0.0.1:6379` refused. Production must run Redis before enabling Redis-backed queues/cache/session.

Required production settings:

```env
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

or keep cache/session on file/database if Redis is not available, but do not run production shipping queues with `QUEUE_CONNECTION=sync`.

Server checks:

```bash
php artisan horizon:status
php artisan queue:failed
php artisan queue:restart
php artisan horizon:terminate
```

Supervisor/Horizon must include at least:

- default worker for normal jobs
- `onessta` worker for ONESSTA jobs

### Scheduler

Production cron must run:

```cron
* * * * * cd /path/to/mayush && php artisan schedule:run >> /dev/null 2>&1
```

Server checks:

```bash
php artisan schedule:list
php artisan schedule:run
```

### Mail

Mail configuration is present locally, but live delivery was not tested. Production must verify:

- SMTP credentials
- `MAIL_FROM_ADDRESS`
- outbound firewall rules
- queued mail worker

Server check:

```bash
php artisan tinker
Mail::raw('Production mail smoke test', fn ($m) => $m->to('qa@example.com')->subject('Mayush mail smoke'));
```

### Storage / Media

Local `public/storage` link exists. Production must verify:

```bash
php artisan storage:link
```

Then inspect:

- product listing images
- product detail images
- brand logos
- invoice/download media
- imported media references

## Commands Run

```bash
php artisan list
php artisan schedule:list
composer show laravel/horizon
composer show mayush/onessta-shipping
php artisan queue:failed
php artisan migrate:status
php artisan tinker --execute="..."
php artisan test tests\Feature\OnesstaIntegrationTest.php tests\Feature\OrderConfirmationWorkflowTest.php tests\Feature\DevOps\CiAndOpsReadinessTest.php
php artisan test tests\Feature\OnesstaOrderDiagnosticsTest.php tests\Feature\OrderConfirmationWorkflowTest.php tests\Feature\OnesstaIntegrationTest.php
php artisan test tests\Feature\Payment\CmiGatewayDiagnosticsTest.php tests\Feature\Payment\PaymentVaultRegressionTest.php tests\Feature\Security\RouteHardeningTest.php
APP_ENV=production APP_DEBUG=false php artisan config:cache
APP_ENV=production APP_DEBUG=false php artisan route:cache
APP_ENV=production APP_DEBUG=false php artisan route:list
composer validate --no-check-publish
php artisan route:cache
Get-ChildItem -Path app,routes,resources -Recurse -File | Select-String -Pattern '(?<![A-Za-z0-9_])(dd|dump|ray|var_dump)\s*\('
```

## Results

- ONESSTA command discovery: passed.
- Schedule list: passed.
- ONESSTA migrations: ran locally.
- Storage link: present locally.
- Production-like config/route cache: passed.
- Debugbar/Ignition production route exposure: none found.
- ONESSTA/order confirmation/DevOps tests: 19 passed, 74 assertions.
- ONESSTA order diagnostic/order confirmation/integration tests: 17 passed, 61 assertions.
- CMI diagnostic/payment vault/route hardening tests: 10 passed, 36 assertions.
- Composer validation: passed.
- Final route cache: passed.
- Production debug artifact scan: no `dd()`, `dump()`, `ray()`, or `var_dump()` matches in `app`, `routes`, or `resources`.
- `composer show laravel/horizon`: installed, v5.45.5.
- `composer show mayush/onessta-shipping`: not installed as a Composer package; it is wired through root `composer.json` PSR-4 autoload and local path repository. Production deployment must include `packages/mayush/onessta-shipping` and run `composer dump-autoload`, or install the package formally.
- Local failed jobs check: one existing failed mail job was present on the local Redis/default queue. Inspect, retry, or clear production failed jobs before launch.

## Go / No-Go

Local code readiness: **Go**

Production deployment: **No-Go until server checks pass**, specifically:

- Redis/Horizon or Supervisor worker is running.
- ONESSTA credentials validate against the live platform.
- A controlled order creates a shipment locally and in ONESSTA.
- CMI live/sandbox callback is verified with IP allowlist.
- Mail smoke test succeeds.
- Storage/media smoke test succeeds.
