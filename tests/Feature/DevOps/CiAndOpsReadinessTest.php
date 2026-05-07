<?php

namespace Tests\Feature\DevOps;

use Tests\TestCase;

class CiAndOpsReadinessTest extends TestCase
{
    public function test_quality_gates_run_production_safety_checks(): void
    {
        $workflow = file_get_contents(base_path('.github/workflows/quality-gates.yml'));

        $this->assertStringContainsString('composer validate --no-check-publish --strict', $workflow);
        $this->assertStringContainsString('grep -RInE', $workflow);
        $this->assertStringContainsString('php artisan config:cache', $workflow);
        $this->assertStringContainsString('php artisan route:cache', $workflow);
        $this->assertStringContainsString('php artisan test --stop-on-failure', $workflow);
    }

    public function test_deploy_quality_gate_matches_core_safety_checks(): void
    {
        $workflow = file_get_contents(base_path('.github/workflows/deploy.yml'));

        $this->assertStringContainsString('composer validate --no-check-publish --strict', $workflow);
        $this->assertStringContainsString('Block Production Debug Artifacts', $workflow);
        $this->assertStringContainsString('php artisan config:cache', $workflow);
        $this->assertStringContainsString('php artisan route:cache', $workflow);
        $this->assertStringContainsString('php artisan test --stop-on-failure', $workflow);
    }

    public function test_scheduler_and_queue_readiness_docs_exist(): void
    {
        $docs = file_get_contents(base_path('docs/scheduler-queue-readiness.md'));

        $this->assertStringContainsString('php8.2 artisan schedule:run', $docs);
        $this->assertStringContainsString('Mayush\Shipping\Onessta\Jobs\PollTrackingJob', $docs);
        $this->assertStringContainsString('AggregateDailyAnalyticsJob', $docs);
        $this->assertStringContainsString('[program:mayush-worker-default]', $docs);
        $this->assertStringContainsString('[program:mayush-worker-onessta]', $docs);
    }
}
