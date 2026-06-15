# ONESSTA Shipping Addon for Mayush

A fully-featured Laravel addon package providing ONESSTA 3PL (Third-Party Logistics) integration for the Mayush E-Commerce Platform.

## Features

- ✅ **Shipment Creation** — Create shipments via ONESSTA API
- ✅ **Parcel Tracking** — Pull-based polling + Push-based webhooks
- ✅ **City Synchronization** — Sync cities and pickup cities from ONESSTA
- ✅ **Webhook Processing** — Real-time status updates with HMAC-SHA256 signature verification
- ✅ **Order Observer** — Automatically triggers shipment creation on paid orders
- ✅ **Stock Sync** — Optional product catalog and stock management
- ❌ **Shipping Quotes** — Not supported (UNDSOCUMENTED by ONESSTA)
- ❌ **Label Generation** — Not supported (UNDSOCUMENTED by ONESSTA)
- ❌ **Sandbox Mode** — Pending vendor documentation

## Requirements

- PHP >= 8.1
- Laravel >= 10.0
- Redis or file-based cache
- Queue worker (Redis recommended)

## Installation

### 1. Mount as Path Repository

Add to your `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "packages/mayush/onessta-shipping",
        "options": {"symlink": true}
    }
]
```

### 2. Require the Package

```bash
composer require mayush/onessta-shipping:* --dev
```

### 3. Publish Configuration

```bash
php artisan vendor:publish --tag=onessta-config
```

### 4. Configure Environment Variables

Add to your `.env`:

```env
ONESSTA_ENABLED=true
ONESSTA_MODE=live
ONESSTA_BASE_URL=https://api.onessta.com/api/v1
ONESSTA_TOKEN=your-bearer-token
ONESSTA_API_KEY=your-api-key
ONESSTA_CLIENT_ID=your-client-id
ONESSTA_WEBHOOK_SECRET=your-webhook-secret
ONESSTA_WEBHOOK_API_KEY=your-webhook-api-key
```

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Register the Observer

In `App\Providers\AppServiceProvider::boot()`:

```php
use App\Models\Order;
use Mayush\Shipping\Onessta\Observers\OrderObserver;

Order::observe(OrderObserver::class);
```

## Artisan Commands

```bash
# Sync cities
php artisan onessta:sync-cities
php artisan onessta:sync-cities --force

# Sync pickup cities
php artisan onessta:sync-pickup-cities
php artisan onessta:sync-pickup-cities --force

# Poll tracking for all active shipments
php artisan onessta:poll-tracking

# Flush caches
php artisan onessta:cache:flush-cities
php artisan onessta:cache:flush-pickup-cities
```

## Routes

### Webhook (Public)
```
POST /webhooks/onessta
```

### Admin (Authenticated)
```
GET  /admin/shipping/onessta
GET  /admin/shipping/onessta/shipments
POST /admin/shipping/onessta/sync-cities
POST /admin/shipping/onessta/sync-pickup-cities
POST /admin/shipping/onessta/poll-tracking
GET  /admin/shipping/onessta/validate-credentials
```

## Testing

```bash
# Run all tests
composer test

# Run with coverage
composer test-coverage
```

## License

Proprietary — Mayush E-Commerce Platform
