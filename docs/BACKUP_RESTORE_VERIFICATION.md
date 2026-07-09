# Backup Restore Verification

## 1. Purpose

Mayush must have a safe, documented, testable backup and restore verification path before any future pruning preview or cleanup work. This audit adds read-only inspection and guarded restore planning only. It does not add automated pruning, automated restore execution, or production overwrite behavior.

## 2. Critical Data Covered

The backup plan must cover the database schema and data for users, sellers, shops, products, uploads, orders, combined_orders, order_details, payment_attempts, cmi_callback_logs, payments, payment_tokens, refunds/refund_requests, wallets, ONESSTA shipments/tracking, audit logs, notifications, product/catalog data, and business_settings.

File coverage must include uploads/media, product images, shop/seller documents, invoices if stored as files, generated documents, storage symlinks, and `.env.example`. Real `.env` secrets are required for runtime recovery but must be stored outside Git and outside public web paths.

## 3. Database Backup Requirements

The project already has `scripts/maintenance/backup-database.ps1`, which reads local environment settings and uses `mysqldump` for MySQL. The deploy workflow also creates a pre-migration `mysqldump` before production migrations. Database backups must include schema, data, routines, triggers, and transaction-safe export options.

Backups must not be created in Git-tracked paths. SQL dumps are ignored by `.gitignore`, and `storage/app/backups/` is explicitly ignored.

## 4. Upload/Media Backup Requirements

The current repo does not include an automated upload/media backup command or scheduler entry. Production operations must back up `public/uploads`, `public/all`, `public/storage`, `storage/app/public`, and any seller document or generated invoice/document directories used by the deployment.

Media backups should be versioned separately from database dumps so product images, shop documents, and upload records can be restored together.

## 5. Secrets Handling

Do not commit `.env`, backup archives, database dumps, APP_KEY, DB passwords, SMTP credentials, CMI/payment keys, ONESSTA keys, AWS keys, Backblaze keys, or API tokens. Audit commands intentionally print only non-secret labels such as connection name, driver, database name, disk name, and paths.

## 6. Backup Storage Rules

Store backups outside the web root, outside Git, and away from public download paths. Production deployment currently uses `/home/mayushdesign/db-backups` for pre-migration database backups. Local manual backups may use `storage/app/backups/`, but dumps under that path must remain ignored and must not be uploaded to Git.

Encrypted off-server storage is still an operations requirement. Keep encryption keys outside the application repository.

## 7. Backup Rotation Recommendation

Use a retention policy that keeps recent daily backups, weekly backups for medium-term recovery, and monthly backups for long-term legal or operational recovery. Rotation must be configured outside Git and should alert on failed backup creation, failed upload, missing encryption, or stale latest backup timestamps.

## 8. Restore Verification Procedure

Never restore into the active database. Use a sanitized local dump or a controlled backup and restore it only into a disposable local/test database such as `mayush_restore_verify`.

Run:

```bash
php artisan mayush:restore:verify --dump=/path/to/dump.sql --database=mayush_restore_verify --confirm
```

The command validates guardrails and prints manual instructions. It does not execute a restore.

After manual import into the disposable database, verify counts and spot-check users, products, orders, uploads, payment_attempts, cmi_callback_logs, refunds, sellers, shops, and ONESSTA records.

## 9. Local/Staging Restore Procedure

Use `APP_ENV=local` or `APP_ENV=testing`, or set `MAYUSH_ALLOW_RESTORE_TEST=true` for a controlled non-production staging verification. Confirm the target database is disposable and not equal to the current configured database.

Point a local/test `.env` at the disposable database after import, run `php artisan optimize:clear`, then run integrity checks and the relevant test suite. Keep real secrets out of terminal logs.

## 10. Production Restore Warning

Production restore is a separate emergency operation that requires human approval, a maintenance window, a fresh backup of the current state, and a tested rollback plan. The added verification command refuses production and must not be used to overwrite the active production database.

## 11. Integrity Checklist After Restore

Check database connectivity, migration table consistency, users count, sellers count, shops count, products count, uploads count, orders count, combined_orders count, order_details count, payment_attempts count, cmi_callback_logs count, payments/payment_tokens counts, refunds count, ONESSTA shipment/tracking counts, audit/notification counts, storage symlink validity, product image availability, seller document availability, invoice/generated document availability, queue/failed job state if needed, and application boot via `php artisan app:preflight-restore`.

## 12. Commands Added

`php artisan mayush:backup:audit` prints read-only backup readiness, package/script/scheduler status, storage paths, and critical coverage requirements. It creates no backup and mutates no rows.

`php artisan mayush:restore:verify --dump=/path/to/dump.sql --database=mayush_restore_verify --confirm` validates restore safety guards and prints manual local/staging restore verification steps. It does not run destructive SQL, does not restore over the current database, and does not print secrets.

## 13. Tests Added

`tests/Feature/Maintenance/BackupRestoreVerificationTest.php` proves the audit command runs, secrets are not exposed, rows are not mutated, production restore verification is refused, current-database targets are refused, explicit local/test guards are required, missing dumps are refused, backup files are not created in repo paths, critical tables are listed, and documentation exists.

## 14. Remaining Risks

Upload/media backups are not yet automated or scheduled in Laravel. Backup encryption and off-server rotation are operational requirements outside this code change. Restore verification still requires a human to create/import into a disposable database and run integrity checks. Production emergency restore needs a separate approved runbook and operator validation.

## 15. Production Checklist Before Pruning

Confirm a fresh database backup exists, confirm upload/media backup coverage exists, confirm off-server encrypted storage and rotation are active, run a local/staging restore verification into a disposable database, complete the integrity checklist, run `php artisan test`, review critical retention protections, and keep future pruning limited to dry-run-only preview until backup/restore evidence is accepted.
