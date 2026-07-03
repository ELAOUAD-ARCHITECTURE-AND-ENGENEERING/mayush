# Mayush Observability & Monitoring

## 1. Purpose
This document outlines the observability and monitoring foundation for the Mayush Marketplace. The goal is to provide safe, read-only operational visibility into production health, specifically focusing on payments, shipping, queues, and performance anomalies, without exposing sensitive customer or financial data.

## 2. Tools Installed/Configured
- **Laravel Pulse:** For Application Performance Monitoring (APM), tracking slow requests, slow queries, and exceptions.
- **Laravel Horizon:** For Redis queue monitoring and failed job visibility.
- **Custom CLI Commands:** For targeted operational audits and health checks.
- **Domain-Driven Logging:** Segregated log channels for high-risk domains (payments, shipping).

## 3. Pulse Dashboard Access Rules
- **Path:** `/pulse`
- **Access:** Strictly restricted to authenticated users with `admin` or `staff` user types.
- **Protection:** Enforced via `viewPulse` Gate in `AuthServiceProvider`.

## 4. Horizon Dashboard Access Rules
- **Path:** `/horizon`
- **Access:** Strictly restricted to authenticated users with `admin` or `staff` user types.
- **Protection:** Enforced via `viewHorizon` Gate in `HorizonServiceProvider`.

## 5. Health Check Command
- **Command:** `php artisan mayush:health-check`
- **Purpose:** Fast, read-only validation of core infrastructure and basic anomalies.
- **Metrics Reported:** Environment, Debug Mode, Database/Queue/Cache connections, failed jobs, stuck payments, shipped unpaid orders, disk space.

## 6. Operations Audit Command
- **Command:** `php artisan mayush:operations:audit`
- **Purpose:** Deep-dive operational audit designed for NOC alerting and cron integration.
- **Metrics Reported:** Detailed CMI callback failures, duplicate callbacks, 24h stale payments, Horizon status.
- **Exit Code:** Returns `1` if critical anomalies (e.g., shipped unpaid orders, Horizon down) are detected.

## 7. Log Channels
All logs are daily rotated and stored in `storage/logs/`.
- `payments.log`: CMI callbacks, Express Buy failures, payment expirations.
- `shipping.log`: ONESSTA sync failures.
- `queues.log`: General queue anomalies.
- `search.log`: Semantic search and Gemini fallback failures.
- `security.log`: Authentication anomalies, suspicious API requests.
- `performance.log`: Slow queries and performance bottlenecks not caught by Pulse.

## 8. Payment Monitoring Signals
- Failed CMI callbacks (`processing_status != success`).
- Ignored duplicate CMI callbacks (replay attack or retry loops).
- Stuck payments (status `initiated` or `pending` > 24h).
- Payment expirations.

## 9. Shipping Monitoring Signals
- Shipped but unpaid orders (critical fraud/operational signal).
- Failed ONESSTA API shipments.

## 10. Queue Monitoring Signals
- Global failed jobs count.
- Pending image optimization jobs (queue backlog).
- Horizon master supervisor status.

## 11. Search Monitoring Signals
- Logged to `search.log`. Monitors failures when calling external LLM/Gemini APIs for semantic search.

## 12. Security Monitoring Signals
- Tracked in `security.log`.
- Unauthorized dashboard access attempts (403/404 on admin routes).

## 13. Nightwatch Recommendation/Status
- **Status:** Not fully enabled.
- **Recommendation:** Laravel Nightwatch / OhDear is recommended for external uptime and certificate monitoring. Placeholders (`NIGHTWATCH_ENABLED`, `NIGHTWATCH_TOKEN`) have been added to `.env.example`. Do not activate until credentials are provisioned.

## 14. Production Setup Checklist
- [ ] Ensure `APP_DEBUG=false` in `.env`.
- [ ] Run `php artisan pulse:work` in supervisor if needed for processing metrics.
- [ ] Run `php artisan horizon` in supervisor.
- [ ] Schedule `php artisan mayush:operations:audit` in scheduler/cron for regular anomaly alerting.
- [ ] Validate `/pulse` and `/horizon` are inaccessible to guests and customers.

## 15. Remaining Risks
- **MySQL Bloat:** Pulse can generate significant DB load. Ensure the `pulse:clear` command is scheduled or retention periods are kept minimal (e.g., 7 days).
- **Log Disk Usage:** New daily log channels may consume disk space. Log rotation handles this, but server storage should be monitored.
- **No Active Alerting:** Currently, the audit commands report to CLI but do not send emails or Slack messages. Integration with a notification channel is the next recommended step.

## 16. Pulse Retention Recommendation
- **Initial Setting:** Keep Pulse data retention around 7 days initially.
- **Cleanup Strategy:** Schedule `php artisan pulse:clear` daily or weekly if the database grows too quickly, or configure retention limits inside `config/pulse.php` if applicable.
- **Monitoring:** Monitor database size (specifically `pulse_*` tables) closely after launch. Adjust retention downwards if performance is impacted.

## 17. Alerting Recommendation for Operations Audit
- The `mayush:operations:audit` command returns a non-zero exit code upon detecting critical anomalies (e.g., Horizon inactive, shipped but unpaid orders).
- **Integration:** It is highly recommended to integrate this command with external alerting tools (like PagerDuty, Slack webhooks, or email) so the NOC is actively notified rather than relying on manual CLI checks.
