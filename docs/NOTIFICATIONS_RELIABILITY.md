# Mayush Notifications Reliability Architecture

This document outlines the advanced reliability patterns implemented for all Notification, Email, and SMS dispatches in Mayush Marketplace.

## 1. Transactional Outbox Pattern (`$afterCommit = true`)
To prevent "ghost notifications" (notifications sent even if a DB transaction rolls back), all critical notification classes (`OrderNotification`, `EmailManager`, `InvoiceEmailManager`, `SendSmsJob`) now define:
```php
public $afterCommit = true;
```
This guarantees payloads are only pushed to Redis/Horizon **after** MySQL confirms the transaction is safely committed.

## 2. Strict Idempotency Guards
Duplicate webhooks (like a CMI payment retry storm) or double-clicked buttons can cause duplicate notification dispatches. We mitigate this on two layers:
1. **Cache Locks**: `NotificationUtility` wraps dispatch operations in a 30-second `Cache::lock`. If a duplicate request arrives, it is suppressed silently.
2. **Native Redis Deduplication**: High-priority jobs implement `ShouldBeUniqueUntilProcessing` and define a custom `uniqueId()` tied to the order ID and state. If multiple jobs hit Redis, only one is processed.

## 3. Horizon Tracing & Telemetry
To trace a specific order's notifications across Emails, DB Notifications, and SMS:
- Jobs implement `public function tags()` returning arrays like `['order:1234', 'sms', 'notification:placed']`.
- Horizon UI now correctly maps these tags to track full lifecycle delivery.

## 4. Provider Rate Limiting
- `SendSmsJob` implements the `RateLimited` middleware. If we need to bulk-send stock alerts, it respects the provider API limits (e.g., 100 per minute) instead of failing or getting blacklisted.

## 5. Topological Queue Mapping
Notifications are heavily segmented to prevent low-priority jobs from blocking payment confirmations:
- `notifications`: Internal DB and Firebase notifications.
- `emails`: Invoice, tracking, and standard SMTP workflows.
- `sms`: External HTTP calls for Twilio, Nexmo, Fast2SMS.

## 6. Safe Serialization (PII Scrubbing)
Payloads passed to Notifications and Mailables have been constrained to ensure `failed_jobs` logs do not expose full `User` objects or raw passwords/tokens.

*Implemented by Mayush Reliability Engineering.*
