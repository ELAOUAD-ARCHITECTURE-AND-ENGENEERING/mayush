<?php

namespace App\Console\Commands;

use App\Services\Maintenance\DatabaseRetentionAuditService;
use Illuminate\Console\Command;

class DatabaseRetentionAuditCommand extends Command
{
    protected $signature = 'mayush:retention-audit';

    protected $description = 'Run a read-only database retention safety audit';

    public function handle(DatabaseRetentionAuditService $auditService): int
    {
        $audit = $auditService->audit();

        $this->warn('READ-ONLY AUDIT MODE. NO DATA WILL BE DELETED.');
        $this->line('Connection: '.$audit['connection'].' ('.$audit['driver'].')');
        $this->line('Database: '.$audit['database']);
        $this->line('Tables discovered: '.$audit['table_count']);
        $this->newLine();

        $this->table(
            ['Table', 'Rows', 'Size Bytes', 'Key Columns', 'Date Columns', 'Category'],
            array_map(fn (array $table): array => [
                $table['table'],
                $table['rows'],
                $table['size_bytes'] ?? 'n/a',
                $this->formatList($table['key_columns']),
                $this->formatList($table['date_columns']),
                $table['category'],
            ], $audit['tables'])
        );

        foreach ($auditService->categories() as $category) {
            $this->newLine();
            $this->info($category);
            $tables = $audit['groups'][$category] ?? [];
            $this->line($tables === [] ? '(none)' : implode(', ', $tables));
        }

        $this->newLine();
        $this->warn('No DELETE, UPDATE, TRUNCATE, DROP, ALTER, or pruning execution is performed by this command.');

        return self::SUCCESS;
    }

    private function formatList(array $values): string
    {
        return $values === [] ? '-' : implode(', ', $values);
    }
}
