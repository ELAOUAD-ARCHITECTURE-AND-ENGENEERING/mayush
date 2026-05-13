# Scheduler and Queue Readiness

Date: 2026-05-07

This document records the production scheduler and queue requirements for the Mayush Laravel marketplace.

## Scheduler

Production cron must run Laravel's scheduler every minute:

```cron
* * * * * cd /home/mayushdesign/public_html && php8.2 artisan schedule:run >> /dev/null 2>&1
```

Adjust the path and PHP binary for non-production environments.

Current `php artisan schedule:list` output:

```text
0   0 * * *  php artisan orders:cancel_unpaid
0   * * * *  php artisan orders:send_reminders
0   0 * * *  php artisan search:reindex
0   0 * * *  php artisan promotions:expire
0   * * * *  php artisan elite:expire
0   0 * * *  php artisan inventory:check-velocity
0   0 * * *  php artisan inventory:update-affinities --threshold=2
0   0 * * *  php artisan inventory:prune-affinities --days=30
0   * * * *  php artisan stock:send-alerts
30  2 * * *  php artisan app:generate-sitemap
0   2 * * *  php artisan vault:prune-expired
0   0 * * *  Mayush\Shipping\Onessta\Jobs\SyncCitiesJob
0   1 * * *  Mayush\Shipping\Onessta\Jobs\SyncPickupCitiesJob
*/5 * * * *  Mayush\Shipping\Onessta\Jobs\PollTrackingJob
5   0 * * *  App\Jobs\AggregateDailyAnalyticsJob
10  0 * * *  App\Jobs\AggregateVendorPerformanceJob
15  0 * * *  App\Jobs\AggregateMarketingMetricsJob
20  0 * * *  App\Jobs\AggregateSecurityMetricsJob
```

Scheduler notes:

- `stock:send-alerts` is hourly and safe to rerun; sent subscriptions are marked with `notified_at`.
- `app:generate-sitemap` rewrites the public sitemap and should run after route/cache deployment is healthy.
- ONESSTA polling runs every five minutes and requires valid ONESSTA credentials when `ONESSTA_ENABLED=true`.
- Analytics aggregation jobs use date-based upserts, so reruns replace the same daily snapshot instead of duplicating it.
- Search reindex and inventory affinity commands should be monitored after large catalog imports.

## Queues

Production must not rely on `QUEUE_CONNECTION=sync` for shipping, analytics, semantic search, or notification behavior.

Recommended production queue settings:

```env
QUEUE_CONNECTION=redis
ONESSTA_QUEUE_CONNECTION=redis
ONESSTA_CREATE_SHIPMENT_QUEUE_CONNECTION=redis
ONESSTA_QUEUE_NAME=onessta
```

Critical queued work:

- ONESSTA: `CreateShipmentJob`, `PollTrackingJob`, `ProcessWebhookJob`, `SyncCitiesJob`, `SyncPickupCitiesJob`.
- Analytics: `AggregateDailyAnalyticsJob`, `AggregateVendorPerformanceJob`, `AggregateMarketingMetricsJob`, `AggregateSecurityMetricsJob`.
- Search: `SyncSemanticEmbeddingJob`.
- Stock/notifications: stock alert mail dispatch and critical alert notifications.
- Catalog intelligence: `ProcessFrequentlyBoughtJob`.

Failure handling:

- ONESSTA create/webhook jobs log permanent failures and emit/update failure state.
- Semantic embedding job retries three times and logs a critical failure if retries are exhausted.
- Polling jobs log per-shipment failures and continue processing the remaining batch.
- Laravel `failed_jobs` storage should be configured and monitored for all queue workers.

## Supervisor Example

Use Horizon if it is enabled for the server. If Horizon is not used, run separate queue workers so ONESSTA does not block the default queue.

```ini
[program:mayush-worker-default]
process_name=%(program_name)s_%(process_num)02d
command=php8.2 /home/mayushdesign/public_html/artisan queue:work redis --queue=default,notifications,emails --sleep=3 --tries=3 --timeout=120
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=mayushdesign
numprocs=2
redirect_stderr=true
stdout_logfile=/home/mayushdesign/public_html/storage/logs/worker-default.log
stopwaitsecs=3600

[program:mayush-worker-onessta]
process_name=%(program_name)s_%(process_num)02d
command=php8.2 /home/mayushdesign/public_html/artisan queue:work redis --queue=onessta --sleep=3 --tries=3 --timeout=180
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=mayushdesign
numprocs=1
redirect_stderr=true
stdout_logfile=/home/mayushdesign/public_html/storage/logs/worker-onessta.log
stopwaitsecs=3600
```

After deploy:

```bash
php8.2 artisan queue:restart
php8.2 artisan horizon:terminate
php8.2 artisan schedule:list
php8.2 artisan queue:failed
```

## Production Checklist

- Cron entry exists and points to the active release path.
- Queue workers or Horizon are running after deploy.
- Redis is reachable when queue/cache/session use Redis.
- `failed_jobs` table exists and is monitored.
- ONESSTA credentials and webhook secrets are present when ONESSTA is enabled.
- `storage/logs` is writable by the PHP and queue users.
- `php artisan schedule:list` returns without errors after deployment.
