# Notification Center v2 production runbook

## What must run in production

Notification Center v2 requires:

- PHP 8.2 or newer with `bcmath`, `curl`, `dom`, `fileinfo`, `gd`, `intl`,
  `mbstring`, `pcntl`, `pdo_mysql`, `posix`, `redis`, `xml`, and `zip`.
- Redis for Laravel queues and Horizon.
- Horizon running continuously under Supervisor.
- Laravel's scheduler running once per minute.
- Reverb running on private address `127.0.0.1:8080` under Supervisor.
- Apache 2.4 with `mod_proxy`, `mod_proxy_http`, `mod_proxy_wstunnel`, and
  `mod_headers`, exposing Reverb through WSS.
- Existing working SMTP configuration for email.
- Existing Mayush SMS provider configuration before SMS is enabled.
- A Firebase service-account JSON file outside the repository before FCM is
  enabled.

The setup script supports Ubuntu/Debian. Other Linux distributions must install
equivalent packages manually.

## Files supplied for production

- `deploy/notifications-v2/production-setup.sh`: installation, deployment,
  migration, restart, and verification commands.
- `deploy/notifications-v2/production.env.example`: production environment
  template without real secrets.
- `deploy/notifications-v2/supervisor-reverb.conf.template`: Reverb process.
- `deploy/notifications-v2/supervisor-horizon.conf.template`: Horizon process.
- `deploy/notifications-v2/scheduler.cron.template`: Laravel scheduler.
- `deploy/apache/mayush-reverb-vhost.conf.example`: Apache 2.4 WSS reverse proxy.

## Required environment configuration

Copy the keys from `deploy/notifications-v2/production.env.example` into the
server's existing `.env`. Do not replace the entire production `.env`.

Generate independent secrets:

```bash
openssl rand -hex 32
openssl rand -hex 32
openssl rand -hex 32
```

Use the generated values for `REVERB_APP_KEY`, `REVERB_APP_SECRET`, and
`NOTIFICATION_DELIVERY_WEBHOOK_SECRET`. Never reuse `APP_KEY`, payment secrets,
or database credentials.

Initial safe flags:

```dotenv
NOTIFICATIONS_V2_ENABLED=false
NOTIFICATION_BROADCASTING_ENABLED=false
NOTIFICATION_SMS_ENABLED=false
FCM_V1_ENABLED=false
```

Keep all four flags `false` until the additive migration, backfill dry run,
Horizon verification, and application canary checks are complete. Enabling
`NOTIFICATIONS_V2_ENABLED` then enables immutable audit records and the durable
inbox while the other three flags continue to prevent unverified external/live
delivery.

### Reverb

Production values should resemble:

```dotenv
BROADCAST_DRIVER=reverb
REVERB_APP_ID=mayush-production
REVERB_APP_KEY=<generated-secret>
REVERB_APP_SECRET=<generated-secret>
REVERB_HOST=mayushdesign.com
REVERB_PORT=443
REVERB_SCHEME=https
REVERB_SERVER_HOST=127.0.0.1
REVERB_SERVER_PORT=8080
REVERB_ALLOWED_ORIGINS=https://mayushdesign.com
REVERB_SCALING_ENABLED=false
```

`REVERB_HOST` is a hostname without `https://`. Never use `*` in
`REVERB_ALLOWED_ORIGINS`.

### Firebase HTTP v1

Create or select a Firebase service account allowed to send FCM messages. Put
its JSON file outside `public_html`:

```bash
sudo install -d -m 0750 -o mayushdesign -g mayushdesign /home/mayushdesign/secrets
sudo install -m 0600 -o mayushdesign -g mayushdesign \
  /secure-upload/firebase.json \
  /home/mayushdesign/secrets/firebase.json
```

Configure:

```dotenv
FCM_PROJECT_ID=<firebase-project-id>
FCM_SERVICE_ACCOUNT_PATH=/home/mayushdesign/secrets/firebase.json
```

No service-account content or device token may be placed in Git or logs.

## First server installation

Run these commands after the notification code is deployed but before enabling
broadcast, SMS, or FCM:

```bash
cd /home/mayushdesign/public_html

sudo APP_DIR=/home/mayushdesign/public_html \
  APP_USER=mayushdesign \
  PHP_BIN=/usr/bin/php8.2 \
  bash deploy/notifications-v2/production-setup.sh install-system

APP_DIR=/home/mayushdesign/public_html \
  APP_USER=mayushdesign \
  PHP_BIN=/usr/bin/php8.2 \
  bash deploy/notifications-v2/production-setup.sh check

sudo APP_DIR=/home/mayushdesign/public_html \
  APP_USER=mayushdesign \
  PHP_BIN=/usr/bin/php8.2 \
  bash deploy/notifications-v2/production-setup.sh install-supervisor
```

`install-supervisor` installs Reverb, installs Horizon only when another Horizon
Supervisor program does not already exist, and installs the Laravel scheduler
only when no existing scheduler entry is found.

## Apache 2.4 WSS configuration

Merge `deploy/apache/mayush-reverb-vhost.conf.example` into the existing
HTTPS vhost. It proxies WebSocket traffic from `/app` and the Reverb HTTP API
from `/apps` to `127.0.0.1:8080`; port 8080 must remain private.

Validate and reload only after reviewing the merged vhost:

```bash
sudo apachectl configtest
sudo systemctl reload apache2
```

The browser must use the public `https://mayushdesign.com` / `wss://mayushdesign.com`
configuration. `REVERB_SERVER_HOST=127.0.0.1` is only the internal bind address.

Do not enable broadcasting until an authenticated WSS connection succeeds and
an unauthorized private-channel subscription is rejected.

## Deploy the schema and application

The deployment command validates the environment, creates a MySQL backup,
installs locked Composer dependencies, runs additive migrations, builds Laravel
caches, restarts Horizon/Reverb, performs a retention dry run, and verifies
routes and services:

```bash
cd /home/mayushdesign/public_html

APP_DIR=/home/mayushdesign/public_html \
APP_USER=mayushdesign \
PHP_BIN=/usr/bin/php8.2 \
BACKUP_DATABASE=true \
bash deploy/notifications-v2/production-setup.sh deploy
```

Storefront assets are committed. If the server must rebuild them, install Node
22 and run the command with `BUILD_ASSETS=true`.

## Activation order

### Stage 1: audit and inbox

Keep:

```dotenv
NOTIFICATIONS_V2_ENABLED=true
NOTIFICATION_BROADCASTING_ENABLED=false
NOTIFICATION_SMS_ENABLED=false
FCM_V1_ENABLED=false
```

Create test events for:

- Successful and failed payments.
- Order placement and status changes.
- Refund or dispute updates.
- Seller status changes.
- Payout changes.
- Security or account alerts.

Verify one `notification_events` row per occurrence, one delivery row per
recipient/channel, a mandatory inbox notification for every critical event, and
no duplicate audit or inbox row after a retry.

### Stage 2: Reverb

Set:

```dotenv
NOTIFICATION_BROADCASTING_ENABLED=true
```

Then run:

```bash
php8.2 artisan optimize:clear
php8.2 artisan config:cache
php8.2 artisan horizon:terminate
php8.2 artisan reverb:restart
sudo supervisorctl restart mayush-reverb
```

Test the unread badge, cross-tab read/archive updates, reconnect reconciliation,
browser acknowledgement, offline inbox retrieval, and Reverb unavailability.

### Stage 3: FCM

After the service-account file and project id are verified, set:

```dotenv
FCM_V1_ENABLED=true
```

Rebuild configuration and terminate Horizon. Send a canary push and verify the
delivery first becomes `sent`; only a supported callback may mark it
`delivered`. Verify invalid device tokens are revoked.

### Stage 4: SMS

Confirm the selected SMS provider, sender, credentials, and test destination in
Mayush. Then set:

```dotenv
NOTIFICATION_SMS_ENABLED=true
```

Rebuild configuration and terminate Horizon. Test a critical security event
with a controlled phone number before broader use.

## Verification and monitoring

Run at any time:

```bash
APP_DIR=/home/mayushdesign/public_html \
APP_USER=mayushdesign \
PHP_BIN=/usr/bin/php8.2 \
bash deploy/notifications-v2/production-setup.sh verify

php8.2 artisan notifications:prune-inbox --dry-run
php8.2 artisan schedule:list
sudo supervisorctl status
```

Monitor Horizon and Pulse for queue latency, retries, failed deliveries,
delivery states, Reverb activity, acknowledgements, and revoked device tokens.
Logs must contain event/delivery IDs only—not notification content, personal
data, provider credentials, or device tokens.

## Rollback

Disable channels first:

```dotenv
NOTIFICATION_BROADCASTING_ENABLED=false
NOTIFICATION_SMS_ENABLED=false
FCM_V1_ENABLED=false
```

If required, also set:

```dotenv
NOTIFICATIONS_V2_ENABLED=false
```

Then clear and rebuild configuration and terminate Horizon. Do not reverse the
additive migration and do not delete `notification_events`,
`notification_deliveries`, or `notification_delivery_attempts`. Their audit
history is required for reconciliation.
