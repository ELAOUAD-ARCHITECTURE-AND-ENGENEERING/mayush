# ONESSTA 3PL Shipping Addon — Comprehensive Implementation Plan

## Document Information

| Field | Value |
|---|---|
| **Project** | mayush/onessta-shipping |
| **Type** | Laravel Addon Package |
| **Base Application** | Mayush E-Commerce Platform |
| **Target Version** | v1.0.0 |
| **Min. Code Coverage** | 80% |
| **Documentation** | [ShippingProvider.txt](../ShippingProvider.txt) |

---

## Table of Contents

1. [Technical Specifications](#1-technical-specifications)
2. [Development Phases & Milestones](#2-development-phases--milestones)
3. [Unit Test Suite](#3-unit-test-suite)
4. [Test Case Documentation](#4-test-case-documentation)
5. [Mock Data Requirements](#5-mock-data-requirements)
6. [Integration Testing Procedures](#6-integration-testing-procedures)
7. [Quality Standards](#7-quality-standards)
8. [Success Criteria](#8-success-criteria)

---

## 1. Technical Specifications

### 1.1 Package Information

```yaml
package_name: mayush/onessta-shipping
namespace: Mayush\Shipping\Onessta
type: Laravel Addon (Composer Path Repository)
laravel_version: ">=10.0"
php_version: ">=8.1"
```

### 1.2 Package Directory Structure

```
packages/
  mayush/
    onessta-shipping/
      composer.json
      README.md
      LICENSE
      src/
        OnesstaShippingServiceProvider.php
        Contracts/
          CarrierInterface.php
          QuoteInterface.php
          LabelInterface.php
          WebhookHandlerInterface.php
        Config/
          onessta.php
        DTOs/
          AddressDto.php
          ShipmentRequestDto.php
          ShipmentResponseDto.php
          TrackingEventDto.php
          TrackingResponseDto.php
          CityDto.php
          PickupCityDto.php
          ProductDto.php
          WebhookPayloadDto.php
        Exceptions/
          OnesstaException.php
          AuthenticationException.php
          ValidationException.php
          UnsupportedCapabilityException.php
          SignatureVerificationException.php
          RemoteApiException.php
          CityMappingException.php
          ShipmentCreationException.php
        Http/
          Controllers/
            WebhookController.php
            AdminController.php
          Middleware/
            VerifyOnesstaSignature.php
            OnesstaAddonEnabled.php
        Client/
          OnesstaClient.php
          RequestSigner.php
          WebhookSignatureVerifier.php
          RateLimiter.php
        Services/
          AuthService.php
          ShipmentService.php
          TrackingService.php
          CatalogService.php
          ReferenceDataService.php
          WebhookService.php
          QuoteService.php
          LabelService.php
        Jobs/
          SyncCitiesJob.php
          SyncPickupCitiesJob.php
          PollTrackingJob.php
          ProcessWebhookJob.php
          PushStockUpdateJob.php
          CreateShipmentJob.php
          RetryShipmentJob.php
        Events/
          ShipmentCreated.php
          ShipmentCreationFailed.php
          ShipmentStatusUpdated.php
          ShipmentInfoUpdated.php
          WebhookReceived.php
          WebhookFailed.php
          CitiesSynced.php
          PickupCitiesSynced.php
        Listeners/
          UpdateOrderDeliveryStatus.php
          NotifyAdminOnShipmentFailure.php
          SyncLocalCityMapping.php
        Models/
          OnesstaShipment.php
          OnesstaTrackingEvent.php
          OnesstaWebhookLog.php
          OnesstaCityMap.php
          OnesstaPickupCityMap.php
        Database/
          Migrations/
            2026_06_20_000001_create_onessta_shipments_table.php
            2026_06_20_000002_create_onessta_tracking_events_table.php
            2026_06_20_000003_create_onessta_webhook_logs_table.php
            2026_06_20_000004_create_onessta_city_maps_table.php
            2026_06_20_000005_create_onessta_pickup_city_maps_table.php
        Observers/
          OrderObserver.php
        Routes/
          webhooks.php
          admin.php
        Resources/
          onessta_shipment.php        # API Resource
          onessta_tracking_event.php  # API Resource
        Tests/
          Unit/
            UnitTestCase.php
            Client/
              OnesstaClientTest.php
              RequestSignerTest.php
              WebhookSignatureVerifierTest.php
            Services/
              AuthServiceTest.php
              ShipmentServiceTest.php
              TrackingServiceTest.php
              ReferenceDataServiceTest.php
              WebhookServiceTest.php
            DTOs/
              ShipmentRequestDtoTest.php
              ShipmentResponseDtoTest.php
              TrackingResponseDtoTest.php
            Jobs/
              CreateShipmentJobTest.php
              SyncCitiesJobTest.php
              ProcessWebhookJobTest.php
          Feature/
            FeatureTestCase.php
            WebhookTest.php
            ShipmentCreationTest.php
            TrackingSyncTest.php
            CityMappingTest.php
            AdminControllerTest.php
```

### 1.3 Configuration Specification

**File:** `config/onessta.php`

```php
<?php

return [
    'enabled' => env('ONESSTA_ENABLED', false),

    'mode' => env('ONESSTA_MODE', 'live'),
    // 'live' | 'sandbox' (sandbox requires ONESSTA provided URL)

    'base_url' => env('ONESSTA_BASE_URL', 'https://api.onessta.com/api/v1'),

    'auth' => [
        'token' => env('ONESSTA_TOKEN'),
        'api_key' => env('ONESSTA_API_KEY'),
        'client_id' => env('ONESSTA_CLIENT_ID'),
    ],

    'http' => [
        'timeout' => (int) env('ONESSTA_TIMEOUT', 30),
        'connect_timeout' => (int) env('ONESSTA_CONNECT_TIMEOUT', 10),
        'retry_times' => (int) env('ONESSTA_RETRY_TIMES', 3),
        'retry_sleep_ms' => (int) env('ONESSTA_RETRY_SLEEP_MS', 500),
        'retry_codes' => [408, 502, 503, 504],
    ],

    'queue' => [
        'connection' => env('ONESSTA_QUEUE_CONNECTION', 'redis'),
        'name' => env('ONESSTA_QUEUE_NAME', 'onessta'),
        'create_shipment_retry' => [60, 300, 900],     // seconds
        'poll_tracking_retry' => [30, 120, 300],
        'webhook_process_retry' => [10, 30, 60],
    ],

    'cache' => [
        'store' => env('ONESSTA_CACHE_STORE', 'redis'),
        'ttl_cities' => (int) env('ONESSTA_CACHE_TTL_CITIES', 86400),
        'ttl_pickup_cities' => (int) env('ONESSTA_CACHE_TTL_PICKUP_CITIES', 86400),
        'ttl_tracking' => (int) env('ONESSTA_CACHE_TTL_TRACKING', 300),
        'ttl_capabilities' => (int) env('ONESSTA_CACHE_TTL_CAPABILITIES', 999999999),
    ],

    'webhook' => [
        'enabled' => env('ONESSTA_WEBHOOK_ENABLED', true),
        'api_key' => env('ONESSTA_WEBHOOK_API_KEY'),
        'secret' => env('ONESSTA_WEBHOOK_SECRET'),
        'route' => env('ONESSTA_WEBHOOK_ROUTE', '/webhooks/onessta'),
        'queue' => (bool) env('ONESSTA_WEBHOOK_QUEUE', true),
        'fail_on_signature_mismatch' => (bool) env('ONESSTA_FAIL_ON_SIGNATURE_MISMATCH', true),
    ],

    'capabilities' => [
        'quotes' => (bool) env('ONESSTA_SUPPORT_QUOTES', false),
        'labels' => (bool) env('ONESSTA_SUPPORT_LABELS', false),
        'products' => (bool) env('ONESSTA_SUPPORT_PRODUCTS', true),
        'stock' => (bool) env('ONESSTA_SUPPORT_STOCK', true),
    ],

    'polling' => [
        'enabled' => (bool) env('ONESSTA_POLLING_ENABLED', true),
        'interval_minutes' => (int) env('ONESSTA_POLLING_INTERVAL_MINUTES', 5),
        'max_parcels_per_run' => (int) env('ONESSTA_POLLING_MAX_PARCELS', 100),
    ],

    'throttle' => [
        'tracking_per_parcel_per_min' => 1,
        'cities_refresh_per_day' => 1,
        'pickup_cities_refresh_per_day' => 1,
    ],

    'log_channel' => env('ONESSTA_LOG_CHANNEL', 'stack'),
];
```

### 1.4 Environment Variables

```env
# Core
ONESSTA_ENABLED=true
ONESSTA_MODE=live
ONESSTA_BASE_URL=https://api.onessta.com/api/v1

# Authentication
ONESSTA_TOKEN=
ONESSTA_API_KEY=
ONESSTA_CLIENT_ID=

# HTTP
ONESSTA_TIMEOUT=30
ONESSTA_CONNECT_TIMEOUT=10
ONESSTA_RETRY_TIMES=3
ONESSTA_RETRY_SLEEP_MS=500

# Queue
ONESSTA_QUEUE_CONNECTION=redis
ONESSTA_QUEUE_NAME=onessta

# Cache
ONESSTA_CACHE_STORE=redis
ONESSTA_CACHE_TTL_CITIES=86400
ONESSTA_CACHE_TTL_PICKUP_CITIES=86400
ONESSTA_CACHE_TTL_TRACKING=300

# Webhook
ONESSTA_WEBHOOK_ENABLED=true
ONESSTA_WEBHOOK_API_KEY=
ONESSTA_WEBHOOK_SECRET=
ONESSTA_WEBHOOK_ROUTE=/webhooks/onessta
ONESSTA_WEBHOOK_QUEUE=true
ONESSTA_FAIL_ON_SIGNATURE_MISMATCH=true

# Polling
ONESSTA_POLLING_ENABLED=true
ONESSTA_POLLING_INTERVAL_MINUTES=5
ONESSTA_POLLING_MAX_PARCELS=100

# Capabilities (flags only — no implementation without docs)
ONESSTA_SUPPORT_QUOTES=false
ONESSTA_SUPPORT_LABELS=false
ONESSTA_SUPPORT_PRODUCTS=true
ONESSTA_SUPPORT_STOCK=true

# Logging
ONESSTA_LOG_CHANNEL=stack
```

### 1.5 Database Schema Specification

#### Table: `onessta_shipments`

| Column | Type | Constraints | Description |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| order_id | BIGINT UNSIGNED | NULLABLE, INDEX | Link to Mayush orders.id |
| external_id | VARCHAR(255) | NULLABLE | ONESSTA external/parcel ID |
| code | VARCHAR(255) | UNIQUE, NOT NULL | Shipment tracking code |
| receiver | VARCHAR(255) | NOT NULL | Recipient name |
| phone | VARCHAR(50) | NOT NULL | Recipient phone |
| address | TEXT | NOT NULL | Delivery address |
| city_id | BIGINT UNSIGNED | NULLABLE, INDEX | Local Mayush city_id |
| city_name | VARCHAR(255) | NULLABLE | City name |
| remote_city_id | INT UNSIGNED | NULLABLE | ONESSTA city ID |
| pickup_city_id | INT UNSIGNED | NULLABLE | Pickup/warehouse city ID |
| pickup_city_name | VARCHAR(255) | NULLABLE | Pickup city name |
| price | DECIMAL(12,2) | NOT NULL DEFAULT 0 | Order value |
| sku | TEXT | NULLABLE | Product SKUs (semicolon-separated) |
| note | TEXT | NULLABLE | Internal note |
| product_nature | VARCHAR(100) | NULLABLE | Product type |
| can_open | BOOLEAN | DEFAULT FALSE | Can be opened on delivery |
| replace | BOOLEAN | DEFAULT FALSE | Replace allowed |
| status | VARCHAR(100) | NULLABLE | Current ONESSTA status |
| status_second | VARCHAR(100) | NULLABLE | Secondary status |
| situation | VARCHAR(100) | NULLABLE | Current situation |
| last_status_comment | TEXT | NULLABLE | Latest status note |
| reported_date | TIMESTAMP | NULLABLE | Last reported date |
| raw_request | JSON | NULLABLE | Original API request |
| raw_response | JSON | NULLABLE | Original API response |
| created_at_remote | TIMESTAMP | NULLABLE | Remote creation time |
| updated_at_remote | TIMESTAMP | NULLABLE | Remote update time |
| synced_at | TIMESTAMP | NULLABLE | Last sync time |
| created_at | TIMESTAMP | NOT NULL | Local creation |
| updated_at | TIMESTAMP | NOT NULL | Local update |

**Indexes:**
- `idx_code` on `code`
- `idx_order_id` on `order_id`
- `idx_status` on `status`
- `idx_remote_city_id` on `remote_city_id`

#### Table: `onessta_tracking_events`

| Column | Type | Constraints |
|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT |
| onessta_shipment_id | BIGINT UNSIGNED | FK, NOT NULL, INDEX |
| status | VARCHAR(100) | NOT NULL |
| name | VARCHAR(255) | NULLABLE |
| created_at_remote | TIMESTAMP | NULLABLE |
| new_date | TIMESTAMP | NULLABLE |
| raw_payload | JSON | NULLABLE |
| created_at | TIMESTAMP | NOT NULL |
| updated_at | TIMESTAMP | NOT NULL |

**Unique constraint:** `UNIQUE(onessta_shipment_id, status, created_at_remote)`

#### Table: `onessta_webhook_logs`

| Column | Type | Constraints |
|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT |
| event_type | VARCHAR(100) | NOT NULL, INDEX |
| header_api_key | VARCHAR(255) | NULLABLE |
| header_signature | VARCHAR(255) | NULLABLE |
| header_event | VARCHAR(100) | NULLABLE |
| payload | LONGTEXT | NOT NULL |
| signature_valid | BOOLEAN | NULLABLE |
| processed | BOOLEAN | DEFAULT FALSE, INDEX |
| error_message | TEXT | NULLABLE |
| onessta_shipment_id | BIGINT UNSIGNED | NULLABLE, FK, INDEX |
| processed_at | TIMESTAMP | NULLABLE |
| created_at | TIMESTAMP | NOT NULL |
| updated_at | TIMESTAMP | NOT NULL |

#### Table: `onessta_city_maps`

| Column | Type | Constraints |
|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT |
| remote_city_id | INT UNSIGNED | UNIQUE, NOT NULL |
| remote_city_name | VARCHAR(255) | NOT NULL |
| local_city_id | BIGINT UNSIGNED | NULLABLE, INDEX |
| local_city_name | VARCHAR(255) | NULLABLE |
| is_pickup | BOOLEAN | DEFAULT FALSE |
| active | BOOLEAN | DEFAULT TRUE, INDEX |
| created_at | TIMESTAMP | NOT NULL |
| updated_at | TIMESTAMP | NOT NULL |

#### Table: `onessta_pickup_city_maps`

| Column | Type | Constraints |
|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT |
| remote_city_id | INT UNSIGNED | UNIQUE, NOT NULL |
| remote_city_name | VARCHAR(255) | NOT NULL |
| local_city_id | BIGINT UNSIGNED | NULLABLE, INDEX |
| local_city_name | VARCHAR(255) | NULLABLE |
| active | BOOLEAN | DEFAULT TRUE, INDEX |
| created_at | TIMESTAMP | NOT NULL |
| updated_at | TIMESTAMP | NOT NULL |

---

## 2. Development Phases & Milestones

### Phase 1: Foundation (Milestone M1)

**Duration:** 1 week
**Deliverables:** Package skeleton, config, migrations, HTTP client, auth headers

#### Tasks

1. **Package Setup**
   - Create `packages/mayush/onessta-shipping` directory structure
   - Create `composer.json` with proper autoloading (`Psr-4: Mayush\\Shipping\\Onessta\\`)
   - Create `OnesstaShippingServiceProvider` with config merge, migrations loading, route registration
   - Register provider in `config/app.php` via path repository

2. **Configuration**
   - Create `config/onessta.php` with all env mappings
   - Create `.env.example` entries
   - Add environment validation (throw if required keys missing when enabled)

3. **Database Migrations**
   - Create all 5 migration files
   - Add safe `up()`/`down()` with `hasTable`/`hasColumn` checks
   - Test migration runs and rolls back cleanly

4. **Eloquent Models**
   - Create all 5 models with proper relationships
   - Define fillable, casts, scopes, accessors
   - `OnesstaShipment` → `belongsTo(Order)`, `hasMany(TrackingEvent)`
   - `OnesstaTrackingEvent` → `belongsTo(OnesstaShipment)`
   - `OnesstaWebhookLog` → `belongsTo(OnesstaShipment)`
   - `OnesstaCityMap` → `belongsTo(City::class, 'local_city_id')`

5. **HTTP Client**
   - Create `OnesstaClient` as a Laravel HTTP Client wrapper
   - Implement retry logic with configurable retry codes
   - Implement base headers: `Authorization: Bearer`, `API-Key`, `Client-ID`, `Content-Type`, `Accept`
   - Implement `get()`, `post()` methods
   - Implement `RequestSigner` for HMAC-SHA256 signing

6. **Service Provider Registration**
   - Bind `OnesstaClient` as singleton
   - Bind interfaces to concrete implementations
   - Auto-discover routes if supported

#### Exit Criteria
- [ ] `php artisan vendor:publish --tag=onessta-config` publishes config
- [ ] `php artisan migrate` runs all 5 migrations
- [ ] `ONESSTA_TOKEN`, `ONESSTA_API_KEY`, `ONESSTA_CLIENT_ID` configured
- [ ] `OnesstaClient` makes authenticated requests with correct headers
- [ ] Fake HTTP test proves headers are built correctly
- [ ] All 5 models are instantiable and have correct relationships

---

### Phase 2: Reference Data (Milestone M2)

**Duration:** 1 week
**Deliverables:** Cities sync, pickup cities sync, caching, city mapping

#### Tasks

1. **ReferenceDataService**
   - `syncCities(): array` — calls `GET /p/cities`, stores in `onessta_city_maps`
   - `syncPickupCities(): array` — calls `GET /p/pickup_cities`, stores in `onessta_pickup_city_maps`
   - Handle pagination if exists (not documented but guard against it)
   - Use upsert to avoid duplicates

2. **Cache Layer**
   - Wrap city lists in `Cache::remember()`
   - TTL: 24 hours for cities, 24 hours for pickup cities
   - Add cache flush commands:
     - `php artisan onessta:cache:flush-cities`
     - `php artisan onessta:cache:flush-pickup-cities`

3. **City Resolution**
   - `resolveCity(int $localCityId): ?int` — look up `onessta_city_maps` by `local_city_id`
   - `resolveLocalCity(int $remoteCityId): ?int` — reverse lookup
   - If not found, return null and log warning

4. **Scheduled Sync Jobs**
   - Register `SyncCitiesJob` in `app/Console/Kernel.php`
   - Run daily at midnight
   - Register `SyncPickupCitiesJob`
   - Run daily at 1am

5. **Admin UI (optional)**
   - Admin page to manually trigger city sync
   - View city mapping table
   - Manual mapping override capability

#### Exit Criteria
- [ ] `SyncCitiesJob` syncs all cities from ONESSTA
- [ ] `SyncPickupCitiesJob` syncs all pickup cities
- [ ] Cache hit returns data without HTTP call
- [ ] Cache miss triggers HTTP call and caches result
- [ ] Duplicates are not inserted on re-sync
- [ ] `resolveCity()` correctly maps local to remote IDs
- [ ] Unit tests cover all ReferenceDataService methods (min 80% coverage)

---

### Phase 3: Shipment Core (Milestone M3)

**Duration:** 1.5 weeks
**Deliverables:** Create shipment, fetch by code, persist state, fire events

#### Tasks

1. **DTOs**
   - `ShipmentRequestDto` — build from Mayush Order
   - `ShipmentResponseDto` — parse API response
   - `AddressDto` — address normalization

2. **ShipmentService**
   - `createShipment(ShipmentRequestDto $dto): OnesstaShipment`
   - `fetchByCode(string $code): OnesstaShipment`
   - `toOnesstaStatus(string $status): string` — normalize to ONESSTA status
   - Persist to `onessta_shipments` with raw request/response

3. **OrderObserver**
   - `created(Order $order)` — check if `$order->shipping_method === 'onessta'`
   - Check `addon_is_activated('onessta')`
   - Check `$order->payment_status === 'paid'`
   - Dispatch `CreateShipmentJob`

4. **CreateShipmentJob**
   - Retry backoff: [60, 300, 900] seconds
   - Queue: `onessta`
   - On failure: dispatch `NotifyAdminOnShipmentFailure` event

5. **Events**
   - `ShipmentCreated` — dispatched after successful creation
   - `ShipmentCreationFailed` — dispatched after all retries exhausted
   - `ShipmentStatusUpdated` — dispatched on status change

6. **Webhook Route**
   - Register `POST /webhooks/onessta`
   - Expose publicly (no auth middleware)
   - Verify X-Api-Key and X-Signature headers

#### Exit Criteria
- [ ] `POST /api/v1/p/parcels/add` succeeds with valid payload
- [ ] Shipment persisted with raw request/response
- [ ] `order_id` correctly linked
- [ ] Duplicate code returns appropriate error
- [ ] `CreateShipmentJob` dispatched on Order creation
- [ ] Event `ShipmentCreated` fired on success
- [ ] Event `ShipmentCreationFailed` fired after all retries
- [ ] Unit tests for ShipmentService (min 80% coverage)
- [ ] Feature tests for full creation flow

---

### Phase 4: Tracking (Milestone M4)

**Duration:** 1 week
**Deliverables:** Poll tracking, webhook processing, history persistence

#### Tasks

1. **TrackingService**
   - `track(string $code): TrackingResponseDto`
   - `parseHistory(array $history): Collection<TrackingEventDto>`
   - `persistEvents(OnesstaShipment $shipment, Collection $events): int` — insert new events only

2. **PollTrackingJob**
   - Fetch all `onessta_shipments` with status NOT IN (`DELIVERED`, `RETURNED`, `CANCELLED`)
   - Limit to `ONESSTA_POLLING_MAX_PARCELS` per run
   - Respect throttle: 1 request per parcel per 5 minutes (cache-based)
   - Retry backoff: [30, 120, 300] seconds

3. **Webhook Processing (ProcessWebhookJob)**
   - Parse event type from `X-Webhook-Event` header
   - Handle `parcel.status_updated`:
     - Update `onessta_shipment.status`
     - Add tracking event
     - Fire `ShipmentStatusUpdated`
   - Handle `parcel.status_second_updated`:
     - Update `onessta_shipment.status_second`
   - Handle `parcel.info_updated`:
     - Update `onessta_shipment.situation`
     - Update `onessta_shipment.reported_date`
   - Return 200 quickly (< 30s requirement)

4. **Idempotency**
   - `onessta_tracking_events` unique constraint prevents duplicate events
   - Webhook log marks `processed=1` after successful processing
   - Skip already-processed webhooks by `code + event_type`

5. **Order Delivery Status Sync**
   - `UpdateOrderDeliveryStatus` listener
   - Maps ONESSTA status to Mayush `delivery_status`
   - Updates `orders.delivery_status`

#### Exit Criteria
- [ ] `GET /api/v1/p/parcels/tracking` returns history correctly
- [ ] Duplicate events are not inserted (unique constraint)
- [ ] `PollTrackingJob` respects rate limits
- [ ] All 3 webhook event types process correctly
- [ ] Invalid signature rejected with 403
- [ ] Delivery status updated on order
- [ ] Unit tests for TrackingService (min 80% coverage)

---

### Phase 5: Webhooks (Milestone M5)

**Duration:** 1 week
**Deliverables:** Signature verification, async processing, idempotency

#### Tasks

1. **WebhookSignatureVerifier**
   - HMAC-SHA256 over raw JSON body
   - Compare with `X-Signature` header
   - Config: `fail_on_signature_mismatch` flag

2. **WebhookController**
   - Capture raw body: `$request->getContent()`
   - Log to `onessta_webhook_logs` BEFORE processing
   - Verify API key
   - Verify signature
   - Dispatch `ProcessWebhookJob`
   - Return 200 OK immediately

3. **Idempotency Guards**
   - Check if `code + event_type + payload_hash` already processed
   - Skip if duplicate within 5-minute window

4. **Failed Webhook Handling**
   - 3 retries at 10s, 30s, 60s
   - Mark `processed=1` only after successful processing
   - Log `error_message` on failure

#### Exit Criteria
- [ ] Valid signature passes verification
- [ ] Invalid signature returns 403
- [ ] Endpoint responds within 30s
- [ ] Duplicate webhooks do not create duplicate events
- [ ] All 3 event types processed
- [ ] Webhook logs captured for audit
- [ ] Unit tests for WebhookSignatureVerifier (min 80% coverage)

---

### Phase 6: Catalog Sync (Optional) (Milestone M6)

**Duration:** 1 week
**Deliverables:** Product listing, add product, stock updates

#### Tasks

1. **CatalogService**
   - `listProducts(int $page = 1): array`
   - `addProduct(ProductDto $dto): bool`
   - `updateStock(int $remoteProductId, int $stock): bool`

2. **PushStockUpdateJob**
   - Triggered on `ProductStockUpdated` event
   - Respects capability flag
   - Retry backoff: [60, 300, 900]

#### Exit Criteria
- [ ] Products listed from ONESSTA
- [ ] Stock updated on ONESSTA when local stock changes
- [ ] Capability flags disable safely

---

### Phase 7: Testing & QA (Milestone M7)

**Duration:** 1.5 weeks
**Deliverables:** Full test suite, 80%+ coverage, integration tests

#### Tasks

1. **Unit Tests**
   - All services, clients, DTOs, verifiers
   - Minimum 80% code coverage
   - Run via `php artisan test --coverage`

2. **Feature Tests**
   - Webhook endpoint tests
   - Full shipment creation flow
   - City mapping flow
   - Tracking sync flow

3. **Integration Tests (in app context)**
   - Service provider boots cleanly
   - Config publishes correctly
   - Migrations run cleanly
   - Package routes load

4. **Final QA Checklist**
   - All items from Part 8 checklist completed
   - No hardcoded credentials
   - All exceptions properly caught
   - Logging at appropriate levels
   - Config cached after publish

---

## 3. Unit Test Suite

### 3.1 Test Structure

```
Tests/
├── Unit/
│   ├── UnitTestCase.php              # Base class with setup
│   ├── Client/
│   │   ├── OnesstaClientTest.php
│   │   ├── RequestSignerTest.php
│   │   └── WebhookSignatureVerifierTest.php
│   ├── Services/
│   │   ├── AuthServiceTest.php
│   │   ├── ShipmentServiceTest.php
│   │   ├── TrackingServiceTest.php
│   │   ├── ReferenceDataServiceTest.php
│   │   └── WebhookServiceTest.php
│   ├── DTOs/
│   │   ├── ShipmentRequestDtoTest.php
│   │   ├── ShipmentResponseDtoTest.php
│   │   └── TrackingResponseDtoTest.php
│   └── Jobs/
│       ├── CreateShipmentJobTest.php
│       ├── SyncCitiesJobTest.php
│       └── ProcessWebhookJobTest.php
└── Feature/
    ├── FeatureTestCase.php
    ├── WebhookTest.php
    ├── ShipmentCreationTest.php
    ├── TrackingSyncTest.php
    ├── CityMappingTest.php
    └── AdminControllerTest.php
```

### 3.2 Minimum Coverage Requirements

| Component | Min Coverage |
|---|---|
| OnesstaClient | 90% |
| RequestSigner | 95% |
| WebhookSignatureVerifier | 95% |
| ShipmentService | 85% |
| TrackingService | 85% |
| ReferenceDataService | 80% |
| WebhookService | 80% |
| ShipmentRequestDto | 90% |
| ShipmentResponseDto | 90% |
| Overall Package | **80%** |

### 3.3 Sample Unit Test Skeletons

#### OnesstaClientTest.php

```php
<?php

namespace Mayush\Shipping\Onessta\Tests\Unit\Client;

use Mayush\Shipping\Onessta\Client\OnesstaClient;
use Mayush\Shipping\Onessta\Tests\Unit\UnitTestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

class OnesstaClientTest extends UnitTestCase
{
    protected OnesstaClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new OnesstaClient();
    }

    public function test_auth_headers_are_built_correctly(): void
    {
        Http::fake([
            '*' => Http::response(['status' => 'ok'], 200),
        ]);

        $this->client->post('/test', []);

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', 'Bearer test-token')
                && $request->hasHeader('API-Key', 'test-api-key')
                && $request->hasHeader('Client-ID', 'test-client-id')
                && $request->hasHeader('Accept', 'application/json')
                && $request->hasHeader('Content-Type', 'application/json');
        });
    }

    public function test_retry_on_502_error(): void
    {
        Http::fakeSequence()
            ->push('Server Error', 502)
            ->push('OK', 200);

        $response = $this->client->post('/test', ['key' => 'value']);

        $this->assertEquals('OK', $response->body());
        $this->assertEquals(2, Http::total());
    }

    public function test_no_retry_on_401_error(): void
    {
        Http::fake([
            '*' => Http::response('Unauthorized', 401),
        ]);

        $this->expectException(\Mayush\Shipping\Onessta\Exceptions\AuthenticationException::class);

        $this->client->post('/test', []);
    }

    public function test_timeout_config_respected(): void
    {
        config(['onessta.http.timeout' => 5]);

        Http::fake([
            '*' => Http::response(['ok' => true], 200),
        ]);

        $this->client->post('/test', []);

        Http::assertSent(function (Request $request) {
            return $request->timeout() === 5;
        });
    }
}
```

#### ShipmentServiceTest.php

```php
<?php

namespace Mayush\Shipping\Onessta\Tests\Unit\Services;

use Mayush\Shipping\Onessta\DTOs\ShipmentRequestDto;
use Mayush\Shipping\Onessta\DTOs\ShipmentResponseDto;
use Mayush\Shipping\Onessta\Exceptions\ValidationException;
use Mayush\Shipping\Onessta\Models\OnesstaShipment;
use Mayush\Shipping\Onessta\Services\ShipmentService;
use Mayush\Shipping\Onessta\Tests\Unit\UnitTestCase;
use Illuminate\Support\Facades\Http;
use Mockery;

class ShipmentServiceTest extends UnitTestCase
{
    protected ShipmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ShipmentService();
    }

    public function test_create_shipment_maps_all_documented_fields(): void
    {
        Http::fake([
            '*' => Http::response([
                'code' => 'ORD-12345',
                'status' => 'WAITING_PICKUP',
                'situation' => 'none',
            ], 200),
        ]);

        $dto = new ShipmentRequestDto([
            'code' => 'ORD-12345',
            'receiver' => 'John Doe',
            'phone' => '+1234567890',
            'price' => 99.99,
            'city' => 1,
            'address' => '123 Main St',
        ]);

        $shipment = $this->service->createShipment($dto);

        $this->assertInstanceOf(OnesstaShipment::class, $shipment);
        $this->assertEquals('ORD-12345', $shipment->code);
        $this->assertEquals('John Doe', $shipment->receiver);
        $this->assertEquals('WAITING_PICKUP', $shipment->status);
        $this->assertNotNull($shipment->raw_request);
        $this->assertNotNull($shipment->raw_response);
    }

    public function test_create_shipment_throws_on_duplicate_code(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'code already used'], 422),
        ]);

        $dto = new ShipmentRequestDto(['code' => 'DUPE-123']);

        $this->expectException(ValidationException::class);
        $this->service->createShipment($dto);
    }

    public function test_fetch_by_code_returns_shipment(): void
    {
        Http::fake([
            '*' => Http::response([
                'code' => 'ORD-999',
                'status' => 'PICKED_UP',
                'situation' => 'in_transit',
            ], 200),
        ]);

        $shipment = $this->service->fetchByCode('ORD-999');

        $this->assertEquals('ORD-999', $shipment->code);
        $this->assertEquals('PICKED_UP', $shipment->status);
    }

    public function test_fetch_by_code_throws_on_not_found(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'not found'], 404),
        ]);

        $this->expectException(\Mayush\Shipping\Onessta\Exceptions\RemoteApiException::class);
        $this->service->fetchByCode('NONEXISTENT');
    }

    public function test_shipment_request_dto_validates_required_fields(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ShipmentRequestDto([]); // Missing required fields
    }
}
```

#### WebhookSignatureVerifierTest.php

```php
<?php

namespace Mayush\Shipping\Onessta\Tests\Unit\Client;

use Mayush\Shipping\Onessta\Client\WebhookSignatureVerifier;
use Mayush\Shipping\Onessta\Exceptions\SignatureVerificationException;
use Mayush\Shipping\Onessta\Tests\Unit\UnitTestCase;

class WebhookSignatureVerifierTest extends UnitTestCase
{
    protected WebhookSignatureVerifier $verifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->verifier = new WebhookSignatureVerifier();
    }

    public function test_accepts_valid_signature(): void
    {
        $payload = '{"code":"ORD-1","status":"DELIVERED"}';
        $secret = 'webhook-secret-key';
        $signature = hash_hmac('sha256', $payload, $secret);

        config(['onessta.webhook.secret' => $secret]);

        $result = $this->verifier->verify($payload, $signature);

        $this->assertTrue($result);
    }

    public function test_rejects_invalid_signature(): void
    {
        $payload = '{"code":"ORD-1","status":"DELIVERED"}';
        config(['onessta.webhook.secret' => 'real-secret']);
        config(['onessta.webhook.fail_on_signature_mismatch' => true]);

        $this->expectException(SignatureVerificationException::class);
        $this->verifier->verify($payload, 'invalid-signature');
    }

    public function test_rejects_when_secret_not_configured(): void
    {
        config(['onessta.webhook.secret' => null]);

        $this->expectException(SignatureVerificationException::class);
        $this->verifier->verify('{}', 'any-signature');
    }

    public function test_validates_against_empty_payload(): void
    {
        config(['onessta.webhook.secret' => 'secret']);

        $this->expectException(SignatureVerificationException::class);
        $this->verifier->verify('', 'some-signature');
    }
}
```

### 3.4 Base Test Class

```php
<?php

namespace Mayush\Shipping\Onessta\Tests\Unit;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Mayush\Shipping\Onessta\OnesstaShippingServiceProvider;
use Illuminate\Support\Facades\Config;

abstract class UnitTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Set default test config
        Config::set('onessta.enabled', true);
        Config::set('onessta.mode', 'live');
        Config::set('onessta.base_url', 'https://api.onessta.com/api/v1');
        Config::set('onessta.auth.token', 'test-token');
        Config::set('onessta.auth.api_key', 'test-api-key');
        Config::set('onessta.auth.client_id', 'test-client-id');
        Config::set('onessta.http.timeout', 30);
        Config::set('onessta.http.connect_timeout', 10);
        Config::set('onessta.http.retry_times', 3);
        Config::set('onessta.webhook.secret', 'webhook-secret-key');
        Config::set('onessta.webhook.fail_on_signature_mismatch', true);
    }
}
```

---

## 4. Test Case Documentation

### 4.1 Unit Test Cases

| ID | Test Class | Test Method | Input | Expected Output | Status |
|---|---|---|---|---|---|
| TC-U-001 | OnesstaClientTest | `test_auth_headers_are_built_correctly` | POST /test | Headers contain Bearer/API-Key/Client-ID | ⬜ |
| TC-U-002 | OnesstaClientTest | `test_retry_on_502_error` | HTTP 502 → 200 | Response is 200, 2 requests made | ⬜ |
| TC-U-003 | OnesstaClientTest | `test_no_retry_on_401_error` | HTTP 401 | Throw AuthenticationException | ⬜ |
| TC-U-004 | OnesstaClientTest | `test_timeout_config_respected` | POST /test | Request timeout matches config | ⬜ |
| TC-U-005 | RequestSignerTest | `test_generates_valid_hmac_sha256` | payload + secret | Valid HMAC string | ⬜ |
| TC-U-006 | RequestSignerTest | `test_different_payloads_produce_different_signatures` | payload1, payload2 | Different HMAC strings | ⬜ |
| TC-U-007 | WebhookSignatureVerifierTest | `test_accepts_valid_signature` | valid payload + signature | `true` | ⬜ |
| TC-U-008 | WebhookSignatureVerifierTest | `test_rejects_invalid_signature` | payload + wrong sig | Throw SignatureVerificationException | ⬜ |
| TC-U-009 | WebhookSignatureVerifierTest | `test_rejects_empty_secret` | secret = null | Throw SignatureVerificationException | ⬜ |
| TC-U-010 | ShipmentServiceTest | `test_create_shipment_maps_all_fields` | ShipmentRequestDto | OnesstaShipment persisted | ⬜ |
| TC-U-011 | ShipmentServiceTest | `test_create_shipment_throws_on_duplicate_code` | duplicate code | Throw ValidationException | ⬜ |
| TC-U-012 | ShipmentServiceTest | `test_fetch_by_code_returns_shipment` | valid code | OnesstaShipment with code | ⬜ |
| TC-U-013 | ShipmentServiceTest | `test_fetch_by_code_throws_on_not_found` | invalid code | Throw RemoteApiException | ⬜ |
| TC-U-014 | TrackingServiceTest | `test_track_returns_tracking_response` | code | TrackingResponseDto with history | ⬜ |
| TC-U-015 | TrackingServiceTest | `test_persist_events_skips_duplicates` | duplicate events | 0 new events inserted | ⬜ |
| TC-U-016 | TrackingServiceTest | `test_maps_onessta_status_to_delivery_status` | DELIVERED | delivery_status = 'delivered' | ⬜ |
| TC-U-017 | ReferenceDataServiceTest | `test_sync_cities_caches_result` | GET /p/cities | Cache hit on second call | ⬜ |
| TC-U-018 | ReferenceDataServiceTest | `test_sync_cities_upserts_not_duplicates` | re-sync | same count, no duplicates | ⬜ |
| TC-U-019 | ReferenceDataServiceTest | `test_resolve_city_returns_remote_id` | local city id | remote city id or null | ⬜ |
| TC-U-020 | ReferenceDataServiceTest | `test_resolve_city_returns_null_for_unmapped` | unmapped id | null | ⬜ |
| TC-U-021 | ShipmentRequestDtoTest | `test_validates_required_fields` | empty array | Throw InvalidArgumentException | ⬜ |
| TC-U-022 | ShipmentRequestDtoTest | `test_builds_correct_json_structure` | valid data | JSON matches spec | ⬜ |
| TC-U-023 | ShipmentResponseDtoTest | `test_parses_status_correctly` | API response | Status field populated | ⬜ |
| TC-U-024 | CreateShipmentJobTest | `test_job_retries_on_failure` | API timeout | 3 retries then failed event | ⬜ |
| TC-U-025 | SyncCitiesJobTest | `test_respects_cache_ttl` | within TTL | No HTTP call made | ⬜ |
| TC-U-026 | ProcessWebhookJobTest | `test_processes_status_updated_event` | status_updated payload | Status updated in DB | ⬜ |
| TC-U-027 | ProcessWebhookJobTest | `test_ignores_duplicate_webhook` | same payload twice | Second call ignored | ⬜ |

### 4.2 Feature Test Cases

| ID | Test Class | Test Method | Scenario | Expected |
|---|---|---|---|---|
| TC-F-001 | WebhookTest | `test_webhook_returns_200_quickly` | Valid webhook received | 200 OK < 30s | ⬜ |
| TC-F-002 | WebhookTest | `test_webhook_rejects_invalid_signature` | Wrong X-Signature | 403 Forbidden | ⬜ |
| TC-F-003 | WebhookTest | `test_webhook_logs_raw_payload` | Any webhook | Log entry created | ⬜ |
| TC-F-004 | ShipmentCreationTest | `test_order_observer_dispatches_job` | Paid order with onessta shipping | CreateShipmentJob dispatched | ⬜ |
| TC-F-005 | ShipmentCreationTest | `test_shipment_created_event_fired` | Successful creation | Event dispatched | ⬜ |
| TC-F-006 | TrackingSyncTest | `test_poll_tracking_updates_existing_shipment` | Running job | History events added | ⬜ |
| TC-F-007 | CityMappingTest | `test_city_sync_populates_mapping_table` | SyncCitiesJob run | Mappings created | ⬜ |
| TC-F-008 | CityMappingTest | `test_resolve_city_after_sync` | After sync | Correct mapping returned | ⬜ |

---

## 5. Mock Data Requirements

### 5.1 Mock ONESSTA API Responses

```php
<?php

namespace Mayush\Shipping\Onessta\Tests\Stubs;

class OnestaApiStub
{
    public static function shipmentCreatedResponse(): array
    {
        return [
            'code' => 'ORD-12345',
            'status' => 'WAITING_PICKUP',
            'situation' => 'none',
            'receiver' => 'John Doe',
            'phone' => '+1234567890',
            'address' => '123 Main St, City',
            'price' => 99.99,
            'city' => ['id' => 1, 'name' => 'Test City'],
            'created_at' => '2026-06-20T10:00:00Z',
            'updated_at' => '2026-06-20T10:00:00Z',
        ];
    }

    public static function trackingResponse(): array
    {
        return [
            'code' => 'ORD-12345',
            'status' => 'PICKED_UP',
            'situation' => 'in_transit',
            'history' => [
                [
                    'status' => 'WAITING_PICKUP',
                    'name' => 'Awaiting Pickup',
                    'created_at' => '2026-06-20T10:00:00Z',
                    'new_date' => '2026-06-20',
                ],
                [
                    'status' => 'PICKED_UP',
                    'name' => 'Picked Up',
                    'created_at' => '2026-06-20T14:00:00Z',
                    'new_date' => '2026-06-20',
                ],
            ],
        ];
    }

    public static function citiesResponse(): array
    {
        return [
            ['id' => 1, 'name' => 'City A'],
            ['id' => 2, 'name' => 'City B'],
            ['id' => 3, 'name' => 'City C'],
        ];
    }

    public static function webhookStatusUpdatedPayload(): array
    {
        return [
            'event' => 'parcel.status_updated',
            'code' => 'ORD-12345',
            'status' => 'SENT',
            'situation' => 'on_the_way',
            'timestamp' => '2026-06-20T16:00:00Z',
        ];
    }

    public static function errorCodeAlreadyUsed(): array
    {
        return ['error' => 'code already used'];
    }

    public static function errorCityNotFound(): array
    {
        return ['error' => 'city not found'];
    }
}
```

### 5.2 Mock Order Fixture

```php
<?php

namespace Mayush\Shipping\Onessta\Tests\Stubs;

use App\Models\Order;
use App\Models\User;
use App\Models\City;
use Illuminate\Support\Facades\Auth;

class OrderStub
{
    public static function createPaidOnesstaOrder(): Order
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'phone' => '+1234567890',
        ]);

        $city = City::factory()->create([
            'name' => 'Test City',
            'country_id' => 1,
        ]);

        $order = Order::factory()->create([
            'code' => 'ORD-' . uniqid(),
            'user_id' => $user->id,
            'seller_id' => 1,
            'payment_status' => 'paid',
            'delivery_status' => 'pending',
            'shipping_method' => 'onessta',
            'grand_total' => 99.99,
            'shipping_address' => json_encode([
                'name' => 'John Doe',
                'phone' => '+1234567890',
                'address' => '123 Main St, City',
                'city_id' => $city->id,
                'postal_code' => '12345',
            ]),
        ]);

        return $order;
    }
}
```

---

## 6. Integration Testing Procedures

### 6.1 Package Installation Test

```bash
# 1. Mount package via path repository in composer.json
composer config repositories.onessta '{"type":"path","url":"packages/mayush/onessta-shipping","options":{"symlink":true}}'
composer require mayush/onessta-shipping:* --dev

# 2. Verify service provider auto-discovers or register manually
# Add to config/app.php:
# 'providers' => [
#     Mayush\Shipping\Onessta\OnesstaShippingServiceProvider::class,
# ],

# 3. Publish config
php artisan vendor:publish --tag=onessta-config

# 4. Run migrations
php artisan migrate

# 5. Verify routes loaded
php artisan route:list | grep onessta
php artisan route:list | grep webhook

# Expected:
# POST webhooks/onessta
# GET  admin/shipping/onessta/sync-cities
# POST admin/shipping/onessta/sync-cities
```

### 6.2 Webhook Integration Test (Ngrok)

```bash
# 1. Start ngrok tunnel
ngrok http 80

# 2. Copy forwarding URL
# https://abc123.ngrok.io

# 3. Set webhook URL in ONESSTA dashboard:
# https://abc123.ngrok.io/webhooks/onessta

# 4. Configure env vars
ONESSTA_WEBHOOK_API_KEY=your-webhook-api-key
ONESSTA_WEBHOOK_SECRET=your-webhook-secret

# 5. Trigger test order in Mayush
# POST /webhooks/onessta from ONESSTA

# 6. Verify:
# - 200 OK returned quickly
# - onessta_webhook_logs entry created
# - ProcessWebhookJob dispatched
```

### 6.3 Shipment Creation Integration Test

```bash
# 1. Configure credentials
ONESSTA_ENABLED=true
ONESSTA_TOKEN=your-token
ONESSTA_API_KEY=your-api-key
ONESSTA_CLIENT_ID=your-client-id

# 2. Create test order with shipping_method = 'onessta'
# (via admin panel or API)

# 3. Verify:
# - OnesstaShipment record created
# - order.shipping_method = 'onessta'
# - Events fired correctly
# - Tracking appears in ONESSTA dashboard
```

### 6.4 City Sync Integration Test

```bash
# 1. Clear existing mappings
php artisan tinker
>>> App\Models\OnesstaCityMap::truncate();

# 2. Run sync manually
php artisan onessta:sync-cities

# 3. Verify
php artisan tinker
>>> dd(App\Models\OnesstaCityMap::count()); // Should be > 0
>>> App\Models\OnesstaCityMap::first();     // Check mapping

# 4. Check cache
php artisan cache:get onessta:cities 2>/dev/null || echo "Not cached"

# 5. Run again — should use cache
php artisan onessta:sync-cities --use-cache

# 6. Force refresh
php artisan onessta:sync-cities --force
```

---

## 7. Quality Standards

### 7.1 Code Quality

| Standard | Requirement |
|---|---|
| PHP Version | >= 8.1 |
| Style | PSR-12 |
| Type Declarations | All method signatures must have return types |
| DocBlocks | All public methods documented |
| Null Safety | All properties/return types use `?` where nullable |
| Exceptions | Custom exceptions for all failure modes |

### 7.2 Testing Standards

| Standard | Requirement |
|---|---|
| Min. Coverage | 80% overall |
| Critical Paths | 100% coverage on OnesstaClient, Signer, Verifier |
| Mocking | Use Mockery for external HTTP calls |
| Data Providers | Use `@dataProvider` for parametric tests |
| Naming | `test_<method>_<scenario>_<expected>` |
| Independence | Tests must not depend on each other |

### 7.3 Security Standards

| Standard | Requirement |
|---|---|
| Secrets | Never log or expose auth tokens |
| Webhooks | Always verify signature before processing |
| Input Validation | Validate all DTOs before API calls |
| Rate Limiting | Local throttle on all polling operations |
| Fail Mode | Fail closed: `ONESSTA_FAIL_ON_SIGNATURE_MISMATCH=true` |

### 7.4 Performance Standards

| Standard | Requirement |
|---|---|
| Webhook Response | < 30 seconds |
| City Sync | < 60 seconds |
| Tracking Poll | < 5 minutes per parcel |
| API Timeout | 30 seconds |
| Cache TTL (Cities) | 24 hours |
| Cache TTL (Tracking) | 5 minutes |

---

## 8. Success Criteria

### Pre-Deployment Checklist

#### Core
- [ ] Package installs via Composer path repository
- [ ] Service provider auto-discovers or registers manually
- [ ] Config publishes correctly
- [ ] All 5 migrations run and rollback cleanly
- [ ] Queue connection configured
- [ ] Cache store configured

#### Authentication
- [ ] Bearer token included on all API calls
- [ ] API-Key header sent correctly
- [ ] Client-ID header sent correctly
- [ ] Invalid credentials produce `AuthenticationException`

#### Reference Data
- [ ] `GET /p/cities` syncs successfully
- [ ] `GET /p/pickup_cities` syncs successfully
- [ ] Cache hit and refresh verified
- [ ] City mapping table populated
- [ ] Duplicate sync does not create duplicates

#### Shipment Flow
- [ ] Shipment create succeeds with valid payload
- [ ] Duplicate code returns `ValidationException`
- [ ] Shipment fetch by code succeeds
- [ ] `onessta_shipments` record contains raw request/response
- [ ] `order_id` correctly linked

#### Tracking
- [ ] Tracking fetch exercises successfully
- [ ] History rows persisted correctly
- [ ] Duplicate tracking rows prevented (unique constraint)
- [ ] Polling backoff verified

#### Webhooks
- [ ] `parcel.status_updated` processes correctly
- [ ] `parcel.status_second_updated` processes correctly
- [ ] `parcel.info_updated` processes correctly
- [ ] HMAC signature verified correctly
- [ ] Invalid signature rejection works
- [ ] Duplicate webhook replay is idempotent
- [ ] Endpoint responds within 30s

#### Unsupported Capabilities
- [ ] `quote()` method throws `UnsupportedCapabilityException`
- [ ] `getLabel()` throws `UnsupportedCapabilityException`
- [ ] Sandbox base URL marked pending vendor documentation
- [ ] Vendor rate-limit policy marked undocumented

#### Environment Validation
- [ ] Live credentials tested in live mode
- [ ] Sandbox blocked unless vendor provides sandbox docs

#### Testing
- [ ] All unit tests pass
- [ ] All feature tests pass
- [ ] Overall code coverage >= 80%
- [ ] Critical path coverage = 100%

---

## Milestone Summary

| Milestone | Duration | Key Deliverables |
|---|---|---|
| **M1: Foundation** | 1 week | Package skeleton, config, migrations, HTTP client, auth |
| **M2: Reference Data** | 1 week | Cities sync, pickup cities, cache layer, mapping |
| **M3: Shipment Core** | 1.5 weeks | Create shipment, fetch by code, observer, events |
| **M4: Tracking** | 1 week | Poll tracking, history persistence, status sync |
| **M5: Webhooks** | 1 week | Signature verification, async processing, idempotency |
| **M6: Catalog (Optional)** | 1 week | Product listing, stock updates |
| **M7: Testing & QA** | 1.5 weeks | Full test suite, 80%+ coverage, integration tests |
| **TOTAL** | **7+ weeks** | **Production-ready ONESSTA addon** |

---

## Appendix A: Error Codes

| Code | ONESSTA Meaning | Mayush Action |
|---|---|---|
| `Unauthenticated.` | Invalid credentials | Disable addon, alert admin |
| `invalid api key secret` | Wrong API key | Disable addon, alert admin |
| `invalid client ID` | Wrong client ID | Disable addon, alert admin |
| `required field missing` | Validation error | Log, do not retry |
| `city not found` | Unmapped city | Dispatch city sync, retry |
| `code already used` | Duplicate shipment | Skip, log warning |
| `reference already exists` | Duplicate reference | Skip, log warning |

---

## Appendix B: ONESSTA → Mayush Status Mapping

| ONESSTA Status | ONESSTA Situation | Mayush `delivery_status` |
|---|---|---|
| `WAITING_PICKUP` | none | `pending` |
| `PICKED_UP` | none | `picked_up` |
| `SENT` | `on_the_way` | `on_the_way` |
| `RECEIVED` | `at_hub` | `processing` |
| `DISTRIBUTION` | `out_for_delivery` | `out_for_delivery` |
| `DELIVERED` | none | `delivered` |
| `RETURNING` | none | `return_requested` |
| `RETURNED` | none | `refunded` |
| `CANCELLED` | none | `cancelled` |

---

*Document Version: 1.0*
*Created: 2026-06-20*
*Based on: ShippingProvider.txt*
