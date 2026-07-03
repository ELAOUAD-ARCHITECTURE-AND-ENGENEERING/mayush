# Mayush Horizon Queue Separation Architecture

This document outlines the advanced queue architecture and separation implemented to ensure that heavy, time-consuming background tasks do not delay critical operations such as payment processing, order confirmations, and system alerts.

## 1. Supervisor Segregation Strategy

The queue processing is divided into specific Horizon supervisors, each handling a specific tier of queues based on their criticality. This ensures resource allocation strictly adheres to business priorities.

- **`supervisor-critical`**
  - **Queues:** `critical`, `payments`, `shipping`
  - **Purpose:** Fast-track execution of non-negotiable operations (e.g., CMI payment completions, critical health alerts, ONESSTA shipment creation).
  - **Priority:** Highest. Handled immediately.

- **`supervisor-communications`**
  - **Queues:** `notifications`, `emails`, `sms`
  - **Purpose:** Send user-facing alerts and invoices reliably without being blocked by media or reporting tasks.
  - **Priority:** High. Handled quickly.

- **`supervisor-media-search`**
  - **Queues:** `search`, `embeddings`, `images`
  - **Purpose:** Handle payload-heavy and computationally expensive tasks like resizing uploaded images or generating semantic embeddings via the Gemini AI.
  - **Priority:** Medium. Timeouts are relaxed and processes isolated.

- **`supervisor-maintenance`**
  - **Queues:** `reports`, `audits`, `default`
  - **Purpose:** Nightly, daily, or infrequent bulk aggregations (e.g., generating vendor analytics or security metrics).
  - **Priority:** Lowest. `nice` values are strictly set, yielding CPU time to other processes.

## 2. Job Class Mapping

To maintain this isolation, the `$queue` property must explicitly map to one of the above queues in any newly created background jobs.

| Job Category | Target Queue | Target Timeout | Target Retries |
|---|---|---|---|
| ONESSTA API | `shipping` | `60s` - `300s` | `2-3` |
| Image Optimization | `images` | `180s` | `3` |
| Semantic Embeddings | `embeddings` | `120s` | `3` |
| Analytics & Reports | `reports`, `audits` | `120s` - `300s` | `1` |
| User Notifications | `notifications` | `60s` | `3` |
| System Alerts | `critical` | `60s` | `3` |
| Mailables | `emails` | `60s` | `3` |

## 3. Implementation Details

- **Constructors:** All queued classes (`Job`, `Mailable`, `Notification`, `Listener`) enforce their target queue via `$this->onQueue('queue-name');` within their constructor. This circumvents strict PHP 8.2 composition errors associated with the Laravel `Queueable` trait.
- **Failures:** `config/queue.php` correctly writes permanently failed jobs to the `failed_jobs` table. These should be periodically monitored via the operations audit.

## 4. Testing & Auditing

Run the following test to ensure supervisors and jobs remain mapped correctly:
```bash
php artisan test --filter=QueueArchitectureTest
```
