# Storefront Lighthouse 99 Runbook

## Deploy Order

1. Run migrations and restart Horizon.
2. Validate `php8.2 artisan schedule:list --no-ansi`.
3. Queue visible images first:

   ```bash
   php8.2 artisan images:audit --repair --priority-storefront --include-static --limit=500
   php8.2 artisan images:status
   ```

4. Keep the hourly resumable audit enabled while the `images` queue drains.
5. After active heroes have a medium WebP, run:

   ```bash
   php8.2 artisan storefront:performance-readiness
   ```

Original uploads are never replaced. SVG, GIF, and external URLs remain untouched.

## Staging Profiling

Set stable fixture slugs and run the median-of-three matrix:

```bash
set LIGHTHOUSE_BASE_URL=https://staging.example.com
set LIGHTHOUSE_CATEGORY_SLUG=office-furniture
set LIGHTHOUSE_PRODUCT_SLUG=representative-product
set LIGHTHOUSE_CLOUDFLARE_MODE=restricted-bypass
set LIGHTHOUSE_CACHE_STATE=warm
npm run performance:lighthouse
```

Reports are written to `storage/app/lighthouse`. Cart and checkout use a guest session created through the normal product add-to-cart control. Override `LIGHTHOUSE_ADD_TO_CART_SELECTOR` only when a staging fixture uses a specialized purchase button.

Set `STOREFRONT_SERVER_TIMING=true` on staging while investigating HTML response time. The HTML response reports application and database timings without enabling edge caching.

## Cloudflare

Create a WAF skip rule for the Lighthouse runner IP only. Do not weaken normal customer protection.

Create an immutable cache rule for hashed build files, uploads, fonts, WebP, and AVIF paths:

```text
/build/*
/assets/*
/uploads/*
```

Do not edge-cache storefront HTML because cart, checkout, and session state remain request-specific. Purge changed legacy asset paths once after rollout. Keep Rocket Loader disabled for application bundles.

Run a second smoke suite through normal Cloudflare protection. Record Cloudflare challenge, Turnstile, and analytics warnings separately from application regressions.

## Workers

Production Horizon includes `supervisor-images` with two processes, 256 MB memory, three attempts, and a 180-second timeout.

For non-Horizon Supervisor deployments, keep a dedicated process group:

```ini
[program:mayush-images]
command=php8.2 /home/mayushdesign/public_html/artisan queue:work redis --queue=images --tries=3 --timeout=180 --memory=256
numprocs=2
autostart=true
autorestart=true
stopwaitsecs=210
```
