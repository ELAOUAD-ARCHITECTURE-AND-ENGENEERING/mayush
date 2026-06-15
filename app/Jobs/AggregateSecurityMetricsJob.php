<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\AuditLog;
use App\Models\HealthMetric;
use App\Models\Analytics\AnalyticsDailySummary;
use Carbon\Carbon;

class AggregateSecurityMetricsJob implements ShouldQueue
{
    public $tries = 1;
    public $timeout = 120;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?string $dateToAggregate;

    public function __construct(?string $dateToAggregate = null)
    {
        $this->onQueue('audits');
        $this->dateToAggregate = $dateToAggregate;
    }

    public function handle(): void
    {
        $targetDate = $this->dateToAggregate 
            ? Carbon::parse($this->dateToAggregate) 
            : Carbon::yesterday();

        $startOfDay = $targetDate->copy()->startOfDay();
        $endOfDay = $targetDate->copy()->endOfDay();

        // Failed Logins
        $failedLogins = AuditLog::where('action_type', 'FAILED_LOGIN')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->count();
            
        $this->upsertSummary('failed_logins', 'global', $failedLogins, $targetDate->toDateString());

        // Real-time Warning for high failed login attempts
        if ($failedLogins > 50) {
            app(\App\Services\Security\AlertService::class)->send(
                "High volume of failed logins detected ($failedLogins) on " . $targetDate->toDateString(),
                'warning',
                ['metric' => 'failed_logins', 'date' => $targetDate->toDateString()]
            );
        }

        // Blocked Uploads / Malware
        $blockedUploads = AuditLog::where('action_type', 'MALWARE_BLOCKED')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->count();
            
        $this->upsertSummary('blocked_uploads', 'global', $blockedUploads, $targetDate->toDateString());

        if ($blockedUploads > 5) {
            app(\App\Services\Security\AlertService::class)->send(
                "Malware upload attempts detected ($blockedUploads) on " . $targetDate->toDateString(),
                'critical',
                ['metric' => 'blocked_uploads', 'date' => $targetDate->toDateString()]
            );
        }

        // Check average latency for performance tracking
        $avgLatency = HealthMetric::where('type', 'latency')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->avg('value') ?? 0;

        $this->upsertSummary('avg_latency', 'global', $avgLatency, $targetDate->toDateString());
    }

    private function upsertSummary(string $metric, string $dimension, float $value, string $date): void
    {
        AnalyticsDailySummary::updateOrCreate(
            ['metric_type' => $metric, 'dimension' => $dimension, 'date' => $date],
            ['value' => $value]
        );
    }
}
