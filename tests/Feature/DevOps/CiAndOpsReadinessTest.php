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
        $this->assertStringContainsString('CMI_ALLOWED_IPS: ${{ secrets.CMI_ALLOWED_IPS }}', $workflow);
        $this->assertStringContainsString('CMI_CONFIG_FAIL', $workflow);
        $this->assertStringContainsString('php8.2 artisan cmi:diagnose --production', $workflow);
        $this->assertStringContainsString('php artisan config:cache', $workflow);
        $this->assertStringContainsString('php artisan route:cache', $workflow);
        $this->assertStringContainsString('php artisan test --stop-on-failure', $workflow);
    }

    public function test_scheduler_and_queue_readiness_docs_exist(): void
    {
        $docs = file_get_contents(base_path('docs/scheduler-queue-readiness.md'));

        $this->assertStringContainsString('php8.2 artisan schedule:run', $docs);
        $this->assertStringContainsString('ONESSTA_CREATE_SHIPMENT_QUEUE_CONNECTION=redis', $docs);
        $this->assertStringContainsString('Mayush\Shipping\Onessta\Jobs\PollTrackingJob', $docs);
        $this->assertStringContainsString('AggregateDailyAnalyticsJob', $docs);
        $this->assertStringContainsString('[program:mayush-worker-default]', $docs);
        $this->assertStringContainsString('[program:mayush-worker-onessta]', $docs);
    }

    public function test_onessta_create_shipment_queue_inherits_production_queue_connection(): void
    {
        $original = $this->captureEnv([
            'QUEUE_CONNECTION',
            'ONESSTA_QUEUE_CONNECTION',
            'ONESSTA_CREATE_SHIPMENT_QUEUE_CONNECTION',
        ]);

        try {
            putenv('QUEUE_CONNECTION=redis');
            $_ENV['QUEUE_CONNECTION'] = 'redis';
            $_SERVER['QUEUE_CONNECTION'] = 'redis';
            putenv('ONESSTA_QUEUE_CONNECTION');
            unset($_ENV['ONESSTA_QUEUE_CONNECTION'], $_SERVER['ONESSTA_QUEUE_CONNECTION']);
            putenv('ONESSTA_CREATE_SHIPMENT_QUEUE_CONNECTION');
            unset($_ENV['ONESSTA_CREATE_SHIPMENT_QUEUE_CONNECTION'], $_SERVER['ONESSTA_CREATE_SHIPMENT_QUEUE_CONNECTION']);

            $config = require base_path('packages/mayush/onessta-shipping/config/onessta.php');

            $this->assertSame('redis', $config['queue']['connection']);
            $this->assertSame('redis', $config['queue']['create_shipment_connection']);
        } finally {
            $this->restoreEnv($original);
        }
    }

    private function captureEnv(array $keys): array
    {
        $values = [];

        foreach ($keys as $key) {
            $values[$key] = [
                'putenv' => getenv($key),
                '_ENV' => array_key_exists($key, $_ENV) ? $_ENV[$key] : null,
                '_SERVER' => array_key_exists($key, $_SERVER) ? $_SERVER[$key] : null,
                'has_env' => array_key_exists($key, $_ENV),
                'has_server' => array_key_exists($key, $_SERVER),
            ];
        }

        return $values;
    }

    private function restoreEnv(array $values): void
    {
        foreach ($values as $key => $value) {
            if ($value['putenv'] === false) {
                putenv($key);
            } else {
                putenv($key . '=' . $value['putenv']);
            }

            if ($value['has_env']) {
                $_ENV[$key] = $value['_ENV'];
            } else {
                unset($_ENV[$key]);
            }

            if ($value['has_server']) {
                $_SERVER[$key] = $value['_SERVER'];
            } else {
                unset($_SERVER[$key]);
            }
        }
    }
}
