# Mayush Marketplace Rate Limiting & Security Hardening

## Overview
As part of the observability and security hardening process, advanced rate limiting was implemented across sensitive routes to protect Mayush Marketplace from brute force, credential stuffing, DDoS, and API abuse.

The rate limiting was implemented using Laravel 10's native Rate Limiter, ensuring that all policies are clearly defined in `App\Providers\RouteServiceProvider` and applied granularly across the application routes.

## Final Limiter Table

| Flow | Limiter Alias | Limit | Key Strategy | Routes Applied |
| :--- | :--- | :--- | :--- | :--- |
| **User Login** | `auth-login` | 5 / min | `IP + md5(email)` | `users/login`, `deliveryboy/login`, `users/login/cart` |
| **Seller Login** | `seller-login` | 5 / min | `IP + md5(email)` | `seller/login` |
| **Admin Login** | `admin-login` | 3 / min | `IP + md5(email)` | `admin/login` |
| **Registration** | `auth-register` | 3 / min | `IP` | `customer-reg/verification-code-send` |
| **Password Reset** | `password-reset`| 3 / min | `IP` | `password/reset/email/submit` |
| **Checkout Submit** | `checkout-submit`| 10 / min | `User ID fallback IP` | `checkout/*`, `cmi/pay` |
| **Express Buy** | `express-buy` | 3 / min | `User ID fallback IP` | `express-buy/*` |
| **CMI Webhooks** | `cmi-webhook` | 30 / min | `IP` | `cmi/callback` |
| **ONESSTA Webhooks**| `onessta-webhook`| 30 / min | `IP` | `webhooks/onessta` |
| **Search (Text)** | `search` | 60 / min | `User ID fallback IP` | `search*` |
| **Search (Semantic)**| `search-semantic`| 20 / min | `User ID fallback IP` | Semantic search endpoints |
| **Blog Subscribe** | `blog-lead` | 3 / min | `IP` | `blog/subscribe` |
| **Contact Form** | `contact-form` | 3 / min | `IP` | `contact` |
| **Seller App** | `seller-application`| 2 / min | `IP` | `shops` (POST) |
| **File Upload** | `file-upload` | 20 / min | `User ID fallback IP` | Upload endpoints |
| **Global API** | `api` | 60 / min | `User ID fallback IP` | `api/*` |

*Note: For login forms, we use an MD5 hash of the email/phone combined with the IP. This prevents attackers from hammering the same IP with different emails or hammering a specific email from different IPs, while keeping the PII (email/phone) mathematically hashed in the cache/Redis instance for privacy.*

## Security Logging & Monitoring (Global 429 Handler)

All rate limit exceedances (HTTP 429 ThrottleRequestsException) are caught globally in `App\Exceptions\Handler@render`. To prevent log and database floods during high-volume attacks, we implemented the following strategies:

1. **Cache-Based Suppression**: A 15-minute suppression window is applied per IP and path. Successive 429 errors from the same IP/path within 15 minutes will only be logged to the database and broadcasted once. Standard log file writes have a 1-minute suppression.
2. **Sensitive Route Classification**: Only 429 events hitting sensitive routes (login, registration, password reset, checkout, webhooks) generate an `AuditLog` database row and a real-time `SecurityAlert` event.
3. **Data Sanitization**: Passwords, CVVs, credit cards, and webhook payloads are explicitly stripped from any logged requests. Non-sensitive 429s (like rapid searching) write exclusively to the `security` Laravel logging channel.

## Webhook Safety
We verify that legitimate, high-volume callback traffic from payment gateways (CMI) and shipping providers (ONESSTA) are not falsely blocked:
- **CMI**: Allowed 30 requests per minute per IP. This safely accommodates their retries and bulk status updates without breaking payment flow.
- **ONESSTA**: Allowed 30 requests per minute per IP to handle shipping status webhooks.

## Tuning Guide
If false positives occur (e.g., legitimate traffic is blocked):
1. **Locate Limiter**: Open `App\Providers\RouteServiceProvider.php`.
2. **Adjust Limit**: Modify the `Limit::perMinute(...)` value. Do not increase webhook limits past 100/min without load testing first.
3. **Clear Cache**: Run `php artisan cache:clear` to clear active rate limiter blocks immediately.

## Remaining Risks
1. **Distributed Attacks**: Because most limiters group by IP (for guests), a highly distributed botnet using thousands of rotating residential proxies may still bypass the `x/min per IP` limit. In such cases, Cloudflare WAF rate limiting must be activated in front of the application.
2. **False Positives in NATs**: Large corporate offices or dorms sharing a single outbound public IPv4 might collectively trigger IP-based limits (e.g. `search` at 60/min). If users report getting blocked frequently while browsing, consider bumping guest search limits to 120/min.

## Tests
Rate limiting behavior is covered by 17 automated integration test scenarios located in:
`tests/Feature/Security/RateLimitingTest.php`

These tests verify valid traffic is allowed, brute force requests trigger a `429 Too Many Requests`, webhooks function normally under valid volumes, and the global exception handler properly suppresses database floods.
