<?php

namespace App\Services\Maintenance;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class DatabaseRetentionAuditService
{
    public const PROTECTED_FOREVER = 'PROTECTED_FOREVER';
    public const ARCHIVE_BEFORE_PRUNE = 'ARCHIVE_BEFORE_PRUNE';
    public const DIRECT_PRUNE_CANDIDATE = 'DIRECT_PRUNE_CANDIDATE';
    public const UNKNOWN_PROTECTED = 'UNKNOWN_PROTECTED';

    private const REFERENCE_PATTERN = '/(user_id|seller_id|shop_id|product_id|upload_id|order_id|combined_order_id|payment_id|refund_id|invoice|transaction|wallet|payout|shipping|tracking|webhook|callback)/i';

    public function audit(?ConnectionInterface $connection = null): array
    {
        $this->stopObservabilityRecording();

        $connection ??= DB::connection();
        $tables = array_map(fn (array $table): array => array_merge($table, $this->classify($table['table'])), $this->inventory($connection));

        return [
            'connection' => $connection->getName(),
            'driver' => $connection->getDriverName(),
            'database' => $connection->getDatabaseName(),
            'table_count' => count($tables),
            'tables' => $tables,
            'groups' => $this->groupTables($tables),
        ];
    }

    public function classify(string $table): array
    {
        $normalized = strtolower($table);

        if (in_array($normalized, $this->configuredTables('protected_tables'), true)) {
            return [
                'category' => self::PROTECTED_FOREVER,
                'reason' => $this->protectedReason($normalized),
                'future_action' => 'Never automatically delete. Keep behind a protected-table blacklist.',
            ];
        }

        if (in_array($normalized, $this->configuredTables('archive_before_prune_tables'), true)) {
            return [
                'category' => self::ARCHIVE_BEFORE_PRUNE,
                'reason' => 'Operational, audit, analytics, notification, webhook, or support log that may be useful for disputes or diagnostics.',
                'future_action' => 'Archive/export first, then require manual approval before any future pruning.',
            ];
        }

        if (in_array($normalized, $this->configuredTables('direct_prune_candidate_tables'), true)) {
            return [
                'category' => self::DIRECT_PRUNE_CANDIDATE,
                'reason' => 'Clearly technical transient data, queue noise, expired token data, or observability metrics.',
                'future_action' => 'Only consider in a future dry-run pruning command with tests and manual approval.',
            ];
        }

        if (in_array($normalized, $this->configuredTables('unknown_protected_tables'), true)) {
            return [
                'category' => self::UNKNOWN_PROTECTED,
                'reason' => 'Explicitly marked for manual review and protected until reviewed.',
                'future_action' => 'Do not prune. Manually review ownership and business/legal impact first.',
            ];
        }

        return [
            'category' => self::UNKNOWN_PROTECTED,
            'reason' => 'No explicit classification exists, so the safety default is protected.',
            'future_action' => 'Do not prune. Add an explicit classification only after manual review.',
        ];
    }

    public function configuredTables(string $key): array
    {
        return array_values(array_unique(array_map(
            fn (string $table): string => strtolower($table),
            Config::get("mayush_retention.{$key}", [])
        )));
    }

    public function categories(): array
    {
        return [
            self::PROTECTED_FOREVER,
            self::ARCHIVE_BEFORE_PRUNE,
            self::DIRECT_PRUNE_CANDIDATE,
            self::UNKNOWN_PROTECTED,
        ];
    }

    private function inventory(ConnectionInterface $connection): array
    {
        return match ($connection->getDriverName()) {
            'mysql' => $this->mysqlInventory($connection),
            'sqlite' => $this->sqliteInventory($connection),
            default => $this->portableInventory($connection),
        };
    }

    private function mysqlInventory(ConnectionInterface $connection): array
    {
        $statusRows = collect($connection->select('SHOW TABLE STATUS'))->keyBy('Name');

        return $statusRows
            ->keys()
            ->sort()
            ->values()
            ->map(function (string $table) use ($connection, $statusRows): array {
                $columns = $connection->select('SHOW COLUMNS FROM '.$this->quoteMySqlIdentifier($table));
                $status = $statusRows->get($table);
                $row = $connection->selectOne('SELECT COUNT(*) AS row_count FROM '.$this->quoteMySqlIdentifier($table));

                return $this->tableInventoryRow(
                    $table,
                    (int) ($row->row_count ?? 0),
                    (int) (($status->Data_length ?? 0) + ($status->Index_length ?? 0)),
                    $columns,
                    'Field',
                    'Type',
                    'Key'
                );
            })
            ->all();
    }

    private function sqliteInventory(ConnectionInterface $connection): array
    {
        return collect($connection->select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"))
            ->pluck('name')
            ->sort()
            ->values()
            ->map(function (string $table) use ($connection): array {
                $columns = $connection->select("PRAGMA table_info('".str_replace("'", "''", $table)."')");
                $row = $connection->selectOne('SELECT COUNT(*) AS row_count FROM '.$this->quoteSqliteIdentifier($table));

                return $this->tableInventoryRow(
                    $table,
                    (int) ($row->row_count ?? 0),
                    null,
                    $columns,
                    'name',
                    'type',
                    'pk'
                );
            })
            ->all();
    }

    private function portableInventory(ConnectionInterface $connection): array
    {
        return collect($connection->getSchemaBuilder()->getTables())
            ->pluck('name')
            ->sort()
            ->values()
            ->map(fn (string $table): array => array_merge([
                'table' => $table,
                'rows' => 0,
                'size_bytes' => null,
                'primary_key' => [],
                'date_columns' => [],
                'status_columns' => [],
                'reference_columns' => [],
                'key_columns' => [],
            ]))
            ->all();
    }

    private function tableInventoryRow(
        string $table,
        int $rows,
        ?int $sizeBytes,
        array $columns,
        string $nameProperty,
        string $typeProperty,
        string $keyProperty
    ): array {
        $primaryKey = [];
        $dateColumns = [];
        $statusColumns = [];
        $referenceColumns = [];

        foreach ($columns as $column) {
            $name = (string) $column->{$nameProperty};
            $type = strtolower((string) $column->{$typeProperty});
            $key = $column->{$keyProperty} ?? null;

            if ($key === 'PRI' || $key === 1) {
                $primaryKey[] = $name;
            }

            if (preg_match('/(date|time|year|timestamp)/', $type) || preg_match('/(^|_)(date|time|at|expires|expired|created|updated|deleted)(_|$)/i', $name)) {
                $dateColumns[] = $name;
            }

            if (preg_match('/(^|_)(status|state|type|result|response|success|failed|active|approved|verified|confirmed|published)(_|$)/i', $name) || str_starts_with($name, 'is_')) {
                $statusColumns[] = $name;
            }

            if (preg_match(self::REFERENCE_PATTERN, $name)) {
                $referenceColumns[] = $name;
            }
        }

        $keyColumns = array_values(array_unique(array_merge($primaryKey, $referenceColumns)));

        return [
            'table' => $table,
            'rows' => $rows,
            'size_bytes' => $sizeBytes,
            'primary_key' => array_values(array_unique($primaryKey)),
            'date_columns' => array_values(array_unique($dateColumns)),
            'status_columns' => array_values(array_unique($statusColumns)),
            'reference_columns' => array_values(array_unique($referenceColumns)),
            'key_columns' => $keyColumns,
        ];
    }

    private function groupTables(array $tables): array
    {
        $groups = array_fill_keys($this->categories(), []);

        foreach ($tables as $table) {
            $groups[$table['category']][] = $table['table'];
        }

        foreach ($groups as $category => $tableNames) {
            sort($tableNames);
            $groups[$category] = $tableNames;
        }

        return $groups;
    }

    private function protectedReason(string $table): string
    {
        if (preg_match('/order|payment|callback|refund|wallet|transaction|commission|invoice|shipping|tracking|onessta|payku|proxypay/', $table)) {
            return 'Payment, order, refund, wallet, shipping, or accounting evidence that may be needed for tax, dispute, or legal proof.';
        }

        if (preg_match('/user|seller|shop|staff|role|permission|token|credential|address|ticket|message|conversation|review/', $table)) {
            return 'Identity, access, seller/customer, support, or trust data that must not be automatically deleted.';
        }

        if (preg_match('/product|category|brand|upload|stock|inventory|coupon|promotion|flash|preorder|attribute|color|size|warrant/', $table)) {
            return 'Catalog, merchandising, inventory, media, or storefront business content.';
        }

        if (preg_match('/setting|config|template|translation|language|currency|tax|zone|country|state|city|area|page|blog|addon|carrier|pickup/', $table)) {
            return 'Configuration, localization, tax, geographic, content, or integration data required by the application.';
        }

        return 'Business or application data protected by the retention safety blacklist.';
    }

    private function quoteMySqlIdentifier(string $identifier): string
    {
        return '`'.str_replace('`', '``', $identifier).'`';
    }

    private function quoteSqliteIdentifier(string $identifier): string
    {
        return '"'.str_replace('"', '""', $identifier).'"';
    }

    private function stopObservabilityRecording(): void
    {
        if (class_exists(\Laravel\Pulse\Facades\Pulse::class)) {
            \Laravel\Pulse\Facades\Pulse::stopRecording();
        }
    }
}
