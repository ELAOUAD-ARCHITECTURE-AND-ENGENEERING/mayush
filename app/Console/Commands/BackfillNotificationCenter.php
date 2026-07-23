<?php

namespace App\Console\Commands;

use App\Models\NotificationDevice;
use App\Models\NotificationType;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BackfillNotificationCenter extends Command
{
    protected $signature = 'notifications:backfill
        {--dry-run : Inspect the legacy records without changing the database}
        {--batch=500 : Number of records to process per batch}
        {--notifications-from= : Resume notifications after this legacy notification id}
        {--devices-from=0 : Resume users after this numeric user id}
        {--only= : Run only notifications, devices, or types}';

    protected $description = 'Backfill legacy notification data into Notification Center v2 safely and idempotently.';

    private const TECHNICAL_TYPE_FIELDS = [
        'category',
        'severity',
        'mandatory_inbox',
        'default_in_app',
        'default_broadcast',
        'default_email',
        'default_sms',
        'default_push',
    ];

    private const LEGACY_TYPE_MAP = [
        'App\\Notifications\\OrderNotification' => 'order_placed',
        'App\\Notifications\\PayoutNotification' => 'payout_status_updated',
        'App\\Notifications\\PreorderNotification' => 'order_placed',
        'App\\Notifications\\ShopVerificationNotification' => 'seller_status_updated',
        'App\\Notifications\\ShopProductNotification' => 'product_status_updated',
        'App\\Notifications\\ProductRestockedNotification' => 'product_restocked',
        'App\\Notifications\\RestockNotification' => 'product_restocked',
        'App\\Notifications\\PredictiveRestockNotification' => 'stock_alert',
        'App\\Notifications\\CustomNotification' => 'custom',
        'App\\Notifications\\EliteApplicationNotification' => 'account_changed',
    ];

    public function handle(): int
    {
        $batch = max(1, (int) $this->option('batch'));
        $only = $this->option('only');
        $allowed = ['notifications', 'devices', 'types'];

        if ($only !== null && !in_array($only, $allowed, true)) {
            $this->error('The --only option must be notifications, devices, or types.');

            return self::INVALID;
        }

        if (!$this->hasV2Schema()) {
            $this->error('Notification Center v2 tables are missing. Run the additive migration first.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $this->line($dryRun ? 'Dry run: no database writes will be performed.' : 'Backfill started.');

        if ($only === null || $only === 'types') {
            $this->backfillTypes($dryRun);
        }
        if ($only === null || $only === 'notifications') {
            $this->backfillNotifications($batch, $dryRun);
        }
        if ($only === null || $only === 'devices') {
            $this->backfillDevices($batch, $dryRun);
        }

        $this->info($dryRun ? 'Dry run complete.' : 'Backfill complete. Re-running is safe.');

        return self::SUCCESS;
    }

    private function hasV2Schema(): bool
    {
        return Schema::hasTable('notification_events')
            && Schema::hasTable('notification_deliveries')
            && Schema::hasTable('notification_devices');
    }

    private function backfillTypes(bool $dryRun): void
    {
        if (!Schema::hasTable('notification_types')) {
            $this->warn('notification_types does not exist; type backfill skipped.');

            return;
        }

        $definitions = config('notifications_v2.events', []);
        $created = 0;
        $updated = 0;

        foreach ($definitions as $definition) {
            $type = (string) ($definition['type'] ?? '');
            if ($type === '') {
                continue;
            }

            $technical = $this->technicalTypeValues($definition);
            $existing = NotificationType::where('type', $type)->first();

            if (!$existing) {
                $values = array_merge([
                    'type' => $type,
                    'name' => $definition['title'] ?? 'Notification',
                    'default_text' => $definition['title'] ?? 'Notification',
                    'status' => 1,
                ], $this->onlyExistingColumns($technical));

                if (!$dryRun) {
                    NotificationType::create($values);
                }
                $created++;

                continue;
            }

            $updates = $this->onlyExistingColumns($technical);
            if ($updates === []) {
                continue;
            }

            if (!$dryRun) {
                // These are the only fields this command is allowed to change.
                $existing->forceFill($updates)->save();
            }
            $updated++;
        }

        $this->line("Types: {$created} missing, {$updated} technical updates.");
    }

    private function backfillNotifications(int $batch, bool $dryRun): void
    {
        if (!Schema::hasTable('notifications') || !Schema::hasColumn('notifications', 'notification_type_id')) {
            $this->warn('Legacy notifications or notification_type_id is missing; notification backfill skipped.');

            return;
        }

        $query = DB::table('notifications')
            ->select(['id', 'type', 'notification_type_id', 'category'])
            ->where(function ($query) {
                $query->whereIn('type', array_keys(self::LEGACY_TYPE_MAP))
                    ->orWhereIn('type', array_map(
                        static fn (string $type): string => lcfirst($type),
                        array_keys(self::LEGACY_TYPE_MAP)
                    ));
            })
            ->orderBy('id');

        $cursor = $this->option('notifications-from');
        if ($cursor !== null && $cursor !== '') {
            $query->where('id', '>', $cursor);
        }

        $total = (clone $query)->count();
        $normalized = 0;
        $linked = 0;
        $progress = $this->output->createProgressBar($total);
        $progress->setFormat(' %current%/%max% [%bar%] %message%');
        $progress->setMessage('notifications');
        $progress->start();
        $lastId = $cursor;

        while (true) {
            $records = (clone $query)
                ->when($lastId !== null && $lastId !== '', fn ($q) => $q->where('id', '>', $lastId))
                ->limit($batch)
                ->get();

            if ($records->isEmpty()) {
                break;
            }

            foreach ($records as $record) {
                $canonicalType = $this->canonicalNotificationClass((string) $record->type);
                $type = self::LEGACY_TYPE_MAP[$canonicalType] ?? null;

                if (!$dryRun && $canonicalType !== $record->type) {
                    DB::table('notifications')->where('id', $record->id)->update(['type' => $canonicalType]);
                }
                $normalized += $canonicalType !== $record->type ? 1 : 0;

                if ($type && is_null($record->notification_type_id)) {
                    $notificationTypeId = NotificationType::where('type', $type)->value('id');
                    if ($notificationTypeId) {
                        $updates = ['notification_type_id' => $notificationTypeId];
                        if (is_null($record->category) && Schema::hasColumn('notifications', 'category')) {
                            $updates['category'] = NotificationType::whereKey($notificationTypeId)->value('category');
                        }
                        if (!$dryRun) {
                            DB::table('notifications')->where('id', $record->id)->whereNull('notification_type_id')->update($updates);
                        }
                        $linked++;
                    }
                }

                $lastId = (string) $record->id;
                $progress->advance();
            }
        }

        $progress->finish();
        $this->newLine();
        $this->line("Notifications: {$normalized} class names normalized, {$linked} type links prepared.");
        if ($lastId !== null && $lastId !== '') {
            $this->line("Resume cursor: --notifications-from={$lastId}");
        }
    }

    private function backfillDevices(int $batch, bool $dryRun): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'device_token')) {
            $this->warn('users.device_token is missing; device backfill skipped.');

            return;
        }

        $lastId = max(0, (int) $this->option('devices-from'));
        $total = DB::table('users')
            ->where('id', '>', $lastId)
            ->whereNotNull('device_token')
            ->where('device_token', '!=', '')
            ->count();
        $migrated = 0;
        $conflicts = 0;
        $progress = $this->output->createProgressBar($total);
        $progress->setFormat(' %current%/%max% [%bar%] %message%');
        $progress->setMessage('devices');
        $progress->start();

        while (true) {
            $users = DB::table('users')
                ->select(['id', 'device_token'])
                ->where('id', '>', $lastId)
                ->whereNotNull('device_token')
                ->where('device_token', '!=', '')
                ->orderBy('id')
                ->limit($batch)
                ->get();

            if ($users->isEmpty()) {
                break;
            }

            foreach ($users as $user) {
                $token = (string) $user->device_token;
                $hash = hash('sha256', $token);
                $device = NotificationDevice::where('token_hash', $hash)->first();

                if ($device && (int) $device->user_id !== (int) $user->id) {
                    $conflicts++;
                } elseif (!$dryRun) {
                    try {
                        $device ??= new NotificationDevice([
                            'id' => (string) Str::uuid(),
                            'token_hash' => $hash,
                        ]);
                        $device->user_id = $user->id;
                        $device->token = $token;
                        $device->platform = $device->platform ?: 'legacy';
                        $device->last_seen_at ??= now();
                        $device->save();
                        $migrated++;
                    } catch (QueryException $exception) {
                        // A concurrent rerun may win the unique token_hash insert.
                        $winner = NotificationDevice::where('token_hash', $hash)->first();
                        if (!$winner || (int) $winner->user_id !== (int) $user->id) {
                            $conflicts++;
                        }
                    }
                } else {
                    $migrated++;
                }

                $lastId = (int) $user->id;
                $progress->advance();
            }
        }

        $progress->finish();
        $this->newLine();
        $this->line("Devices: {$migrated} prepared, {$conflicts} token conflicts skipped.");
        $this->line("Resume cursor: --devices-from={$lastId}");
    }

    private function canonicalNotificationClass(string $type): string
    {
        foreach (array_keys(self::LEGACY_TYPE_MAP) as $className) {
            if (strcasecmp($className, $type) === 0) {
                return $className;
            }
        }

        return $type;
    }

    private function technicalTypeValues(array $definition): array
    {
        $channels = array_flip($definition['channels'] ?? []);

        return [
            'category' => $definition['category'] ?? 'system',
            'severity' => $definition['severity'] ?? 'info',
            'mandatory_inbox' => (bool) ($definition['mandatory_inbox'] ?? false),
            'default_in_app' => isset($channels['in_app']),
            'default_broadcast' => isset($channels['broadcast']),
            'default_email' => isset($channels['email']),
            'default_sms' => isset($channels['sms']),
            'default_push' => isset($channels['push']),
        ];
    }

    private function onlyExistingColumns(array $values): array
    {
        return array_filter(
            $values,
            fn ($value, $column) => Schema::hasColumn('notification_types', $column),
            ARRAY_FILTER_USE_BOTH
        );
    }
}
